<?php
/**
 * Output our dynamic CSS.
 *
 * @package Olympus Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 *  Build the CSS from our block attributes.
 *
 * @param string $content The content we're looking through.
 *
 * @return string The dynamic CSS.
 */
function olympus_blocks_get_dynamic_css( $content = '' ) {
	if ( ! $content ) {
		return;
	}

	$data = olympus_blocks_get_block_data( $content );

	if ( empty( $data ) ) {
		return;
	}

	$blocks_exist = false;
	$icon_css_added = false;
	$main_css_data = array();
	$desktop_css_data = array();
	$tablet_css_data = array();
	$tablet_only_css_data = array();
	$mobile_css_data = array();

	foreach ( $data as $name => $blockData ) {

		/**
		 * Get our Section block CSS.
		 */
		if ( 'section' === $name ) {
			if ( empty( $blockData ) ) {
				continue;
			}

			$blocks_exist = true;

			$css = new Olympus_Blocks_Dynamic_CSS();
			$desktop_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_only_css = new Olympus_Blocks_Dynamic_CSS();
			$mobile_css = new Olympus_Blocks_Dynamic_CSS();

			$css->set_selector( '.ob-section .wp-block-image img' );
			$css->add_property( 'vertical-align', 'middle' );

			$css->set_selector( '.ob-section .ob-shape' );
			$css->add_property( 'position', 'absolute' );
			$css->add_property( 'overflow', 'hidden' );
			$css->add_property( 'pointer-events', 'none' );
			$css->add_property( 'line-height', '0' );

			$css->set_selector( '.ob-section .ob-shape svg' );
			$css->add_property( 'fill', 'currentColor' );

			foreach ( $blockData as $atts ) {
				if ( ! isset( $atts['uniqueId'] ) ) {
					continue;
				}

				$defaults = olympus_blocks_get_block_defaults();

				$settings = wp_parse_args(
					$atts,
					$defaults['section']
				);

				$id = $atts['uniqueId'];

				if ( ! isset( $settings['bgOptions']['selector'] ) ) {
					$settings['bgOptions']['selector'] = 'element';
				}

				$sectionWidth = $settings['sectionWidth'];

				if ( empty( $sectionWidth ) ) {
					$sectionWidth = $defaults['section']['sectionWidth'];
				}

				$backgroundImageValue = olympus_blocks_get_background_image_css( 'image', $settings );
				$gradientValue = olympus_blocks_get_background_image_css( 'gradient', $settings );
				$hasBgImage = $settings['bgImage'];

				$css->set_selector( '.ob-section-' . $id );
				$css->add_property( 'margin', array( $settings['marginTop'], $settings['marginRight'], $settings['marginBottom'], $settings['marginLeft'] ), $settings['marginUnit'] );

				if ( 'contained' === $settings['outerSection'] && ! $settings['isLayout'] ) {
					if ( ! empty( $sectionWidth ) ) {
						$css->add_property( 'max-width', absint( $sectionWidth ), 'px' );
						$css->add_property( 'margin-left', 'auto' );
						$css->add_property( 'margin-right', 'auto' );
					}
				}

				$css->add_property( 'background-color', olympus_blocks_hex2rgba( $settings['backgroundColor'], $settings['backgroundColorOpacity'] ) );
				$css->add_property( 'color', $settings['textColor'] );

				if ( $hasBgImage && 'element' === $settings['bgOptions']['selector'] && $backgroundImageValue ) {
					$css->add_property( 'background-image', $backgroundImageValue );
					$css->add_property( 'background-repeat', $settings['bgOptions']['repeat'] );
					$css->add_property( 'background-position', $settings['bgOptions']['position'] );
					$css->add_property( 'background-size', $settings['bgOptions']['size'] );
					$css->add_property( 'background-attachment', $settings['bgOptions']['attachment'] );
				} elseif ( $settings['gradient'] && 'element' === $settings['gradientSelector'] ) {
					$css->add_property( 'background-image', $gradientValue );
				}

				if (
					( $hasBgImage && 'pseudo-element' === $settings['bgOptions']['selector'] ) ||
					$settings['zindex'] ||
					( $settings['gradient'] && 'pseudo-element' === $settings['gradientSelector'] )
				) {
					$css->add_property( 'position', 'relative' );
				}

				if (
					( $hasBgImage && 'pseudo-element' === $settings['bgOptions']['selector'] ) ||
					( $settings['gradient'] && 'pseudo-element' === $settings['gradientSelector'] )
				) {
					$css->add_property( 'overflow', 'hidden' );
				}

				if ( $settings['zindex'] ) {
					$css->add_property( 'z-index', $settings['zindex'] );
				}

				$css->add_property( 'border-radius', array( $settings['borderRadiusTopLeft'], $settings['borderRadiusTopRight'], $settings['borderRadiusBottomRight'], $settings['borderRadiusBottomLeft'] ), $settings['borderRadiusUnit'] );
				$css->add_property( 'min-height', $settings['minHeight'], $settings['minHeightUnit'] );
				if ( $settings['borderSizeTop'] || $settings['borderSizeRight'] || $settings['borderSizeBottom'] || $settings['borderSizeLeft'] ) {
					$css->add_property( 'border-width', array( $settings['borderSizeTop'], $settings['borderSizeRight'], $settings['borderSizeBottom'], $settings['borderSizeLeft'] ), 'px' );
					$css->add_property( 'border-style', $settings['borderStyle'] );
					$css->add_property( 'border-color', olympus_blocks_hex2rgba( $settings['borderColor'], $settings['borderColorOpacity'] ) );
				}

				// Set flags so we don't duplicate this CSS in media queries.
				$usingMinHeightFlex = false;
				$usingMinHeightInnerWidth = false;

				if ( $settings['minHeight'] && $settings['verticalAlign'] && ! $settings['isLayout'] ) {
					$css->add_property( 'display', 'flex' );
					$css->add_property( 'flex-direction', 'row' );
					$css->add_property( 'align-items', $settings['verticalAlign'] );

					$usingMinHeightFlex = true;
				}

				$css->add_property( 'text-align', $settings['textAlign'] );

				$innerZindex = $settings['innerZindex'];

				$css->set_selector( '.ob-section-' . $id . ':before' );

				if ( $hasBgImage && 'pseudo-element' === $settings['bgOptions']['selector'] ) {
					$css->add_property( 'content', '""' );
					$css->add_property( 'background-image', $backgroundImageValue );
					$css->add_property( 'background-repeat', $settings['bgOptions']['repeat'] );
					$css->add_property( 'background-position', $settings['bgOptions']['position'] );
					$css->add_property( 'background-size', $settings['bgOptions']['size'] );
					$css->add_property( 'background-attachment', $settings['bgOptions']['attachment'] );
					$css->add_property( 'z-index', '0' );
					$css->add_property( 'position', 'absolute' );
					$css->add_property( 'top', '0' );
					$css->add_property( 'right', '0' );
					$css->add_property( 'bottom', '0' );
					$css->add_property( 'left', '0' );
					$css->add_property( 'transition', 'inherit' );
					$css->add_property( 'border-radius', array( $settings['borderRadiusTopLeft'], $settings['borderRadiusTopRight'], $settings['borderRadiusBottomRight'], $settings['borderRadiusBottomLeft'] ), $settings['borderRadiusUnit'] );

					if ( isset( $settings['bgOptions']['opacity'] ) && 1 !== $settings['bgOptions']['opacity'] ) {
						$css->add_property( 'opacity', $settings['bgOptions']['opacity'] );
					}

					if ( ! $innerZindex ) {
						$innerZindex = 1;
					}
				}

				if ( $settings['gradient'] && 'pseudo-element' === $settings['gradientSelector'] ) {
					$css->set_selector( '.ob-section-' . $id . ':after' );
					$css->add_property( 'content', '""' );
					$css->add_property( 'background-image', $gradientValue );
					$css->add_property( 'z-index', '0' );
					$css->add_property( 'position', 'absolute' );
					$css->add_property( 'top', '0' );
					$css->add_property( 'right', '0' );
					$css->add_property( 'bottom', '0' );
					$css->add_property( 'left', '0' );

					if ( ! $innerZindex ) {
						$innerZindex = 1;
					}
				}

				$css->set_selector( '.ob-section-' . $id . ' > .ob-inside-section' );
				if ( $settings['paddingTop'] || $settings['paddingRight'] || $settings['paddingBottom'] || $settings['paddingLeft'] ) {
					$css->add_property( 'padding', array( $settings['paddingTop'], $settings['paddingRight'], $settings['paddingBottom'], $settings['paddingLeft'] ), $settings['paddingUnit'] );
				} else {
					$css->add_property( 'padding', '10px' );
				}

				if ( 'contained' === $settings['innerSection'] && ! $settings['isLayout'] ) {
					if ( ! empty( $sectionWidth ) ) {
						$css->add_property( 'max-width', absint( $sectionWidth ), 'px' );
						$css->add_property( 'margin-left', 'auto' );
						$css->add_property( 'margin-right', 'auto' );
					}
				}

				if ( $usingMinHeightFlex ) {
					$css->add_property( 'width', '100%' );

					$usingMinHeightInnerWidth = true;
				}

				if ( $innerZindex || 0 === $innerZindex ) {
					$css->add_property( 'z-index', $innerZindex );
					$css->add_property( 'position', 'relative' );
				}

				$css->set_selector( '.ob-section-' . $id . ' a, .ob-section-' . $id . ' a:visited' );
				$css->add_property( 'color', $settings['linkColor'] );

				$css->set_selector( '.ob-section-' . $id . ' a:hover' );
				$css->add_property( 'color', $settings['linkColorHover'] );

				if ( $settings['isLayout'] ) {
					$css->set_selector( '.ob-layout-wrapper > .ob-layout-column-' . $id );
					$css->add_property( 'width', $settings['width'], '%' );

					$css->add_property( 'flex-grow', $settings['flexGrow'] );
					$css->add_property( 'flex-shrink', $settings['flexShrink'] );

					if ( is_numeric( $settings['flexBasis'] ) ) {
						$css->add_property( 'flex-basis', $settings['flexBasis'], $settings['flexBasisUnit'] );
					} else {
						$css->add_property( 'flex-basis', $settings['flexBasis'] );
					}
				}

				if ( $settings['removeVerticalGap'] ) {
					$desktop_css->set_selector( '.ob-layout-wrapper > div.ob-layout-column-' . $id );
					$desktop_css->add_property( 'padding-bottom', '0' );
				}

				$css->set_selector( '.ob-layout-wrapper > .ob-layout-column-' . $id . ' > .ob-section' );
				$css->add_property( 'justify-content', $settings['verticalAlign'] );

				if ( ! empty( $settings['shapeDividers'] ) ) {
					$css->set_selector( '.ob-section-' . $id );
					$css->add_property( 'position', 'relative' );

					$default_styles = olympus_blocks_get_default_styles();

					foreach ( (array) $settings['shapeDividers'] as $index => $options ) {
						$shapeNumber = $index + 1;

						$shapeOptions = wp_parse_args(
							$options,
							$default_styles['section']['shapeDividers']
						);

						$shapeTransforms = array();

						if ( 'bottom' === $shapeOptions['location'] ) {
							$shapeTransforms[] = 'scaleY(-1)';
						}

						if ( $shapeOptions['flipHorizontally'] ) {
							$shapeTransforms[] = 'scaleX(-1)';
						}

						$css->set_selector( '.ob-section-' . $id . ' > .ob-shapes .ob-shape-' . $shapeNumber );
						$css->add_property( 'color', olympus_blocks_hex2rgba( $shapeOptions['color'], $shapeOptions['colorOpacity'] ) );
						$css->add_property( 'z-index', $shapeOptions['zindex'] );

						if ( 'top' === $shapeOptions['location'] || 'bottom' === $shapeOptions['location'] ) {
							$css->add_property( 'left', '0' );
							$css->add_property( 'right', '0' );
						}

						if ( 'bottom' === $shapeOptions['location'] ) {
							$css->add_property( 'bottom', '-1px' );
						}

						if ( 'top' === $shapeOptions['location'] ) {
							$css->add_property( 'top', '-1px' );
						}

						if ( ! empty( $shapeTransforms ) ) {
							$css->add_property( 'transform', implode( ' ', $shapeTransforms ) );
						}

						$css->set_selector( '.ob-section-' . $id . ' > .ob-shapes .ob-shape-' . $shapeNumber . ' svg' );
						$css->add_property( 'height', $shapeOptions['height'], 'px' );
						$css->add_property( 'width', '100%' );

						if ( 'top' === $shapeOptions['location'] || 'bottom' === $shapeOptions['location'] ) {
							$css->add_property( 'position', 'relative' );
							$css->add_property( 'left', '50%' );
							$css->add_property( 'transform', 'translateX(-50%)' );
							$css->add_property( 'min-width', '100%' );
						}
					}
				}

				$tablet_css->set_selector( '.ob-section-' . $id );
				$tablet_css->add_property( 'margin', array( $settings['marginTopTablet'], $settings['marginRightTablet'], $settings['marginBottomTablet'], $settings['marginLeftTablet'] ), $settings['marginUnit'] );
				$tablet_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftTablet'], $settings['borderRadiusTopRightTablet'], $settings['borderRadiusBottomRightTablet'], $settings['borderRadiusBottomLeftTablet'] ), $settings['borderRadiusUnit'] );
				$tablet_css->add_property( 'min-height', $settings['minHeightTablet'], $settings['minHeightUnitTablet'] );
				if ( $settings['borderSizeTopTablet'] || $settings['borderSizeRightTablet'] || $settings['borderSizeBottomTablet'] || $settings['borderSizeLeftTablet'] ) {
					$tablet_css->add_property( 'border-width', array( $settings['borderSizeTopTablet'], $settings['borderSizeRightTablet'], $settings['borderSizeBottomTablet'], $settings['borderSizeLeftTablet'] ), 'px' );
				}

				if ( ! $settings['isLayout'] ) {
					if ( ! $usingMinHeightFlex && $settings['minHeightTablet'] && 'inherit' !== $settings['verticalAlignTablet'] ) {
						$tablet_css->add_property( 'display', 'flex' );
						$tablet_css->add_property( 'flex-direction', 'row' );

						$usingMinHeightFlex = true;
					}

					if ( $usingMinHeightFlex && 'inherit' !== $settings['verticalAlignTablet'] ) {
						$tablet_css->add_property( 'align-items', $settings['verticalAlignTablet'] );
					}
				}

				$tablet_css->add_property( 'text-align', $settings['textAlignTablet'] );

				$tablet_css->set_selector( '.ob-section-' . $id . ' > .ob-inside-section' );
				$tablet_css->add_property( 'padding', array( $settings['paddingTopTablet'], $settings['paddingRightTablet'], $settings['paddingBottomTablet'], $settings['paddingLeftTablet'] ), $settings['paddingUnit'] );

				$usingMinHeightInnerWidthBoxSizing = false;

				if ( ! $settings['isLayout'] ) {
					// Needs 100% width if it's a flex item.
					if ( ! $usingMinHeightInnerWidth && $settings['minHeightTablet'] && 'inherit' !== $settings['verticalAlignTablet'] ) {
						$tablet_css->add_property( 'width', '100%' );

						$usingMinHeightInnerWidth = true;
					} elseif ( $usingMinHeightInnerWidth ) {
						if ( 'contained' === $settings['innerSection'] && ! $settings['isLayout'] ) {
							$tablet_css->add_property( 'box-sizing', 'border-box' );

							$usingMinHeightInnerWidthBoxSizing = true;
						}
					}
				}

				$tablet_css->set_selector( '.ob-layout-wrapper > .ob-layout-column-' . $id );

				if ( ! $settings['autoWidthTablet'] ) {
					$tablet_css->add_property( 'width', $settings['widthTablet'], '%' );
				} else {
					$tablet_css->add_property( 'width', 'auto' );
				}

				$tablet_css->add_property( 'flex-grow', $settings['flexGrowTablet'] );
				$tablet_css->add_property( 'flex-shrink', $settings['flexShrinkTablet'] );

				if ( is_numeric( $settings['flexBasisTablet'] ) ) {
					$tablet_css->add_property( 'flex-basis', $settings['flexBasisTablet'], $settings['flexBasisUnit'] );
				} else {
					$tablet_css->add_property( 'flex-basis', $settings['flexBasisTablet'] );
				}

				if ( $settings['isLayout'] ) {
					$tablet_css->add_property( 'order', $settings['orderTablet'] );
				}

				if ( $settings['removeVerticalGapTablet'] ) {
					$tablet_only_css->set_selector( '.ob-layout-wrapper > div.ob-layout-column-' . $id );
					$tablet_only_css->add_property( 'padding-bottom', '0' );
				}

				$tablet_css->set_selector( '.ob-layout-wrapper > .ob-layout-column-' . $id . ' > .ob-section' );

				if ( 'inherit' !== $settings['verticalAlignTablet'] ) {
					$tablet_css->add_property( 'justify-content', $settings['verticalAlignTablet'] );
				}

				if ( $hasBgImage && 'pseudo-element' === $settings['bgOptions']['selector'] ) {
					$tablet_css->set_selector( '.ob-section-' . $id . ':before' );
					$tablet_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftTablet'], $settings['borderRadiusTopRightTablet'], $settings['borderRadiusBottomRightTablet'], $settings['borderRadiusBottomLeftTablet'] ), $settings['borderRadiusUnit'] );
				}

				if ( ! empty( $settings['shapeDividers'] ) ) {
					$default_styles = olympus_blocks_get_default_styles();

					foreach ( (array) $settings['shapeDividers'] as $index => $options ) {
						$shapeNumber = $index + 1;

						$shapeOptions = wp_parse_args(
							$options,
							$default_styles['section']['shapeDividers']
						);

						$tablet_css->set_selector( '.ob-section-' . $id . ' > .ob-shapes .ob-shape-' . $shapeNumber . ' svg' );
						$tablet_css->add_property( 'height', $shapeOptions['heightTablet'], 'px' );
						$tablet_css->add_property( 'width', '100%' );
					}
				}

				$mobile_css->set_selector( '.ob-section-' . $id );
				$mobile_css->add_property( 'margin', array( $settings['marginTopMobile'], $settings['marginRightMobile'], $settings['marginBottomMobile'], $settings['marginLeftMobile'] ), $settings['marginUnit'] );
				$mobile_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftMobile'], $settings['borderRadiusTopRightMobile'], $settings['borderRadiusBottomRightMobile'], $settings['borderRadiusBottomLeftMobile'] ), $settings['borderRadiusUnit'] );
				$mobile_css->add_property( 'min-height', $settings['minHeightMobile'], $settings['minHeightUnitMobile'] );
				if ( $settings['borderSizeTopMobile'] || $settings['borderSizeRightMobile'] || $settings['borderSizeBottomMobile'] || $settings['borderSizeLeftMobile'] ) {
					$mobile_css->add_property( 'border-width', array( $settings['borderSizeTopMobile'], $settings['borderSizeRightMobile'], $settings['borderSizeBottomMobile'], $settings['borderSizeLeftMobile'] ), 'px' );
				}

				if ( ! $settings['isLayout'] ) {
					if ( ! $usingMinHeightFlex && $settings['minHeightMobile'] && 'inherit' !== $settings['verticalAlignMobile'] ) {
						$mobile_css->add_property( 'display', 'flex' );
						$mobile_css->add_property( 'flex-direction', 'row' );

						$usingMinHeightFlex = true;
					}

					if ( $usingMinHeightFlex && 'inherit' !== $settings['verticalAlignMobile'] ) {
						$mobile_css->add_property( 'align-items', $settings['verticalAlignMobile'] );
					}
				}

				$mobile_css->add_property( 'text-align', $settings['textAlignMobile'] );

				$mobile_css->set_selector( '.ob-section-' . $id . ' > .ob-inside-section' );
				$mobile_css->add_property( 'padding', array( $settings['paddingTopMobile'], $settings['paddingRightMobile'], $settings['paddingBottomMobile'], $settings['paddingLeftMobile'] ), $settings['paddingUnit'] );

				if ( ! $settings['isLayout'] ) {
					// Needs 100% width if it's a flex item.
					if ( ! $usingMinHeightInnerWidth && $settings['minHeightMobile'] && 'inherit' !== $settings['verticalAlignMobile'] ) {
						$mobile_css->add_property( 'width', '100%' );
					} elseif ( $usingMinHeightInnerWidth && ! $usingMinHeightInnerWidthBoxSizing ) {
						if ( 'contained' === $settings['innerSection'] && ! $settings['isLayout'] ) {
							$mobile_css->add_property( 'box-sizing', 'border-box' );
						}
					}
				}

				$mobile_css->set_selector( '.ob-layout-wrapper > .ob-layout-column-' . $id );

				if ( ! $settings['autoWidthMobile'] ) {
					$mobile_css->add_property( 'width', $settings['widthMobile'], '%' );
				}

				if ( $settings['autoWidthMobile'] ) {
					$mobile_css->add_property( 'width', 'auto' );
				}

				$mobile_css->add_property( 'flex-grow', $settings['flexGrowMobile'] );
				$mobile_css->add_property( 'flex-shrink', $settings['flexShrinkMobile'] );

				if ( is_numeric( $settings['flexBasisMobile'] ) ) {
					$mobile_css->add_property( 'flex-basis', $settings['flexBasisMobile'], $settings['flexBasisUnit'] );
				} else {
					$mobile_css->add_property( 'flex-basis', $settings['flexBasisMobile'] );
				}

				if ( $settings['isLayout'] ) {
					$mobile_css->add_property( 'order', $settings['orderMobile'] );
				}

				if ( $settings['removeVerticalGapMobile'] ) {
					$mobile_css->set_selector( '.ob-layout-wrapper > div.ob-layout-column-' . $id );
					$mobile_css->add_property( 'padding-bottom', '0' );
				}

				$mobile_css->set_selector( '.ob-layout-wrapper > .ob-layout-column-' . $id . ' > .ob-section' );

				if ( 'inherit' !== $settings['verticalAlignMobile'] ) {
					$mobile_css->add_property( 'justify-content', $settings['verticalAlignMobile'] );
				}

				if ( $hasBgImage && 'pseudo-element' === $settings['bgOptions']['selector'] ) {
					$mobile_css->set_selector( '.ob-section-' . $id . ':before' );
					$mobile_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftMobile'], $settings['borderRadiusTopRightMobile'], $settings['borderRadiusBottomRightMobile'], $settings['borderRadiusBottomLeftMobile'] ), $settings['borderRadiusUnit'] );
				}

				if ( ! empty( $settings['shapeDividers'] ) ) {
					$default_styles = olympus_blocks_get_default_styles();

					foreach ( (array) $settings['shapeDividers'] as $index => $options ) {
						$shapeNumber = $index + 1;

						$shapeOptions = wp_parse_args(
							$options,
							$default_styles['section']['shapeDividers']
						);

						$mobile_css->set_selector( '.ob-section-' . $id . ' > .ob-shapes .ob-shape-' . $shapeNumber . ' svg' );
						$mobile_css->add_property( 'height', $shapeOptions['heightMobile'], 'px' );
						$mobile_css->add_property( 'width', '100%' );
					}
				}

				if ( $hasBgImage && 'fixed' === $settings['bgOptions']['attachment'] ) {
					if ( 'element' === $settings['bgOptions']['selector'] ) {
						$mobile_css->set_selector( '.ob-section-' . $id );
					}

					if ( 'pseudo-element' === $settings['bgOptions']['selector'] ) {
						$mobile_css->set_selector( '.ob-section-' . $id . ':before' );
					}

					$mobile_css->add_property( 'background-attachment', 'initial' );
				}

				/**
				 * Do olympus_blocks_block_css_data hook
				 *
				 * @param string $name The name of our block.
				 * @param array  $settings The settings for the current block.
				 * @param object $css Our desktop/main CSS data.
				 * @param object $desktop_css Our desktop only CSS data.
				 * @param object $tablet_css Our tablet CSS data.
				 * @param object $tablet_only_css Our tablet only CSS data.
				 * @param object $mobile_css Our mobile CSS data.
				 */
				do_action(
					'olympus_blocks_block_css_data',
					$name,
					$settings,
					$css,
					$desktop_css,
					$tablet_css,
					$tablet_only_css,
					$mobile_css
				);
			}

			if ( $css->css_output() ) {
				$main_css_data[] = $css->css_output();
			}

			if ( $desktop_css->css_output() ) {
				$desktop_css_data[] = $desktop_css->css_output();
			}

			if ( $tablet_css->css_output() ) {
				$tablet_css_data[] = $tablet_css->css_output();
			}

			if ( $tablet_only_css->css_output() ) {
				$tablet_only_css_data[] = $tablet_only_css->css_output();
			}

			if ( $mobile_css->css_output() ) {
				$mobile_css_data[] = $mobile_css->css_output();
			}
		}

		/**
		 * Get our Layout block CSS.
		 */
		if ( 'layout' === $name ) {
			if ( empty( $blockData ) ) {
				continue;
			}

			$blocks_exist = true;

			$css = new Olympus_Blocks_Dynamic_CSS();
			$desktop_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_only_css = new Olympus_Blocks_Dynamic_CSS();
			$mobile_css = new Olympus_Blocks_Dynamic_CSS();

			$css->set_selector( '.ob-layout-wrapper' );
			$css->add_property( 'display', 'flex' );
			$css->add_property( 'flex-wrap', 'wrap' );

			$css->set_selector( '.ob-layout-wrapper > .ob-layout-column > .ob-section' );
			$css->add_property( 'display', 'flex' );
			$css->add_property( 'flex-direction', 'column' );
			$css->add_property( 'height', '100%' );

			$css->set_selector( '.ob-layout-column' );
			$css->add_property( 'box-sizing', 'border-box' );

			$css->set_selector( '.ob-layout-wrapper .wp-block-image' );
			$css->add_property( 'margin-bottom', '0' );

			foreach ( $blockData as $atts ) {
				if ( ! isset( $atts['uniqueId'] ) ) {
					continue;
				}

				$defaults = olympus_blocks_get_block_defaults();

				$settings = wp_parse_args(
					$atts,
					$defaults['layoutSection']
				);

				$id = $atts['uniqueId'];

				$gap_direction = 'left';

				if ( is_rtl() ) {
					$gap_direction = 'right';
				}

				if ( (string) $settings['horizontalGap'] === (string) $defaults['layoutSection']['horizontalGap'] ) {
					$settings['horizontalGap'] = '';
				}

				$css->set_selector( '.ob-layout-wrapper-' . $id );
				$css->add_property( 'align-items', $settings['verticalAlign'] );
				$css->add_property( 'justify-content', $settings['horizontalAlign'] );

				if ( $settings['horizontalGap'] ) {
					$css->add_property( 'margin-' . $gap_direction, '-' . $settings['horizontalGap'] . 'px' );
				}

				$css->set_selector( '.ob-layout-wrapper-' . $id . ' > .ob-layout-column' );
				$css->add_property( 'padding-' . $gap_direction, $settings['horizontalGap'], 'px' );
				$css->add_property( 'padding-bottom', $settings['verticalGap'], 'px' );

				$tablet_css->set_selector( '.ob-layout-wrapper-' . $id );

				if ( 'inherit' !== $settings['verticalAlignTablet'] ) {
					$tablet_css->add_property( 'align-items', $settings['verticalAlignTablet'] );
				}

				if ( 'inherit' !== $settings['horizontalAlignTablet'] ) {
					$tablet_css->add_property( 'justify-content', $settings['horizontalAlignTablet'] );
				}

				if ( $settings['horizontalGapTablet'] ) {
					$tablet_css->add_property( 'margin-' . $gap_direction, '-' . $settings['horizontalGapTablet'] . 'px' );
				} elseif ( 0 === $settings['horizontalGapTablet'] ) {
					$tablet_css->add_property( 'margin-' . $gap_direction, $settings['horizontalGapTablet'] );
				}

				$tablet_css->set_selector( '.ob-layout-wrapper-' . $id . ' > .ob-layout-column' );
				$tablet_css->add_property( 'padding-' . $gap_direction, $settings['horizontalGapTablet'], 'px' );
				$tablet_css->add_property( 'padding-bottom', $settings['verticalGapTablet'], 'px' );

				$mobile_css->set_selector( '.ob-layout-wrapper-' . $id );

				if ( 'inherit' !== $settings['verticalAlignMobile'] ) {
					$mobile_css->add_property( 'align-items', $settings['verticalAlignMobile'] );
				}

				if ( 'inherit' !== $settings['horizontalAlignMobile'] ) {
					$mobile_css->add_property( 'justify-content', $settings['horizontalAlignMobile'] );
				}

				if ( $settings['horizontalGapMobile'] ) {
					$mobile_css->add_property( 'margin-' . $gap_direction, '-' . $settings['horizontalGapMobile'] . 'px' );
				} elseif ( 0 === $settings['horizontalGapMobile'] ) {
					$mobile_css->add_property( 'margin-' . $gap_direction, $settings['horizontalGapMobile'] );
				}

				$mobile_css->set_selector( '.ob-layout-wrapper-' . $id . ' > .ob-layout-column' );
				$mobile_css->add_property( 'padding-' . $gap_direction, $settings['horizontalGapMobile'], 'px' );
				$mobile_css->add_property( 'padding-bottom', $settings['verticalGapMobile'], 'px' );

				/**
				 * Do olympus_blocks_block_css_data hook
				 *
				 * @param string $name The name of our block.
				 * @param array  $settings The settings for the current block.
				 * @param object $css Our desktop/main CSS data.
				 * @param object $desktop_css Our desktop only CSS data.
				 * @param object $tablet_css Our tablet CSS data.
				 * @param object $tablet_only_css Our tablet only CSS data.
				 * @param object $mobile_css Our mobile CSS data.
				 */
				do_action(
					'olympus_blocks_block_css_data',
					$name,
					$settings,
					$css,
					$desktop_css,
					$tablet_css,
					$tablet_only_css,
					$mobile_css
				);
			}

			if ( $css->css_output() ) {
				$main_css_data[] = $css->css_output();
			}

			if ( $desktop_css->css_output() ) {
				$desktop_css_data[] = $desktop_css->css_output();
			}

			if ( $tablet_css->css_output() ) {
				$tablet_css_data[] = $tablet_css->css_output();
			}

			if ( $tablet_only_css->css_output() ) {
				$tablet_only_css_data[] = $tablet_only_css->css_output();
			}

			if ( $mobile_css->css_output() ) {
				$mobile_css_data[] = $mobile_css->css_output();
			}
		}

		/**
		 * Get our Button Container block CSS.
		 */
		if ( 'button-container' === $name ) {
			if ( empty( $blockData ) ) {
				continue;
			}

			$blocks_exist = true;

			$css = new Olympus_Blocks_Dynamic_CSS();
			$desktop_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_only_css = new Olympus_Blocks_Dynamic_CSS();
			$mobile_css = new Olympus_Blocks_Dynamic_CSS();

			$css->set_selector( '.ob-button-wrapper' );
			$css->add_property( 'display', 'flex' );
			$css->add_property( 'flex-wrap', 'wrap' );
			$css->add_property( 'align-items', 'flex-start' );
			$css->add_property( 'justify-content', 'flex-start' );
			$css->add_property( 'clear', 'both' );

			foreach ( $blockData as $atts ) {
				if ( ! isset( $atts['uniqueId'] ) ) {
					continue;
				}

				$defaults = olympus_blocks_get_block_defaults();

				$settings = wp_parse_args(
					$atts,
					$defaults['buttonContainer']
				);

				$id = $atts['uniqueId'];

				$css->set_selector( '.ob-button-wrapper-' . $id );
				$css->add_property( 'margin', array( $settings['marginTop'], $settings['marginRight'], $settings['marginBottom'], $settings['marginLeft'] ), $settings['marginUnit'] );
				$css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['align'] ) );

				$stack_desktop = $desktop_css;
				$stack_tablet_only = $tablet_only_css;

				if ( $settings['stack'] ) {
					$stack_desktop->set_selector( '.ob-button-wrapper-' . $id );
					$stack_desktop->add_property( 'flex-direction', 'column' );
					$stack_desktop->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['align'] ) );
				}

				if ( $settings['fillHorizontalSpace'] ) {
					$stack_desktop->set_selector( '.ob-button-wrapper-' . $id . ' > .ob-button' );
					$stack_desktop->add_property( 'flex', '1' );
				}

				if ( $settings['stack'] && $settings['fillHorizontalSpace'] ) {
					$stack_desktop->add_property( 'width', '100%' );
					$stack_desktop->add_property( 'box-sizing', 'border-box' );
				}

				$tablet_css->set_selector( '.ob-button-wrapper-' . $id );
				$tablet_css->add_property( 'margin', array( $settings['marginTopTablet'], $settings['marginRightTablet'], $settings['marginBottomTablet'], $settings['marginLeftTablet'] ), $settings['marginUnit'] );
				$tablet_css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['alignTablet'] ) );

				if ( $settings['stackTablet'] ) {
					$stack_tablet_only->set_selector( '.ob-button-wrapper-' . $id );
					$stack_tablet_only->add_property( 'flex-direction', 'column' );
					$stack_tablet_only->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['alignTablet'] ) );
				}

				if ( $settings['fillHorizontalSpaceTablet'] ) {
					$stack_tablet_only->set_selector( '.ob-button-wrapper-' . $id . ' > .ob-button' );
					$stack_tablet_only->add_property( 'flex', '1' );
				}

				if ( $settings['stackTablet'] && $settings['fillHorizontalSpaceTablet'] ) {
					$stack_tablet_only->add_property( 'width', '100%' );
					$stack_tablet_only->add_property( 'box-sizing', 'border-box' );
				}

				$mobile_css->set_selector( '.ob-button-wrapper-' . $id );
				$mobile_css->add_property( 'margin', array( $settings['marginTopMobile'], $settings['marginRightMobile'], $settings['marginBottomMobile'], $settings['marginLeftMobile'] ), $settings['marginUnit'] );
				$mobile_css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['alignMobile'] ) );

				if ( $settings['stackMobile'] ) {
					$mobile_css->add_property( 'flex-direction', 'column' );
					$mobile_css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['alignMobile'] ) );
				}

				if ( $settings['fillHorizontalSpaceMobile'] ) {
					$mobile_css->set_selector( '.ob-button-wrapper-' . $id . ' > .ob-button' );
					$mobile_css->add_property( 'flex', '1' );
				}

				if ( $settings['stackMobile'] && $settings['fillHorizontalSpaceMobile'] ) {
					$mobile_css->add_property( 'width', '100%' );
					$mobile_css->add_property( 'box-sizing', 'border-box' );
				}

				/**
				 * Do olympus_blocks_block_css_data hook
				 *
				 * @param string $name The name of our block.
				 * @param array  $settings The settings for the current block.
				 * @param object $css Our desktop/main CSS data.
				 * @param object $desktop_css Our desktop only CSS data.
				 * @param object $tablet_css Our tablet CSS data.
				 * @param object $tablet_only_css Our tablet only CSS data.
				 * @param object $mobile_css Our mobile CSS data.
				 */
				do_action(
					'olympus_blocks_block_css_data',
					$name,
					$settings,
					$css,
					$desktop_css,
					$tablet_css,
					$tablet_only_css,
					$mobile_css
				);
			}

			if ( $css->css_output() ) {
				$main_css_data[] = $css->css_output();
			}

			if ( $desktop_css->css_output() ) {
				$desktop_css_data[] = $desktop_css->css_output();
			}

			if ( $tablet_css->css_output() ) {
				$tablet_css_data[] = $tablet_css->css_output();
			}

			if ( $tablet_only_css->css_output() ) {
				$tablet_only_css_data[] = $tablet_only_css->css_output();
			}

			if ( $mobile_css->css_output() ) {
				$mobile_css_data[] = $mobile_css->css_output();
			}
		}

		/**
		 * Get our Button block CSS.
		 */
		if ( 'button' === $name ) {
			if ( empty( $blockData ) ) {
				continue;
			}

			$blocks_exist = true;

			$css = new Olympus_Blocks_Dynamic_CSS();
			$desktop_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_only_css = new Olympus_Blocks_Dynamic_CSS();
			$mobile_css = new Olympus_Blocks_Dynamic_CSS();

			if ( ! $icon_css_added ) {
				$css->set_selector( '.ob-icon' );
				$css->add_property( 'display', 'inline-flex' );
				$css->add_property( 'line-height', '0' );

				$css->set_selector( '.ob-icon svg' );
				$css->add_property( 'height', '1em' );
				$css->add_property( 'width', '1em' );
				$css->add_property( 'fill', 'currentColor' );

				$icon_css_added = true;
			}

			$css->set_selector( '.ob-button-wrapper .ob-button' );
			$css->add_property( 'display', 'inline-flex' );
			$css->add_property( 'align-items', 'center' );
			$css->add_property( 'justify-content', 'center' );
			$css->add_property( 'text-align', 'center' );
			$css->add_property( 'text-decoration', 'none' );
			$css->add_property( 'transition', '.2s ease-in-out' );

			$css->set_selector( '.ob-button-wrapper .ob-button .ob-icon' );
			$css->add_property( 'align-items', 'center' );

			foreach ( $blockData as $atts ) {
				if ( ! isset( $atts['uniqueId'] ) ) {
					continue;
				}

				$defaults = olympus_blocks_get_block_defaults();

				$settings = wp_parse_args(
					$atts,
					$defaults['button']
				);

				$id = $atts['uniqueId'];

				$selector = 'a.ob-button-' . $id;

				if ( isset( $atts['hasUrl'] ) && ! $atts['hasUrl'] ) {
					$selector = '.ob-button-' . $id;
				}

				// Back-compatibility for when icon held a value.
				if ( $settings['icon'] ) {
					$settings['hasIcon'] = true;
				}

				$fontFamily = $settings['fontFamily'];

				$gradientColorStopOneValue = '';
				$gradientColorStopTwoValue = '';

				if ( $settings['gradient'] ) {
					if ( $settings['gradientColorOne'] && '' !== $settings['gradientColorStopOne'] ) {
						$gradientColorStopOneValue = ' ' . $settings['gradientColorStopOne'] . '%';
					}

					if ( $settings['gradientColorTwo'] && '' !== $settings['gradientColorStopTwo'] ) {
						$gradientColorStopTwoValue = ' ' . $settings['gradientColorStopTwo'] . '%';
					}
				}

				$css->set_selector( '.ob-button-wrapper ' . $selector . ',.ob-button-wrapper ' . $selector . ':visited' );
				$css->add_property( 'background-color', olympus_blocks_hex2rgba( $settings['backgroundColor'], $settings['backgroundColorOpacity'] ) );
				$css->add_property( 'color', $settings['textColor'] );

				if ( $settings['gradient'] ) {
					$css->add_property( 'background-image', 'linear-gradient(' . $settings['gradientDirection'] . 'deg, ' . olympus_blocks_hex2rgba( $settings['gradientColorOne'], $settings['gradientColorOneOpacity'] ) . $gradientColorStopOneValue . ', ' . olympus_blocks_hex2rgba( $settings['gradientColorTwo'], $settings['gradientColorTwoOpacity'] ) . $gradientColorStopTwoValue . ')' );
				}

				if ( $fontFamily && 'Default' !== $fontFamily ) {
					$css->add_property( 'font-family', $fontFamily );
				}
				$css->add_property( 'font-size', $settings['fontSize'], $settings['fontSizeUnit'] );
				$css->add_property( 'font-weight', $settings['fontWeight'] );
				$css->add_property( 'text-transform', $settings['textTransform'] );
				$css->add_property( 'letter-spacing', $settings['letterSpacing'], 'em' );
				$css->add_property( 'line-height', $settings['lineHeight'], $settings['lineHeightUnit'] );
				$css->add_property( 'padding', array( $settings['paddingTop'], $settings['paddingRight'], $settings['paddingBottom'], $settings['paddingLeft'] ), $settings['paddingUnit'] );
				$css->add_property( 'border-radius', array( $settings['borderRadiusTopLeft'], $settings['borderRadiusTopRight'], $settings['borderRadiusBottomRight'], $settings['borderRadiusBottomLeft'] ), $settings['borderRadiusUnit'] );
				$css->add_property( 'margin', array( $settings['marginTop'], $settings['marginRight'], $settings['marginBottom'], $settings['marginLeft'] ), $settings['marginUnit'] );
				$css->add_property( 'text-transform', $settings['textTransform'] );
				if ( $settings['borderSizeTop'] || $settings['borderSizeRight'] || $settings['borderSizeBottom'] || $settings['borderSizeLeft'] ) {
					$css->add_property( 'border-width', array( $settings['borderSizeTop'], $settings['borderSizeRight'], $settings['borderSizeBottom'], $settings['borderSizeLeft'] ), 'px' );
					$css->add_property( 'border-style', $settings['borderStyle'] );
					$css->add_property( 'border-color', olympus_blocks_hex2rgba( $settings['borderColor'], $settings['borderColorOpacity'] ) );
				}

				if ( $settings['hasIcon'] ) {
					$css->add_property( 'display', 'inline-flex' );
					$css->add_property( 'align-items', 'center' );
				}

				$css->set_selector( '.ob-button-wrapper ' . $selector . ':hover,.ob-button-wrapper ' . $selector . ':active,.ob-button-wrapper ' . $selector . ':focus' );
				$css->add_property( 'background-color', olympus_blocks_hex2rgba( $settings['backgroundColorHover'], $settings['backgroundColorHoverOpacity'] ) );
				$css->add_property( 'color', $settings['textColorHover'] );
				$css->add_property( 'border-color', olympus_blocks_hex2rgba( $settings['borderColorHover'], $settings['borderColorHoverOpacity'] ) );

				if ( $settings['hasIcon'] ) {
					$css->set_selector( $selector . ' .ob-icon' );
					$css->add_property( 'font-size', $settings['iconSize'], $settings['iconSizeUnit'] );

					if ( ! $settings['removeText'] ) {
						$css->add_property( 'padding', array( $settings['iconPaddingTop'], $settings['iconPaddingRight'], $settings['iconPaddingBottom'], $settings['iconPaddingLeft'] ), $settings['iconPaddingUnit'] );
					}
				}

				$tablet_css->set_selector( '.ob-button-wrapper ' . $selector );
				$tablet_css->add_property( 'font-size', $settings['fontSizeTablet'], $settings['fontSizeUnit'] );
				$tablet_css->add_property( 'line-height', $settings['lineHeightTablet'], $settings['lineHeightUnit'] );
				$tablet_css->add_property( 'letter-spacing', $settings['letterSpacingTablet'], 'em' );
				$tablet_css->add_property( 'padding', array( $settings['paddingTopTablet'], $settings['paddingRightTablet'], $settings['paddingBottomTablet'], $settings['paddingLeftTablet'] ), $settings['paddingUnit'] );
				$tablet_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftTablet'], $settings['borderRadiusTopRightTablet'], $settings['borderRadiusBottomRightTablet'], $settings['borderRadiusBottomLeftTablet'] ), $settings['borderRadiusUnit'] );
				$tablet_css->add_property( 'margin', array( $settings['marginTopTablet'], $settings['marginRightTablet'], $settings['marginBottomTablet'], $settings['marginLeftTablet'] ), $settings['marginUnit'] );
				if ( $settings['borderSizeTopTablet'] || $settings['borderSizeRightTablet'] || $settings['borderSizeBottomTablet'] || $settings['borderSizeLeftTablet'] ) {
					$tablet_css->add_property( 'border-width', array( $settings['borderSizeTopTablet'], $settings['borderSizeRightTablet'], $settings['borderSizeBottomTablet'], $settings['borderSizeLeftTablet'] ), 'px' );
				}

				if ( $settings['hasIcon'] ) {
					$tablet_css->set_selector( $selector . ' .ob-icon' );
					$tablet_css->add_property( 'font-size', $settings['iconSizeTablet'], $settings['iconSizeUnit'] );

					if ( ! $settings['removeText'] ) {
						$tablet_css->add_property( 'padding', array( $settings['iconPaddingTopTablet'], $settings['iconPaddingRightTablet'], $settings['iconPaddingBottomTablet'], $settings['iconPaddingLeftTablet'] ), $settings['iconPaddingUnit'] );
					}
				}

				$mobile_css->set_selector( '.ob-button-wrapper ' . $selector );
				$mobile_css->add_property( 'font-size', $settings['fontSizeMobile'], $settings['fontSizeUnit'] );
				$mobile_css->add_property( 'line-height', $settings['lineHeightMobile'], $settings['lineHeightUnit'] );
				$mobile_css->add_property( 'letter-spacing', $settings['letterSpacingMobile'], 'em' );
				$mobile_css->add_property( 'padding', array( $settings['paddingTopMobile'], $settings['paddingRightMobile'], $settings['paddingBottomMobile'], $settings['paddingLeftMobile'] ), $settings['paddingUnit'] );
				$mobile_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftMobile'], $settings['borderRadiusTopRightMobile'], $settings['borderRadiusBottomRightMobile'], $settings['borderRadiusBottomLeftMobile'] ), $settings['borderRadiusUnit'] );
				$mobile_css->add_property( 'margin', array( $settings['marginTopMobile'], $settings['marginRightMobile'], $settings['marginBottomMobile'], $settings['marginLeftMobile'] ), $settings['marginUnit'] );
				if ( $settings['borderSizeTopMobile'] || $settings['borderSizeRightMobile'] || $settings['borderSizeBottomMobile'] || $settings['borderSizeLeftMobile'] ) {
					$mobile_css->add_property( 'border-width', array( $settings['borderSizeTopMobile'], $settings['borderSizeRightMobile'], $settings['borderSizeBottomMobile'], $settings['borderSizeLeftMobile'] ), 'px' );
				}

				if ( $settings['hasIcon'] ) {
					$mobile_css->set_selector( $selector . ' .ob-icon' );
					$mobile_css->add_property( 'font-size', $settings['iconSizeMobile'], $settings['iconSizeUnit'] );

					if ( ! $settings['removeText'] ) {
						$mobile_css->add_property( 'padding', array( $settings['iconPaddingTopMobile'], $settings['iconPaddingRightMobile'], $settings['iconPaddingBottomMobile'], $settings['iconPaddingLeftMobile'] ), $settings['iconPaddingUnit'] );
					}
				}

				/**
				 * Do olympus_blocks_block_css_data hook
				 *
				 * @param string $name The name of our block.
				 * @param array  $settings The settings for the current block.
				 * @param object $css Our desktop/main CSS data.
				 * @param object $desktop_css Our desktop only CSS data.
				 * @param object $tablet_css Our tablet CSS data.
				 * @param object $tablet_only_css Our tablet only CSS data.
				 * @param object $mobile_css Our mobile CSS data.
				 */
				do_action(
					'olympus_blocks_block_css_data',
					$name,
					$settings,
					$css,
					$desktop_css,
					$tablet_css,
					$tablet_only_css,
					$mobile_css
				);
			}

			if ( $css->css_output() ) {
				$main_css_data[] = $css->css_output();
			}

			if ( $desktop_css->css_output() ) {
				$desktop_css_data[] = $desktop_css->css_output();
			}

			if ( $tablet_css->css_output() ) {
				$tablet_css_data[] = $tablet_css->css_output();
			}

			if ( $tablet_only_css->css_output() ) {
				$tablet_only_css_data[] = $tablet_only_css->css_output();
			}

			if ( $mobile_css->css_output() ) {
				$mobile_css_data[] = $mobile_css->css_output();
			}
		}

		/**
		 * Get our Divider block CSS.
		 */
		if ( 'divider' === $name ) {
			if ( empty( $blockData ) ) {
				continue;
			}

			$blocks_exist = true;

			$css = new Olympus_Blocks_Dynamic_CSS();
			$desktop_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_only_css = new Olympus_Blocks_Dynamic_CSS();
			$mobile_css = new Olympus_Blocks_Dynamic_CSS();

			foreach ( $blockData as $atts ) {
				if ( ! isset( $atts['uniqueId'] ) ) {
					continue;
				}

				$defaults = olympus_blocks_get_block_defaults();

				$settings = wp_parse_args(
					$atts,
					$defaults['divider']
				);

				$id = $atts['uniqueId'];

				$selector = '.ob-divider-' . $id;

				$css->set_selector( $selector );
				$css->add_property( 'display', 'flex' );
				$css->add_property( 'width', '100%' );
				if ( ! empty( $settings['height'] ) ) {
					$css->add_property( 'padding-top', 'calc(' . absint( $settings['height'] ) . $settings['heightUnit'] . ' / 2)' );
					$css->add_property( 'padding-bottom', 'calc(' . absint( $settings['height'] ) . $settings['heightUnit'] . ' / 2)' );
				}

				if ( 'none' !== $settings['dividerStyle'] ) {
					$css->set_selector( $selector );
					$css->add_property( '--divider-border-style', $settings['dividerStyle'] );
					$css->add_property( '--divider-color', olympus_blocks_hex2rgba( $settings['dividerColor'], $settings['dividerColorOpacity'] ) );
					if ( 'solid' === $settings['dividerStyle'] ||
					'double' === $settings['dividerStyle'] ||
					'dotted' === $settings['dividerStyle'] ||
					'dashed' === $settings['dividerStyle'] ) {
						$css->add_property( '--divider-border-width', $settings['dividerHeight'], $settings['dividerHeightUnit'] );
					}
					$css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['align'] ) );

					if ( 'solid' !== $settings['dividerStyle'] &&
					'double' !== $settings['dividerStyle'] &&
					'dotted' !== $settings['dividerStyle'] &&
					'dashed' !== $settings['dividerStyle'] ) {
						$css->set_selector( $selector );
						$css->add_property( '--divider-pattern-url', 'url("data:image/svg+xml,' . olympus_blocks_svg_to_data_uri( $settings['dividerStyle'] ) .'")' );
						if ( $settings['dividerSize'] ) {
							$css->add_property( '--divider-pattern-height', $settings['dividerSize'], 'px' );
						} else {
							$css->add_property( '--divider-pattern-height', '20px' );
						}

						$svg_shapes = olympus_blocks_get_separator_styles();
						$selected_pattern = $svg_shapes[ $settings['dividerStyle'] ];
						if ( true === $selected_pattern['supports_amount'] ) {
							$css->add_property( '--divider-pattern-size', $settings['dividerAmount'], $settings['dividerAmountUnit'] );
						}
						$css->add_property( '--divider-pattern-repeat', 'repeat-x' );

						$css->set_selector( $selector . ' .ob-divider-separator' );
						$css->add_property( 'min-height', 'var(--divider-pattern-height)' );
						$css->add_property( '-webkit-mask-size', 'var(--divider-pattern-size) 100%' );
						$css->add_property( 'mask-size', 'var(--divider-pattern-size) 100%' );
						$css->add_property( '-webkit-mask-repeat', 'var(--divider-pattern-repeat)' );
						$css->add_property( 'mask-repeat', 'var(--divider-pattern-repeat)' );
						$css->add_property( 'background-color', 'var(--divider-color)' );
						$css->add_property( '-webkit-mask-image', 'var(--divider-pattern-url)' );
						$css->add_property( 'mask-image', 'var(--divider-pattern-url)' );
					}

					$css->set_selector( $selector . ' .ob-divider-separator' );
					$css->add_property( 'display', 'flex' );
					$css->add_property( 'border-top', 'var(--divider-border-width) var(--divider-border-style) var(--divider-color)' );
					$css->add_property( 'width', $settings['dividerWidth'], $settings['dividerWidthUnit'] );
				}

				if ( ! empty( $settings['heightTablet'] ) ) {
					$tablet_css->set_selector( $selector );
					$tablet_css->add_property( 'padding-top', 'calc(' . absint( $settings['heightTablet'] ) . $settings['heightTabletUnit'] . ' / 2)' );
					$tablet_css->add_property( 'padding-bottom', 'calc(' . absint( $settings['heightTablet'] ) . $settings['heightTabletUnit'] . ' / 2)' );
				}

				if ( 'none' !== $settings['dividerStyle'] ) {
					$tablet_css->set_selector( $selector );
					if ( 'solid' === $settings['dividerStyle'] ||
					'double' === $settings['dividerStyle'] ||
					'dotted' === $settings['dividerStyle'] ||
					'dashed' === $settings['dividerStyle'] ) {
						$tablet_css->add_property( '--divider-border-width', $settings['dividerHeightTablet'], $settings['dividerHeightTabletUnit'] );
					}
					$tablet_css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['alignTablet'] ) );

					$tablet_css->set_selector( $selector . ' .ob-divider-separator' );
					$tablet_css->add_property( 'width', $settings['dividerWidthTablet'], $settings['dividerWidthTablet'] );
				}

				if ( ! empty( $settings['heightMobile'] ) ) {
					$mobile_css->set_selector( $selector );
					$mobile_css->add_property( 'padding-top', 'calc(' . absint( $settings['heightMobile'] ) . $settings['heightMobileUnit'] . ' / 2)' );
					$mobile_css->add_property( 'padding-bottom', 'calc(' . absint( $settings['heightMobile'] ) . $settings['heightMobileUnit'] . ' / 2)' );
				}

				if ( 'none' !== $settings['dividerStyle'] ) {
					$mobile_css->set_selector( $selector );
					if ( 'solid' === $settings['dividerStyle'] ||
					'double' === $settings['dividerStyle'] ||
					'dotted' === $settings['dividerStyle'] ||
					'dashed' === $settings['dividerStyle'] ) {
						$mobile_css->add_property( '--divider-border-width', $settings['dividerHeightMobile'], $settings['dividerHeightMobileUnit'] );
					}
					$mobile_css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['alignMobile'] ) );

					$mobile_css->set_selector( $selector . ' .ob-divider-separator' );
					$mobile_css->add_property( 'width', $settings['dividerWidthMobile'], $settings['dividerWidthMobileUnit'] );
				}

				/**
				 * Do olympus_blocks_block_css_data hook
				 *
				 * @param string $name The name of our block.
				 * @param array  $settings The settings for the current block.
				 * @param object $css Our desktop/main CSS data.
				 * @param object $desktop_css Our desktop only CSS data.
				 * @param object $tablet_css Our tablet CSS data.
				 * @param object $tablet_only_css Our tablet only CSS data.
				 * @param object $mobile_css Our mobile CSS data.
				 */
				do_action(
					'olympus_blocks_block_css_data',
					$name,
					$settings,
					$css,
					$desktop_css,
					$tablet_css,
					$tablet_only_css,
					$mobile_css
				);
			}

			if ( $css->css_output() ) {
				$main_css_data[] = $css->css_output();
			}

			if ( $desktop_css->css_output() ) {
				$desktop_css_data[] = $desktop_css->css_output();
			}

			if ( $tablet_css->css_output() ) {
				$tablet_css_data[] = $tablet_css->css_output();
			}

			if ( $tablet_only_css->css_output() ) {
				$tablet_only_css_data[] = $tablet_only_css->css_output();
			}

			if ( $mobile_css->css_output() ) {
				$mobile_css_data[] = $mobile_css->css_output();
			}
		}

		/**
		 * Get our Heading block CSS.
		 */
		if ( 'heading' === $name ) {
			if ( empty( $blockData ) ) {
				continue;
			}

			$blocks_exist = true;

			$css = new Olympus_Blocks_Dynamic_CSS();
			$desktop_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_only_css = new Olympus_Blocks_Dynamic_CSS();
			$mobile_css = new Olympus_Blocks_Dynamic_CSS();

			if ( ! $icon_css_added ) {
				$css->set_selector( '.ob-icon' );
				$css->add_property( 'display', 'inline-flex' );
				$css->add_property( 'line-height', '0' );

				$css->set_selector( '.ob-icon svg' );
				$css->add_property( 'height', '1em' );
				$css->add_property( 'width', '1em' );
				$css->add_property( 'fill', 'currentColor' );

				$icon_css_added = true;
			}

			$css->set_selector( '.ob-highlight' );
			$css->add_property( 'background', 'none' );
			$css->add_property( 'color', 'unset' );

			foreach ( $blockData as $atts ) {
				if ( ! isset( $atts['uniqueId'] ) ) {
					continue;
				}

				$defaults = olympus_blocks_get_block_defaults();

				$settings = wp_parse_args(
					$atts,
					$defaults['heading']
				);

				$id = $atts['uniqueId'];

				$selector = '.ob-heading-' . $id;

				if ( apply_filters( 'olympus_blocks_heading_selector_tagname', true, $atts ) ) {
					$selector = $settings['element'] . $selector;
				}

				// Back-compatibility for when icon held a value.
				if ( $settings['icon'] ) {
					$settings['hasIcon'] = true;
				}

				$fontFamily = $settings['fontFamily'];

				$css->set_selector( $selector );
				if ( $fontFamily && 'Default' !== $fontFamily ) {
					$css->add_property( 'font-family', $fontFamily );
				}
				$css->add_property( 'text-align', $settings['align'] );
				$css->add_property( 'color', $settings['textColor'] );
				$css->add_property( 'background-color', olympus_blocks_hex2rgba( $settings['backgroundColor'], $settings['backgroundColorOpacity'] ) );
				$css->add_property( 'font-size', $settings['fontSize'], $settings['fontSizeUnit'] );
				$css->add_property( 'font-weight', $settings['fontWeight'] );
				$css->add_property( 'text-transform', $settings['textTransform'] );
				$css->add_property( 'line-height', $settings['lineHeight'], $settings['lineHeightUnit'] );
				$css->add_property( 'letter-spacing', $settings['letterSpacing'], 'em' );
				$css->add_property( 'padding', array( $settings['paddingTop'], $settings['paddingRight'], $settings['paddingBottom'], $settings['paddingLeft'] ), $settings['paddingUnit'] );
				$css->add_property( 'margin', array( $settings['marginTop'], $settings['marginRight'], $settings['marginBottom'], $settings['marginLeft'] ), $settings['marginUnit'] );
				$css->add_property( 'border-radius', array( $settings['borderRadiusTopLeft'], $settings['borderRadiusTopRight'], $settings['borderRadiusBottomRight'], $settings['borderRadiusBottomLeft'] ), $settings['borderRadiusUnit'] );
				if ( $settings['borderSizeTop'] || $settings['borderSizeRight'] || $settings['borderSizeBottom'] || $settings['borderSizeLeft'] ) {
					$css->add_property( 'border-width', array( $settings['borderSizeTop'], $settings['borderSizeRight'], $settings['borderSizeBottom'], $settings['borderSizeLeft'] ), 'px' );
					$css->add_property( 'border-style', $settings['borderStyle'] );
					$css->add_property( 'border-color', olympus_blocks_hex2rgba( $settings['borderColor'], $settings['borderColorOpacity'] ) );
				}

				if ( $settings['inlineWidth'] ) {
					if ( $settings['hasIcon'] ) {
						$css->add_property( 'display', 'inline-flex' );
					} else {
						$css->add_property( 'display', 'inline-block' );
					}
				}

				if ( $settings['hasIcon'] ) {
					if ( ! $settings['inlineWidth'] ) {
						$css->add_property( 'display', 'flex' );
					}

					if ( 'above' === $settings['iconLocation'] ) {
						$css->add_property( 'text-align', $settings['align'] );
					} else {
						$css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['align'] ) );
					}

					if ( 'inline' === $settings['iconLocation'] ) {
						$css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['iconVerticalAlign'] ) );
					}

					if ( 'above' === $settings['iconLocation'] ) {
						$css->add_property( 'flex-direction', 'column' );
					}
				}

				$css->set_selector( $selector . ' a' );
				$css->add_property( 'color', $settings['linkColor'] );

				$css->set_selector( $selector . ' a:hover' );
				$css->add_property( 'color', $settings['linkColorHover'] );

				if ( $settings['hasIcon'] ) {
					$css->set_selector( $selector . ' .ob-icon' );
					$css->add_property( 'color', olympus_blocks_hex2rgba( $settings['iconColor'], $settings['iconColorOpacity'] ) );

					if ( ! $settings['removeText'] ) {
						$css->add_property( 'padding', array( $settings['iconPaddingTop'], $settings['iconPaddingRight'], $settings['iconPaddingBottom'], $settings['iconPaddingLeft'] ), $settings['iconPaddingUnit'] );
					}

					if ( 'above' === $settings['iconLocation'] ) {
						$css->add_property( 'display', 'inline' );
					}

					$css->set_selector( $selector . ' .ob-icon svg' );
					$css->add_property( 'width', $settings['iconSize'], $settings['iconSizeUnit'] );
					$css->add_property( 'height', $settings['iconSize'], $settings['iconSizeUnit'] );
				}

				if ( $settings['highlightTextColor'] ) {
					$css->set_selector( $selector . ' .ob-highlight' );
					$css->add_property( 'color', $settings['highlightTextColor'] );
				}

				$tablet_css->set_selector( $selector );
				$tablet_css->add_property( 'text-align', $settings['alignTablet'] );
				$tablet_css->add_property( 'font-size', $settings['fontSizeTablet'], $settings['fontSizeUnit'] );
				$tablet_css->add_property( 'line-height', $settings['lineHeightTablet'], $settings['lineHeightUnit'] );
				$tablet_css->add_property( 'letter-spacing', $settings['letterSpacingTablet'], 'em' );
				$tablet_css->add_property( 'margin', array( $settings['marginTopTablet'], $settings['marginRightTablet'], $settings['marginBottomTablet'], $settings['marginLeftTablet'] ), $settings['marginUnit'] );
				$tablet_css->add_property( 'padding', array( $settings['paddingTopTablet'], $settings['paddingRightTablet'], $settings['paddingBottomTablet'], $settings['paddingLeftTablet'] ), $settings['paddingUnit'] );
				$tablet_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftTablet'], $settings['borderRadiusTopRightTablet'], $settings['borderRadiusBottomRightTablet'], $settings['borderRadiusBottomLeftTablet'] ), $settings['borderRadiusUnit'] );
				if ( $settings['borderSizeTopTablet'] || $settings['borderSizeRightTablet'] || $settings['borderSizeBottomTablet'] || $settings['borderSizeLeftTablet'] ) {
					$tablet_css->add_property( 'border-width', array( $settings['borderSizeTopTablet'], $settings['borderSizeRightTablet'], $settings['borderSizeBottomTablet'], $settings['borderSizeLeftTablet'] ), 'px' );
				}

				if ( $settings['inlineWidthTablet'] ) {
					if ( $settings['hasIcon'] ) {
						$tablet_css->add_property( 'display', 'inline-flex' );
					} else {
						$tablet_css->add_property( 'display', 'inline-block' );
					}
				}

				if ( $settings['hasIcon'] ) {
					$tablet_css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['alignTablet'] ) );

					if ( 'inline' === $settings['iconLocationTablet'] ) {
						$tablet_css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['iconVerticalAlignTablet'] ) );
					}

					if ( 'above' === $settings['iconLocation'] && 'inline' === $settings['iconLocationTablet'] ) {
						$tablet_css->add_property( 'flex-direction', 'inherit' );
					}

					if ( 'above' === $settings['iconLocationTablet'] ) {
						$tablet_css->add_property( 'flex-direction', 'column' );
					}

					$tablet_css->set_selector( $selector . ' .ob-icon' );

					if ( ! $settings['removeText'] ) {
						$tablet_css->add_property( 'padding', array( $settings['iconPaddingTopTablet'], $settings['iconPaddingRightTablet'], $settings['iconPaddingBottomTablet'], $settings['iconPaddingLeftTablet'] ), $settings['iconPaddingUnit'] );
					}

					if ( 'above' === $settings['iconLocationTablet'] || ( 'above' === $settings['iconLocation'] && '' == $settings['iconLocationTablet'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						$tablet_css->add_property( 'align-self', olympus_blocks_get_flexbox_align( $settings['alignTablet'] ) );
					}

					if ( 'above' === $settings['iconLocationTablet'] ) {
						$tablet_css->add_property( 'display', 'inline' );
					}

					$tablet_css->set_selector( $selector . ' .ob-icon svg' );
					$tablet_css->add_property( 'width', $settings['iconSizeTablet'], $settings['iconSizeUnit'] );
					$tablet_css->add_property( 'height', $settings['iconSizeTablet'], $settings['iconSizeUnit'] );
				}

				$mobile_css->set_selector( $selector );
				$mobile_css->add_property( 'text-align', $settings['alignMobile'] );
				$mobile_css->add_property( 'font-size', $settings['fontSizeMobile'], $settings['fontSizeUnit'] );
				$mobile_css->add_property( 'line-height', $settings['lineHeightMobile'], $settings['lineHeightUnit'] );
				$mobile_css->add_property( 'letter-spacing', $settings['letterSpacingMobile'], 'em' );
				$mobile_css->add_property( 'margin', array( $settings['marginTopMobile'], $settings['marginRightMobile'], $settings['marginBottomMobile'], $settings['marginLeftMobile'] ), $settings['marginUnit'] );
				$mobile_css->add_property( 'padding', array( $settings['paddingTopMobile'], $settings['paddingRightMobile'], $settings['paddingBottomMobile'], $settings['paddingLeftMobile'] ), $settings['paddingUnit'] );
				$mobile_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftMobile'], $settings['borderRadiusTopRightMobile'], $settings['borderRadiusBottomRightMobile'], $settings['borderRadiusBottomLeftMobile'] ), $settings['borderRadiusUnit'] );
				if ( $settings['borderSizeTopMobile'] || $settings['borderSizeRightMobile'] || $settings['borderSizeBottomMobile'] || $settings['borderSizeLeftMobile'] ) {
					$mobile_css->add_property( 'border-width', array( $settings['borderSizeTopMobile'], $settings['borderSizeRightMobile'], $settings['borderSizeBottomMobile'], $settings['borderSizeLeftMobile'] ), 'px' );
				}

				if ( $settings['inlineWidthMobile'] ) {
					if ( $settings['hasIcon'] ) {
						$mobile_css->add_property( 'display', 'inline-flex' );
					} else {
						$mobile_css->add_property( 'display', 'inline-block' );
					}
				}

				if ( $settings['hasIcon'] ) {
					$mobile_css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['alignMobile'] ) );

					if ( 'inline' === $settings['iconLocationMobile'] ) {
						$mobile_css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['iconVerticalAlignMobile'] ) );
					}

					if ( ( 'above' === $settings['iconLocation'] || 'inline' === $settings['iconLocationTablet'] ) && 'inline' === $settings['iconLocationMobile'] ) {
						$mobile_css->add_property( 'flex-direction', 'inherit' );
					}

					if ( 'above' === $settings['iconLocationMobile'] ) {
						$mobile_css->add_property( 'flex-direction', 'column' );
					}

					$mobile_css->set_selector( $selector . ' .ob-icon' );

					if ( ! $settings['removeText'] ) {
						$mobile_css->add_property( 'padding', array( $settings['iconPaddingTopMobile'], $settings['iconPaddingRightMobile'], $settings['iconPaddingBottomMobile'], $settings['iconPaddingLeftMobile'] ), $settings['iconPaddingUnit'] );
					}

					if ( 'above' === $settings['iconLocationMobile'] || ( 'above' === $settings['iconLocation'] && '' == $settings['iconLocationMobile'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						$mobile_css->add_property( 'align-self', olympus_blocks_get_flexbox_align( $settings['alignMobile'] ) );
					}

					if ( 'above' === $settings['iconLocationMobile'] ) {
						$mobile_css->add_property( 'display', 'inline' );
					}

					$mobile_css->set_selector( $selector . ' .ob-icon svg' );
					$mobile_css->add_property( 'width', $settings['iconSizeMobile'], $settings['iconSizeUnit'] );
					$mobile_css->add_property( 'height', $settings['iconSizeMobile'], $settings['iconSizeUnit'] );
				}

				/**
				 * Do olympus_blocks_block_css_data hook
				 *
				 * @param string $name The name of our block.
				 * @param array  $settings The settings for the current block.
				 * @param object $css Our desktop/main CSS data.
				 * @param object $desktop_css Our desktop only CSS data.
				 * @param object $tablet_css Our tablet CSS data.
				 * @param object $tablet_only_css Our tablet only CSS data.
				 * @param object $mobile_css Our mobile CSS data.
				 */
				do_action(
					'olympus_blocks_block_css_data',
					$name,
					$settings,
					$css,
					$desktop_css,
					$tablet_css,
					$tablet_only_css,
					$mobile_css
				);
			}

			if ( $css->css_output() ) {
				$main_css_data[] = $css->css_output();
			}

			if ( $desktop_css->css_output() ) {
				$desktop_css_data[] = $desktop_css->css_output();
			}

			if ( $tablet_css->css_output() ) {
				$tablet_css_data[] = $tablet_css->css_output();
			}

			if ( $tablet_only_css->css_output() ) {
				$tablet_only_css_data[] = $tablet_only_css->css_output();
			}

			if ( $mobile_css->css_output() ) {
				$mobile_css_data[] = $mobile_css->css_output();
			}
		}

		/**
		 * Get our Icon Box block CSS.
		 */
		if ( 'icon-box' === $name ) {
			if ( empty( $blockData ) ) {
				continue;
			}

			$blocks_exist = true;

			$css = new Olympus_Blocks_Dynamic_CSS();
			$desktop_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_css = new Olympus_Blocks_Dynamic_CSS();
			$tablet_only_css = new Olympus_Blocks_Dynamic_CSS();
			$mobile_css = new Olympus_Blocks_Dynamic_CSS();

			foreach ( $blockData as $atts ) {
				if ( ! isset( $atts['uniqueId'] ) ) {
					continue;
				}

				$defaults = olympus_blocks_get_block_defaults();

				$settings = wp_parse_args(
					$atts,
					$defaults['iconBox']
				);

				$id = $atts['uniqueId'];

				$selector = '.ob-icon-box-' . $id;

				$fontFamily = $settings['fontFamily'];
				$contentFontFamily = $settings['contentFontFamily'];

				$css->set_selector( $selector );
				$css->add_property( 'background-color', olympus_blocks_hex2rgba( $settings['backgroundColor'], $settings['backgroundColorOpacity'] ) );
				$css->add_property( 'padding', array( $settings['paddingTop'], $settings['paddingRight'], $settings['paddingBottom'], $settings['paddingLeft'] ), $settings['paddingUnit'] );
				$css->add_property( 'margin', array( $settings['marginTop'], $settings['marginRight'], $settings['marginBottom'], $settings['marginLeft'] ), $settings['marginUnit'] );
				$css->add_property( 'border-radius', array( $settings['borderRadiusTopLeft'], $settings['borderRadiusTopRight'], $settings['borderRadiusBottomRight'], $settings['borderRadiusBottomLeft'] ), $settings['borderRadiusUnit'] );
				if ( $settings['borderSizeTop'] || $settings['borderSizeRight'] || $settings['borderSizeBottom'] || $settings['borderSizeLeft'] ) {
					$css->add_property( 'border-width', array( $settings['borderSizeTop'], $settings['borderSizeRight'], $settings['borderSizeBottom'], $settings['borderSizeLeft'] ), 'px' );
					$css->add_property( 'border-style', $settings['borderStyle'] );
					$css->add_property( 'border-color', olympus_blocks_hex2rgba( $settings['borderColor'], $settings['borderColorOpacity'] ) );
				}

				if ( ! $settings['removeText'] ) {
					if ( 'above' === $settings['iconLocation'] ) {
						$css->set_selector( $selector . ' .ob-icon-box-content' );
						$css->add_property( 'text-align', $settings['align'] );
					}

					$css->set_selector( $selector . ' .ob-icon-box-heading' );
					if ( $fontFamily && 'Default' !== $fontFamily ) {
						$css->add_property( 'font-family', $fontFamily );
					}
					$css->add_property( 'font-size', $settings['fontSize'], $settings['fontSizeUnit'] );
					$css->add_property( 'line-height', $settings['lineHeight'], $settings['lineHeightUnit'] );
					$css->add_property( 'letter-spacing', $settings['letterSpacing'], 'em' );
					$css->add_property( 'font-weight', $settings['fontWeight'] );
					$css->add_property( 'text-transform', $settings['textTransform'] );
					$css->add_property( 'color', $settings['headingColor'] );

					if ( ! $settings['separator'] ) {
						$css->set_selector( $selector . ' .ob-icon-box-heading' );
						$css->add_property( 'margin-bottom', $settings['headingMarginBottom'], $settings['headingMarginBottomUnit'] );
					}

					if ( $settings['separator'] ) {
						$css->set_selector( $selector . ' .ob-icon-box-heading' );
						$css->add_property( 'margin-bottom', '0' );

						$css->set_selector( $selector . ' .ob-icon-box-separator' );
						$css->add_property( 'display', 'inline-block' );
						$css->add_property( 'border-top-width', $settings['separatorHeight'], $settings['separatorHeightUnit'] );
						$css->add_property( 'border-top-style', $settings['separatorStyle'] );
						$css->add_property( 'border-color', olympus_blocks_hex2rgba( $settings['separatorColor'], $settings['separatorColorOpacity'] ) );
						$css->add_property( 'width', $settings['separatorWidth'], $settings['separatorWidthUnit'] );
						$css->add_property( 'margin', 'calc(' . absint( $settings['separatorGap'] ) . 'px / 2) 0' );
					}

					$css->set_selector( $selector . ' .ob-icon-box-content-text' );
					if ( $contentFontFamily && 'Default' !== $contentFontFamily ) {
						$css->add_property( 'font-family', $contentFontFamily );
					}
					$css->add_property( 'font-size', $settings['contentFontSize'], $settings['contentFontSizeUnit'] );
					$css->add_property( 'line-height', $settings['contentLineHeight'], $settings['contentLineHeightUnit'] );
					$css->add_property( 'letter-spacing', $settings['contentLetterSpacing'], 'em' );
					$css->add_property( 'font-weight', $settings['contentFontWeight'] );
					$css->add_property( 'text-transform', $settings['contentTextTransform'] );
					$css->add_property( 'margin', '0' );
					$css->add_property( 'color', $settings['contentColor'] );

					$css->set_selector( $selector . ' a' );
					$css->add_property( 'color', $settings['linkColor'] );

					$css->set_selector( $selector . ' a:hover' );
					$css->add_property( 'color', $settings['linkColorHover'] );
				}

				if ( ( 'icon' === $settings['iconStyle'] && $settings['icon'] ) || ( 'image' === $settings['iconStyle'] && $settings['image'] ) ) {
					$css->set_selector( $selector );
					$css->add_property( 'display', 'flex' );
					$css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['align'] ) );

					if ( 'inline' === $settings['iconLocation'] ) {
						$css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['iconVerticalAlign'] ) );
					} else {
						$css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['align'] ) );
					}

					if ( 'above' === $settings['iconLocation'] ) {
						$css->add_property( 'flex-direction', 'column' );
						$css->add_property( 'text-align', $settings['align'] );
					}

					$css->set_selector( $selector . ' .ob-icon' );
					$css->add_property( 'display', 'inline-flex' );
					$css->add_property( 'line-height', '0' );
					$css->add_property( 'color', olympus_blocks_hex2rgba( $settings['iconColor'], $settings['iconColorOpacity'] ) );

					if ( ! $settings['removeText'] ) {
						$css->add_property( 'padding', array( $settings['iconPaddingTop'], $settings['iconPaddingRight'], $settings['iconPaddingBottom'], $settings['iconPaddingLeft'] ), $settings['iconPaddingUnit'] );
					}

					if ( $settings['icon'] ) {
						$css->set_selector( $selector . ' .ob-icon svg' );
						if ( ! empty( $settings['iconSize'] ) ) {
							$css->add_property( 'width', $settings['iconSize'], $settings['iconSizeUnit'] );
							$css->add_property( 'height', $settings['iconSize'], $settings['iconSizeUnit'] );
						} else {
							$css->add_property( 'height', '1em' );
							$css->add_property( 'width', '1em' );
						}
						$css->add_property( 'fill', 'currentColor' );
					}
					
					if ( $settings['image'] ) {
						$css->set_selector( $selector . ' .ob-icon img' );
						$css->add_property( 'width', $settings['iconSize'], $settings['iconSizeUnit'] );
					}
				}

				if ( $settings['highlightTextColor'] ) {
					$css->set_selector( $selector . ' .ob-highlight' );
					$css->add_property( 'color', $settings['highlightTextColor'] );
				}
				
				$tablet_css->set_selector( $selector );
				$tablet_css->add_property( 'margin', array( $settings['marginTopTablet'], $settings['marginRightTablet'], $settings['marginBottomTablet'], $settings['marginLeftTablet'] ), $settings['marginUnit'] );
				$tablet_css->add_property( 'padding', array( $settings['paddingTopTablet'], $settings['paddingRightTablet'], $settings['paddingBottomTablet'], $settings['paddingLeftTablet'] ), $settings['paddingUnit'] );
				$tablet_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftTablet'], $settings['borderRadiusTopRightTablet'], $settings['borderRadiusBottomRightTablet'], $settings['borderRadiusBottomLeftTablet'] ), $settings['borderRadiusUnit'] );
				if ( $settings['borderSizeTopTablet'] || $settings['borderSizeRightTablet'] || $settings['borderSizeBottomTablet'] || $settings['borderSizeLeftTablet'] ) {
					$tablet_css->add_property( 'border-width', array( $settings['borderSizeTopTablet'], $settings['borderSizeRightTablet'], $settings['borderSizeBottomTablet'], $settings['borderSizeLeftTablet'] ), 'px' );
				}

				if ( ! $settings['removeText'] ) {
					$tablet_css->set_selector( $selector . ' .ob-icon-box-content' );
					if ( 'inline' === $settings['iconLocationTablet'] || ( 'above' === $settings['iconLocation'] && 'inline' === $settings['iconLocationTablet'] ) ) {
						$tablet_css->add_property( 'text-align', 'inherit' );
					} else {
						$tablet_css->add_property( 'text-align', $settings['alignTablet'] );
					}

					$tablet_css->set_selector( $selector . ' .ob-icon-box-heading' );
					$tablet_css->add_property( 'font-size', $settings['fontSizeTablet'], $settings['fontSizeUnit'] );
					$tablet_css->add_property( 'line-height', $settings['lineHeightTablet'], $settings['lineHeightUnit'] );
					$tablet_css->add_property( 'letter-spacing', $settings['letterSpacingTablet'], 'em' );
					$tablet_css->add_property( 'margin-bottom', $settings['headingMarginBottomTablet'], $settings['headingMarginBottomUnit'] );

					if ( $settings['separator'] ) {
						$tablet_css->set_selector( $selector . ' .ob-icon-box-separator' );
						$tablet_css->add_property( 'border-top-width', $settings['separatorHeightTablet'], $settings['separatorHeightTabletUnit'] );
						$tablet_css->add_property( 'width', $settings['separatorWidthTablet'], $settings['separatorWidthTabletUnit'] );
						$tablet_css->add_property( 'margin', 'calc(' . absint( $settings['separatorGapTablet'] ) . 'px / 2) 0' );
					}

					$tablet_css->set_selector( $selector . ' .ob-icon-box-content-text' );
					$tablet_css->add_property( 'font-size', $settings['contentFontSizeTablet'], $settings['contentFontSizeUnit'] );
					$tablet_css->add_property( 'line-height', $settings['contentLineHeightTablet'], $settings['contentLineHeightUnit'] );
					$tablet_css->add_property( 'letter-spacing', $settings['contentLetterSpacingTablet'], 'em' );
				}

				if ( ( 'icon' === $settings['iconStyle'] && $settings['icon'] ) || ( 'image' === $settings['iconStyle'] && $settings['image'] ) ) {
					$tablet_css->set_selector( $selector );
					$tablet_css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['alignTablet'] ) );

					if ( 'inline' === $settings['iconLocationTablet'] ) {
						$tablet_css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['iconVerticalAlignTablet'] ) );
					} else {
						$tablet_css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['alignTablet'] ) );
					}

					if ( 'above' === $settings['iconLocation'] && 'inline' === $settings['iconLocationTablet'] ) {
						$tablet_css->add_property( 'flex-direction', 'inherit' );
					}

					if ( 'above' === $settings['iconLocationTablet'] ) {
						$tablet_css->add_property( 'flex-direction', 'column' );
						$tablet_css->add_property( 'text-align', $settings['alignTablet'] );
					}

					$tablet_css->set_selector( $selector . ' .ob-icon' );

					if ( ! $settings['removeText'] ) {
						$tablet_css->add_property( 'padding', array( $settings['iconPaddingTopTablet'], $settings['iconPaddingRightTablet'], $settings['iconPaddingBottomTablet'], $settings['iconPaddingLeftTablet'] ), $settings['iconPaddingUnit'] );
					}

					if ( 'above' === $settings['iconLocationTablet'] ) {
						$tablet_css->add_property( 'display', 'inline' );
					}

					if ( $settings['icon'] ) {
						$tablet_css->set_selector( $selector . ' .ob-icon svg' );
						$tablet_css->add_property( 'width', $settings['iconSizeTablet'], $settings['iconSizeUnit'] );
						$tablet_css->add_property( 'height', $settings['iconSizeTablet'], $settings['iconSizeUnit'] );
					}
					
					if ( $settings['image'] ) {
						$tablet_css->set_selector( $selector . ' .ob-icon img' );
						$tablet_css->add_property( 'width', $settings['iconSizeTablet'], $settings['iconSizeUnit'] );
					}
				}

				$mobile_css->set_selector( $selector );
				$mobile_css->add_property( 'margin', array( $settings['marginTopMobile'], $settings['marginRightMobile'], $settings['marginBottomMobile'], $settings['marginLeftMobile'] ), $settings['marginUnit'] );
				$mobile_css->add_property( 'padding', array( $settings['paddingTopMobile'], $settings['paddingRightMobile'], $settings['paddingBottomMobile'], $settings['paddingLeftMobile'] ), $settings['paddingUnit'] );
				$mobile_css->add_property( 'border-radius', array( $settings['borderRadiusTopLeftMobile'], $settings['borderRadiusTopRightMobile'], $settings['borderRadiusBottomRightMobile'], $settings['borderRadiusBottomLeftMobile'] ), $settings['borderRadiusUnit'] );
				if ( $settings['borderSizeTopMobile'] || $settings['borderSizeRightMobile'] || $settings['borderSizeBottomMobile'] || $settings['borderSizeLeftMobile'] ) {
					$mobile_css->add_property( 'border-width', array( $settings['borderSizeTopMobile'], $settings['borderSizeRightMobile'], $settings['borderSizeBottomMobile'], $settings['borderSizeLeftMobile'] ), 'px' );
				}
		
				if ( ! $settings['removeText'] ) {
					$mobile_css->set_selector( $selector . ' .ob-icon-box-content' );
					if ( 'inline' === $settings['iconLocationMobile'] || ( ( 'above' === $settings['iconLocation'] || 'above' === $settings['iconLocationTablet'] ) && 'inline' === $settings['iconLocationMobile'] ) ) {
						$mobile_css->add_property( 'text-align', 'inherit' );
					} else {
						$mobile_css->add_property( 'text-align', $settings['alignMobile'] );
					}

					$mobile_css->set_selector( $selector . ' .ob-icon-box-heading' );
					$mobile_css->add_property( 'font-size', $settings['fontSizeMobile'], $settings['fontSizeUnit'] );
					$mobile_css->add_property( 'line-height', $settings['lineHeightMobile'], $settings['lineHeightUnit'] );
					$mobile_css->add_property( 'letter-spacing', $settings['letterSpacingMobile'], 'em' );
					$mobile_css->add_property( 'margin-bottom', $settings['headingMarginBottomMobile'], $settings['headingMarginBottomUnit'] );

					if ( $settings['separator'] ) {
						$mobile_css->set_selector( $selector . ' .ob-icon-box-separator' );
						$mobile_css->add_property( 'border-top-width', $settings['separatorHeightMobile'], $settings['separatorHeightMobileUnit'] );
						$mobile_css->add_property( 'width', $settings['separatorWidthMobile'], $settings['separatorWidthMobileUnit'] );
						$mobile_css->add_property( 'margin', 'calc(' . absint( $settings['separatorGapMobile'] ) . 'px / 2) 0' );
					}

					$mobile_css->set_selector( $selector . ' .ob-icon-box-content-text' );
					$mobile_css->add_property( 'font-size', $settings['contentFontSizeMobile'], $settings['contentFontSizeUnit'] );
					$mobile_css->add_property( 'line-height', $settings['contentLineHeightMobile'], $settings['contentLineHeightUnit'] );
					$mobile_css->add_property( 'letter-spacing', $settings['contentLetterSpacingMobile'], 'em' );
				}

				if ( ( 'icon' === $settings['iconStyle'] && $settings['icon'] ) || ( 'image' === $settings['iconStyle'] && $settings['image'] ) ) {
					$mobile_css->set_selector( $selector );
					$mobile_css->add_property( 'justify-content', olympus_blocks_get_flexbox_align( $settings['alignMobile'] ) );

					if ( 'inline' === $settings['iconLocationMobile'] ) {
						$mobile_css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['iconVerticalAlignMobile'] ) );
					} else {
						$mobile_css->add_property( 'align-items', olympus_blocks_get_flexbox_align( $settings['alignMobile'] ) );
					}

					if ( ( 'above' === $settings['iconLocation'] || 'inline' === $settings['iconLocationTablet'] ) && 'inline' === $settings['iconLocationMobile'] ) {
						$mobile_css->add_property( 'flex-direction', 'inherit' );
					}

					if ( 'above' === $settings['iconLocationMobile'] ) {
						$mobile_css->add_property( 'flex-direction', 'column' );
						$mobile_css->add_property( 'text-align', $settings['alignMobile'] );
					}

					$mobile_css->set_selector( $selector . ' .ob-icon' );

					if ( ! $settings['removeText'] ) {
						$mobile_css->add_property( 'padding', array( $settings['iconPaddingTopMobile'], $settings['iconPaddingRightMobile'], $settings['iconPaddingBottomMobile'], $settings['iconPaddingLeftMobile'] ), $settings['iconPaddingUnit'] );
					}

					if ( 'above' === $settings['iconLocationMobile'] ) {
						$mobile_css->add_property( 'display', 'inline' );
					}

					if ( $settings['icon'] ) {
						$mobile_css->set_selector( $selector . ' .ob-icon svg' );
						$mobile_css->add_property( 'width', $settings['iconSizeMobile'], $settings['iconSizeUnit'] );
						$mobile_css->add_property( 'height', $settings['iconSizeMobile'], $settings['iconSizeUnit'] );
					}
					
					if ( $settings['image'] ) {
						$mobile_css->set_selector( $selector . ' .ob-icon img' );
						$mobile_css->add_property( 'width', $settings['iconSizeMobile'], $settings['iconSizeUnit'] );
					}
				}

				/**
				 * Do olympus_blocks_block_css_data hook
				 *
				 * @param string $name The name of our block.
				 * @param array  $settings The settings for the current block.
				 * @param object $css Our desktop/main CSS data.
				 * @param object $desktop_css Our desktop only CSS data.
				 * @param object $tablet_css Our tablet CSS data.
				 * @param object $tablet_only_css Our tablet only CSS data.
				 * @param object $mobile_css Our mobile CSS data.
				 */
				do_action(
					'olympus_blocks_block_css_data',
					$name,
					$settings,
					$css,
					$desktop_css,
					$tablet_css,
					$tablet_only_css,
					$mobile_css
				);
			}

			if ( $css->css_output() ) {
				$main_css_data[] = $css->css_output();
			}

			if ( $desktop_css->css_output() ) {
				$desktop_css_data[] = $desktop_css->css_output();
			}

			if ( $tablet_css->css_output() ) {
				$tablet_css_data[] = $tablet_css->css_output();
			}

			if ( $tablet_only_css->css_output() ) {
				$tablet_only_css_data[] = $tablet_only_css->css_output();
			}

			if ( $mobile_css->css_output() ) {
				$mobile_css_data[] = $mobile_css->css_output();
			}
		}
	}

	if ( ! $blocks_exist ) {
		return false;
	}

	return apply_filters(
		'olympus_blocks_css_device_data',
		array(
			'main' => $main_css_data,
			'desktop' => $desktop_css_data,
			'tablet' => $tablet_css_data,
			'tablet_only' => $tablet_only_css_data,
			'mobile' => $mobile_css_data,
		),
		$settings
	);
}

/**
 * Turn our CSS array into plain CSS.
 *
 * @param array $data Our CSS data.
 */
function olympus_blocks_get_parsed_css( $data ) {
	$output = '';

	foreach ( $data as $device => $selectors ) {
		foreach ( $selectors as $selector => $properties ) {
			if ( ! count( $properties ) ) {
				continue;
			}

			$temporary_output = $selector . '{';
			$elements_added = 0;

			foreach ( $properties as $key => $value ) {
				if ( empty( $value ) ) {
					continue;
				}

				$elements_added++;
				$temporary_output .= $value;
			}

			$temporary_output .= '}';

			if ( $elements_added > 0 ) {
				$output .= $temporary_output;
			}
		}
	}

	return $output;
}

/**
 * Print our CSS for each block.
 */
function olympus_blocks_get_frontend_block_css() {
	if ( ! function_exists( 'has_blocks' ) ) {
		return;
	}

	$content = olympus_blocks_get_parsed_content();

	if ( ! $content ) {
		return;
	}

	$data = olympus_blocks_get_dynamic_css( $content );

	if ( ! $data ) {
		return;
	}

	$css = '';

	$css .= olympus_blocks_get_parsed_css( $data['main'] );

	if ( ! empty( $data['desktop'] ) ) {
		$css .= sprintf(
			'@media %1$s {%2$s}',
			olympus_blocks_get_media_query( 'desktop' ),
			olympus_blocks_get_parsed_css( $data['desktop'] )
		);
	}

	if ( ! empty( $data['tablet'] ) ) {
		$css .= sprintf(
			'@media %1$s {%2$s}',
			olympus_blocks_get_media_query( 'tablet' ),
			olympus_blocks_get_parsed_css( $data['tablet'] )
		);
	}

	if ( ! empty( $data['tablet_only'] ) ) {
		$css .= sprintf(
			'@media %1$s {%2$s}',
			olympus_blocks_get_media_query( 'tablet_only' ),
			olympus_blocks_get_parsed_css( $data['tablet_only'] )
		);
	}

	if ( ! empty( $data['mobile'] ) ) {
		$css .= sprintf(
			'@media %1$s {%2$s}',
			olympus_blocks_get_media_query( 'mobile' ),
			olympus_blocks_get_parsed_css( $data['mobile'] )
		);
	}

	return apply_filters( 'olympus_blocks_css_output', $css );
}
