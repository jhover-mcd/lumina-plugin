/**
 * Lumina Instagram Feed — Curated photo picker.
 */
(function ($) {
	'use strict';

	var $form = $('#lumina-ig-curate-form');
	if (!$form.length) {
		return;
	}

	var $selectedList = $('#lumina-ig-curate-selected');
	var $emptyState = $('#lumina-ig-curate-selected-empty');
	var $count = $('#lumina-ig-curate-count');

	function itemId($el) {
		return String($el.attr('data-id') || '');
	}

	function updateCount() {
		var count = $selectedList.children('.lumina-ig-curate__selected-item').length;
		$count.text(count);
		$emptyState.prop('hidden', count > 0);
	}

	function addSelected(id, imageUrl) {
		if (!id || $selectedList.find('.lumina-ig-curate__selected-item[data-id="' + id + '"]').length) {
			return;
		}

		var $item = $(
			'<li class="lumina-ig-curate__selected-item" data-id="' + id + '">' +
				'<span class="lumina-ig-curate__drag" aria-hidden="true" title="Drag to reorder">&#8942;&#8942;</span>' +
				'<img src="' + imageUrl + '" alt="" />' +
				'<input type="hidden" name="curated_selected[]" value="' + id + '" />' +
				'<button type="button" class="lumina-ig-curate__remove" aria-label="Remove from feed">&times;</button>' +
			'</li>'
		);

		$selectedList.append($item);
		updateCount();
	}

	function removeSelected(id) {
		$selectedList.find('.lumina-ig-curate__selected-item[data-id="' + id + '"]').remove();
		$('.lumina-ig-curate__photo[data-id="' + id + '"]')
			.removeClass('is-selected')
			.attr('aria-pressed', 'false');
		updateCount();
	}

	if ($.fn.sortable) {
		$selectedList.sortable({
			items: '> .lumina-ig-curate__selected-item',
			handle: '.lumina-ig-curate__drag',
			placeholder: 'lumina-ig-curate__selected-placeholder',
			forcePlaceholderSize: true,
			tolerance: 'pointer',
			cancel: '.lumina-ig-curate__remove'
		});
	}

	$form.on('click', '.lumina-ig-curate__photo', function () {
		var $photo = $(this);
		var id = itemId($photo);
		var image = $photo.attr('data-image');

		if ($photo.hasClass('is-selected')) {
			removeSelected(id);
			return;
		}

		$photo.addClass('is-selected').attr('aria-pressed', 'true');
		addSelected(id, image);
	});

	$form.on('click', '.lumina-ig-curate__remove', function (e) {
		e.preventDefault();
		var id = itemId($(this).closest('.lumina-ig-curate__selected-item'));
		removeSelected(id);
	});

	updateCount();
})(jQuery);
