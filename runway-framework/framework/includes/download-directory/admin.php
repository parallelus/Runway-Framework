<?php

global $directory;
global $extm, $theme_updater_admin, $auth_manager_admin;
if(!function_exists('WP_Filesystem'))
	require_once(ABSPATH . 'wp-admin/includes/file.php');
WP_Filesystem();
global $wp_filesystem;
	
$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';

$response_pre = $wp_filesystem->get_contents( $directory->extensions_server_url . "get_extensions&search={$search}&page={$page}", false);
$response_pre = json_decode( $response_pre );

$postdata = array(
	'runway_token' => (isset($auth_manager_admin->token)) ? $auth_manager_admin->token : '',
	'extensions' => $response_pre->extensions
);

$post_args = array(
	'method' => 'POST',
	'timeout' => 10,
	'body' => $postdata
    );

$response_json = wp_remote_post($theme_updater_admin->url_update_exts.'/wp-admin/admin-ajax.php?action=sync_downloads', $post_args);

$this->extensions_Paid = array();
if(!empty($response_json)) {
	$this->extensions_Paid = json_decode($response_json['body']);
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'install' ) {
	$item = $_GET['item'];
	$extension_zip_file_name = $directory->downloads_dir . $item . '.zip';
	$zipPath = (isset($this->extensions_Paid[0]->Path)) ? '&zip='.$this->extensions_Paid[0]->Path : '';

	$extension_zip = $wp_filesystem->get_contents( $directory->extensions_server_url . "download_extension&item={$item}".$zipPath);
	if(strlen($extension_zip) !== 0) {
	//	$extension_zip = base64_decode( $extension_zip );
		$extension_zip = runway_base_decode( $extension_zip, true );

		$wp_filesystem->put_contents($extension_zip_file_name, $extension_zip, FS_CHMOD_FILE);

		chmod( $extension_zip_file_name, 0755 );

		echo $extm->load_new_extension( $extension_zip_file_name );
	}
	else {
		if(!empty($response_json)) {
			$ext_name = empty($this->extensions_Paid)?  $extm->extensions_List[$item.'/load.php']['Name'] : $response_pre->extensions->$item->Name;
			echo '<div id="message" class="error"><p>' . rf__('The error upon an attempt to install '.$ext_name.' extension') . '</p></div>';
		}
	}
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

		if ( $response_pre->on_page == 0 ) {
			$response_pre->on_page = 1;
		}

		$response_exts = $response_pre->extensions;
		$response = $response_pre;
		unset($response->extensions);

		if(isset($response_exts)) {
			foreach($response_exts as $key => $resp_ext) {
				if (isset($this->extensions_Paid) && !empty($this->extensions_Paid)) {	
					foreach($this->extensions_Paid as $exts) {
						// Overwrite if also exists as product entery.
						if($resp_ext->Name == trim($exts->Name) && $resp_ext->Version != $exts->Version) {
							$response->extensions[$key] = $resp_ext;
							$response->extensions[$key]->Version = $exts->Version;
						}
					}
				} else {
					// From Directory/Download Server
					$response->extensions[$key] = $resp_ext;
					$response->extensions[$key]->Version = $resp_ext->Version;
				}
			}
		}

		include_once 'views/browse.php';

	} break;
}

?>
