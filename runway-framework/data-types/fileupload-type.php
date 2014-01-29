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
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
?>
		<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
		<input id="upload_image-<?php echo $this->field->alias; ?>" class="custom-data-type" <?php echo $section; ?> data-type="fileupload-type" type="text" size="36" name="<?php echo $this->field->alias; ?>" value="<?php echo @stripslashes( $value ); ?>" <?php $this->link(); ?> />

		<button id="upload_image_button-<?php echo $this->field->alias; ?>" class="button"><?php _e( 'Select File', 'framework' ); ?></button>
		<?php
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
?>
		<script type="text/javascript">
			(function($){
				$(function(){
					$("#upload_image_button-<?php echo $this->field->alias; ?>").click(function(e) {
						e.preventDefault();

						formfield = $("#upload_image-<?php echo $this->field->alias; ?>").attr("name");
						tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");

						// replace thickbox and overlay
						var $tb = $('#TB_window');
						var $ov = $('#TB_overlay');
						$('#customize-preview').append($tb).append($ov);
						$tb.css({
							position: 'absolute',
							left: '50%',
							top: '50%'
						});

						window.send_to_editor = function(html) {
							imgurl = $("img", html).attr("src");
							$("#upload_image-<?php echo $this->field->alias; ?>").val(imgurl);
							tb_remove();
						}

						return false;
					});
				});
			})(jQuery);
		</script><?php

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

		<script id="fileupload-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            Values:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">
		            <input name="values" value="${values}" class="settings-input" type="text">

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
		                {{if required == 'Yes'}}
		                <input data-set="required" name="required" value="Yes" checked="true" type="checkbox">
		                {{else}}
		                <input data-set="required" name="required" value="Yes" type="checkbox">
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
		            name: 'File upload',
		            alias: '<?php echo self::$type_slug ?>',
                    settingsFormTemplateID: '<?php echo self::$type_slug ?>'
		        });

		    });
		</script>

	<?php }
}

?>
