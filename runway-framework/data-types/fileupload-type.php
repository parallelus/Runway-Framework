<?php
class Fileupload_type extends Data_Type {

	public $type = 'fileupload-type';
	public static $type_slug = 'fileupload-type';
	public $label = 'Fileupload';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
			extract( $vals );
		}

		$value = ( $vals != null ) ? $this->field->saved : $this->get_value();
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.esc_attr($this->page->section).'"' : '';

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
			<legend class="customize-control-title"><span><?php echo stripslashes($this->field->title) ?></span></legend>
			<?php
				for ($key = 0; $key < $count; $key++) {
			?>
				<input id="upload_image-<?php echo esc_attr($this->field->alias); ?>_<?php echo esc_attr($key); ?>" class="custom-file-upload custom-data-type" <?php echo rf_string($section); // escaped above ?>
					data-type="fileupload-type" type="text" size="36" name="<?php echo esc_attr($this->field->alias); ?>[]"
					value="<?php echo @stripslashes(isset($this->field->value[$key]) ? $this->field->value[$key] : '' ); ?>" <?php $this->link(); ?> />

					<span class="field_label">
						<button id="upload_image_button-<?php echo esc_attr($this->field->alias.'_'.$key); ?>" class="custom-file-upload-button button"><?php _e('Select File', 'runway'); ?></button>
					</span>
					<a href="#" class="delete_fileupload_field"><?php echo __('Delete', 'runway'); ?></a><br>
			<?php
				}

			$field = array(
				'field_name' => $this->field->alias,
				'type' => 'text',
				'class' => 'custom-file-upload custom-data-type',
				'size' => '36',
				'data_section' => isset($this->page->section) ? $this->page->section : '',
				'data_type' => 'fileupload-type',
				'after_field' => __('Select File', 'runway'),
				'value' => '#'
			);
			$this->enable_repeating($field);
		?>
		<script type='text/javascript'>
                    var file_frame;
			var current_button;
			var attached_input;

			(function($){
				$('body').on('click', '.custom-file-upload-button', function(e){
					e.preventDefault();
					e.stopPropagation();
					current_button = $(this);
					attached_input = current_button.parent().prev();

					if ( file_frame ) {
						file_frame.open();
						return;
					}

					file_frame = wp.media.frames.file_frame = wp.media({
						multiple: false
					});

					file_frame.on( 'select', function() {
						attachment = file_frame.state().get('selection').first().toJSON();
						attached_input.val(attachment.url);
						attached_input.focus();

						if ( wp.customize ) {
							var api = wp.customize;
							var values_array = [];
							var name = attached_input.attr('name').replace(/\[\d*\]$/, "");
							$('input[name="'+attached_input.attr('name')+'"]').each(function(){
								values_array.push($(this).val());
							});
							api.instance(name).set(values_array);
						}

						var e = jQuery.Event("keypress");
						e.which = 13; //choose the one you want
						e.keyCode = 13;
						attached_input.trigger(e);
					});

					file_frame.open();
				});
			})(jQuery);
		</script>
	<?php
	} else {

		$input_value = ( $vals != null ) ? $this->field->saved : $this->get_value();
		if(!is_string($input_value) && !is_numeric($input_value))
		{
			if(is_array($input_value) && isset($input_value[0]))
				$input_value = $input_value[0];
			else
				$input_value = "";
		}
		?>
		<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
		<input id="upload_image-<?php echo esc_attr($this->field->alias); ?>" class="custom-data-type" <?php echo rf_string($section); // escaped above ?> data-type="fileupload-type" <?php echo parent::add_data_conditional_display($this->field); ?> type="text" size="36" name="<?php echo esc_attr($this->field->alias); ?>" value="<?php echo esc_attr(@stripslashes( $input_value )); ?>" <?php $this->link(); ?> />

		<span class="field_label">
			<button id="upload_image_button-<?php echo esc_attr($this->field->alias); ?>" class="button"><?php _e( 'Select File', 'runway' ); ?></button>
		</span>

		<script type="text/javascript">
			var file_frame;
			var current_button;
			var attached_input;

			(function($){

				$("#upload_image-<?php echo esc_js($this->field->alias); ?>").keydown(function(e){
					console.log('Yes keydown triggered. ' + e.which)
				});


					$("#upload_image_button-<?php echo esc_js($this->field->alias); ?>").click(function(e) {
						e.preventDefault();
						current_button = $(this);
						attached_input = current_button.parent().prev();

						if ( file_frame ) {
							file_frame.open();
							return;
						}

						file_frame = wp.media.frames.file_frame = wp.media({
							multiple: false
						});

						file_frame.on( 'select', function() {
							attachment = file_frame.state().get('selection').first().toJSON();
							attached_input.val(attachment.url);
							attached_input.trigger('change');

							if ( wp.customize ) {
								var api = wp.customize;
								var mysetting = api.instance(attached_input.attr('name'));
								api.instance(attached_input.attr('name')).set(attachment.url);
							}

							var e = jQuery.Event("keypress");
							e.which = 13; //choose the one you want
							e.keyCode = 13;
							attached_input.trigger(e);
						});

						file_frame.open();
					});

			})(jQuery);
		</script><?php
		}

		do_action( self::$type_slug . '_after_render_content', $this );
	}

	public static function render_settings() { ?>

		<script id="fileupload-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Values', 'runway'); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">
		            <input name="values" value="${values}" class="settings-input" type="text">

		        </div>
		        <div class="clear"></div>

		    </div>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Required', 'runway'); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <label>
		                {{if required == 'Yes'}}
		                <input data-set="required" name="required" value="Yes" checked="true" type="checkbox">
		                {{else}}
		                <input data-set="required" name="required" value="Yes" type="checkbox">
		                {{/if}}
		                <?php echo __('Yes', 'runway'); ?>
		            </label>

		            <span class="settings-field-caption"><?php echo __('Is this a required field?', 'runway'); ?></span><br>

		            <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

		            <span class="settings-field-caption"><?php echo __('Optional. Enter a custom error message.', 'runway'); ?></span>

		        </div>
		        <div class="clear"></div>

		    </div>

		    <!-- Repeating settings -->
		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Repeating', 'runway'); ?>:
		        </label>
		        <div class="settings-in">
		            <label>
		                {{if repeating == 'Yes'}}
		                    <input data-set="repeating" name="repeating" value="Yes" checked="true" type="checkbox">
		                {{else}}
		                    <input data-set="repeating" name="repeating" value="Yes" type="checkbox">
		                {{/if}}
		                <?php echo __('Yes', 'runway'); ?>
		            </label>
		            <span class="settings-field-caption"><?php echo __('Can this field repeat with multiple values?', 'runway'); ?></span>
		        </div>
		        <div class="clear"></div>
		    </div>

			<?php parent::render_conditional_display(); ?>
		    <?php do_action( self::$type_slug . '_after_render_settings' ); ?>

		</script>

	<?php }

	public function get_value() {

		$value = parent::get_value();

		if ( is_array( $value ) ) {
			return ( isset( $this->field->values ) ) ? $this->field->values : '';
		} else {
			return $value;
		}

	}

	public static function data_type_register() { ?>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {

				builder.registerDataType({
					name: '<?php echo __('File upload', 'runway'); ?>',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});

			});
		</script>

	<?php }

	public function enable_repeating($field = array()) {
		if (!empty($field)) :
			extract($field);

		$add_id = 'add_' . $field_name;
		$del_id = 'del_' . $field_name;
		?>
			<div id="<?php echo esc_attr($add_id); ?>">
				<a href="#">
					<?php echo __('Add Field', 'runway'); ?>
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
							.attr('size', '<?php echo esc_js($size); ?>')
							.attr('data-type', '<?php echo esc_js($data_type); ?>')
							.attr('data-section', '<?php echo isset($data_section) ? esc_js($data_section) : ""; ?>')
							.insertBefore($(this)).focus();

							field.click(function(e){
							e.preventDefault();
							});

							$('#header').focus();
							field.after('<br>');
							field.after('<span class="field_label"> <button class="custom-file-upload-button button"><?php echo esc_js($after_field) ?></button> </span>');
							field.next().after('<a href="#" class="delete_fileupload_field"><?php echo __('Delete', 'runway'); ?></a>');

							if(typeof reinitialize_customize_instance == 'function') {
								reinitialize_customize_instance('<?php echo esc_js($field_name) ?>');
							}
						});

						$('body').on('click', '.delete_fileupload_field', function(e){
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

}

?>
