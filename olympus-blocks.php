<?php
/**
 * Plugin Name: Olympus Blocks
 * Plugin URI: https://wpolympus.com
 * Description: Transform Gutenberg into a real page builder with amazing and powerful blocks.
 * Version: 1.0.0
 * Requires at least: 5.6
 * Requires PHP: 7.0
 * Author: UranusWP
 * Author URI: https://uranuswp.com/about-us/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: olympus-blocks
 *
 * @package Olympus Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'OLY_BLOCKS_VERSION', '1.0.0' );
define( 'OLY_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'OLY_BLOCKS_DIR_URL', plugin_dir_url( __FILE__ ) );

// Load necessary files.
require_once OLY_BLOCKS_DIR . 'includes/functions.php';
require_once OLY_BLOCKS_DIR . 'includes/general.php';
require_once OLY_BLOCKS_DIR . 'includes/defaults.php';
require_once OLY_BLOCKS_DIR . 'includes/generate-css.php';
require_once OLY_BLOCKS_DIR . 'includes/class-olympus-blocks-dynamic-css.php';
require_once OLY_BLOCKS_DIR . 'includes/class-olympus-blocks-enqueue-css.php';
require_once OLY_BLOCKS_DIR . 'includes/class-olympus-blocks-settings.php';
require_once OLY_BLOCKS_DIR . 'includes/class-olympus-blocks-plugin-update.php';
require_once OLY_BLOCKS_DIR . 'includes/class-olympus-blocks-render-blocks.php';
require_once OLY_BLOCKS_DIR . 'includes/class-olympus-blocks-rest.php';

/**
 * Load Olympus Blocks textdomain.
 */
function olympus_blocks_load_plugin_textdomain() {
	load_plugin_textdomain( 'olympus-blocks' );
}
add_action( 'plugins_loaded', 'olympus_blocks_load_plugin_textdomain' );

/**
 * Adds a redirect option during plugin activation on non-multisite installs.
 *
 * @param bool $network_wide Whether or not the plugin is being network activated.
 */
function olympus_blocks_do_activate( $network_wide = false ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only used to do a redirect. False positive.
	if ( ! $network_wide && ! isset( $_GET['activate-multi'] ) ) {
		update_option( 'olympus_blocks_do_activation_redirect', true );
	}
}
register_activation_hook( __FILE__, 'olympus_blocks_do_activate' );
