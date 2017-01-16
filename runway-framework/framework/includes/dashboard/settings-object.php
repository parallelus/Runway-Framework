<?php

class Dashboard_Admin extends Runway_Admin_Object {

	// Add hooks & crooks
	public function add_actions() {

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_get_credits', array( $this, 'get_credits' ) );
		add_action( 'wp_ajax_nopriv_get_credits', array( $this, 'get_credits' ) );

	}

	public function init() {

		if ( isset( $_REQUEST['navigation'] ) && ! empty( $_REQUEST['navigation'] ) ) {
			global $Dashboard_Admin;
			$Dashboard_Admin->navigation = $_REQUEST['navigation'];
		}

		$api_link             = 'http://para.llel.us/accounts/api/index.php';
		$this->url            = esc_url_raw( admin_url( 'admin.php?page=dashboard#credits' ) );
		$this->request        = 'get_achievements';
		$this->token          = '5d5a0dd456289d0c9e6070a86ef160b9';
		$this->selectableSort = isset( $_POST['sort'] ) ? $_POST['sort'] : 'achievements_count';
		$this->perPage        = isset( $_POST['perPage'] ) ? $_POST['perPage'] : 20;
		$this->startPage      = isset( $_POST['startPage'] ) ? $_POST['startPage'] : 0;
		$this->state          = isset( $_POST['state'] ) ? $_POST['state'] : 0;

		if ( isset( $_POST['sort'] ) ) {
			if ( $_POST['sort'] == 'achievements_count_desc' ) {
				$this->sort     = 'achievements_count';
				$this->sortDest = 'desc';
			} else if ( $_POST['sort'] == 'achievements_count_asc' ) {
				$this->sort     = 'achievements_count';
				$this->sortDest = 'asc';
			} else if ( $_POST['sort'] == 'user_name_asc' ) {
				$this->sort     = 'username';
				$this->sortDest = 'asc';
			} else if ( $_POST['sort'] == 'user_name_desc' ) {
				$this->sort     = 'username';
				$this->sortDest = 'desc';
			}
		} else {
			$this->sort     = 'achievements_count';
			$this->sortDest = 'desc';
		}

		$args = array(
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'timeout' => 10,
			'body'    => array(
				'token'     => $this->token,
				'request'   => $this->request,
				'sort'      => $this->sort,
				'sortOrder' => $this->sortDest,
				'perPage'   => $this->perPage,
				'startPage' => $this->startPage,
				'state'     => $this->state
			)
		);

		$credits       = wp_remote_post( $api_link, $args );
		$this->credits = array();
		if ( ! is_wp_error( $credits ) && isset( $credits['body'] ) && $credits['body'] !== '[]' ) {
			$this->credits = json_decode( $credits['body'], true );
		}

		if ( isset( $this->credits['success'] ) && $this->credits['success'] ) {
			$this->total       = $this->credits['totalResults'];
			$this->currentPage = $this->startPage + 1;
		}

	}

	public function get_credits() {
		//TODO ajax query for credits page
	}

	public function validate_sumbission() {

		// If all is OKq
		return true;

	}

	public function load_objects() {

		global $dashboard;
		$this->data = $dashboard->load_objects();

		return $this->data;

	}

}
