<?php

class Auth_Manager_Admin extends Runway_Admin_Object {
	public $auth, $runwaywp_url, $token;

	public function __construct($settings){
		parent::__construct($settings);

		$this->runwaywp_url = 'http://runwaywp.com/sites/main';

		// get settings
		$options = get_option($this->option_key);
		
		if($options){
			$this->auth = true;
			$this->token = $options;
		}
		else {
			$this->auth = false;
		}
		
		add_action('init', array($this, 'remove_old_options'));
	}

	function remove_old_options() {
		global $wpdb;
		$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%auth-manager%'");
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

	public function auth_user_login($login, $psw){
		
		$this->login = $login;
		$this->psw = $psw;
		
		if(isset($this->login, $this->psw)){		
			// build post
			
			$postdata = array(
				'login' => $this->login,
				'psw' => $this->psw,
				'site_url' => site_url()
			);

			$post_args = array(
				'method' => 'POST',
				'timeout' => 10,
				'body' => $postdata
			    );			

			$request_url = $this->runwaywp_url . 'wp-admin/admin-ajax.php?action=auth_user';

			$this->auth = false;
			$response = wp_remote_post($request_url, $post_args);
			if(is_a($response, 'WP_Error'))
				return $this->auth;
			
			$response_string = json_decode($response['body']);
			
			if($response['response']['code'] != '404' && json_last_error() === 0) {
				if($response_string->success === true) {
					$this->auth = true;
					update_option($this->option_key, $response_string->userToken);
				}
			}

			return $this->auth;
		}
	}
	
	public function auth_user_signout() {
		delete_option($this->option_key);
		$this->auth = false;
		return $this->auth;
	}

}
?>
