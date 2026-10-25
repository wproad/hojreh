<?php
/**
 * Template Name: دموی کارت‌های محصول
 *
 * A shop-owner reference page showing the four card variants and how to
 * compose product grids with the [hojreh_products] shortcode.
 *
 * @package Hojreh
 */

get_header();
?>
<div class="wrap hojreh-page">
	<header class="hojreh-page__head hojreh-demo-head">
		<p class="hojreh-eyebrow">مرجع طراحی</p>
		<h1 class="hojreh-page__title">چهار مدل کارت محصول</h1>
		<p class="hojreh-demo-lead">هر بخش را می‌توانید با یک شورت‌کد در هر صفحه‌ای از سایت استفاده کنید. رنگ، فاصله‌ها و حالت تیره به‌صورت خودکار با تنظیمات قالب هماهنگ می‌شوند.</p>
	</header>

	<section class="hojreh-section hojreh-demo-section">
		<div class="hojreh-demo-section__head">
			<div>
				<h2 class="hojreh-section__title">۱. کارت مینیمال</h2>
				<p>تصویر بزرگ، عنوان دو خطی و دکمه‌ی افزودن سریع. مناسب صفحه فروشگاه و شبکه‌های محصول.</p>
			</div>
			<code class="hojreh-code">[hojreh_products card="minimal" limit="4"]</code>
		</div>
		<?php echo do_shortcode( '[hojreh_products card="minimal" limit="4" columns="4" link=""]' ); ?>
	</section>

	<section class="hojreh-section hojreh-demo-section">
		<div class="hojreh-demo-section__head">
			<div>
				<h2 class="hojreh-section__title">۲. کارت پوششی</h2>
				<p>متن و قیمت روی تصویر؛ مناسب بخش «منتخب» و کمپین‌های بصری.</p>
			</div>
			<code class="hojreh-code">[hojreh_products card="overlay" limit="4"]</code>
		</div>
		<?php echo do_shortcode( '[hojreh_products card="overlay" limit="4" columns="4" link=""]' ); ?>
	</section>

	<section class="hojreh-section hojreh-demo-section">
		<div class="hojreh-demo-section__head">
			<div>
				<h2 class="hojreh-section__title">۳. کارت کادردار</h2>
				<p>قاب روشن، متن وسط‌چین و دکمه‌ی تمام‌عرض؛ مناسب دسته‌بندی‌های ویژه.</p>
			</div>
			<code class="hojreh-code">[hojreh_products card="bordered" limit="4"]</code>
		</div>
		<?php echo do_shortcode( '[hojreh_products card="bordered" limit="4" columns="4" link=""]' ); ?>
	</section>

	<section class="hojreh-section hojreh-demo-section">
		<div class="hojreh-demo-section__head">
			<div>
				<h2 class="hojreh-section__title">۴. کارت افقی</h2>
				<p>برای لیست‌ها، محصولات مرتبط در ستون کنار و صفحات جستجو؛ در موبایل تک‌ستونی نمایش داده می‌شود.</p>
			</div>
			<code class="hojreh-code">[hojreh_products card="horizontal" limit="4" columns="2"]</code>
		</div>
		<?php echo do_shortcode( '[hojreh_products card="horizontal" limit="4" columns="2" link=""]' ); ?>
	</section>

	<section class="hojreh-section hojreh-demo-section">
		<div class="hojreh-demo-section__head">
			<div>
				<h2 class="hojreh-section__title">فیلترها و ترتیب</h2>
				<p>شورت‌کد از دسته‌بندی، محصولات تخفیف‌دار و محصولات منتخب هم پشتیبانی می‌کند.</p>
			</div>
		</div>
		<pre class="hojreh-codeblock">[hojreh_products category="kitchen" limit="8" card="minimal" title="آشپزخانه"]
[hojreh_products on_sale="1" limit="4" card="bordered" title="تخفیف‌ها"]
[hojreh_products featured="1" limit="8" card="overlay" orderby="date"]</pre>
	</section>
</div>
<?php
get_footer();
