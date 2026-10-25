<?php
/**
 * Page template.
 *
 * @package Hojreh
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="wrap hojreh-page">
		<header class="hojreh-page__head">
			<h1 class="hojreh-page__title"><?php the_title(); ?></h1>
		</header>

		<?php
		$hojreh_is_store_page = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
		?>
		<article <?php post_class( $hojreh_is_store_page ? 'hojreh-woo-page' : 'hojreh-prose' ); ?>>
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<nav class="hojreh-page-links">',
					'after'  => '</nav>',
				)
			);
			?>
		</article>
	</div>
	<?php
endwhile;

get_footer();
