<?php
/**
 * Sincronización de inventario con Dropi.
 *
 * Dropify trae una opción «Sincronizar stock», pero su tarea programada está
 * desactivada en el propio plugin (update_stock_job() termina con un
 * «return true» en la primera línea) y además consulta un endpoint que
 * responde «No tiene permisos». Aquí se reemplaza: cada hora se consulta
 * products/v2 —el mismo que usa Dropify para importar— y se actualizan las
 * existencias de los productos simples y de cada talla o color.
 *
 * Solo toca el inventario, nunca los precios.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL base de la API de integraciones de Dropi según el país de la tienda.
 *
 * @return string
 */
function hadara_dropi_api_url() {
	$urls = array(
		'CO' => 'https://api.dropi.co/integrations/',
		'PA' => 'https://api.dropi.pa/integrations/',
		'MX' => 'https://api.dropi.mx/integrations/',
		'EC' => 'https://api.dropi.ec/integrations/',
		'CL' => 'https://api.dropi.cl/integrations/',
	);

	$country = wc_get_base_location()['country'];

	return isset( $urls[ $country ] ) ? $urls[ $country ] : $urls['CO'];
}

/**
 * Lee un dato serializado que Dropify guarda (a veces dos veces serializado).
 *
 * @param int    $post_id Producto o variación.
 * @param string $key     Clave del meta.
 * @return object|null
 */
function hadara_dropi_meta( $post_id, $key ) {
	$value = get_post_meta( $post_id, $key, true );

	if ( is_string( $value ) && is_serialized( $value ) ) {
		$value = unserialize( $value, array( 'allowed_classes' => array( 'stdClass' ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
	}

	return is_object( $value ) ? $value : null;
}

/**
 * Suma las existencias de todas las bodegas de un registro de Dropi.
 *
 * @param array $warehouses Bodegas con su campo stock.
 * @return int
 */
function hadara_dropi_sum_stock( $warehouses ) {
	$total = 0;

	foreach ( (array) $warehouses as $warehouse ) {
		$total += isset( $warehouse->stock ) ? max( 0, (int) $warehouse->stock ) : 0;
	}

	return $total;
}

/**
 * Consulta un producto en Dropi.
 *
 * @param int    $dropi_id ID del producto en Dropi.
 * @param string $token    Token de la tienda en Dropi.
 * @return object|WP_Error
 */
function hadara_dropi_fetch_product( $dropi_id, $token ) {
	$response = wp_remote_get(
		hadara_dropi_api_url() . 'products/v2/' . absint( $dropi_id ),
		array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type'          => 'application/json;charset=UTF-8',
				'dropi-integration-key' => $token,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ) );

	if ( empty( $body->isSuccess ) || empty( $body->objects ) ) {
		return new WP_Error( 'hadara_dropi', isset( $body->message ) ? $body->message : __( 'Respuesta inesperada de Dropi.', 'hadara' ) );
	}

	return $body->objects;
}

/**
 * Actualiza las existencias de un producto importado de Dropi.
 *
 * @param int $product_id Producto de WooCommerce.
 * @return true|WP_Error
 */
function hadara_dropi_sync_product( $product_id ) {
	$product = wc_get_product( $product_id );
	$dropi   = hadara_dropi_meta( $product_id, '_dropi_product' );
	$token   = (string) get_post_meta( $product_id, '_dropi_token', true );

	if ( ! $product || ! $dropi || empty( $dropi->id ) || '' === $token ) {
		return new WP_Error( 'hadara_dropi', __( 'El producto no está vinculado a Dropi.', 'hadara' ) );
	}

	$remote = hadara_dropi_fetch_product( $dropi->id, $token );

	if ( is_wp_error( $remote ) ) {
		update_post_meta( $product_id, '_hadara_dropi_stock_error', $remote->get_error_message() );

		return $remote;
	}

	if ( $product->is_type( 'variable' ) ) {
		// Existencias por variación de Dropi, indexadas por su ID.
		$remote_stock = array();

		foreach ( (array) $remote->variations as $variation ) {
			$remote_stock[ (string) $variation->id ] = hadara_dropi_sum_stock( isset( $variation->warehouse_product_variation ) ? $variation->warehouse_product_variation : array() );
		}

		foreach ( $product->get_children() as $variation_id ) {
			$local = hadara_dropi_meta( $variation_id, '_dropi_variation' );

			if ( ! $local || ! isset( $remote_stock[ (string) $local->id ] ) ) {
				continue;
			}

			$variation = wc_get_product( $variation_id );

			if ( ! $variation ) {
				continue;
			}

			$variation->set_manage_stock( true );
			$variation->set_stock_quantity( $remote_stock[ (string) $local->id ] );
			$variation->set_backorders( 'no' );
			$variation->save();
		}

		// El inventario se lleva por talla, no en el producto principal.
		if ( $product->get_manage_stock() ) {
			$product->set_manage_stock( false );
			$product->save();
		}

		WC_Product_Variable::sync( $product_id );
	} else {
		$stock = hadara_dropi_sum_stock( isset( $remote->warehouse_product ) ? $remote->warehouse_product : array() );

		// En Dropi es variable pero en la tienda se dejó como simple (por
		// ejemplo, al cambiar el tipo para fijar un precio): se suma el stock de
		// todas las variaciones en lugar de dejarlo en cero.
		if ( isset( $remote->type ) && 'VARIABLE' === $remote->type ) {
			$stock = 0;

			foreach ( (array) $remote->variations as $variation ) {
				$stock += hadara_dropi_sum_stock( isset( $variation->warehouse_product_variation ) ? $variation->warehouse_product_variation : array() );
			}

			update_post_meta( $product_id, '_hadara_dropi_type_mismatch', 1 );
		} else {
			delete_post_meta( $product_id, '_hadara_dropi_type_mismatch' );
		}

		$product->set_manage_stock( true );
		$product->set_stock_quantity( $stock );
		$product->set_backorders( 'no' );
		$product->save();
	}

	delete_post_meta( $product_id, '_hadara_dropi_stock_error' );
	update_post_meta( $product_id, '_hadara_dropi_stock_synced', time() );

	return true;
}

/**
 * Sincroniza todos los productos de Dropi.
 *
 * @return array{ok:int,errors:int}
 */
function hadara_dropi_sync_all() {
	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'private', 'draft' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_dropi_product_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	$result = array(
		'ok'     => 0,
		'errors' => 0,
	);

	foreach ( $ids as $id ) {
		if ( true === hadara_dropi_sync_product( $id ) ) {
			$result['ok']++;
		} else {
			$result['errors']++;
		}
	}

	update_option( 'hadara_dropi_stock_last_run', array( 'time' => time() ) + $result, false );

	return $result;
}

/*
 * Tarea programada cada hora.
 */
add_action( 'hadara_dropi_stock_sync', 'hadara_dropi_sync_all' );

/**
 * Programa la sincronización horaria si no lo está.
 */
function hadara_dropi_schedule() {
	if ( ! wp_next_scheduled( 'hadara_dropi_stock_sync' ) ) {
		wp_schedule_event( time() + 300, 'hourly', 'hadara_dropi_stock_sync' );
	}
}
add_action( 'init', 'hadara_dropi_schedule' );

/**
 * Al importar un producto de Dropi, su inventario se sincroniza un minuto
 * después (Dropify crea las variaciones al final de la importación).
 *
 * @param int    $meta_id    ID del meta.
 * @param int    $object_id  Producto.
 * @param string $meta_key   Clave.
 */
function hadara_dropi_sync_after_import( $meta_id, $object_id, $meta_key ) {
	if ( '_dropi_product_id' === $meta_key && 'product' === get_post_type( $object_id ) ) {
		wp_schedule_single_event( time() + 60, 'hadara_dropi_stock_sync_one', array( (int) $object_id ) );
	}
}
add_action( 'added_post_meta', 'hadara_dropi_sync_after_import', 10, 3 );
add_action( 'hadara_dropi_stock_sync_one', 'hadara_dropi_sync_product' );

/*
 * ---------------------------------------------------------------------------
 * Sincronización manual desde el administrador
 * ---------------------------------------------------------------------------
 */

/**
 * Enlace «Sincronizar stock con Dropi» en el listado de productos.
 *
 * @param array   $actions Acciones de la fila.
 * @param WP_Post $post    Producto.
 * @return array
 */
function hadara_dropi_row_action( $actions, $post ) {
	if ( 'product' !== $post->post_type || ! get_post_meta( $post->ID, '_dropi_product_id', true ) || ! current_user_can( 'edit_product', $post->ID ) ) {
		return $actions;
	}

	$url = wp_nonce_url( admin_url( 'admin-post.php?action=hadara_dropi_stock&product_id=' . $post->ID ), 'hadara_dropi_stock_' . $post->ID );

	$actions['hadara_dropi_stock'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Sincronizar stock con Dropi', 'hadara' ) . '</a>';

	return $actions;
}
add_filter( 'post_row_actions', 'hadara_dropi_row_action', 10, 2 );

/**
 * Procesa la sincronización manual de un producto.
 */
function hadara_dropi_manual_sync() {
	$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;

	check_admin_referer( 'hadara_dropi_stock_' . $product_id );

	if ( ! current_user_can( 'edit_product', $product_id ) ) {
		wp_die( esc_html__( 'No tiene permisos para editar este producto.', 'hadara' ) );
	}

	$result = hadara_dropi_sync_product( $product_id );

	wp_safe_redirect(
		add_query_arg(
			array(
				'post_type'          => 'product',
				'hadara_dropi_stock' => true === $result ? 'ok' : 'error',
			),
			admin_url( 'edit.php' )
		)
	);
	exit;
}
add_action( 'admin_post_hadara_dropi_stock', 'hadara_dropi_manual_sync' );

/**
 * Aviso tras la sincronización manual.
 */
function hadara_dropi_admin_notice() {
	if ( empty( $_GET['hadara_dropi_stock'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$ok = 'ok' === $_GET['hadara_dropi_stock']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	printf(
		'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
		$ok ? 'success' : 'error',
		esc_html( $ok ? __( 'Inventario actualizado con las existencias de Dropi.', 'hadara' ) : __( 'No se pudo consultar Dropi. Revise el token de la tienda en el menú Dropi.', 'hadara' ) )
	);
}
add_action( 'admin_notices', 'hadara_dropi_admin_notice' );

/**
 * Las tallas o colores agotados aparecen deshabilitados en el selector.
 *
 * @param bool                 $active    Si la variación se puede elegir.
 * @param WC_Product_Variation $variation Variación.
 * @return bool
 */
function hadara_disable_out_of_stock_variations( $active, $variation ) {
	return $variation->is_in_stock() ? $active : false;
}
add_filter( 'woocommerce_variation_is_active', 'hadara_disable_out_of_stock_variations', 10, 2 );

/*
 * ---------------------------------------------------------------------------
 * Pedidos que Dropi rechaza
 * ---------------------------------------------------------------------------
 *
 * Si Dropi rechaza un pedido (por ejemplo, porque la ganancia queda en cero
 * tras el flete), Dropify solo lo anota en un campo oculto: el cliente ve su
 * pedido confirmado y nadie se entera. Aquí se deja una nota en el pedido, se
 * avisa por correo al administrador y se muestra un aviso en el escritorio.
 */

/**
 * Reacciona cuando Dropify guarda el resultado de enviar un pedido.
 *
 * @param int    $meta_id   ID del meta.
 * @param int    $object_id Pedido.
 * @param string $meta_key  Clave.
 * @param mixed  $value     Valor.
 */
function hadara_dropi_order_result( $meta_id, $object_id, $meta_key, $value ) {
	if ( '_is_dropi_order' !== $meta_key || ! is_string( $value ) || 0 !== strpos( $value, 'Dropi sync error' ) ) {
		return;
	}

	$order = wc_get_order( $object_id );

	if ( ! $order ) {
		return;
	}

	$reason = trim( str_replace( 'Dropi sync error:', '', $value ) );

	/* translators: %s: motivo que devuelve Dropi. */
	$order->add_order_note( sprintf( __( 'Dropi NO recibió este pedido: %s. Corrija la causa y use «Send to dropi» en el listado de pedidos, o cree el pedido directamente en Dropi.', 'hadara' ), $reason ) );

	wp_mail(
		get_option( 'admin_email' ),
		/* translators: %d: número de pedido. */
		sprintf( __( '[Tienda] Dropi rechazó el pedido #%d', 'hadara' ), $order->get_id() ),
		sprintf(
			/* translators: 1: número de pedido, 2: motivo, 3: enlace al pedido. */
			__( "El pedido #%1\$d se confirmó en la tienda, pero Dropi no lo recibió.\n\nMotivo: %2\$s\n\nEl cliente cree que su pedido está en camino. Revíselo aquí:\n%3\$s", 'hadara' ),
			$order->get_id(),
			$reason,
			$order->get_edit_order_url()
		)
	);
}
add_action( 'added_post_meta', 'hadara_dropi_order_result', 10, 4 );
add_action( 'updated_post_meta', 'hadara_dropi_order_result', 10, 4 );

/**
 * Aviso en el escritorio mientras haya pedidos en curso que Dropi no tiene.
 */
function hadara_dropi_failed_orders_notice() {
	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}

	$failed = wc_get_orders(
		array(
			'limit'        => 20,
			'status'       => array( 'processing', 'on-hold' ),
			'return'       => 'ids',
			'meta_key'     => '_is_dropi_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'   => 'Dropi sync error', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_compare' => 'LIKE',
		)
	);

	if ( ! $failed ) {
		return;
	}

	$links = array_map(
		static function ( $id ) {
			$order = wc_get_order( $id );

			return $order ? '<a href="' . esc_url( $order->get_edit_order_url() ) . '">#' . (int) $id . '</a>' : '';
		},
		$failed
	);

	printf(
		'<div class="notice notice-error"><p><strong>%1$s</strong> %2$s %3$s</p></div>',
		esc_html__( 'Dropi rechazó pedidos que el cliente ve confirmados:', 'hadara' ),
		wp_kses_post( implode( ', ', array_filter( $links ) ) ),
		esc_html__( 'Abra cada pedido para ver el motivo.', 'hadara' )
	);
}
add_action( 'admin_notices', 'hadara_dropi_failed_orders_notice' );

/*
 * ---------------------------------------------------------------------------
 * Margen frente al costo de Dropi
 * ---------------------------------------------------------------------------
 */

/**
 * Costo en Dropi y precio de venta de un producto o variación.
 *
 * @param WC_Product $product Producto simple o variación.
 * @return array{cost:float,price:float}|null
 */
function hadara_dropi_cost( $product ) {
	$data = $product->is_type( 'variation' ) ? hadara_dropi_meta( $product->get_id(), '_dropi_variation' ) : hadara_dropi_meta( $product->get_id(), '_dropi_product' );

	if ( ! $data || ! isset( $data->sale_price ) ) {
		return null;
	}

	return array(
		'cost'  => (float) $data->sale_price,
		'price' => (float) $product->get_price(),
	);
}

/**
 * Caja «Margen con Dropi» en el editor del producto.
 */
function hadara_dropi_margin_box() {
	global $post;

	if ( $post && get_post_meta( $post->ID, '_dropi_product_id', true ) ) {
		add_meta_box( 'hadara-dropi-margin', __( 'Margen con Dropi', 'hadara' ), 'hadara_render_dropi_margin_box', 'product', 'side', 'high' );
	}
}
add_action( 'add_meta_boxes_product', 'hadara_dropi_margin_box' );

/**
 * Pinta la caja «Margen con Dropi».
 *
 * @param WP_Post $post Producto.
 */
function hadara_render_dropi_margin_box( $post ) {
	$product = wc_get_product( $post->ID );
	$rows    = array();

	if ( $product && $product->is_type( 'variable' ) ) {
		foreach ( $product->get_children() as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			$cost      = $variation ? hadara_dropi_cost( $variation ) : null;

			if ( $cost ) {
				$rows[] = array( wc_get_formatted_variation( $variation, true, false ) ) + $cost;
			}
		}
	} elseif ( $product ) {
		$cost = hadara_dropi_cost( $product );

		if ( $cost ) {
			$rows[] = array( __( 'Producto', 'hadara' ) ) + $cost;
		}
	}

	if ( ! $rows ) {
		echo '<p>' . esc_html__( 'Sin datos de costo de Dropi.', 'hadara' ) . '</p>';

		return;
	}

	echo '<table class="widefat striped"><thead><tr><th></th><th>' . esc_html__( 'Precio', 'hadara' ) . '</th><th>' . esc_html__( 'Costo', 'hadara' ) . '</th><th>' . esc_html__( 'Margen', 'hadara' ) . '</th></tr></thead><tbody>';

	foreach ( $rows as $row ) {
		$margin = $row['price'] - $row['cost'];

		printf(
			'<tr><td>%1$s</td><td>%2$s</td><td>%3$s</td><td style="font-weight:600;color:%5$s">%4$s</td></tr>',
			esc_html( $row[0] ),
			wp_kses_post( wc_price( $row['price'] ) ),
			wp_kses_post( wc_price( $row['cost'] ) ),
			wp_kses_post( wc_price( $margin ) ),
			$margin > 0 ? '#1d6b3a' : '#b42318'
		);
	}

	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'El margen es antes del flete y de la comisión de recaudo, que Dropi descuenta de él. Si no alcanza a cubrirlos, Dropi rechaza el pedido. Consulte el flete a cada ciudad en el cotizador de Dropi.', 'hadara' ) . '</p>';
}

/**
 * Aviso en el editor si un producto con tallas en Dropi quedó como simple.
 */
function hadara_dropi_type_mismatch_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'product' !== $screen->id || empty( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$product_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$dropi      = hadara_dropi_meta( $product_id, '_dropi_product' );
	$product    = wc_get_product( $product_id );

	if ( ! $dropi || ! $product || 'VARIABLE' !== ( isset( $dropi->type ) ? $dropi->type : '' ) || $product->is_type( 'variable' ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'Este producto tiene tallas o colores en Dropi, pero aquí está como producto simple.', 'hadara' ) . '</strong> ' . esc_html__( 'El cliente no puede elegir talla y el pedido llega a Dropi sin ella. Vuelva a importarlo desde Dropi como variable y cambie el precio en la pestaña «Variaciones» (Acciones masivas → Establecer precios normales).', 'hadara' ) . '</p></div>';
}
add_action( 'admin_notices', 'hadara_dropi_type_mismatch_notice' );
