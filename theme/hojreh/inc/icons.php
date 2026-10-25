<?php
/**
 * Tabler icon helper. The SVG sprite is a static, cacheable asset.
 *
 * @package Hojreh
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return an inline <svg> that references a symbol in the sprite.
 *
 * @param string               $name Icon name, e.g. "shopping-cart".
 * @param array<string, mixed> $args Optional: size, class, stroke, label.
 * @return string
 */
function hojreh_icon( $name, $args = array() ) {
	$name = strtolower( preg_replace( '/[^a-z0-9-]/i', '', (string) $name ) );

	if ( ! $name ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'size'   => 20,
			'class'  => '',
			'stroke' => 1.7,
			'label'  => '',
		)
	);

	$href = HOJREH_URI . '/assets/icons/sprite.svg#i-' . $name;

	if ( ! file_exists( HOJREH_DIR . '/assets/icons/sprite.svg' ) ) {
		return '';
	}

	$classes = trim( 'icon ' . $args['class'] );
	$label   = $args['label'];
	$a11y    = $label
		? 'role="img" aria-label="' . esc_attr( $label ) . '"'
		: 'aria-hidden="true"';

	return sprintf(
		'<svg class="%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%3$s" stroke-linecap="round" stroke-linejoin="round" %4$s focusable="false"><use href="%5$s"></use></svg>',
		esc_attr( $classes ),
		(int) $args['size'],
		esc_attr( (string) $args['stroke'] ),
		$a11y,
		esc_url( $href )
	);
}

/**
 * Echo an icon.
 *
 * @param string               $name Icon name.
 * @param array<string, mixed> $args Optional args.
 */
function hojreh_the_icon( $name, $args = array() ) {
	echo hojreh_icon( $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
}
