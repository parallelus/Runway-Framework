<?php

class Auth_Manager_Admin extends Runway_Admin_Object {
	public $auth, $login, $psw, $runwaywp_url;

	public function __construct($settings){
		parent::__construct($settings);

		$this->runwaywp_url = 'http://runwaywp.com/sites/main';
		//$this->runwaywp_url = 'http://wptest.loc/';

		// get settings
		$options = get_option($this->option_key);
		if($options){
			$this->auth = $options['auth'];
			$this->login = $options['login'];
			$this->psw = $options['psw'];
		}

		// if($this->auth == 'false'){
		// 	$this->auth_user_login();
		// }
	}

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );

	}

	function init() {
		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $auth_manager_admin;
			$auth_manager_admin->navigation = $_REQUEST['navigation'];
		}
	}

	function validate_sumbission() {

		// If all is OKq
		return true;

	}

	function load_objects() {
		global $auth_manager;
		$this->data = $auth_manager->load_objects();
		return $this->data;
	}

	public function set_user_credentials($login, $psw){
		if(isset($login, $psw)){
			$this->login = $login;
			$this->psw = $psw;
			update_option($this->option_key, array(
				'auth' => false,
				'login' => $login,
				'psw' => $psw,
			));
		}
		else return false;
	}

	public function auth_user_login(){
		if(isset($this->login, $this->psw)){		
			// build post
			$postdata = array(
				'login' => $this->login,
				'psw' => $this->psw,
			);

			$post_args = array(
				'method' => 'POST',
				'timeout' => 10,
				'body' => $postdata
			    );			

			$request_url = $this->runwaywp_url . 'wp-admin/admin-ajax.php?action=auth_user';

			$response = wp_remote_post($request_url, $post_args);

			if($response['body']){
				$this->auth = true;
			}
			else {
				$this->auth = false;
			}
			update_option($this->option_key, array(
				'auth' => $this->auth,
				'login' => $this->login,
				'psw' => $this->psw
			)); 			
			return $this->auth;
		}
	}

}
?>
