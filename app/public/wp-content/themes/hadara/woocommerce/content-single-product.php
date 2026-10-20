<?php
/**
 * Página de venta de un producto de la tienda.
 *
 * Sustituye la ficha estándar de WooCommerce por una página pensada para
 * convertir, con el orden habitual de las tiendas de venta directa:
 *
 * 1. Fotos, título, gancho, precio, beneficios clave y compra (con tallas en
 *    botones, entrega estimada y sellos de confianza).
 * 2. Beneficios completos y una segunda llamada a la compra.
 * 3. Fotos grandes («Míralo en detalle»).
 * 4. Cómo comprar, descripción, guía de tallas, testimonios y preguntas.
 * 5. Cierre con precio y botón, y barra fija de compra en el móvil.
 *
 * Todo lo que se muestra sale de datos reales del producto o de la tienda; las
 * secciones sin datos no aparecen. Si la visita llega desde un anuncio, el CSS
 * oculta además la navegación del sitio (ver hadara_landing_mode_script()).
 *
 * @package Hadara
 */

defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$hadara_sales = hadara_sales_data( $product );

$hadara_wa = hadara_whatsapp_url(
	sprintf(
		/* translators: 1: producto, 2: enlace. */
		__( 'Hola, tengo una pregunta sobre %1$s (%2$s).', 'hadara' ),
		get_the_title(),
		get_permalink()
	)
);

/**
 * Botón secundario que lleva a la compra.
 *
 * @param string $label Texto.
 */
$hadara_cta = static function ( $label ) {
	printf(
		'<a class="btn btn--accent btn--lg sales-cta" href="#comprar" data-sales-buy>%s</a>',
		esc_html( $label )
	);
};
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'sales-page', $product ); ?>>

	<section class="sales-hero">
		<div class="sales-hero__media">
			<?php
			woocommerce_show_product_sale_flash();
			woocommerce_show_product_images();
			?>
		</div>

		<div class="sales-hero__info">
			<?php the_title( '<h1 class="product_title sales-hero__title">', '</h1>' ); ?>

			<?php if ( $hadara_sales['hook'] ) : ?>
				<p class="sales-hero__hook"><?php echo esc_html( $hadara_sales['hook'] ); ?></p>
			<?php endif; ?>

			<div class="sales-price">
				<?php woocommerce_template_single_price(); ?>
				<?php if ( $hadara_sales['savings'] ) : ?>
					<span class="sales-price__save"><?php echo esc_html( $hadara_sales['savings'] ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $hadara_sales['online_price'] ) ) : ?>
				<p class="sales-price__online"><?php echo hadara_icon( 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $hadara_sales['online_price'] ); ?></span></p>
			<?php endif; ?>

			<?php if ( $hadara_sales['benefits'] ) : ?>
				<ul class="sales-checks">
					<?php foreach ( array_slice( $hadara_sales['benefits'], 0, 3 ) as $hadara_benefit ) : ?>
						<li><?php echo hadara_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $hadara_benefit ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="sales-buy" id="comprar">
				<?php if ( $hadara_sales['low_stock'] ) : ?>
					<p class="sales-buy__stock"><span class="sales-buy__pulse" aria-hidden="true"></span><?php echo esc_html( $hadara_sales['low_stock'] ); ?></p>
				<?php endif; ?>

				<?php woocommerce_template_single_add_to_cart(); ?>

				<?php if ( $hadara_sales['size_guide'] ) : ?>
					<a class="sales-buy__guide" href="#guia-de-tallas"><?php echo hadara_icon( 'ruler' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Ver guía de tallas', 'hadara' ); ?></a>
				<?php endif; ?>

				<?php if ( $hadara_sales['delivery'] ) : ?>
					<p class="sales-buy__delivery" data-delivery-min="<?php echo (int) $hadara_sales['delivery']['min']; ?>" data-delivery-max="<?php echo (int) $hadara_sales['delivery']['max']; ?>">
						<?php echo hadara_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span data-delivery-text>
							<?php
							printf(
								/* translators: 1: días mínimos, 2: días máximos. */
								esc_html__( 'Entrega estimada: de %1$d a %2$d días hábiles', 'hadara' ),
								(int) $hadara_sales['delivery']['min'],
								(int) $hadara_sales['delivery']['max']
							);
							?>
						</span>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $hadara_sales['trust'] ) : ?>
				<ul class="sales-trust sales-trust--<?php echo count( $hadara_sales['trust'] ); ?>">
					<?php foreach ( $hadara_sales['trust'] as $hadara_icon => $hadara_text ) : ?>
						<li><?php echo hadara_icon( $hadara_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $hadara_text ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $hadara_wa ) : ?>
				<p class="sales-hero__help">
					<?php esc_html_e( '¿Tiene dudas antes de comprar?', 'hadara' ); ?>
					<a href="<?php echo esc_url( $hadara_wa ); ?>" target="_blank" rel="noopener nofollow"><?php echo hadara_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Escríbanos por WhatsApp', 'hadara' ); ?></a>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( count( $hadara_sales['benefits'] ) > 3 ) : ?>
		<section class="sales-section">
			<h2 class="sales-section__title"><?php esc_html_e( 'Por qué le va a encantar', 'hadara' ); ?></h2>
			<ul class="sales-benefits">
				<?php foreach ( $hadara_sales['benefits'] as $hadara_benefit ) : ?>
					<li><?php echo hadara_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $hadara_benefit ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<div class="sales-section__cta"><?php $hadara_cta( __( 'Lo quiero', 'hadara' ) ); ?></div>
		</section>
	<?php endif; ?>

	<?php if ( count( $hadara_sales['photos'] ) > 1 ) : ?>
		<section class="sales-section sales-photos">
			<h2 class="sales-section__title"><?php esc_html_e( 'Míralo en detalle', 'hadara' ); ?></h2>
			<div class="sales-photos__grid">
				<?php foreach ( $hadara_sales['photos'] as $hadara_photo ) : ?>
					<figure class="sales-photos__item">
						<?php
						echo wp_get_attachment_image(
							$hadara_photo,
							'woocommerce_single',
							false,
							array(
								'loading' => 'lazy',
								'sizes'   => '(max-width: 860px) 100vw, 410px',
								'alt'     => get_the_title(),
							)
						);
						?>
					</figure>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $hadara_sales['cod'] ) : ?>
		<section class="sales-section sales-steps">
			<h2 class="sales-section__title"><?php esc_html_e( 'Compre fácil y pague al recibir', 'hadara' ); ?></h2>
			<ol class="sales-steps__list">
				<li>
					<strong><?php esc_html_e( 'Haga su pedido', 'hadara' ); ?></strong>
					<span><?php esc_html_e( 'Solo su nombre, celular y dirección. Sin tarjetas.', 'hadara' ); ?></span>
				</li>
				<li>
					<strong><?php esc_html_e( 'Lo despachamos', 'hadara' ); ?></strong>
					<span><?php esc_html_e( 'Le confirmamos el pedido y lo enviamos por transportadora.', 'hadara' ); ?></span>
				</li>
				<li>
					<strong><?php esc_html_e( 'Paga al recibir', 'hadara' ); ?></strong>
					<span><?php esc_html_e( 'Paga en efectivo cuando el producto llega a su puerta.', 'hadara' ); ?></span>
				</li>
			</ol>
		</section>
	<?php endif; ?>

	<?php if ( get_the_content() ) : ?>
		<section class="sales-section sales-description">
			<h2 class="sales-section__title"><?php esc_html_e( 'Detalles del producto', 'hadara' ); ?></h2>
			<div class="prose"><?php the_content(); ?></div>
		</section>
	<?php endif; ?>

	<?php if ( $hadara_sales['size_guide'] ) : ?>
		<section class="sales-section sales-sizes" id="guia-de-tallas">
			<h2 class="sales-section__title"><?php esc_html_e( 'Guía de tallas', 'hadara' ); ?></h2>
			<div class="sales-sizes__scroll">
				<table class="sales-sizes__table">
					<?php foreach ( $hadara_sales['size_guide'] as $hadara_index => $hadara_row ) : ?>
						<tr>
							<?php foreach ( $hadara_row as $hadara_cell ) : ?>
								<?php if ( 0 === $hadara_index ) : ?>
									<th scope="col"><?php echo esc_html( $hadara_cell ); ?></th>
								<?php else : ?>
									<td><?php echo esc_html( $hadara_cell ); ?></td>
								<?php endif; ?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
			<?php if ( $hadara_wa ) : ?>
				<p class="sales-sizes__help"><?php esc_html_e( '¿Entre dos tallas?', 'hadara' ); ?> <a href="<?php echo esc_url( $hadara_wa ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'Pregúntenos por WhatsApp', 'hadara' ); ?></a></p>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php if ( $hadara_sales['testimonials'] ) : ?>
		<section class="sales-section sales-reviews">
			<h2 class="sales-section__title"><?php esc_html_e( 'Lo que dicen nuestros clientes', 'hadara' ); ?></h2>
			<div class="sales-reviews__grid">
				<?php foreach ( $hadara_sales['testimonials'] as $hadara_review ) : ?>
					<blockquote class="sales-reviews__item">
						<p><?php echo esc_html( $hadara_review['text'] ); ?></p>
						<footer><strong><?php echo esc_html( $hadara_review['name'] ); ?></strong><?php echo $hadara_review['city'] ? ' · ' . esc_html( $hadara_review['city'] ) : ''; ?></footer>
					</blockquote>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $hadara_sales['faq'] ) : ?>
		<section class="sales-section sales-faq">
			<h2 class="sales-section__title"><?php esc_html_e( 'Preguntas frecuentes', 'hadara' ); ?></h2>
			<?php foreach ( $hadara_sales['faq'] as $hadara_item ) : ?>
				<details class="sales-faq__item">
					<summary><?php echo esc_html( $hadara_item['q'] ); ?></summary>
					<p><?php echo esc_html( $hadara_item['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<section class="sales-final">
		<p class="sales-final__title"><?php the_title(); ?></p>
		<div class="sales-final__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<?php $hadara_cta( __( 'Comprar ahora', 'hadara' ) ); ?>
		<?php if ( $hadara_sales['trust'] ) : ?>
			<p class="sales-final__note"><?php echo esc_html( implode( ' · ', $hadara_sales['trust'] ) ); ?></p>
		<?php endif; ?>
	</section>

	<div class="sales-related">
		<?php woocommerce_output_related_products(); ?>
	</div>
</div>

<div class="sales-bar" data-sales-bar hidden>
	<div class="sales-bar__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
	<a class="btn btn--accent" href="#comprar" data-sales-buy><?php esc_html_e( 'Comprar ahora', 'hadara' ); ?></a>
</div>

<?php
// El marcado Product de WooCommerce se genera en el resumen estándar, que esta
// plantilla no usa: se pide aquí.
if ( isset( WC()->structured_data ) ) {
	WC()->structured_data->generate_product_data( $product );
}

do_action( 'woocommerce_after_single_product' );
