<?php
/*
 *  Based on the work of Henrik Melin and Kal Ström's "More Fields", "More Types" and "More Taxonomies" plugins.
 *  http://more-plugins.se/
*/

$runway_framework = 'RUNWAY_FRAMEWORK';

if ( !defined( $runway_framework ) ) {
	class Runway_Object {

		public $settings, $filter, $data_modified, $data_default;

		function __construct( $settings ) {
			$this->settings = $settings;
			if ( isset( $settings['alias'] ) && !empty( $settings['alias'] ) )
				$this->slug = sanitize_title( $settings['alias'] );
			else
				$this->slug = sanitize_title( $settings['name'] );
			$this->init( $settings );
			$this->filter = str_replace( '-', '_', sanitize_title( $this->settings['name'] ) ) . '_saved';
			$this->data_default = array();
			$this->data_modified = array();
			$this->data_loaded = array();
		}

		/**
		* This function is intentionally left blank
		*
		* Overwritten by indiviudal plugin admin objects, if needed.
		**/			
		function init( $settings ) { }		

		function object_to_array( $data ) {
			if ( is_object( $data ) ) $data = get_object_vars( $data );
			return is_array( $data ) ? array_map( array( $this, 'object_to_array' ), $data ) : $data;
		}

		function get_objects( $keys = array() ) {
			if ( empty( $this->data_loaded ) ) $this->data_loaded = $this->load_objects();
			if ( !empty( $keys ) ) {
				$ret = array();
				foreach ( $keys as $key ) {
					foreach ( (array) $this->data_loaded[$key] as $name => $var ) {
						$ret[$name] = $this->data_loaded[$key][$name];
					}
				}
				return $ret;
			}
			return $this->data_loaded;
		}

		function load_objects( $data = array() ) {
			$plugin = get_option( $this->settings['option_key'], array() );

			$data['_framework'] = $this->object_to_array( $plugin );
			if ( !$data['_framework'] ) $data['_framework'] = array();

			$saved = $this->saved_data();
			$data['_framework_saved'] = $this->object_to_array( $saved );
			if ( !$data['_framework_saved'] ) $data['_framework_saved'] = array();
			foreach ( (array) $this->data_modified as $key => $item ) {
				// Remove the defaults
				if ( array_key_exists( $key, (array) $this->data_default ) )
					unset( $this->data_modified[$key] );
				/*
				if (array_key_exists($key, $data['_framework']))
					unset($this->data_modified[$key]);
				if (array_key_exists($key, (array) $data['_framework_saved']))
					unset($this->data_modified[$key]);
				*/
			}

			$data['_other'] = $this->object_to_array( $this->data_modified );
			if ( !$data['_other'] ) $data['_other'] = array();

			$data['_default'] = $this->object_to_array( $this->data_default );
			if ( !$data['_default'] ) $data['_default'] = array();

			$this->data_loaded = $data;
			return $data;
		}

		function saved_data() {
			$data = array();
			$saved = array();
			$saved = apply_filters( $this->filter, $saved );
			foreach ( $saved as $key => $type ) {
				$data[$key] = $type;
				$data[$key]['file'] = true;
			}

			return $data;
		}
	}
				
}

if ( !is_callable( '__d' ) ) {
	function __d( $d ) {
		if ( !defined( 'THEME_FRAMEWORK_DEV' ) ) return false;
		if ( !$d ) return false;
		echo '<pre>';
		print_r( $d );
		echo '</pre>';
	}
}