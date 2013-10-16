<?php

class Reports_Admin_Object extends Runway_Admin_Object {


	// Add hooks & crooks
	function add_actions() {
		add_action( 'init', array( $this, 'init' ) );		
	}	

	function init() {

		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $reports_admin;
			$reports_admin->navigation = $_REQUEST['navigation'];
		}
	}

	function after_settings_init() {
		/* nothing */
	}

	function validate_sumbission() {        
					
		return true;
		
	}
	
	function load_objects() {
		
	}

}
?>