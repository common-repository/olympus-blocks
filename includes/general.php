<?php
/**
 * General actions and filters.
 *
 * @package Olympus Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enqueue Gutenberg block assets for backend editor.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 */
function olympus_blocks_do_block_editor_assets() {
	global $pagenow;

	$olympus_blocks_deps = array( 'wp-blocks', 'wp-i18n', 'wp-editor', 'wp-element', 'wp-compose', 'wp-data' );

	if ( 'widgets.php' === $pagenow ) {
		unset( $olympus_blocks_deps[2] );
	}

	wp_enqueue_script(
		'olympus-blocks',
		OLY_BLOCKS_DIR_URL . 'dist/blocks.js',
		$olympus_blocks_deps,
		filemtime( OLY_BLOCKS_DIR . 'dist/blocks.js' ),
		true
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'olympus-blocks', 'olympus-blocks' );
	}

	wp_enqueue_style(
		'olympus-blocks',
		OLY_BLOCKS_DIR_URL . 'dist/blocks.css',
		array( 'wp-edit-blocks' ),
		filemtime( OLY_BLOCKS_DIR . 'dist/blocks.css' )
	);

	$image_sizes = get_intermediate_image_sizes();
	$image_sizes = array_diff( $image_sizes, array( '1536x1536', '2048x2048' ) );
	$image_sizes[] = 'full';

	wp_localize_script(
		'olympus-blocks',
		'olyBlocksInfo',
		array(
			'isOlympus' => defined( 'OLY_VERSION' ),
			'hasCustomFields' => post_type_supports( get_post_type(), 'custom-fields' ),
			'imageSizes' => $image_sizes,
			'svgDivider' => olympus_blocks_get_separator_styles(),
			'svgShapes' => olympus_blocks_get_svg_shapes(),
			'syncResponsivePreviews' => olympus_blocks_get_option( 'sync_responsive_previews' ),
		)
	);

	$defaults = olympus_blocks_get_block_defaults();

	wp_localize_script(
		'olympus-blocks',
		'olyBlocksDefaults',
		$defaults
	);

	wp_localize_script(
		'olympus-blocks',
		'olyBlocksStyling',
		olympus_blocks_get_default_styles()
	);
}
add_action( 'enqueue_block_editor_assets', 'olympus_blocks_do_block_editor_assets' );

/**
 * Add category to Gutenberg.
 *
 * @param array $categories Existing categories.
 */
function olympus_blocks_do_category( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'olympus-blocks',
				'title' => __( 'Olympus Blocks', 'olympus-blocks' ),
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'olympus_blocks_do_category' );

/**
 * Do Google Fonts.
 */
function olympus_blocks_do_google_fonts() {
	$fonts_url = olympus_blocks_get_google_fonts_uri();

	if ( $fonts_url ) {
		wp_enqueue_style( 'olympus-blocks-google-fonts', $fonts_url, array(), null, 'all' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}
}
add_action( 'wp_enqueue_scripts', 'olympus_blocks_do_google_fonts' );
add_action( 'enqueue_block_editor_assets', 'olympus_blocks_do_google_fonts' );

/**
 * Set our CSS print method.
 *
 * @param string $method Existing method.
 */
function olympus_blocks_set_load_css_file( $method ) {
	$method = olympus_blocks_get_option( 'load_css_file' );

	if ( is_single() ) {
		$method = 'inline';
	}

	return $method;
}
add_filter( 'olympus_blocks_load_css_file', 'olympus_blocks_set_load_css_file' );

/**
 * Add blocks that can be displayed in post excerpts.
 *
 * @param array $allowed Existing allowed blocks.
 */
function olympus_blocks_set_excerpt_allowed_blocks( $allowed ) {
	$allowed[] = 'olympusblocks/heading';
	$allowed[] = 'olympusblocks/divider';
	$allowed[] = 'olympusblocks/section';

	return $allowed;
}
add_filter( 'excerpt_allowed_blocks', 'olympus_blocks_set_excerpt_allowed_blocks' );

/**
 * Get our divider SVG icons.
 *
 * @param array icons.
 */
function olympus_blocks_get_separator_styles() {
	return array(
		'curly'   => [
			'label' => _x( 'Curly', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M0,21c3.3,0,8.3-0.9,15.7-7.1c6.6-5.4,4.4-9.3,2.4-10.3c-3.4-1.8-7.7,1.3-7.3,8.8C11.2,20,17.1,21,24,21"/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => false,
			'divider_group' => 'line',
		],
		'curved'   => [
			'label' => _x( 'Curved', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M0,6c6,0,6,13,12,13S18,6,24,6"/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => false,
			'divider_group' => 'line',
		],
		'multiple'   => [
			'label' => _x( 'Multiple', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M24,8v12H0V8H24z M24,4v1H0V4H24z"/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'pattern',
		],
		'slashes' => [
			'label' => _x( 'Slashes', 'shapes', 'olympus-blocks' ),
			'shape' => '<g transform="translate(-12.000000, 0)"><path d="M28,0L10,18"/><path d="M18,0L0,18"/><path d="M48,0L30,18"/><path d="M38,0L20,18"/></g>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => false,
			'view_box' => '0 0 20 16',
			'divider_group' => 'line',
		],
		'squared' => [
			'label' => _x( 'Squared', 'shapes', 'olympus-blocks' ),
			'shape' => '<polyline points="0,6 6,6 6,18 18,18 18,6 24,6 	"/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => false,
			'divider_group' => 'line',
		],
		'wavy'   => [
			'label' => _x( 'Wavy', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M0,6c6,0,0.9,11.1,6.9,11.1S18,6,24,6"/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => false,
			'divider_group' => 'line',
		],
		'zigzag'  => [
			'label' => _x( 'Zigzag', 'shapes', 'olympus-blocks' ),
			'shape' => '<polyline points="0,18 12,6 24,18 "/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => false,
			'divider_group' => 'line',
		],
		'arrows'   => [
			'label' => _x( 'Arrows', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M14.2,4c0.3,0,0.5,0.1,0.7,0.3l7.9,7.2c0.2,0.2,0.3,0.4,0.3,0.7s-0.1,0.5-0.3,0.7l-7.9,7.2c-0.2,0.2-0.4,0.3-0.7,0.3s-0.5-0.1-0.7-0.3s-0.3-0.4-0.3-0.7l0-2.9l-11.5,0c-0.4,0-0.7-0.3-0.7-0.7V9.4C1,9,1.3,8.7,1.7,8.7l11.5,0l0-3.6c0-0.3,0.1-0.5,0.3-0.7S13.9,4,14.2,4z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => true,
			'round' => true,
			'divider_group' => 'pattern',
		],
		'pluses'   => [
			'label' => _x( 'Pluses', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M21.4,9.6h-7.1V2.6c0-0.9-0.7-1.6-1.6-1.6h-1.6c-0.9,0-1.6,0.7-1.6,1.6v7.1H2.6C1.7,9.6,1,10.3,1,11.2v1.6c0,0.9,0.7,1.6,1.6,1.6h7.1v7.1c0,0.9,0.7,1.6,1.6,1.6h1.6c0.9,0,1.6-0.7,1.6-1.6v-7.1h7.1c0.9,0,1.6-0.7,1.6-1.6v-1.6C23,10.3,22.3,9.6,21.4,9.6z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => true,
			'round' => false,
			'divider_group' => 'pattern',
		],
		'rhombus'   => [
			'label' => _x( 'Rhombus', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M12.7,2.3c-0.4-0.4-1.1-0.4-1.5,0l-8,9.1c-0.3,0.4-0.3,0.9,0,1.2l8,9.1c0.4,0.4,1.1,0.4,1.5,0l8-9.1c0.3-0.4,0.3-0.9,0-1.2L12.7,2.3z"/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => false,
			'divider_group' => 'pattern',
		],
		'parallelogram'   => [
			'label' => _x( 'Parallelogram', 'shapes', 'olympus-blocks' ),
			'shape' => '<polygon points="9.4,2 24,2 14.6,21.6 0,21.6"/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => false,
			'divider_group' => 'pattern',
		],
		'rectangles'   => [
			'label' => _x( 'Rectangles', 'shapes', 'olympus-blocks' ),
			'shape' => '<rect x="15" y="0" width="30" height="30"/>',
			'preserve_aspect_ratio' => false,
			'supports_amount' => true,
			'round' => true,
			'divider_group' => 'pattern',
			'view_box' => '0 0 60 30',
		],
		'dots_tribal'   => [
			'label' => _x( 'Dots', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M3,10.2c2.6,0,2.6,2,2.6,3.2S4.4,16.5,3,16.5s-3-1.4-3-3.2S0.4,10.2,3,10.2z M18.8,10.2c1.7,0,3.2,1.4,3.2,3.2s-1.4,3.2-3.2,3.2c-1.7,0-3.2-1.4-3.2-3.2S17,10.2,18.8,10.2z M34.6,10.2c1.5,0,2.6,1.4,2.6,3.2s-0.5,3.2-1.9,3.2c-1.5,0-3.4-1.4-3.4-3.2S33.1,10.2,34.6,10.2z M50.5,10.2c1.7,0,3.2,1.4,3.2,3.2s-1.4,3.2-3.2,3.2c-1.7,0-3.3-0.9-3.3-2.6S48.7,10.2,50.5,10.2z M66.2,10.2c1.5,0,3.4,1.4,3.4,3.2s-1.9,3.2-3.4,3.2c-1.5,0-2.6-0.4-2.6-2.1S64.8,10.2,66.2,10.2z M82.2,10.2c1.7,0.8,2.6,1.4,2.6,3.2s-0.1,3.2-1.6,3.2c-1.5,0-3.7-1.4-3.7-3.2S80.5,9.4,82.2,10.2zM98.6,10.2c1.5,0,2.6,0.4,2.6,2.1s-1.2,4.2-2.6,4.2c-1.5,0-3.7-0.4-3.7-2.1S97.1,10.2,98.6,10.2z M113.4,10.2c1.2,0,2.2,0.9,2.2,3.2s-0.1,3.2-1.3,3.2s-3.1-1.4-3.1-3.2S112.2,10.2,113.4,10.2z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 126 26',
		],
		'trees_2_tribal'   => [
			'label' => _x( 'Fir Tree', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M111.9,18.3v3.4H109v-3.4H111.9z M90.8,18.3v3.4H88v-3.4H90.8z M69.8,18.3v3.4h-2.9v-3.4H69.8z M48.8,18.3v3.4h-2.9v-3.4H48.8z M27.7,18.3v3.4h-2.9v-3.4H27.7z M6.7,18.3v3.4H3.8v-3.4H6.7z M46.4,4l4.3,4.8l-1.8,0l3.5,4.4l-2.2-0.1l3,3.3l-11,0.4l3.6-3.8l-2.9-0.1l3.1-4.2l-1.9,0L46.4,4z M111.4,4l2.4,4.8l-1.8,0l3.5,4.4l-2.5-0.1l3.3,3.3h-11l3.1-3.4l-2.5-0.1l3.1-4.2l-1.9,0L111.4,4z M89.9,4l2.9,4.8l-1.9,0l3.2,4.2l-2.5,0l3.5,3.5l-11-0.4l3-3.1l-2.4,0L88,8.8l-1.9,0L89.9,4z M68.6,4l3,4.4l-1.9,0.1l3.4,4.1l-2.7,0.1l3.8,3.7H63.8l2.9-3.6l-2.9,0.1L67,8.7l-2,0.1L68.6,4z M26.5,4l3,4.4l-1.9,0.1l3.7,4.7l-2.5-0.1l3.3,3.3H21l3.1-3.4l-2.5-0.1l3.2-4.3l-2,0.1L26.5,4z M4.9,4l3.7,4.8l-1.5,0l3.1,4.2L7.6,13l3.4,3.4H0l3-3.3l-2.3,0.1l3.5-4.4l-2.3,0L4.9,4z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 126 26',
		],
		'rounds_tribal'   => [
			'label' => _x( 'Half Rounds', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M11.9,15.9L11.9,15.9L0,16c-0.2-3.7,1.5-5.7,4.9-6C10,9.6,12.4,14.2,11.9,15.9zM26.9,15.9L26.9,15.9L15,16c0.5-3.7,2.5-5.7,5.9-6C26,9.6,27.4,14.2,26.9,15.9z M37.1,10c3.4,0.3,5.1,2.3,4.9,6H30.1C29.5,14.4,31.9,9.6,37.1,10z M57,15.9L57,15.9L45,16c0-3.4,1.6-5.4,4.9-5.9C54.8,9.3,57.4,14.2,57,15.9z M71.9,15.9L71.9,15.9L60,16c-0.2-3.7,1.5-5.7,4.9-6C70,9.6,72.4,14.2,71.9,15.9z M82.2,10c3.4,0.3,5,2.3,4.8,6H75.3C74,13,77.1,9.6,82.2,10zM101.9,15.9L101.9,15.9L90,16c-0.2-3.7,1.5-5.7,4.9-6C100,9.6,102.4,14.2,101.9,15.9z M112.1,10.1c2.7,0.5,4.3,2.5,4.9,5.9h-11.9l0,0C104.5,14.4,108,9.3,112.1,10.1z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 120 26',
		],
		'leaves_tribal'   => [
			'label' => _x( 'Leaves', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M3,1.5C5,4.9,6,8.8,6,13s-1.7,8.1-5,11.5C0.3,21.1,0,17.2,0,13S1,4.9,3,1.5z M16,1.5c2,3.4,3,7.3,3,11.5s-1,8.1-3,11.5c-2-4.1-3-8.3-3-12.5S14,4.3,16,1.5z M29,1.5c2,4.8,3,9.3,3,13.5s-1,7.4-3,9.5c-2-3.4-3-7.3-3-11.5S27,4.9,29,1.5z M41.1,1.5C43.7,4.9,45,8.8,45,13s-1,8.1-3,11.5c-2-3.4-3-7.3-3-11.5S39.7,4.9,41.1,1.5zM55,1.5c2,2.8,3,6.3,3,10.5s-1.3,8.4-4,12.5c-1.3-3.4-2-7.3-2-11.5S53,4.9,55,1.5z M68,1.5c2,3.4,3,7.3,3,11.5s-0.7,8.1-2,11.5c-2.7-4.8-4-9.3-4-13.5S66,3.6,68,1.5z M82,1.5c1.3,4.8,2,9.3,2,13.5s-1,7.4-3,9.5c-2-3.4-3-7.3-3-11.5S79.3,4.9,82,1.5z M94,1.5c2,3.4,3,7.3,3,11.5s-1.3,8.1-4,11.5c-1.3-1.4-2-4.3-2-8.5S92,6.9,94,1.5z M107,1.5c2,2.1,3,5.3,3,9.5s-0.7,8.7-2,13.5c-2.7-3.4-4-7.3-4-11.5S105,4.9,107,1.5z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 117 26',
		],
		'stripes_tribal'   => [
			'label' => _x( 'Stripes', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M54,1.6V26h-9V2.5L54,1.6z M69,1.6v23.3L60,26V1.6H69z M24,1.6v23.5l-9-0.6V1.6H24z M30,0l9,0.7v24.5h-9V0z M9,2.5v22H0V3.7L9,2.5z M75,1.6l9,0.9v22h-9V1.6z M99,2.7v21.7h-9V3.8L99,2.7z M114,3.8v20.7l-9-0.5V3.8L114,3.8z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 120 26',
		],
		'squares_tribal'   => [
			'label' => _x( 'Squares', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M46.8,7.8v11.5L36,18.6V7.8H46.8z M82.4,7.8L84,18.6l-12,0.7L70.4,7.8H82.4z M0,7.8l12,0.9v9.9H1.3L0,7.8z M30,7.8v10.8H19L18,7.8H30z M63.7,7.8L66,18.6H54V9.5L63.7,7.8z M89.8,7L102,7.8v10.8H91.2L89.8,7zM108,7.8l12,0.9v8.9l-12,1V7.8z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 126 26',
		],
		'trees_tribal'   => [
			'label' => _x( 'Trees', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M6.4,2l4.2,5.7H7.7v2.7l3.8,5.2l-3.8,0v7.8H4.8v-7.8H0l4.8-5.2V7.7H1.1L6.4,2z M25.6,2L31,7.7h-3.7v2.7l4.8,5.2h-4.8v7.8h-2.8v-7.8l-3.8,0l3.8-5.2V7.7h-2.9L25.6,2z M47.5,2l4.2,5.7h-3.3v2.7l3.8,5.2l-3.8,0l0.4,7.8h-2.8v-7.8H41l4.8-5.2V7.7h-3.7L47.5,2z M66.2,2l5.4,5.7h-3.7v2.7l4.8,5.2h-4.8v7.8H65v-7.8l-3.8,0l3.8-5.2V7.7h-2.9L66.2,2zM87.4,2l4.8,5.7h-2.9v3.1l3.8,4.8l-3.8,0v7.8h-2.8v-7.8h-4.8l4.8-4.8V7.7h-3.7L87.4,2z M107.3,2l5.4,5.7h-3.7v2.7l4.8,5.2h-4.8v7.8H106v-7.8l-3.8,0l3.8-5.2V7.7h-2.9L107.3,2z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 123 26',
		],
		'planes_tribal'   => [
			'label' => _x( 'Tribal', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M29.6,10.3l2.1,2.2l-3.6,3.3h7v2.9h-7l3.6,3.5l-2.1,1.7l-5.2-5.2h-5.8v-2.9h5.8L29.6,10.3z M70.9,9.6l2.1,1.7l-3.6,3.5h7v2.9h-7l3.6,3.3l-2.1,2.2l-5.2-5.5h-5.8v-2.9h5.8L70.9,9.6z M111.5,9.6l2.1,1.7l-3.6,3.5h7v2.9h-7l3.6,3.3l-2.1,2.2l-5.2-5.5h-5.8v-2.9h5.8L111.5,9.6z M50.2,2.7l2.1,1.7l-3.6,3.5h7v2.9h-7l3.6,3.3l-2.1,2.2L45,10.7h-5.8V7.9H45L50.2,2.7z M11,2l2.1,1.7L9.6,7.2h7V10h-7l3.6,3.3L11,15.5L5.8,10H0V7.2h5.8L11,2z M91.5,2l2.1,2.2l-3.6,3.3h7v2.9h-7l3.6,3.5l-2.1,1.7l-5.2-5.2h-5.8V7.5h5.8L91.5,2z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 121 26',
		],
		'x_tribal'   => [
			'label' => _x( 'X', 'shapes', 'olympus-blocks' ),
			'shape' => '<path d="M10.7,6l2.5,2.6l-4,4.3l4,5.4l-2.5,1.9l-4.5-5.2l-3.9,4.2L0.7,17L4,13.1L0,8.6l2.3-1.3l3.9,3.9L10.7,6z M23.9,6.6l4.2,4.5L32,7.2l2.3,1.3l-4,4.5l3.2,3.9L32,19.1l-3.9-3.3l-4.5,4.3l-2.5-1.9l4.4-5.1l-4.2-3.9L23.9,6.6zM73.5,6L76,8.6l-4,4.3l4,5.4l-2.5,1.9l-4.5-5.2l-3.9,4.2L63.5,17l4.1-4.7L63.5,8l2.3-1.3l4.1,3.6L73.5,6z M94,6l2.5,2.6l-4,4.3l4,5.4L94,20.1l-3.9-5l-3.9,4.2L84,17l3.2-3.9L84,8.6l2.3-1.3l3.2,3.9L94,6z M106.9,6l4.5,5.1l3.9-3.9l2.3,1.3l-4,4.5l3.2,3.9l-1.6,2.1l-3.9-4.2l-4.5,5.2l-2.5-1.9l4-5.4l-4-4.3L106.9,6z M53.1,6l2.5,2.6l-4,4.3l4,4.6l-2.5,1.9l-4.5-4.5l-3.5,4.5L43.1,17l3.2-3.9l-4-4.5l2.3-1.3l3.9,3.9L53.1,6z"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 126 26',
		],
		'zigzag_tribal'   => [
			'label' => _x( 'Zigzag', 'shapes', 'olympus-blocks' ),
			'shape' => '<polygon points="0,14.4 0,21 11.5,12.4 21.3,20 30.4,11.1 40.3,20 51,12.4 60.6,20 69.6,11.1 79.3,20 90.1,12.4 99.6,20 109.7,11.1 120,21 120,14.4 109.7,5 99.6,13 90.1,5 79.3,14.5 71,5.7 60.6,12.4 51,5 40.3,14.5 31.1,5 21.3,13 11.5,5 	"/>',
			'preserve_aspect_ratio' => true,
			'supports_amount' => false,
			'round' => false,
			'divider_group' => 'tribal',
			'view_box' => '0 0 120 26',
		],
	);
}

/**
 * Build SVG element markup based on the widgets settings.
 *
 * @return string - An SVG element.
 */
function olympus_blocks_build_svg( $svg ) {
	$svg_shapes = olympus_blocks_get_separator_styles();

	$selected_pattern = $svg_shapes[ $svg ];
	$preserve_aspect_ratio = $selected_pattern['preserve_aspect_ratio'] ? 'xMidYMid meet' : 'none';
	$view_box = isset( $selected_pattern['view_box'] ) ? $selected_pattern['view_box'] : '0 0 24 24';

	$attr = [
		'preserveAspectRatio' => $preserve_aspect_ratio,
		'overflow' => 'visible',
		'height' => '100%',
		'viewBox' => $view_box,
	];

	if ( 'line' !== $selected_pattern['divider_group'] ) {
		$attr['fill'] = 'black';
		$attr['stroke'] = 'none';
	} else {
		$attr['fill'] = 'none';
		$attr['stroke'] = 'black';
		$attr['stroke-linecap'] = 'square';
		$attr['stroke-miterlimit'] = '10';
	}

	$shape = $selected_pattern['shape'];
	$render = array();
	foreach ( $attr as $key => $val ) {
		$render[] = sprintf( '%1$s="%2$s"', $key, esc_attr( $val ) );
	}

	return '<svg xmlns="http://www.w3.org/2000/svg" ' . implode( ' ', $render ) . '>' . $shape . '</svg>';
}

function olympus_blocks_svg_to_data_uri( $svg ) {
	return str_replace(
		[ '<', '>', '"', '#' ],
		[ '%3C', '%3E', "'", '%23' ],
		olympus_blocks_build_svg( $svg )
	);
}

/**
 * Add shape divider to Section.
 *
 * @param string $output The current block output.
 * @param array  $attributes The current block attributes.
 */
function olympus_blocks_do_shape_divider( $output, $attributes ) {
	$defaults = olympus_blocks_get_block_defaults();

	$settings = wp_parse_args(
		$attributes,
		$defaults['section']
	);

	if ( ! empty( $settings['shapeDividers'] ) ) {
		$shapes = olympus_blocks_get_svg_shapes();
		$shape_values = array();

		foreach ( $shapes as $group => $data ) {
			if ( ! empty( $data['svgs'] ) && is_array( $data['svgs'] ) ) {
				foreach ( $data['svgs'] as $key => $shape ) {
					$shape_values[ $key ] = $shape['icon'];
				}
			}
		}

		$output .= '<div class="ob-shapes">';

		foreach ( (array) $settings['shapeDividers'] as $index => $option ) {
			if ( ! empty( $option['shape'] ) ) {
				if ( isset( $shape_values[ $option['shape'] ] ) ) {
					$shapeNumber = $index + 1;

					$output .= sprintf(
						'<div class="ob-shape ob-shape-' . $shapeNumber . '">%s</div>',
						$shape_values[ $option['shape'] ]
					);
				}
			}
		}

		$output .= '</div>';
	}

	return $output;
}
add_filter( 'olympus_blocks_before_section_close', 'olympus_blocks_do_shape_divider', 10, 2 );

/**
 * Process all widget content for potential styling.
 *
 * @param string $content The existing content to process.
 */
function olympus_blocks_do_widget_styling( $content ) {
	$widget_blocks = get_option( 'widget_block' );

	foreach ( (array) $widget_blocks as $block ) {
		if ( isset( $block['content'] ) ) {
			$content .= $block['content'];
		}
	}

	return $content;
}
add_filter( 'olympus_blocks_do_content', 'olympus_blocks_do_widget_styling' );
