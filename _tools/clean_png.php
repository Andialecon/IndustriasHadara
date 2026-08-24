<?php
/**
 * Borra de la biblioteca los PNG del catálogo que ya fueron sustituidos por sus
 * equivalentes en WebP. No toca el logo ni el favicon.
 *
 * Uso: wp eval-file _tools/clean_png.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 'Ejecutar con WP-CLI.' );
}

$attachments = get_posts(
	array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => '_hadara_source',
				'value'   => '\\.png$',
				'compare' => 'REGEXP',
			),
		),
	)
);

$deleted = 0;

foreach ( $attachments as $attachment_id ) {
	$source = (string) get_post_meta( $attachment_id, '_hadara_source', true );

	// El logo y el favicon siguen siendo PNG.
	if ( ! preg_match( '/^p\d+-\d+\.png$/', $source ) ) {
		continue;
	}

	if ( wp_delete_attachment( $attachment_id, true ) ) {
		++$deleted;
	}
}

WP_CLI::success( sprintf( 'PNG del catálogo eliminados: %d', $deleted ) );
