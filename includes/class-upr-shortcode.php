<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Shortcode
 * Registers and handles [universal_post_rss_loop] shortcode
 */
class UPR_Shortcode {

	public static function init() {
		add_shortcode( 'universal_post_rss_loop', array( __CLASS__, 'render_shortcode' ) );
	}

	/**
	 * Shortcode callback
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Enclosed shortcode content.
	 * @return string HTML output.
	 */
	public static function render_shortcode( $atts = array(), $content = null ) {
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		// Convert shortcode string booleans ('true'/'false') to real booleans
		foreach ( $atts as $key => $val ) {
			if ( strtolower( $val ) === 'true' ) {
				$atts[ $key ] = true;
			} elseif ( strtolower( $val ) === 'false' ) {
				$atts[ $key ] = false;
			}
		}

		// If user passes custom HTML inside shortcode content
		if ( ! empty( $content ) && empty( $atts['custom_html'] ) ) {
			$atts['custom_html'] = $content;
			$atts['layout']      = 'custom';
		}

		return UPR_Renderer::render( $atts );
	}
}

UPR_Shortcode::init();
