<?php
class Checkbox_type extends Data_Type {

	public $type = 'checkbox-type';
	public static $type_slug = 'checkbox-type';
	public $label = 'Checkbox List';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
			$this->field->values = preg_replace( "/\\r\\n|\\n|\\r/", "\r\n", $this->field->values );
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
					} else {
						$key = str_replace( ' ', '-', trim( strtolower( $this->field->values[0] ) ) );
						$key_values[$key] = $this->field->values[1];
					}
				}
			}
		}


		$options = array();
		$checked_list = array();

		$this->field->values = ( $vals != null ) ? $this->field->saved : (array) $this->get_value();

		if ( isset( $this->field->value[$this->field->alias] ) && isset( $this->field->value ) && in_array( 'field_types', (array) $this->field->value ) ) {
			$this->field->value = $this->field->value[$this->field->alias];
		}

		if ( empty( $this->field->values[0] ) ) {
			$this->field->values = $checked_list;
		}

		if ( isset( $this->field->values[0] ) && $this->field->values[0] == 'Array' ) {
			$this->field->values = array( 0 => $options['values'] );
		}

		$html = '';
		$len = count( $key_values );
		$count = 0;

		$key_values = apply_filters( $this->field->alias . '_data_options', $key_values ); // allow filters to alter values

		foreach ( $key_values as $key => $val ) {
			$count++;
			// Options will over-ride values
			if ( !isset( $options[$key] ) ) {
				$options[$key] = array( 'class' => '', 'disabled' => '' );
			}

			$class = ( $a = $options[$key]['class'] ) ? 'class="' . $a . '"' : '';
			$readonly = ( $options[$key]['disabled'] ) ? ' disabled="disabled"' : '';

			if ( array_key_exists( 'value', (array)$options[$key] ) ) {
				$checked = ( $options[$key]['value'] ) ? ' checked="checked" ' : '';
			} elseif ( is_array( $this->field->values ) ) {
				$checked = ( in_array( $key, $this->field->values ) ) ? ' checked="checked"' : '';
			}

			if ( !isset( $this->field->class ) ) {
				$this->field->class = '';
			}
			$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
			$html .= '<label>
				<input
				class="input-check custom-data-type"
				'.$section.'
				type="checkbox"
				data-type="checkbox-type"
				value="'.$key.'"
				name="'.$this->field->alias.'[]" '.$this->field->class.' '.$readonly.' '.$checked.'/>'.stripslashes( $val ).'</label>';

			if ( isset( $options[$key]['text'] ) ) {
				if ( $t = $options[$key]['text'] ) {
					$html .= '<em>' . $t . '</em>';
				}
			}

			if ( $count < $len ) {
				$html .= '<br>';
			}
		}

		// Add the fieldset container
		$html = '<fieldset><legend class="screen-reader-text"><span>'. $this->field->title .'</span></legend>'. $html .'</fieldset>';

		echo $html;

		do_action( self::$type_slug . '_after_render_content', $this );

		/* dirty hack to make multiple elms on customize.php page */
		if ( $this->is_customize_theme_page ) { ?>
			<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
			<input type="hidden" <?php $this->link(); ?> name="<?php echo $this->field->alias ?>" value="" />

			<script type="text/javascript">

				var name = '<?php echo $this->field->alias; ?>';

				jQuery('[name="'+name+'[]"]').on('click', function () {

					var value = [];

					jQuery('[name="'+name+'[]"]:checked').each(function () {
		            	value.push(jQuery(this).val());
		            });

		            jQuery('[name="'+name+'"]').val(value).trigger('change');

				});

			</script>
		<?php }
		/* dirty hack to make multiple elms on customize.php page */
	}

	public function save( $value = null ) {

		/* dirty hack to make multiple elms on customize.php page */
		$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
		parent::save( explode( ',', $submited_value->{$this->field->alias} ) );
		/* dirty hack to make multiple elms on customize.php page */

	}

	public static function render_settings() { ?>

	<script id="checkbox-type" type="text/x-jquery-tmpl">

	<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

    <div class="settings-container">
        <label class="settings-title">
            Values:
            <br><span class="settings-title-caption"></span>
        </label>
        <div class="settings-in">

            <textarea data-set="values" name="values" class="settings-textarea checkbox-type" id="settings-values">${values}</textarea>

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
		            name: 'Checkbox List',
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

		        $('body').on('keyup', '.checkbox-type', function(e){
		            if(e.keyCode == 13){
		                render_values(this);
		            }
		        });

		        $('body').on('blur', '.checkbox-type', function(e){
		            render_values(this);
		        });
            });

        </script>

	<?php }
} ?>
