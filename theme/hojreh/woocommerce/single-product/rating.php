<?php
/**
 * Single product rating — numeric, Persian digits, no icon font.
 *
 * @package Hojreh
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! wc_review_ratings_enabled() ) {
	return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

if ( $rating_count <= 0 ) {
	return;
}
?>
<div class="woocommerce-product-rating">
	<?php echo hojreh_render_rating( '', $average, $review_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php if ( comments_open() ) : ?>
		<a href="#reviews" class="woocommerce-review-link">
			<?php
			echo esc_html(
				sprintf(
					'%s بازخورد مشتری',
					hojreh_fa_digits( number_format_i18n( $review_count ) )
				)
			);
			?>
		</a>
	<?php endif; ?>
</div>
