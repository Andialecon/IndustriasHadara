<?php
/**
 * Importa el catálogo curado (líneas, productos e imágenes) y deja las páginas,
 * los menús y las opciones del sitio listas para el tema hadara.
 *
 * Uso: wp eval-file _tools/import.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 'Ejecutar con WP-CLI.' );
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$base    = dirname( __DIR__ ) . '/_tools/out';
$catalog = json_decode( file_get_contents( $base . '/catalog.json' ), true );

if ( empty( $catalog['products'] ) ) {
	WP_CLI::error( 'No se pudo leer catalog.json' );
}

/**
 * Sube un archivo local a la biblioteca de medios, reutilizándolo si ya existe.
 *
 * Si existe la versión WebP del archivo se usa esa: pesa una décima parte que el
 * PNG extraído del catálogo y conserva la transparencia.
 */
function hadara_import_image( $path, $title, $alt ) {
	$webp = preg_replace( '#(.*)[/\\\\]img[/\\\\]([^/\\\\]+)\.(png|jpe?g)$#i', '$1/webp/$2.webp', $path );

	if ( $webp !== $path && file_exists( $webp ) ) {
		$path = $webp;
	}

	$name = basename( $path );

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_hadara_source',
			'meta_value'     => $name,
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$upload = wp_upload_bits( $name, null, file_get_contents( $path ) );

	if ( ! empty( $upload['error'] ) ) {
		WP_CLI::warning( $name . ': ' . $upload['error'] );
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $upload['type'],
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $attachment_id, '_hadara_source', $name );

	return (int) $attachment_id;
}

/* ------------------------------------------------------------------ líneas */

$term_ids = array();

foreach ( $catalog['categories'] as $index => $category ) {
	$term = get_term_by( 'slug', $category['slug'], 'linea' );

	if ( $term ) {
		wp_update_term(
			$term->term_id,
			'linea',
			array(
				'name'        => $category['name'],
				'description' => $category['intro'],
			)
		);
		$term_id = $term->term_id;
	} else {
		$created = wp_insert_term(
			$category['name'],
			'linea',
			array(
				'slug'        => $category['slug'],
				'description' => $category['intro'],
			)
		);

		if ( is_wp_error( $created ) ) {
			WP_CLI::warning( $category['slug'] . ': ' . $created->get_error_message() );
			continue;
		}

		$term_id = $created['term_id'];
	}

	update_term_meta( $term_id, '_hadara_seo_title', $category['title'] );
	update_term_meta( $term_id, '_hadara_order', $index + 1 );
	update_term_meta( $term_id, '_hadara_menu_label', $category['menu'] );

	$term_ids[ $category['slug'] ] = (int) $term_id;
}

WP_CLI::log( sprintf( 'Líneas: %d', count( $term_ids ) ) );

/* --------------------------------------------------------------- productos */

$line_thumbs = array();
$imported    = 0;

foreach ( $catalog['products'] as $index => $product ) {
	$slug     = sanitize_title( $product['name'] );
	$existing = get_page_by_path( $slug, OBJECT, 'producto' );

	$postarr = array(
		'post_type'    => 'producto',
		'post_status'  => 'publish',
		'post_title'   => $product['name'],
		'post_name'    => $slug,
		'post_excerpt' => $product['excerpt'],
		'post_content' => $product['desc'],
		'menu_order'   => $index + 1,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id       = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( $slug . ': ' . $post_id->get_error_message() );
		continue;
	}

	wp_set_object_terms( $post_id, array( $term_ids[ $product['cat'] ] ), 'linea', false );
	update_post_meta( $post_id, '_hadara_specs', wp_slash( (array) $product['specs'] ) );

	$thumb_id = hadara_import_image( $base . '/img/' . $product['img'], $product['name'], $product['name'] );

	if ( $thumb_id ) {
		set_post_thumbnail( $post_id, $thumb_id );

		if ( empty( $line_thumbs[ $product['cat'] ] ) ) {
			$line_thumbs[ $product['cat'] ] = $thumb_id;
		}
	}

	$gallery = array();

	foreach ( (array) $product['gallery'] as $file ) {
		$gallery_id = hadara_import_image( $base . '/img/' . $file, $product['name'], $product['name'] );

		if ( $gallery_id ) {
			$gallery[] = $gallery_id;
		}
	}

	if ( $gallery ) {
		update_post_meta( $post_id, '_hadara_gallery', $gallery );
	} else {
		delete_post_meta( $post_id, '_hadara_gallery' );
	}

	++$imported;
}

foreach ( $line_thumbs as $slug => $thumb_id ) {
	if ( isset( $term_ids[ $slug ] ) ) {
		update_term_meta( $term_ids[ $slug ], '_hadara_thumb_id', $thumb_id );
	}
}

WP_CLI::log( sprintf( 'Productos: %d', $imported ) );

/* -------------------------------------------------------- logo y favicon */

$theme_img = get_theme_file_path( 'assets/img' );

$logo_dark  = hadara_import_image( $theme_img . '/logo-industrias-hadara.png', 'Industrias Hadara', 'Industrias Hadara' );
$logo_light = hadara_import_image( $theme_img . '/logo-industrias-hadara-claro.png', 'Industrias Hadara', 'Industrias Hadara' );
$icon       = hadara_import_image( $theme_img . '/icono-industrias-hadara.png', 'Industrias Hadara', 'Industrias Hadara' );

if ( $logo_dark ) {
	set_theme_mod( 'hadara_logo_dark', $logo_dark );
	set_theme_mod( 'custom_logo', $logo_dark );
}

if ( $logo_light ) {
	set_theme_mod( 'hadara_logo_light', $logo_light );
}

if ( $icon ) {
	update_option( 'site_icon', $icon );
}

WP_CLI::success( 'Catálogo importado.' );
