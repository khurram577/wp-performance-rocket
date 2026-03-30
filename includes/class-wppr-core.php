<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPR_Core {
	public function init() {
		$options = get_option( 'wppr_settings', array() );

		// Initialize Caching
		if ( ! empty( $options['enable_page_caching'] ) ) {
			$caching = new WPPR_Caching();
			$caching->init();
		}

		// Initialize Optimization
		$optimization = new WPPR_Optimization( $options );
		$optimization->init();

		// Initialize Database (Only loaded when needed via admin or cron)
		if ( is_admin() ) {
			$database = new WPPR_Database();
			add_action( 'wp_ajax_wppr_clean_database', array( $database, 'ajax_clean_database' ) );
		}
	}
}
