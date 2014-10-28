<?php

global $directory;
global $extm, $theme_updater_admin, $auth_manager_admin;
if(!function_exists('WP_Filesystem'))
	require_once(ABSPATH . 'wp-admin/includes/file.php');
WP_Filesystem();
global $wp_filesystem;
	
$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';

$current_page = 1;

if ( isset( $_REQUEST['current_page'] ) ) {
	$current_page = $_REQUEST['current_page'];
}

$page = $current_page - 1;
$search = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';

$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=> "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n"
	)
);
$context = stream_context_create($opts);
// $response_pre = file_get_contents($directory->extensions_server_url . "get_extensions&search={$search}&page={$page}", false, $context);
$response_pre = file_get_contents($directory->extensions_server_url . "get_extensions", false, $context);
$response_pre = (isset($response_pre) && !empty($response_pre))? json_decode( $response_pre ) : (object)$response_pre;

if ( !isset($response_pre->on_page) || (isset($response_pre->on_page) && $response_pre->on_page == 0) ) {
	$response_pre->on_page = 1;
}

$postdata = array(
	'runway_token' => (isset($auth_manager_admin->token)) ? $auth_manager_admin->token : '',
	'extensions' => $response_pre->extensions
);
$post_args = array(
	'method' => 'POST',
	'header'=> "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n",
	'timeout' => 10,
	'body' => $postdata
    );

$response_json = wp_remote_post($theme_updater_admin->url_update_exts.'/wp-admin/admin-ajax.php?action=sync_downloads', $post_args);
$this->extensions_Paid = array();
if(!is_a($response_json, 'WP_Error') && isset($response_json['body']) && $response_json['body'] !== '[]') {
	$this->extensions_Paid = json_decode($response_json['body']);
}

// foreach($this->extensions_Paid as $item_shop) {
// 	$item_name = str_replace('-', '_', sanitize_key($item_shop->Files[0]->name));
// }

if ( isset( $_GET['action'] ) && $_GET['action'] == 'install' ) {
	$item = $_GET['item'];
	$extension_zip_file_name = $directory->downloads_dir . $item . '.zip';
	$paid_arr = (array)$this->extensions_Paid;
	$zipPath = (isset($paid_arr[$item]->Path)) ? '&zip='.$paid_arr[$item]->Path : '';

	$opts = array(
	  'http'=>array(
	    'method'=>"GET",
	    'header'=> "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n"
		)
	);
	$context = stream_context_create($opts);
	$extension_zip = file_get_contents($directory->extensions_server_url . "download_extension&item={$item}".$zipPath, false, $context);

	if(strlen($extension_zip) !== 0) {
		$extension_zip = runway_base_decode( $extension_zip, true );

		$wp_filesystem->put_contents($extension_zip_file_name, $extension_zip, FS_CHMOD_FILE);

		$permissions = substr(sprintf('%o', fileperms($extension_zip_file_name)), -4);
		if($permissions < '0755')
			chmod( $extension_zip_file_name, 0755 );

		echo $extm->load_new_extension( $extension_zip_file_name );
	}
	else {
		if(!empty($response_json)) {
			$paid_arr = (array)$this->extensions_Paid;
			$ext_name = empty($this->extensions_Paid)?  $extm->extensions_List[$item.'/load.php']['Name'] : $paid_arr[$item]->Name;
			echo '<div id="message" class="error"><p>' . rf__('The error upon an attempt to install '.$ext_name.' extension') . '</p></div>';
		}
	}
}

// Load the content
switch ( $tab ) {

default: {

		$response_exts = $response_pre->extensions;
		$response = $response_pre;
		unset($response->extensions);

		if(isset($response_exts)) {
			foreach($response_exts as $key => $resp_ext) {
				if (isset($this->extensions_Paid) && !empty($this->extensions_Paid)) {	
					foreach($this->extensions_Paid as $exts) {
						// Overwrite if also exists as product entery.
						$response->extensions[$key] = $resp_ext;
						$response->extensions[$key]->Version = $resp_ext->Version;
						if($key == sanitize_title($exts->Name) && runway_check_versions($exts->Version, $resp_ext->Version)) {
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

		//include_once 'views/browse.php';
		include_once 'views/add-ones.php';

	} break;
}

?>
