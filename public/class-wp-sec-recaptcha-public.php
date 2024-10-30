<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://integer.pt
 * @since      1.0.0
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/public
 * @author     Integer Consulting <info@integer.pt>
 */
class DevsoftIn_Wp_Sec_Recaptcha_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	public static function validate_captcha( $user ) {
		if ( ! isset( $_POST['g-recaptcha-response'] ) || ! self::captcha_verification() ) {
			return new WP_Error( 'empty_captcha', '<strong>ERROR</strong>: Please confirm you are not a robot.' );
			exit;
		}

		return $user;
	}

	public static function captcha_verification() {

		$option = get_option( 'sec_display_recaptcha' );

		$response = filter_input( INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_URL );
		if ( empty( $response ) ) {
			$response = '';
		}

		$remote_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );


		$request = wp_remote_get(
			'https://www.google.com/recaptcha/api/siteverify?secret=' . $option['secret_key'] . '&response=' . $response . '&remoteip=' . $remote_ip
		);

		$response_body = wp_remote_retrieve_body( $request );

		$result = json_decode( $response_body, true );

		return $result['success'];
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-sec-public.css', array(),
			$this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( 'login_google_api', 'https://www.google.com/recaptcha/api.js?onload=submitDisable' );
		wp_enqueue_script( 'login_google_api' );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-sec-public.js', array( 'jquery' ),
			$this->version, false );

	}

	public function initialize_recaptcha() {
		$optin = get_option( 'sec_display_recaptcha' );

		printf( '<div class="g-recaptcha" id="g-recaptcha" data-sitekey="%s" data-theme="light"></div>',
			$optin['site_key'] );
	}
}
