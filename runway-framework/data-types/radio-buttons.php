<?php
class Radio_buttons extends Data_Type {

	public $type = 'radio-buttons';
	public static $type_slug = 'radio-buttons';
	public $label = 'Radio Buttons';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
			$this->field->values = preg_replace( "/\\r\\n|\\n|\\r/", '\\n\\r', $this->field->values );
		}

		$value = ( $vals != null ) ? $this->field->saved : $this->get_value();

		$key_values = array();

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

		$name = isset( $alias ) ? $alias : $this->field->alias;
		$vars = $key_values;
		$checked = 1;

        if(isset($this->field->repeating) && $this->field->repeating == 'Yes'){
            $this->get_value();
            if(isset($this->field->value) && is_array($this->field->value)) {
                foreach($this->field->value as $key=>$tmp_value) {
                    if(is_string($key))
                        unset($this->field->value[$key]);
                }
            }
            else if(!is_array($this->field->value) && is_string($this->field->value))
            {
                $tmp_arr = array();
                $tmp_arr[] = $this->field->value;
                $this->field->value = $tmp_arr;
            }

            $count = isset($this->field->value) ? count((array)$this->field->value) : 1;
            if($count == 0) 
                $count = 1;
    
            $len = count( $vars );
            $vars = apply_filters( $this->field->alias . '_data_options', $vars );
            
            for( $key_val = 0; $key_val < $count; $key_val++ ) {
                $cnt = 0;
                $html = "<div class='radio_group'>";
                
                foreach ( $vars as $key => $val ) {
                        $cnt++;

                        $checked = ( isset($this->field->value[$key_val]) && $key == trim( $this->field->value[$key_val] ) ) ? 'checked="checked"' : '';
                        $section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
                        $html .= '<label><input '.$this->get_link().' class="input-radio custom-data-type" '.$section.' data-type="radio-buttons" type="radio" name="'.$this->field->alias.'['.$key_val.']" value="'.$key.'" '.$checked.' />'.stripslashes( $val ).'</label>';
                        if ( $cnt < $len ) $html .= '<br>';
                }
                echo $html."</div>";
                ?>
                <a href="#" class="delete__radio_buttons_field">Delete</a><br><br>
                <?php
            }
            $field = array(
                    'field_name' => $this->field->alias,
                    'start_number' => $count,
                    'type' => 'radio',
                    'class' => 'input-radio custom-data-type',
                    'data_section' =>  isset( $this->page->section ) ? $this->page->section : '',
                    'data_type' => 'radio-buttons',
                    'after_field' => '',
                    'value' => '#'
            );
            $this->enable_repeating($field, $key_values);
            
        } else {
        
            $html = '';

            $set = $value;

            if ( !isset( $set ) || empty( $set ) ) {
                    $set = $value;
            }

            $len = count( $vars );
            $count = 0;

            $vars = apply_filters( $this->field->alias . '_data_options', $vars ); // allow filters to alter values
            
            foreach ( $vars as $key => $val ) {
                    $count++;

                    $checked = ( is_string( $set ) && $key == trim( $set ) ) ? 'checked="checked"' : '';
                    $section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
                    $html .= '<label><input '.$this->get_link().' class="input-radio custom-data-type" '.$section.' data-type="radio-buttons" type="radio" name="'.$this->field->alias.'" value="'.$key.'" '.$checked.' />'.stripslashes( $val ).'</label>';
                    if ( $count < $len ) $html .= '<br>';
            }

            // Add the fieldset container
            $html = '<fieldset><legend class="screen-reader-text"><span>'. stripslashes( $this->field->title ) .'</span></legend>'. stripslashes( $html ) .'</fieldset>';

            echo $html;
        }
                
		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public static function render_settings() { ?>

		<script id="radio-buttons" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            Values:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <textarea data-set="values" name="values" class="settings-textarea radio-buttons-type">${values}</textarea>

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
		            name: 'Radio buttons',
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

		        $('body').on('keyup', '.radio-buttons-type', function(e){
		            if(e.keyCode == 13){
		                render_values(this);
		            }
		        });

		        $('body').on('blur', '.radio-buttons-type', function(e){
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
                        <div id="<?php echo $add_id; ?>">
                                <a href="#">
                                        Add Field
                                </a>
                        </div>			

                        <script type="text/javascript">
                                (function($){
                                        $(document).ready(function(){
                                                var field = $.parseJSON('<?php echo json_encode($field); ?>');
                                                var start_radio_groups_index = <?php echo $start_number;?>;
                                                
                                                $('#<?php echo $add_id; ?>').click(function(e){
                                                        e.preventDefault();
                                                        var field = $('<div class="radio_group">');
                                                        
                                                        <?php foreach($default_values as $val_key=>$val) { ?>
                                                        var child_field = $('<input/>', {
                                                                type: '<?php echo $type; ?>',
                                                                class: '<?php echo $class; ?>',
                                                                name: '<?php echo $field_name; ?>['+start_radio_groups_index+']',
                                                                value: "<?php echo $val_key;?>"
                                                        })							
                                                        .attr('data-type', '<?php echo $data_type; ?>')
                                                        .attr('data-section', '<?php echo isset($data_section) ? $data_section : ""; ?>');
                                                        
                                                        field.append(child_field);
                                                        field.append("<?php echo $val;?><br/>");
                                                        <?php } ?>
                                                        start_radio_groups_index++;
                                                            
                                                        field.insertBefore($(this));

                                                        $('#header').focus();
                                                        field.after('<br><br>');
                                                        field.after('<span class="field_label"> <?php echo $after_field ?> </span>');
                                                        field.next().after('<a href="#" class="delete__radio_buttons_field">Delete</a>');
                                                });

                                                $('body').on('click', '.delete__radio_buttons_field', function(e){
                                                        e.preventDefault();
                                                        $(this).prev('.field_label').remove();
                                                        $(this).prev('.radio_group').remove();
                                                        $(this).next('br').remove();
                                                        $(this).next('br').remove();
                                                        $(this).remove();
                                                });
                                        });
                                })(jQuery);
                        </script>
                <?php
        endif;
    }
} ?>
