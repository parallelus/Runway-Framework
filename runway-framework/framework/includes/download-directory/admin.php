<?php

global $directory;
global $extm, $theme_updater_admin, $auth_manager_admin;
if(!function_exists('WP_Filesystem'))
	require_once(ABSPATH . 'wp-admin/includes/file.php');
WP_Filesystem();
global $wp_filesystem;
	
$search = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';

$exts_addons_server = wp_remote_get($directory->extensions_server_url . "get_extensions");
$extensions_addons_server = (isset($exts_addons_server['body']) && !empty($exts_addons_server['body']))? json_decode( $exts_addons_server['body'] ) : (object)$exts_addons_server['body'];

$addons_type = isset($_GET['addons'])? $_GET['addons'] : 'extensions';

$postdata = array(
	'runway_token' => (isset($auth_manager_admin->token)) ? $auth_manager_admin->token : '',
	'extensions' => $extensions_addons_server->extensions,
	'type' => $addons_type
);
$post_args = array(
	'method' => 'POST',
	'header'=> "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n",
	'timeout' => 10,
	'body' => $postdata
    );

$exts_addons = wp_remote_post($theme_updater_admin->url_update_exts.'/wp-admin/admin-ajax.php?action=sync_downloads', $post_args);
$this->extensions_addons = array();
if(!is_a($exts_addons, 'WP_Error') && isset($exts_addons['body']) && $exts_addons['body'] !== '[]') {
	$this->extensions_addons = json_decode($exts_addons['body']);
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'install' ) {
	$item = $_GET['item'];
	$extension_zip_file_name = $directory->downloads_dir . $item . '.zip';
	$zipPath = (isset($this->extensions_addons->$item->Path)) ? '&zip='.$this->extensions_addons->$item->Path : '';

	$extension_zip = wp_remote_get($directory->extensions_server_url . "download_extension&item={$item}".$zipPath);

	if( !empty($extension_zip['body']) ) {
		$extension_zip = runway_base_decode( $extension_zip['body'], true );

		$wp_filesystem->put_contents($extension_zip_file_name, $extension_zip, FS_CHMOD_FILE);

		$permissions = substr(sprintf('%o', fileperms($extension_zip_file_name)), -4);
		if($permissions < '0755')
			chmod( $extension_zip_file_name, 0755 );

		echo $extm->load_new_extension( $extension_zip_file_name );
	}
	else {
		$ext_err_name = (isset($this->extensions_addons->$item->Name) && !empty($this->extensions_addons->$item->Name))? $this->extensions_addons->$item->Name : __('Unknown', 'framework');
		echo '<div id="message" class="error"><p>' . rf__('The error upon an attempt to install '.$ext_err_name.' extension') . '</p></div>';
	}
}

include_once 'views/add-ons.php';

?>
