
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

	// Speed Test via PageSpeed Insights API
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

		// Validate if it's a localhost URL
		try {
			var urlObj = new URL(targetUrl);
			if (urlObj.hostname === 'localhost' || urlObj.hostname === '127.0.0.1' || urlObj.hostname.endsWith('.local')) {
				alert('Google PageSpeed Insights requires a publicly accessible URL. It cannot test localhost or local development environments. Please enter a live URL.');
				return;
			}
		} catch (err) {
			alert('Please enter a valid URL format (e.g., https://example.com).');
			return;
		}
		
		$spinner.addClass('is-active');
		$btn.prop('disabled', true);
		$results.hide();
		
		var apiUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' + encodeURIComponent(targetUrl);

		// Run Desktop Test
		$.get(apiUrl + '&strategy=desktop', function(data) {
			var score = Math.round(data.lighthouseResult.categories.performance.score * 100);
			updateScoreCircle('#score-desktop', score);
			
			// Run Mobile Test sequentially
			$.get(apiUrl + '&strategy=mobile', function(dataMobile) {
				var scoreMobile = Math.round(dataMobile.lighthouseResult.categories.performance.score * 100);
				updateScoreCircle('#score-mobile', scoreMobile);
				
				$spinner.removeClass('is-active');
				$btn.prop('disabled', false);
				$results.fadeIn();
			}).fail(function() {
				$('#score-mobile').text('Err');
				$spinner.removeClass('is-active');
				$btn.prop('disabled', false);
				$results.fadeIn();
			});

		}).fail(function(jqXHR) {
			var errorMsg = 'Failed to run speed test. Please ensure your site is publicly accessible and not behind a firewall or password protection.';
			if (jqXHR.responseJSON && jqXHR.responseJSON.error && jqXHR.responseJSON.error.message) {
				errorMsg += '\n\nDetails: ' + jqXHR.responseJSON.error.message;
			}
			alert(errorMsg);
			$spinner.removeClass('is-active');
			$btn.prop('disabled', false);
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
