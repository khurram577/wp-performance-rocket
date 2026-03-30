<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPR_Admin {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
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
