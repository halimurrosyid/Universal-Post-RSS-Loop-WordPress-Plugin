<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UPR_Admin
 * Manages WP Admin Menu, Settings, Cache Clearing, and RSS Feed Tester tool.
 */
class UPR_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_upr_test_rss_feed', array( __CLASS__, 'ajax_test_rss_feed' ) );
		add_action( 'admin_post_upr_clear_cache', array( __CLASS__, 'handle_clear_cache' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'add_plugin_row_meta' ), 10, 2 );
		add_action( 'admin_footer', array( __CLASS__, 'render_plugin_details_modal' ) );
	}

	public static function register_admin_menu() {
		add_menu_page(
			__( 'Universal Post & RSS Loop', 'universal-post-rss-loop' ),
			__( 'Universal Loop', 'universal-post-rss-loop' ),
			'manage_options',
			'universal-post-rss-loop',
			array( __CLASS__, 'render_settings_page' ),
			'dashicons-rss',
			30
		);

		add_submenu_page(
			'universal-post-rss-loop',
			__( 'Settings', 'universal-post-rss-loop' ),
			__( 'Settings', 'universal-post-rss-loop' ),
			'manage_options',
			'universal-post-rss-loop',
			array( __CLASS__, 'render_settings_page' )
		);

		add_submenu_page(
			'universal-post-rss-loop',
			__( 'RSS Tester', 'universal-post-rss-loop' ),
			__( 'RSS Tester', 'universal-post-rss-loop' ),
			'manage_options',
			'upr-rss-tester',
			array( __CLASS__, 'render_rss_tester_page' )
		);
	}

	public static function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'universal-post-rss-loop' ) === false && strpos( $hook, 'upr-rss-tester' ) === false ) {
			return;
		}

		wp_enqueue_style( 'upr-admin-css', UPR_PLUGIN_URL . 'assets/css/admin.css', array(), UPR_VERSION );
		wp_enqueue_style( 'upr-frontend-css', UPR_PLUGIN_URL . 'assets/css/frontend.css', array(), UPR_VERSION );
		wp_enqueue_script( 'upr-admin-js', UPR_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), UPR_VERSION, true );

		wp_localize_script(
			'upr-admin-js',
			'uprAdmin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'upr_admin_nonce' ),
			)
		);
	}

	public static function register_settings() {
		register_setting( 'upr_options_group', 'upr_default_cache_duration' );
		register_setting( 'upr_options_group', 'upr_default_fallback_image' );
		register_setting( 'upr_options_group', 'upr_dedupe_mode' );
	}

	public static function handle_clear_cache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'universal-post-rss-loop' ) );
		}

		check_admin_referer( 'upr_clear_cache_action', 'upr_clear_cache_nonce' );

		$cleared = UPR_Cache::clear_all_cache();

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'universal-post-rss-loop',
					'cleared' => $cleared,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
		?>
		<div class="wrap upr-admin-wrap">
			<h1><span class="dashicons dashicons-rss"></span> <?php esc_html_e( 'Universal Post & RSS Loop Settings', 'universal-post-rss-loop' ); ?></h1>

			<?php if ( isset( $_GET['cleared'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php printf( esc_html__( 'Success: Cleared %d cached feed transients.', 'universal-post-rss-loop' ), intval( $_GET['cleared'] ) ); ?></p>
				</div>
			<?php endif; ?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=universal-post-rss-loop&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General', 'universal-post-rss-loop' ); ?></a>
				<a href="?page=universal-post-rss-loop&tab=shortcode" class="nav-tab <?php echo $active_tab === 'shortcode' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Shortcode Guide', 'universal-post-rss-loop' ); ?></a>
				<a href="?page=universal-post-rss-loop&tab=cache" class="nav-tab <?php echo $active_tab === 'cache' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Cache', 'universal-post-rss-loop' ); ?></a>
				<a href="?page=universal-post-rss-loop&tab=rss" class="nav-tab <?php echo $active_tab === 'rss' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'RSS Settings', 'universal-post-rss-loop' ); ?></a>
				<a href="?page=universal-post-rss-loop&tab=templates" class="nav-tab <?php echo $active_tab === 'templates' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Templates & Overrides', 'universal-post-rss-loop' ); ?></a>
				<a href="?page=universal-post-rss-loop&tab=advanced" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Advanced & Debug', 'universal-post-rss-loop' ); ?></a>
			</h2>

			<div class="upr-admin-card">
				<?php if ( $active_tab === 'general' ) : ?>
					<form method="post" action="options.php">
						<?php
						settings_fields( 'upr_options_group' );
						do_settings_sections( 'upr_options_group' );
						?>
						<table class="form-table">
							<tr>
								<th scope="row"><?php esc_html_e( 'Default Fallback Image URL', 'universal-post-rss-loop' ); ?></th>
								<td>
									<input type="url" name="upr_default_fallback_image" value="<?php echo esc_attr( get_option( 'upr_default_fallback_image', '' ) ); ?>" class="regular-text" placeholder="https://example.com/default-thumbnail.jpg" />
									<p class="description"><?php esc_html_e( 'Used when an RSS item or Post has no image.', 'universal-post-rss-loop' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Default Deduplication Mode', 'universal-post-rss-loop' ); ?></th>
								<td>
									<select name="upr_dedupe_mode">
										<option value="url_only" <?php selected( get_option( 'upr_dedupe_mode', 'url_only' ), 'url_only' ); ?>><?php esc_html_e( 'URL Only (Default)', 'universal-post-rss-loop' ); ?></option>
										<option value="guid_url" <?php selected( get_option( 'upr_dedupe_mode' ), 'guid_url' ); ?>><?php esc_html_e( 'GUID + URL', 'universal-post-rss-loop' ); ?></option>
										<option value="url_title" <?php selected( get_option( 'upr_dedupe_mode' ), 'url_title' ); ?>><?php esc_html_e( 'URL + Normalized Title', 'universal-post-rss-loop' ); ?></option>
										<option value="strict" <?php selected( get_option( 'upr_dedupe_mode' ), 'strict' ); ?>><?php esc_html_e( 'Strict (URL + GUID + Title)', 'universal-post-rss-loop' ); ?></option>
									</select>
								</td>
							</tr>
						</table>
						<?php submit_button(); ?>
					</form>

				<?php elseif ( $active_tab === 'shortcode' ) : ?>
					<h3>📖 <?php esc_html_e( 'Shortcode Documentation & Reference', 'universal-post-rss-loop' ); ?></h3>
					<p><?php esc_html_e( 'You can insert the loop anywhere on your website using the shortcode:', 'universal-post-rss-loop' ); ?></p>
					<code>[universal_post_rss_loop]</code>

					<h4>1. <?php esc_html_e( 'Shortcode Examples', 'universal-post-rss-loop' ); ?></h4>
					<p><strong>A. Single RSS Feed (Grid 3 Columns, Modern Style):</strong></p>
					<pre><code>[universal_post_rss_loop source="rss" feed_url="https://site.com/feed/" card_style="modern" limit="6" columns="3"]</code></pre>

					<p><strong>B. Multiple RSS Feeds (Merged & Sorted):</strong></p>
					<pre><code>[universal_post_rss_loop source="rss" feeds="https://site-a.com/feed/, https://site-b.com/feed/" limit="8" columns="4"]</code></pre>

					<p><strong>C. WordPress Posts Native:</strong></p>
					<pre><code>[universal_post_rss_loop source="posts" post_type="post" category="news" limit="6" columns="3"]</code></pre>

					<p><strong>D. Custom Colors & Pill Buttons:</strong></p>
					<pre><code>[universal_post_rss_loop source="rss" feed_url="https://site.com/feed/" card_style="glass" button_style="pill" card_bg="#ffffff" title_color="#0f172a" button_bg="#2563eb"]</code></pre>

					<h4>2. <?php esc_html_e( 'Full Attribute Parameter Reference', 'universal-post-rss-loop' ); ?></h4>
					<table class="widefat striped">
						<thead>
							<tr>
								<th>Attribute</th>
								<th>Accepted Values</th>
								<th>Default</th>
								<th>Description</th>
							</tr>
						</thead>
						<tbody>
							<tr><td><code>source</code></td><td><code>posts</code>, <code>rss</code></td><td><code>posts</code></td><td>Data source selector</td></tr>
							<tr><td><code>feed_url</code></td><td>Valid URL</td><td><em>Empty</em></td><td>Single RSS / Atom Feed URL</td></tr>
							<tr><td><code>feeds</code></td><td>Comma-separated URLs</td><td><em>Empty</em></td><td>Multiple RSS Feed URLs to merge</td></tr>
							<tr><td><code>limit</code></td><td>Number (1–100)</td><td><code>6</code></td><td>Number of articles to display</td></tr>
							<tr><td><code>card_style</code></td><td><code>classic</code>, <code>modern</code>, <code>minimal</code>, <code>overlay</code>, <code>glass</code></td><td><code>classic</code></td><td>Card preset design style</td></tr>
							<tr><td><code>button_style</code></td><td><code>solid</code>, <code>outline</code>, <code>pill</code>, <code>link</code></td><td><code>solid</code></td><td>Read More button style</td></tr>
							<tr><td><code>border_radius</code></td><td><code>none</code>, <code>small</code>, <code>medium</code>, <code>large</code>, <code>full</code></td><td><code>medium</code></td><td>Card corner border radius</td></tr>
							<tr><td><code>layout</code></td><td><code>grid</code>, <code>list</code>, <code>horizontal</code>, <code>custom</code></td><td><code>grid</code></td><td>Layout container type</td></tr>
							<tr><td><code>columns</code></td><td><code>1</code> to <code>6</code></td><td><code>3</code></td><td>Number of grid columns</td></tr>
							<tr><td><code>card_bg</code></td><td>Hex Color (e.g. <code>#ffffff</code>)</td><td><em>Default</em></td><td>Card background color</td></tr>
							<tr><td><code>title_color</code></td><td>Hex Color (e.g. <code>#0f172a</code>)</td><td><em>Default</em></td><td>Title font color</td></tr>
							<tr><td><code>button_bg</code></td><td>Hex Color (e.g. <code>#2563eb</code>)</td><td><em>Default</em></td><td>Button background color</td></tr>
							<tr><td><code>show_image</code></td><td><code>true</code>, <code>false</code></td><td><code>true</code></td><td>Toggle featured / RSS image</td></tr>
							<tr><td><code>show_title</code></td><td><code>true</code>, <code>false</code></td><td><code>true</code></td><td>Toggle post / RSS title</td></tr>
							<tr><td><code>show_excerpt</code></td><td><code>true</code>, <code>false</code></td><td><code>true</code></td><td>Toggle post excerpt</td></tr>
							<tr><td><code>show_date</code></td><td><code>true</code>, <code>false</code></td><td><code>true</code></td><td>Toggle publication date</td></tr>
							<tr><td><code>show_source</code></td><td><code>true</code>, <code>false</code></td><td><code>true</code></td><td>Toggle RSS source website name</td></tr>
							<tr><td><code>show_read_more</code></td><td><code>true</code>, <code>false</code></td><td><code>true</code></td><td>Toggle Read More button</td></tr>
							<tr><td><code>read_more_text</code></td><td>Text string</td><td><code>Read More</code></td><td>Custom button text</td></tr>
						</tbody>
					</table>

				<?php elseif ( $active_tab === 'cache' ) : ?>
					<h3><?php esc_html_e( 'Feed Cache Management', 'universal-post-rss-loop' ); ?></h3>
					<p><?php esc_html_e( 'RSS feed results are cached using WordPress Transients to ensure high website loading performance.', 'universal-post-rss-loop' ); ?></p>
					
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="upr_clear_cache" />
						<?php wp_nonce_field( 'upr_clear_cache_action', 'upr_clear_cache_nonce' ); ?>
						<p>
							<?php submit_button( __( 'Clear All Feed Cache', 'universal-post-rss-loop' ), 'delete', 'submit', false ); ?>
						</p>
					</form>

				<?php elseif ( $active_tab === 'rss' ) : ?>
					<h3><?php esc_html_e( 'RSS Engine Information', 'universal-post-rss-loop' ); ?></h3>
					<p><?php esc_html_e( 'Use our dedicated RSS Feed Tester tool to diagnose external feed connectivity and XML structure.', 'universal-post-rss-loop' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=upr-rss-tester' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Open RSS Feed Tester →', 'universal-post-rss-loop' ); ?></a>

				<?php elseif ( $active_tab === 'templates' ) : ?>
					<h3><?php esc_html_e( 'Theme Template Overrides', 'universal-post-rss-loop' ); ?></h3>
					<p><?php esc_html_e( 'You can copy plugin template files into your active theme to customize HTML structure:', 'universal-post-rss-loop' ); ?></p>
					<code>wp-content/themes/YOUR-THEME/universal-post-rss-loop/item.php</code><br><br>
					<code>wp-content/themes/YOUR-THEME/universal-post-rss-loop/loop.php</code>

					<h4><?php esc_html_e( 'Custom HTML Template Placeholders', 'universal-post-rss-loop' ); ?></h4>
					<ul>
						<li><code>{{image}}</code> - <?php esc_html_e( 'Featured or RSS image URL', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{title}}</code> - <?php esc_html_e( 'Item title', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{excerpt}}</code> - <?php esc_html_e( 'Item excerpt/description snippet', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{date}}</code> - <?php esc_html_e( 'Publication date', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{author}}</code> - <?php esc_html_e( 'Author name', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{category}}</code> - <?php esc_html_e( 'Category name', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{source_name}}</code> - <?php esc_html_e( 'Source website name', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{source_url}}</code> - <?php esc_html_e( 'Source website homepage URL', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{url}}</code> - <?php esc_html_e( 'Original article link (WP Permalink or External RSS URL)', 'universal-post-rss-loop' ); ?></li>
						<li><code>{{read_more}}</code> - <?php esc_html_e( 'Read More text', 'universal-post-rss-loop' ); ?></li>
					</ul>

				<?php elseif ( $active_tab === 'advanced' ) : ?>
					<h3><?php esc_html_e( 'System Debug Information', 'universal-post-rss-loop' ); ?></h3>
					<table class="widefat striped">
						<tr><td><strong>Plugin Version</strong></td><td><?php echo UPR_VERSION; ?></td></tr>
						<tr><td><strong>PHP Version</strong></td><td><?php echo PHP_VERSION; ?></td></tr>
						<tr><td><strong>WordPress Version</strong></td><td><?php echo get_bloginfo( 'version' ); ?></td></tr>
						<tr><td><strong>SimpleXML Available</strong></td><td><?php echo class_exists( 'SimpleXMLElement' ) ? 'Yes (Active)' : 'No (Required for RSS)'; ?></td></tr>
						<tr><td><strong>cURL Installed</strong></td><td><?php echo function_exists( 'curl_init' ) ? 'Yes (Active)' : 'No'; ?></td></tr>
						<tr><td><strong>WPBakery Detected</strong></td><td><?php echo function_exists( 'vc_map' ) ? 'Yes' : 'No'; ?></td></tr>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public static function render_rss_tester_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap upr-admin-wrap">
			<h1><span class="dashicons dashicons-rest-api"></span> <?php esc_html_e( 'RSS Feed Tester', 'universal-post-rss-loop' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Test any external RSS or Atom feed URL to diagnose HTTP connectivity, security/WAF status, XML syntax, image parsing, and preview output.', 'universal-post-rss-loop' ); ?></p>

			<div class="upr-tester-box">
				<div class="upr-tester-form">
					<label for="upr_test_url"><strong><?php esc_html_e( 'RSS Feed URL:', 'universal-post-rss-loop' ); ?></strong></label>
					<div class="upr-tester-input-group">
						<input type="url" id="upr_test_url" class="large-text" placeholder="https://example.com/feed/" value="https://wordpress.org/news/feed/" />
						<button type="button" id="upr_btn_test" class="button button-primary button-hero"><?php esc_html_e( 'Test Feed', 'universal-post-rss-loop' ); ?></button>
					</div>
				</div>

				<div id="upr_tester_loading" style="display:none;" class="upr-tester-loading">
					<span class="spinner is-active"></span> <?php esc_html_e( 'Testing feed URL... Please wait.', 'universal-post-rss-loop' ); ?>
				</div>

				<div id="upr_tester_results" style="display:none;" class="upr-tester-results">
					<!-- AJAX result injected here -->
				</div>
			</div>
		</div>
		<?php
	}

	public static function ajax_test_rss_feed() {
		check_ajax_referer( 'upr_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized user action.', 'universal-post-rss-loop' ) ) );
		}

		$feed_url = isset( $_POST['feed_url'] ) ? esc_url_raw( $_POST['feed_url'] ) : '';
		if ( empty( $feed_url ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid feed URL.', 'universal-post-rss-loop' ) ) );
		}

		$result = UPR_RSS_Parser::parse_feed( $feed_url );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'status_code' => $result->get_error_code(),
					'message'     => $result->get_error_message(),
				)
			);
		}

		$first_item = ! empty( $result['items'] ) ? $result['items'][0] : null;
		$preview_html = '';

		if ( $first_item ) {
			$normalized_item = new UPR_Item(
				array(
					'id'          => 'rss_test_1',
					'title'       => $first_item['title'],
					'url'         => $first_item['url'],
					'image'       => $first_item['image'],
					'excerpt'     => $first_item['excerpt'],
					'date'        => $first_item['date'],
					'author'      => $first_item['author'],
					'category'    => $first_item['category'],
					'source_name' => $first_item['source_name'],
					'source_url'  => $first_item['source_url'],
					'is_external' => true,
				)
			);

			$preview_html = UPR_Template::render(
				'item.php',
				array(
					'item'     => $normalized_item,
					'settings' => array(
						'show_image'     => true,
						'show_title'     => true,
						'show_excerpt'   => true,
						'show_date'      => true,
						'show_author'    => true,
						'show_source'    => true,
						'show_read_more' => true,
						'link_behavior'  => 'card',
						'open_new_tab'   => true,
					),
				)
			);
		}

		wp_send_json_success(
			array(
				'feed_url'      => $feed_url,
				'http_status'   => isset( $result['http_status'] ) ? $result['http_status'] : 200,
				'content_type'  => isset( $result['content_type'] ) ? $result['content_type'] : 'application/rss+xml',
				'feed_type'     => isset( $result['feed_type'] ) ? $result['feed_type'] : 'RSS 2.0',
				'item_count'    => count( $result['items'] ),
				'has_image'     => $first_item && ! empty( $first_item['image'] ),
				'first_image'   => $first_item ? $first_item['image'] : '',
				'first_title'   => $first_item ? $first_item['title'] : '',
				'first_url'     => $first_item ? $first_item['url'] : '',
				'preview_html'  => $preview_html,
			)
		);
	}

	/**
	 * Add "View details" link to plugin row meta in wp-admin/plugins.php
	 */
	public static function add_plugin_row_meta( $links, $file ) {
		if ( $file === plugin_basename( UPR_PLUGIN_FILE ) ) {
			add_thickbox();
			$details_link = sprintf(
				'<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
				esc_url( '#TB_inline?width=750&height=550&inlineId=upr-plugin-details-modal' ),
				esc_attr__( 'More information about Universal Post & RSS Loop', 'universal-post-rss-loop' ),
				esc_attr__( 'Universal Post & RSS Loop Details', 'universal-post-rss-loop' ),
				esc_html__( 'View details', 'universal-post-rss-loop' )
			);
			$links[] = $details_link;
		}
		return $links;
	}

	/**
	 * Render Thickbox Popup Modal HTML in Admin Footer
	 */
	public static function render_plugin_details_modal() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== 'plugins' ) {
			return;
		}
		?>
		<div id="upr-plugin-details-modal" style="display:none;">
			<div class="upr-modal-container">
				<div class="upr-modal-header">
					<h2>Universal Post & RSS Loop <span class="upr-modal-version">v<?php echo UPR_VERSION; ?></span></h2>
					<p class="upr-modal-author">
						<?php esc_html_e( 'By', 'universal-post-rss-loop' ); ?> 
						<a href="https://ajidmujaddid.staff.telkomuniversity.ac.id/" target="_blank" rel="noopener">Mujaddid Halimurrosyid</a> 
						| Telkom University
					</p>
				</div>

				<div class="upr-modal-body">
					<div class="upr-modal-section">
						<h3>🚀 <?php esc_html_e( 'Description & Key Concept', 'universal-post-rss-loop' ); ?></h3>
						<p><?php esc_html_e( 'Universal Post & RSS Loop is an all-in-one post display engine designed to seamlessly switch data sources between local WordPress Posts and external RSS/Atom Feeds while preserving identical card layouts.', 'universal-post-rss-loop' ); ?></p>
						<p><strong><?php esc_html_e( 'Zero Database Import:', 'universal-post-rss-loop' ); ?></strong> <?php esc_html_e( 'External RSS items are dynamically fetched and cached. Clicking RSS cards redirects users directly to original external article URLs.', 'universal-post-rss-loop' ); ?></p>
					</div>

					<div class="upr-modal-section">
						<h3>✨ <?php esc_html_e( 'Features & Presets (v1.1.0)', 'universal-post-rss-loop' ); ?></h3>
						<ul>
							<li><strong>5 Layout Presets:</strong> Classic Card, Modern Floating, Minimalist Flat, Overlay Hero, and Glassmorphism Frosted Glass.</li>
							<li><strong>Color Customization:</strong> Customize Card Background, Title font color, Excerpt color, Badge pill colors, and Button colors.</li>
							<li><strong>Button Variants:</strong> Solid Filled, Outline, Pill Rounded, and Text Link.</li>
							<li><strong>Multi-Feed Engine:</strong> Merge, sort by date, and deduplicate multiple RSS feed URLs.</li>
							<li><strong>RSS Tester Tool:</strong> Built-in diagnostic tool under Settings → Universal Post & RSS Loop → RSS Tester.</li>
						</ul>
					</div>

					<div class="upr-modal-section">
						<h3>📝 <?php esc_html_e( 'Usage & Editors', 'universal-post-rss-loop' ); ?></h3>
						<p><strong>Gutenberg Block:</strong> Search block <code>Universal Post & RSS Loop</code>.</p>
						<p><strong>WPBakery Element:</strong> Add element <code>Universal Post & RSS Loop</code>.</p>
						<p><strong>Shortcode:</strong></p>
						<pre><code>[universal_post_rss_loop source="rss" feed_url="https://example.com/feed/" card_style="modern" limit="6" columns="3"]</code></pre>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

UPR_Admin::init();
