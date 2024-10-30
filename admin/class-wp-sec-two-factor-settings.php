<?php

class DevsoftIn_Wp_Sec_TwoFactorAuth_Settings {

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
	 * Function that loads sessions from the 2FA.
	 *
	 * @return void
	 */
	public function initialize_2fa_options() {
		if ( false == get_option( 'sec_2fa_options' ) ) {
			$default_array = $this->default_display_options();
			add_option( 'sec_2fa_options', $default_array );
		}

		add_settings_section(
			'2fa_settings_section',
			__( '2FA Options', $this->plugin_name ),
			array( $this, 'general_options_callback' ),
			'sec_2fa_options'
		);

		add_settings_field(
			'email_method',
			__( 'Select methods', $this->plugin_name ),
			array( $this, 'method_2fa_callback' ),
			'sec_2fa_options',
			'2fa_settings_section'
		);

		add_settings_field(
			'who_use_2fa',
			__( 'In which users do you want to use 2fa?', $this->plugin_name ),
			array( $this, 'who_will_use_2fa_callback' ),
			'sec_2fa_options',
			'2fa_settings_section'
		);

		register_setting(
			'sec_2fa_options',
			'sec_2fa_options'
		);
	}

	/**
	 * Create an array with the attributes that will be used in this area.
	 *
	 * @return array
	 */
	public function default_display_options() {

		$defaults = array(
			'email_method' => '',
			'who_use_2fa'  => '',
		);

		return $defaults;
	}

	/**
	 * Displays a description for the page.
	 *
	 * @return void
	 */
	public function general_options_callback() {
		printf( '<p>%s</p>', esc_html__( 'Protect your WordPress admin with Two Factor Auth.', $this->plugin_name ) );
	}

	/**
	 * Sets the type to be notified with 2FA.
	 *
	 * @return void
	 */
	public function method_2fa_callback() {

		$options = get_option( 'sec_2fa_options' );

		$email_method = isset( $options['email_method'] ) ? $options['email_method'] : '';

		$email_checked = ( 1 == $email_method ) ? 'checked="checked"' : '';

		printf(
			"<label for='sec_2fa_options-method-email'>
		        <input type='checkbox' id='sec_2fa_options_method_email' name='sec_2fa_options[email_method]' value='1' %s />
		        <span>%s</span>
		        </label>",
			esc_attr__( $email_checked ),
			esc_html__( 'Email', $this->plugin_name )
		);
	}

	/**
	 * Defines the type of user to use 2FA.
	 *
	 * @return void
	 */
	public function who_will_use_2fa_callback() {
		$options = get_option( 'sec_2fa_options' );

		$whoUse2fa = isset( $options['who_use_2fa'] ) ? $options['who_use_2fa'] : '';

		$all   = ( 'all' == $whoUse2fa ) ? 'selected="selected"' : '';
		$admin = ( 'admin' == $whoUse2fa ) ? 'selected="selected"' : '';

		printf(
			"<select name='sec_2fa_options[who_use_2fa]' id='sec_2fa_options-users-types'>
                    <option value='%s' %s>%s</option>
                    <option value='%s' %s>%s</option>
                </select>",
			esc_attr__( 'all' ),
			esc_attr__( $all ),
			esc_html__( 'All users', $this->plugin_name ),
			esc_attr__( 'administrator' ),
			esc_attr__( $admin ),
			esc_html__( 'Only admin role', $this->plugin_name )
		);
	}
}
