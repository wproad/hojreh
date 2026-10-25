<?php
/**
 * Front-end performance tweaks. No build step required.
 *
 * @package Hojreh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current page uses WooCommerce blocks (cart/checkout).
 *
 * @return bool
 */
function hojreh_page_uses_wc_blocks() {
	if ( ! is_singular() ) {
		return false;
	}

	$post = get_post();

	return $post && ( has_block( 'woocommerce/cart', $post ) || has_block( 'woocommerce/checkout', $post ) );
}

/**
 * Remove block styles on classic, shortcode-driven pages.
 */
function hojreh_dequeue_block_assets() {
	if ( is_admin() || hojreh_page_uses_wc_blocks() ) {
		return;
	}

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'core-block-supports' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wp-emoji-styles' );
	wp_dequeue_script( 'wp-embed' );
}
add_action( 'wp_enqueue_scripts', 'hojreh_dequeue_block_assets', 100 );

/**
 * Front-end cleanup: emoji, embeds and generator tags.
 */
function hojreh_clean_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_footer', 'wp_print_emoji_detection_script' );
}
add_action( 'init', 'hojreh_clean_head' );

/**
 * Stop WordPress from injecting block-theme global styles on this classic theme.
 */
function hojreh_remove_global_styles() {
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles_css_custom_properties' );
}
add_action( 'init', 'hojreh_remove_global_styles' );

/**
 * Load WooCommerce scripts only where its UI genuinely needs them.
 */
function hojreh_trim_wc_scripts() {
	if ( is_admin() ) {
		return;
	}

	foreach ( array( 'sourcebuster-js', 'wc-order-attribution' ) as $handle ) {
		wp_dequeue_script( $handle );
	}

	$is_store_flow = ( function_exists( 'is_product' ) && is_product() )
		|| ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() );

	if ( $is_store_flow ) {
		return;
	}

	$unneeded = array(
		'woocommerce',
		'wc-add-to-cart',
		'wc-cart-fragments',
		'jquery-blockui',
		'js-cookie',
		'selectWoo',
		'wc-cart',
		'wc-checkout',
		'wc-country-select',
		'wc-address-i18n',
		'wc-password-strength-meter',
	);

	foreach ( $unneeded as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'hojreh_trim_wc_scripts', 99 );

/**
 * Disable oEmbed endpoints entirely.
 */
function hojreh_disable_embeds() {
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );
	add_filter( 'embed_oembed_discover', '__return_false' );
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'hojreh_disable_embeds' );

/**
 * Do not load dashicons for anonymous visitors.
 */
function hojreh_dequeue_dashicons() {
	if ( ! is_admin() && ! is_user_logged_in() ) {
		wp_deregister_style( 'dashicons' );
	}
}
add_action( 'wp_enqueue_scripts', 'hojreh_dequeue_dashicons', 100 );

/**
 * Drop WooCommerce's cart-fragments script: the drawer has its own lightweight layer.
 *
 * @param array<int, string> $scripts Script handles.
 * @return array<int, string>
 */
function hojreh_dequeue_cart_fragments( $scripts ) {
	return array_values( array_diff( $scripts, array( 'wc-cart-fragments' ) ) );
}
add_filter( 'woocommerce_get_script_handles', 'hojreh_dequeue_cart_fragments' );

/**
 * Remove the WooCommerce block styles that classic pages do not need.
 */
function hojreh_dequeue_wc_block_styles() {
	if ( hojreh_page_uses_wc_blocks() ) {
		return;
	}

	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'wc-blocks-style-all-products' );
}
add_action( 'wp_enqueue_scripts', 'hojreh_dequeue_wc_block_styles', 100 );

/**
 * Belt and braces: remove cart fragments after WooCommerce registers it.
 */
function hojreh_dequeue_cart_fragments_late() {
	wp_dequeue_script( 'wc-cart-fragments' );
}
add_action( 'wp_enqueue_scripts', 'hojreh_dequeue_cart_fragments_late', 99 );

/**
 * Add a tiny resource hint for the only render-blocking asset.
 */
function hojreh_preload_css() {
	$file = '/assets/dist/app.css';

	if ( ! file_exists( HOJREH_DIR . $file ) ) {
		return;
	}

	printf(
		'<link rel="preload" href="%s" as="style">' . "\n",
		esc_url( HOJREH_URI . $file )
	);
}
add_action( 'wp_head', 'hojreh_preload_css', 2 );
