<?php
/**
 * Lightweight AJAX cart endpoints and drawer payloads.
 *
 * @package Hojreh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Verify the cart nonce.
 */
function hojreh_ajax_verify() {
	if ( ! check_ajax_referer( 'hojreh_cart', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'نشست شما منقضی شده است. صفحه را دوباره بارگذاری کنید.' ), 403 );
	}
}

/**
 * Build the JSON payload used by the drawer.
 *
 * @param array<string, mixed> $extra Extra fields.
 * @return array<string, mixed>
 */
function hojreh_cart_payload( $extra = array() ) {
	$cart = function_exists( 'WC' ) ? WC()->cart : null;

	if ( ! $cart ) {
		return array_merge(
			array(
				'html'     => '',
				'count'    => 0,
				'subtotal' => '',
				'empty'    => true,
			),
			$extra
		);
	}

	$cart->calculate_totals();

	return array_merge(
		array(
			'html'     => hojreh_mini_cart_html(),
			'count'    => (int) $cart->get_cart_contents_count(),
			'subtotal' => $cart->get_cart_subtotal(),
			'empty'    => $cart->is_empty(),
			'cartUrl'  => wc_get_cart_url(),
		),
		$extra
	);
}

/**
 * Extract a readable error notice from WooCommerce.
 *
 * @return string
 */
function hojreh_take_error_notice() {
	$notices = wc_get_notices( 'error' );
	wc_clear_notices();

	if ( ! $notices ) {
		return 'افزودن این محصول به سبد خرید ممکن نیست.';
	}

	$first = reset( $notices );

	if ( is_array( $first ) && ! empty( $first['notice'] ) ) {
		return wp_strip_all_tags( $first['notice'] );
	}

	return wp_strip_all_tags( (string) $first );
}

/**
 * AJAX: add an item to the cart.
 */
function hojreh_ajax_add_to_cart() {
	hojreh_ajax_verify();

	$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;
	$variations   = array();

	if ( empty( $product_id ) || $quantity < 1 ) {
		wp_send_json_error( array( 'message' => 'محصول یا تعداد نامعتبر است.' ), 400 );
	}

	if ( ! empty( $_POST['variations'] ) && is_array( $_POST['variations'] ) ) {
		foreach ( wp_unslash( $_POST['variations'] ) as $key => $value ) {
			$key = sanitize_key( $key );

			if ( 0 !== strpos( $key, 'attribute_' ) ) {
				continue;
			}

			$variations[ $key ] = sanitize_text_field( $value );
		}
	}

	try {
		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );
	} catch ( Throwable $exception ) {
		wp_send_json_error( array( 'message' => $exception->getMessage() ), 400 );
	}

	if ( ! $cart_item_key ) {
		wp_send_json_error( array( 'message' => hojreh_take_error_notice() ), 400 );
	}

	wc_clear_notices();

	wp_send_json_success( hojreh_cart_payload( array( 'message' => 'به سبد خرید اضافه شد.' ) ) );
}
add_action( 'wc_ajax_hojreh_add_to_cart', 'hojreh_ajax_add_to_cart' );

/**
 * AJAX: refresh the drawer.
 */
function hojreh_ajax_get_cart() {
	hojreh_ajax_verify();

	wp_send_json_success( hojreh_cart_payload() );
}
add_action( 'wc_ajax_hojreh_get_cart', 'hojreh_ajax_get_cart' );

/**
 * AJAX: change the quantity of a cart item.
 */
function hojreh_ajax_update_cart() {
	hojreh_ajax_verify();

	$key      = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$quantity = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 0;

	if ( ! $key ) {
		wp_send_json_error( array( 'message' => 'آیتم سبد خرید پیدا نشد.' ), 400 );
	}

	try {
		WC()->cart->set_quantity( $key, max( 0, $quantity ), true );
	} catch ( Throwable $exception ) {
		wp_send_json_error( array( 'message' => $exception->getMessage() ), 400 );
	}

	wp_send_json_success( hojreh_cart_payload() );
}
add_action( 'wc_ajax_hojreh_update_cart', 'hojreh_ajax_update_cart' );

/**
 * AJAX: remove a cart item.
 */
function hojreh_ajax_remove_item() {
	hojreh_ajax_verify();

	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';

	if ( ! $key ) {
		wp_send_json_error( array( 'message' => 'آیتم سبد خرید پیدا نشد.' ), 400 );
	}

	try {
		WC()->cart->remove_cart_item( $key );
	} catch ( Throwable $exception ) {
		wp_send_json_error( array( 'message' => $exception->getMessage() ), 400 );
	}

	wp_send_json_success( hojreh_cart_payload() );
}
add_action( 'wc_ajax_hojreh_remove_item', 'hojreh_ajax_remove_item' );
