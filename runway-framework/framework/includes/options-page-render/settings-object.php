<?php

class Generic_Admin_Object extends Runway_Admin_Object {

	var $elements = null;

	// Add hooks & crooks
	function add_actions() {

		//include JS for drag and drop layout manager (cutsom jquery UI)
		add_action( 'admin_print_scripts-' . $this->parent_menu . '_page_' . $this->slug, array( &$this, 'load_admin_js' ) );

	}

	function after_settings_init() {
		/* nothing */
	}

	function do_error_message( $field_information ) {

		$message_template = null;

		$default_errors_messages = array(
			'url' => '%field_name% may be url',
			'email' => '%field_name% may be email',
			'alpha_only' => '%field_name% may be only letters',
			'alpha_num_only' => '%field_name% may be only letters or digits',
			'num_only' => '%field_name% may be only digits',
		);
		$default_required_messages = 'is required';

		if ( !$message_template ) {
			if ( isset( $field_information->validation ) && !empty( $field_information->validation) && !empty( $field_information->validationMessage) ) {
				$message_template = $field_information->title . ': '. $field_information->validationMessage;
			} else {
				$message_template = isset($default_errors_messages[$field_information->validation])? $default_errors_messages[$field_information->validation] : '';
			}
		}

		if(empty($field_information->value) && in_array($field_information->required, array('true', 'Yes') ) ) {
			$br = empty($message_template)? '' : '<br>';
			if(! empty($field_information->requiredMessage) )
				$message_template = $field_information->title . ': '. $field_information->requiredMessage . $br .$message_template;
			else
				$message_template = $field_information->title . ' ' . $default_required_messages . $br .$message_template;
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
			if ( isset($element->template) && $element->template == 'field' ) {
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

		if ( isset($_POST['action']) && $_POST['action'] == 'save' ){
			foreach ( $fields_settings as $key => $field_settings ) {
				// validation flag
				$is_valid = true;

				if ( isset( $_POST[$key] ) ) {
					$value = $_POST[$key];
					$field_settings->value = $_POST[$key];
				}

				// apply validation
				if ( has_filter( "{$field_settings->type}-before_validation" ) )
					$field_settings = do_action( "{$field_settings->type}-before_validation", $this, $field_settings );

				if ( has_filter( "{$field_settings->type}_validation" ) ) {
					$is_valid = do_action( "{$field_settings->type}_validation", $this, $field_settings );
				}

				if ( isset($field_settings->validation) && isset( $validation_rules[$field_settings->validation] ) &&
					!preg_match( $validation_rules[$field_settings->validation], $value ) ) {
					$is_valid = false;
				}

				if ( empty( $value ) ) {
					if ( isset($field_settings->required) && in_array($field_settings->required, array('true', 'Yes') ) ) {
						$is_valid = false;
					} else {
						$is_valid = ($is_valid == false)? false : true;
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
			$this->message = 'Validation error: <br>';
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
