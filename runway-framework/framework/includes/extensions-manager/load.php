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
	'name' => __('Extensions', 'framework'),
	'option_key' => $shortname.'extensions-manager',
	'fields' => $fields,
	'default' => $default,
	'parent_menu' => 'hidden', // managed by framework
	'menu_permissions' => 'administrator',
	'file' => __FILE__,
);

// Required components
include_once 'object.php';

$Extm_Settings = new ExtmSettingsObject( $settings );

// Load admin components
if ( is_admin() ) {
	include_once 'settings-object.php';
	$extm = new Extm_Admin( $settings );
}else {
	$extm = new ExtmSettingsObject( $settings );
}
do_action( 'extension_manager_is_load' );

// Setup a custom button in the title
function title_button_new_extension( $title ) {
	if ( $_GET['page'] == 'extensions' ) {
		$title .= ' <a href="admin.php?page=extensions&navigation=add-extension" class="add-new-h2">'. __( 'Upload New', 'framework' ) .'</a> <a href="admin.php?page=directory" class="add-new-h2">'. __( 'Search Directory', 'framework' ) .'</a>';
	}
	return $title;
}

add_action( 'add_report', 'extensions_manager_report' );

function extensions_manager_report( $reports_object ) {

	$extensions_dir = get_template_directory() . '/extensions/';

	$reports_object->assign_report( array(
			'source' => 'Extensions Manager',
			'report_key' => 'extension_dir_exists',
			'path' => $extensions_dir,
			'success_message' => __( 'Extensions directory', 'framework' ) .' ('.$extensions_dir.') '.__( 'exists', 'framework' ).'.',
			'fail_message' => __( 'Extensions directory', 'framework' ) .' ('.$extensions_dir.') '.__( 'does not exist', 'framework' ).'.',			
		), 'DIR_EXISTS' );

	$reports_object->assign_report( array(
			'source' => 'Extensions Manager',
			'report_key' => 'extension_dir_writable',
			'path' => $extensions_dir,
			'success_message' => __( 'Extensions directory', 'framework' ) .' ('.$extensions_dir.') '.__( 'is writable', 'framework' ).'.',
			'fail_message' => __( 'Extensions directory', 'framework' ) .' ('.$extensions_dir.') '.__( 'is not writable', 'framework' ).'.',				
		), 'IS_WRITABLE' );
}

add_filter( 'framework_admin_title', 'title_button_new_extension' );
?>
