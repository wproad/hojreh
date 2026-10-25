<?php
/**
 * Reusable front-end pieces: digits, branding, product cards, mini cart, shortcode.
 *
 * @package Hojreh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Convert Latin digits to Persian digits.
 *
 * @param string|int|float $value Value to convert.
 * @return string
 */
function hojreh_fa_digits( $value ) {
	return strtr(
		(string) $value,
		array(
			'0' => '۰',
			'1' => '۱',
			'2' => '۲',
			'3' => '۳',
			'4' => '۴',
			'5' => '۵',
			'6' => '۶',
			'7' => '۷',
			'8' => '۸',
			'9' => '۹',
			',' => '٬',
		)
	);
}

/**
 * Convert Persian digits back to Latin for URLs such as tel:.
 *
 * @param string $value Value.
 * @return string
 */
function hojreh_latin_digits( $value ) {
	return strtr(
		(string) $value,
		array(
			'۰' => '0',
			'۱' => '1',
			'۲' => '2',
			'۳' => '3',
			'۴' => '4',
			'۵' => '5',
			'۶' => '6',
			'۷' => '7',
			'۸' => '8',
			'۹' => '9',
			'٬' => '',
			'،' => '',
		)
	);
}

/**
 * Current product card variant.
 *
 * @return string
 */
function hojreh_get_card_variant() {
	$variant = (string) hojreh_get_option( 'default_card', 'minimal' );

	if ( ! array_key_exists( $variant, hojreh_card_variants() ) ) {
		$variant = 'minimal';
	}

	return (string) apply_filters( 'hojreh_product_card_variant', $variant );
}

/**
 * Site branding with a text fallback.
 */
function hojreh_branding() {
	if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	$name     = get_bloginfo( 'name' );
	$tagline  = get_bloginfo( 'description' );
	$initial  = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );
	?>
	<a class="hojreh-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<span class="hojreh-brand__mark" aria-hidden="true"><?php echo esc_html( $initial ? $initial : 'ح' ); ?></span>
		<span class="hojreh-brand__text">
			<span class="hojreh-brand__name"><?php echo esc_html( $name ); ?></span>
			<?php if ( $tagline ) : ?>
				<span class="hojreh-brand__tagline"><?php echo esc_html( $tagline ); ?></span>
			<?php endif; ?>
		</span>
	</a>
	<?php
}

/**
 * Sale percentage for a product.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function hojreh_sale_percentage( $product ) {
	if ( ! $product->is_type( 'simple' ) && ! $product->is_type( 'variation' ) ) {
		return 0;
	}

	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();

	if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
		return 0;
	}

	return (int) round( ( ( $regular - $sale ) / $regular ) * 100 );
}

/**
 * Compact star + rating.
 *
 * @param WC_Product $product Product.
 * @param bool       $light   Use light colours (overlay cards).
 * @return string
 */
function hojreh_product_rating( $product, $light = false ) {
	$rating = (float) $product->get_average_rating();
	$count  = (int) $product->get_review_count();

	if ( $rating <= 0 ) {
		return '';
	}

	return sprintf(
		'<span class="hojreh-rating %1$s">%2$s<span>%3$s</span>%4$s</span>',
		$light ? 'text-white/85' : 'text-muted',
		hojreh_icon( 'star', array( 'size' => 14, 'class' => 'text-amber-400 fill-current' ) ),
		esc_html( hojreh_fa_digits( number_format_i18n( $rating, 1 ) ) ),
		$count > 0 ? '<span class="opacity-70">(' . esc_html( hojreh_fa_digits( number_format_i18n( $count ) ) ) . ')</span>' : ''
	);
}

/**
 * Badges for a product card.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function hojreh_product_badges( $product ) {
	$badges = array();

	if ( ! $product->is_in_stock() ) {
		$badges[] = array(
			'label' => 'ناموجود',
			'class' => 'bg-ink text-page',
		);
	} elseif ( $product->is_on_sale() ) {
		$percent = hojreh_sale_percentage( $product );
		$badges[] = array(
			'label' => $percent ? hojreh_fa_digits( $percent ) . '٪ تخفیف' : 'تخفیف',
			'class' => 'bg-accent text-[var(--accent-ink)]',
		);
	}

	if ( ! $badges ) {
		return '';
	}

	$html = '<div class="hojreh-badges">';

	foreach ( $badges as $badge ) {
		$html .= sprintf( '<span class="hojreh-badge %s">%s</span>', esc_attr( $badge['class'] ), esc_html( $badge['label'] ) );
	}

	return $html . '</div>';
}

/**
 * Add-to-cart control for cards.
 *
 * @param WC_Product $product Product.
 * @param string     $style   icon|full|compact.
 * @param bool       $light   Light styling for overlay cards.
 * @return string
 */
function hojreh_card_atc( $product, $style = 'icon', $light = false ) {
	$can_ajax = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();
	$label    = sprintf( 'افزودن %s به سبد خرید', $product->get_name() );

	if ( $can_ajax ) {
		$classes = array(
			'icon'    => 'hojreh-add-btn hojreh-add-btn--icon' . ( $light ? ' hojreh-add-btn--light' : '' ),
			'full'    => 'hojreh-add-btn hojreh-add-btn--full',
			'compact' => 'hojreh-add-btn hojreh-add-btn--compact',
		);

		return sprintf(
			'<button type="button" class="%1$s" data-hojreh-atc data-product-id="%2$d" data-quantity="1" aria-label="%3$s">%4$s<span class="hojreh-add-btn__label">افزودن</span></button>',
			esc_attr( $classes[ $style ] ?? $classes['icon'] ),
			(int) $product->get_id(),
			esc_attr( $label ),
			hojreh_icon( 'plus', array( 'size' => 18 ) )
		);
	}

	if ( ! $product->is_in_stock() ) {
		return sprintf(
			'<span class="hojreh-add-btn hojreh-add-btn--disabled">%s<span class="hojreh-add-btn__label">ناموجود</span></span>',
			hojreh_icon( 'x', array( 'size' => 18 ) )
		);
	}

	$text = $product->is_type( 'variable' ) ? 'انتخاب گزینه‌ها' : 'مشاهده محصول';

	return sprintf(
		'<a href="%1$s" class="hojreh-add-btn hojreh-add-btn--link %2$s">%3$s<span class="hojreh-add-btn__label">%4$s</span></a>',
		esc_url( $product->get_permalink() ),
		$light ? 'hojreh-add-btn--light' : '',
		hojreh_icon( 'arrow-left', array( 'size' => 17 ) ),
		esc_html( $text )
	);
}

/**
 * Render a product card.
 *
 * @param WC_Product|int|null  $product Product or ID.
 * @param array<string, mixed> $args    Card options.
 * @return string
 */
function hojreh_product_card( $product = null, $args = array() ) {
	$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;

	if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'variant'     => hojreh_get_card_variant(),
			'wrap'        => true,
			'class'       => '',
			'image_size'  => 'hojreh-card',
			'show_rating' => true,
			'show_badge'  => true,
			'eager'       => false,
		)
	);

	$variant = array_key_exists( $args['variant'], hojreh_card_variants() ) ? $args['variant'] : 'minimal';

	$title     = $product->get_name();
	$permalink = $product->get_permalink();
	$price     = $product->get_price_html();
	$image_id  = $product->get_image_id();

	$image_attr = array(
		'class'    => 'hojreh-card__img',
		'alt'      => $title,
		'loading'  => $args['eager'] ? 'eager' : 'lazy',
		'decoding' => 'async',
		'sizes'    => '(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 50vw',
	);

	if ( $args['eager'] ) {
		$image_attr['fetchpriority'] = 'high';
	}

	$image = $image_id
		? wp_get_attachment_image( $image_id, $args['image_size'], false, $image_attr )
		: wc_placeholder_img( $args['image_size'], $image_attr );

	$badges  = $args['show_badge'] ? hojreh_product_badges( $product ) : '';
	$rating  = $args['show_rating'] ? hojreh_product_rating( $product, 'overlay' === $variant ) : '';
	$excerpt = wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 14 );

	ob_start();

	switch ( $variant ) {
		case 'overlay':
			?>
			<div class="hojreh-card hojreh-card--overlay">
				<a href="<?php echo esc_url( $permalink ); ?>" class="hojreh-card__media" tabindex="-1" aria-hidden="true">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="hojreh-card__shade"></span>
					<?php echo $badges; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="hojreh-card__overlay-body">
						<span class="hojreh-card__title"><?php echo esc_html( $title ); ?></span>
						<?php if ( $rating ) : ?>
							<?php echo $rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
						<span class="hojreh-card__price"><?php echo $price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</a>
				<?php echo hojreh_card_atc( $product, 'icon', true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php
			break;

		case 'bordered':
			?>
			<div class="hojreh-card hojreh-card--bordered">
				<a href="<?php echo esc_url( $permalink ); ?>" class="hojreh-card__media" tabindex="-1" aria-hidden="true">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo $badges; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<div class="hojreh-card__body">
					<?php echo $rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<a href="<?php echo esc_url( $permalink ); ?>" class="hojreh-card__title"><?php echo esc_html( $title ); ?></a>
					<span class="hojreh-card__price"><?php echo $price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php echo hojreh_card_atc( $product, 'full' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
			<?php
			break;

		case 'horizontal':
			?>
			<div class="hojreh-card hojreh-card--horizontal">
				<a href="<?php echo esc_url( $permalink ); ?>" class="hojreh-card__media" tabindex="-1" aria-hidden="true">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo $badges; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<div class="hojreh-card__body">
					<div class="hojreh-card__text">
						<a href="<?php echo esc_url( $permalink ); ?>" class="hojreh-card__title"><?php echo esc_html( $title ); ?></a>
						<?php if ( $excerpt ) : ?>
							<p class="hojreh-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
						<?php endif; ?>
						<?php echo $rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="hojreh-card__bottom">
						<span class="hojreh-card__price"><?php echo $price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php echo hojreh_card_atc( $product, 'compact' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
			</div>
			<?php
			break;

		default:
			?>
			<div class="hojreh-card hojreh-card--minimal">
				<a href="<?php echo esc_url( $permalink ); ?>" class="hojreh-card__media" tabindex="-1" aria-hidden="true">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo $badges; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<div class="hojreh-card__body">
					<div class="hojreh-card__row">
						<div class="hojreh-card__text">
							<?php if ( $rating ) : ?>
								<?php echo $rating; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endif; ?>
							<a href="<?php echo esc_url( $permalink ); ?>" class="hojreh-card__title"><?php echo esc_html( $title ); ?></a>
							<span class="hojreh-card__price"><?php echo $price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</div>
						<?php echo hojreh_card_atc( $product, 'icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
			</div>
			<?php
			break;
	}

	$card = ob_get_clean();

	if ( ! $args['wrap'] ) {
		return $card;
	}

	ob_start();
	wc_product_class( 'hojreh-product-item hojreh-product-item--' . $variant . ' ' . $args['class'], $product );
	$classes = trim( ob_get_clean() );

	return '<li class="' . esc_attr( $classes ) . '">' . $card . '</li>';
}

/**
 * Print a product card.
 *
 * @param WC_Product|int|null  $product Product.
 * @param array<string, mixed> $args    Options.
 */
function hojreh_the_product_card( $product = null, $args = array() ) {
	echo hojreh_product_card( $product, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Header cart counter.
 *
 * @return string
 */
function hojreh_cart_count_html() {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

	return sprintf(
		'<span class="hojreh-cart-count%1$s" data-cart-count="%2$d" %3$s>%4$s</span>',
		$count > 0 ? ' is-filled' : '',
		(int) $count,
		$count > 0 ? '' : 'hidden',
		esc_html( hojreh_fa_digits( number_format_i18n( $count ) ) )
	);
}

/**
 * Quantity stepper markup.
 *
 * @param string $key      Cart item key (used by the drawer).
 * @param int    $value    Quantity.
 * @param bool   $disabled Whether the product is sold individually.
 * @param int    $max      Max quantity, 0 for unlimited.
 * @return string
 */
function hojreh_qty_control( $key, $value, $disabled = false, $max = 0 ) {
	$max_attr = $max > 0 ? ' max="' . esc_attr( (string) $max ) . '"' : '';

	return sprintf(
		'<div class="hojreh-qty" data-cart-key="%1$s">
			<button type="button" class="hojreh-qty__btn" data-qty="dec" aria-label="کاهش تعداد"%3$s>%4$s</button>
			<input class="hojreh-qty__input" type="number" inputmode="numeric" min="0"%2$s value="%5$d" aria-label="تعداد" %3$s>
			<button type="button" class="hojreh-qty__btn" data-qty="inc" aria-label="افزایش تعداد"%3$s>%6$s</button>
		</div>',
		esc_attr( $key ),
		$max_attr,
		$disabled ? ' disabled' : '',
		hojreh_icon( 'minus', array( 'size' => 14 ) ),
		(int) $value,
		hojreh_icon( 'plus', array( 'size' => 14 ) )
	);
}

/**
 * Mini cart markup used by the drawer and AJAX.
 *
 * @return string
 */
function hojreh_mini_cart_html() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	$cart = WC()->cart;

	ob_start();

	if ( $cart->is_empty() ) {
		?>
		<div class="hojreh-drawer-empty">
			<span class="hojreh-drawer-empty__icon"><?php hojreh_the_icon( 'shopping-bag', array( 'size' => 32 ) ); ?></span>
			<p class="hojreh-drawer-empty__title">سبد خرید شما خالی است</p>
			<p class="hojreh-drawer-empty__text">چند انتخاب خوب، فاصله‌ی کمی با شما دارد.</p>
			<a class="btn btn-primary" href="<?php echo esc_url( hojreh_shop_url() ); ?>">شروع خرید</a>
		</div>
		<?php
	} else {
		?>
		<ul class="hojreh-drawer-items">
			<?php
			foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
					continue;
				}

				$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail    = $_product->get_image(
					'woocommerce_gallery_thumbnail',
					array(
						'class'    => 'hojreh-drawer-item__img',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'alt'      => $_product->get_name(),
					)
				);
				$max_qty = $_product->get_max_purchase_quantity();
				?>
				<li class="hojreh-drawer-item" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
					<a class="hojreh-drawer-item__media" href="<?php echo esc_url( $_product->get_permalink() ); ?>">
						<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<div class="hojreh-drawer-item__body">
						<div class="hojreh-drawer-item__head">
							<a class="hojreh-drawer-item__name" href="<?php echo esc_url( $_product->get_permalink() ); ?>"><?php echo wp_kses_post( $product_name ); ?></a>
							<button type="button" class="hojreh-drawer-item__remove" data-hojreh-remove="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="حذف <?php echo esc_attr( $_product->get_name() ); ?>">
								<?php hojreh_the_icon( 'trash', array( 'size' => 16 ) ); ?>
							</button>
						</div>
						<?php
						$item_data = wc_get_formatted_cart_item_data( $cart_item );
						if ( $item_data ) {
							echo '<div class="hojreh-drawer-item__meta">' . wp_kses_post( $item_data ) . '</div>';
						}
						?>
						<div class="hojreh-drawer-item__bottom">
							<?php echo hojreh_qty_control( $cart_item_key, $cart_item['quantity'], $_product->is_sold_individually(), $max_qty ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="hojreh-drawer-item__price">
								<?php echo apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</div>
					</div>
				</li>
				<?php
			}
			?>
		</ul>

		<div class="hojreh-drawer-summary">
			<div class="hojreh-drawer-summary__row">
				<span>جمع سبد خرید</span>
				<strong class="hojreh-drawer-subtotal"><?php echo $cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
			</div>
			<p class="hojreh-drawer-summary__note">هزینه ارسال در مرحله تسویه حساب محاسبه می‌شود.</p>
			<div class="hojreh-drawer-actions">
				<a class="btn btn-secondary" href="<?php echo esc_url( wc_get_cart_url() ); ?>">مشاهده سبد</a>
				<a class="btn btn-primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">تسویه حساب</a>
			</div>
		</div>
		<?php
	}

	return ob_get_clean();
}

/**
 * Cart fragments for native WooCommerce AJAX events.
 *
 * @param array<string, string> $fragments Fragments.
 * @return array<string, string>
 */
function hojreh_cart_fragments( $fragments ) {
	$fragments['.hojreh-cart-count'] = hojreh_cart_count_html();
	$fragments['#hojreh-drawer-body'] = hojreh_mini_cart_html();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'hojreh_cart_fragments' );

/**
 * Product grid shortcode.
 *
 * @param array<string, string> $atts Attributes.
 * @return string
 */
function hojreh_products_shortcode( $atts ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return '';
	}

	$atts = shortcode_atts(
		array(
			'card'     => hojreh_get_card_variant(),
			'columns'  => '',
			'limit'    => 8,
			'category' => '',
			'title'    => '',
			'link'     => '',
			'on_sale'  => '',
			'featured' => '',
			'orderby'  => 'date',
			'class'    => '',
		),
		$atts,
		'hojreh_products'
	);

	$limit = min( 24, max( 1, absint( $atts['limit'] ) ) );
	$query = array(
		'status'   => 'publish',
		'limit'    => $limit,
		'paginate' => false,
		'orderby'  => in_array( $atts['orderby'], array( 'date', 'price', 'popularity', 'rating', 'menu_order', 'rand' ), true ) ? $atts['orderby'] : 'date',
		'order'    => 'DESC',
	);

	if ( 'rand' === $query['orderby'] ) {
		$query['orderby'] = 'rand';
	}

	if ( $atts['category'] ) {
		$query['category'] = array_map( 'sanitize_title', array_filter( explode( ',', $atts['category'] ) ) );
	}

	if ( $atts['on_sale'] ) {
		$sale_ids = wc_get_product_ids_on_sale();
		$query['include'] = $sale_ids ? $sale_ids : array( 0 );
	}

	if ( $atts['featured'] ) {
		$query['featured'] = true;
	}

	$products = wc_get_products( $query );

	if ( empty( $products ) ) {
		return '';
	}

	$columns = absint( $atts['columns'] );
	$columns = $columns >= 2 && $columns <= 4 ? $columns : (int) hojreh_get_option( 'shop_columns', 4 );
	$link    = $atts['link'] ? $atts['link'] : hojreh_shop_url();
	$title   = $atts['title'];

	ob_start();
	?>
	<section class="hojreh-section <?php echo esc_attr( $atts['class'] ); ?>">
		<?php if ( $title || $atts['link'] ) : ?>
			<div class="hojreh-section__head">
				<?php if ( $title ) : ?>
					<h2 class="hojreh-section__title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>
				<?php if ( $link ) : ?>
					<a class="hojreh-section__link" href="<?php echo esc_url( $link ); ?>">
						همه محصولات <?php hojreh_the_icon( 'arrow-left', array( 'size' => 16 ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<ul class="products hojreh-products hojreh-products--<?php echo esc_attr( $atts['card'] ); ?>" style="--lg-cols:<?php echo esc_attr( (string) $columns ); ?>">
			<?php
			$index = 0;
			foreach ( $products as $product ) {
				echo hojreh_product_card( $product, array( 'variant' => $atts['card'], 'eager' => $index < 2 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				++$index;
			}
			?>
		</ul>
	</section>
	<?php

	return ob_get_clean();
}
add_shortcode( 'hojreh_products', 'hojreh_products_shortcode' );

/**
 * Convert prices to Persian digits without touching inputs.
 *
 * @param string $formatted Formatted price HTML.
 * @return string
 */
function hojreh_persian_price( $formatted ) {
	return hojreh_fa_digits( $formatted );
}
add_filter( 'wc_price', 'hojreh_persian_price' );
