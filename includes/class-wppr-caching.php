<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPR_Caching {
	private $cache_dir;

	public function __construct() {
		$upload_dir = wp_upload_dir();
		$this->cache_dir = trailingslashit( $upload_dir['basedir'] ) . 'wppr-cache/';
	}

	public function init() {
		add_action( 'template_redirect', array( $this, 'start_buffer' ), 0 );
		add_action( 'save_post', array( $this, 'clear_all_cache' ) );
		add_action( 'comment_post', array( $this, 'clear_all_cache' ) );
	}

	public function setup_cache_dir() {
		if ( ! file_exists( $this->cache_dir ) ) {
			wp_mkdir_p( $this->cache_dir );
		}
	}

	public function start_buffer() {
		if ( $this->should_cache() ) {
			$cache_file = $this->get_cache_file_path();
			if ( file_exists( $cache_file ) && ( time() - filemtime( $cache_file ) < 3600 * 24 ) ) {
				readfile( $cache_file );
				echo "<!-- Cached by WP Performance Rocket -->";
				exit;
			}
			ob_start( array( $this, 'end_buffer' ) );
		}
	}

	public function end_buffer( $buffer ) {
		if ( strlen( $buffer ) > 255 ) {
			$this->setup_cache_dir();
			file_put_contents( $this->get_cache_file_path(), $buffer );
		}
		return $buffer;
	}

	private function should_cache() {
		if ( is_user_logged_in() || is_admin() || is_search() || $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
			return false;
		}
		return true;
	}

	private function get_cache_file_path() {
		$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		return $this->cache_dir . md5( $url ) . '.html';
	}

	public function clear_all_cache() {
		if ( file_exists( $this->cache_dir ) ) {
			$files = glob( $this->cache_dir . '*' );
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file );
				}
			}
		}
	}

	public function add_htaccess_rules() {
		$rules = "
# BEGIN WP Performance Rocket
<IfModule mod_expires.c>
	ExpiresActive On
	ExpiresByType image/jpg \"access plus 1 year\"
	ExpiresByType image/jpeg \"access plus 1 year\"
	ExpiresByType image/gif \"access plus 1 year\"
	ExpiresByType image/png \"access plus 1 year\"
	ExpiresByType text/css \"access plus 1 month\"
	ExpiresByType application/pdf \"access plus 1 month\"
	ExpiresByType text/x-javascript \"access plus 1 month\"
	ExpiresByType application/x-shockwave-flash \"access plus 1 month\"
	ExpiresByType image/x-icon \"access plus 1 year\"
	ExpiresDefault \"access plus 2 days\"
</IfModule>
<IfModule mod_deflate.c>
	AddOutputFilterByType DEFLATE text/plain
	AddOutputFilterByType DEFLATE text/html
	AddOutputFilterByType DEFLATE text/xml
	AddOutputFilterByType DEFLATE text/css
	AddOutputFilterByType DEFLATE application/xml
	AddOutputFilterByType DEFLATE application/xhtml+xml
	AddOutputFilterByType DEFLATE application/rss+xml
	AddOutputFilterByType DEFLATE application/javascript
	AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
# END WP Performance Rocket
";
		require_once( ABSPATH . 'wp-admin/includes/misc.php' );
		insert_with_markers( get_home_path() . '.htaccess', 'WP Performance Rocket', explode( "\n", $rules ) );
	}

	public function remove_htaccess_rules() {
		require_once( ABSPATH . 'wp-admin/includes/misc.php' );
		insert_with_markers( get_home_path() . '.htaccess', 'WP Performance Rocket', array() );
	}
}
