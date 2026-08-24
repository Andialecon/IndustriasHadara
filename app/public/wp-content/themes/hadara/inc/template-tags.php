<?php
/**
 * Funciones de plantilla reutilizables.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Devuelve el logotipo del sitio, en versión oscura o clara.
 *
 * @param string $variant «dark» para fondos claros, «light» para fondos oscuros.
 * @return string
 */
function hadara_logo( $variant = 'dark' ) {
	$attachment_id = (int) get_theme_mod( 'light' === $variant ? 'hadara_logo_light' : 'hadara_logo_dark' );

	if ( $attachment_id ) {
		return wp_get_attachment_image(
			$attachment_id,
			'full',
			false,
			array(
				'class'         => 'site-logo__img',
				'alt'           => hadara_brand( 'name' ),
				'loading'       => 'eager',
				'decoding'      => 'sync',
				// La prioridad alta se reserva para la fotografía de la portada,
				// que es la imagen grande que mide el navegador.
				'fetchpriority' => 'auto',
			)
		);
	}

	$file = 'light' === $variant ? 'logo-industrias-hadara-claro.png' : 'logo-industrias-hadara.png';

	if ( ! file_exists( get_theme_file_path( 'assets/img/' . $file ) ) ) {
		return '<span class="site-logo__text">' . esc_html( hadara_brand( 'name' ) ) . '</span>';
	}

	return sprintf(
		'<img class="site-logo__img" src="%s" width="857" height="169" alt="%s" loading="eager" decoding="sync">',
		esc_url( get_theme_file_uri( 'assets/img/' . $file ) ),
		esc_attr( hadara_brand( 'name' ) )
	);
}

/**
 * Fotografía del equipo que abre la portada, recortada del banner de marca.
 *
 * @return string
 */
function hadara_hero_photo() {
	$file = 'hero-equipo.webp';

	if ( ! file_exists( get_theme_file_path( 'assets/img/' . $file ) ) ) {
		return '';
	}

	return sprintf(
		'<figure class="hero__photo"><img src="%1$s" width="958" height="1080" alt="%2$s" loading="eager" decoding="sync" fetchpriority="high"></figure>',
		esc_url( get_theme_file_uri( 'assets/img/' . $file ) ),
		esc_attr__( 'Trabajadora con polo y trabajador con overol de dotación azul con el logo de Industrias Hadara bordado', 'hadara' )
	);
}

/**
 * Botón de WhatsApp.
 *
 * @param array $args Configuración del botón.
 * @return string
 */
function hadara_whatsapp_button( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'label'   => __( 'Cotizar por WhatsApp', 'hadara' ),
			'message' => '',
			'class'   => 'btn btn--whatsapp',
			'post'    => null,
		)
	);

	$url = $args['post'] ? hadara_product_whatsapp_url( $args['post'] ) : hadara_whatsapp_url( $args['message'] );

	if ( '' === $url ) {
		return '';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s" target="_blank" rel="noopener nofollow">%3$s<span>%4$s</span></a>',
		esc_attr( $args['class'] ),
		esc_url( $url ),
		hadara_icon( 'whatsapp' ),
		esc_html( $args['label'] )
	);
}

/**
 * Iconos SVG en línea.
 *
 * @param string $name Nombre del icono.
 * @return string
 */
function hadara_icon( $name ) {
	$icons = array(
		'whatsapp' => '<path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.97L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.85 9.85 0 0 0 12.04 2Zm0 1.67c2.2 0 4.27.86 5.82 2.42a8.2 8.2 0 0 1 2.42 5.83c0 4.54-3.7 8.23-8.24 8.23a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-3.12.82.83-3.04-.19-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24Zm-3.2 4.2c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.03 0 1.2.87 2.35.99 2.51.12.16 1.7 2.72 4.12 3.7 2.02.8 2.43.64 2.87.6.44-.04 1.42-.58 1.62-1.15.2-.56.2-1.05.14-1.15-.06-.1-.22-.16-.46-.28-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.8-.76.96-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.42-1.34-1.66-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.16-.38-.32-.39-.46-.4h-.03Z"/>',
		'phone'    => '<path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.24.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z"/>',
		'mail'     => '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm8 7L4.5 6.5h15L12 11Zm0 2.2 8-4.8V18H4V8.4l8 4.8Z"/>',
		'pin'      => '<path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/>',
		'check'    => '<path d="M9.55 17.6 4.4 12.45l1.4-1.4 3.75 3.74 8.65-8.64 1.4 1.4Z"/>',
		'arrow'    => '<path d="M13.2 5.6 11.8 7l4 4H4v2h11.8l-4 4 1.4 1.4L19.6 12Z"/>',
		'shield'   => '<path d="M12 2 4 5v6.1c0 5 3.4 9.6 8 10.9 4.6-1.3 8-5.9 8-10.9V5l-8-3Zm-1.1 13.4-3.2-3.2 1.4-1.4 1.8 1.8 4.3-4.3 1.4 1.4-5.7 5.7Z"/>',
		'truck'    => '<path d="M3 5h11v9H3V5Zm12 3h3.3l2.7 3.4V16h-2a2 2 0 1 1-4 0h-1V8Zm-9 8a2 2 0 1 1-4 0H1V5a1 1 0 0 1 1-1h13v11h-3.1a2 2 0 0 0-3.8 0H6Z"/>',
		'ruler'    => '<path d="M2 8h20v8H2V8Zm2 2v4h1.5v-2H7v2h1.5v-2H10v2h1.5v-2H13v2h1.5v-2H16v2h1.5v-2H19v2h1V10H4Z"/>',
		'tag'      => '<path d="M12.4 2.6 21 11.2a2 2 0 0 1 0 2.8l-7 7a2 2 0 0 1-2.8 0L2.6 12.4A2 2 0 0 1 2 11V4a2 2 0 0 1 2-2h7a2 2 0 0 1 1.4.6ZM6.5 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/>',
		'search'   => '<path d="M10.5 3a7.5 7.5 0 0 1 5.95 12.1l3.72 3.73-1.41 1.41-3.73-3.72A7.5 7.5 0 1 1 10.5 3Zm0 2a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11Z"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return '<svg class="icon icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

/**
 * Tarjeta de producto para las rejillas del catálogo.
 *
 * @param int|WP_Post|null $post Producto.
 */
function hadara_product_card( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return;
	}

	$specs = hadara_get_specs( $post );
	$sizes = '';

	foreach ( array( 'Tallas', 'Talla', 'Tallas pantalón' ) as $key ) {
		if ( isset( $specs[ $key ] ) ) {
			$sizes = $specs[ $key ];
			break;
		}
	}
	?>
	<article class="card">
		<a class="card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<?php
				echo get_the_post_thumbnail(
					$post,
					'hadara-card',
					array(
						'class'   => 'card__img',
						'alt'     => get_the_title( $post ),
						'loading' => 'lazy',
					)
				);
				?>
			<?php endif; ?>
		</a>
		<div class="card__body">
			<h3 class="card__title">
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
			</h3>
			<?php if ( $sizes ) : ?>
				<p class="card__meta"><?php echo hadara_icon( 'ruler' ); ?><?php echo esc_html( $sizes ); ?></p>
			<?php endif; ?>
			<div class="card__actions">
				<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php esc_html_e( 'Ver ficha', 'hadara' ); ?></a>
				<?php echo hadara_whatsapp_button( array( 'label' => __( 'Cotizar', 'hadara' ), 'class' => 'btn btn--whatsapp btn--sm', 'post' => $post ) ); ?>
			</div>
		</div>
	</article>
	<?php
}

/**
 * Tarjeta de una línea del catálogo.
 *
 * @param WP_Term $term Línea.
 */
function hadara_line_card( $term ) {
	$thumb_id = (int) get_term_meta( $term->term_id, '_hadara_thumb_id', true );
	?>
	<a class="line-card" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
		<span class="line-card__media">
			<?php if ( $thumb_id ) : ?>
				<?php
				echo wp_get_attachment_image(
					$thumb_id,
					'hadara-card',
					false,
					array(
						'class'   => 'line-card__img',
						'alt'     => $term->name,
						'loading' => 'lazy',
					)
				);
				?>
			<?php endif; ?>
		</span>
		<span class="line-card__body">
			<span class="line-card__title"><?php echo esc_html( $term->name ); ?></span>
			<span class="line-card__count">
				<?php
				printf(
					/* translators: %d: número de referencias. */
					esc_html( _n( '%d referencia', '%d referencias', (int) $term->count, 'hadara' ) ),
					(int) $term->count
				);
				?>
			</span>
		</span>
	</a>
	<?php
}

/**
 * Devuelve la ruta de migas de pan como arreglo de pares título/enlace.
 *
 * @return array<int,array{label:string,url:string}>
 */
function hadara_breadcrumb_trail() {
	$trail = array(
		array(
			'label' => __( 'Inicio', 'hadara' ),
			'url'   => home_url( '/' ),
		),
	);

	$archive_url  = get_post_type_archive_link( 'producto' );
	$catalog_item = array(
		'label' => __( 'Catálogo', 'hadara' ),
		'url'   => $archive_url ? $archive_url : '',
	);

	if ( is_singular( 'producto' ) ) {
		$trail[] = $catalog_item;
		$terms   = get_the_terms( get_queried_object_id(), 'linea' );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$trail[] = array(
				'label' => $terms[0]->name,
				'url'   => get_term_link( $terms[0] ),
			);
		}

		$trail[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_tax( 'linea' ) ) {
		$trail[] = $catalog_item;
		$trail[] = array(
			'label' => single_term_title( '', false ),
			'url'   => '',
		);
	} elseif ( is_post_type_archive( 'producto' ) ) {
		$trail[] = array(
			'label' => __( 'Catálogo', 'hadara' ),
			'url'   => '',
		);
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$trail[] = array(
				'label' => get_the_title( $ancestor ),
				'url'   => get_permalink( $ancestor ),
			);
		}

		$trail[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$trail[] = array(
			'label' => __( 'Resultados de búsqueda', 'hadara' ),
			'url'   => '',
		);
	} elseif ( is_404() ) {
		$trail[] = array(
			'label' => __( 'Página no encontrada', 'hadara' ),
			'url'   => '',
		);
	} elseif ( is_singular() ) {
		$trail[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	}

	return $trail;
}

/**
 * Pinta las migas de pan.
 */
function hadara_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$trail = hadara_breadcrumb_trail();

	if ( count( $trail ) < 2 ) {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Ruta de navegación', 'hadara' ) . '"><div class="container"><ol>';

	foreach ( $trail as $index => $item ) {
		$is_last = ( count( $trail ) - 1 ) === $index;

		echo '<li>';

		if ( $item['url'] && ! $is_last ) {
			printf( '<a href="%s">%s</a>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
		} else {
			printf( '<span aria-current="page">%s</span>', esc_html( $item['label'] ) );
		}

		echo '</li>';
	}

	echo '</ol></div></nav>';
}

/**
 * Botón flotante de WhatsApp.
 */
function hadara_floating_whatsapp() {
	$url = hadara_whatsapp_url();

	if ( '' === $url ) {
		return;
	}
	?>
	<a class="wa-float" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener nofollow"
		aria-label="<?php esc_attr_e( 'Escribir por WhatsApp', 'hadara' ); ?>">
		<?php echo hadara_icon( 'whatsapp' ); ?>
		<span class="wa-float__label"><?php esc_html_e( 'Cotizar', 'hadara' ); ?></span>
	</a>
	<?php
}
add_action( 'wp_footer', 'hadara_floating_whatsapp' );

/**
 * Devuelve productos por slug, en el orden pedido, completando con los más
 * recientes si alguno de los slugs no existe.
 *
 * @param string[] $slugs Slugs deseados.
 * @param int      $limit Número de productos a devolver.
 * @return WP_Post[]
 */
function hadara_products_by_slug( $slugs, $limit = 0 ) {
	$limit = $limit ? $limit : count( $slugs );

	$found = $slugs ? get_posts(
		array(
			'post_type'      => 'producto',
			'post_name__in'  => $slugs,
			'posts_per_page' => $limit,
			'orderby'        => 'post_name__in',
			'no_found_rows'  => true,
		)
	) : array();

	if ( count( $found ) >= $limit ) {
		return array_slice( $found, 0, $limit );
	}

	$fill = get_posts(
		array(
			'post_type'      => 'producto',
			'posts_per_page' => $limit - count( $found ),
			'post__not_in'   => wp_list_pluck( $found, 'ID' ),
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	return array_merge( $found, $fill );
}

/**
 * Menú de respaldo cuando no hay uno asignado en Apariencia → Menús.
 */
function hadara_nav_fallback() {
	$archive = get_post_type_archive_link( 'producto' );

	echo '<ul class="menu">';
	printf( '<li><a href="%s">%s</a></li>', esc_url( home_url( '/' ) ), esc_html__( 'Inicio', 'hadara' ) );

	if ( $archive ) {
		echo '<li class="menu-item-has-children">';
		printf( '<a href="%s">%s</a>', esc_url( $archive ), esc_html__( 'Catálogo', 'hadara' ) );

		$lines = hadara_get_lines();

		if ( $lines ) {
			echo '<ul class="sub-menu">';

			foreach ( $lines as $line ) {
				printf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $line ) ), esc_html( $line->name ) );
			}

			echo '</ul>';
		}

		echo '</li>';
	}

	foreach ( array( 'nosotros' => __( 'Nosotros', 'hadara' ), 'contacto' => __( 'Contacto', 'hadara' ) ) as $slug => $label ) {
		$page = get_page_by_path( $slug );

		if ( $page ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $page ) ), esc_html( $label ) );
		}
	}

	echo '</ul>';
}

/**
 * Devuelve las líneas del catálogo ordenadas.
 *
 * @param int $limit Número máximo de líneas.
 * @return WP_Term[]
 */
function hadara_get_lines( $limit = 0 ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'linea',
			'hide_empty' => true,
			'meta_key'   => '_hadara_order',
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
			'number'     => $limit ? $limit : 0,
		)
	);

	if ( is_wp_error( $terms ) || ! $terms ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'linea',
				'hide_empty' => true,
				'orderby'    => 'name',
				'number'     => $limit ? $limit : 0,
			)
		);
	}

	return is_wp_error( $terms ) ? array() : $terms;
}
