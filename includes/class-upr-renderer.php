<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Renderer
 * Execution engine that fetches data from Provider and renders Loop using Templates.
 */
class UPR_Renderer {

	/**
	 * Main render entry point.
	 *
	 * @param array $atts Component/Block attributes.
	 * @return string HTML output.
	 */
	public static function render( array $atts = array() ) {
		$defaults = array(
			// Source settings
			'source'              => 'posts', // 'posts' or 'rss'
			
			// WP Post Query settings
			'post_type'           => 'post',
			'category'            => '',
			'author'              => '',
			'tag'                 => '',
			'limit'               => 6,
			'order'               => 'DESC',
			'orderby'             => 'date',
			'offset'              => 0,
			'include'             => '',
			'exclude'             => '',
			'exclude_current'     => false,

			// RSS Feed settings
			'feed_url'            => '',
			'feeds'               => '',
			'cache_duration'      => 3600,
			'dedupe_mode'         => 'url_only',
			'download_rss_images' => false,
			'fallback_image'      => '',
			'open_new_tab'        => true,

			// Layout settings
			'layout'              => 'grid', // 'grid', 'list', 'horizontal', 'custom'
			'columns'             => 3,
			'custom_html'         => '',

			// Image settings
			'show_image'          => true,
			'image_position'      => 'top', // 'top', 'left', 'right'
			'image_ratio'         => '16:9', // '16:9', '4:3', '1:1', '3:2', 'auto'
			'object_fit'          => 'cover', // 'cover', 'contain', 'fill'

			// Title settings
			'show_title'          => true,
			'title_tag'           => 'h3',
			'max_title_chars'     => 0,
			'max_title_lines'     => 0,

			// Excerpt settings
			'show_excerpt'        => true,
			'max_excerpt_chars'   => 0,
			'max_excerpt_lines'   => 0,

			// Meta settings
			'show_date'           => true,
			'show_author'         => true,
			'show_category'       => true,
			'show_source'         => true,

			// Read More settings
			'show_read_more'      => true,
			'read_more_text'      => __( 'Read More', 'universal-post-rss-loop' ),

			// Interactive Features (v2.0.0)
			'show_search_bar'     => false,
			'show_filter_tabs'    => false,
			'show_read_time'      => false,
			'show_social_share'   => false,
			'pagination_type'     => 'none', // 'none', 'load_more', 'numeric'
			'items_per_page'      => 6,

			// Preset & Style Customization
			'card_style'          => 'classic', // 'classic', 'modern', 'minimal', 'overlay', 'glass'
			'button_style'        => 'solid', // 'link', 'solid', 'outline', 'pill'
			'button_width'        => 'auto', // 'auto', 'full'
			'border_radius'       => 'medium', // 'none', 'small', 'medium', 'large', 'full'
			'box_shadow'          => 'soft', // 'none', 'soft', 'floating', 'heavy'
			'card_padding'        => 'normal', // 'compact', 'normal', 'spacious'
			'title_font_size'     => 'medium', // 'small', 'medium', 'large', 'xlarge'
			'excerpt_font_size'   => 'medium', // 'small', 'medium', 'large'
			'custom_title_font_size' => '',
			'custom_excerpt_font_size' => '',
			'font_family'         => 'inherit', // 'inherit', 'inter', 'roboto', 'poppins', 'playfair', 'monospace', 'custom'
			'custom_font_family'  => '',
			'image_hover_effect'  => 'zoom', // 'zoom', 'brighten', 'none'
			'badge_position'      => 'auto', // 'auto', 'overlay', 'inline'

			// Colors Palette
			'card_bg'             => '',
			'border_color'        => '',
			'title_color'         => '',
			'title_hover_color'   => '',
			'excerpt_color'       => '',
			'meta_color'          => '',
			'badge_bg'            => '',
			'badge_color'         => '',
			'button_bg'           => '',
			'button_color'        => '',
			'button_hover_bg'     => '',

			// Link Behavior
			'link_behavior'       => 'card', // 'card', 'title', 'image', 'button', 'all'
		);

		$settings = wp_parse_args( $atts, $defaults );

		// Security: Strict Attribute Sanitization
		$settings['source']             = in_array( strtolower( $settings['source'] ), array( 'posts', 'rss' ), true ) ? strtolower( $settings['source'] ) : 'posts';
		$settings['limit']              = min( max( intval( $settings['limit'] ), 1 ), 100 );
		$settings['columns']            = min( max( intval( $settings['columns'] ), 1 ), 6 );
		$settings['pagination_type']    = sanitize_key( $settings['pagination_type'] );
		$settings['items_per_page']     = min( max( intval( $settings['items_per_page'] ), 1 ), 50 );
		$settings['card_style']         = sanitize_key( $settings['card_style'] );
		$settings['button_style']       = sanitize_key( $settings['button_style'] );
		$settings['button_width']       = sanitize_key( $settings['button_width'] );
		$settings['border_radius']      = sanitize_key( $settings['border_radius'] );
		$settings['box_shadow']         = sanitize_key( $settings['box_shadow'] );
		$settings['card_padding']       = sanitize_key( $settings['card_padding'] );
		$settings['title_font_size']    = sanitize_key( $settings['title_font_size'] );
		$settings['excerpt_font_size']  = sanitize_key( $settings['excerpt_font_size'] );
		$settings['image_hover_effect'] = sanitize_key( $settings['image_hover_effect'] );
		$settings['badge_position']     = sanitize_key( $settings['badge_position'] );
		$settings['layout']             = sanitize_key( $settings['layout'] );
		$settings['title_tag']          = in_array( strtolower( $settings['title_tag'] ), array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p' ), true ) ? strtolower( $settings['title_tag'] ) : 'h3';

		// Instantiate Data Provider based on Source Switch
		if ( $settings['source'] === 'rss' ) {
			$provider = new UPR_RSS_Provider();
		} else {
			$provider = new UPR_Post_Provider();
		}

		// Fetch normalized UPR_Item array
		$items = $provider->get_items( $settings );

		if ( empty( $items ) ) {
			return '<div class="upr-no-items">' . esc_html__( 'No posts or RSS items found.', 'universal-post-rss-loop' ) . '</div>';
		}

		// Render Loop Template
		return UPR_Template::render(
			'loop.php',
			array(
				'items'    => $items,
				'settings' => $settings,
			)
		);
	}
}
