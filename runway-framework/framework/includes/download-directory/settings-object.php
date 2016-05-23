<?php

class Directory_Admin extends Runway_Admin_Object {

	public $downloads_dir, $extensions_server_url;

	// Add hooks & crooks
	function add_actions() {
		$upload_dir = wp_upload_dir( );
		$this->downloads_dir = $upload_dir['basedir'].'/downloads-directory/';
		$this->edd_dir = $upload_dir['basedir'].'/edd/';
		$this->extensions_server_url = 'http://runwaywp.com/sites/main/wp-admin/admin-ajax.php?action=';

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_get_extensions', array( $this, 'get_extensions' ) );
		add_action( 'wp_ajax_nopriv_get_extensions', array( $this, 'get_extensions' ) );
		add_action( 'wp_ajax_download_extension', array( $this, 'download_extension' ) );
		add_action( 'wp_ajax_nopriv_download_extension', array( $this, 'download_extension' ) );
	}

	function init() {
		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			//global $Directory_Admin;
			//$Directory_Admin->navigation = $_REQUEST['navigation'];
			$this->navigation = $_REQUEST['navigation'];
		}
	}

	function validate_sumbission() {

		// If all is OK
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
			//return json_decode( base64_decode( $responce->data ) );
			return json_decode( runway_base_decode( $responce->data ) );
		} else {
			return false;
		}

	}

	function request_extensions_list( $data = array() ) {

		return $this->make_request( FRAMEWORK_URL .'framework/includes/download-directory/dummy/index.php', $data );

	}

	function build_extensions_list() {
		global $settingshortname;

		$this->option_key = $settingshortname.'extensions-server';
		$this->server_extensions = get_option( $this->option_key );

		return $this->server_extensions;

	}

	function download_extension() {

		extract( $_REQUEST );

		if ( isset( $item ) ) {
			//$extension_file_name = (isset($_REQUEST['zip'])) ? $_REQUEST['zip']."/{$item}.zip" : $this->downloads_dir."/{$item}.zip";
			//$extension_file_name = $this->edd_dir."{$item}.zip";
			$extension_file_name = $this->edd_dir."{$item}";
			//$extension_file_name = file_exists( $extension_file_name )? $extension_file_name : "{$this->downloads_dir}{$item}.zip";

			$body = array();
			if ( file_exists( $extension_file_name ) ) {
				if(!function_exists('WP_Filesystem'))
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				WP_Filesystem();
				global $wp_filesystem;
				$content = $wp_filesystem->get_contents($extension_file_name);
				$content = runway_base_encode($content);
				$body['success'] = true;
				$body['content'] = $content;
				$body['error_message'] = '';
			} else {
				$body['success'] = false;
				$body['content'] = '';
				$body['error_message'] = sprintf( __('File <b>%s</b> not found on server', 'runway'), $item);
			}
			echo json_encode($body);
		}

		die();
	}

	function get_extensions() {

		extract( $_REQUEST );

		$extensions = $this->build_extensions_list();

		if ( isset( $search ) && !empty( $search ) ) {
			$search_keys = array( 'Name', 'Description', 'Author', 'Title', 'AuthorName' );
			$search_results = array();

			if( isset($extensions) && !empty($extensions) ) {
				foreach ( $extensions as $slug => $extension ) {
					foreach ( $extension as $key => $option ) {
						if ( in_array( $key, $search_keys ) ) {
							if ( strstr( strtolower( $option ), strtolower( $search ) ) != false ) {
								$search_results[$slug] = $extension;
							}
						}
					}
				}
			}

			$extensions = $search_results;
		}

		$on_page = 20;

		if ( !isset( $page ) && empty( $page ) ) {
			$page = 0;
		}
		$total = count( $extensions );

		$extensions = array_slice( $extensions, $page * $on_page, $on_page );

		echo json_encode( array( 'page' => $page, 'total_count' => $total, 'on_page' => $on_page, 'extensions' => $extensions ) );

		exit();

	}
}
?>
