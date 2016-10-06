<?php

global $directory, $extm, $theme_updater_admin, $auth_manager_admin;
$wp_filesystem = get_runway_wp_filesystem();

$search = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';

$exts_addons_server = wp_remote_get( $directory->extensions_server_url . 'get_extensions' );

$items_installed = array();
$addons_type     = isset( $_GET['addons'] ) ? $_GET['addons'] : 'extensions';
switch ( $addons_type ) {
	case 'themes':
		$themes               = wp_get_themes();
		$items_installed      = ( isset( $themes ) && ! empty( $themes ) ) ? array_keys( $themes ) : array();
		$items_installed_link = admin_url( 'admin.php?page=themes' );
		break;

	case 'extensions':
		foreach ( $extm->extensions_List as $key => $val ) {
			$items_installed[] = substr( $key, 0, strpos( $key, '/' ) );
		}
		$items_installed_link = admin_url( 'admin.php?page=extensions' );
		break;

	case 'plugins':
		break;

	default:
		break;

}

$postdata  = array(
	'runway_token' => isset( $auth_manager_admin->token ) ? $auth_manager_admin->token : '',
	'type'         => $addons_type
);

$post_args = array(
	'method'  => 'POST',
	'headers' => array( 'Accept' => 'text/html' ),
	'timeout' => 10,
	'body'    => $postdata
);

$exts_addons = wp_remote_post( $theme_updater_admin->url_update_exts . '/wp-admin/admin-ajax.php?action=sync_downloads', $post_args );

$this->extensions_addons = array();
if ( ! is_wp_error( $exts_addons ) && isset( $exts_addons['body'] ) && $exts_addons['body'] !== '[]' ) {
	$this->extensions_addons = json_decode( $exts_addons['body'] );
}

if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'install' || $_GET['action'] == 'download' ) ) {
	$item                    = $_GET['item'];
	$extension_zip_file_name = $directory->downloads_dir . $item . '.zip';
	$item_file = $item . '.zip';

	$extension_zip = wp_remote_get(
		$directory->extensions_server_url . "download_extension&item={$item_file}",
		array( 'timeout' => 30 )
	);

	if ( ! empty( $extension_zip['body'] ) ) {
		$body = json_decode( $extension_zip['body'], true );
		if ( isset( $body['success'] ) && $body['success'] ) {
			$extension_zip                = runway_base_decode( $body['content'], true );
			$prep_extension_zip_file_name = runway_prepare_path( $extension_zip_file_name );

			$wp_filesystem->put_contents( $prep_extension_zip_file_name, $extension_zip, FS_CHMOD_FILE );

			$permissions = $wp_filesystem->getchmod( $prep_extension_zip_file_name );
			if ( $permissions < '755' ) {
				$wp_filesystem->chmod( $prep_extension_zip_file_name, 0755 );
			}

			if ( $addons_type == 'extensions' ) {
				echo '<div id="message" class="updated"><p>' . $extm->load_new_extension( $extension_zip_file_name ) . '</p></div>';
			} else {
				echo '<div id="message" class="updated"><p>' . __( 'Theme has been downloaded', 'runway' ) . '</p></div>';
			}
		} else {
			include_once __DIR__ . '/views/error-msg.php';
		}
	} else {
		include_once __DIR__ . '/views/error-msg.php';
	}
}

include_once __DIR__ . '/views/add-ons.php';
