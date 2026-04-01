<?php
/**
 * Plugin Name:       Performance Rocket
 * Plugin URI:        https://github.com/khurram577/wp-performance-rocket
 * Description:       Simple and lightweight WordPress speed optimization plugin. Compresses images, minifies HTML, enables lazy loading, and defers JavaScript for faster loading times on both mobile and desktop.
 * Version:           1.6.0
 * Author:            Khurram Ali
 * Author URI:        https://github.com/Khurram577
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       performance-rocket
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Plugin Version
 */
define( 'WPPR_VERSION', '1.6.0' );
define( 'WPPR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPPR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Fix for Plugin Check: Sanitization callback required for register_setting()
 */
function wppr_register_settings() {
    register_setting(
        'wppr_settings',           // Option group
        'wppr_options',            // Option name
        'sanitize_text_field'      // Required sanitization callback
    );
}
add_action( 'admin_init', 'wppr_register_settings' );

// Include core files
require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-core.php';
require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-caching.php';
require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-optimization.php';
require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-database.php';

if ( is_admin() ) {
    require_once WPPR_PLUGIN_DIR . 'admin/class-wppr-admin.php';
}

/**
 * Initialize the plugin
 */
function wppr_init() {
    $core = new WPPR_Core();
    $core->init();
}
add_action( 'plugins_loaded', 'wppr_init' );

/**
 * Activation Hook
 */
register_activation_hook( __FILE__, 'wppr_activate' );
function wppr_activate() {
    // Setup cache directory and default rules
    if ( class_exists( 'WPPR_Caching' ) ) {
        $caching = new WPPR_Caching();
        $caching->setup_cache_dir();
        $caching->add_htaccess_rules();
    }
}

/**
 * Deactivation Hook
 */
register_deactivation_hook( __FILE__, 'wppr_deactivate' );
function wppr_deactivate() {
    if ( class_exists( 'WPPR_Caching' ) ) {
        $caching = new WPPR_Caching();
        $caching->remove_htaccess_rules();
        $caching->clear_all_cache();
    }
}