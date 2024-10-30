<?php

class DevsoftIn_Wp_Sec_Gdpr_Settings {

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
	 * Load Dependencies
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-utils.php';
		$this->utils = new DevsoftIn_Wp_Sec_Utils();
	}

	/**
	 * Enqueue Scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name . '_gdpr',
			plugin_dir_url( __FILE__ ) . 'js/wp-sec-gdpr.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name . '_gdpr',
			'gdpr_settings_ajax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			)
		);
	}

	/**
	 * Function that loads sessions from the gdpr.
	 *
	 * @return void
	 */
	public function initialize_gdpr_options() {
		if ( false == get_option( 'sec_report' ) ) {
			$default_array = $this->default_display_options();
			add_option( 'sec_report', $default_array );
		}

		add_settings_section(
			'gdpr_settings_section',
			__( 'GDPR Options', $this->plugin_name ),
			array( $this, 'general_options_callback' ),
			'sec_report'
		);

		add_settings_field(
			'email_method',
			__( 'GDPR', $this->plugin_name ),
			array( $this, 'method_gdpr_callback' ),
			'sec_report',
			'gdpr_settings_section'
		);

		register_setting(
			'sec_report',
			'sec_report'
		);
	}

	/**
	 * Create an array with the attributes that will be used in this area.
	 *
	 * @return array
	 */
	public function default_display_options() {

		$defaults = array(
			'active'   => true,
			'host_key' => $this->utils->register_host( true ),
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
			'<p>%s</p>',
			esc_html__(
				'We can send the data generated in the plugin to our database so that we can use it as feedback to evolve the plugin.',
				$this->plugin_name
			)
		);
	}

	/**
	 * Gdpr Method Callback
	 *
	 * @since 1.0.0
	 */
	public function method_gdpr_callback() {
		$this->utils->print_option_switch( 'active', 'sec_report' );
	}

	/**
	 * Change options
	 *
	 * @return string
	 */
	public function change_options() {
		if ( ! is_admin() ) {
			wp_die();
		}

		$options = get_option( 'sec_report' );
		$param   = '';
		if ( isset( $_POST['param'] ) ) {
			$param = sanitize_key( $_POST['param'] );
		}

		if ( $options[ $param ] == '1' || $options[ $param ] == true ) {
			$options[ $param ] = '0';
		} else {
			$options[ $param ] = '1';
		}

		update_option( 'sec_report', $options );

		return $options[ $param ];
	}
}
