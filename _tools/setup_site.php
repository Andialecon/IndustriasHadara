<?php
/**
 * Crea las páginas del sitio, los menús y ajusta las opciones generales.
 *
 * Uso: wp eval-file _tools/setup_site.php
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit( 'Ejecutar con WP-CLI.' );
}

/**
 * Crea o actualiza una página por slug.
 */
function hadara_upsert_page( $slug, $title, $excerpt, $content, $template = '' ) {
	$existing = get_page_by_path( $slug );

	$postarr = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_excerpt' => $excerpt,
		'post_content' => $content,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$page_id       = wp_update_post( $postarr, true );
	} else {
		$page_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $page_id ) ) {
		WP_CLI::warning( $slug . ': ' . $page_id->get_error_message() );
		return 0;
	}

	if ( $template ) {
		update_post_meta( $page_id, '_wp_page_template', $template );
	} else {
		delete_post_meta( $page_id, '_wp_page_template' );
	}

	return (int) $page_id;
}

/* --------------------------------------------------------------- páginas */

$home_id = hadara_upsert_page(
	'inicio',
	'Inicio',
	'Dotación industrial y elementos de protección personal para empresas: ropa de trabajo, calzado de seguridad y EPP certificado.',
	''
);

$about_content = <<<'HTML'
<p>Industrias Hadara es una empresa dedicada al suministro de <strong>dotación industrial y elementos de protección personal</strong> para compañías que necesitan vestir y proteger a su personal operativo, técnico y administrativo. Trabajamos con empresas de industria y manufactura, construcción, metalmecánica, alimentos, energía, salud, minería y logística.</p>

<h2>Lo que hacemos</h2>
<p>Acompañamos el proceso completo de una dotación: revisamos las labores y los riesgos de cada cargo, definimos las prendas y el EPP que corresponden, levantamos la curva de tallas del equipo, marcamos las prendas con la imagen corporativa de la empresa y despachamos. Cuando llega un trabajador nuevo, la reposición se hace con la misma referencia, la misma tela y el mismo color.</p>

<h2>Cómo elegimos lo que vendemos</h2>
<p>Nuestro catálogo está armado con criterio de uso, no de precio de lista. Las camisas y los pantalones se seleccionan por gramaje y por comportamiento de la tela en jornadas largas; el calzado, por tipo de puntera, resistencia de la suela y propiedades dieléctricas; los guantes, por nivel de riesgo mecánico o químico; y los equipos de protección respiratoria, visual y para trabajo en alturas, por la norma que aplica a cada tarea.</p>

<h2>Líneas que manejamos</h2>
<ul>
<li><strong>Ropa de trabajo:</strong> camisas en jean, dril y oxford, jeans de 14 onzas y licrados, pantalones en dril, overoles enterizos y de dos piezas, chalecos reflectivos y en dril.</li>
<li><strong>Dotación para soldador:</strong> delantales, mangas, polainas, pantalones, chaquetas y capuchas en carnaza y vaqueta.</li>
<li><strong>Protección personal:</strong> cascos dieléctricos, gafas y monogafas, caretas, respiradores y filtros, protección auditiva, guantes por nivel de riesgo y equipos para trabajo en alturas.</li>
<li><strong>Calzado industrial:</strong> botas de seguridad con puntera de acero o composite, botas en PVC y calzado de dotación administrativa.</li>
<li><strong>Condiciones especiales:</strong> impermeables en PVC y ropa térmica para cuarto frío.</li>
</ul>

<h2>Tallas y marcación</h2>
<p>Manejamos ropa de la talla S a la 5XL, pantalón caballero de la 28 a la 48, pantalón dama de la 6 a la 26 y calzado de la 34 a la 46, con horma femenina real. Bordamos o estampamos el logo institucional en camisas, chaquetas, overoles y chalecos, en la ubicación que defina el manual de marca de cada empresa.</p>

<h2>Cómo trabajamos la cotización</h2>
<p>No vendemos por carrito de compras: cada dotación tiene tallas, cantidades y marcación distintas, y el precio cambia con el volumen. Por eso atendemos por WhatsApp, revisamos lo que necesita y enviamos una cotización con precios por referencia, valor de la marcación y tiempo de entrega.</p>
HTML;

$about_id = hadara_upsert_page(
	'nosotros',
	'Nosotros',
	'Somos proveedores de dotación industrial y EPP para empresas: asesoría por puesto de trabajo, curva de tallas completa y marcación con su logo.',
	$about_content
);

$contact_content = <<<'HTML'
<h2>Cotizaciones para empresas</h2>
<p>Atendemos solicitudes de <strong>dotación industrial y elementos de protección personal</strong> para empresas de cualquier tamaño, desde un equipo de cinco personas hasta plantas con varios turnos. La forma más rápida de recibir una cotización es escribirnos por WhatsApp.</p>

<h3>Qué necesitamos saber</h3>
<ul>
<li>La labor que realiza el personal y el riesgo al que está expuesto, si aún no tiene definidas las referencias.</li>
<li>Las referencias del catálogo que le interesan, si ya sabe qué necesita.</li>
<li>El número de trabajadores y la curva de tallas, aunque sea aproximada.</li>
<li>Si requiere bordado o estampado del logo de la empresa.</li>
<li>La ciudad de entrega.</li>
</ul>

<h3>Qué recibe de vuelta</h3>
<p>Una cotización con el precio por referencia y por volumen, las tallas disponibles, el valor de la marcación y el tiempo de entrega. Si alguna referencia no es la más adecuada para la labor, se lo decimos y le proponemos la alternativa.</p>

<h3>Compras públicas y proveedores</h3>
<p>Si su empresa requiere documentación para registro de proveedores, fichas técnicas o certificados de los elementos de protección personal, indíquelo en el mensaje y los adjuntamos con la cotización.</p>
HTML;

$contact_id = hadara_upsert_page(
	'contacto',
	'Contacto',
	'Escríbanos por WhatsApp para cotizar dotación industrial y EPP: indique la labor, las tallas, las cantidades y la ciudad de entrega.',
	$contact_content,
	'templates/contacto.php'
);

WP_CLI::log( sprintf( 'Páginas: inicio %d, nosotros %d, contacto %d', $home_id, $about_id, $contact_id ) );

/* --------------------------------------------------------------- opciones */

update_option( 'blogname', hadara_brand( 'name' ) );
update_option( 'blogdescription', hadara_brand( 'tagline' ) );

if ( $home_id ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
	update_option( 'page_for_posts', 0 );
}

update_option( 'timezone_string', 'America/Bogota' );
update_option( 'WPLANG', 'es_CO' );
update_option( 'blog_public', 1 );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );
update_option( 'thumbnail_crop', 0 );

/* ----------------------------------------------------------------- menús */

/**
 * Crea un menú vacío en su ubicación, borrando el anterior si existía.
 */
function hadara_reset_menu( $name, $location ) {
	$menu = wp_get_nav_menu_object( $name );

	if ( $menu ) {
		wp_delete_nav_menu( $menu->term_id );
	}

	$menu_id   = wp_create_nav_menu( $name );
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return $menu_id;
}

/**
 * Añade un elemento a un menú.
 */
function hadara_menu_item( $menu_id, $args, $order, $parent = 0 ) {
	return wp_update_nav_menu_item(
		$menu_id,
		0,
		array_merge(
			array(
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $order,
				'menu-item-parent-id' => $parent,
			),
			$args
		)
	);
}

$primary = hadara_reset_menu( 'Menú principal', 'primary' );

hadara_menu_item(
	$primary,
	array(
		'menu-item-title'     => 'Inicio',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $home_id,
		'menu-item-type'      => 'post_type',
	),
	1
);

$catalog_item = hadara_menu_item(
	$primary,
	array(
		'menu-item-title'     => 'Catálogo',
		'menu-item-url'       => get_post_type_archive_link( 'producto' ),
		'menu-item-object'    => 'producto',
		'menu-item-type'      => 'post_type_archive',
	),
	2
);

$order = 1;

foreach ( hadara_get_lines() as $line ) {
	$label = get_term_meta( $line->term_id, '_hadara_menu_label', true );

	hadara_menu_item(
		$primary,
		array(
			'menu-item-title'     => $label ? $label : $line->name,
			'menu-item-object'    => 'linea',
			'menu-item-object-id' => $line->term_id,
			'menu-item-type'      => 'taxonomy',
		),
		$order++,
		$catalog_item
	);
}

hadara_menu_item(
	$primary,
	array(
		'menu-item-title'     => 'Nosotros',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $about_id,
		'menu-item-type'      => 'post_type',
	),
	3
);

hadara_menu_item(
	$primary,
	array(
		'menu-item-title'     => 'Contacto',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $contact_id,
		'menu-item-type'      => 'post_type',
	),
	4
);

$footer = hadara_reset_menu( 'Menú del pie', 'footer' );

hadara_menu_item(
	$footer,
	array(
		'menu-item-title' => 'Catálogo completo',
		'menu-item-url'   => get_post_type_archive_link( 'producto' ),
		'menu-item-object' => 'producto',
		'menu-item-type'  => 'post_type_archive',
	),
	1
);

$footer_order = 2;

foreach ( array( $about_id => 'Nosotros', $contact_id => 'Contacto' ) as $page_id => $label ) {
	hadara_menu_item(
		$footer,
		array(
			'menu-item-title'     => $label,
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type'      => 'post_type',
		),
		$footer_order++
	);
}

$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );

if ( ! $privacy_id ) {
	$privacy = get_page_by_path( 'privacy-policy' );
	$privacy_id = $privacy ? $privacy->ID : 0;
}

if ( $privacy_id ) {
	wp_update_post(
		array(
			'ID'          => $privacy_id,
			'post_title'  => 'Política de privacidad',
			'post_name'   => 'politica-de-privacidad',
			'post_status' => 'publish',
		)
	);

	update_option( 'wp_page_for_privacy_policy', $privacy_id );

	$legal = hadara_reset_menu( 'Enlaces legales', 'legal' );

	hadara_menu_item(
		$legal,
		array(
			'menu-item-title'     => 'Política de privacidad',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $privacy_id,
			'menu-item-type'      => 'post_type',
		),
		1
	);
}

WP_CLI::success( 'Páginas, menús y opciones listos.' );
