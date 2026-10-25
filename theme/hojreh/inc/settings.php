<?php
/**
 * Theme settings: defaults, sanitising and the dashboard screen.
 *
 * @package Hojreh
 */

defined( 'ABSPATH' ) || exit;

/**
 * All theme settings and their defaults.
 *
 * @return array<string, mixed>
 */
function hojreh_default_settings() {
	return array(
		'accent'       => '#0f766e',
		'default_card' => 'minimal',
		'shop_columns' => 4,
		'dark_mode'    => 1,
		'color_scheme' => 'system',
		'sticky_atc'   => 1,
		'announcement' => 'ارسال رایگان برای سفارش‌های بالای ۵۰۰٬۰۰۰ تومان',
		'hero_title'   => 'خانه‌ی اشیای خوب',
		'hero_text'    => 'انتخاب‌های مینیمال، باکیفیت و ماندگار برای زندگی روزمره.',
		'hero_image'   => 0,
		'hero_cta'     => 'دیدن محصولات',
		'hero_url'     => '',
		'phone'        => '۰۲۱-۰۰۰۰۰۰۰۰',
		'email'        => 'hello@example.com',
		'address'      => 'تهران، خیابان ولیعصر',
		'instagram'    => '',
	);
}

/**
 * Get all settings merged over the defaults.
 *
 * @return array<string, mixed>
 */
function hojreh_get_settings() {
	$saved = get_option( 'hojreh_settings', array() );

	return wp_parse_args( is_array( $saved ) ? $saved : array(), hojreh_default_settings() );
}

/**
 * Get a single setting.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function hojreh_get_option( $key, $default = null ) {
	$settings = hojreh_get_settings();

	return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
}

/**
 * Pick a readable text colour for a background colour.
 *
 * @param string $hex Hex colour.
 * @return string
 */
function hojreh_contrast_color( $hex ) {
	$hex = ltrim( (string) $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) ) {
		return '#ffffff';
	}

	$r = hexdec( substr( $hex, 0, 2 ) ) / 255;
	$g = hexdec( substr( $hex, 2, 2 ) ) / 255;
	$b = hexdec( substr( $hex, 4, 2 ) ) / 255;

	$luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

	return $luminance > 0.62 ? '#1c1917' : '#ffffff';
}

/**
 * Whitelisted product card variants.
 *
 * @return array<string, string>
 */
function hojreh_card_variants() {
	return array(
		'minimal'    => 'مینیمال (تصویر بزرگ + قیمت)',
		'overlay'    => 'پوششی (متن روی تصویر)',
		'bordered'   => 'کادردار (وسط‌چین)',
		'horizontal' => 'افقی (لیست)',
	);
}

/**
 * Register the single option with the Settings API.
 */
function hojreh_register_settings() {
	register_setting(
		'hojreh_settings_group',
		'hojreh_settings',
		array(
			'type'              => 'array',
			'default'           => hojreh_default_settings(),
			'sanitize_callback' => 'hojreh_sanitize_settings',
		)
	);

	add_settings_section( 'hojreh_brand', 'برند و ظاهر', 'hojreh_section_brand_intro', 'hojreh-settings' );
	add_settings_section( 'hojreh_shop', 'فروشگاه', 'hojreh_section_shop_intro', 'hojreh-settings' );
	add_settings_section( 'hojreh_home', 'صفحه اصلی', 'hojreh_section_home_intro', 'hojreh-settings' );
	add_settings_section( 'hojreh_contact', 'اطلاعات تماس', 'hojreh_section_contact_intro', 'hojreh-settings' );

	add_settings_field( 'hojreh_accent', 'رنگ اصلی', 'hojreh_field_accent', 'hojreh-settings', 'hojreh_brand' );
	add_settings_field( 'hojreh_announcement', 'نوار اطلاع‌رسانی', 'hojreh_field_announcement', 'hojreh-settings', 'hojreh_brand' );
	add_settings_field( 'hojreh_color_scheme', 'حالت رنگی پیش‌فرض', 'hojreh_field_color_scheme', 'hojreh-settings', 'hojreh_brand' );
	add_settings_field( 'hojreh_dark_mode', 'دکمه تغییر تم', 'hojreh_field_dark_mode', 'hojreh-settings', 'hojreh_brand' );

	add_settings_field( 'hojreh_default_card', 'کارت محصول پیش‌فرض', 'hojreh_field_default_card', 'hojreh-settings', 'hojreh_shop' );
	add_settings_field( 'hojreh_shop_columns', 'تعداد ستون‌های دسکتاپ', 'hojreh_field_shop_columns', 'hojreh-settings', 'hojreh_shop' );
	add_settings_field( 'hojreh_sticky_atc', 'دکمه خرید چسبان موبایل', 'hojreh_field_sticky_atc', 'hojreh-settings', 'hojreh_shop' );

	add_settings_field( 'hojreh_hero_title', 'عنوان هیرو', 'hojreh_field_hero_title', 'hojreh-settings', 'hojreh_home' );
	add_settings_field( 'hojreh_hero_text', 'توضیح هیرو', 'hojreh_field_hero_text', 'hojreh-settings', 'hojreh_home' );
	add_settings_field( 'hojreh_hero_image', 'تصویر هیرو', 'hojreh_field_hero_image', 'hojreh-settings', 'hojreh_home' );
	add_settings_field( 'hojreh_hero_cta', 'متن دکمه هیرو', 'hojreh_field_hero_cta', 'hojreh-settings', 'hojreh_home' );
	add_settings_field( 'hojreh_hero_url', 'لینک دکمه هیرو', 'hojreh_field_hero_url', 'hojreh-settings', 'hojreh_home' );

	add_settings_field( 'hojreh_phone', 'تلفن', 'hojreh_field_phone', 'hojreh-settings', 'hojreh_contact' );
	add_settings_field( 'hojreh_email', 'ایمیل', 'hojreh_field_email', 'hojreh-settings', 'hojreh_contact' );
	add_settings_field( 'hojreh_address', 'نشانی', 'hojreh_field_address', 'hojreh-settings', 'hojreh_contact' );
	add_settings_field( 'hojreh_instagram', 'اینستاگرام', 'hojreh_field_instagram', 'hojreh-settings', 'hojreh_contact' );
}
add_action( 'admin_init', 'hojreh_register_settings' );

/**
 * Sanitise the settings array.
 *
 * @param mixed $input Raw input.
 * @return array<string, mixed>
 */
function hojreh_sanitize_settings( $input ) {
	$defaults = hojreh_default_settings();
	$input    = is_array( $input ) ? $input : array();
	$out      = array();

	$accent              = sanitize_hex_color( $input['accent'] ?? '' );
	$out['accent']       = $accent ? $accent : $defaults['accent'];
	$out['default_card'] = array_key_exists( $input['default_card'] ?? '', hojreh_card_variants() ) ? $input['default_card'] : $defaults['default_card'];
	$out['shop_columns'] = min( 4, max( 2, absint( $input['shop_columns'] ?? $defaults['shop_columns'] ) ) );
	$out['dark_mode']    = empty( $input['dark_mode'] ) ? 0 : 1;
	$out['sticky_atc']   = empty( $input['sticky_atc'] ) ? 0 : 1;
	$out['color_scheme'] = in_array( $input['color_scheme'] ?? '', array( 'system', 'light', 'dark' ), true ) ? $input['color_scheme'] : $defaults['color_scheme'];

	$text_fields = array( 'announcement', 'hero_title', 'hero_text', 'hero_cta', 'phone', 'email', 'address' );

	foreach ( $text_fields as $field ) {
		$out[ $field ] = sanitize_text_field( wp_unslash( $input[ $field ] ?? '' ) );
	}

	$out['hero_image'] = absint( $input['hero_image'] ?? 0 );
	$out['hero_url']   = esc_url_raw( wp_unslash( $input['hero_url'] ?? '' ) );
	$out['instagram']  = esc_url_raw( wp_unslash( $input['instagram'] ?? '' ) );

	if ( $out['instagram'] && ! preg_match( '#^https?://#i', $out['instagram'] ) ) {
		$out['instagram'] = 'https://instagram.com/' . ltrim( $out['instagram'], '@/' );
	}

	return $out;
}

/**
 * Settings screen.
 */
function hojreh_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap hojreh-admin">
		<div class="hojreh-admin__head">
			<div>
				<h1>تنظیمات قالب حجره</h1>
				<p>پیکربندی سریع قالب. همه‌چیز با پیش‌فرض‌های سنسور کار می‌کند.</p>
			</div>
			<div class="hojreh-admin__badges">
				<span class="hojreh-pill">نسخه <?php echo esc_html( HOJREH_VERSION ); ?></span>
				<?php $hojreh_demo_page = get_page_by_path( 'card-demo' ); ?>
				<?php if ( $hojreh_demo_page ) : ?>
					<a class="button" href="<?php echo esc_url( get_permalink( $hojreh_demo_page ) ); ?>" target="_blank" rel="noopener">دموی کارت‌ها</a>
				<?php endif; ?>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">مشاهده سایت</a>
			</div>
		</div>

		<?php settings_errors( 'hojreh_settings' ); ?>

		<form action="options.php" method="post" class="hojreh-admin__form">
			<?php
			settings_fields( 'hojreh_settings_group' );
			do_settings_sections( 'hojreh-settings' );
			submit_button( 'ذخیره تغییرات', 'primary', 'submit', false );
			?>
		</form>

		<div class="hojreh-admin__help">
			<h2>راهنمای سریع</h2>
			<ul>
				<li>برای ساخت صفحه با کارت‌های محصول از شورت‌کد <code>[hojreh_products]</code> استفاده کنید.</li>
				<li>نمونه: <code>[hojreh_products card="overlay" columns="4" limit="8" title="منتخب حجره"]</code></li>
				<li>صفحات ووکامرس (فروشگاه، سبد خرید، تسویه حساب، حساب کاربری) را با شورت‌کدهای خود ووکامرس نگه دارید.</li>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * Register the admin menu entry.
 */
function hojreh_admin_menu() {
	add_menu_page(
		'تنظیمات حجره',
		'حجره',
		'manage_options',
		'hojreh-settings',
		'hojreh_render_settings_page',
		'dashicons-store',
		59
	);
}
add_action( 'admin_menu', 'hojreh_admin_menu' );

/**
 * Enqueue admin assets only on the theme screen.
 *
 * @param string $hook Current admin page hook.
 */
function hojreh_admin_assets( $hook ) {
	if ( 'toplevel_page_hojreh-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_media();
	wp_enqueue_style( 'hojreh-admin', HOJREH_URI . '/assets/admin.css', array(), HOJREH_VERSION );
	wp_enqueue_script( 'hojreh-admin', HOJREH_URI . '/assets/admin.js', array( 'jquery', 'wp-color-picker' ), HOJREH_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'hojreh_admin_assets' );

/**
 * Add a settings link on the themes screen.
 *
 * @param array<int, string> $links Action links.
 * @return array<int, string>
 */
function hojreh_theme_action_links( $links ) {
	$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=hojreh-settings' ) ) . '">تنظیمات قالب</a>';

	return $links;
}
add_filter( 'theme_action_links_' . get_option( 'template' ), 'hojreh_theme_action_links' );

/**
 * Notice when WooCommerce is not active.
 */
function hojreh_wc_missing_notice() {
	if ( class_exists( 'WooCommerce' ) || ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>قالب حجره برای فروشگاه‌ها ساخته شده است. برای فعال شدن قالب‌های فروشگاه، افزونه ووکامرس را فعال کنید.</p></div>';
}
add_action( 'admin_notices', 'hojreh_wc_missing_notice' );

function hojreh_section_brand_intro() {
	echo '<p>رنگ اصلی، نوار بالای سایت و حالت رنگی.</p>';
}

function hojreh_section_shop_intro() {
	echo '<p>ظاهر پیش‌فرض محصولات و تجربه خرید.</p>';
}

function hojreh_section_home_intro() {
	echo '<p>محتوای بخش هیرو در صفحه اصلی. اگر خالی بماند مقادیر پیش‌فرض نمایش داده می‌شود.</p>';
}

function hojreh_section_contact_intro() {
	echo '<p>این اطلاعات در فوتر سایت نمایش داده می‌شود.</p>';
}

/**
 * Render a settings text input.
 *
 * @param string $key   Setting key.
 * @param string $type  Input type.
 * @param string $class Extra class.
 */
function hojreh_settings_input( $key, $type = 'text', $class = 'regular-text' ) {
	$settings = hojreh_get_settings();
	printf(
		'<input type="%1$s" class="%2$s" name="hojreh_settings[%3$s]" id="hojreh_%3$s" value="%4$s">',
		esc_attr( $type ),
		esc_attr( $class ),
		esc_attr( $key ),
		esc_attr( $settings[ $key ] ?? '' )
	);
}

function hojreh_field_accent() {
	$settings = hojreh_get_settings();
	printf(
		'<input type="text" class="hojreh-color" name="hojreh_settings[accent]" value="%1$s" data-default-color="%2$s"><p class="description">رنگ دکمه‌ها، لینک‌ها و نشانه‌های فعال.</p>',
		esc_attr( $settings['accent'] ),
		esc_attr( hojreh_default_settings()['accent'] )
	);
}

function hojreh_field_announcement() {
	hojreh_settings_input( 'announcement', 'text', 'large-text' );
	echo '<p class="description">برای پنهان کردن نوار، خالی بگذارید.</p>';
}

function hojreh_field_color_scheme() {
	$settings = hojreh_get_settings();
	?>
	<select name="hojreh_settings[color_scheme]" id="hojreh_color_scheme">
		<?php foreach ( array( 'system' => 'هماهنگ با سیستم', 'light' => 'روشن', 'dark' => 'تیره' ) as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['color_scheme'], $value ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
}

function hojreh_field_dark_mode() {
	$settings = hojreh_get_settings();
	?>
	<label>
		<input type="checkbox" name="hojreh_settings[dark_mode]" value="1" <?php checked( ! empty( $settings['dark_mode'] ) ); ?>>
		نمایش دکمه تغییر حالت روشن/تیره در هدر
	</label>
	<?php
}

function hojreh_field_default_card() {
	$settings = hojreh_get_settings();
	?>
	<select name="hojreh_settings[default_card]" id="hojreh_default_card">
		<?php foreach ( hojreh_card_variants() as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['default_card'], $value ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">در شورت‌کد می‌توانید با صفت <code>card</code> آن را تغییر دهید.</p>
	<?php
}

function hojreh_field_shop_columns() {
	$settings = hojreh_get_settings();
	?>
	<select name="hojreh_settings[shop_columns]" id="hojreh_shop_columns">
		<?php foreach ( array( 2, 3, 4 ) as $value ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (int) $settings['shop_columns'], $value ); ?>><?php echo esc_html( hojreh_fa_digits( (string) $value ) ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">در موبایل همیشه ۲ ستون و در تبلت ۳ ستون نمایش داده می‌شود.</p>
	<?php
}

function hojreh_field_sticky_atc() {
	$settings = hojreh_get_settings();
	?>
	<label>
		<input type="checkbox" name="hojreh_settings[sticky_atc]" value="1" <?php checked( ! empty( $settings['sticky_atc'] ) ); ?>>
		در صفحه محصول موبایل، نوار خرید چسبان نمایش داده شود
	</label>
	<?php
}

function hojreh_field_hero_title() {
	hojreh_settings_input( 'hero_title', 'text', 'large-text' );
}

function hojreh_field_hero_text() {
	hojreh_settings_input( 'hero_text', 'text', 'large-text' );
}

function hojreh_field_hero_cta() {
	hojreh_settings_input( 'hero_cta' );
}

function hojreh_field_hero_url() {
	hojreh_settings_input( 'hero_url', 'url', 'large-text' );
	echo '<p class="description">خالی بگذارید تا به فروشگاه لینک شود.</p>';
}

function hojreh_field_hero_image() {
	$settings = hojreh_get_settings();
	$image_id = absint( $settings['hero_image'] );
	$src      = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	?>
	<div class="hojreh-media">
		<input type="hidden" name="hojreh_settings[hero_image]" id="hojreh_hero_image" value="<?php echo esc_attr( $image_id ); ?>">
		<div class="hojreh-media__preview" data-preview>
			<?php if ( $src ) : ?>
				<img src="<?php echo esc_url( $src ); ?>" alt="">
			<?php endif; ?>
		</div>
		<button type="button" class="button" data-media-select>انتخاب تصویر</button>
		<button type="button" class="button-link" data-media-remove>حذف</button>
		<p class="description">تصویر افقی با کیفیت (حدود ۱۲۰۰ در ۹۰۰ پیکسل).</p>
	</div>
	<?php
}

function hojreh_field_phone() {
	hojreh_settings_input( 'phone' );
}

function hojreh_field_email() {
	hojreh_settings_input( 'email', 'email' );
}

function hojreh_field_address() {
	hojreh_settings_input( 'address', 'text', 'large-text' );
}

function hojreh_field_instagram() {
	hojreh_settings_input( 'instagram', 'url', 'large-text' );
}

/**
 * Show the settings page in the admin bar.
 *
 * @param WP_Admin_Bar $bar Admin bar instance.
 */
function hojreh_admin_bar_link( $bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$bar->add_node(
		array(
			'id'    => 'hojreh-settings',
			'title' => 'تنظیمات حجره',
			'href'  => admin_url( 'admin.php?page=hojreh-settings' ),
		)
	);
}
add_action( 'admin_bar_menu', 'hojreh_admin_bar_link', 90 );
