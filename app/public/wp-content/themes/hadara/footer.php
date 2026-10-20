<?php
/**
 * Pie del sitio.
 *
 * @package Hadara
 */

$hadara_lines = hadara_get_lines();
?>
</main>

<?php // La franja de cotización B2B no aplica a la tienda en línea. ?>
<?php if ( ! is_page_template( 'templates/contacto.php' ) && ! ( function_exists( 'hadara_is_shop_view' ) && hadara_is_shop_view() ) ) : ?>
<section class="cta-band">
	<div class="container cta-band__inner">
		<div>
			<h2 class="cta-band__title"><?php esc_html_e( '¿Necesita dotar a su equipo de trabajo?', 'hadara' ); ?></h2>
			<p class="cta-band__text"><?php esc_html_e( 'Cuéntenos la labor, las tallas y las cantidades. Le armamos la dotación completa y le enviamos la cotización el mismo día.', 'hadara' ); ?></p>
		</div>
	</div>
</section>
<?php endif; ?>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<a class="site-logo site-logo--light" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php echo hadara_logo( 'light' ); ?>
			</a>
			<p class="site-footer__about">
				<?php esc_html_e( 'Suministramos dotación industrial y elementos de protección personal a empresas de industria, construcción, alimentos, salud, minería y logística. Asesoramos la selección por puesto de trabajo y marcamos las prendas con su imagen corporativa.', 'hadara' ); ?>
			</p>
		</div>

		<?php if ( $hadara_lines ) : ?>
			<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Líneas del catálogo', 'hadara' ); ?>">
				<h2 class="site-footer__title"><?php esc_html_e( 'Líneas del catálogo', 'hadara' ); ?></h2>
				<ul class="site-footer__list">
					<?php foreach ( array_slice( $hadara_lines, 0, 8 ) as $hadara_line ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $hadara_line ) ); ?>"><?php echo esc_html( $hadara_line->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Enlaces del sitio', 'hadara' ); ?>">
			<h2 class="site-footer__title"><?php esc_html_e( 'El sitio', 'hadara' ); ?></h2>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'site-footer__list',
						'depth'          => 1,
					)
				);
			} else {
				echo '<ul class="site-footer__list">';
				$hadara_archive = get_post_type_archive_link( 'producto' );

				if ( $hadara_archive ) {
					printf( '<li><a href="%s">%s</a></li>', esc_url( $hadara_archive ), esc_html__( 'Catálogo completo', 'hadara' ) );
				}

				foreach ( array( 'nosotros' => __( 'Nosotros', 'hadara' ), 'contacto' => __( 'Contacto', 'hadara' ) ) as $hadara_slug => $hadara_label ) {
					$hadara_page = get_page_by_path( $hadara_slug );

					if ( $hadara_page ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $hadara_page ) ), esc_html( $hadara_label ) );
					}
				}

				echo '</ul>';
			}
			?>
		</nav>

		<div class="site-footer__col">
			<h2 class="site-footer__title"><?php esc_html_e( 'Contacto', 'hadara' ); ?></h2>
			<ul class="site-footer__contact">
				<?php if ( hadara_brand( 'phone_label' ) ) : ?>
					<li>
						<?php echo hadara_icon( 'phone' ); ?>
						<a href="tel:+<?php echo esc_attr( preg_replace( '/\D/', '', hadara_brand( 'whatsapp' ) ) ); ?>"><?php echo esc_html( hadara_brand( 'phone_label' ) ); ?></a>
					</li>
				<?php endif; ?>
				<?php if ( hadara_brand( 'email' ) ) : ?>
					<li>
						<?php echo hadara_icon( 'mail' ); ?>
						<a href="mailto:<?php echo esc_attr( hadara_brand( 'email' ) ); ?>"><?php echo esc_html( hadara_brand( 'email' ) ); ?></a>
					</li>
				<?php endif; ?>
				<?php if ( hadara_has_address() ) : ?>
					<li><?php echo hadara_icon( 'pin' ); ?><span><?php echo esc_html( hadara_address_line() ); ?></span></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>

	<div class="site-footer__bottom">
		<div class="container site-footer__bottom-inner">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( hadara_brand( 'name' ) ); ?>. <?php esc_html_e( 'Todos los derechos reservados.', 'hadara' ); ?></p>
			<?php
			if ( has_nav_menu( 'legal' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'legal',
						'container'      => false,
						'menu_class'     => 'site-footer__legal',
						'depth'          => 1,
					)
				);
			}
			?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
