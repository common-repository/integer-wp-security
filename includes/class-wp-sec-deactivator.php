<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://integer.pt
 * @since      1.0.0
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wp_Sec
 * @subpackage Wp_Sec/includes
 * @author     Integer Consulting <info@integer.pt>
 */
class Wp_Sec_Deactivator {

	const TABLE_LOGIN_LOG_ERROR  = 'sec_login_error_logs';
	const TABLE_NEW_USER         = 'sec_new_users_logs';
	const TABLE_PLUGIN_INSTALL   = 'sec_plugins_logs';
	const TABLE_LOGS             = 'sec_logs';
	const TABLE_MALWARE          = 'sec_malware';
	const TABLE_IP_BLOCK         = 'sec_ip_block';
	const TABLE_DASHBOARD_FIELDS = 'sec_dashboard_fields';

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		
	}
}
