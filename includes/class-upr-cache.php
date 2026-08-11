<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Cache
 * Manages Transient caching for RSS Feeds and Cache clearing actions.
 */
class UPR_Cache {

	const PREFIX = 'upr_cache_';

	/**
	 * Get cached data by key
	 *
	 * @param string $key Unique cache key.
	 * @return mixed|false
	 */
	public static function get( $key ) {
		return get_transient( self::PREFIX . md5( $key ) );
	}

	/**
	 * Set cached data
	 *
	 * @param string $key Cache key.
	 * @param mixed  $data Data to cache.
	 * @param int    $expiration Expiration in seconds (default 3600 = 1 hr).
	 * @return bool
	 */
	public static function set( $key, $data, $expiration = 3600 ) {
		return set_transient( self::PREFIX . md5( $key ), $data, $expiration );
	}

	/**
	 * Delete specific transient
	 *
	 * @param string $key Cache key.
	 * @return bool
	 */
	public static function delete( $key ) {
		return delete_transient( self::PREFIX . md5( $key ) );
	}

	/**
	 * Clear all Universal Post & RSS Loop transients from wp_options table.
	 *
	 * @return int Number of transients deleted.
	 */
	public static function clear_all_cache() {
		global $wpdb;
		$count = 0;
		$prefix = '_transient_' . self::PREFIX;
		$timeout_prefix = '_transient_timeout_' . self::PREFIX;

		$sql = $wpdb->prepare(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( $prefix ) . '%',
			$wpdb->esc_like( $timeout_prefix ) . '%'
		);

		$options = $wpdb->get_col( $sql );
		if ( ! empty( $options ) ) {
			foreach ( $options as $option_name ) {
				delete_option( $option_name );
				$count++;
			}
		}

		return intval( $count / 2 );
	}

	/**
	 * Setup WP-Cron background pre-caching event
	 */
	public static function init_cron() {
		if ( ! wp_next_scheduled( 'upr_cron_refresh_feeds_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'upr_cron_refresh_feeds_event' );
		}
		add_action( 'upr_cron_refresh_feeds_event', array( __CLASS__, 'cron_precache_feeds' ) );
	}

	/**
	 * WP-Cron callback to pre-fetch and warm cache for default feeds
	 */
	public static function cron_precache_feeds() {
		$default_fallback = get_option( 'upr_default_fallback_image', '' );
		$dedupe_mode      = get_option( 'upr_dedupe_mode', 'url_only' );

		// Warm cache for common feeds if any exist in options
		$tracked_feeds = get_option( 'upr_tracked_feed_urls', array() );
		if ( ! empty( $tracked_feeds ) && is_array( $tracked_feeds ) ) {
			$provider = new UPR_RSS_Provider();
			foreach ( $tracked_feeds as $feed_url ) {
				$provider->get_items(
					array(
						'feed_url'       => $feed_url,
						'cache_duration' => 3600,
						'dedupe_mode'    => $dedupe_mode,
						'fallback_image' => $default_fallback,
					)
				);
			}
		}
	}
}

UPR_Cache::init_cron();
