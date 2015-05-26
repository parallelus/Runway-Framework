<?php
class Select_type extends Data_Type {

	public $type = 'select-type';
	public static $type_slug = 'select-type';
	public $label = 'Select';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
			$this->field->values = preg_replace( "/\\r\\n|\\n|\\r/", '\\n\\r', $this->field->values );
		}

		$value = ( $vals != null ) ? $this->field->saved : $this->get_value();

		$key_values = array();
		if ( isset( $this->field->values ) && !empty( $this->field->values ) ) {
			if(is_array($this->field->values))
				$this->field->values = "";
			
			if ( strstr( $this->field->values, "\r\n" ) ) {
				$rows = explode( "\r\n", $this->field->values );
			}
			else {
				$rows = explode( "\\r\\n", $this->field->values );
			}
			foreach ( $rows as $v ) {
				if ( $v != '' ) {
					$v = htmlspecialchars_decode( $v );
					$this->field->values = explode( '=>', $v );
					if ( count( $this->field->values ) == 1 ) {
						$key = str_replace( ' ', '-', trim( strtolower( $this->field->values[0] ) ) );
						$key_values[$key] = $this->field->values[0];
					}else {
						$key = str_replace( ' ', '-', trim( strtolower( $this->field->values[0] ) ) );
						$key_values[$key] = $this->field->values[1];
					}
				}
			}
		}

		$vals = $value;
		$key_values = apply_filters( $this->field->alias . '_data_options', $key_values ); // allow filters to alter values
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.esc_attr($this->page->section).'"' : '';
		$customize_title = stripslashes( $this->field->title );
                ?>
		<legend class='customize-control-title'><span><?php echo  $customize_title; ?></span></legend>
                <?php
		if(isset($this->field->repeating) && $this->field->repeating == 'Yes'){
			$vals = isset($this->field->value) ? $this->field->value : array();
            
			if(isset($vals) && is_array($vals)) {
				foreach($vals as $key=>$tmp_value) {
					if(is_string($key))
						unset($vals[$key]);
				}
			}
            
			$count = isset($vals) ? count((array)$vals) : 1;
			if($count == 0) 
				$count = 1;
            
			for( $key = 0; $key < $count; $key++ ) {
			?>
			<select <?php $this->link();?>
				class='input-select custom-data-type'
				<?php echo  $section; // escaped above ?>
				data-type='select-type'
				name="<?php echo esc_attr($this->field->alias); ?>[]">
			<?php foreach($key_values as $select_value_key=>$val) { 
				$html = "";
				if ( $val == 'OPTION_GROUP_START' || $val == 'OPTION_GROUP_END' ) {
					$html .= ( $val == 'OPTION_GROUP_START' ) ? '<optgroup label="'.$select_value_key.'">' : '</optgroup>';
				} else {
					if($vals[$key] == trim($select_value_key))
						$checked = ' selected="selected"';
					else
					$checked = '';
					$html .= '<option value="'.$select_value_key.'" '.$checked.'>'.stripslashes( $val ).'</option>';
				}
				echo  $html;
			} ?>
			</select>
			<a href="#" class="delete_select_field"><?php echo __('Delete', 'framework'); ?></a><br>
			<?php
			}

			$field = array(
				'field_name' => $this->field->alias,
				'type' => 'select',
				'class' => 'input-select custom-data-type',
				'data_section' =>  isset( $this->page->section ) ? $this->page->section : '',
				'data_type' => 'select-type',
				'after_field' => '',
				'value' => '#'
			);
			$this->enable_repeating($field, $key_values);
			$this->wp_customize_js();
		} else {
			$html = "<select " . $this->get_link() . " class='input-select custom-data-type' " . parent::add_data_conditional_display($this->field) . " $section data-type='select-type' name='{$this->field->alias}'>";

			foreach ( $key_values as $key => $val ) {
				if ( $val == 'OPTION_GROUP_START' || $val == 'OPTION_GROUP_END' ) {
					$html .= ( $val == 'OPTION_GROUP_START' ) ? '<optgroup label="'.$key.'">' : '</optgroup>';
				} else {
					$checked = ( is_string( $vals ) && $key == trim( $vals ) ) ? ' selected="selected"' : '';
					$html .= '<option value="'.$key.'" '.$checked.'>'.stripslashes( $val ).'</option>';
				}
			}

			$html .= '</select>';

			echo  $html;
		}
        
		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public static function render_settings() { ?>

		<script id="select-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Values', 'framework'); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <textarea data-set="values" name="values" class="settings-textarea select-type" id="select-values">${values}</textarea>
		            <span class="settings-field-caption"><?php printf( __('Enter options in the format: %s key=>value %s', 'framework'), '<code>', '</code>'); ?></span>

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

	public function get_value() {

		$value = parent::get_value();
		if ( is_array( $value ) && isset( $value[0] ) ) {
			return $value[0];
		} else {
			return $value;
		}

	}

	public static function data_type_register() { ?>

        <script type="text/javascript">

            jQuery(document).ready(function ($) {
                builder.registerDataType({
		            name: '<?php echo __('Select', 'framework'); ?>',
		            alias: '<?php echo self::$type_slug ?>',
                settingsFormTemplateID: '<?php echo self::$type_slug ?>'
		        });
		        function render_values(selector){
		            var str_render = new String($(selector).val());
		            var result_string = new Array();
		            str_render = str_render.split('\n')
		            for(var key in str_render){
		                if(str_render[key] != ''){
		                    str_render[key] = new String(str_render[key]);
		                    str_render[key] = str_render[key].split('=>');
		                    if(str_render[key].length == 1){
		                        result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-')+'=>'+ $.trim(str_render[key][0]));
		                    }
		                    else if(str_render[key].length == 2){
		                        if(str_render[key][1] != ''){
		                            result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-')+'=>'+ $.trim(str_render[key][1]));
		                        }
		                        else{
		                            result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-')+'=>'+ $.trim(str_render[key][0]));
		                        }
		                    }
		                }
		            }
		            $(selector).val(result_string.join('\n')+'\n');
		        }

		        $('body').on('keyup', '.select-type', function(e){
		            if(e.keyCode == 13){
		                render_values(this);
		            }
		        });

		        $('body').on('blur', '.select-type', function(e){
		            render_values(this);
		        });
            });

        </script>

	<?php }
    
	public function enable_repeating($field = array(), $default_values = array() ){
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
						var field = $('<select>', {
							type: '<?php echo esc_js($type); ?>',
							class: '<?php echo esc_js($class); ?>',
							name: '<?php echo esc_js($field_name); ?>[]',
							value: ""
						})							
						.attr('data-type', '<?php echo esc_js($data_type); ?>')
						.attr('data-section', '<?php echo isset($data_section) ? $data_section : ""; ?>');
                                                
						<?php foreach($default_values as $val_key=>$val) { 
							$html = "";
							if ( $val == 'OPTION_GROUP_START' || $val == 'OPTION_GROUP_END' ) {
								$html .= ( $val == 'OPTION_GROUP_START' ) ? '<optgroup label="'.$val_key.'">' : '</optgroup>';
							} else {
								$html .= '<option value="'.$val_key.'" >'.stripslashes( $val ).'</option>';
							}
						?>
						field.append('<?php echo esc_js($html);?>');
						<?php } ?>
                                                            
						field.insertBefore($(this));

						field.click(function(e){
							e.preventDefault();
						});

						$('#header').focus();
						field.after('<br>');
						field.after('<span class="field_label"> <?php echo esc_js($after_field) ?> </span>');
						field.next().after('<a href="#" class="delete_select_field"><?php echo __('Delete', 'framework'); ?></a>');
                                                        
						if(typeof reinitialize_customize_select_instance == 'function') {
							reinitialize_customize_select_instance('<?php echo esc_js($field_name) ?>');
						}
					});

					$('body').on('click', '.delete_select_field', function(e){
						e.preventDefault();
						$(this).prev('.field_label').remove();
						$(this).prev().remove();
						$(this).next('br').remove();
						$(this).remove();
                                                        
						if(typeof reinitialize_customize_select_instance == 'function') {
							reinitialize_customize_select_instance('<?php echo esc_js($field_name) ?>');
						}
					});
                                                        
					if ( wp.customize ) {
						if(typeof reinitialize_customize_select_instance == 'function') {
							var api = wp.customize;
							api.bind('ready', function(){
								reinitialize_customize_select_instance('<?php echo esc_js($field_name) ?>');
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
				$('body').on('change', 'select[name="<?php echo esc_js($this->field->alias);?>[]"]', function(){
					reinitialize_customize_select_instance('<?php echo esc_js($this->field->alias);?>');
				});
			})(jQuery);
                
			if(typeof reinitialize_customize_select_instance !== 'function') {
				function reinitialize_customize_select_instance(alias) {
					(function($){
						if ( wp.customize ) {
							var values_array = [];
							alias = alias.replace(/\[\d*\]$/, "");
							$('select[name="'+alias+'[]"]').each(function(){
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
