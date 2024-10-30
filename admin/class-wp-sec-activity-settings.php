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
class DevsoftIn_Wp_Sec_Activity_Settings {

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
			$this->plugin_name . '_activity',
			plugin_dir_url( __FILE__ ) . 'js/wp-sec-activity.js',
			array( 'jquery' ),
			$this->version,
			false
		);
		wp_localize_script(
			$this->plugin_name . '_activity',
			'activity_settings_ajax',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			)
		);
	}

	/**
	 * Loads the session and settings to use the activity.
	 *
	 * @return void
	 */
	public function initialize_activity_settings() {
		if ( false == get_option( 'sec_activity' ) ) {
			$default_array = $this->default_display_options();
			add_option( 'sec_activity', $default_array );
		}

		add_settings_section(
			'activity_settings_section',
			__( 'Activity Options', $this->plugin_name ),
			array( $this, 'general_options_callback' ),
			'sec_activity'
		);

		add_settings_field(
			'activity_comment_black_word',
			__( 'Blocks comments with spam', $this->plugin_name ),
			array( $this, 'check_to_block_comment_black_word' ),
			'sec_activity',
			'activity_settings_section'
		);

		add_settings_field(
			'activity_check_to_block_ip',
			__( 'Check to block ip', $this->plugin_name ),
			array( $this, 'check_to_block_ip' ),
			'sec_activity',
			'activity_settings_section'
		);

		add_settings_field(
			'activity_wrong_login',
			__( 'Check to block ip from wrong login', $this->plugin_name ),
			array( $this, 'check_wrong_login' ),
			'sec_activity',
			'activity_settings_section'
		);

		add_settings_field(
			'activity_blocking_time',
			__( 'Define time to blocking', $this->plugin_name ),
			array( $this, 'check_blocking_time' ),
			'sec_activity',
			'activity_settings_section'
		);

		register_setting(
			'sec_activity',
			'sec_activity'
		);
	}

	/**
	 * Create an array with the attributes that will be used in this area.
	 *
	 * @return array
	 */
	public function default_display_options() {

		return array(
			'activity_check_to_block_ip'  => '',
			'activity_wrong_login'        => '',
			'activity_comment_black_word' => '',
			'activity_time_block_ip'      => '5',
			'activity_time_wrong_login'   => '5',
			'activity_blocking_time'      => '24',
			'activity_time_comment_spam'  => '0',
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
			esc_html__( 'Protect your WordPress with Activity.', $this->plugin_name )
		);
	}

	/**
	 * Check_to_block_ip
	 *
	 * @return void
	 */
	public function check_to_block_ip() {
		$options        = get_option( 'sec_activity' );
		$check_block_ip = '';
		$time_block_ip  = '5';
		if ( ! empty( $options['activity_time_wrong_login'] ) ) {
			$time_block_ip = $options['activity_time_wrong_login'];
		}
		if ( ! empty( $options['activity_check_to_block_ip'] ) ) {
			$check_block_ip = $options['activity_check_to_block_ip'];
		}

		$setting_checked = ( 1 == $check_block_ip ) ? 'checked' : '';


		printf(
			"<label class='switch'>
                 <input type='checkbox' id='activity_check_to_block_ip' 
                    name='sec_activity[activity_check_to_block_ip]' value='1' %s />
                 <span class='slider round '></span>
             </label><br/><br/><p>%s</p>
                <input type='number' id='time_block_ip'  name='time_block_ip' value='%s' /> &nbsp&nbsp
                <input type='button' class='button' id='change_time_block_ip'  name='change_time_block_ip' value='%s' /> &nbsp&nbsp",
			esc_attr__( $setting_checked ),
			esc_html__( 'Changing the default value for the number of times an ip tries to access',
				$this->plugin_name ),
			esc_attr( $time_block_ip ),
			esc_html__( 'Change', $this->plugin_name )
		);

	}

	/**
	 * Check_wrong_login
	 *
	 * @return void
	 */
	public function check_wrong_login() {
		$options = get_option( 'sec_activity' );

		$check_blockLogin = isset( $options['activity_wrong_login'] ) ? (int) $options['activity_wrong_login'] : '';

		$setting_checked = ( 1 == $check_blockLogin ) ? 'checked' : '';

		$time_block_ip = '5';
		if ( ! empty( $options['activity_time_wrong_login'] ) ) {
			$time_block_ip = $options['activity_time_wrong_login'];
		}

		printf(
			"<label class='switch'>
            <input type='checkbox' id='activity_wrong_login' name='sec_activity[activity_wrong_login]' value='1' %s />
            <span class='slider round '></span>
            </label><br/><br/><p>%s</p>
                <input type='number' id='time_wrong_login'  name='time_wrong_login' value='%s' /> &nbsp&nbsp
                <input type='button' class='button' id='change_time_wrong_login'  name='change_time_wrong_login' value='%s' /> &nbsp&nbsp",
			esc_attr( $setting_checked ),
			esc_html__(
				'Changing the default value for the number of times that an error can happen on login',
				$this->plugin_name
			),
			esc_attr( $time_block_ip ),
			esc_attr__( 'Change', $this->plugin_name )
		);
	}

	/**
	 * Check_wrong_login
	 *
	 * @return void
	 */
	public function check_blocking_time() {
		$options = get_option( 'sec_activity' );

		$blocking_time = isset( $options['activity_blocking_time'] ) ? $options['activity_blocking_time'] : '24';

		?>
        <select name="blocking_time" id="activity_blocking_time" class="select_blocking_time">
            <option <?php selected( $blocking_time, '1', true ); ?> value="1">1 Hora</option>
            <option <?php selected( $blocking_time, '6', true ); ?> value="6">6 Horas</option>
            <option <?php selected( $blocking_time, '12', true ); ?> value="12">12 Horas</option>
            <option <?php selected( $blocking_time, '24', true ); ?> value="24">1 Dia</option>
            <option <?php selected( $blocking_time, '48', true ); ?> value="48">2 Dia</option>
            <option <?php selected( $blocking_time, '72', true ); ?> value="72">3 Dia</option>
            <option <?php selected( $blocking_time, '144', true ); ?> value="144">6 Dia</option>
        </select>
		<?php
	}

	/**
	 * Check to block comment black word
	 *
	 * @return void
	 */
	public function check_to_block_comment_black_word() {
		$options            = get_option( 'sec_activity' );
		$black_word_comment = '';
		$time_comment_spam  = '0';
		if ( ! empty( $options['activity_comment_black_word'] ) ) {
			$black_word_comment = $options['activity_comment_black_word'];
		}
		if ( ! empty( $options['activity_time_comment_spam'] ) ) {
			$time_comment_spam = $options['activity_time_comment_spam'];
		}

		$setting_checked = ( 1 == $black_word_comment ) ? 'checked' : '';

		printf(
			"<label class='switch'>" .
			"<input type='checkbox' id='activity_comment_black_word' name='sec_activity[activity_comment_black_word]' value='1' %s />" .
			"<span class='slider round '></span>" .
			'</label>',
			esc_attr( $setting_checked )
		);

		printf(
			'<br/><br/><p>Changing the default value for the number of times that user can send spam</p>' .
			"<input type='number' id='time_comment_spam'  name='time_comment_spam' value='%s' /> &nbsp&nbsp" .
			"<input type='button' class='button' id='change_time_comment_spam'  name='change_time_comment_spam' value='Change' /> &nbsp&nbsp",
			esc_attr( $time_comment_spam )
		);
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

		$options = get_option( 'sec_activity' );
		$param   = '';

		if ( isset( $_POST['param'] ) ) {
			$param = sanitize_key( $_POST['param'] );
		}

		$activity_time_block_ip = filter_input(
			INPUT_POST,
			'activity_time_block_ip',
			FILTER_SANITIZE_NUMBER_INT
		);

		$activity_time_wrong_login = filter_input(
			INPUT_POST,
			'activity_time_wrong_login',
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);

		$activity_blocking_time = filter_input(
			INPUT_POST,
			'activity_blocking_time',
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);

		$activity_time_comment_spam = filter_input(
			INPUT_POST,
			'activity_time_comment_spam',
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);

		if ( ( ! empty( $param ) && $options[ $param ] == '1' ) || $options[ $param ] == true ) {
			$options[ $param ] = '0';
			$this->utils->send_report( 'hardening', $param, false );
		} elseif ( ! empty( $param ) ) {
			$options[ $param ] = filter_var( '1', FILTER_VALIDATE_FLOAT );
			$this->utils->send_report( 'hardening', $param, true );
		} elseif ( ! empty( $activity_time_block_ip ) ) {
			$options['activity_time_block_ip'] = filter_var( $activity_time_block_ip, FILTER_VALIDATE_FLOAT );
		} elseif ( ! empty( $activity_time_wrong_login ) ) {
			$options['activity_time_wrong_login'] = filter_var( $activity_time_wrong_login, FILTER_VALIDATE_FLOAT );
		} elseif ( ! empty( $activity_blocking_time ) ) {
			$options['activity_blocking_time'] = filter_var( $activity_blocking_time, FILTER_VALIDATE_FLOAT );
		} elseif ( ! empty( $activity_time_comment_spam ) ) {
			$options['activity_time_comment_spam'] = filter_var( $activity_time_comment_spam, FILTER_VALIDATE_FLOAT );
		}

		update_option( 'sec_activity', $options );

		return $options[ $param ];
	}

	/**
	 * Pre comment approved, check if have black words and link in comment.
	 *
	 * @param [type] $approved .
	 * @param [type] $commentdata .
	 *
	 * @return int|string
	 */
	public function pre_comment_approved( $approved, $commentdata ) {

		$ip = $this->utils->get_user_ip( $_SERVER );

		if ( $this->utils->check_ip_block( $ip ) ) {
			return 'spam';
		}

		$black_words = array(
			'pharmacy',
			'viagra',
			'Canadian',
			'sex',
			'Bukkake',
			'Youtube video downloader',
			'ciallis',
			'tadalafil',
			'amoxicillin',
			'lasix',
			'levitra',
			'cytotec',
			'vardenafil',
		);

		$comment = strtolower( $commentdata['comment_content'] );

		foreach ( $black_words as $word ) {
			if ( false !== strpos( $comment, strtolower( $word ) ) &&
			     ( false !== strpos( $comment, 'http' ) || false !== strpos( $comment, 'www' ) ) ) {
				$approved = 'spam';

				$this->block_comment_ip( $ip );
			}
		}

		return $approved;
	}

	private function block_comment_ip( $ip ) {
		$options = get_option( 'sec_activity' );
		$content = array(
			'ip'         => $ip,
			'path'       => $this->request_uri_comment(),
			'time'       => date( 'Y-m-d H:i:s' ),
			'time_block' => date( 'Y-m-d H:i:s', strtotime( $options['activity_blocking_time'] . ' hour' ) ),
			'reason'     => 'span in comments',
			'level'      => 1,
			'suspect'    => true,
			'block'      => false,
		);
		if ( $this->utils->suspicious_access( $content, intval( $options['activity_time_comment_spam'] ) ) ) {
			$content['suspect'] = false;
			$content['block']   = true;
			$this->insert_ip_block( $content );
			$this->utils->file_list_ip_blocks( $content, false, $ip );
		} else {
			$this->insert_ip_block( $content );
		}
	}

	private function request_uri_comment() {

		if ( isset( $_SERVER['HTTP_REFERER'] ) && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			return parse_url( $_SERVER['HTTP_REFERER'] )['path'];
		}

		return '';
	}

	public function insert_ip_block( $content ) {
		global $wpdb;

		$wpdb->query(
			'INSERT INTO ' . $this->getTableIpBlock() . " (ip, time, time_block, reason, level, suspect, block, path) 
			VALUES ('" . sanitize_text_field( $content['ip'] ) . "', '" . date( $content['time'] ) . "', '" . date( $content['time_block'] ) . "', '" . sanitize_text_field( $content['reason'] ) . "', '" . sanitize_text_field( $content['level'] ) . "', '" . sanitize_text_field( $content['suspect'] ) . "', '" . sanitize_text_field( $content['block'] ) . "', '" . sanitize_text_field( $content['path'] ) . "')"
		);

		//Used to email the admin about the IP blocked in system
		$options_email_notification = get_option( 'sec_email_notification' );
		if (
			isset( $options_email_notification['all_email_notification'] )
			&& $options_email_notification['all_email_notification'] == '1'
			&& $content['block'] == '1'
		) {
			$this->ip_block_notification_to_admin( [
				sanitize_text_field( $content['ip'] ),
				date( $content['time'] ),
				sanitize_text_field( $content['reason'] )
			] );
		}
	}

	public function getTableIpBlock() {
		global $wpdb;

		return $wpdb->prefix . 'sec_ip_block';
	}

	/**
	 * Calls send_email_to_admin() in class-wp-sec-utils.php
	 *
	 * @param $params array Contains IP, Time and Reason of the block
	 */
	public function ip_block_notification_to_admin( $params ) {
		$this->utils->send_email_to_admin(
			'IP blocked on your system',
			'The IP <b>%s</b> was blocked at <b>%s</b> for the reason <b>%s</b>',
			$params
		);
	}
}
