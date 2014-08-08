<?php

/**
 * Base Runway Configurations
 * =============================================================================
 *
 * Sets the default framework configurations such as folder locations, variables
 * and other configurations used by Runway.
 *
 */


//-----------------------------------------------------------------
// Default variables and information
//-----------------------------------------------------------------
$themeInfo   	= rw_get_theme_data();
$themeVersion  	= trim( $themeInfo['Version'] );
$themeTitle  	= trim( $themeInfo['Title'] );
$shortname   	= apply_filters( 'shortname', sanitize_title( $themeTitle . '_' ) );

//................................................................
// shortcuts variables
//................................................................
$themeUrl   	= apply_filters( 'themeUrl', trailingslashit( get_stylesheet_directory_uri() ) );
$themeDir   	= apply_filters( 'themeDir', trailingslashit( get_stylesheet_directory() ) );
$frameworkUrl  	= apply_filters( 'frameworkUrl', trailingslashit( get_template_directory_uri() ) );
$frameworkDir  	= apply_filters( 'frameworkDir', trailingslashit( get_template_directory() ) );
$frameworkText  = apply_filters( 'frameworkText', 'runway' );
$developerMode  = apply_filters( 'developerMode', ( get_template() == 'runway-framework' ) ); // enables developer mode options
$developerTools = apply_filters( 'developerTools', $developerMode );       // makes extra developer tools visible
$libsDir  	= apply_filters( 'frameworkDir', trailingslashit( get_template_directory().'/framework/libs/' ) );

//................................................................
// set as constants
//................................................................
define( 'THEME_NAME', $themeTitle );        // Theme title
define( 'THEME_VERSION', $themeVersion );   // Theme version number
define( 'THEME_URL', $themeUrl );           // URL of theme folder (includes child themes)
define( 'THEME_DIR', $themeDir );           // Server path to theme folder (includes child themes)
define( 'FRAMEWORK_URL', $frameworkUrl );   // URL of framework folder
define( 'FRAMEWORK_DIR', $frameworkDir );   // Server path to framework folder
define( 'FRAMEWORK_TEXT', $frameworkText ); // (deprecated) The text domain for translation functions
define( 'IS_CHILD', (!file_exists(get_stylesheet_directory().'/framework/'))? true : false);     // Sets a constant if this is a child (not standalone) theme
define( 'MIN_PHP_VERSION', '5.3.1' );		// Min PHP version
define( 'LIBS_DIR', $libsDir);

//-----------------------------------------------------------------
// Additional framework specific options
//-----------------------------------------------------------------
// $is_child = FALSE;
$expStyleSheetDir = explode( '/', get_stylesheet_directory() );
$theme_name = array_pop( $expStyleSheetDir );
$ext_manager_load = get_template_directory() . '/extensions/extensions-manager/load.php';

?>