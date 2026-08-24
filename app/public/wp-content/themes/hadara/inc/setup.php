<?php
/**
 * Configuración general del tema y carga de recursos.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Soportes del tema, menús y tamaños de imagen.
 */
function hadara_setup() {
	load_theme_textdomain( 'hadara', get_theme_file_path( 'languages' ) );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	register_nav_menus(
		array(
			'primary' => __( 'Menú principal', 'hadara' ),
			'footer'  => __( 'Menú del pie', 'hadara' ),
			'legal'   => __( 'Enlaces legales', 'hadara' ),
		)
	);

	// Las fotos de producto llegan sobre fondo transparente y con relaciones de
	// aspecto muy distintas, por eso se escalan sin recortar.
	add_image_size( 'hadara-card', 640, 640, false );
	add_image_size( 'hadara-detail', 1100, 1100, false );

	set_post_thumbnail_size( 640, 640, false );
}
add_action( 'after_setup_theme', 'hadara_setup' );

/**
 * Ancho del contenido usado por los embeds.
 */
function hadara_content_width() {
	$GLOBALS['content_width'] = 780;
}
add_action( 'after_setup_theme', 'hadara_content_width', 0 );

/**
 * Hojas de estilo y scripts del front.
 */
function hadara_assets() {
	wp_enqueue_style(
		'hadara-fonts',
		get_theme_file_uri( 'assets/css/fonts.css' ),
		array(),
		HADARA_VERSION
	);

	wp_enqueue_style(
		'hadara-main',
		get_theme_file_uri( 'assets/css/main.css' ),
		array( 'hadara-fonts' ),
		HADARA_VERSION
	);

	wp_enqueue_script(
		'hadara-site',
		get_theme_file_uri( 'assets/js/site.js' ),
		array(),
		HADARA_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'hadara_assets' );

/**
 * Añade defer al JS del tema (no tiene dependencias).
 *
 * @param string $tag    Etiqueta script.
 * @param string $handle Handle.
 * @return string
 */
function hadara_defer_scripts( $tag, $handle ) {
	if ( 'hadara-site' === $handle && false === strpos( $tag, ' defer' ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'hadara_defer_scripts', 10, 2 );

/**
 * Atributo sizes explícito en las fotos del catálogo.
 *
 * @param array        $attr       Atributos.
 * @param WP_Post      $attachment Adjunto.
 * @param string|int[] $size       Tamaño.
 * @return array
 */
function hadara_image_sizes_attr( $attr, $attachment, $size ) {
	if ( ! empty( $attr['sizes'] ) ) {
		return $attr;
	}

	if ( 'hadara-card' === $size ) {
		$attr['sizes'] = '(max-width: 640px) 100vw, (max-width: 1024px) 45vw, 320px';
	} elseif ( 'hadara-detail' === $size ) {
		$attr['sizes'] = '(max-width: 1024px) 100vw, 550px';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'hadara_image_sizes_attr', 10, 3 );

/**
 * Controles del personalizador para los logotipos (schema y cabecera).
 *
 * @param WP_Customize_Manager $wp_customize Personalizador.
 */
function hadara_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'hadara_branding',
		array(
			'title'    => __( 'Marca Hadara', 'hadara' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'hadara_logo_dark',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'hadara_logo_dark',
			array(
				'label'     => __( 'Logotipo (fondos claros)', 'hadara' ),
				'section'   => 'hadara_branding',
				'mime_type' => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'hadara_logo_light',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'hadara_logo_light',
			array(
				'label'     => __( 'Logotipo (fondos oscuros)', 'hadara' ),
				'section'   => 'hadara_branding',
				'mime_type' => 'image',
			)
		)
	);

	$wp_customize->add_section(
		'hadara_hero',
		array(
			'title'       => __( 'Banner de portada', 'hadara' ),
			'description' => __( 'Textos del banner principal de la página de inicio.', 'hadara' ),
			'priority'    => 32,
		)
	);

	$hadara_hero_fields = array(
		'eyebrow' => array(
			'label' => __( 'Etiqueta superior', 'hadara' ),
			'type'  => 'text',
		),
		'title'   => array(
			'label' => __( 'Título (primera línea)', 'hadara' ),
			'type'  => 'text',
		),
		'accent'  => array(
			'label'       => __( 'Título destacado (naranja)', 'hadara' ),
			'description' => __( 'Se muestra en naranja, a continuación de la primera línea.', 'hadara' ),
			'type'        => 'text',
		),
		'lead'    => array(
			'label' => __( 'Párrafo de apoyo', 'hadara' ),
			'type'  => 'textarea',
		),
		'point_1' => array(
			'label' => __( 'Viñeta 1', 'hadara' ),
			'type'  => 'text',
		),
		'point_2' => array(
			'label' => __( 'Viñeta 2', 'hadara' ),
			'type'  => 'text',
		),
		'point_3' => array(
			'label' => __( 'Viñeta 3', 'hadara' ),
			'type'  => 'text',
		),
		'point_4' => array(
			'label'       => __( 'Viñeta 4', 'hadara' ),
			'description' => __( 'Deje una viñeta vacía para ocultarla.', 'hadara' ),
			'type'        => 'text',
		),
	);

	$hadara_hero_defaults = hadara_hero_defaults();

	foreach ( $hadara_hero_fields as $hadara_key => $hadara_field ) {
		$wp_customize->add_setting(
			'hadara_hero_' . $hadara_key,
			array(
				'default'           => $hadara_hero_defaults[ $hadara_key ],
				'sanitize_callback' => 'textarea' === $hadara_field['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'hadara_hero_' . $hadara_key,
			array(
				'label'       => $hadara_field['label'],
				'description' => isset( $hadara_field['description'] ) ? $hadara_field['description'] : '',
				'section'     => 'hadara_hero',
				'type'        => $hadara_field['type'],
			)
		);
	}
}
add_action( 'customize_register', 'hadara_customize_register' );

/**
 * Recursos del editor de productos: la galería de imágenes usa la biblioteca de medios.
 *
 * @param string $hook Pantalla del administrador.
 */
function hadara_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'producto' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_style(
		'hadara-admin',
		get_theme_file_uri( 'assets/css/admin.css' ),
		array(),
		HADARA_VERSION
	);

	wp_enqueue_script(
		'hadara-admin-gallery',
		get_theme_file_uri( 'assets/js/admin-gallery.js' ),
		array(),
		HADARA_VERSION,
		true
	);

	wp_localize_script(
		'hadara-admin-gallery',
		'hadaraGallery',
		array(
			'title'  => __( 'Imágenes del producto', 'hadara' ),
			'button' => __( 'Añadir a la ficha', 'hadara' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'hadara_admin_assets' );

/**
 * Precarga la tipografía de titulares para evitar el salto de texto.
 */
function hadara_preload_fonts() {
	$fonts = array( 'barlow-condensed-700.woff2', 'inter-400-700.woff2' );

	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( 'assets/fonts/' . $font ) )
		);
	}
}
add_action( 'wp_head', 'hadara_preload_fonts', 1 );

/**
 * Retira recursos del núcleo que este tema no utiliza.
 */
function hadara_dequeue_unused() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'hadara_dequeue_unused', 100 );

/**
 * Quita el generador y los enlaces de descubrimiento que no aportan al sitio.
 */
function hadara_clean_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'hadara_clean_head' );

/**
 * Número de productos por página en el catálogo.
 *
 * @param WP_Query $query Consulta principal.
 */
function hadara_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'producto' ) || $query->is_tax( 'linea' ) ) {
		$query->set( 'posts_per_page', 24 );
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'hadara_archive_query' );

/**
 * Registra el área de widgets del pie.
 */
function hadara_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Pie de página', 'hadara' ),
			'id'            => 'footer-1',
			'description'   => __( 'Bloque adicional en el pie de página.', 'hadara' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="footer-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'hadara_widgets_init' );

/**
 * Indica si el H1 de portada todavía es copy comercial sin la palabra del nicho.
 *
 * @param string $title Título guardado en el personalizador.
 * @return bool
 */
function hadara_hero_title_needs_seo_update( $title ) {
	$title = trim( (string) $title );

	if ( '' === $title ) {
		return true;
	}

	$weak = array(
		'Estilo y Calidad al mejor precio',
		'Estilo y Calidad al',
		'Dotación industrial que',
	);

	if ( in_array( $title, $weak, true ) ) {
		return true;
	}

	return false !== mb_stripos( $title, 'Estilo y Calidad' );
}

/**
 * Vuelca las reglas de reescritura (CPT, taxonomía y mapa del sitio).
 */
function hadara_flush_rewrites() {
	flush_rewrite_rules( false );
}
add_action( 'after_switch_theme', 'hadara_flush_rewrites' );

/**
 * Migraciones al actualizar el tema: H1 de portada y reglas del sitemap.
 */
function hadara_maybe_upgrade() {
	$stored = (string) get_option( 'hadara_theme_version', '' );

	if ( $stored === HADARA_VERSION ) {
		return;
	}

	if ( hadara_hero_title_needs_seo_update( get_theme_mod( 'hadara_hero_title', '' ) ) ) {
		$defaults = hadara_hero_defaults();
		set_theme_mod( 'hadara_hero_title', $defaults['title'] );
		set_theme_mod( 'hadara_hero_accent', $defaults['accent'] );
	}

	hadara_flush_rewrites();
	update_option( 'hadara_theme_version', HADARA_VERSION );
}
add_action( 'init', 'hadara_maybe_upgrade', 20 );
