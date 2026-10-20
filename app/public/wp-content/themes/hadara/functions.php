<?php
/**
 * Arranque del tema Industrias Hadara.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HADARA_VERSION', '1.10.1' );

require_once get_theme_file_path( 'inc/brand.php' );
require_once get_theme_file_path( 'inc/setup.php' );
require_once get_theme_file_path( 'inc/post-types.php' );
require_once get_theme_file_path( 'inc/template-tags.php' );
require_once get_theme_file_path( 'inc/seo.php' );
require_once get_theme_file_path( 'inc/schema.php' );

if ( class_exists( 'WooCommerce' ) ) {
	require_once get_theme_file_path( 'inc/woocommerce.php' );
}
