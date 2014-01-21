<?php
// unexpected issues cap
if ( !class_exists( 'WP_Customize_Control' ) ) {
	class WP_Customize_Control {

		public function __call( $name, $param ) {

			return false;

		}
	}
}

// field prototype class
class Data_Type extends WP_Customize_Control {

	public $type = 'data-type';
	public static $type_slug = 'data-type';
	public $label = '';

	public $page;
	public $field;

	public function __construct( $page, $field, $wp_customize = null, $alias = null, $params = null ) {

		$this->page = $page;
		$this->field = $field;

		if ( $wp_customize ) {
			$this->is_customize_theme_page = true;
			parent::__construct( $wp_customize, $alias, $params );
		} else {
			$this->is_customize_theme_page = false;
		}

		if ( method_exists( $this, 'assign_handlers' ) ) {
			$this->assign_handlers();
		}
	}

	public static function assign_actions_and_filters() {

	}

	public function save( $value = null ) {

		$page_options = get_option( $this->page->option_key );

		if ( !$value ) {
			$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
			$value = $submited_value->{$this->field->alias};
		}

		$page_options[$this->field->alias] = $value;

		$page_options['field_types'][$this->field->alias] = $this->type;

		update_option( $this->page->option_key, $page_options );
	}

	public function get_value() {

		$this->field->value = $this->page->get_val( $this->field->alias );		

		if ( is_array($this->field->value) && empty( $this->field->value ) ) {
			$this->field->value = ( isset( $this->field->values ) ) ? $this->field->values : ''; // error check for notice "Undefined property: stdClass::$values"
		}

		$this->field = apply_filters( self::$type_slug . '_get_value_filter', $this->field );
		return $this->field->value;
	}

	public static function render_settings() {

	}

	public static function data_type_register() { ?>

	<?php }

}

?>
