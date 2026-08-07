/**
 * Lumina Instagram Feed — Admin scripts.
 */
(function ($) {
	'use strict';

	function collectDesignSettings() {
		var settings = {};

		$('#lumina-ig-design-form .lumina-ig-design-input').each(function () {
			var $el = $(this);
			var name = $el.attr('name');

			if (!name) return;

			var match = name.match(/\[([^\]]+)\]$/);
			if (!match) return;

			var key = match[1];

			if ($el.attr('type') === 'checkbox') {
				settings[key] = $el.is(':checked') ? '1' : '0';
			} else {
				settings[key] = $el.val();
			}
		});

		return settings;
	}

	function toggleFeedModeFields() {
		var isCurated = $('input[name="lumina_ig_settings[feed_mode]"]:checked').val() === 'curated';
		$('.lumina-ig-curated-only').toggle(isCurated);
		$('.lumina-ig-live-only').toggle(!isCurated);
	}

	$('input[name="lumina_ig_settings[feed_mode]"]').on('change', toggleFeedModeFields);
	toggleFeedModeFields();

	$('#lumina-ig-preview-btn').on('click', function () {
		var $btn = $(this);
		var $preview = $('#lumina-ig-live-preview');

		$btn.prop('disabled', true).text('Loading…');

		$.post(luminaIgAdmin.ajaxUrl, {
			action: 'lumina_ig_preview',
			nonce: luminaIgAdmin.nonce,
			settings: collectDesignSettings()
		})
			.done(function (response) {
				if (response.success && response.data.html) {
					$preview.html(response.data.html);
				} else {
					$preview.html('<p class="lumina-ig-live-preview__placeholder">Preview failed. Check API settings.</p>');
				}
			})
			.fail(function () {
				$preview.html('<p class="lumina-ig-live-preview__placeholder">Preview request failed.</p>');
			})
			.always(function () {
				$btn.prop('disabled', false).text('Live Preview');
			});
	});
})(jQuery);
