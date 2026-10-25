/* global jQuery, wp */
(function ($) {
	'use strict';

	$(function () {
		if ($.fn.wpColorPicker) {
			$('.hojreh-color').wpColorPicker();
		}

		var frame = null;

		$(document).on('click', '[data-media-select]', function (event) {
			event.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: 'انتخاب تصویر هیرو',
				library: { type: 'image' },
				button: { text: 'استفاده از این تصویر' },
				multiple: false,
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				var field = $('#hojreh_hero_image');
				var preview = field.closest('.hojreh-media').find('[data-preview]');
				var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

				field.val(attachment.id);
				preview.empty().append($('<img alt="">').attr('src', url));
			});

			frame.open();
		});

		$(document).on('click', '[data-media-remove]', function (event) {
			event.preventDefault();
			$('#hojreh_hero_image').val('0');
			$('[data-preview]').empty();
		});
	});
})(jQuery);
