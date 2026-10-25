<?php
/**
 * Hojreh — minimal, fast WooCommerce theme.
 *
 * @package Hojreh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HOJREH_VERSION', '1.0.0' );
define( 'HOJREH_DIR', get_template_directory() );
define( 'HOJREH_URI', get_template_directory_uri() );

$hojreh_includes = array(
	'inc/settings.php',
	'inc/icons.php',
	'inc/setup.php',
	'inc/template-tags.php',
	'inc/woocommerce.php',
	'inc/ajax-cart.php',
	'inc/performance.php',
);

foreach ( $hojreh_includes as $hojreh_file ) {
	require_once HOJREH_DIR . '/' . $hojreh_file;
}

unset( $hojreh_includes, $hojreh_file );
