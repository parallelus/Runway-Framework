<?php
/*
	Extension Name: Report Manager
	Extension URI:
	Version: 0.1
	Description: This extension notify user about permissions on directories
	Author:
	Author URI:
	Text Domain:
	Domain Path:
	Network:
	Site Wide Only:
*/

// Load this section of the admin
$fields = array(
	'var'   => array(),
	'array' => array()
);

$default = array();

$settings = array(
	'name'             => __( 'Reports', 'runway' ),
	'option_key'       => $shortname . 'report-manager',
	'fields'           => $fields,
	'default'          => $default,
	'parent_menu'      => 'hidden',
	'menu_permissions' => 'administrator',
	'file'             => __FILE__,
	'css'              => array(
		FRAMEWORK_URL . 'framework/includes/report-manager/css/style.css',
	),
);

// Required components
include __DIR__ . '/object.php';

$reports_admin = new Reports_Object( $settings );

// Load admin components
if ( is_admin() ) {
	include __DIR__ . '/settings-object.php';
	$reports = new Reports_Admin_Object( $settings );
}

if ( ! function_exists( 'title_button_fix_all_issues' ) ) {
	// Setup a custom button in the title
	function title_button_fix_all_issues( $title ) {

		global $reports;
		if ( $_GET['page'] == 'reports' && $reports->have_fails() ) {
			$title .= ' <a href="' . admin_url( 'admin.php?page=reports&action=fix-all-issues' ) . '" class="add-new-h2">' .
			          __( 'Fix all issues', 'runway' ) .
			          '</a>';
		}

		return $title;

	}
	add_filter( 'framework_admin_title', 'title_button_fix_all_issues' );
}

do_action( 'reports_manager_is_load' );
