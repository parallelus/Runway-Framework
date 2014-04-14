<?php
class Colorpicker_type extends Data_Type {

	public $type = 'colorpicker-type';
	public static $type_slug = 'colorpicker-type';
	public $label = 'Colorpicker';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
			extract( $vals );
		}

		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
	?>

	<label>
		<div class="customize-control-content">
		<?php if(isset($this->field->repeating) && $this->field->repeating == 'Yes'){
                    
			$this->get_value();
			if (isset($this->field->value) && is_array($this->field->value)) {
				foreach ($this->field->value as $key => $tmp_value) {
					if (is_string($key))
						unset($this->field->value[$key]);
				}
			}

			$count = isset($this->field->value) ? count((array) $this->field->value) : 1;
			if ($count == 0)
				$count = 1;
			?>
				<legend class="customize-control-title"><span><?php echo stripslashes($this->field->title) ?></span></legend>
			<?php
			for ($key = 0; $key < $count; $key++) {
				if (isset($this->field->value) && is_array($this->field->value))
					$repeat_value = (isset($this->field->value[$key])) ? $this->field->value[$key] : '';
				else
					$repeat_value = "";
			?>
				<input class="color-picker-hex custom-data-type" <?php echo $section; ?> 
					data-type="colorpicker-type" type="text" maxlength="7" <?php $this->link(); ?> 
					name="<?php echo $this->field->alias ?>[]" 
					value="<?php echo ( isset($repeat_value) && $repeat_value != '' ) ? $repeat_value : ''; ?>" />
				<a href="#" class="delete_colorpicker_field">Delete</a><br>
				<?php
			}

			$field = array(
				'field_name' => $this->field->alias,
				'type' => 'text',
				'class' => 'color-picker-hex custom-data-type',
				'data_section' => isset($this->page->section) ? $this->page->section : '',
				'data_type' => 'colorpicker-type',
				'after_field' => '',
				'value' => '#'
			);
			$this->enable_repeating($field);
			$this->wp_customize_js();
			?>
			<script type="text/javascript">
				(function () {

					var name = '<?php echo $this->field->alias; ?>';

					jQuery(function () {

						jQuery('.color-picker-hex.custom-data-type').wpColorPicker({ change: function () {

							setTimeout(function () {

								jQuery('.color-picker-hex.custom-data-type').trigger('change');

							}, 50);

						}});

					});

				})();
			</script>
		<?php
		} else { 
			$input_value = ( $vals != null ) ? $this->field->saved : $this->get_value();
			if(!is_string($input_value) && !is_numeric($input_value)) {
				if(is_array($input_value) && isset($input_value[0]))
					$input_value = $input_value[0];
				else
					$input_value = "";
			}
		?>
			<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
			<input class="color-picker-hex custom-data-type" <?php echo $section; ?> data-type="colorpicker-type" type="text" maxlength="7" <?php $this->link(); ?> name="<?php echo $this->field->alias ?>" value="<?php echo $input_value; ?>" />
			<script type="text/javascript">
				(function () {

					var name = '<?php echo $this->field->alias; ?>';

					jQuery(function () {

						jQuery('[name="'+name+'"]').wpColorPicker({ change: function () {

							setTimeout(function () {

								jQuery('[name="'+name+'"]').trigger('change');

							}, 50);

						}});

					});

				})();
			</script>
		<?php } ?>
		</div>
	</label> <?php

		do_action( self::$type_slug . '_after_render_content', $this );
	}

	public function save( $value = '' ) {
		if ( $value == '' ) {
			$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
			$value = $submited_value->{$this->field->alias};
		}
		
		if(is_string($value)) {
			if ( strstr( $value, '#' ) == false ) {
				$value = '#' . $value;
			}
		}
		else if(is_array($value)) {
			foreach($value as $tmp_key => $tmp_val) {
				if ( strstr( $tmp_val, '#' ) == false ) {
					$value[$tmp_key] = '#' . $tmp_val;
				}
			}
		}

		if(is_object($value)) {
			$value = "";
		}
		
		SingletonSaveCusomizeData::getInstance()->set_option($this->page->option_key);
		SingletonSaveCusomizeData::getInstance()->save_data($this->field->alias, $value, $this->type);

	}

	public function get_value() {

		$value = parent::get_value();
	
		if ( is_array( $value ) )
			$value = ( isset( $this->field->values ) ) ? $this->field->values : '';
		if ( strstr( $value, '#' ) === false ) {
			$value = '#' . $value;
		}

		return $value;

	}

	public static function render_settings() { ?>

		<script id="colorpicker-type" type="text/x-jquery-tmpl">

		    <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		<div class="settings-container">
		    <label class="settings-title">
			<?php echo __('Values', 'framework'); ?>:
			<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">
			<input name="values" value="${values}" class="settings-input color-picker" type="text" maxlength="7">
			<br><span class="settings-field-caption"></span>
		    </div>
		</div><div class="clear"></div>

		<div class="settings-container">
		    <label class="settings-title">
			<?php echo __('Required', 'framework'); ?>:
			<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">

			<label>
			    {{if required == 'Yes'}}
			    <input data-set="required" name="required" value="Yes" checked="true" type="checkbox">
			    {{else}}
			    <input data-set="required" name="required" value="Yes" type="checkbox">
			    {{/if}}
			    <?php echo __('Yes', 'framework'); ?>
			</label>

			<br><span class="settings-field-caption"><?php echo __('Is this a required field', 'framework'); ?>.</span><br>

			<input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

			<br><span class="settings-field-caption"><?php echo __('Optional. Enter a custom error message', 'framework'); ?>.</span>
		    </div>
		</div><div class="clear"></div>

		<!-- Repeating settings -->
		<div class="settings-container">
		    <label class="settings-title">
				<?php echo __('Repeating', 'framework'); ?>:
		    </label>
		    <div class="settings-in">
			<label class="settings-title"> 
			    {{if repeating == 'Yes'}}
				<input data-set="repeating" name="repeating" value="Yes" checked="true" type="checkbox">
			    {{else}}
				<input data-set="repeating" name="repeating" value="Yes" type="checkbox">
			    {{/if}}
			    <?php echo __('Yes', 'framework'); ?>
			</label>
			<br><span class="settings-title-caption"><?php echo __('Can this field repeat with multiple values', 'framework'); ?>.</span>
		    </div>
		</div><div class="clear"></div>

		<?php do_action( self::$type_slug . '_after_render_settings' ); ?>

	    </script>

	<?php }

	public static function data_type_register() { ?>

		<script type="text/javascript">

			function colorPickerInit() {

				setTimeout(function () {
					jQuery('.color-picker').wpColorPicker();
					jQuery('.settings-select').one('change', colorPickerInit());
				}, 200);

			}

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: 'Colorpicker',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>',
					onSettingsDialogOpen: function () {
						colorPickerInit();
					}
				});
			});

		</script>

	<?php }
    
	public function enable_repeating($field = array() ){
		if(!empty($field)) :
			extract($field);

			$add_id = 'add_'.$field_name;
			$del_id = 'del_'.$field_name;

			?>
			<div id="<?php echo $add_id; ?>">
				<a href="#">
					Add Field
				</a>
			</div>			

			<script type="text/javascript">
				(function($){
					$(document).ready(function(){
						var field = $.parseJSON('<?php echo json_encode($field); ?>');
                                                
						$('#<?php echo $add_id; ?>').click(function(e){
							e.preventDefault();
							var field = $('<input/>', {
								type: '<?php echo $type; ?>',
								class: '<?php echo $class; ?>',
								name: '<?php echo $field_name; ?>[]',
								value: ""
							})							
							.attr('data-type', '<?php echo $data_type; ?>')
							.attr('data-section', '<?php echo isset($data_section) ? $data_section : ""; ?>')
							.insertBefore($(this)).focus();

							field.click(function(e){
								e.preventDefault();
							});

							$('#header').focus();
							field.after('<br>');
							field.after('<span class="field_label"> <?php echo $after_field ?> </span>');
							field.next().after('<a href="#" class="delete_colorpicker_field">Delete</a>');
                                                        
							field.wpColorPicker({ change: function () {
								setTimeout(function () {
									field.trigger('change');
								}, 50);
							}});
                                                    
							if(typeof reinitialize_customize_instance == 'function') {
								reinitialize_customize_instance('<?php echo $field_name ?>');
							}
						});

						$('body').on('click', '.delete_colorpicker_field', function(e){
							e.preventDefault();
							$(this).prev('.field_label').remove();
							$(this).prev().remove();
							$(this).next('br').remove();
							$(this).remove();
                                                        
							if(typeof reinitialize_customize_instance == 'function') {
								reinitialize_customize_instance('<?php echo $field_name ?>');
							}
						});
                                                        
						if ( wp.customize ) {
							if(typeof reinitialize_customize_instance == 'function') {
								var api = wp.customize;
								api.bind('ready', function(){
									reinitialize_customize_instance('<?php echo $field_name ?>');
								});
							}
						}
					});
				})(jQuery);
		</script>
		<?php
	endif;
	}
} ?>
