<?php
/**
 * Handles option changes on plugin updates.
 *
 * @package Olympus Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Process option updates if necessary.
 */
class Olympus_Blocks_Plugin_Update {
	/**
	 * Class instance.
	 *
	 * @access private
	 * @var $instance Class instance.
	 */
	private static $instance;

	/**
	 * Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', __CLASS__ . '::init', 5 );
		} else {
			add_action( 'wp', __CLASS__ . '::init', 5 );
		}
	}

	/**
	 * Implement plugin update logic.
	 */
	public static function init() {
		if ( is_customize_preview() ) {
			return;
		}

		$saved_version = get_option( 'olympus_blocks_version', false );

		if ( false === $saved_version ) {
			if ( 'admin_init' === current_action() ) {
				// If we're in the admin, add our version to the database.
				update_option( 'olympus_blocks_version', sanitize_text_field( OLY_BLOCKS_VERSION ) );
			}

			// Not an existing install, so no need to proceed further.
			return;
		}

		if ( version_compare( $saved_version, OLY_BLOCKS_VERSION, '=' ) ) {
			return;
		}

		// Force regenerate our static CSS files.
		update_option( 'olympus_blocks_dynamic_css_posts', array() );

		// Last thing to do is update our version.
		update_option( 'olympus_blocks_version', sanitize_text_field( OLY_BLOCKS_VERSION ) );
	}
}
Olympus_Blocks_Plugin_Update::get_instance();
