<?php

class Directory_Admin extends Runway_Admin_Object {

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $Directory_Admin;
			$Directory_Admin->navigation = $_REQUEST['navigation'];
		}
	}

	function validate_sumbission() {

		// If all is OKq
		return true;

	}

	function load_objects() {
		global $Directory_Admin;
		$this->data = $Directory_Admin->load_objects();
		return $this->data;
	}

}
?>
