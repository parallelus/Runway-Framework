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
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
		$customize_title = stripslashes( $this->field->title );
		$html = "<legend class='customize-control-title'><span>$customize_title</span></legend>";
		$html .= "<select " . $this->get_link() . " class='input-select custom-data-type' $section data-type='select-type' name='{$this->field->alias}'>";

		foreach ( $key_values as $key => $val ) {
			if ( $val == 'OPTION_GROUP_START' || $val == 'OPTION_GROUP_END' ) {
				$html .= ( $val == 'OPTION_GROUP_START' ) ? '<optgroup label="'.$key.'">' : '</optgroup>';
			} else {
				$checked = ( is_string( $vals ) && $key == trim( $vals ) ) ? ' selected="selected"' : '';
				$html .= '<option value="'.$key.'" '.$checked.'>'.stripslashes( $val ).'</option>';
			}
		}

		$html .= '</select>';

		echo $html;

		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public function enable_repeating($field_name){
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
						$('#<?php echo $add_id; ?>').click(function(e){
							e.preventDefault();
							var field = $('<input/>', {
								type: 'text',
								class: 'input-text custom-data-type',
								name: '<?php echo $field_name; ?>[]'
							})							
							.attr('data-type', 'input-text')
							.insertBefore($(this));

							field.after('<a href="#" class="delete_field">Delete</a><br>');							
						});

						$('body').on('click', '.delete_field', function(e){
							e.preventDefault();
							$(this).prev('input').remove();
							$(this).next('br').remove();
							$(this).remove();
						});
					});
				})(jQuery);
			</script>
		<?php
	}

	public static function render_settings() { ?>

		<script id="select-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            Values:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <textarea data-set="values" name="values" class="settings-textarea select-type" id="select-values">${values}</textarea>

		            <br><span class="settings-field-caption"></span>

		        </div>

		    </div><div class="clear"></div>

		    <!-- Repeating settings -->
		    <div class="settings-container">
		        <label class="settings-title">
		            Repeating:                  
		        </label>
		        <div class="settings-in">
		            <label class="settings-title"> 
		                {{if repeating == 'Yes'}}
		                    <input data-set="repeating" name="repeating" value="Yes" checked="true" type="checkbox">
		                {{else}}
		                    <input data-set="repeating" name="repeating" value="Yes" type="checkbox">
		                {{/if}}
		                Yes
		            </label>
		            <br><span class="settings-title-caption">Can this field repeat with multiple values.</span>
		        </div>
		    </div><div class="clear"></div>

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
		            name: 'Select',
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
} ?>
