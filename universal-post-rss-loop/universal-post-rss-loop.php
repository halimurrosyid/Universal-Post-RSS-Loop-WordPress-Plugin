<?php
/**
 * Plugin Name:       Universal Post & RSS Loop
 * Plugin URI:        https://github.com/halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin
 * Description:       Unified post grid/list display for WordPress Posts and External RSS Feeds using the exact same card design.
 * Version:           2.0.2
 * Author:            Mujaddid Halimurrosyid
 * Author URI:        https://ajidmujaddid.staff.telkomuniversity.ac.id/
 * Text Domain:       universal-post-rss-loop
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * GitHub Plugin URI: https://github.com/halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin
 * Primary Branch:    main
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'UPR_VERSION', '2.0.2' );
define( 'UPR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UPR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'UPR_PLUGIN_FILE', __FILE__ );

// Include GitHub Auto-Updater
require_once UPR_PLUGIN_DIR . 'includes/class-upr-github-updater.php';
if ( is_admin() ) {
	new UPR_GitHub_Updater( UPR_PLUGIN_FILE, UPR_VERSION );
}

// Require Core Architecture Files.
require_once UPR_PLUGIN_DIR . 'includes/class-upr-item.php';
require_once UPR_PLUGIN_DIR . 'includes/abstract-upr-provider.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-post-provider.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-rss-parser.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-rss-provider.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-cache.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-template.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-renderer.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-shortcode.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-gutenberg.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-wpbakery.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-admin.php';
require_once UPR_PLUGIN_DIR . 'includes/class-upr-plugin.php';

/**
 * Initialize Plugin Singleton
 */
function upr_init() {
	return UPR_Plugin::get_instance();
}
add_action( 'plugins_loaded', 'upr_init' );
