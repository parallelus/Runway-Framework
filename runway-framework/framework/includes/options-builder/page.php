<?php

require_once 'fields/field.php';

class page {

	private $data = null;

	function __construct( $json ) {

		$this->data = json_decode( $json );

	}

	// build fields
	function build_fields( $fields ) {

		foreach ( $fields as $key => $field ) {
			if ( $key != 'none' ) {
				$this->data->elements->$key->type = 'input';

				field::create( $this->data->elements->$key )->get_html();
			}
		}
	}

	// create containers
	function build_containers( $containers ) {

		foreach ( $containers as $key => $container ) {
			if ( $key != 'none' ) {
				$this->debug( $this->data->elements->$key );
				$this->build_fields( $container );
				$this->debug( $this->data->elements->$key );
			}
		}

	}

	// create tabs containers
	function build_tabs( $tabs ) {

		foreach ( $tabs as $key => $tab ) {
			if ( $key != 'none' ) {
				$this->debug( $this->data->elements->$key );
				$this->build_containers( $tab );
				$this->debug( $this->data->elements->$key );
			}
		}

	}

	function build_page( $order ) {

		// start bufer
		ob_start();

		$this->build_tabs( $order );

		$page = ob_get_contents();
		ob_end_clean();

		return $page;

	}

	function get_html() {

		$page = $this->build_page( $this->data->sortOrder );

		echo $page;
	}
}

?>
