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
