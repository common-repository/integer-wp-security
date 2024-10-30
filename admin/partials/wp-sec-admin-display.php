<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://integer.pt
 * @since      1.0.0
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="wp-sec" class="wrap">
	<?php
	$active_tab = '';
	if ( isset( $_GET['tab'] ) ) {
		$active_tab = sanitize_key( $_GET['tab'] );
	} else {
		$active_tab = 'hardening';
	}

	$active_tab_secundary = '';
	if ( isset( $_GET['tab_secundary'] ) ) {
		$active_tab_secundary = sanitize_key( $_GET['tab_secundary'] );
	} else {
		$active_tab_secundary = 'tab_options';
	}

	?>
	<?php if ( false !== get_option( 'activated_dashboard' ) ) : ?>
        <div>
			<?php
			settings_fields( 'sec_configuration_settings' );
			do_settings_sections( 'sec_configuration_settings' );
			?>
        </div>
	<?php else : ?>

        <h2><?php printf( esc_html( get_admin_page_title() ) ); ?></h2>

        <input type="hidden" id="show_resume_dashboard" name="name_show_resume_dashboard"
               value="<?php printf( get_option( 'activated_dashboard' ) === false ? 'true' : 'false' ); ?>"/>
		<?php if ( false === get_option( 'activated_dashboard' ) ) : ?>
            <div id="config_header"></div>
            <div id="config_content"></div>
            <div id="config_footer"></div>
		<?php endif; ?>

		<?php
		printf(
			'<div class="nav-tab-wrapper2 tab_primary" style="background-color: #2c2c2c/*workaround*/">
                    <h2 class="margin1">
                        <a href="%s" class="nav-tab-default %s">%s</a>
                        <a href="%s" class="nav-tab-default %s">%s</a>
                        <a href="%s" class="nav-tab-default %s">%s</a>   
                        <a href="%s" class="nav-tab-default %s">%s</a>   
                    </h2>
            </div>',
			admin_url( '?page=integer-wp-security&tab=hardening' ),
			$active_tab === 'hardening' ? esc_attr__( 'nav-tab-active-orange' ) : '',
			esc_html__( 'Hardening', $this->plugin_name ),
			admin_url( '?page=integer-wp-security&tab=verifications_options' ),
			$active_tab === 'verifications_options' ? esc_attr__( 'nav-tab-active-orange' ) : '',
			esc_html__( 'Verifications', $this->plugin_name ),
			admin_url( '?page=integer-wp-security&tab=logs_options' ),
			$active_tab === 'logs_options' ? esc_attr__( 'nav-tab-active-orange' ) : '',
			esc_html__( 'Logs', $this->plugin_name ),
			admin_url( '?page=integer-wp-security&tab=gdpr_settings' ),
			$active_tab === 'gdpr_settings' ? esc_attr__( 'nav-tab-active-orange' ) : '',
			esc_html__( 'Gdpr', $this->plugin_name )
		);

		?>

		<?php if ( $active_tab !== 'verifications_options' ) : ?>
			<?php
			$reset = '';
			if ( ! isset( $_REQUEST['settings-updated'] ) && isset( $_REQUEST['reset'] ) && null !== $_REQUEST['reset'] ) {
				$reset = sanitize_key( $_REQUEST['reset'] );
			}

			// if ($active_tab === 'malware_settings') {
			//     printf('<div class="card card-custom card-dark" style="margin-top:0px;/*workaround*/"');
			//     settings_fields('sec_malware_settings');
			//     do_settings_sections('sec_malware_settings');
			//     printf('</div>');
			// }
			if ( $active_tab === 'gdpr_settings' ) {
				printf( '<div class="card card-custom card-dark" style="margin-top:0px;/*workaround*/"' );
				settings_fields( 'sec_report' );
				do_settings_sections( 'sec_report' );
				printf( '</div>' );
			}
			if ( $active_tab === 'logs_options' ) {
				printf( '<div class="card card-custom card-dark" style="margin-top:0px;/*workaround*/"' );
				settings_fields( 'sec_logs_settings' );
				do_settings_sections( 'sec_logs_settings' );
				printf( '</div>' );
			}
			if ( $active_tab === 'hardening' ) {
				printf(
					'<div><div class="nav-tab-wrapper2" style="background-color: #2c2c2c/*workaround*/">
                            <h2 class="margin1 font-tab-secundary">
                                <a id="tab_options" href="%s" class="nav-tab-secundary %s">%s</a>
                                <a href="%s" class="nav-tab-secundary %s">%s</a>
                                <a href="%s" class="nav-tab-secundary %s">%s</a>
                                <a href="%s" class="nav-tab-secundary %s">%s</a>
                                <a href="%s" class="nav-tab-secundary %s">%s</a>   
                            </h2>
                    </div>',
					admin_url( '?page=integer-wp-security&tab=hardening&tab_secundary=tab_options' ),
					$active_tab_secundary == 'tab_options' ? 'nav-tab-active-blue' : '',
					__( 'Hardening Settings', $this->plugin_name ),
					admin_url( '?page=integer-wp-security&tab=hardening&tab_secundary=tab_2fa' ),
					$active_tab_secundary == 'tab_2fa' ? 'nav-tab-active-blue' : '',
					__( '2FA Options', $this->plugin_name ),
					admin_url( '?page=integer-wp-security&tab=hardening&tab_secundary=tab_recaptcha' ),
					$active_tab_secundary == 'tab_recaptcha' ? 'nav-tab-active-blue' : '',
					__( 'reCaptcha Options', $this->plugin_name ),
					admin_url( '?page=integer-wp-security&tab=hardening&tab_secundary=tab_activity' ),
					$active_tab_secundary == 'tab_activity' ? 'nav-tab-active-blue' : '',
					__( 'Activity Options', $this->plugin_name ),
					admin_url( '?page=integer-wp-security&tab=hardening&tab_secundary=tab_email_notifications' ),
					$active_tab_secundary == 'tab_email_notifications' ? 'nav-tab-active-blue' : '',
					__( 'Email notifications', $this->plugin_name )
				);

				if ( $reset === 'recaptcha' ) {
					delete_option( 'sec_display_recaptcha' );
				}
				$options_recaptcha = get_option( 'sec_display_recaptcha' );
				$actived_recaptcha = false;
				if ( $options_recaptcha['site_key'] != '' && $options_recaptcha['secret_key'] != '' ) {
					$actived_recaptcha = true;
				}

				if ( $reset === '2fa' ) {
					delete_option( 'sec_2fa_options' );
				}
				$options_2fa = get_option( 'sec_2fa_options' );
				$actived_2fa = false;
				if ( isset( $options_2fa['email_method'] ) &&
				     ( $options_2fa['email_method'] != '' && $options_2fa['who_use_2fa'] != '' ) ) {
					$actived_2fa = true;
				}

				if ( $active_tab_secundary === "tab_options" ) {
					printf( '<div class="card card-custom card-dark">' );
					settings_fields( 'sec_hardening' );
					do_settings_sections( 'sec_hardening' );
					printf( '</div>' );
				}
				if ( $active_tab_secundary === "tab_2fa" ) {
					printf( '<div class="card card-custom card-dark margin1"><form method="post" action="options.php">' );
					settings_fields( 'sec_2fa_options' );
					do_settings_sections( 'sec_2fa_options' );
					printf( '<p class="buttons-admin">' );

					if ( $actived_2fa ) {
						printf(
							'<a class="button-secondary" href="?page=%s&tab=%s&reset=2fa" title="Reset">Reset</a>
                            <input type="submit" class="button button-primary" name="submit" disabled="disabled" value="Actived" />',
							$this->plugin_name,
							$active_tab
						);
					} else {
						printf( "<input type='submit' class='button button-primary' name='submit' value='Active' />" );
					}
					printf( '</p></form></div>' );
				}
				if ( $active_tab_secundary === "tab_recaptcha" ) {
					printf( '<div class="card card-custom card-dark margin1"><form method="post" action="options.php">' );
					settings_fields( 'sec_display_recaptcha' );
					do_settings_sections( 'sec_display_recaptcha' );
					printf( '<p class="buttons-admin">' );

					if ( $actived_recaptcha ) {
						printf(
							'<a class="button-secondary" href="?page=%s&tab=%s&reset=recaptcha" title="Reset">Reset</a>
                                    <input type="submit" class="button button-primary" name="submit" disabled="disabled" value="Actived" />',
							$this->plugin_name,
							$active_tab
						);
					} else {
						printf( "<input type='submit' class='button button-primary' name='submit' value='Active' />" );
					}
					printf( '</p></form></div>' );
				}
				if ( $active_tab_secundary === "tab_activity" ) {
					printf( '<div class="card card-custom card-dark margin1">' );
					settings_fields( 'sec_activity' );
					do_settings_sections( 'sec_activity' );
					printf( '</div>' );
				}
				if ( $active_tab_secundary === "tab_email_notifications" ) {
					printf( '<div class="card card-custom card-dark margin1">' );
					settings_fields( 'sec_email_notification' );
					do_settings_sections( 'sec_email_notification' );
					printf( '</div>' );
				}
				printf( '</div>' );
			}
			?>
            </form>
		<?php else : ?>
            <div>
				<?php
				if ( $active_tab === 'verifications_options' ) {
					settings_fields( 'sec_verifications_settings' );
					do_settings_sections( 'sec_verifications_settings' );
				}
				?>
            </div>
		<?php endif; ?>
	<?php endif; ?>
</div>



