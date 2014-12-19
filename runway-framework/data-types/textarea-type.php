<?php
class Textarea_type extends Data_Type {

	public $type = 'textarea-type';
	public static $type_slug = 'textarea-type';
	public $label = 'Textarea';

	public function render_content( $vals = null ) {

		do_action(self::$type_slug . '_before_render_content', $this);

		if ($vals != null) {
			$this->field = (object) $vals;
		}

		$value = ( $vals != null ) ? $this->field->saved : $this->get_value();
		$section = ( isset($this->page->section) && $this->page->section != '' ) ? 'data-section="' . $this->page->section . '"' : '';
		$customize_title = stripslashes($this->field->title);
		
		if (isset($this->field->repeating) && $this->field->repeating == 'Yes') {
			$this->get_value();
			if (isset($this->field->value) && is_array($this->field->value)) {
				foreach ($this->field->value as $key => $tmp_value) {
					if (is_string($key))
						unset($this->field->value[$key]);
				}
			}
			else if (!is_array($this->field->value) && is_string($this->field->value)) {
				$tmp_arr = array();
				$tmp_arr[] = $this->field->value;
				$this->field->value = $tmp_arr;
			}

			$count = isset($this->field->value) ? count((array) $this->field->value) : 1;
			if ($count == 0)
				$count = 1;
			?>
			<legend class='customize-control-title'><span><?php echo $customize_title; ?></span></legend>
			<?php
			for ($key = 0; $key < $count; $key++) {
			?>
				<textarea
					class="input-textarea<?php echo " " . $this->field->cssClass; ?> custom-data-type"
					<?php $this->link() ?>
					name="<?php echo $this->field->alias; ?>[]"
					<?php echo $section; ?>
					data-type='textarea-image'><?php echo isset($this->field->value[$key]) && is_string($this->field->value[$key]) ? $this->field->value[$key] : ''; ?></textarea>
				<a href="#" class="delete_textarea_field"><?php echo __('Delete', 'framework'); ?></a><br>
			<?php
			}

			$field = array(
				'field_name' => $this->field->alias,
				'class' => 'input-textarea ' . $this->field->cssClass . ' custom-data-type',
				'data_section' => isset($this->page->section) ? $this->page->section : '',
				'data_type' => 'textarea-image',
				'after_field' => '',
				'value' => '#'
			);
			$this->enable_repeating($field);
			$this->wp_customize_js();
		} else {
			?>
			<legend class='customize-control-title'><span><?php echo $customize_title; ?></span></legend>
				<textarea
					class="input-textarea<?php echo " " . $this->field->cssClass; ?> custom-data-type"
					<?php $this->link() ?> 
					<?php echo parent::add_data_conditional_display($this->field); ?>
					name="<?php echo $this->field->alias; ?>"
					<?php echo $section; ?>
					data-type='textarea-image'><?php echo is_string( $value )? $value : ''; ?></textarea><?php
		}
        
		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public function get_value() {

		$value = parent::get_value();

		if (is_string($value)) {  // because string is array always
			return $value;
		} else {
			return ( isset($this->field->values) ) ? $this->field->values : '';
		}
	}

	public static function render_settings() { ?>

		<script id="textarea-type" type="text/x-jquery-tmpl">

		    <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		<div class="settings-container">
		    <label class="settings-title">
				<?php echo __('Values', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">

			<textarea data-set="values" name="values" class="settings-textarea">${values}</textarea>

		    </div>
		    <div class="clear"></div>

		</div>

		<div class="settings-container">
		    <label class="settings-title">
				<?php echo __('Required', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">

			<label>
			    {{if required == 'true'}}
			    <input data-set="required" name="required" value="true" checked="true" type="checkbox">
			    {{else}}
			    <input data-set="required" name="required" value="true" type="checkbox">
			    {{/if}}
			    <?php echo __('Yes', 'framework'); ?>
			</label>

			<span class="settings-field-caption"><?php echo __('Is this a required field?', 'framework'); ?></span><br>

			<input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

			<span class="settings-field-caption"><?php echo __('Optional. Enter a custom error message.', 'framework'); ?></span>

		    </div>
		    <div class="clear"></div>

		</div>

		<div class="settings-container">
		    <label class="settings-title">
				<?php echo __('CSS Class', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">

			<input data-set="cssClass" name="cssClass" value="${cssClass}" class="settings-input" type="text">

		    </div>
		    <div class="clear"></div>

		</div>

		<!-- Repeating settings -->
		<div class="settings-container">
		    <label class="settings-title">
				<?php echo __('Repeating', 'framework'); ?>:
		    </label>
		    <div class="settings-in">
			<label> 
			    {{if repeating == 'Yes'}}
				<input data-set="repeating" name="repeating" value="Yes" checked="true" type="checkbox">
			    {{else}}
				<input data-set="repeating" name="repeating" value="Yes" type="checkbox">
			    {{/if}}
			    <?php echo __('Yes', 'framework'); ?>
			</label>
			<span class="settings-field-caption"><?php echo __('Can this field repeat with multiple values?', 'framework'); ?></span>
		    </div>
		    <div class="clear"></div>
		</div>

		<?php parent::render_conditional_display(); ?>		
		<?php do_action( self::$type_slug . '_after_render_settings' ); ?>

	    </script>

	<?php }

	public static function data_type_register() { ?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: '<?php echo __('Textarea', 'framework'); ?>',
					separate: 'none',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});
			});

		</script>

	<?php }
    
	public function enable_repeating($field = array(), $default_values = array() ){
		if(!empty($field)) :
			extract($field);

		$add_id = 'add_' . $field_name;
		$del_id = 'del_'.$field_name;

		?>
		<div id="<?php echo $add_id; ?>">
			<a href="#">
				<?php echo __('Add Field', 'framework'); ?>
			</a>
		</div>			

		<script type="text/javascript">
			(function($){
				$(document).ready(function(){
					var field = $.parseJSON('<?php echo json_encode($field); ?>');

					$('#<?php echo $add_id; ?>').click(function(e){
						e.preventDefault();
						var field = $('<textarea>', {
							class: '<?php echo $class; ?>',
							name: '<?php echo $field_name; ?>[]',
							value: ""
						})							
						.attr('data-type', '<?php echo $data_type; ?>')
						.attr('data-section', '<?php echo isset($data_section) ? $data_section : ""; ?>');
						field.insertBefore($(this));

						field.click(function(e){
							e.preventDefault();
						});

						$('#header').focus();
						field.after('<br>');
						field.after('<span class="field_label"> <?php echo $after_field ?> </span>');
						field.next().after('<a href="#" class="delete_textarea_field"><?php echo __('Delete', 'framework'); ?></a>');
                                                        
						if(typeof reinitialize_customize_textarea_instance == 'function') {
							reinitialize_customize_textarea_instance('<?php echo $field_name ?>');
						}
					});

					$('body').on('click', '.delete_textarea_field', function(e){
						e.preventDefault();
						$(this).prev('.field_label').remove();
						$(this).prev().remove();
						$(this).next('br').remove();
						$(this).remove();
                                                        
						if(typeof reinitialize_customize_textarea_instance == 'function') {
							reinitialize_customize_textarea_instance('<?php echo $field_name ?>');
						}
					});
                                                        
					if ( wp.customize ) {
						if(typeof reinitialize_customize_textarea_instance == 'function') {
							var api = wp.customize;
								api.bind('ready', function(){
									reinitialize_customize_textarea_instance('<?php echo $field_name ?>');
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
				$('body').on('change', 'textarea[name="<?php echo $this->field->alias;?>[]"]', function(){
					reinitialize_customize_textarea_instance('<?php echo $this->field->alias;?>');
				});
			})(jQuery);
                
			if(typeof reinitialize_customize_textarea_instance !== 'function') {
				function reinitialize_customize_textarea_instance(alias) {
					(function($){
						if ( wp.customize ) {
							var values_array = [];
							alias = alias.replace(/\[\d*\]$/, "");
							$('textarea[name="'+alias+'[]"]').each(function(){
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
} ?>
