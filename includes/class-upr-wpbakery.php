<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_WPBakery
 * Integrates element with WPBakery Page Builder via vc_map() (v1.3.0)
 */
class UPR_WPBakery {

	public static function init() {
		add_action( 'vc_before_init', array( __CLASS__, 'integrate_with_vc' ) );
	}

	public static function integrate_with_vc() {
		if ( ! function_exists( 'vc_map' ) ) {
			return;
		}

		vc_map(
			array(
				'name'        => __( 'Universal Post & RSS Loop', 'universal-post-rss-loop' ),
				'base'        => 'universal_post_rss_loop',
				'description' => __( 'Display WP Posts or External RSS Feeds with unified design', 'universal-post-rss-loop' ),
				'category'    => __( 'Content', 'universal-post-rss-loop' ),
				'icon'        => 'icon-wpb-application-icon-large',
				'params'      => array(
					// Source tab
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Data Source', 'universal-post-rss-loop' ),
						'param_name'  => 'source',
						'value'       => array(
							__( 'WordPress Posts', 'universal-post-rss-loop' ) => 'posts',
							__( 'RSS Feed', 'universal-post-rss-loop' )        => 'rss',
						),
						'admin_label' => true,
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Post Type (Auto Detected)', 'universal-post-rss-loop' ),
						'param_name'  => 'post_type',
						'value'       => array_flip( UPR_Post_Provider::get_public_post_types() ),
						'std'         => 'post',
						'admin_label' => true,
						'dependency'  => array( 'element' => 'source', 'value' => array( 'posts' ) ),
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Category (Auto Detected)', 'universal-post-rss-loop' ),
						'param_name'  => 'category',
						'value'       => array_flip( UPR_Post_Provider::get_categories_list() ),
						'std'         => '',
						'dependency'  => array( 'element' => 'source', 'value' => array( 'posts' ) ),
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),

					// Interactive Features Tab (v2.0.0)
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Enable Live Search Bar', 'universal-post-rss-loop' ),
						'param_name'  => 'show_search_bar',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'group'       => __( 'Interactive Features', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Enable Category Filter Tabs', 'universal-post-rss-loop' ),
						'param_name'  => 'show_filter_tabs',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'group'       => __( 'Interactive Features', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Show Estimated Read Time (⏱️ min read)', 'universal-post-rss-loop' ),
						'param_name'  => 'show_read_time',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'group'       => __( 'Interactive Features', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Show Social Media Share Icons', 'universal-post-rss-loop' ),
						'param_name'  => 'show_social_share',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'group'       => __( 'Interactive Features', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Pagination Mode', 'universal-post-rss-loop' ),
						'param_name'  => 'pagination_type',
						'value'       => array(
							__( 'None (Show All Items)', 'universal-post-rss-loop' )                => 'none',
							__( 'Load More Button', 'universal-post-rss-loop' )                     => 'load_more',
							__( 'Numeric Page Numbers (1, 2, 3...)', 'universal-post-rss-loop' )    => 'numeric',
						),
						'std'         => 'none',
						'group'       => __( 'Interactive Features', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Items Per Page', 'universal-post-rss-loop' ),
						'param_name'  => 'items_per_page',
						'value'       => 6,
						'dependency'  => array( 'element' => 'pagination_type', 'value' => array( 'load_more', 'numeric' ) ),
						'group'       => __( 'Interactive Features', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Filter by Author (ID or Username)', 'universal-post-rss-loop' ),
						'param_name'  => 'author',
						'value'       => '',
						'dependency'  => array( 'element' => 'source', 'value' => array( 'posts' ) ),
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Exclude Post IDs (Sembunyikan Artikel)', 'universal-post-rss-loop' ),
						'param_name'  => 'exclude',
						'value'       => '',
						'description' => __( 'Masukkan ID postingan yang ingin disembunyikan, dipisahkan koma (misal: 15, 42, 108).', 'universal-post-rss-loop' ),
						'dependency'  => array( 'element' => 'source', 'value' => array( 'posts' ) ),
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Sembunyikan Artikel Yang Sedang Dibaca (Exclude Current Post)', 'universal-post-rss-loop' ),
						'param_name'  => 'exclude_current',
						'value'       => array( __( 'Ya, sembunyikan artikel halaman ini (Cocok untuk Related Posts)', 'universal-post-rss-loop' ) => 'true' ),
						'dependency'  => array( 'element' => 'source', 'value' => array( 'posts' ) ),
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'RSS Feed URL', 'universal-post-rss-loop' ),
						'param_name'  => 'feed_url',
						'value'       => '',
						'dependency'  => array( 'element' => 'source', 'value' => array( 'rss' ) ),
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textarea',
						'heading'     => __( 'Multiple Feed URLs (Comma/Line Separated)', 'universal-post-rss-loop' ),
						'param_name'  => 'feeds',
						'value'       => '',
						'dependency'  => array( 'element' => 'source', 'value' => array( 'rss' ) ),
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Number of Items (Limit)', 'universal-post-rss-loop' ),
						'param_name'  => 'limit',
						'value'       => 6,
						'group'       => __( 'Data Source', 'universal-post-rss-loop' ),
					),

					// Preset & Design Geometry Tab
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Card Preset Style', 'universal-post-rss-loop' ),
						'param_name'  => 'card_style',
						'value'       => array(
							__( 'Classic Card (Border & Soft Shadow)', 'universal-post-rss-loop' )     => 'classic',
							__( 'Modern Card (Floating & Badge Overlay)', 'universal-post-rss-loop' )    => 'modern',
							__( 'Minimalist (Flat & Bottom Border)', 'universal-post-rss-loop' )        => 'minimal',
							__( 'Overlay Hero (Full Dark Backdrop)', 'universal-post-rss-loop' )       => 'overlay',
							__( 'Glassmorphism (Frosted Glass Effect)', 'universal-post-rss-loop' )     => 'glass',
						),
						'admin_label' => true,
						'group'       => __( 'Presets & Geometry', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Card Inner Padding', 'universal-post-rss-loop' ),
						'param_name'  => 'card_padding',
						'value'       => array(
							'Compact (14px)'  => 'compact',
							'Normal (20px)'   => 'normal',
							'Spacious (28px)' => 'spacious',
						),
						'std'         => 'normal',
						'group'       => __( 'Presets & Geometry', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Box Shadow Intensity', 'universal-post-rss-loop' ),
						'param_name'  => 'box_shadow',
						'value'       => array(
							'None (Flat)'             => 'none',
							'Soft Shadow'             => 'soft',
							'Floating Medium Shadow' => 'floating',
							'Heavy Deep Shadow'       => 'heavy',
						),
						'std'         => 'soft',
						'group'       => __( 'Presets & Geometry', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Border Radius', 'universal-post-rss-loop' ),
						'param_name'  => 'border_radius',
						'value'       => array(
							'Square (0px)'       => 'none',
							'Small (6px)'        => 'small',
							'Medium (12px)'      => 'medium',
							'Large (20px)'       => 'large',
							'Full Rounded (32px)' => 'full',
						),
						'std'         => 'medium',
						'group'       => __( 'Presets & Geometry', 'universal-post-rss-loop' ),
					),

					// Typography Tab
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Font Family', 'universal-post-rss-loop' ),
						'param_name'  => 'font_family',
						'value'       => array(
							__( 'Inherit (WordPress Theme Font)', 'universal-post-rss-loop' ) => 'inherit',
							'Inter (Sans-Serif)'       => 'inter',
							'Roboto (Sans-Serif)'      => 'roboto',
							'Poppins (Sans-Serif)'     => 'poppins',
							'Playfair Display (Serif)' => 'playfair',
							'Monospace (Code Style)'   => 'monospace',
							'Custom Font Family'       => 'custom',
						),
						'std'         => 'inherit',
						'group'       => __( 'Typography', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Custom Font Family (e.g. "Open Sans", sans-serif)', 'universal-post-rss-loop' ),
						'param_name'  => 'custom_font_family',
						'value'       => '',
						'group'       => __( 'Typography', 'universal-post-rss-loop' ),
						'dependency'  => array( 'element' => 'font_family', 'value' => array( 'custom' ) ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Title Font Size', 'universal-post-rss-loop' ),
						'param_name'  => 'title_font_size',
						'value'       => array(
							'Small (16px)'       => 'small',
							'Medium (18px)'      => 'medium',
							'Large (22px)'       => 'large',
							'Extra Large (26px)' => 'xlarge',
						),
						'std'         => 'medium',
						'group'       => __( 'Typography', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Custom Title Font Size (e.g. 17px, 1.2rem)', 'universal-post-rss-loop' ),
						'param_name'  => 'custom_title_font_size',
						'value'       => '',
						'description' => __( 'Leave empty to use Title Font Size dropdown preset above.', 'universal-post-rss-loop' ),
						'group'       => __( 'Typography', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Max Title Chars (0 = No Limit)', 'universal-post-rss-loop' ),
						'param_name'  => 'max_title_chars',
						'value'       => 0,
						'group'       => __( 'Typography', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Excerpt Font Size', 'universal-post-rss-loop' ),
						'param_name'  => 'excerpt_font_size',
						'value'       => array(
							'Small (13px)'  => 'small',
							'Medium (14px)' => 'medium',
							'Large (15px)'  => 'large',
						),
						'std'         => 'medium',
						'group'       => __( 'Typography', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Custom Excerpt Font Size (e.g. 14px, 0.9rem)', 'universal-post-rss-loop' ),
						'param_name'  => 'custom_excerpt_font_size',
						'value'       => '',
						'description' => __( 'Leave empty to use Excerpt Font Size dropdown preset above.', 'universal-post-rss-loop' ),
						'group'       => __( 'Typography', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Max Excerpt Chars (0 = No Limit)', 'universal-post-rss-loop' ),
						'param_name'  => 'max_excerpt_chars',
						'value'       => 0,
						'group'       => __( 'Typography', 'universal-post-rss-loop' ),
					),

					// Button Tab
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Button Style', 'universal-post-rss-loop' ),
						'param_name'  => 'button_style',
						'value'       => array(
							__( 'Solid Filled Button', 'universal-post-rss-loop' ) => 'solid',
							__( 'Outline Button', 'universal-post-rss-loop' )      => 'outline',
							__( 'Pill Rounded Button', 'universal-post-rss-loop' ) => 'pill',
							__( 'Simple Text Link', 'universal-post-rss-loop' )    => 'link',
						),
						'group'       => __( 'Button & CTA', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Button Width', 'universal-post-rss-loop' ),
						'param_name'  => 'button_width',
						'value'       => array(
							'Auto (Text Length)' => 'auto',
							'Full Width (100%)'  => 'full',
						),
						'std'         => 'auto',
						'group'       => __( 'Button & CTA', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Read More Button Text', 'universal-post-rss-loop' ),
						'param_name'  => 'read_more_text',
						'value'       => __( 'Read More', 'universal-post-rss-loop' ),
						'group'       => __( 'Button & CTA', 'universal-post-rss-loop' ),
					),

					// Colors Group Tab
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Card Background Color', 'universal-post-rss-loop' ),
						'param_name'  => 'card_bg',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Card Border Color', 'universal-post-rss-loop' ),
						'param_name'  => 'border_color',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Title Font Color', 'universal-post-rss-loop' ),
						'param_name'  => 'title_color',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Title Hover Color', 'universal-post-rss-loop' ),
						'param_name'  => 'title_hover_color',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Excerpt Font Color', 'universal-post-rss-loop' ),
						'param_name'  => 'excerpt_color',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Meta Text Color', 'universal-post-rss-loop' ),
						'param_name'  => 'meta_color',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Badge Background Color', 'universal-post-rss-loop' ),
						'param_name'  => 'badge_bg',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Badge Text Color', 'universal-post-rss-loop' ),
						'param_name'  => 'badge_color',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Button Background Color', 'universal-post-rss-loop' ),
						'param_name'  => 'button_bg',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'colorpicker',
						'heading'     => __( 'Button Text Color', 'universal-post-rss-loop' ),
						'param_name'  => 'button_color',
						'group'       => __( 'Colors', 'universal-post-rss-loop' ),
					),

					// Layout Tab
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Layout Mode', 'universal-post-rss-loop' ),
						'param_name'  => 'layout',
						'value'       => array(
							__( 'Grid', 'universal-post-rss-loop' )       => 'grid',
							__( 'List', 'universal-post-rss-loop' )       => 'list',
							__( 'Horizontal', 'universal-post-rss-loop' ) => 'horizontal',
							__( 'Custom HTML', 'universal-post-rss-loop' ) => 'custom',
						),
						'group'       => __( 'Layout', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Columns', 'universal-post-rss-loop' ),
						'param_name'  => 'columns',
						'value'       => array(
							'1 Column'  => 1,
							'2 Columns' => 2,
							'3 Columns' => 3,
							'4 Columns' => 4,
							'5 Columns' => 5,
							'6 Columns' => 6,
						),
						'std'         => 3,
						'dependency'  => array( 'element' => 'layout', 'value' => array( 'grid' ) ),
						'group'       => __( 'Layout', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'textarea_raw_html',
						'heading'     => __( 'Custom HTML Template', 'universal-post-rss-loop' ),
						'param_name'  => 'custom_html',
						'description' => __( 'Use placeholders: {{image}}, {{title}}, {{excerpt}}, {{date}}, {{author}}, {{category}}, {{source_name}}, {{url}}, {{read_more}}', 'universal-post-rss-loop' ),
						'dependency'  => array( 'element' => 'layout', 'value' => array( 'custom' ) ),
						'group'       => __( 'Layout', 'universal-post-rss-loop' ),
					),

					// Display Tab
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Show Image', 'universal-post-rss-loop' ),
						'param_name'  => 'show_image',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'std'         => 'true',
						'group'       => __( 'Display Options', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Show Title', 'universal-post-rss-loop' ),
						'param_name'  => 'show_title',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'std'         => 'true',
						'group'       => __( 'Display Options', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Show Excerpt', 'universal-post-rss-loop' ),
						'param_name'  => 'show_excerpt',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'std'         => 'true',
						'group'       => __( 'Display Options', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Show Meta (Date/Author/Source)', 'universal-post-rss-loop' ),
						'param_name'  => 'show_date',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'std'         => 'true',
						'group'       => __( 'Display Options', 'universal-post-rss-loop' ),
					),
					array(
						'type'        => 'checkbox',
						'heading'     => __( 'Show Read More Button', 'universal-post-rss-loop' ),
						'param_name'  => 'show_read_more',
						'value'       => array( __( 'Yes', 'universal-post-rss-loop' ) => 'true' ),
						'std'         => 'true',
						'group'       => __( 'Display Options', 'universal-post-rss-loop' ),
					),
				),
			)
		);
	}
}

UPR_WPBakery::init();
