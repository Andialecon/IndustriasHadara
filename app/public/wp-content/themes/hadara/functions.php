<?php
/**
 * Arranque del tema Industrias Hadara.
 *
 * @package Hadara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HADARA_VERSION', '1.4.0' );

require_once get_theme_file_path( 'inc/brand.php' );
require_once get_theme_file_path( 'inc/setup.php' );
require_once get_theme_file_path( 'inc/post-types.php' );
require_once get_theme_file_path( 'inc/template-tags.php' );
require_once get_theme_file_path( 'inc/seo.php' );
require_once get_theme_file_path( 'inc/schema.php' );
