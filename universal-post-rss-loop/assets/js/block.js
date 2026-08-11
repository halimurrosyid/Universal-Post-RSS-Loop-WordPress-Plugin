(function(blocks, element, components, editor, serverSideRender, i18n) {
	'use strict';

	var el = element.createElement;
	var __ = i18n.__;
	var InspectorControls = editor.InspectorControls;
	var ColorPalette = editor.ColorPalette || components.ColorPalette;
	var ServerSideRender = serverSideRender;

	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	var ToggleControl = components.ToggleControl;
	var RangeControl = components.RangeControl;

	blocks.registerBlockType('upr/universal-post-rss-loop', {
		title: __('Universal Post & RSS Loop', 'universal-post-rss-loop'),
		icon: 'rss',
		category: 'widgets',
		keywords: [__('post loop', 'universal-post-rss-loop'), __('rss feed', 'universal-post-rss-loop'), __('rss', 'universal-post-rss-loop')],
		attributes: {
			source: { type: 'string', default: 'posts' },
			post_type: { type: 'string', default: 'post' },
			category: { type: 'string', default: '' },
			author: { type: 'string', default: '' },
			limit: { type: 'number', default: 6 },
			order: { type: 'string', default: 'DESC' },
			orderby: { type: 'string', default: 'date' },

			feed_url: { type: 'string', default: '' },
			feeds: { type: 'string', default: '' },
			cache_duration: { type: 'number', default: 3600 },
			dedupe_mode: { type: 'string', default: 'url_only' },

			layout: { type: 'string', default: 'grid' },
			columns: { type: 'number', default: 3 },
			custom_html: { type: 'string', default: '' },

			// Interactive Features (v2.0.0)
			show_search_bar: { type: 'boolean', default: false },
			show_filter_tabs: { type: 'boolean', default: false },
			show_read_time: { type: 'boolean', default: false },
			show_social_share: { type: 'boolean', default: false },
			pagination_type: { type: 'string', default: 'none' },
			items_per_page: { type: 'number', default: 6 },

			card_style: { type: 'string', default: 'classic' },
			button_style: { type: 'string', default: 'solid' },
			button_width: { type: 'string', default: 'auto' },
			border_radius: { type: 'string', default: 'medium' },
			box_shadow: { type: 'string', default: 'soft' },
			card_padding: { type: 'string', default: 'normal' },

			title_font_size: { type: 'string', default: 'medium' },
			excerpt_font_size: { type: 'string', default: 'medium' },
			max_title_chars: { type: 'number', default: 0 },
			max_excerpt_chars: { type: 'number', default: 0 },

			image_hover_effect: { type: 'string', default: 'zoom' },
			badge_position: { type: 'string', default: 'auto' },

			card_bg: { type: 'string', default: '' },
			border_color: { type: 'string', default: '' },
			title_color: { type: 'string', default: '' },
			title_hover_color: { type: 'string', default: '' },
			excerpt_color: { type: 'string', default: '' },
			meta_color: { type: 'string', default: '' },
			badge_bg: { type: 'string', default: '' },
			badge_color: { type: 'string', default: '' },
			button_bg: { type: 'string', default: '' },
			button_color: { type: 'string', default: '' },

			show_image: { type: 'boolean', default: true },
			image_position: { type: 'string', default: 'top' },
			image_ratio: { type: 'string', default: '16:9' },
			object_fit: { type: 'string', default: 'cover' },

			show_title: { type: 'boolean', default: true },
			title_tag: { type: 'string', default: 'h3' },

			show_excerpt: { type: 'boolean', default: true },
			show_date: { type: 'boolean', default: true },
			show_author: { type: 'boolean', default: true },
			show_category: { type: 'boolean', default: true },
			show_source: { type: 'boolean', default: true },

			show_read_more: { type: 'boolean', default: true },
			read_more_text: { type: 'string', default: 'Read More' },
			link_behavior: { type: 'string', default: 'card' },
			open_new_tab: { type: 'boolean', default: true }
		},

		edit: function(props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var inspectorControls = el(InspectorControls, {},
				// Data Source Panel
				el(PanelBody, { title: __('Data Source', 'universal-post-rss-loop'), initialOpen: true },
					el(SelectControl, {
						label: __('Select Source', 'universal-post-rss-loop'),
						value: attributes.source,
						options: [
							{ label: __('WordPress Posts', 'universal-post-rss-loop'), value: 'posts' },
							{ label: __('RSS Feed', 'universal-post-rss-loop'), value: 'rss' }
						],
						onChange: function(val) { setAttributes({ source: val }); }
					}),

					attributes.source === 'posts' && el(SelectControl, {
						label: __('Post Type (Auto Detected)', 'universal-post-rss-loop'),
						value: attributes.post_type,
						options: (window.uprData && window.uprData.post_types) ? window.uprData.post_types : [{ label: 'Post (post)', value: 'post' }],
						onChange: function(val) { setAttributes({ post_type: val }); }
					}),

					attributes.source === 'posts' && el(SelectControl, {
						label: __('Category (Auto Detected)', 'universal-post-rss-loop'),
						value: attributes.category,
						options: (window.uprData && window.uprData.categories) ? window.uprData.categories : [{ label: '-- All Categories --', value: '' }],
						onChange: function(val) { setAttributes({ category: val }); }
					}),

					attributes.source === 'rss' && el(TextControl, {
						label: __('RSS Feed URL', 'universal-post-rss-loop'),
						value: attributes.feed_url,
						placeholder: 'https://example.com/feed/',
						onChange: function(val) { setAttributes({ feed_url: val }); }
					}),

					attributes.source === 'rss' && el(TextareaControl, {
						label: __('Multiple Feed URLs (Comma/Line separated)', 'universal-post-rss-loop'),
						value: attributes.feeds,
						placeholder: 'https://site-a.com/feed/\nhttps://site-b.com/feed/',
						onChange: function(val) { setAttributes({ feeds: val }); }
					}),

					el(RangeControl, {
						label: __('Number of Total Items (Limit)', 'universal-post-rss-loop'),
						value: attributes.limit,
						min: 1,
						max: 50,
						onChange: function(val) { setAttributes({ limit: val }); }
					})
				),

				// Interactive Features Panel (v2.0.0)
				el(PanelBody, { title: __('Interactive Features (Search, Filters & Pagination)', 'universal-post-rss-loop'), initialOpen: false },
					el(ToggleControl, {
						label: __('Enable Live Search Bar', 'universal-post-rss-loop'),
						checked: attributes.show_search_bar,
						onChange: function(val) { setAttributes({ show_search_bar: val }); }
					}),

					el(ToggleControl, {
						label: __('Enable Category Filter Tabs', 'universal-post-rss-loop'),
						checked: attributes.show_filter_tabs,
						onChange: function(val) { setAttributes({ show_filter_tabs: val }); }
					}),

					el(ToggleControl, {
						label: __('Show Estimated Read Time (⏱️ min read)', 'universal-post-rss-loop'),
						checked: attributes.show_read_time,
						onChange: function(val) { setAttributes({ show_read_time: val }); }
					}),

					el(ToggleControl, {
						label: __('Show Social Media Share Icons', 'universal-post-rss-loop'),
						checked: attributes.show_social_share,
						onChange: function(val) { setAttributes({ show_social_share: val }); }
					}),

					el(SelectControl, {
						label: __('Pagination Mode', 'universal-post-rss-loop'),
						value: attributes.pagination_type,
						options: [
							{ label: __('None (Show All Items)', 'universal-post-rss-loop'), value: 'none' },
							{ label: __('Load More Button', 'universal-post-rss-loop'), value: 'load_more' },
							{ label: __('Numeric Page Numbers (1, 2, 3...)', 'universal-post-rss-loop'), value: 'numeric' }
						],
						onChange: function(val) { setAttributes({ pagination_type: val }); }
					}),

					attributes.pagination_type !== 'none' && el(RangeControl, {
						label: __('Items Per Page', 'universal-post-rss-loop'),
						value: attributes.items_per_page,
						min: 1,
						max: 30,
						onChange: function(val) { setAttributes({ items_per_page: val }); }
					})
				),

				// Preset & Card Design Panel
				el(PanelBody, { title: __('Card Presets & Layout Geometry', 'universal-post-rss-loop'), initialOpen: false },
					el(SelectControl, {
						label: __('Card Preset Style', 'universal-post-rss-loop'),
						value: attributes.card_style,
						options: [
							{ label: __('Classic Card (Border & Soft Shadow)', 'universal-post-rss-loop'), value: 'classic' },
							{ label: __('Modern Card (Floating & Badge Overlay)', 'universal-post-rss-loop'), value: 'modern' },
							{ label: __('Minimalist (Flat & Bottom Border)', 'universal-post-rss-loop'), value: 'minimal' },
							{ label: __('Overlay Hero (Full Dark Backdrop)', 'universal-post-rss-loop'), value: 'overlay' },
							{ label: __('Glassmorphism (Frosted Glass Effect)', 'universal-post-rss-loop'), value: 'glass' }
						],
						onChange: function(val) { setAttributes({ card_style: val }); }
					}),

					el(SelectControl, {
						label: __('Card Inner Padding', 'universal-post-rss-loop'),
						value: attributes.card_padding,
						options: [
							{ label: __('Compact (14px)', 'universal-post-rss-loop'), value: 'compact' },
							{ label: __('Normal (20px)', 'universal-post-rss-loop'), value: 'normal' },
							{ label: __('Spacious (28px)', 'universal-post-rss-loop'), value: 'spacious' }
						],
						onChange: function(val) { setAttributes({ card_padding: val }); }
					}),

					el(SelectControl, {
						label: __('Box Shadow Intensity', 'universal-post-rss-loop'),
						value: attributes.box_shadow,
						options: [
							{ label: __('None (Flat)', 'universal-post-rss-loop'), value: 'none' },
							{ label: __('Soft Shadow', 'universal-post-rss-loop'), value: 'soft' },
							{ label: __('Floating Medium Shadow', 'universal-post-rss-loop'), value: 'floating' },
							{ label: __('Heavy Deep Shadow', 'universal-post-rss-loop'), value: 'heavy' }
						],
						onChange: function(val) { setAttributes({ box_shadow: val }); }
					}),

					el(SelectControl, {
						label: __('Border Radius', 'universal-post-rss-loop'),
						value: attributes.border_radius,
						options: [
							{ label: __('Square (0px)', 'universal-post-rss-loop'), value: 'none' },
							{ label: __('Small (6px)', 'universal-post-rss-loop'), value: 'small' },
							{ label: __('Medium (12px)', 'universal-post-rss-loop'), value: 'medium' },
							{ label: __('Large (20px)', 'universal-post-rss-loop'), value: 'large' },
							{ label: __('Full Rounded (32px)', 'universal-post-rss-loop'), value: 'full' }
						],
						onChange: function(val) { setAttributes({ border_radius: val }); }
					})
				),

				// Typography & Truncation Panel
				el(PanelBody, { title: __('Typography & Text Truncation', 'universal-post-rss-loop'), initialOpen: false },
					el(SelectControl, {
						label: __('Title Font Size', 'universal-post-rss-loop'),
						value: attributes.title_font_size,
						options: [
							{ label: __('Small (16px)', 'universal-post-rss-loop'), value: 'small' },
							{ label: __('Medium (18px)', 'universal-post-rss-loop'), value: 'medium' },
							{ label: __('Large (22px)', 'universal-post-rss-loop'), value: 'large' },
							{ label: __('Extra Large (26px)', 'universal-post-rss-loop'), value: 'xlarge' }
						],
						onChange: function(val) { setAttributes({ title_font_size: val }); }
					}),

					el(RangeControl, {
						label: __('Max Title Length (Chars, 0 = No Limit)', 'universal-post-rss-loop'),
						value: attributes.max_title_chars,
						min: 0,
						max: 200,
						onChange: function(val) { setAttributes({ max_title_chars: val }); }
					}),

					el(SelectControl, {
						label: __('Excerpt Font Size', 'universal-post-rss-loop'),
						value: attributes.excerpt_font_size,
						options: [
							{ label: __('Small (13px)', 'universal-post-rss-loop'), value: 'small' },
							{ label: __('Medium (14px)', 'universal-post-rss-loop'), value: 'medium' },
							{ label: __('Large (15px)', 'universal-post-rss-loop'), value: 'large' }
						],
						onChange: function(val) { setAttributes({ excerpt_font_size: val }); }
					}),

					el(RangeControl, {
						label: __('Max Excerpt Length (Chars, 0 = No Limit)', 'universal-post-rss-loop'),
						value: attributes.max_excerpt_chars,
						min: 0,
						max: 300,
						onChange: function(val) { setAttributes({ max_excerpt_chars: val }); }
					})
				),

				// Media & Image Effects Panel
				el(PanelBody, { title: __('Media & Image Effects', 'universal-post-rss-loop'), initialOpen: false },
					el(ToggleControl, {
						label: __('Show Featured / RSS Image', 'universal-post-rss-loop'),
						checked: attributes.show_image,
						onChange: function(val) { setAttributes({ show_image: val }); }
					}),

					attributes.show_image && el(SelectControl, {
						label: __('Image Aspect Ratio', 'universal-post-rss-loop'),
						value: attributes.image_ratio,
						options: [
							{ label: '16:9 Widescreen', value: '16:9' },
							{ label: '4:3 Standard', value: '4:3' },
							{ label: '1:1 Square', value: '1:1' },
							{ label: '3:2 Photo', value: '3:2' },
							{ label: 'Auto (Natural Size)', value: 'auto' }
						],
						onChange: function(val) { setAttributes({ image_ratio: val }); }
					}),

					attributes.show_image && el(SelectControl, {
						label: __('Image Hover Effect', 'universal-post-rss-loop'),
						value: attributes.image_hover_effect,
						options: [
							{ label: __('Zoom In', 'universal-post-rss-loop'), value: 'zoom' },
							{ label: __('Brighten', 'universal-post-rss-loop'), value: 'brighten' },
							{ label: __('None', 'universal-post-rss-loop'), value: 'none' }
						],
						onChange: function(val) { setAttributes({ image_hover_effect: val }); }
					}),

					el(SelectControl, {
						label: __('Badge Position (Category / Source)', 'universal-post-rss-loop'),
						value: attributes.badge_position,
						options: [
							{ label: __('Auto (Default per Preset)', 'universal-post-rss-loop'), value: 'auto' },
							{ label: __('Floating Overlay on Image', 'universal-post-rss-loop'), value: 'overlay' },
							{ label: __('Inline inside Meta Text', 'universal-post-rss-loop'), value: 'inline' }
						],
						onChange: function(val) { setAttributes({ badge_position: val }); }
					})
				),

				// Button Styling Panel
				el(PanelBody, { title: __('Read More Button Controls', 'universal-post-rss-loop'), initialOpen: false },
					el(ToggleControl, {
						label: __('Show Read More Button', 'universal-post-rss-loop'),
						checked: attributes.show_read_more,
						onChange: function(val) { setAttributes({ show_read_more: val }); }
					}),

					attributes.show_read_more && el(TextControl, {
						label: __('Read More Button Text', 'universal-post-rss-loop'),
						value: attributes.read_more_text,
						onChange: function(val) { setAttributes({ read_more_text: val }); }
					}),

					attributes.show_read_more && el(SelectControl, {
						label: __('Button Style', 'universal-post-rss-loop'),
						value: attributes.button_style,
						options: [
							{ label: __('Solid Filled Button', 'universal-post-rss-loop'), value: 'solid' },
							{ label: __('Outline Button', 'universal-post-rss-loop'), value: 'outline' },
							{ label: __('Pill Rounded Button', 'universal-post-rss-loop'), value: 'pill' },
							{ label: __('Simple Text Link', 'universal-post-rss-loop'), value: 'link' }
						],
						onChange: function(val) { setAttributes({ button_style: val }); }
					}),

					attributes.show_read_more && el(SelectControl, {
						label: __('Button Width', 'universal-post-rss-loop'),
						value: attributes.button_width,
						options: [
							{ label: __('Auto (Text Length)', 'universal-post-rss-loop'), value: 'auto' },
							{ label: __('Full Width (100%)', 'universal-post-rss-loop'), value: 'full' }
						],
						onChange: function(val) { setAttributes({ button_width: val }); }
					})
				),

				// Layout Grid Panel
				el(PanelBody, { title: __('Layout & Grid Options', 'universal-post-rss-loop'), initialOpen: false },
					el(SelectControl, {
						label: __('Layout Mode', 'universal-post-rss-loop'),
						value: attributes.layout,
						options: [
							{ label: __('Grid', 'universal-post-rss-loop'), value: 'grid' },
							{ label: __('List', 'universal-post-rss-loop'), value: 'list' },
							{ label: __('Horizontal Scroll', 'universal-post-rss-loop'), value: 'horizontal' },
							{ label: __('Custom HTML Template', 'universal-post-rss-loop'), value: 'custom' }
						],
						onChange: function(val) { setAttributes({ layout: val }); }
					}),

					attributes.layout === 'grid' && el(RangeControl, {
						label: __('Columns (1 - 6)', 'universal-post-rss-loop'),
						value: attributes.columns,
						min: 1,
						max: 6,
						onChange: function(val) { setAttributes({ columns: val }); }
					}),

					attributes.layout === 'custom' && el(TextareaControl, {
						label: __('Custom HTML Code', 'universal-post-rss-loop'),
						value: attributes.custom_html,
						help: __('Placeholders: {{image}}, {{title}}, {{excerpt}}, {{date}}, {{author}}, {{category}}, {{source_name}}, {{url}}, {{read_more}}', 'universal-post-rss-loop'),
						onChange: function(val) { setAttributes({ custom_html: val }); }
					})
				),

				// Granular Colors Panel
				el(PanelBody, { title: __('Color Customization Palette', 'universal-post-rss-loop'), initialOpen: false },
					el('p', {}, __('Card Background Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.card_bg,
						onChange: function(val) { setAttributes({ card_bg: val || '' }); }
					}),

					el('p', {}, __('Card Border Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.border_color,
						onChange: function(val) { setAttributes({ border_color: val || '' }); }
					}),

					el('p', {}, __('Title Font Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.title_color,
						onChange: function(val) { setAttributes({ title_color: val || '' }); }
					}),

					el('p', {}, __('Title Hover Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.title_hover_color,
						onChange: function(val) { setAttributes({ title_hover_color: val || '' }); }
					}),

					el('p', {}, __('Excerpt Font Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.excerpt_color,
						onChange: function(val) { setAttributes({ excerpt_color: val || '' }); }
					}),

					el('p', {}, __('Meta Text Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.meta_color,
						onChange: function(val) { setAttributes({ meta_color: val || '' }); }
					}),

					el('p', {}, __('Badge Background Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.badge_bg,
						onChange: function(val) { setAttributes({ badge_bg: val || '' }); }
					}),

					el('p', {}, __('Badge Text Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.badge_color,
						onChange: function(val) { setAttributes({ badge_color: val || '' }); }
					}),

					el('p', {}, __('Button Background Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.button_bg,
						onChange: function(val) { setAttributes({ button_bg: val || '' }); }
					}),

					el('p', {}, __('Button Text Color', 'universal-post-rss-loop')),
					el(ColorPalette, {
						value: attributes.button_color,
						onChange: function(val) { setAttributes({ button_color: val || '' }); }
					})
				)
			);

			return [
				inspectorControls,
				el(ServerSideRender, {
					block: 'upr/universal-post-rss-loop',
					attributes: attributes
				})
			];
		},

		save: function() {
			return null; // Rendered server-side
		}
	});

})(
	window.wp.blocks,
	window.wp.element,
	window.wp.components,
	window.wp.editor,
	window.wp.serverSideRender,
	window.wp.i18n
);
