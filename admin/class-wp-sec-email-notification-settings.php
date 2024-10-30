<?php

class DevsoftIn_Wp_Sec_Email_Notification_Settings {

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
	 * Utils Class
	 *
	 * @access private
	 * @var class Wp_Sec_Utils
	 */
	private $utils;

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

		$this->load_dependencies();
	}

	/**
	 * Load dependencies
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-utils.php';
		$this->utils = new DevsoftIn_Wp_Sec_Utils();
	}

	/**
	 * Loads the scripts for the logs session.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '_email-notification',
			plugin_dir_url( __FILE__ ) . 'js/wp-sec-email-notification.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name . '_email-notification',
			'email_notification_settings_ajax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			)
		);
	}

	/**
	 * Function that loads sessions from the email notifications.
	 *
	 * @return void
	 */
	public function initialize_email_notification_settings() {
		if ( false == get_option( 'sec_email_notification' ) ) {
			$default_array = $this->default_display_settings();
			add_option( 'sec_email_notification', $default_array );
		}

		add_settings_section(
			'email_notification_section',
			__( 'Email notification settings', $this->plugin_name ),
			array( $this, 'general_settings_callback' ),
			'sec_email_notification'
		);

		add_settings_field(
			'all_email_notification',
			__( 'Send email notifications', $this->plugin_name ),
			array( $this, 'all_email_notification_callback' ),
			'sec_email_notification',
			'email_notification_section'
		);

		register_setting(
			'sec_email_notification',
			'sec_email_notification'
		);
	}

	/**
	 * Create an array with the attributes that will be used in this area.
	 *
	 * @return array
	 */
	public function default_display_settings() {
		return ( array(
			'all_email_notification' => ''
		) );
	}

	/**
	 * Displays a description for the page.
	 *
	 * @return void
	 */
	public function general_settings_callback() {
		printf(
			'<p>%s</p>',
			esc_html__( 'Protect your WordPress admin with Email Notifications.', $this->plugin_name )
		);
	}

	/**
	 * Notifies admin with all possible emails
	 *
	 * @return void
	 */
	public function all_email_notification_callback() {
		$this->utils->print_option_switch( 'all_email_notification', 'sec_email_notification' );
		printf(
			'<p><b>%s</b></p>
             <p>• %s</p>
             <p>• %s</p>
             <p>• %s</p>
             <p>• %s</p>',
			esc_html__( 'This enables emails for the following reasons:', $this->plugin_name ),
			esc_html__( 'Login accesses: successful and unsuccessful', $this->plugin_name ),
			esc_html__( 'IP blocks', $this->plugin_name ),
			esc_html__( 'Activation and deactivation of other plugins', $this->plugin_name ),
			esc_html__( 'User deletion', $this->plugin_name )
		);
	}

	/**
	 * Changes the email notification options.
	 *
	 * @return string
	 */
	public function change_options() {
		if ( ! is_admin() ) {
			wp_die();
		}

		$options = get_option( 'sec_email_notification' );
		$param   = '';
		if ( isset( $_POST['param'] ) ) {
			$param = sanitize_key( $_POST['param'] );
		}

		if ( $options[ $param ] == '1' || $options[ $param ] == true ) {
			$options[ $param ] = '0';
		} else {
			$options[ $param ] = '1';
		}

		update_option( 'sec_email_notification', $options );

		return $options[ $param ];
	}

}
