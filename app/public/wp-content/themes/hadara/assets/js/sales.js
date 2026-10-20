/**
 * Página de venta: barra fija de compra en móvil, botones «Comprar ahora»
 * secundarios y evento AddToCart del Meta Pixel.
 */
(function () {
	'use strict';

	// FlexSlider pone las flechas al final de la galería, debajo de las
	// miniaturas: se mueven dentro del visor para que queden sobre la foto.
	if (window.jQuery) {
		window.jQuery('.sales-page .woocommerce-product-gallery').on('wc-product-gallery-after-init', function () {
			var nav = this.querySelector('.flex-direction-nav');
			var viewport = this.querySelector('.flex-viewport');

			if (nav && viewport) {
				viewport.appendChild(nav);
			}
		});
	}

	// En el pago, cambiar de método recalcula el total: el descuento por pagar
	// en línea aparece o desaparece al momento.
	if (window.jQuery && document.querySelector('form.checkout')) {
		window.jQuery('form.checkout').on('change', 'input[name="payment_method"]', function () {
			window.jQuery(document.body).trigger('update_checkout');
		});
	}

	var form = document.querySelector('.sales-page form.cart');

	if (!form) {
		return;
	}

	// Fecha estimada de entrega en días hábiles (lunes a sábado). Se calcula en
	// el navegador para que la caché del hosting no muestre fechas viejas.
	var delivery = document.querySelector('[data-delivery-min]');

	if (delivery && window.Intl) {
		var addBusinessDays = function (days) {
			var date = new Date();

			while (days > 0) {
				date.setDate(date.getDate() + 1);

				if (date.getDay() !== 0) {
					days--;
				}
			}

			return date;
		};
		var format = new Intl.DateTimeFormat('es-CO', { weekday: 'short', day: 'numeric', month: 'short' });
		var from = addBusinessDays(parseInt(delivery.getAttribute('data-delivery-min'), 10) || 1);
		var to = addBusinessDays(parseInt(delivery.getAttribute('data-delivery-max'), 10) || 1);
		var text = delivery.querySelector('[data-delivery-text]');

		if (text) {
			text.textContent = from.getTime() === to.getTime()
				? 'Llega el ' + format.format(to)
				: 'Llega entre el ' + format.format(from) + ' y el ' + format.format(to);
		}
	}

	// Tallas y colores como botones: más rápido de elegir en el celular que una
	// lista desplegable. El select original sigue siendo el que usa WooCommerce.
	var buildPills = function () {
		form.querySelectorAll('table.variations select').forEach(function (select) {
			if (select.dataset.pills) {
				return;
			}

			select.dataset.pills = '1';

			var group = document.createElement('div');

			group.className = 'sales-pills';
			group.setAttribute('role', 'group');
			select.parentNode.insertBefore(group, select);
			select.classList.add('sales-pills__select');

			Array.prototype.forEach.call(select.options, function (option) {
				if (!option.value) {
					return;
				}

				var pill = document.createElement('button');

				pill.type = 'button';
				pill.className = 'sales-pills__item';
				pill.textContent = option.text;
				pill.dataset.value = option.value;
				pill.addEventListener('click', function () {
					if (pill.disabled) {
						return;
					}

					select.value = select.value === option.value ? '' : option.value;
					window.jQuery(select).trigger('change');
				});
				group.appendChild(pill);
			});
		});

		syncPills();
	};

	var syncPills = function () {
		form.querySelectorAll('.sales-pills').forEach(function (group) {
			var select = group.nextElementSibling;

			if (!select || select.tagName !== 'SELECT') {
				return;
			}

			group.querySelectorAll('.sales-pills__item').forEach(function (pill) {
				var option = Array.prototype.find.call(select.options, function (o) {
					return o.value === pill.dataset.value;
				});
				var available = option && !option.disabled && (option.classList.contains('enabled') || !option.className);

				pill.disabled = !available;
				pill.classList.toggle('is-out', !available);
				pill.classList.toggle('is-active', select.value === pill.dataset.value);
				pill.setAttribute('aria-pressed', select.value === pill.dataset.value ? 'true' : 'false');
			});
		});
	};

	if (form.classList.contains('variations_form') && window.jQuery) {
		window.jQuery(form)
			.on('wc_variation_form woocommerce_update_variation_values', function () {
				buildPills();
				syncPills();
			})
			.on('change', 'select', syncPills)
			.on('reset_data', syncPills);

		buildPills();
	}

	// Botones − y + alrededor de la cantidad (más cómodos que las flechas del
	// campo numérico en el celular).
	form.querySelectorAll('.quantity').forEach(function (box) {
		var input = box.querySelector('input.qty');

		if (!input || input.type === 'hidden' || box.querySelector('.sales-qty__btn')) {
			return;
		}

		var make = function (label, text, step) {
			var button = document.createElement('button');

			button.type = 'button';
			button.className = 'sales-qty__btn';
			button.setAttribute('aria-label', label);
			button.textContent = text;
			button.addEventListener('click', function () {
				var min = parseFloat(input.min) || 1;
				var max = parseFloat(input.max) || Infinity;
				var value = (parseFloat(input.value) || min) + step;

				input.value = Math.min(max, Math.max(min, value));
				input.dispatchEvent(new Event('change', { bubbles: true }));
			});

			return button;
		};

		var minus = make('Quitar una unidad', '−', -1);
		var plus = make('Agregar una unidad', '+', 1);
		var sync = function () {
			var min = parseFloat(input.min) || 1;
			var max = parseFloat(input.max) || Infinity;
			var value = parseFloat(input.value) || min;

			minus.disabled = value <= min;
			plus.disabled = value >= max;
		};

		box.insertBefore(minus, input);
		box.appendChild(plus);
		input.addEventListener('change', sync);
		input.addEventListener('input', sync);
		// WooCommerce cambia el máximo al elegir una talla con pocas unidades.
		new MutationObserver(sync).observe(input, { attributes: true, attributeFilter: ['max', 'min'] });
		sync();
	});

	var buyBox = document.getElementById('comprar');
	var bar = document.querySelector('[data-sales-bar]');
	var isVariable = form.classList.contains('variations_form');

	// Los botones secundarios compran directo si no hay opciones que elegir;
	// si las hay, llevan al formulario para que el cliente las escoja.
	document.querySelectorAll('[data-sales-buy]').forEach(function (button) {
		button.addEventListener('click', function (event) {
			event.preventDefault();

			var submit = form.querySelector('.single_add_to_cart_button');

			if (!isVariable && submit && !submit.disabled) {
				if (form.requestSubmit) {
					form.requestSubmit(submit);
				} else {
					submit.click();
				}
				return;
			}

			buyBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
		});
	});

	// La barra fija se ve mientras el botón principal no está en pantalla: al
	// llegar (en móvil queda debajo de las fotos) y después de pasarlo.
	if (bar && buyBox && 'IntersectionObserver' in window) {
		bar.hidden = false;

		new IntersectionObserver(function (entries) {
			bar.classList.toggle('is-visible', !entries[0].isIntersecting);
		}).observe(buyBox);
	}

	form.addEventListener('submit', function () {
		if (typeof window.fbq === 'function' && window.hadaraPixelProduct) {
			var qty = form.querySelector('input.qty');
			var data = Object.assign({}, window.hadaraPixelProduct);

			data.value = data.value * (qty ? parseInt(qty.value, 10) || 1 : 1);
			window.fbq('track', 'AddToCart', data);
		}
	});
})();
