<?php
/*
	Extension Name: Admin Pages Manager
	Extension URI:
	Version: 0.1
	Description: Admin Pages Manager module
	Author:
	Author URI:
	Text Domain:
	Domain Path:
	Network:
	Site Wide Only:
*/

// Create pages dir
if ( ! file_exists( get_stylesheet_directory() . '/data/pages' ) ) {
	try_to_create_folder( get_stylesheet_directory() . '/data/pages' );
}

// Load this section of the admin
$fields = array(
	'var'   => array(),
	'array' => array()
);

$default = array();

$settings = array(
	'name'             => __( 'Options Builder', 'runway' ),
	'option_key'       => $shortname . 'options-builder',
	'fields'           => $fields,
	'default'          => $default,
	'parent_menu'      => 'hidden', // managed by framework
	'menu_permissions' => 'administrator',
	'file'             => __FILE__,
	'js'               => array(
		'wp-color-picker',
		'formsbuilder',
		'ace',
		'rw_nouislider',
		FRAMEWORK_URL . 'framework/js/farbtastic/farbtastic.js',
	),
	'css'              => array(
		'wp-color-picker',
		'formsbuilder-style',
		'rw_nouislider_css',
		FRAMEWORK_URL . 'framework/js/farbtastic/farbtastic.css',
	),
);

// Required components
include __DIR__ . '/object.php';

global $apm;
$ApmAdmin = new Apm_Settings_Object( $settings );

// Load admin components
if ( is_admin() ) {
	include __DIR__ . '/settings-object.php';
	$apm = new Apm_Admin( $settings );
}

do_action( 'options_builder_is_load' );

if ( ! function_exists( 'options_page_render_report' ) ) {
	// Setup a custom button in the title
	function title_button_add( $title ) {

		if ( $_GET['page'] == 'options-builder' ) {
			$title .= ' <a href="' . admin_url( 'admin.php?page=options-builder&navigation=new-page' ) . '" class="add-new-h2">' . __( 'New Admin Page',
					'runway' ) . '</a>';
		}

		return $title;

	}
	add_filter( 'framework_admin_title', 'title_button_add' );
}

if ( ! function_exists( 'options_page_render_report' ) ) {
	function options_page_render_report( $reports_object ) {

		$pages_dir = get_stylesheet_directory() . '/data/pages/';
		$reports_object->assign_report( array(
			'source'          => 'Options Builder',
			'report_key'      => 'pages_dir_exists',
			'path'            => $pages_dir,
			'success_message' => sprintf( __( 'Pages dir (%s) is exists.', 'runway' ), $pages_dir ),
			'fail_message'    => sprintf( __( 'Pages dir (%s) is not exists.', 'runway' ), $pages_dir ),
		), 'DIR_EXISTS' );

		$reports_object->assign_report( array(
			'source'          => 'Options Builder',
			'report_key'      => 'pages_dir_writable',
			'path'            => $pages_dir,
			'success_message' => sprintf( __( 'Pages dir (%s) is writable.', 'runway' ), $pages_dir ),
			'fail_message'    => sprintf( __( 'Pages dir (%s) is not writable.', 'runway' ), $pages_dir ),
		), 'IS_WRITABLE' );

	}
	add_action( 'add_report', 'options_page_render_report' );
}

if ( ! function_exists( 'try_to_create_folder' ) ) {
	function try_to_create_folder( $pathname ) {

		if ( is_writable( $pathname ) ) {
			mkdir( $pathname, 0755, true );

			return true;
		} else {
			return false;
		}
		
	}
}
