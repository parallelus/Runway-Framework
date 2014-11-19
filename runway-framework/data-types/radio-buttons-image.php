<?php
class Radio_buttons_image extends Data_Type {

	public $type = 'radio-buttons-image';
	public static $type_slug = 'radio-buttons-image';
	public $label = 'Radio Buttons Image';

	public function save( $value = null ) {

		/* dirty hack to make multiple elms on customize.php page */
		$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
		$value = $submited_value->{$this->field->alias};

		if(is_object($value)) {
			$value = "";
		}
		
		SingletonSaveCusomizeData::getInstance()->set_option($this->page->option_key);
		SingletonSaveCusomizeData::getInstance()->save_data($this->field->alias, $value, $this->type);
	}

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
			$this->field->values = preg_replace( "/\\r\\n|\\n|\\r/", '\\n\\r', $this->field->values );
		}

	?>

		<script type="text/javascript">
			(function($) {
				$('body').on('click', '.radio-image', function(){
					$(this).siblings('div').removeClass('checked');
					$(this).addClass('checked');
				});
			})(jQuery);
		</script>
		<?php
		$value = ( $vals != null ) ? $this->field->saved : $this->get_value();

		if ( is_array( $value ) && isset( $value[0] ) ) {
			$value = $value[0];
		}

		$key_values = array();
		$comments = array();
		if (isset($this->field->values) && !empty($this->field->values)) {

			if (strstr($this->field->values, "\r\n")) {
				$rows = explode("\r\n", $this->field->values);
			} else {
				$rows = explode("\\r\\n", $this->field->values);
			}
			foreach ($rows as $v) {
				if ($v != '') {
					$v = htmlspecialchars_decode($v);
					$this->field->values = explode('=>', $v);
					if (count($this->field->values) == 1) {
						$key = str_replace(' ', '-', trim(strtolower($this->field->values[0])));
						$key_values[$key] = $this->field->values[0];
					} else {
						$key = str_replace(' ', '-', trim(strtolower($this->field->values[0])));
						$key_values[$key] = $this->field->values[1];
					}
				}
			}
		}

		$name = $this->field->alias;
		$vars = $key_values;
		$checked = 1;
		$customize_title = stripslashes($this->field->title);
		
		if (isset($this->field->repeating) && $this->field->repeating == 'Yes') {
			?>
			<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
			<?php
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

			$len = count($vars);
			$vars = apply_filters($this->field->alias . '_data_options', $vars);

			for ($key_val = 0; $key_val < $count; $key_val++) {
				$cnt = 0;
				$html = "<div class='radio_group_image'>";

				foreach ($vars as $key => $val) {
					$cnt++;

					$checked = ( isset($this->field->value[$key_val]) && $key == trim($this->field->value[$key_val]) ) ? 'checked="checked"' : '';
					$class = 'radio-image';
					if ($checked != '')
						$class .= ' checked';

					$image_size = isset($this->field->image_size) ? $this->field->image_size : '';
					$comment = isset($comments[$key]) ? $comments[$key] : '';
					$section = ( isset($this->page->section) && $this->page->section != '' ) ? 'data-section="' . $this->page->section . '"' : '';
					$html .= "
						<div style='width: {$image_size}px; vertical-align: middle; display: inline-block; text-align: center; vertical-align: top;' class='" . stripslashes($class) . "'>
							<label>
								<dt class='wp-caption-dt'>
									<img src='" . stripslashes($val) . "' width='$image_size' height='" . $image_size . "'>
								</dt>
								<p>" . stripslashes($comment) . "</p>
								<input " . $this->get_link() . " class='input-radio custom-data-type' " . $section . " data-type='radio-buttons-image' type='radio' name='" . $this->field->alias . "[" . $key_val . "]' value='$key' $checked style='display: none;'/>
							</label>
						</div>";
				}
				echo $html . "</div>";
				?>
					<a href="#" class="delete_radio_image_field"><?php echo __('Delete', 'framework'); ?></a><br><br>
				<?php
			}
			$field = array(
				'field_name' => $this->field->alias,
				'start_number' => $count,
				'type' => 'radio',
				'class' => 'input-radio custom-data-type',
				'data_section' => isset($this->page->section) ? $this->page->section : '',
				'data_type' => 'radio-buttons',
				'after_field' => '',
				'value' => '#'
			);
			$this->enable_repeating($field, $key_values);
			$this->wp_customize_js();
		} else {

			$html = "";
			$set = $value;
			if (!isset($set) || empty($set)) {
				$set = $checked;
			}

			$vars = apply_filters($this->field->alias . '_data_options', $vars); // allow filters to alter values

			foreach ($vars as $key => $val) {
				$checked = ( $key == $set ) ? ' checked="checked"' : '';
				$class = 'radio-image';
				if ($checked != '')
					$class .= ' checked';

				$image_size = isset($this->field->image_size) ? $this->field->image_size : '';
				$comment = isset($comments[$key]) ? $comments[$key] : '';
				$section = ( isset($this->page->section) && $this->page->section != '' ) ? 'data-section="' . $this->page->section . '"' : '';
				$html .= "
					<div style='width: {$image_size}px; vertical-align: middle; display: inline-block; text-align: center; vertical-align: top;' class='" . stripslashes($class) . "'>
						<label>
							<dt class='wp-caption-dt'>
								<img src='" . stripslashes($val) . "' width='$image_size' height='" . $image_size . "'>
							</dt>
							<p>" . stripslashes($comment) . "</p>
							<input " . $this->get_link() . " class='input-radio custom-data-type' " . $section . " data-type='radio-buttons-image' type='radio' name='" . $this->field->alias . "' value='$key' $checked style='display: none;'/>
						</label>
					</div>";
			}

			$title = isset($title) ? $title : '';
			$html = '<fieldset><legend class="screen-reader-text"><span>' . stripslashes($title) . '</span></legend><legend class="customize-control-title"><span>' . $customize_title . '</span></legend>' . stripslashes($html) . '</fieldset>';

			echo $html;
		}
		do_action(self::$type_slug . '_after_render_content', $this);
		
	}

	public static function render_settings() { ?>

		<script id="radio-buttons-image" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Image size', 'framework'); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">
		            <select name="image_size" class="settings-select">
		                <option {{if image_size == ''}} selected="true" {{/if}} value=""><?php echo __('Real size', 'framework'); ?></option>
		                <option {{if image_size == '16'}} selected="true" {{/if}} value="16">16x16</option>
		                <option {{if image_size == '24'}} selected="true" {{/if}} value="24">24x24</option>
		                <option {{if image_size == '32'}} selected="true" {{/if}} value="32">32x32</option>
		                <option {{if image_size == '48'}} selected="true" {{/if}} value="48">48x48</option>
		                <option {{if image_size == '64'}} selected="true" {{/if}} value="64">64x64</option>
		                <option {{if image_size == '128'}} selected="true" {{/if}} value="128">128x128</option>
		            </select>
		            <br><span class="settings-field-caption"></span>

		        </div>

		    </div><div class="clear"></div>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Values', 'framework'); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <textarea data-set="values" name="values" class="settings-textarea radio-buttons-image-type">${values}</textarea>

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
		                {{if required == 'true'}}
		                <input data-set="required" name="required" value="true" checked="true" type="checkbox">
		                {{else}}
		                <input data-set="required" name="required" value="true" type="checkbox">
		                {{/if}}
		                <?php echo __('Yes', 'framework'); ?>
		            </label>

		            <br><span class="settings-field-caption"><?php echo __('Is this a required field?', 'framework'); ?>.</span><br>

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
		            <br><span class="settings-title-caption"><?php echo __('Can this field repeat with multiple values?', 'framework'); ?>.</span>
		        </div>
		    </div><div class="clear"></div>

			<?php parent::render_conditional_display(); ?>
		    <?php do_action( self::$type_slug . '_after_render_settings' ); ?>

		</script>

	<?php }

	public static function data_type_register() { ?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {

	        builder.registerDataType({
	            name: 'Image radio buttons',
	            alias: '<?php echo self::$type_slug ?>',
                settingsFormTemplateID: '<?php echo self::$type_slug ?>'
	        });

	        function values_render_rbi(selector){
	            var str_render = new String($(selector).val());
	            var result_string = new Array();
	            str_render = str_render.split('\n')
	            for(var key in str_render){
	                if(str_render[key] != ''){
	                    str_render[key] = new String(str_render[key]);
	                    str_render[key] = str_render[key].split('=>');
	                    if(str_render[key].length == 1){
	                        result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-')+'=><?php echo __('INSERT LINK TO IMAGE', 'framework'); ?>!');
	                    }
	                    else if(str_render[key].length == 2){
	                        if(str_render[key][1] != ''){
	                            result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-')+'=>'+ $.trim(str_render[key][1]));
	                        }
	                    }
	                    else if(str_render[key].length == 3){
	                        if(str_render[key][2] != ''){
	                            result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-')+'=>'+ $.trim(str_render[key][1]) +
	                                    '=>'+$.trim(str_render[key][2]));
	                        }
	                        else{
	                            result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-')+'=>'+ $.trim(str_render[key][1]) +
	                                    '=>INSERT A COMMENT!');
	                        }
	                    }
	                }
	            }
	            $(selector).val(result_string.join('\n')+'\n');
	        }

	        $('body').on('keyup', '.radio-buttons-image-type', function(e){
	            if(e.keyCode == 13){
	                values_render_rbi(this);
	            }
	        });

	        $('body').on('blur', '.radio-buttons-image-type', function(e){
	            values_render_rbi(this);
	        });

	    });

	</script>

	<?php }
        
	public function enable_repeating($field = array(), $default_values = array()) {
		if (!empty($field)) :
			extract($field);

		$add_id = 'add_' . $field_name;
		$del_id = 'del_' . $field_name;

		$div_class = 'radio-image';

		$image_size = isset($this->field->image_size) ? $this->field->image_size : '';
		$comment = '';
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
					var start_radio_groups_index = <?php echo $start_number; ?>;

					$('#<?php echo $add_id; ?>').click(function(e){
						e.preventDefault();
						var field = $('<div class="radio_group_image">');

						<?php foreach ($default_values as $val_key => $val) { ?>
							var child_field = $('<div style="width: <?php echo $image_size; ?>px; vertical-align: middle; display: inline-block; text-align: center; vertical-align: top;" class="<?php echo stripslashes($div_class); ?>">'+
										'<label>'+
											'<dt class="wp-caption-dt">'+
												'<img src="<?php echo stripslashes($val); ?>" width="<?php echo $image_size; ?>px" height="<?php echo $image_size; ?>px">'+
											'</dt>'+
											'<p></p>'+
											'<input <?php echo $this->get_link(); ?> class="<?php echo $class; ?>" data-section="<?php echo $data_section; ?>" type="<?php echo $type; ?>" name="<?php echo $field_name; ?>['+start_radio_groups_index+']" value="<?php echo $val_key; ?>" style="display: none" data-type="<?php echo $data_type; ?>"/>'+
										'</label>'+
									'</div>');

							field.append(child_field);
						<?php } ?>
						start_radio_groups_index++;

						field.insertBefore($(this));

						$('#header').focus();
						field.after('<br><br>');
						field.after('<span class="field_label"> <?php echo $after_field ?> </span>');
						field.next().after('<a href="#" class="delete_radio_image_field"><?php echo __('Delete', 'framework'); ?></a>');

						if(typeof reinitialize_customize_radio_image_instance == 'function') {
							reinitialize_customize_radio_image_instance('<?php echo $field_name ?>');
						}
					});

					$('body').on('click', '.delete_radio_image_field', function(e){
						e.preventDefault();
						$(this).prev('.field_label').remove();
						$(this).prev('.radio_group_image').remove();
						$(this).next('br').remove();
						$(this).next('br').remove();
						$(this).remove();

						if(typeof reinitialize_customize_radio_image_instance == 'function') {
							reinitialize_customize_radio_image_instance('<?php echo $field_name ?>');
						}
					});

					if ( wp.customize ) {
						if(typeof reinitialize_customize_radio_image_instance == 'function') {
							var api = wp.customize;
							api.bind('ready', function(){
								reinitialize_customize_radio_image_instance('<?php echo $field_name ?>');
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
			$('body').on('click', 'input[name^="<?php echo $this->field->alias;?>"]', function(e){
				reinitialize_customize_radio_image_instance('<?php echo $this->field->alias;?>');
				if ( wp.customize ) {
					e.preventDefault();
					e.stopPropagation();
				}
			});
		})(jQuery);

		if(typeof reinitialize_customize_radio_image_instance !== 'function') {
			function reinitialize_customize_radio_image_instance(alias) {
				(function($){
					if ( wp.customize ) {
						var values_array = [];
						var current_index = -1;
						var next_index = -1;

						alias = alias.replace(/(\[\d*\])?\[\d*\]$/, "");
						$('input[name^="'+alias+'"]').each(function(){
							var matched = $(this).attr('name').match(/\[(\d*)\]$/);
							if(current_index != parseInt(matched[1], 10)) {
								current_index = parseInt(matched[1], 10);
								next_index ++;
								values_array[next_index] = '';
							}

							if($(this).parents('.radio-image').hasClass('checked')) {
								values_array[next_index] = $(this).val();
							}
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

?>
