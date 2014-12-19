<?php
/**
 * Registered actions:
 * 1. before_search_extensions
 * 2. after_search_extensios
 * 3. before_activate_extension
 * 4. after_activate_extension
 * 5. before_deactivate_extension
 * 6. after_deactivate_extension
 * 7. before_delete_extension
 * 8. after_delete_extension
 * 9. before_load_extension
 * 10. after_load_extension
 */
class Extm_Admin extends Runway_Admin_Object {

	public $extensions_dir, $theme_name, $admin_settings, $themes_path, $ext_manager_load_file, $core_extensions,
	$data_dir, $option_key, $extensions_List;

	function __construct( $settings ) {
		parent::__construct($settings);
		// global $settings;
		$this->option_key = $settings['option_key'];
		$this->core_extensions = get_template_directory() . '/framework/includes/';
		$this->data_dir = get_stylesheet_directory() . '/data';
		$this->ext_manager_load_file = get_template_directory() . '/framework/includes/extensions-manager/load.php';
		$this->extensions_dir = get_template_directory() . '/extensions/';
		$this->theme_name = get_stylesheet();
		$this->admin_settings = get_option( $this->option_key );
		$this->themes_path = explode( '/', get_template_directory() );
		unset( $this->themes_path[count( $this->themes_path ) - 1] );
		$this->themes_path = implode( '/', $this->themes_path );

		$this->extensions_List = $this->get_extensions_list( $this->extensions_dir );
	}
	
	function get_admin_data( $option_key ) {

		return get_option( $option_key.'_extensions-manager' );

	}

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );

	}

	function init() {

		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $extm;
			$extm->navigation = $_REQUEST['navigation'];
		}

	}

	function after_settings_init() {

	}

	function validate_sumbission() {
		// If all is OKq
		return true;

	}

	function load_objects() {

		// global $extm;
		// $this->data = $extm->load_objects();
		// return $this->data;

	}

	/**
	 * Search extensions function
	 *
	 * @param array   $exts
	 * @param unknown $keyword
	 * @return array
	 */
	function search_exts( $exts = array(), $keyword ) {

		do_action( 'before_search_extensions' );

		foreach ( $exts as $key => $value ) {
			$ext_name = strtolower( $value['Name'] );
			$keyword = strtolower( $keyword );
			if ( !strstr( $ext_name, $keyword ) ) {
				unset( $exts[$key] );
			}
		}

		do_action( 'after_search_extensions' );

		return $exts;

	}

	/**
	 * Check resolution to do the action (activate/deactivate/delete)
	 *
	 * @param unknown $dependences
	 * @return bool
	 */
	function resolution_to_action( $dependences ) {

		$resolution = FALSE;
		if ( empty( $dependences ) ) {
			$resolution = TRUE;
		}

		if ( !empty( $dependences ) ) {
			foreach ( $dependences as $depsendece ) {
				if ( $this->check_dependence_extension( $depsendece ) ) {
					$resolution = TRUE;
				} else {
					$resolution = FALSE;
				}
			}
		} else {
			$resolution = TRUE;
		}

		return $resolution;

	}

	/**
	 * Check the extensions dependencies
	 *
	 * @param unknown $dependence
	 * @return bool
	 */
	function check_dependence_extension( $dependence ) {

		$ext_on = FALSE;
		$dependence = explode( '|', $dependence );
		if ( isset( $this->admin_settings['extensions'][$this->theme_name] ) ) {
			foreach ( $this->admin_settings['extensions'][$this->theme_name] as $ext ) {
				if ( $ext == $dependence[1] ) {
					$ext_on = TRUE;
				}
			}
		}
		if ( file_exists( $this->extensions_dir . $dependence[1] ) && $ext_on ) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	/**
	 * Get the dependent extensions
	 *
	 * @param unknown $ext
	 * @return array
	 */
	function get_dependent_extensions( $ext ) {

		$dependets = array();
		$exts_list = $this->get_extensions_list( $this->extensions_dir );

		foreach ( $exts_list as $ext_key => $ext_info ) {
			if ( $ext_info['DepsExts'] != '' ) {
				foreach ( $ext_info['DepsExts'] as $deps ) {
					$deps = explode( '|', $deps );
					if ( $deps[1] == $ext ) {
						$dependets[] = $ext_key;
					}
				}
			}
		}
		return $dependets;

	}

	/**
	 * Get extension dependencies list
	 *
	 * @param unknown $ext
	 * @return array
	 */
	function get_extension_dependencies( $ext ) {

		$dependencies = array();
		$ext_info = $this->get_extension_data( $ext );
		if ( isset( $ext_info['DepsExts'] ) && is_array( $ext_info['DepsExts'] ) ) {
			foreach ( $ext_info['DepsExts'] as $dep ) {
				$dep = explode( '|', $dep );
				$dependencies[] = $dep[1];

				$dep_tmp = $this->get_extension_data( $this->extensions_dir . $dep[1] );
				if ( isset( $dep_tmp['DepsExts'] ) && !empty( $dep_tmp['DepsExts'] ) ) {
					$dependencies = array_merge( $dependencies, $this->get_extension_dependencies( $this->extensions_dir . $dep[1] ) );
				}
			}
		}
		$dependencies = array_unique( $dependencies );

		return $dependencies;

	}


	/**
	 * Deactivate extension function
	 *
	 * @param unknown $extension
	 * @return string
	 */
	function deactivate_extension( $extension ) {

		do_action( 'before_deactivate_extension' );

		if ( isset( $this->admin_settings['extensions'][$this->theme_name]['active'] ) && isset( $extension ) ) {
			foreach ( $this->admin_settings['extensions'][$this->theme_name]['active'] as $key => $value ) {
				if ( $value == urldecode( $extension ) ) {
					$this->admin_settings['extensions'][$this->theme_name]['unactive'][$key] = $this->admin_settings['extensions'][$this->theme_name]['active'][$key];
					unset( $this->admin_settings['extensions'][$this->theme_name]['active'][$key] );
					update_option( $this->option_key, $this->admin_settings );

					$also_deactivate = $this->get_dependent_extensions( $extension );
					if ( !empty( $also_deactivate ) ) {
						foreach ( $also_deactivate as $deact ) {
							$this->deactivate_extension( $deact );
						}
					}
					do_action( 'after_deactivate_extension' );
					return __( 'Extension deactivated', 'framework' );
				}
			}
		} else {
			return false;
		}

	}

	/**
	 * Activate extensions list without check to resolutions. Using only to activated dependencies-list for extension
	 *
	 * @param unknown $extensions
	 * @return string
	 */
	function activate_extensions_without_resolution_check( $extensions ) {

		$act_exts = array();
		foreach ( $extensions as $extension ) {
			$this->admin_settings['extensions'][$this->theme_name][] = urldecode( $extension );
			$this->admin_settings['extensions'][$this->theme_name] = array_unique( $this->admin_settings['extensions'][$this->theme_name] );
			update_option( $this->option_key, $this->admin_settings );
			$extension = $this->get_extension_data( $this->extensions_dir . $extension );
			$act_exts[] = $extension['Name'];
		}
		return __( 'Extension', 'framework' ).': <b>' . implode( ',', $act_exts ) . '</b> '.__( 'activated', 'framework' );

	}

	/**
	 * Deactivate extension function
	 *
	 * @param unknown $extension
	 * @return string
	 */
	function activate_extension( $extension ) {

		do_action( 'before_activate_extension' );

		$ext_data = $this->get_extension_data( $this->extensions_dir . $extension );
		if ( $this->resolution_to_action( $ext_data['DepsExts'] ) ) {
			$this->admin_settings['extensions'][$this->theme_name]['unactive'] = array_filter(
				(array)$this->admin_settings['extensions'][$this->theme_name]['unactive'],
				function( $elm ) use ( $extension ) {
					if ( $elm != $extension ) {
						return $elm;
					}
				}
			);
			unset( $this->admin_settings['extensions'][$this->theme_name]['unactive'][urldecode( $extension )] );
			$this->admin_settings['extensions'][$this->theme_name]['active'][] = urldecode( $extension );
			$this->admin_settings['extensions'][$this->theme_name]['active'] = array_unique( $this->admin_settings['extensions'][$this->theme_name]['active'] );
			update_option( $this->option_key, $this->admin_settings );

			do_action( 'after_activate_extension' );
			return __( 'Extension activate', 'framework' );
		} else {
			$deps_list = '<b>' . $ext_data['Name'] . '</b> - '.__( 'extension not activate. To activate this extension you must activate next extensions', 'framework' ).':<ul>';
			$dep_exts = array();
			foreach ( $ext_data['DepsExts'] as $dep ) {
				$dep_info = explode( '|', $dep );
				if ( !$this->is_activated( $dep_info[1] ) && preg_match( '|(.+)/load.php|', $dep_info[1] ) ) {
					$dep_exts[] = $dep_info[1];
				}
			}

			foreach ( $dep_exts as $dep_ext ) {
				$tmp_dep = $this->get_extension_data( $this->extensions_dir . $dep_ext );
				$tmp_dep = array_filter( (array)$tmp_dep['DepsExts'], 'is_active_filter' );

				if ( is_array( $tmp_dep ) && !empty( $tmp_dep ) ) {
					foreach ( $tmp_dep as $tmp ) {
						$tmp = explode( '|', $tmp );
						if ( $this->extensions_dir . $tmp[0] )
							$dep_exts[] = $tmp[0];
					}
				}
			}
			$dep_exts[] = $extension;
			$dep_exts = array_unique( $dep_exts );

			foreach ( $dep_exts as $dep_ext ) {
				if ( $dep_ext != '' ) {
					$dep_info = $this->get_extension_data( $this->extensions_dir . $dep_ext );
					$deps_list .= '<li><b>- ' . $dep_info['Name'] . '</b></li>';
				}
			}

			$deps_list .= '</ul>';
			$deps_list .= '<b><a href="admin.php?page=extensions&navigation=extension-activate&dep-exts=' . implode( ',', $dep_exts ) . '">'.__( 'Activate dependencies and selected extensions', 'framework' ).'</a></b>';
			return $deps_list;
		}

	}

	/**
	 * Check activity of extensions
	 *
	 * @param unknown $ext
	 * @return bool
	 */
	function is_activated( $ext ) {

		if ( isset($this->admin_settings['extensions'][$this->theme_name]['active']) && in_array( $ext, (array)$this->admin_settings['extensions'][$this->theme_name]['active'] ) )
			return TRUE;
		else return FALSE;

	}

	/**
	 * Get extensions list by extension dir
	 *
	 * @param unknown $exts_dir
	 * @return array
	 */
	function get_extensions_list( $exts_dir ) {

		$runway_extensions = array();
		$plugins_dir = @ opendir( $exts_dir );
		$ext_files = array();
		if ( $plugins_dir ) {
			while ( ( $file = readdir( $plugins_dir ) ) !== false ) {
				if ( substr( $file, 0, 1 ) == '.' )
					continue;
				if ( is_dir( $exts_dir . '/' . $file ) ) {
					$plugins_subdir = @ opendir( $exts_dir . '/' . $file );
					if ( $plugins_subdir ) {
						while ( ( $subfile = readdir( $plugins_subdir ) ) !== false ) {
							if ( substr( $subfile, 0, 1 ) == '.' )
								continue;
							if ( substr( $subfile, -4 ) == '.php' )
								$ext_files[] = $file.'/'.$subfile;
						}
						closedir( $plugins_subdir );
					}
				} else {
					if ( substr( $file, -4 ) == '.php' )
						$ext_files[] = $file;
				}
			}
			closedir( $plugins_dir );
		}

		if ( empty( $ext_files ) )
			return $runway_extensions;

		foreach ( $ext_files as $ext_file ) {
			if ( !is_readable( $exts_dir.'/'.$ext_file ) ) {
				continue;
			}

			$ext_data = $this->get_extension_data( $exts_dir.'/'.$ext_file );

			if ( empty ( $ext_data['Name'] ) ) {
				continue;
			}

			$runway_extensions[plugin_basename( $ext_file )] = $ext_data;
		}

		return $runway_extensions;

	}

	/**
	 * Get extension data by PHP comments similar to the WP plugins
	 *
	 * @param unknown $ext_file
	 * @param bool    $markup
	 * @param bool    $translate
	 * @return array|string
	 */
	function get_extension_data( $ext_file ) {

		$default_headers = array(
			'Name' => 'Extension Name',
			'ExtensionURI' => 'Extension URI',
			'Version' => 'Version',
			'Description' => 'Description',
			'Author' => 'Author',
			'AuthorURI' => 'Author URI',
			'TextDomain' => 'Text Domain',
			'DomainPath' => 'Domain Path',
			// 'Network' => 'Network',
			// '_sitewide' => 'Site Wide Only',
			'DepsExts' => 'Dependence Extensions',
		);
		$ext_data = array();
		$ext_data = get_file_data( $ext_file, $default_headers );

		$ext_data['Title'] = $ext_data['Name'];
		$ext_data['AuthorName'] = $ext_data['Author'];

		if ( $ext_data['DepsExts'] ) {
			$ext_data['DepsExts'] = explode( ',', $ext_data['DepsExts'] );
		}

		return $ext_data;

	}

	/**
	 * Deleting extension function
	 *
	 * @param unknown $ext
	 * @return string
	 */
	function del_extension( $ext ) {
		global $wp_filesystem;
		
		do_action( 'before_delete_extension' );

		$del_ext = explode( '/', $ext );
		
		if ( is_dir( $this->extensions_dir . $del_ext[0] ) ) {
			$wp_filesystem->delete($this->extensions_dir . $del_ext[0], true);
		} elseif ( is_file( $this->extensions_dir . $del_ext[0] ) ) {
			unlink( $this->extensions_dir . $del_ext[0] );
		}
		
		foreach ( $this->admin_settings as $key => $value ) {
			if ( $value == urldecode( $ext ) ) {
				unset( $this->admin_settings );
				update_option( $this->option_key, $this->admin_settings );
			}
		}

		do_action( 'after_delete_extension' );
		return __( 'Extension was deleted', 'framework' );

	}

	function get_active_extensions_list( $theme_name ) {

		$theme_name = strtolower( $theme_name );
		if( $theme_name != get_stylesheet() ) {
			$theme = wp_get_theme( $theme_name );
			$admin_data = $this->get_admin_data( $theme->Name );
			$exts_list = !empty( $admin_data['extensions'][$theme_name]['active'] ) ? 
				$admin_data['extensions'][$theme_name]['active'] :
				array();
		}
		else {
			$exts_list = !empty( $this->admin_settings['extensions'][$theme_name]['active'] ) ? 
				$this->admin_settings['extensions'][$theme_name]['active'] :
				array();
		}

		return (array)$exts_list;

	}

	function get_desible_extensions_list( $theme_name ) {
		$exts = $this->get_extensions_list( $this->extensions_dir );
		$desible = array();
		foreach ( $exts as $extension => $extension_info ) {
			if ( !$this->is_activated( $extension ) ) {
				$desible[$extension] = $extension_info;
			}
		}

		return $desible;
	}



	function load_new_extension( $file ) {

		do_action( 'before_load_extension' );
		$upload_dir = wp_upload_dir();

		$zip = new ZipArchive();
		$res = $zip->open($file);

		if ( file_exists( $file ) ) {
			if ( is_writable( $this->extensions_dir ) ) {
				if(unzip_file($file, $this->extensions_dir) !== true) {
					return __( 'Install error', 'framework' ).': '.$zip->getStatusString();
				}
				else {
					$ext = explode( '/', $zip->getNameIndex( 0 ) );
					$ext = $ext[0].'/load.php';
					$ext_info = $this->get_extension_data( $this->extensions_dir.$ext );

					if ( $zip->status == 0 ) {
						do_action( 'after_load_extension' );
						return __( 'Extension', 'framework' ).' <b>'.$ext_info['Name'].'</b> '.__( 'has been installed. Do you want to activate it? ', 'framework' ).' <a href="admin.php?page=extensions&navigation=extension-activate&ext='.$ext.'">'.__( 'Activate it', 'framework' ).'</a>?';
					}
					else {
						return __( 'Install error', 'framework' ).': '.$zip->getStatusString();
					}

				}
			}
			else return __( 'Extensions directory must be writable', 'framework' );
		}

		$overrides = array( 'test_form' => false, 'test_type' => false );
		$ext_file = wp_handle_upload( $file, $overrides );
		if ( isset( $ext_file['error'] ) ) {
			return '<b>'.__( 'ERROR', 'framework' ).':</b>'.$ext_file['error'];
		}
		else {
			if ( is_writable( $this->extensions_dir ) ) {
				$exploded = $exploded = explode( '.', $file['name'] );
				$ext = $exploded[0].'/load.php';
				
				if ( !file_exists( $this->extensions_dir.$ext ) ) {
					unzip_file($ext_file['file'], $this->extensions_dir);
					$ext_info = $this->get_extension_data( $this->extensions_dir.$ext );
					unlink( $ext_file['file'] );
				}
				else {
					unlink( $ext_file['file'] );
					return __( 'Extension has already installed', 'framework' );
				}

				if ( $zip->status == 0 ) {
					do_action( 'after_load_extension' );
					return __( 'The extension', 'framework' ).' <b>'.$ext_info['Name'].'</b> '.__( 'installed successfully. Would you like to', 'framework' ).' <a href="admin.php?page=extensions&navigation=extension-activate&ext='.$ext.'">'.__( 'activate it now', 'framework' ).'</a>?';
				}
				else {
					return __( 'Install error', 'framework' ).': '.$zip->getStatusString();
				}
			}
		}

	}

	public function is_install( $extension_key ) {
		$extension = $extension_key.'/load.php';
		$ext_list = $this->get_extensions_list( $this->extensions_dir );
		if ( isset( $ext_list[$extension] ) ) {
			return true;
		}
		else return false;
	}

}


/**
 * Callback function to check is active extensions
 *
 * @param unknown $var
 * @return bool
 */
function is_active_filter( $var ) {
	global $extm;
	$tmp = explode( '|', $var );
	return !$extm->is_activated( $tmp[0] );
}
?>
