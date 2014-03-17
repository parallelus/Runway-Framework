	<?php

global $developer_tools, $Themes_Manager, $extm;
$extensions_dir = get_template_directory() . '/framework/extensions/';

$required = '<em class="required">' . __( 'Required', 'framework' ) . '</em>';
$_data = $developer_tools->data;

$themes_path = explode( '/', get_template_directory() );
unset( $themes_path[count( $themes_path ) - 1] );
$themes_path = implode( '/', $themes_path );

$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
switch ( $action ) {
case 'delete-package':{
		$package = isset( $_REQUEST['package'] ) ? $_REQUEST['package'] : '';
		$name = isset( $_REQUEST['name'] ) ? $_REQUEST['name'] : '';
		if ( $name != '' && $package != '' ) {
			$alone_theme_file = "$name-($package).a.zip";
			$child_theme_file = "$name-($package).c.zip";
			$download_dir = $developer_tools->themes_path."/$name/download/";
			if ( unlink( $download_dir.$alone_theme_file ) ) {
				// out message
			}

			if ( unlink( $download_dir.$child_theme_file ) ) {
				// out message
			}
		}
	} break;
}

switch ( $this->navigation ) {
case 'do-package': {

		if ( isset( $_REQUEST['name'] ) ) {
			$vals['developer_tools'] = $developer_tools;
			$vals['Themes_Manager_Admin'] = $developer_tools;
			$this->view( 'download', false, $vals );
		} else {
			echo 'oops...';
		}
	} break;

case 'do-download': {
		$theme_settings = $developer_tools->load_settings( $_REQUEST['name'] );
		$history = $theme_settings['History'];

		$vals['developer_tools'] = $developer_tools;
		$vals['Themes_Manager_Admin'] = $developer_tools;
		$this->view( 'download', false, $vals );
	} break;

case 'duplicate-theme': {
		/* under construction */
		if ( isset( $_REQUEST['name'] ) && isset( $_REQUEST['new_name'] ) ) {
			$options = $developer_tools->make_theme_copy( $_REQUEST['name'], $_REQUEST['new_name'] );

			$link = home_url().'/wp-admin/admin.php?page=themes&navigation=edit-theme&name='.$_REQUEST['new_name'];
			$redirect = '<script type="text/javascript">window.location = "'.$link.'";</script>';
			echo $redirect;
		}
	} break;

case 'edit-theme': {
		$developer_tools->mode = 'edit';

		if ( isset( $_REQUEST['save'] ) ) {
			$post = stripslashes_deep( $_POST['theme_options'] );
			$errors = $developer_tools->validate_theme_settings( $post );
			if ( count( $errors ) ) {
				$options = $post;
				$this->view( 'theme-conf' );
			} else {
				$options = $developer_tools->build_and_save_theme( $post, false );

				if ( $post['Folder'] != $post['old_folder_name'] ) {
					update_option( 'stylesheet', $post['Folder'] );
				}

				$ts = time();
				$history = $developer_tools->get_history( $options['Folder'] );
				$alone_package_download_url = $developer_tools->build_alone_theme( $options['Folder'], $ts );
				$child_package_download_url = $developer_tools->build_child_package( $options['Folder'], $ts );
				$developer_tools->make_package_info_from_ts( $options['Folder'], $ts );

				$link = home_url().'/wp-admin/admin.php?page=themes';
    			$redirect = '<script type="text/javascript">window.location = "'.$link.'";</script>';
    			echo $redirect;
			}
		} else {
			$this->view( 'theme-conf' );
		}
	} break;

case 'delete-theme': {

		if ( isset( $_REQUEST['confirm'] ) ) {
			if ( isset( $_REQUEST['name'] ) && $_REQUEST['name'] != 'runway' ) {
				$developer_tools->delete_child_theme( $_REQUEST['name'] );
			}

			require_once 'views/themes-list.php';
		}
		else {
			if ( isset( $_REQUEST['name'] ) ) {
				$del_theme_info = rw_get_theme_data( $themes_path.'/'.$_REQUEST['name'] );
				include_once 'views/del-theme-confirmation.php';
			}
		}
	} break;

case 'new-theme': {
		$developer_tools->mode = 'new';

		if ( isset( $_POST['theme_options'] ) ) {
			$post = stripslashes_deep( $_POST['theme_options'] );
			$errors = $developer_tools->validate_theme_settings( $post );
			if ( count( $errors ) ) {
				$options = $post;
				$this->view( 'theme-conf' );
			} else {
				$options = $developer_tools->build_and_save_theme( $post );
				require_once 'views/themes-list.php';
			}
		} else {
			$this->view( 'theme-conf' );
		}
	} break;

case 'list-runway-themes': { }

case 'confirm-del-package':{
		$name = $_REQUEST['name'];
		$package = isset( $_REQUEST['package'] ) ? $_REQUEST['package'] : '';
		$alone_theme_file = "$name-($package).a.zip";
		$child_theme_file = "$name-($package).c.zip";
		$package_info = $developer_tools->make_package_info_from_ts( $name, $package );
		include_once 'views/del-package-confirmation.php';
	} break;

default: {
		require_once 'views/themes-list.php';
	} break;
}
?>
