
jQuery(document).ready(function($) {
	// Tab Switching
	$('.wppr-tabs li').on('click', function() {
		var tab = $(this).data('tab');
		$('.wppr-tabs li').removeClass('active');
		$(this).addClass('active');
		$('.wppr-tab-content').removeClass('active');
		$('#tab-' + tab).addClass('active');
	});

	// Database Cleanup
	$('#wppr-clean-db').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $spinner = $('#wppr-db-spinner');
		var $msg = $('#wppr-db-message');

		$spinner.addClass('is-active');
		$btn.prop('disabled', true);
		$msg.text('');

		$.ajax({
			url: wppr_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'wppr_clean_database',
				security: wppr_ajax.nonce
			},
			success: function(response) {
				$spinner.removeClass('is-active');
				$btn.prop('disabled', false);
				if (response.success) {
					$msg.text(response.data.message).css('color', '#0cce6b');
				} else {
					$msg.text('Error cleaning database.').css('color', '#ff4e42');
				}
			},
			error: function() {
				$spinner.removeClass('is-active');
				$btn.prop('disabled', false);
				$msg.text('Request failed.').css('color', '#ff4e42');
			}
		});
	});

	// Optimize Entire Website
	$('#wppr-optimize-all').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $spinner = $('#wppr-optimize-spinner');
		var $msg = $('#wppr-optimize-message');

		$spinner.addClass('is-active');
		$btn.prop('disabled', true);
		$msg.text('Optimizing...').css('color', '#646970');

		$.ajax({
			url: wppr_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'wppr_optimize_all',
				security: wppr_ajax.nonce
			},
			success: function(response) {
				$spinner.removeClass('is-active');
				$btn.prop('disabled', false);
				if (response.success) {
					$msg.text(response.data.message).css('color', '#0cce6b');
				} else {
					$msg.text('Optimization failed.').css('color', '#ff4e42');
				}
			},
			error: function() {
				$spinner.removeClass('is-active');
				$btn.prop('disabled', false);
				$msg.text('Request failed.').css('color', '#ff4e42');
			}
		});
	});

	// Custom Performance Analyzer
	$('#wppr-run-test').on('click', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $spinner = $('#wppr-test-spinner');
		var $results = $('#wppr-results');
		
		var targetUrl = $('#wppr-test-url').val().trim();
		
		if (!targetUrl) {
			alert('Please enter a valid URL to test.');
			return;
		}

		$spinner.addClass('is-active');
		$btn.prop('disabled', true);
		$results.hide();
		
		$.ajax({
			url: wppr_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'wppr_run_analysis',
				security: wppr_ajax.nonce,
				url: targetUrl
			},
			success: function(response) {
				$spinner.removeClass('is-active');
				$btn.prop('disabled', false);
				
				if (response.success) {
					var data = response.data;
					
					// Update UI
					updateScoreCircle('#score-overall', data.score);
					
					$('#metric-ttfb').text(data.ttfb + ' ms').css('color', data.ttfb < 300 ? '#0cce6b' : (data.ttfb < 600 ? '#ffa400' : '#ff4e42'));
					$('#metric-size').text(data.page_size_kb + ' KB').css('color', data.page_size_kb < 1000 ? '#0cce6b' : (data.page_size_kb < 2000 ? '#ffa400' : '#ff4e42'));
					$('#metric-requests').text(data.total_requests).css('color', data.total_requests < 40 ? '#0cce6b' : (data.total_requests < 80 ? '#ffa400' : '#ff4e42'));
					$('#metric-dom').text(data.dom_elements).css('color', data.dom_elements < 800 ? '#0cce6b' : (data.dom_elements < 1500 ? '#ffa400' : '#ff4e42'));
					
					$('#metric-compression').text(data.is_compressed ? 'Active' : 'Missing').css('color', data.is_compressed ? '#0cce6b' : '#ff4e42');
					$('#metric-cache').text(data.has_caching ? 'Active' : 'Missing').css('color', data.has_caching ? '#0cce6b' : '#ff4e42');

					$results.fadeIn();
				} else {
					alert('Analysis failed: ' + response.data);
				}
			},
			error: function() {
				alert('Failed to connect to the server for analysis.');
				$spinner.removeClass('is-active');
				$btn.prop('disabled', false);
			}
		});
	});

	function updateScoreCircle(selector, score) {
		var $circle = $(selector);
		$circle.text(score);
		$circle.removeClass('good average poor');
		if (score >= 90) {
			$circle.addClass('good');
		} else if (score >= 50) {
			$circle.addClass('average');
		} else {
			$circle.addClass('poor');
		}
	}
});
