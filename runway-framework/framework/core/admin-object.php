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

			// Set extension or page name
			$this->name = (isset($settings['name'])) ? $settings['name'] : __('Options', 'framework');

			// if dynamic page set slug as alias else create slug from title
			$this->slug = isset( $settings['alias'] ) ? sanitize_title( $settings['alias'] ) : sanitize_title( $this->name );

			$this->pref = '';

			if(isset($this->builder_page)) {						
				$this->pref = $this->get_menu_adapt($this->builder_page->settings->adminMenuTopItem);								
			} else {
				$this->pref = $this->pref . strtolower('framework');
			}

			$this->page_hook = $this->pref . "_page_" . $this->slug;			
			
			// set fields (for static reated page objects)
			$this->fields = isset($settings['fields']) ? $settings['fields'] : array( 'var' => array(),'array' => array() );

			// maybe deprecated
			$this->settings_file = isset( $settings['settings_file'] ) ? $settings['settings_file'] : 'admin.php';

			// set extension dir
			$this->dir = (isset($settings['file'])) ? plugin_dir_path( $settings['file'] ) : '';

			// broken or deprecated
			$this->url = (isset($settings['file'])) ? plugin_dir_url( $settings['file'] ) : '';

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
			$this->option_key = (isset($settings['option_key'])) ? $settings['option_key'] : uniqid();

			// set default values
			$this->default = isset($settings['default']) ? $settings['default'] : array();

			// set default keys
			$this->default_keys = ( $a = $this->default ) ? $a : array();

			// Create Settings Menu
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );

			// fix parent slugs for dynamic created pages
			if ( isset( $settings['dynamic'] ) ) {
				$this->parent_slug = 'admin';
				$this->dynamic = true;
			} else {
				$this->parent_slug = $this->get_menu_adapt( $this->parent_menu );
			}

			// Handle requests
			add_action( $this->page_hook, array( $this, 'settings_init' ) );
			add_action( $this->page_hook, array( $this, 'request_handler' ) );

			// Add JS & css on settings page
			//add_action('admin_head-' . $this->parent_menu . '_page_' . $this->slug, array(&$this, 'settings_head'));
			//-!!!!--- ABOVE: Moved to admin_menu() function because it's just easier to call ---!!!!-\\

			// COMMENTED OUT BECAUSE OF ERROR MESSAGE IN WP PLUGINS ADMIN // 
			// add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );

			add_action( 'init', array( $this, 'admin_init' ), 11 );

			$this->add_actions();

			$this->add_key = '57UPhPh'; // I'm not sure we need this any longer. We should look closely where it's applied and determine the importance.

			// get database data
			$database_data = get_option( $this->option_key );

			if ( empty( $database_data ) ) {
				$this->has_save_data = false;
			} else {
				$this->has_save_data = true;
			}

			$this->html = $html = new Html($this);
		}

		function settings_save_button( $text = 'Save', $class = 'button' ) {
			$this->keys = isset( $this->keys ) ? $this->keys : array();
			$keys = implode( ',', (array) $this->keys ); 

			$import_key = (!is_array($this->get_val( 'import_key' ))) ? $this->get_val( 'import_key' ) : '';
			$ancestor_key = (!is_array($this->get_val( 'ancestor_key' ))) ? $this->get_val( 'ancestor_key' ) : '';

			?>

			<input type="hidden" name='version_key' value='<?php echo esc_attr($this->get_version_id()); ?>' />
			<input type="hidden" name='import_key' value='<?php echo esc_attr($import_key); ?>' />
			<input type="hidden" name='ancestor_key' value='<?php echo esc_attr($ancestor_key); ?>' />
			<input type="hidden" name='originating_keys' value='<?php echo esc_attr($keys); ?>' />
			<input type="hidden" name='action' value='save' />
			<p class="submit">
				<input type="submit" class='<?php echo esc_attr($class); ?>' value='<?php rf_e($text); ?>' />
			</p>
			</form>

		<?php
		}

		// experimental
		function view( $view, $return_as_string = false, $values = null) {

			$html = $this->html;

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
			else {
				global $shortname;
				
				if(isset($shortname) && $shortname != "")
					return preg_replace('/_$/', "", $shortname);
				else
					return preg_replace('/\s/', '-', strtolower(THEME_NAME));
			}

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
					$this_page = add_submenu_page( $this->menu_url, $this->name, $this->name, $this->menu_permissions, $this->slug, array( $this, 'options_page' ) );				
				} else {
					$this_page = add_submenu_page( 'admin.php', $this->name, $this->name, $this->menu_permissions, $this->slug, array( $this, 'options_page' ) );				
				}

				add_action( 'runway_page_' . $this->slug, array( $this, 'options_page' ));
				add_action( 'admin_head-' . $this_page, array( $this, 'settings_head' ) );
				add_action( 'admin_print_styles-' . $this_page, array( $this, 'include_extension_css' ), 11 ); // add CSS specific to this page

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
			global $translation_array;
		
			if ( isset( $this->js ) && !empty( $this->js ) ) {
				foreach ( $this->js as $js ) {
					if ( preg_match( '|(.+)/(.+).js|', $js ) ) {
						$explodeJS = explode( '/', $js );
						$js_name = array_pop( $explodeJS );
						if (!wp_script_is( $js_name, 'registered' ))
							wp_register_script($js_name, $js);
						wp_localize_script( $js_name, 'translations_js', $translation_array );
						wp_enqueue_script( $js_name, $js );
					}
					else {
						if (!wp_script_is( $js, 'registered' ))
							wp_register_script($js, $js);
						wp_localize_script( $js, 'translations_js', $translation_array );
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
			$this->include_extension_js();
			// !!! --- MOVED CSS include to admin_menu enqueue for easier reference and proper hooking in header --- !!! ///
			// $this->include_extension_css();

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
				if ( $this->action == 'save' && !empty($_POST) ) {
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
					return $this->error( __( 'Someting has gone awry. Sorry.', 'framework' ) );

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
					$this->message = __( 'Saved!', 'framework' );
				}
			}

			if ( $this->action == 'delete' ) {
				$data = $this->unset_data( $this->action_keys );
				$this->save_data();
				$this->message = __( 'Deleted!', 'framework' );
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
				$description = '<p class="pageDescription">'. wp_kses_post( $this->builder_page->settings->pageDescription ).'</p>';
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
			<div id="theme-framework" class="<?php echo esc_attr($containerClass); ?>">

				<div id="icon-options-general" class="icon32"><br /></div>

			<?php
			echo  $adminPageTitle . PHP_EOL; // The title
			echo  $adminPageDesc . PHP_EOL; // The description
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
				<div id="theme-framework" class="has-right-sidebar <?php echo esc_attr($this->slug); ?> <?php echo esc_attr($this->slug . '-' . $this->navigation); ?>">

					<div id="icon-options-general" class="icon32"><br /></div>
					<h2><?php echo  $this->name; ?></h2>

					<div class="inner-sidebar metabox-holder">



						<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">

							<?php

							// Right Column Box ?>
							<div class="dpostbox theme-framework-box">
								<h3 class="hndle"><span><?php _e('Title', 'framework'); ?></span></h3>
								<div class="inside">
									<ul class="action-links">

										<li>
											<dl>
												<dt><a href="#"><?php _e('A Title', 'framework'); ?></a></dt>
												<dd><?php _e('Description', 'framework'); ?></dd>
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
		function nonce_action( $args = array() ) {

			if ( empty( $args ) ) $args = $_GET;

			$action = $this->slug . '-action_';
			if ( isset( $args['navigation'], $args['action'] ) ) {
				if ( $a = esc_attr( $args['navigation'] ) ) $action .= $a;
				if ( $a = esc_attr( $args['action'] ) ) $action .= $a;
			}
			return $action;
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
			<li><a href="<?php echo esc_url($this->settings_url); ?>"><?php echo  $this->name; ?></a></li>
			<?php
			for ( $i = 0; $i < count( $levels ); $i++ ) {
				$selected = ( $i == count( $levels ) - 1 ) ? ' selected="selected"' : '';
				echo '<li ' . $selected . '>' . $levels[$i] . '</li>';
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
						return confirm("<?php _e( 'Are you sure you want to delete?', 'framework' ); ?>");
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
			<link rel='stylesheet' type='text/css' href='<?php echo esc_url($css); ?>' />
			<?php
		}		

		function field_template_path($field) {
			
			$theme_data = rw_get_theme_data();

			$template_path = null;

			if ( file_exists( THEME_DIR . 'data-types/' . $field->type . '.php' ) ) {
				$template_path = THEME_DIR . 'data-types/' . $field->type . '.php';
			} else {
				$found_in_theme_dirs = false;
				if(is_dir( THEME_DIR . 'data-types')) {
					$names = runway_scandir(THEME_DIR . 'data-types', array('data-type.php'));
					foreach ( $names as $name ) {
						if(is_dir( THEME_DIR . 'data-types/' . $name) && file_exists(THEME_DIR . 'data-types/' . $name . "/" . $field->type . '.php'))
						{
							$template_path = THEME_DIR . 'data-types/' . $name . "/" . $field->type . '.php';
							$found_in_theme_dirs = true;
							break;
						}
					}
				}
				
				$found_in_framework_dirs = false;
				if(!$found_in_theme_dirs) {
					$names = runway_scandir(get_theme_root().'/'.$theme_data['Template'] . '/data-types', array('data-type.php'));
					foreach ( $names as $name ) {
						if(is_dir( get_theme_root().'/'.$theme_data['Template'] . '/data-types/' . $name) && 
							file_exists(get_theme_root().'/'.$theme_data['Template'] . '/data-types/' . $name . "/" . $field->type . '.php'))
						{
							$template_path = get_theme_root().'/'.$theme_data['Template'] . '/data-types/' . $name . "/" . $field->type . '.php';
							$found_in_framework_dirs = true;
							break;
						}
					}
				}
				
				if(!$found_in_framework_dirs && !$found_in_theme_dirs) {
					$template_path = get_theme_root().'/'.$theme_data['Template'].'/data-types/'.$field->type.'.php';
				}
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