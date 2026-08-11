(function($) {
	'use strict';

	$(document).ready(function() {

		// 1. Live Search Bar
		$('.upr-live-search-input').on('keyup input', function() {
			var query = $(this).val().toLowerCase().trim();
			var $container = $(this).closest('.upr-container');
			var $wrappers = $container.find('.upr-item-wrapper');

			if (query === '') {
				$wrappers.removeClass('upr-search-hidden').show();
			} else {
				$wrappers.each(function() {
					var text = $(this).data('search') || '';
					if (text.indexOf(query) !== -1) {
						$(this).removeClass('upr-search-hidden').fadeIn(200);
					} else {
						$(this).addClass('upr-search-hidden').hide();
					}
				});
			}
		});

		// 2. Category Filter Tabs
		$('.upr-filter-btn').on('click', function(e) {
			e.preventDefault();
			var filter = $(this).data('filter');
			var $container = $(this).closest('.upr-container');
			var $btnGroup = $(this).closest('.upr-filter-tabs');
			var $wrappers = $container.find('.upr-item-wrapper');

			$btnGroup.find('.upr-filter-btn').removeClass('active');
			$(this).addClass('active');

			if (filter === 'all') {
				$wrappers.removeClass('upr-filter-hidden').fadeIn(250);
			} else {
				$wrappers.each(function() {
					var cat = $(this).data('category');
					if (cat === filter) {
						$(this).removeClass('upr-filter-hidden').fadeIn(250);
					} else {
						$(this).addClass('upr-filter-hidden').hide();
					}
				});
			}
		});

		// 3. Load More Button Pagination
		$('.upr-load-more-btn').on('click', function(e) {
			e.preventDefault();
			var $container = $(this).closest('.upr-container');
			var perPage = parseInt($container.data('per-page'), 10) || 6;
			var $hiddenItems = $container.find('.upr-item-wrapper.upr-page-hidden');

			if ($hiddenItems.length > 0) {
				$hiddenItems.slice(0, perPage).removeClass('upr-page-hidden').hide().fadeIn(300);
			}

			// Hide button if no more hidden items
			if ($container.find('.upr-item-wrapper.upr-page-hidden').length === 0) {
				$(this).parent().fadeOut(200);
			}
		});

		// 4. Numeric Pagination
		$('.upr-page-num').on('click', function(e) {
			e.preventDefault();
			var pageNum = parseInt($(this).data('page'), 10) || 1;
			var $container = $(this).closest('.upr-container');
			var perPage = parseInt($container.data('per-page'), 10) || 6;
			var $wrappers = $container.find('.upr-item-wrapper');

			$(this).siblings().removeClass('active');
			$(this).addClass('active');

			var startIndex = (pageNum - 1) * perPage;
			var endIndex = startIndex + perPage;

			$wrappers.each(function(index) {
				if (index >= startIndex && index < endIndex) {
					$(this).removeClass('upr-page-hidden').fadeIn(250);
				} else {
					$(this).addClass('upr-page-hidden').hide();
				}
			});
		});

	});

})(jQuery);
