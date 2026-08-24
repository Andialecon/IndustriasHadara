/**
 * Galería de imágenes del producto en el editor.
 *
 * Guarda los identificadores de los adjuntos, en orden, en un campo oculto que
 * viaja con el formulario del producto.
 */
(function () {
	'use strict';

	var strings = window.hadaraGallery || {};

	function init(root) {
		var input = root.querySelector('[data-hadara-gallery-input]');
		var list = root.querySelector('[data-hadara-gallery-list]');
		var empty = root.querySelector('[data-hadara-gallery-empty]');
		var template = root.querySelector('[data-hadara-gallery-template]');
		var addButton = root.querySelector('[data-hadara-gallery-add]');
		var frame;

		if (!input || !list || !template || !addButton) {
			return;
		}

		function ids() {
			return Array.prototype.map.call(list.children, function (item) {
				return item.dataset.id;
			});
		}

		function sync() {
			input.value = ids().join(',');

			if (empty) {
				empty.hidden = list.children.length > 0;
			}
		}

		function add(attachment) {
			if (ids().indexOf(String(attachment.id)) !== -1) {
				return;
			}

			var item = template.content.firstElementChild.cloneNode(true);
			var image = item.querySelector('img');
			var sizes = attachment.sizes || {};
			var size = sizes.thumbnail || sizes.medium || sizes.full;

			item.dataset.id = attachment.id;
			image.src = size ? size.url : attachment.url;
			list.appendChild(item);
		}

		addButton.addEventListener('click', function () {
			if (!frame) {
				frame = window.wp.media({
					title: strings.title || '',
					button: { text: strings.button || '' },
					library: { type: 'image' },
					multiple: 'add',
				});

				frame.on('select', function () {
					frame.state().get('selection').each(function (attachment) {
						add(attachment.toJSON());
					});

					sync();
				});
			}

			frame.open();
		});

		list.addEventListener('click', function (event) {
			var button = event.target.closest('button');

			if (!button) {
				return;
			}

			var item = button.closest('.hadara-gallery__item');

			if (button.hasAttribute('data-hadara-gallery-remove')) {
				item.remove();
			} else if (button.dataset.hadaraGalleryMove === '-1' && item.previousElementSibling) {
				list.insertBefore(item, item.previousElementSibling);
			} else if (button.dataset.hadaraGalleryMove === '1' && item.nextElementSibling) {
				list.insertBefore(item.nextElementSibling, item);
			}

			sync();
		});

		sync();
	}

	document.addEventListener('DOMContentLoaded', function () {
		Array.prototype.forEach.call(document.querySelectorAll('[data-hadara-gallery]'), init);
	});
})();
