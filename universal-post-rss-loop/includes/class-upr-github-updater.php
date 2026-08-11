<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Native GitHub Auto-Updater for WordPress Plugin (v2.0.2)
 * Enables 1-click update directly from wp-admin/plugins.php via GitHub Releases or Tags
 */
class UPR_GitHub_Updater {

	private $file;
	private $plugin_slug;
	private $version;
	private $github_repo = 'halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin';

	public function __construct( $file, $version ) {
		$this->file        = $file;
		$this->plugin_slug = plugin_basename( $file );
		$this->version     = $version;

		add_filter( 'site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'post_install' ), 10, 3 );
		add_filter( 'plugin_row_meta', array( $this, 'add_check_update_link' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'handle_force_check' ) );
	}

	/**
	 * Add "Check for updates" link in plugin row meta
	 */
	public function add_check_update_link( $links, $file ) {
		if ( $file === $this->plugin_slug ) {
			$url = wp_nonce_url( admin_url( 'plugins.php?upr_check_update=1' ), 'upr_check_update_nonce' );
			$links[] = '<a href="' . esc_url( $url ) . '" style="color:#2563eb;font-weight:600;">🔄 Check for updates</a>';
		}
		return $links;
	}

	/**
	 * Handle manual force update check trigger
	 */
	public function handle_force_check() {
		if ( isset( $_GET['upr_check_update'] ) && check_admin_referer( 'upr_check_update_nonce' ) ) {
			delete_transient( 'upr_github_release_info' );
			delete_site_transient( 'update_plugins' );
			wp_safe_redirect( admin_url( 'plugins.php?upr_checked=1' ) );
			exit;
		}
	}

	/**
	 * Check for updates against GitHub Releases or Tags API
	 */
	public function check_update( $transient ) {
		if ( empty( $transient ) || ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $transient;
		}

		$github_version = ltrim( $release->tag_name, 'v' );

		if ( version_compare( $this->version, $github_version, '<' ) ) {
			$download_url = ! empty( $release->assets[0]->browser_download_url )
				? $release->assets[0]->browser_download_url
				: ( ! empty( $release->zipball_url ) ? $release->zipball_url : 'https://github.com/' . $this->github_repo . '/archive/refs/tags/v' . $github_version . '.zip' );

			$obj              = new stdClass();
			$obj->slug        = 'universal-post-rss-loop';
			$obj->plugin      = $this->plugin_slug;
			$obj->new_version = $github_version;
			$obj->url         = 'https://github.com/' . $this->github_repo;
			$obj->package     = $download_url;

			$transient->response[ $this->plugin_slug ] = $obj;
		}

		return $transient;
	}

	/**
	 * Populate WP Plugin Details popup modal
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || ( $args->slug !== $this->plugin_slug && $args->slug !== 'universal-post-rss-loop' ) ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $result;
		}

		$github_version = ltrim( $release->tag_name, 'v' );

		$res                = new stdClass();
		$res->name          = 'Universal Post & RSS Loop';
		$res->slug          = $this->plugin_slug;
		$res->version       = $github_version;
		$res->author        = '<a href="https://ajidmujaddid.staff.telkomuniversity.ac.id/">Mujaddid Halimurrosyid</a>';
		$res->homepage      = 'https://github.com/' . $this->github_repo;
		$res->download_link = ! empty( $release->assets[0]->browser_download_url )
			? $release->assets[0]->browser_download_url
			: ( ! empty( $release->zipball_url ) ? $release->zipball_url : 'https://github.com/' . $this->github_repo . '/archive/refs/tags/v' . $github_version . '.zip' );

		$res->sections = array(
			'description' => 'Unified post grid/list display for WordPress Posts and External RSS Feeds using the exact same card design.',
			'changelog'   => isset( $release->body ) ? wp_kses_post( nl2br( $release->body ) ) : 'See GitHub releases for details.',
		);

		return $res;
	}

	/**
	 * Ensure unzipped folder matches exact plugin directory name
	 */
	public function post_install( $true, $hook_extra, $result ) {
		global $wp_filesystem;

		$proper_folder = UPR_PLUGIN_DIR;
		$install_directory = isset( $result['destination'] ) ? $result['destination'] : '';

		if ( ! empty( $install_directory ) && $install_directory !== $proper_folder ) {
			$wp_filesystem->move( $install_directory, $proper_folder );
			$result['destination'] = $proper_folder;
		}

		return $result;
	}

	/**
	 * Fetch latest release from GitHub API with fallback to tags API
	 */
	private function get_latest_release() {
		$cache_key = 'upr_github_release_info';

		if ( isset( $_GET['upr_check_update'] ) || isset( $_GET['force-check'] ) || ( isset( $GLOBALS['pagenow'] ) && 'update-core.php' === $GLOBALS['pagenow'] ) ) {
			delete_transient( $cache_key );
		} else {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		// Try official releases first
		$url      = 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest';
		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
				),
				'timeout' => 10,
			)
		);

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! empty( $body ) && is_object( $body ) ) {
				set_transient( $cache_key, $body, 1 * HOUR_IN_SECONDS );
				return $body;
			}
		}

		// Fallback to tags API if no GitHub Release object exists
		$tags_url = 'https://api.github.com/repos/' . $this->github_repo . '/tags';
		$tags_res = wp_remote_get(
			$tags_url,
			array(
				'headers' => array(
					'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
				),
				'timeout' => 10,
			)
		);

		if ( ! is_wp_error( $tags_res ) && 200 === wp_remote_retrieve_response_code( $tags_res ) ) {
			$tags = json_decode( wp_remote_retrieve_body( $tags_res ) );
			if ( ! empty( $tags ) && is_array( $tags ) ) {
				$latest_tag = $tags[0];
				$body = new stdClass();
				$body->tag_name = $latest_tag->name;
				$body->zipball_url = 'https://github.com/' . $this->github_repo . '/archive/refs/tags/' . $latest_tag->name . '.zip';
				$body->body = 'Release ' . $latest_tag->name;
				set_transient( $cache_key, $body, 1 * HOUR_IN_SECONDS );
				return $body;
			}
		}

		return false;
	}
}
