<?php

class Generic_Admin_Object extends Runway_Admin_Object {

	var $elements = null;

	// Add hooks & crooks
	function add_actions() {

		//include JS for drag and drop layout manager (cutsom jquery UI)
		add_action( 'admin_print_scripts-' . $this->parent_menu . '_page_' . $this->slug, array( $this, 'load_admin_js' ) );

	}

	function after_settings_init() {
		/* nothing */
	}

	function do_error_message( $field_information ) {

		$message_template = null;

		$default_errors_messages = array(
			'url' => '%field_name% ' . __('may be url', 'framework'),
			'email' => '%field_name% ' . __('may be email', 'framework'),
			'alpha_only' => '%field_name% ' . __('may be only letters', 'framework'),
			'alpha_num_only' => '%field_name% ' . __('may be only letters or digits', 'framework'),
			'num_only' => '%field_name% ' . __('may be only digits', 'framework'),
		);
		$default_required_messages = __('is required', 'framework');

		if ( !$message_template ) {
			if ( isset( $field_information->validation ) && !empty( $field_information->validation ) && !empty( $field_information->validationMessage ) ) {
				$message_template = $field_information->title . ': '. $field_information->validationMessage;
			} else {
				$message_template = isset( $default_errors_messages[$field_information->validation] )? $default_errors_messages[$field_information->validation] : '';
			}
		}

		if(isset($field_information->repeating) && $field_information->repeating === 'Yes' && is_array($field_information->value)) {
			$hasEmpty = false;
			
			foreach($field_information->value as $tmp_key => $tmp_val) {
				if($field_information->type === 'checkbox-type') {
					if(is_array($tmp_val)) {
						foreach($tmp_val as $tmp_checkbox_sub_key => $tmp_checkbox_sub_value) {
							if(empty($tmp_checkbox_sub_value)) { 
								$hasEmpty = true;
							}
						}
					}
				} else {
					if(empty($tmp_val)) { 
						$hasEmpty = true;
					}
				}
			}
			
			if($hasEmpty) {
				$br = empty( $message_template )? '' : '<br>';
				if ( ! empty( $field_information->requiredMessage ) )
					$message_template = $field_information->title . ': '. $field_information->requiredMessage . $br .$message_template;
				else
					$message_template = $field_information->title . ' ' . $default_required_messages . $br .$message_template;
			}
		} else {
			if ( empty( $field_information->value ) && in_array( $field_information->required, array( 'true', 'Yes' ) ) ) {
				$br = empty( $message_template )? '' : '<br>';
				if ( ! empty( $field_information->requiredMessage ) )
					$message_template = $field_information->title . ': '. $field_information->requiredMessage . $br .$message_template;
				else
					$message_template = $field_information->title . ' ' . $default_required_messages . $br .$message_template;
			}
		}

		$vars = array(
			'field_name' => $field_information->title,
		);

		foreach ( $vars as $key => $value ) {
			$message_template = preg_replace( '/%'.$key.'%/' , $value , $message_template );
		}

		return $message_template;
	}

	function validate_sumbission() {

		$fields_settings = array();
		foreach ( $this->elements as $element ) {
			if ( isset( $element->template ) && $element->template == 'field' ) {
				$fields_settings[$element->alias] = $element;
			}
		}
		$index = $this->option_key;
		$navigation = $index;

		// Save settings - setup
		if ( $this->navigation == $navigation ) {
			// check for post back "submit" action
			if ( !$_POST ) return false;
			// Set the index
			$_POST['index'] = $index; //'design_setting';
		}

		// Check for data, if none load the defaults (only necessary for admin home screen)
		$verifyData = $this->get_val( $index, '_framework' ); // has the page been saved before, ever?
		if ( empty( $verifyData ) && $this->action != 'save' ) {
			// no data saved and we're not saving now so... load default data
			$this->action = 'add';
			$this->keys = array( '_framework', $this->add_key );
		} else {
			// otherwise, set the key to the default (for admin home)
			$this->keys = array( '_framework', $index );
		}

		$validation_rules = array(
			'url' => '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
			'email' => '/[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})/i',
			'alpha_only' => '/^[A-Za-z]+$/',
			'alpha_num_only' => '/^[A-Za-z0-9]+$/',
			'num_only' => '/^[0-9]+$/',
		);

		// validation errors list
		$errors = array();
		// loop submited data

		if ( isset( $_POST['action'] ) && $_POST['action'] == 'save' ) {
			foreach ( $fields_settings as $key => $field_settings ) {
				// validation flag
				$is_valid = true;

				if ( isset( $_POST[$key] ) ) {
					$value = $_POST[$key];
					$field_settings->value = $_POST[$key];
				} else {
					$value = null;
					$field_settings->value = $value;
				}
				
				if(is_object($value)) {
					$value = '';
					$field_settings->value = '';
				}
				
				if($field_settings->type === 'checkbox-type') {
					$value = $this->updateRepeatingCheckbox($value);
					$field_settings->value = $value;
				}
				if(($field_settings->type === 'radio-buttons' || $field_settings->type === 'radio-buttons-image')) {
					$value = $this->updateRepeatingRadio($value);
					$field_settings->value = $value;
				}

				// apply validation
				if ( has_filter( "{$field_settings->type}-before_validation" ) )
					$field_settings = do_action( "{$field_settings->type}-before_validation", $this, $field_settings );

				if ( has_filter( "{$field_settings->type}_validation" ) ) {
					$is_valid = do_action( "{$field_settings->type}_validation", $this, $field_settings );
				}
				
				if(isset($field_settings->repeating) && $field_settings->repeating === 'Yes' && is_array($value)) {
					if(isset( $field_settings->validation ) && isset( $validation_rules[$field_settings->validation] )) {
						foreach($value as $tmp_key => $tmp_val) {
							if(is_string($tmp_val) && !preg_match( $validation_rules[$field_settings->validation], $tmp_val )) {
								$is_valid = false;
							}
						}
					}
					foreach($value as $tmp_key => $tmp_val) {
						if($field_settings->type === 'checkbox-type') {
							if(is_array($tmp_val)) {
								foreach($tmp_val as $tmp_checkbox_sub_key => $tmp_checkbox_sub_value) {
									if(empty($tmp_checkbox_sub_value)) { 
										if ( isset( $field_settings->required ) && in_array( $field_settings->required, array( 'true', 'Yes' ) ) ) {
											$is_valid = false;
										} else {
											$is_valid = ( $is_valid == false )? false : true;
										}
									}
								}
							}
						} else {
							if(empty($tmp_val)) { 
								if ( isset( $field_settings->required ) && in_array( $field_settings->required, array( 'true', 'Yes' ) ) ) {
									$is_valid = false;
								} else {
									$is_valid = ( $is_valid == false )? false : true;
								}
							}
						}
					}
				} else {
					if ( isset( $field_settings->validation ) && isset( $validation_rules[$field_settings->validation] ) &&
						!preg_match( $validation_rules[$field_settings->validation], $value ) ) {
						$is_valid = false;
					}

					if ( empty( $value ) ) {
						if ( isset( $field_settings->required ) && in_array( $field_settings->required, array( 'true', 'Yes' ) ) ) {
							$is_valid = false;
						} else {
							$is_valid = ( $is_valid == false )? false : true;
						}
					}
				}

				// if not valid data make message about this
				if ( !$is_valid ) {
					//                debug($field_settings->type);
					$errors[] = $this->do_error_message( $field_settings ) . '<br>';
				}
			}
		}

		$is_error = ( count( $errors ) == 0 ) ? FALSE : TRUE;

		if ( $is_error ) {
			// output validation messages
			$this->message = __('Validation error', 'framework').': <br>';
			foreach ( $errors as $error ) {
				$this->message .= $error;
			}
			return false;
		}

		// This might convert into a method for calling all the validate functions
		// attached to the inputs, or it could become unnecessary.

		// If all is OK
		return true;

	}
        
	function save_data( $data = array() ) {
        
		if (empty($data))
			$data = $this->data['_framework'];
		if ($this->dynamic && isset($data[$this->option_key]))
			$data = $data[$this->option_key];
		
		if (is_array($data) && isset($data['field_types'])) {
			foreach ($data['field_types'] as $field_type_key => $field_type_value) {
				switch ($field_type_value) {
					case "checkbox-type":
						if (isset($data[$field_type_key])) {
							$data[$field_type_key] = $this->updateRepeatingCheckbox($data[$field_type_key]);
						}
						break;

					case "radio-buttons-image":
					case "radio-buttons":
						if (isset($data[$field_type_key])) {
							$data[$field_type_key] = $this->updateRepeatingRadio($data[$field_type_key]);
						}
						break;
				}
			}
		}

		update_option( $this->option_key, $data );
	}
	
	//function for correct saving repeated checkboxes
	private function updateRepeatingCheckbox($field_values) {
		if(is_array($field_values)) {
			foreach($field_values as $checkbox_type_key => $checkbox_type_value) {
				if(is_array($checkbox_type_value)) {
					foreach($field_values[$checkbox_type_key] as $sub_checkbox_type_key => $sub_checkbox_type_value) {
						if($sub_checkbox_type_value === 'false') {
						    unset($field_values[$checkbox_type_key][$sub_checkbox_type_key]);
						}
					}
					if(count($field_values[$checkbox_type_key]) == 0) {
						$field_values[$checkbox_type_key][0] = '';
					}
				}
			}
		} else {
			if(is_string($field_values) && $field_values === 'false') {
				$field_values = null;
			}
		}
		return $field_values;
	}
	
	//function for correct saving repeated radiobuttons
	private function updateRepeatingRadio($field_values) {
		if(is_array($field_values)) {
			foreach($field_values as $radio_type_key => $radio_type_value) {
				if ($radio_type_value === 'false') {
					$field_values[$radio_type_key] = "";
				}
			}
		} else {
			if(is_string($field_values) && $field_values === 'false') {
				$field_values = null;
			}
		}
		return $field_values;
	}

	// Setup the data reference for this page/area
	// We've made this generic so it should work for all pages now.
	function load_objects() {

		// Get the page id/alias
		$alias = $_GET['page'];

		$page_options = $GLOBALS['page_options'];
		$current = $page_options[$alias];

		$this->elements = $current['elements'];

		// Retrieve the global objects
		global ${$current['object']};

		// Get the data
		$this->data = ${$current['object']}->load_objects();

		return $this->data;

	}

	function load_admin_js() {
		/* nothing */
	}

}

/*


*/

?>
