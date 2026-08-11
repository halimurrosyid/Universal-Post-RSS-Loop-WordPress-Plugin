<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Native GitHub Auto-Updater for WordPress Plugin
 * Enables 1-click update directly from wp-admin/plugins.php via GitHub Releases
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
	}

	/**
	 * Check for updates against GitHub Releases API
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $transient;
		}

		$github_version = ltrim( $release->tag_name, 'v' );

		if ( version_compare( $this->version, $github_version, '<' ) ) {
			$download_url = ! empty( $release->assets[0]->browser_download_url )
				? $release->assets[0]->browser_download_url
				: $release->zipball_url;

			$obj              = new stdClass();
			$obj->slug        = $this->plugin_slug;
			$obj->plugin      = $this->plugin_slug;
			$obj->new_version = $github_version;
			$obj->url         = 'https://github.com/' . $this->github_repo;
			$obj->package     = $download_url;
			$obj->icons       = array( 'default' => 'https://raw.githubusercontent.com/' . $this->github_repo . '/main/assets/icon.png' );

			$transient->response[ $this->plugin_slug ] = $obj;
		}

		return $transient;
	}

	/**
	 * Populate WP Plugin Details popup modal
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || $args->slug !== $this->plugin_slug ) {
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
			: $release->zipball_url;

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
		$install_directory = $result['destination'];

		if ( $install_directory !== $proper_folder ) {
			$wp_filesystem->move( $install_directory, $proper_folder );
			$result['destination'] = $proper_folder;
		}

		return $result;
	}

	/**
	 * Fetch latest release from GitHub API with transient caching
	 */
	private function get_latest_release() {
		$cache_key = 'upr_github_release_info';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

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

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );
		if ( empty( $body ) || ! is_object( $body ) ) {
			return false;
		}

		set_transient( $cache_key, $body, 6 * HOUR_IN_SECONDS );
		return $body;
	}
}
