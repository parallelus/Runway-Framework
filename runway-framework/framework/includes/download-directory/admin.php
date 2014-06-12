<?php

global $directory;
global $extm;
if(!function_exists('WP_Filesystem'))
	require_once(ABSPATH . 'wp-admin/includes/file.php');
WP_Filesystem();
global $wp_filesystem;
	
$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';

if ( isset( $_GET['action'] ) && $_GET['action'] == 'install' ) {
	$item = $_GET['item'];

	//$extension_zip = file_get_contents( $directory->extensions_server_url . "download_extension&item={$item}", false );
	$extension_zip = $wp_filesystem->get_contents( $directory->extensions_server_url . "download_extension&item={$item}");
	$extension_zip = base64_decode( $extension_zip );
	$extension_zip_file_name = $directory->downloads_dir . $item . '.zip';
	
	$wp_filesystem->put_contents($extension_zip_file_name, $extension_zip, FS_CHMOD_FILE);
	//file_put_contents( $extension_zip_file_name, $extension_zip );
	chmod( $extension_zip_file_name, 0777 );

	echo $extm->load_new_extension( $extension_zip_file_name );
}

// Load the content
switch ( $tab ) {

default: {

		$current_page = 1;

		if ( isset( $_REQUEST['current_page'] ) ) {
			$current_page = $_REQUEST['current_page'];
		}

		$page = $current_page - 1;
		$search = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';
		//$response = file_get_contents( $directory->extensions_server_url . "get_extensions&search={$search}&page={$page}", false );
		$response = $wp_filesystem->get_contents( $directory->extensions_server_url . "get_extensions&search={$search}&page={$page}");
		$response = json_decode( $response );

		if ( $response->on_page == 0 ) {
			$response->on_page = 1;
		}

		include_once 'views/browse.php';
//		include_once 'views/downloads.php';

	} break;
}

?>
