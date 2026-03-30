
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
});
