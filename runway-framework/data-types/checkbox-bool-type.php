<?php
class Checkbox_bool_type extends Data_Type {

	public $type = 'checkbox-bool-type';
	public static $type_slug = 'checkbox-bool-type';
	public $label = 'Checkbox (true / false)';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
		}
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
		if(isset($this->field->repeating) && $this->field->repeating == 'Yes'):
			$this->get_value();
        
		if (isset($this->field->value) && is_array($this->field->value)) {
			foreach ($this->field->value as $key => $tmp_value) {
				if (is_string($key))
					unset($this->field->value[$key]);
			}
		}

		$count = isset($this->field->value) ? count((array)$this->field->value) : 1;
		if($count == 0) 
			$count = 1;
		?>
		<fieldset>
			<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
                        
		<?php for( $key = 0; $key < $count; $key++ ) { ?>
			<input <?php $this->link(); ?> class="input-check custom-data-type" <?php echo $section; ?> data-type="checkbox-bool-type" type="checkbox" value="true" name="<?php echo $this->field->alias ?>[]" <?php  if ( isset($this->field->value[$key]) && $this->field->value[$key] == 'true' ) echo 'checked '; ?> /> 
			<span class="field_label"><?php _e( 'Yes', 'framework' ) ?></span>
                                
			<a href="#" class="delete_checkbox_bool_field">Delete</a><br>
		<?php } ?>        
		<?php 
			$field = array(
				'field_name' => $this->field->alias,
				'type' => 'checkbox',
				'class' => 'input-check custom-data-type',
				'data_section' =>  isset( $this->page->section ) ? $this->page->section : '',
				'data_type' => 'checkbox-bool-type',
				'after_field' => __( 'Yes', 'framework' )
			);
			$this->enable_repeating($field); 
			$this->wp_customize_js();
		?>
		</fieldset>
		<?php
		else:
		?>
			<fieldset>
				<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
				<input type="hidden" value="false" name="<?php echo $this->field->alias ?>"  />
				<label>
					<input <?php $this->link(); ?> class="input-check custom-data-type" <?php echo $section; ?> data-type="checkbox-bool-type" type="checkbox" value="true" name="<?php echo $this->field->alias ?>" <?php  if ( $this->get_value() == 'true' ) echo 'checked '; ?> /> <?php _e( 'Yes', 'framework' ) ?>
				</label>
			</fieldset> 
		<?php
		endif;

		do_action( self::$type_slug . '_after_render_content', $this );
	}	

	public static function render_settings() { ?>

		<script id="checkbox-bool-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __('Value', 'framework'); ?>:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">

					<label>
						{{if values == 'true'}}
						<input name="values" value="true" checked="true" type="checkbox">
						{{else}}
						<input name="values" value="true" type="checkbox">
						{{/if}}
						<?php echo __('Checked', 'framework'); ?>
					</label>

					<br><span class="settings-field-caption"></span>
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

	public function get_value() {

		$value = parent::get_value();

		if ( is_array( $value ) ) {
			return $this->field->values;
		} else {
			return $value;
		}

	}

	public static function data_type_register() { ?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: 'Checkbox (true / false)',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
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
						.insertBefore($(this));

						$('#header').focus();
						field.after('<br>');
						field.after('<span class="field_label"> <?php echo $after_field ?> </span>');
						field.next().after('<a href="#" class="delete_checkbox_bool_field">Delete</a>');
								
						if(typeof reinitialize_customize_checkbox_bool_instance == 'function') {
							reinitialize_customize_checkbox_bool_instance('<?php echo $field_name ?>');
						}
					});

					$('body').on('click', '.delete_checkbox_bool_field', function(e){
						e.preventDefault();
						$(this).prev('.field_label').remove();
						$(this).prev('input').remove();
						$(this).next('br').remove();
						$(this).remove();
								
						if(typeof reinitialize_customize_checkbox_bool_instance == 'function') {
							reinitialize_customize_checkbox_bool_instance('<?php echo $field_name ?>');
						}
					});
							
					if ( wp.customize ) {
						if(typeof reinitialize_customize_checkbox_bool_instance == 'function') {
							var api = wp.customize;
							api.bind('ready', function(){
								reinitialize_customize_checkbox_bool_instance('<?php echo $field_name ?>');
							});
						}
					}
				});
			})(jQuery);
		</script>
	<?php
	endif;
	}
	
	public function wp_customize_js() {
	?>
		<script type="text/javascript">
		(function($){
			$('body').on('click', 'input[name^="<?php echo $this->field->alias;?>"]', function(){
				reinitialize_customize_checkbox_bool_instance('<?php echo $this->field->alias;?>');
			});
		})(jQuery);
                
		if(typeof reinitialize_customize_checkbox_bool_instance !== 'function') {
			function reinitialize_customize_checkbox_bool_instance(alias) {
				(function($){
					if ( wp.customize ) {
						var values_array = [];
						alias = alias.replace(/(\[\d*\])?\[\d*\]$/, "");
						$('input[name^="'+alias+'"]').each(function(){
							values_array.push($(this).prop("checked") ? $(this).prop("checked") : 'false');
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
} ?>
