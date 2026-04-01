<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$options = get_option( 'wppr_settings', array() );
?>
<div class="wrap wppr-wrap">
	<div class="wppr-header">
		<div class="wppr-logo">
			<img src="<?php echo esc_url( WPPR_PLUGIN_URL . 'admin/assets/images/rocket-logo.svg' ); ?>" alt="Rocket Logo">
			<h1>Performance Rocket</h1>
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
					<div style="margin-bottom: 30px; padding: 20px; background: #fff; border: 1px solid #e2e4e7; border-radius: 8px; display: flex; align-items: center; gap: 15px;">
						<button type="button" id="wppr-optimize-all" class="button button-primary button-hero" style="background: #f27d26; border-color: #f27d26; text-shadow: none;">Optimize Entire Website</button>
						<span class="spinner" id="wppr-optimize-spinner"></span>
						<p id="wppr-optimize-message" style="margin: 0; font-weight: 500;"></p>
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
