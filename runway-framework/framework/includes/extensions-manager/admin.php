<?php

global $extm, $Extm_Admin, $developer_tools;

$info_message = '';
$exts = array();
$no_writable = FALSE;

$link = home_url().'/wp-admin/admin.php?page=extensions';
$redirect = '<script type="text/javascript">window.location = "'.$link.'";</script>';

if ( !is_writable( $extm->extensions_dir ) && !is_writable( $extm->data_dir ) ) {
	$info_message = '<b>NOTIFICATION</b>: You must have write permissions for ' . $extm->extensions_dir.
		'. All your actions not be saved';
	$no_writable = TRUE;	
}

$exts = $extm->get_extensions_list( $extm->extensions_dir );
$ext_upgrade_total = 0;
$ext_inactive_total = 0;
$ext_all_total = 0;

if ( !empty( $exts ) ) {
	$ext_all_total = count( $exts );
	foreach ( $exts as $ext_key => $ext_val ) {
		if ( !$extm->is_activated( $ext_key ) ) {
			$ext_inactive_total++;
		}
	}
}
switch ( $Extm_Admin->navigation ) {
	case 'extension-activate':{	// Activate extension
		if ( !$no_writable ) {
			if ( isset( $_GET['ext'] ) ) {
				$info_message = $extm->activate_extension( $_GET['ext'] );
			}
			elseif ( isset( $_GET['dep-exts'] ) ) {
				$dep_exts_list = explode( ',', $_GET['dep-exts'] );
				$info_message = $extm->activate_extensions_without_resolution_check( $dep_exts_list );
			}
		}
		else {
			$info_message = '<b>ERROR</b>: You must have write permissions for ' . $extm->extensions_dir.
				'. All your actions not be saved';
		}
		echo $redirect;
	} break;	
	case 'extension-deactivate':{ // Deactivate extension
		if ( !$no_writable ) {
			$also_deactivate = $extm->get_dependent_extensions( 'options-builder/load.php' );
			if ( isset( $_GET['ext'] ) ) {
				$info_message = $extm->deactivate_extension( $_GET['ext'] );
			}
		}
		else {
			$info_message = '<b>ERROR</b>: You must have write permissions for ' . $extm->extensions_dir.
				'. All your actions not be saved';
		}
		echo $redirect;

	} break;
	// Add new extension
	case 'add-extension':{
		if ( !$_POST['ext-submit'] ) {
			if ( is_writable( $extm->extensions_dir ) ) {
				include_once 'views/add-extension.php';
			}
			else {
				echo 'Extension dir must be writable to add new extension';
			}
		}
		else {
			if ( empty( $_POST ) || !wp_verify_nonce( $_POST['extension-upload-field'], 'extension-upload-action' ) ) {
				print 'Sorry, your nonce did not verify.';
				exit;
			}
			else {
				if ( isset( $_FILES['extzip']['name'] ) && $_FILES['extzip']['name'] != '' ) {
					$file_ext = array_pop( explode( '.', $_FILES['extzip']['name'] ) );
					if ( $file_ext == 'zip' ) {
						$info_message = $extm->load_new_extension( $_FILES['extzip'] );
						$exts = $extm->get_extensions_list( $extm->extensions_dir );
						include_once 'views/admin-home.php';
					}
					else {
						$info_message = 'File must have <b>.zip</b> extension Please choose another file.';
						include_once 'views/add-extension.php';
					}
				}
				else {
					$info_message = 'Select a file';
					include_once 'views/add-extension.php';
				}
			}
		}
	} break;
	case 'del-extension-confirm':{
		include_once 'views/del-extension-confirmation.php';
	} break;
	// Delete extension
	case 'del-extension':{
		if ( !$no_writable && $_GET['confirm'] == 'true' ) {
			if ( isset( $extm->admin_settings['extensions'][$extm->theme_name] ) && isset( $_GET['ext'] ) ) {
				$info_message = $extm->del_extension( urldecode( $_GET['ext'] ) );
			}
		}
		else {
			$info_message = '<b>ERROR</b>: You must have write permissions for ' . $extm->extensions_dir.
				'. All your actions not be saved';
		}
		echo $redirect;
	} break;
	// Bulk operations with extensions
	case 'bulk-actions':{
		$no_activated = array();
		if ( isset( $_POST['bulk-actions-submit'] ) ) {
			if ( !$no_writable ) {
				switch ( $_POST['action'] ) {
					case 'activate-selected':{
						$ext_chk = $_POST['ext_chk'];
						if($ext_chk[0] == 'on')
							array_shift($ext_chk);  // remove checkbox for all
						$dep_exts = array(); $deps_names = array(); $to_active_list = array();
						foreach ( $ext_chk as $cheked ) 
						{
							if ( $cheked != '' ) {
								$deps = $extm->get_extension_dependencies( $extm->extensions_dir.$cheked );
								$tmp_dp = array();

								if ( !empty( $deps ) && is_array( $deps ) ) {
									foreach ( $deps as $key => $dep ) {
										if ( !$extm->is_activated( $dep ) ) {
											$tmp_dp[] = $dep;
										}
									}
								}

								if ( empty( $tmp_dp ) ) {
									$info_message = $extm->activate_extension( $cheked );
								}
								else {
									$to_active_list[] = $cheked;
									$dep_exts = array_merge( $tmp_dp, $dep_exts );
									$dep_exts = array_unique( $dep_exts );

									$tmp = $extm->get_extension_data( $extm->extensions_dir.$cheked );
									$deps_names[] = $tmp['Name'];
								}
							}
						}

						if ( !empty( $dep_exts ) ) {
							$deps_list = '<b>'.implode( ', ', $deps_names ).'</b> - extensions not activate. To activate this extension you must activate next extensions:<ul>';

							foreach ( $dep_exts as $dep_ext ) {
								$dep_info = $extm->get_extension_data( $extm->extensions_dir.$dep_ext );
								$deps_list .= '<li><b>- '.$dep_info['Name'].'</b></li>';
							}

							$dep_exts = array_merge( $dep_exts, $to_active_list );
							$deps_list .= '</ul>';
							$deps_list .= '<b><a href="admin.php?page=extensions&navigation=extension-activate&dep-exts='.implode( ',', $dep_exts ).'">Activate dependencies and selected extensions</a></b>';
							$info_message = $deps_list;
						}
					} break;
					case 'deactivate-selected':{
						$ext_chk = $_POST['ext_chk']; $info_message = 'Extensions deactivated';
						foreach ( $ext_chk as $cheked ) {
							if ( $cheked != '' ) { 
								foreach ( $extm->admin_settings['extensions'][$extm->theme_name]['active'] as $key => $value ) { 
									if ( $value == $cheked ) { 
										$info_message = $extm->deactivate_extension( $cheked ).'<br>';
									}
								}
							}
						}
					} break;
					case 'delete-selected':{
						$ext_chk = $_POST['ext_chk'];
						foreach ( $ext_chk as $cheked ) {
							if ( $cheked != '' ) {
								$info_message = $extm->del_extension( $cheked );
							}
						}
					} break;
					default: {
						$info_message = 'Please, select the action';
					}
				}
			}
			else {
				$info_message = '<b>ERROR</b>: You must have write permissions for ' . $extm->extensions_dir.
					'. All your actions not be saved';
			}
		}
		echo $redirect;
	} break;
	case 'search':{
		if ( $_POST['exts-search-input'] != '' && isset( $_POST['exts-search-input'] ) ) {
			$exts = $extm->get_extensions_list( $extm->extensions_dir );
			$exts = $extm->search_exts( $exts, $_POST['exts-search-input'] );
		}
		include_once 'views/admin-home.php';
	} break;
	case 'inactive':{
		$exts = $extm->get_extensions_list( $extm->extensions_dir );
		$tmp = array();
		foreach ( $exts as $ext_key => $ext_val ) {
			if ( !$extm->is_activated( $ext_key ) ) {
				$tmp[$ext_key] = $ext_val;
			}
		}
		$exts = $tmp; unset( $tmp );
		include_once 'views/admin-home.php';
	}break;
	// Default
	default : {
		$exts = $extm->get_extensions_list( $extm->extensions_dir );
		include_once 'views/admin-home.php';
	} break;
}

// Function to recursively remove a directory
function rrmdir( $dir ) {
	foreach ( glob( $dir . '/*' ) as $file ) {
		if ( is_dir( $file ) )
			rrmdir( $file );
		else
			unlink( $file );
	}
	rmdir( $dir );
}
?>
