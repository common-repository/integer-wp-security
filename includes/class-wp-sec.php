<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://integer.pt
 * @since      1.0.0
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Sec
 * @subpackage Wp_Sec/includes
 * @author     Integer Consulting <info@integer.pt>
 */
class DevsoftIn_Wp_Sec {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      DevsoftIn_Wp_Sec_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	protected $plugin_public;
	protected $plugin_hardening;
	protected $plugin_logs;
	protected $plugin_recaptcha_settings;
	protected $plugin_2fa_settings;
	protected $plugin_malware_scanner_settings;
	protected $plugin_admin;
	protected $plugin_recaptcha_public;
	protected $plugin_activity_settings;
	protected $plugin_two_factor_public;
	protected $plugin_gdpr_settings;
	protected $plugin_email_notification_settings;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'DEVSOFTIN_WP_SEC_VERSION' ) ) {
			$this->version = DEVSOFTIN_WP_SEC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'integer-wp-security';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Sec_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Sec_i18n. Defines internationalization functionality.
	 * - Wp_Sec_Admin. Defines all hooks for the admin area.
	 * - Wp_Sec_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-recaptcha-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-hardening-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-two-factor-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-malware-scanner-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-verification-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-configuration-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-logs-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-activity-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-email-notification-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-gdpr-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-sec-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-sec-recaptcha-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-sec-two-factor-public.php';

		$this->loader = new DevsoftIn_Wp_Sec_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Sec_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new DevsoftIn_Sec_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$this->loadAdminClass();

		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts' );
		// $this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_malware_scanner_settings, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_logs, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_hardening, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_configuration_settings, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_activity_settings, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_email_notification_settings,
			'enqueue_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_gdpr_settings, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $this->plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $this->plugin_recaptcha_settings, 'initialize_display_recaptcha' );
		$this->loader->add_action( 'admin_init', $this->plugin_2fa_settings, 'initialize_2fa_options' );
		$this->loader->add_action( 'admin_init', $this->plugin_hardening, 'initialize_hardening' );
		// $this->loader->add_action( 'admin_init', $this->plugin_malware_scanner_settings, 'initialize_malware_setting_settings' );
		$this->loader->add_action( 'admin_init', $this->plugin_verification_settings,
			'initialize_verification_settings' );
		$this->loader->add_action( 'admin_init', $this->plugin_configuration_settings,
			'initialize_configuration_settings' );
		$this->loader->add_action( 'admin_init', $this->plugin_logs, 'initialize_logs' );
		$this->loader->add_action( 'admin_init', $this->plugin_activity_settings, 'initialize_activity_settings' );
		$this->loader->add_action( 'admin_init', $this->plugin_email_notification_settings,
			'initialize_email_notification_settings' );
		$this->loader->add_action( 'admin_init', $this->plugin_gdpr_settings, 'initialize_gdpr_options' );

		$this->loader->add_action( 'admin_footer', $this->plugin_admin, 'uninstall_dialog' );

		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $this->plugin_admin, 'add_action_links' );

		$this->loader->add_action( 'wp_ajax_gdpr_change_options', $this->plugin_gdpr_settings, 'change_options' );

		// ACTIVITY
		$this->loader->add_action( 'wp_ajax_activity_change_options', $this->plugin_activity_settings,
			'change_options' );

		$options_activity = get_option( 'sec_activity' );

		if ( isset( $options_activity['activity_comment_black_word'] ) && $options_activity['activity_comment_black_word'] == '1' ) {
			$this->loader->add_action( 'pre_comment_approved', $this->plugin_activity_settings, 'pre_comment_approved',
				10, 2 );
		}
		if ( isset( $options_activity['activity_check_to_block_ip'] ) && $options_activity['activity_check_to_block_ip'] == '1' ) {
			$this->loader->add_action( 'wp_loaded', $this->plugin_hardening, 'wp_loaded_ip_block' );
		}
		if ( isset( $options_activity['activity_wrong_login'] ) && $options_activity['activity_wrong_login'] == '1' ) {
			$this->loader->add_action( 'wp_login_failed', $this->plugin_hardening, 'wp_hardening_login_error', 10, 2 );
		}

		// MALWARE
		// $this->loader->add_action( 'wp_ajax_last_scanner', $this->plugin_malware_scanner_settings, 'last_scanner' );
		// $this->loader->add_action( 'wp_ajax_scanner_data_malware', $this->plugin_malware_scanner_settings, 'exec_scanner' );

		// CONFIGURATION
		$this->loader->add_action( 'wp_ajax_execute_analize', $this->plugin_configuration_settings, 'execute_analize' );
		$this->loader->add_action( 'wp_ajax_last_scanner', $this->plugin_configuration_settings, 'last_scanner' );
		$this->loader->add_action( 'wp_ajax_config_fixed', $this->plugin_configuration_settings, 'config_fixed' );

		// HARDENING
		$this->loader->add_action( 'wp_ajax_hardening_change_options', $this->plugin_hardening, 'change_options' );
		$this->loader->add_action( 'wp_ajax_hardening_change_change_permission_files', $this->plugin_hardening,
			'change_permission_files' );
		$this->loader->add_action( 'wp_ajax_hardening_change_prefix_tables', $this->plugin_hardening,
			'change_prefix_tables' );
		$this->loader->add_action( 'wp_ajax_hardening_change_login_url', $this->plugin_hardening, 'change_login_url' );
		$this->loader->add_action( 'wp_ajax_hardening_reset_login_url', $this->plugin_hardening, 'reset_login_url' );

		$options_hardening = get_option( 'sec_hardening' );

		if ( get_option( 'sec_hardening_login_url' ) !== ''
		     && get_option( 'sec_hardening_login_url' ) !== false
		     && strpos( trim( get_option( 'sec_hardening_login_url' ) ), 'wp-admin' ) === false
		     && strpos( trim( get_option( 'sec_hardening_login_url' ) ), 'wp-login' ) === false
		     && strpos( trim( get_option( 'sec_hardening_login_url' ) ), 'wp-includes' ) === false ) {
			$this->loader->add_action( 'wp_loaded', $this->plugin_hardening, 'wp_loaded' );
			$this->loader->add_filter( 'site_url', $this->plugin_hardening, 'site_url', 10, 3 );
			$this->loader->add_filter( 'network_site_url', $this->plugin_hardening, 'network_site_url', 10, 3 );
			$this->loader->add_filter( 'wp_redirect', $this->plugin_hardening, 'wp_redirect', 10, 2 );
			$this->loader->add_filter( 'site_option_welcome_email', $this->plugin_hardening, 'welcome_email', 10, 1 );
			remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
		}

		if ( isset( $options_hardening['remove_version_wordpress'] ) && $options_hardening['remove_version_wordpress'] == '1' ) {
			$this->removeVersionWordpress( true );
		} else {
			$this->removeVersionWordpress( false );
		}

		if ( isset( $options_hardening['hardening_author_page'] ) && $options_hardening['hardening_author_page'] == '1' ) {
			$this->loader->add_action( 'template_redirect', $this->plugin_hardening, 'author_page_redirect' );
		}

		// LOGS
		$this->loader->add_action( 'wp_ajax_logs_login_errors', $this->plugin_logs, 'logs_search', 10, 2 );
		$this->loader->add_action( 'wp_ajax_logs_change_options', $this->plugin_logs, 'change_options' );

		$optionsLogs = get_option( 'sec_logs_settings' );
		if ( isset( $optionsLogs['new_users'] ) && $optionsLogs['new_users'] == '1' ) {
			$this->loader->add_action( 'user_register', $this->plugin_logs, 'save_new_users', 10, 1 );
		}
		if ( isset( $optionsLogs['plugin_logs'] ) && $optionsLogs['plugin_logs'] == '1' ) {
			$this->loader->add_action( 'activated_plugin', $this->plugin_logs, 'save_new_plugin_installed', 10, 1 );
			$this->loader->add_action( 'deactivated_plugin', $this->plugin_logs, 'save_deactivate_plugin_installed', 10,
				1 );
		}

		// DISABLED PLUGIN
		$options = get_option( 'sec_report' );
		if ( ! empty( $options['active'] ) && ! empty( $options['host_key'] ) ) {

			$this->loader->add_action( 'wp_ajax_send_uninstall_feedback', $this->plugin_admin,
				'send_uninstall_feedback',
				20 );
			$this->loader->add_action( 'wp_ajax_email_notifications_change_options',
				$this->plugin_email_notification_settings, 'change_options', 20 );
		}


		// EMAIL NOTIFICATIONS
		$options_email_notification = get_option( 'sec_email_notification' );
		if ( isset( $options_email_notification['all_email_notification'] ) && $options_email_notification['all_email_notification'] == '1' ) {
			$this->loader->add_action( 'wp_login', $this->plugin_admin, 'login_email_notification_to_admin', 10,
				1 ); //Login Successful email
			$this->loader->add_action( 'wp_login_failed', $this->plugin_admin, 'login_email_notification_to_admin', 10,
				2 ); //Login failed email
			$this->loader->add_action( 'activated_plugin', $this->plugin_admin,
				'activate_plugin_email_notification_to_admin', 10, 1 ); //Activate plugin
			$this->loader->add_action( 'deactivated_plugin', $this->plugin_admin,
				'deactivate_plugin_email_notification_to_admin', 10, 1 ); //Deactivate plugin
			// user_register already existed, it's commented for future use
			// $this->loader->add_action('user_register', $this->plugin_admin, 'new_user_email_notification_to_admin', 10, 1); //User creation
			$this->loader->add_action( 'delete_user', $this->plugin_admin, 'del_user_notification_to_admin', 10,
				3 ); //User deletion
			// The hook to the notification for IP block was reused
			// The function that it's hooking to is "insert_ip_block" in "class-wp-sec-activity-settings"
			// This happened because the notification is will   only be send when the IP is blocked
		}
	}

	private function loadAdminClass() {
		$this->plugin_public                      = new DevsoftIn_Wp_Sec_Public( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_hardening                   = new DevsoftIn_Wp_Sec_Hardening_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_logs                        = new DevsoftIn_Wp_Sec_Logs_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_recaptcha_settings          = new DevsoftIn_Wp_Sec_Recaptcha_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_2fa_settings                = new DevsoftIn_Wp_Sec_TwoFactorAuth_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_malware_scanner_settings    = new DevsoftIn_Wp_Sec_Malware_Scanner_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_configuration_settings      = new DevsoftIn_Wp_Sec_Configuration_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_verification_settings       = new DevsoftIn_Wp_Sec_Verification_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_admin                       = new DevsoftIn_Wp_Sec_Admin( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_activity_settings           = new DevsoftIn_Wp_Sec_Activity_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_email_notification_settings = new DevsoftIn_Wp_Sec_Email_Notification_Settings( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_gdpr_settings               = new DevsoftIn_Wp_Sec_Gdpr_Settings( $this->get_plugin_name(),
			$this->get_version() );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	private function removeVersionWordpress( $enable ) {
		if ( $enable ) {
			remove_action( 'wp_head', 'wp_generator' );
			$this->loader->add_filter( 'style_loader_src', $this->plugin_public, 'sdt_remove_ver_css_js', 9999, 2 );
			$this->loader->add_filter( 'script_loader_src', $this->plugin_public, 'sdt_remove_ver_css_js', 9999, 2 );
		} else {
			add_action( 'wp_head', 'wp_generator' );
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$this->loadPublicClass();

		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts' );

		// RECAPTCHA
		$options = get_option( 'sec_display_recaptcha' );
		if ( isset( $options['site_key'] ) && isset( $options['secret_key'] ) &&
		     $this->validKeys( $options['secret_key'] ) && $this->validKeys( $options['secret_key'] ) ) {

			$this->loader->add_action( 'login_enqueue_scripts', $this->plugin_recaptcha_public, 'enqueue_styles' );
			$this->loader->add_action( 'login_enqueue_scripts', $this->plugin_recaptcha_public, 'enqueue_scripts' );
			$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_recaptcha_public, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_recaptcha_public, 'enqueue_scripts' );

			$this->loader->add_action( 'login_form', $this->plugin_recaptcha_public, 'initialize_recaptcha' );
			$this->loader->add_action( 'comment_form', $this->plugin_recaptcha_public, 'initialize_recaptcha' );
			$this->loader->add_action( 'lostpassword_form', $this->plugin_recaptcha_public, 'initialize_recaptcha' );

			$this->loader->add_action( 'wp_authenticate_user', $this->plugin_recaptcha_public, 'validate_captcha' );
			$this->loader->add_filter( 'lostpassword_errors', $this->plugin_recaptcha_public, 'validate_captcha' );
		}

		// LOGS
		$optionsLogs = get_option( 'sec_logs_settings' );

		if ( isset( $optionsLogs['login_error'] ) && $optionsLogs['login_error'] == '1' ) {
			$this->loader->add_action( 'wp_login_failed', $this->plugin_logs, 'save_login_errors', 10, 2 );
			$this->loader->add_filter( 'wp_login', $this->plugin_logs, 'save_login_errors', 10, 1 );
		}

		// 2FA
		$this->loader->add_filter( 'wp_login', $this->plugin_two_factor_public, 'initialize_2fa_email', 10, 2 );
		$this->loader->add_action( 'login_form_validate_2fa', $this->plugin_two_factor_public, 'login_form_validate' );

		// HARDENING
		$options_hardening = get_option( 'sec_hardening' );

		if ( isset( $options_hardening['remove_version_wordpress'] ) && $options_hardening['remove_version_wordpress'] == '1' ) {
			$this->removeVersionWordpress( true );
		} else {
			$this->removeVersionWordpress( false );
		}
	}

	private function loadPublicClass() {
		$this->plugin_public            = new DevsoftIn_Wp_Sec_Public( $this->get_plugin_name(), $this->get_version() );
		$this->plugin_recaptcha_public  = new DevsoftIn_Wp_Sec_Recaptcha_Public( $this->get_plugin_name(),
			$this->get_version() );
		$this->plugin_two_factor_public = new DevsoftIn_Wp_Sec_Two_Factor_Public(
			$this->get_plugin_name(),
			$this->get_version()
		);
		$this->plugin_logs              = new DevsoftIn_Wp_Sec_Logs_Settings( $this->get_plugin_name(),
			$this->get_version() );
	}

	/**
	 * Valid Keys.
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	private function validKeys( $string ) {
		if ( strlen( $string ) === 40 ) {
			return true;
		}

		return false;

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    DevsoftIn_Wp_Sec_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

}
