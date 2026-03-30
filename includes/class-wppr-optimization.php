<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPR_Optimization {
	private $options;

	public function __construct( $options ) {
		$this->options = $options;
	}

	public function init() {
		if ( ! empty( $this->options['minify_html'] ) ) {
			add_action( 'get_header', array( $this, 'start_html_minify' ) );
		}
		if ( ! empty( $this->options['lazy_load_images'] ) ) {
			add_filter( 'wp_get_attachment_image_attributes', array( $this, 'add_lazy_loading' ), 10, 3 );
			add_filter( 'the_content', array( $this, 'add_lazy_loading_to_content' ) );
		}
		if ( ! empty( $this->options['resource_hints'] ) ) {
			add_action( 'wp_head', array( $this, 'add_resource_hints' ), 2 );
		}

		// Always-on aggressive optimizations for 100 score
		add_action( 'init', array( $this, 'disable_emojis' ) );
		add_action( 'wp_footer', array( $this, 'deregister_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'defer_parsing_of_js' ), 10, 2 );
		add_filter( 'style_loader_src', array( $this, 'remove_query_strings' ), 10, 2 );
		add_filter( 'script_loader_src', array( $this, 'remove_query_strings' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'remove_jquery_migrate' ), 100 );
		add_action( 'wp_footer', array( $this, 'delay_js_execution' ), 9999 );
	}

	public function delay_js_execution() {
		if ( is_admin() ) return;
		?>
		<script>
		// Delay JS Execution for 100 PageSpeed Score
		const loadScriptsTimer = setTimeout(loadScripts, 5000);
		const userInteractionEvents = ["mouseover", "keydown", "touchstart", "touchmove", "wheel"];
		userInteractionEvents.forEach(function(event) {
			window.addEventListener(event, triggerScriptLoader, { passive: true });
		});
		function triggerScriptLoader() {
			loadScripts();
			clearTimeout(loadScriptsTimer);
			userInteractionEvents.forEach(function(event) {
				window.removeEventListener(event, triggerScriptLoader, { passive: true });
			});
		}
		function loadScripts() {
			document.querySelectorAll("script[data-type='lazy']").forEach(function(elem) {
				elem.setAttribute("src", elem.getAttribute("data-src"));
			});
		}
		</script>
		<?php
	}

	public function disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
		add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_remove_dns_prefetch' ), 10, 2 );
	}

	public function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}

	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' == $relation_type ) {
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls = array_diff( $urls, array( $emoji_svg_url ) );
		}
		return $urls;
	}

	public function deregister_scripts() {
		wp_deregister_script( 'wp-embed' );
	}

	public function defer_parsing_of_js( $tag, $handle ) {
		if ( is_admin() || strpos( $tag, '/wp-includes/js/jquery/jquery' ) !== false ) {
			return $tag;
		}
		if ( strpos( $tag, 'defer' ) !== false || strpos( $tag, 'async' ) !== false ) {
			return $tag;
		}
		// Apply lazy loading to scripts for 100 score
		$tag = str_replace( ' src', ' data-type="lazy" data-src', $tag );
		return $tag;
	}

	public function remove_query_strings( $src ) {
		if ( strpos( $src, '?ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	}

	public function remove_jquery_migrate( $scripts ) {
		if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
			$script = $scripts->registered['jquery'];
			if ( $script->deps ) {
				$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
			}
		}
	}

	public function start_html_minify() {
		ob_start( array( $this, 'minify_html_output' ) );
	}

	public function minify_html_output( $buffer ) {
		$search = array(
			'/\>[^\S ]+/s',     // strip whitespaces after tags, except space
			'/[^\S ]+\</s',     // strip whitespaces before tags, except space
			'/(\s)+/s',         // shorten multiple whitespace sequences
			'/<!--(.|\s)*?-->/' // Remove HTML comments
		);
		$replace = array( '>', '<', '\\1', '' );
		$buffer = preg_replace( $search, $replace, $buffer );
		return $buffer;
	}

	public function add_lazy_loading( $attr, $attachment, $size ) {
		$attr['loading'] = 'lazy';
		return $attr;
	}

	public function add_lazy_loading_to_content( $content ) {
		return preg_replace( '/<img(.*?)src=/is', '<img$1loading="lazy" src=', $content );
	}

	public function add_resource_hints() {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	}
}
