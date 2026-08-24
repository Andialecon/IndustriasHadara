<?php
/**
 * Ficha de producto.
 *
 * @package Hadara
 */

get_header();

while ( have_posts() ) :
	the_post();

	$hadara_terms   = get_the_terms( get_the_ID(), 'linea' );
	$hadara_line    = ( $hadara_terms && ! is_wp_error( $hadara_terms ) ) ? $hadara_terms[0] : null;
	$hadara_specs   = hadara_get_specs();
	$hadara_gallery = hadara_get_gallery();
	$hadara_main_id = get_post_thumbnail_id();
	$hadara_related = $hadara_line ? get_posts(
		array(
			'post_type'      => 'producto',
			'posts_per_page' => 4,
			'post__not_in'   => array( get_the_ID() ),
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'linea',
					'field'    => 'term_id',
					'terms'    => $hadara_line->term_id,
				),
			),
		)
	) : array();
	?>

	<article <?php post_class( 'product' ); ?>>
		<div class="container product__top">
			<div class="product__gallery" data-gallery>
				<?php
				$hadara_thumbs = $hadara_main_id
					? array_values( array_filter( array_merge( array( $hadara_main_id ), $hadara_gallery ) ) )
					: array();
				$hadara_has_many = count( $hadara_thumbs ) > 1;
				?>
				<figure class="product__figure" data-zoom-root>
					<?php if ( $hadara_main_id ) : ?>
						<?php
						$hadara_main_zoom = wp_get_attachment_image_src( $hadara_main_id, 'full' );
						?>
						<div class="product__zoom-stage<?php echo $hadara_has_many ? ' product__zoom-stage--swipe' : ''; ?>">
							<?php
							echo wp_get_attachment_image(
								$hadara_main_id,
								'hadara-detail',
								false,
								array(
									'alt'               => get_the_title(),
									'data-gallery-main' => 'true',
									'data-zoom-src'     => $hadara_main_zoom ? $hadara_main_zoom[0] : '',
									'loading'           => 'eager',
									'decoding'          => 'sync',
									'fetchpriority'     => 'high',
								)
							);
							?>
							<div class="product__lens" data-zoom-lens hidden aria-hidden="true"></div>

							<?php if ( $hadara_has_many ) : ?>
								<button type="button" class="product__nav product__nav--prev" data-gallery-prev aria-label="<?php esc_attr_e( 'Imagen anterior', 'hadara' ); ?>">
									<?php echo hadara_icon( 'arrow' ); ?>
								</button>
								<button type="button" class="product__nav product__nav--next" data-gallery-next aria-label="<?php esc_attr_e( 'Imagen siguiente', 'hadara' ); ?>">
									<?php echo hadara_icon( 'arrow' ); ?>
								</button>
							<?php endif; ?>
						</div>
						<p class="product__zoom-hint">
							<?php echo hadara_icon( 'search' ); ?>
							<span>
								<?php
								echo $hadara_has_many
									? esc_html__( 'Deslice a los lados para ver más fotos. Pase el cursor para ampliar.', 'hadara' )
									: esc_html__( 'Pase el cursor o toque la imagen para ampliar', 'hadara' );
								?>
							</span>
						</p>
					<?php endif; ?>
				</figure>

				<?php if ( $hadara_has_many ) : ?>
					<div class="product__thumbs" role="list">
						<?php foreach ( $hadara_thumbs as $hadara_index => $hadara_thumb_id ) : ?>
							<?php
							$hadara_full   = wp_get_attachment_image_src( $hadara_thumb_id, 'hadara-detail' );
							$hadara_zoom   = wp_get_attachment_image_src( $hadara_thumb_id, 'full' );
							$hadara_srcset = wp_get_attachment_image_srcset( $hadara_thumb_id, 'hadara-detail' );
							?>
							<button type="button" class="product__thumb<?php echo 0 === $hadara_index ? ' is-active' : ''; ?>"
								data-gallery-thumb
								data-full="<?php echo esc_url( $hadara_full ? $hadara_full[0] : '' ); ?>"
								data-zoom="<?php echo esc_url( $hadara_zoom ? $hadara_zoom[0] : '' ); ?>"
								<?php echo $hadara_srcset ? 'data-srcset="' . esc_attr( $hadara_srcset ) . '"' : ''; ?>
								aria-pressed="<?php echo 0 === $hadara_index ? 'true' : 'false'; ?>"
								aria-label="<?php echo esc_attr( sprintf( __( 'Ver imagen %d', 'hadara' ), $hadara_index + 1 ) ); ?>">
								<?php echo wp_get_attachment_image( $hadara_thumb_id, 'thumbnail', false, array( 'alt' => '' ) ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="product__info">
				<?php if ( $hadara_line ) : ?>
					<a class="product__line" href="<?php echo esc_url( get_term_link( $hadara_line ) ); ?>">
						<?php echo hadara_icon( 'tag' ); ?><?php echo esc_html( $hadara_line->name ); ?>
					</a>
				<?php endif; ?>

				<h1><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="product__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>

				<div class="product__actions">
					<?php echo hadara_whatsapp_button( array( 'label' => __( 'Cotizar esta referencia', 'hadara' ), 'class' => 'btn btn--whatsapp btn--lg', 'post' => get_post() ) ); ?>
					<?php if ( hadara_brand( 'phone_label' ) ) : ?>
						<a class="btn btn--ghost btn--lg" href="tel:+<?php echo esc_attr( preg_replace( '/\D/', '', hadara_brand( 'whatsapp' ) ) ); ?>">
							<?php echo hadara_icon( 'phone' ); ?><?php echo esc_html( hadara_brand( 'phone_label' ) ); ?>
						</a>
					<?php endif; ?>
				</div>

				<p class="product__note">
					<?php echo hadara_icon( 'check' ); ?>
					<span><?php esc_html_e( 'Cotización por WhatsApp según cantidad y marcación. Indíquenos tallas y unidades y le respondemos el mismo día.', 'hadara' ); ?></span>
				</p>

				<?php if ( $hadara_specs ) : ?>
					<table class="specs">
						<caption><?php esc_html_e( 'Especificaciones', 'hadara' ); ?></caption>
						<tbody>
							<?php foreach ( $hadara_specs as $hadara_label => $hadara_value ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $hadara_label ); ?></th>
									<td><?php echo esc_html( $hadara_value ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( get_the_content() ) : ?>
			<div class="container">
				<div class="product__description prose">
					<h2><?php esc_html_e( 'Sobre esta referencia', 'hadara' ); ?></h2>
					<?php the_content(); ?>
				</div>
			</div>
		<?php endif; ?>
	</article>

	<?php if ( $hadara_related ) : ?>
		<section class="section section--bg">
			<div class="container">
				<div class="section__head">
					<p class="eyebrow"><?php esc_html_e( 'De la misma línea', 'hadara' ); ?></p>
					<h2><?php esc_html_e( 'Complete la dotación', 'hadara' ); ?></h2>
				</div>

				<div class="product-grid">
					<?php foreach ( $hadara_related as $hadara_item ) : ?>
						<?php hadara_product_card( $hadara_item ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
endwhile;

get_footer();
