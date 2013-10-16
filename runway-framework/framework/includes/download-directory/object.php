<?php

class Directory_Settings_Object {

	public $downloads_dir, $extensions_server_url;

	function __construct() {

		$upload_dir = wp_upload_dir( );
		$this->downloads_dir = $upload_dir['basedir'].'/download_dir/';
		$this->extensions_server_url = 'http://beta.runwaywp.com/wp-admin/admin-ajax.php?action=';

	}

	function make_request( $url = '', $data = array() ) {

		$responce = json_decode( file_get_contents( $url, false ) );

		if ( isset( $responce->hash ) && $responce->hash == md5( $responce->data ) ) {
			return json_decode( base64_decode( $responce->data ) );
		} else {
			return false;
		}

	}

	function request_extensions_list( $data = array() ) {

		// return $this->make_request( home_url().'/wp-content/themes/runway-framework/framework/includes/download-directory/dummy/index.php', $data );
		return $this->make_request( FRAMEWORK_URL .'framework/includes/download-directory/dummy/index.php', $data );

	}
}

?>
