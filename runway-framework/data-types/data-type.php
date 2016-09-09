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

    private static $customized_array_values = array();

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
		add_filter('data_type_transport', array("Data_Type", 'data_type_filter'), 10, 3);
	}

	public static function data_type_filter($type, $field_type, $field_alias) {
		//$type = "postMessage"; 				// uncomment if need use transport = postMessage

		return $type;
	}

	public function save( $value = null ) {

		if(is_a($value, 'WP_Customize_Settings') || is_a($value, 'WP_Customize_Setting'))
			$value = null;

		if(!isset($_REQUEST['customized'])) {
			$page_options = get_option( $this->page->option_key );
			if(is_object($value)) {
				$value = "";
			}

			$page_options[$this->field->alias] = $value;

			$page_options['field_types'][$this->field->alias] = $this->type;
			update_option( $this->page->option_key, $page_options );
		}
		else {
			$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
			$value = $submited_value->{$this->field->alias};

			if(is_object($value)) {
				$value = "";
			}

			SingletonSaveCusomizeData::getInstance()->set_option($this->page->option_key);
			SingletonSaveCusomizeData::getInstance()->save_data($this->field->alias, $value, $this->type);
		}
	}

	public function sanitize_value( $value ) {
		return $value;
	}

	public function get_value() {

		$this->field->value = $this->page->get_val( $this->field->alias );

		if ( is_array($this->field->value) && empty( $this->field->value ) ) {
			$this->field->value = ( isset( $this->field->values ) ) ? $this->field->values : ''; // error check for notice "Undefined property: stdClass::$values"
		}

		if(is_object($this->field->value)) {
			$this->field->value = "";
		}

		$this->field = apply_filters( self::$type_slug . '_get_value_filter', $this->field );
		return $this->field->value;
	}

        public function link( $setting_key = 'default' ) {
		if ( ! isset( $this->settings[ $setting_key ] ) )
			return '';

		if(isset($this->field->repeating) && $this->field->repeating == 'Yes')
			echo 'data-customize-setting-link="' . esc_attr( $this->settings[ $setting_key ]->id ). '[]"';
		else
			echo 'data-customize-setting-link="' . esc_attr( $this->settings[ $setting_key ]->id ) . '"';
        }

	public function return_link($setting_key = 'default') {
		if ( ! isset( $this->settings[ $setting_key ] ) )
			return '';

		if(isset($this->field->repeating) && $this->field->repeating == 'Yes')
			return 'data-customize-setting-link="' . esc_attr( $this->settings[ $setting_key ]->id ). '[]"';
		else
			return 'data-customize-setting-link="' . esc_attr( $this->settings[ $setting_key ]->id ) . '"';
	}

	public static function render_settings() {

	}

	public static function add_data_conditional_display($field) {

		$data = '';
		if( isset($field->conditionalAlias) && !empty($field->conditionalAlias) ) {
			$data.= 'data-conditionalAlias="' . $field->conditionalAlias . '" ';
			if( isset($field->conditionalValue) && !empty($field->conditionalValue) )
				$data.= 'data-conditionalValue="' . $field->conditionalValue . '" ';
			if( isset($field->conditionalAction) && !empty($field->conditionalAction) )
				$data.= 'data-conditionalAction="' . $field->conditionalAction . '" ';
		}

		return $data;
	}

	public static function add_data_conditional_display_repeating($data, $field) {

		if( isset($field->conditionalAlias) && !empty($field->conditionalAlias) ) {
			$data['data-conditionalAlias'] = $field->conditionalAlias;
			if( isset($field->conditionalValue) && !empty($field->conditionalValue) )
				$data['data-conditionalValue'] = $field->conditionalValue;
			if( isset($field->conditionalAction) && !empty($field->conditionalAction) )
				$data['data-conditionalAction'] = $field->conditionalAction;
		}

		return $data;
	}

	public static function render_conditional_display() { ?>

		    <div class="settings-container section-heading">
		    	<p class="settings-title"><?php echo __('Conditional Display', 'runway'); ?></p>
		    	<p class="settings-field-caption"><?php echo __('Use the value of another option to conditionally show or hide this option.', 'runway'); ?></p>
		    	<div class="clear"></div>
		    </div>

		    <div class="settings-container">
		    	<label class="settings-title"><?php echo __('Control alias', 'runway'); ?>:
		        </label>
		        <div class="settings-in">
		            <input type="text" name="conditionalAlias" value="${conditionalAlias}" />
		            <span class="settings-field-caption"><?php echo __('Alias of the control option.', 'runway'); ?></span>
		        </div>
		        <div class="clear"></div>
		    </div>

		    <div class="settings-container">
		    	<label class="settings-title"><?php echo __('Control value', 'runway'); ?>:
		        </label>
		        <div class="settings-in">
		            <input type="text" name="conditionalValue" value="${conditionalValue}" />
		            <span class="settings-field-caption"><?php echo __('Value of the control option to trigger display.', 'runway'); ?></span>
		        </div>
		        <div class="clear"></div>
		    </div>

			<div class="settings-container">
				<label class="settings-title"><?php echo __('Display action', 'runway'); ?>:
				</label>
				<div class="settings-in">
					<?php if( isset( $conditionalAction ) ): ?>
							{{if conditionalAction == 'show'}}
				            	<label><input data-set="conditionalAction" name="conditionalAction" value="show" checked="true" type="radio"><?php echo __('Show', 'runway'); ?></label><br>
				            	<label><input data-set="conditionalAction" name="conditionalAction" value="hide" type="radio"><?php echo __('Hide', 'runway'); ?></label>
				            {{else}}
				            	<label><input data-set="conditionalAction" name="conditionalAction" value="show" type="radio"><?php echo __('Show', 'runway'); ?></label><br>
				            	<label><input data-set="conditionalAction" name="conditionalAction" value="hide" checked="true" type="radio"><?php echo __('Hide', 'runway'); ?></label>
				            {{/if}}
					<?php else: ?>
			            	<label><input data-set="conditionalAction" name="conditionalAction" value="show" type="radio"><?php echo __('Show', 'runway'); ?></label><br>
			            	<label><input data-set="conditionalAction" name="conditionalAction" value="hide" checked="true" type="radio"><?php echo __('Hide', 'runway'); ?></label>
					<?php endif; ?>	
		            <span class="settings-field-caption"><?php echo __('The action triggered by the conditional value.', 'runway'); ?></span>
				</div>
				<div class="clear"></div>
			</div> <?php
	}

	public static function data_type_register() { ?>

	<?php }

	public function enable_repeating($field = array() ){
		if(!empty($field)) :
			extract($field);

		$add_id = 'add_'.$field_name;
		$del_id = 'del_'.$field_name;

		?>
			<div id="<?php echo esc_attr($add_id); ?>">
				<a href="#">
					Add Field
				</a>
			</div>

			<script type="text/javascript">
				(function($){
					$(document).ready(function(){
						var field = $.parseJSON('<?php echo json_encode($field); ?>');
						$('#<?php echo esc_js($add_id); ?>').click(function(e){
							e.preventDefault();
							var field = $('<input/>', {
								type: '<?php echo esc_js($type); ?>',
								class: '<?php echo esc_js($class); ?>',
								name: '<?php echo esc_js($field_name); ?>[]',
								value: ""
							})
							.attr('data-type', '<?php echo esc_js($data_type); ?>')
							.attr('data-section', '<?php echo isset($data_section) ? esc_js($data_section) : ""; ?>')
							.insertBefore($(this)).focus();

							field.click(function(e){
								e.preventDefault();
							});

							$('#header').focus();
							field.after('<br>');
							field.after('<span class="field_label"> <?php echo esc_js($after_field) ?> </span>');
							field.next().after('<a href="#" class="delete_field"><?php echo __('Delete', 'runway'); ?></a>');

							if(typeof reinitialize_customize_instance == 'function') {
								reinitialize_customize_instance('<?php echo esc_js($field_name) ?>');
							}
						});

						$('body').on('click', '.delete_field', function(e){
							e.preventDefault();
							$(this).prev('.field_label').remove();
							$(this).prev('input').remove();
							$(this).next('br').remove();
							$(this).remove();

							if(typeof reinitialize_customize_instance == 'function') {
								reinitialize_customize_instance('<?php echo esc_js($field_name) ?>');
							}
						});

						if ( wp.customize ) {
							if(typeof reinitialize_customize_instance == 'function') {
								var api = wp.customize;
								api.bind('ready', function(){
									reinitialize_customize_instance('<?php echo esc_js($field_name) ?>');
								});
							}
						}
					});
				})(jQuery);
			</script>
		<?php
		endif;
	}


        public function wp_customize_js(){
		?>
		<script type="text/javascript">
			(function($){
				$('body').on('change', 'input[name="<?php echo esc_js($this->field->alias);?>[]"]', function(){
					reinitialize_customize_instance('<?php echo esc_js($this->field->alias);?>');
				});
			})(jQuery);

			if(typeof reinitialize_customize_instance !== 'function') {
				function reinitialize_customize_instance(alias) {
					(function($){
						if ( wp.customize ) {
						var values_array = [];
						alias = alias.replace(/\[\d*\]$/, "");
						$('input[name="'+alias+'[]"]').each(function(){
							values_array.push($(this).val());
						});

						var api = wp.customize;
						api.instance(alias).set(values_array);
					}
					})(jQuery);
				}
			}
		</script>
		<?php
	}
}

class SingletonSaveCusomizeData {
	private static $instance;
	private static $data;
	private static $key;

	private function __construct(){}
	private function __clone()    {}
	private function __wakeup()   {}

	public static function getInstance() {
		if ( empty(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function save_data($alias, $value, $type) {
		self::$data[$alias] = $value;
		self::$data['field_types'][$alias] = $type;

		update_option( self::$key, self::$data );
	}

	public function set_option($key) {
		if(self::$key !== $key)
		{
			self::$data = get_option( $key );
			self::$key = $key;
		}
	}
}


?>