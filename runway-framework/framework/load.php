<?php
// debug
function out( $what ) {
	$bt = debug_backtrace();
	echo '<br><br>' . $bt[0]['file'] . '[' . $bt[0]['line'] . ']: <br><pre>' . print_r( $what, true ) . '</pre><br>';
}

define('MIN_PHP_VERSION_ID', 50301);

function runway_php_version( $version = false ) {
	if (!defined ('PHP_VERSION_ID'))
	{
		$ver = array_map ('intval', explode ('.', PHP_VERSION, 3));
		$ver[0] *= 10000;
		$ver[1] *= 100;
		define ('PHP_VERSION_ID', array_sum ($ver));
		
		unset ($ver);
	}
	
	$tmp_version = array_map ('intval', explode ('.', PHP_VERSION, 3));
	$tmp_version[0] *= 10000;
	$tmp_version[1] *= 100;
	$tmp_version_id = array_sum ($tmp_version);
	$php_version_id = PHP_VERSION_ID;
	
	if($php_version_id < $tmp_version_id) {
		$php_version_id = $tmp_version_id;
	}

	if($version == true)
		return $php_version_id;
	else {
		if($php_version_id >= MIN_PHP_VERSION_ID)
			return true;
		else
			return false;
	}
}

// Test the PHP version before we continue
if ( runway_php_version(true) >= MIN_PHP_VERSION_ID ) {

	// We're good! This at least v5.3.1

	//-----------------------------------------------------------------
	// Functions needing to run before config.php is loaded
	//-----------------------------------------------------------------
	include_once 'includes/load-functions.php';


	//-----------------------------------------------------------------
	// Framework configuration variables
	//-----------------------------------------------------------------
	include_once 'defaults.php';


	//-----------------------------------------------------------------
	// Load framework components
	//-----------------------------------------------------------------
	include_once 'core/common-object.php';
	
	//-----------------------------------------------------------------
	// Load translations for javascript
	//-----------------------------------------------------------------
	include_once 'translations-js.php';


	//-----------------------------------------------------------------
	// Initialize the admin components
	//-----------------------------------------------------------------
	if ( is_admin() ) {
		include_once 'core/admin-object.php';

		db_json_sync();
		check_theme_ID();
		prepare_translate_files();
	}

	load_data_types();
	load_framework_libraries();

	//-----------------------------------------------------------------
	// Extensions
	//-----------------------------------------------------------------

	// Get available extensions
	//................................................................
	$extensions = get_extensions();

	// Set filters on get option and update option for extensions
	//................................................................
	foreach ( $extensions as $extension_name => $extension_path ) {
		$key = $shortname.$extension_name;		
		add_filter( 'pre_option_'.$key, 'theme_option_filter', 10, 1 );
		add_action( 'update_option', 'theme_option_dual_save_filter', 10, 3 );
	}

	// Set filters on get option and update option for all forms, which build with FormsBuilder
	//................................................................
	$forms = new FormsBuilder();
	if( ! empty( $forms->options_pages ) )	
		foreach ($forms->options_pages as $key => $value) {
			$key = $shortname.$key;
			add_filter( 'pre_option_'.$key, 'theme_option_filter', 10, 1 );
			add_action( 'update_option', 'theme_option_dual_save_filter', 10, 3 );
		}

	// TODO: add filters to all pages created with FormsBuilder

	//................................................................
	// Load extensions PHP file
	//................................................................
	include_once 'includes/load-extensions.php';


	//-----------------------------------------------------------------
	// ** Temporary ** Theme Menu
	//-----------------------------------------------------------------

	// We're adding a menu for the currently active theme just to nest
	// the theme option pages inside. This will probably need to be
	// integrated into the framework better in the future.

	function add_framework_menu() {
		global $extm;

		if ( get_template() == 'runway-framework' ) {
			// Runway menu
			add_menu_page( 'Runway', 'Runway', 'administrator', 'framework-options', 'do_nothing', FRAMEWORK_URL.'framework/images/menu-runway.png' );
			// Downloads menu
			add_menu_page( 'Downloads', 'Downloads', 'administrator', 'downloads', 'do_nothing', FRAMEWORK_URL.'framework/images/menu-downloads.png' );
		}
		// Custom theme menu
		$currentThemeName = ( trim( THEME_NAME ) == trim( 'Runway' ) ) ? 'Child Theme' : THEME_NAME;
		add_menu_page( '', $currentThemeName, 'administrator', 'current-theme', 'do_nothing' );

	}
	add_action( 'admin_menu', 'add_framework_menu', 5 );

	if ( !function_exists( 'do_nothing' ) ) {
		function do_nothing() { }
	}

	function add_framework_submenu() {
		// Runway sub-menu
		add_submenu_page( 'framework-options', 'Runway Development Sandbox', 'Dashboard', 'administrator', 'dashboard' );
		add_submenu_page( 'framework-options', 'Runway Development Sandbox', 'Add-ons', 'administrator', 'directory' );
		add_submenu_page( 'framework-options', 'Runway Development Sandbox', 'Themes', 'administrator', 'themes' );
		add_submenu_page( 'framework-options', 'Runway Development Sandbox', 'Extensions', 'administrator', 'extensions' );
		add_submenu_page( 'framework-options', 'Runway Development Sandbox', 'Options Builder', 'administrator', 'options-builder' );
	}
	add_action( 'admin_menu', 'add_framework_submenu', 9 ); // higher priority, 9, forces default items to top of sub-menu

	function add_child_theme_submenu() {
		// Child theme sub-menu
		if( get_template() == 'runway-framework' )
			add_submenu_page( 'current-theme', 'Add Options Page', '+ Add Options Page', 'administrator', 'admin.php?page=options-builder&navigation=new-page' );
	}
	add_action( 'admin_menu', 'add_child_theme_submenu', 11 ); // lower priority, 11, forces item to end of sub-menu

	function clear_submenu() {
		global $submenu;
		unset( $submenu['framework-options'][0] );
		unset( $submenu['downloads'][0] );
		unset( $submenu['current-theme'][0] );
	}
	add_action( 'admin_menu', 'clear_submenu', 100 );

	function framework_localization() {
		$langDir = apply_filters('rf_languages_dir', get_template_directory() . '/languages');
		$isLoadedDir = load_theme_textdomain('framework', $langDir);
		
		/*if(!$isLoadedDir) {
			add_action('admin_notices', 'framework_localization_warning_message');
		}*/
	}
	add_action('after_setup_theme', 'framework_localization');

	function framework_localization_warning_message() {
		echo '<div id="message" class="error">'.__('Localization directory not exists or empty. Textdomain hasn\'t loaded.', 'framework').'</div>';
	}
	
	//-----------------------------------------------------------------
	// WP-Pointers (temporary location)
	//-----------------------------------------------------------------

	// Dashboard "Getting Started"
	if ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] !== 'dashboard' && !isset( $_GET['activate-default'] ) ) {
		WP_Pointers::add_pointer( 'all', 'a.wp-first-item[href=\'admin.php?page=dashboard\']', array( 'title' => __('Start Here', 'framework'), 'body' => '<p>'.__('Visit the dashboard and learn how Runway works to start making awesome themes today.', 'framework').'</p>' ), 'edge: "left", align: "center"' );
	}

} else {

	// This is no good. The PHP version needs to be higher. :(
	// ----------------------------------------------------------------

	add_action( 'admin_notices', 'php_version_warning_message' );
	function php_version_warning_message() {
		global $current_screen;
		echo '<div id="message" class="error">',
		'<h3><strong>'.__('You must have PHP v5.3.1 or later to use this theme.', 'framework').'</strong></h3>',
		'<p>'.__('You can try adding the following to the top of to your .htaccess file in the WordPress root directory', 'framework').':</p>',
		'<p><code style="font-size: 14px; font-weight: 800;">AddType application/x-httpd-php53 .php</code></p>',
		'<p>.'.__('If that does not work, contact your host and ask them to update your PHP version. The theme will not be functional until this issue is corrected.', 'framework').'</p>',
		'</div>';
	}

}

?>
