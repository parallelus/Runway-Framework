<?php
/**
 * Registered actions:
 * 1. before_save_theme_settings - takes theme settings options array
 * 2. after_save_theme_settings - takes theme settings options array
 * 3. before_build_theme_css - takes theme options array
 * 4. after_build_theme_css - takes theme css string
 * 5. before_delete_child_theme - takes theme name
 * 6. after_delete_chold_theme - takes theme name
 * 7. before_build_child_package - takes theme name
 * 8. after_build_child_package - takes theme name and download path
 * 9. before_build_alone_theme - takes theme name
 * 10. after_build_alone_theme - takes theme name and download path
 */

class Themes_Manager_Admin extends Runway_Admin_Object {
	public $extensions_dir;

	function __construct($settings){
		parent::__construct($settings);

		$this->extensions_dir = get_template_directory() . '/framework/extensions/';
		$this->themes_path = $this->build_themes_path();
		$this->themes_url = home_url().'/wp-content/themes';
		$this->default_theme_package_path = get_template_directory() . '/framework/themes/default.zip';
	}

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );
		add_action('wp_ajax_get_package_tags', array($this, 'ajax_get_package_tags'));
		add_action('wp_ajax_update_package_tags', array($this, 'ajax_update_package_tags'));
	}

	function init() {
		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			$this->navigation = $_REQUEST['navigation'];
		}
	}

	function after_settings_init() {

	}

	function validate_sumbission() {

		// If all is OKq
		return true;

	}

	function get_package_tags( $id ) {

		$packages_dir = THEME_DIR.'data/packages';
		if (!is_dir($packages_dir))
		    mkdir($packages_dir, 0755, true);

		$tags_file = $packages_dir.'/package_'.$id;
		if(file_exists($tags_file)) {
			if(!function_exists('WP_Filesystem'))
				require_once(ABSPATH . 'wp-admin/includes/file.php');
			WP_Filesystem();
			global $wp_filesystem;
			$tags = $wp_filesystem->get_contents($tags_file);
			return $tags;
		}

		return false;
	}

	function ajax_get_package_tags( ) {

		$tags = $this->get_package_tags( $_REQUEST['id'] );
		die($tags);
	}

	function ajax_update_package_tags() {

		$tags = array('id' => $_REQUEST['id'],
					  'tags_show' => isset($_REQUEST['tags_show'])? $_REQUEST['tags_show'] : '',
					  'tags_edit' => isset($_REQUEST['tags_edit'])? $_REQUEST['tags_edit'] : ''
			    );
		$this->update_package_tags( $tags );
		die();
	}

	function update_package_tags( $tags = array() ) {

		$packages_dir = THEME_DIR.'data/packages';
		if (!is_dir($packages_dir))
		    mkdir($packages_dir, 0755, true);

		$tags_file = $packages_dir.'/package_'.$tags['id'];

		if(!function_exists('WP_Filesystem'))
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->put_contents($tags_file, json_encode($tags), FS_CHMOD_FILE);
	}

	function load_objects() {
		global $developer_tools;
		$this->data = $developer_tools->load_objects();
		return $this->data;
	}

	// theme settings validation rules
	function validate_theme_settings( $settings = null ) {

		$errors = array();

		// if settings are empty
		if ( !$settings )
			return $errors[] = '';

		// Theme title validation
		if ( !isset( $settings['Name'] ) || empty( $settings['Name'] ) )
			$errors[] = __('Theme title is required', 'runway');

		if ( !preg_match( '/([a-zA-Z])/', $settings['Name'] ) ) {
			$errors[] = __('Theme title need to have at least one character', 'runway');
		}

		if ( empty( $settings['Folder'] ) && isset( $settings['Name'] ) && !empty( $settings['Name'] ) ) {
			$settings['Folder'] = $this->make_theme_folder_from_name( $settings['Name'] );
		}

		$_REQUEST['base_name'] = ( isset( $_REQUEST['base_name'] ) ) ? $_REQUEST['base_name'] : '';
		if ( $_REQUEST['base_name'] != $settings['Folder'] ) {
			if ( file_exists( $this->themes_path . '/' . $settings['Folder'] ) ) {
				$errors[] = __('Please choose another theme folder', 'runway');
			}
		}

		return $errors;

	}

	// search only for Runway themes or themes based on Runway
	function search_themes() {

		$themes_dir = opendir( $this->themes_path );
		$themes_list = array();

		while ( $dir = readdir( $themes_dir ) ) {
			if ( $dir != '.' && $dir != '..' && is_dir( $this->themes_path.'/'.$dir ) ) {
				//$theme = $this->load_settings($dir);
				$theme = rw_get_theme_data( $this->themes_path.'/'.$dir );
				// add to list themes which based on runway
				if ( file_exists( $this->themes_path.'/'.$dir.'/style.css' ) )
					if ( $theme['Template'] == 'runway-framework' )
						$themes_list[$dir] = $theme;
			}
		}
		if ( file_exists( $this->themes_path.'/runway-framework' ) )
			$themes_list['runway-framework'] = rw_get_theme_data( $this->themes_path.'/runway-framework' );

		return $themes_list;

	}

	function make_theme_copy( $name = null, $new_name = null ) {

		if ( !$name || !$new_name ) return false;

		if ( file_exists( $this->themes_path . '/' . $new_name ) ) return false;

		$themeInfo   	= rw_get_theme_data();
		$themeTitle  	= trim( $themeInfo['Title'] );

		// copy source theme
		$this->copy_r( $this->themes_path . '/' . $name, $this->themes_path . '/' . $new_name );

		if(!function_exists('WP_Filesystem'))
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		$settings = $wp_filesystem->get_contents($this->themes_path . '/' . $new_name . '/data/settings.json');

		$settings = json_decode($settings, true);
		$settings['Folder'] = $new_name;
		$theme_prefix_old = isset($settings['ThemeID'])? $settings['ThemeID'] : apply_filters( 'shortname', sanitize_title( $themeTitle ) );
		$settings['ThemeID'] = create_theme_ID();
		unset($settings['isPrefixID']);

		if( change_theme_prefix( $theme_prefix_old, $settings['ThemeID'], $this->themes_path . '/' . $new_name . '/data' ) ) {
			$wp_filesystem->put_contents($this->themes_path . '/' . $new_name . '/data/settings.json', json_encode($settings), FS_CHMOD_FILE);
		}

		$theme_info = $wp_filesystem->get_contents( $this->themes_path . '/' . $new_name . '/style.css' );
		$theme_info = str_replace( "Theme Name:     $name", "Theme Name:     $new_name", $theme_info );
		$wp_filesystem->put_contents($this->themes_path . '/' . $new_name . '/style.css', $theme_info, FS_CHMOD_FILE);

		return $settings;

	}

	// extract wordpress themes path
	function build_themes_path() {

		$path = explode( '/', get_template_directory() );
		unset( $path[count( $path ) - 1] );

		return implode( '/', $path );

	}

	// return extended theme information array
	function theme_information( $folder ) {

		if ( !file_exists( $this->themes_path . '/' . $folder . '/style.css' ) ) return null;

		$theme = rw_get_theme_data( $this->themes_path . '/' . $folder );

		if ( file_exists( $this->themes_path . '/' . $folder . '/screenshot.png' ) ) {
			$theme['screenshot'] = home_url() . '/wp-content/themes/' . $folder . '/screenshot.png';
		} else {
			$theme['screenshot'] = home_url() . '/wp-content/themes/runway-framework/screenshot.png';
		}

		$theme['Folder_location'] = '/wp-content/themes/' . $folder;
		$theme['Folder'] = $folder;

		return $theme;
	}

	// save settings array to JSON file
	function save_settings( $theme_folder, $settings ) {

		do_action( 'before_save_theme_settings', $settings );
		$json = json_encode( $settings );

		if(!function_exists('WP_Filesystem'))
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->put_contents($this->themes_path . '/' . $theme_folder . '/data/settings.json', $json, FS_CHMOD_FILE);
		do_action( 'after_save_theme_settings', $settings );

	}

	// load settings array from JSON file
	function load_settings( $theme_folder ) {

		if(!function_exists('WP_Filesystem'))
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;

		$settings = array();
		$settings_file = $this->themes_path . '/' . $theme_folder . '/data/settings.json';
		if ( file_exists( $this->themes_path . '/' . $theme_folder . '/data/settings.json' ) ) {
			$json = $wp_filesystem->get_contents($this->themes_path . '/' . $theme_folder . '/data/settings.json');
			$settings = json_decode( $json, true );
		}
		else {
			if ( !file_exists( $this->themes_path . '/' . $theme_folder . '/data' ) ) {
				if ( is_writable( $this->themes_path . '/' . $theme_folder . '/data' ) ) {
					if ( mkdir( $this->themes_path . '/' . $theme_folder . '/data', 0755, true ) ) {
						$wp_filesystem->put_contents($settings_file, '', FS_CHMOD_FILE);
					}
				}
			}
			elseif ( !file_exists( $settings_file ) ) {
				$wp_filesystem->put_contents($settings_file, '', FS_CHMOD_FILE);
			}
		}

		return $settings;
	}

	function make_theme_folder_from_name( $name = null ) {

		$folder = strtolower( $name );
		$folder = str_replace( ' ', '-', $folder );
		$folder = str_replace( "'", '-', $folder );

		return $folder;

	}

	// build and save child theme
	function build_and_save_theme( $options , $new_theme = true ) {
		global $extm;
		if(!function_exists('WP_Filesystem'))
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;

		// extract tags from string
		$options['Tags'] = explode( ' ', $options['Tags'] );

		// set template to runway-framework
		$options['Template'] = 'runway-framework';

		// if theme folder unknown name folder like theme name
		if ( !isset( $options['Folder'] ) || empty( $options['Folder'] ) )
			$options['Folder'] = $this->make_theme_folder_from_name( $options['Name'] );
		else
			$options['Folder'] = $this->make_theme_folder_from_name( $options['Folder'] );

		// check form mode new or edit(duplicate)
		$this->mode = ( isset( $this->mode ) ) ? $this->mode : '';
		if ( $this->mode == 'new' ) {
			if ( file_exists( $this->themes_path . '/' . $options['Folder'] ) ) return false;
			mkdir( $this->themes_path . '/' . $options['Folder'] );
		} else {
			// change theme folder
			if ( !file_exists( $this->themes_path . '/' . $options['Folder'] ) ) {
				rename( $this->themes_path . '/' . $_REQUEST['name'], $this->themes_path . '/' . $options['Folder'] );
				// change file names into changed theme folder
				$this->rename_history_packages( $options['Folder'] );
			}
		}

		if ( !file_exists( $this->themes_path . '/' . $options['Folder'] . '/data' ) ) {
			mkdir( $this->themes_path . '/' . $options['Folder'] . '/data' );
		}

		// check if have new screenshot and if true move file to theme folder
		if ( $_FILES['theme_options']['name']['Screenshot'] != '' ) {
			imagepng(
				imagecreatefromstring(
					$wp_filesystem->get_contents($_FILES['theme_options']['tmp_name']['Screenshot'])
				),
				$this->themes_path . '/' . $options['Folder'] . '/screenshot.png'
			);
			$options['Screenshot'] = true;
		}

		// check if have new custom icon and if true move file to theme folder
		if ( $_FILES['theme_options']['name']['CustomIcon'] != '' ) {

			imagepng(
				imagecreatefromstring(
					$wp_filesystem->get_contents($_FILES['theme_options']['tmp_name']['CustomIcon'])
				),
				$this->themes_path . '/' . $options['Folder'] . '/custom-icon.png'
			);

	        $image = wp_get_image_editor($_FILES['theme_options']['tmp_name']['CustomIcon']);

        	if(!is_wp_error($image)){
	             $image->resize(36, 36);
    	         $image->save($this->themes_path . '/' . $options['Folder'] . '/custom-icon.png');
	        }

			$options['CustomIcon'] = true;
		}

		if ( file_exists( $this->themes_path . '/' . $options['Folder'] . '/custom-icon.png' ) ) {
			$options['CustomIcon'] = true;
		}

		// If no custom screenshot copy default
		if ( file_exists( $this->themes_path . '/' . $options['Folder'] . '/screenshot.png' ) ) {
			$options['Screenshot'] = true;
		}
		else {
			copy( $this->themes_path.'/'.$options['Template'].'/screenshot.png', $this->themes_path.'/'.$options['Folder'].'/screenshot.png' );
			$options['Screenshot'] = true;
		}

		// save settings to JSON
		$theme_prefix = get_theme_prefix( $options['Folder'] );
		if( $this->mode == 'new' )
			$options['ThemeID'] = create_theme_ID();
		else
			$options['ThemeID'] = empty($theme_prefix)? create_theme_ID() : $theme_prefix;
		$this->save_settings( $options['Folder'], $options );

		if ( $new_theme ) {
			// Add functions.php
			$functions = '';
			if ( $this->themes_path . '/' . $options['Template'] . '/functions.php' ) {
				$functions = '<?php /* child theme functions */ ?>';
			}
			$wp_filesystem->put_contents($this->themes_path . '/' . $options['Folder'] . '/functions.php', $functions, FS_CHMOD_FILE);

			// save settings into wordpress style.css
			$wp_filesystem->put_contents($this->themes_path . '/' . $options['Folder'] . '/style.css', $this->build_theme_css( $options ), FS_CHMOD_FILE);
		}
		else {
			$matches = array();
			$css = $wp_filesystem->get_contents($this->themes_path . '/' . $options['Folder'] . '/style.css');

			if(preg_match('/^\s*\/\*\*!/i', $css))
				$is_sass = true;
			else
				$is_sass = false;
			$css = preg_replace( '/\/\*\*?!?([^\*]*)\*?\*\//i', '', $css );
			$new_css = $this->build_theme_css( $options, false, $is_sass ).$css;

			// save settings into wordpress style.css
			$wp_filesystem->put_contents($this->themes_path . '/' . $options['Folder'] . '/style.css', $new_css, FS_CHMOD_FILE);
		}

		// return settings to enable activate theme popup
		return $options;
	}

	// if disabled history each time before create new
	// packages will be deleted previous created
	function clear_old_packages( $dir = null ) {

		if ( !$dir ) return false;

		// load theme settings
		$settings = $this->load_settings( $dir );

		// check if history enabled
		if ( !$settings['History'] ) {
			// remove download folder (if already exists)
			if ( file_exists( "{$this->themes_path}/$dir/download" ) )
				$this->rrmdir( "{$this->themes_path}/$dir/download" );
			// male new download dir
			mkdir( "{$this->themes_path}/$dir/download" );
		}
	}



	// function-template for chuild theme css
	function build_theme_css( $options = null, $alone = false, $is_sass = false ) {
		do_action( 'before_build_theme_css', $options );
		if ( !$options ) return false;

		$lines = array();
		extract( $options );

		$lines[] = $is_sass? "/**!\n" : "/*\n";

		if ( !empty( $Tags ) && is_array( $Tags ) ) {
			$Tags = implode( ',', $Tags );
			if ( $Tags == ',' ) $Tags = '';
		}

		if ( isset( $Name ) )
			$lines[] = "Theme Name: {$Name}\n";
		// if ( isset( $Icon ) )
			// $lines[] = "Icon: {$Icon}\n";
		if ( isset( $URI ) )
			$lines[] = "Theme URI: {$URI}\n";
		if ( isset( $Description ) )
			$lines[] = "Description: {$Description}\n";
		if ( isset( $AuthorName ) )
			$lines[] = "Author: {$AuthorName}\n";
		if ( isset( $AuthorURI ) )
			$lines[] = "Author URI: {$AuthorURI}\n";

		if ( !$alone ) {
			if ( !isset( $Template ) || $Template != false )
				$lines[] = "Template: runway-framework\n";
		}

		if ( isset( $Version ) )
			$lines[] = "Version: {$Version}\n";
		if ( isset( $Tags ) && $Tags != "")
			$lines[] = "Tags: {$Tags}\n";
		if ( isset( $License ) && $License != "")
			$lines[] = "License: {$License}\n";
		if ( isset( $LicenseURI ) && $LicenseURI != "")
			$lines[] = "License URI: {$LicenseURI}\n";
		if ( isset( $Comments ) )
			$lines[] = "{$Comments}\n";

		$lines[] = $is_sass? '**/' : '*/';
		$string = '';

		foreach ( $lines as $line ) {
			$string .= $line;
		}
		do_action( 'after_build_theme_css', $string );
		return $string;
	}

	// recursive copy
	function copy_r( $path, $dest, $exlude = array() ) {
		if ( is_dir( $path ) ) {
			@mkdir( $dest );
			$objects = runway_scandir( $path );
			if ( sizeof( $objects ) > 0 ) {
				foreach ( $objects as $file ) {
					// go on
					if ( is_dir( $path.DS.$file ) ) {
						if ( !in_array( $file, $exlude ) )
							$this->copy_r( $path.DS.$file, $dest.DS.$file );
					} else {
						copy( $path.DS.$file, $dest.DS.$file );
					}
				}
			}
			return true;
		} elseif ( is_file( $path ) ) {
			return copy( $path, $dest );
		} else {
			return false;
		}
	}

	// recursive delete
	function rrmdir( $dir ) {
		foreach ( glob( $dir . '/*' ) as $file ) {
			if ( is_dir( $file ) ) $this->rrmdir( $file );
			else unlink( $file );
		}

		@rmdir( $dir );
	}

	// delete child theme
	function delete_child_theme( $theme_name = null ) {

		do_action( 'before_delete_child_theme', $theme_name );

		if ( !$theme_name ) return false;

		$theme = $this->load_settings( $theme_name );

		$theme['Template'] = ( isset( $theme['Template'] ) ) ? $theme['Template'] : 'runway-framework';
		if ( $theme['Template'] != 'runway-framework' ) return false;

		$dir = $this->themes_path . '/' . $theme_name;

		if ( is_dir( $dir ) ) {
			$objects = runway_scandir( $dir );
			foreach ( $objects as $object ) {
				if ( filetype( $dir.'/'.$object ) == 'dir' ) $this->rrmdir( $dir.'/'.$object ); else unlink( $dir.'/'.$object );
			}
			reset( $objects );
			rmdir( $dir );
		}
		do_action( 'after_delete_child_theme', $theme_name );
	}

	/**
	 * Recursive adding files in zip archive
	 *
	 * @param unknown $path
	 * @param unknown $path_in_zip
	 * @param ZipArchive $zip
	 */
	function add_to_zip_r( $path, $path_in_zip, $zip, $exclude = array() ) {
		if ( !file_exists( $path ) ) return;
		if(!function_exists('WP_Filesystem'))
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;

		$files = runway_scandir( $path );
		foreach ( $files as $file ) {
			if ( !in_array( $file, $exclude ) ) {
				if ( is_dir( $path.'/'.$file ) ) {
					$zip->addEmptyDir( $path_in_zip.$file );
					$this->add_to_zip_r( $path.'/'.$file, $path_in_zip.$file.'/', $zip );
				}
				elseif ( is_file( $path.'/'.$file ) ) {
					$zip->addFromString( $path_in_zip.$file, $wp_filesystem->get_contents( $path.'/'.$file ) );
				}
			}
		}
	}

	function add_to_tmp_dir_r( $path, $path_in_zip, $exclude = array() ) {
		if ( !file_exists( $path ) ) return;
		if(!function_exists('WP_Filesystem'))
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;

		$files = runway_scandir( $path );
		foreach ( $files as $file ) {
			if ( !in_array( $file, $exclude ) ) {
				if ( is_dir( $path.'/'.$file ) ) {
					$wp_filesystem->mkdir( $path_in_zip.$file );
					$this->add_to_tmp_dir_r( $path . '/' . $file, $path_in_zip . $file . '/', $exclude );
				}
				elseif ( is_file( $path.'/'.$file ) ) {
					$wp_filesystem->copy( $path.'/'.$file, $path_in_zip.$file );
				}
			}
		}
	}

	/**
	 * build_child_package - make child themes packages
	 *
	 * @param mixed   $theme_name Theme name.
	 * @param mixed   $ts         Time stamp to make unique download archive name.
	 *
	 * @access public
	 *
	 * @return mixed Value.
	 */
	function build_child_package( $theme_name = null, $ts = null ) {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		WP_Filesystem();
		global $wp_filesystem;

		do_action( 'before_build_child_package' );

		if ( ! $theme_name || ! $ts ) {
			return false;
		}

		if ( ! is_writable( $this->themes_path . '/' . $theme_name ) ) {
			wp_die( sprintf( __( 'Please set write permissions for %s and then refresh page', 'runway' ), $this->themes_path ) );
		}

		$packages_storage_path = "$this->themes_path/{$theme_name}/download";

		if ( ! file_exists( $packages_storage_path ) ) {
			mkdir( $packages_storage_path );
		}

		$zip_file_name = "{$theme_name}-({$ts}).c.zip";
		$source        = "$this->themes_path/{$theme_name}";
		$source        = str_replace( '\\', '/', realpath( $source ) );

		if ( class_exists( 'ZipArchive' ) ) {

			$zip = new ZipArchive();

			$zip->open( $packages_storage_path . '/' . $zip_file_name, ZIPARCHIVE::CREATE );

			if ( is_dir( $source ) === true ) {
				$files = runway_scandir( $source );
				foreach ( $files as $file ) {
					$file = $source . '/' . $file;

					if ( is_dir( $file ) === true ) {
						$zip->addEmptyDir( str_replace( $source . '/', "{$theme_name}/", $file . '/' ) );
						$arr = explode( '/', $file );
						if ( array_pop( $arr ) == 'assets' ) {
							$this->add_to_zip_r( $file, $theme_name . '/assets/', $zip );
						}
						if ( array_pop( $arr ) == 'data' ) {
							$this->add_to_zip_r( $file, $theme_name . '/data/', $zip );
						}
					} else if ( is_file( $file ) === true ) {
						$zip->addFromString( str_replace( $source . '/', "{$theme_name}/", $file ), $wp_filesystem->get_contents( $file ) );
					}
				}
			} else if ( is_file( $source ) === true ) {
				$zip->addFromString( basename( $source ), $wp_filesystem->get_contents( $source ) );
			}

			$zip->close();

		} else {

			$temp_dir = get_temp_dir();
			if ( ! is_writable( $temp_dir ) ) {
				wp_die( sprintf( __( 'Please set write permissions for %s and then refresh page', 'runway' ), $temp_dir ) );
			}

			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

			$temp_dir = trailingslashit( $temp_dir . 'themes/' . time() );
			$wp_filesystem->mkdir( get_temp_dir() . 'themes' );
			$wp_filesystem->mkdir( $temp_dir );

			if ( is_dir( $source ) === true ) {
				$temp_theme_dir = $temp_dir . $theme_name;
				$wp_filesystem->mkdir( $temp_theme_dir );

				$files = runway_scandir( $source );
				foreach ( $files as $file ) {
					$file = $source . '/' . $file;

					if ( is_dir( $file ) === true ) {
						$wp_filesystem->mkdir( str_replace( $source . '/', "{$temp_theme_dir}/", $file . '/' ) );
						$arr = explode( '/', $file );
						if ( array_pop( $arr ) == 'assets' ) {
							$this->add_to_tmp_dir_r( $file, $temp_theme_dir . '/assets/' );
						}
						if ( array_pop( $arr ) == 'data' ) {
							$this->add_to_tmp_dir_r( $file, $temp_theme_dir . '/data/' );
						}
					} else if ( is_file( $file ) === true ) {
						$wp_filesystem->copy( $file, $temp_theme_dir . '/' . basename( $file ) );
					}
				}
			} else if ( is_file( $source ) === true ) {
				$wp_filesystem->copy( $source, $temp_dir . '/' . basename( $source ) );
			}

			// make an archive
			$zip = new PclZip( $packages_storage_path . '/' . $zip_file_name );
			$zip->create( array( $temp_dir ), '', $temp_dir );

			$wp_filesystem->rmdir( $temp_dir, true );
		}

		do_action( 'after_build_child_package', $theme_name, home_url() . "/wp-content/themes/{$theme_name}/download/child/{$zip_file_name}" );

		return home_url() . "/wp-content/themes/{$theme_name}/download/child/{$zip_file_name}";
	}


	/**
	 * build_alone_theme - make alone theme package
	 *
	 * @param mixed   $theme_name Theme name.
	 * @param mixed   $ts         Time stamp to make unique download archive name.
	 *
	 * @access public
	 *
	 * @return mixed Value.
	 */
	function build_alone_theme( $theme_name = null, $ts = null ) {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		WP_Filesystem();
		global $wp_filesystem;
		global $extm;

		do_action( 'before_build_alone_theme', $theme_name );

		if ( ! $theme_name || ! $ts ) {
			return false;
		}

		if ( ! is_writable( $this->themes_path . '/' . $theme_name ) ) {
			wp_die( sprintf( __( 'Please set write permissions for %s and then refresh page', 'runway' ), $this->themes_path . '/' . $theme_name ) );
		}

		$packages_storage_path = "$this->themes_path/{$theme_name}/download";

		if ( ! file_exists( $packages_storage_path ) ) {
			mkdir( $packages_storage_path );
		}

		$zip_file_name     = "{$theme_name}-({$ts}).a.zip";
		$source            = "$this->themes_path/runway-framework";
		$source            = str_replace( '\\', '/', realpath( $source ) );
		$framework_dir     = FRAMEWORK_DIR . 'framework/';
		$framework_exclude = array( 'themes', 'includes' );
		$includes_dir      = FRAMEWORK_DIR . 'framework/includes/';
		$includes_exclude  = array(
			'report-manager',
			'download-directory',
			'dashboard',
			'pointers',
			'auth-manager',
			'options-builder',
			'theme-updater'
		);
		$data_types_dir    = FRAMEWORK_DIR . 'data-types/';
		$extensions_list   = $extm->get_active_extensions_list( $theme_name );

		// merge functions.php
		$functions = ( file_exists( $source . '/functions.php' ) ) ? $wp_filesystem->get_contents( $source . '/functions.php' ) : '';
		if ( file_exists( "{$this->themes_path}/{$theme_name}/functions.php" ) ) {
			$functions .= $wp_filesystem->get_contents( "{$this->themes_path}/{$theme_name}/functions.php" );
		}

		// build plugin header
		$theme_data         = rw_get_theme_data( get_theme_root() . '/' . $theme_name );
		$theme_data['Tags'] = implode( ' ', $theme_data['Tags'] );
		// $theme_data['Icon'] = rw_get_custom_theme_data('Icon', get_theme_root().'/'.$theme_name);
		$theme_data['License']    = rw_get_custom_theme_data( 'License', get_theme_root() . '/' . $theme_name );
		$theme_data['LicenseURI'] = rw_get_custom_theme_data( 'License URI', get_theme_root() . '/' . $theme_name );
		$css                      = $this->build_theme_css( $theme_data, true );

		// merge style.css
		$css_ext = ( file_exists( "{$this->themes_path}/{$theme_name}/style.css" ) ) ? $wp_filesystem->get_contents( "{$this->themes_path}/{$theme_name}/style.css" ) : '';
		$css_ext = $this->remove_plugin_header( $css_ext, $theme_data['Name'] );
		$css_ext = $css . $css_ext;

		if ( false && class_exists( 'ZipArchive' ) ) {

			$zip = new ZipArchive();

			$zip->open( $packages_storage_path . '/' . $zip_file_name, ZIPARCHIVE::CREATE );

			// Copy framework and data types folder
			$zip->addEmptyDir( $theme_name . '/framework/' );
			$this->add_to_zip_r( $framework_dir, $theme_name . '/framework/', $zip, $framework_exclude );
			$zip->addEmptyDir( $theme_name . '/framework/includes/' );
			$this->add_to_zip_r( $includes_dir, $theme_name . '/framework/includes/', $zip, $includes_exclude );
			$zip->addEmptyDir( $theme_name . '/data-types/' );
			$this->add_to_zip_r( $data_types_dir, $theme_name . '/data-types/', $zip );

			// Add active extensions in package
			$zip->addEmptyDir( $theme_name . '/extensions/' );

			foreach ( $extensions_list as $ext ) {
				if ( is_string( $ext ) ) {
					$ext_dir = explode( '/', $ext );
					$file    = $source . '/extensions/' . $ext_dir[0] . '/';
					$this->add_to_zip_r( $file, $theme_name . '/extensions/' . $ext_dir[0] . '/', $zip );
				}
			}

			$zip->addFromString( $theme_name . '/functions.php', $functions );

			$zip->addFromString( $theme_name . '/style.css', $css_ext );

			// copy child theme files
			$this->add_to_zip_r( get_theme_root() . '/' . $theme_name, $theme_name . '/', $zip, array(
				'download',
				'functions.php',
				'style.css'
			) );

			$zip->close();

		} else {

			$temp_dir = get_temp_dir();
			if ( ! is_writable( $temp_dir ) ) {
				wp_die( sprintf( __( 'Please set write permissions for %s and then refresh page', 'runway' ), $temp_dir ) );
			}

			require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

			$temp_dir = trailingslashit( $temp_dir . 'themes/' . time() );
			$wp_filesystem->mkdir( get_temp_dir() . 'themes' );
			$wp_filesystem->mkdir( $temp_dir );
			$temp_theme_dir = $temp_dir . $theme_name;
			$wp_filesystem->mkdir( $temp_theme_dir );

			// copy framework
			$wp_filesystem->mkdir( $temp_theme_dir . '/framework/' );
			$this->add_to_tmp_dir_r( $framework_dir, $temp_theme_dir . '/framework/', $framework_exclude );

			// copy includes
			$wp_filesystem->mkdir( $temp_theme_dir . '/framework/includes/' );
			$this->add_to_tmp_dir_r( $includes_dir, $temp_theme_dir . '/framework/includes/', $includes_exclude );

			// copy data-types
			$wp_filesystem->mkdir( $temp_theme_dir . '/data-types/' );
			$this->add_to_tmp_dir_r( $data_types_dir, $temp_theme_dir . '/data-types/' );

			// copy extensions
			$wp_filesystem->mkdir( $temp_theme_dir . '/extensions/' );
			foreach ( $extensions_list as $ext ) {
				if ( is_string( $ext ) ) {
					$ext_dir = explode( '/', $ext );
					$file    = $source . '/extensions/' . $ext_dir[0] . '/';
					if ( ! file_exists( $file ) ) {
						continue;
					}
					$wp_filesystem->mkdir( $temp_theme_dir . '/extensions/' . $ext_dir[0] );
					$this->add_to_tmp_dir_r( $file, $temp_theme_dir . '/extensions/' . $ext_dir[0] . '/' );
				}
			}

			// copy functions.php
			$wp_filesystem->put_contents( $temp_theme_dir . '/functions.php', $functions );

			// copy syles.css
			$wp_filesystem->put_contents( $temp_theme_dir . '/style.css', $css_ext );

			// copy child theme files
			$this->add_to_tmp_dir_r( get_theme_root() . '/' . $theme_name, $temp_theme_dir . '/', array(
				'download',
				'functions.php',
				'style.css'
			) );

			// make an archive
			$zip = new PclZip( $packages_storage_path . '/' . $zip_file_name );
			$zip->create( array( $temp_dir ), '', $temp_dir );

			$wp_filesystem->rmdir( $temp_dir, true );
		}

		do_action( 'after_build_alone_theme', $theme_name, home_url() . "/wp-content/themes/{$theme_name}/download/child/{$zip_file_name}" );

		return home_url() . "/wp-content/themes/{$theme_name}/download/child/{$zip_file_name}";
	}

	// remove plugin header in merged css file
	function remove_plugin_header( $css_ext = null, $theme_name = null ) {

		$start = 0;
		do {
			$pos = strpos( $css_ext, 'Theme Name: '.$theme_name, $start );
			$pos_begin = strpos( $css_ext, '/*', $start );
			$pos_end = strpos( $css_ext, '*/', $start );
			if ( $pos > $pos_begin && $pos < $pos_end ) {
				$css_ext = substr_replace( $css_ext, '', $pos_begin, $pos_end - $pos_begin + 2 );
			}
			$start = $pos_begin;
		} while ( $pos !== false );

		return $css_ext;
	}

	// build package info from TS (timestamp)
	function make_package_info_from_ts( $theme_name = null, $ts = null ) {
		if ( !$theme_name || !$ts ) return false;

		return array(
			'exp' => $ts,
			'date' => date( 'F j, Y', $ts ),
			'time' => date( 'g:i a', $ts ),
			'c_file' => file_exists( "{$this->themes_path}/{$theme_name}/download/{$theme_name}-({$ts}).c.zip" ) ? "{$theme_name}-({$ts}).c.zip" : '',
			'a_file' => file_exists( "{$this->themes_path}/{$theme_name}/download/{$theme_name}-({$ts}).a.zip" ) ? "{$theme_name}-({$ts}).a.zip" : '',
			'c_hash' => file_exists( "{$this->themes_path}/{$theme_name}/download/{$theme_name}-({$ts}).c.zip" ) ? md5_file( "{$this->themes_path}/{$theme_name}/download/{$theme_name}-({$ts}).c.zip" ) : '',
			'a_hash' => file_exists( "{$this->themes_path}/{$theme_name}/download/{$theme_name}-({$ts}).a.zip" ) ? md5_file( "{$this->themes_path}/{$theme_name}/download/{$theme_name}-({$ts}).a.zip" ) : '',
		);

	}

	// search previous created packages
	function get_history( $theme_name = null ) {
		if ( !$theme_name ) return false;

		$history = array();

		if ( file_exists( $this->themes_path . "/{$theme_name}/download" ) )
			$packages_dir = opendir( $this->themes_path . "/{$theme_name}/download" );

		if ( isset( $packages_dir ) && $packages_dir ) {
			while ( $file = readdir( $packages_dir ) ) {
				if ( $file != '.' && $file != '..' ) {
					if ( preg_match( '/.zip/', $file ) ) {
						preg_match( '/\((\d+)\)/', $file, $matches );
						if ( count( $matches > 0 ) ) {
							$ts = $matches[0];
							$ts = str_replace( '(', '', $ts );
							$ts = str_replace( ')', '', $ts );
						}
						else {
							continue;
						}
						$history[$ts] = $this->make_package_info_from_ts( $theme_name, $ts );
					}
				}
			}

			// Sort array (newest to oldest)
			krsort( $history );

			// remove packages if their count exceeds 10
			$to_del = array_slice( $history, 10 );
			foreach ( $to_del as $ts => $info ) {
				unset( $history[$ts] );
				if ( !empty($info['c_file']) && file_exists( $this->themes_path . "/{$theme_name}/download/".$info['c_file'] ) )
					unlink( $this->themes_path . "/{$theme_name}/download/".$info['c_file'] );
				if ( !empty($info['a_file']) && file_exists( $this->themes_path . "/{$theme_name}/download/".$info['a_file'] ) )
					unlink( $this->themes_path . "/{$theme_name}/download/".$info['a_file'] );
			}
		}

		return $history;
	}

	// URL for theme screenshot
	function screenshot_url( $theme_folder = null ) {
		if ( !$theme_folder ) return false;

		$path = "{$this->themes_path}/{$theme_folder}/screenshot.png";

		if ( !file_exists( $path ) ) {
			copy( "{$this->themes_path}/runway-framework/screenshot.png", $path );
		}

		return home_url() . "/wp-content/themes/{$theme_folder}/screenshot.png";

	}

	function rename_history_packages( $theme_folder = null ) {

		$packages_storage_path = "$this->themes_path/{$theme_folder}/download";
		if ( file_exists( $packages_storage_path ) ) {
			$download_dir = opendir( $packages_storage_path );
			while ( $file = readdir( $download_dir ) ) {
				if ( $file != '.' && $file != '..' ) {
					$pos = strpos( $file, '-(' );
					if ( $pos > 0 ) {
						$old_theme = substr( $file, 0, $pos );
						$new_file = str_replace( $old_theme, $theme_folder, $file );
						rename( $packages_storage_path.'/'.$file, $packages_storage_path.'/'.$new_file );
					}
				}
			}
		}


	}

	function get_other_runway_themes() {
		global $directory, $theme_updater_admin, $auth_manager_admin;

		$postdata = array(
			'extensions' => '',
			'type' => 'Themes',
			'runway_token' => (isset($auth_manager_admin->token)) ? $auth_manager_admin->token : '',
		);

		$post_args = array(
			'method' => 'POST',
			'headers'=> array('Accept' => 'text/html'),
			'timeout' => 10,
			'body' => $postdata
		);

		$response = wp_remote_post($theme_updater_admin->url_update_exts.'/wp-admin/admin-ajax.php?action=sync_downloads', $post_args);

		return json_decode($response['body']);
	}

}

function runway_admin_themes_list_prepare( $theme ) {

	// Set the variables
	// --------------------------------------------

	// Name
	$t['name'] = ( isset( $theme['Name'] ) ) ? $theme['Name'] : '['. __( 'No theme name', 'runway' ) .']';
	// Description
	$t['description'] = ( isset( $theme['Description'] ) ) ? '<p class="theme-description">'. $theme['Description'] .'<p>' : false;
	// Author
	if ( isset( $theme['Author'] ) ) {
		$t['author'] = $theme['Author'];
		if ( isset( $theme['AuthorURI'] ) ) {
			$t['author'] = '<a href="'. $theme['AuthorURI'] .'">'. $t['author'] .'</a>';
		}
		$t['author'] = '<li>'. sprintf( __( 'By %s', 'runway' ), $t['author'] ) .'</li>';
	} else {
		$t['author'] = '';
	}

	// Version
	$t['version'] = ( isset( $theme['Version'] ) ) ? '<li><strong>'. __( 'Version', 'runway' ) .'</strong> '. $theme['Version'] : false;
	// Folder
	$t['folder'] = ( isset( $theme['Folder'] ) ) ? $theme['Folder'] : false;
	// Image
	$t['image'] = ( isset( $t['folder'] ) ) ? home_url() . '/wp-content/themes/' . $t['folder'] . '/screenshot.png' : false;

	// URLs
	// --------------------------------------------

	// Activate URL
	$t['activateURL'] = wp_nonce_url( 'themes.php?action=activate&amp;template='.urlencode( $t['folder'] ).'&amp;stylesheet='.urlencode( $t['folder'] ), 'switch-theme_' . $t['folder'] );
	// Preview URL
	$t['previewURL'] = home_url();
	if ( is_ssl() ) $t['previewURL'] = str_replace( 'http://', 'https://', $t['previewURL'] );
	$t['previewURL'] = esc_url(htmlspecialchars(
		add_query_arg(
			array(
				'preview' => 1,
				'template' => strtolower( urlencode( $theme['Template'] ) ),
				'stylesheet' => strtolower( urlencode( $t['folder'] ) ),
				'preview_iframe' => false,
				'TB_iframe' => 'false'
			),
			$t['previewURL']
		)
	));
	// Edit URL
	$t['editURL'] = esc_url('admin.php?page=themes&navigation=edit-theme&name='. $t['folder']);
	// Delete URL
	$t['deleteURL'] = esc_url('admin.php?page=themes&navigation=delete-theme&name='. $t['folder']);
	// Download URL
	$t['downloadURL'] = esc_url('admin.php?page=themes&navigation=do-package&name='. $t['folder']);
	// History URL
	$t['historyURL'] = esc_url('admin.php?page=themes&navigation=do-download&name='. $t['folder']);

	// Links
	// --------------------------------------------

	// Activate Link
	$t['activateLink'] = '<a class="activate-theme" href="'. $t['activateURL'] .'">'. __( 'Activate', 'runway' ) .'</a>';
	// Preview Link
	$t['previewLink'] = '<a target="_blank" href="'. $t['previewURL'] .'">'. __( 'Preview', 'runway' ) .'</a>';
	// Edit Link
	$t['editLink'] = '<a href="'. $t['editURL'] .'">'. __( 'Edit', 'runway' ) .'</a>';
	// Duplicate link
	$t['duplicateLink'] = '<a class="duplicate-theme" data-theme-folder="'. esc_attr( $t['folder'] ) .'" data-theme-name="'. esc_attr( $t['name'] ) .'" href="javascript: void(0);">'. wp_kses_post( __( 'Duplicate', 'runway' ) ) .'</a>';
	// Delete link
	$t['deleteLink'] = '<a href="'. $t['deleteURL'] .'" class="submitdelete deletion">'. __( 'Delete', 'runway' ) .'</a>';
	// Download / History
	$t['downloadLink'] = '<a class="get-package" href="'. $t['downloadURL'] .'">'. __( 'Packages &amp; Downloads', 'runway' ) .'</a>';
	if ( isset( $theme['History'] ) && $theme['History'] ) {
		$t['downloadLink'] .= ' | <a class="get-download" href="'. $t['historyURL'] .'">'. __( 'History', 'runway' ) .'</a>';
	}
	$t['downloadLink'] = '<span>'. $t['downloadLink'] .'</span>';


	// Combined Secions of Theme Blocks
	// --------------------------------------------

	// Theme Image / Screenshot
	$screenshotLink = ( strtolower( $t['name'] ) != 'runway' ) ? $t['editURL'] : '#' ;
	$t['screenshot'] = '<a href="'. $screenshotLink .'" class="screenshot"><img src="'. $t['image'] .'" alt=""></a>';
	// Theme Info Block
	$t['themeInfo'] = '<div><ul class="theme-info">'. $t['author'] . $t['version'] .'</ul>'. $t['description'] .'</div><p class="theme-options">'. __( 'Folder location:', 'runway' ) .'<code>/themes/'. $t['folder'] .'</code></p>';

	return $t;

}
?>
