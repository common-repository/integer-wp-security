<?php

class DevsoftIn_Wp_Sec_Utils {

	const FILE_IP_BLOCK = '/../admin/js/ip_block/list_ip_block.json';
	const FILE_SUSPICIOUS_ACCESS = '/../admin/js/ip_block/suspicious_access.json';
	const COLLECTOR_API_URL = 'https://integer-collector.azurewebsites.net/api';

	public function __construct() {
	}

	/**
	 * Rewrite the config.php file with the new prefix table name.
	 *
	 * @param string $prefix .
	 *
	 * @return void
	 */
	public function change_config_prefix( $prefix ) {
		$config_file  = $this->getConfigFile();
		$content      = $this->fileContent( $config_file );
		$content_line = explode( "\n", $content );
		$new_content  = array();
		foreach ( $content_line as $line ) {
			if ( strpos( str_replace( ' ', '', $line ), '$table_prefix' ) !== false ) {
				$new_content[] = '$table_prefix = "' . $prefix . '";';
				continue;
			}
			$new_content[] = $line;
		}
		$content = implode( "\n", $new_content );
		$this->writeFile( $config_file, $content );
	}

	private function getConfigFile() {
		$configFile = $this->getConfigPath();

		if ( ! is_writable( $configFile ) ) {
			return;
		}

		return $configFile;
	}

	private function getConfigPath() {
		return get_home_path() . '/wp-config.php';
	}

	private function fileContent( $path ) {
		return (string) ( is_readable( $path ) ? @file_get_contents( $path ) : '' );
	}

	private function writeFile( $file, $content ) {
		@file_put_contents( $file, $content, LOCK_EX );
	}

	public function changeConfigFile( $enable, $key ) {
		$configFile  = $this->getConfigFile();
		$content     = $this->fileContent( $configFile );
		$contentLine = explode( "\n", $content );
		$newContent  = $this->searchAndChangeConfigVariable( $contentLine, $enable, $key );
		$content     = implode( "\n", $newContent );
		$this->writeFile( $configFile, $content );
	}

	private function searchAndChangeConfigVariable( $contentLine, $enable, $key ) {
		$newContent = array();
		$found      = false;
		foreach ( $contentLine as $line ) {
			if ( strpos( str_replace( ' ', '', $line ), "define('$key'" ) !== false ) {
				$found = true;
				if ( $enable ) {
					$newContent[] = "define( '$key', true );";
				} else {
					$newContent[] = "define( '$key', false );";
				}
				continue;
			}
			$newContent[] = $line;
		}

		if ( ! $found ) {
			if ( $enable ) {
				$newContent[] = "define( '$key', true );";
			} else {
				$newContent[] = "define( '$key', false );";
			}
		}

		return $newContent;
	}

	public function disableXlmrpc( $enable ) {
		if ( $enable ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		} else {
			remove_filter( 'xmlrpc_enabled', '__return_false' );
		}
		wp_die( $enable );
	}

	public function disableDirectoryBrowsing( $enable ) {
		$filename = get_home_path() . '/.htaccess';

		$file = @realpath( $filename );

		if ( ! $file ) {
			$this->writeFile( get_home_path() . '/.htaccess', 'Options All -Indexes' );

			return;
		}

		$content     = $this->fileContent( $file );
		$contentLine = explode( "\n", $content );

		$newContent = array();
		$status     = false;
		$found      = false;
		foreach ( $contentLine as $line ) {
			if ( strpos( $line, 'Options' ) !== false ) {
				if ( trim( $line ) === 'Options All -Indexes' && $enable ) {
					$status = true;
					break;
				}
				if ( $enable ) {
					$newContent[] = 'Options All -Indexes';
				}
				$found = true;

				continue;
			}
			$newContent[] = $line;
		}

		if ( $status ) {
			return;
		}

		if ( ! $found && $enable ) {
			$newContent[] = 'Options All -Indexes';
		}

		$content = implode( "\n", $newContent );
		$this->writeFile( $file, $content );
	}

	/**
	 * Suspicious access.
	 *
	 * @return boolean
	 */
	public function suspicious_access( $content, $max_frequent = 0 ) {
		$path = dirname( __FILE__ ) . self::FILE_SUSPICIOUS_ACCESS;
		$file = json_decode( utf8_encode( file_get_contents( $path ) ), true );

		if ( null !== $file && 0 !== count( $file ) ) {
			array_push( $file, $content );
		} else {
			$file = array( $content );
		}

		$frequent = 0;

		foreach ( $file as $value ) {
			$time = new DateTime( $value['time_block'] );
			$now  = new DateTime();
			if ( $content['ip'] === $value['ip'] && $time > $now ) {
				$frequent ++;
			}
		}

		file_put_contents( $path, json_encode( $file ) );

		return $max_frequent < $frequent ? true : false;
	}

	/**
	 * Check ip block on list
	 *
	 * @param string $ip .
	 *
	 * @return boolean
	 */
	public function check_ip_block( $ip ) {
		$path = dirname( __FILE__ ) . self::FILE_IP_BLOCK;
		if ( ! file_exists( $path ) ) {
			return false;
		}

		$file = json_decode( utf8_encode( file_get_contents( $path ) ), true );
		if ( null === $file || count( $file ) === 0 ) {
			return false;
		}

		$block = false;
		foreach ( $file as $key => $value ) {
			if ( isset( $value['time_block'] ) && isset( $value['ip'] ) ) {
				$time = new DateTime( $value['time_block'] );
				$now  = new DateTime();
				if ( $ip === $value['ip'] && $time > $now ) {
					$block = true;
				} elseif ( $ip === $value['ip'] && $time < $now ) {
					unset( $file[ $key ] );
					$this->file_list_ip_blocks( $file, true );
				}
			}
		}

		return $block;
	}

	/**
	 * Create or change file and list ip block.
	 *
	 * @param string $content .
	 *
	 * @return void
	 */
	public function file_list_ip_blocks( $content = null, $rewrite = false, $ip = false ) {
		$path = dirname( __FILE__ ) . self::FILE_IP_BLOCK;
		if ( ! file_exists( $path ) ) {
			file_put_contents( $path, json_encode( array( $content ) ) );
		}
		$file = json_decode( utf8_encode( file_get_contents( $path ) ), true );
		if ( true === $rewrite || true === $file ) {
			file_put_contents( $path, json_encode( array( $content ) ) );
		} else {
			if ( is_array( $file ) && 0 !== count( $file ) ) {
				foreach ( $file as $key => $value ) {
					if (
						isset( $value['time_block'] )
						&& isset( $value['ip'] )
						&& $ip === $value['ip']
					) {
						unset( $file[ $key ] );
					}
				}
			}
			if ( is_null( $file ) ) {
				$file = array();
			}

			array_push( $file, $content );
			file_put_contents( $path, json_encode( $file ) );
		}
	}

	/**
	 * Check access allow.
	 *
	 * @param mixed $path .
	 *
	 * @return boolean
	 */
	public function check_access_allow( $path, $option ) {
		$wpcontent  = preg_match( '/^.wp-content\/uploads\/.*.php$/i', $path['path'] );
		$wpincludes = preg_match( '/^.wp-includes\/.*.php$/i', $path['path'] );

		$loginphp = false;
		$endphp   = false;

		if ( '' !== $option && false !== $option
		     && strpos( trim( $option ), 'wp-admin' ) === false
		     && strpos( trim( $option ), 'wp-login' ) === false
		     && strpos( trim( $option ), 'wp-includes' ) === false ) {
			$loginphp = preg_match( '/^.*.wp-login.php$/i', $path['path'] );
			$endphp   = preg_match( '/^.*login$/i', $path['path'] );
		}

		return ( $wpcontent || $wpincludes || $endphp || $loginphp ) ? 1 : 0;
	}

	/**
	 * Get user ip.
	 *
	 * @param mixed $server .
	 *
	 * @return string
	 */
	public function get_user_ip( $server ) {
		if ( ! empty( $server['HTTP_CLIENT_IP'] ) ) {
			$ip = $server['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $server['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $server['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $server['REMOTE_ADDR'];
		}

		return $ip;
	}

	/**
	 * UPDATE dashboard_fields.
	 *
	 * @param string $type .
	 * @param mixed $value .
	 *
	 * @return void
	 */
	public function dashboard_fields( $type, $value ) {
		global $wpdb;

		$result = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'sec_dashboard_fields' . ' WHERE type = "' . $type . '"',
			ARRAY_A );

		if ( $result && count( $result ) > 0 ) {
			foreach ( $result as $dashboard ) {
				if ( $type === $dashboard['type'] ) {
					if ( is_numeric( $value ) ) {
						$value = (int) $dashboard['value'] + $value;
					}

					$wpdb->query( 'UPDATE ' . $wpdb->prefix . 'sec_dashboard_fields SET value = "' . $value . '", created_at=now()  WHERE type = "' . $type . '"' );
				}
			}
		} else {
			$wpdb->query( 'INSERT INTO ' . $wpdb->prefix . 'sec_dashboard_fields' . " (type, value) VALUES ('" . $type . "', '" . $value . "')" );
		}
	}

	/**
	 * Return dashboard_fields.
	 *
	 * @return void
	 */
	public function dashboard_values() {
		global $wpdb;

		$result = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'sec_dashboard_fields', ARRAY_A );

		return $result;
	}

	/**
	 * Send report to api wp-sec
	 *
	 * @param string $sector .
	 * @param string $key .
	 * @param boolean $value .
	 * @param mixed $details .
	 */
	public function send_report( $sector, $key, $value, $details = null ) {

		$options = get_option( 'sec_report' );

		if ( ! $options['active'] || '0' === $options['active'] || ! $options['host_key'] || null === $options['host_key'] ) {
			return;
		}

		$url = self::COLLECTOR_API_URL . '/logs';

		$fields = array(
			'sector'     => $sector,
			'key'        => $key,
			'value'      => $value,
			'host_token' => $options['host_key'],
		);

		if ( ! $details ) {
			array_push( $fields, $details );
		}

		$this->request( $url, $fields );
	}

	/**
	 * @param string $url
	 * @param array $fields
	 *
	 * @return mixed
	 */
	private function request( $url, $fields ) {

		$args     = array(
			'body' => $fields
		);
		$response = wp_remote_post( $url, $args );

		return $response['body'];
	}

	/**
	 * Register this application WordPress to api wp-sec
	 *
	 * @param string $report .
	 *
	 * @return string
	 */
	public function register_host( $report ) {

		if ( ! $report || 'false' === $report ) {
			return null;
		}

		$url = self::COLLECTOR_API_URL . '/host';

		$fields = array(
			'url' => get_home_url(),
		);

		$result = json_decode( $this->request( $url, $fields ) );

		if ( $result && $result['token'] ) {
			return $result['token'];
		}

		return null;
	}

	/**
	 * @param string $optionId
	 * @param string $optionSection
	 */
	public function print_option_switch( $option_id, $option_section ) {

		$options = get_option( $option_section );

		$aux_value = isset( $options[ $option_id ] ) ? $options[ $option_id ] : '';

		$setting_checked = ( 1 == $aux_value ) ? 'checked' : '';

		printf(
			"<label class='switch' for='%s'>
			    <input type='checkbox' id='%s' name='%s' value='1' %s />
			    <span class='slider round '></span>
		        </label>",
			esc_attr( $option_id ),
			esc_attr( $option_id ),
			esc_attr( "{$option_section}[{$option_id}]" ),
			esc_attr( $setting_checked )
		);
	}

	/**
	 * Method that sends an email to the website's admin
	 *
	 * @param $subject string Email subject
	 * @param $message string Email message
	 * @param $params array with the parameters to fill the email message
	 */
	public function send_email_to_admin( $subject, $message, $params = null ) {

		if ( null !== $params ) {
			$message = vsprintf( $message, $params );
		}

		$to = filter_var( get_option( 'admin_email' ), FILTER_VALIDATE_EMAIL );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		$uid                  = uniqid();
		$name                 = 'logoInteger';
		$path                 = plugins_url( '/admin/images/logo_horizontal_dark_grey.png', __DIR__ );
		$inline_attachments[] = array(
			'uid'  => $uid,
			'name' => $name,
			'file' => $path,
		);

		add_action( 'phpmailer_init', function ( &$phpmailer ) use ( $inline_attachments ) {
			$phpmailer->SMTPKeepAlive = true;
			foreach ( $inline_attachments as $a ) {
				$phpmailer->AddEmbeddedImage( $a['file'], $a['uid'], $a['name'] );
			}
		}
		);

		$message .= '<br><br><img src="cid:' . $uid . '" width="453" height="127">'; // Attaches the inline image
		wp_mail( $to, $subject, $message, $headers ); //Sends the email

		foreach ( $inline_attachments as $a ) { //resets the array
			if ( file_exists( $a['name'] ) ) {
				unlink( $a['name'] );
			}
		}

	}
}
