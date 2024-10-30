<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://integer.pt
 * @since      1.0.0
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Sec
 * @subpackage Wp_Sec/public
 * @author     Integer Consulting <info@integer.pt>
 */
class DevsoftIn_Wp_Sec_Two_Factor_Public {

	const TOKEN_TIMESTAMP = 'devsoftin_wp_sec_two_factor_token_timestamp';
	const TOKEN_KEY = 'devsoftin_wp_sec_two_factor_token';

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

	private $plugin_logs;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->load_dependencies( $this->plugin_name, $this->version );
	}

	private function load_dependencies( $plugin_name, $version ) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sec-logs-settings.php';
		$this->plugin_logs = new DevsoftIn_Wp_Sec_Logs_Settings( $plugin_name, $version );
	}

	public function user_has_token( $user_id ) {
		$hashed_token = $this->get_user_token( $user_id );
		if ( ! empty( $hashed_token ) ) {
			return true;
		}

		return false;
	}

	public function get_user_token( $user_id ) {
		$hashed_token = get_user_meta( $user_id, self::TOKEN_KEY, true );
		if ( ! empty( $hashed_token ) && is_string( $hashed_token ) ) {
			return $hashed_token;
		}

		return false;
	}

	public function pre_process_authentication( $user ) {
		if ( isset( $user->ID ) && isset( $_REQUEST['resend_code'] ) ) {
			$this->generate_and_email_token( $user );

			return true;
		}

		return false;
	}

	public function generate_and_email_token( $user ) {
		$token   = $this->generate_token( $user->ID );
		$subject = wp_strip_all_tags( sprintf( __( 'Your login confirmation code for %s', $this->plugin_name ),
			get_bloginfo( 'name' ) ) );
		$message = wp_strip_all_tags( sprintf( __( 'Enter %s to log in.', $this->plugin_name ), $token ) );

		$subject = apply_filters( 'wp_sec_two_factor_token_email_subject', $subject, $user->ID );
		$message = apply_filters( 'wp_sec_two_factor_token_email_message', $message, $token, $user->ID );

		wp_mail( $user->user_email, $subject, $message );

		return $token;
	}

	public function generate_token( $user_id ) {
		$token = $this->get_code();
		update_user_meta( $user_id, self::TOKEN_TIMESTAMP, time() );
		update_user_meta( $user_id, self::TOKEN_KEY, wp_hash( $token ) );

		return $token;
	}

	public function get_code( $length = 8, $chars = '1234567890' ) {
		$code = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$code .= substr( $chars, wp_rand( 0, strlen( $chars ) - 1 ), 1 );
		}

		return $code;
	}

	public function login_form_validate() {

		if ( ! isset( $_POST['user_id'] ) ) {


			return;
		}

		$user = get_userdata( (int) $_POST['user_id'] );

		if ( ! $user ) {
			return;
		}
		$url_redirect = filter_input( INPUT_POST, 'redirect_to', FILTER_SANITIZE_URL );
		$resend_code  = filter_input( INPUT_POST, 'resend_code', FILTER_SANITIZE_URL );

		if ( isset( $user->ID ) && ! empty( $resend_code ) ) {
			$this->initialize_2fa_email( $user->user_login, $user, $url_redirect );
			exit;
		}

		$validate = $this->validate_authentication( $user );

		if ( true !== $validate ) {
			$error = new WP_Error();
			$error->add( 'incorrect_2fa_code', 'ERROR: Invalid 2FA code.' );
			do_action( 'wp_login_failed', $user->user_login, $error );

			$this->initialize_2fa_email( $user->user_login, $user, $url_redirect,
				__( 'ERROR: Invalid verification code.', $this->plugin_name ) );
			exit;
		}
		$rememberme = false;
		if ( isset( $_REQUEST['rememberme'] ) && $_REQUEST['rememberme'] ) {
			$rememberme = true;
		}

		wp_set_auth_cookie( $user->ID, $rememberme );
		do_action( 'two_factor_user_authenticated', $user );

		$redirect_to = apply_filters( 'login_redirect', $url_redirect, $url_redirect, $user );

		$this->plugin_logs->save_login_errors( $user, null );

		wp_safe_redirect( $redirect_to );
		exit;
	}

	public function initialize_2fa_email( $user_login, $user, $redirect_to = null, $error_msg = null ) {

		$option = get_option( 'sec_2fa_options' );

		if ( ! $user || ! $user->ID ||
		     ! $option['email_method'] == 1 || ! $option['who_use_2fa'] ||
		     ! $this->this_user_use_two_factor( $user, $option['who_use_2fa'] ) ) {
			return;
		}

		$rememberme = intval( $this->rememberme() );

		if ( ! $redirect_to ) {
			$redirect_to = ! empty( $url_redirect ) ? $url_redirect : admin_url();
		}

		wp_clear_auth_cookie();

		login_header();

		if ( ! empty( $error_msg ) ) {
			printf( '<div id="login_error"><strong>%s</strong><br /></div>', esc_html( $error_msg ) );
		}

		$token = '';
		// Falta invalidar o token antigo para pode usar esse IF.
		// if ( ! $this->user_has_token( $user->ID ) || $this->user_token_has_expired( $user->ID ) ) {
		$token = $this->generate_and_email_token( $user );
		// }

		$urlLogin = $this->login_url( array( 'action' => 'validate_2fa' ), 'login_post' );

		// Falta um botao para reenviar o codigo.
		printf(
			'<form name="validate_2fa_form" id="loginform" action="%s" method="post" autocomplete="off">
				<input type="hidden" name="provider"      id="provider"      value="email" />
				<input type="hidden" name="user_id"    id="user_id"    value="%u" />
				<input type="hidden" name="rememberme"    id="rememberme"    value="%s" />
				<input type="hidden" name="redirect_to" value="%s" />
				<p>%s</p>
				<p>
					<label for="authcode">%s</label>
					<input type="text" name="code" id="authcode" class="input" value="" size="20" />
					<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="%s"></p>
				</p>
				<p>
					<input type="submit" class="button" name="resend_code" value="%s" />
				</p>
				<script type="text/javascript">
					setTimeout( function(){
						var d;
						try{ 
							d = document.getElementById("authcode");
							d.value = "";
							d.focus();
						} catch(e){}
					}, 200);
				</script>
		</form>',
			$urlLogin,
			$user->ID,
			$rememberme,
			$redirect_to,
			__( 'A verification code has been sent to the email address associated with your account.',
				$this->plugin_name ),
			__( 'Verification Code:', $this->plugin_name ),
			__( 'Log In', $this->plugin_name ),
			__( 'Resend Code', $this->plugin_name )
		);

		exit;
	}

	public function this_user_use_two_factor( $user, $whoUse ) {

		if ( $whoUse === 'all' ) {
			return true;
		}

		foreach ( $user->roles as $roles ) {
			if ( $roles === $whoUse ) {
				return true;
			}
		}

		return false;
	}

	public function rememberme() {
		$rememberme = false;

		if ( ! empty( $_REQUEST['rememberme'] ) ) {
			$rememberme = true;
		}

		return (bool) apply_filters( 'two_factor_rememberme', $rememberme );
	}

	public function login_url( $params = array(), $scheme = 'login' ) {
		if ( ! is_array( $params ) ) {
			$params = array();
		}

		$params = urlencode_deep( $params );

		return add_query_arg( $params, site_url( 'wp-login.php', $scheme ) );
	}

	public function validate_authentication( $user ) {
		if ( ! isset( $user->ID ) || ! isset( $_REQUEST['code'] ) ) {
			return false;
		}

		return $this->validate_token( $user->ID, sanitize_key( $_REQUEST['code'] ) );
	}

	public function validate_token( $user_id, $token ) {
		$hashed_token = $this->get_user_token( $user_id );
		if ( empty( $hashed_token ) || ( wp_hash( $token ) !== $hashed_token ) ) {

			return false;
		}
		if ( $this->user_token_has_expired( $user_id ) ) {
			return false;
		}

		$this->delete_token( $user_id );

		return true;
	}

	public function user_token_has_expired( $user_id ) {
		$token_lifetime = $this->user_token_lifetime( $user_id );
		$token_ttl      = $this->user_token_ttl( $user_id );

		if ( is_int( $token_lifetime ) && $token_lifetime <= $token_ttl ) {
			return false;
		}

		return true;
	}

	public function user_token_lifetime( $user_id ) {
		$timestamp = intval( get_user_meta( $user_id, self::TOKEN_TIMESTAMP, true ) );

		if ( ! empty( $timestamp ) ) {
			return time() - $timestamp;
		}

		return null;
	}

	public function user_token_ttl( $user_id ) {
		$token_ttl = 5 * MINUTE_IN_SECONDS;

		return (int) apply_filters( 'two_factor_token_ttl', $token_ttl, $user_id );
	}

	public function delete_token( $user_id ) {
		delete_user_meta( $user_id, self::TOKEN_KEY );
	}


}
