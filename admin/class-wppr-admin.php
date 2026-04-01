<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WPPR_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        
        add_filter( 'plugin_action_links_performance-rocket/performance-rocket.php', array( $this, 'add_settings_link' ) );

        add_action( 'wp_ajax_wppr_optimize_all', array( $this, 'ajax_optimize_all' ) );
    }

    /**
     * Add Settings link on Plugins page
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=wppr-settings">' . __( 'Settings', 'performance-rocket' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * AJAX Handler - Optimize Everything
     */
    public function ajax_optimize_all() {
        check_ajax_referer( 'wppr_admin_nonce', 'security' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        // 1. Clear Page Cache
        if ( class_exists( 'WPPR_Caching' ) ) {
            $caching = new WPPR_Caching();
            $caching->clear_all_cache();
        }

        // 2. Clear Object Cache
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }

        // 3. Clear OPcache (if available)
        if ( function_exists( 'opcache_reset' ) ) {
            @opcache_reset();
        }

        // 4. Database Cleanup
        global $wpdb;

        // Revisions
        $wpdb->query( "DELETE FROM $wpdb->posts WHERE post_type = 'revision'" );
        // Auto Drafts
        $wpdb->query( "DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'" );
        // Trashed Posts
        $wpdb->query( "DELETE FROM $wpdb->posts WHERE post_status = 'trash'" );
        // Spam Comments
        $wpdb->query( "DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'" );
        // Trashed Comments
        $wpdb->query( "DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'" );
        // Expired Transients
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_%' AND option_value < " . time() );
        // All Transients
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'" );
        // Orphaned Postmeta
        $wpdb->query( "DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL" );
        // Orphaned Commentmeta
        $wpdb->query( "DELETE cm FROM $wpdb->commentmeta cm LEFT JOIN $wpdb->comments wc ON wc.comment_ID = cm.comment_id WHERE wc.comment_ID IS NULL" );

        // 5. Optimize Tables
        $tables = $wpdb->get_col( "SHOW TABLES" );
        foreach ( $tables as $table ) {
            $wpdb->query( "OPTIMIZE TABLE `$table`" );
        }

        // 6. Preload Homepage
        wp_remote_get( get_home_url(), array( 'timeout' => 5, 'sslverify' => false ) );

        wp_send_json_success( array(
            'message' => __( 'Website fully optimized! Cache cleared, database cleaned, and tables optimized.', 'performance-rocket' )
        ) );
    }

    /**
     * Add Admin Menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Performance Rocket',
            'Performance Rocket',
            'manage_options',
            'wppr-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-rocket',
            80
        );
    }

    /**
     * Enqueue Admin Assets
     */
    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_wppr-settings' !== $hook ) {
            return;
        }
        
        wp_enqueue_style( 'wppr-admin-style', 
            WPPR_PLUGIN_URL . 'admin/assets/css/admin-style.css', 
            array(), 
            WPPR_VERSION 
        );

        wp_enqueue_script( 'wppr-admin-script', 
            WPPR_PLUGIN_URL . 'admin/assets/js/admin-script.js', 
            array( 'jquery' ), 
            WPPR_VERSION, 
            true 
        );
        
        wp_localize_script( 'wppr-admin-script', 'wppr_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wppr_admin_nonce' ),
            'site_url' => get_site_url()
        ) );
    }

    /**
     * Register Settings with Proper Sanitization (Fixes Plugin Check Error)
     */
    public function register_settings() {
        register_setting(
            'wppr_settings_group',     // Option group
            'wppr_settings',           // Option name
            array( $this, 'sanitize_settings' )   // Sanitization callback
        );
    }

    /**
     * Sanitization Callback (Required by Plugin Check)
     */
    public function sanitize_settings( $input ) {
        if ( ! is_array( $input ) ) {
            return array();
        }

        $sanitized = array();

        // Sanitize any settings you may add in future
        foreach ( $input as $key => $value ) {
            $sanitized[ sanitize_key( $key ) ] = sanitize_text_field( $value );
        }

        return $sanitized;
    }

    /**
     * Render Settings Page
     */
    public function render_settings_page() {
        require_once WPPR_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
}

// Initialize Admin Class
new WPPR_Admin();