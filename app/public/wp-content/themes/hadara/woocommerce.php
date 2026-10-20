<?php
/**
 * Plantilla de WooCommerce: tienda, categorías y ficha de producto.
 *
 * El carrito, la compra y la cuenta son páginas normales y usan page.php.
 *
 * @package Hadara
 */

get_header();

if ( ! is_singular( 'product' ) ) :
	$hadara_lead = '';

	if ( is_product_taxonomy() ) {
		$hadara_lead = term_description();
	} elseif ( is_shop() ) {
		$hadara_lead = get_the_excerpt( wc_get_page_id( 'shop' ) );
	}
	?>
	<section class="page-hero">
		<div class="container page-hero__inner">
			<p class="eyebrow"><?php esc_html_e( 'Tienda en línea', 'hadara' ); ?></p>
			<h1><?php woocommerce_page_title(); ?></h1>
			<?php if ( $hadara_lead ) : ?>
				<div class="page-hero__lead"><?php echo wp_kses_post( wpautop( $hadara_lead ) ); ?></div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<div class="shop">
	<div class="container">
		<?php woocommerce_content(); ?>
	</div>
</div>

<?php
get_footer();
