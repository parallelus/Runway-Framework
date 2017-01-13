<?php
/*
	Extension Name: Themes manager
	Extension URI:
	Version: 0.1
	Description: Themes manager module
	Author:
	Author URI:
	Text Domain:
	Domain Path:
	Network:
	Site Wide Only:
*/

// Settings
$fields = array(
	'var'   => array(),
	'array' => array()
);

$default = array();

global $settingshortname;

$settings = array(
	'name'             => __( 'Runway Themes', 'runway' ),
	'alias'            => 'themes',
	'option_key'       => $settingshortname . 'developer-tools',
	'fields'           => $fields,
	'default'          => $default,
	'parent_menu'      => 'hidden', // managed by framework
	'menu_permissions' => 'administrator',
	'file'             => __FILE__,
	'js'               => array(
		//'theme',
		FRAMEWORK_URL . 'framework/js/jquery-ui.min.js',
		FRAMEWORK_URL . 'framework/js/jquery.tmpl.min.js',
	),
	'css'              => array(
		FRAMEWORK_URL . 'framework/includes/themes-manager/css/style.css',
	),
);

global $developer_tools, $Themes_Manager;

if ( ! defined( 'DS' ) ) {
	define( 'DS', DIRECTORY_SEPARATOR );
}

// Required components
include_once __DIR__ . '/object.php';

$Themes_Manager = new Themes_Manager_Settings_Object( $settings );

// Load admin components
if ( is_admin() ) {
	include_once __DIR__ . '/settings-object.php';
	$developer_tools = new Themes_Manager_Admin( $settings );
}

do_action( 'themes_manager_is_load' );

if ( ! function_exists( 'title_button_themes' ) ) {
	// Setup a custom button in the title
	function title_button_themes( $title ) {

		if ( get_bloginfo( 'version' ) >= 4 ) {
			$install_url = 'theme-install.php?upload';
		} else {
			$install_url = 'theme-install.php?tab=upload';
		}

		if ( $_GET['page'] == 'themes' ) {
			$title .= ' <a href="' . admin_url( 'admin.php?page=themes&navigation=new-theme' ) . '" class="add-new-h2">' .
			          __( 'New Theme', 'runway' ) . '</a> <a href="' . admin_url( $install_url ) . '" class="add-new-h2">' .
			          __( 'Install', 'runway' ) . '</a>';
		}

		return $title;
	}
	add_filter( 'framework_admin_title', 'title_button_themes' );
}

if ( ! function_exists( 'runway_admin_themes_list_prepare' ) ) {
	function runway_admin_themes_list_prepare( $theme ) {

		// Set the variables
		// --------------------------------------------

		// Name
		$t['name'] = ( isset( $theme['Name'] ) ) ? $theme['Name'] : '[' . __( 'No theme name', 'runway' ) . ']';
		// Description
		$t['description'] = ( isset( $theme['Description'] ) ) ? '<p class="theme-description">' . $theme['Description'] . '<p>' : false;
		// Author
		if ( isset( $theme['Author'] ) ) {
			$t['author'] = $theme['Author'];
			if ( isset( $theme['AuthorURI'] ) ) {
				$t['author'] = '<a href="' . $theme['AuthorURI'] . '">' . $t['author'] . '</a>';
			}
			$t['author'] = '<li>' . sprintf( __( 'By %s', 'runway' ), $t['author'] ) . '</li>';
		} else {
			$t['author'] = '';
		}

		// Version
		$t['version'] = ( isset( $theme['Version'] ) ) ? '<li><strong>' . __( 'Version', 'runway' ) . '</strong> ' . $theme['Version'] : false;
		// Folder
		$t['folder'] = ( isset( $theme['Folder'] ) ) ? $theme['Folder'] : false;
		// Image
		$t['image'] = ( isset( $t['folder'] ) ) ? home_url() . '/wp-content/themes/' . $t['folder'] . '/screenshot.png' : false;

		// URLs
		// --------------------------------------------

		// Activate URL
		$t['activateURL'] = wp_nonce_url( 'themes.php?action=activate&amp;template=' . urlencode( $t['folder'] ) .
		                                  '&amp;stylesheet=' . urlencode( $t['folder'] ), 'switch-theme_' . $t['folder'] );
		// Preview URL
		$t['previewURL'] = home_url();
		if ( is_ssl() ) {
			$t['previewURL'] = str_replace( 'http://', 'https://', $t['previewURL'] );
		}
		$t['previewURL'] = esc_url( htmlspecialchars(
			add_query_arg(
				array(
					'preview'        => 1,
					'template'       => strtolower( urlencode( $theme['Template'] ) ),
					'stylesheet'     => strtolower( urlencode( $t['folder'] ) ),
					'preview_iframe' => false,
					'TB_iframe'      => 'false'
				),
				$t['previewURL']
			)
		) );
		// Edit URL
		$t['editURL'] = esc_url( 'admin.php?page=themes&navigation=edit-theme&name=' . $t['folder'] );
		// Delete URL
		$t['deleteURL'] = esc_url( 'admin.php?page=themes&navigation=delete-theme&name=' . $t['folder'] );
		// Download URL
		$t['downloadURL'] = esc_url( 'admin.php?page=themes&navigation=do-package&name=' . $t['folder'] . '&_wpnonce=' .  wp_create_nonce( 'packages' ) );
		// History URL
		$t['historyURL'] = esc_url( 'admin.php?page=themes&navigation=do-download&name=' . $t['folder'] );

		// Links
		// --------------------------------------------

		// Activate Link
		$t['activateLink'] = '<a class="activate-theme" href="' . $t['activateURL'] . '">' . __( 'Activate',
				'runway' ) . '</a>';
		// Preview Link
		$t['previewLink'] = '<a target="_blank" href="' . $t['previewURL'] . '">' . __( 'Preview', 'runway' ) . '</a>';
		// Edit Link
		$t['editLink'] = '<a href="' . $t['editURL'] . '">' . __( 'Edit', 'runway' ) . '</a>';
		// Duplicate link
		$t['duplicateLink'] = '<a class="duplicate-theme" data-theme-folder="' . esc_attr( $t['folder'] ) . '" data-theme-name="' .
		                      esc_attr( $t['name'] ) . '" href="javascript: void(0);">' . wp_kses_post( __( 'Duplicate', 'runway' ) ) . '</a>';
		// Delete link
		$t['deleteLink'] = '<a href="' . $t['deleteURL'] . '" class="submitdelete deletion">' . __( 'Delete', 'runway' ) . '</a>';
		// Download / History
		$t['downloadLink'] = '<a class="get-package" href="' . $t['downloadURL'] . '">' . __( 'Packages &amp; Downloads', 'runway' ) . '</a>';
		if ( isset( $theme['History'] ) && $theme['History'] ) {
			$t['downloadLink'] .= ' | <a class="get-download" href="' . $t['historyURL'] . '">' . __( 'History', 'runway' ) . '</a>';
		}
		$t['downloadLink'] = '<span>' . $t['downloadLink'] . '</span>';


		// Combined Sections of Theme Blocks
		// --------------------------------------------

		// Theme Image / Screenshot
		$screenshotLink  = ( strtolower( $t['name'] ) != 'runway' ) ? $t['editURL'] : '#';
		$t['screenshot'] = '<a href="' . $screenshotLink . '" class="screenshot"><img src="' . $t['image'] . '" alt=""></a>';
		// Theme Info Block
		$t['themeInfo'] = '<div><ul class="theme-info">' . $t['author'] . $t['version'] . '</ul>' . $t['description'] .
		                  '</div><p class="theme-options">' . __( 'Folder location:', 'runway' ) . '<code>/themes/' . $t['folder'] . '</code></p>';

		return $t;

	}
}
