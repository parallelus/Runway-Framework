<?php
switch ( $this->navigation ) {
	case 'add-extension':{
		if ( !isset($_POST['ext-submit']) ) {
			if ( is_writable( $this->downloads_dir ) ) {
				include_once 'views/add-extension.php';
			}
			else {
				echo __('Downloads dir must be writable to add new extension', 'framework');
			}
		}
		else {
			if ( empty( $_POST ) || !wp_verify_nonce( $_POST['extension-upload-field'], 'extension-upload-action' ) ) {
				print __('Sorry, your nonce did not verify.', 'framework');
				exit;
			}
			else {
				if ( isset( $_FILES['extzip']['name'] ) && $_FILES['extzip']['name'] != '' ) {
					$file_path = $_FILES['extzip']['name'];
					$file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
					if ( $file_ext == 'zip' ) {
						move_uploaded_file( $_FILES['extzip']['tmp_name'], $this->downloads_dir.$_FILES['extzip']['name'] );
						$zip = zip_open( $this->downloads_dir.$_FILES['extzip']['name'] );
						if ( $zip ) {
							while ( $zip_entry = zip_read( $zip ) ) {
								$entry_name = zip_entry_name( $zip_entry );
								if ( strstr( $entry_name, 'load.php' ) ) {
									$ext_slug = explode( '/', $entry_name );
									$ext_slug = $ext_slug[0];
									if ( zip_entry_open( $zip, $zip_entry, 'r' ) ) {
										$extension_loadphp = zip_entry_read( $zip_entry, zip_entry_filesize( $zip_entry ) );
										$ext_data = $this->get_extension_data( $extension_loadphp );
										$this->server_extensions[$ext_slug] = $ext_data;
										$this->server_extensions[$ext_slug]['Download_url'] =
											"http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?action=download_extension&item={$ext_slug}";
										update_option( $this->option_key, $this->server_extensions );
										include_once 'views/admin-home.php';
									}
								}
							}
						}
					}
					else {
						$info_message = 'File must have <b>.zip</b> extension Please choose another file.';
					}
				}
				else {
					$info_message = 'Select a file';
					include_once 'views/add-extension.php';
				}
			}
		}
	} break;
	case 'del-extension-confirmation':{
		include_once 'views/del-extension-confirmation.php';
	} break;
	case 'del-extension':{
		if ( $_GET['confirm'] == 'true' ) {
			if ( file_exists( $this->downloads_dir.$_GET['ext'].'.zip' ) ) {
				unlink( $this->downloads_dir.$_GET['ext'].'.zip' );
				unset( $this->server_extensions[$_GET['ext']] );
				update_option( $this->option_key, $this->server_extensions );
				include_once 'views/admin-home.php';
			}
		}
	} break;
	default:{
		include_once 'views/admin-home.php';
	} break;
}
?>
