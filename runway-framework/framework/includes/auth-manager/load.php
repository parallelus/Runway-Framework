<?php
/*
    Extension Name: Accounts
    Extension URI:
    Version: 0.1
    Description: Accounts
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

$settings = array(
    'name' => __('Accounts', 'framework'),
    'option_key' => 'runway_authorization_token',
    'fields' => $fields,
    'default' => $default,
    'parent_menu' => 'framework-options', // managed by framework
    'menu_permissions' => 'administrator',
    'file' => __FILE__,    
);

####################### TEMPORARY SECURITY PATCH #######################
#
# This code will remove any existing data from a user's install related 
# to the authorization manager and hide the feature temporarily.
#
########################################################################

$options = get_option($settings['option_key']);
if ($options) { 
	delete_option($settings['option_key']);
	unset($options);
}

########################################################################


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
