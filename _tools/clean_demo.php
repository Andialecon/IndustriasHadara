<?php
/**
 * Vacía la papelera y retira los medios y plantillas que dejó la demo de tienda
 * (Blocksy Modern Shop + Elementor). Los archivos del catálogo se reconocen por
 * la meta «_hadara_source» y no se tocan.
 *
 * Uso: wp eval-file _tools/clean_demo.php [contar]
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 'Ejecutar con WP-CLI.' );
}

$dry_run = in_array( 'contar', (array) $args, true );

$trashed = get_posts(
	array(
		'post_type'      => 'any',
		'post_status'    => 'trash',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

$elementor = get_posts(
	array(
		'post_type'      => array( 'elementor_library', 'e-floating-buttons', 'elementor_snippet', 'elementor_font', 'elementor_icons' ),
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

$attachments = get_posts(
	array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => '_hadara_source',
				'compare' => 'NOT EXISTS',
			),
		),
	)
);

$bytes = 0;

foreach ( $attachments as $attachment_id ) {
	$file = get_attached_file( $attachment_id );

	if ( $file && file_exists( $file ) ) {
		$bytes += filesize( $file );
	}
}

WP_CLI::log(
	sprintf(
		'Papelera: %d · Plantillas Elementor: %d · Medios de la demo: %d (%s MB de originales)',
		count( $trashed ),
		count( $elementor ),
		count( $attachments ),
		number_format( $bytes / 1048576, 1 )
	)
);

if ( $dry_run ) {
	WP_CLI::success( 'Solo conteo, no se borró nada.' );
	return;
}

foreach ( array_merge( $trashed, $elementor ) as $post_id ) {
	wp_delete_post( $post_id, true );
}

foreach ( $attachments as $attachment_id ) {
	wp_delete_attachment( $attachment_id, true );
}

WP_CLI::success( 'Papelera vaciada y medios de la demo eliminados.' );
