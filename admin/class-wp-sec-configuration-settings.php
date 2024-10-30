<?php

/**
 * Wp_Sec_Configuration_Settings
 */
class DevsoftIn_Wp_Sec_Configuration_Settings {

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
	 * @var
	 */
	private $verification;

	private $malware;

	private $hardening;

	private $utils;

	private $logs;

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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-verification-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-malware-scanner-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-hardening-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-logs-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-utils.php';
		$this->verification = new DevsoftIn_Wp_Sec_Verification_Settings( $this->plugin_name, $this->version );
		$this->malware      = new DevsoftIn_Wp_Sec_Malware_Scanner_Settings( $this->plugin_name, $this->version );
		$this->hardening    = new DevsoftIn_Wp_Sec_Hardening_Settings( $this->plugin_name, $this->version );
		$this->logs         = new DevsoftIn_Wp_Sec_Logs_Settings( $this->plugin_name, $this->version );
		$this->utils        = new DevsoftIn_Wp_Sec_Utils();
	}

	/**
	 * Loads the scripts for the malware session.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '_configuration',
			plugin_dir_url( __FILE__ ) . 'js/wp-sec-configuration.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name . '_configuration',
			'configuration_scanner_ajax',
			array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'plugins_url' => plugins_url( '', __DIR__ ),
			)
		);
	}

	public function config_fixed() {

		if ( get_option( 'activated_dashboard' ) === '1' ) {
			$this->hardening->execute_change_permission_files();

			delete_option( 'activated_dashboard' );
		}

		wp_die();
	}

	/**
	 * Function that loads sessions from the checks screen.
	 *
	 * @return void
	 */
	public function initialize_configuration_settings() {
		// delete_option( 'activated_dashboard' );

		if ( false == get_option( 'sec_configuration_settings' ) ) {
			add_option( 'sec_configuration_settings', array() );
		}

		add_settings_section(
			'configuration_settings',
			__( '', $this->plugin_name ),
			array( $this, 'general_options_callback' ),
			'sec_configuration_settings'
		);

		register_setting(
			'sec_configuration_settings',
			'sec_configuration_settings'
		);
	}

	/**
	 * Displays a description for the page.
	 *
	 * @return void
	 */
	public function general_options_callback() {
		$url_image = plugins_url( '/integer-wp-security/admin/images/imagotype_grey_and_white_shield.png' );

		printf( '<div><div id="config_header"></div>
				<div id="config_content">
					<div style="display: flex; justify-content: space-around; width: 100%;" class="background-dark">
						<img src="%1$s" style="padding: 36px; height: 130px;">
						<div style="display: flex; flex-wrap: wrap; align-items: center;">
							<div>
								<div class="medium-size padding-12 padding-left-off">%2$s</div>
								<div class="small-size">%3$s</div>
								<div class="small-size">%4$s</div>
								<div class="small-size" style="padding-top: 24px; display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
									<label class="switch" for="users_can_register" style="margin-right: 12px">
										<input type="checkbox" id="users_can_register" name="users_can_register" value="1" checked="checked" />
										<span class="slider round"></span>
									</label>
									<div>%5$s</div>
								</div>
							</div>
						</div>
						<div style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; padding-right: 36px;" id="button-scan">
							<input class="button_dashboard fix_now" type="button" id="button-scanner" name="scanner" value="%6$s" />
						</div>
					</div>
				</div>
				<div id="config_footer"></div></div>',
			esc_url_raw( $url_image ),
			esc_html__(
				'Welcome to the Integer WordPress Security Configurator.',
				$this->plugin_name
			),
			esc_html__(
				'Integer WordPress Security is here to help you. All your security issues are covered. That simple.',
				$this->plugin_name
			),
			esc_html__(
				'Let\'s jump into the setup and perform a first scan on your WordPress website to identify any security and performance issues.',
				$this->plugin_name
			),
			esc_html__(
				'We can send the data generated in the plugin to our database so that we can use it as feedback to evolve the plugin.',
				$this->plugin_name
			),
			esc_attr__( 'Scan Now', $this->plugin_name )
		);
	}

	/**
	 * Execute analize to dashboard.
	 *
	 * @return void
	 */
	public function execute_analize() {

		$report = filter_input(
			INPUT_POST,
			'report',
			FILTER_SANITIZE_NUMBER_INT
		);

		if ( empty( $report ) ) {
			$report = 0;
		}

		add_option(
			'sec_report',
			array(
				'active'   => $report,
				'host_key' => $this->utils->register_host( $report ),
			)
		);

		$hardening = array(
			'add reCAPTCHA or double authentication at administrative login',
			'disable theme editing',
			'hides the version of wordpress',
			'disables WP_DEBUG to not show errors',
			"disables access to WordPress directories by the client's browser",
			'remove the author page on the blog',
		);

		$logs = $this->logs->count_logs_block_attack();

		$this->utils->dashboard_fields( 'ip_attacks', strval( $logs['suspect'] ) );
		$this->utils->dashboard_fields( 'ip_blocks', strval( $logs['block'] ) );

		$dash = array();

		$dash['table_wrong_prefix'] = $this->verification->get_table_without_prefix();
		$dash['user_default']       = $this->verification->get_users_easy();
		$dash['password_default']   = $this->verification->get_password_easy();
		$dash['php_version']        = $this->verification->get_php_version_defaults_callback();
		$dash['file_permissions']   = $this->verification->find_files_wrong_permission( ABSPATH );
		// $malware_files              = $this->malware->check_integrity_files();
		$logs = $this->logs->count_logs_block_attack();

		// $dash['suspicious_file'] = $malware_files['data']['suspicious_file'];
		// $dash['corrupted_files'] = $malware_files['data']['corrupted_files'];
		$dash['ip_attacks'] = $logs['suspect'];
		$dash['ip_blocks']  = $logs['block'];

		$this->utils->dashboard_fields( 'table_wrong_prefix', strval( count( $dash['table_wrong_prefix'] ) ) );
		$this->utils->dashboard_fields( 'password_default', strval( count( $dash['password_default'] ) ) );
		$this->utils->dashboard_fields( 'user_default', strval( count( $dash['user_default'] ) ) );
		$this->utils->dashboard_fields( 'php_version', $dash['php_version']['message'] );
		$this->utils->dashboard_fields( 'file_permissions', strval( count( $dash['file_permissions'] ) ) );
		$this->utils->dashboard_fields( 'suspicious_file', strval( count( $dash['suspicious_file'] ) ) );
		$this->utils->dashboard_fields( 'corrupted_files', strval( count( $dash['corrupted_files'] ) ) );
		$this->utils->dashboard_fields( 'ip_attacks', strval( $dash['ip_attacks'] ) );
		$this->utils->dashboard_fields( 'ip_blocks', strval( $dash['ip_blocks'] ) );

		$this->utils->send_report(
			'verification',
			'table_wrong_prefix',
			true,
			array(
				'total' => strval( count( $dash['table_wrong_prefix'] ) )
			)
		);
		$this->utils->send_report(
			'verification',
			'password_default',
			true,
			array(
				'total' => strval( count( $dash['password_default'] )
				)
			)
		);
		$this->utils->send_report(
			'verification',
			'user_default',
			true,
			array(
				'total' => strval( count( $dash['user_default'] ) )
			)
		);
		$this->utils->send_report(
			'verification',
			'php_version',
			true,
			array( 'total' => $dash['php_version'] )
		);
		$this->utils->send_report(
			'verification',
			'file_permissions',
			true,
			array(
				'total' => strval( count( $dash['file_permissions'] ) )
			)
		);
		// $this->utils->send_report( 'malware', 'suspicious_file', true, array( 'total' => strval( count( $dash['suspicious_file'] ) ) ) );
		// $this->utils->send_report( 'malware', 'corrupted_files', true, array( 'total' => strval( count( $dash['corrupted_files'] ) ) ) );
		$this->utils->send_report(
			'hardening',
			'ip_attacks',
			true,
			array(
				'total' => strval( $dash['ip_attacks'] )
			)
		);
		$this->utils->send_report(
			'hardening',
			'ip_blocks',
			true,
			array(
				'total' => strval( $dash['ip_blocks'] )
			)
		);

		$dashboard_values = $this->utils->dashboard_values();

		$dashboard = current( $dashboard_values );

		printf(
			wp_json_encode(
				array(
					'status'  => '1',
					'message' => 'return result scanner',
					'data'    => array(
						'tables_without_prefix' => $this->get_value_dashboard(
							$dashboard_values,
							'table_wrong_prefix'
						),
						'easy_users'            => $this->get_value_dashboard(
							$dashboard_values,
							'user_default'
						),
						'easy_password'         => $this->get_value_dashboard(
							$dashboard_values,
							'password_default'
						),
						'php_version'           => $this->get_value_dashboard(
							$dashboard_values,
							'php_version'
						),
						'files_permission'      => $this->get_value_dashboard(
							$dashboard_values,
							'file_permissions'
						),
						// 'suspicious_file'       => $this->get_value_dashboard( $dashboard_values, 'suspicious_file' ),
						// 'corrupted_files'       => $this->get_value_dashboard( $dashboard_values, 'corrupted_files' ),
						'ip_attacks'            => $this->get_value_dashboard(
							$dashboard_values,
							'ip_attacks'
						),
						'ip_blocks'             => $this->get_value_dashboard(
							$dashboard_values,
							'ip_blocks'
						),
						'hardening_options'     => $hardening,
						'image_path'            => get_site_url(),
						'created_at'            => date( 'F j, Y \a\t G:i', strtotime( $dashboard['created_at'] ) )
					),
				)
			)
		);
		wp_die();
	}

	private function get_value_dashboard( $dashboard, $type ) {
		foreach ( $dashboard as $key => $value ) {
			if ( $value['type'] === $type ) {
				return $value['value'];
			}
		}
	}

	public function last_scanner() {
		$dashboard_values = $this->utils->dashboard_values();
		$dashboard        = current( $dashboard_values );

		printf(
			wp_json_encode(
				array(
					'status'  => '1',
					'message' => 'return last result scanner',
					'data'    => array(
						'tables_without_prefix' => $this->get_value_dashboard(
							$dashboard_values,
							'table_wrong_prefix'
						),
						'easy_users'            => $this->get_value_dashboard(
							$dashboard_values,
							'user_default'
						),
						'easy_password'         => $this->get_value_dashboard(
							$dashboard_values,
							'password_default'
						),
						'php_version'           => $this->get_value_dashboard(
							$dashboard_values,
							'php_version'
						),
						'files_permission'      => $this->get_value_dashboard(
							$dashboard_values,
							'file_permissions'
						),
						'ip_attacks'            => $this->get_value_dashboard(
							$dashboard_values,
							'ip_attacks'
						),
						'ip_blocks'             => $this->get_value_dashboard(
							$dashboard_values,
							'ip_blocks'
						),
						'image_path'            => get_site_url(),
						'created_at'            => date( 'F j, Y \a\t G:i', strtotime( $dashboard['created_at'] ) )
					),
				)
			)
		);
		wp_die();
	}

	private function rerun_dashboard( $created_at ) {
		$data_rerun = date( 'Y-m-d', strtotime( $created_at . ' + 7 days' ) );
		$data_atual = date( 'Y-m-d' );

		if ( $data_atual > $data_rerun ) {
			return true;
		}

		return false;
	}
}
