<?php
/*
	Extension Name: Extension manager
	Extension URI:
	Version: 0.1
	Description: This extensions manageing module
	Author:
	Author URI:
	Text Domain:
	Domain Path:
	Network:
	Site Wide Only:
*/

// Load this section of the admin
$fields = array(
	'var' => array(),
	'array' => array()
);

$default = array();

$settings = array(
	'name' => 'Extensions',
	'option_key' => $shortname.'extensions-manager',
	'fields' => $fields,
	'default' => $default,
	'parent_menu' => 'hidden', // managed by framework
	'menu_permissions' => 'administrator',
	'file' => __FILE__,
);

// Required components
include 'object.php';

$extm = new ExtmSettingsObject( $settings );

// Load admin components
if ( is_admin() ) {
	include 'settings-object.php';
	$Extm_Admin = new Extm_Admin( $settings );
}
do_action( 'extension_manager_is_load' );

// Setup a custom button in the title
function title_button_new_extension( $title ) {
	if ( $_GET['page'] == 'extensions' ) {
		$title .= ' <a href="admin.php?page=extensions&navigation=add-extension" class="add-new-h2">'. __( 'Upload New', 'framework' ) .'</a> <a href="admin.php?page=directory" class="add-new-h2">'. __( 'Search Directory', 'framework' ) .'</a>';
	}
	return $title;
}

add_action('add_report', 'extensions_manager_report');

function extensions_manager_report($reports_object){
	
	$extensions_dir = get_template_directory() . '/extensions/';
	
	$reports_object->assign_report(array(
		'source' => 'Extensions Manager',
		'report_key' => 'extension_dir_exists',
		'path' => $extensions_dir,
		'success_message' => 'Extensions directory ('.$extensions_dir.') exists.',
		'fail_message' => 'Extensions directory ('.$extensions_dir.') does not exist.',
	), 'DIR_EXISTS' );

	$reports_object->assign_report(array(
		'source' => 'Extensions Manager',
		'report_key' => 'extension_dir_writable',
		'path' => $extensions_dir,
		'success_message' => 'Extensions directory ('.$extensions_dir.') is writable.',
		'fail_message' => 'Extensions directory ('.$extensions_dir.') is not writable.',
	), 'IS_WRITABLE' );	
}

add_filter( 'framework_admin_title', 'title_button_new_extension' );
?>
