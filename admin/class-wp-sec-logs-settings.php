<?php

class DevsoftIn_Wp_Sec_Logs_Settings {

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
	 * Activity Settings Class
	 *
	 * @access private
	 * @var class DevsoftIn_Wp_Sec_Activity_Settings
	 */
	private $activity_settings;

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
	 * Load Dependencies
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-utils.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-activity-settings.php';
		$this->utils             = new DevsoftIn_Wp_Sec_Utils();
		$this->activity_settings = new DevsoftIn_Wp_Sec_Activity_Settings( $this->plugin_name, $this->version );
	}

	/**
	 * Loads the scripts for the logs session.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '_logs',
			plugin_dir_url( __FILE__ ) . 'js/wp-sec-logs.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name . '_logs',
			'logs_settings_ajax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			)
		);
	}

	/**
	 * Function that loads sessions from the Logs.
	 *
	 * @return void
	 */
	public function initialize_logs() {

		$this->utils->send_report( 'tabs', 'sec_logs_settings', true );

		if ( false == get_option( 'sec_logs_settings' ) ) {
			$default_array = $this->default_display_options();
			add_option( 'sec_logs_settings', $default_array );
		}

		add_settings_section(
			'logs_section',
			__( 'logs settings', $this->plugin_name ),
			array( $this, 'general_options_callback' ),
			'sec_logs_settings'
		);

		add_settings_field(
			'login_error',
			__( 'Enable save login errors', $this->plugin_name ),
			array( $this, 'login_errors_callback' ),
			'sec_logs_settings',
			'logs_section'
		);

		add_settings_field(
			'new_users',
			__( 'Saves the new user registration log', $this->plugin_name ),
			array( $this, 'new_users_callback' ),
			'sec_logs_settings',
			'logs_section'
		);

		add_settings_field(
			'plugin_logs',
			__( 'Saves register log when active and deactivate', $this->plugin_name ),
			array( $this, 'plugin_logs_callback' ),
			'sec_logs_settings',
			'logs_section'
		);

		add_settings_section(
			'logs_tabs_section',
			'',
			array( $this, 'tabs_logs' ),
			'sec_logs_settings'
		);

		register_setting(
			'sec_logs_settings',
			'sec_logs_settings'
		);
	}

	/**
	 * Create an array with the attributes that will be used in this area.
	 *
	 * @return array
	 */
	public function default_display_options() {

		$defaults = array(
			'login_error' => '',
			'new_users'   => '',
			'plugin_logs' => '',
		);

		return $defaults;
	}

	/**
	 * Displays a description for the page.
	 *
	 * @return void
	 */
	public function general_options_callback() {
		printf( '<p>%s</p>', esc_html__( 'Enable save WordPress logs.', $this->plugin_name ) );
	}

	/**
	 * Load the log tabs
	 *
	 * @return void
	 */
	public function tabs_logs() {
		$options = get_option( 'sec_logs_settings' );

		printf( "</div>
                <div class='nav-bar-div card-dark' id='wp-sec'>
                    %s
                    <br/>
                </div>
                <div id='result-logs' class='card card-custom card-dark'>
                </div>
                <div>",
			$this->get_tags( $options )
		);
	}

	/**
	 * @param $options
	 *
	 * @return string|void
	 */
	private function get_tags( $options ) {
		$html = '<div id="wp-sec-submenu">
                 <ul class="wp-sec-submenu-list">';

		if ( isset( $options['login_error'] ) && '1' === $options['login_error'] ) {
			$html .= sprintf(
				'
					<li>
						<a id="login" class="nav-tab-logs nav-tab-active-blue">%s</a>
					</li>',
				esc_html__( 'Login', $this->plugin_name )
			);
		}
		if ( isset( $options['new_users'] ) && '1' === $options['new_users'] ) {
			$html .= sprintf(
				'
					<li>
						<a id="new-users" class="nav-tab-logs">%s</a>
					</li>',
				esc_html__( 'New Users', $this->plugin_name )
			);
		}
		if ( isset( $options['plugin_logs'] ) && '1' === $options['plugin_logs'] ) {
			$html .= sprintf(
				'
					<li>
						<a id="plugin-logs" class="nav-tab-logs">%s</a>
					</li>',
				esc_html__( 'Plugins', $this->plugin_name )
			);
		}

		$options_activity = get_option( 'sec_activity' );

		if ( isset( $options_activity['activity_check_to_block_ip'] ) && $options_activity['activity_check_to_block_ip'] == 1 ||
		     isset( $options_activity['activity_wrong_login'] ) && $options_activity['activity_wrong_login'] == 1 ||
		     isset( $options_activity['activity_comment_black_word'] ) && $options_activity['activity_comment_black_word'] == 1 ) {

			$html .= sprintf(
				'
					<li>
						<a id="activity-attacks" class="nav-tab-logs">%s</a>
					</li>
                   <li>
                   	<a id="activity-blocks" class="nav-tab-logs">%s</a>
                   	</li>',
				esc_html__( 'Attacks', $this->plugin_name ),
				esc_html__( 'Blocks', $this->plugin_name )
			);
		}

		$html .= '</ul></div>';

		if ( '' === $html ) {
			return;
		}

		return $html;
	}

	/**
	 * Enables the capture of error logins
	 *
	 * @return void
	 */
	public function login_errors_callback() {
		$this->utils->print_option_switch( 'login_error', 'sec_logs_settings' );
	}

	/**
	 * Enables capturing the creation of new users.
	 *
	 * @return void
	 */
	public function new_users_callback() {
		$this->utils->print_option_switch( 'new_users', 'sec_logs_settings' );
	}

	/**
	 * Enables the capture of installation logs and removal of plugins.
	 *
	 * @return void
	 */
	public function plugin_logs_callback() {
		$this->utils->print_option_switch( 'plugin_logs', 'sec_logs_settings' );
	}

	/**
	 * Load the registered logs for the activated settings.
	 *
	 * @return void
	 */
	public function logs_search() {
		if ( ! is_admin() ) {
			wp_die();
		}

		global $wpdb;
		$result = array();

		$data['param'] = '';
		$offset        = 0;
		$per_page      = 20;
		if ( isset( $_POST['param'] ) ) {
			$data['param'] = sanitize_key( $_POST['param'] );
		}

		if ( isset( $_POST['page'] ) ) {
			$data['page'] = intval( $_POST['page'] );
		}

		if ( intval( $data['page'] ) > 1 ) {
			$offset = ( intval( $data['page'] ) * $per_page ) - 1;
		}

		if ( $data['param'] == 'login' ) {
			$total  = $wpdb->get_results( 'SELECT count(id) as total FROM ' . $this->getTableLogs() . ' WHERE type = "login_logs"',
				ARRAY_A );
			$total  = $total[0]['total'];
			$result = $wpdb->get_results( "SELECT username as 'first_value', error as 'second_value', description as 'third_value', created_at as 'fourth_value' FROM " . $this->getTableLogs() . " WHERE type = 'login_logs' ORDER BY created_at DESC LIMIT " . $per_page . ' OFFSET ' . $offset . ';',
				ARRAY_A );
		}
		if ( $data['param'] == 'new-users' ) {
			$total  = $wpdb->get_results( 'SELECT count(id) as total FROM ' . $this->getTableLogs() . ' WHERE type = "users_logs"',
				ARRAY_A );
			$total  = $total[0]['total'];
			$result = $wpdb->get_results( "SELECT wpu.id as 'first_value', wpu.user_login as 'second_value', wpu.user_email as 'third_value', user_logs.created_at as 'fourth_value' FROM " . $this->getTableLogs() . ' as user_logs INNER JOIN ' . $wpdb->prefix . 'users wpu ON wpu . id = user_logs . user_id WHERE type = "users_logs" ORDER BY created_at DESC LIMIT ' . $per_page . ' OFFSET ' . $offset . ';',
				ARRAY_A );
		}
		if ( $data['param'] == 'plugin-logs' ) {
			$total  = $wpdb->get_results( 'SELECT count(id) as total FROM ' . $this->getTableLogs() . ' WHERE type = "plugin_logs"',
				ARRAY_A );
			$total  = $total[0]['total'];
			$result = $wpdb->get_results( "SELECT description as 'first_value', status as 'second_value', created_at as 'third_value' FROM " . $this->getTableLogs() . ' WHERE type = "plugin_logs" ORDER BY created_at DESC LIMIT ' . $per_page . ' OFFSET ' . $offset . ';',
				ARRAY_A );

			foreach ( $result as $key => $value ) {
				$result[ $key ]['second_value'] = $result[ $key ]['second_value'] == 'true' ? 'Actived' : 'Deactivate';
			}
		}
		if ( $data['param'] == 'activity-attacks' ) {
			$total = $wpdb->get_results( 'SELECT count(id) as total FROM ' . $this->activity_settings->getTableIpBlock() . ' WHERE suspect = true',
				ARRAY_A );
			$total = $total[0]['total'];
			$this->utils->send_report( 'hardening', 'ip_attacks', true, array( 'total' => strval( $total ) ) );
			$result = $wpdb->get_results( 'SELECT ip as "first_value", reason as "second_value", time as "third_value", path as "fourth_value" FROM ' . $this->activity_settings->getTableIpBlock() . ' WHERE suspect = true ORDER BY created_at DESC LIMIT ' . $per_page . ' OFFSET ' . $offset . ';',
				ARRAY_A );
		}
		if ( $data['param'] == 'activity-blocks' ) {
			$total = $wpdb->get_results( 'SELECT count(id) as total FROM ' . $this->activity_settings->getTableIpBlock() . ' WHERE block = true',
				ARRAY_A );
			$total = $total[0]['total'];
			$this->utils->send_report( 'hardening', 'ip_blocks', true, array( 'total' => strval( $total ) ) );
			$result = $wpdb->get_results( 'SELECT ip as "first_value", reason as "second_value", time as "third_value", path as "fourth_value" FROM ' . $this->activity_settings->getTableIpBlock() . ' WHERE block = true ORDER BY created_at DESC LIMIT ' . $per_page . ' OFFSET ' . $offset . ';',
				ARRAY_A );
		}

		printf(
			wp_json_encode(
				array(
					'status'   => '1',
					'total'    => $total,
					'per_page' => "$per_page",
					'page'     => $data['page'],
					'message'  => 'return result login error logs',
					'data'     => $result,
				)
			)
		);
		wp_die();
	}

	private function getTableLogs() {
		global $wpdb;

		return $wpdb->prefix . 'sec_logs';
	}

	/**
	 * Save installed plugins.
	 *
	 * @param string $plugin .
	 *
	 * @return void
	 */
	public function save_new_plugin_installed( $plugin ) {
		global $wpdb;
		$current_user = wp_get_current_user();

		$wpdb->query( 'INSERT INTO ' . $this->getTableLogs() . " (description, status, type, user_id) VALUES ('" . sanitize_text_field( $plugin ) . "', 'true', 'plugin_logs', " . $current_user->get( 'ID' ) . ')' );
	}

	/**
	 * Saves when a plugin is disabled
	 *
	 * @param string $plugin .
	 *
	 * @return void
	 */

	public function save_deactivate_plugin_installed( $plugin ) {
		global $wpdb;

		if ( strpos( $plugin, $this->plugin_name ) !== false ) {
			return;
		}
		$current_user = wp_get_current_user();

		$wpdb->query( 'INSERT INTO ' . $this->getTableLogs() . " (description, status, type, user_id) VALUES ('" . sanitize_text_field( $plugin ) . "', 'false', 'plugin_logs', " . $current_user->get( 'ID' ) . ')' );
	}

	/**
	 * Saves errors on login
	 *
	 * @param string $user .
	 * @param string|object $error .
	 *
	 * @return void
	 */
	public function save_login_errors( $user, $error = null ) {
		global $wpdb;
		if ( $error && $error->has_errors() ) {
			$wpdb->query( 'INSERT INTO ' . $this->getTableLogs() . " (error, description, username, type) VALUES ('" . sanitize_text_field( $error->get_error_code() ) . "', '" . sanitize_text_field( $error->get_error_message() ) . "', '" . sanitize_text_field( $user ) . "', 'login_logs')" );
		} else {
			if ( is_string( $user ) ) {
				$wpdb->query( 'INSERT INTO ' . $this->getTableLogs() . " (description, username, type) VALUES ('Login successful . ', '" . sanitize_text_field( $user ) . "', 'login_logs')" );
			} else {
				$wpdb->query( 'INSERT INTO ' . $this->getTableLogs() . " (user_id, description, username, type) VALUES ('" . $user->get( 'ID' ) . "', 'Login successful . ', '" . sanitize_text_field( $user->get( 'user_login' ) ) . "', 'login_logs')" );
			}
		}
	}

	/**
	 * Saves when a user is registered.
	 *
	 * @param string $user .
	 *
	 * @return void
	 */
	public function save_new_users( $user ) {
		global $wpdb;

		$wpdb->query( 'INSERT INTO ' . $this->getTableLogs() . " (user_id, type, status) VALUES ('" . sanitize_text_field( $user ) . "', 'users_logs', 'created')" );
	}

	/**
	 * Changes the hardening options.
	 *
	 * @return string
	 */
	public function change_options() {
		if ( ! is_admin() ) {
			wp_die();
		}

		$options = get_option( 'sec_logs_settings' );
		$param   = '';
		if ( isset( $_POST['param'] ) ) {
			$param = sanitize_key( $_POST['param'] );
		}

		if ( $options[ $param ] == '1' || $options[ $param ] == true ) {
			$report_value      = false;
			$options[ $param ] = '0';
		} else {
			$report_value      = true;
			$options[ $param ] = '1';
		}

		update_option( 'sec_logs_settings', $options );

		$this->utils->send_report( 'logs', $param, $report_value );

		printf( $this->get_tags( $options ) );

		wp_die();
	}

	public function count_logs_block_attack() {
		global $wpdb;

		$block = $wpdb->get_results( 'SELECT count(id) as total FROM ' . $this->activity_settings->getTableIpBlock() . ' WHERE block = true',
			ARRAY_A );
		$block = $block[0]['total'];

		$suspect = $wpdb->get_results( 'SELECT count(id) as total FROM ' . $this->activity_settings->getTableIpBlock() . ' WHERE suspect = true',
			ARRAY_A );
		$suspect = $suspect[0]['total'];

		return array(
			'block'   => $block,
			'suspect' => $suspect,
		);
	}
}
