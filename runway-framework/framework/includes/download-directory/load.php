<?php
/*
    Extension Name: Download Directory
    Extension URI:
    Version: 0.1
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
    'name' => 'Directory',
    'alias' => 'directory',
    'option_key' => $settingshortname.'download-directory',
    'fields' => $fields,
    'default' => $default,
    'parent_menu' => 'downloads',
    'menu_permissions' => 'administrator',
    'file' => __FILE__,
    'js' => array(
        FRAMEWORK_URL.'framework/js/jquery-ui.min.js',
    ),
);

global $directory, $Directory_Admin;

// Required components
include 'object.php';

$directory = new Directory_Settings_Object( $settings );

// Load admin components
if ( is_admin() ) {
    include 'settings-object.php';
    $Directory_Admin = new Directory_Admin( $settings );
}

function test_requested() {
    echo 'Test';
    exit();
}

add_action( 'wp_ajax_test', 'test_requested' );

add_action( 'add_report', 'download_directory_report' );

function download_directory_report( $reports_object ) {
    $upload_dir = wp_upload_dir( );
    $downloads_tmp_dir = $upload_dir['basedir'].'/download_dir/';
    $reports_object->assign_report( array(
            'source' => 'Download Directory',
            'report_key' => 'download_tmp_dir_exists',
            'path' => $downloads_tmp_dir,
            'success_message' => 'Downlads directory ('.$downloads_tmp_dir.') exists.',
            'fail_message' => 'Downlads directory ('.$downloads_tmp_dir.') does not exist.',
        ), 'DIR_EXISTS' );

    $reports_object->assign_report( array(
            'source' => 'Download Directory',
            'report_key' => 'download_tmp_dir_writable',
            'path' => $downloads_tmp_dir,
            'success_message' => 'Downlads directory ('.$downloads_tmp_dir.') is writable.',
            'fail_message' => 'Downlads directory ('.$downloads_tmp_dir.') is not writable.',
        ), 'IS_WRITABLE' );
}
?>
