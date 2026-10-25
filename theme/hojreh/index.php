<?php
/**
 * Blog / fallback index.
 *
 * @package Hojreh
 */

get_header();
?>
<div class="wrap hojreh-page">
	<header class="hojreh-page__head">
		<p class="hojreh-eyebrow">ژورنال حجره</p>
		<h1 class="hojreh-page__title"><?php echo is_home() && ! is_front_page() ? esc_html( single_post_title( '', false ) ) : 'نوشته‌ها'; ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
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
							<span class="hojreh-post-card__placeholder"><?php hojreh_the_icon( 'box', array( 'size' => 28 ) ); ?></span>
						<?php endif; ?>
					</a>
					<div class="hojreh-post-card__body">
						<time class="hojreh-post-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( hojreh_fa_digits( get_the_date() ) ); ?></time>
						<h2 class="hojreh-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="hojreh-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => 'قبلی', 'next_text' => 'بعدی' ) ); ?>
	<?php else : ?>
		<p class="hojreh-empty">مطلبی برای نمایش پیدا نشد.</p>
	<?php endif; ?>
</div>
<?php
get_footer();
