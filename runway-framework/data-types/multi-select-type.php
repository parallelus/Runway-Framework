<?php
class Multi_select_type extends Data_Type {

	public $type = 'multi-select-type';
	public static $type_slug = 'multi-select-type';
	public $label = 'Multiselect';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
			$this->field->values = preg_replace( "/\\r\\n|\\n|\\r/", '\\n\\r', $this->field->values );
		}

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
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
		$html ='<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>';
		$html .= '<select multiple class="input-select custom-data-type" '.$section.' data-type="multi-select-type" name="'.$this->field->alias.'[]" size="5" style="height: 103px;">';

		$value = ( $vals != null ) ? $this->field->saved : $this->get_value();

		if ( isset( $this->field->value[$this->field->alias] ) && isset( $this->field->value[$this->field->alias] ) && isset( $this->field->value ) && array_key_exists( 'field_types', $this->field->value ) ) {
			$value = $this->field->value[$this->field->alias];
		}

		$key_values = apply_filters( $this->field->alias . '_data_options', $key_values ); // allow filters to alter values

		$html .= '<option value="no">No value</option>';
		foreach ( $key_values as $key => $val ) {
			if ( is_array( $value ) ) {
				$checked = ( in_array( $key, $value ) ) ? ' selected="selected"' : '';
			}
			else
				$checked = '';

			if ( $val != '' ) {
				$html .= '<option value="'.$key.'"'.$checked.'>'.stripslashes( $val ).'</option>';
			}
		}
		$html .= '</select>';

		echo $html;

		do_action( self::$type_slug . '_after_render_content', $this );

		/* dirty hack to make multiple elms on customize.php page */
		if ( $this->is_customize_theme_page ) { ?>

			<input <?php $this->link(); ?> name="<?php echo $this->field->alias ?>" value="" />

			<script type="text/javascript">

				var name = '<?php echo $this->field->alias; ?>';

				jQuery('[name="'+name+'[]"] option').on('click', function () {

					var value = [];

					jQuery('[name="'+name+'[]"] option:selected').each(function () {

		            	value.push(jQuery(this).val());

		            });

		            jQuery('[name="'+name+'"]').val(value).trigger('change');

				});

			</script>
		<?php }
		/* dirty hack to make multiple elms on customize.php page */

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

	public function save( $value = null ) {

		/* dirty hack to make multiple elms on customize.php page */
		$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );

		parent::save( explode( ',', $submited_value->{$this->field->alias} ) );
		/* dirty hack to make multiple elms on customize.php page */

	}

	public static function render_settings() { ?>

	<script id="multi-select-type" type="text/x-jquery-tmpl">

		<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

    <div class="settings-container">
        <label class="settings-title">
            Values:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <textarea data-set="values" name="values" class="settings-textarea multi-select-type">${values}</textarea>

            <br><span class="settings-field-caption"></span>

        </div>

    </div><div class="clear"></div>

    <div class="settings-container">
        <label class="settings-title">
            Required:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <label>
                {{if required == 'true'}}
                <input data-set="required" name="required" value="true" checked="true" type="checkbox">
                {{else}}
                <input data-set="required" name="required" value="true" type="checkbox">
                {{/if}}
                Yes
            </label>

            <br><span class="settings-field-caption">Is this a required field.</span><br>

            <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

            <br><span class="settings-field-caption">Optional. Enter a custom error message.</span>

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

	public static function data_type_register() { ?>

        <script type="text/javascript">

            jQuery(document).ready(function ($) {
                builder.registerDataType({
		            name: 'Multiselect',
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

		        $('body').on('keyup', '.multi-select-type', function(e){
		            if(e.keyCode == 13){
		                render_values(this);
		            }
		        });

		        $('body').on('blur', '.multi-select-type', function(e){
		            render_values(this);
		        });
            });

        </script>

    <?php }
} ?>
