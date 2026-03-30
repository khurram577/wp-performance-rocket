<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPPR_Analyzer {
	public function ajax_run_analysis() {
		check_ajax_referer( 'wppr_admin_nonce', 'security' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '';
		
		if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			wp_send_json_error( 'Invalid URL provided.' );
		}

		$results = $this->analyze_url( $url );
		
		if ( is_wp_error( $results ) ) {
			wp_send_json_error( $results->get_error_message() );
		}

		wp_send_json_success( $results );
	}

	private function analyze_url( $url ) {
		$ttfb = 0;
		$page_size = 0;
		$body = '';
		$headers = array();
		
		if ( function_exists( 'curl_init' ) ) {
			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HEADER, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 15 );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_USERAGENT, 'WP Performance Rocket Analyzer/1.0' );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			
			$response = curl_exec( $ch );
			
			if ( ! curl_errno( $ch ) ) {
				$info = curl_getinfo( $ch );
				$ttfb = round( $info['starttransfer_time'] * 1000 );
				$header_size = $info['header_size'];
				$header_text = substr( $response, 0, $header_size );
				$body = substr( $response, $header_size );
				$page_size = strlen( $body );
				
				foreach ( explode( "\r\n", $header_text ) as $i => $line ) {
					if ( $i === 0 || empty( $line ) ) continue;
					$parts = explode( ': ', $line, 2 );
					if ( count( $parts ) === 2 ) {
						$headers[ strtolower( $parts[0] ) ] = $parts[1];
					}
				}
			} else {
				return new WP_Error( 'curl_error', curl_error( $ch ) );
			}
			curl_close( $ch );
		} else {
			$start_time = microtime( true );
			$response = wp_remote_get( $url, array( 'timeout' => 15, 'sslverify' => false ) );
			$end_time = microtime( true );
			
			if ( is_wp_error( $response ) ) return $response;
			
			$ttfb = round( ( $end_time - $start_time ) * 1000 );
			$body = wp_remote_retrieve_body( $response );
			$page_size = strlen( $body );
			$headers = wp_remote_retrieve_headers( $response );
		}

		$content_encoding = isset( $headers['content-encoding'] ) ? (is_array($headers['content-encoding']) ? implode(',', $headers['content-encoding']) : $headers['content-encoding']) : '';
		$is_compressed = ( strpos( $content_encoding, 'gzip' ) !== false || strpos( $content_encoding, 'br' ) !== false || strpos( $content_encoding, 'deflate' ) !== false );

		$cache_control = isset( $headers['cache-control'] ) ? (is_array($headers['cache-control']) ? implode(',', $headers['cache-control']) : $headers['cache-control']) : '';
		$has_caching = ( strpos( $cache_control, 'max-age' ) !== false || strpos( $cache_control, 'public' ) !== false );

		$dom_elements = 0;
		$scripts = 0;
		$styles = 0;
		$images = 0;

		if ( ! empty( $body ) ) {
			libxml_use_internal_errors( true );
			$dom = new DOMDocument();
			@$dom->loadHTML( mb_convert_encoding( $body, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_NOWARNING | LIBXML_NOERROR );
			
			$dom_elements = $dom->getElementsByTagName( '*' )->length;
			$scripts = $dom->getElementsByTagName( 'script' )->length;
			$styles = $dom->getElementsByTagName( 'link' )->length;
			$images = $dom->getElementsByTagName( 'img' )->length;
			
			libxml_clear_errors();
		}

		$total_requests = $scripts + $styles + $images + 1;

		$score = 100;

		if ( $ttfb > 200 ) $score -= min( 30, ( $ttfb - 200 ) / 20 );
		$size_kb = $page_size / 1024;
		if ( $size_kb > 1000 ) $score -= min( 20, ( $size_kb - 1000 ) / 100 );
		if ( $total_requests > 40 ) $score -= min( 20, ( $total_requests - 40 ) * 0.5 );
		if ( $dom_elements > 800 ) $score -= min( 15, ( $dom_elements - 800 ) / 50 );
		if ( ! $is_compressed ) $score -= 10;
		if ( ! $has_caching ) $score -= 5;

		$score = max( 0, min( 100, round( $score ) ) );

		return array(
			'score'          => $score,
			'ttfb'           => $ttfb,
			'page_size_kb'   => round( $size_kb, 1 ),
			'dom_elements'   => $dom_elements,
			'total_requests' => $total_requests,
			'is_compressed'  => $is_compressed,
			'has_caching'    => $has_caching,
		);
	}
}
