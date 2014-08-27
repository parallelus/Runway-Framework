<?php

// fields classes autoload
global $fields_classes_loaded;

if ( !$fields_classes_loaded ) {
	foreach ( glob( get_template_directory() . '/framework/options-builder/fields/classes/*.php' ) as $filename ) {
		require_once $filename;
	}

	$fields_classes_loaded = true;
}

// fields template class and factory
class field {

	// class factory function
	public static function create( $options ) {

		// cast array to object
		if ( is_array( $options ) ) {
			$options = (object) $options;
		}

		// check if this type class exists
		// if exists return new field type class
		if ( isset( $options->type ) && class_exists( $options->type ) ) {
			return new $options->type( $options );
		}
		else {
			return null;
		}

	}

	function __construct( $options ) {

		// assign field options
		$this->options = $options;

	}

	function get_html() {
		ob_start();
		?>
				<div class="empty"><?php echo __('This is empty field', 'framework'); ?></div>

			<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}


}

?>
