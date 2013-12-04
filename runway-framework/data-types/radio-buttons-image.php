<?php
class Radio_buttons_image extends Data_Type {

	public $type = 'radio-buttons-image';
	public static $type_slug = 'radio-buttons-image';
	public $label = 'Radio Buttons Image';

	public function save( $value = null ) {

		/* dirty hack to make multiple elms on customize.php page */
		$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );

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

		$name = $this->field->alias; $vars = $key_values; $checked = 1;
		$customize_title = stripslashes( $this->field->title );
		$html = "";
		$set = $value;
		if ( !isset( $set ) || empty( $set ) ) {
			$set = $checked;
		}

		$vars = apply_filters( $this->field->alias . '_data_options', $vars ); // allow filters to alter values

		foreach ( $vars as $key => $val ) {
			$checked = ( $key == $set ) ? ' checked="checked"' : '';
			$class = 'radio-image';
			if ( $checked != '' ) $class .= ' checked';

			$image_size = isset( $this->field->image_size ) ? $this->field->image_size : '';
			$comment = isset( $comments[$key] ) ? $comments[$key] : '';
			$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
			$html .= "
			<div style='width: {$image_size}px; vertical-align: middle; display: inline-block; text-align: center; vertical-align: top;' class='".stripslashes( $class )."'>
				<label>
					<dt class='wp-caption-dt'>
						<img src='".stripslashes( $val )."' width='$image_size' height='".$image_size."'>
					</dt>
					<p>".stripslashes( $comment )."</p>
					<input ".$this->get_link()." class='input-radio custom-data-type' ".$section." data-type='radio-buttons-image' type='radio' name='".$this->field->alias."' value='$key' $checked style='display: none;'/>
				</label>
			</div>";
		}

		$title = isset( $title ) ? $title : '';
		$html = '<fieldset><legend class="screen-reader-text"><span>'.stripslashes( $title ) .'</span></legend><legend class="customize-control-title"><span>'.$customize_title.'</span></legend>'. stripslashes( $html ) .'</fieldset>';

		echo $html;

		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public static function render_settings() { ?>

		<script id="radio-buttons-image" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            Image size:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">
		            <select name="image_size" class="settings-select">
		                <option {{if image_size == ''}} selected="true" {{/if}} value="">Real size</option>
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
		            Values:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <textarea data-set="values" name="values" class="settings-textarea radio-buttons-image-type">${values}</textarea>

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
	                        result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-')+'=>INSERT LINK TO IMAGE!');
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
}

?>
