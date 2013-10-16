<?php

class Extm_Admin extends Runway_Admin_Object {

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );
		
	}

	function init() {

		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $Extm_Admin;
			$Extm_Admin->navigation = $_REQUEST['navigation'];
		}

	}

	function after_settings_init() {

	}

	function validate_sumbission() {
		// If all is OKq
		return true;

	}

	function load_objects() {

		global $extm;
		$this->data = $extm->load_objects();
		return $this->data;

	}

}
?>
