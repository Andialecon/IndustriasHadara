<?php
/**
 * Tipo de contenido «Producto» y taxonomía «Línea».
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra el tipo de contenido y la taxonomía del catálogo.
 */
function hadara_register_catalog() {
	register_post_type(
		'producto',
		array(
			'labels'             => array(
				'name'               => __( 'Productos', 'hadara' ),
				'singular_name'      => __( 'Producto', 'hadara' ),
				'add_new_item'       => __( 'Añadir producto', 'hadara' ),
				'edit_item'          => __( 'Editar producto', 'hadara' ),
				'new_item'           => __( 'Nuevo producto', 'hadara' ),
				'view_item'          => __( 'Ver producto', 'hadara' ),
				'search_items'       => __( 'Buscar productos', 'hadara' ),
				'not_found'          => __( 'No se encontraron productos', 'hadara' ),
				'all_items'          => __( 'Todos los productos', 'hadara' ),
				'menu_name'          => __( 'Catálogo', 'hadara' ),
			),
			'public'             => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-products',
			'menu_position'      => 20,
			'has_archive'        => 'catalogo',
			'rewrite'            => array(
				'slug'       => 'producto',
				'with_front' => false,
			),
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions', 'custom-fields' ),
			'taxonomies'         => array( 'linea' ),
			'hierarchical'       => false,
		)
	);

	register_taxonomy(
		'linea',
		array( 'producto' ),
		array(
			'labels'            => array(
				'name'          => __( 'Líneas', 'hadara' ),
				'singular_name' => __( 'Línea', 'hadara' ),
				'add_new_item'  => __( 'Añadir línea', 'hadara' ),
				'edit_item'     => __( 'Editar línea', 'hadara' ),
				'menu_name'     => __( 'Líneas', 'hadara' ),
			),
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
			'rewrite'           => array(
				'slug'       => 'linea',
				'with_front' => false,
			),
		)
	);

	register_term_meta(
		'linea',
		'_hadara_seo_title',
		array(
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => false,
			'description'  => __( 'Título alternativo para buscadores.', 'hadara' ),
		)
	);

	register_term_meta(
		'linea',
		'_hadara_seo_description',
		array(
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => false,
			'description'  => __( 'Meta description de la línea.', 'hadara' ),
		)
	);

	register_term_meta(
		'linea',
		'_hadara_order',
		array(
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => false,
			'description'  => __( 'Orden de la línea en el catálogo.', 'hadara' ),
		)
	);

	register_term_meta(
		'linea',
		'_hadara_thumb_id',
		array(
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => false,
			'description'  => __( 'Imagen que representa la línea.', 'hadara' ),
		)
	);
}
add_action( 'init', 'hadara_register_catalog' );

/**
 * Caja SEO del producto (title + meta description).
 */
function hadara_add_seo_box() {
	add_meta_box(
		'hadara-seo',
		__( 'SEO', 'hadara' ),
		'hadara_render_seo_box',
		'producto',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'hadara_add_seo_box' );

/**
 * Pinta la caja SEO del producto.
 *
 * @param WP_Post $post Producto.
 */
function hadara_render_seo_box( $post ) {
	wp_nonce_field( 'hadara_seo', 'hadara_seo_nonce' );

	$title = (string) get_post_meta( $post->ID, '_hadara_seo_title', true );
	$desc  = (string) get_post_meta( $post->ID, '_hadara_seo_description', true );
	?>
	<p>
		<label for="hadara_seo_title"><strong><?php esc_html_e( 'Título para buscadores', 'hadara' ); ?></strong></label>
		<input type="text" class="widefat" id="hadara_seo_title" name="hadara_seo_title" value="<?php echo esc_attr( $title ); ?>" maxlength="70">
		<span class="description"><?php esc_html_e( 'Si se deja vacío se usa el nombre del producto.', 'hadara' ); ?></span>
	</p>
	<p>
		<label for="hadara_seo_description"><strong><?php esc_html_e( 'Meta description', 'hadara' ); ?></strong></label>
		<textarea class="widefat" id="hadara_seo_description" name="hadara_seo_description" rows="4" maxlength="165"><?php echo esc_textarea( $desc ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Resumen de ~155 caracteres. Si se deja vacío se usa el extracto o el contenido.', 'hadara' ); ?></span>
	</p>
	<?php
}

/**
 * Guarda el SEO del producto.
 *
 * @param int $post_id Producto.
 */
function hadara_save_seo( $post_id ) {
	if ( ! isset( $_POST['hadara_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hadara_seo_nonce'] ), 'hadara_seo' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$title = isset( $_POST['hadara_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['hadara_seo_title'] ) ) : '';
	$desc  = isset( $_POST['hadara_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hadara_seo_description'] ) ) : '';

	if ( '' === $title ) {
		delete_post_meta( $post_id, '_hadara_seo_title' );
	} else {
		update_post_meta( $post_id, '_hadara_seo_title', $title );
	}

	if ( '' === $desc ) {
		delete_post_meta( $post_id, '_hadara_seo_description' );
	} else {
		update_post_meta( $post_id, '_hadara_seo_description', $desc );
	}
}
add_action( 'save_post_producto', 'hadara_save_seo' );

/**
 * Caja para las especificaciones técnicas del producto.
 */
function hadara_add_specs_box() {
	add_meta_box(
		'hadara-specs',
		__( 'Especificaciones técnicas', 'hadara' ),
		'hadara_render_specs_box',
		'producto',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'hadara_add_specs_box' );

/**
 * Pinta la caja de especificaciones.
 *
 * @param WP_Post $post Producto en edición.
 */
function hadara_render_specs_box( $post ) {
	wp_nonce_field( 'hadara_specs', 'hadara_specs_nonce' );

	$specs = hadara_get_specs( $post );
	$lines = array();

	foreach ( $specs as $label => $value ) {
		$lines[] = $label . ': ' . $value;
	}

	echo '<p>' . esc_html__( 'Una especificación por línea, con el formato «Clave: valor». Se muestran como tabla en la ficha del producto y en el marcado para buscadores.', 'hadara' ) . '</p>';
	printf(
		'<textarea name="hadara_specs" rows="8" style="width:100%%;font-family:monospace" placeholder="%s">%s</textarea>',
		esc_attr__( 'Tela: Jean 14 onzas', 'hadara' ),
		esc_textarea( implode( "\n", $lines ) )
	);
}

/**
 * Guarda las especificaciones.
 *
 * @param int $post_id Producto.
 */
function hadara_save_specs( $post_id ) {
	if ( ! isset( $_POST['hadara_specs_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hadara_specs_nonce'] ), 'hadara_specs' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$raw   = isset( $_POST['hadara_specs'] ) ? wp_unslash( $_POST['hadara_specs'] ) : '';
	$specs = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		if ( ! str_contains( $line, ':' ) ) {
			continue;
		}

		list( $label, $value ) = explode( ':', $line, 2 );
		$label                 = sanitize_text_field( trim( $label ) );
		$value                 = sanitize_text_field( trim( $value ) );

		if ( '' !== $label && '' !== $value ) {
			$specs[ $label ] = $value;
		}
	}

	if ( $specs ) {
		update_post_meta( $post_id, '_hadara_specs', $specs );
	} else {
		delete_post_meta( $post_id, '_hadara_specs' );
	}
}
add_action( 'save_post_producto', 'hadara_save_specs' );

/**
 * Devuelve las especificaciones de un producto.
 *
 * @param int|WP_Post|null $post Producto.
 * @return array<string,string>
 */
function hadara_get_specs( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return array();
	}

	$specs = get_post_meta( $post->ID, '_hadara_specs', true );

	return is_array( $specs ) ? $specs : array();
}

/**
 * Caja para las imágenes adicionales del producto.
 *
 * En el editor de bloques aparece al expandir «Cajas meta» al final de la pantalla.
 */
function hadara_add_gallery_box() {
	add_meta_box(
		'hadara-gallery',
		__( 'Galería de imágenes', 'hadara' ),
		'hadara_render_gallery_box',
		'producto',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'hadara_add_gallery_box' );

/**
 * Pinta la caja de la galería.
 *
 * @param WP_Post $post Producto en edición.
 */
function hadara_render_gallery_box( $post ) {
	wp_nonce_field( 'hadara_gallery', 'hadara_gallery_nonce' );

	$ids = hadara_get_gallery( $post );
	?>
	<p class="description">
		<?php esc_html_e( 'Además de la imagen destacada (panel derecho), añada aquí otras vistas (espalda, detalle, colores). Aparecerán como miniaturas en la ficha.', 'hadara' ); ?>
	</p>

	<div class="hadara-gallery" data-hadara-gallery>
		<input type="hidden" name="hadara_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-hadara-gallery-input>

		<ul class="hadara-gallery__list" data-hadara-gallery-list>
			<?php foreach ( $ids as $id ) : ?>
				<?php hadara_gallery_item( $id ); ?>
			<?php endforeach; ?>
		</ul>

		<p class="hadara-gallery__empty" data-hadara-gallery-empty<?php echo $ids ? ' hidden' : ''; ?>>
			<?php esc_html_e( 'Todavía no hay imágenes adicionales.', 'hadara' ); ?>
		</p>

		<button type="button" class="button button-primary hadara-gallery__add" data-hadara-gallery-add>
			<?php esc_html_e( 'Añadir imágenes', 'hadara' ); ?>
		</button>

		<template data-hadara-gallery-template>
			<?php hadara_gallery_item( 0 ); ?>
		</template>
	</div>
	<?php
}

/**
 * Pinta una miniatura de la galería con sus controles.
 *
 * Se usa tanto para las imágenes ya guardadas como para la plantilla que
 * duplica el script al añadir imágenes nuevas.
 *
 * @param int $id Adjunto. Cero para la plantilla vacía.
 */
function hadara_gallery_item( $id ) {
	$image = $id ? wp_get_attachment_image( $id, 'thumbnail', false, array( 'alt' => '' ) ) : '<img src="" alt="">';
	?>
	<li class="hadara-gallery__item" data-id="<?php echo esc_attr( $id ? $id : '' ); ?>">
		<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- marcado del núcleo. ?>
		<span class="hadara-gallery__actions">
			<button type="button" class="button-link" data-hadara-gallery-move="-1" aria-label="<?php esc_attr_e( 'Mover antes', 'hadara' ); ?>">&larr;</button>
			<button type="button" class="button-link" data-hadara-gallery-move="1" aria-label="<?php esc_attr_e( 'Mover después', 'hadara' ); ?>">&rarr;</button>
			<button type="button" class="button-link hadara-gallery__remove" data-hadara-gallery-remove aria-label="<?php esc_attr_e( 'Quitar imagen', 'hadara' ); ?>">&times;</button>
		</span>
	</li>
	<?php
}

/**
 * Guarda la galería.
 *
 * @param int $post_id Producto.
 */
function hadara_save_gallery( $post_id ) {
	if ( ! isset( $_POST['hadara_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hadara_gallery_nonce'] ), 'hadara_gallery' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['hadara_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['hadara_gallery'] ) ) : '';
	$ids = array();

	foreach ( explode( ',', $raw ) as $id ) {
		$id = (int) trim( $id );

		if ( $id > 0 && wp_attachment_is_image( $id ) && ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}

	if ( $ids ) {
		update_post_meta( $post_id, '_hadara_gallery', $ids );
	} else {
		delete_post_meta( $post_id, '_hadara_gallery' );
	}
}
add_action( 'save_post_producto', 'hadara_save_gallery' );

/**
 * Devuelve las imágenes adicionales de un producto.
 *
 * @param int|WP_Post|null $post Producto.
 * @return int[]
 */
function hadara_get_gallery( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return array();
	}

	$ids = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, '_hadara_gallery', true ) ) );

	// La destacada abre la ficha: si también está en la galería se repetiría.
	$ids = array_diff( $ids, array( (int) get_post_thumbnail_id( $post ) ) );

	return array_values( array_unique( $ids ) );
}

/**
 * Campos extra en el formulario de edición de una línea.
 *
 * @param WP_Term $term Término.
 */
function hadara_linea_edit_fields( $term ) {
	$seo_title = get_term_meta( $term->term_id, '_hadara_seo_title', true );
	$seo_desc  = get_term_meta( $term->term_id, '_hadara_seo_description', true );
	$thumb_id  = (int) get_term_meta( $term->term_id, '_hadara_thumb_id', true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="hadara_seo_title"><?php esc_html_e( 'Título para buscadores', 'hadara' ); ?></label></th>
		<td>
			<input type="text" id="hadara_seo_title" name="hadara_seo_title" value="<?php echo esc_attr( $seo_title ); ?>" class="large-text">
			<p class="description"><?php esc_html_e( 'Se usa en la etiqueta title de la página de la línea. Si se deja vacío se usa el nombre.', 'hadara' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="hadara_seo_description"><?php esc_html_e( 'Meta description', 'hadara' ); ?></label></th>
		<td>
			<textarea id="hadara_seo_description" name="hadara_seo_description" rows="3" class="large-text"><?php echo esc_textarea( $seo_desc ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Resumen para buscadores (~155 caracteres). Si se deja vacío se usa la descripción de la línea.', 'hadara' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="hadara_thumb_id"><?php esc_html_e( 'ID de la imagen', 'hadara' ); ?></label></th>
		<td>
			<input type="number" id="hadara_thumb_id" name="hadara_thumb_id" value="<?php echo esc_attr( $thumb_id ? $thumb_id : '' ); ?>" class="small-text">
			<p class="description"><?php esc_html_e( 'ID del archivo en la biblioteca de medios que representa la línea.', 'hadara' ); ?></p>
			<?php if ( $thumb_id ) : ?>
				<?php echo wp_get_attachment_image( $thumb_id, 'thumbnail' ); ?>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}
add_action( 'linea_edit_form_fields', 'hadara_linea_edit_fields' );

/**
 * Guarda los campos extra de una línea.
 *
 * @param int $term_id Término.
 */
function hadara_linea_save_fields( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	if ( isset( $_POST['hadara_seo_title'] ) ) {
		$title = sanitize_text_field( wp_unslash( $_POST['hadara_seo_title'] ) );

		if ( '' === $title ) {
			delete_term_meta( $term_id, '_hadara_seo_title' );
		} else {
			update_term_meta( $term_id, '_hadara_seo_title', $title );
		}
	}

	if ( isset( $_POST['hadara_seo_description'] ) ) {
		$desc = sanitize_textarea_field( wp_unslash( $_POST['hadara_seo_description'] ) );

		if ( '' === $desc ) {
			delete_term_meta( $term_id, '_hadara_seo_description' );
		} else {
			update_term_meta( $term_id, '_hadara_seo_description', $desc );
		}
	}

	if ( isset( $_POST['hadara_thumb_id'] ) ) {
		$thumb_id = (int) $_POST['hadara_thumb_id'];

		if ( $thumb_id > 0 ) {
			update_term_meta( $term_id, '_hadara_thumb_id', $thumb_id );
		} else {
			delete_term_meta( $term_id, '_hadara_thumb_id' );
		}
	}
}
add_action( 'edited_linea', 'hadara_linea_save_fields' );
