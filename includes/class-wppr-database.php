<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPR_Database {
	
	public function ajax_clean_database() {
		check_ajax_referer( 'wppr_admin_nonce', 'security' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		global $wpdb;
		$cleaned = 0;

		// Clean Revisions
		$revisions = $wpdb->query( "DELETE FROM $wpdb->posts WHERE post_type = 'revision'" );
		if ( $revisions ) $cleaned += $revisions;

		// Clean Auto Drafts
		$drafts = $wpdb->query( "DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'" );
		if ( $drafts ) $cleaned += $drafts;

		// Clean Transients
		$transients = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'" );
		if ( $transients ) $cleaned += $transients;

		wp_send_json_success( array( 'message' => sprintf( __( 'Database optimized! %d items cleaned.', 'wp-performance-rocket' ), $cleaned ) ) );
	}
}
