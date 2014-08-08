<?php

class Dashboard_Admin extends Runway_Admin_Object {

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );

	}

	function init() {
		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $Dashboard_Admin;
			$Dashboard_Admin->navigation = $_REQUEST['navigation'];
		}

		$api_link = 'http://para.llel.us/accounts/api/index.php';
		$this->url = network_admin_url('admin.php?page=dashboard#credits');
		$this->request = 'get_achievements';
		$this->token = '5d5a0dd456289d0c9e6070a86ef160b9';
		$this->sort = isset($_POST['sort'])? $_POST['sort'] : 'achievements_count';

		wp_enqueue_script('sort_credits-js', get_theme_root_uri() . '/ra/assets/js/sort_credits.js');

		$postdata = http_build_query(
		    array(
				'token' => $this->token,
				'request' => $this->request,
				'sort' => $this->sort
			)
		);

		$opts = array('http' =>
		    array(
		        'method'  => 'POST',
		        'header'  => 'Content-type: application/x-www-form-urlencoded',
		        'content' => $postdata
		    )
		);

		$context  = stream_context_create($opts);

		$this->credits = json_decode(file_get_contents($api_link, false, $context), true);		
	}

	function validate_sumbission() {

		// If all is OKq
		return true;

	}

	function load_objects() {
		global $dashboard;
		$this->data = $dashboard->load_objects();
		return $this->data;
	}

}
?>
