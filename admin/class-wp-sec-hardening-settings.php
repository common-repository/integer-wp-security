<?php

class DevsoftIn_Wp_Sec_Hardening_Settings {

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
	 * Class Wp_Sec_Utils
	 *
	 * @access private
	 * @var class utils
	 */
	private $utils;

	/**
	 * Class Wp_Sec_Activity_Settings
	 *
	 * @access private
	 * @var class Wp_Sec_Activity_Settings
	 */
	private $activity_settings;

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
			$this->plugin_name . '_hardening',
			plugin_dir_url( __FILE__ ) . 'js/wp-sec-hardening.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name . '_hardening',
			'hardening_settings_ajax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			)
		);
	}

	/**
	 * Function that loads sessions from the hardening.
	 *
	 * @return void
	 */
	public function initialize_hardening() {

		$this->utils->send_report( 'tabs', 'sec_hardening_settings', true );

		if ( false == get_option( 'sec_hardening' ) ) {
			$default_array = $this->default_display_options();
			add_option( 'sec_hardening', $default_array );
		}

		add_settings_section(
			'hardening_section',
			__( 'Hardening settings', $this->plugin_name ),
			array( $this, 'general_options_callback' ),
			'sec_hardening'
		);

		add_settings_field(
			'disable_plugin_theme_editor',
			__( 'Disable Plugin Theme Editor', $this->plugin_name ),
			array( $this, 'disable_plugin_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		add_settings_field(
			'remove_version_wordpress',
			__( 'Hidden WordPress version', $this->plugin_name ),
			array( $this, 'remove_version_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		add_settings_field(
			'hardening_wp_debug',
			__( 'Allow WP Debug mode', $this->plugin_name ),
			array( $this, 'harding_allow_wp_debug_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		add_settings_field(
			'hardening_disable_directory',
			__( 'Disable directory browsing', $this->plugin_name ),
			array( $this, 'hardening_disable_directory_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		add_settings_field(
			'hardening_disable_xmlrpc',
			__( 'Disable xmlrpc, it protect of brute force and malicious use of this option', $this->plugin_name ),
			array( $this, 'hardening_disable_xmlrpc_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		add_settings_field(
			'hardening_author_page',
			__( 'Disable author page', $this->plugin_name ),
			array( $this, 'disable_redirect_author_page_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		add_settings_field(
			'hardening_permission',
			__( 'Corrects the permission of all your files and directories', $this->plugin_name ),
			array( $this, 'change_permissions_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		add_settings_field(
			'hardening_tables',
			__( 'Change the prefix of tables', $this->plugin_name ),
			array( $this, 'change_prefix_table_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		add_settings_field(
			'hardening_login_url',
			__( 'Change the login url', $this->plugin_name ),
			array( $this, 'change_login_url_callback' ),
			'sec_hardening',
			'hardening_section'
		);

		register_setting(
			'sec_hardening',
			'sec_hardening'
		);
	}

	/**
	 * Create an array with the attributes that will be used in this area.
	 *
	 * @return void
	 */
	public function default_display_options() {

		return array(
			'disable_plugin_theme_editor' => '',
			'remove_version_wordpress'    => '',
			'hardening_wp_debug'          => WP_DEBUG,
			'hardening_disable_directory' => '',
			'hardening_disable_xmlrpc'    => '',
			'hardening_author_page'       => '',
		);
	}

	/**
	 * Displays a description for the page.
	 *
	 * @return void
	 */
	public function general_options_callback() {
		printf(
			'<p>%s</p>',
			esc_html__(
				'Protect your WordPress with Disable Plugin Theme Editor and Hidden WordPress version',
				$this->plugin_name
			)
		);
	}

	/**
	 * Change_permissions
	 *
	 * @return void
	 */
	public function change_permissions_callback() {
		printf(
			"<p>%s 
                <a href='https://wordpress.org/support/article/hardening-wordpress/#changing-file-permissions' target='_black'>
                	%s
                </a>
            </p>
	        <input type='button' class='button' id='fix_permission'  name='fix_permission' value='Fix permissions' />",
			esc_html__( 'The permissions suggested by WordPress, as you can', $this->plugin_name ),
			esc_html__( 'read here.', $this->plugin_name )
		);
	}

	/**
	 * Display change prefix table.
	 *
	 * @return void
	 */
	public function change_prefix_table_callback() {
		global $wpdb;

		printf(
			"<p>%s</p>
                <input type='hidden' id='old_prefix_name'  name='old_prefix_name' value='%s' />
                <input type='text' id='prefix_name'  name='prefix_name' value='%s' />
                <input type='button' class='button' id='change_name'  name='change_name' value='%s' />",
			esc_html__(
				'Changing the prefix of your WordPress tables can bring you more security',
				$this->plugin_name
			),
			esc_attr( $wpdb->prefix ),
			esc_attr( $wpdb->prefix ),
			esc_attr__( 'Change prefix', $this->plugin_name )
		);
	}

	/**
	 * Display change login url.
	 *
	 * @return void
	 */
	public function change_login_url_callback() {

		$login_url = get_option( 'sec_hardening_login_url' ) ? get_option( 'sec_hardening_login_url' ) : 'wp-login.php';

		printf(
			"<p>%s</p>
                <input type='hidden' id='old_login_url'  name='old_login_url' value='%s' />
		        <input type='text' id='login_url'  name='login_url' value='%s'/>
                <input type='button' class='button' id='change_login_url'  name='change_login_url' value='Change login url' />&nbsp&nbsp
		        <input type='button' class='button reset_button' id='reset_login_url'  name='reset_login_url' value='%s' />",
			esc_html__(
				'Changing the default login url of WordPress this is can bring you more security',
				$this->plugin_name
			),
			esc_attr( $login_url ),
			esc_attr( $login_url ),
			esc_attr__( 'Reset login url', $this->plugin_name )
		);
	}

	/**
	 * Disables theme editing
	 *
	 * @return void
	 */
	public function disable_plugin_callback() {
		$this->utils->print_option_switch( 'disable_plugin_theme_editor', 'sec_hardening' );
	}

	/**
	 * Removes the WordPress version on the frontend.
	 *
	 * @return void
	 */
	public function remove_version_callback() {
		$this->utils->print_option_switch( 'remove_version_wordpress', 'sec_hardening' );
	}

	/**
	 * Enables and disables WP_DEBUG.
	 *
	 * @return void
	 */
	public function harding_allow_wp_debug_callback() {
		$this->utils->print_option_switch( 'hardening_wp_debug', 'sec_hardening' );
	}

	/**
	 * Enable and disable redirect to author page.
	 *
	 * @return void
	 */
	public function disable_redirect_author_page_callback() {
		$this->utils->print_option_switch( 'hardening_author_page', 'sec_hardening' );
	}

	/**
	 * Disables access to WordPress directories in the browser.
	 *
	 * @return void
	 */
	public function hardening_disable_directory_callback() {
		$this->utils->print_option_switch( 'hardening_disable_directory', 'sec_hardening' );
	}

	/**
	 * Disables xmlrpc to WordPress api.
	 *
	 * @return void
	 */
	public function hardening_disable_xmlrpc_callback() {
		$this->utils->print_option_switch( 'hardening_disable_xmlrpc', 'sec_hardening' );
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

		$options = get_option( 'sec_hardening' );
		$param   = '';
		if ( isset( $_POST['param'] ) ) {
			$param = sanitize_key( $_POST['param'] );
		}

		if ( $options[ $param ] == '1' || $options[ $param ] == true ) {
			$options[ $param ] = '0';
		} else {
			$options[ $param ] = '1';
		}

		update_option( 'sec_hardening', $options );

		$this->active_hardening( $param, $options[ $param ] );

		return $options[ $param ];
	}

	private function active_hardening( $option, $value ) {
		$value = $value == '1' ? true : false;

		$this->utils->send_report( 'hardening', $option, $value );

		switch ( $option ) {
			case 'disable_plugin_theme_editor':
				$this->utils->changeConfigFile( $value, 'DISALLOW_FILE_EDIT' );
			case 'hardening_wp_debug':
				$this->utils->changeConfigFile( $value, 'WP_DEBUG' );
			case 'hardening_disable_directory':
				$this->utils->disableDirectoryBrowsing( $value );
			case 'hardening_disable_xmlrpc':
				$this->utils->disableXlmrpc( $value );
		}
	}

	/**
	 * Changes the permission of WordPress directories and files.
	 *
	 * @return void
	 */
	public function change_permission_files() {
		if ( ! is_admin() ) {
			wp_die();
		}

		if ( ! is_writable( ABSPATH ) ) {
			printf(
				wp_json_encode(
					array(
						'status'  => '0',
						'action'  => 'change_permission_files',
						'message' => 'The system user is not allowed to change permissions on wordpress folders and files.',
						'data'    => array(),
					)
				)
			);
			wp_die();
		}

		$this->execute_change_permission_files();

		$this->utils->send_report( 'hardening', 'change_permission_files', true );

		printf(
			wp_json_encode(
				array(
					'status'  => '1',
					'action'  => 'change_permission_files',
					'message' => 'Congratulations, all of your WordPress files have the expected permissions.',
					'data'    => array(),
				)
			)
		);
		wp_die();
	}

	public function execute_change_permission_files() {
		$dir   = 'find ' . ABSPATH . ' -type d -exec chmod 755 {} \;';
		$files = 'find ' . ABSPATH . ' -type f -exec chmod 644 {} \;';

		shell_exec( $dir );
		shell_exec( $files );
	}

	/**
	 * Change the prefix table name
	 *
	 * @return void
	 */
	public function change_prefix_tables() {
		if ( ! is_admin() ) {
			wp_die();
		}

		global $wpdb;
		$old_prefix     = $wpdb->prefix;
		$data['prefix'] = '';
		if ( isset( $_POST['prefix'] ) ) {
			$data['prefix'] = sanitize_key( $_POST['prefix'] );
		}

		if ( ! $data['prefix'] || trim( $data['prefix'] ) == '' || trim( $data['prefix'] ) === $old_prefix ) {
			$this->utils->send_report( 'hardening', 'change_prefix_tables', false );
			printf(
				wp_json_encode(
					array(
						'status'  => '0',
						'action'  => 'change_prefix_tables',
						'message' => 'A valid prefix was not passed.',
						'data'    => array(),
					)
				)
			);
			wp_die();
		}

		$new_prefix = trim( $data['prefix'] );
		if ( substr( $new_prefix, - 1 ) != '_' ) {
			$new_prefix = $new_prefix . '_';
		}

		$sql    = 'SHOW TABLES FROM ' . DB_NAME;
		$tables = $wpdb->get_results( $sql, ARRAY_A );

		$tables_with_prefix = array();

		$tables_dbname = 'Tables_in_' . DB_NAME;


		$this->utils->change_config_prefix( $new_prefix );

		foreach ( $tables as $key => $table ) {
			$old_name = $table[ $tables_dbname ];
			if ( substr( $old_name, 0, strlen( $old_prefix ) ) === $old_prefix ) {
				$new_name = str_replace( $old_prefix, $new_prefix, $old_name );
				$wpdb->get_results( 'RENAME TABLE ' . $old_name . ' TO ' . $new_name . ';' );
			}
		}
		$wpdb->get_results( 'UPDATE ' . $new_prefix . 'options SET option_name = REPLACE(option_name, "' . $old_prefix . '", "' . $new_prefix . '") WHERE option_name LIKE "' . $old_prefix . '%";' );
		$wpdb->get_results( 'UPDATE ' . $new_prefix . 'usermeta SET meta_key = REPLACE(meta_key, "' . $old_prefix . '", "' . $new_prefix . '") WHERE meta_key LIKE "' . $old_prefix . '%";' );

		$this->utils->send_report( 'hardening', 'change_prefix_tables', true );

		printf(
			wp_json_encode(
				array(
					'status'  => '1',
					'action'  => 'change_prefix_tables',
					'message' => 'Prefix changed successfully.',
					'data'    => array(),
				)
			)
		);
		wp_die();
	}

	/**
	 * Reset the WordPress login url
	 *
	 * @return void
	 */
	public function reset_login_url() {
		if ( ! is_admin() ) {
			wp_die();
		}

		delete_option( 'sec_hardening_login_url' );

		$this->utils->send_report( 'hardening', 'reset_login_url', true );

		printf(
			wp_json_encode(
				array(
					'status'  => '1',
					'action'  => 'reset_login_url',
					'message' => 'Reset login url successfully.',
					'data'    => array(),
				)
			)
		);
		wp_die();
	}

	/**
	 * Change the WordPress login url
	 *
	 * @return void
	 */
	public function change_login_url() {
		if ( ! is_admin() ) {
			wp_die();
		}

		$url_old           = esc_url_raw( get_site_option( 'sec_hardening_login_url', 'login' ) );
		$data['login_url'] = filter_input( INPUT_POST, 'login_url', FILTER_SANITIZE_URL );
		if ( ! $data['login_url']
		     || trim( $data['login_url'] ) == ''
		     || trim( $data['login_url'] ) === $url_old
		     || strpos( trim( $data['login_url'] ), 'wp-admin' ) !== false
		     || strpos( trim( $data['login_url'] ), 'wp-login' ) !== false
		     || strpos( trim( $data['login_url'] ), 'wp-includes' ) !== false
		) {
			$this->utils->send_report( 'hardening', 'change_login_url', false );
			printf(
				wp_json_encode(
					array(
						'status'  => '0',
						'action'  => 'change_login_url',
						'message' => 'New url login is invalid.',
						'data'    => array(),
					)
				)
			);
			wp_die();
		}

		update_site_option( 'sec_hardening_login_url', trim( $data['login_url'] ) );

		$this->utils->send_report( 'hardening', 'change_login_url', true );

		printf(
			wp_json_encode(
				array(
					'status'  => '1',
					'action'  => 'change_login_url',
					'message' => 'Login url changed successfully.',
					'data'    => array(),
				)
			)
		);
		wp_die();
	}

	/**
	 * Check the url login and change to new url login
	 *
	 * @return void
	 */
	public function wp_loaded() {
		if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) ) {
			wp_die( __( 'You must log in to access the admin area.', 'change-wp-admin-login' ) );
		}

		global $pagenow;
		$wp_page_now = $pagenow;

		$request                 = parse_url( $_SERVER['REQUEST_URI'] );
		$sec_hardening_login_url = get_site_option( 'sec_hardening_login_url', 'login' );

		if ( ( untrailingslashit( $request['path'] ) === '/wp-login.php' ||
		       untrailingslashit( $request['path'] ) === '/login' ) ) {

			if ( ! defined( 'WP_USE_THEMES' ) ) {
				define( 'WP_USE_THEMES', true );
			}

			wp();

			require_once ABSPATH . WPINC . '/template-loader.php';

			die();
		} elseif ( untrailingslashit( $request['path'] ) === home_url( $sec_hardening_login_url, 'relative' ) ) {
			global $error, $interim_login, $action, $user_login;

			@require_once ABSPATH . 'wp-login.php';

			die;
		}
	}

	/**
	 * Wp_hardening_login_error.
	 *
	 * @return void
	 */
	public function wp_hardening_login_error( $user, $error = null ) {
		$ip = $this->utils->get_user_ip( $_SERVER );

		$options = get_option( 'sec_activity' );

		$content = array(
			'ip'         => $ip,
			'path'       => $_SERVER['REQUEST_URI'],
			'time'       => date( 'Y-m-d H:i:s' ),
			'time_block' => date( 'Y-m-d H:i:s', strtotime( $options['activity_blocking_time'] . ' hour' ) ),
			'reason'     => 'login wrong',
			'suspect'    => true,
			'block'      => false,
			'level'      => 1,
		);

		if ( $this->utils->suspicious_access( $content, intval( $options['activity_time_wrong_login'] ) ) ) {
			$content['suspect'] = false;
			$content['block']   = true;
			$this->activity_settings->insert_ip_block( $content );
			$this->utils->file_list_ip_blocks( $content, false, $ip );
			wp_die( __( 'You are temporarily blocked.', 'integer-wp-security' ) );
		} else {
			$this->activity_settings->insert_ip_block( $content );
		}
	}

	/**
	 * Wp_loaded ip block
	 *
	 * @return void
	 */
	public function wp_loaded_ip_block() {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}

		$ip = $this->utils->get_user_ip( $_SERVER );

		if ( $this->utils->check_ip_block( $ip ) ) {
			wp_die( __( 'You are temporarily blocked.', 'integer-wp-security' ) );
		}

		$options = get_option( 'sec_activity' );

		$allow = $this->utils->check_access_allow( parse_url( $_SERVER['REQUEST_URI'] ),
			trim( get_option( 'sec_hardening_login_url' ) ) );

		if ( $allow === 0 || ! $allow ) {
			return;
		}

		$content = array(
			'ip'         => $ip,
			'path'       => $_SERVER['REQUEST_URI'],
			'time'       => date( 'Y-m-d H:i:s' ),
			'time_block' => date( 'Y-m-d H:i:s', strtotime( $options['activity_blocking_time'] . ' hour' ) ),
			'reason'     => 'page fail',
			'level'      => 1,
			'suspect'    => true,
			'block'      => false,
		);

		$suspicious = $allow ? $this->utils->suspicious_access( $content,
			intval( $options['activity_time_block_ip'] ) ) : false;

		if ( $allow === 1 && $suspicious ) {
			$content['suspect'] = false;
			$content['block']   = true;
			$this->activity_settings->insert_ip_block( $content );
			$this->utils->file_list_ip_blocks( $content, false, $ip );
			wp_die( __( 'You are temporarily blocked.', 'integer-wp-security' ) );
		} else {
			$this->activity_settings->insert_ip_block( $content );
		}
	}

	/**
	 * Hidden author page redirect
	 *
	 * @return void
	 */
	public function author_page_redirect() {
		if ( is_author() ) {
			wp_redirect( home_url() );
		}
	}

	/**
	 * Filters the site URL .
	 *
	 * @param string $url .
	 * @param string $path .
	 * @param string $scheme .
	 *
	 * @return string
	 */
	public function site_url( $url, $path, $scheme ) {
		return $this->filter_wp_url_login_php( $url, $scheme );
	}

	/**
	 * Filter the url to new login url.
	 *
	 * @param string $url .
	 * @param string $scheme .
	 *
	 * @return string
	 */
	private function filter_wp_url_login_php( $url, $scheme = null ) {
		if ( strpos( $url, 'wp-login.php' ) !== false ) {
			if ( is_ssl() ) {
				$scheme = 'https';
			}

			$args = explode( '?', $url );

			if ( isset( $args[1] ) ) {
				parse_str( $args[1], $args );
				$url = add_query_arg( $args, $this->new_login_url( $scheme ) );
			} else {
				$url = $this->new_login_url( $scheme );
			}
		}

		return $url;
	}

	/**
	 * New Login Url
	 *
	 * @param null $scheme
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function new_login_url( $scheme = null ) {
		if ( get_option( 'permalink_structure' ) ) {
			return trailingslashit( home_url( '/', $scheme ) . get_site_option( 'sec_hardening_login_url', 'login' ) );
		}

		return home_url( '/', $scheme ) . '?' . get_site_option( 'sec_hardening_login_url', 'login' );

	}

	/**
	 * Filters the network site URL.
	 *
	 * @param string $url .
	 * @param string $path .
	 * @param string $scheme .
	 *
	 * @return string
	 */
	public function network_site_url( $url, $path, $scheme ) {
		return $this->filter_wp_url_login_php( $url, $scheme );
	}

	/**
	 * Filters the redirect location.
	 *
	 * @param string $location .
	 * @param string $status .
	 *
	 * @return string
	 */
	public function wp_redirect( $location, $status ) {
		return $this->filter_wp_url_login_php( $location );
	}

	/**
	 * Welcome email.
	 *
	 * @param string $value .
	 *
	 * @return string
	 */
	public function welcome_email( $value ) {
		return $value = str_replace( 'wp-login.php',
			trailingslashit( get_site_option( 'sec_hardening_login_url', 'login' ) ), $value );
	}

	/**
	 * User trailingslashit
	 *
	 * @param string $string .
	 *
	 * @return mixed
	 */
	private function user_trailingslashit( $string ) {
		return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );
	}

	/**
	 * Use trailing slashes
	 *
	 * @return bool
	 */
	private function use_trailing_slashes() {
		return '/' === substr( get_option( 'permalink_structure' ), - 1, 1 );
	}
}
