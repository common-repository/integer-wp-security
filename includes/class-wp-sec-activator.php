<?php

/**
 * Fired during plugin activation
 *
 * @link       https://integer.pt
 * @since      1.0.0
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Sec
 * @subpackage Wp_Sec/includes
 * @author     Integer Consulting <info@integer.pt>
 */
class Wp_Sec_Activator {
	const TABLE_LOGS = 'sec_logs';
	const TABLE_MALWARE = 'sec_malware';
	const TABLE_IP_BLOCK = 'sec_ip_block';
	const TABLE_DASHBOARD_FIELDS = 'sec_dashboard_fields';

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::register_table_logs();
		self::register_table_malware();
		self::register_table_ip_block();
		self::register_table_dashboard();
		self::enable_options_hardening();
		self::enable_optins_logs();
		self::enable_optins_activity();
		self::enable_options_email();
	}

	/**
	 *  Register log table
	 *
	 * This method register log tables to save datas.
	 *
	 * @since    1.0.0
	 */
	private static function register_table_logs() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$table_logs = $wpdb->prefix . self::TABLE_LOGS;

		if ( $wpdb->get_var( "Show tables like '" . $table_logs . "'" ) !== $table_logs ) {
			$table_query_logs = 'CREATE TABLE `' . $table_logs . '` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`user_id` bigint DEFAULT NULL,
					`username` varchar(150) DEFAULT NULL,
					`status` varchar(150) DEFAULT NULL,
					`description` text DEFAULT NULL,
					`error` varchar(150) DEFAULT NULL,
					`type` varchar(150) NOT NULL,
					`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`) 
				)';
			dbDelta( $table_query_logs );
		}
	}

	/**
	 *  Register malware table
	 *
	 * This method register malware table to save datas.
	 *
	 * @since    1.0.0
	 */
	private static function register_table_malware() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$table_malware = $wpdb->prefix . self::TABLE_MALWARE;

		if ( $wpdb->get_var( "Show tables like '" . $table_malware . "'" ) !== $table_malware ) {
			$table_query_logs = 'CREATE TABLE `' . $table_malware . '` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`user_id` bigint DEFAULT NULL,
					`count_checked_files` int DEFAULT NULL,
					`count_corrupted_files` int DEFAULT NULL,
					`count_suspicious_file` int DEFAULT NULL,
					`json_corrupted_files` text DEFAULT NULL,
					`json_suspicious_file` text DEFAULT NULL,
					`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`) 
				)';
			dbDelta( $table_query_logs );
		}
	}

	/**
	 *  Register ip block table
	 *
	 * This method register ip block table to save datas.
	 *
	 * @since    1.0.0
	 */
	private static function register_table_ip_block() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$table_ip_block = $wpdb->prefix . self::TABLE_IP_BLOCK;

		if ( $wpdb->get_var( "Show tables like '" . $table_ip_block . "'" ) !== $table_ip_block ) {
			$table_query_logs = 'CREATE TABLE `' . $table_ip_block . '` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`ip` text DEFAULT NULL,
					`time` timestamp NOT NULL,
					`time_block` timestamp NOT NULL,
					`reason` text DEFAULT NULL,
					`level` text DEFAULT NULL,
					`suspect` boolean NOT NULL,
					`block` boolean NOT NULL,
					`path` text DEFAULT NULL,
					`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`) 
				)';
			dbDelta( $table_query_logs );
		}
	}

	/**
	 *  Register ip block table
	 *
	 * This method register ip block table to save datas.
	 *
	 * @since    1.0.0
	 */
	private static function register_table_dashboard() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$dashboard_fields = $wpdb->prefix . self::TABLE_DASHBOARD_FIELDS;

		if ( $wpdb->get_var( "Show tables like '" . $dashboard_fields . "'" ) !== $dashboard_fields ) {
			$table_query_logs = 'CREATE TABLE `' . $dashboard_fields . '` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`type` text DEFAULT NULL,
					`value` text DEFAULT NULL,
					`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id`) 
				)';
			dbDelta( $table_query_logs );
		}
	}

	/**
	 * Enable all options hardening
	 *
	 * @return void
	 */
	private static function enable_options_hardening() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-utils.php';
		$utils = new DevsoftIn_Wp_Sec_Utils();

		$hardening = array(
			'disable_plugin_theme_editor' => '1',
			'remove_version_wordpress'    => '1',
			'hardening_wp_debug'          => true,
			'hardening_disable_directory' => '1',
			'hardening_author_page'       => '1',
			'hardening_check_to_block_ip' => '1',
			'hardening_wrong_login'       => '1',
		);

		add_option( 'sec_hardening', $hardening );

		$utils->changeConfigFile( true, 'DISALLOW_FILE_EDIT' );
		$utils->changeConfigFile( true, 'WP_DEBUG' );
		$utils->disableDirectoryBrowsing( true );
	}

	/**
	 * Enable all optins logs.
	 *
	 * @return void
	 */
	private static function enable_optins_logs() {

		$logs = array(
			'login_error' => '1',
			'new_users'   => '1',
			'plugin_logs' => '1',
		);

		add_option( 'sec_logs_settings', $logs );
	}

	/**
	 * Enable all activity options.
	 *
	 * @return void
	 */
	private static function enable_optins_activity() {

		$activity = array(
			'activity_check_to_block_ip'  => '1',
			'activity_wrong_login'        => '1',
			'activity_comment_black_word' => '1',
			'activity_time_block_ip'      => '5',
			'activity_time_wrong_login'   => '5',
			'activity_blocking_time'      => '24',
			'activity_time_comment_spam'  => '0',
		);

		add_option( 'sec_activity', $activity );
	}

	/**
	 * Enable all email options.
	 *
	 * @return void
	 */
	private static function enable_options_email() {
		$email_notifications = array(
			'all_email_notification' => '1'
		);

		add_option( 'sec_email_notification', $email_notifications );
	}
}
