<?php

class Directory_Admin extends Runway_Admin_Object {

	public $downloads_dir, $extensions_server_url;
	
	// Add hooks & crooks
	function add_actions() {
		$upload_dir = wp_upload_dir( );
		$this->downloads_dir = $upload_dir['basedir'].'/download_dir/';
		$this->extensions_server_url = 'http://runwaywp.com/sites/main/wp-admin/admin-ajax.php?action=';

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			//global $Directory_Admin;
			//$Directory_Admin->navigation = $_REQUEST['navigation'];
			$this->navigation = $_REQUEST['navigation'];
		}
	}

	function validate_sumbission() {

		// If all is OKq
		return true;

	}

	function load_objects() {
		// global $Directory_Admin;
		// $this->data = $Directory_Admin->load_objects();
		// return $this->data;
	}

	function make_request( $url = '', $data = array() ) {
		if(!function_exists('WP_Filesystem'))
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		WP_Filesystem();
		global $wp_filesystem;
				
		//$responce = json_decode( file_get_contents( $url, false ) );
		$responce = json_decode( $wp_filesystem->get_contents( $url, false ) );

		if ( isset( $responce->hash ) && $responce->hash == md5( $responce->data ) ) {
			return json_decode( base64_decode( $responce->data ) );
			//return json_decode( runway_base_decode( $responce->data ) );
		} else {
			return false;
		}

	}

	function request_extensions_list( $data = array() ) {

		return $this->make_request( FRAMEWORK_URL .'framework/includes/download-directory/dummy/index.php', $data );

	}

}
?>
