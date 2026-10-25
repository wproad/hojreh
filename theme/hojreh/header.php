<?php
/**
 * Header.
 *
 * @package Hojreh
 */

$hojreh_announcement = hojreh_get_option( 'announcement', '' );
$hojreh_account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$hojreh_shop_url     = hojreh_shop_url();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">

	<?php if ( $hojreh_announcement ) : ?>
		<div class="hojreh-announcement">
			<div class="wrap hojreh-announcement__inner">
				<?php hojreh_the_icon( 'sparkles', array( 'size' => 15 ) ); ?>
				<span><?php echo esc_html( $hojreh_announcement ); ?></span>
			</div>
		</div>
	<?php endif; ?>

	<header class="hojreh-header" data-header>
		<div class="wrap hojreh-header__inner">
			<button type="button" class="icon-btn hojreh-menu-toggle lg:hidden" data-menu-toggle aria-controls="hojreh-mobile-menu" aria-expanded="false" aria-label="باز کردن منو">
				<?php hojreh_the_icon( 'menu-2', array( 'size' => 22 ) ); ?>
			</button>

			<?php hojreh_branding(); ?>

			<nav class="hojreh-nav" aria-label="منوی اصلی">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'hojreh-menu',
						'fallback_cb'    => 'hojreh_primary_menu_fallback',
						'depth'          => 2,
					)
				);
				?>
			</nav>

			<div class="hojreh-header__actions">
				<button type="button" class="icon-btn" data-search-toggle aria-controls="hojreh-search" aria-expanded="false" aria-label="جستجو">
					<?php hojreh_the_icon( 'search', array( 'size' => 21 ) ); ?>
				</button>

				<?php if ( hojreh_get_option( 'dark_mode', 1 ) ) : ?>
					<button type="button" class="icon-btn" data-theme-toggle aria-label="تغییر حالت روشن و تیره" title="حالت روشن / تیره">
						<span class="hojreh-theme-icon hojreh-theme-icon--moon"><?php hojreh_the_icon( 'moon', array( 'size' => 20 ) ); ?></span>
						<span class="hojreh-theme-icon hojreh-theme-icon--sun"><?php hojreh_the_icon( 'sun', array( 'size' => 20 ) ); ?></span>
					</button>
				<?php endif; ?>

				<a class="icon-btn hojreh-account-link" href="<?php echo esc_url( $hojreh_account_url ); ?>" aria-label="حساب کاربری">
					<?php hojreh_the_icon( 'user', array( 'size' => 21 ) ); ?>
				</a>

				<button type="button" class="hojreh-cart-button" data-drawer-open aria-haspopup="dialog" aria-controls="hojreh-drawer" aria-label="باز کردن سبد خرید">
					<?php hojreh_the_icon( 'shopping-bag', array( 'size' => 21 ) ); ?>
					<?php echo hojreh_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>
		</div>

		<div class="hojreh-search" id="hojreh-search" hidden>
			<div class="wrap">
				<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="hojreh-search__form">
					<label class="screen-reader-text" for="hojreh-search-input">جستجو در محصولات</label>
					<?php hojreh_the_icon( 'search', array( 'size' => 20, 'class' => 'hojreh-search__icon' ) ); ?>
					<input type="search" id="hojreh-search-input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="جستجو در محصولات…" autocomplete="off">
					<input type="hidden" name="post_type" value="product">
					<button type="submit" class="btn btn-primary btn-sm">جستجو</button>
				</form>
			</div>
		</div>

		<div class="hojreh-mobile-menu" id="hojreh-mobile-menu" hidden>
			<nav aria-label="منوی موبایل">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'hojreh-mobile-menu__list',
						'fallback_cb'    => 'hojreh_primary_menu_fallback',
						'depth'          => 1,
					)
				);
				?>
			</nav>
			<div class="hojreh-mobile-menu__footer">
				<a class="btn btn-secondary w-full" href="<?php echo esc_url( $hojreh_account_url ); ?>">
					<?php hojreh_the_icon( 'user', array( 'size' => 18 ) ); ?>
					حساب کاربری
				</a>
				<a class="btn btn-primary w-full" href="<?php echo esc_url( $hojreh_shop_url ); ?>">
					<?php hojreh_the_icon( 'shopping-bag', array( 'size' => 18 ) ); ?>
					شروع خرید
				</a>
			</div>
		</div>
	</header>

	<main id="main" class="site-main">
