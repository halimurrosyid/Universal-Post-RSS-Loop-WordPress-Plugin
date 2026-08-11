<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_RSS_Provider
 * Data Provider for External RSS Feeds (Supports Multiple Feeds, Caching, Merge, Deduplication, Image Handling).
 */
class UPR_RSS_Provider extends Abstract_UPR_Provider {

	/**
	 * Fetch, merge, deduplicate, and normalize items from single or multiple RSS feeds.
	 *
	 * @param array $args RSS Provider configuration options.
	 * @return UPR_Item[]
	 */
	public function get_items( array $args = array() ) {
		$defaults = array(
			'feed_url'            => '',
			'feeds'               => '',
			'limit'               => 6,
			'order'               => 'DESC',
			'orderby'             => 'date',
			'cache_duration'      => 3600, // in seconds (e.g. 3600 for 1h)
			'dedupe_mode'         => 'url_only', // url_only, guid_url, url_title, strict
			'download_rss_images' => false,
			'fallback_image'      => '',
		);

		$parsed_args = wp_parse_args( $args, $defaults );

		// Extract feed URLs list
		$raw_urls = array();
		if ( ! empty( $parsed_args['feed_url'] ) ) {
			$raw_urls[] = $parsed_args['feed_url'];
		}
		if ( ! empty( $parsed_args['feeds'] ) ) {
			if ( is_array( $parsed_args['feeds'] ) ) {
				$raw_urls = array_merge( $raw_urls, $parsed_args['feeds'] );
			} else {
				$split_urls = preg_split( '/[\r\n,]+/', $parsed_args['feeds'] );
				$raw_urls   = array_merge( $raw_urls, $split_urls );
			}
		}

		$feed_urls = array_unique( array_filter( array_map( 'trim', $raw_urls ) ) );
		if ( empty( $feed_urls ) ) {
			return array();
		}

		// Generate cache key based on feed URLs and options
		$cache_key = 'rss_pool_' . md5( implode( '|', $feed_urls ) . '_' . $parsed_args['dedupe_mode'] );
		$cache_sec = intval( $parsed_args['cache_duration'] );

		// Check cache
		$cached_data = UPR_Cache::get( $cache_key );
		if ( false !== $cached_data && is_array( $cached_data ) ) {
			return self::slice_and_convert_items( $cached_data, $parsed_args );
		}

		// Process and merge feeds
		$all_raw_items = array();

		foreach ( $feed_urls as $url ) {
			$parsed = UPR_RSS_Parser::parse_feed(
				$url,
				array(
					'fallback_image' => $parsed_args['fallback_image'],
				)
			);

			if ( ! is_wp_error( $parsed ) && ! empty( $parsed['items'] ) && is_array( $parsed['items'] ) ) {
				foreach ( $parsed['items'] as $item ) {
					$all_raw_items[] = $item;
				}
			}
		}

		if ( empty( $all_raw_items ) ) {
			return array();
		}

		// Deduplicate items
		$deduped_items = self::deduplicate_items( $all_raw_items, $parsed_args['dedupe_mode'] );

		// Optional: Download RSS images to WP Media Library if requested
		if ( ! empty( $parsed_args['download_rss_images'] ) ) {
			foreach ( $deduped_items as &$item ) {
				if ( ! empty( $item['image'] ) ) {
					$item['image'] = self::maybe_download_image( $item['image'], $item['title'] );
				}
			}
		}

		// Save full pool to cache
		UPR_Cache::set( $cache_key, $deduped_items, $cache_sec );

		return self::slice_and_convert_items( $deduped_items, $parsed_args );
	}

	/**
	 * Deduplicate raw RSS items array based on selected mode.
	 */
	private static function deduplicate_items( array $items, $mode = 'url_only' ) {
		$unique     = array();
		$seen_urls  = array();
		$seen_guids = array();
		$seen_titles = array();

		foreach ( $items as $item ) {
			$url  = strtolower( trim( $item['url'] ) );
			$guid = strtolower( trim( $item['guid'] ) );
			$norm_title = self::normalize_title( $item['title'] );

			$is_duplicate = false;

			switch ( $mode ) {
				case 'guid_url':
					if ( ( ! empty( $guid ) && isset( $seen_guids[ $guid ] ) ) || ( ! empty( $url ) && isset( $seen_urls[ $url ] ) ) ) {
						$is_duplicate = true;
					}
					break;
				case 'url_title':
					if ( ( ! empty( $url ) && isset( $seen_urls[ $url ] ) ) || ( ! empty( $norm_title ) && isset( $seen_titles[ $norm_title ] ) ) ) {
						$is_duplicate = true;
					}
					break;
				case 'strict':
					if ( ( ! empty( $url ) && isset( $seen_urls[ $url ] ) ) ||
						( ! empty( $guid ) && isset( $seen_guids[ $guid ] ) ) ||
						( ! empty( $norm_title ) && isset( $seen_titles[ $norm_title ] ) ) ) {
						$is_duplicate = true;
					}
					break;
				case 'url_only':
				default:
					if ( ! empty( $url ) && isset( $seen_urls[ $url ] ) ) {
						$is_duplicate = true;
					}
					break;
			}

			if ( ! $is_duplicate ) {
				if ( ! empty( $url ) ) {
					$seen_urls[ $url ] = true;
				}
				if ( ! empty( $guid ) ) {
					$seen_guids[ $guid ] = true;
				}
				if ( ! empty( $norm_title ) ) {
					$seen_titles[ $norm_title ] = true;
				}
				$unique[] = $item;
			}
		}

		return $unique;
	}

	/**
	 * Normalize title for string comparison
	 */
	private static function normalize_title( $title ) {
		$title = wp_strip_all_tags( $title );
		$title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
		$title = mb_strtolower( $title, 'UTF-8' );
		$title = preg_replace( '/[^\w\s]/u', '', $title );
		$title = preg_replace( '/\s+/', ' ', $title );
		return trim( $title );
	}

	/**
	 * Sort, slice by limit, and convert raw RSS items to UPR_Item objects
	 */
	private static function slice_and_convert_items( array $raw_items, array $args ) {
		$orderby = ! empty( $args['orderby'] ) ? $args['orderby'] : 'date';
		$order   = ! empty( $args['order'] ) ? strtoupper( $args['order'] ) : 'DESC';

		usort(
			$raw_items,
			function( $a, $b ) use ( $orderby, $order ) {
				if ( $orderby === 'title' ) {
					$cmp = strcmp( $a['title'], $b['title'] );
				} else {
					$cmp = $a['timestamp'] - $b['timestamp'];
				}
				return $order === 'DESC' ? -$cmp : $cmp;
			}
		);

		$limit  = intval( $args['limit'] );
		$sliced = array_slice( $raw_items, 0, $limit );

		$normalized = array();
		foreach ( $sliced as $item ) {
			$item_data = array(
				'id'          => 'rss_' . md5( ! empty( $item['url'] ) ? $item['url'] : $item['title'] ),
				'title'       => $item['title'],
				'url'         => $item['url'],
				'image'       => $item['image'],
				'excerpt'     => $item['excerpt'],
				'date'        => $item['date'],
				'timestamp'   => $item['timestamp'],
				'author'      => $item['author'],
				'category'    => $item['category'],
				'source_name' => $item['source_name'],
				'source_url'  => $item['source_url'],
				'is_external' => true,
			);
			$normalized[] = new UPR_Item( $item_data );
		}

		return $normalized;
	}

	/**
	 * Download RSS image to Media Library if enabled
	 */
	private static function maybe_download_image( $image_url, $title ) {
		if ( empty( $image_url ) ) {
			return '';
		}

		// Security: Only allow http and https protocols for sideloading
		$scheme = wp_parse_url( $image_url, PHP_URL_SCHEME );
		if ( ! in_array( strtolower( $scheme ), array( 'http', 'https' ), true ) ) {
			return '';
		}

		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		// Check if already downloaded (transient mapping)
		$cache_key = 'img_dl_' . md5( $image_url );
		$existing  = get_transient( $cache_key );
		if ( $existing ) {
			return $existing;
		}

		$attachment_id = media_sideload_image( $image_url, 0, $title, 'id' );
		if ( ! is_wp_error( $attachment_id ) ) {
			$local_url = wp_get_attachment_url( $attachment_id );
			if ( $local_url ) {
				set_transient( $cache_key, $local_url, MONTH_IN_SECONDS );
				return $local_url;
			}
		}

		return $image_url;
	}
}
