<?php
/**
 * Builds our dynamic CSS.
 *
 * @package Olympus Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Creates minified css via PHP.
 */
class Olympus_Blocks_Dynamic_CSS {

	/**
	 * The css selector that you're currently adding rules to
	 *
	 * @access protected
	 * @var string
	 */
	protected $_selector = ''; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Stores the final css output with all of its rules for the current selector.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_selector_output = ''; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Stores all of the rules that will be added to the selector
	 *
	 * @access protected
	 * @var string
	 */
	protected $_css = ''; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * The string that holds all of the css to output
	 *
	 * @access protected
	 * @var array
	 */
	protected $_output = array(); // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

	/**
	 * Sets a selector to the object and changes the current selector to a new one
	 *
	 * @access public
	 *
	 * @param  string $selector - the css identifier of the html that you wish to target.
	 * @return $this
	 */
	public function set_selector( $selector = '' ) {
		// Render the css in the output string everytime the selector changes.
		if ( '' !== $this->_selector ) {
			$this->add_selector_rules_to_output();
		}

		$this->_selector = $selector;
		return $this;
	}

	/**
	 * Adds a css property with value to the css output
	 *
	 * @access public
	 *
	 * @param  string $property - the css property.
	 * @param  string $value - the value to be placed with the property.
	 * @param  string $unit - the unit for the value (px).
	 * @return $this
	 */
	public function add_property( $property, $value, $unit = false ) {
		if ( empty( $value ) && ! is_numeric( $value ) ) {
			return false;
		}

		if ( is_array( $value ) && ! array_filter( $value, 'is_numeric' ) ) {
			return false;
		}

		if ( is_array( $value ) ) {
			$valueTop = olympus_blocks_has_number_value( $value[0] );
			$valueRight = olympus_blocks_has_number_value( $value[1] );
			$valueBottom = olympus_blocks_has_number_value( $value[2] );
			$valueLeft = olympus_blocks_has_number_value( $value[3] );

			if ( $valueTop || $valueRight || $valueBottom || $valueLeft ) {
				$value = olympus_blocks_get_shorthand_css( $value[0], $value[1], $value[2], $value[3], $unit );

				$this->_css .= $property . ':' . $value . ';';
				return $this;
			}
		}

		// Add our unit to our value if it exists.
		if ( $unit ) {
			$value = $value . $unit;
		}

		$this->_css .= $property . ':' . $value . ';';
		return $this;
	}

	/**
	 * Adds the current selector rules to the output variable
	 *
	 * @access private
	 *
	 * @return $this
	 */
	private function add_selector_rules_to_output() {
		if ( ! empty( $this->_css ) ) {
			$this->_selector_output = $this->_selector;
			$this->_output[ $this->_selector_output ][] = $this->_css;
			$this->_output[ $this->_selector_output ] = array_unique( $this->_output[ $this->_selector_output ] );

			// Reset the css.
			$this->_css = '';
		}

		return $this;
	}

	/**
	 * Returns the minified css in the $_output variable
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function css_output() {
		// Add current selector's rules to output.
		$this->add_selector_rules_to_output();

		return $this->_output;
	}
}
