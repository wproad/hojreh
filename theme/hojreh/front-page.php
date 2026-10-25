<?php
/**
 * Front page: minimal editorial hero, categories and product showcases.
 *
 * @package Hojreh
 */

get_header();

$hojreh_settings = hojreh_get_settings();
$hojreh_shop_url = hojreh_shop_url();
$hojreh_hero_url = ! empty( $hojreh_settings['hero_url'] ) ? $hojreh_settings['hero_url'] : $hojreh_shop_url;
$hojreh_hero_id  = absint( $hojreh_settings['hero_image'] );
$hojreh_product  = null;

if ( function_exists( 'wc_get_products' ) ) {
	$hojreh_featured = wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => 1,
			'featured' => true,
		)
	);

	if ( empty( $hojreh_featured ) ) {
		$hojreh_featured = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 1,
				'orderby' => 'date',
			)
		);
	}

	$hojreh_product = $hojreh_featured[0] ?? null;
}

if ( ! $hojreh_hero_id && $hojreh_product ) {
	$hojreh_hero_id = $hojreh_product->get_image_id();
}

$hojreh_categories = array();

if ( taxonomy_exists( 'product_cat' ) ) {
	$hojreh_categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => 4,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $hojreh_categories ) ) {
		$hojreh_categories = array();
	}
}
?>

<section class="hojreh-hero">
	<div class="wrap hojreh-hero__inner">
		<div class="hojreh-hero__copy">
			<p class="hojreh-eyebrow"><span class="hojreh-pulse"></span> مجموعه‌ی حجره</p>
			<h1 class="hojreh-hero__title"><?php echo esc_html( $hojreh_settings['hero_title'] ); ?></h1>
			<p class="hojreh-hero__text"><?php echo esc_html( $hojreh_settings['hero_text'] ); ?></p>
			<div class="hojreh-hero__actions">
				<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $hojreh_hero_url ); ?>">
					<?php echo esc_html( $hojreh_settings['hero_cta'] ); ?>
					<?php hojreh_the_icon( 'arrow-left', array( 'size' => 18 ) ); ?>
				</a>
				<a class="btn btn-ghost" href="#newest">تازه‌ها</a>
			</div>
			<ul class="hojreh-hero__meta">
				<li><?php hojreh_the_icon( 'check', array( 'size' => 15 ) ); ?> ارسال سریع</li>
				<li><?php hojreh_the_icon( 'check', array( 'size' => 15 ) ); ?> ضمانت اصالت</li>
				<li><?php hojreh_the_icon( 'check', array( 'size' => 15 ) ); ?> بازگشت ۷ روزه</li>
			</ul>
		</div>

		<div class="hojreh-hero__visual">
			<div class="hojreh-hero__frame">
				<?php if ( $hojreh_hero_id ) : ?>
					<?php
					echo wp_get_attachment_image(
						$hojreh_hero_id,
						'hojreh-hero',
						false,
						array(
							'class'         => 'hojreh-hero__img',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
							'alt'           => $hojreh_settings['hero_title'],
						)
					);
					?>
				<?php else : ?>
					<span class="hojreh-hero__placeholder"><?php hojreh_the_icon( 'box', array( 'size' => 48 ) ); ?></span>
				<?php endif; ?>
				<span class="hojreh-hero__glow" aria-hidden="true"></span>
			</div>

			<?php if ( $hojreh_product ) : ?>
				<a class="hojreh-hero__product" href="<?php echo esc_url( $hojreh_product->get_permalink() ); ?>">
					<span class="hojreh-hero__product-thumb"><?php echo $hojreh_product->get_image( 'woocommerce_gallery_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="hojreh-hero__product-body">
						<small>پیشنهاد حجره</small>
						<strong><?php echo esc_html( $hojreh_product->get_name() ); ?></strong>
						<span class="hojreh-hero__product-price"><?php echo $hojreh_product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
					<span class="hojreh-hero__product-arrow"><?php hojreh_the_icon( 'arrow-left', array( 'size' => 17 ) ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="hojreh-values">
	<div class="wrap hojreh-values__grid">
		<?php
		$hojreh_values = array(
			array( 'truck', 'ارسال سریع', 'تحویل ۱ تا ۳ روز کاری' ),
			array( 'shield-check', 'ضمانت اصالت', 'کالای اصل و باکیفیت' ),
			array( 'refresh', 'بازگشت آسان', '۷ روز فرصت بازگشت' ),
			array( 'headphones', 'پشتیبانی واقعی', 'پاسخ‌گویی در ساعات کاری' ),
		);

		foreach ( $hojreh_values as $hojreh_value ) :
			?>
			<div class="hojreh-values__item">
				<span class="hojreh-values__icon"><?php hojreh_the_icon( $hojreh_value[0], array( 'size' => 20 ) ); ?></span>
				<span>
					<strong><?php echo esc_html( $hojreh_value[1] ); ?></strong>
					<small><?php echo esc_html( $hojreh_value[2] ); ?></small>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php if ( $hojreh_categories ) : ?>
	<section class="hojreh-section wrap hojreh-categories">
		<div class="hojreh-section__head">
			<h2 class="hojreh-section__title">دسته‌بندی‌ها</h2>
			<a class="hojreh-section__link" href="<?php echo esc_url( $hojreh_shop_url ); ?>">همه محصولات <?php hojreh_the_icon( 'arrow-left', array( 'size' => 16 ) ); ?></a>
		</div>

		<div class="hojreh-category-grid">
			<?php foreach ( $hojreh_categories as $hojreh_category ) : ?>
				<?php
				$hojreh_thumb_id = (int) get_term_meta( $hojreh_category->term_id, 'thumbnail_id', true );
				$hojreh_link     = get_term_link( $hojreh_category );

				if ( is_wp_error( $hojreh_link ) ) {
					continue;
				}
				?>
				<a class="hojreh-category" href="<?php echo esc_url( $hojreh_link ); ?>">
					<span class="hojreh-category__media">
						<?php if ( $hojreh_thumb_id ) : ?>
							<?php echo wp_get_attachment_image( $hojreh_thumb_id, 'hojreh-category', false, array( 'class' => 'hojreh-category__img', 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<span class="hojreh-category__placeholder"><?php hojreh_the_icon( 'layout-grid', array( 'size' => 26 ) ); ?></span>
						<?php endif; ?>
					</span>
					<span class="hojreh-category__body">
						<strong><?php echo esc_html( $hojreh_category->name ); ?></strong>
						<small><?php echo esc_html( hojreh_fa_digits( number_format_i18n( $hojreh_category->count ) ) ); ?> محصول</small>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<div class="wrap">
	<?php echo do_shortcode( '[hojreh_products featured="1" limit="8" card="overlay" title="منتخب حجره" orderby="date"]' ); ?>
</div>

<section class="wrap hojreh-promo-wrap">
	<div class="hojreh-promo">
		<div class="hojreh-promo__copy">
			<p class="hojreh-eyebrow hojreh-eyebrow--light">پیشنهاد این هفته</p>
			<h2>چند انتخاب خوب، با قیمتی بهتر.</h2>
			<p>محصولات منتخب با تخفیف محدود؛ تا پایان موجودی.</p>
		</div>
		<a class="btn btn-light btn-lg" href="<?php echo esc_url( $hojreh_shop_url ); ?>">
			دیدن تخفیف‌ها
			<?php hojreh_the_icon( 'arrow-left', array( 'size' => 18 ) ); ?>
		</a>
		<span class="hojreh-promo__shape hojreh-promo__shape--one" aria-hidden="true"></span>
		<span class="hojreh-promo__shape hojreh-promo__shape--two" aria-hidden="true"></span>
	</div>
</section>

<div class="wrap" id="newest">
	<?php echo do_shortcode( '[hojreh_products limit="8" card="minimal" title="تازه‌های حجره" orderby="date"]' ); ?>
</div>

<section class="wrap hojreh-editorial">
	<div class="hojreh-editorial__body">
		<p class="hojreh-eyebrow">درباره حجره</p>
		<h2>کمتر، اما بهتر.</h2>
		<p>حجره یک انتخاب محدود و سنجیده است؛ اشیایی که سال‌ها با شما می‌مانند. بدون شلوغی، بدون انتخاب‌های بی‌پایان.</p>
		<a class="hojreh-section__link" href="<?php echo esc_url( $hojreh_shop_url ); ?>">آشنایی با محصولات <?php hojreh_the_icon( 'arrow-left', array( 'size' => 16 ) ); ?></a>
	</div>
	<div class="hojreh-editorial__stats">
		<div><strong><?php echo esc_html( hojreh_fa_digits( '۷' ) ); ?> روز</strong><span>فرصت بازگشت</span></div>
		<div><strong><?php echo esc_html( hojreh_fa_digits( '۲۴' ) ); ?> ساعت</strong><span>پاسخ‌گویی</span></div>
		<div><strong><?php echo esc_html( hojreh_fa_digits( '۱۰۰' ) ); ?>٪</strong><span>ضمانت اصالت</span></div>
	</div>
</section>

<?php
get_footer();
