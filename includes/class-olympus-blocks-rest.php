<?php
/**
 * Rest API functions
 *
 * @package Olympus Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Olympus_Blocks_Rest
 */
class Olympus_Blocks_Rest extends WP_REST_Controller {
	/**
	 * Instance.
	 *
	 * @access private
	 * @var object Instance
	 */
	private static $instance;

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'olympusblocks/v';

	/**
	 * Version.
	 *
	 * @var string
	 */
	protected $version = '1';

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
	 * olympus_blocks_Rest constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register rest routes.
	 */
	public function register_routes() {
		$namespace = $this->namespace . $this->version;

		// Update Settings.
		register_rest_route(
			$namespace,
			'/settings/',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);

		// Regenerate CSS Files.
		register_rest_route(
			$namespace,
			'/regenerate_css_files/',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'regenerate_css_files' ),
				'permission_callback' => array( $this, 'update_settings_permission' ),
			)
		);
	}

	/**
	 * Get edit options permissions.
	 *
	 * @return bool
	 */
	public function update_settings_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Sanitize our options.
	 *
	 * @param string $name The setting name.
	 * @param mixed  $value The value to save.
	 */
	public function sanitize_value( $name, $value ) {
		$callbacks = apply_filters(
			'olympus_blocks_option_sanitize_callbacks',
			array(
				'load_css_file' => 'rest_sanitize_boolean',
				'sync_responsive_previews' => 'rest_sanitize_boolean',
			)
		);

		$callback = $callbacks[ $name ];

		if ( ! is_callable( $callback ) ) {
			return sanitize_text_field( $value );
		}

		return $callback( $value );
	}

	/**
	 * Update Settings.
	 *
	 * @param WP_REST_Request $request  request object.
	 *
	 * @return mixed
	 */
	public function update_settings( WP_REST_Request $request ) {
		$current_settings = get_option( 'olympus-blocks', array() );
		$new_settings = $request->get_param( 'settings' );

		foreach ( $new_settings as $name => $value ) {
			// Skip if the option hasn't changed.
			if ( isset( $current_settings[ $name ] ) && $current_settings[ $name ] === $new_settings[ $name ] ) {
				unset( $new_settings[ $name ] );
				continue;
			}

			// Only save options that we know about.
			if ( ! array_key_exists( $name, olympus_blocks_get_option_defaults() ) ) {
				unset( $new_settings[ $name ] );
				continue;
			}

			// Sanitize our value.
			$new_settings[ $name ] = $this->sanitize_value( $name, $value );
		}

		if ( empty( $new_settings ) ) {
			return $this->success( __( 'No changes found.', 'olympus-blocks' ) );
		}

		if ( is_array( $new_settings ) ) {
			update_option( 'olympus-blocks', array_merge( $current_settings, $new_settings ) );
		}

		return $this->success( __( 'Settings saved.', 'olympus-blocks' ) );
	}

	/**
	 * Regenerate CSS Files.
	 *
	 * @param WP_REST_Request $request  request object.
	 *
	 * @return mixed
	 */
	public function regenerate_css_files( WP_REST_Request $request ) {
		update_option( 'olympus_blocks_dynamic_css_posts', array() );

		return $this->success( __( 'CSS files regenerated.', 'olympus-blocks' ) );
	}

	/**
	 * Success rest.
	 *
	 * @param mixed $response response data.
	 * @return mixed
	 */
	public function success( $response ) {
		return new WP_REST_Response(
			array(
				'success'  => true,
				'response' => $response,
			),
			200
		);
	}

	/**
	 * Error rest.
	 *
	 * @param mixed $code     error code.
	 * @param mixed $response response data.
	 * @return mixed
	 */
	public function error( $code, $response ) {
		return new WP_REST_Response(
			array(
				'error'      => true,
				'success'    => false,
				'error_code' => $code,
				'response'   => $response,
			),
			401
		);
	}
}
Olympus_Blocks_Rest::get_instance();
