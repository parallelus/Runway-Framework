<?php

/**
 * Based on the work of Henrik Melin and Kal Ström's "More Fields", "More Types" and "More Taxonomies" plugins.
 * http://more-plugins.se/
 */

$runway_framework_admin = 'RUNWAY_ADMIN_FRAMEWORK';
if ( !defined( $runway_framework_admin ) ) {
	class Runway_Admin_Object {

		var $name, $slug, $settings_file, $dir, $options_url, $option_key, $data, $url, $footed, $action, $navigation, $message, $error, $js, $css,
		$builder_page, $dynamic, $keys;

		function __construct( $settings ) {

			if ( isset( $settings['js'] ) ) {
				$this->js = $settings['js'];
			}

			if ( isset( $settings['css'] ) ) {
				$this->css = $settings['css'];
			}

			if ( !isset( $this->action ) && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'save' ) {
				$this->action = 'save';
			}

			if ( isset( $settings['builder_page'] ) )
				$this->builder_page = $settings['builder_page'];

			$this->admin_layout = 'default';
			if ( isset( $settings['wp_containers'] ) ) {
				$this->admin_layout = $settings['wp_containers'];
			}

			//set extension or page name
			$this->name = $settings['name'];

			// if dynamic page set slug as alias else create slug from title
			$this->slug = isset( $settings['alias'] ) ? sanitize_title( $settings['alias'] ) : sanitize_title( $settings['name'] );

			$this->pref = '';

			if(isset($this->builder_page)) {						
				$this->pref = $this->get_menu_adapt($this->builder_page->settings->adminMenuTopItem);								
			} else {
				$this->pref = $this->pref . strtolower(THEME_NAME);
			}

			$this->page_hook = $this->pref . "_page_" . $this->slug;			
			
			// set fields (for static reated page objects)
			$this->fields = isset($settings['fields']) ? $settings['fields'] : array( 'var' => array(),'array' => array() );

			// maybe deprecated
			$this->settings_file = isset( $settings['settings_file'] ) ? $settings['settings_file'] : 'admin.php';

			// set extension dir
			$this->dir = plugin_dir_path( $settings['file'] );

			// broken or deprecated
			$this->url = plugin_dir_url( $settings['file'] );

			// set access permissions
			$this->menu_permissions = isset( $settings['menu_permissions'] ) ? $settings['menu_permissions'] : 'manage_options';

			// set admin menu parent item
			if(!isset($this->parent_menu)) {
				$this->parent_menu = isset( $settings['parent_menu'] ) ? $settings['parent_menu'] : 'settings';							
			}

			// set parent menu url (slug)
			$this->menu_url = $this->get_admin_menu( $this->parent_menu );
					
			// set parent menu url
			$this->options_url = 
				strpos($this->parent_menu, '.php') === false && $this->parent_menu == $this->get_admin_menu( $this->parent_menu ) ? 
					'admin.php?page=' . $this->slug : 
					$this->menu_url . '?page=' . $this->slug;

			// ???
			$this->settings_url = $this->options_url;

			// set database option key
			$this->option_key = $settings['option_key'];

			// set default values
			$this->default = isset($settings['default']) ? $settings['default'] : array();

			// set default keys
			$this->default_keys = ( $a = $this->default ) ? $a : array();

			// Create Settings Menu
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_head', array( &$this, 'admin_head' ) );

			// fix parent slugs for dynamic created pages
			if ( isset( $settings['dynamic'] ) ) {
				$this->parent_slug = 'admin';
				$this->dynamic = true;
			} else {
				$this->parent_slug = $this->get_menu_adapt( $this->parent_menu );
			}

			// Handle requests
			add_action( $this->page_hook, array( &$this, 'settings_init' ) );
			add_action( $this->page_hook, array( &$this, 'request_handler' ) );

			// Add JS & css on settings page
			//add_action('admin_head-' . $this->parent_menu . '_page_' . $this->slug, array(&$this, 'settings_head'));
			//-!!!!--- ABOVE: Moved to admin_menu() function because it's just easier to call ---!!!!-\\

			// COMMENTED OUT BECAUSE OF ERROR MESSAGE IN WP PLUGINS ADMIN // 
			// add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );

			add_action( 'init', array( &$this, 'admin_init' ), 11 );

			$this->add_actions();

			$this->add_key = '57UPhPh'; // I'm not sure we need this any longer. We should look closely where it's applied and determine the importance.

			// get database data
			$database_data = get_option( $this->option_key );

			if ( empty( $database_data ) ) {
				$this->has_save_data = false;
			} else {
				$this->has_save_data = true;
			}
		}

		// experimental
		function view( $view, $return_as_string = false, $values = null) {

			if($values != null){
				extract($values);
			}

			$view_path = $this->dir . 'views/' . $view . ".php";
			if(file_exists($view_path)) {
				if(!$return_as_string) {
					include_once $view_path;	
				} else {
					ob_start();
					include_once $view_path;	
					$view_content = ob_get_contents();
					ob_end_clean();
					return $view_content;
				}
				
			}

		}
		// experimental

		// get admin menu slug by file_name
		function get_menu_adapt( $url ) {

			$slugs = array(
				'index.php'                => 'dashboard',
				'edit.php'                 => 'posts',
				'upload.php'               => 'media',
				'link-manager.php'         => 'links',
				'edit.php?post_type=page'  => 'pages',
				'edit-comments.php'        => 'comments',
				'themes.php'               => 'appearance',
				'plugins.php'              => 'plugins',
				'users.php'                => 'users',
				'tools.php'                => 'tools',
				'options-general.php'      => 'settings',
				'admin.php'				   => 'load',
			);

			if ( isset( $slugs[$url] ) )
				return $slugs[$url];
			else
				return strtolower(THEME_NAME);

		}

		// get admin menu file_name by slug
		function get_admin_menu( $parentMenu, $slug = null ) {

			$menu = strtolower( $parentMenu );
			$parent_slug = array(
				'dashboard'  => 'index.php',
				'posts'   => 'edit.php',
				'media'   => 'upload.php',
				'links'   => 'link-manager.php',
				'pages'   => 'edit.php?post_type=page',
				'comments'   => 'edit-comments.php',
				'appearance'  => 'themes.php',
				'plugins'   => 'plugins.php',
				'users'   => 'users.php',
				'tools'   => 'tools.php',
				'settings'   => 'options-general.php',
				'disabled'   => 'disabled', // special case for hiding admin menus
			);

			if ( isset( $parent_slug[$menu] ) ) {
				$menuURL = $parent_slug[$menu];				
			} else {
				$menuURL = $menu;
			}

			return  $menuURL;

		}

		function get_field_by_alias($alias = '') {

			if(!isset($this->cache)) {
				if(isset($this->builder_page)) {
					foreach ($this->builder_page->elements as $page_element) {
						if($page_element != 'none' && $page_element->template == 'field') {
							$this->cache[$page_element->alias] = $page_element;							
						}
					}					
				}
			}

			if(isset($this->cache[$alias])) {
				return $this->cache[$alias];
			} else {
				return false;
			}

		}

		function self_url( $navigation = '', $additional_params = array() ) {

			$url = $this->options_url;

			if ( $navigation ) {
				$additional_params['navigation'] = $navigation;
			}

			if ( count( $additional_params ) ) {
				$url .= '&' . http_build_query( $additional_params );
			}

			return $url;
		}

		function admin_init() {

			/* nothing */

		}

		function add_actions() {

			// This function was intentionally left blank

		}	

		// attach submenu items to admin menu
		function admin_menu() {

			global $menu_items_from_theme, $menu_items_sections, $menu_items_icons, $menu, $submenu;

			if ( $this->menu_url != 'disabled' ) {
				// Register menu
				if ( isset($this->dynamic) && $this->dynamic == true ) {
					$menu_items_from_theme[] = $this->options_url;
					if ( isset( $this->builder_page->settings->icon ) )
						if ( $this->builder_page->settings->icon == 'custom-icon' ) {
							$menu_items_icons[$this->options_url] = get_stylesheet_directory_uri() . '/data/icons/' . $this->builder_page->settings->icon_file;
						} else {
							$menu_items_icons[$this->options_url] = $this->builder_page->settings->icon;
						}
				}

				if($this->menu_url != 'hidden') {
					$this_page = add_submenu_page( $this->menu_url, $this->name, $this->name, $this->menu_permissions, $this->slug, array( &$this, 'options_page' ) );				
				} else {
					$this_page = add_submenu_page( 'admin.php', $this->name, $this->name, $this->menu_permissions, $this->slug, array( &$this, 'options_page' ) );				
				}

				add_action('runway_page_' . $this->slug, array( &$this, 'options_page' ));
				add_action( 'admin_head-' . $this_page, array( &$this, 'settings_head' ) );

			}

		}		

		/*
		**
		**
		*/
		function admin_head() {

			add_thickbox();

		}

		/**
		 * Includes css files to extension
		 */
		function include_extension_css() {

			if ( isset( $this->css ) && !empty( $this->css ) ) {
				foreach ( $this->css as $css ) {
					if ( preg_match( '|(.+)/(.+).css|', $css ) ) {
						$explodeCSS = explode( '/', $css );
						$css_name = array_pop( $explodeCSS );
						wp_enqueue_style( $css_name, $css );
					}
					else {
						wp_enqueue_style( $css );
					}
				}
			}

		}

		/**
		 * Including js files to extension
		 */
		function include_extension_js() {

			if ( isset( $this->js ) && !empty( $this->js ) ) {
				foreach ( $this->js as $js ) {
					if ( preg_match( '|(.+)/(.+).js|', $js ) ) {
						$explodeJS = explode( '/', $js );
						$js_name = array_pop( $explodeJS );
						wp_enqueue_script( $js_name, $js );
					}
					else {
						wp_enqueue_script( $js );
					}
				}
			}

		}

		/*
		**
		**
		*/
		function options_page() {

			// including js and cs
			$this->include_extension_css();
			$this->include_extension_js();

			$this->options_page_wrapper_header();

			// Errors trump notifications
			if ( $this->error )
				echo '<div class="updated fade error"><p><strong>' . $this->error . '</strong></p></div>';
			elseif ( $this->message )
				echo '<div class="updated fade"><p><strong>' . $this->message . '</strong></p></div>';

			// Load the settings file
			if ( !$this->footed ) {
				if ( $this->settings_file ) {
					require $this->dir . $this->settings_file;
				}
			}

			$this->options_page_wrapper_footer();

		}

		function html_encode_for_export( $data ) {

			if ( is_array( $data ) ) {
				$d = array();
				foreach ( $data as $k => $v ) {
					if ( is_array( $v ) ) {
						$d[$k] = $this->html_encode_for_export( $v );
					} else {
						// remove line breaks and excape html
						$d[$k] = esc_html( str_replace( array( '\r\n', '\n', '\r', '][' ), array( '', '', '', '] [' ), $v ) ); // "][" dirty fix, shortcodes need space between end of previous and start of next
					}
				}
				return $d;
			}
			return $data;
		}				

		function get_data( $s = array(), $override = false ) {

			if ( empty( $s ) && !$override ) $s = $this->keys;
			if ( count( $s ) == 0 ) return $this->data;
			if ( count( $s ) == 1 ) return $this->data[$s[0]];
			if ( count( $s ) == 2 ) return $this->data[$s[0]][$s[1]];
			if ( count( $s ) == 3 ) return $this->data[$s[0]][$s[1]][$s[2]];
			if ( count( $s ) == 4 ) return $this->data[$s[0]][$s[1]][$s[2]][$s[3]];
			if ( count( $s ) == 5 ) return $this->data[$s[0]][$s[1]][$s[2]][$s[3]][$s[4]];
			if ( count( $s ) == 6 ) return $this->data[$s[0]][$s[1]][$s[2]][$s[3]][$s[4]][$s[5]];
			return $this->data;

		}

		function set_data( $value, $s = array(), $override = false ) {

			if ( empty( $s ) && !$override ) $s = $this->keys;
			if ( count( $s ) == 0 ) $this->data = $value;
			if ( count( $s ) == 1 ) $this->data[$s[0]] = $value;
			if ( count( $s ) == 2 ) $this->data[$s[0]][$s[1]] = $value;
			if ( count( $s ) == 3 ) $this->data[$s[0]][$s[1]][$s[2]] = $value;
			if ( count( $s ) == 4 ) $this->data[$s[0]][$s[1]][$s[2]][$s[3]] = $value;
			if ( count( $s ) == 5 ) $this->data[$s[0]][$s[1]][$s[2]][$s[3]][$s[4]] = $value;
			if ( count( $s ) == 6 ) $this->data[$s[0]][$s[1]][$s[2]][$s[3]][$s[4]][$s[5]] = $value;

			return $this->data;

		}

		function unset_data( $s = array() ) {

			if ( empty( $s ) ) $s = $this->keys;
			$key = array_pop( $s );
			$arr = $this->get_data( $s, true );
			if ( $arr[$key] ) unset( $arr[$key] );
			$this->set_data( $arr, $s, true );
			return $this->data;

		}

		/*
		**	settings_init()
		**
		**	Extract variables that define what we're trying to do.
		*/
		function settings_init() {

			// Single vars
			$fs = array( 'action', 'navigation' );
			foreach ( $fs as $f ) $this->{$f} = (isset($_REQUEST[$f])) ? esc_attr( $_REQUEST[$f] ) : '';

			// Array vars
			$fs = array( 'keys', 'action_keys' );
			foreach ( $fs as $f ) {
				$a = (isset($_GET[$f])) ? esc_attr( $_GET[$f] ) : '';
				$argh = $this->extract_array( $a );
				$this->{$f} = $argh;
			}

			$this->after_settings_init();

			return true;

		}

		function after_settings_init() {

			/*
			** This function is intentionally left blank
			**
			** Overwritten by indiviudal plugin admin objects, if needed.
			*/

		}

		/*
		**
		**	Parse requests...
		*/
		function request_handler() {

			// Load up our data, internal and external
			$this->load_objects();

			// Ponce som en lugercheck!
			if ( isset($_GET['_wpnonce']) && $nonce = esc_attr( $_GET['_wpnonce'] ) )
				check_admin_referer( $this->nonce_action() );

			// Check whatever you want - validate_submission should return false if
			// things don't stack up.

			if ( !( $this->validate_sumbission() ) ) {
				if ( $this->action == 'save' ) {
					$keys = $this->keys;
					if ( !empty( $this->action_keys ) ) {
						$keys = $this->action_keys;
						$this->keys = $keys;
					}
					$this->set_data( $this->extract_submission(), $keys );
				}
				return false;
			}

			if ( $this->navigation == 'export' ) {
				return $this->export_data();
			}

			if ( $this->action == 'move' ) {
				// At what level are we moving?
				$action_keys = $this->extract_array( esc_attr( $_GET['action_keys'] ) );
				if ( empty( $action_keys ) ) array_push( $action_keys, '_framework' );
				$data = $this->get_data( $action_keys );

				if ( empty( $data ) )
					return $this->error( __( 'Someting has gone awry. Sorry.', THEME_NAME ) );

				// Which element is being moved?
				$row = esc_attr( $_GET['row'] );

				// Move a key
				$up = ( 'up' == esc_attr( $_GET['direction'] ) ) ? true : false;
				$data = $this->move_field( $data, $row, $up );

				// Save the data
				$this->set_data( $data, $action_keys );
				$this->save_data();
			}

			if ( $this->action == 'save' ) {
				$arr = $this->extract_submission();

				// The $_POST['index'] needs to be set externally, this is
				// last index of the data to be saved
				$index = $arr['index'];

				$keys  = isset($arr['originating_keys'])? $arr['originating_keys'] : array();
				$old_last_key = isset($arr['originating_keys'])? $keys[count( $keys ) - 1] : '';

				// We can only save to '_framework'
				if ( !isset($keys[0]) || $keys[0] != '_framework' ) {
					$arr['ancestor_key'] = (isset($keys[1])) ? $keys[1] : '';
					$keys[0] = '_framework';
				}

				// Is this not new stuff?
				if ( !$this->dynamic && $index != $this->add_key ) {
					// Ok, so it's not new, but has it changed?
					if ( $old_last_key != $index ) {
						// The old keys are now redundant
						$this->unset_data( $keys );
					}
				}

				if ( $keys[0] != '_framework' ) {
					array_pop( $keys );
				}

				array_push( $keys, $index );
				unset( $arr['originating_keys'] );

				// Set and save and provide feedback
				if ( count( $keys ) >= 1 ) {
					foreach ( $keys as $key => $value ) {
						if ( is_array( $value ) ) {
							$keys[$key] = $value[0];
						}
					}

					$this->set_data( $arr, $keys );
					$this->save_data();
					$this->message = __( 'Saved!', 'more_plugins' );
				}
			}

			if ( $this->action == 'delete' ) {
				$data = $this->unset_data( $this->action_keys );
				$this->save_data();
				$this->message = __( 'Deleted!', THEME_NAME );
			}

			if ( count( $this->keys ) && $this->action == 'add' ) {
				// Extract the last key
				$last = $this->keys[count( $this->keys ) - 1];

				// Are we trying to add stuff?
				if ( !$this->dynamic && $last == $this->add_key ) {
					$this->data = $this->set_data( $this->default, $this->keys );
				}
			}

			$this->after_request_handler();

		}

		function after_request_handler() {

			/*
			** This function is intentionally left blank
			**
			** Overwritten by indiviudal plugin admin objects, if needed - mostly
			** used for cross more-plugins functionality
			*/

		}

		function extract_submission() {
			// version with auto select submission type
			if ( !isset( $this->fields['var'] ) && !isset( $this->fields['array'] ) ) {
				array_push( $this->fields, 'originating_keys' );
				array_push( $this->fields, 'index' );
				array_push( $this->fields, 'ancestor_key' );
				array_push( $this->fields, 'version_key' );
				array_push( $this->fields, 'import_key' );

				$arr = array();

				foreach ( $this->fields as $key => $field ) {
					if ( is_array( $_POST[$field_key] ) ) {
						if ( is_string( $field ) ) {
							$v = esc_attr( $_POST[$field] );
							/*if ( $field == 'disable_wp_content' ) {
								$disable = array();
								foreach ( $_POST['disable_wp_content'] as $key => $val ) {
									foreach ( $val as $_val ) {
										$disable[$_val][] = $key;
									}
								}
								$v = serialize( $disable );
							}*/
							$arr[$field] = ( stripslashes( $v ) );
						} else {
							if ( is_array( $field ) ) {
								foreach ( $field as $key2 ) {
									$post = $this->extract_array( $_POST[$key . ',' . $key2] );
									$arr[$key][$key2] = ( stripslashes( $post[0] ) );
								}
							}
						}
					} else {
						if ( is_array( $field ) ) {
							$tmp_index = trim( preg_replace( '/[^\d]+/', '', $field ) );
							$separate = (array)$this->elements;

							$separate = ( isset( $separate[$tmp_index]->separate ) ) ?
								$separate[$tmp_index]->separate : '';

							$vals = ( $separate == 'none' ) ?
								$this->extract_array( $_POST[$field], false ):
								$this->extract_array( $_POST[$field] );

							foreach ( $vals as $k => $v ) {
								if ( !is_array( $v ) && !is_object( $v ) ) {
									$arr[$field][$k] = ( stripslashes( $v ) );
								} else {
									$arr[$field][$k] = $this->object_to_array( $v );
								}
							}
						} else {
							foreach ( $field as $level2 => $field2 ) {
								$post = $this->extract_array( $_POST[$level1 . ',' . $field2] );
								$arr[$level1][$field2] = ( stripslashes( $post[0] ) );
							}
						}
					}
				}

				return $arr;
			} else {
				if ( !is_array( $this->fields ) ) {
					$this->fields = array();
				}

				if ( !is_array( $this->fields['var'] ) )
					$this->fields['var'] = array();

				if ( !array_key_exists('array', $this->fields) || !is_array( $this->fields['array'] ) )
					$this->fields['array'] = array();

				// Add required params
				array_push( $this->fields['array'], 'originating_keys' );
				array_push( $this->fields['var'], 'index' );
				array_push( $this->fields['var'], 'ancestor_key' );
				array_push( $this->fields['var'], 'version_key' );
				array_push( $this->fields['var'], 'import_key' );

				// Ekkstrakkt
				$arr = array();

				foreach ( $this->fields['var'] as $key => $field ) {

					$field_object = $this->get_field_by_alias($field);

					if($field_object !== false) {
						$arr['field_types'][$field] = $field_object->type;	
					}

					if ( isset( $_POST[$field] ) ) {
						if ( is_string( $_POST[ $field ] ) ) {
							$arr[$field] = esc_attr( $_POST[$field] );
						} else {
							$arr[$field] = $_POST[$field];
						}
					} else {
						$arr[$field] = null;
					}
				}

				return $arr;
			}

			return array();

		}

		/*
		** 	Might be storing serialized data or might be a
		**	comma separated list
		*/
		function extract_array( $a, $separate = true ) {

			// *Might* be storing json data or *might* be a
			// comma separated list

			if ( is_array( $a ) ) return $a;

			if ( $a ) {
				// $a be a json object
				$b = json_decode( stripslashes_deep( $a ), true );
				if ( is_array( $b ) ) return $this->slasherize( $b, true );

				// Is this a comma separated list?
				if ( strpos( $a, ',' ) && $separate == true ) {
					return explode( ',', $a );
				}
				elseif ( $separate == false ) {
					return array( 0 => $a );
				}
				// $a is just a single value
				return array( $a );
			}

			// $a is empty
			return array();

		}

		/*
		**
		**
		*/
		function stripslashes_deep( $string ) {

			while ( strpos( $string, '\\' ) )
				$string = stripslashes( $string );
			return $string;

		}

		/*
		**
		**
		*/
		function object_to_array( $data ) {

			if ( is_array( $data ) || is_object( $data ) ) {
				$result = array();
				foreach ( $data as $key => $value ) $result[$key] = $this->object_to_array( $value );
				return $result;
			}
			return $data;

		}		

		/*
		**
		**
		*/
		function save_data( $data = array() ) {
			if ( empty( $data ) ) $data = $this->data['_framework'];
			if ( $this->dynamic && isset($data[$this->option_key]) ) $data = $data[$this->option_key];
			update_option( $this->option_key, $data );
		}

		/*
		**
		**	Overwrite this function in subclass to validate
		**	the submission data.
		*/
		function validate_sumbission() {
			// Somthing
			//return true;
			return false;
		}

		/*
		**
		**
		*/
		function error( $error ) {
			$this->error = $error;
			return false;
		}

		/*
		**
		**
		*/
		function set_navigation( $navigation ) {
			$_GET['navigation'] = $navigation;
			$_POST['navigation'] = $navigation;
			$this->navigation = $navigation;
			return $navigation;
		}

		/*
		**
		**
		*/
		function options_page_wrapper_header() {
			$adminPageTitle = '';
			$adminPageDesc = '';

			// Create the admin page structure
			// --------------------------------------------------------------------------------------------

			// Page title
			if ( !isset( $this->builder_page ) || $this->builder_page->settings->showPageTitle ) {
				$adminPageTitle = '<h2 class="adminTitle">'.apply_filters( 'framework_admin_title', $this->name ).'</h2>';
				$hasTitleClass = 'hasPageTitle';
			}

			// Page description
			if ( isset( $this->builder_page ) && $this->builder_page->settings->pageDescription ) {
				$description = '<p class="pageDescription">'.esc_html( $this->builder_page->settings->pageDescription ).'</p>';
				$adminPageDesc = apply_filters( 'framework_admin_description', $description );
			}

			// Container class
			$containerClass = $this->slug ;

			if ( $this->navigation ) {
				$containerClass .= ' '. $this->slug . '-' . $this->navigation;
			}

			if ( isset($hasTitleClass) && $hasTitleClass ) {
				$containerClass .= ' '. $hasTitleClass;
			}

?>
			<div class="wrap">
			<div id="theme-framework" class="<?php echo $containerClass; ?>">

				<div id="icon-options-general" class="icon32"><br /></div>

			<?php
			echo $adminPageTitle . PHP_EOL; // The title
			echo $adminPageDesc . PHP_EOL; // The description
?>

			<?php

			// Header tab navigation
			if ( isset($this->builder_page->sortOrder) && count( (array) $this->builder_page->sortOrder ) > 1 ) : ?>
				<h2 class="nav-tab-wrapper tab-controlls">
				<?php
				$is_first_tab = true;
				foreach ( $this->builder_page->sortOrder as $tab => $container ) {
					$active = ( $is_first_tab ) ? 'nav-tab-active' : '';
					echo '<a data-tabrel="#tabs-'. $tab .'" href="#tabs-'. $tab .'" class="nav-tab '. $active .'">'. $this->builder_page->elements->$tab->title .'</a>';
					$is_first_tab = false;
				} ?>

					</h2>	
				<?php 
			endif;

			// Post Body Containers - disable with the setting: 'wp_containers' => 'none'
			if ( isset($this->admin_layout) && $this->admin_layout != 'none' ) : ?>

				<div id="post-body">
					<div id="post-body-content">

				<?php
			endif;


			$this->headed = true;
			return;

			/*

			// This is what would have happened if we weren't skipping it above.
			// --------------------------------------------------------------------------------------------

			?>
				<div class="wrap">
				<div id="theme-framework" class="has-right-sidebar <?php echo $this->slug; ?> <?php echo $this->slug . '-' . $this->navigation; ?>">

					<div id="icon-options-general" class="icon32"><br /></div>
					<h2><?php echo $this->name; ?></h2>

					<div class="inner-sidebar metabox-holder">



						<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">

							<?php

							// Right Column Box ?>
							<div class="dpostbox theme-framework-box">
								<h3 class="hndle"><span><?php _e('Title', THEME_NAME); ?></span></h3>
								<div class="inside">
									<ul class="action-links">

										<li>
											<dl>
												<dt><a href="#"><?php _e('A Title', THEME_NAME); ?></a></dt>
												<dd><?php _e('Description', THEME_NAME); ?></dd>
											</dl>
										</li>

									</ul>
								</div>
							</div>


						</div> <!-- END id="side-sortables" class="meta-box-sortabless ui-sortable" -->
					</div> <!-- END class="inner-sidebar metabox-holder" -->

					<div id="post-body">
						<div id="post-body-content" class="has-sidebar-content">
					<?php
				$this->headed = true;

				*/

		}

		/*
		**
		**
		*/
		function options_page_wrapper_footer() {

			if ( $this->footed ) return false;
			
			if ( isset($this->admin_layout) && $this->admin_layout != 'none' ) : ?>
						</div> <!-- / #post-body-content -->
					</div> <!-- / #post-body -->
			<?php endif; ?>

				</div> <!-- / #theme-framework -->
			</div> <!-- / #wrap -->
			<?php
			$this->footed = true;

		}

		/*
		**
		**
		*/
		function condition( $condition, $message, $type = 'error' ) {

			if ( !isset( $this->is_ok ) ) $this->is_ok = true;

			// If there is an error already return
			if ( !$this->is_ok && $type = 'error' ) return $this->is_ok;

			if ( $condition == false && $type != 'silent' ) {
				echo '<div class="updated fade"><p>' . $message . '</p></div>';

				// Don't set the error flag if this is a warning.
				if ( $type == 'error' ) $this->is_ok = false;
			}

			return $condition == true;

		}

		/*
		**
		**
		*/
		function checkboxes( $name, $title, $values, $arr ) { ?>

			<tr>
				<th scope="row" valign="top"><?php echo $title; ?></th>
				<td>
			<?php
			foreach ( $values as $key => $title2 ) {
				$checked = ( in_array( $key, (array) $arr[$name] ) ) ? " checked='checked'" : ''; ?>
				<label><input type="checkbox" name="<?php echo $name; ?>[]" value="<?php echo $key; ?>" <?php echo $checked; ?>> <?php echo $title2; ?></label>
			<?php } ?>
				</td>
			</tr>
			<?php

		}

		/*
		**
		**
		*/
		function bool_var( $name, $title, $arr ) { ?>

			<tr>
				<th scope="row" valign="top"><?php echo $title; ?></th>
				<td>
					<?php
			$true = ( $arr[$name] ) ? " checked='checked'" : '';
			$false = ( $true ) ?  '' : " checked='checked'"; ?>
					<label><input type="radio" name="<?php echo $name; ?>" value="true" <?php echo $true; ?>> <?php echo $title2; ?> Yes</label>
					<label><input type="radio" name="<?php echo $name; ?>" value="false" <?php echo $false; ?>> <?php echo $title2; ?> No</label>
				</td>
			</tr>
			<?php

		}

		/*
		**
		**
		*/
		function move_field( $data, $nbr, $up = true ) {

			// Are we moving out of bounds?
			if ( count( $data ) == 1 ) return $data;
			if ( $nbr >= count( $data ) - 1 && !$up ) return $data;
			if ( $nbr == 0 && $up ) return $data;

			$new = array();
			$ctr = 0;
			$offset = ( $up ) ? 0 : 1;
			foreach ( $data as $key => $arr ) {
				if ( $ctr == $nbr - 1 + $offset ) $tmp_key = $key;
				else $new[$key] = $arr;
				if ( $ctr == $nbr + $offset ) $new[$tmp_key] = $data[$tmp_key];
				$ctr++;
			}
			return $new;

		}

		/*
		**
		**
		*/
		function updown_link( $nbr, $total, $args = array() ) {
			$html = '';
			$link = array( 'row' => $nbr, 'navigation' => $this->navigation, 'action' => 'move' );

			// Are we adding more stuff to our link?
			if ( !empty( $args ) ) $link = array_merge( $link, $args );

			// Build the links
			if ( $nbr > 0 ) $html .= ' | ' . $this->settings_link( '&uarr;', array_merge( $link, array( 'direction' => 'up' ) ) );
			if ( $nbr < $total - 1 ) $html .= ' | ' . $this->settings_link( '&darr;', array_merge( $link, array( 'direction' => 'down' ) ) );
			return $html;
		}

		/*
		**
		**
		*/
		function settings_link( $text, $args ) {

			$link = $this->options_url;

			foreach ( $args as $key => $value ) {
				if ( $key == 'class' ) continue;
				if ( !$value ) continue;
				if ( is_array( $value ) ) $value = implode( ',', $value );
				$link .= '&' . $key . '=' . urlencode( $value );
			}

			$link = wp_nonce_url( $link, $this->nonce_action( $args ) );
			$args['class'] = isset( $args['class'] ) ? $args['class'] : '';
			$class = ( $c = $args['class'] ) ? $c : 'more-common';
			$html = "<a class='$class' href='$link'>$text</a>";

			if ( !$text ) {
				return $link;
			}

			return $html;

		}

		/*
		**
		**
		*/
		function nonce_action( $args = array() ) {

			if ( empty( $args ) ) $args = $_GET;

			$action = $this->slug . '-action_';
			if ( isset( $args['navigation'], $args['action'] ) ) {
				if ( $a = esc_attr( $args['navigation'] ) ) $action .= $a;
				if ( $a = esc_attr( $args['action'] ) ) $action .= $a;
			}
			return $action;
		}

		/*
		**
		**
		*/
		function table_header( $titles ) { ?>

			<table class="widefat">
				<thead>
					<tr>
			<?php
			foreach ( (array) $titles as $title ) : ?>
				<th><?php echo $title; ?></th>
			<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
			<?php
		}

		/*
		**
		**
		*/
		function table_footer( $titles ) { ?>

			</tbody><tfoot><tr>
			<?php
			foreach ( (array) $titles as $title ) : ?>
				<th><?php echo $title; ?></th>
			<?php endforeach; ?>
			</tr></tfoot></table>
			<?php

		}

		/*
		**
		**
		*/
		function table_row( $contents, $nbr, $class = '' ) {
			$class .= ( $nbr++ % 2 ) ? ' alternate ' : '' ; ?>
			<tr class="<?php echo $class; ?>">
			<?php
			$count = 1;
			$total = count( $contents );
			foreach ( (array) $contents as $content ) {
				$tdClass = ( $count == $total ) ? ' class="last-td"' : ''; ?>
				<td<?php echo $tdClass; ?>><?php echo $content; ?></td>
			<?php $count++; } ?>
			</tr>
			<?php
		}

		/*
		**
		**
		*/
		function setting_row( $cols, $class = '' ) { ?>

			<tr class="<?php echo $class; ?>"><th scope="row" valign="top">
				<?php echo array_shift( $cols ); ?></th>
			<?php
			foreach ( $cols as $col ) { ?>
				<td><?php echo $col; ?></td>
			<?php
			} ?></tr>
			<?php
		}

		function get_val( $name, $k = array() ) {			

			if ( empty( $k ) ) 
				$k = $this->keys;
			
			$s = array();

			foreach ( (array) $k as $b ) {
				if ( strpos( $b, ',' ) ) {
					$c = explode( ',', str_replace( ' ', '', $b ) );
					foreach ( $c as $d ) $s[] = $d;
				}
				else $s[] = $b;
			}

			// Deal with comma separated field names
			if ( strpos( $name, ',' ) === false) {
				$c = explode( ',', str_replace( ' ', '', $name ) );
				foreach ( $c as $d ) $s[] = $d;
			} else {				
				$s[] = $name;
			}

			// Iterate through the data
			$subdata = $this->data;

			foreach ( $s as $key ) {
				if ( $this->dynamic === true ) {
					if ( isset( $subdata[$key] ) ) {
						$subdata = $subdata[$key];						
					}
				} else {
					$subdata = $subdata[$key];
				}
			}

			if ( !is_array( $subdata ) ) $subdata = stripslashes( $subdata );

			return $subdata;
		}

		/*
		**
		**
		*/
		function settings_input( $name, $s = null, $additional_options = null ) {

			$cssClass = '';
			if ( !$s || is_array( $s ) ) {
				$value = esc_attr( $this->get_val( $name, $s ) );
			} else {
				$value = $s;
			}

			if ( empty( $value ) && isset( $additional_options->values ) ) $value = $additional_options->values;
			if ( isset( $additional_options->cssClass ) ) $cssClass = $additional_options->cssClass;

			$html = '<input class="input-text '.@$cssClass.'" type="text" name="' . $name . '" value="' . $value . '">';
			return $html;

		}

		/*
		**
		**
		*/
		function settings_bool( $name, $set = array() ) {

			$vars = array( true => 'Yes', false => 'No' );
			$set = $this->get_val( $name );
			if ( is_array( $set ) ) {
				$set = 0;
			}
			$html = $this->settings_radiobuttons( $name, $vars, array(), $set );
			return $html;

		}

		function settings_radiobuttons( $name, $vars, $comments = array(), $checked = 1 ) {

			$html = '';
			$set = $this->get_val( $name );
			if ( !isset( $set ) || empty( $set ) ) {
				$set = $checked;
			}

			foreach ( $vars as $key => $value ) {
				$checked = ( $key == $set ) ? ' checked="checked"' : '';
				$html .= "<label><input class='input-radio' type='radio' name='$name' value='$key' $checked /> $value</label> ";

				if ( isset( $comments[$key] ) && $c == $comments[$key] ) {
					$html .= $this->format_comment( $c );
				}
			}

			return $html;

		}

		function settings_radiobuttons_image( $name, $vars, $comments = array(), $image_size, $checked = 1 ) {

			$html = '';
			$set = $this->get_val( $name );

			if ( !isset( $set ) || empty( $set ) ) {
				$set = $checked;
			}

			foreach ( $vars as $key => $value ) {
				$checked = ( $key == $set ) ? ' checked="checked"' : '';
				$html .= "<label><input class='input-radio' type='radio' name='$name' value='$key' $checked /><img src='$value' width='$image_size' height='$image_size'> </label> ";
				if ( isset( $comments[$key] ) ) {
					if ( $c = $comments[$key] ) {
						$html .= $this->format_comment( $c );
					}
				}
			}
			return $html;

		}

		function settings_hidden( $name, $var = 0 ) {

			if ( !$var ) {
				$var = $this->get_val( $name );
			}

			// added condition to test for array so hidden can also be used with individual fields
			if ( is_array( $var ) ) {
				$value = ( $var ) ? json_encode( $this->slasherize( $var ) ) : '';
			} else {
				$value = $var;
			}

			$html = $typeof ."<input type='hidden' name='$name' value='$value'>";

			return $html;

		}

		function slasherize( $var, $strip = false ) {

			$ret = array();
			$word = '2ew8dhpf7f3';
			foreach ( $var as $k => $v ) {
				if ( !$strip ) $ret[$k] = ( is_array( $v ) ) ? $this->slasherize( $v ) :  str_replace( array( '"', "'" ), array( $word, strrev( $word ) ), stripslashes_deep( htmlspecialchars_decode( $v ) ) );
				else $ret[$k] = ( is_array( $v ) ) ? $this->slasherize( $v, true ) :  str_replace( array( $word, strrev( $word ) ), array( '"', "'" ), $v );
			}
			return $ret;

		}
		
        /**
		 * Load all css files from a directory
		 * 
		 * @param $file_dir = file path to css directory
		 * @return array - full server path to each file
		 * 
		 */
		function loadFolderCSS($file_dir) {
			$stylesheets = '';

			if ($css_dir = opendir($file_dir)) {
				while (false !== ($file = readdir($css_dir))) {
					if (preg_match('/^[A-Za-z0-9_-]+\\.css$/', $file) > 0) {
						$stylesheets[] = trailingslashit($file_dir) . $file;
					}
				}
				closedir($css_dir);
			}

			return $stylesheets;
		} 

		/**
		 * Get the theme's CSS skin files 
		 * @return array Key is CSS file name, Value is CSS file name
		 */
		function get_skin_css() {

			$themeFolder = get_stylesheet_directory(); // If child theme it will search child theme folder only
			$css_files = $this->loadFolderCSS( $themeFolder ); 
			$skins = array();
		
			if ( is_array( $css_files ) ) {
				$base = array( trailingslashit(get_template_directory()), trailingslashit(get_stylesheet_directory()) );
		
				foreach ( $css_files as $skin ) {
					$basename = str_replace($base, '', $skin);
		
					$skin_data = implode( '', file( $skin ));
		
					$name = '';
					if ( preg_match( '|Skin Name:(.*)$|mi', $skin_data, $name ) )
						$name = _cleanup_header_comment($name[1]);
		
					if ( !empty( $name ) ) {
						$skins[$basename] = trim( $name );
					}
				}
			}
		
			return $skins;
		}

		/*
		**
		**
		*/
		function checkbox_list( $name, $vars, $options = array(), $checked_list = array() ) {

			$values = (array) $this->get_val( $name );

			if ( empty( $values[0] ) ) {
				$values = $checked_list;
			}

			if ( $values[0] == 'Array' ) {
				$values = array( 0 => $options['values'] );
			}

			$html = '';

			foreach ( $vars as $key => $val ) {
				// Options will over-ride values
				$class = ( $a = $options[$key]['class'] ) ? 'class="' . $a . '"' : '';
				$readonly = ( $options[$key]['disabled'] ) ? ' disabled="disabled"' : '';

				if ( array_key_exists( 'value', (array) $options[$key] ) )
					$checked = ( $options[$key]['value'] ) ? ' checked="checked" ' : '';
				else if ( is_array( $values ) )
						$checked = ( in_array( $key, $values ) ) ? ' checked="checked"' : '';

					$html .= "<label><input class='input-check' type='checkbox' value='$key' name='${name}[]' $class $readonly $checked /> $val</label>";
				if ( $t = $options[$key]['text'] ) $html .= format_comment( $t );
			}
			return $html;

		}

		function settings_select( $name, $vars, $values = false ) {

			$values = ( $values ) ? $values : $this->get_val( $name );

			$html = "<select class='input-select' name='$name'>";

			if ( !empty( $vars ) ) {
				foreach ( $vars as $key => $val ) {
					$checked = ( $key == $values ) ? ' selected="selected"' : '';
					$html .= "<option value='$key' $checked> $val</option>";
				}
			}

			$html .= '</select>';

			return $html;

		}

		function settings_textarea( $name, $s = null, $additional_options = null ) {

			$cssClass = '';
			if ( $s ) {
				$value = $s;
			} else {
				$value = $this->get_val( $name );
			}

			if ( empty( $value ) && isset( $additional_options->values ) ) $value = $additional_options->values;
			if ( isset( $additional_options->cssClass ) ) $cssClass = $additional_options->cssClass;

			$html = "<textarea class='input-textarea ".@$cssClass."' name='$name'>$value</textarea>";

			return $html;

		}

		function settings_colorpicker( $name, $s = null, $additional_options = null ) {

			if ( !$s || is_array( $s ) ) {
				$value = esc_attr( $this->get_val( $name, $s ) );
			} else {
				$value = $s;
			}

			if ( empty( $value ) && isset( $additional_options->values ) ) $value = $additional_options->values;
			if ( isset( $additional_options->cssClass ) ) $cssClass = $additional_options->cssClass;

			$html = '<input type="text" id="color" name="'.$name.'" class = "input-text '.$cssClass.'"
			value="'.$value.'" style="background-color:'.$value.'" maxlength="7" />';
			$html .= '<div id="colorpick-dialog" name = "' . $name . '" style="text-align:center;" title="' . $additional_options->title . '">';
			$html .= '<input type="text" id="color-colorpick" maxlength="7" name="'.$name.'" value="'.$value.'" style="background-color:'.$value.'; visibility: hidden; position:absolute;" />';
			$html .= '<div id="colorpicker" align="center" name = "'.$name.'"></div>';
			$html .= '<br><button class="button" id="color-colorpick-done" name="'.$name.'" style="visibility: hidden; position:absolute;">'. __( 'Apply Color', 'framework' ) .'</button>';
			// $html .= '<input type="button" id="color-colorpick-done" name="'.$name.'" style="visibility: hidden; position:absolute;" value="Done pick color" /></div>';

			$html .= '
			<script type="text/javascript">
				(function ($) {
					if($("#color[name=\''.$name.'\']").val() == ""){
						$("#color[name=\''.$name.'\']").val("#ffffff");
						$("#color-colorpick[name=\''.$name.'\']").val("#ffffff");
					}

					$(function () {
						$("#color[name=\''.$name.'\']").focus(function(){
							$("#colorpick-dialog[name=\''.$name.'\']").dialog({
								position: ["center"],
								modal: true,
								resizable: false
							});

							$(".ui-widget-overlay").click(function(){

								$("#colorpick-dialog[name=\''.$name.'\']").dialog("close");
							});

							$("#colorpicker[name=\''.$name.'\']").farbtastic("#color[name=\''.$name.'\'], #color-colorpick[name=\''.$name.'\']");
							$("#color-colorpick[name=\''.$name.'\']").css("visibility", "visible");
							$("#color-colorpick[name=\''.$name.'\']").css("position", "inherit");
							$("#color-colorpick-done[name=\''.$name.'\']").css("visibility", "visible");
							$("#color-colorpick-done[name=\''.$name.'\']").css("position", "inherit");
						});



						 $("#color[name=\''.$name.'\']").change(function(){
							 var picker = $.farbtastic("#colorpicker[name=\''.$name.'\']");  //picker variable
							 picker.setColor($("#color[name=\''.$name.'\']").value); //set initial color
						 });

						 $("#color-colorpick-done[name=\''.$name.'\']").click(function(){
							$("#colorpick-dialog[name=\''.$name.'\']").dialog("close");
						 });
					 })

				})(jQuery);
			</script>';

			return $html;

		}

		function get_version_id( $prefix = 'id_' ) {
			$key1 = base_convert( mt_rand( 0x1679616, 0x39AA3FF ), 10, 36 );
			$key2 = base_convert( microtime(), 10, 36 );
			$id = $prefix . $key1 . $key2;
			return $id;
		}

		/*
		**
		**
		*/
		function navigation_bar( $levels ) {

			?><ul class="nav-bar-trail">
			<li><a href="<?php echo $this->settings_url; ?>"><?php echo $this->name; ?></a></li>
			<?php
			for ( $i = 0; $i < count( $levels ); $i++ ) {
				$selected = ( $i == count( $levels ) - 1 ) ? ' selected="selected"' : '';
				echo '<li ' . $selected . '">' . $levels[$i] . '</li>';
			}
			?></ul><?php

		}

		/*
		**
		**
		*/
		function settings_head() { ?>

			<script type="text/javascript">
			//<![CDATA[
				jQuery(document).ready(function($){
					$("a.common-delete, a.more-common-delete").click(function(){
						return confirm("<?php _e( 'Are you sure you want to delete?', THEME_NAME ); ?>");
					});
					$("#post-body-content .postbox").each( function(){
							var handle = jQuery(this).children('.hndle, .handlediv');
							var content = jQuery(this).children('.inside');
							handle.click( function(){
								content.slideToggle();
								return false;
							});
					});	
				});
			//]]>
			</script>
			<?php $css = FRAMEWORK_URL . 'framework/css/styles.css'; ?>
			<link rel='stylesheet' type='text/css' href='<?php echo $css; ?>' />
			<?php
		}

		function settings_form_header( $args = array() ) {
			$keys = isset( $_GET['keys'] ) ? $_GET['keys'] : '';
			$defaults = array( 'action' => 'save', 'keys' => $keys );
			$args = wp_parse_args( $args, $defaults ); ?>
			<?php $url = $this->settings_link( false, $args ); ?>
				<form method="post" action='<?php echo $url; ?>'>
			<?php
		}

		function format_comment( $comment ) {
			// return '<em class="howto">' . $comment . '</em>';
			return '<p class="description">' . $comment . '</p>';
		}

		function settings_save_button( $text = 'Save', $class = 'button' ) {
			$this->keys = isset( $this->keys ) ? $this->keys : array();
			$keys = implode( ',', (array) $this->keys ); ?>

			<input type="hidden" name='version_key' value='<?php echo $this->get_version_id(); ?>' />
			<input type="hidden" name='import_key' value='<?php echo $this->get_val( 'import_key' ); ?>' />
			<input type="hidden" name='ancestor_key' value='<?php echo $this->get_val( 'ancestor_key' ); ?>' />
			<input type="hidden" name='originating_keys' value='<?php echo $keys; ?>' />
			<input type="hidden" name='action' value='save' />
			<p class="submit">
				<input type="submit" class='<?php echo $class; ?>' value='<?php _e( $text, THEME_NAME ); ?>' />
			</p>
			</form>

		<?php
		}

		function permalink_warning() {
			global $wp_rewrite;
			
			if ( empty( $wp_rewrite->permalink_structure ) ) {
				$html = '<em class="warning">';
				$html .= __( 'Permalinks are currently not enabled! To use this feature, enable permalinks in the <a href="options-permalink.php">Permalink Settings</a>.', THEME_NAME );
				$html .= '</em>';
				return $html;
			} else { 
				return '';
			}
		}

		function settings_multicheck( $name, $vars, $values = false, $_name = '' ) {

			global $theme_settings;

			$values = ( $values ) ? $values : $this->get_val( $name );
			// $values = ( $values ) ? $values : unserialize( $theme_settings->data_loaded['_framework']['options']['disable_wp_content'] );

			if ( !is_array( $values ) )
				$values = unserialize( $values );

			if ( $_name ) {
				$tvalues = array();
				if ( is_array( $values ) ) {
					foreach ( $values as $tkey => $tval ) {
						if ( is_array( $tval ) ) {
							foreach ( $tval as $ttkey => $ttval ) {
								if ( $ttval == $_name ) {
									$tvalues[] = $tkey;
								}
							}
						}
					}
				}

				$values = $tvalues;
			}

			$html = '';

			foreach ( $vars as $key => $val ) {
				$checked = ( in_array( $key, $values ) ) ? ' checked="checked"' : '';
				$post_type = get_post_type_object( $val );
				$html .= '
						<label>
							<input type="checkbox" name="'. $name.'['.$_name.'][]" value="'. $key .'" '. $checked .'>
							'. ( !empty( $post_type->labels->singular_name ) ? $post_type->labels->singular_name : $post_type->labels->menu_name ) .'
						</label>';
			}

			return $html;
		}

		function settings_multiselect( $name, $vars, $values = false, $_name = '' ) {

			global $theme_settings;
			$values = ( $values ) ? $values : $this->get_val( $name );
			// $values = ( $values ) ? $values : unserialize( $theme_settings->data_loaded['_framework']['options']['disable_wp_content'] );

			if ( !is_array( $values ) )
				$values = unserialize( $values );

			if ( $_name ) {
				$tvalues = array();
				if ( is_array( $values ) ) {
					foreach ( $values as $tkey => $tval ) {
						if ( is_array( $tval ) ) {
							foreach ( $tval as $ttkey => $ttval ) {
								if ( $ttval == $_name ) {
									$tvalues[] = $tkey;
								}
							}
						}
					}
				}

				$values = $tvalues;
			}

			$html = '<select multiple class="input-select" name="' . $name . ( $_name ? "[{$_name}]" : '' ) . '[]" size="5" style="height: 103px;">';

			$html .= '<option value="no">No value</option>';

			foreach ( $vars as $key => $val ) {
				if ( is_array( $values ) ) {
					$checked = ( in_array( $key, $values ) ) ? ' selected="selected"' : '';
				}

				if ( $val != '' ) {
					$html .= '<option value="'.$key.'"'.$checked.'>'.$val.'</option>';
				}
			}
			$html .= '</select>';
			return $html;
		}

		function field_template_path($field) {
			
			$theme_data = rw_get_theme_data();

			$template_path = null;

			if ( file_exists( THEME_DIR . 'data-types/' . $field->type . '.php' ) ) {
				$template_path = THEME_DIR . 'data-types/' . $field->type . '.php';
			} else {
				$template_path = get_theme_root().'/'.$theme_data['Template'].'/data-types/'.$field->type.'.php';
			}

			if(file_exists($template_path)) {
				return $template_path;
			} else {
				return false;
			}

		}		

		function render_field($field) {

			ob_start();
			
			extract( (array)$field );			

			include $template_path;
			
			return ob_get_clean();
			

		}

		function get_rendered_field_content($field) {
			
			ob_start();						

			$field->render_content();	
			
			$html = ob_get_contents();
			ob_end_clean();
			
			return $html;

		}

		/* loading data-types for dynamic created pages */
		function dynamic_template_field( $field ) {			

			// set field template file path
			$field->template_path = $this->field_template_path($field);						
			if( $field->template_path !== false) {

				/* new data types loading */

				// include_once get_theme_root() . '/runway-framework/data-types/data-type.php';									
				include_once FRAMEWORK_DIR . 'data-types/data-type.php';									
				include_once $field->template_path;			

				$class_Name = ucfirst(str_replace('-', '_', $field->type));

				$field_object = new $class_Name($this, $field);				

				return $this->get_rendered_field_content($field_object);
			}
		}

		/******/

		function wp_customize_page_render ($wp_customize) {
			// $data_types_path = get_theme_root() . "/runway-framework/data-types";
			$data_types_path = FRAMEWORK_DIR . 'data-types';
			$data_types_base = $data_types_path . '/data-type.php';

			$this->data_types = array();

			if(!file_exists($data_types_path) || !file_exists($data_types_base)) {
				wp_die("Error: data types");
			} else {
				include_once $data_types_base;

				foreach(array_diff(scandir($data_types_path), array('..', '.', 'data-type.php')) as $filename) {							
					include_once "$data_types_path/$filename";	
				}
			}			

		    $this->data = get_option( $this->option_key );

			// including js and cs
			// $this->include_extension_css();
			// $this->include_extension_js();

			foreach($this->builder_page->sortOrder as $tab) {
				if($tab != 'none') {
					foreach($tab as $container_id => $container_fields) {
						if($container_id != 'none') {
							$container = $this->builder_page->elements->$container_id;
							
							if(
							   isset($container->display_on_customization_page) && 
							   $container->display_on_customization_page == true) {

								$wp_customize->add_section( $container->index, array(
						        	'title' => $container->title, //Visible title of section
					        		'description' => '', 
							    ) );			
															
								foreach($container_fields as $field_id) {								
									if($field_id != 'none') {																																				
										$field = $this->builder_page->elements->$field_id; 									

										$class_Name = ucfirst(str_replace('-', '_', $field->type));

										if(class_exists($class_Name)) {
											$wp_customize->add_setting( $field->alias, array(
										    	'default' => '',
											) );

											$option_field = new $class_Name( 
											    $this,
											    $field,                           
												$wp_customize, 
												$field->alias, 
												array(
												    'label' => $field->title,
												    'section' => $container->index,
												    'settings' => $field->alias,
												) 
											);

											add_action('customize_save_' .$field->alias, array($option_field, 'save'));
											add_filter('customize_value_' . $field->alias, array($option_field, 'get_value'));

											$wp_customize->add_control( $option_field );										
										}
									}								
								}
							}
						}						
					}	
				}				
			}
		}
	}
}

define( $runway_framework_admin, true );

if ( !is_callable( '__d' ) ) {
	function __d( $d ) {
		echo '<pre>';
		print_r( $d );
		echo '</pre>';
	}
}

?>