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
class DevsoftIn_Wp_Sec_Admin {

	/**
	 * Class Utils
	 *
	 * @var used to point to utils file
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private $utils;

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

		$this->load_dependencies();
	}

	/**
	 * Function used to load dependencies
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-utils.php';
		$this->utils = new DevsoftIn_Wp_Sec_Utils();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Sec_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Sec_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-sec-admin-dark-mode.css', array(), $this->version, 'all' );
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/wp-sec-admin.css',
			array(),
			$this->version, 'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Sec_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Sec_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script(
			$this->plugin_name . '_dashboard',
			plugin_dir_url( __FILE__ ) . 'js/wp-sec-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name . '_dashboard',
			'dashboard_scanner_ajax',
			array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'pluguins_url' => plugins_url( '', __DIR__ ),
			)
		);
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_options_page(
			'',
			'WP Security',
			'manage_options',
			$this->plugin_name,
			array(
				$this,
				'display_plugin_setup_page'
			)
		);

		add_menu_page(
			__( '', $this->plugin_name ),
			__( 'WP Security', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_setup_page' )
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */

	public function add_action_links( $links ) {
		$settings_link = array(
			sprintf(
				'<a href=%s>%s</a>',
				esc_url_raw( admin_url( 'options-general.php?page=' . $this->plugin_name ) ),
				esc_html__( 'Settings', $this->plugin_name )
			)
		);

		return array_merge( $settings_link, $links );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */

	public function display_plugin_setup_page() {
		include_once 'partials/wp-sec-admin-display.php';
	}

	/**
	 * A uninstall_dialog.
	 *
	 * @return void
	 */
	public function uninstall_dialog() {

		$screen = get_current_screen();
		if ( ! is_admin() || ! isset( $screen->id ) ) {
			return;
		}

		if ( ! in_array( $screen->id, array( 'plugins', 'plugins-network' ) ) ) {
			return;
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/wp-sec-admin-uninstall.php';
	}

	/**
	 * send_uninstall_feedback
	 *
	 * @return void
	 */
	public function send_uninstall_feedback() {
		$options = get_option( 'sec_report' );
		if ( empty( $options['active'] ) || empty( $options['host_key'] ) ) {
			return;
		}
		$utils = new DevsoftIn_Wp_Sec_Utils();

		$message = sprint(
			'%. Site: %s',
			esc_html( $_POST['msg'] ),
			get_site_url()
		);
		$utils->send_report( 'uninstall', 'reason_uninstall', $message );

	}

	/**
	 * Notifies the admin about valid and invalid logins
	 * Calls send_email_to_admin() in class-wp-sec-utils.php
	 *
	 * @param $user string username
	 * @param  $error array Error code + message, used for logging
	 *
	 * @return void
	 */
	public function login_email_notification_to_admin( $user, $error = null ) {
		$ip       = $this->utils->get_user_ip( $_SERVER );
		$datetime = date( 'Y-m-d H:i:s' );
		$message  = "A user %s in your system, the following information was logged: <br> %s";

		if ( $error && $error->has_errors() ) { //Login error message
			$subject = __( 'Login Failed', $this->plugin_name );
			$message = sprintf(
				$message,
				__( 'failed a log in', $this->plugin_name ),
				"<br>Username: <b>%s</b>
                 <br>IP: <b>%s</b>
                 <br>Error: <b>%s</b>
                 <br>Time of the Event: <b>%s</b>
                 <br>"
			);
			$error   = sprintf( "%s: %s", sanitize_text_field( $error->get_error_code() ),
				esc_html( $error->get_error_message() ) );
			$this->utils->send_email_to_admin( $subject, $message, [
				sanitize_text_field( $user ),
				$ip,
				$error,
				$datetime
			] );
		} else { //Login Successful message
			$subject = __( "Login Successful", $this->plugin_name );
			$message = sprintf(
				$message,
				__( "logged in successfully", $this->plugin_name ),
				"<br>Username: <b>%s</b>
                 <br>IP: <b>%s</b>
                 <br>Time of the Event: <b>%s</b>
                 <br>"
			);
			$this->utils->send_email_to_admin( $subject, $message, [ sanitize_text_field( $user ), $ip, $datetime ] );
		}
	}

	/**
	 * Notifies admin when a plugin is activated
	 *
	 * @param $plugin string activated plugin name
	 *
	 * @return void
	 *
	 */
	public function activate_plugin_email_notification_to_admin( $plugin ) {
		if ( strpos( $plugin,
				$this->plugin_name ) === false ) { // In case the plugin is wp-sec, don't try to send the email
			$this->general_plugin_email_notification_to_admin( $plugin, true );
		}
	}

	/**
	 * Support function for the activate/deactivate plugin notifying functions
	 * Calls send_email_to_admin() in class-wp-sec-utils.php
	 *
	 * @param $plugin string (de)activated plugin name
	 * @param $is_active
	 *
	 * @return void
	 */
	public function general_plugin_email_notification_to_admin( $plugin, $is_active ) {
		if ( $is_active ) {
			$subject = __( 'Plugin Activated', $this->plugin_name );

			$message = __( 'The plugin <b>%s</b> was <b>activated</b> and the following information was logged:',
				$this->plugin_name );
			$message .= sprintf( '<br>%s', __( 'Data of the user whom activated the plugin:', $this->plugin_name ) );
		} else {
			$subject = __( 'Plugin Deactivated', $this->plugin_name );

			$message = __( 'The plugin <b>%s</b> was <b>deactivated</b> and the following information was logged:',
				$this->plugin_name );
			$message .= sprintf( "<br>%s", __( 'Data of the user whom deactivated the plugin:', $this->plugin_name ) );

		}
		$current_user = wp_get_current_user();

		$message .= $this->get_user_data_in_form_of_ulist( $current_user );
		$message .= '<br>Time of Event: <b>%s</b>';


		$params = [
			sanitize_text_field( $plugin ),
			date( 'Y-m-d H:i:s' )
		];

		$this->utils->send_email_to_admin( $subject, $message, $params );
	}

	/**
	 * Get user data.
	 *
	 * @param object $user
	 *
	 * @return string
	 */
	public function get_user_data_in_form_of_ulist( $user ) {
		return sprintf(
			'<ul>
                 <li>
                     User ID: <b>%d</b>
                 </li>
                 <li> 
                     Username: <b>%s</b>
                 </li> 
                 <li>
                     User\'s email: <b>%s</b>
                 </li>
             </ul>',
			esc_html__( $user->get( 'ID' ) ),
			esc_html__( $user->user_login ),
			esc_html__( $user->user_email )
		);
	}

	/**
	 * Notifies admin when a plugin is deactivated
	 *
	 * @param $plugin string deactivated plugin name
	 *
	 * @return void
	 *
	 */
	public function deactivate_plugin_email_notification_to_admin( $plugin ) {
		if ( strpos( $plugin,
				$this->plugin_name ) === false ) { // In case the plugin is wp-sec, don't try to send the email
			$this->general_plugin_email_notification_to_admin( $plugin, false );
		}
	}

	/**
	 * Notifies admin when a user is created
	 *
	 * @param $user_id int id of the created user
	 *
	 * @return void
	 */
	public function new_user_email_notification_to_admin( $user_id ) {
		$this->general_user_email_notification_to_admin( $user_id, true );
	}

	/**
	 * Support function for the created/deleted user notifying functions
	 * Calls send_email_to_admin() in class-wp-sec-utils.php
	 *
	 * @param $user_id int id of the created/deleted user
	 * @param $is_new_user bool if true sends created message, else sends deleted message
	 *
	 * @return void
	 */
	public function general_user_email_notification_to_admin( $user_id, $is_new_user ) {
		$affected_user = get_user_by( 'id', $user_id );
		if ( $is_new_user ) {
			$subject = __( 'New user in the system', $this->plugin_name );
			$message = sprintf( "%s<br>%s<br>%s<br>%s",
				__( 'A new user was inserted in your system.', $this->plugin_name ),
				__( 'Data of new user:', $this->plugin_name ),
				$this->get_user_data_in_form_of_ulist( $affected_user ),
				__( 'Data of the user whom created the user:', $this->plugin_name )
			);
		} else {
			$subject = __( 'User deleted in the system', $this->plugin_name );
			$message = sprintf( "%s<br>%s<br>%s<br>%s",
				__( 'A user was deleted in your system.', $this->plugin_name ),
				__( 'Data of deleted user:', $this->plugin_name ),
				$this->get_user_data_in_form_of_ulist( $affected_user ),
				__( 'Data of the user whom deleted the user:', $this->plugin_name )
			);
		}

		$current_user = wp_get_current_user();
		$message      .= $this->get_user_data_in_form_of_ulist( $current_user );
		$message      .= '<br>Time of Event: <b>%s</b>';

		$params = [
			date( 'Y-m-d H:i:s' )
		];

		$this->utils->send_email_to_admin( $subject, $message, $params );
	}

	/**
	 * Notifies admin when a user is deleted
	 *
	 * @param $user_id int id of the deleted user
	 *
	 * @return void
	 */
	public function del_user_notification_to_admin( $user_id ) {
		$this->general_user_email_notification_to_admin( $user_id, false );
	}
}
