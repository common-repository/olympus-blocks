<?php
/**
 * This file handles the dynamic parts of our blocks.
 *
 * @package Olympus Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render the dynamic aspects of our blocks.
 */
class Olympus_Blocks_Render_Block {
	/**
	 * Instance.
	 *
	 * @access private
	 * @var object Instance
	 */
	private static $instance;

	/**
	 * Initiator.
	 *
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register our dynamic blocks.
	 */
	public function register_blocks() {
		register_block_type(
			'olympusblocks/section',
			array(
				'title' => esc_html__( 'Section', 'olympus-blocks' ),
				'render_callback' => array( $this, 'do_section_block' ),
				'editor_script' => 'olympus-blocks',
				'editor_style' => 'olympus-blocks',
			)
		);

		register_block_type(
			'olympusblocks/layout',
			array(
				'title' => esc_html__( 'Layout', 'olympus-blocks' ),
				'render_callback' => array( $this, 'do_layout_block' ),
			)
		);

		register_block_type(
			'olympusblocks/button-container',
			array(
				'title' => esc_html__( 'Buttons', 'olympus-blocks' ),
				'render_callback' => array( $this, 'do_button_container' ),
			)
		);
	}

	/**
	 * Output the dynamic aspects of our Section block.
	 *
	 * @param array  $attributes The block attributes.
	 * @param string $content The inner blocks.
	 */
	public function do_section_block( $attributes, $content ) {
		if ( ! isset( $attributes['isDynamic'] ) || ! $attributes['isDynamic'] ) {
			return $content;
		}

		$defaults = olympus_blocks_get_block_defaults();

		$settings = wp_parse_args(
			$attributes,
			$defaults['section']
		);

		$output = '';

		if ( $settings['isLayout'] ) {
			$layoutItemClassNames = array(
				'ob-layout-column',
				'ob-layout-column-' . $settings['uniqueId'],
			);

			$output .= sprintf(
				'<div %s>',
				olympus_blocks_attr(
					'layout-item',
					array(
						'class' => implode( ' ', $layoutItemClassNames ),
					),
					$settings
				)
			);
		}

		$classNames = array(
			'ob-section',
			'ob-section-' . $settings['uniqueId'],
		);

		if ( ! empty( $settings['className'] ) ) {
			$classNames[] = $settings['className'];
		}

		if ( ! $settings['isLayout'] && ! empty( $settings['align'] ) ) {
			$classNames[] = 'align' . $settings['align'];
		}

		$tagName = apply_filters( 'olympus_blocks_section_tagname', $settings['tagName'], $attributes );

		$allowedTagNames = apply_filters(
			'olympus_blocks_section_allowed_tagnames',
			array(
				'section',
				'div',
				'header',
				'article',
				'main',
				'aside',
				'footer',
			),
			$attributes
		);

		if ( ! in_array( $tagName, $allowedTagNames ) ) {
			$tagName = 'section';
		}

		if ( $settings['isLayout'] ) {
			$tagName = 'div';
		}

		$output .= sprintf(
			'<%1$s %2$s>',
			$tagName,
			olympus_blocks_attr(
				'section',
				array(
					'id' => isset( $settings['anchor'] ) ? $settings['anchor'] : null,
					'class' => implode( ' ', $classNames ),
				),
				$settings
			)
		);

		$output = apply_filters( 'olympus_blocks_after_section_open', $output, $attributes );
		$output .= '<div class="ob-inside-section">';
		$output = apply_filters( 'olympus_blocks_inside_section', $output, $attributes );
		$output .= $content;
		$output .= '</div>';
		$output = apply_filters( 'olympus_blocks_before_section_close', $output, $attributes );

		$output .= sprintf(
			'</%s>',
			$tagName
		);

		if ( $settings['isLayout'] ) {
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Output the dynamic aspects of our Layout block.
	 *
	 * @param array  $attributes The block attributes.
	 * @param string $content The inner blocks.
	 */
	public function do_layout_block( $attributes, $content ) {
		if ( ! isset( $attributes['isDynamic'] ) || ! $attributes['isDynamic'] ) {
			return $content;
		}

		$defaults = olympus_blocks_get_block_defaults();

		$settings = wp_parse_args(
			$attributes,
			$defaults['layoutSection']
		);

		$classNames = array(
			'ob-layout-wrapper',
			'ob-layout-wrapper-' . $settings['uniqueId'],
		);

		if ( ! empty( $settings['className'] ) ) {
			$classNames[] = $settings['className'];
		}

		$output = sprintf(
			'<div %s>',
			olympus_blocks_attr(
				'layout-wrapper',
				array(
					'id' => isset( $settings['anchor'] ) ? $settings['anchor'] : null,
					'class' => implode( ' ', $classNames ),
				),
				$settings
			)
		);

		$output .= $content;

		$output .= '</div>';

		return $output;
	}

	/**
	 * Output the dynamic aspects of our Button Container block.
	 *
	 * @param array  $attributes The block attributes.
	 * @param string $content The inner blocks.
	 */
	public function do_button_container( $attributes, $content ) {
		if ( ! isset( $attributes['isDynamic'] ) || ! $attributes['isDynamic'] ) {
			return $content;
		}

		$defaults = olympus_blocks_get_block_defaults();

		$settings = wp_parse_args(
			$attributes,
			$defaults['buttonContainer']
		);

		$classNames = array(
			'ob-button-wrapper',
			'ob-button-wrapper-' . $settings['uniqueId'],
		);

		if ( ! empty( $settings['className'] ) ) {
			$classNames[] = $settings['className'];
		}

		$output = sprintf(
			'<div %s>',
			olympus_blocks_attr(
				'button-container',
				array(
					'id' => isset( $settings['anchor'] ) ? $settings['anchor'] : null,
					'class' => implode( ' ', $classNames ),
				),
				$settings
			)
		);

		$output .= $content;

		$output .= '</div>';

		return $output;
	}
}

Olympus_Blocks_Render_Block::get_instance();
