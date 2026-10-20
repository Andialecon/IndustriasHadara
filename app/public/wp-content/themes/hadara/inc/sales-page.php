<?php
/**
 * Página de venta de los productos de la tienda y tráfico de anuncios.
 *
 * - Datos de la página de venta (gancho, beneficios, preguntas) editables por
 *   producto, con valores por defecto sensatos.
 * - «Comprar ahora»: añade el producto y lleva directo al pago.
 * - Modo anuncio: si la visita viene de Facebook o Instagram, se oculta la
 *   navegación del sitio en la ficha y en el pago.
 * - Meta Pixel opcional con los eventos de compra.
 *
 * Se carga desde inc/woocommerce.php, solo con WooCommerce activo.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * ---------------------------------------------------------------------------
 * Datos de la página de venta
 * ---------------------------------------------------------------------------
 */

/**
 * Indica si el pago contra entrega está activo.
 *
 * @return bool
 */
function hadara_cod_enabled() {
	$settings = get_option( 'woocommerce_cod_settings', array() );

	return isset( $settings['enabled'] ) && 'yes' === $settings['enabled'];
}

/**
 * Indica si hay envío gratis sin mínimo de compra en alguna zona.
 *
 * @return bool
 */
function hadara_free_shipping_enabled() {
	static $enabled = null;

	if ( null !== $enabled ) {
		return $enabled;
	}

	$enabled = false;

	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return $enabled;
	}

	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		foreach ( $zone['shipping_methods'] as $method ) {
			if ( 'free_shipping' === $method->id && 'yes' === $method->enabled && in_array( $method->get_option( 'requires' ), array( '', 'no' ), true ) ) {
				$enabled = true;

				return $enabled;
			}
		}
	}

	return $enabled;
}

/**
 * Convierte un texto de varias líneas en una lista limpia.
 *
 * @param string $text Texto.
 * @return string[]
 */
function hadara_lines_to_list( $text ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $text );
	$lines = array_map(
		static function ( $line ) {
			return trim( preg_replace( '/^[\s\-\*•·✔✓✅]+/u', '', $line ) );
		},
		$lines
	);

	return array_values( array_filter( $lines, 'strlen' ) );
}

/**
 * Preguntas frecuentes por defecto de la tienda.
 *
 * Solo afirman lo que la configuración de la tienda garantiza.
 *
 * @return array<int,array{q:string,a:string}>
 */
function hadara_default_sales_faq() {
	$faq = array();

	if ( hadara_cod_enabled() ) {
		$faq[] = array(
			'q' => __( '¿Cómo pago?', 'hadara' ),
			'a' => __( 'Paga en efectivo al transportador cuando recibe el pedido. No necesita tarjeta ni pagar por adelantado.', 'hadara' ),
		);
	}

	$faq[] = array(
		'q' => __( '¿Cuánto cuesta el envío?', 'hadara' ),
		'a' => hadara_free_shipping_enabled()
			? __( 'El envío es gratis a cualquier ciudad o municipio de Colombia.', 'hadara' )
			: __( 'El valor del envío se calcula al finalizar la compra según su ciudad.', 'hadara' ),
	);

	$faq[] = array(
		'q' => __( '¿Cuánto tarda en llegar?', 'hadara' ),
		'a' => __( 'Despachamos por transportadora a todo el país. El tiempo depende de la ciudad de destino; le confirmamos el pedido y el envío por WhatsApp.', 'hadara' ),
	);

	$faq[] = array(
		'q' => __( '¿Cómo elijo la talla o el color?', 'hadara' ),
		'a' => __( 'Si el producto tiene opciones, elíjalas antes de comprar. Si no, escríbalas en «Talla, color e indicaciones de entrega» al finalizar la compra.', 'hadara' ),
	);

	return $faq;
}

/**
 * Reúne lo que necesita la página de venta de un producto.
 *
 * @param WC_Product $product Producto.
 * @return array
 */
function hadara_sales_data( $product ) {
	$id   = $product->get_id();
	$data = array(
		'hook'      => trim( (string) get_post_meta( $id, '_hadara_sales_hook', true ) ),
		'benefits'  => hadara_lines_to_list( get_post_meta( $id, '_hadara_sales_benefits', true ) ),
		'faq'       => array(),
		'savings'   => '',
		'low_stock' => '',
		'cod'       => hadara_cod_enabled(),
		'trust'     => array(),
	);

	// Sin gancho propio se usa la descripción corta, en una sola frase.
	if ( '' === $data['hook'] && $product->get_short_description() ) {
		$data['hook'] = hadara_trim_description( trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $product->get_short_description() ) ) ), 160 );
	}

	foreach ( hadara_lines_to_list( get_post_meta( $id, '_hadara_sales_faq', true ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );

		if ( 2 === count( $parts ) && $parts[0] && $parts[1] ) {
			$data['faq'][] = array(
				'q' => $parts[0],
				'a' => $parts[1],
			);
		}
	}

	$data['faq'] = array_merge( $data['faq'], hadara_default_sales_faq() );

	// Ahorro real: solo cuando hay un precio rebajado configurado.
	if ( $product->is_type( 'simple' ) && $product->is_on_sale() ) {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();

		if ( $regular > $sale && $sale > 0 ) {
			$data['savings'] = sprintf(
				/* translators: 1: porcentaje, 2: valor ahorrado. */
				__( 'Ahorra %1$d%% (%2$s)', 'hadara' ),
				round( ( $regular - $sale ) / $regular * 100 ),
				wp_strip_all_tags( wc_price( $regular - $sale ) )
			);
		}
	}

	// Existencias reales (Dropify las sincroniza con el proveedor).
	if ( $product->managing_stock() && $product->is_in_stock() ) {
		$stock = (int) $product->get_stock_quantity();

		if ( $stock > 0 && $stock <= 10 ) {
			$data['low_stock'] = sprintf(
				/* translators: %d: unidades. */
				_n( 'Queda %d unidad disponible', 'Quedan %d unidades disponibles', $stock, 'hadara' ),
				$stock
			);
		}
	}

	if ( $data['cod'] ) {
		$data['trust']['cash'] = __( 'Pago contra entrega', 'hadara' );
	}

	if ( hadara_free_shipping_enabled() ) {
		$data['trust']['truck'] = __( 'Envío gratis a toda Colombia', 'hadara' );
	}

	if ( $data['cod'] ) {
		$data['trust']['shield'] = __( 'Sin pagos por adelantado', 'hadara' );
	}

	/**
	 * Permite ajustar los datos de la página de venta.
	 *
	 * @param array      $data    Datos.
	 * @param WC_Product $product Producto.
	 */
	return apply_filters( 'hadara_sales_data', $data, $product );
}

/*
 * ---------------------------------------------------------------------------
 * Campos editables en el producto
 * ---------------------------------------------------------------------------
 */

/**
 * Caja «Página de venta» en el editor de productos.
 */
function hadara_add_sales_box() {
	add_meta_box( 'hadara-sales', __( 'Página de venta', 'hadara' ), 'hadara_render_sales_box', 'product', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'hadara_add_sales_box' );

/**
 * Pinta la caja «Página de venta».
 *
 * @param WP_Post $post Producto.
 */
function hadara_render_sales_box( $post ) {
	wp_nonce_field( 'hadara_sales', 'hadara_sales_nonce' );
	?>
	<p>
		<label for="hadara_sales_hook"><strong><?php esc_html_e( 'Gancho principal', 'hadara' ); ?></strong></label>
		<input type="text" class="widefat" id="hadara_sales_hook" name="hadara_sales_hook" maxlength="160"
			value="<?php echo esc_attr( get_post_meta( $post->ID, '_hadara_sales_hook', true ) ); ?>"
			placeholder="<?php esc_attr_e( 'Ej.: El jean que se ajusta a su figura y no se deforma con el uso', 'hadara' ); ?>">
		<span class="description"><?php esc_html_e( 'Una frase debajo del título con el beneficio más fuerte. Si se deja vacío se usa la descripción corta.', 'hadara' ); ?></span>
	</p>
	<p>
		<label for="hadara_sales_benefits"><strong><?php esc_html_e( 'Beneficios (uno por línea)', 'hadara' ); ?></strong></label>
		<textarea class="widefat" id="hadara_sales_benefits" name="hadara_sales_benefits" rows="6"
			placeholder="<?php esc_attr_e( "Tela índigo con elasticidad que no se deforma\nTiro alto que estiliza la figura\nTallas de la 6 a la 16", 'hadara' ); ?>"><?php echo esc_textarea( get_post_meta( $post->ID, '_hadara_sales_benefits', true ) ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Los tres primeros salen junto al botón de compra; el resto, más abajo.', 'hadara' ); ?></span>
	</p>
	<p>
		<label for="hadara_sales_faq"><strong><?php esc_html_e( 'Preguntas frecuentes propias', 'hadara' ); ?></strong></label>
		<textarea class="widefat" id="hadara_sales_faq" name="hadara_sales_faq" rows="4"
			placeholder="<?php esc_attr_e( '¿Es tela rígida o elástica? | Es índigo confort, con elasticidad.', 'hadara' ); ?>"><?php echo esc_textarea( get_post_meta( $post->ID, '_hadara_sales_faq', true ) ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Una por línea, con el formato «Pregunta | Respuesta». Se muestran antes de las preguntas generales de pago y envío.', 'hadara' ); ?></span>
	</p>
	<p>
		<label for="hadara_sales_size_guide"><strong><?php esc_html_e( 'Guía de tallas', 'hadara' ); ?></strong></label>
		<textarea class="widefat" id="hadara_sales_size_guide" name="hadara_sales_size_guide" rows="5"
			placeholder="<?php esc_attr_e( "Talla | Cintura (cm) | Cadera (cm)
6 | 64 | 90
8 | 68 | 94", 'hadara' ); ?>"><?php echo esc_textarea( get_post_meta( $post->ID, '_hadara_sales_size_guide', true ) ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Una fila por línea con columnas separadas por «|»; la primera fila es el encabezado. Use las medidas que da el proveedor.', 'hadara' ); ?></span>
	</p>
	<p>
		<label for="hadara_sales_testimonials"><strong><?php esc_html_e( 'Testimonios de clientes', 'hadara' ); ?></strong></label>
		<textarea class="widefat" id="hadara_sales_testimonials" name="hadara_sales_testimonials" rows="4"
			placeholder="<?php esc_attr_e( 'Nombre | Ciudad | Lo que dijo el cliente', 'hadara' ); ?>"><?php echo esc_textarea( get_post_meta( $post->ID, '_hadara_sales_testimonials', true ) ); ?></textarea>
		<span class="description" style="color:#b42318"><?php esc_html_e( 'Solo opiniones reales de clientes que compraron, con su permiso. Publicar testimonios inventados es publicidad engañosa (Estatuto del Consumidor) y Meta puede rechazar los anuncios. Si se deja vacío, la sección no aparece.', 'hadara' ); ?></span>
	</p>
	<?php
}

/**
 * Guarda la caja «Página de venta».
 *
 * @param int $post_id Producto.
 */
function hadara_save_sales_box( $post_id ) {
	if ( ! isset( $_POST['hadara_sales_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hadara_sales_nonce'] ), 'hadara_sales' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$fields = array(
		'_hadara_sales_hook'     => isset( $_POST['hadara_sales_hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hadara_sales_hook'] ) ) : '',
		'_hadara_sales_benefits' => isset( $_POST['hadara_sales_benefits'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hadara_sales_benefits'] ) ) : '',
		'_hadara_sales_faq'      => isset( $_POST['hadara_sales_faq'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hadara_sales_faq'] ) ) : '',
		'_hadara_sales_size_guide'   => isset( $_POST['hadara_sales_size_guide'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hadara_sales_size_guide'] ) ) : '',
		'_hadara_sales_testimonials' => isset( $_POST['hadara_sales_testimonials'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hadara_sales_testimonials'] ) ) : '',
	);

	foreach ( $fields as $key => $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_product', 'hadara_save_sales_box' );

/*
 * ---------------------------------------------------------------------------
 * «Comprar ahora»: directo al pago
 * ---------------------------------------------------------------------------
 */

/**
 * Marca el formulario de la ficha como compra directa.
 */
function hadara_buy_now_field() {
	echo '<input type="hidden" name="hadara_buy_now" value="1">';
}
add_action( 'woocommerce_before_add_to_cart_button', 'hadara_buy_now_field' );

/**
 * Texto del botón de la ficha.
 *
 * @return string
 */
function hadara_single_button_text() {
	return __( 'Comprar ahora', 'hadara' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'hadara_single_button_text' );

/**
 * Indica si la petición es una compra directa.
 *
 * @return bool
 */
function hadara_is_buy_now() {
	return ! empty( $_REQUEST['hadara_buy_now'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * La compra directa lleva solo este producto: se vacía el carrito antes.
 *
 * @param bool $passed Validación.
 * @return bool
 */
function hadara_buy_now_empty_cart( $passed ) {
	if ( $passed && hadara_is_buy_now() && WC()->cart && ! WC()->cart->is_empty() ) {
		WC()->cart->empty_cart();
	}

	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'hadara_buy_now_empty_cart', 1 );

/**
 * Tras añadir, la compra directa va a la página de pago.
 *
 * @param string $url URL de destino.
 * @return string
 */
function hadara_buy_now_redirect( $url ) {
	return hadara_is_buy_now() ? wc_get_checkout_url() : $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'hadara_buy_now_redirect', 20 );

/**
 * Sin el aviso «se ha añadido a su carrito» en la página de pago.
 *
 * @param string $message Aviso.
 * @return string
 */
function hadara_buy_now_message( $message ) {
	return hadara_is_buy_now() ? '' : $message;
}
add_filter( 'wc_add_to_cart_message_html', 'hadara_buy_now_message' );

/**
 * Script de la página de venta: barra fija de compra y eventos del píxel.
 */
function hadara_sales_assets() {
	if ( ! is_product() && ! is_checkout() ) {
		return;
	}

	wp_enqueue_script( 'hadara-sales', get_theme_file_uri( 'assets/js/sales.js' ), array(), HADARA_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'hadara_sales_assets' );

/*
 * ---------------------------------------------------------------------------
 * Modo anuncio
 * ---------------------------------------------------------------------------
 */

/**
 * Detecta en el navegador si la visita viene de un anuncio y lo recuerda una
 * hora, para que el pago también se muestre sin distracciones.
 *
 * Se hace en el navegador y no en PHP para que funcione aunque el hosting
 * guarde las páginas en caché (las cachés suelen ignorar fbclid).
 */
function hadara_landing_mode_script() {
	if ( ! function_exists( 'hadara_is_shop_view' ) || ! hadara_is_shop_view() ) {
		return;
	}
	?>
	<script>
	(function () {
		try {
			var q = location.search, s = window.sessionStorage, k = 'hadaraLanding';
			if (/[?&](fbclid|landing)=|[?&]utm_source=(facebook|fb|ig|instagram|meta)\b/i.test(q)) { s.setItem(k, Date.now()); }
			var t = +s.getItem(k);
			if (t && Date.now() - t < 36e5) { document.documentElement.classList.add('is-landing'); }
		} catch (e) {}
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'hadara_landing_mode_script', 0 );

/*
 * ---------------------------------------------------------------------------
 * Meta Pixel (opcional)
 * ---------------------------------------------------------------------------
 */

/**
 * Campo del identificador del píxel en el personalizador.
 *
 * @param WP_Customize_Manager $wp_customize Personalizador.
 */
function hadara_pixel_customize( $wp_customize ) {
	$wp_customize->add_setting(
		'hadara_meta_pixel_id',
		array(
			'default'           => '',
			'sanitize_callback' => static function ( $value ) {
				return preg_replace( '/\D/', '', (string) $value );
			},
		)
	);

	$wp_customize->add_control(
		'hadara_meta_pixel_id',
		array(
			'label'       => __( 'ID del Meta Pixel (Facebook)', 'hadara' ),
			'description' => __( 'Solo números. Activa los eventos de la tienda: vista de producto, inicio de pago y compra. Déjelo vacío para no cargar el píxel.', 'hadara' ),
			'section'     => 'hadara_branding',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'hadara_pixel_customize', 20 );

/**
 * Identificador del píxel, si está configurado.
 *
 * @return string
 */
function hadara_pixel_id() {
	return (string) get_theme_mod( 'hadara_meta_pixel_id', '' );
}

/**
 * Datos de un producto para los eventos del píxel.
 *
 * @param WC_Product $product Producto.
 * @return array
 */
function hadara_pixel_product( $product ) {
	return array(
		'content_ids'  => array( (string) ( $product->get_sku() ? $product->get_sku() : $product->get_id() ) ),
		'content_name' => $product->get_name(),
		'content_type' => 'product',
		'value'        => (float) wc_get_price_to_display( $product ),
		'currency'     => get_woocommerce_currency(),
	);
}

/**
 * Código base del píxel y eventos de la vista actual.
 */
function hadara_pixel_head() {
	$pixel = hadara_pixel_id();

	if ( '' === $pixel ) {
		return;
	}

	$events = array();

	if ( is_product() ) {
		$product = wc_get_product( get_queried_object_id() );

		if ( $product ) {
			$events[] = array( 'ViewContent', hadara_pixel_product( $product ), null );
		}
	} elseif ( is_order_received_page() ) {
		$order_id = absint( get_query_var( 'order-received' ) );
		$order    = $order_id ? wc_get_order( $order_id ) : false;
		$key      = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $order && hash_equals( $order->get_order_key(), $key ) && ! $order->get_meta( '_hadara_pixel_purchase' ) ) {
			$ids = array();

			foreach ( $order->get_items() as $item ) {
				$item_product = $item->get_product();
				$ids[]        = (string) ( $item_product && $item_product->get_sku() ? $item_product->get_sku() : $item->get_product_id() );
			}

			$events[] = array(
				'Purchase',
				array(
					'content_ids'  => $ids,
					'content_type' => 'product',
					'num_items'    => $order->get_item_count(),
					'value'        => (float) $order->get_total(),
					'currency'     => $order->get_currency(),
				),
				'pedido-' . $order->get_id(),
			);

			// Se envía una sola vez aunque el cliente recargue la página.
			$order->update_meta_data( '_hadara_pixel_purchase', 1 );
			$order->save();
		}
	} elseif ( is_checkout() && WC()->cart && ! WC()->cart->is_empty() ) {
		$ids = array();

		foreach ( WC()->cart->get_cart() as $item ) {
			$ids[] = (string) ( $item['data']->get_sku() ? $item['data']->get_sku() : $item['product_id'] );
		}

		$events[] = array(
			'InitiateCheckout',
			array(
				'content_ids'  => $ids,
				'content_type' => 'product',
				'num_items'    => WC()->cart->get_cart_contents_count(),
				'value'        => (float) WC()->cart->get_total( 'edit' ),
				'currency'     => get_woocommerce_currency(),
			),
			null,
		);
	}
	?>
	<script>
	!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', <?php echo wp_json_encode( $pixel ); ?>);
	fbq('track', 'PageView');
	<?php foreach ( $events as $event ) : ?>
	fbq('track', <?php echo wp_json_encode( $event[0] ); ?>, <?php echo wp_json_encode( $event[1] ); ?><?php echo $event[2] ? ', ' . wp_json_encode( array( 'eventID' => $event[2] ) ) : ''; ?>);
	<?php endforeach; ?>
	<?php if ( is_product() && ! empty( $events[0][1] ) ) : ?>
	window.hadaraPixelProduct = <?php echo wp_json_encode( $events[0][1] ); ?>;
	<?php endif; ?>
	</script>
	<?php
}
add_action( 'wp_head', 'hadara_pixel_head', 5 );

/*
 * ---------------------------------------------------------------------------
 * Pago más directo
 * ---------------------------------------------------------------------------
 */

/**
 * Textos del pago pensados para un comprador de contra entrega.
 *
 * @param string $translation Texto traducido.
 * @param string $text        Texto original.
 * @param string $domain      Dominio.
 * @return string
 */
function hadara_checkout_texts( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translation;
	}

	if ( 'Billing details' === $text || 'Billing &amp; Shipping' === $text ) {
		return __( 'Datos para la entrega', 'hadara' );
	}

	return $translation;
}
add_filter( 'gettext', 'hadara_checkout_texts', 10, 3 );

/**
 * Botón final del pago.
 *
 * @return string
 */
function hadara_order_button_text() {
	// Texto por defecto; cada pasarela trae el suyo (ver hadara_payment_labels()).
	return __( 'Confirmar pedido', 'hadara' );
}
add_filter( 'woocommerce_order_button_text', 'hadara_order_button_text' );

/**
 * Flechas de anterior y siguiente en el visor de fotos del producto.
 *
 * @param array $options Opciones del carrusel (FlexSlider).
 * @return array
 */
function hadara_gallery_arrows( $options ) {
	$options['directionNav'] = true;
	$options['animationLoop'] = true; // De la última foto vuelve a la primera.
	$options['prevText']     = __( 'Foto anterior', 'hadara' );
	$options['nextText']     = __( 'Foto siguiente', 'hadara' );

	return $options;
}
add_filter( 'woocommerce_single_product_carousel_options', 'hadara_gallery_arrows' );

/**
 * Si un atributo tiene una sola opción (por ejemplo, un único color), se deja
 * elegida: un clic menos para el comprador.
 *
 * @param array $args Argumentos del selector de la variación.
 * @return array
 */
function hadara_autoselect_single_option( $args ) {
	if ( empty( $args['selected'] ) && ! empty( $args['options'] ) && 1 === count( $args['options'] ) ) {
		$args['selected'] = reset( $args['options'] );
	}

	return $args;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'hadara_autoselect_single_option' );

/**
 * «1 disponibles» → «Última unidad disponible».
 *
 * @param string     $text    Texto de disponibilidad.
 * @param WC_Product $product Producto o variación.
 * @return string
 */
function hadara_availability_text( $text, $product ) {
	if ( $product->managing_stock() && $product->is_in_stock() && 1 === (int) $product->get_stock_quantity() ) {
		return __( 'Última unidad disponible', 'hadara' );
	}

	return $text;
}
add_filter( 'woocommerce_get_availability_text', 'hadara_availability_text', 10, 2 );

/*
 * ---------------------------------------------------------------------------
 * Ajustes de la página de venta (Personalizador → Tienda: página de venta)
 * ---------------------------------------------------------------------------
 */

/**
 * Valores por defecto de los ajustes de la tienda.
 *
 * @return array<string,string|int>
 */
function hadara_shop_defaults() {
	return array(
		'announcement' => '',
		'delivery_min' => 2,
		'delivery_max' => 6,
		'guarantee'    => '',
		'online_discount' => 5,
	);
}

/**
 * Lee un ajuste de la tienda.
 *
 * @param string $key Clave.
 * @return string|int
 */
function hadara_shop_setting( $key ) {
	$defaults = hadara_shop_defaults();

	return get_theme_mod( 'hadara_shop_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Sección y controles en el personalizador.
 *
 * @param WP_Customize_Manager $wp_customize Personalizador.
 */
function hadara_shop_customize( $wp_customize ) {
	$wp_customize->add_section(
		'hadara_shop',
		array(
			'title'       => __( 'Tienda: página de venta', 'hadara' ),
			'description' => __( 'Textos de la tienda en línea. Escriba solo lo que pueda cumplir: lo que promete aquí lo ve cada cliente.', 'hadara' ),
			'priority'    => 34,
		)
	);

	$fields = array(
		'announcement' => array(
			'label'       => __( 'Barra de anuncio', 'hadara' ),
			'description' => __( 'Texto de la franja superior en la tienda. Vacío: se arma solo con el envío gratis y el pago contra entrega si están activos.', 'hadara' ),
			'type'        => 'text',
			'sanitize'    => 'sanitize_text_field',
		),
		'delivery_min' => array(
			'label'       => __( 'Entrega: días hábiles mínimos', 'hadara' ),
			'description' => __( 'Para la fecha estimada «Llega entre…». Use los tiempos reales de las transportadoras de Dropi. Ponga 0 en ambos para ocultarla.', 'hadara' ),
			'type'        => 'number',
			'sanitize'    => 'absint',
		),
		'delivery_max' => array(
			'label'    => __( 'Entrega: días hábiles máximos', 'hadara' ),
			'type'     => 'number',
			'sanitize' => 'absint',
		),
		'online_discount' => array(
			'label'       => __( 'Descuento por pagar en línea (%)', 'hadara' ),
			'description' => __( 'Se aplica al pagar con Wompi (tarjeta, PSE, Nequi). 0 para desactivarlo. Debe ser menor que lo que ahorra en comisión de recaudo y devoluciones.', 'hadara' ),
			'type'        => 'number',
			'sanitize'    => 'absint',
		),
		'guarantee'    => array(
			'label'       => __( 'Garantía o cambios (resumen)', 'hadara' ),
			'description' => __( 'Ej.: «Cambio de talla gratis dentro de los 5 días siguientes a la entrega». Debe coincidir con su política de cambios. Vacío: no se muestra.', 'hadara' ),
			'type'        => 'textarea',
			'sanitize'    => 'sanitize_textarea_field',
		),
	);

	$defaults = hadara_shop_defaults();

	foreach ( $fields as $key => $field ) {
		$wp_customize->add_setting(
			'hadara_shop_' . $key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $field['sanitize'],
			)
		);

		$wp_customize->add_control(
			'hadara_shop_' . $key,
			array(
				'label'       => $field['label'],
				'description' => isset( $field['description'] ) ? $field['description'] : '',
				'section'     => 'hadara_shop',
				'type'        => $field['type'],
			)
		);
	}
}
add_action( 'customize_register', 'hadara_shop_customize', 30 );

/**
 * Texto de la barra de anuncio de la tienda (vacío fuera de la tienda).
 *
 * @return string
 */
function hadara_shop_announcement() {
	if ( ! function_exists( 'hadara_is_shop_view' ) || ! hadara_is_shop_view() ) {
		return '';
	}

	$text = trim( (string) hadara_shop_setting( 'announcement' ) );

	if ( '' !== $text ) {
		return $text;
	}

	$parts = array();

	if ( hadara_free_shipping_enabled() ) {
		$parts[] = __( 'Envío gratis a toda Colombia', 'hadara' );
	}

	if ( hadara_cod_enabled() ) {
		$parts[] = __( 'Paga al recibir', 'hadara' );
	}

	return implode( ' · ', $parts );
}

/**
 * Convierte el texto «A | B | C» de varias líneas en filas.
 *
 * @param string $text Texto.
 * @param int    $min  Columnas mínimas por fila.
 * @return array<int,string[]>
 */
function hadara_lines_to_rows( $text, $min = 2 ) {
	$rows = array();

	foreach ( hadara_lines_to_list( $text ) as $line ) {
		$cells = array_map( 'trim', explode( '|', $line ) );

		if ( count( $cells ) >= $min ) {
			$rows[] = $cells;
		}
	}

	return $rows;
}

/**
 * Añade a la página de venta la guía de tallas, testimonios, entrega,
 * garantía y la escasez real de los productos con tallas.
 *
 * @param array      $data    Datos de la página.
 * @param WC_Product $product Producto.
 * @return array
 */
function hadara_sales_data_extra( $data, $product ) {
	$id = $product->get_id();

	$data['size_guide']   = hadara_lines_to_rows( get_post_meta( $id, '_hadara_sales_size_guide', true ) );
	$data['testimonials'] = array();

	foreach ( hadara_lines_to_rows( get_post_meta( $id, '_hadara_sales_testimonials', true ), 3 ) as $row ) {
		$data['testimonials'][] = array(
			'name' => $row[0],
			'city' => $row[1],
			'text' => implode( ' | ', array_slice( $row, 2 ) ),
		);
	}

	$min = (int) hadara_shop_setting( 'delivery_min' );
	$max = (int) hadara_shop_setting( 'delivery_max' );

	$data['delivery'] = $max > 0 ? array(
		'min' => min( $min, $max ),
		'max' => $max,
	) : array();

	$data['guarantee'] = trim( (string) hadara_shop_setting( 'guarantee' ) );

	if ( $data['guarantee'] ) {
		$data['trust']['check'] = __( 'Garantía de cambio', 'hadara' );
		array_unshift(
			$data['faq'],
			array(
				'q' => __( '¿Y si no me queda o no me gusta?', 'hadara' ),
				'a' => $data['guarantee'],
			)
		);
	}

	// Escasez real en productos con tallas: suma de las tallas disponibles.
	if ( '' === $data['low_stock'] && $product->is_type( 'variable' ) ) {
		$total   = 0;
		$managed = false;

		foreach ( $product->get_children() as $variation_id ) {
			$variation = wc_get_product( $variation_id );

			if ( $variation && $variation->managing_stock() ) {
				$managed = true;
				$total  += max( 0, (int) $variation->get_stock_quantity() );
			}
		}

		if ( $managed && $total > 0 && $total <= 10 ) {
			/* translators: %d: unidades. */
			$data['low_stock'] = sprintf( _n( 'Queda %d unidad entre todas las tallas', 'Quedan %d unidades entre todas las tallas', $total, 'hadara' ), $total );
		}
	}

	// Fotos para la sección «Míralo en detalle».
	$data['photos'] = array_values( array_filter( array_merge( array( (int) $product->get_image_id() ), array_map( 'intval', $product->get_gallery_image_ids() ) ) ) );

	return $data;
}
add_filter( 'hadara_sales_data', 'hadara_sales_data_extra', 10, 2 );

/**
 * La etiqueta de oferta muestra el porcentaje real de descuento.
 *
 * @param string     $html    Etiqueta.
 * @param WP_Post    $post    Entrada.
 * @param WC_Product $product Producto.
 * @return string
 */
function hadara_sale_flash( $html, $post, $product ) {
	$percent = 0;
	$items   = $product->is_type( 'variable' ) ? array_filter( array_map( 'wc_get_product', $product->get_children() ) ) : array( $product );

	foreach ( $items as $item ) {
		$regular = (float) $item->get_regular_price();

		if ( $item->is_on_sale() && $regular > 0 ) {
			$percent = max( $percent, round( ( $regular - (float) $item->get_sale_price() ) / $regular * 100 ) );
		}
	}

	return $percent >= 1 ? '<span class="onsale">-' . (int) $percent . '%</span>' : $html;
}
add_filter( 'woocommerce_sale_flash', 'hadara_sale_flash', 10, 3 );

/*
 * ---------------------------------------------------------------------------
 * Descuento por pagar en línea (Wompi)
 * ---------------------------------------------------------------------------
 *
 * El envío es gratis para todos; quien paga en línea recibe además un
 * descuento. Se plantea como ganancia y no como recargo al contra entrega:
 * cada pedido pagado por adelantado no se devuelve ni paga recaudo.
 */

/**
 * ID de la pasarela de pago en línea.
 */
const HADARA_ONLINE_GATEWAY = 'wompi';

/**
 * Porcentaje de descuento si la pasarela en línea está disponible; 0 si no.
 *
 * El plugin de Wompi solo se ofrece cuando tiene sus cuatro llaves, así que
 * mientras no esté configurado no se anuncia ningún descuento.
 *
 * @return int
 */
function hadara_online_discount_percent() {
	static $percent = null;

	if ( null !== $percent ) {
		return $percent;
	}

	$percent = min( 50, (int) hadara_shop_setting( 'online_discount' ) );

	if ( $percent <= 0 ) {
		$percent = 0;

		return $percent;
	}

	// Se miran los ajustes guardados y no is_available(): el plugin de Wompi
	// consulta su API en cada llamada a is_available() sin guardar el resultado,
	// y esto se usa en cada página de producto.
	$settings = get_option( 'woocommerce_' . HADARA_ONLINE_GATEWAY . '_settings', array() );
	$prefix   = isset( $settings['testmode'] ) && 'yes' === $settings['testmode'] ? 'test_' : '';
	$ready    = isset( $settings['enabled'] ) && 'yes' === $settings['enabled'];

	foreach ( array( 'public_key', 'private_key', 'event_secret_key', 'integrity_key' ) as $key ) {
		$ready = $ready && ! empty( $settings[ $prefix . $key ] );
	}

	if ( ! $ready ) {
		$percent = 0;
	}

	return $percent;
}

/**
 * Aplica el descuento al elegir el pago en línea.
 *
 * @param WC_Cart $cart Carrito.
 */
function hadara_online_discount_fee( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}

	$percent = hadara_online_discount_percent();

	if ( ! $percent || ! WC()->session || HADARA_ONLINE_GATEWAY !== WC()->session->get( 'chosen_payment_method' ) ) {
		return;
	}

	$base = (float) $cart->get_subtotal() - (float) $cart->get_discount_total();

	if ( $base <= 0 ) {
		return;
	}

	$cart->add_fee(
		/* translators: %d: porcentaje. */
		sprintf( __( 'Descuento por pago en línea (%d%%)', 'hadara' ), $percent ),
		-round( $base * $percent / 100 ),
		false
	);
}
add_action( 'woocommerce_cart_calculate_fees', 'hadara_online_discount_fee' );

/**
 * Textos de los métodos de pago y del botón según el método elegido.
 *
 * WooCommerce cambia el texto del botón con el atributo order_button_text de
 * cada pasarela al seleccionarla.
 *
 * @param WC_Payment_Gateway[] $gateways Pasarelas disponibles.
 * @return WC_Payment_Gateway[]
 */
function hadara_payment_labels( $gateways ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $gateways;
	}

	$percent = hadara_online_discount_percent();

	if ( isset( $gateways['cod'] ) ) {
		$gateways['cod']->order_button_text = __( 'Confirmar pedido · Pago contra entrega', 'hadara' );
	}

	if ( isset( $gateways[ HADARA_ONLINE_GATEWAY ] ) ) {
		$gateways[ HADARA_ONLINE_GATEWAY ]->order_button_text = __( 'Pagar en línea con Wompi', 'hadara' );

		if ( $percent && false === strpos( $gateways[ HADARA_ONLINE_GATEWAY ]->title, '%' ) ) {
			/* translators: %d: porcentaje. */
			$gateways[ HADARA_ONLINE_GATEWAY ]->title = sprintf( __( 'Pago en línea: tarjeta, PSE o Nequi (ahorra %d%%)', 'hadara' ), $percent );
		}
	}

	return $gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'hadara_payment_labels', 20 );

/**
 * Precio pagando en línea, para mostrarlo en la página de venta.
 *
 * @param WC_Product $product Producto.
 * @return string Texto listo para mostrar, o vacío.
 */
function hadara_online_price_text( $product ) {
	$percent = hadara_online_discount_percent();

	if ( ! $percent ) {
		return '';
	}

	$price = $product->is_type( 'variable' ) ? (float) $product->get_variation_price( 'min', true ) : (float) wc_get_price_to_display( $product );

	if ( $price <= 0 ) {
		return '';
	}

	return sprintf(
		/* translators: 1: «desde» para productos con tallas, 2: precio, 3: porcentaje. */
		__( 'O %1$s%2$s pagando en línea (%3$d%% de descuento)', 'hadara' ),
		$product->is_type( 'variable' ) && $product->get_variation_price( 'min' ) !== $product->get_variation_price( 'max' ) ? __( 'desde ', 'hadara' ) : '',
		wp_strip_all_tags( wc_price( $price - round( $price * $percent / 100 ) ) ),
		$percent
	);
}

/**
 * Pregunta frecuente sobre los medios de pago cuando hay pago en línea.
 *
 * @param array      $data    Datos de la página de venta.
 * @param WC_Product $product Producto.
 * @return array
 */
function hadara_online_discount_sales_data( $data, $product ) {
	$data['online_price'] = hadara_online_price_text( $product );
	$percent              = hadara_online_discount_percent();

	if ( $percent ) {
		foreach ( $data['faq'] as $index => $item ) {
			if ( __( '¿Cómo pago?', 'hadara' ) === $item['q'] ) {
				$data['faq'][ $index ]['a'] = sprintf(
					/* translators: %d: porcentaje. */
					__( 'Como prefiera: en efectivo al recibir el pedido, o en línea con tarjeta, PSE o Nequi a través de Wompi, con %d%% de descuento.', 'hadara' ),
					$percent
				);
			}
		}
	}

	return $data;
}
add_filter( 'hadara_sales_data', 'hadara_online_discount_sales_data', 20, 2 );
