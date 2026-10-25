<?php
/**
 * Footer, cart drawer and mobile tab bar.
 *
 * @package Hojreh
 */

$hojreh_settings    = hojreh_get_settings();
$hojreh_account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$hojreh_cart_url    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
$hojreh_shop_url    = hojreh_shop_url();
$hojreh_year        = date_i18n( 'Y' );

$hojreh_cats = array();

if ( taxonomy_exists( 'product_cat' ) ) {
	$hojreh_cats = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => 5,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);

	if ( is_wp_error( $hojreh_cats ) ) {
		$hojreh_cats = array();
	}
}

$hojreh_is_shop_active  = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || is_product() );
$hojreh_is_cart_active  = function_exists( 'is_cart' ) && is_cart();
$hojreh_is_account_active = function_exists( 'is_account_page' ) && is_account_page();
?>
	</main>

	<footer class="hojreh-footer">
		<div class="wrap">
			<div class="hojreh-footer__grid">
				<div class="hojreh-footer__brand">
					<?php hojreh_branding(); ?>
					<p class="hojreh-footer__about">کالاهای ساده و ماندگار، انتخاب‌شده برای زندگی روزمره.</p>
					<?php if ( ! empty( $hojreh_settings['instagram'] ) ) : ?>
						<a class="hojreh-footer__social" href="<?php echo esc_url( $hojreh_settings['instagram'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="اینستاگرام">
							<?php hojreh_the_icon( 'brand-instagram', array( 'size' => 19 ) ); ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="hojreh-footer__col">
					<h2 class="hojreh-footer__title">فروشگاه</h2>
					<ul class="hojreh-footer__links">
						<li><a href="<?php echo esc_url( $hojreh_shop_url ); ?>">همه محصولات</a></li>
						<?php foreach ( $hojreh_cats as $hojreh_cat ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $hojreh_cat ) ); ?>"><?php echo esc_html( $hojreh_cat->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="hojreh-footer__col">
					<h2 class="hojreh-footer__title">دسترسی سریع</h2>
					<?php
					if ( has_nav_menu( 'footer' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'menu_class'     => 'hojreh-footer__links',
								'depth'          => 1,
							)
						);
					} else {
						?>
						<ul class="hojreh-footer__links">
							<li><a href="<?php echo esc_url( $hojreh_cart_url ); ?>">سبد خرید</a></li>
							<li><a href="<?php echo esc_url( $hojreh_account_url ); ?>">حساب کاربری</a></li>
							<li><a href="<?php echo esc_url( $hojreh_shop_url ); ?>">فروشگاه</a></li>
						</ul>
						<?php
					}
					?>
				</div>

				<div class="hojreh-footer__col">
					<h2 class="hojreh-footer__title">تماس با ما</h2>
					<ul class="hojreh-footer__contact">
						<?php if ( ! empty( $hojreh_settings['phone'] ) ) : ?>
							<li>
								<?php hojreh_the_icon( 'phone', array( 'size' => 17 ) ); ?>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', hojreh_latin_digits( $hojreh_settings['phone'] ) ) ); ?>" dir="ltr"><?php echo esc_html( $hojreh_settings['phone'] ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( ! empty( $hojreh_settings['email'] ) ) : ?>
							<li>
								<?php hojreh_the_icon( 'mail', array( 'size' => 17 ) ); ?>
								<a href="mailto:<?php echo esc_attr( $hojreh_settings['email'] ); ?>" dir="ltr"><?php echo esc_html( $hojreh_settings['email'] ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( ! empty( $hojreh_settings['address'] ) ) : ?>
							<li>
								<?php hojreh_the_icon( 'map-pin', array( 'size' => 17 ) ); ?>
								<span><?php echo esc_html( $hojreh_settings['address'] ); ?></span>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>

			<div class="hojreh-footer__bottom">
				<p>© <?php echo esc_html( hojreh_fa_digits( $hojreh_year ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?> — تمام حقوق محفوظ است.</p>
				<p class="hojreh-footer__trust">
					<?php hojreh_the_icon( 'lock', array( 'size' => 15 ) ); ?>
					پرداخت امن
					<span aria-hidden="true">·</span>
					<?php hojreh_the_icon( 'truck', array( 'size' => 15 ) ); ?>
					ارسال سریع
				</p>
			</div>
		</div>
	</footer>

	<nav class="hojreh-bottom-nav" aria-label="دسترسی سریع موبایل">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hojreh-bottom-nav__item<?php echo is_front_page() ? ' is-active' : ''; ?>">
			<?php hojreh_the_icon( 'home', array( 'size' => 21 ) ); ?>
			<span>خانه</span>
		</a>
		<a href="<?php echo esc_url( $hojreh_shop_url ); ?>" class="hojreh-bottom-nav__item<?php echo $hojreh_is_shop_active ? ' is-active' : ''; ?>">
			<?php hojreh_the_icon( 'layout-grid', array( 'size' => 21 ) ); ?>
			<span>فروشگاه</span>
		</a>
		<button type="button" class="hojreh-bottom-nav__item<?php echo $hojreh_is_cart_active ? ' is-active' : ''; ?>" data-drawer-open aria-haspopup="dialog" aria-controls="hojreh-drawer">
			<span class="hojreh-bottom-nav__cart">
				<?php hojreh_the_icon( 'shopping-bag', array( 'size' => 21 ) ); ?>
				<?php echo hojreh_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<span>سبد خرید</span>
		</button>
		<a href="<?php echo esc_url( $hojreh_account_url ); ?>" class="hojreh-bottom-nav__item<?php echo $hojreh_is_account_active ? ' is-active' : ''; ?>">
			<?php hojreh_the_icon( 'user', array( 'size' => 21 ) ); ?>
			<span>حساب</span>
		</a>
	</nav>

	<?php if ( function_exists( 'is_product' ) && is_product() && hojreh_get_option( 'sticky_atc', 1 ) ) : ?>
		<?php
		global $product;

		if ( $product instanceof WC_Product && $product->is_in_stock() ) :
			?>
			<div class="hojreh-sticky-atc" data-sticky-atc aria-hidden="true">
				<div class="hojreh-sticky-atc__meta">
					<span class="hojreh-sticky-atc__name"><?php echo esc_html( $product->get_name() ); ?></span>
					<span class="hojreh-sticky-atc__price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</div>
				<button type="button" class="btn btn-primary hojreh-sticky-atc__button" data-sticky-atc-button data-product-type="<?php echo esc_attr( $product->get_type() ); ?>">
					<?php hojreh_the_icon( 'shopping-bag', array( 'size' => 18 ) ); ?>
					افزودن به سبد
				</button>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="hojreh-backdrop" data-drawer-backdrop hidden></div>

	<aside class="hojreh-drawer" id="hojreh-drawer" role="dialog" aria-modal="true" aria-labelledby="hojreh-drawer-title" aria-hidden="true">
		<header class="hojreh-drawer__head">
			<div>
				<p class="hojreh-eyebrow">سبد خرید</p>
				<h2 class="hojreh-drawer__title" id="hojreh-drawer-title">خرید شما</h2>
			</div>
			<button type="button" class="icon-btn" data-drawer-close aria-label="بستن سبد خرید">
				<?php hojreh_the_icon( 'x', array( 'size' => 22 ) ); ?>
			</button>
		</header>

		<div class="hojreh-drawer__body" id="hojreh-drawer-body" aria-live="polite">
			<?php echo hojreh_mini_cart_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</aside>

	<div class="hojreh-toast" id="hojreh-toast" role="status" aria-live="polite" hidden></div>

	<?php wp_footer(); ?>
</div>
</body>
</html>
