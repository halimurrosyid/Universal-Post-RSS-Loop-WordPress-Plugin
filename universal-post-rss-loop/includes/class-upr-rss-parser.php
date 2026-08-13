<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_RSS_Parser
 * Handles XML parsing for RSS 2.0 and Atom feeds, with rich validation and image extraction. (v2.0.5)
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

		// Allow intranet/campus domain host requests (e.g. *.telkomuniversity.ac.id)
		add_filter( 'http_request_host_is_external', '__return_true', 999 );

		// Standard Chrome User-Agent string to bypass Cloudflare / ModSecurity WAF User-Agent blocks
		$user_agent = apply_filters(
			'upr_rss_user_agent',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 UniversalPostRSSLoop/' . UPR_VERSION
		);

		$request_args = array(
			'timeout'     => 15,
			'redirection' => 5,
			'user-agent'  => $user_agent,
			'sslverify'   => apply_filters( 'upr_rss_ssl_verify', false ),
			'headers'     => array(
				'Accept'        => 'application/rss+xml, application/xml, text/xml, */*',
				'Cache-Control' => 'no-cache',
			),
		);

		// Tier 1: Try safe remote get
		$response = wp_safe_remote_get( $url, $request_args );

		// Tier 2: Fallback to wp_remote_get if wp_safe_remote_get blocked internal/loopback IP resolution
		if ( is_wp_error( $response ) ) {
			$response = wp_remote_get( $url, $request_args );
		}

		// Tier 3: Fallback to direct cURL if WordPress HTTP API blocked host resolution on campus network
		if ( is_wp_error( $response ) && function_exists( 'curl_init' ) ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 5 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
			curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Accept: application/rss+xml, application/xml, text/xml, */*',
				'Cache-Control: no-cache',
			) );
			$curl_body = curl_exec( $ch );
			$curl_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			curl_close( $ch );

			if ( ! empty( $curl_body ) && $curl_code >= 200 && $curl_code < 400 ) {
				$response = array(
					'response' => array( 'code' => $curl_code ),
					'body'     => $curl_body,
				);
			}
		}

		remove_filter( 'http_request_host_is_external', '__return_true', 999 );

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

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new WP_Error( 'empty_body', __( 'Feed server returned an empty response body.', 'universal-post-rss-loop' ) );
		}

		return self::parse_xml( $body, $options );
	}

	/**
	 * Parse XML String into structured data array.
	 */
	public static function parse_xml( $xml_string, array $options = array() ) {
		// Anti-XXE XML Parser Settings
		$disable_entities = function_exists( 'libxml_disable_entity_loader' ) ? @libxml_disable_entity_loader( true ) : true;
		$use_errors       = libxml_use_internal_errors( true );

		$xml = simplexml_load_string( $xml_string, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOENT | LIBXML_NOWARNING | LIBXML_NOERROR );

		libxml_use_internal_errors( $use_errors );
		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			@libxml_disable_entity_loader( $disable_entities );
		}

		if ( ! $xml ) {
			return new WP_Error( 'xml_parse_error', __( 'Failed to parse XML syntax. Ensure the URL returns valid RSS or Atom XML format.', 'universal-post-rss-loop' ) );
		}

		$fallback_img = ! empty( $options['fallback_image'] ) ? $options['fallback_image'] : '';

		// Detect RSS vs Atom
		if ( isset( $xml->channel ) ) {
			return self::parse_rss2( $xml, $fallback_img );
		} elseif ( $xml->getName() === 'feed' ) {
			return self::parse_atom( $xml, $fallback_img );
		}

		return new WP_Error( 'unknown_feed_format', __( 'Unknown feed format. Only RSS 2.0 and Atom feeds are supported.', 'universal-post-rss-loop' ) );
	}

	/**
	 * Parse RSS 2.0 XML
	 */
	private static function parse_rss2( $xml, $fallback_img = '' ) {
		$source_name = (string) $xml->channel->title;
		$source_url  = (string) $xml->channel->link;
		$items       = array();

		foreach ( $xml->channel->item as $item ) {
			$title       = (string) $item->title;
			$url         = (string) $item->link;
			$guid        = (string) $item->guid;
			$author      = (string) $item->author;
			$pub_date    = (string) $item->pubDate;
			$category    = (string) $item->category;
			$description = (string) $item->description;

			// Content encoded (full HTML)
			$content_encoded = '';
			$namespaces      = $item->getNameSpaces( true );
			if ( isset( $namespaces['content'] ) ) {
				$content_ns      = $item->children( $namespaces['content'] );
				$content_encoded = (string) $content_ns->encoded;
			}

			// Extract best available featured image via 5-tier fallback
			$image = self::extract_image( $item, $description, $content_encoded, $fallback_img );

			// Format Date
			$formatted_date = '';
			if ( ! empty( $pub_date ) ) {
				$timestamp = strtotime( $pub_date );
				if ( $timestamp ) {
					$formatted_date = date_i18n( get_option( 'date_format' ), $timestamp );
				}
			}

			// Sanitize excerpt
			$excerpt_raw = ! empty( $description ) ? $description : $content_encoded;
			$excerpt     = wp_strip_all_tags( $excerpt_raw );

			$items[] = array(
				'title'       => trim( $title ),
				'url'         => trim( $url ),
				'guid'        => trim( $guid ),
				'image'       => esc_url_raw( $image ),
				'excerpt'     => trim( $excerpt ),
				'date'        => $formatted_date,
				'author'      => trim( $author ),
				'category'    => trim( $category ),
				'source_name' => trim( $source_name ),
				'source_url'  => trim( $source_url ),
				'is_external' => true,
			);
		}

		return array(
			'source_name' => $source_name,
			'source_url'  => $source_url,
			'items'       => $items,
		);
	}

	/**
	 * Parse Atom Feed XML
	 */
	private static function parse_atom( $xml, $fallback_img = '' ) {
		$source_name = (string) $xml->title;
		$source_url  = '';
		foreach ( $xml->link as $link ) {
			if ( (string) $link['rel'] === 'alternate' || empty( $link['rel'] ) ) {
				$source_url = (string) $link['href'];
				break;
			}
		}

		$items = array();
		foreach ( $xml->entry as $entry ) {
			$title   = (string) $entry->title;
			$url     = '';
			foreach ( $entry->link as $link ) {
				if ( (string) $link['rel'] === 'alternate' || empty( $link['rel'] ) ) {
					$url = (string) $link['href'];
					break;
				}
			}

			$guid        = (string) $entry->id;
			$author      = isset( $entry->author->name ) ? (string) $entry->author->name : '';
			$pub_date    = isset( $entry->published ) ? (string) $entry->published : (string) $entry->updated;
			$summary     = (string) $entry->summary;
			$content     = (string) $entry->content;

			$image       = self::extract_image( $entry, $summary, $content, $fallback_img );
			$formatted_d = '';
			if ( ! empty( $pub_date ) ) {
				$t = strtotime( $pub_date );
				if ( $t ) {
					$formatted_d = date_i18n( get_option( 'date_format' ), $t );
				}
			}

			$excerpt = wp_strip_all_tags( ! empty( $summary ) ? $summary : $content );

			$items[] = array(
				'title'       => trim( $title ),
				'url'         => trim( $url ),
				'guid'        => trim( $guid ),
				'image'       => esc_url_raw( $image ),
				'excerpt'     => trim( $excerpt ),
				'date'        => $formatted_d,
				'author'      => trim( $author ),
				'category'    => '',
				'source_name' => trim( $source_name ),
				'source_url'  => trim( $source_url ),
				'is_external' => true,
			);
		}

		return array(
			'source_name' => $source_name,
			'source_url'  => $source_url,
			'items'       => $items,
		);
	}

	/**
	 * Extract image with 5-tier fallback mechanism.
	 */
	private static function extract_image( $item, $html1 = '', $html2 = '', $fallback = '' ) {
		// Tier 1: media:content / media:thumbnail
		$namespaces = $item->getNameSpaces( true );
		if ( isset( $namespaces['media'] ) ) {
			$media = $item->children( $namespaces['media'] );
			if ( isset( $media->content ) ) {
				foreach ( $media->content as $m ) {
					$attributes = $m->attributes();
					if ( isset( $attributes['url'] ) ) {
						$url = (string) $attributes['url'];
						if ( preg_match( '/\.(jpg|jpeg|png|gif|webp|svg)/i', $url ) ) {
							return $url;
						}
					}
				}
			}
			if ( isset( $media->thumbnail ) ) {
				$attributes = $media->thumbnail->attributes();
				if ( isset( $attributes['url'] ) ) {
					return (string) $attributes['url'];
				}
			}
		}

		// Tier 2: enclosure
		if ( isset( $item->enclosure ) ) {
			foreach ( $item->enclosure as $enc ) {
				$type = (string) $enc['type'];
				if ( strpos( $type, 'image/' ) === 0 || preg_match( '/\.(jpg|jpeg|png|gif|webp)/i', (string) $enc['url'] ) ) {
					return (string) $enc['url'];
				}
			}
		}

		// Tier 3: First <img> tag in HTML content
		$combined_html = $html1 . ' ' . $html2;
		if ( ! empty( $combined_html ) ) {
			if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $combined_html, $matches ) ) {
				$img_src = $matches[1];
				if ( strpos( $img_src, 'data:image/' ) !== 0 ) {
					return $img_src;
				}
			}
		}

		// Tier 4: Global Fallback Image
		if ( ! empty( $fallback ) ) {
			return $fallback;
		}

		// Tier 5: Default SVG Data URI Placeholder
		return 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400"><rect width="600" height="400" fill="%23f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="20" fill="%2394a3b8">No Image Available</text></svg>';
	}
}
