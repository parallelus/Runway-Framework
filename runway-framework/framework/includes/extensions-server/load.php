<?php
/*
	Extension Name: Extensions server
	Extension URI:
	Version: 0.8
	Description: Find new extensions and add-ons for the Runway framework.
	Author:
	Author URI:
	Text Domain:
	Domain Path:
	Network:
	Site Wide Only:
*/

// Settings
$fields = array(
	'var' => array(),
	'array' => array()
);

$default = array();

global $settingshortname;

$settings = array(
	'name' => __('Add-ons Server', 'framework'),
	'alias' => 'server',
	'option_key' => $settingshortname.'extensions-server',
	'fields' => $fields,
	'default' => $default,
	'parent_menu' => 'hidden',
	'menu_permissions' => 'administrator',
	'file' => __FILE__,
);

global $server, $Server_Admin;

// Required components
include 'object.php';

$server = new Server_Settings_Object( $settings );

// Load admin components
if ( is_admin() ) {
	include 'settings-object.php';
	$Server_Admin = new Server_Admin( $settings );
}

do_action( 'extensions_server_is_load' );

// Setup a custom button in the title
function title_button_upload_exts( $title ) {
	if ( $_GET['page'] == 'server' ) {
		$title .= ' <a href="'.admin_url('admin.php?page=server&navigation=add-extension').'" class="add-new-h2">'. __( 'Upload New', 'framework' ) .'</a> <a href="'.admin_url('admin.php?page=directory').'" class="add-new-h2">'. __( 'Search Directory', 'framework' ) .'</a>';
	}
	return $title;
}
add_filter( 'framework_admin_title', 'title_button_upload_exts' );

add_action('add_report', 'extensions_server_report');

function extensions_server_report($reports_object){
	$upload_dir = wp_upload_dir( );
	$downloads_dir = $upload_dir['basedir'].'/downloads-directory';
	$reports_object->assign_report(array(
		'source' => 'Extensions Server',
		'report_key' => 'download_dir_exists',
		'path' => $downloads_dir,
		'success_message' => __('Downlads directory', 'framework') . ' (' . $downloads_dir . ') ' . __('is exists', 'framework') . '.',		
		'fail_message' => __('Downlads directory', 'framework') . ' (' . $downloads_dir . ') ' . __('is not exists', 'framework') . '.',		
	), 'FILE_EXISTS' );

	$reports_object->assign_report(array(
		'source' => 'Extensions Server',
		'report_key' => 'download_dir_writable',
		'path' => $downloads_dir,
		'success_message' => __('Downlads directory', 'framework') . ' (' . $downloads_dir . ') ' . __('is writable', 'framework') . '.',		
		'fail_message' => __('Downlads directory', 'framework') . ' (' . $downloads_dir . ') ' . __('is not writable', 'framework') . '.',		
	), 'IS_WRITABLE' );	

	$sources_dir = $upload_dir['basedir'].'/downloads-sources/';
	$reports_object->assign_report(array(
		'source' => 'Extensions Server',
		'report_key' => 'download_sources_dir_exists',
		'path' => $sources_dir,
		'success_message' => __('Sources directory', 'framework') . ' (' . $sources_dir . ') ' . __('is exists', 'framework') . '.',		
		'fail_message' => __('Sources directory', 'framework') . ' (' . $sources_dir . ') ' . __('is not exists', 'framework') . '.',	
	), 'FILE_EXISTS' );

	$reports_object->assign_report(array(
		'source' => 'Extensions Server',
		'report_key' => 'download_sources_dir_writable',
		'path' => $sources_dir,
		'success_message' => __('Sources directory', 'framework') . ' (' . $sources_dir . ') ' . __('is writable', 'framework') . '.',		
		'fail_message' => __('Sources directory', 'framework') . ' (' . $sources_dir . ') ' . __('is not writable', 'framework') . '.',
	), 'IS_WRITABLE' );	
}

?>
