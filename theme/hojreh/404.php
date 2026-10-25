<?php
/**
 * 404 template.
 *
 * @package Hojreh
 */

get_header();
?>
<div class="wrap">
	<div class="hojreh-empty-state hojreh-empty-state--404">
		<span class="hojreh-empty-state__code">۴۰۴</span>
		<h1>این صفحه پیدا نشد</h1>
		<p>ممکن است آدرس عوض شده باشد یا محصول از فروشگاه حذف شده باشد.</p>
		<?php get_search_form(); ?>
		<div class="hojreh-empty-state__actions">
			<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">بازگشت به خانه</a>
			<a class="btn btn-secondary" href="<?php echo esc_url( hojreh_shop_url() ); ?>">رفتن به فروشگاه</a>
		</div>
	</div>
</div>
<?php
get_footer();
