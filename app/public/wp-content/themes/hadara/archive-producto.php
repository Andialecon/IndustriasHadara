<?php
/**
 * Catálogo completo y páginas de línea.
 *
 * @package Hadara
 */

get_header();

$hadara_term    = is_tax( 'linea' ) ? get_queried_object() : null;
$hadara_lines   = hadara_get_lines();
$hadara_archive = get_post_type_archive_link( 'producto' );
$hadara_total   = (int) $GLOBALS['wp_query']->found_posts;
?>

<section class="page-hero">
	<div class="container page-hero__inner">
		<p class="eyebrow"><?php esc_html_e( 'Catálogo', 'hadara' ); ?></p>
		<?php if ( $hadara_term ) : ?>
			<h1><?php echo esc_html( $hadara_term->name ); ?></h1>
			<?php if ( $hadara_term->description ) : ?>
				<p class="page-hero__lead"><?php echo esc_html( $hadara_term->description ); ?></p>
			<?php endif; ?>
		<?php else : ?>
			<h1><?php esc_html_e( 'Catálogo de dotación industrial', 'hadara' ); ?></h1>
			<p class="page-hero__lead">
				<?php esc_html_e( 'Ropa de trabajo, calzado de seguridad y elementos de protección personal organizados por línea. Cada ficha incluye tela, tallas y referencia para que pida exactamente lo que necesita.', 'hadara' ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>

<div class="container">
	<div class="catalog-layout">
		<aside class="catalog-sidebar">
			<h2 class="catalog-sidebar__title"><?php esc_html_e( 'Líneas', 'hadara' ); ?></h2>
			<ul class="catalog-filter">
				<li class="<?php echo $hadara_term ? '' : 'is-active'; ?>">
					<a href="<?php echo esc_url( $hadara_archive ); ?>"><?php esc_html_e( 'Todo el catálogo', 'hadara' ); ?></a>
				</li>
				<?php foreach ( $hadara_lines as $hadara_line ) : ?>
					<li class="<?php echo ( $hadara_term && $hadara_term->term_id === $hadara_line->term_id ) ? 'is-active' : ''; ?>">
						<a href="<?php echo esc_url( get_term_link( $hadara_line ) ); ?>">
							<span><?php echo esc_html( $hadara_line->name ); ?></span>
							<em><?php echo (int) $hadara_line->count; ?></em>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="catalog-aside-card">
				<h3><?php esc_html_e( '¿No sabe qué pedir?', 'hadara' ); ?></h3>
				<p><?php esc_html_e( 'Díganos la labor, el número de trabajadores y la ciudad. Le armamos la dotación completa por cargo y le enviamos la cotización.', 'hadara' ); ?></p>
				<?php echo hadara_whatsapp_button( array( 'class' => 'btn btn--whatsapp btn--block btn--sm' ) ); ?>
			</div>
		</aside>

		<div class="catalog-main">
			<?php if ( have_posts() ) : ?>
				<p class="catalog-results">
					<?php
					printf(
						/* translators: %s: número de referencias. */
						esc_html( _n( '%s referencia disponible', '%s referencias disponibles', $hadara_total, 'hadara' ) ),
						esc_html( number_format_i18n( $hadara_total ) )
					);
					?>
				</p>

				<div class="product-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						hadara_product_card( get_post() );
					endwhile;
					?>
				</div>

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
						esc_attr__( 'Paginación del catálogo', 'hadara' ),
						wp_kses_post( $hadara_pagination )
					);
				}
				?>
			<?php else : ?>
				<div class="notice">
					<p><?php esc_html_e( 'Todavía no hay referencias publicadas en esta línea.', 'hadara' ); ?></p>
					<?php echo hadara_whatsapp_button( array( 'label' => __( 'Consultar disponibilidad', 'hadara' ), 'class' => 'btn btn--whatsapp btn--sm' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
get_footer();
