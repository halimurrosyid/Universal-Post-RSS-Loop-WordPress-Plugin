(function($) {
	'use strict';

	$(document).ready(function() {
		$('#upr_btn_test').on('click', function(e) {
			e.preventDefault();

			var feedUrl = $('#upr_test_url').val().trim();
			if (!feedUrl) {
				alert('Please enter an RSS Feed URL.');
				return;
			}

			$('#upr_tester_loading').show();
			$('#upr_tester_results').hide().empty();

			$.ajax({
				url: uprAdmin.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'upr_test_rss_feed',
					nonce: uprAdmin.nonce,
					feed_url: feedUrl
				},
				success: function(response) {
					$('#upr_tester_loading').hide();

					if (response.success) {
						var d = response.data;
						var html = '';

						html += '<div class="upr-result-grid">';
						
						// HTTP Status
						html += '<div class="upr-stat-card">';
						html += '  <div class="upr-stat-label">HTTP Status</div>';
						html += '  <div class="upr-stat-val upr-badge-success">' + d.http_status + ' OK</div>';
						html += '</div>';

						// Feed Type
						html += '<div class="upr-stat-card">';
						html += '  <div class="upr-stat-label">Feed Format</div>';
						html += '  <div class="upr-stat-val upr-badge-info">' + d.feed_type + '</div>';
						html += '</div>';

						// Items Count
						html += '<div class="upr-stat-card">';
						html += '  <div class="upr-stat-label">Items Found</div>';
						html += '  <div class="upr-stat-val">' + d.item_count + '</div>';
						html += '</div>';

						// Image Status
						html += '<div class="upr-stat-card">';
						html += '  <div class="upr-stat-label">Image Status</div>';
						html += '  <div class="upr-stat-val ' + (d.has_image ? 'upr-badge-success' : '') + '">' + (d.has_image ? 'Found' : 'No Image') + '</div>';
						html += '</div>';

						html += '</div>'; // end grid

						// First item summary
						html += '<h3>First Item Parsed:</h3>';
						html += '<p><strong>Title:</strong> ' + escapeHtml(d.first_title) + '</p>';
						html += '<p><strong>Original URL:</strong> <a href="' + escapeHtml(d.first_url) + '" target="_blank" rel="noopener">' + escapeHtml(d.first_url) + '</a></p>';

						// Preview box
						html += '<div class="upr-preview-section">';
						html += '  <h4>Live Renderer Card Preview:</h4>';
						html += '  <div class="upr-loop upr-layout-grid upr-cols-1">' + d.preview_html + '</div>';
						html += '</div>';

						$('#upr_tester_results').html(html).slideDown();
					} else {
						var err = response.data;
						var errHtml = '<div class="upr-error-box">';
						errHtml += '<h4>Feed Test Failed (Error Code: ' + escapeHtml(err.status_code || 'ERROR') + ')</h4>';
						errHtml += '<p>' + escapeHtml(err.message || 'Unknown error occurred while fetching RSS feed.') + '</p>';
						errHtml += '</div>';

						$('#upr_tester_results').html(errHtml).slideDown();
					}
				},
				error: function() {
					$('#upr_tester_loading').hide();
					$('#upr_tester_results').html('<div class="upr-error-box"><h4>Server Connection Error</h4><p>Could not contact WordPress AJAX endpoint.</p></div>').slideDown();
				}
			});
		});
	});

	function escapeHtml(text) {
		if (!text) return '';
		return text
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

})(jQuery);
