/**
 * Comportamientos del front: menú móvil, carrusel de portada y galería de producto.
 */
(function () {
	'use strict';

	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('menu-principal');

	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});

		// En móvil el primer toque sobre un elemento con submenú lo despliega
		// en lugar de navegar, para que las líneas del catálogo sean accesibles.
		nav.querySelectorAll('.menu-item-has-children > a').forEach(function (link) {
			link.addEventListener('click', function (event) {
				if (window.matchMedia('(min-width: 861px)').matches) {
					return;
				}

				var submenu = link.parentElement.querySelector('.sub-menu');

				if (submenu && !link.parentElement.classList.contains('is-expanded')) {
					event.preventDefault();
					link.parentElement.classList.add('is-expanded');
				}
			});
		});
	}

	var spotlight = document.querySelector('[data-spotlight]');

	if (spotlight) {
		var spotlightScroller = spotlight.querySelector('[data-spotlight-scroller]');
		var spotlightTrack = spotlight.querySelector('.product-spotlight__track');
		var spotlightPrev = spotlight.querySelector('[data-spotlight-prev]');
		var spotlightNext = spotlight.querySelector('[data-spotlight-next]');
		var spotlightReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var spotlightTimer;

		if (spotlightScroller && spotlightTrack) {
			var originalSlides = Array.prototype.slice.call(spotlightTrack.children);
			var originalCount = originalSlides.length;

			originalSlides.forEach(function (slide) {
				var clone = slide.cloneNode(true);
				clone.setAttribute('aria-hidden', 'true');
				clone.querySelectorAll('a, button, input').forEach(function (el) {
					el.setAttribute('tabindex', '-1');
				});
				spotlightTrack.appendChild(clone);
			});

			function spotlightStep() {
				var slide = spotlightTrack.querySelector('.product-spotlight__slide');

				if (!slide) {
					return spotlightScroller.clientWidth;
				}

				var styles = window.getComputedStyle(spotlightTrack);
				var gap = parseFloat(styles.columnGap || styles.gap) || 16;

				return slide.getBoundingClientRect().width + gap;
			}

			function spotlightSetWidth() {
				return originalCount * spotlightStep();
			}

			function normalizeSpotlight() {
				var set = spotlightSetWidth();

				if (set <= 0) {
					return;
				}

				var left = spotlightScroller.scrollLeft;

				if (left >= set) {
					spotlightScroller.scrollLeft = left - set;
				} else if (left < 0) {
					spotlightScroller.scrollLeft = left + set;
				}
			}

			function goSpotlight(direction) {
				var set = spotlightSetWidth();
				var step = spotlightStep();
				var motion = spotlightReduce ? 'auto' : 'smooth';

				if (direction < 0 && spotlightScroller.scrollLeft <= 2 && set > 0) {
					spotlightScroller.scrollLeft += set;
				}

				spotlightScroller.scrollBy({
					left: direction * step,
					behavior: motion,
				});
			}

			function stopSpotlight() {
				window.clearInterval(spotlightTimer);
				spotlightTimer = null;
			}

			function startSpotlight() {
				if (spotlightReduce || originalCount < 2) {
					return;
				}

				stopSpotlight();
				spotlightTimer = window.setInterval(function () {
					goSpotlight(1);
				}, 4800);
			}

			function onSpotlightSettled() {
				window.clearTimeout(spotlightScroller._loopTimer);
				normalizeSpotlight();
			}

			spotlightScroller.addEventListener('scrollend', onSpotlightSettled);
			spotlightScroller.addEventListener(
				'scroll',
				function () {
					window.clearTimeout(spotlightScroller._loopTimer);
					spotlightScroller._loopTimer = window.setTimeout(onSpotlightSettled, 480);
				},
				{ passive: true }
			);

			if (spotlightPrev) {
				spotlightPrev.addEventListener('click', function () {
					goSpotlight(-1);
				});
			}

			if (spotlightNext) {
				spotlightNext.addEventListener('click', function () {
					goSpotlight(1);
				});
			}

			if ('IntersectionObserver' in window) {
				var spotlightObserver = new IntersectionObserver(
					function (entries) {
						if (entries[0].isIntersecting) {
							startSpotlight();
						} else {
							stopSpotlight();
						}
					},
					{ threshold: 0.35 }
				);

				spotlightObserver.observe(spotlight);
			} else {
				startSpotlight();
			}
		}
	}

	var gallery = document.querySelector('[data-gallery]');

	if (!gallery) {
		return;
	}

	var main = gallery.querySelector('[data-gallery-main]');
	var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-gallery-thumb]'));
	var stage = gallery.querySelector('.product__zoom-stage');
	var lens = gallery.querySelector('[data-zoom-lens]');
	var prevBtn = gallery.querySelector('[data-gallery-prev]');
	var nextBtn = gallery.querySelector('[data-gallery-next]');
	var ZOOM = 2.6;
	var SWIPE_THRESHOLD = 48;
	var index = 0;

	function setActiveThumb(activeIndex) {
		thumbs.forEach(function (other, i) {
			var isActive = i === activeIndex;
			other.classList.toggle('is-active', isActive);
			other.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});
	}

	function showImage(nextIndex) {
		if (!main || !thumbs.length) {
			return;
		}

		var total = thumbs.length;
		index = ((nextIndex % total) + total) % total;

		var thumb = thumbs[index];
		var full = thumb.getAttribute('data-full');
		var srcset = thumb.getAttribute('data-srcset');
		var zoom = thumb.getAttribute('data-zoom');

		if (!full) {
			return;
		}

		main.classList.remove('is-swiping');
		// Fuerza un reflow suave al cambiar de foto.
		void main.offsetWidth;
		main.classList.add('is-swiping');

		main.setAttribute('src', full);

		if (srcset) {
			main.setAttribute('srcset', srcset);
		} else {
			main.removeAttribute('srcset');
		}

		if (zoom) {
			main.setAttribute('data-zoom-src', zoom);
		}

		setActiveThumb(index);
		hideLens();
	}

	thumbs.forEach(function (thumb, i) {
		thumb.addEventListener('click', function () {
			showImage(i);
		});
	});

	if (prevBtn) {
		prevBtn.addEventListener('click', function (event) {
			event.preventDefault();
			showImage(index - 1);
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function (event) {
			event.preventDefault();
			showImage(index + 1);
		});
	}

	if (!main || !stage || !lens) {
		return;
	}

	function hideLens() {
		lens.hidden = true;
		stage.classList.remove('is-zooming');
	}

	function showLens() {
		lens.hidden = false;
		stage.classList.add('is-zooming');
	}

	function moveLens(event) {
		var zoomSrc = main.getAttribute('data-zoom-src') || main.getAttribute('src');

		if (!zoomSrc) {
			return;
		}

		var rect = stage.getBoundingClientRect();
		var x = event.clientX - rect.left;
		var y = event.clientY - rect.top;

		if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
			hideLens();
			return;
		}

		showLens();

		var lensW = lens.offsetWidth || 148;
		var lensH = lens.offsetHeight || 148;
		var halfW = lensW / 2;
		var halfH = lensH / 2;
		var left = Math.max(0, Math.min(x - halfW, rect.width - lensW));
		var top = Math.max(0, Math.min(y - halfH, rect.height - lensH));

		lens.style.left = left + 'px';
		lens.style.top = top + 'px';
		lens.style.backgroundImage = 'url("' + zoomSrc + '")';
		lens.style.backgroundRepeat = 'no-repeat';
		lens.style.backgroundSize = rect.width * ZOOM + 'px ' + rect.height * ZOOM + 'px';
		lens.style.backgroundPosition =
			-(x * ZOOM - halfW) + 'px ' + -(y * ZOOM - halfH) + 'px';
	}

	// Lupa solo con ratón: en táctil el gesto horizontal cambia de foto.
	stage.addEventListener('mouseenter', function (event) {
		moveLens(event);
	});

	stage.addEventListener('mousemove', function (event) {
		moveLens(event);
	});

	stage.addEventListener('mouseleave', hideLens);

	if (thumbs.length > 1) {
		var touchStartX = 0;
		var touchStartY = 0;
		var touchDeltaX = 0;
		var swiping = false;
		var decided = false;

		stage.addEventListener(
			'touchstart',
			function (event) {
				if (event.touches.length !== 1) {
					return;
				}

				touchStartX = event.touches[0].clientX;
				touchStartY = event.touches[0].clientY;
				touchDeltaX = 0;
				swiping = false;
				decided = false;
				hideLens();
			},
			{ passive: true }
		);

		stage.addEventListener(
			'touchmove',
			function (event) {
				if (event.touches.length !== 1) {
					return;
				}

				var dx = event.touches[0].clientX - touchStartX;
				var dy = event.touches[0].clientY - touchStartY;

				if (!decided) {
					if (Math.abs(dx) < 10 && Math.abs(dy) < 10) {
						return;
					}

					decided = true;
					swiping = Math.abs(dx) > Math.abs(dy);
				}

				if (!swiping) {
					return;
				}

				event.preventDefault();
				touchDeltaX = dx;
				main.style.transform = 'translateX(' + dx * 0.35 + 'px)';
				main.style.opacity = String(Math.max(0.45, 1 - Math.abs(dx) / 360));
			},
			{ passive: false }
		);

		function endSwipe() {
			if (swiping) {
				main.style.transform = '';
				main.style.opacity = '';

				if (touchDeltaX <= -SWIPE_THRESHOLD) {
					showImage(index + 1);
				} else if (touchDeltaX >= SWIPE_THRESHOLD) {
					showImage(index - 1);
				}
			}

			touchDeltaX = 0;
			swiping = false;
			decided = false;
		}

		stage.addEventListener('touchend', endSwipe);
		stage.addEventListener('touchcancel', endSwipe);

		gallery.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowLeft') {
				event.preventDefault();
				showImage(index - 1);
			} else if (event.key === 'ArrowRight') {
				event.preventDefault();
				showImage(index + 1);
			}
		});
	}
})();
