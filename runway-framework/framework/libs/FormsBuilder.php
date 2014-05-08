<?php

class FormsBuilder {
	public $forms_path, $js_path, $templates_path, $default_form_settings, $save_action, $save_custom_options_action, $resolutions, $formsbuilder_path,
	$option_key, $options_pages, $section;

	public function __construct( $save_action = 'save_form_from_builder', $save_custom_options_action = 'save_custom_options' ) {

		$this->init_variables(); // Initialize variables
		$this->add_actions( $save_action, $save_custom_options_action ); // Add actions function

		if ( is_admin() ) {
			$this->add_to_customize_page();
		}
	}

	// Include data-types
	function load_data_types() {
		global $data_types_list;

		foreach ( $data_types_list as $slug => $data_type ) {

			$data_type['classname']::data_type_register();
			$data_type['classname']::render_settings();

		}
	}

	// Return form builder
	public function form_builder( $options = array() ) {
		$this->load_data_types();
		// Set options to default settings if it empty
		if ( empty( $options ) ) {
			$options = $this->default_form_settings;
		}
		// Extract form options
		extract( $options );

		// Include builder templates
		include $this->templates_path.'base-fields.php';
		include $this->templates_path.'page-settings.php';
		include $this->templates_path.'field-settings-preview.php';
		include $this->templates_path.'field-settings-form.php';
		include $this->templates_path.'tab-settings-form.php';
		include $this->templates_path.'container-settings-form.php';

		// Include builder
		require_once LIBS_DIR."formsbuilder/builderview.php";
	}

	public function render_form( $page_options = array(), $default_save = true, $object = null, $admin_object = null, $custom_alias = null ) {

		// out($page_options);
		if ( !empty( $page_options ) ) {
			$alias = $page_options->settings->alias;
			$current = $this->prepare_form( $page_options );
			// out($page_options);
			$settings = $this->make_settings( $current );
			global ${$current['object']}, ${$current['admin_object']};

			if ( $object != null && $admin_object != null ) {
				${$current['object']} = $object;
				${$current['admin_object']} = $admin_object;
			}
			else {
				$settings = $this->make_settings( $current );
				if ( !empty( $settings ) ) {
					${$current['object']} = new Runway_Object( $settings );
					${$current['admin_object']} = new Runway_Admin_Object( $settings );

				}
			}

			${$current['admin_object']}->section = $this->section;

			if ( $alias != '' && !empty( $current ) ) {
				require LIBS_DIR."formsbuilder/render-form.php";
				return true;
			}
			else return false;

		}
	}

	// Add options page to pages list
	public function add_page_to_pages_list( $page = null ) {
		if ( !empty( $_REQUEST['json_form'] ) && $page == null ) {
			$page_options = json_decode( stripslashes( $_REQUEST['json_form'] ) );
			$this->options_pages[$page_options->settings->alias] = $page_options;
			update_option( $this->option_key, $this->options_pages );
			die();
		}
		else {
			$this->options_pages[$page->settings->alias] = $page;
			update_option( $this->option_key, $this->options_pages );
		}
	}

	public function add_to_customize_page() {
		if ( !empty( $this->options_pages ) ) {
			foreach ( $this->options_pages as $key => $page_options ) {
				if ( !empty( $page_options ) ) {
					$current = $this->prepare_form( $page_options );
					$settings = $this->make_settings( $current );

					if ( !empty( $settings ) ) {
						${$current['admin_object']} = new Settings_Object( $settings );
					}

					add_action( 'customize_controls_print_styles', array( ${$current['admin_object']}, 'include_extension_css' ) );
					add_action( 'customize_controls_print_scripts', array( ${$current['admin_object']}, 'include_extension_js' ) );

					$_this = ${$current['admin_object']};
					add_action( 'customize_register' , function ( $wp_customize ) use ( $_this ) {
						// $data_types_path = get_theme_root() . "/runway-framework/data-types";
						$data_types_path = FRAMEWORK_DIR . 'data-types';
						$data_types_base = $data_types_path . '/data-type.php';

						$_this->data_types = array();

						if ( !file_exists( $data_types_path ) || !file_exists( $data_types_base ) ) {
							wp_die( "Error: data types" );
						} else {
							include_once $data_types_base;

							foreach ( array_diff( scandir( $data_types_path ), array( '..', '.', 'data-type.php' ) ) as $filename ) {
								include_once "$data_types_path/$filename";
							}
						}

						$_this->data = get_option( $_this->option_key );

						// including js and cs
						// $_this->include_extension_css();
						// $_this->include_extension_js();

						foreach ( $_this->builder_page->sortOrder as $tab ) {
							if ( $tab != 'none' ) {
								foreach ( $tab as $container_id => $container_fields ) {
									if ( $container_id != 'none' ) {
										$container = $_this->builder_page->elements->$container_id;

										if (
											isset( $container->display_on_customization_page ) &&
											$container->display_on_customization_page == true ) {

											$wp_customize->add_section( $container->index, array(
													'title' => $container->title, //Visible title of section
													'description' => '',
													'priority' => isset($container->priority)? $container->priority : ''
												) );
											
											$priority_level = 0;
											foreach ( $container_fields as $field_id ) {
												if ( $field_id != 'none' ) {
													$field = $_this->builder_page->elements->$field_id;

													$class_Name = ucfirst( str_replace( '-', '_', $field->type ) );

													if ( class_exists( $class_Name ) ) {
														$wp_customize->add_setting( $field->alias, array(
																'default' => '',
																'type' => 'customize'
															) );

														$option_field = new $class_Name(
															$_this,
															$field,
															$wp_customize,
															$field->alias,
															array(
																'label' => $field->title,
																'section' => $container->index,
																'settings' => $field->alias,
																'priority' => $priority_level
															)
														);
														$priority_level++;

														add_action( 'customize_save_' .$field->alias, array( $option_field, 'save' ) );
														add_filter( 'customize_value_' . $field->alias, array( $option_field, 'get_value' ) );

														$wp_customize->add_control( $option_field );
													}
												}
											}
										}
									}
								}
							}
						}
					} );
				}
			}
		}
	}

	public function prepare_form( $page = null ) {
		if ( $page != null ) {
			global $shortname;
			$page_options = array();
			if ( !isset( $page->settings->title ) ) {
				$page->settings->title = $page->settings->page_id;
			}

			// Eventually this should be a field in the page settings. When that happens we'll also need
			// to update the core admin object to use the alias as the URL 'page=alias' instead of a
			// sanatized version of the name like it is now.
			if ( !isset( $page->settings->alias ) )
				$page->settings->alias = sanitize_title( $page->settings->title );

			$alias = $page->settings->alias;
			$id = $page->settings->page_id;

			// Setup the values
			$page_options['builder_page'] = $page;
			$page_options['id'] = $id;
			$page_options['alias'] = $alias;

			$page_options['var'] = array();
			$page_options['arrays'] = array();
			$page_options['submission_keys'] = array();

			$array_types = array( 'checkbox-type', 'multiselect' );

			foreach ( $page->elements as $element ) {
				if ( isset( $element->template ) ) {
					if ( $element->template == 'field' ) {
						$result = array();

						$page_options['fields']['var'][] = $element->alias;

						// values from a function
						$search_function_preg = '/get_values_from=\"(?P<functions>\w+)\"/';

						if ( isset( $element->values ) && !empty( $element->values ) ) {
							if(is_array($element->values)) {
								$element->values = implode("=>", $element->values);
							}
							$value = html_entity_decode( $element->values );
							preg_match_all( $search_function_preg, $value, $result );
						}

						if ( isset( $result['functions'] ) ) {
							if ( count( $result['functions'] ) ) {
								$element->values = '';
								foreach ( $result['functions'] as $function ) {
									if ( function_exists( $function ) ) {
										$element->values .= $function();
									}
								}
							}
						}
					}
				}
			}

			$page_options['defaults'] = array();

			$page_options['name'] = $page->settings->title;
			$page_options['option_key'] = $shortname.$page->settings->alias;
			$page_options['parent_menu'] = $page->settings->adminMenuTopItem;
			$page_options['menu_permissions'] = (isset($page->settings->access)) ? $page->settings->access : 'edit_theme_options';
			// wp_die($page->settings->access);


			$page_options['object'] = 'object_'.$id;
			$page_options['admin_object'] = 'admin_object_'.$id;
			$page_options['elements'] = $page->elements;

			$page->sortOrder = ( isset( $page->sortOrder ) ) ? $page->sortOrder : '';
			$page_options['sortOrder'] = $page->sortOrder;

			return $page_options;
		}
	}

	public function make_settings( $page_options = array() ) {
		$default = $page_options['defaults'];
		$page_options['fields'] = ( isset( $page_options['fields'] ) ) ? $page_options['fields'] : '';
		$settings = array(
			'builder_page' => $page_options['builder_page'],
			'name' => $page_options['name'],
			'alias' => $page_options['alias'],
			'option_key' => $page_options['option_key'],
			'fields' => $page_options['fields'],
			'default' => $default,
			'parent_menu' => $page_options['parent_menu'],
			'menu_permissions' => $page_options['menu_permissions'],
			'dynamic' => true,
			'file' => __FILE__,
			'js' => array(
				'jquery',
				'wp-color-picker',
				FRAMEWORK_URL.'framework/js/jquery-ui.min.js',
				FRAMEWORK_URL.'framework/js/jquery.cookie.js',
				FRAMEWORK_URL.'framework/includes/options-page-render/js/scripts.js',
			),
			'css' => array(
				'wp-color-picker',
				FRAMEWORK_URL.'framework/css/styles.css',
				FRAMEWORK_URL.'framework/includes/options-page-render/css/style.css',
				FRAMEWORK_URL.'framework/js/farbtastic/farbtastic.css',
			)
		);

		return $settings;
	}

	/**********************************************/
	public function save( $json = '', $callback = false ) {
		if ( $callback && $json != '' ) {
			$callback( $json );
		}
		elseif ( $json != '' ) {
			// TODO: save as form
			$form = json_decode( $json );
			if( IS_CHILD && get_template() == 'runway-framework')
				file_put_contents( $this->forms_path.$form->page_id.'.json', $json );
		}
	}

	public function save_form_from_builder() {
		$json_form = $_REQUEST['json_form'];
		echo $json_form; die();
		// $this->save($json_form);
	}

	public function save_custom_options( $options = null, $custom_alias = null ) {
		$is_ajax = false;
		if ( $options == null ) {
			$is_ajax = true;
			$options = $_REQUEST;
			$custom_alias = $options['custom_alias'];
		}

		if ( !empty( $options['form_key'] ) && !empty( $options['vals'] ) ) {
			$save = array();

			foreach ( $options['types'] as $key => $value ) {
				$save['field_types'][$key] = trim( str_replace( 'custom-data-type', '', $value ) );
			}

			foreach ( $options['vals'] as $key => $value ) {
				$save[$key] = $value;
			}
			$alias = ( $custom_alias == null ) ?$this->option_key.$options['form_key'] : $custom_alias;

			update_option( $alias, $save );
		}

		if ( $is_ajax )
			die();
	}

	public function get_custom_options_vals( $form = '', $custom_alias = false ) {
		if ( $form != '' ) {
			global $shortname;
			$alias = $this->option_key.$form;
			if ( $custom_alias ) {
				$alias = $shortname.$form;
			}

			$form_values = get_option( $alias );

			$vals = array(
				'_framework' => $form_values,
				'_framework_saved' => array(
					// nothing todo
				),
				'_other' => array(
					// nothing todo
				),
				'_default' => array(
					// nothing todo
				),
			);

			if ( $form_values ) {
				$vals['_framework']['index'] = $form;
				$vals['_framework']['ancestor_key'] = '';
				$vals['_framework']['version_key'] = '';
				$vals['_framework']['import_key'] = '';
			}

			return $vals;
		}
		else return false;
	}

	public function init_variables() {
		global $shortname;

		$this->formsbuilder_path = LIBS_DIR.'formsbuilder/';
		$this->templates_path = LIBS_DIR.'formsbuilder/templates/';
		$this->js_path = FRAMEWORK_URL.'framework/libs/formsbuilder/js/';
		$this->css_path = FRAMEWORK_URL.'framework/libs/formsbuilder/css/';
		$this->forms_path = get_stylesheet_directory() . '/data/forms/';
		$this->option_key = $shortname.'formsbuilder_';
		$this->section = '';

		$this->options_pages = get_option( $this->option_key );

		$new_form_id = time();
		$settings = array(
			'page' => array(
				'settings' => array(
					'page_id' => $new_form_id,
					'title' => 'New Form',
					'alias' => 'form',
					'adminMenuTopItem' => 'current-theme',
					'showPageTitle' => 'true',
				),
				'elements' => array(),
			),
			'new_page_id' => $new_form_id,
			'settings' => array(
				'tabs' => true,
				'containers' => true,
				'fields' => true,
				'form_settings' => true
			),
		);

		$this->default_form_settings = $settings;
		$this->default_form_settings['page_json'] = json_encode( $settings['page'] );

		$this->resolutions = array(
			'title' => true,
			'alias' => true,
			'settings' => true,
			'options-tabs' => true,
			'options-containers' => true,
			'options-fields' => true,

		);
	}

	public function add_actions( $save_action, $save_custom_options_action ) {
		$this->save_action = $save_action;
		if ( $this->save_action == 'save_form_from_builder' ) {
			add_action( 'wp_ajax_save_form_from_builder', array( $this, 'save_form_from_builder' ) );
		}

		$this->save_custom_options_action = $save_custom_options_action;
		if ( $this->save_custom_options_action == 'save_custom_options' ) {
			add_action( 'wp_ajax_save_custom_options', array( $this, 'save_custom_options' ) );
		}

		add_action( 'admin_print_styles', array( $this, 'include_styles' ) );
		add_action( 'admin_print_scripts', array( $this, 'include_scripts' ) );
		add_action( 'wp_ajax_add_page_to_pages_list', array( $this, 'add_page_to_pages_list' ) );
	}

	// Include styles
	public function include_styles() {
		wp_register_style( 'formsbuilder-style', $this->css_path.'styles.css' );
	}

	// Include scripts
	public function include_scripts() {
		// Include builder js-plugin
		wp_register_script( 'jquery-tmpl', FRAMEWORK_URL.'framework/js/jquery.tmpl.min.js', array( 'jquery' ) );
		wp_register_script( 'jquery-cookie', FRAMEWORK_URL.'framework/js/jquery.cookie.js', array( 'jquery' ) );
		wp_register_script( 'jquery-ui-min', FRAMEWORK_URL.'framework/js/jquery-ui.min.js', array( 'jquery' ) );

		wp_register_script(
			'formsbuilder',
			$this->js_path.'formsbuilder.js',
			array(
				'jquery',
				'jquery-ui-min',
				'jquery-tmpl',
				'jquery-cookie',
			)
		);
		
		wp_register_script('ace', FRAMEWORK_URL.'framework/js/ace/src-noconflict/ace.js');
		
		global $translation_array;
		wp_localize_script( 'formsbuilder', 'translations_js', $translation_array );
	}
}

if ( is_admin() ) {
	// class to create Runway_Admin_Object's in Forms builder without standart actions
	class Settings_Object extends Runway_Admin_Object{

		public function load_objects() {}
		public function options_page() {}
		public function admin_menu() {}

	}
}

?>
