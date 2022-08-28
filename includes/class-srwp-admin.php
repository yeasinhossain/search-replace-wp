<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Registers styles and scripts, adds the custom administration page,
 * and processes user input on the "search/replace" form.
 *
 * @link       https://yeasin.me
 * @since      1.0.0
 *
 * @package    Search_Replace_WP
 * @subpackage Search_Replace_WP/includes
 * @author     Yeasin
 */

// Prevent direct access.
if ( ! defined( 'SRWP_PATH' ) ) exit;

class SRWP_Admin {

	/**
	 * Register any CSS and JS used by the plugin.
	 * @since    1.0.0
	 * @access 	 public
	 * @param    string $hook Used for determining which page(s) to load our scripts.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'tools_page_search-replace-wp' === $hook ) {
			wp_enqueue_style( 'search-replace-wp', SRWP_URL . 'assets/css/search-replace-wp.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'jquery-style', SRWP_URL . 'assets/css/jquery-ui.min.css', array(), $this->version, 'all' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'search-replace-wp', SRWP_URL . 'assets/js/search-replace-wp.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );

			wp_localize_script( 'search-replace-wp', 'srwp_object_vars', array(
				'page_size' 	=> get_option( 'srwp_page_size' ) ? absint( get_option( 'srwp_page_size' ) ) : 18000,
				'max_results'	=> get_option( 'srwp_max_results' ) ? absint( get_option( 'srwp_max_results' ) ) : 65,
				'endpoint' 		=> SRWP_AJAX::get_endpoint(),
				'ajax_nonce' 	=> wp_create_nonce( 'srwp_ajax_nonce' ),
				'no_search' 	=> __( 'No search string was defined, please enter a URL or string to search for.', 'search-replace-wp' ),
				'no_tables' 	=> __( 'Please select the tables that you want to update.', 'search-replace-wp' ),
				'unknown' 		=> __( 'An error occurred processing your request. Try decreasing the "Max Page Size", or contact support.', 'search-replace-wp' ),
				'processing'	=> __( 'Processing...', 'search-replace-wp' )
			) );

		}
	}

	/**
	 * Register any menu pages used by the plugin.
	 * @since  1.0.0
	 * @access public
	 */
	public function srwp_menu_pages() {
		$cap = apply_filters( 'srwp_capability', 'install_plugins' );
		add_submenu_page( 'tools.php', __( ' Search & Replace WP', 'search-replace-wp' ), __( ' Search & Replace WP', 'search-replace-wp' ), $cap, 'search-replace-wp', array( $this, 'srwp_menu_pages_callback' ) );
	}

	/**
	 * The callback for creating a new submenu page under the "Tools" menu.
	 * @access public
	 */
	public function srwp_menu_pages_callback() {
		require_once SRWP_PATH . 'templates/srwp-dashboard.php';
	}

	/**
	 * Renders the result or error onto the search-replace-wp admin page.
	 * @access public
	 */
	public static function render_result() {

		if ( isset( $_GET['import'] ) ) {
			echo '<div class="updated"><p>' . __( 'Database imported successfully.', 'search-replace-wp' ) . '</p></div>';
		}

		if ( isset( $_GET['result'] ) && $result = get_transient( 'srwp_results' ) ) {

			if ( isset( $result['dry_run'] ) && $result['dry_run'] === 'on' ) {
				$msg = sprintf( __( '<p><strong>DRY RUN:</strong> <strong>%d</strong> tables were searched, <strong>%d</strong> cells were found that need to be updated, and <strong>%d</strong> changes were made.</p><p><a href="%s" class="thickbox" title="Dry Run Details">Click here</a> for more details, or use the form below to run the search/replace.</p>', 'search-replace-wp' ),
					$result['tables'],
					$result['change'],
					$result['updates'],
					get_admin_url() . 'admin-post.php?action=srwp_view_details&TB_iframe=true&width=800&height=500'
				);
			} else {
				$msg = sprintf( __( '<p>During the search/replace, <strong>%d</strong> tables were searched, with <strong>%d</strong> cells changed in <strong>%d</strong> updates.</p><p><a href="%s" class="thickbox" title="Search/Replace Details">Click here</a> for more details.</p>', 'search-replace-wp' ),
					$result['tables'],
					$result['change'],
					$result['updates'],
					get_admin_url() . 'admin-post.php?action=srwp_view_details&TB_iframe=true&width=800&height=500'
				);
			}

			echo '<div class="updated">' . $msg . '</div>';

		}

	}

	/**
	 * Prefills the given value on the search/replace page (dry run, live run, from profile).
	 * @access public
	 * @param  string $value The value to check for.
	 * @param  string $type  The type of the value we're filling.
	 */
	public static function prefill_value( $value, $type = 'text' ) {

		// Grab the correct data to prefill.
		if ( isset( $_GET['result'] ) && get_transient( 'srwp_results' ) ) {
			$values = get_transient( 'srwp_results' );
		} elseif ( get_option( 'srwp_profiles' ) && isset( $_GET['srwp_profile'] ) ) {

			$profile  = stripslashes( $_GET['srwp_profile'] );
			$profiles = get_option( 'srwp_profiles' );

			if ( isset( $profiles[$profile] ) ) {
				$values = $profiles[$profile];
			} else {
				$values = array();
			}

		} else {
			$values = array();
		}

		// Prefill the value.
		if ( isset( $values[$value] ) ) {

			if ( 'checkbox' === $type && 'on' === $values[$value] ) {
				echo 'checked';
			} else {
				echo str_replace( '#SRWP_BACKSLASH#', '\\', esc_attr( htmlentities( $values[$value] ) ) );
			}

		}

	}

	/**
	 * Loads the tables available to run a search replace, prefilling if already
	 * selected the tables.
	 * @access public
	 */
	public static function load_tables() {

		// Get the tables and their sizes.
		$tables 	= SRWP_DB::get_tables();
		$sizes 		= SRWP_DB::get_sizes();
		$profiles 	= get_option( 'srwp_profiles' );

		echo '<select id="srwp-table-select" name="select_tables[]" multiple="multiple" style="">';

		foreach ( $tables as $table ) {

			// Try to get the size for this specific table.
			$table_size = isset( $sizes[$table] ) ? $sizes[$table] : '';

			if ( isset( $_GET['result'] ) && get_transient( 'srwp_results' ) ) {

				$result = get_transient( 'srwp_results' );

				if ( isset( $result['table_reports'][$table] ) ) {
					echo "<option value='$table' selected>$table $table_size</option>";
				} else {
					echo "<option value='$table'>$table $table_size</option>";
				}

			} elseif ( isset( $_GET['srwp_profile'] ) && 'create_new' !== $_GET['srwp_profile'] ) {

				$profile        = stripslashes( $_GET['srwp_profile'] );
				$profile_tables = array_flip( $profiles[$profile]['select_tables'] );

				if ( isset( $profile_tables[$table] ) ) {
					echo "<option value='$table' selected>$table $table_size</option>";
				} else {
					echo "<option value='$table'>$table $table_size</option>";
				}

			} else {
				echo "<option value='$table'>$table $table_size</option>";
			}

		}

		echo '</select>';

	}

	/**
	 * Loads the result details (via Thickbox).
	 * @access public
	 */
	public function load_details() {

		if ( get_transient( 'srwp_results' ) ) {
			$results	    = get_transient( 'srwp_results' );
			$min 			= ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';
			$srwp_styles	    = SRWP_URL . 'assets/css/search-replace-wp.css?v=' . SRWP_VERSION;
			$table		    = isset( $_GET['table'] ) ? esc_attr( $_GET['table'] ) : '';

			?>
			<link href="<?php echo esc_url( get_admin_url( null, '/css/common' . $min . '.css' ) ); ?>" rel="stylesheet" type="text/css" />
			<link href="<?php echo esc_url( $srwp_styles ); ?>" rel="stylesheet" type="text/css">

			<?php

				if ( isset( $_GET['changes'] ) && isset( $results['table_reports'][$table]['changes'] ) ) {

					printf( '<p id="srwp-back-to-overview"><strong><a href="%s">%s</a></strong></p>', get_admin_url() . 'admin-post.php?action=srwp_view_details', __( '<-- Back to overview', 'search-replace-wp' ) );

					echo '<div id="srwp-details-view-wrap"><table id="srwp-details-view" class="widefat">';

					$search_for   = str_replace( '#SRWP_BACKSLASH#', '\\', htmlentities( $results['search_for'] ) );
					$replace_with = str_replace( '#SRWP_BACKSLASH#', '\\', htmlentities( $results['replace_with'] ) );

					foreach ( $results['table_reports'][$table]['changes'] as $change ) {

						// Escape HTML
						$from_str 	= htmlentities( $change['from'] );

						// Highlight the changes.
						if ( true == $results['case_insensitive'] ) {
							$from_str 	= str_ireplace( $search_for, '<span class="srwp-old-val">' . $search_for . '</span>', $from_str );
						} else {
							$from_str 	= str_replace( $search_for, '<span class="srwp-old-val">' . $search_for . '</span>', $from_str );
						}

						$to_str = str_replace( '<span class="srwp-old-val">' . $search_for . '</span>', '<span class="srwp-new-val">' . $replace_with . '</span>', $from_str );

						echo '<tr class="srwp-row-desc"><td><strong>' . sprintf( __( 'Row %d, Column \'%s\'', 'search-replace-wp' ), $change['row'], $change['column'] ) . '</strong></td></tr>';
						echo '<tr><td class="srwp-change">' . $from_str . '</td><td class="srwp-change">' . $to_str . '</td></tr>';
					}
					echo '</table></div>';
				} else {
					?>
						<div style="padding:10px;">
							<table id="srwp-results-table" class="widefat">
								<thead>
									<tr><th class="srwp-first"><?php _e( 'Table', 'search-replace-wp' ); ?></th><th class="srwp-second"><?php _e( 'Changes Found', 'search-replace-wp' ); ?></th><th class="srwp-third"><?php _e( 'Rows Updated', 'search-replace-wp' ); ?></th><th class="srwp-fourth"><?php _e( 'Time', 'search-replace-wp' ); ?></th></tr>
								</thead>
								<tbody>
								<?php
									foreach ( $results['table_reports'] as $table_name => $report ) {
										$time = $report['end'] - $report['start'];

										if ( $report['change'] != 0 ) {
											$report['change'] = '<strong>' . $report['change'] . '</strong>';

											if ( is_array( $report['changes'] ) ) {
												$report['change'] .= ' <a href="?action=srwp_view_details&changes=true&table=' . $table_name . '">[' . __( 'View', 'search-replace-wp' ) . ']</a>';
											}

										}

										if ( $report['updates'] != 0 ) {
											$report['updates'] = '<strong>' . $report['updates'] . '</strong>';
										}

										if ( 'bsrtmp_' === substr( $table_name, 0, 7 ) ) {
											$table_name = substr( $table_name, 7 );
										}

										echo '<tr><td class="srwp-first">' . $table_name . '</td><td class="srwp-second">' . $report['change'] . '</td><td class="srwp-third">' . $report['updates'] . '</td><td class="srwp-fourth">' . round( $time, 3 ) . __( ' seconds', 'search-replace-wp' ) . '</td></tr>';
									}
								?>
								</tbody>
							</table>
						</div>

					<?php
				}
		}
	}

	/**
	 * Loads a profile.
	 * @access public
	 */
	public function process_load_profile() {
		$profiles = get_option( 'srwp_profiles' ) ? get_option( 'srwp_profiles' ) : array();
		$profile  = stripslashes( $_POST['srwp_profile'] );

		if ( isset( $profiles[ $profile ] ) ) {
			$url = get_admin_url() . 'tools.php?page=search-replace-wp&srwp_profile=' . rawurlencode( $profile );
		} else {
			$url = get_admin_url() . 'tools.php?page=search-replace-wp&srwp_profile=create_new';
 		}

 		wp_redirect( $url );
 		exit;
	}

	/**
	 * Deletes a profile.
	 * @access public
	 */
	public function process_delete_profile() {
		$profiles = get_option( 'srwp_profiles' );
		$profile  = stripslashes( $_POST['srwp_profile'] );

		if ( isset( $profiles[ $profile ] ) ) {
			unset( $profiles[ $profile ] );
		}
		update_option( 'srwp_profiles', $profiles );

		wp_redirect( get_admin_url() . 'tools.php?page=search-replace-wp' );
		exit;
	}

	/**
	 * Gets an array of saved profiles.
	 * @access public
	 * @return array
	 */
	public static function get_profiles() {
		return get_option( 'srwp_profiles' ) ? get_option( 'srwp_profiles' ) : array();
	}

	/**
	 * Saves a profile to the options.
	 * @access public
	 * @param  array $profile An array containing the name and options of the profile.
	 * @return boolean
	 */
	public static function save_profile( $profile ) {
		$profiles 	= get_option( 'srwp_profiles' ) ? get_option( 'srwp_profiles' ) : array();
		$updated 	= array_merge( $profiles, $profile );
		return update_option( 'srwp_profiles', $updated );
	}

	/**
	 * Updates profiles to the current version.
	 * @access public
	 */
	public function upgrade_profiles() {
		if ( get_option( 'srwp_profile_version' ) !== '1.2' ) {

			$profiles = SRWP_Admin::get_profiles();

			if ( empty( $profiles ) ) {
				return;
			}

			foreach ( $profiles as $profile_name => $values ) {

				if ( isset( $profiles[$profile_name]['search'] ) ) {
					$profiles[$profile_name]['search_for'] = $profiles[$profile_name]['search'];
					unset( $profiles[$profile_name]['search'] );
				}

				if ( isset( $profiles[$profile_name]['replace'] ) ) {
					$profiles[$profile_name]['replace_with'] 	= $profiles[$profile_name]['replace'];
					unset( $profiles[$profile_name]['replace'] );
				}

				if ( isset( $profiles[$profile_name]['tables'] ) ) {
					$profiles[$profile_name]['select_tables'] 	= $profiles[$profile_name]['tables'];
					unset( $profiles[$profile_name]['tables'] );
				}

				if ( isset( $profiles[$profile_name]['case_insensitive'] ) && $profiles[$profile_name]['case_insensitive'] == 1 ) {
					$profiles[$profile_name]['case_insensitive'] = 'on';
				} else {
					$profiles[$profile_name]['case_insensitive'] = 'off';
				}

				if ( isset( $profiles[$profile_name]['replace_guids'] ) && $profiles[$profile_name]['replace_guids'] == 1 ) {
					$profiles[$profile_name]['replace_guids'] = 'on';
				} else {
					$profiles[$profile_name]['replace_guids'] = 'off';
				}

			}

			update_option( 'srwp_profiles', $profiles );
			add_option( 'srwp_profile_version', '1.2' );
		}
	}



	/**
	 * Downloads the backup file.
	 * @access public
	 */
	public function download_backup() {
		$cap = apply_filters( 'srwp_capability', 'install_plugins' );
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		$db = new SRWP_DB();

		if ( '' !== get_option( 'srwp_enable_gzip' ) && file_exists( $db->file . '.gz' ) ) {
			$file = $db->file . '.gz';
			$name = 'srwp_db_backup.sql.gz';
		} else {
			$file = $db->file;
			$name = 'srwp_db_backup.sql';
		}

		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );

		readfile( $file );
		die();

	}

	/**
	 * Downloads the system info file for support.
	 * @access public
	 */
	public function download_sysinfo() {
		check_admin_referer( 'srwp_download_sysinfo', 'srwp_sysinfo_nonce' );

		$cap = apply_filters( 'srwp_capability', 'install_plugins' );
		if ( ! current_user_can( $cap ) ) {
			return;
		}

		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="srwp-system-info.txt"' );

		echo wp_strip_all_tags( $_POST['srwp-sysinfo'] );
		die();
	}

	/**
	 * Compresses a file.
	 * @access public
	 *
	 * @param 	string 	$file 	The filename to compress.
	 * @param 	string 	$level 	The compression level to use.
	 * @return 	string|boolean
	 */
	public static function compress_file( $file, $level = 9 ) {
		$dest 	= $file . '.gz';
		$mode 	= 'wb' . $level;
		$error 	= false;

		if ( $fp_out = gzopen( $dest, $mode ) ) {

	        if ( $fp_in = fopen( $file,'rb' ) ) {
	        	while ( ! feof( $fp_in ) ) {
	                gzwrite( $fp_out, fread( $fp_in, 1024 * 512 ) );
	        	}
	            fclose( $fp_in );
	        } else {
	            $error = true;
	        }

	        gzclose( $fp_out );
		} else {
			$error = true;
		}

		if ( $error ) {
			return false;
		}

		return $dest;
	}

	/**
	 * Uncompress a file.
	 * @access public
	 *
	 * @param  string $file The file to uncompress.
	 * @param  string $dest The destination of the uncompressed file.
	 * @return string|boolean
	 */
	public static function decompress_file( $file, $dest ) {

		$error = false;

		if ( $fp_in = gzopen( $file, 'rb' ) ) {

			if ( $fp_out = fopen( $dest, 'w' ) ) {
				while( ! gzeof( $fp_in ) ) {
					$string = gzread( $fp_in, '4096' );
					fwrite( $fp_out, $string, strlen( $string ) );
				}
				fclose( $fp_out );
			} else {
				$error = true;
			}

			gzclose( $fp_in );
		} else {
			$error = true;
		}

		if ( $error ) {
			return false;
		}

		return $dest;
	}

}
