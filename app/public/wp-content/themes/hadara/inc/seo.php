<?php
/**
 * Etiquetas para buscadores y redes sociales.
 *
 * El tema resuelve el título, la descripción, la URL canónica y las etiquetas
 * Open Graph sin depender de un plugin de SEO. Si más adelante se instala uno,
 * basta con desactivar estas acciones mediante el filtro «hadara_manage_seo».
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Indica si el tema debe encargarse de las etiquetas de SEO.
 *
 * @return bool
 */
function hadara_manages_seo() {
	$plugin_active = defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' );

	/**
	 * Permite ceder el control del SEO a un plugin.
	 *
	 * @param bool $manage Si el tema gestiona las etiquetas.
	 */
	return (bool) apply_filters( 'hadara_manage_seo', ! $plugin_active );
}

/**
 * Título SEO personalizado de un producto.
 *
 * @param int|WP_Post|null $post Producto.
 * @return string
 */
function hadara_get_seo_title( $post = null ) {
	$post = get_post( $post );

	return $post ? (string) get_post_meta( $post->ID, '_hadara_seo_title', true ) : '';
}

/**
 * Descripción SEO personalizada de un producto.
 *
 * @param int|WP_Post|null $post Producto.
 * @return string
 */
function hadara_get_seo_description( $post = null ) {
	$post = get_post( $post );

	return $post ? (string) get_post_meta( $post->ID, '_hadara_seo_description', true ) : '';
}

/**
 * Descripción de la vista actual, recortada a una longitud usable.
 *
 * @return string
 */
function hadara_meta_description() {
	$description = '';

	if ( is_front_page() ) {
		$description = sprintf(
			/* translators: %s: nombre de la empresa. */
			__( '%s: dotación industrial en Colombia. Jeans, pantalones, camisas, overoles, chalecos y calzado de seguridad con su logo. Cotice por WhatsApp.', 'hadara' ),
			hadara_brand( 'name' )
		);
	} elseif ( is_tax( 'linea' ) ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$seo_description = (string) get_term_meta( $term->term_id, '_hadara_seo_description', true );
			$description     = $seo_description ? $seo_description : $term->description;
		}
	} elseif ( is_post_type_archive( 'producto' ) ) {
		$description = __( 'Catálogo de dotación industrial por líneas: jeans, pantalones, camisas, overoles, chalecos, capuchas y calzado de seguridad.', 'hadara' );
	} elseif ( is_singular( 'producto' ) ) {
		$description = hadara_get_seo_description();

		if ( '' === $description ) {
			$post        = get_queried_object();
			$description = $post instanceof WP_Post ? $post->post_excerpt : '';

			if ( '' === $description && $post instanceof WP_Post ) {
				$description = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
			}
		}
	} elseif ( is_singular() ) {
		$post        = get_queried_object();
		$description = $post instanceof WP_Post ? $post->post_excerpt : '';

		if ( '' === $description && $post instanceof WP_Post ) {
			$description = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		}
	} elseif ( is_search() ) {
		$description = sprintf(
			/* translators: %s: términos buscados. */
			__( 'Resultados de búsqueda para «%s» en el catálogo de dotación industrial.', 'hadara' ),
			get_search_query()
		);
	}

	if ( '' === $description ) {
		$description = get_bloginfo( 'description', 'display' );
	}

	$description = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $description ) ) );

	return hadara_trim_description( $description );
}

/**
 * Recorta una descripción a lo que Google muestra (unos 155 caracteres).
 *
 * Prefiere cortar al final de una frase para no dejar textos a medias como
 * «…labores eléctrica…»; si la primera frase es demasiado corta, corta en el
 * último espacio.
 *
 * @param string $description Texto limpio.
 * @param int    $max         Longitud máxima.
 * @return string
 */
function hadara_trim_description( $description, $max = 155 ) {
	if ( mb_strlen( $description ) <= $max ) {
		return $description;
	}

	$cut = mb_substr( $description, 0, $max );

	if ( preg_match( '/^(.{70,}[.!?])\s/u', $cut . ' ', $match ) ) {
		return $match[1];
	}

	$space = mb_strrpos( $cut, ' ' );
	$cut   = false === $space ? mb_substr( $cut, 0, $max - 1 ) : mb_substr( $cut, 0, $space );

	return rtrim( $cut, " ,.;:-" ) . '…';
}

/**
 * Título de la vista actual.
 *
 * @param array $parts Partes del título calculadas por WordPress.
 * @return array
 */
function hadara_document_title( $parts ) {
	if ( ! hadara_manages_seo() ) {
		return $parts;
	}

	if ( is_front_page() ) {
		$parts['title'] = hadara_brand( 'name' ) . ' | ' . __( 'Dotación industrial en Colombia', 'hadara' );
		unset( $parts['tagline'], $parts['site'] );

		return $parts;
	}

	if ( is_singular( 'producto' ) ) {
		$seo_title = hadara_get_seo_title();

		if ( $seo_title ) {
			$parts['title'] = $seo_title;
		}
	}

	if ( is_tax( 'linea' ) ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$seo_title = get_term_meta( $term->term_id, '_hadara_seo_title', true );

			if ( $seo_title ) {
				$parts['title'] = $seo_title;
			}
		}
	}

	if ( is_post_type_archive( 'producto' ) ) {
		$parts['title'] = __( 'Catálogo de dotación industrial', 'hadara' );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'hadara_document_title' );

/**
 * Separador del título.
 *
 * @return string
 */
function hadara_title_separator() {
	return '|';
}
add_filter( 'document_title_separator', 'hadara_title_separator' );

/**
 * Directivas robots vía el API del núcleo (evita meta robots duplicados).
 *
 * @param array $robots Directivas actuales.
 * @return array
 */
function hadara_wp_robots( $robots ) {
	if ( ! hadara_manages_seo() ) {
		return $robots;
	}

	if ( hadara_is_sitemap_request() ) {
		unset( $robots['noindex'] );
		$robots['index']  = true;
		$robots['follow'] = true;

		return $robots;
	}

	if ( is_search() || is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'], $robots['max-image-preview'], $robots['max-snippet'] );

		return $robots;
	}

	$robots['index']             = true;
	$robots['follow']            = true;
	$robots['max-image-preview'] = 'large';
	// Como texto: el núcleo solo imprime «directiva:valor» para valores string.
	$robots['max-snippet']       = '-1';

	return $robots;
}
add_filter( 'wp_robots', 'hadara_wp_robots' );

/**
 * Imprime descripción, canónica y etiquetas sociales.
 */
function hadara_meta_tags() {
	if ( ! hadara_manages_seo() ) {
		return;
	}

	$description = hadara_meta_description();
	$canonical   = hadara_canonical_url();

	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( $canonical ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}

	$title = wp_get_document_title();
	$image = hadara_social_image_data();

	printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'producto' ) ? 'product' : ( is_singular() ? 'article' : 'website' ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( hadara_brand( 'name' ) ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );

	if ( $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( $canonical ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $canonical ) );
	}

	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image['url'] ) );

		if ( ! empty( $image['width'] ) ) {
			printf( '<meta property="og:image:width" content="%d">' . "\n", (int) $image['width'] );
		}

		if ( ! empty( $image['height'] ) ) {
			printf( '<meta property="og:image:height" content="%d">' . "\n", (int) $image['height'] );
		}

		if ( ! empty( $image['alt'] ) ) {
			printf( '<meta property="og:image:alt" content="%s">' . "\n", esc_attr( $image['alt'] ) );
		}

		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image['url'] ) );

		if ( ! empty( $image['alt'] ) ) {
			printf( '<meta name="twitter:image:alt" content="%s">' . "\n", esc_attr( $image['alt'] ) );
		}
	} else {
		echo '<meta name="twitter:card" content="summary">' . "\n";
	}

	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );

	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	}
}
add_action( 'wp_head', 'hadara_meta_tags', 2 );

/**
 * Retira la canónica del núcleo: el tema ya imprime la suya y dos etiquetas
 * rel="canonical" en la misma página pueden hacer que Google ignore ambas.
 */
function hadara_remove_core_canonical() {
	if ( hadara_manages_seo() ) {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}
add_action( 'wp', 'hadara_remove_core_canonical' );

/**
 * URL canónica de la vista actual.
 *
 * @return string
 */
function hadara_canonical_url() {
	// En los listados paginados la canónica es la propia página: apuntar todas a
	// la primera esconde de los buscadores las referencias de la página dos.
	if ( is_paged() && ! is_singular() ) {
		return (string) get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
	}

	if ( is_front_page() ) {
		return home_url( '/' );
	}

	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		$link = $term instanceof WP_Term ? get_term_link( $term ) : '';

		return is_wp_error( $link ) ? '' : (string) $link;
	}

	if ( is_post_type_archive() ) {
		return (string) get_post_type_archive_link( get_query_var( 'post_type' ) ?: 'producto' );
	}

	if ( is_home() ) {
		$page_for_posts = (int) get_option( 'page_for_posts' );

		return $page_for_posts ? (string) get_permalink( $page_for_posts ) : home_url( '/' );
	}

	return '';
}

/**
 * Imagen representativa para redes sociales (URL + medidas + alt).
 *
 * @return array{url:string,width:int,height:int,alt:string}|null
 */
function hadara_social_image_data() {
	if ( is_singular() && has_post_thumbnail() ) {
		$attachment_id = get_post_thumbnail_id();
		$src           = wp_get_attachment_image_src( $attachment_id, 'hadara-detail' );

		if ( $src ) {
			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => $alt ? $alt : get_the_title(),
			);
		}
	}

	if ( is_tax( 'linea' ) ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$thumb_id = (int) get_term_meta( $term->term_id, '_hadara_thumb_id', true );
			$src      = $thumb_id ? wp_get_attachment_image_src( $thumb_id, 'hadara-detail' ) : false;

			if ( $src ) {
				$alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );

				return array(
					'url'    => $src[0],
					'width'  => (int) $src[1],
					'height' => (int) $src[2],
					'alt'    => $alt ? $alt : $term->name,
				);
			}
		}
	}

	// Para el resto de las vistas se comparte el banner de marca, que tiene la
	// proporción que piden las redes sociales.
	$banner = get_theme_file_path( 'assets/img/banner-hadara.jpg' );

	if ( file_exists( $banner ) ) {
		$size = @getimagesize( $banner ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		return array(
			'url'    => get_theme_file_uri( 'assets/img/banner-hadara.jpg' ),
			'width'  => $size ? (int) $size[0] : 1200,
			'height' => $size ? (int) $size[1] : 630,
			'alt'    => hadara_brand( 'name' ),
		);
	}

	return hadara_logo_image_data( 'dark' );
}

/**
 * URL de la imagen social (compatibilidad).
 *
 * @return string
 */
function hadara_social_image() {
	$data = hadara_social_image_data();

	return $data ? $data['url'] : '';
}

/**
 * Añade los productos y las líneas al mapa del sitio del núcleo.
 *
 * WordPress ya incluye los tipos y taxonomías públicas; este filtro solo excluye
 * las páginas de sistema que no deben aparecer.
 *
 * @param array $args Argumentos de la consulta del mapa del sitio.
 * @return array
 */
function hadara_sitemap_exclude( $args ) {
	$excluded = array_filter(
		array(
			(int) get_option( 'wp_page_for_privacy_policy' ),
		)
	);

	if ( $excluded ) {
		$args['post__not_in'] = isset( $args['post__not_in'] ) ? array_merge( $args['post__not_in'], $excluded ) : $excluded;
	}

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'hadara_sitemap_exclude' );

/**
 * Retira del mapa del sitio el listado de autores: este sitio no publica firmas.
 *
 * @param WP_Sitemaps_Provider $provider Proveedor.
 * @param string               $name     Nombre del proveedor.
 * @return WP_Sitemaps_Provider|false
 */
function hadara_sitemap_providers( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'hadara_sitemap_providers', 10, 2 );

/**
 * Query var del mapa del sitio del tema.
 *
 * @param string[] $vars Variables.
 * @return string[]
 */
function hadara_sitemap_query_vars( $vars ) {
	$vars[] = 'hadara_sitemap';

	return $vars;
}
add_filter( 'query_vars', 'hadara_sitemap_query_vars' );

/**
 * Reescrituras de /sitemap.xml y /wp-sitemap.xml hacia el tema.
 *
 * Van al frente para no depender de las reglas del núcleo, que en producción
 * devolvían 404 (LiteSpeed / reglas no vaciadas tras la migración).
 */
function hadara_sitemap_rewrites() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?hadara_sitemap=1', 'top' );
	add_rewrite_rule( '^wp-sitemap\.xml$', 'index.php?hadara_sitemap=1', 'top' );
}
add_action( 'init', 'hadara_sitemap_rewrites', 1 );

/**
 * Indica si la petición actual pide el mapa del sitio.
 *
 * @return bool
 */
function hadara_is_sitemap_request() {
	if ( get_query_var( 'hadara_sitemap' ) ) {
		return true;
	}

	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
	$path = untrailingslashit( (string) $path );

	$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home_path = untrailingslashit( (string) $home_path );

	if ( $home_path && 0 === strpos( $path, $home_path ) ) {
		$path = substr( $path, strlen( $home_path ) );
		$path = untrailingslashit( (string) $path );
	}

	return in_array( $path, array( '/sitemap.xml', '/wp-sitemap.xml' ), true );
}

/**
 * URLs públicas que deben aparecer en el mapa del sitio.
 *
 * @return array<int,array{loc:string,lastmod?:string}>
 */
function hadara_sitemap_entries() {
	$entries = array();
	$seen    = array();

	$add = static function ( $loc, $lastmod = '' ) use ( &$entries, &$seen ) {
		$loc = esc_url_raw( $loc );

		if ( ! $loc || isset( $seen[ $loc ] ) ) {
			return;
		}

		$seen[ $loc ] = true;
		$item         = array( 'loc' => $loc );

		if ( $lastmod ) {
			$item['lastmod'] = gmdate( 'c', strtotime( $lastmod ) ?: time() );
		}

		$entries[] = $item;
	};

	$add( home_url( '/' ), get_lastpostmodified( 'gmt' ) );

	$archive = get_post_type_archive_link( 'producto' );

	if ( $archive ) {
		$add( $archive, get_lastpostmodified( 'gmt', 'producto' ) );
	}

	$front_id   = (int) get_option( 'page_on_front' );
	$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'modified',
			'no_found_rows'  => true,
		)
	);

	foreach ( $pages as $page ) {
		if ( (int) $page->ID === $front_id || (int) $page->ID === $privacy_id ) {
			continue;
		}

		$add( get_permalink( $page ), $page->post_modified_gmt );
	}

	$products = get_posts(
		array(
			'post_type'      => 'producto',
			'post_status'    => 'publish',
			'posts_per_page' => 500,
			'orderby'        => 'modified',
			'no_found_rows'  => true,
		)
	);

	// Una línea cambia cuando cambia cualquiera de sus productos.
	$line_modified = array();

	foreach ( $products as $product ) {
		$add( get_permalink( $product ), $product->post_modified_gmt );

		$product_terms = get_the_terms( $product, 'linea' );

		if ( $product_terms && ! is_wp_error( $product_terms ) ) {
			foreach ( $product_terms as $product_term ) {
				$current = isset( $line_modified[ $product_term->term_id ] ) ? $line_modified[ $product_term->term_id ] : '';

				if ( $product->post_modified_gmt > $current ) {
					$line_modified[ $product_term->term_id ] = $product->post_modified_gmt;
				}
			}
		}
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'linea',
			'hide_empty' => true,
		)
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$link = get_term_link( $term );

			if ( ! is_wp_error( $link ) ) {
				$add( $link, isset( $line_modified[ $term->term_id ] ) ? $line_modified[ $term->term_id ] : '' );
			}
		}
	}

	return $entries;
}

/**
 * Envía el mapa del sitio como XML y termina la petición.
 */
function hadara_print_url_sitemap() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	status_header( 200 );
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-LiteSpeed-Cache-Control: no-cache' );
	nocache_headers();

	$entries = hadara_sitemap_entries();

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

	foreach ( $entries as $entry ) {
		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_xml( $entry['loc'] ) . "</loc>\n";

		if ( ! empty( $entry['lastmod'] ) ) {
			echo "\t\t<lastmod>" . esc_xml( $entry['lastmod'] ) . "</lastmod>\n";
		}

		echo "\t</url>\n";
	}

	echo '</urlset>';
	exit;
}

/**
 * Sirve el mapa del tema cuando la petición llega a WordPress.
 */
function hadara_serve_sitemap() {
	if ( ! hadara_is_sitemap_request() ) {
		return;
	}

	hadara_print_url_sitemap();
}
add_action( 'template_redirect', 'hadara_serve_sitemap', 0 );

/**
 * Anuncia /sitemap.xml en robots.txt en lugar del mapa del núcleo.
 *
 * /wp-sitemap.xml sirve el mismo contenido, así que se anuncia una sola URL.
 *
 * @param string $output  robots.txt.
 * @param bool   $public  Si el sitio es público.
 * @return string
 */
function hadara_robots_txt( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}

	$sitemap = home_url( '/sitemap.xml' );
	$output  = preg_replace( '/^Sitemap:\s*' . preg_quote( home_url( '/wp-sitemap.xml' ), '/' ) . '\s*$/mi', '', $output );
	$output  = preg_replace( "/\n{3,}/", "\n\n", $output );

	if ( false === strpos( $output, $sitemap ) ) {
		$output = rtrim( $output ) . "\n\nSitemap: " . $sitemap . "\n";
	}

	return $output;
}
add_filter( 'robots_txt', 'hadara_robots_txt', 20, 2 );
