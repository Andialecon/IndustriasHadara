<?php
/**
 * Cabecera del sitio.
 *
 * @package Hadara
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#contenido"><?php esc_html_e( 'Saltar al contenido', 'hadara' ); ?></a>

<?php
// En la tienda la barra superior muestra la oferta de compra (envío y pago).
$hadara_announcement = function_exists( 'hadara_shop_announcement' ) ? hadara_shop_announcement() : '';
?>
<?php if ( $hadara_announcement ) : ?>
<div class="topbar topbar--shop">
	<div class="container topbar__inner">
		<p class="topbar__claim"><?php echo esc_html( $hadara_announcement ); ?></p>
	</div>
</div>
<?php else : ?>
<div class="topbar">
	<div class="container topbar__inner">
		<p class="topbar__claim"><?php echo hadara_icon( 'truck' ); ?><?php esc_html_e( 'Ropa de trabajo y calzado de seguridad con despacho a todo el país', 'hadara' ); ?></p>
		<?php if ( hadara_brand( 'phone_label' ) ) : ?>
			<a class="topbar__phone" href="tel:+<?php echo esc_attr( preg_replace( '/\D/', '', hadara_brand( 'whatsapp' ) ) ); ?>">
				<?php echo hadara_icon( 'phone' ); ?><?php echo esc_html( hadara_brand( 'phone_label' ) ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
<?php endif; ?>

<header class="site-header" id="cabecera">
	<div class="container site-header__inner">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"
			aria-label="<?php echo esc_attr( sprintf( /* translators: %s: nombre del sitio. */ __( '%s, ir al inicio', 'hadara' ), hadara_brand( 'name' ) ) ); ?>">
			<?php echo hadara_logo( 'dark' ); ?>
		</a>

		<?php echo function_exists( 'hadara_cart_link' ) ? hadara_cart_link() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal">
			<span class="nav-toggle__bars" aria-hidden="true"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Abrir el menú', 'hadara' ); ?></span>
		</button>

		<nav class="site-nav" id="menu-principal" aria-label="<?php esc_attr_e( 'Menú principal', 'hadara' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'hadara_nav_fallback',
				)
			);
			?>

			<div class="site-nav__cta">
				<?php echo hadara_whatsapp_button(); ?>
			</div>
		</nav>
	</div>
</header>

<?php hadara_breadcrumbs(); ?>

<main class="site-main" id="contenido">
