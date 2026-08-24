<?php
/**
 * Sustituye en la biblioteca de medios el logotipo provisional por el oficial y
 * vuelve a fijar el logotipo del tema y el icono del sitio.
 *
 * Uso: wp eval-file _tools/brand_import.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 'Ejecutar con WP-CLI.' );
}

require_once ABSPATH . 'wp-admin/includes/image.php';

$files = array(
	'logo-industrias-hadara.png'       => 'hadara_logo_dark',
	'logo-industrias-hadara-claro.png' => 'hadara_logo_light',
	'icono-industrias-hadara.png'      => 'site_icon',
);

foreach ( array_keys( $files ) as $name ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_hadara_source',
			'meta_value'     => $name,
		)
	);

	foreach ( $existing as $attachment_id ) {
		wp_delete_attachment( $attachment_id, true );
		WP_CLI::log( 'Reemplazado: ' . $name . ' (#' . $attachment_id . ')' );
	}
}

foreach ( $files as $name => $target ) {
	$path = get_theme_file_path( 'assets/img/' . $name );

	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( $name . ': no está en el tema.' );
		continue;
	}

	$upload = wp_upload_bits( $name, null, file_get_contents( $path ) );

	if ( ! empty( $upload['error'] ) ) {
		WP_CLI::warning( $name . ': ' . $upload['error'] );
		continue;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $upload['type'],
			'post_title'     => hadara_brand( 'name' ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		WP_CLI::warning( $name . ': no se pudo registrar en la biblioteca.' );
		continue;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', hadara_brand( 'name' ) );
	update_post_meta( $attachment_id, '_hadara_source', $name );

	if ( 'site_icon' === $target ) {
		update_option( 'site_icon', $attachment_id );
	} else {
		set_theme_mod( $target, $attachment_id );

		if ( 'hadara_logo_dark' === $target ) {
			set_theme_mod( 'custom_logo', $attachment_id );
		}
	}

	WP_CLI::log( sprintf( '%s -> #%d', $name, $attachment_id ) );
}

WP_CLI::success( 'Marca oficial instalada.' );
