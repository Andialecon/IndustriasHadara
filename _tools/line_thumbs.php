<?php
/**
 * Fija la imagen que representa cada línea cuando la primera referencia de la
 * línea no es la más reconocible.
 *
 * Uso: wp eval-file _tools/line_thumbs.php
 */

$choices = array(
	'impermeables-y-cuarto-frio' => 'impermeable-en-pvc-de-cuatro-piezas',
	'dotacion-soldador'          => 'chaqueta-en-vaqueta-para-soldador',
	'mangas-capuchas-delantales' => 'manga-en-jean',
	'trabajo-en-alturas'         => 'arrestador-de-caida-con-eslinga-incorporada',
);

foreach ( $choices as $line_slug => $product_slug ) {
	$term    = get_term_by( 'slug', $line_slug, 'linea' );
	$product = get_page_by_path( $product_slug, OBJECT, 'producto' );

	if ( ! $term || ! $product ) {
		WP_CLI::warning( $line_slug . ': no se encontró la línea o el producto.' );
		continue;
	}

	$thumb_id = get_post_thumbnail_id( $product );

	if ( ! $thumb_id ) {
		WP_CLI::warning( $product_slug . ': sin imagen.' );
		continue;
	}

	update_term_meta( $term->term_id, '_hadara_thumb_id', $thumb_id );
	WP_CLI::log( $line_slug . ' -> ' . $product_slug );
}

WP_CLI::success( 'Imágenes de las líneas actualizadas.' );
