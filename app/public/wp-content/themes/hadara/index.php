<?php
/**
 * Plantilla de reserva para listados de entradas.
 *
 * @package Hadara
 */

get_header();
?>

<section class="page-hero">
	<div class="container page-hero__inner">
		<h1>
			<?php
			if ( is_home() && ! is_front_page() ) {
				single_post_title();
			} else {
				esc_html_e( 'Publicaciones', 'hadara' );
			}
			?>
		</h1>
	</div>
</section>

<div class="page-content">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<ul class="entry-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li>
						<article <?php post_class( 'entry-card' ); ?>>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						</article>
					</li>
				<?php endwhile; ?>
			</ul>

			<?php
			$hadara_pagination = paginate_links(
				array(
					'prev_text' => __( 'Anterior', 'hadara' ),
					'next_text' => __( 'Siguiente', 'hadara' ),
				)
			);

			if ( $hadara_pagination ) {
				printf(
					'<nav class="pagination" aria-label="%s">%s</nav>',
					esc_attr__( 'Paginación', 'hadara' ),
					wp_kses_post( $hadara_pagination )
				);
			}
			?>
		<?php else : ?>
			<p class="notice"><?php esc_html_e( 'Todavía no hay publicaciones.', 'hadara' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
