<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$options = get_option( 'wppr_settings', array() );
?>
<div class="wrap wppr-wrap">
	<div class="wppr-header">
		<div class="wppr-logo">
			<img src="<?php echo esc_url( WPPR_PLUGIN_URL . 'admin/assets/images/rocket-logo.svg' ); ?>" alt="Rocket Logo">
			<h1>WP Performance Rocket</h1>
		</div>
		<p class="wppr-author">By <a href="https://github.com/Khurram577" target="_blank">Khurram Ali</a></p>
	</div>

	<div class="wppr-container">
		<div class="wppr-sidebar">
			<ul class="wppr-tabs">
				<li class="active" data-tab="dashboard">Dashboard & Speed Test</li>
				<li data-tab="caching">Advanced Caching</li>
				<li data-tab="file-opt">File Optimization</li>
				<li data-tab="image-opt">Image Optimization</li>
				<li data-tab="database">Database</li>
			</ul>
		</div>

		<div class="wppr-content">
			<form method="post" action="options.php">
				<?php settings_fields( 'wppr_settings_group' ); ?>

				<!-- Dashboard Tab -->
				<div id="tab-dashboard" class="wppr-tab-content active">
					<h2>Custom Performance Analyzer</h2>
					<p>Analyze your website's real-time performance metrics directly from your server. No external APIs required.</p>
					
					<div class="wppr-speed-test-controls" style="display: flex; gap: 10px; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
						<input type="url" id="wppr-test-url" value="<?php echo esc_url( get_site_url() ); ?>" class="regular-text" style="padding: 5px 10px; font-size: 14px; min-width: 300px;" placeholder="https://example.com">
						<button type="button" id="wppr-run-test" class="button button-primary button-hero">Analyze Performance</button>
						<span class="spinner" id="wppr-test-spinner"></span>
					</div>

					<div style="margin-bottom: 30px; padding: 20px; background: #fff; border: 1px solid #e2e4e7; border-radius: 8px; display: flex; align-items: center; gap: 15px;">
						<button type="button" id="wppr-optimize-all" class="button button-primary button-hero" style="background: #f27d26; border-color: #f27d26; text-shadow: none;">Optimize Entire Website</button>
						<span class="spinner" id="wppr-optimize-spinner"></span>
						<p id="wppr-optimize-message" style="margin: 0; font-weight: 500;"></p>
					</div>

					<div class="wppr-results" id="wppr-results" style="display: none;">
						<div class="wppr-main-score">
							<h3>Overall Performance Score</h3>
							<div class="wppr-score-circle" id="score-overall">--</div>
						</div>
						
						<div class="wppr-metrics-grid">
							<div class="wppr-metric-box">
								<h4>Server Response (TTFB)</h4>
								<span id="metric-ttfb" class="wppr-metric-value">--</span>
								<p>Time to first byte</p>
							</div>
							<div class="wppr-metric-box">
								<h4>Page Size</h4>
								<span id="metric-size" class="wppr-metric-value">--</span>
								<p>Total HTML size</p>
							</div>
							<div class="wppr-metric-box">
								<h4>Requests</h4>
								<span id="metric-requests" class="wppr-metric-value">--</span>
								<p>Scripts, styles, images</p>
							</div>
							<div class="wppr-metric-box">
								<h4>DOM Elements</h4>
								<span id="metric-dom" class="wppr-metric-value">--</span>
								<p>HTML complexity</p>
							</div>
							<div class="wppr-metric-box">
								<h4>Compression</h4>
								<span id="metric-compression" class="wppr-metric-value">--</span>
								<p>GZIP or Brotli</p>
							</div>
							<div class="wppr-metric-box">
								<h4>Cache Headers</h4>
								<span id="metric-cache" class="wppr-metric-value">--</span>
								<p>Browser caching</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Caching Tab -->
				<div id="tab-caching" class="wppr-tab-content">
					<h2>Advanced Caching</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Enable Page Caching</th>
							<td>
								<label class="wppr-switch">
									<input type="checkbox" name="wppr_settings[enable_page_caching]" value="1" <?php checked( 1, isset( $options['enable_page_caching'] ) ? $options['enable_page_caching'] : 0 ); ?> />
									<span class="wppr-slider round"></span>
								</label>
								<p class="description">Creates static HTML files of your pages to serve them faster.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Browser Caching & GZIP</th>
							<td>
								<p class="description">Automatically enabled via .htaccess upon plugin activation.</p>
							</td>
						</tr>
					</table>
				</div>

				<!-- File Optimization Tab -->
				<div id="tab-file-opt" class="wppr-tab-content">
					<h2>File Optimization</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Minify HTML</th>
							<td>
								<label class="wppr-switch">
									<input type="checkbox" name="wppr_settings[minify_html]" value="1" <?php checked( 1, isset( $options['minify_html'] ) ? $options['minify_html'] : 0 ); ?> />
									<span class="wppr-slider round"></span>
								</label>
								<p class="description">Removes whitespace and comments to reduce page size.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Resource Hints</th>
							<td>
								<label class="wppr-switch">
									<input type="checkbox" name="wppr_settings[resource_hints]" value="1" <?php checked( 1, isset( $options['resource_hints'] ) ? $options['resource_hints'] : 0 ); ?> />
									<span class="wppr-slider round"></span>
								</label>
								<p class="description">Adds preconnect and prefetch hints for Google Fonts and other external assets.</p>
							</td>
						</tr>
					</table>
				</div>

				<!-- Image Optimization Tab -->
				<div id="tab-image-opt" class="wppr-tab-content">
					<h2>Image Optimization</h2>
					<table class="form-table">
						<tr>
							<th scope="row">Lazy Load Images</th>
							<td>
								<label class="wppr-switch">
									<input type="checkbox" name="wppr_settings[lazy_load_images]" value="1" <?php checked( 1, isset( $options['lazy_load_images'] ) ? $options['lazy_load_images'] : 0 ); ?> />
									<span class="wppr-slider round"></span>
								</label>
								<p class="description">Defers loading of offscreen images to improve page load time.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">WebP Support</th>
							<td>
								<p class="description">WordPress natively supports WebP. Ensure you upload WebP images for best performance.</p>
							</td>
						</tr>
					</table>
				</div>

				<!-- Database Tab -->
				<div id="tab-database" class="wppr-tab-content">
					<h2>Database Optimization</h2>
					<p>Clean up your database to reduce its size and improve query performance.</p>
					
					<div class="wppr-db-actions">
						<button type="button" id="wppr-clean-db" class="button button-secondary">Clean Revisions & Transients</button>
						<span class="spinner" id="wppr-db-spinner"></span>
						<p id="wppr-db-message"></p>
					</div>
				</div>

				<div class="wppr-footer-actions">
					<?php submit_button( 'Save Changes' ); ?>
				</div>
			</form>
		</div>
	</div>
</div>
