/**
 * Lumina Instagram Feed — Frontend interactions.
 */
(function () {
	'use strict';

	function initCarousel(feed) {
		var inner = feed.querySelector('.lumina-ig-feed__inner');
		var prev = feed.querySelector('.lumina-ig-nav--prev');
		var next = feed.querySelector('.lumina-ig-nav--next');

		if (!inner) return;

		var scrollAmount = function () {
			var item = inner.querySelector('.lumina-ig-item');
			if (!item) return 300;
			var style = window.getComputedStyle(inner);
			var gap = parseFloat(style.gap) || 16;
			return item.offsetWidth + gap;
		};

		if (prev) {
			prev.addEventListener('click', function () {
				inner.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
			});
		}

		if (next) {
			next.addEventListener('click', function () {
				inner.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
			});
		}

		if (feed.classList.contains('lumina-ig-carousel-autoplay')) {
			var speed = parseInt(feed.getAttribute('data-speed'), 10) || 4000;
			var timer = setInterval(function () {
				var atEnd = inner.scrollLeft + inner.clientWidth >= inner.scrollWidth - 4;
				if (atEnd) {
					inner.scrollTo({ left: 0, behavior: 'smooth' });
				} else {
					inner.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
				}
			}, speed);

			feed.addEventListener('mouseenter', function () { clearInterval(timer); });
		}
	}

	function init() {
		document.querySelectorAll('.lumina-ig-layout-carousel').forEach(initCarousel);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
