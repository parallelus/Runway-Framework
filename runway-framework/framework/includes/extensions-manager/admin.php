<?php

global $extm, $developer_tools;

$info_message = '';
$exts         = array();
$no_writable  = false;

$link     = admin_url( 'admin.php?page=extensions' );
$redirect = '<script type="text/javascript">window.location = "' . esc_url_raw( $link ) . '";</script>';

if ( ! is_writable( $extm->extensions_dir ) && ! is_writable( $extm->data_dir ) ) {
	$info_message = sprintf( __( '<b>NOTIFICATION</b>: You must have write permissions for %s. All your actions not be saved.', 'runway' ), $extm->extensions_dir );
	$no_writable  = true;
}

$exts               = $extm->get_extensions_list( $extm->extensions_dir );
$ext_upgrade_total  = 0;
$ext_inactive_total = 0;
$ext_all_total      = 0;

if ( ! empty( $exts ) ) {
	$ext_all_total = count( $exts );
	foreach ( $exts as $ext_key => $ext_val ) {
		if ( ! $extm->is_activated( $ext_key ) ) {
			$ext_inactive_total++;
		}
	}
}

switch ( $this->navigation ) {
	case 'extension-activate': { // Activate extension
		check_admin_referer( 'extension-activate' );

		if ( ! $no_writable ) {
			if ( isset( $_GET['ext'] ) ) {
				$info_message = $extm->activate_extension( $_GET['ext'] );
			} elseif ( isset( $_GET['dep-exts'] ) ) {
				$dep_exts_list = explode( ',', $_GET['dep-exts'] );
				$info_message  = $extm->activate_extensions_without_resolution_check( $dep_exts_list );
			}
		} else {
			$info_message = sprintf(
				__( '<b>ERROR</b>: You must have write permissions for %s. All your actions not be saved.', 'runway' ),
				$extm->extensions_dir
			);
		}

		echo rf_string( $redirect ); // escaped above
	}
		break;

	case 'extension-deactivate': { // Deactivate extension
		check_admin_referer( 'extension-deactivate' );

		if ( ! $no_writable ) {
			$also_deactivate = $extm->get_dependent_extensions( 'options-builder/load.php' );
			if ( isset( $_GET['ext'] ) ) {
				$info_message = $extm->deactivate_extension( $_GET['ext'] );
			}
		} else {
			$info_message = sprintf(
				__( '<b>ERROR</b>: You must have write permissions for %s. All your actions not be saved.', 'runway' ), 
				$extm->extensions_dir 
			);
		}
		echo rf_string( $redirect ); // escaped above

	}
		break;

	case 'add-extension': { // Add new extension
		if ( ! isset( $_POST['ext-submit'] ) ) {
			if ( is_writable( $extm->extensions_dir ) ) {
				include_once __DIR__ . '/views/add-extension.php';
			} else {
				echo __( 'Extension dir must be writable to add new extension', 'runway' );
			}
		} else {
			if ( empty( $_POST ) || ! wp_verify_nonce( $_POST['extension-upload-field'], 'extension-upload-action' ) ) {
				print __( 'Sorry, your nonce did not verify.', 'runway' );
				exit;
			} else {
				if ( isset( $_FILES['extzip']['name'] ) && $_FILES['extzip']['name'] != '' ) {
					$exploded = explode( '.', $_FILES['extzip']['name'] );
					$file_ext = array_pop( $exploded );
					if ( $file_ext == 'zip' ) {
						$info_message = $extm->load_new_extension( $_FILES['extzip']['tmp_name'] );
						$exts         = $extm->get_extensions_list( $extm->extensions_dir );
						include_once __DIR__ . '/views/admin-home.php';
					} else {
						$info_message = __( 'File must have <b>.zip</b> extension. Please choose another file.', 'runway' );
						include_once __DIR__ . '/views/add-extension.php';
					}
				} else {
					$info_message = __( 'Select a file', 'runway' );
					include_once __DIR__ . '/views/add-extension.php';
				}
			}
		}
	}
		break;

	case 'del-extension-confirm': {
		include_once __DIR__ . '/views/del-extension-confirmation.php';
	}
		break;

	case 'del-extension': { // Delete extension
		check_admin_referer( 'del-extension' );

		if ( ! $no_writable && $_GET['confirm'] == 'true' ) {
			if ( isset( $extm->admin_settings['extensions'][ $extm->theme_name ] ) && isset( $_GET['ext'] ) ) {
				$info_message = $extm->del_extension( urldecode( $_GET['ext'] ) );
			}
		} else {
			$info_message = sprintf(
				__( '<b>ERROR</b>: You must have write permissions for %s. All your actions not be saved.', 'runway' ),
				$extm->extensions_dir
			);
		}
		echo rf_string( $redirect ); // escaped above
	}
		break;

	case 'bulk-actions': { // Bulk operations with extensions
		check_admin_referer( 'extensions-bulk-actions' );

		$no_activated = array();

		if ( isset( $_POST['bulk-actions-submit'] ) ) {
			if ( ! $no_writable ) {

				$ext_chk = ( array_key_exists( 'ext_chk', $_POST ) && is_array( $_POST['ext_chk'] ) ) ? $_POST['ext_chk'] : array();
				if ( in_array( 'on', $ext_chk ) ) {
					unset( $ext_chk[ array_search( 'on', $ext_chk ) ] ); // remove checkbox for all
				}

				switch ( $_POST['action'] ) {
					case 'activate-selected': {
						$dep_exts       = array();
						$deps_names     = array();
						$to_active_list = array();
						foreach ( $ext_chk as $cheked ) {
							if ( $cheked != '' ) {
								$deps   = $extm->get_extension_dependencies( $extm->extensions_dir . $cheked );
								$tmp_dp = array();

								if ( ! empty( $deps ) && is_array( $deps ) ) {
									foreach ( $deps as $key => $dep ) {
										if ( ! $extm->is_activated( $dep ) ) {
											$tmp_dp[] = $dep;
										}
									}
								}

								if ( empty( $tmp_dp ) ) {
									$info_message = $extm->activate_extension( $cheked );
								} else {
									$to_active_list[] = $cheked;
									$dep_exts         = array_merge( $tmp_dp, $dep_exts );
									$dep_exts         = array_unique( $dep_exts );

									$tmp          = $extm->get_extension_data( $extm->extensions_dir . $cheked );
									$deps_names[] = $tmp['Name'];
								}
							}
						}

						if ( ! empty( $dep_exts ) ) {
							$deps_list = '<b>' . implode( ', ',$deps_names ) . '</b> - ' .
							             __( 'extensions not activate. To activate this extension you must activate next extensions', 'runway' ) . ':<ul>';

							foreach ( $dep_exts as $dep_ext ) {
								$dep_info = $extm->get_extension_data( $extm->extensions_dir . $dep_ext );
								$deps_list .= '<li><b>- ' . $dep_info['Name'] . '</b></li>';
							}

							$dep_exts = array_merge( $dep_exts, $to_active_list );
							$deps_list .= '</ul>';
							$deps_list .= '<b><a href="' . admin_url( 'admin.php?page=extensions&navigation=extension-activate&dep-exts=' . implode( ',',	$dep_exts ) ) .
							              '">' . __( 'Activate dependencies and selected extensions', 'runway' ) . '</a></b>';
							$info_message = $deps_list;
						}
					}
						break;

					case 'deactivate-selected': {
						$info_message = 'Extensions deactivated';
						foreach ( $ext_chk as $cheked ) {
							if ( $cheked != '' ) {
								foreach ( $extm->admin_settings['extensions'][ $extm->theme_name ]['active'] as $key => $value ) {
									if ( $value == $cheked ) {
										$info_message = $extm->deactivate_extension( $cheked ) . '<br>';
									}
								}
							}
						}
					}
						break;

					case 'delete-selected': {
						foreach ( $ext_chk as $cheked ) {
							if ( $cheked != '' ) {
								$info_message = $extm->del_extension( $cheked );
							}
						}
					}
						break;

					default: {
						$info_message = __( 'Please, select the action', 'runway' );
					}
				}
			} else {
				$info_message = sprintf(
					__( '<b>ERROR</b>: You must have write permissions for %s. All your actions not be saved.', 'runway' ), 
					$extm->extensions_dir 
				);
			}
		}
		echo rf_string( $redirect ); // escaped above
	}
		break;

	case 'search': {
		if ( $_POST['exts-search-input'] != '' && isset( $_POST['exts-search-input'] ) ) {
			$exts = $extm->get_extensions_list( $extm->extensions_dir );
			$exts = $extm->search_exts( $exts, $_POST['exts-search-input'] );
		}
		include_once __DIR__ . '/views/admin-home.php';
	}
		break;

	case 'inactive': {
		$exts = $extm->get_extensions_list( $extm->extensions_dir );
		$tmp  = array();
		foreach ( $exts as $ext_key => $ext_val ) {
			if ( ! $extm->is_activated( $ext_key ) ) {
				$tmp[ $ext_key ] = $ext_val;
			}
		}
		$exts = $tmp;
		unset( $tmp );
		include_once __DIR__ . '/views/admin-home.php';
	}
		break;
	
	default : {// Default
		$exts = $extm->get_extensions_list( $extm->extensions_dir );
		include_once __DIR__ . '/views/admin-home.php';
	}
		break;
	
}

// Function to recursively remove a directory
if ( ! function_exists( 'rrmdir' ) ) {
	function rrmdir( $dir ) {
		foreach ( glob( $dir . '/*' ) as $file ) {
			if ( is_dir( $file ) ) {
				rrmdir( $file );
			} else {
				unlink( $file );
			}
		}
		rmdir( $dir );
	}
}
