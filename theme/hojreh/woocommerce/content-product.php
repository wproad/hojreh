<?php
/**
 * Product loop item — renders one of the four card variants.
 *
 * @package Hojreh
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product, $woocommerce_loop;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$hojreh_eager = isset( $woocommerce_loop['loop'] ) && (int) $woocommerce_loop['loop'] <= 4;

echo hojreh_product_card( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$product,
	array(
		'variant' => hojreh_get_card_variant(),
		'eager'   => $hojreh_eager,
	)
);
