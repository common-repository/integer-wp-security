<?php

/**
 * Wp_Sec_Verification_Settings
 */
class DevsoftIn_Wp_Sec_Verification_Settings {

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

	private $files_wrong_perms;

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

		$this->plugin_name       = $plugin_name;
		$this->version           = $version;
		$this->files_wrong_perms = array();
		$this->load_dependencies();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sec-utils.php';
		$this->utils = new DevsoftIn_Wp_Sec_Utils();
	}

	/**
	 * Function that loads sessions from the checks screen.
	 *
	 * @return void
	 */
	public function initialize_verification_settings() {

		$this->utils->send_report( 'tabs', 'sec_verifications_settings', true );

		if ( false === get_option( 'sec_verifications_settings' ) ) {
			add_option( 'sec_verifications_settings', array() );
		}

		add_settings_section(
			'verifications_description_section',
			__( '', $this->plugin_name ),
			array( $this, 'general_options_callback' ),
			'sec_verifications_settings'
		);

		add_settings_section(
			'verifications_tables_section',
			null,
			array( $this, 'check_prefix_tables_callback' ),
			'sec_verifications_settings'
		);

		add_settings_section(
			'verifications_userlogin_section',
			null,
			array( $this, 'check_username_defaults_callback' ),
			'sec_verifications_settings'
		);

		add_settings_section(
			'verifications_password_section',
			null,
			array( $this, 'check_password_defaults_callback' ),
			'sec_verifications_settings'
		);

		add_settings_section(
			'verifications_php_version_section',
			null,
			array( $this, 'check_php_version_defaults_callback' ),
			'sec_verifications_settings'
		);

		add_settings_section(
			'verifications_permission_files_section',
			null,
			array( $this, 'check_permission_files_defaults_callback' ),
			'sec_verifications_settings'
		);

		register_setting(
			'sec_verifications_settings',
			'sec_verifications_settings'
		);
	}

	/**
	 * Title of the page.
	 *
	 * @echo string
	 */
	public function general_options_callback() {
		printf(
			'<div class="card card_max_size  card-custom card-dark">
                <h2 class="no-margin" style="margin-top: 25px;">%1$s</h2>
                <p class="no-margin">%2$s</p>
            </div>',
			esc_html__( 'Verifications', $this->plugin_name ),
			esc_html__( 'Protect your WordPress.', $this->plugin_name )
		);
	}

	/**
	 * Checks whether the tables have the prefix defined.
	 *
	 * @echo string
	 */
	public function check_prefix_tables_callback() {
		$tables_without_prefix = $this->get_table_without_prefix();
		?>
        <div class="card card_max_size card-custom card-dark">
            <p style="margin-bottom: 5px !important;"><?php printf( esc_html( 'Checks if there are tables without the standard prefix.' ) ) ?></p>
			<?php if ( count( $tables_without_prefix ) > 0 ) : ?>
                <div class="card card_danger">There are tables that do use the standard prefix.</div>
                <br/>
                <div class="">
                    <!--                    <table class="widefat orange-lines table-orange" style="width: 100%;">-->
                    <table class="widefat  full-orange-lines" style="width: 100%; ">
                        <thead>
                        <tr>
                            <th class="">Tables</th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $tables_without_prefix as $key => $table ) : ?>
                            <tr class="orange-lines"
								<?php printf( ! ( $key % 2 ) ? '' : esc_attr( 'class="alternate"' ) ); ?> >
                                <td class="  font-orange">
                                    <label for="tablecell">
										<?php printf( esc_attr( $table ) ); ?>
                                    </label>
                                </td>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
			<?php else : ?>
                <div class="card card_success">All tables aren't following the standard prefix</div>
			<?php endif; ?>
        </div>
		<?php
	}

	public function get_table_without_prefix() {
		global $wpdb;

		$sql                   = 'SHOW TABLES FROM `' . DB_NAME . '`';
		$tables                = $wpdb->get_results( $sql, ARRAY_A );
		$tables_without_prefix = array();

		$tables_dbname = 'Tables_in_' . DB_NAME;

		foreach ( $tables as $key => $table ) {
//			if ( substr( $table[ $tables_dbname ], 0, strlen( $wpdb->prefix ) ) !== $wpdb->prefix ) {
			if ( substr( $table[ $tables_dbname ], 0, strlen( $wpdb->prefix ) ) == 'wp_' ) {
				array_push( $tables_without_prefix, $table[ $tables_dbname ] );
			}
		}

		return $tables_without_prefix;
	}

	/**
	 * checks for users with easy names.
	 *
	 * @return void
	 */
	public function check_username_defaults_callback() {
		$users = $this->get_users_easy();
		$this->show_card_users(
			$users,
			'Checks if you have active users in your database with easy names.',
			'There are active users with easy names in your base.',
			'There are no active users with easy names in your database.'
		);
	}

	public function get_users_easy() {
		global $wpdb;

		$user_names = array( 'admin', 'root', 'administrator', 'manager', 'guest', 'user', 'test', 'support' );

		$sql   = 'SELECT * FROM ' . $wpdb->prefix . 'users where 
            user_login IN (' . $this->prepare_array_for_query( $user_names ) . ')';
		$users = $wpdb->get_results( $sql, ARRAY_A );

		return $users;
	}

	/**
	 * Prepare_array_for_query
	 *
	 * @param array $array Array.
	 *
	 * @return array
	 */
	private function prepare_array_for_query( $array ) {
		$array = array_map(
			function ( $item ) {
				return "'" . esc_sql( $item ) . "'";
			},
			$array
		);

		return implode( ', ', $array );
	}

	/**
	 * Show_card_users
	 *
	 * @param array $users Array de usuários.
	 * @param mixed $title Titulo do box.
	 * @param mixed $message_failed Mensagem de erro.
	 * @param mixed $message_success Mensagem de sucesso.
	 *
	 * @return void
	 */
	private function show_card_users( $users, $title, $message_failed, $message_success ) {
		?>
        <div class="card card_max_size card-custom card-dark">
            <p style="margin-bottom: 5px !important;"><?php printf( esc_html( $title ) ) ?></p>
			<?php
			if ( count( $users ) > 0 ) :
				;
				?>
                <div class="card card_danger"><?php printf( esc_html( $message_failed ) ); ?></div>
                <br/>
                <table class="widefat  full-orange-lines" style="width: 100%;">
                    <thead>
                    <tr>
                        <th class="row-title"><?php esc_html_e( 'ID', $this->plugin_name ); ?></th>
                        <th class="row-title"><?php esc_html_e( 'User_login', $this->plugin_name ); ?></th>
                        <th class="row-title"><?php esc_html_e( 'Email', $this->plugin_name ); ?></th>
                        <th class="row-title"><?php esc_html_e( 'Display name', $this->plugin_name ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					foreach ( $users as $key => $user ) {
						$class        = ! ( $key % 2 ) ? '' : "class='alternate'";
						$id           = $user['ID'];
						$user_login   = $user['user_login'];
						$user_email   = $user['user_email'];
						$display_name = $user['display_name'];
						printf(
							'<tr %s>
                                    <td class="">
                                        <label for="tablecell">%u</label>
                                    </td>
							        <td class="">
							            <label for="tablecell">%s</label>
							        </td>
							        <td class="">
							            <label for="tablecell">%s</label>
							        </td>
							        <td class="">
							            <label for="tablecell">%s</label>
							        </td>
							    </tr>',
							esc_attr( $class ),
							esc_html( $id ),
							esc_html( $user_login ),
							esc_html( $user_email ),
							esc_html( $display_name )
						);
					}
					?>
                    </tbody>
                </table>
			<?php else : ?>
                <div class="card card_success"><?php printf( esc_html( $message_success ) ); ?></div>
			<?php endif; ?>
        </div>
		<?php
	}

	/**
	 * checks if there are easy passwords linked to users.
	 *
	 * @return void
	 */
	public function check_password_defaults_callback() {
		$pass_easy = $this->get_password_easy();
		$this->show_card_users(
			$pass_easy,
			'Checks if you have active users in your database with easy password.',
			'There are active users with easy password in your base.',
			'There are no active users with easy password in your database.'
		);
	}

	public function get_password_easy() {
		global $wpdb;

		$passwords = array(
			'admin',
			'root',
			'administrator',
			'pass',
			'guest',
			'user',
			'test',
			'123',
			'123456',
			'12345678',
			'012345',
			'pass',
			'010203',
		);

		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'users';

		$users = $wpdb->get_results( $sql, ARRAY_A );

		$pass_easy = array();

		foreach ( $users as $user ) {
			foreach ( $passwords as $pass ) {
				if ( wp_check_password( $pass, $user['user_pass'], $user['ID'] ) ) {
					array_push( $pass_easy, $user );
				}
			}
		}

		return $pass_easy;
	}

	/**
	 * check the version of php that WordPress is running on.
	 *
	 * @return void
	 */
	public function check_php_version_defaults_callback() {
		$version = $this->get_php_version_defaults_callback();

		$this->show_card_php_version(
			'Checks whether the version of PHP used is an actively supported version.',
			$version['message'],
			$version['status']
		);
	}

	public function get_php_version_defaults_callback() {

		$php = explode( '.', phpversion() );
		$php = $php[0] . '.' . $php[1];

		$old_version         = version_compare( $php, '5.6', '<' );
		$not_oficial_version = version_compare( $php, '7.4', '>' );

		if ( $old_version && $not_oficial_version ) {
			$message = 'Your php version is ' . phpversion() . '.   A release that is no longer supported. Users of this release should upgrade as soon as possible, as they may be exposed to unpatched security vulnerabilities.';

			return array(
				'message' => esc_html( $message ),
				'status'  => 'failed',
			);
		} else {
			$phpversions = array(
				'5.6' => array(
					'active'   => '2019-01-01',
					'security' => '2019-01-01',
				),
				'7.0' => array(
					'active'   => '2018-01-01',
					'security' => '2019-01-01',
				),
				'7.1' => array(
					'active'   => '2018-11-30',
					'security' => '2019-11-30',
				),
				'7.2' => array(
					'active'   => '2019-11-30',
					'security' => '2020-11-30',
				),
				'7.3' => array(
					'active'   => '2020-12-06',
					'security' => '2021-12-06',
				),
				'7.4' => array(
					'active'   => '2021-11-28',
					'security' => '2022-11-28',
				),
			);

			if ( strtotime( $phpversions[ $php ]['active'] ) > strtotime( Date( 'Y-m-d' ) ) ) {
				$message = 'Your php version is ' . phpversion() . '. A release that is being actively supported. Reported bugs and security issues are fixed and regular point releases are made.';

				return array(
					'message' => esc_html( $message ),
					'status'  => 'success',
				);
			} elseif ( strtotime( $phpversions[ $php ]['security'] ) > strtotime( Date( 'Y-m-d' ) ) ) {
				$message = 'Your php version is ' . phpversion() . '.  A release that is supported for critical security issues only. Releases are only made on an as-needed basis.';

				return array(
					'message' => esc_html( $message ),
					'status'  => 'warning',
				);
			} else {
				$message = 'Your php version is ' . phpversion() . '.   A release that is no longer supported. Users of this release should upgrade as soon as possible, as they may be exposed to unpatched security vulnerabilities.';

				return array(
					'message' => esc_html( $message ),
					'status'  => 'failed',
				);
			}
		}
	}

	/**
	 * Show_card_users
	 *
	 * @param string $title Titulo do box.
	 * @param string $message Mensagem apresentada para o usuário.
	 * @param string $class Classe do box.
	 *
	 * @return void
	 */
	private function show_card_php_version( $title, $message, $class ) {
		printf(
			'<div class="card card_max_size card-custom card-dark">
                    <p style="margin-bottom: 5px !important;">%s</p>
                    <div class="card card_%s">%s</div>
                </div>',
			esc_html( $title ),
			esc_attr( $class ),
			esc_html( $message )
		);
	}

	/**
	 * checks that folder and file permissions are correct.
	 *
	 * @return void
	 */
	public function check_permission_files_defaults_callback() {
		$files_wrong_perms = $this->find_files_wrong_permission( ABSPATH );

		$this->show_card_files_wrong_perms( $files_wrong_perms );
	}

	public function find_files_wrong_permission( $path ) {
		$files_wrong_perms = array();

		$skipped_files = array(
			'.',
			'..',
			'.git',
		);

		$files = scandir( $path );

		foreach ( $files as $file ) {
			if ( in_array( $file, $skipped_files ) ) {
				continue;
			}
			if ( is_dir( $path . $file ) ) {
				$this->scan_directory( $path . $file );
			} else {
				if ( ! is_file( $path . $file ) ) {
					continue;
				}

				$file_parts = pathinfo( $path . $file );

				if ( empty( $file_parts['extension'] ) ) {
					continue;
				}

				if ( $file_parts['extension'] != 'html' && $file_parts['extension'] != 'js' && $file_parts['extension'] != 'xml' && $file_parts['extension'] != 'php' ) {
					continue;
				}

				$perm = $this->file_perms( $path . $file, true );
				if ( $perm !== '644' && $perm !== '0644' ) {
					array_push(
						$files_wrong_perms,
						array(
							'path' => $path . $file,
							'perm' => $perm,
						)
					);
				}
			}
		}

		return $files_wrong_perms;
	}

	private function scan_directory( $path ) {

		$skipped_files = array(
			'.',
			'..',
			'.git',
		);

		$files = scandir( $path );

		foreach ( $files as $file ) {
			if ( in_array( $file, $skipped_files ) ) {
				continue;
			}
			$realpath_file = $path . '/' . $file;

			if ( is_dir( $realpath_file ) ) {
				$perm = $this->file_perms( $realpath_file, true );
				if ( $perm !== '755' && $perm !== '0755' ) {
					array_push(
						$this->files_wrong_perms,
						array(
							'path' => $realpath_file,
							'perm' => $perm,
						)
					);
				}
				$this->scan_directory( $realpath_file );
			} else {
				if ( ! is_file( $realpath_file ) ) {
					continue;
				}

				$file_parts = pathinfo( $path . $file );

				if ( ! empty( $file_parts['extension'] ) && $file_parts['extension'] != 'html' && $file_parts['extension'] != 'js' && $file_parts['extension'] != 'xml' && $file_parts['extension'] != 'php' ) {
					continue;
				}

				$perm = $this->file_perms( $realpath_file, true );
				if ( $perm !== '644' && $perm !== '0644' ) {
					array_push(
						$this->files_wrong_perms,
						array(
							'path' => $realpath_file,
							'perm' => $perm,
						)
					);
				}
			}
		}
	}

	private function file_perms( $file, $octal = false ) {
		if ( ! file_exists( $file ) ) {
			return false;
		}

		$perms = fileperms( $file );

		$cut = $octal ? 2 : 3;

		return substr( decoct( $perms ), $cut );
	}

	private
	function show_card_files_wrong_perms(
		$files_wrong_perms
	) {
		?>
        <div class="card card_max_size  card-custom card-dark">
        <p style="margin-bottom: 5px !important;">
			<?php
			printf( esc_html__( 'Checks whether your WordPress files have the expected permissions.' ) )
			?>
        </p>
		<?php
		if ( count( $files_wrong_perms ) > 0 ) :
			?>
            <div id="result_file_perms">
                <div class="card card_danger">You have files with the wrong permissions, this can cause security and
                    even
                    malfunction for your WordPress. Below is the list of problem files.
                </div>
                <br/>
                <table class="widefat" style="width: 100%;">
                    <thead>
                    <tr>
                        <th class="row-title"><?php esc_html_e( 'Path', $this->plugin_name ); ?></th>
                        <th class="row-title"><?php esc_html_e( 'Permission', $this->plugin_name ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					foreach ( $files_wrong_perms as $key => $file ) {
						$class = ! ( $key % 2 ) ? '' : "class='alternate'";
						$path  = $file['path'];
						$perm  = $file['perm'];
						printf(
							'<tr %s>
						        <td class="row-title">
						            <label for="tablecell">%s</label>
						        </td>
						        <td class="row-title">
						            <label for="tablecell">%s</label>
						        </td>
						    </tr>',
							esc_attr( $class ),
							esc_html( $path ),
							esc_html( $perm )
						);
					}
					?>
                    </tbody>
                </table>
                <br/>
                <div>Correct your WordPress folder and file permissions following the instructions in your WordPress
                    documentation. To know more <a
                            href="https://wordpress.org/support/article/hardening-wordpress/#changing-file-permissions"
                            target="_black">click here</a>.</br>
                    <div>
                        You can also correct the scripts by clicking here.
                        <a class="button" name="fix_permission"
                           href="?page=<?php printf( '%s&tab=hardening', $this->plugin_name ); ?>">Fix
                            permissions </a>
                    </div>
                </div>
            </div>
		<?php
		else :
			?>
            <div id="result_file_perms">
                <div class="card card_success">You don't have files with the wrong permissions.</div>
                <br/>
            </div>
		<?php endif; ?>
		<?php
	}

}
