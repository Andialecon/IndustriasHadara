<?php
/**
 * Portada.
 *
 * @package Hadara
 */

get_header();

$hadara_archive = get_post_type_archive_link( 'producto' );
$hadara_lines   = hadara_get_lines();
$hadara_featured = hadara_products_by_slug(
	array(
		'camisa-jean-7-onzas',
		'jean-caballero-14-onzas',
		'overol-en-dril-enterizo',
		'pantalon-en-dril-cuatro-bolsillos',
		'chaleco-en-dril-tipo-periodista',
		'bota-de-seguridad-blanca',
		'camisa-en-dril',
		'chaqueta-jean-para-soldador-14-onzas',
	),
	8
);
?>

<section class="hero">
	<div class="container hero__inner">
		<div class="hero__content">
			<p class="eyebrow"><?php echo hadara_icon( 'shield' ); ?><?php echo esc_html( hadara_hero_text( 'eyebrow' ) ); ?></p>
			<h1><?php echo esc_html( hadara_hero_text( 'title' ) ); ?> <span class="accent"><?php echo esc_html( hadara_hero_text( 'accent' ) ); ?></span></h1>
			<p class="hero__lead">
				<?php echo esc_html( hadara_hero_text( 'lead' ) ); ?>
			</p>

			<div class="hero__actions">
				<?php if ( $hadara_archive ) : ?>
					<a class="btn btn--accent btn--lg" href="<?php echo esc_url( $hadara_archive ); ?>">
						<?php esc_html_e( 'Ver el catálogo', 'hadara' ); ?><?php echo hadara_icon( 'arrow' ); ?>
					</a>
				<?php endif; ?>
				<?php echo hadara_whatsapp_button( array( 'label' => __( 'Pedir cotización', 'hadara' ), 'class' => 'btn btn--whatsapp btn--lg' ) ); ?>
			</div>

			<?php
			$hadara_hero_points = array_filter(
				array(
					hadara_hero_text( 'point_1' ),
					hadara_hero_text( 'point_2' ),
					hadara_hero_text( 'point_3' ),
					hadara_hero_text( 'point_4' ),
				)
			);
			?>
			<?php if ( $hadara_hero_points ) : ?>
				<ul class="hero__points">
					<?php foreach ( $hadara_hero_points as $hadara_point ) : ?>
						<li><?php echo hadara_icon( 'check' ); ?><?php echo esc_html( $hadara_point ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php echo hadara_hero_photo(); ?>
	</div>
</section>

<?php if ( $hadara_featured ) : ?>
	<section class="product-spotlight" data-spotlight aria-labelledby="product-spotlight-title">
		<div class="container product-spotlight__inner">
			<div class="product-spotlight__head">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Más populares', 'hadara' ); ?></p>
					<h2 id="product-spotlight-title"><?php esc_html_e( 'Las referencias que más piden las empresas', 'hadara' ); ?></h2>
				</div>
				<div class="product-spotlight__nav">
					<button type="button" class="product-spotlight__btn" data-spotlight-prev aria-controls="product-spotlight-track" aria-label="<?php esc_attr_e( 'Ver productos anteriores', 'hadara' ); ?>">
						<?php echo hadara_icon( 'chevron-left' ); ?>
					</button>
					<button type="button" class="product-spotlight__btn" data-spotlight-next aria-controls="product-spotlight-track" aria-label="<?php esc_attr_e( 'Ver más productos', 'hadara' ); ?>">
						<?php echo hadara_icon( 'chevron-right' ); ?>
					</button>
				</div>
			</div>

			<div class="product-spotlight__viewport" data-spotlight-scroller>
				<ul class="product-spotlight__track" id="product-spotlight-track">
					<?php foreach ( $hadara_featured as $hadara_index => $hadara_item ) : ?>
						<li class="product-spotlight__slide">
							<?php hadara_spotlight_card( $hadara_item, (int) $hadara_index ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( $hadara_lines ) : ?>
	<section class="section">
		<div class="container">
			<div class="section__head">
				<p class="eyebrow"><?php esc_html_e( 'Catálogo por líneas', 'hadara' ); ?></p>
				<h2><?php esc_html_e( 'Todo lo que necesita una dotación completa', 'hadara' ); ?></h2>
				<p class="section__lead">
					<?php esc_html_e( 'Organizamos el catálogo por líneas para que arme la dotación de cada cargo sin dar vueltas: desde la ropa de trabajo hasta la protección específica de cada riesgo.', 'hadara' ); ?>
				</p>
			</div>

			<div class="line-grid">
				<?php foreach ( $hadara_lines as $hadara_line ) : ?>
					<?php hadara_line_card( $hadara_line ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section section--bg">
	<div class="container">
		<div class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Por qué Industrias Hadara', 'hadara' ); ?></p>
			<h2><?php esc_html_e( 'Un proveedor que entiende el piso de planta', 'hadara' ); ?></h2>
			<p class="section__lead"><?php esc_html_e( 'Llevamos la dotación como lo que es: un tema de seguridad y de imagen, no una compra por catálogo.', 'hadara' ); ?></p>
		</div>

		<div class="feature-grid">
			<div class="feature">
				<span class="feature__icon"><?php echo hadara_icon( 'shield' ); ?></span>
				<h3><?php esc_html_e( 'Asesoría por puesto de trabajo', 'hadara' ); ?></h3>
				<p><?php esc_html_e( 'No vendemos una lista de referencias. Revisamos la labor de cada cargo y le proponemos la prenda que corresponde, con la talla y el acabado adecuados.', 'hadara' ); ?></p>
			</div>
			<div class="feature">
				<span class="feature__icon"><?php echo hadara_icon( 'ruler' ); ?></span>
				<h3><?php esc_html_e( 'Curva de tallas completa', 'hadara' ); ?></h3>
				<p><?php esc_html_e( 'Ropa de la S a la 5XL, pantalones de caballero de la 28 a la 48, de dama de la 6 a la 26 y calzado de la 34 a la 46. Con horma femenina real, no tallas pequeñas de molde masculino.', 'hadara' ); ?></p>
			</div>
			<div class="feature">
				<span class="feature__icon"><?php echo hadara_icon( 'tag' ); ?></span>
				<h3><?php esc_html_e( 'Marcación con su imagen', 'hadara' ); ?></h3>
				<p><?php esc_html_e( 'Bordamos o estampamos el logo institucional en camisas, chaquetas, overoles y chalecos, en la ubicación que defina su manual de marca.', 'hadara' ); ?></p>
			</div>
			<div class="feature">
				<span class="feature__icon"><?php echo hadara_icon( 'truck' ); ?></span>
				<h3><?php esc_html_e( 'Reposición sin sobresaltos', 'hadara' ); ?></h3>
				<p><?php esc_html_e( 'Mantenemos las referencias del catálogo para que la dotación de un trabajador nuevo sea idéntica a la del resto del equipo, sin cambios de tela ni de color.', 'hadara' ); ?></p>
			</div>
		</div>
	</div>
</section>

<?php if ( $hadara_featured ) : ?>
	<section class="section">
		<div class="container">
			<div class="section__head">
				<p class="eyebrow"><?php esc_html_e( 'Más solicitados', 'hadara' ); ?></p>
				<h2><?php esc_html_e( 'Las referencias que más rotan', 'hadara' ); ?></h2>
				<p class="section__lead"><?php esc_html_e( 'Prendas de trabajo que resuelven la mayoría de las dotaciones. Si no encuentra una referencia, escríbanos por WhatsApp.', 'hadara' ); ?></p>
			</div>

			<div class="product-grid">
				<?php foreach ( $hadara_featured as $hadara_item ) : ?>
					<?php hadara_product_card( $hadara_item ); ?>
				<?php endforeach; ?>
			</div>

			<?php if ( $hadara_archive ) : ?>
				<div class="section__foot">
					<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( $hadara_archive ); ?>">
						<?php esc_html_e( 'Ver todas las referencias', 'hadara' ); ?><?php echo hadara_icon( 'arrow' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<section class="section section--bg">
	<div class="container">
		<div class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Sectores', 'hadara' ); ?></p>
			<h2><?php esc_html_e( 'Dotamos empresas de todos los sectores', 'hadara' ); ?></h2>
		</div>

		<ul class="sector-list">
			<li><?php echo hadara_icon( 'check' ); ?><span><strong><?php esc_html_e( 'Industria y manufactura', 'hadara' ); ?></strong><span><?php esc_html_e( 'Overoles en dril, camisas en dril y jeans de 14 onzas para planta y mantenimiento.', 'hadara' ); ?></span></span></li>
			<li><?php echo hadara_icon( 'check' ); ?><span><strong><?php esc_html_e( 'Construcción y obra civil', 'hadara' ); ?></strong><span><?php esc_html_e( 'Chalecos tipo periodista, jeans reforzados y botas con puntera composite dieléctrica.', 'hadara' ); ?></span></span></li>
			<li><?php echo hadara_icon( 'check' ); ?><span><strong><?php esc_html_e( 'Metalmecánica y soldadura', 'hadara' ); ?></strong><span><?php esc_html_e( 'Camisas y chaquetas en jean de 14 onzas para soldador y pulimiento.', 'hadara' ); ?></span></span></li>
			<li><?php echo hadara_icon( 'check' ); ?><span><strong><?php esc_html_e( 'Alimentos y áreas limpias', 'hadara' ); ?></strong><span><?php esc_html_e( 'Camisas oxford, overoles en dril y bota blanca dieléctrica con puntera composite.', 'hadara' ); ?></span></span></li>
			<li><?php echo hadara_icon( 'check' ); ?><span><strong><?php esc_html_e( 'Energía y telecomunicaciones', 'hadara' ); ?></strong><span><?php esc_html_e( 'Jean dieléctrico sin herrajes metálicos y calzado dieléctrico.', 'hadara' ); ?></span></span></li>
			<li><?php echo hadara_icon( 'check' ); ?><span><strong><?php esc_html_e( 'Logística y servicios generales', 'hadara' ); ?></strong><span><?php esc_html_e( 'Pantalones en dril, camisas oxford y jeans cómodos para jornadas largas.', 'hadara' ); ?></span></span></li>
		</ul>
	</div>
</section>

<section class="section section--ink">
	<div class="container">
		<div class="section__head">
			<p class="eyebrow"><?php esc_html_e( 'Cómo compramos juntos', 'hadara' ); ?></p>
			<h2><?php esc_html_e( 'Tres pasos y su dotación queda resuelta', 'hadara' ); ?></h2>
		</div>

		<ol class="steps">
			<li>
				<h3><?php esc_html_e( 'Cuéntenos qué necesita', 'hadara' ); ?></h3>
				<p><?php esc_html_e( 'Escríbanos por WhatsApp con las referencias que le interesan, o simplemente describa la labor y el número de trabajadores. Nosotros armamos la propuesta.', 'hadara' ); ?></p>
			</li>
			<li>
				<h3><?php esc_html_e( 'Reciba la cotización', 'hadara' ); ?></h3>
				<p><?php esc_html_e( 'Le enviamos la cotización por referencia y por volumen, con las tallas disponibles, el valor de la marcación y el tiempo de entrega.', 'hadara' ); ?></p>
			</li>
			<li>
				<h3><?php esc_html_e( 'Despachamos y reponemos', 'hadara' ); ?></h3>
				<p><?php esc_html_e( 'Confirmada la orden, producimos la marcación y despachamos a su ciudad. Quedamos con su curva de tallas registrada para las reposiciones.', 'hadara' ); ?></p>
			</li>
		</ol>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="section__head section__head--center">
			<p class="eyebrow"><?php esc_html_e( 'Preguntas frecuentes', 'hadara' ); ?></p>
			<h2><?php esc_html_e( 'Lo que suelen preguntarnos antes de comprar', 'hadara' ); ?></h2>
		</div>

		<div class="faq">
			<?php foreach ( hadara_faq_items() as $hadara_index => $hadara_faq ) : ?>
				<details<?php echo 0 === $hadara_index ? ' open' : ''; ?>>
					<summary><?php echo esc_html( $hadara_faq['q'] ); ?></summary>
					<p><?php echo esc_html( $hadara_faq['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_footer();
