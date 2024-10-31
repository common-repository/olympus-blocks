<?php
/**
 * Our settings page.
 *
 * @package Olympus Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Build our settings page.
 */
class Olympus_Blocks_Settings {
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
		add_action( 'admin_menu', array( $this, 'add_menu' ), 15 );
		add_action( 'olympus_blocks_settings_area', array( $this, 'add_settings_container' ) );
		add_action( 'admin_init', array( $this, 'dashboard_redirect' ) );
	}

	/**
	 * Add our Dashboard menu item.
	 */
	public function add_menu() {
		$page = add_theme_page(
			apply_filters( 'oly_blocks_menu_page_title', esc_html__( 'Olympus Blocks', 'olympus-blocks' ) ),
			apply_filters( 'oly_blocks_page_title', esc_html__( 'Olympus Blocks', 'olympus-blocks' ) ),
			apply_filters( 'oly_blocks_dashboard_page_capability', 'manage_options' ),
			'oly_blocks_settings',
			array( $this, 'settings_page' ),
		);

		add_action( "admin_print_scripts-{$page}", array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue our scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'olympus-blocks-settings',
			OLY_BLOCKS_DIR_URL . 'dist/dashboard.js',
			array( 'wp-api', 'wp-i18n', 'wp-components', 'wp-element', 'wp-api-fetch' ),
			OLY_BLOCKS_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'olympus-blocks-settings', 'olympus-blocks' );
		}

		wp_localize_script(
			'olympus-blocks-settings',
			'olyBlocksSettings',
			array(
				'settings' => wp_parse_args(
					get_option( 'olympus-blocks', array() ),
					olympus_blocks_get_option_defaults()
				),
				'assetsPath' => OLY_BLOCKS_DIR_URL . 'assets/',
			)
		);

		wp_enqueue_style(
			'olympus-blocks-settings-build',
			OLY_BLOCKS_DIR_URL . 'dist/dashboard.css',
			array( 'wp-components' ),
			OLY_BLOCKS_VERSION
		);
	}

	/**
	 * Add settings container.
	 */
	public function add_settings_container() {
		echo '<div id="oly-blocks-default-settings"></div>';
	}

	/**
	 * Output our Dashboard HTML.
	 */
	public function settings_page() {
		?>
			<div id="oly-blocks-dashboard">
				<div class="oly-blocks-settings-area">
					<?php do_action( 'olympus_blocks_settings_area' ); ?>
				</div>
			</div>
		<?php
	}

	/**
	 * Redirect to the Dashboard page on single plugin activation.
	 */
	public function dashboard_redirect() {
		$do_redirect = apply_filters( 'olympus_blocks_do_activation_redirect', get_option( 'olympus_blocks_do_activation_redirect', false ) );
	
		if ( $do_redirect ) {
			delete_option( 'olympus_blocks_do_activation_redirect' );
			wp_safe_redirect( esc_url( admin_url( 'themes.php?page=oly_blocks_settings' ) ) );
			exit;
		}
	}
}
Olympus_Blocks_Settings::get_instance();
