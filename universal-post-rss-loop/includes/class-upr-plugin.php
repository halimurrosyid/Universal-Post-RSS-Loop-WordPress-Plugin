<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Plugin
 * Core Singleton Bootstrap for Universal Post & RSS Loop Plugin
 */
class UPR_Plugin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'universal-post-rss-loop', false, dirname( plugin_basename( UPR_PLUGIN_FILE ) ) . '/languages' );
	}

	public function enqueue_frontend_assets() {
		wp_enqueue_style(
			'upr-frontend-css',
			UPR_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			UPR_VERSION
		);

		wp_enqueue_script(
			'upr-frontend-js',
			UPR_PLUGIN_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			UPR_VERSION,
			true
		);
	}
}
