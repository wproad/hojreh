<?php
/**
 * Single post.
 *
 * @package Hojreh
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="wrap hojreh-single-post">
		<header class="hojreh-single-post__head">
			<p class="hojreh-eyebrow"><?php echo esc_html( hojreh_fa_digits( get_the_date() ) ); ?></p>
			<h1 class="hojreh-page__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="hojreh-single-post__media">
				<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="hojreh-prose">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<nav class="hojreh-page-links">',
					'after'  => '</nav>',
				)
			);
			?>
		</div>

		<footer class="hojreh-single-post__foot">
			<?php the_tags( '<ul class="hojreh-tags"><li>', '</li><li>', '</li></ul>' ); ?>
			<a class="btn btn-secondary" href="<?php echo esc_url( hojreh_shop_url() ); ?>">رفتن به فروشگاه</a>
		</footer>
	</article>
	<?php
endwhile;

get_footer();
