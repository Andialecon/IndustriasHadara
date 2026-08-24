<?php
/**
 * Datos de la marca y del contacto comercial.
 *
 * Este archivo es el único lugar que hay que editar para cambiar el teléfono,
 * el WhatsApp, el correo o la ubicación: el resto del tema y el marcado
 * estructurado leen desde aquí.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Devuelve un dato de la marca, o el arreglo completo si no se pide una clave.
 *
 * @param string|null $key Clave solicitada.
 * @return mixed
 */
function hadara_brand( $key = null ) {
	static $brand = null;

	if ( null === $brand ) {
		$brand = array(
			'name'        => 'Industrias Hadara',
			'tagline'     => 'Dotación industrial: ropa de trabajo y calzado de seguridad',

			// Número de WhatsApp en formato internacional, solo dígitos.
			'whatsapp'    => '573052317979',
			'phone_label' => '+57 305 231 7979',

			// Datos opcionales: si se dejan vacíos, el tema los oculta y no los
			// publica en el marcado estructurado.
			'email'       => '',
			'street'      => '',
			'city'        => '',
			'region'      => '',
			'postal_code' => '',
			'country'     => 'CO',

			// Texto visible en la página de contacto.
			'hours_label' => 'Lunes a viernes de 8:00 a.m. a 6:00 p.m. y sábados de 8:00 a.m. a 12:00 m.',

			// Horario estructurado para schema.org.
			'opening_hours' => array(
				array(
					'days'  => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
					'opens' => '08:00',
					'closes'=> '18:00',
				),
				array(
					'days'  => array( 'Saturday' ),
					'opens' => '08:00',
					'closes'=> '12:00',
				),
			),

			// Cobertura comercial (schema areaServed).
			'area_served' => 'Colombia',

			'facebook'    => '',
			'instagram'   => '',
			'linkedin'    => '',
		);

		/**
		 * Permite sobreescribir los datos de la marca desde un plugin.
		 *
		 * @param array $brand Datos de la marca.
		 */
		$brand = apply_filters( 'hadara_brand', $brand );
	}

	if ( null === $key ) {
		return $brand;
	}

	return isset( $brand[ $key ] ) ? $brand[ $key ] : '';
}

/**
 * Construye un enlace de WhatsApp con un mensaje previamente redactado.
 *
 * @param string $message Mensaje que verá el asesor.
 * @return string
 */
function hadara_whatsapp_url( $message = '' ) {
	$number = preg_replace( '/\D/', '', (string) hadara_brand( 'whatsapp' ) );

	if ( '' === $number ) {
		return '';
	}

	if ( '' === $message ) {
		$message = sprintf(
			/* translators: %s: nombre de la empresa. */
			__( 'Hola %s, quiero cotizar dotación industrial para mi empresa.', 'hadara' ),
			hadara_brand( 'name' )
		);
	}

	return 'https://wa.me/' . $number . '?text=' . rawurlencode( $message );
}

/**
 * Enlace de WhatsApp para cotizar un producto concreto.
 *
 * @param int|WP_Post|null $post Producto.
 * @return string
 */
function hadara_product_whatsapp_url( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return hadara_whatsapp_url();
	}

	return hadara_whatsapp_url(
		sprintf(
			/* translators: 1: nombre del producto, 2: enlace del producto. */
			__( 'Hola, quiero cotizar este producto: %1$s (%2$s). ¿Me confirman tallas disponibles y tiempo de entrega?', 'hadara' ),
			get_the_title( $post ),
			get_permalink( $post )
		)
	);
}

/**
 * Indica si hay datos suficientes para mostrar una dirección.
 *
 * @return bool
 */
function hadara_has_address() {
	return '' !== hadara_brand( 'street' ) || '' !== hadara_brand( 'city' );
}

/**
 * Dirección en una sola línea.
 *
 * @return string
 */
function hadara_address_line() {
	$parts = array_filter(
		array(
			hadara_brand( 'street' ),
			hadara_brand( 'city' ),
			hadara_brand( 'region' ),
		)
	);

	return implode( ', ', $parts );
}

/**
 * Datos del logotipo para schema y redes (URL absoluta + medidas).
 *
 * @param string $variant «dark» o «light».
 * @return array{url:string,width:int,height:int,alt:string}|null
 */
function hadara_logo_image_data( $variant = 'dark' ) {
	$attachment_id = (int) get_theme_mod( 'light' === $variant ? 'hadara_logo_light' : 'hadara_logo_dark' );

	if ( $attachment_id ) {
		$src = wp_get_attachment_image_src( $attachment_id, 'full' );

		if ( $src ) {
			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => hadara_brand( 'name' ),
			);
		}
	}

	$file = 'light' === $variant ? 'logo-industrias-hadara-claro.png' : 'logo-industrias-hadara.png';
	$path = get_theme_file_path( 'assets/img/' . $file );

	if ( ! file_exists( $path ) ) {
		return null;
	}

	$size = @getimagesize( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	return array(
		'url'    => get_theme_file_uri( 'assets/img/' . $file ),
		'width'  => $size ? (int) $size[0] : 857,
		'height' => $size ? (int) $size[1] : 169,
		'alt'    => hadara_brand( 'name' ),
	);
}
