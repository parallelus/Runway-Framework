<?php
/*
	Extension Name: Runway Dashboard
	Extension URI:
	Version: 1.0
	Description: Runway Dashboard
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
	'name' => __('Dashboard', 'framework'),
	'option_key' => $settingshortname.'dashboard',
	'fields' => $fields,
	'default' => $default,
	'parent_menu' => 'hidden', // managed by framework
	'menu_permissions' => 'administrator',
	'file' => __FILE__,
	'js' => array(
		FRAMEWORK_URL.'framework/includes/dashboard/js/sort_credits.js',
	),
	'css' => array(
		FRAMEWORK_URL.'framework/includes/dashboard/css/style.css',
		FRAMEWORK_URL.'framework/includes/dashboard/css/style_credits.css',
	),
);

global $dashboard, $Dashboard_Admin;

// Required components
include 'object.php';

$dashboard = new Dashboard_Settings_Object( $settings );

// Load admin components
if ( is_admin() ) {
	include 'settings-object.php';
	$Dashboard_Admin = new Dashboard_Admin( $settings );
}
?>
