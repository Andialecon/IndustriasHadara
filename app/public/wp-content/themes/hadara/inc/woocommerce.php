<?php
/**
 * Integración con WooCommerce para la tienda en línea (productos de Dropi).
 *
 * El catálogo B2B propio sigue en el tipo «producto» (/catalogo/); WooCommerce
 * vive aparte en /tienda/. Este archivo solo se carga si WooCommerce está activo.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Indica si la vista actual pertenece a la tienda.
 *
 * @return bool
 */
function hadara_is_shop_view() {
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
}

/**
 * Soporte del tema para WooCommerce.
 */
function hadara_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 420,
			'single_image_width'    => 800,
			'product_grid'          => array(
				'default_columns' => 4,
				'default_rows'    => 3,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'hadara_woocommerce_setup' );

/*
 * El título y la descripción de la tienda y de las categorías los pinta la
 * cabecera del tema (woocommerce.php); las migas de pan, header.php.
 */
add_filter( 'woocommerce_show_page_title', '__return_false' );
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Hoja de estilos de la tienda, solo en sus páginas.
 */
function hadara_woocommerce_assets() {
	if ( ! hadara_is_shop_view() ) {
		return;
	}

	wp_enqueue_style(
		'hadara-woocommerce',
		get_theme_file_uri( 'assets/css/woocommerce.css' ),
		array( 'hadara-main' ),
		HADARA_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'hadara_woocommerce_assets', 20 );

/**
 * Fuera de la tienda no se cargan los estilos de WooCommerce.
 *
 * @param array $styles Hojas de WooCommerce.
 * @return array
 */
function hadara_woocommerce_styles( $styles ) {
	return hadara_is_shop_view() ? $styles : array();
}
add_filter( 'woocommerce_enqueue_styles', 'hadara_woocommerce_styles' );

/**
 * Fuera de la tienda tampoco hacen falta sus scripts: el contador del carrito
 * de la cabecera se pinta en el servidor.
 */
function hadara_woocommerce_dequeue_scripts() {
	if ( hadara_is_shop_view() ) {
		return;
	}

	foreach ( array( 'woocommerce', 'wc-add-to-cart', 'wc-cart-fragments', 'wc-order-attribution', 'sourcebuster-js' ) as $handle ) {
		wp_dequeue_script( $handle );
	}

	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'woocommerce-inline' );
}
add_action( 'wp_enqueue_scripts', 'hadara_woocommerce_dequeue_scripts', 100 );

/**
 * Enlace al carrito para la cabecera.
 *
 * @return string
 */
function hadara_cart_link() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	$count = (int) WC()->cart->get_cart_contents_count();

	return sprintf(
		'<a class="cart-link" href="%1$s" aria-label="%2$s">%3$s<span class="cart-link__label">%4$s</span><span class="cart-link__count"%5$s>%6$d</span></a>',
		esc_url( wc_get_cart_url() ),
		esc_attr( sprintf( /* translators: %d: productos en el carrito. */ _n( 'Carrito, %d producto', 'Carrito, %d productos', $count, 'hadara' ), $count ) ),
		hadara_icon( 'cart' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Carrito', 'hadara' ),
		$count ? '' : ' hidden',
		$count
	);
}

/**
 * Actualiza el contador de la cabecera al añadir productos por AJAX.
 *
 * @param array $fragments Fragmentos HTML.
 * @return array
 */
function hadara_cart_fragment( $fragments ) {
	$fragments['a.cart-link'] = hadara_cart_link();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'hadara_cart_fragment' );

/**
 * Botones de la tienda con el estilo del tema.
 *
 * @param array $args Argumentos del botón «Añadir al carrito».
 * @return array
 */
function hadara_woocommerce_loop_button_args( $args ) {
	$args['class'] = trim( ( isset( $args['class'] ) ? $args['class'] : '' ) . ' btn btn--accent btn--sm' );

	return $args;
}
add_filter( 'woocommerce_loop_add_to_cart_args', 'hadara_woocommerce_loop_button_args' );

/**
 * Campos de la compra pensados para despachos de Dropi en Colombia.
 *
 * Dropi necesita un teléfono que conteste el transportador, el departamento y
 * la ciudad. La empresa no aplica a ventas al detal y el código postal casi
 * nadie lo conoce, así que se ocultan.
 *
 * @param array $fields Campos de facturación y envío.
 * @return array
 */
function hadara_checkout_fields( $fields ) {
	unset( $fields['billing']['billing_company'], $fields['shipping']['shipping_company'] );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'hadara_checkout_fields', 20 );

/**
 * Etiquetas y orden de los campos de dirección.
 *
 * Van aquí y no en woocommerce_checkout_fields porque el script de direcciones
 * de WooCommerce vuelve a aplicar en el navegador las etiquetas y prioridades
 * de estos campos por defecto.
 *
 * @param array $fields Campos de dirección por defecto.
 * @return array
 */
function hadara_default_address_fields( $fields ) {
	if ( isset( $fields['phone'] ) ) {
		$fields['phone']['required'] = true;
		$fields['phone']['label']    = __( 'Celular (para coordinar la entrega)', 'hadara' );
		$fields['phone']['priority'] = 25;
	}

	if ( isset( $fields['address_1'] ) ) {
		$fields['address_1']['placeholder'] = __( 'Calle, carrera, número y barrio', 'hadara' );
	}

	return $fields;
}
add_filter( 'woocommerce_default_address_fields', 'hadara_default_address_fields', 20 );

/**
 * Etiquetas de dirección para Colombia: «Departamento» y «Ciudad o municipio».
 *
 * @param array $locale Configuración regional de direcciones.
 * @return array
 */
function hadara_colombia_address_locale( $locale ) {
	$locale['CO']['state']['label']    = __( 'Departamento', 'hadara' );
	$locale['CO']['state']['required'] = true;
	$locale['CO']['city']['label']     = __( 'Ciudad o municipio', 'hadara' );
	$locale['CO']['postcode']['required'] = false;
	$locale['CO']['postcode']['hidden']   = true;

	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'hadara_colombia_address_locale' );

/*
 * ---------------------------------------------------------------------------
 * Ajustes para Dropify (integración con Dropi)
 * ---------------------------------------------------------------------------
 *
 * Van en el tema y no en el plugin para que sobrevivan a sus actualizaciones.
 */

/*
 * Dropify descarga las fotos con file_get_contents(), que por defecto no envía
 * User-Agent, y el CDN de Dropi (CloudFront) responde 403 a esas peticiones: el
 * plugin guardaba entonces archivos de 0 bytes y la tienda quedaba sin imagen.
 */
if ( ! ini_get( 'user_agent' ) ) {
	ini_set( 'user_agent', 'Mozilla/5.0 (compatible; IndustriasHadara/1.0; WordPress)' ); // phpcs:ignore WordPress.PHP.IniSet.Risky
}

/**
 * Indica si la petición actual es una importación de productos de Dropify.
 *
 * @return bool
 */
function hadara_is_dropi_import() {
	return wp_doing_ajax() && isset( $_REQUEST['action'] ) && 'import' === $_REQUEST['action']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Añade a la galería del producto las fotos que importa Dropify.
 *
 * El plugin usa la primera foto como imagen destacada y deja las demás sueltas
 * en la biblioteca de medios.
 *
 * @param int $attachment_id Adjunto recién creado.
 */
function hadara_dropi_gallery( $attachment_id ) {
	if ( ! hadara_is_dropi_import() ) {
		return;
	}

	$product_id = (int) wp_get_post_parent_id( $attachment_id );

	if ( ! $product_id || 'product' !== get_post_type( $product_id ) || ! has_post_thumbnail( $product_id ) ) {
		return;
	}

	hadara_add_to_product_gallery( $product_id, $attachment_id );
}
add_action( 'add_attachment', 'hadara_dropi_gallery' );

/**
 * Añade un adjunto a la galería de un producto si no está ya.
 *
 * @param int $product_id    Producto.
 * @param int $attachment_id Adjunto.
 */
function hadara_add_to_product_gallery( $product_id, $attachment_id ) {
	if ( (int) get_post_thumbnail_id( $product_id ) === (int) $attachment_id ) {
		return;
	}

	$gallery = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $product_id, '_product_image_gallery', true ) ) ) );

	if ( ! in_array( (int) $attachment_id, $gallery, true ) ) {
		$gallery[] = (int) $attachment_id;
		update_post_meta( $product_id, '_product_image_gallery', implode( ',', $gallery ) );
	}
}

/*
 * Las fotos de los proveedores llegan como PNG de hasta 10 MB: se limita el
 * original a 1600 px y las versiones que usa la tienda se guardan en WebP.
 */
add_filter(
	'big_image_size_threshold',
	static function () {
		return 1600;
	}
);

/**
 * Formato de salida de las imágenes generadas por WordPress.
 *
 * @param array $formats Formatos por tipo MIME.
 * @return array
 */
function hadara_image_output_format( $formats ) {
	$formats['image/png']  = 'image/webp';
	$formats['image/jpeg'] = 'image/webp';

	return $formats;
}
add_filter( 'image_editor_output_format', 'hadara_image_output_format' );

/**
 * Los productos de Dropi no siempre traen tallas como variaciones: las notas
 * del pedido llegan a Dropi, así que se pide ahí la talla y el color.
 *
 * @param array $fields Campos de la compra.
 * @return array
 */
function hadara_order_notes_field( $fields ) {
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'Talla, color e indicaciones de entrega', 'hadara' );
		$fields['order']['order_comments']['placeholder'] = __( 'Ej.: talla 10, azul claro. Entregar en portería.', 'hadara' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'hadara_order_notes_field', 30 );

/**
 * La casilla «Acepto los términos» solo aparece cuando la página de términos
 * está publicada: WooCommerce la muestra aunque siga en borrador, y el enlace
 * llevaría a un error 404.
 *
 * @param bool $show Si se muestra.
 * @return bool
 */
function hadara_show_terms_only_if_published( $show ) {
	$page_id = wc_terms_and_conditions_page_id();

	return $show && $page_id && 'publish' === get_post_status( $page_id );
}
add_filter( 'woocommerce_checkout_show_terms', 'hadara_show_terms_only_if_published' );

require_once get_theme_file_path( 'inc/sales-page.php' );
require_once get_theme_file_path( 'inc/dropi-stock.php' );
