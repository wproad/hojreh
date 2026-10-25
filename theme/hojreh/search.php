<?php
/**
 * Search results.
 *
 * @package Hojreh
 */

get_header();

$hojreh_is_product_search = ( 'product' === get_query_var( 'post_type' ) ) || ( isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="wrap hojreh-page">
	<header class="hojreh-page__head">
		<p class="hojreh-eyebrow">جستجو</p>
		<h1 class="hojreh-page__title">نتایج «<?php echo esc_html( get_search_query() ); ?>»</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php if ( $hojreh_is_product_search && function_exists( 'wc_get_product' ) ) : ?>
			<ul class="products hojreh-products hojreh-products--search" style="--lg-cols:<?php echo esc_attr( (string) hojreh_get_option( 'shop_columns', 4 ) ); ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					$hojreh_product = wc_get_product( get_the_ID() );

					if ( $hojreh_product ) {
						echo hojreh_product_card( $hojreh_product, array( 'variant' => hojreh_get_card_variant() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				endwhile;
				?>
			</ul>
		<?php else : ?>
			<div class="hojreh-post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'hojreh-post-card' ); ?>>
						<a class="hojreh-post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'class' => 'hojreh-post-card__img' ) ); ?>
							<?php else : ?>
								<span class="hojreh-post-card__placeholder"><?php hojreh_the_icon( 'search', array( 'size' => 28 ) ); ?></span>
							<?php endif; ?>
						</a>
						<div class="hojreh-post-card__body">
							<h2 class="hojreh-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="hojreh-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>

		<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => 'قبلی', 'next_text' => 'بعدی' ) ); ?>
	<?php else : ?>
		<div class="hojreh-empty-state">
			<?php hojreh_the_icon( 'search', array( 'size' => 32 ) ); ?>
			<h2>چیزی پیدا نشد</h2>
			<p>عبارت دیگری را امتحان کنید یا از دسته‌بندی‌ها کمک بگیرید.</p>
			<?php get_search_form(); ?>
			<a class="btn btn-primary" href="<?php echo esc_url( hojreh_shop_url() ); ?>">دیدن همه محصولات</a>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
