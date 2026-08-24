<?php
/**
 * Página estándar.
 *
 * @package Hadara
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="page-hero">
		<div class="container page-hero__inner">
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="page-hero__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<div class="page-content">
		<div class="container">
			<article <?php post_class( 'page-content__inner prose' ); ?>>
				<?php
				the_content();

				wp_link_pages(
					array(
						'before' => '<nav class="pagination">',
						'after'  => '</nav>',
					)
				);
				?>
			</article>
		</div>
	</div>
	<?php
endwhile;

get_footer();
