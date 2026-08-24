<?php
/**
 * Busca imágenes huérfanas en uploads: archivos que ya no pertenecen a ningún
 * elemento de la biblioteca de medios (restos de las demos y de la primera
 * importación en PNG).
 *
 * Uso: wp eval-file _tools/orphans.php [borrar]
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 'Ejecutar con WP-CLI.' );
}

$delete = in_array( 'borrar', (array) $args, true );

$uploads  = wp_get_upload_dir();
$base     = wp_normalize_path( $uploads['basedir'] );
$in_use   = array();
$attached = get_posts(
	array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

foreach ( $attached as $attachment_id ) {
	$file = get_post_meta( $attachment_id, '_wp_attached_file', true );

	if ( ! $file ) {
		continue;
	}

	$in_use[ $base . '/' . $file ] = true;

	$meta = wp_get_attachment_metadata( $attachment_id );
	$dir  = trailingslashit( $base . '/' . dirname( $file ) );

	if ( ! empty( $meta['original_image'] ) ) {
		$in_use[ $dir . $meta['original_image'] ] = true;
	}

	foreach ( (array) ( $meta['sizes'] ?? array() ) as $size ) {
		if ( ! empty( $size['file'] ) ) {
			$in_use[ $dir . $size['file'] ] = true;
		}
	}
}

$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS ) );
$orphans  = array();
$bytes    = 0;

foreach ( $iterator as $item ) {
	if ( ! $item->isFile() ) {
		continue;
	}

	$path = wp_normalize_path( $item->getPathname() );

	if ( ! preg_match( '/\.(png|jpe?g|webp|gif|avif|svg)$/i', $path ) || isset( $in_use[ $path ] ) ) {
		continue;
	}

	$orphans[] = $path;
	$bytes    += $item->getSize();
}

WP_CLI::log( sprintf( 'Huérfanas: %d archivos, %s MB', count( $orphans ), number_format( $bytes / 1048576, 1 ) ) );

$by_dir = array();

foreach ( $orphans as $path ) {
	$key            = str_replace( $base . '/', '', dirname( $path ) );
	$by_dir[ $key ] = ( $by_dir[ $key ] ?? 0 ) + 1;
}

foreach ( $by_dir as $dir => $count ) {
	WP_CLI::log( sprintf( '  %s: %d', $dir, $count ) );
}

if ( ! $delete ) {
	WP_CLI::success( 'Solo inventario, no se borró nada.' );
	return;
}

foreach ( $orphans as $path ) {
	wp_delete_file( $path );
}

WP_CLI::success( 'Imágenes huérfanas eliminadas.' );
