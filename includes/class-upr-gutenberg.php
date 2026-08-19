<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Gutenberg
 * Registers Gutenberg Block for Universal Post & RSS Loop
 */
class UPR_Gutenberg {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_block' ) );
	}

	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'upr-block-js',
			UPR_PLUGIN_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-server-side-render', 'wp-i18n' ),
			UPR_VERSION
		);

		// Localize auto-detected Post Types and Categories for Gutenberg Block
		$post_types_raw = UPR_Post_Provider::get_public_post_types();
		$post_types_options = array();
		foreach ( $post_types_raw as $val => $label ) {
			$post_types_options[] = array(
				'label' => $label,
				'value' => $val,
			);
		}

		$cats_raw = UPR_Post_Provider::get_categories_list();
		$cats_options = array();
		foreach ( $cats_raw as $val => $label ) {
			$cats_options[] = array(
				'label' => $label,
				'value' => $val,
			);
		}

		wp_localize_script(
			'upr-block-js',
			'uprData',
			array(
				'post_types' => $post_types_options,
				'categories' => $cats_options,
			)
		);

		wp_register_style(
			'upr-frontend-css',
			UPR_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			UPR_VERSION
		);

		register_block_type(
			'upr/universal-post-rss-loop',
			array(
				'editor_script'   => 'upr-block-js',
				'style'           => 'upr-frontend-css',
				'render_callback' => array( __CLASS__, 'render_block' ),
				'attributes'      => array(
					'source'              => array( 'type' => 'string', 'default' => 'posts' ),
					'post_type'           => array( 'type' => 'string', 'default' => 'post' ),
					'category'            => array( 'type' => 'string', 'default' => '' ),
					'author'              => array( 'type' => 'string', 'default' => '' ),
					'exclude'             => array( 'type' => 'string', 'default' => '' ),
					'exclude_post_select' => array( 'type' => 'string', 'default' => '' ),
					'rss_exclude'         => array( 'type' => 'string', 'default' => '' ),
					'exclude_current'     => array( 'type' => 'boolean', 'default' => false ),
					'limit'               => array( 'type' => 'number', 'default' => 6 ),
					'order'               => array( 'type' => 'string', 'default' => 'DESC' ),
					'orderby'             => array( 'type' => 'string', 'default' => 'date' ),
					
					'feed_url'            => array( 'type' => 'string', 'default' => '' ),
					'feeds'               => array( 'type' => 'string', 'default' => '' ),
					'cache_duration'      => array( 'type' => 'number', 'default' => 3600 ),
					'dedupe_mode'         => array( 'type' => 'string', 'default' => 'url_only' ),

					'layout'              => array( 'type' => 'string', 'default' => 'grid' ),
					'columns'             => array( 'type' => 'number', 'default' => 3 ),
					'custom_html'         => array( 'type' => 'string', 'default' => '' ),

					'show_image'          => array( 'type' => 'boolean', 'default' => true ),
					'image_position'      => array( 'type' => 'string', 'default' => 'top' ),
					'image_ratio'         => array( 'type' => 'string', 'default' => '16:9' ),
					'object_fit'          => array( 'type' => 'string', 'default' => 'cover' ),

					'show_title'          => array( 'type' => 'boolean', 'default' => true ),
					'title_tag'           => array( 'type' => 'string', 'default' => 'h3' ),
					'max_title_chars'     => array( 'type' => 'number', 'default' => 0 ),

					'show_excerpt'        => array( 'type' => 'boolean', 'default' => true ),
					'max_excerpt_chars'   => array( 'type' => 'number', 'default' => 0 ),

					'show_date'           => array( 'type' => 'boolean', 'default' => true ),
					'show_author'         => array( 'type' => 'boolean', 'default' => true ),
					'show_category'       => array( 'type' => 'boolean', 'default' => true ),
					'show_source'         => array( 'type' => 'boolean', 'default' => true ),

					'show_read_more'      => array( 'type' => 'boolean', 'default' => true ),
					'read_more_text'      => array( 'type' => 'string', 'default' => 'Read More' ),
					'link_behavior'       => array( 'type' => 'string', 'default' => 'card' ),
					'open_new_tab'        => array( 'type' => 'boolean', 'default' => true ),

					// Interactive Features (v2.0.0)
					'show_search_bar'     => array( 'type' => 'boolean', 'default' => false ),
					'show_filter_tabs'    => array( 'type' => 'boolean', 'default' => false ),
					'show_read_time'      => array( 'type' => 'boolean', 'default' => false ),
					'show_social_share'   => array( 'type' => 'boolean', 'default' => false ),
					'pagination_type'     => array( 'type' => 'string', 'default' => 'none' ),
					'items_per_page'      => array( 'type' => 'number', 'default' => 6 ),

					// Style & Color Customization (v1.3.0)
					'card_style'          => array( 'type' => 'string', 'default' => 'classic' ),
					'button_style'        => array( 'type' => 'string', 'default' => 'solid' ),
					'button_width'        => array( 'type' => 'string', 'default' => 'auto' ),
					'border_radius'       => array( 'type' => 'string', 'default' => 'medium' ),
					'box_shadow'          => array( 'type' => 'string', 'default' => 'soft' ),
					'card_padding'        => array( 'type' => 'string', 'default' => 'normal' ),
					'title_font_size'     => array( 'type' => 'string', 'default' => 'medium' ),
					'excerpt_font_size'   => array( 'type' => 'string', 'default' => 'medium' ),
					'custom_title_font_size' => array( 'type' => 'string', 'default' => '' ),
					'custom_excerpt_font_size' => array( 'type' => 'string', 'default' => '' ),
					'font_family'         => array( 'type' => 'string', 'default' => 'inherit' ),
					'custom_font_family'  => array( 'type' => 'string', 'default' => '' ),
					'image_hover_effect'  => array( 'type' => 'string', 'default' => 'zoom' ),
					'badge_position'      => array( 'type' => 'string', 'default' => 'auto' ),

					'card_bg'             => array( 'type' => 'string', 'default' => '' ),
					'border_color'        => array( 'type' => 'string', 'default' => '' ),
					'title_color'         => array( 'type' => 'string', 'default' => '' ),
					'title_hover_color'   => array( 'type' => 'string', 'default' => '' ),
					'excerpt_color'       => array( 'type' => 'string', 'default' => '' ),
					'meta_color'          => array( 'type' => 'string', 'default' => '' ),
					'badge_bg'            => array( 'type' => 'string', 'default' => '' ),
					'badge_color'         => array( 'type' => 'string', 'default' => '' ),
					'button_bg'           => array( 'type' => 'string', 'default' => '' ),
					'button_color'        => array( 'type' => 'string', 'default' => '' ),
					'button_hover_bg'     => array( 'type' => 'string', 'default' => '' ),
				),
			)
		);
	}

	public static function render_block( $attributes ) {
		return UPR_Renderer::render( $attributes );
	}
}

UPR_Gutenberg::init();
