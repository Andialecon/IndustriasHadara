<?php
/**
 * Página no encontrada.
 *
 * @package Hadara
 */

get_header();

$hadara_archive = get_post_type_archive_link( 'producto' );
$hadara_lines   = hadara_get_lines( 6 );
?>

<section class="page-hero">
	<div class="container page-hero__inner">
		<h1><?php esc_html_e( 'No encontramos esta página', 'hadara' ); ?></h1>
		<p class="page-hero__lead">
			<?php esc_html_e( 'Puede que la referencia haya cambiado de nombre. Busque la prenda o vuelva al catálogo.', 'hadara' ); ?>
		</p>
		<?php get_search_form(); ?>
	</div>
</section>

<div class="page-content">
	<div class="container">
		<?php if ( $hadara_archive ) : ?>
			<p>
				<a class="btn btn--accent" href="<?php echo esc_url( $hadara_archive ); ?>">
					<?php esc_html_e( 'Ir al catálogo', 'hadara' ); ?><?php echo hadara_icon( 'arrow' ); ?>
				</a>
			</p>
		<?php endif; ?>

		<?php if ( $hadara_lines ) : ?>
			<h2><?php esc_html_e( 'Líneas más consultadas', 'hadara' ); ?></h2>
			<div class="line-grid">
				<?php foreach ( $hadara_lines as $hadara_line ) : ?>
					<?php hadara_line_card( $hadara_line ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php
get_footer();
