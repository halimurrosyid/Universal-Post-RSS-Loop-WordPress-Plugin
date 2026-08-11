<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_RSS_Parser
 * Handles XML parsing for RSS 2.0 and Atom feeds, with rich validation and image extraction.
 */
class UPR_RSS_Parser {

	/**
	 * Fetch and parse an RSS or Atom feed from a URL.
	 *
	 * @param string $url RSS Feed URL.
	 * @param array  $options Optional parser settings.
	 * @return array|WP_Error Parsed data array on success or WP_Error on failure.
	 */
	public static function parse_feed( $url, array $options = array() ) {
		$url = trim( $url );
		if ( empty( $url ) ) {
			return new WP_Error( 'empty_url', __( 'RSS Feed URL is empty.', 'universal-post-rss-loop' ) );
		}

		// Security: Validate protocol
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		if ( ! in_array( strtolower( $scheme ), array( 'http', 'https' ), true ) ) {
			return new WP_Error( 'invalid_protocol', __( 'Only http:// and https:// URLs are allowed for security.', 'universal-post-rss-loop' ) );
		}

		// Remote HTTP request using WP HTTP API
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'     => 15,
				'redirection' => 5,
				'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) UniversalPostRSSLoop/' . UPR_VERSION . ' (WordPress Plugin)',
				'sslverify'   => apply_filters( 'upr_rss_ssl_verify', true ),
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_msg = $response->get_error_message();
			return new WP_Error(
				'http_request_failed',
				sprintf(
					/* translators: %s: detailed error message */
					__( 'Feed request failed (%s). Possible causes: Invalid URL, server blocking request, SSL handshake failure, or timeout.', 'universal-post-rss-loop' ),
					$error_msg
				)
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code !== 200 ) {
			return new WP_Error(
				'http_error_' . $status_code,
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Feed server returned HTTP status %d. Possible causes: Server block/WAF (403), Feed URL not found (404), or Server Error (500).', 'universal-post-rss-loop' ),
					$status_code
				)
			);
		}

		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		$body         = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return new WP_Error( 'empty_response', __( 'Feed server returned empty response content.', 'universal-post-rss-loop' ) );
		}

		// Security: Suppress libxml errors and disable external entity loading (Anti-XXE protection)
		libxml_use_internal_errors( true );
		$previous_entity_loader = false;
		if ( function_exists( 'libxml_disable_entity_loader' ) && PHP_VERSION_ID < 80000 ) {
			$previous_entity_loader = @libxml_disable_entity_loader( true );
		}

		$xml = simplexml_load_string( $body, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET );
		$xml_errors = libxml_get_errors();
		libxml_clear_errors();

		if ( function_exists( 'libxml_disable_entity_loader' ) && PHP_VERSION_ID < 80000 ) {
			@libxml_disable_entity_loader( $previous_entity_loader );
		}

		if ( ! $xml ) {
			$err_detail = ! empty( $xml_errors ) ? $xml_errors[0]->message : __( 'Invalid XML syntax', 'universal-post-rss-loop' );
			return new WP_Error(
				'xml_parse_error',
				sprintf(
					/* translators: %s: XML error message */
					__( 'Feed XML could not be parsed (%s). The URL might point to a web page instead of an RSS/Atom XML feed.', 'universal-post-rss-loop' ),
					trim( $err_detail )
				)
			);
		}

		// Detect RSS 2.0 vs Atom 1.0 vs RSS 1.0 (RDF)
		$feed_type = 'unknown';
		$items     = array();
		$channel_title = '';
		$channel_url   = '';

		if ( isset( $xml->channel ) ) {
			// RSS 2.0
			$feed_type     = 'RSS 2.0';
			$channel_title = (string) $xml->channel->title;
			$channel_url   = (string) $xml->channel->link;

			if ( isset( $xml->channel->item ) ) {
				foreach ( $xml->channel->item as $item ) {
					$items[] = self::parse_rss_item( $item, $channel_title, $channel_url, $options );
				}
			}
		} elseif ( $xml->getName() === 'feed' || isset( $xml->entry ) ) {
			// Atom 1.0
			$feed_type     = 'Atom 1.0';
			$channel_title = (string) $xml->title;
			
			// Find channel link
			if ( isset( $xml->link ) ) {
				foreach ( $xml->link as $link ) {
					$rel = (string) $link['rel'];
					if ( empty( $rel ) || $rel === 'alternate' ) {
						$channel_url = (string) $link['href'];
						break;
					}
				}
			}

			if ( isset( $xml->entry ) ) {
				foreach ( $xml->entry as $entry ) {
					$items[] = self::parse_atom_entry( $entry, $channel_title, $channel_url, $options );
				}
			}
		} else {
			return new WP_Error( 'unsupported_feed', __( 'Feed structure is not recognized as RSS 2.0 or Atom 1.0.', 'universal-post-rss-loop' ) );
		}

		return array(
			'http_status'   => $status_code,
			'content_type'  => $content_type,
			'feed_type'     => $feed_type,
			'channel_title' => $channel_title,
			'channel_url'   => $channel_url,
			'items'         => $items,
		);
	}

	/**
	 * Parse an RSS 2.0 item node.
	 */
	private static function parse_rss_item( $item, $channel_title, $channel_url, array $options ) {
		$namespaces = $item->getNamespaces( true );

		$title = (string) $item->title;
		$link  = (string) $item->link;
		$guid  = (string) $item->guid;
		if ( empty( $link ) && ! empty( $guid ) && filter_var( $guid, FILTER_VALIDATE_URL ) ) {
			$link = $guid;
		}

		// Publication date
		$pub_date  = (string) $item->pubDate;
		$timestamp = ! empty( $pub_date ) ? strtotime( $pub_date ) : time();
		$date_fmt  = $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : '';

		// Author
		$author = (string) $item->author;
		if ( empty( $author ) && isset( $namespaces['dc'] ) ) {
			$dc     = $item->children( $namespaces['dc'] );
			$author = (string) $dc->creator;
		}

		// Category
		$category = (string) $item->category;

		// Description & Content snippet
		$description = (string) $item->description;
		$content     = '';
		if ( isset( $namespaces['content'] ) ) {
			$content_ns = $item->children( $namespaces['content'] );
			$content    = (string) $content_ns->encoded;
		}

		$raw_html = ! empty( $content ) ? $content : $description;
		$excerpt  = wp_strip_all_tags( $description );
		if ( empty( $excerpt ) ) {
			$excerpt = wp_strip_all_tags( $content );
		}
		$excerpt = wp_trim_words( html_entity_decode( $excerpt, ENT_QUOTES, 'UTF-8' ), 30, '...' );

		// Image extraction hierarchy
		$image_url = self::extract_image_from_node( $item, $namespaces, $raw_html, $options );

		return array(
			'guid'        => $guid,
			'title'       => html_entity_decode( trim( $title ), ENT_QUOTES, 'UTF-8' ),
			'url'         => esc_url_raw( trim( $link ) ),
			'image'       => $image_url,
			'excerpt'     => $excerpt,
			'date'        => $date_fmt,
			'timestamp'   => $timestamp,
			'author'      => html_entity_decode( trim( $author ), ENT_QUOTES, 'UTF-8' ),
			'category'    => html_entity_decode( trim( $category ), ENT_QUOTES, 'UTF-8' ),
			'source_name' => html_entity_decode( trim( $channel_title ), ENT_QUOTES, 'UTF-8' ),
			'source_url'  => esc_url_raw( trim( $channel_url ) ),
		);
	}

	/**
	 * Parse an Atom 1.0 entry node.
	 */
	private static function parse_atom_entry( $entry, $channel_title, $channel_url, array $options ) {
		$namespaces = $entry->getNamespaces( true );

		$title = (string) $entry->title;
		$id    = (string) $entry->id;

		// Link
		$link = '';
		if ( isset( $entry->link ) ) {
			foreach ( $entry->link as $l ) {
				$rel = (string) $l['rel'];
				if ( empty( $rel ) || $rel === 'alternate' ) {
					$link = (string) $l['href'];
					break;
				}
			}
		}

		// Published / Updated date
		$pub_date  = (string) $entry->published;
		if ( empty( $pub_date ) ) {
			$pub_date = (string) $entry->updated;
		}
		$timestamp = ! empty( $pub_date ) ? strtotime( $pub_date ) : time();
		$date_fmt  = $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : '';

		// Author
		$author = '';
		if ( isset( $entry->author->name ) ) {
			$author = (string) $entry->author->name;
		}

		// Category
		$category = '';
		if ( isset( $entry->category['term'] ) ) {
			$category = (string) $entry->category['term'];
		}

		// Summary / Content
		$summary = (string) $entry->summary;
		$content = (string) $entry->content;
		$raw_html = ! empty( $content ) ? $content : $summary;

		$excerpt = wp_strip_all_tags( $summary );
		if ( empty( $excerpt ) ) {
			$excerpt = wp_strip_all_tags( $content );
		}
		$excerpt = wp_trim_words( html_entity_decode( $excerpt, ENT_QUOTES, 'UTF-8' ), 30, '...' );

		$image_url = self::extract_image_from_node( $entry, $namespaces, $raw_html, $options );

		return array(
			'guid'        => $id,
			'title'       => html_entity_decode( trim( $title ), ENT_QUOTES, 'UTF-8' ),
			'url'         => esc_url_raw( trim( $link ) ),
			'image'       => $image_url,
			'excerpt'     => $excerpt,
			'date'        => $date_fmt,
			'timestamp'   => $timestamp,
			'author'      => html_entity_decode( trim( $author ), ENT_QUOTES, 'UTF-8' ),
			'category'    => html_entity_decode( trim( $category ), ENT_QUOTES, 'UTF-8' ),
			'source_name' => html_entity_decode( trim( $channel_title ), ENT_QUOTES, 'UTF-8' ),
			'source_url'  => esc_url_raw( trim( $channel_url ) ),
		);
	}

	/**
	 * Extract image from node using 5-step fallback logic.
	 * 1. media:content / media:thumbnail
	 * 2. enclosure image
	 * 3. image element
	 * 4. 1st <img> from HTML content
	 * 5. fallback image option
	 */
	private static function extract_image_from_node( $node, array $namespaces, $raw_html, array $options ) {
		// 1. Check media:content / media:thumbnail
		if ( isset( $namespaces['media'] ) ) {
			$media = $node->children( $namespaces['media'] );
			if ( isset( $media->content ) ) {
				foreach ( $media->content as $m ) {
					$url  = (string) $m->attributes()->url;
					$type = (string) $m->attributes()->type;
					$medium = (string) $m->attributes()->medium;
					if ( ! empty( $url ) && ( empty( $type ) || strpos( $type, 'image' ) !== false || $medium === 'image' ) ) {
						return esc_url_raw( $url );
					}
				}
			}
			if ( isset( $media->thumbnail ) ) {
				$url = (string) $media->thumbnail->attributes()->url;
				if ( ! empty( $url ) ) {
					return esc_url_raw( $url );
				}
			}
		}

		// 2. Check enclosure
		if ( isset( $node->enclosure ) ) {
			foreach ( $node->enclosure as $enc ) {
				$url  = (string) $enc->attributes()->url;
				$type = (string) $enc->attributes()->type;
				if ( ! empty( $url ) && strpos( $type, 'image' ) !== false ) {
					return esc_url_raw( $url );
				}
			}
		}

		// 3. Check image child
		if ( isset( $node->image->url ) ) {
			$url = (string) $node->image->url;
			if ( ! empty( $url ) ) {
				return esc_url_raw( $url );
			}
		}

		// 4. First <img> tag in HTML content
		if ( ! empty( $raw_html ) ) {
			if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $raw_html, $matches ) ) {
				if ( ! empty( $matches[1] ) ) {
					return esc_url_raw( $matches[1] );
				}
			}
		}

		// 5. Fallback image option if specified
		if ( ! empty( $options['fallback_image'] ) ) {
			return esc_url_raw( $options['fallback_image'] );
		}

		return '';
	}
}
