<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://integer.pt
 * @since      1.0.0
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/admin
 * @author     Integer Consulting <info@integer.pt>
 */
class DevsoftIn_Wp_Sec_Recaptcha_Settings {

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Loads the session and settings to use the recaptcha.
	 *
	 * @return void
	 */
	public function initialize_display_recaptcha() {
		if ( false == get_option( 'sec_display_recaptcha' ) ) {
			$default_array = $this->default_display_options();
			add_option( 'sec_display_recaptcha', $default_array );
		}

		add_settings_section(
			'recaptcha_settings_section',
			__( 'reCaptcha Options', $this->plugin_name ),
			array( $this, 'general_options_callback' ),
			'sec_display_recaptcha'
		);

		add_settings_field(
			'site_key',
			__( 'Site key', $this->plugin_name ),
			array( $this, 'site_key_callback' ),
			'sec_display_recaptcha',
			'recaptcha_settings_section'
		);

		add_settings_field(
			'secret_key',
			__( 'Secret key', $this->plugin_name ),
			array( $this, 'secret_key_callback' ),
			'sec_display_recaptcha',
			'recaptcha_settings_section'
		);

		register_setting(
			'sec_display_recaptcha',
			'sec_display_recaptcha'
		);
	}

	/**
	 * Create an array with the attributes that will be used in this area.
	 *
	 * @return array
	 */
	public function default_display_options() {

		$defaults = array(
			'site_key'   => '',
			'secret_key' => '',
		);

		return $defaults;
	}

	/**
	 * Displays a description for the page.
	 *
	 * @return void
	 */
	public function general_options_callback() {

		printf(
			'<p>%s</br>%s<a href="https://www.google.com/recaptcha/admin" target=\"_blank\">%s.</a></p>',
			esc_html__( 'Protect your WordPress admin with Google reCAPTCHA.', $this->plugin_name ),
			esc_html__( 'Get the information listed below ', $this->plugin_name ),
			esc_html__( 'Here', $this->plugin_name )
		);
	}

	/**
	 * Presents the site_key configuration
	 *
	 * @return void
	 */
	public function site_key_callback() {

		$options  = get_option( 'sec_display_recaptcha' );
		$site_key = isset( $options['site_key'] ) ? $options['site_key'] : '';

		printf(
			'<input type="text" id="site_key" name="sec_display_recaptcha[site_key]" value="%s" />',
			esc_attr( $site_key )
		);
	}

	/**
	 * Presents the secret_key configuration
	 *
	 * @return void
	 */
	public function secret_key_callback() {
		$options    = get_option( 'sec_display_recaptcha' );
		$secret_key = isset( $options['secret_key'] ) ? $options['secret_key'] : '';

		printf(
			'<input type="text" id="secret_key" name="sec_display_recaptcha[secret_key]" value="%s" />',
			esc_attr( $secret_key )
		);
	}
}
