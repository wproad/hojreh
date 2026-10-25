<?php
/**
 * Demo content seeder for the Hojreh theme.
 *
 * Run with: docker compose --profile cli run --rm wpcli wp eval-file /scripts/seed.php
 *
 * @package Hojreh
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';

$theme_functions = ABSPATH . 'wp-content/themes/hojreh/functions.php';

if ( file_exists( $theme_functions ) ) {
	require_once $theme_functions;
}

if ( ! function_exists( 'WC' ) ) {
	WP_CLI::error( 'WooCommerce must be active.' );
}

/**
 * Import a seed image once and return its attachment ID.
 *
 * @param string $relative Relative path under /scripts/seed-assets.
 * @param string $title    Attachment title.
 * @return int
 */
function hojreh_seed_image( $relative, $title ) {
	$source = '/scripts/seed-assets/' . ltrim( $relative, '/' );

	if ( ! file_exists( $source ) ) {
		WP_CLI::warning( 'Missing image: ' . $source );

		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'title'          => $title,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_hojreh_seed',
					'value'   => $relative,
					'compare' => '=',
				),
			),
		)
	);

	if ( $existing ) {
		return (int) $existing[0];
	}

	$contents = file_get_contents( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$upload   = wp_upload_bits( basename( $source ), null, $contents );

	if ( ! empty( $upload['error'] ) ) {
		WP_CLI::warning( 'Upload failed for ' . $relative . ': ' . $upload['error'] );

		return 0;
	}

	$filetype   = wp_check_filetype( $upload['file'] );
	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => $title,
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attachment_id = wp_insert_attachment( $attachment, $upload['file'] );

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return 0;
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
	update_post_meta( $attachment_id, '_hojreh_seed', $relative );

	return (int) $attachment_id;
}

/**
 * Short Persian price helper for descriptions.
 *
 * @param int $amount Amount in Toman.
 * @return string
 */
function hojreh_price_text( $amount ) {
	return number_format( $amount, 0, '.', '٬' ) . ' تومان';
}

/* -------------------------------------------------------------------------
 * Store basics
 * ---------------------------------------------------------------------- */
update_option( 'blogname', 'حجره' );
update_option( 'blogdescription', 'اشیای خوب برای زندگی روزمره' );
update_option( 'admin_email', 'hello@hojreh.local' );
update_option( 'woocommerce_store_address', 'خیابان کریم‌خان، پلاک ۲۴' );
update_option( 'woocommerce_store_city', 'تهران' );
update_option( 'woocommerce_store_postcode', '1418834567' );
update_option( 'woocommerce_default_country', 'IR:THR' );
update_option( 'woocommerce_currency', 'IRT' );
update_option( 'woocommerce_currency_pos', 'right_space' );
update_option( 'woocommerce_price_thousand_sep', '٬' );
update_option( 'woocommerce_price_decimal_sep', '٫' );
update_option( 'woocommerce_price_num_decimals', 0 );
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'woocommerce_allow_tracking', 'no' );
update_option( 'woocommerce_enable_guest_checkout', 'yes' );
update_option( 'woocommerce_enable_checkout_login_reminder', 'yes' );
update_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' );
update_option( 'woocommerce_cart_redirect_after_add', 'no' );
update_option( 'woocommerce_enable_reviews', 'yes' );
update_option( 'woocommerce_enable_review_rating', 'yes' );
update_option( 'woocommerce_review_rating_required', 'no' );
update_option( 'woocommerce_review_rating_verification_required', 'no' );
update_option( 'woocommerce_calc_taxes', 'no' );
update_option( 'woocommerce_enable_shipping_calc', 'yes' );
update_option( 'woocommerce_shipping_cost_requires_address', 'no' );
update_option( 'woocommerce_weight_unit', 'kg' );
update_option( 'woocommerce_dimension_unit', 'cm' );
update_option( 'woocommerce_thumbnail_cropping', 'custom' );
update_option( 'woocommerce_thumbnail_cropping_custom_width', '4' );
update_option( 'woocommerce_thumbnail_cropping_custom_height', '5' );
update_option( 'woocommerce_cod_settings', array(
	'enabled'      => 'yes',
	'title'        => 'پرداخت در محل',
	'description'  => 'هزینه سفارش را هنگام تحویل، پرداخت کنید.',
	'instructions' => 'سفارش شما پس از هماهنگی تلفنی برای ارسال آماده می‌شود.',
) );

// Classic, fast, styleable store pages.
foreach ( array(
	'cart'      => '[woocommerce_cart]',
	'checkout'  => '[woocommerce_checkout]',
	'myaccount' => '[woocommerce_my_account]',
) as $page_key => $shortcode ) {
	$page_id = wc_get_page_id( $page_key );

	if ( $page_id > 0 ) {
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $shortcode,
			)
		);
	}
}

$shop_id = wc_get_page_id( 'shop' );

if ( $shop_id > 0 ) {
	wp_update_post(
		array(
			'ID'           => $shop_id,
			'post_title'   => 'فروشگاه',
			'post_excerpt' => 'مجموعه‌ای محدود و سنجیده از اشیای کاربردی؛ انتخاب‌هایی که سال‌ها با شما می‌مانند.',
		)
	);
}

/* -------------------------------------------------------------------------
 * Images
 * ---------------------------------------------------------------------- */
$image_ids = array();

$images = array(
	'hero'                 => 'images/hero.jpg',
	'product-mug'          => 'products/mug-sunrise.jpg',
	'product-notebook'     => 'products/notebook-linen.jpg',
	'product-speaker'      => 'products/speaker-nova.jpg',
	'product-lamp'         => 'products/lamp-halo.jpg',
	'product-tote'         => 'products/tote-hojreh.jpg',
	'product-candle'       => 'products/candle-sandalwood.jpg',
	'product-glass'        => 'products/glass-coffee.jpg',
	'product-planner'      => 'products/planner-daily.jpg',
	'product-headphones'   => 'products/headphones-quiet.jpg',
	'product-shirt'        => 'products/shirt-organic.jpg',
	'product-plate'        => 'products/plate-handmade.jpg',
	'product-vase'         => 'products/vase-terracotta.jpg',
	'product-blanket'      => 'products/blanket-wool.jpg',
	'product-pen'          => 'products/pen-metal.jpg',
	'category-home'        => 'categories/home-living.jpg',
	'category-kitchen'     => 'categories/kitchen.jpg',
	'category-stationery'  => 'categories/stationery.jpg',
	'category-audio'       => 'categories/audio.jpg',
	'category-wear'        => 'categories/wear.jpg',
	'blog-small-space'     => 'blog-small-space.jpg',
	'blog-materials'       => 'blog-materials.jpg',
	'blog-habits'          => 'blog-habits.jpg',
);

// Hero lives at the seed-assets root.
$images['hero']                 = 'hero.jpg';
$images['blog-small-space']     = 'blog-small-space.jpg';
$images['blog-materials']       = 'blog-materials.jpg';

$titles = array(
	'hero'                => 'چیدمان حجره',
	'product-mug'         => 'ماگ سرامیکی صبح',
	'product-notebook'    => 'دفتر یادداشت پارچه‌ای',
	'product-speaker'     => 'اسپیکر چوبی نوا',
	'product-lamp'        => 'چراغ رومیزی هاله',
	'product-tote'        => 'کیف خرید حجره',
	'product-candle'      => 'شمع سویا چوب صندل',
	'product-glass'       => 'فنجان قهوه شیشه‌ای',
	'product-planner'     => 'دفتر برنامه‌ریزی روزانه',
	'product-headphones'  => 'هدفون بی‌سیم سکوت',
	'product-shirt'       => 'تی‌شرت پنبه ارگانیک',
	'product-plate'       => 'بشقاب سرامیکی دست‌ساز',
	'product-vase'        => 'گلدان سفالی',
	'product-blanket'     => 'پتوی پشمی نرم',
	'product-pen'         => 'خودکار فلزی',
	'category-home'       => 'خانه و زندگی',
	'category-kitchen'    => 'آشپزخانه',
	'category-stationery' => 'نوشت‌افزار',
	'category-audio'      => 'صوتی',
	'category-wear'       => 'پوشیدنی',
	'blog-small-space'    => 'مینیمالیسم در فضای کوچک',
	'blog-materials'      => 'جنس خوب، عمر طولانی',
	'blog-habits'         => 'سه عادت ساده برای خرید بهتر',
);

foreach ( $images as $key => $path ) {
	$image_ids[ $key ] = hojreh_seed_image( $path, $titles[ $key ] ?? $key );
}

/* -------------------------------------------------------------------------
 * Categories
 * ---------------------------------------------------------------------- */
$categories = array(
	'home-living' => array( 'خانه و زندگی', 'Home & Living', 'category-home', 'اشیای آرام و کاربردی برای خانه.' ),
	'kitchen'     => array( 'آشپزخانه', 'Kitchen', 'category-kitchen', 'ظرف‌ها و ابزارهایی برای آشپزی روزمره.' ),
	'stationery'  => array( 'نوشت‌افزار', 'Stationery', 'category-stationery', 'دفتر، برنامه‌ریز و ابزار نوشتن.' ),
	'audio'       => array( 'صوتی', 'Audio', 'category-audio', 'صدای خوب، بدون شلوغی.' ),
	'wear'        => array( 'پوشیدنی', 'Wear', 'category-wear', 'پوشاک ساده و ماندگار.' ),
);

$category_ids = array();

foreach ( $categories as $slug => $data ) {
	$existing = term_exists( $slug, 'product_cat' );

	if ( $existing ) {
		$category_ids[ $slug ] = (int) $existing['term_id'];
		wp_update_term( $category_ids[ $slug ], 'product_cat', array( 'name' => $data[0], 'description' => $data[3] ) );
	} else {
		$created = wp_insert_term(
			$data[0],
			'product_cat',
			array(
				'slug'        => $slug,
				'description' => $data[3],
			)
		);

		if ( is_wp_error( $created ) ) {
			WP_CLI::warning( 'Category failed: ' . $slug . ' — ' . $created->get_error_message() );
			continue;
		}

		$category_ids[ $slug ] = (int) $created['term_id'];
	}

	update_term_meta( $category_ids[ $slug ], 'thumbnail_id', $image_ids[ $data[2] ] ?? 0 );
	update_term_meta( $category_ids[ $slug ], 'display_type', 'default' );
}

/* -------------------------------------------------------------------------
 * Products
 * ---------------------------------------------------------------------- */
$catalog = array(
	array(
		'name'    => 'ماگ سرامیکی «صبح»',
		'slug'    => 'mug-sunrise',
		'cat'     => 'kitchen',
		'price'   => 420000,
		'sale'    => 360000,
		'featured'=> true,
		'image'   => 'product-mug',
		'sku'     => 'HOJ-MUG-01',
		'short'   => 'ماگ دست‌ساز سرامیکی با لعاب مات و لبه‌ی نرم؛ برای قهوه‌ی صبح و چای عصر.',
		'desc'    => '<p>ماگ «صبح» با بدنه‌ای ضخیم و لعاب مات ساخته شده تا گرمای نوشیدنی را بیشتر نگه دارد. فرم ساده و بی‌اضافه‌ی آن، روی میز کار یا کنار تخت هم خوب می‌نشیند.</p><ul><li>سرامیک درجه‌یک، پخت در دمای بالا</li><li>حجم ۳۲۰ میلی‌لیتر</li><li>مناسب ماشین ظرف‌شویی و مایکروویو</li></ul>',
		'reviews' => array(
			array( 'سارا محمدی', 5, 'دقیقاً همان چیزی بود که در عکس دیدم. لبه‌اش خیلی نرم است و دست را نمی‌سوزاند.' ),
			array( 'امیر رضایی', 4, 'کیفیت ساخت خوب است. کمی سنگین‌تر از ماگ‌های معمولی است که برای من مزیت است.' ),
			array( 'نگار کریمی', 5, 'بسته‌بندی خیلی مرتب بود و سالم رسید.' ),
		),
	),
	array(
		'name'    => 'دفتر یادداشت پارچه‌ای A5',
		'slug'    => 'notebook-linen',
		'cat'     => 'stationery',
		'price'   => 290000,
		'sale'    => 0,
		'featured'=> true,
		'image'   => 'product-notebook',
		'sku'     => 'HOJ-NB-02',
		'short'   => 'دفتر A5 با جلد پارچه‌ای، کاغذ کرم و دوخت بازشو؛ همراه روزمره‌ی ایده‌ها.',
		'desc'    => '<p>جلد پارچه‌ای و بافت مات این دفتر، حس یک شیء قدیمی و دوست‌داشتنی را دارد. کاغذ کرم‌رنگ آن برای نوشتن با خودکار و مداد نرم انتخاب شده است.</p><ul><li>۱۶۰ صفحه‌ی خط‌دار</li><li>کاغذ ۹۰ گرمی</li><li>همراه با بند نشانه</li></ul>',
		'reviews' => array(
			array( 'مینا تهرانی', 5, 'کاغذش خیلی خوش‌دست است و جلد پارچه‌ای کیفیت بالایی دارد.' ),
			array( 'پویا احمدی', 4, 'برای هدیه گرفتم، ظاهر شیکی دارد.' ),
		),
	),
	array(
		'name'    => 'اسپیکر بلوتوثی چوبی «نوا»',
		'slug'    => 'speaker-nova',
		'cat'     => 'audio',
		'price'   => 2850000,
		'sale'    => 2490000,
		'featured'=> true,
		'image'   => 'product-speaker',
		'sku'     => 'HOJ-AU-03',
		'short'   => 'بدنه‌ی چوب گردو، صدای گرم و باتری ۱۲ ساعته؛ بدون سیم، بدون شلوغی.',
		'desc'    => '<p>«نوا» یک اسپیکر جمع‌وجمع با بدنه‌ی چوب گردو است که صدایی گرم و متعادل ارائه می‌دهد. دکمه‌های بالای دستگاه ساده‌اند و اتصال بلوتوث در چند ثانیه انجام می‌شود.</p><ul><li>بلوتوث ۵.۳ با برد ۱۰ متر</li><li>باتری تا ۱۲ ساعت پخش</li><li>وزن ۶۸۰ گرم</li></ul>',
		'reviews' => array(
			array( 'رضا شریفی', 5, 'برای اتاق کوچک عالی است. صدای بیس متعادل و آزاردهنده نیست.' ),
			array( 'الهام نوری', 4, 'ظاهرش خیلی زیباست؛ فقط دوست داشتم کیف حمل هم داشته باشد.' ),
		),
	),
	array(
		'name'    => 'چراغ رومیزی قابل شارژ «هاله»',
		'slug'    => 'lamp-halo',
		'cat'     => 'home-living',
		'price'   => 1650000,
		'sale'    => 0,
		'featured'=> true,
		'image'   => 'product-lamp',
		'sku'     => 'HOJ-HL-04',
		'short'   => 'نور گرم و قابل تنظیم، باتری قابل شارژ و بدنه‌ی فلزی مات؛ برای میز کار و کنار تخت.',
		'desc'    => '<p>چراغ «هاله» با سه سطح نور گرم، فضای کوچکی را روشن می‌کند بدون اینکه چشم را اذیت کند. بدون سیم است و با کابل USB-C شارژ می‌شود.</p><ul><li>سه سطح روشنایی</li><li>تا ۲۰ ساعت نور در کمترین حالت</li><li>بدنه‌ی آلومینیوم رنگ‌شده</li></ul>',
		'reviews' => array(
			array( 'مریم صادقی', 5, 'نورش دقیقاً گرم و آرام است. برای مطالعه‌ی شب عالی است.' ),
			array( 'کیانوش فراهانی', 4, 'کیفیت خوب، باتری هم طولانی مدت جواب می‌دهد.' ),
		),
	),
	array(
		'name'    => 'کیف خرید پارچه‌ای «حجره»',
		'slug'    => 'tote-hojreh',
		'cat'     => 'wear',
		'price'   => 350000,
		'sale'    => 290000,
		'featured'=> true,
		'image'   => 'product-tote',
		'sku'     => 'HOJ-WR-05',
		'short'   => 'کتان ضخیم، دوخت تقویت‌شده و جیب داخلی؛ کیفی که هر روز همراه‌تان می‌ماند.',
		'desc'    => '<p>کیف پارچه‌ای حجره از کتان ضخیم ساخته شده و دسته‌های آن با دوخت تقویت‌شده به بدنه متصل شده‌اند. جیب داخلی کوچک برای گوشی و کلید دارد.</p><ul><li>ابعاد ۳۸ × ۴۲ سانتی‌متر</li><li>جیب داخلی</li><li>قابل شست‌وشو با دست</li></ul>',
		'reviews' => array(
			array( 'آیدا موسوی', 5, 'خیلی محکم است و جای زیادی دارد. رنگش هم دقیقاً مثل عکس است.' ),
		),
	),
	array(
		'name'    => 'شمع سویا با رایحه چوب صندل',
		'slug'    => 'candle-sandalwood',
		'cat'     => 'home-living',
		'price'   => 380000,
		'sale'    => 0,
		'featured'=> false,
		'image'   => 'product-candle',
		'sku'     => 'HOJ-HL-06',
		'short'   => 'شمع سویا با فتیله‌ی پنبه‌ای و رایحه‌ی گرم چوب صندل؛ حدود ۳۵ ساعت سوخت.',
		'desc'    => '<p>شمع سویا در ظرف شیشه‌ای مات با فتیله‌ی پنبه‌ای ساخته شده است. رایحه‌ی چوب صندل و وتیور، فضایی آرام و گرم ایجاد می‌کند.</p><ul><li>موم سویا، بدون پارافین</li><li>زمان سوخت حدود ۳۵ ساعت</li><li>ظرف قابل استفاده‌ی مجدد</li></ul>',
		'reviews' => array(),
	),
	array(
		'name'    => 'فنجان قهوه دوجداره شیشه‌ای',
		'slug'    => 'glass-coffee',
		'cat'     => 'kitchen',
		'price'   => 520000,
		'sale'    => 0,
		'featured'=> false,
		'image'   => 'product-glass',
		'sku'     => 'HOJ-KT-07',
		'short'   => 'شیشه‌ی بروسیلیکات دوجداره؛ نوشیدنی گرم بدون سوختن دست و بدون تعریق روی میز.',
		'desc'    => '<p>فنجان دوجداره‌ی شیشه‌ای، لایه‌ی هوا را بین دو جداره نگه می‌دارد تا دمای نوشیدنی بیشتر بماند و سطح بیرونی خنک بماند. مناسب اسپرسو و قهوه‌ی دمی.</p><ul><li>شیشه‌ی بروسیلیکات مقاوم</li><li>حجم ۲۵۰ میلی‌لیتر</li><li>مناسب ماشین ظرف‌شویی</li></ul>',
		'reviews' => array(),
	),
	array(
		'name'    => 'دفتر برنامه‌ریزی روزانه',
		'slug'    => 'planner-daily',
		'cat'     => 'stationery',
		'price'   => 450000,
		'sale'    => 0,
		'featured'=> true,
		'image'   => 'product-planner',
		'sku'     => 'HOJ-NB-08',
		'short'   => 'برنامه‌ریز بدون تاریخ با بخش‌های روزانه، اهداف ماه و یادداشت آزاد.',
		'desc'    => '<p>این برنامه‌ریز تاریخ ندارد؛ هر زمان که شروع کنید، از ابتدا با شما همراه می‌شود. هر صفحه شامل سه اولویت روز، برنامه‌ی ساعتی و یک بخش کوتاه برای بازبینی است.</p><ul><li>۱۹۲ صفحه</li><li>کاغذ ۱۰۰ گرمی کرم</li><li>جلد سخت با بند</li></ul>',
		'reviews' => array(
			array( 'بهار کیانی', 5, 'ساختارش ساده و کاربردی است، بدون شلوغی.' ),
		),
	),
	array(
		'name'    => 'هدفون بی‌سیم «سکوت»',
		'slug'    => 'headphones-quiet',
		'cat'     => 'audio',
		'price'   => 3450000,
		'sale'    => 2990000,
		'featured'=> true,
		'image'   => 'product-headphones',
		'sku'     => 'HOJ-AU-09',
		'short'   => 'حذف نویز فعال، ۳۰ ساعت باتری و بالشتک‌های نرم؛ برای تمرکز در هر جا.',
		'desc'    => '<p>هدفون «سکوت» با حذف نویز فعال، صدای محیط را کم می‌کند تا روی کار یا موسیقی تمرکز کنید. بالشتک‌های پارچه‌ای نرم هستند و فشار زیادی به گوش نمی‌آورند.</p><ul><li>حذف نویز فعال (ANC)</li><li>تا ۳۰ ساعت پخش با ANC خاموش</li><li>شارژ سریع USB-C</li></ul>',
		'reviews' => array(
			array( 'محمد حسینی', 5, 'حذف نویزش در مترو واقعاً کار می‌کند.' ),
			array( 'نازنین اکبری', 4, 'صدای خوب و راحتی بالا؛ کیف حمل ساده است.' ),
		),
	),
	array(
		'name'    => 'تی‌شرت پنبه‌ی ارگانیک',
		'slug'    => 'shirt-organic',
		'cat'     => 'wear',
		'price'   => 690000,
		'sale'    => 0,
		'featured'=> true,
		'image'   => 'product-shirt',
		'sku'     => 'HOJ-WR-10',
		'short'   => 'پنبه‌ی ارگانیک، برش راحت و رنگ‌های خنثی؛ یک تی‌شرت برای هر روز.',
		'desc'    => '<p>تی‌شرت حجره از پنبه‌ی ارگانیک با بافت متوسط ساخته شده است. یقه و سرشانه‌ها تقویت شده‌اند تا پس از شست‌وشوهای زیاد فرم خود را حفظ کند.</p><ul><li>۱۰۰٪ پنبه‌ی ارگانیک</li><li>برش راحت (Relaxed Fit)</li><li>رنگ‌بندی خنثی</li></ul>',
		'reviews' => array(
			array( 'سینا مرادی', 5, 'پارچه‌اش نرم است و بعد از شستن آب نرفت.' ),
		),
	),
	array(
		'name'    => 'بشقاب سرامیکی دست‌ساز',
		'slug'    => 'plate-handmade',
		'cat'     => 'kitchen',
		'price'   => 580000,
		'sale'    => 0,
		'featured'=> false,
		'image'   => 'product-plate',
		'sku'     => 'HOJ-KT-11',
		'short'   => 'بشقاب سرامیکی با لعاب مات و لبه‌ی نامنظم؛ هر قطعه کمی متفاوت است.',
		'desc'    => '<p>این بشقاب روی چرخ سفالگری شکل گرفته و لعاب مات آن با دست اجرا شده است. تفاوت‌های جزئی در فرم و لعاب، بخشی از شخصیت هر قطعه است.</p><ul><li>قطر ۲۴ سانتی‌متر</li><li>مناسب ماشین ظرف‌شویی</li><li>ساخت دستی در ایران</li></ul>',
		'reviews' => array(),
	),
	array(
		'name'    => 'گلدان سفالی کوچک',
		'slug'    => 'vase-terracotta',
		'cat'     => 'home-living',
		'price'   => 320000,
		'sale'    => 0,
		'featured'=> true,
		'image'   => 'product-vase',
		'sku'     => 'HOJ-HL-12',
		'short'   => 'گلدان سفالی با بافت طبیعی؛ برای یک شاخه یا چند ساقه‌ی خشک.',
		'desc'    => '<p>گلدان سفالی کوچک با لعاب شفاف پوشیده شده تا بافت طبیعی خاک رس دیده شود. برای شاخه‌های کوتاه، گیاهان خشک یا به‌تنهایی به‌عنوان یک شیء تزئینی مناسب است.</p><ul><li>ارتفاع ۱۸ سانتی‌متر</li><li>سفال بدون لعاب رنگی</li><li>ساخت دستی</li></ul>',
		'reviews' => array(
			array( 'لیلا رستمی', 5, 'بافتش خیلی طبیعی و زیباست. دقیقاً همان حس سفال دست‌ساز را دارد.' ),
		),
	),
	array(
		'name'    => 'پتوی پشمی نرم',
		'slug'    => 'blanket-wool',
		'cat'     => 'home-living',
		'price'   => 1250000,
		'sale'    => 0,
		'featured'=> false,
		'image'   => 'product-blanket',
		'sku'     => 'HOJ-HL-13',
		'short'   => 'پشم مرینوس، بافت سبک و گرم؛ برای نشستن روی مبل یا انتهای تخت.',
		'desc'    => '<p>پتوی پشمی با نخ مرینوس بافته شده است؛ سبک است اما گرمای خوبی دارد. لبه‌ها با دوخت ساده تمام شده‌اند تا سال‌ها دوام بیاورند.</p><ul><li>ابعاد ۱۳۰ × ۱۷۰ سانتی‌متر</li><li>۷۰٪ پشم مرینوس</li><li>شست‌وشوی خشک توصیه می‌شود</li></ul>',
		'reviews' => array(),
	),
	array(
		'name'    => 'خودکار فلزی مشکی',
		'slug'    => 'pen-metal',
		'cat'     => 'stationery',
		'price'   => 150000,
		'sale'    => 0,
		'featured'=> false,
		'image'   => 'product-pen',
		'sku'     => 'HOJ-NB-14',
		'short'   => 'بدنه‌ی آلومینیومی مات، وزن متعادل و نوک نازک؛ برای نوشتن طولانی.',
		'desc'    => '<p>خودکار فلزی حجره با بدنه‌ی آلومینیومی و نوک ۰.۵ میلی‌متری، نوشتنی نرم و یکنواخت دارد. وزن آن متعادل است و دست را خسته نمی‌کند.</p><ul><li>نوک ۰.۵ میلی‌متر</li><li>رنگ جوهر مشکی</li><li>بدنه‌ی آلومینیوم مات</li></ul>',
		'reviews' => array(),
	),
);

$product_ids = array();

foreach ( $catalog as $item ) {
	$existing = get_page_by_path( $item['slug'], OBJECT, 'product' );

	if ( $existing ) {
		$product_ids[ $item['slug'] ] = (int) $existing->ID;
		continue;
	}

	$product = new WC_Product_Simple();
	$product->set_name( $item['name'] );
	$product->set_slug( $item['slug'] );
	$product->set_sku( $item['sku'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_description( $item['desc'] );
	$product->set_short_description( $item['short'] );
	$product->set_regular_price( (string) $item['price'] );
	$product->set_manage_stock( true );
	$product->set_stock_quantity( 18 );
	$product->set_stock_status( 'instock' );
	$product->set_reviews_allowed( true );
	$product->set_featured( (bool) $item['featured'] );

	if ( $item['sale'] > 0 ) {
		$product->set_sale_price( (string) $item['sale'] );
	}

	if ( isset( $category_ids[ $item['cat'] ] ) ) {
		$product->set_category_ids( array( $category_ids[ $item['cat'] ] ) );
	}

	if ( ! empty( $image_ids[ $item['image'] ] ) ) {
		$product->set_image_id( $image_ids[ $item['image'] ] );
	}

	$product_id = $product->save();

	if ( ! $product_id ) {
		WP_CLI::warning( 'Could not create product: ' . $item['name'] );
		continue;
	}

	$product_ids[ $item['slug'] ] = $product_id;

	foreach ( $item['reviews'] as $review ) {
		$comment_id = wp_insert_comment(
			array(
				'comment_post_ID'      => $product_id,
				'comment_author'       => $review[0],
				'comment_author_email' => 'review-' . $product_id . '-' . wp_rand( 100, 999 ) . '@example.com',
				'comment_content'      => $review[2],
				'comment_type'         => 'review',
				'comment_approved'     => 1,
				'comment_date'         => gmdate( 'Y-m-d H:i:s', time() - wp_rand( 86400 * 3, 86400 * 80 ) ),
			)
		);

		if ( $comment_id ) {
			add_comment_meta( $comment_id, 'rating', (int) $review[1], true );
			add_comment_meta( $comment_id, 'verified', 1, true );
		}
	}

	WC_Comments::clear_transients( $product_id );
}

// Put one product out of stock so the UI state is represented.
if ( isset( $product_ids['pen-metal'] ) ) {
	$pen = wc_get_product( $product_ids['pen-metal'] );
	$pen->set_stock_quantity( 0 );
	$pen->set_stock_status( 'outofstock' );
	$pen->save();
}

// Remove the default product category.
$uncategorized = get_term_by( 'slug', 'uncategorized', 'product_cat' );

if ( $uncategorized instanceof WP_Term ) {
	wp_delete_term( $uncategorized->term_id, 'product_cat' );
}

/* -------------------------------------------------------------------------
 * Pages, posts and menus
 * ---------------------------------------------------------------------- */
$about_content = '<p>حجره یک انتخاب محدود است؛ مجموعه‌ای از اشیای کاربردی که با دقت انتخاب شده‌اند تا بدون شلوغی، سال‌ها همراه شما بمانند.</p><h2>چرا حجره؟</h2><p>ما به جای انبوهی از گزینه‌ها، تعداد کمی محصول را انتخاب می‌کنیم: جنس خوب، طراحی ساده و قیمتی منصفانه. هر محصول پیش از اضافه شدن به فروشگاه بررسی می‌شود.</p><ul><li>انتخاب محدود و سنجیده</li><li>ضمانت اصالت کالا</li><li>ارسال سریع و بسته‌بندی ایمن</li><li>۷ روز فرصت بازگشت</li></ul><p>اگر سؤالی دارید، از طریق صفحه‌ی تماس یا حساب کاربری با ما در ارتباط باشید.</p>';

$about_id = wp_insert_post(
	array(
		'post_title'   => 'درباره حجره',
		'post_name'    => 'about',
		'post_content' => $about_content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
	)
);

$home_id = wp_insert_post(
	array(
		'post_title'   => 'خانه',
		'post_name'    => 'home',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_type'    => 'page',
	)
);

$journal_id = wp_insert_post(
	array(
		'post_title'   => 'ژورنال',
		'post_name'    => 'journal',
		'post_content' => '',
		'post_status'  => 'publish',
		'post_type'    => 'page',
	)
);

if ( $home_id && ! is_wp_error( $home_id ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
}

if ( $journal_id && ! is_wp_error( $journal_id ) ) {
	update_option( 'page_for_posts', $journal_id );
}

// A shop-owner reference page for the four product card designs.
$demo_page = get_page_by_path( 'card-demo' );

if ( ! $demo_page ) {
	$demo_id = wp_insert_post(
		array(
			'post_title'   => 'دموی کارت‌های محصول',
			'post_name'    => 'card-demo',
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		)
	);

	if ( $demo_id && ! is_wp_error( $demo_id ) ) {
		update_post_meta( $demo_id, '_wp_page_template', 'page-card-demo.php' );
	}
}

// Remove starter content.
foreach ( array( 'sample-page', 'hello-world' ) as $slug ) {
	$post = get_page_by_path( $slug, OBJECT, 'page' );

	if ( $post ) {
		wp_delete_post( $post->ID, true );
	}

	$post = get_page_by_path( $slug, OBJECT, 'post' );

	if ( $post ) {
		wp_delete_post( $post->ID, true );
	}
}

$posts = array(
	array(
		'title'   => 'چطور یک فضای کوچک را مینیمال بچینیم؟',
		'slug'    => 'minimal-small-space',
		'image'   => 'blog-small-space',
		'excerpt' => 'مینیمالیسم به معنای خالی بودن نیست؛ به معنای انتخاب آگاهانه‌ی چیزهایی است که می‌مانند.',
		'content' => '<p>در فضای کوچک، هر شیء باید دلیلی برای بودن داشته باشد. قبل از خرید، از خودتان بپرسید این شیء چه کاری انجام می‌دهد که هیچ شیء دیگری در خانه انجام نمی‌دهد.</p><h2>۱. اول فضا را خالی کنید</h2><p>همه‌ی وسایل را از سطح‌ها بردارید و فقط چیزهایی را برگردانید که هر روز استفاده می‌کنید. باقی را در یک جعبه بگذارید و بعد از دو هفته دوباره بررسی کنید.</p><h2>۲. رنگ‌ها را محدود کنید</h2><p>دو یا سه رنگ خنثی انتخاب کنید و یک رنگ تأکیدی کوچک. همین محدودیت، فضا را بزرگ‌تر و آرام‌تر نشان می‌دهد.</p><h2>۳. کیفیت را به تعداد ترجیح دهید</h2><p>یک ماگ خوب که هر روز استفاده می‌شود، ارزش بیشتری از پنج ماگ تزئینی دارد. اشیای کمتر اما بهتر، هم فضا را آزاد می‌کنند و هم تجربه‌ی روزمره را بهتر می‌کنند.</p>',
	),
	array(
		'title'   => 'چرا جنس خوب، عمر طولانی‌تری دارد؟',
		'slug'    => 'better-materials',
		'image'   => 'blog-materials',
		'excerpt' => 'درباره‌ی سرامیک، پنبه‌ی ارگانیک و چوب؛ و اینکه چطور انتخاب جنس درست، هزینه‌ی بلندمدت را کم می‌کند.',
		'content' => '<p>وقتی از جنس خوب حرف می‌زنیم، درباره‌ی تجمل حرف نمی‌زنیم. درباره‌ی چیزی حرف می‌زنیم که در برابر استفاده‌ی روزمره دوام می‌آورد و با گذر زمان زیباتر می‌شود.</p><h2>سرامیک</h2><p>سرامیک پخت‌شده در دمای بالا، متخلخل نیست و لعاب آن ترک نمی‌خورد. این یعنی ماگ یا بشقاب شما سال‌ها رنگ و فرم خود را حفظ می‌کند.</p><h2>پنبه‌ی ارگانیک</h2><p>الیاف بلندتر پنبه‌ی ارگانیک باعث می‌شود پارچه پس از شست‌وشوهای مکرر نرم‌تر شود و کمتر پیلینگ بزند.</p><h2>چوب</h2><p>چوب طبیعی با روغن‌کاری مناسب، در برابر رطوبت مقاوم می‌شود و بافت آن با گذر زمان عمیق‌تر می‌شود. فقط کافی است هر چند ماه یک بار آن را nourish کنید.</p><p>انتخاب اشیای کمتر اما باکیفیت، در نهایت هم برای کیف پول و هم برای محیط زیست بهتر است.</p>',
	),
	array(
		'title'   => 'سه عادت ساده برای خرید کمتر و بهتر',
		'slug'    => 'better-buying-habits',
		'image'   => 'blog-habits',
		'excerpt' => 'قبل از کلیک روی «افزودن به سبد»، سه سؤال کوتاه از خودتان بپرسید؛ نتیجه‌اش خانه‌ای آرام‌تر است.',
		'content' => '<p>خرید آگاهانه یک مهارت است، نه یک محدودیت. با سه عادت کوچک می‌توانید چیزهای کمتری بخرید که بیشتر استفاده می‌شوند.</p><h2>۱. یک شب صبر کنید</h2><p>هر چیزی را که در لحظه‌ی هیجان می‌خواهید، به سبد خرید اضافه کنید اما تا فردا آن را نهایی نکنید. بیشتر خریدهای ناخواسته در همین فاصله کنار می‌روند.</p><h2>۲. بپرسید کجا استفاده می‌شود</h2><p>اگر نتوانید سه موقعیت مشخص برای استفاده‌ی روزمره از یک شیء نام ببرید، احتمالاً جای آن در خانه‌ی شما نیست.</p><h2>۳. تعمیر را به جایگزینی ترجیح دهید</h2><p>یک دوخت ساده، تعویض فتیله یا روغن‌کاری چوب می‌تواند عمر یک شیء را سال‌ها افزایش دهد. خرید کمتر و بهتر، هم آرام‌تر است و هم پایدارتر.</p>',
	),
);

foreach ( $posts as $post_data ) {
	$post_id = wp_insert_post(
		array(
			'post_title'    => $post_data['title'],
			'post_name'     => $post_data['slug'],
			'post_content'  => $post_data['content'],
			'post_excerpt'  => $post_data['excerpt'],
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_date'     => gmdate( 'Y-m-d H:i:s', time() - wp_rand( 86400 * 4, 86400 * 30 ) ),
			'post_author'   => 1,
		)
	);

	if ( $post_id && ! empty( $image_ids[ $post_data['image'] ] ) ) {
		set_post_thumbnail( $post_id, $image_ids[ $post_data['image'] ] );
	}
}

// Main menu.
$primary_menu_id = wp_create_nav_menu( 'منوی اصلی' );

if ( ! is_wp_error( $primary_menu_id ) ) {
	$menu_items = array(
		array( 'title' => 'خانه', 'url' => home_url( '/' ) ),
		array( 'title' => 'فروشگاه', 'object_id' => $shop_id, 'type' => 'post_type', 'object' => 'page' ),
		array( 'title' => 'ژورنال', 'object_id' => $journal_id, 'type' => 'post_type', 'object' => 'page' ),
		array( 'title' => 'درباره حجره', 'object_id' => $about_id, 'type' => 'post_type', 'object' => 'page' ),
	);

	foreach ( $menu_items as $item ) {
		$args = array(
			'menu-item-title'  => $item['title'],
			'menu-item-status' => 'publish',
		);

		if ( isset( $item['url'] ) ) {
			$args['menu-item-type'] = 'custom';
			$args['menu-item-url']  = $item['url'];
		} else {
			$args['menu-item-type']      = $item['type'];
			$args['menu-item-object']    = $item['object'];
			$args['menu-item-object-id'] = $item['object_id'];
		}

		wp_update_nav_menu_item( $primary_menu_id, 0, $args );
	}

	set_theme_mod( 'nav_menu_locations', array_merge( (array) get_theme_mod( 'nav_menu_locations', array() ), array( 'primary' => $primary_menu_id ) ) );
}

// Footer menu.
$footer_menu_id = wp_create_nav_menu( 'منوی فوتر' );

if ( ! is_wp_error( $footer_menu_id ) ) {
	$cart_id = wc_get_page_id( 'cart' );
	$account_id = wc_get_page_id( 'myaccount' );

	$footer_items = array(
		array( 'title' => 'سبد خرید', 'id' => $cart_id ),
		array( 'title' => 'حساب کاربری', 'id' => $account_id ),
		array( 'title' => 'درباره حجره', 'id' => $about_id ),
	);

	foreach ( $footer_items as $item ) {
		if ( $item['id'] <= 0 ) {
			continue;
		}

		wp_update_nav_menu_item(
			$footer_menu_id,
			0,
			array(
				'menu-item-title'     => $item['title'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $item['id'],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	set_theme_mod( 'nav_menu_locations', array_merge( (array) get_theme_mod( 'nav_menu_locations', array() ), array( 'footer' => $footer_menu_id ) ) );
}

/* -------------------------------------------------------------------------
 * Shipping
 * ---------------------------------------------------------------------- */
$has_iran_zone = false;

foreach ( WC_Shipping_Zones::get_zones() as $zone_data ) {
	if ( 'ایران' === $zone_data['zone_name'] ) {
		$has_iran_zone = true;
		break;
	}
}

if ( ! $has_iran_zone ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'ایران' );
	$zone->add_location( 'IR', 'country' );
	$zone->save();

	$flat_id = $zone->add_shipping_method( 'flat_rate' );
	update_option(
		'woocommerce_flat_rate_' . $flat_id . '_settings',
		array(
			'title'      => 'ارسال استاندارد',
			'tax_status' => 'taxable',
			'cost'       => '45000',
		)
	);

	$free_id = $zone->add_shipping_method( 'free_shipping' );
	update_option(
		'woocommerce_free_shipping_' . $free_id . '_settings',
		array(
			'title'      => 'ارسال رایگان',
			'requires'   => 'min_amount',
			'min_amount' => '500000',
			'ignore_discounts' => 'no',
		)
	);
}

/* -------------------------------------------------------------------------
 * Theme settings
 * ---------------------------------------------------------------------- */
if ( function_exists( 'hojreh_get_settings' ) ) {
	$settings = hojreh_get_settings();

	$settings['accent']       = '#0f766e';
	$settings['default_card'] = 'minimal';
	$settings['shop_columns'] = 4;
	$settings['dark_mode']    = 1;
	$settings['color_scheme'] = 'system';
	$settings['sticky_atc']   = 1;
	$settings['announcement'] = 'ارسال رایگان برای سفارش‌های بالای ۵۰۰٬۰۰۰ تومان';
	$settings['hero_title']   = 'خانه‌ی اشیای خوب';
	$settings['hero_text']    = 'انتخاب‌های مینیمال، باکیفیت و ماندگار برای زندگی روزمره.';
	$settings['hero_image']   = $image_ids['hero'] ?? 0;
	$settings['hero_cta']     = 'دیدن محصولات';
	$settings['hero_url']     = $shop_id > 0 ? get_permalink( $shop_id ) : home_url( '/' );
	$settings['phone']        = '۰۲۱-۸۸۷۷۶۶۵۵';
	$settings['email']        = 'hello@hojreh.store';
	$settings['address']      = 'تهران، خیابان کریم‌خان، پلاک ۲۴';
	$settings['instagram']    = 'https://instagram.com/hojreh';

	update_option( 'hojreh_settings', $settings );
}

flush_rewrite_rules( false );

WP_CLI::success( 'Hojreh demo content seeded: ' . count( $product_ids ) . ' products, ' . count( $category_ids ) . ' categories.' );
