<?php
/*
    Extension Name: Auth Manager
    Extension URI:
    Version: 0.1
    Description: Auth Manager
    Author:
    Author URI:
    Text Domain:
    Domain Path:
    Network:
    Site Wide Only:
*/

// Reset
//if (0) update_option('dashboard', array());

// Settings
$fields = array(
    'var' => array(),
    'array' => array()
);

$default = array();

global $settingshortname;

$settings = array(
    'name' => 'Auth Manager',
    'option_key' => $settingshortname.'auth-manager',
    'fields' => $fields,
    'default' => $default,
    'parent_menu' => 'framework-options', // managed by framework
    'menu_permissions' => 'administrator',
    'file' => __FILE__,    
);

global $auth_manager, $auth_manager_admin;

// Required components
include 'object.php';

$auth_manager = new Auth_Manager_Object( $settings );

// Load admin components
if ( is_admin() ) {
    include 'settings-object.php';
    $auth_manager_admin = new Auth_Manager_Admin( $settings );
}
?>
