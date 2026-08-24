<?php
/**
 * Template Name: Contacto
 * Template Post Type: page
 *
 * @package Hadara
 */

get_header();

$hadara_whatsapp_digits = preg_replace( '/\D/', '', hadara_brand( 'whatsapp' ) );

while ( have_posts() ) :
	the_post();
	?>
	<section class="page-hero">
		<div class="container page-hero__inner">
			<h1><?php the_title(); ?></h1>
			<p class="page-hero__lead">
				<?php
				echo has_excerpt()
					? esc_html( get_the_excerpt() )
					: esc_html__( 'Atendemos cotizaciones de dotación industrial y elementos de protección personal para empresas. Escríbanos por WhatsApp con la labor, las tallas y las cantidades.', 'hadara' );
				?>
			</p>
		</div>
	</section>

	<div class="contact-grid container">
		<div class="prose">
			<?php the_content(); ?>
		</div>

		<div class="contact-card">
			<h2><?php esc_html_e( 'Datos de contacto', 'hadara' ); ?></h2>
			<ul>
				<?php if ( hadara_brand( 'phone_label' ) ) : ?>
					<li>
						<?php echo hadara_icon( 'phone' ); ?>
						<span>
							<strong><?php esc_html_e( 'Teléfono y WhatsApp', 'hadara' ); ?></strong>
							<a href="tel:+<?php echo esc_attr( $hadara_whatsapp_digits ); ?>"><?php echo esc_html( hadara_brand( 'phone_label' ) ); ?></a>
						</span>
					</li>
				<?php endif; ?>

				<?php if ( hadara_brand( 'email' ) ) : ?>
					<li>
						<?php echo hadara_icon( 'mail' ); ?>
						<span>
							<strong><?php esc_html_e( 'Correo', 'hadara' ); ?></strong>
							<a href="mailto:<?php echo esc_attr( hadara_brand( 'email' ) ); ?>"><?php echo esc_html( hadara_brand( 'email' ) ); ?></a>
						</span>
					</li>
				<?php endif; ?>

				<?php if ( hadara_has_address() ) : ?>
					<li>
						<?php echo hadara_icon( 'pin' ); ?>
						<span>
							<strong><?php esc_html_e( 'Ubicación', 'hadara' ); ?></strong>
							<?php echo esc_html( hadara_address_line() ); ?>
						</span>
					</li>
				<?php endif; ?>

				<li>
					<?php echo hadara_icon( 'check' ); ?>
					<span>
						<strong><?php esc_html_e( 'Horario de atención', 'hadara' ); ?></strong>
						<?php echo esc_html( hadara_brand( 'hours_label' ) ); ?>
					</span>
				</li>
			</ul>

			<?php
			echo hadara_whatsapp_button(
				array(
					'label' => __( 'Pedir cotización por WhatsApp', 'hadara' ),
					'class' => 'btn btn--whatsapp btn--lg btn--block',
				)
			);
			?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
