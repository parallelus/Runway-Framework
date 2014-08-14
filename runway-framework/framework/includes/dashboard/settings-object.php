<?php

class Dashboard_Admin extends Runway_Admin_Object {

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_get_credits', array( $this, 'get_credits') );
		add_action( 'wp_ajax_nopriv_get_credits', array( $this, 'get_credits') );
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
		$this->selectableSort = isset($_POST['sort'])? $_POST['sort'] : 'achievements_count';
		$this->perPage = isset($_POST['perPage']) ? $_POST['perPage'] : '5';
		$this->startPage = isset($_POST['startPage']) ? $_POST['startPage'] : '0';
		
		if(isset($_POST['sort'])) {
			if($_POST['sort'] == 'achievements_count_desc') {
				$this->sort = 'achievements_count';
				$this->sortDest = 'desc';
			}
			else if($_POST['sort'] == 'achievements_count_asc'){ 
				$this->sort = 'achievements_count';
				$this->sortDest = 'asc';
			}
			else if($_POST['sort'] == 'user_name_asc'){ 
				$this->sort = 'user_name';
				$this->sortDest = 'asc';
			}
			else if($_POST['sort'] == 'user_name_desc'){ 
				$this->sort = 'user_name';
				$this->sortDest = 'desc';
			}
		} else {
			$this->sort = 'achievements_count';
			$this->sortDest = 'desc';
		}
		$this->perPage = isset($_POST['perPage'])? $_POST['perPage'] : 5;

		wp_enqueue_script('sort_credits-js', get_theme_root_uri() . '/ra/assets/js/sort_credits.js');

		$postdata = http_build_query(
		    array(
				'token' => $this->token,
				'request' => $this->request,
				'sort' => $this->sort,
				'sortDest' => $this->sortDest,
				'perPage' => $this->perPage,
				'startPage' => $this->startPage
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
		if($this->credits['success']) {
			$this->total = $this->credits['totalResults'];
			$this->currentPage = $this->startPage;
		}
	}
	
	function get_credits() {
		//TODO ajax query for credits page
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
