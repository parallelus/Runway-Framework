<?php

class Auth_Manager_Admin extends Runway_Admin_Object {
	public $auth, $login, $psw, $runwaywp_url;

	public function __construct($settings){
		parent::__construct($settings);

		$this->runwaywp_url = 'http://runwaywp.com/';

		// get settings
		$options = get_option($this->option_key);
		if($options){
			$this->auth = $options['auth'];
			$this->login = $options['login'];
			$this->psw = $options['psw'];
		}
		
		if(!$this->auth){
			$this->auth_user();
		}
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
			update_option($this->option_key, array(
				'auth' => false,
				'login' => $login,
				'psw' => $psw,
			));
		}
		else return false;
	}

	public function auth_user(){
		if(isset($this->login, $this->psw)){		
			// build post
			$postdata = http_build_query(
				array(
					'login' => $this->login,
					'psw' => $this->psw,
				)
			);

			// // set request options
			$opts = array( 
	           'http' => array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata,
				)
			);

			// // get context
			$context  = stream_context_create( $opts );

			$request_url = $this->runwaywp_url . 'wp-admin/admin-ajax.php?action=auth_user';

			// execute query convert/result			
			$file_headers = @get_headers($request_url);
			$exists = ( isset($file_headers[0] ) )? true : false;

			$responce = array();
			if( $exists ){
				$responce = json_decode( file_get_contents( $url . '/wp-admin/admin-ajax.php?action=sync', false, $context ), true );
			}			

			if(!empty($responce)){
				$this->auth = true;
				update_option($this->option_key, array(
					'auth' => $this->auth,
					'login' => $this->login,
					'psw' => $this->psw,
				));

				return true;
			}
			else return false;
		}
	}

}
?>
