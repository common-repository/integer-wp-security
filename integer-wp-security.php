<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://integer.pt
 * @since             1.0.0
 * @package           Wp_Sec
 *
 * @wordpress-plugin
 * Plugin Name:       Integer WP Security
 * Plugin URI:        https://integer.pt/wp-security
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Integer Consulting
 * Author URI:        https://integer.pt
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-sec
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DEVSOFTIN_WP_SEC_VERSION', '1.0.0' );

/**
 * Alternative to $this->plugin_name
 */
define( 'devsoftin_plugin_name', 'integer_wp_security' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-sec-activator.php
 */
if ( ! function_exists( 'devsoftIn_activate_wp_sec' ) ) {
	function devsoftIn_activate_wp_sec() {
		add_option( 'activated_dashboard', true );

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-sec-activator.php';
		Wp_Sec_Activator::activate();

		set_transient( 'transient_devsoftIn_wp_sec_notice_actived', true, 5 );
	}
}


/**
 * Admin Notice on Activation.
 *
 * @since 0.1.0
 */
if ( ! function_exists( 'devsoftIn_wp_sec_notice_actived' ) ) {
	/* Add admin notice */
	add_action( 'admin_notices', 'devsoftIn_wp_sec_notice_actived' );
	function devsoftIn_wp_sec_notice_actived() {

		/* Check transient, if available display notice */
		if ( get_transient( 'transient_devsoftIn_wp_sec_notice_actived' ) ) {
			$url_image = plugins_url( '/integer-wp-security/admin/images/imagotype_grey_and_white_shield.png' );
			printf(
				'<div id="wp-sec" class="updated notice is-dismissible" style="background-color: var(--color-dark-primary)">
				<div style="display: flex; justify-content: space-around; flex-wrap: wrap; align-items: center;">
					<img src="%1$s" style="padding: 36px; height: 130px;">
					<div style="width: 200%; display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
						<div class="medium-size padding-12 padding-left-off">%2$s</div>
						<div class="small-size">%3$s</div>
						<div class="small-size">%4$s</div>
					</div>
					<div  style="width: 100%; display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: center; padding: 36px">
						<a href="%5$s" class="button_dashboard fix_now" type="button" />%6$s</a>
					</div>
				</div> 
			</div>',
				esc_url_raw( $url_image ),
				esc_html__(
					'Hello! Welcome to the Integer WordPress Security Configurator.',
					devsoftin_plugin_name
				),
				esc_html__(
					'Integer WordPress Security is here to help you. All your security issues are covered. That simple.',
					devsoftin_plugin_name
				),
				esc_html__(
					'Let s jump into the setup and perform a first scan on your WordPress website to identify any security and performance issues.',
					devsoftin_plugin_name
				),
				esc_url_raw( admin_url( '?page=integer-wp-security' ) ),
				esc_html__( 'Setup', devsoftin_plugin_name )
			);
			delete_transient( 'transient_devsoftIn_wp_sec_notice_actived' );
		}
	}
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-sec-deactivator.php
 */
if ( ! function_exists( 'devsoftIn_deactivate_wp_sec' ) ) {
	function devsoftIn_deactivate_wp_sec() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-sec-deactivator.php';
		Wp_Sec_Deactivator::deactivate();
	}
}


register_activation_hook( __FILE__, 'devsoftIn_activate_wp_sec' );
register_deactivation_hook( __FILE__, 'devsoftIn_deactivate_wp_sec' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-sec.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function devsoftIn_run_wp_sec() {

	$plugin = new DevsoftIn_Wp_Sec();
	$plugin->run();

}

devsoftIn_run_wp_sec();
