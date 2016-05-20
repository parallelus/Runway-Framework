<?php
/*
    Extension Name: Theme Updater
    Extension URI:
    Version: 0.1
    Description:
    Author: Parallelus
    Author URI: http://para.llel.us
*/

// Settings
$fields = array(
		'var' => array(),
		'array' => array()
);
$default = array();

$settings = array(
	'name' => __('Theme Updater', 'runway'),
	'option_key' => $shortname.'theme_updater',
	'fields' => $fields,
	'default' => $default,
	'parent_menu' => 'hidden',
	'wp_containers' => 'none',
	'interval' => 60*60*24,  /// update interval (24 hours)
	'file' => __FILE__,
);

// Required components
include('object.php');

global $theme_updater, $theme_updater_admin;
$theme_updater = new Theme_Updater_Object($settings);

// Load admin components
if (is_admin()) {
	include('settings-object.php');
	$theme_updater_admin = new Theme_Updater_Admin_Object($settings);
}

?>