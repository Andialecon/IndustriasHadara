<?php
/**
 * Formulario de búsqueda.
 *
 * @package Hadara
 */

$hadara_search_id = wp_unique_id( 'buscar-' );
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $hadara_search_id ); ?>">
		<?php esc_html_e( 'Buscar en el catálogo', 'hadara' ); ?>
	</label>
	<input type="search" id="<?php echo esc_attr( $hadara_search_id ); ?>" name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Overol, camisa en jean, botas…', 'hadara' ); ?>">
	<button class="btn btn--accent" type="submit"><?php esc_html_e( 'Buscar', 'hadara' ); ?></button>
</form>
