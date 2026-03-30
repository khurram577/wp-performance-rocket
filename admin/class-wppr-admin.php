<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once WPPR_PLUGIN_DIR . 'includes/class-wppr-analyzer.php';

class WPPR_Admin {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		
		$analyzer = new WPPR_Analyzer();
		add_action( 'wp_ajax_wppr_run_analysis', array( $analyzer, 'ajax_run_analysis' ) );
		add_action( 'wp_ajax_wppr_optimize_all', array( $this, 'ajax_optimize_all' ) );
	}

	public function ajax_optimize_all() {
		check_ajax_referer( 'wppr_admin_nonce', 'security' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		// Clear cache
		$caching = new WPPR_Caching();
		$caching->clear_all_cache();

		// Clean database
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->posts WHERE post_type = 'revision'" );
		$wpdb->query( "DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'" );

		wp_send_json_success( array( 'message' => __( 'Website fully optimized! Cache cleared and database cleaned.', 'wp-performance-rocket' ) ) );
	}

	public function add_admin_menu() {
		add_menu_page(
			'WP Performance Rocket',
			'Performance Rocket',
			'manage_options',
			'wppr-settings',
			array( $this, 'render_settings_page' ),
			'dashicons-rocket',
			80
		);
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_wppr-settings' !== $hook ) {
			return;
		}
		
		wp_enqueue_style( 'wppr-admin-style', WPPR_PLUGIN_URL . 'admin/assets/css/admin-style.css', array(), WPPR_VERSION );
		wp_enqueue_script( 'wppr-admin-script', WPPR_PLUGIN_URL . 'admin/assets/js/admin-script.js', array( 'jquery' ), WPPR_VERSION, true );
		
		wp_localize_script( 'wppr-admin-script', 'wppr_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'wppr_admin_nonce' ),
			'site_url' => get_site_url()
		) );
	}

	public function register_settings() {
		register_setting( 'wppr_settings_group', 'wppr_settings' );
	}

	public function render_settings_page() {
		require_once WPPR_PLUGIN_DIR . 'admin/views/settings-page.php';
	}
}

new WPPR_Admin();
