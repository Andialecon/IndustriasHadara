<?php
/**
 * Marcado estructurado JSON-LD.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preguntas frecuentes: se usan en la portada y en el marcado FAQPage.
 *
 * @return array<int,array{q:string,a:string}>
 */
function hadara_faq_items() {
	$items = array(
		array(
			'q' => __( '¿Cuál es la cantidad mínima de compra?', 'hadara' ),
			'a' => __( 'Trabajamos desde unidades sueltas para reposición y armamos cotizaciones por volumen para dotaciones completas. Escríbanos por WhatsApp con la cantidad y las tallas que necesita.', 'hadara' ),
		),
		array(
			'q' => __( '¿Bordan o marcan las prendas con el logo de la empresa?', 'hadara' ),
			'a' => __( 'Sí. Marcamos camisas, chaquetas, overoles y chalecos con bordado o estampado del logo institucional, y podemos ubicar la marca en pecho, espalda o manga según su manual de imagen.', 'hadara' ),
		),
		array(
			'q' => __( '¿Qué tallas manejan?', 'hadara' ),
			'a' => __( 'La ropa de trabajo va de la talla S a la 5XL, los pantalones y jeans de caballero de la 28 a la 48, los de dama de la 6 a la 26 y el calzado industrial de la 34 a la 46, según la referencia.', 'hadara' ),
		),
		array(
			'q' => __( '¿Tienen referencias para soldador?', 'hadara' ),
			'a' => __( 'Sí. Manejamos camisa y chaqueta en jean de 14 onzas para soldadura y pulimiento, además de overoles en dril para trabajo general en planta.', 'hadara' ),
		),
		array(
			'q' => __( '¿Hacen envíos a todo el país?', 'hadara' ),
			'a' => __( 'Sí, despachamos a todo Colombia por transportadora. El tiempo de entrega depende de la ciudad y de si la referencia requiere marcación o confección sobre pedido.', 'hadara' ),
		),
		array(
			'q' => __( '¿Cómo pido una cotización?', 'hadara' ),
			'a' => __( 'Escríbanos por WhatsApp indicando las referencias, las tallas y las cantidades. Si no tiene claro qué necesita, cuéntenos la labor y el riesgo del puesto de trabajo y le proponemos la dotación adecuada.', 'hadara' ),
		),
	);

	/**
	 * Permite ajustar las preguntas frecuentes.
	 *
	 * @param array $items Preguntas y respuestas.
	 */
	return apply_filters( 'hadara_faq_items', $items );
}

/**
 * Construye el grafo JSON-LD de la vista actual.
 *
 * @return array
 */
function hadara_schema_graph() {
	$home         = trailingslashit( home_url() );
	$organization = $home . '#organization';
	$website      = $home . '#website';
	$graph        = array();

	$org = array(
		'@type' => array( 'Organization', 'OnlineBusiness' ),
		'@id'   => $organization,
		'name'  => hadara_brand( 'name' ),
		'url'   => $home,
		'description' => hadara_brand( 'tagline' ),
	);

	$logo = hadara_logo_image_data( 'dark' );

	if ( $logo ) {
		$org['logo'] = array(
			'@type'  => 'ImageObject',
			'url'    => $logo['url'],
			'width'  => $logo['width'],
			'height' => $logo['height'],
		);
		$org['image'] = $logo['url'];
	}

	if ( hadara_brand( 'area_served' ) ) {
		$org['areaServed'] = array(
			'@type' => 'Country',
			'name'  => hadara_brand( 'area_served' ),
		);
	}

	$contact = array();

	if ( hadara_brand( 'whatsapp' ) ) {
		$contact['telephone'] = '+' . preg_replace( '/\D/', '', hadara_brand( 'whatsapp' ) );
	}

	if ( hadara_brand( 'email' ) ) {
		$contact['email'] = hadara_brand( 'email' );
	}

	$hours_specs = array();

	foreach ( (array) hadara_brand( 'opening_hours' ) as $slot ) {
		if ( empty( $slot['days'] ) || empty( $slot['opens'] ) || empty( $slot['closes'] ) ) {
			continue;
		}

		$hours_specs[] = array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => $slot['days'],
			'opens'     => $slot['opens'],
			'closes'    => $slot['closes'],
		);
	}

	if ( $contact || $hours_specs ) {
		$org = array_merge( $org, $contact );

		$point = array_merge(
			array(
				'@type'             => 'ContactPoint',
				'contactType'       => 'sales',
				'availableLanguage' => array( 'Spanish' ),
			),
			$contact
		);

		if ( $hours_specs ) {
			$point['hoursAvailable'] = $hours_specs;
			$org['openingHoursSpecification'] = $hours_specs;
		}

		$org['contactPoint'] = array( $point );
	}

	if ( hadara_has_address() ) {
		$address = array( '@type' => 'PostalAddress' );

		if ( hadara_brand( 'street' ) ) {
			$address['streetAddress'] = hadara_brand( 'street' );
		}
		if ( hadara_brand( 'city' ) ) {
			$address['addressLocality'] = hadara_brand( 'city' );
		}
		if ( hadara_brand( 'region' ) ) {
			$address['addressRegion'] = hadara_brand( 'region' );
		}
		if ( hadara_brand( 'postal_code' ) ) {
			$address['postalCode'] = hadara_brand( 'postal_code' );
		}

		$address['addressCountry'] = hadara_brand( 'country' );
		$org['address']            = $address;
		$org['@type']              = array( 'Organization', 'LocalBusiness' );
	}

	$profiles = array_filter(
		array(
			hadara_brand( 'facebook' ),
			hadara_brand( 'instagram' ),
			hadara_brand( 'linkedin' ),
		)
	);

	if ( $profiles ) {
		$org['sameAs'] = array_values( $profiles );
	}

	$graph[] = $org;

	$graph[] = array(
		'@type'           => 'WebSite',
		'@id'             => $website,
		'url'             => $home,
		'name'            => hadara_brand( 'name' ),
		'description'     => hadara_brand( 'tagline' ),
		'inLanguage'      => 'es-CO',
		'publisher'       => array( '@id' => $organization ),
		'potentialAction' => array(
			array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => $home . '?s={search_term_string}',
				),
				'query-input' => 'required name=search_term_string',
			),
		),
	);

	$canonical = hadara_canonical_url();

	if ( $canonical && ! is_front_page() ) {
		$webpage = array(
			'@type'      => 'WebPage',
			'@id'        => $canonical . '#webpage',
			'url'        => $canonical,
			'name'       => wp_get_document_title(),
			'isPartOf'   => array( '@id' => $website ),
			'about'      => array( '@id' => $organization ),
			'inLanguage' => 'es-CO',
		);

		$description = hadara_meta_description();

		if ( $description ) {
			$webpage['description'] = $description;
		}

		$social = hadara_social_image_data();

		if ( $social ) {
			$webpage['primaryImageOfPage'] = array(
				'@type' => 'ImageObject',
				'url'   => $social['url'],
			);
		}

		$graph[] = $webpage;
	}

	$trail = hadara_breadcrumb_trail();

	if ( ! is_front_page() && count( $trail ) > 1 ) {
		$items = array();

		foreach ( $trail as $index => $item ) {
			$entry = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $item['label'],
			);

			if ( $item['url'] ) {
				$entry['item'] = $item['url'];
			}

			$items[] = $entry;
		}

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $canonical . '#breadcrumb',
			'itemListElement' => $items,
		);
	}

	if ( is_front_page() ) {
		$questions = array();

		foreach ( hadara_faq_items() as $item ) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['a'],
				),
			);
		}

		if ( $questions ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $home . '#faq',
				'mainEntity' => $questions,
			);
		}
	}

	if ( is_singular( 'producto' ) ) {
		$permalink = get_permalink();
		$product   = array(
			'@type'       => 'Product',
			'@id'         => $permalink . '#product',
			'name'        => get_the_title(),
			'description' => hadara_meta_description(),
			'url'         => $permalink,
			'brand'       => array(
				'@type' => 'Brand',
				'name'  => hadara_brand( 'name' ),
			),
			'mainEntityOfPage' => array( '@id' => $permalink . '#webpage' ),
		);

		$images = array();

		foreach ( array_merge( array( (int) get_post_thumbnail_id() ), hadara_get_gallery() ) as $image_id ) {
			$src = $image_id ? wp_get_attachment_image_src( $image_id, 'hadara-detail' ) : false;

			if ( $src ) {
				$images[] = $src[0];
			}
		}

		if ( $images ) {
			$product['image'] = 1 === count( $images ) ? $images[0] : $images;
		}

		$terms = get_the_terms( get_the_ID(), 'linea' );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$product['category'] = $terms[0]->name;
		}

		$specs = hadara_get_specs();

		if ( $specs ) {
			$properties = array();

			foreach ( $specs as $label => $value ) {
				$properties[] = array(
					'@type' => 'PropertyValue',
					'name'  => $label,
					'value' => $value,
				);

				// Si hay una referencia interna en las fichas, se expone como MPN.
				if ( in_array( mb_strtolower( $label ), array( 'referencia', 'ref', 'sku', 'código', 'codigo' ), true ) ) {
					$product['mpn'] = $value;
					$product['sku'] = $value;
				}
			}

			$product['additionalProperty'] = $properties;
		}

		// Venta B2B por cotización: la oferta solo se publica si la ficha tiene
		// precio de referencia, porque Google marca como error un Offer sin price.
		$price = (int) get_post_meta( get_the_ID(), '_hadara_price', true );

		if ( $price ) {
			$product['offers'] = array(
				'@type'         => 'Offer',
				'url'           => $permalink,
				'price'         => $price,
				'priceCurrency' => 'COP',
				'availability'  => 'https://schema.org/InStock',
				'itemCondition' => 'https://schema.org/NewCondition',
				'seller'        => array( '@id' => $organization ),
			);
		}

		$graph[] = $product;
	}

	if ( is_post_type_archive( array( 'producto', 'product' ) ) || is_tax( array( 'linea', 'product_cat', 'product_tag' ) ) ) {
		$elements = array();
		$position = 0;
		$total    = 0;

		if ( have_posts() ) {
			global $wp_query;

			$total = (int) $wp_query->found_posts;

			foreach ( $wp_query->posts as $post ) {
				$position++;
				$elements[] = array(
					'@type'    => 'ListItem',
					'position' => $position,
					'url'      => get_permalink( $post ),
					'name'     => get_the_title( $post ),
				);
			}
		}

		$collection = array(
			'@type'      => 'CollectionPage',
			'@id'        => $canonical . '#collection',
			'name'       => wp_get_document_title(),
			'url'        => $canonical,
			'isPartOf'   => array( '@id' => $website ),
			'inLanguage' => 'es-CO',
		);

		if ( $elements ) {
			$collection['mainEntity'] = array(
				'@type'           => 'ItemList',
				'numberOfItems'   => $total ? $total : count( $elements ),
				'itemListElement' => $elements,
			);
		}

		$graph[] = $collection;
	}

	/**
	 * Permite añadir o modificar nodos del grafo.
	 *
	 * @param array $graph Nodos JSON-LD.
	 */
	return apply_filters( 'hadara_schema_graph', $graph );
}

/**
 * Imprime el JSON-LD.
 */
function hadara_print_schema() {
	if ( ! hadara_manages_seo() ) {
		return;
	}

	$graph = hadara_schema_graph();

	if ( ! $graph ) {
		return;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'hadara_print_schema', 20 );
