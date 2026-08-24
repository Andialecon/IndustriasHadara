<?php
/**
 * Deja en el sitio solo los productos de la lista de precios disponibles
 * (mayo 2026) y elimina el resto del catálogo.
 *
 * Uso: wp eval-file _tools/prune_catalog.php
 *      php _tools/prune_catalog.php   (carga wp-load.php si no hay WP-CLI)
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/app/public/wp-load.php';
}

$keep_slugs = array(
	// Jeans
	'jean-caballero-14-onzas',
	'jean-caballero-14-onzas-dielectrico',
	'jean-caballero-licrado',
	'jean-dama-strech-clasico',
	'jean-dama-dr-pretina-alta-slim',
	'jean-caballero-negro-14-onzas',
	// Pantalones
	'pantalon-en-dril-cuatro-bolsillos',
	// Camisas y chaquetas
	'camisa-jean-7-onzas',
	'camisa-jean-para-soldador-14-onzas',
	'chaqueta-jean-para-soldador-14-onzas',
	'camisa-en-dril',
	'camisa-oxford-caballero-y-dama',
	// Overoles
	'overol-en-dril-enterizo',
	'overol-en-dril-de-dos-piezas',
	'overol-en-dril-tipo-piloto',
	// Chalecos
	'chaleco-en-dril-tipo-periodista',
	// Capuchas
	'capucha-en-dril',
	'capucha-en-jean',
	// Calzado
	'bota-de-seguridad-blanca',
);

$keep_slugs = array_flip( $keep_slugs );

$products = get_posts(
	array(
		'post_type'      => 'producto',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	)
);

$deleted = 0;
$kept    = 0;

foreach ( $products as $product ) {
	if ( isset( $keep_slugs[ $product->post_name ] ) ) {
		++$kept;
		continue;
	}

	wp_delete_post( $product->ID, true );
	++$deleted;

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( 'Eliminado: ' . $product->post_title . ' (' . $product->post_name . ')' );
	}
}

// Líneas sin productos publicados.
$lines = get_terms(
	array(
		'taxonomy'   => 'linea',
		'hide_empty' => false,
	)
);

$lines_removed = 0;

if ( ! is_wp_error( $lines ) ) {
	foreach ( $lines as $line ) {
		if ( (int) $line->count > 0 ) {
			continue;
		}

		wp_delete_term( $line->term_id, 'linea' );
		++$lines_removed;

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::log( 'Línea vacía eliminada: ' . $line->name );
		}
	}
}

// Menús: quitar enlaces a líneas que ya no existen.
$locations = get_nav_menu_locations();

foreach ( $locations as $location => $menu_id ) {
	if ( ! $menu_id ) {
		continue;
	}

	$items = wp_get_nav_menu_items( $menu_id );

	if ( ! $items ) {
		continue;
	}

	foreach ( $items as $item ) {
		if ( 'taxonomy' !== $item->type || 'linea' !== $item->object ) {
			continue;
		}

		$term = get_term( (int) $item->object_id, 'linea' );

		if ( ! $term || is_wp_error( $term ) ) {
			wp_delete_post( $item->ID, true );
		}
	}
}

wp_cache_flush();

$message = sprintf(
	'Catálogo reducido: %d referencias activas, %d eliminadas, %d líneas vacías retiradas.',
	$kept,
	$deleted,
	$lines_removed
);

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::success( $message );
} else {
	echo $message . PHP_EOL;
}
