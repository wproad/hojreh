<?php
/**
 * WooCommerce integration: wrappers, archive/single tweaks and copy.
 *
 * @package Hojreh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replace the default WooCommerce markup around our own layout.
 */
function hojreh_wc_hooks() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_all_notices', 10 );
	add_action( 'woocommerce_before_main_content', 'hojreh_wc_wrapper_start', 10 );
	add_action( 'woocommerce_before_main_content', 'woocommerce_output_all_notices', 11 );
	add_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 12 );
	add_action( 'woocommerce_before_main_content', 'hojreh_shop_header', 13 );
	add_action( 'woocommerce_after_main_content', 'hojreh_wc_wrapper_end', 10 );

	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

	// Archive toolbar.
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	add_action( 'woocommerce_before_shop_loop', 'hojreh_shop_toolbar', 20 );

	// Use our own hero title, not the default one.
	add_filter( 'woocommerce_show_page_title', '__return_false' );
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

	// Single product extras.
	add_action( 'woocommerce_single_product_summary', 'hojreh_single_trust', 35 );
	add_action( 'woocommerce_checkout_order_review', 'hojreh_order_review_title', 5 );
	add_filter( 'woocommerce_output_related_products_args', 'hojreh_related_products_args' );
	add_filter( 'woocommerce_upsell_display_args', 'hojreh_related_products_args' );

	// Keep the design consistent.
	add_filter( 'loop_shop_columns', 'hojreh_loop_columns' );
	add_filter( 'woocommerce_breadcrumb_defaults', 'hojreh_breadcrumb_defaults' );
	add_filter( 'woocommerce_checkout_fields', 'hojreh_checkout_fields' );
	add_action( 'woocommerce_review_order_before_submit', 'hojreh_checkout_secure_note' );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

	add_filter( 'woocommerce_product_get_rating_html', 'hojreh_render_rating', 10, 3 );
	add_filter( 'woocommerce_product_tabs', 'hojreh_persian_tabs' );
}
add_action( 'after_setup_theme', 'hojreh_wc_hooks', 20 );

/**
 * Open the shop container.
 */
function hojreh_wc_wrapper_start() {
	echo '<div class="hojreh-wc-wrap"><div class="wrap hojreh-wc">';
}

/**
 * Close the shop container.
 */
function hojreh_wc_wrapper_end() {
	echo '</div></div>';
}

/**
 * Archive header.
 */
function hojreh_shop_header() {
	if ( ! is_shop() && ! is_product_taxonomy() ) {
		return;
	}

	$title = 'فروشگاه';
	$desc  = '';
	$crumb = 'همه محصولات';

	if ( is_product_taxonomy() ) {
		$term  = get_queried_object();
		$title = $term instanceof WP_Term ? $term->name : $title;
		$desc  = $term instanceof WP_Term ? term_description( $term ) : '';
		$crumb = 'دسته‌بندی';
	} else {
		$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;

		if ( $shop_id > 0 ) {
			$title = get_the_title( $shop_id ) ? get_the_title( $shop_id ) : $title;
			$desc  = get_the_excerpt( $shop_id );
		}
	}
	?>
	<header class="hojreh-archive-head">
		<div>
			<p class="hojreh-eyebrow"><?php echo esc_html( $crumb ); ?></p>
			<h1 class="hojreh-archive-title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $desc ) : ?>
				<div class="hojreh-archive-desc"><?php echo wp_kses_post( wpautop( $desc ) ); ?></div>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Result count and ordering row.
 */
function hojreh_shop_toolbar() {
	if ( ! woocommerce_product_loop() ) {
		return;
	}
	?>
	<div class="hojreh-toolbar">
		<div class="hojreh-toolbar__count"><?php woocommerce_result_count(); ?></div>
		<div class="hojreh-toolbar__ordering"><?php woocommerce_catalog_ordering(); ?></div>
	</div>
	<?php
}

/**
 * Shop column count.
 *
 * @return int
 */
function hojreh_loop_columns() {
	return (int) hojreh_get_option( 'shop_columns', 4 );
}

/**
 * Breadcrumb markup defaults.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function hojreh_breadcrumb_defaults( $args ) {
	$args['delimiter']   = '<span class="hojreh-crumb-sep">' . hojreh_icon( 'chevron-left', array( 'size' => 12 ) ) . '</span>';
	$args['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="مسیر صفحه">';
	$args['wrap_after']  = '</nav>';

	return $args;
}

/**
 * Trust row on the single product page.
 */
function hojreh_single_trust() {
	$items = array(
		array( 'truck', 'ارسال سریع', 'تحویل ۱ تا ۳ روز کاری' ),
		array( 'shield-check', 'ضمانت اصالت', 'کالای اصل و باکیفیت' ),
		array( 'refresh', '۷ روز بازگشت', 'خرید بدون ریسک' ),
		array( 'credit-card', 'پرداخت امن', 'درگاه بانکی معتبر' ),
	);

	echo '<ul class="hojreh-trust">';

	foreach ( $items as $item ) {
		printf(
			'<li>%1$s<span><strong>%2$s</strong><small>%3$s</small></span></li>',
			hojreh_icon( $item[0], array( 'size' => 20 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $item[1] ),
			esc_html( $item[2] )
		);
	}

	echo '</ul>';
}

/**
 * Related products grid args.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function hojreh_related_products_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;

	return $args;
}

/**
 * Simplify checkout fields for a typical Iranian store.
 *
 * @param array<string, array<string, array<string, mixed>>> $fields Fields.
 * @return array<string, array<string, array<string, mixed>>>
 */
function hojreh_checkout_fields( $fields ) {
	$remove = array(
		'billing'  => array( 'billing_company', 'billing_address_2' ),
		'shipping' => array( 'shipping_company', 'shipping_address_2' ),
	);

	foreach ( $remove as $group => $keys ) {
		foreach ( $keys as $key ) {
			unset( $fields[ $group ][ $key ] );
		}
	}

	return $fields;
}

/**
 * Summary title inside the order review card.
 */
function hojreh_order_review_title() {
	echo '<h3 class="hojreh-order-review-title">سفارش شما</h3>';
}

/**
 * Percentage sale badge on the single product page.
 *
 * @param string     $html    Default badge HTML.
 * @param WP_Post    $post    Product post.
 * @param WC_Product $product Product.
 * @return string
 */
function hojreh_sale_flash( $html, $post, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $html;
	}

	$percent = hojreh_sale_percentage( $product );

	if ( $percent > 0 ) {
		return '<span class="onsale">' . esc_html( hojreh_fa_digits( $percent ) ) . '٪ تخفیف</span>';
	}

	return $html;
}
add_filter( 'woocommerce_sale_flash', 'hojreh_sale_flash', 10, 3 );

/**
 * Secure payment note below the place-order button.
 */
function hojreh_checkout_secure_note() {
	echo '<p class="hojreh-checkout-note">' . hojreh_icon( 'lock', array( 'size' => 15 ) ) . ' اطلاعات شما رمزنگاری می‌شود و پرداخت از طریق درگاه امن انجام می‌گیرد.</p>';
}

/**
 * Remove WooCommerce's own CSS; the theme ships a smaller, complete design system.
 *
 * @param array<string, array<string, mixed>> $styles Styles.
 * @return array<string, array<string, mixed>>
 */
function hojreh_wc_styles( $styles ) {
	unset( $styles['woocommerce-general'], $styles['woocommerce-layout'], $styles['woocommerce-smallscreen'] );

	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'hojreh_wc_styles' );

/**
 * Numeric rating with Persian digits and a single star (no icon font needed).
 *
 * @param string $html   Default HTML.
 * @param float  $rating Rating.
 * @param int    $count  Review count.
 * @return string
 */
function hojreh_render_rating( $html, $rating, $count ) {
	$rating = (float) $rating;

	if ( $rating <= 0 ) {
		return '';
	}

	return sprintf(
		'<span class="hojreh-rating-chip">%1$s<span>%2$s از ۵</span>%3$s</span>',
		hojreh_icon( 'star', array( 'size' => 14 ) ),
		esc_html( hojreh_fa_digits( number_format_i18n( $rating, 1 ) ) ),
		$count > 0 ? '<span class="hojreh-rating-chip__count">(' . esc_html( hojreh_fa_digits( number_format_i18n( $count ) ) ) . ')</span>' : ''
	);
}

/**
 * Fix a couple of cramped Persian WooCommerce strings.
 *
 * @param string $translated Translated text.
 * @param string $text       Original text.
 * @param string $domain     Text domain.
 * @return string
 */
function hojreh_woocommerce_strings( $translated, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translated;
	}

	return str_replace( 'کدتخفیف', 'کد تخفیف', $translated );
}
add_filter( 'gettext', 'hojreh_woocommerce_strings', 10, 3 );

/**
 * Persian digits in stock strings.
 *
 * @param string $availability Stock text.
 * @return string
 */
function hojreh_stock_digits( $availability ) {
	return hojreh_fa_digits( $availability );
}
add_filter( 'woocommerce_get_availability_text', 'hojreh_stock_digits', 20 );

/**
 * Persian digits inside the product tabs.
 *
 * @param array<string, array<string, mixed>> $tabs Tabs.
 * @return array<string, array<string, mixed>>
 */
function hojreh_persian_tabs( $tabs ) {
	if ( isset( $tabs['reviews']['title'] ) ) {
		$tabs['reviews']['title'] = hojreh_fa_digits( $tabs['reviews']['title'] );
	}

	if ( isset( $tabs['description']['title'] ) ) {
		$tabs['description']['title'] = 'توضیحات';
	}

	if ( isset( $tabs['additional_information']['title'] ) ) {
		$tabs['additional_information']['title'] = 'مشخصات';
	}

	return $tabs;
}

/**
 * Register Iranian Toman as a first-class currency.
 *
 * @param array<string, string> $currencies Currencies.
 * @return array<string, string>
 */
function hojreh_register_toman( $currencies ) {
	$currencies['IRT'] = 'تومان ایران';

	return $currencies;
}
add_filter( 'woocommerce_currencies', 'hojreh_register_toman' );

/**
 * Toman symbol.
 *
 * @param string $symbol   Default symbol.
 * @param string $currency Currency code.
 * @return string
 */
function hojreh_toman_symbol( $symbol, $currency ) {
	return 'IRT' === $currency ? 'تومان' : $symbol;
}
add_filter( 'woocommerce_currency_symbol', 'hojreh_toman_symbol', 10, 2 );

/**
 * Show the friendly empty-cart copy in the cart page.
 *
 * @param string $message Message.
 * @return string
 */
function hojreh_empty_cart_message( $message ) {
	return 'سبد خرید شما خالی است.';
}
add_filter( 'wc_empty_cart_message', 'hojreh_empty_cart_message' );

/**
 * Add a subtle body hook for the current WooCommerce page.
 *
 * @param array<int, string> $classes Classes.
 * @return array<int, string>
 */
function hojreh_wc_body_classes( $classes ) {
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
		$classes[] = 'hojreh-archive';
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$classes[] = 'hojreh-product-page';
	}

	return $classes;
}
add_filter( 'body_class', 'hojreh_wc_body_classes' );
