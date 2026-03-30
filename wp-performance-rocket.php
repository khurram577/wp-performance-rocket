<?php
/**
 * Plugin Name: WP Performance Rocket
 * Plugin URI: https://github.com/Khurram577
 * Description: Advanced performance optimization plugin focusing on caching, file optimization, image lazy loading, and database cleanup.
 * Version: 1.0.1
 * Author: Khurram Ali
 * Author URI: https://github.com/Khurram577
 * Text Domain: wp-performance-rocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'WPPR_VERSION', '1.0.1' );
define( 'WPPR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPPR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include core files
require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-core.php';
require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-caching.php';
require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-optimization.php';
require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-database.php';

if ( is_admin() ) {
	require_once WPPR_PLUGIN_DIR . 'admin/class-wppr-admin.php';
}

// Initialize the plugin
function wppr_init() {
	$core = new WPPR_Core();
	$core->init();
}
add_action( 'plugins_loaded', 'wppr_init' );

// Activation Hook
register_activation_hook( __FILE__, 'wppr_activate' );
function wppr_activate() {
	// Setup cache directories, add default options, etc.
	$caching = new WPPR_Caching();
	$caching->setup_cache_dir();
	$caching->add_htaccess_rules();
}

// Deactivation Hook
register_deactivation_hook( __FILE__, 'wppr_deactivate' );
function wppr_deactivate() {
	$caching = new WPPR_Caching();
	$caching->remove_htaccess_rules();
	$caching->clear_all_cache();
}
