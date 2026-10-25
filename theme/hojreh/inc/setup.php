<?php
/**
 * Theme setup, assets and front-end chrome helpers.
 *
 * @package Hojreh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme supports and menus.
 */
function hojreh_setup() {
	load_theme_textdomain( 'hojreh', HOJREH_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 200,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 1200,
			'product_grid'          => array(
				'default_columns' => (int) hojreh_get_option( 'shop_columns', 4 ),
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
	// A CSS-grid gallery is lighter and more predictable than the bundled slider.
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );

	register_nav_menus(
		array(
			'primary' => 'منوی اصلی',
			'footer'  => 'منوی فوتر',
		)
	);

	add_image_size( 'hojreh-card', 640, 800, true );
	add_image_size( 'hojreh-category', 600, 450, true );
	add_image_size( 'hojreh-hero', 1200, 1440, true );
}
add_action( 'after_setup_theme', 'hojreh_setup' );

/**
 * Load the compiled front-end assets.
 */
function hojreh_assets() {
	$css_rel = '/assets/dist/app.css';
	$js_rel  = '/assets/dist/app.js';

	if ( file_exists( HOJREH_DIR . $css_rel ) ) {
		wp_enqueue_style( 'hojreh', HOJREH_URI . $css_rel, array(), (string) filemtime( HOJREH_DIR . $css_rel ) );
	}

	if ( file_exists( HOJREH_DIR . $js_rel ) ) {
		wp_enqueue_script(
			'hojreh',
			HOJREH_URI . $js_rel,
			array(),
			(string) filemtime( HOJREH_DIR . $js_rel ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	$settings = hojreh_get_settings();
	$accent   = sanitize_hex_color( $settings['accent'] );

	if ( $accent ) {
		wp_add_inline_style(
			'hojreh',
			sprintf(
				':root{--accent:%1$s;--accent-ink:%2$s;--shop-cols:%3$d;}',
				esc_attr( $accent ),
				esc_attr( hojreh_contrast_color( $accent ) ),
				(int) $settings['shop_columns']
			)
		);
	}

	$endpoints = array();

	if ( function_exists( 'WC' ) ) {
		$endpoints = array(
			'add'    => add_query_arg( 'wc-ajax', 'hojreh_add_to_cart', home_url( '/' ) ),
			'cart'   => add_query_arg( 'wc-ajax', 'hojreh_get_cart', home_url( '/' ) ),
			'update' => add_query_arg( 'wc-ajax', 'hojreh_update_cart', home_url( '/' ) ),
			'remove' => add_query_arg( 'wc-ajax', 'hojreh_remove_item', home_url( '/' ) ),
		);
	}

	$data = array(
		'ajax'       => $endpoints,
		'nonce'      => wp_create_nonce( 'hojreh_cart' ),
		'cartUrl'    => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
		'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ),
		'shopUrl'    => function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' ),
		'isCart'     => function_exists( 'is_cart' ) && is_cart(),
		'isCheckout' => function_exists( 'is_checkout' ) && is_checkout(),
		'stickyAtc'  => (bool) $settings['sticky_atc'],
		'i18n'       => array(
			'adding'     => 'در حال افزودن…',
			'added'      => 'به سبد خرید اضافه شد',
			'error'      => 'مشکلی پیش آمد. دوباره تلاش کنید.',
			'empty'      => 'سبد خرید شما خالی است',
			'qtyError'   => 'به‌روزرسانی تعداد ممکن نشد.',
			'confirm'    => 'این محصول از سبد خرید حذف شود؟',
			'addToCart'  => 'افزودن به سبد خرید',
			'choose'     => 'انتخاب گزینه‌ها',
		),
	);

	wp_add_inline_script( 'hojreh', 'window.HOJREH=' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'hojreh_assets' );

/**
 * Early theme bootstrap: preload the font and set the colour mode before paint.
 */
function hojreh_head() {
	$font = HOJREH_URI . '/assets/fonts/Vazirmatn-Variable.woff2';
	?>
	<link rel="preload" href="<?php echo esc_url( $font ); ?>" as="font" type="font/woff2" crossorigin>
	<?php
	$scheme = hojreh_get_option( 'color_scheme', 'system' );
	$scheme = in_array( $scheme, array( 'system', 'light', 'dark' ), true ) ? $scheme : 'system';
	?>
	<script>
	(function(){
		try{
			var fallback = <?php echo wp_json_encode( $scheme ); ?>;
			var stored = localStorage.getItem('hojreh-theme');
			var dark = stored ? stored === 'dark' : (fallback === 'dark' || (fallback === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches));
			var el = document.documentElement;
			el.classList.toggle('dark', dark);
			el.style.colorScheme = dark ? 'dark' : 'light';
		}catch(e){}
	})();
	</script>
	<meta name="theme-color" content="<?php echo esc_attr( hojreh_get_option( 'accent', '#0f766e' ) ); ?>">
	<?php
}
add_action( 'wp_head', 'hojreh_head', 1 );

/**
 * Force the RTL direction: this theme is built for Persian storefronts.
 *
 * @param string $output Language attributes.
 * @return string
 */
function hojreh_language_attributes( $output ) {
	$output = preg_replace( '/\s*dir=("|\')[^"\']*\1/', '', (string) $output );

	return trim( $output . ' dir="rtl"' );
}
add_filter( 'language_attributes', 'hojreh_language_attributes' );

/**
 * Extra body classes.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function hojreh_body_classes( $classes ) {
	$classes[] = 'hojreh';

	if ( ! hojreh_get_option( 'dark_mode', 1 ) ) {
		$classes[] = 'hojreh-no-theme-toggle';
	}

	if ( is_product() ) {
		$classes[] = 'hojreh-single-product';
	}

	$classes[] = 'hojreh-has-bottom-nav';

	return $classes;
}
add_filter( 'body_class', 'hojreh_body_classes' );

/**
 * Skip link.
 */
function hojreh_skip_link() {
	echo '<a class="hojreh-skip" href="#main">رفتن به محتوای اصلی</a>';
}
add_action( 'wp_body_open', 'hojreh_skip_link', 1 );

/**
 * Primary menu fallback so the site looks complete before menus are assigned.
 */
function hojreh_primary_menu_fallback() {
	$links = array(
		home_url( '/' )                => 'خانه',
		hojreh_shop_url()              => 'فروشگاه',
	);

	if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'myaccount' ) > 0 ) {
		$links[ get_permalink( wc_get_page_id( 'myaccount' ) ) ] = 'حساب کاربری';
	}

	echo '<ul class="hojreh-menu">';
	foreach ( $links as $url => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Shop URL helper.
 *
 * @return string
 */
function hojreh_shop_url() {
	if ( function_exists( 'wc_get_page_id' ) ) {
		$shop_id = wc_get_page_id( 'shop' );

		if ( $shop_id > 0 ) {
			return get_permalink( $shop_id );
		}
	}

	return home_url( '/' );
}
