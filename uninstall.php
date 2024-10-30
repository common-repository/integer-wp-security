<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://integer.pt
 * @since      1.0.0
 *
 * @package    Wp_Sec
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$TABLE_LOGIN_LOG_ERROR  = 'devsoftIn_sec_login_error_logs';
$TABLE_NEW_USER         = 'devsoftIn_sec_new_users_logs';
$TABLE_PLUGIN_INSTALL   = 'devsoftIn_sec_plugins_logs';
$TABLE_LOGS             = 'devsoftIn_sec_logs';
$TABLE_MALWARE          = 'devsoftIn_sec_malware';
$TABLE_IP_BLOCK         = 'devsoftIn_sec_ip_block';
$TABLE_DASHBOARD_FIELDS = 'devsoftIn_sec_dashboard_fields';

delete_option( 'devsoftIn_sec_hardening' );
delete_option( 'devsoftIn_sec_logs_settings' );
delete_option( 'devsoftIn_sec_malware_settings' );
delete_option( 'devsoftIn_sec_display_recaptcha' );
delete_option( 'devsoftIn_sec_2fa_options' );
delete_option( 'devsoftIn_sec_verifications_settings' );
delete_option( 'devsoftIn_sec_hardening_login_url' );
delete_option( 'devsoftIn_sec_activity' );
delete_option( 'devsoftIn_sec_report' );

global $wpdb;

$table_malware          = $wpdb->prefix . $TABLE_MALWARE;
$table_login_error      = $wpdb->prefix . $TABLE_LOGIN_LOG_ERROR;
$table_new_user         = $wpdb->prefix . $TABLE_NEW_USER;
$table_plugin_install   = $wpdb->prefix . $TABLE_PLUGIN_INSTALL;
$table_logs             = $wpdb->prefix . $TABLE_LOGS;
$table_ip_block         = $wpdb->prefix . $TABLE_IP_BLOCK;
$table_dashboard_fields = $wpdb->prefix . $TABLE_DASHBOARD_FIELDS;

$wpdb->query( "DROP TABLE IF EXISTS $table_login_error" );
$wpdb->query( "DROP TABLE IF EXISTS $table_new_user" );
$wpdb->query( "DROP TABLE IF EXISTS $table_plugin_install" );
$wpdb->query( "DROP TABLE IF EXISTS $table_logs" );
$wpdb->query( "DROP TABLE IF EXISTS $table_malware" );
$wpdb->query( "DROP TABLE IF EXISTS $table_ip_block" );
$wpdb->query( "DROP TABLE IF EXISTS $table_dashboard_fields" );