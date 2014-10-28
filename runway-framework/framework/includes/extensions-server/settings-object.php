<?php

class Server_Admin extends Runway_Admin_Object {

	public $option_key;
	public $server_extensions;

	function __construct($settings) {

		parent::__construct($settings);

		$this->option_key = $settings['option_key'];		
		$this->server_extensions = get_option( $this->option_key );

		// folders settings
		$upload_dir = wp_upload_dir( );
		$this->sources_dir = $upload_dir['basedir'].'/downloads-sources/';
		if(!file_exists($this->sources_dir)){
			mkdir($this->sources_dir);
		}

		$this->downloads_dir = $upload_dir['basedir'].'/downloads-directory/';
		if(!file_exists($this->downloads_dir)){
			mkdir($this->downloads_dir);
		}

		// set actions
		// add_action( 'wp_ajax_nopriv_get_extensions', array( &$this, 'get_extensions' ) );
		// add_action( 'wp_ajax_nopriv_download_extension', array( &$this, 'download_extension' ) );

		// add_action( 'wp_ajax_get_extensions', array( &$this, 'get_extensions' ) );
		// add_action( 'wp_ajax_download_extension', array( &$this, 'download_extension' ) );

		// ajax URL (tmp)
		$this->ajax_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?action=';
	}

	// extract extention data from load.php file header
	function get_extension_data( $file_data ) {

		$default_headers = array(
			'Name' => 'Extension Name',
			'ExtensionURI' => 'Extension URI',
			'Version' => 'Version',
			'Description' => 'Description',
			'Author' => 'Author',
			'AuthorURI' => 'Author URI',
			'TextDomain' => 'Text Domain',
			'DomainPath' => 'Domain Path',
			'Network' => 'Network',
			'_sitewide' => 'Site Wide Only',
			'DepsExts' => 'Dependence Extensions',
		);
		$ext_data = array();
		$ext_data = $this->get_file_data( $file_data, $default_headers );

		// Site Wide Only is the old header for Network
		if ( !$ext_data['Network'] && $ext_data['_sitewide'] ) {
			_deprecated_argument( __FUNCTION__, '3.0', sprintf( __( 'The <code>%1$s</code> plugin header is deprecated. Use <code>%2$s</code> instead.', 'framework' ), 'Site Wide Only: true', 'Network: true' ) );
			$ext_data['Network'] = $ext_data['_sitewide'];
		}
		$ext_data['Network'] = ( 'true' == strtolower( $ext_data['Network'] ) );
		unset( $ext_data['_sitewide'] );

		if ( $ext_data['DepsExts'] ) {
			$ext_data['DepsExts'] = explode( ',', $ext_data['DepsExts'] );
		}

		return $ext_data;
	}

	function get_file_data( $file_data, $default_headers ){
		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );

		if ( isset($context) && $context && $extra_headers = apply_filters( "extra_{$context}_headers", array() ) ) {
			$extra_headers = array_combine( $extra_headers, $extra_headers ); // keys equal values
			$all_headers = array_merge( $extra_headers, (array) $default_headers );
		} else {
			$all_headers = $default_headers;
		}

		foreach ( $all_headers as $field => $regex ) {
			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
				$all_headers[ $field ] = _cleanup_header_comment( $match[1] );
			else
				$all_headers[ $field ] = '';
		}

		return $all_headers;
	}

	// Add hooks & crooks
	function add_actions() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {

		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			$this->navigation = $_REQUEST['navigation'];
		}
	}

	function validate_sumbission() {

		// If all is OKq
		return true;

	}

	function load_objects() {
		global $Directory_Admin;
		$this->data = $Directory_Admin->load_objects();
		return $this->data;
	}

}
?>
