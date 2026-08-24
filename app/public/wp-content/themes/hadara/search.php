<?php
/**
 * Resultados de búsqueda.
 *
 * @package Hadara
 */

get_header();
?>

<section class="page-hero">
	<div class="container page-hero__inner">
		<h1>
			<?php
			printf(
				/* translators: %s: términos buscados. */
				esc_html__( 'Resultados para «%s»', 'hadara' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
		<p class="page-hero__lead">
			<?php
			printf(
				/* translators: %d: número de resultados. */
				esc_html( _n( '%d resultado en el sitio.', '%d resultados en el sitio.', (int) $wp_query->found_posts, 'hadara' ) ),
				(int) $wp_query->found_posts
			);
			?>
		</p>
		<?php get_search_form(); ?>
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
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
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
					esc_attr__( 'Paginación de resultados', 'hadara' ),
					wp_kses_post( $hadara_pagination )
				);
			}
			?>
		<?php else : ?>
			<p class="notice">
				<?php esc_html_e( 'No encontramos referencias con esos términos. Pruebe con el nombre de la prenda (overol, camisa en jean, pantalón) o escríbanos por WhatsApp y le confirmamos disponibilidad.', 'hadara' ); ?>
			</p>
			<p>
				<?php
				$hadara_archive = get_post_type_archive_link( 'producto' );

				if ( $hadara_archive ) {
					printf(
						'<a class="btn btn--accent" href="%s">%s</a>',
						esc_url( $hadara_archive ),
						esc_html__( 'Ver el catálogo completo', 'hadara' )
					);
				}
				?>
			</p>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
