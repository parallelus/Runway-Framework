<?php

class Themes_Manager_Admin extends Runway_Admin_Object {

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $Themes_Manager_Admin;
			$Themes_Manager_Admin->navigation = $_REQUEST['navigation'];
		}
	}

	function after_settings_init() {

	}

	function validate_sumbission() {

		// If all is OKq
		return true;

	}

	function load_objects() {
		global $developer_tools;
		$this->data = $developer_tools->load_objects();
		return $this->data;
	}

}

function runway_admin_themes_list_prepare( $theme ) {

	// Set the variables
	// --------------------------------------------

	// Name
	$t['name'] = ( isset( $theme['Name'] ) ) ? $theme['Name'] : '['. __( 'No theme name', 'framework' ) .']';
	// Description
	$t['description'] = ( isset( $theme['Description'] ) ) ? '<p class="theme-description">'. $theme['Description'] .'<p>' : false;
	// Author
	if ( isset( $theme['Author'] ) ) {
		$t['author'] = $theme['Author'];
		if ( isset( $theme['AuthorURI'] ) ) {
			$t['author'] = '<a href="'. $theme['AuthorURI'] .'">'. $t['author'] .'</a>';
		}
		$t['author'] = '<li>'. __( 'By', 'framework' ) .' '. $t['author'] .'</li>';
	} else {
		$t['author'] = '';
	}

	// Version
	$t['version'] = ( isset( $theme['Version'] ) ) ? '<li><strong>'. __( 'Version', 'framework' ) .'</strong> '. $theme['Version'] : false;
	// Folder
	$t['folder'] = ( isset( $theme['Folder'] ) ) ? $theme['Folder'] : false;
	// Image
	$t['image'] = ( isset( $t['folder'] ) ) ? get_bloginfo( 'url' ) . '/wp-content/themes/' . $t['folder'] . '/screenshot.png' : false;

	// URLs
	// --------------------------------------------

	// Activate URL
	$t['activateURL'] = wp_nonce_url( 'themes.php?action=activate&amp;template='.urlencode( $t['folder'] ).'&amp;stylesheet='.urlencode( $t['folder'] ), 'switch-theme_' . $t['folder'] );
	// Preview URL
	$t['previewURL'] = esc_url( get_option( 'home' ) . '/' );
	if ( is_ssl() ) $t['previewURL'] = str_replace( 'http://', 'https://', $t['previewURL'] );
	$t['previewURL'] = htmlspecialchars( add_query_arg( array( 'preview' => 1, 'template' => strtolower( urlencode( $theme['Template'] ) ), 'stylesheet' => strtolower( urlencode( $t['folder'] ) ), 'preview_iframe' => false, 'TB_iframe' => 'false' ), $t['previewURL'] ) );
	// Edit URL
	$t['editURL'] = 'admin.php?page=themes&navigation=edit-theme&name='. $t['folder'];
	// Delete URL
	$t['deleteURL'] = 'admin.php?page=themes&navigation=delete-theme&name='. $t['folder'];
	// Download URL
	$t['downloadURL'] = 'admin.php?page=themes&navigation=do-package&name='. $t['folder'];
	// History URL
	$t['historyURL'] = 'admin.php?page=themes&navigation=do-download&name='. $t['folder'];

	// Links
	// --------------------------------------------

	// Activate Link
	$t['activateLink'] = '<a class="activate-theme" href="'. $t['activateURL'] .'">'. __( 'Activate', 'framework' ) .'</a>';
	// Preview Link
	$t['previewLink'] = '<a target="_blank" href="'. $t['previewURL'] .'">'. __( 'Preview', 'framework' ) .'</a>';
	// Edit Link
	$t['editLink'] = '<a href="'. $t['editURL'] .'">'. __( 'Edit', 'framework' ) .'</a>';
	// Duplicate link
	$t['duplicateLink'] = '<a class="duplicate-theme" data-theme-folder="'. $t['folder'] .'" data-theme-name="'. $t['name'] .'" href="javascript: void(0);">'. __( 'Duplicate', 'framework' ) .'</a>';
	// Delete link
	$t['deleteLink'] = '<a href="'. $t['deleteURL'] .'" class="submitdelete deletion">'. __( 'Delete', 'framework' ) .'</a>';
	// Download / History
	$t['downloadLink'] = '<a class="get-package" href="'. $t['downloadURL'] .'">'. __( 'Packages &amp; Downloads', 'framework' ) .'</a>';
	if ( isset( $theme['History'] ) && $theme['History'] ) {
		$t['downloadLink'] .= ' | <a class="get-download" href="'. $t['historyURL'] .'">'. __( 'History', 'framework' ) .'</a>';
	}
	$t['downloadLink'] = '<span>'. $t['downloadLink'] .'</span>';


	// Combined Secions of Theme Blocks
	// --------------------------------------------

	// Theme Image / Screenshot
	$screenshotLink = ( strtolower( $t['name'] ) != 'runway' ) ? $t['editURL'] : '#' ;
	$t['screenshot'] = '<a href="'. $screenshotLink .'" class="screenshot"><img src="'. $t['image'] .'" alt=""></a>';
	// Theme Info Block
	$t['themeInfo'] = '<div><ul class="theme-info">'. $t['author'] . $t['version'] .'</ul>'. $t['description'] .'</div><p class="theme-options">'. __( 'Folder location:', 'framework' ) .'<code>/themes/'. $t['folder'] .'</code></p>';

	return $t;

}
?>
