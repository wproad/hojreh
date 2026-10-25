<?php
/**
 * Search form.
 *
 * @package Hojreh
 */

$hojreh_unique_id = wp_unique_id( 'search-form-' );
?>
<form role="search" method="get" class="hojreh-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $hojreh_unique_id ); ?>">جستجو</label>
	<input type="search" id="<?php echo esc_attr( $hojreh_unique_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="جستجو…">
	<button type="submit" class="btn btn-primary btn-sm">جستجو</button>
</form>
