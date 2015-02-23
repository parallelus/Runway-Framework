<?php

spl_autoload_register( function( $class ) {
		// out(LIBS_DIR);
		if ( file_exists( LIBS_DIR.$class.'.php' ) ) {
			require_once LIBS_DIR.$class.'.php';
		}
	} );

//-----------------------------------------------------------------
// Load data-types
//-----------------------------------------------------------------

if ( !function_exists( 'load_data_types' ) ) :
    function load_data_types() {
		global $data_types_list;

		// $data_types_path = get_theme_root() . "/runway-framework/data-types";
		$data_types_path = FRAMEWORK_DIR .'data-types';
		$data_types_base = $data_types_path . "/data-type.php";
        
		if (!file_exists($data_types_path) || !file_exists($data_types_base)) {
			wp_die("Error: has no data types.");
		} else {
			include_once $data_types_base;

			$data_types_array = include_data_types($data_types_path);

			foreach ($data_types_array as $name => $path) {
				$data_type_slug = basename($path, '.php');
                
                if ($name == 'fileupload-type.php') {
                  if ( ! did_action( 'wp_enqueue_media' ) )
                    wp_enqueue_media();   //Needed for upload field
                }
                
				$data_types_list[$data_type_slug] = array(
				    'filename' => $name,
				    'classname' => ucfirst(str_replace('-', '_', $data_type_slug)),
				);

				// Unsupported in old PHP versions
				$data_types_list[$data_type_slug]['classname']::assign_actions_and_filters();
			}
		}
	}
endif;

if(!function_exists('include_data_types')) {
	function include_data_types($data_types_path) {
		$data_types_array = array();
		foreach ( array_diff( scandir( $data_types_path ), array( '..', '.', 'data-type.php' ) ) as $name ) {
			if(is_dir("$data_types_path/$name")) {
				foreach(array_diff( scandir( "$data_types_path/$name" ), array( '..', '.' ) ) as $filename) {
					if(is_dir("$data_types_path/$name/$filename"))
						continue;
					
					include_once "$data_types_path/$name/$filename";
					$data_types_array[$filename] = "$data_types_path/$name/$filename";
				}
			}
			else {
				include_once "$data_types_path/$name";
				$data_types_array[$name] = "$data_types_path/$name";
			}
		}
		
		return $data_types_array;
	}
}

//-----------------------------------------------------------------
// Get options from DB
//-----------------------------------------------------------------

// recursive find option by path
if ( !function_exists( 'r_option' ) ) {
	function r_option( $path, $array ) {

		$step = trim( current( $path ) );

		if ( count( $path ) > 1 ) {
			array_splice( $path, 0, 1 );
			return r_option( $path, $array[$step] );
		} else {
			return $array[$step];
		}

	}
}

if ( !function_exists( 'rf__' ) ) {
	function rf__( $var, $domain = 'framework' ){
		return call_user_func( '__', $var, $domain );    
	}
}

if ( !function_exists( 'rf_e' ) ) {
	function rf_e( $var, $domain = 'framework' ){
		call_user_func( '_e', $var, $domain );    
	}
}

// register taxonommies to custom post types
if ( !function_exists( 'register_custom_taxonomies' ) ) {	
	function register_custom_taxonomies() {
		global $shortname;

		$content_types_options = get_option($shortname. 'content_types');
		if(isset($content_types_options['taxonomies']))
		foreach ((array)$content_types_options['taxonomies'] as $taxonomy => $values) {
			$content_types_to_adding = array();
			if(isset($content_types_options['content_types']))	
			foreach ((array)$content_types_options['content_types'] as $content_type => $vals) {
				if(isset($vals['taxonomies']) && in_array($taxonomy, $vals['taxonomies'])){
					$content_types_to_adding[] = $content_type;
				}
			}
			register_taxonomy($taxonomy, $content_types_to_adding, array(
				// Hierarchical taxonomy (like categories)
				'hierarchical' => true,
				// This array of options controls the labels displayed in the WordPress Admin UI
				'labels' => $values['labels'],
				// Control the slugs used for this taxonomy
				'rewrite' => array(
					'slug' => $taxonomy, // This controls the base slug that will display before each term
					'with_front' => false, // Don't display the category base before "/locations/"
					'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
				),
			));
		}
	}
	add_action('init', 'register_custom_taxonomies');
}	


// get framework data
if ( !function_exists( 'get_options_data' ) ) {
	// $key (required) to identify options-set in database
	// $option (optional) is path to option in options-set (coma separated values)
	// $default (optional) if nothing was matched then return this value
	function get_options_data( $key, $option = false, $default = null ) {

		if ( $option && isset( $_REQUEST['customized'] ) ) {
			$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
			if ( isset( $submited_value->{$option} ) ) {
				$value = $submited_value->{$option};
				return $value;
			}
		}

		global $shortname;

		if ( empty( $key ) ) {
			return $default;
		}

		// get coma separated path
		if ( $option != false ) {
			$option_path = explode( ',', $option );
			if ( count( $option_path ) == 1 ) {
				$option = current( $option_path );
			} else {
				$option = $option_path;
			}
		}

		// create a database value key based on
		// current active theme key and requested key
		$original_key = $key;
		$key = $shortname . $key;

		// get value from database
		// $value = get_option( $key );

		global $wpdb;
		$result = $wpdb->get_results( "SELECT * FROM wp_options WHERE option_name = '" . $key . "'" );
		$value = isset($result[0]->option_value)? unserialize($result[0]->option_value) : '';

		$key_tmp = explode('_', $original_key);
		if($key_tmp[0] == 'formsbuilder' && !is_null(get_post(end($key_tmp), ARRAY_A))) {
			$meta_value = get_post_meta( end($key_tmp), $option, true );
			if( !empty($meta_value) )
				return $meta_value;			
		}

		// apply data-type filter
		if ( isset( $value['field_types'][$option] ) ) {
			$field_type = $value['field_types'][$option];
			$value[$option] = apply_filters( 'get_options_data_type_' . $field_type, $value[$option] );
		}

		// apply option option_key filter
		$value[$option] = ( isset( $value[$option] ) ) ? $value[$option] : '';
		$value[$option] = apply_filters( 'options_data_' . $option, $value[$option] );
		// apply option page_key+option_key filter
		$opt = ( isset( $value[$option] ) ) ? $value[$option] : '';
		$value[$option] = apply_filters( 'options_data_' . $original_key . '_' . $option, $value[$option] );

		if ( !empty( $value ) ) {
			// return value by defined option path
			if ( $option != false ) {
				if ( is_array( $option ) ) {
					$value = r_option( $option, $value );
					if ( empty( $value ) ) {
						return $default;
					} else {
						return is_array( $value ) && count( $value ) == 1 ? $value[0] : $value;
					}
				} else {
					if ( isset( $value[$option] ) ) {
						return is_array( $value[$option] ) && count( $value[$option] ) == 1 ? $value[$option][0] : $value[$option];
					} else {
						return $default;
					}
				}
			} else {
				return is_array( $value ) && count( $value ) == 1 ? $value[0] : $value;
			}
		} else {
			return $default;
		}

	}
}

// get and display framework data
if ( !function_exists( 'options_data' ) ) {
	function options_data( $key, $option = false, $default = null ) {

		echo get_options_data( $key, $option, $default );

	}
}

if( !function_exists( 'get_font_options_data' )) {
	function get_font_options_data( $key, $option = false, $default = null ) {

		$font_options = get_options_data( $key, $option, $default );

		$options_str = str_replace(' ', '+', trim($font_options['family']));
		// if($font_options['weight'] != '' && $font_options['weight'] == 'bold') {
		// 	$options_str .= ':'.$font_options['weight'];
		// }
		// if($font_options['style'] == 'italic' || $font_options['weight'] != '') {
		// 	$options_str .= ':';
		// 	if($font_options['weight'] == 'bold' && $font_options['style'] == 'italic') {
		// 		$options_str .= "bolditalic";
		// 	}
		// 	else if($font_options['style'] == 'italic' && $font_options['weight'] != '' ){
		// 		$options_str .= 'italic'.$font_options['weight'];
		// 	}
		// 	else if($font_options['style'] == 'italic') {
		// 		$options_str .= 'italic';
		// 	}
		// 	else if($font_options['weight'] != '') {
		// 		$options_str .= $font_options['weight'];
		// 	}
		// }
		$query_args = array( 'family' => $options_str );
		wp_enqueue_style( 'google-font-'.$options_str , add_query_arg( $query_args, "//fonts.googleapis.com/css" ), array(), null );

		return $font_options;
	}
}


//-----------------------------------------------------------------
// Load libraries
//-----------------------------------------------------------------

if ( !function_exists( 'load_framework_libraries' ) ) :
	function load_framework_libraries() {
		$libs_path = get_template_directory().'/framework/libs/';
		if ( file_exists( $libs_path ) ) {
			$libs = scandir( $libs_path );
			global $libraries;
			$libraries = array();
			foreach ( $libs as $key => $lib ) {
				if ( $lib != '.' && $lib != '..' && is_file( $libs_path.$lib ) ) {
					include_once $libs_path.$lib;
					$name = str_replace( '.php', '', str_replace( '-', '_', $lib ) );
					if ( class_exists( $name ) && $name != 'Html' ) {
						$libraries[$name] = new $name();
					}
				}
			}
		}

	}
endif;

//-----------------------------------------------------------------
// Framework functions ???
//-----------------------------------------------------------------

function get_page_values( $page_id ) {
	$result = maybe_unserialize( get_option( 'runway_option_key_'.$page_id, true ) );

	return $result;
}


function get_extensions() {

	// value caching
	global $extensions;

	if ( isset( $extensions ) ) {
		return $extensions;
	}

	// set extensions folders
	$additional_extensions_dir = get_template_directory() . '/extensions/';
	$builtin_extensions_dir = FRAMEWORK_DIR . 'framework/includes/';
	$dynamic_pages_dir = get_template_directory() . '/data/pages';

	$extensions = array();

	// get additional extensions name and path
	$keys = array();
	if ( file_exists( $additional_extensions_dir ) ) {
		$keys = array_diff( (array)scandir( $additional_extensions_dir ), array( '.', '..' ) );
	}

	foreach ( $keys as $key ) {
		$extensions[$key] = $additional_extensions_dir.$key;
	}

	// get built-in extensions name and path
	$keys = array_diff( scandir( $builtin_extensions_dir ), array( '.', '..' ) );

	foreach ( $keys as $key ) {
		$extensions[$key] = $builtin_extensions_dir.$key;
	}

	// get pages names and path
	if ( file_exists( $dynamic_pages_dir ) ) {
		$keys = array_diff( (array)scandir( $dynamic_pages_dir ), array( '.', '..' ) );

		foreach ( $keys as $key ) {
			$key = str_replace( '.json', '', $key );
			$extensions['option_key_'.$key] = $dynamic_pages_dir.'/'.$key;
		}
	}

	return $extensions;
}

function theme_option_filter($pre) {
	if(!function_exists('WP_Filesystem'))
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	WP_Filesystem();
	global $wp_filesystem;
	global $wp_current_filter, $shortname;

	// if current options is from runway extension
	if ( strstr( $wp_current_filter[0], 'pre_option_'.$shortname ) ) {
		
		$option_key = str_replace( 'pre_option_', '', $wp_current_filter[0] );

		// get option from database (the same way as wordpress default)
		global $wpdb;

		$suppress = $wpdb->suppress_errors();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option_key ) );
		$wpdb->suppress_errors( $suppress );

		if ( is_object( $row ) ) {
			// if option was founded then return it
			return maybe_unserialize( $row->option_value );
		}
		else {
			// else search this option in /data folder (situation when user move extension or theme manually)
			$extension_json_settings = THEME_DIR.'/data/'.$option_key.'.json';
			if ( file_exists( $extension_json_settings ) ) {
				// if have option save it into database
				$value = json_decode( $wp_filesystem->get_contents( $extension_json_settings ), true );

				$result = $wpdb->insert( 
					$wpdb->options, 
					array( 
						'option_value' => maybe_serialize($value),
						'option_name' => $option_key
					)
				);
				return $value;
			} else {
				// else search default options in extension folder (situation when this extension
				// was never being installed or need to reset settings to default)

				$extensions = get_extensions();
				$extension_name = str_replace( $shortname, '', $option_key );
				if(isset( $extensions[str_replace( '_', '-', $extension_name )] )) {
					$extension_path = $extensions[str_replace( '_', '-', $extension_name )];
					$default_settings_file = $extension_path . '/default-settings.json';

					if ( file_exists( $default_settings_file ) ) {
						// copy and rename default settings JSON into /data folder
						copy( $default_settings_file, $extension_json_settings );
						$value = json_decode( $wp_filesystem->get_contents( $extension_json_settings ), true );
						// save default settings into database
						update_option( $option_key, $value );
					}
				}
			}
		}
	} else
		return false;

}

function theme_option_dual_save_filter( $option, $oldvalue, $newvalue ) {
	global $wp_current_filter, $shortname;

	$exclude = array(
		$shortname.'report-manager',
		$shortname.'formsbuilder_'
	);

	// check if current option is runway extension option
	$is_runway_option = false;
	$option_key = $option;

	// if current options is from runway extension
	if ( $option_key != '' && strstr( $option, $shortname ) ) {
		global $wpdb;

		if ( false === $oldvalue ) {
			add_option( $option, $newvalue );
		}

		// wp_die(out($newvalue));
		$result = $wpdb->update( $wpdb->options, array( 'option_value' => maybe_serialize( $newvalue ) ), array( 'option_name' => $option ) );

		$extension_name = str_replace( $shortname, '', $option_key );

		// convert option new value from php serialized to JSON format
		$newvalue = maybe_unserialize( $newvalue );
		$newvalue_json = json_encode( $newvalue );

		// save updated option to file in /data folder
		if ( is_writable( THEME_DIR.'data/' ) && !in_array($option, $exclude) ) {
			$settings = get_settings_json();
			$option = isset($settings['ThemeID'])? str_replace($shortname, $settings['ThemeID'] . '_', $option) : $option;
			if( IS_CHILD && get_template() == 'runway-framework') {
				WP_Filesystem();
				global $wp_filesystem;
				$wp_filesystem->put_contents(THEME_DIR.'data/'.$option.'.json', $newvalue_json, FS_CHMOD_FILE);
				//file_put_contents( THEME_DIR.'data/'.$option.'.json', $newvalue_json );
			}
		}
	}

	return $newvalue;
}


function rw_get_custom_theme_data( $name, $theme_dir = null ) {

    WP_Filesystem();
    global $wp_filesystem;

	if ( $theme_dir == null ) {
		$theme_dir = get_stylesheet_directory();
	}

	$info = $wp_filesystem->get_contents( $theme_dir.'/style.css' );

	$start = strpos( $info, $name );
	$data = '';
	if($start > 0) {
		$end = strpos( $info, PHP_EOL, $start );
		$data = trim(str_replace($name . ':', '', substr($info, $start, $end - $start)));
	}

	return $data;
}

function rw_get_theme_data( $theme_dir = null, $stylesheet = null ) {
	if ( function_exists( 'wp_get_theme' ) ) {
		$tmp = wp_get_theme();
		if ( $theme_dir == null ) {
			$theme_dir = get_stylesheet_directory();
		}
	}

	unset( $tmp );

	if ( file_exists( $theme_dir.'/style.css' ) && is_dir( $theme_dir ) ) {
		$stylesheet_files = array();
		$template_files = array();

		$theme_files = scandir( $theme_dir );
		foreach ( $theme_files as $file ) {
			if ( is_file( $theme_dir.'/'.$file ) ) {
				if ( preg_match( '/(.+).css/', $file ) ) {
					$stylesheet_files[] = $theme_dir.'/'.$file;
				}
				else {
					$template_files[] = $theme_dir.'/'.$file;
				}
			}
		}
		if ( $stylesheet == null ) {
			$explodeTheme_dir = explode( '/', $theme_dir );
			$stylesheet = array_pop( $explodeTheme_dir );
		}

		$theme = wp_get_theme( $stylesheet );

		$theme_type = '';
		if ( file_exists( $theme_dir.'/framework/setup.php' ) &&
			file_exists( $theme_dir.'/framework/core/admin-object.php' ) &&
			file_exists( $theme_dir.'/framework/core/common-object.php' ) ) {
			$theme_type = 'runway-framework';
		}

		return array(
			'Name' => $theme->get( 'Name' ),
			'URI' => $theme->get( 'ThemeURI' ),
			'Description' => $theme->get( 'Description' ),
			'Author' => $theme->get( 'Author' ),
			'AuthorURI' => $theme->get( 'AuthorURI' ),
			'Version' => $theme->get( 'Version' ),
			'Template' => $theme->get( 'Template' ),
			'Status' => $theme->get( 'Status' ),
			'Tags' => $theme->get( 'Tags' ),
			'TextDomain' => $theme->get( 'TextDomain' ),
			'DomainPath' => $theme->get( 'DomainPath' ),
			'Title' => $theme->get( 'Name' ),
			'AuthorName' => $theme->get( 'Author' ),
			'StylesheetFiles' => $stylesheet_files,
			'TemplateFiles' => $template_files,
			'Folder' => $stylesheet,
		);
	}

}

function custom_theme_menu_icon() {
	global $menu, $submenu, $Themes_Manager; $theme = rw_get_theme_data();

	$themeKey = null;
	foreach ( $menu as $key => $values ) {
		if ( $menu[$key][0] == $theme['Title'] ) {
			$themeKey = $key;
		}
	}

	if(IS_CHILD) {
		if ( isset( $menu, $Themes_Manager, $submenu ) && $theme['Folder'] != 'runway-framework' ) {
			unset( $submenu['current-theme']['current-theme'] ); // Delete duplicate of theme name
			$options = $Themes_Manager->load_settings( $theme['Folder'] );

			if ( isset( $options['Icon'] ) && $options['Icon'] != '' && $themeKey != null ) {
				$menu[$themeKey][3] = $options['Name'];  // Icon class
				$menu[$themeKey][4] = str_replace( 'menu-icon-generic', '', $menu[$themeKey][4] );
				$menu[$themeKey][4] .= ' '.$options['Icon'];  // Icon class

				if ( $options['Icon'] == 'custom-icon' && file_exists( THEME_DIR.'custom-icon.png' ) ) {
					$menu[$themeKey][6] = get_stylesheet_directory_uri() .'/custom-icon.png';
				} else {
					$menu[$themeKey][6] = isset($options['default-wordpress-icon-class'])? $options['default-wordpress-icon-class'] : '';
				}
			}
		}
	} else {
		$settings = get_settings_json();
		$icon = $settings['Icon'];
		if ( $icon == 'custom-icon' && file_exists( THEME_DIR . 'custom-icon.png' ) ) {
			$menu[$themeKey][6] = get_stylesheet_directory_uri() .'/custom-icon.png';
		} else {

			global $wp_filesystem;

			$settings = json_decode($wp_filesystem->get_contents(THEME_DIR . 'data/settings.json'), true);
			$menu[$themeKey][6] = isset($settings['default-wordpress-icon-class'])? $settings['default-wordpress-icon-class'] : '';
		}
	}
}
add_action( 'admin_head', 'custom_theme_menu_icon' );

function activate_default_child_theme() {
	global $pagenow;
	$theme = rw_get_theme_data();
	if ( is_admin() && $pagenow != 'admin.php' && $pagenow == 'themes.php' && isset( $_GET['activated'] ) && $theme['Folder'] == 'runway-framework' ) {
		wp_redirect( admin_url('admin.php?page=themes&activate-default=activate') );
	}
}
add_action( 'after_setup_theme', 'activate_default_child_theme' );



// Core Admin Menu Object
// -----------------------------------------------------------------

class Admin_menu {
	function __construct() {
		// this is a place holder
	}
}
$admin_menu = new Admin_menu();


// Check for and include "functions-before/after.php"
// -----------------------------------------------------------------

if ( !function_exists( 'before_functions_file' ) ) :
	function before_functions_file() {
		locate_template( 'functions-before.php', true );
	}
add_action( 'functions_before', 'before_functions_file' );
endif;

// if ( !function_exists( 'after_functions_file' ) ) :
// 	function after_functions_file() {
// 		locate_template( 'functions-after.php', true );
// 		register_custom_taxonomies();
// 	}
// add_action( 'functions_after', 'after_functions_file' );
// endif;


function db_json_sync(){
	if  (!function_exists('WP_Filesystem'))
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	WP_Filesystem();
	global $wp_filesystem;
	global $shortname;

	$settings = get_settings_json();

	$option_prefix = $shortname;
	$json_prefix = isset($settings['ThemeID']) ? $settings['ThemeID'] . '_' : $shortname;
	$json_dir = get_stylesheet_directory() . '/data';
	if (IS_CHILD && !is_dir($json_dir)) {
		$json_dir = preg_replace("~\/(?!.*\/)(.*)~", '/' . get_template(), get_stylesheet_directory()) . '/data';
		$json_prefix = apply_filters('shortname', sanitize_title(wp_get_theme(get_template()) . '_'));
	}

	if (is_dir($json_dir)) {
		$ffs = scandir($json_dir);
		
		add_filter('rf_do_not_syncronize', 'do_not_syncronize', 10);

		foreach ($ffs as $ff) {
			if ($ff != '.' && $ff != '..' && pathinfo($ff, PATHINFO_EXTENSION) == 'json') {
				$option_key_json = pathinfo($ff, PATHINFO_FILENAME);
				$option_key = str_replace($json_prefix, $option_prefix, $option_key_json);

				if (in_array($option_key_json, array($json_prefix . 'report-manager')))
					continue;
				if ($option_key_json == $json_prefix . 'formsbuilder_') {
					delete_option($option_key);
					$wp_filesystem->delete($json_dir.'/'.$ff);
					continue;
				}

				if (strpos($option_key_json, $json_prefix) !== false) {
					
					$json = json_decode($wp_filesystem->get_contents($json_dir . '/' . $ff), true);
					// $json = ($option_key_json == $json_prefix . 'formsbuilder_') ? (array) json_decode($wp_filesystem->get_contents($json_dir . '/' . $ff)) :
					// 	json_decode($wp_filesystem->get_contents($json_dir . '/' . $ff), true);
					$db = get_option($option_key);					
					$params = array(
					    'json' => $json,
					    'db' => $db,
					    'current_json_name' => $ff,
					    'json_updated' => $json,
					    'need_update' => false,
					    'excludes' => array(
						array('admin-menu-editor', 'body_structure'),
						array('layouts_manager', 'body_structure'),
						array('layouts_manager', 'layouts'),
						array('layouts_manager', 'headers'),
						array('layouts_manager', 'footers'),
						array('other_options_layout', 'layouts'),
						array('other_options_layout', 'headers'),
						array('other_options_layout', 'footers'),
						array('sidebar_settings', 'sidebars_list'),
						array('layouts_manager', 'contexts'),
						array('content_types', 'content_types'),
						array('content_types', 'taxonomies'),
						array('contact_fields', 'fields'),
						array('contact_fields', 'defaults'),
						'theme_updater'
					    )
					);
					$returned_array = apply_filters('rf_do_not_syncronize', $params);
					$json_updated = $returned_array['json_updated'];
					$need_update = $returned_array['need_update'];
					
					//old functionality
					//$excludes = array('body_structure', 'layouts', 'headers', 'footers', 'sidebars_list', 'contexts', 'content_types', 'taxonomies', 'fields', 'defaults');  // don't synchronize
					//split_data($json, $db, $json_updated, $need_update, $excludes);

					if (!empty($json_updated) && empty($db)) {
						update_option($option_key, $json_updated);
					}
					if ($need_update) {
						update_option($option_key, $json_updated);
					}
				}
			}
		}
	}
}

function do_not_syncronize($params) {
	
	if(isset($params['json'])) {
		foreach($params['json'] as $k => $v) {
			if(is_array($v)) {
				$params['db'][$k] = isset($params['db'][$k]) ? $params['db'][$k] : null;
				$founded = false;
				
				foreach($params['excludes'] as $ex_key => $ex_val) {
					if(!is_array($ex_val) && $k == $ex_val) {
						$founded = true;
						break;
					} else if(is_array($ex_val) && strpos($params['current_json_name'], $ex_val[0]) !== false && $k == $ex_val[1]){
						$founded = true;
						break;
					}
				}
				
				if($founded) {
					if( isset($params['db'][$k]) )
						$params['json_updated'][$k] = $params['db'][$k];
					continue;
				}
				
				$tmp_array = do_not_syncronize(array(
				    'json' => $v,
				    'db' => $params['db'][$k],
				    'current_json_name' => $params['current_json_name'],
				    'json_updated' => $params['json_updated'][$k],
				    'need_update' => $params['need_update'],
				    'excludes' => $params['excludes']
				));
				
				$params['json_updated'][$k] = $tmp_array['json_updated'];
				$params['need_update'] = $tmp_array['need_update'];
			}
			else {
				if(isset($params['db'][$k])) {
					$params['json_updated'][$k] = $params['db'][$k];
				}
				else {
					$params['json_updated'][$k] = $v;
					if(!empty($v))
						$params['need_update'] = true;
				}
			}
		}
	}
	
	return $params;
}

//old functionality
function split_data($json, $db, &$json_updated, &$need_update, &$excludes) {
	
	if(isset($json)) {
		foreach($json as $k => $v) {
			if(is_array($v)) {
				$db[$k] = isset($db[$k])? $db[$k] : null;
				if( in_array($k, $excludes) ) {
					if( isset($db[$k]) )
					  $json_updated[$k] = $db[$k];
					continue;
				}
				split_data($v, $db[$k], $json_updated[$k], $need_update, $excludes);
			}
			else {
				if( isset($db[$k]) ) {
					$json_updated[$k] = $db[$k];
				}
				else {
					$json_updated[$k] = $v;
					if(!empty($v) )
						$need_update = true;
				}
			}
		}
	}
}

function find_custom_recursive($array = array(), $searched_key = '', $returned_key = 0, &$excludes) {
	$tmp_array = array();
	if (!empty($array)) {
		foreach($array as $key=>$value) {
			if(in_array($key, $excludes))
				continue;
			if(is_array($value)) {
				$returned = find_custom_recursive($value, $searched_key, $returned_key, $excludes);
				if(isset($value[$searched_key]) && !empty($value[$searched_key]) && !is_array($value[$searched_key])) {
					if(isset($value[$returned_key]) && !in_array($value[$returned_key], $tmp_array)) {
						$tmp_array[] = trim($value[$returned_key]);
					}
				}
				foreach($returned as $key2 => $value2) {
					if(!empty($value2) && !in_array($value2, $tmp_array)) 
						$tmp_array[] = trim($value2);
				}
			}
			else {
				if(isset($value[$searched_key]) && !empty($value[$searched_key]) && !is_array($value[$searched_key])) {
					if(isset($value[$returned_key]) && !in_array($value[$returned_key], $tmp_array)) {
						$tmp_array[] = trim($value[$returned_key]);
					}
				}
			}
		}
	}
	return $tmp_array;
}

function create_translate_files($translation_dir, $json_dir, $option_prefix, $json_prefix) {
	$ffs = scandir($json_dir);
	$ffs_name = array();
	
	if(!function_exists('WP_Filesystem'))
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	WP_Filesystem();
	global $wp_filesystem;
    foreach($ffs as $ff){
	    if($ff != '.' && $ff != '..' && pathinfo($ff, PATHINFO_EXTENSION) == 'json') {
	    	$option_key_json = pathinfo($ff, PATHINFO_FILENAME);
	    	$ffs_name[] = $option_key_json;
	    	$option_key = str_replace($json_prefix, $option_prefix, $option_key_json);

    		$json = json_decode($wp_filesystem->get_contents( $json_dir . '/' . $ff ), true);

			$translation_file = $translation_dir.'/'.str_replace('.json', '.php', $ff);

			// $excludes = array('layouts', 'headers', 'footers', 'sidebars_list');
			$excludes = array();

			$titles = find_custom_recursive($json, 'title', 'title', $excludes);
			$pageDescription = find_custom_recursive($json, 'pageDescription', 'pageDescription', $excludes);
			$titleCaptions = find_custom_recursive($json, 'titleCaption', 'titleCaption', $excludes);
			$fieldCaptions = find_custom_recursive($json, 'fieldCaption', 'fieldCaption', $excludes);

			$translation_array = array_merge($titles, $pageDescription, $titleCaptions, $fieldCaptions);

			if(!empty($translation_array)) {
				$translation_string = "<?php \r\n// Translation strings\r\n";
				foreach($translation_array as $text)
					$translation_string.= "__('".$text."', 'framework');\r\n";
				$translation_string.= "?>";
				$wp_filesystem->put_contents($translation_file, $translation_string, FS_CHMOD_FILE);
			}
		}	
	}

	$ffs_translation = scandir($translation_dir);
    foreach($ffs_translation as $ff){
	    if($ff != '.' && $ff != '..' && pathinfo($ff, PATHINFO_EXTENSION) == 'php') {
	    	if(!in_array(pathinfo($ff, PATHINFO_FILENAME), $ffs_name))
	    		unlink($translation_dir.'/'.$ff);
	    }
    }
}

function prepare_translate_files(){
	global $shortname;

	$settings = get_settings_json();

	$option_prefix = $shortname;
	$json_prefix = isset($settings['ThemeID'])? $settings['ThemeID'] . '_' : $shortname;
	$json_dir = get_stylesheet_directory() . '/data';

	$translation_dir = $json_dir.'/translation';
	$json_pages_dir = $json_dir.'/pages';
	if (!is_dir($translation_dir.'/pages'))
    	mkdir($translation_dir.'/pages', 0755, true);

    if(is_dir($json_dir)) {
    	create_translate_files($translation_dir, $json_dir, $option_prefix, $json_prefix);
	}    
    if(is_dir($json_pages_dir)) {
    	create_translate_files($translation_dir.'/pages', $json_pages_dir, $option_prefix, $json_prefix);
	}
}


function get_theme_prefix( $folder = false ) {
	$settings = get_settings_json( $folder );
	$theme_prefix = isset($settings['ThemeID'])? $settings['ThemeID'] : '';

	return $theme_prefix;
}

function get_settings_json( $folder = false ) {
	if(!function_exists('WP_Filesystem'))
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	WP_Filesystem();
	global $wp_filesystem;
	$file = (!$folder)? get_stylesheet_directory() . '/data/settings.json' : 
						preg_replace("~\/(?!.*\/)(.*)~", '/'.$folder, get_stylesheet_directory()) . '/data/settings.json';

	$settings = '';
	if ( file_exists( $file ) ) {
		$json = $wp_filesystem->get_contents( $file );
		$settings = json_decode( $json, true );
	}

	return $settings;
}

function create_theme_ID( $length = 0 ) {

    $theme_id = '';
    $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $length = ($length > 0)? $length : 12; // used specified length, or default 12
	for ($i = $length; $i > 0; --$i) {
		$theme_id .= $chars[intval(round((mt_rand() / mt_getrandmax()) * (strlen($chars) - 1)))];
	}

    return $theme_id;
} 

function change_theme_prefix( $theme_name, $theme_id, $json_dir = false ) {
	// global $wpdb, $shortname;

	$json_dir = ($json_dir)? $json_dir : get_stylesheet_directory() . '/data';
    $ffs = scandir($json_dir);

    $flag = true;
	if ( is_dir($json_dir) ) {
		if( !is_writable( $json_dir ) ) {
			$flag = false;
		}
	}
	else 
		$flag = false;

	if( $flag ) {
	    foreach($ffs as $ff){
		    if($ff != '.' && $ff != '..' && pathinfo($ff, PATHINFO_EXTENSION) == 'json') {
		    	$option_key = pathinfo($ff, PATHINFO_FILENAME);

	    		$theme_id_prefix = ($pos = strpos($option_key, '_'))? substr($option_key, 0, $pos+1) : '';
			 	if( $theme_id_prefix != $theme_id . '_' ) {
				 	if (!rename($json_dir.'/'.$ff, str_replace($theme_id_prefix, $theme_id.'_', $json_dir.'/'.$ff)) )
					 	$flag = false;
				}
			}	
		}
	}

	// if($flag) {
	// 	$sql = $wpdb->prepare( "SELECT option_name FROM wp_options WHERE option_name LIKE %s", str_replace('_', '\_', $shortname).'%' );
	// 	$res = $wpdb->get_results($sql, ARRAY_A);

	// 	foreach($res as $key => $option) {
	// 		$data_pre = get_option($option['option_name']);
	// 		$option_key_new = str_replace($shortname, $theme_id.'_', $option['option_name']);
	// 		update_option($option_key_new, $data_pre);
	// 		$data_post = get_option($option_key_new);
	// 		if($data_pre != $data_post) {
	// 			$flag = false;

	// 		}
	// 	} 
	// }

	return $flag;
}

function ask_new_theme( $old_theme, $new_theme, $link) {
	?>
	<div class="error">
		<p><strong>
		<?php echo __('We noticed this theme was previously named', 'framework') . '<i> ' . $old_theme. '</i> '. __('but is now named', 'framework') . ' <i>' . $new_theme . '</i>.<br>' .
		__('If this is a new theme you should create a new unique ID for the data file to avoid any data collisions', 'framework') . '.<br>' .
		__('If this is the same theme and you are just renaming it, you should keep this ID the same' , 'framework') . '.<br>' .
		__('Do you want to create a new ID now?', 'framework') . '</strong>'; ?>
		<a href="<?php echo add_query_arg( 'create-theme-id', 1, $link ); ?>"><?php _e('Yes', 'framework') ?></a>
		<a href="<?php echo add_query_arg( 'create-theme-id', 0, $link ); ?>"><?php _e('No', 'framework') ?></a>
		</p>
	</div>
	<?php
}

function check_theme_ID( $folder = false ) {
	global $shortname;
	if(!function_exists('WP_Filesystem'))
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	WP_Filesystem();
	global $wp_filesystem;

	$settings = get_settings_json( $folder );

	if( isset($settings['Name']) ) {
		$themeInfo = rw_get_theme_data();
		$theme_name_stylecss = $themeInfo['Name'];

		if( isset($settings['ThemeID']) ) {
			$theme_prefix_old = isset($settings['ThemeID'])? $settings['ThemeID'] : $shortname;
			if( change_theme_prefix( $theme_prefix_old, $settings['ThemeID'] ) ) {
				$wp_filesystem->put_contents(get_stylesheet_directory() . '/data/settings.json', json_encode($settings), FS_CHMOD_FILE);
			}
		}

		if(empty($theme_name_stylecss))
			$theme_name_stylecss = __('Unknown Theme (Theme Name in style.css is empty)', 'framework');

		if( isset($settings['Name']) && $theme_name_stylecss != $settings['Name'] ) {

			$link = (IS_CHILD)? admin_url('admin.php?page=themes') : admin_url('themes.php');
			if(isset($_GET['create-theme-id'])) {
				if($_GET['create-theme-id']) {
					$theme_prefix_old = isset($settings['ThemeID'])? $settings['ThemeID'] : $shortname;
					$theme_id = create_theme_ID();
					$settings['ThemeID'] = $theme_id;

				  	if( change_theme_prefix( $theme_prefix_old, $settings['ThemeID'] ) ) {
				  		$settings['Name'] = $theme_name_stylecss;
						$wp_filesystem->put_contents(get_stylesheet_directory() . '/data/settings.json', json_encode($settings), FS_CHMOD_FILE);
				  	}
			  	}
			  	else {
					$settings['Name'] = $theme_name_stylecss;
					$wp_filesystem->put_contents(get_stylesheet_directory() . '/data/settings.json', json_encode($settings), FS_CHMOD_FILE);
			  	}
				$redirect = '<script type="text/javascript">window.location = "'.$link.'";</script>';
				echo $redirect;
			}
			add_action('admin_notices', 'ask_new_theme', 10, 3);
			do_action('admin_notices', $settings['Name'], $theme_name_stylecss, $link );
			remove_action('admin_notices', 'ask_new_theme');
		}
		else
			return true;
	}
	
}

if ( !function_exists( 'runway_base_decode' ) ) {
	function runway_base_decode($data, $is_file = false) {
 	
		$b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

		$i = 0;
		$ac = 0;
		$dec = '';
		$tmp_arr = array();

		if (!$data) {
			return $data;
		}

		$len = strlen($data);
		$dec = '';

		do {
			$h1 = strpos($b64, $data{$i++});
			$h2 = strpos($b64, $data{$i++});
			$h3 = strpos($b64, $data{$i++});
			$h4 = strpos($b64, $data{$i++});

			$bits = $h1 << 18 | $h2 << 12 | $h3 << 6 | $h4;

			$o1 = $bits >> 16 & 0xff;
			$o2 = $bits >> 8 & 0xff;
			$o3 = $bits & 0xff;

			if ($h3 == 64) {
				$dec.= chr($o1);
			} else if ($h4 == 64) {
				$dec.= chr($o1).chr($o2);
			} else {
				$dec.= chr($o1).chr($o2).chr($o3);
			} 
		} while ($i < $len);

		if(!$is_file)
			return preg_replace('/\0+$/', '', $dec);
		else
			return $dec;
     }
}

if ( !function_exists( 'runway_base_encode' ) ) {
    function runway_base_encode($data){

        if (!$data) {
            return $data;
        }

        $b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

        $i = 0;
        $enc = '';
        $data_len = strlen($data);
        do{

            $o1 = ord($data{$i++});
            $o2 = ord($data{$i++});
            $o3 = ord($data{$i++});

            $bits = $o1<<16 | $o2<<8 | $o3;

            $h1 = $bits>>18 & 0x3f;
            $h2 = $bits>>12 & 0x3f;
            $h3 = $bits>>6 & 0x3f;
            $h4 = $bits & 0x3f;

            $enc .= $b64{$h1} . $b64{$h2} . $b64{$h3} . $b64{$h4};

        }while($i < $data_len);

        switch( $data_len % 3 ){
            case 1:
                $enc = substr($enc, 0, -2) . '==';
            break;
            case 2:
                $enc = substr($enc, 0, -1) . '=';
            break;
        }

        return $enc;

    }
}

if(!function_exists('runway_check_versions')) {
	function runway_check_versions($new_version, $old_version) {
		$repository_version = str_replace("v", "", $new_version);
		$repository_version_array = explode('.', $repository_version);
		$current_version_array = explode('.', $old_version);

		$max_version_length = (count($repository_version_array) > count($current_version_array) ? count($repository_version_array) : count($current_version_array));
		$has_update = false;
		for($i = 0; $i < $max_version_length; $i++) {
			if(isset($repository_version_array[$i]) && isset($current_version_array[$i])) {
				if($repository_version_array[$i] > $current_version_array[$i]) {
					$has_update = true;
					break;
				}
			} else if(!isset($current_version_array[$i]) && isset($repository_version_array[$i])) {
				$has_update = true;
				break;
			} else if(isset($current_version_array[$i]) && !isset($repository_version_array[$i])){
				break;
			}
		}
		
		return $has_update;
	}
}
?>