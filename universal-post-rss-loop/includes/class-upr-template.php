<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Template
 * Manages theme template overrides and Custom HTML placeholder replacement.
 */
class UPR_Template {

	/**
	 * Locate and render a PHP template file, allowing theme overrides.
	 *
	 * Search locations:
	 * 1. wp-content/themes/YOUR-THEME/universal-post-rss-loop/{$template_name}
	 * 2. wp-content/plugins/universal-post-rss-loop/templates/{$template_name}
	 *
	 * @param string $template_name Template file name (e.g. 'loop.php' or 'item.php').
	 * @param array  $args Array of variables passed to template.
	 * @return string Rendered HTML content.
	 */
	public static function render( $template_name, array $args = array() ) {
		$template_path = locate_template( array( 'universal-post-rss-loop/' . $template_name ) );

		if ( ! $template_path ) {
			$template_path = UPR_PLUGIN_DIR . 'templates/' . $template_name;
		}

		if ( ! file_exists( $template_path ) ) {
			return '';
		}

		extract( $args, EXTR_SKIP );

		ob_start();
		include $template_path;
		return ob_get_clean();
	}

	/**
	 * Render Custom HTML template by replacing {{placeholder}} tokens with item properties.
	 *
	 * @param string   $html_string Custom HTML code input by user.
	 * @param UPR_Item $item Normalized item object.
	 * @param array    $settings Display settings array.
	 * @return string Replaced HTML code.
	 */
	public static function render_custom_html( $html_string, UPR_Item $item, array $settings = array() ) {
		if ( empty( $html_string ) ) {
			return '';
		}

		$read_more_text = ! empty( $settings['read_more_text'] ) ? $settings['read_more_text'] : __( 'Read More', 'universal-post-rss-loop' );

		$replacements = array(
			'{{id}}'          => esc_attr( $item->id ),
			'{{title}}'       => esc_html( $item->title ),
			'{{url}}'         => esc_url( $item->url ),
			'{{image}}'       => esc_url( $item->image ),
			'{{excerpt}}'     => esc_html( $item->excerpt ),
			'{{date}}'        => esc_html( $item->date ),
			'{{author}}'      => esc_html( $item->author ),
			'{{category}}'    => esc_html( $item->category ),
			'{{source_name}}' => esc_html( $item->source_name ),
			'{{source_url}}'  => esc_url( $item->source_url ),
			'{{read_more}}'   => esc_html( $read_more_text ),
		);

		// Support uppercase placeholders too
		foreach ( array_keys( $replacements ) as $key ) {
			$replacements[ strtoupper( $key ) ] = $replacements[ $key ];
		}

		return strtr( $html_string, $replacements );
	}
}
