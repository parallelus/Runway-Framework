<?php
class Checkbox_bool_type extends Data_Type {

	public $type = 'checkbox-bool-type';
	public static $type_slug = 'checkbox-bool-type';
	public $label = 'Checkbox (true / false)';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object)$vals;
		}
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
?>

		<fieldset>
			<legend class="customize-control-title"><span><?php echo stripslashes( $this->field->title ) ?></span></legend>
			<input type="hidden" value="false" name="<?php echo $this->field->alias ?>"  />
			<label>
				<input <?php $this->link(); ?> class="input-check custom-data-type" <?php echo $section; ?> data-type="checkbox-bool-type" type="checkbox" value="true" name="<?php echo $this->field->alias ?>" <?php  if ( $this->get_value() == 'true' ) echo 'checked '; ?> /> <?php _e( 'Yes', 'framework' ) ?>
			</label>
		</fieldset> <?php

		do_action( self::$type_slug . '_after_render_content', $this );
	}

	public static function render_settings() { ?>

		<script id="checkbox-bool-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

			<div class="settings-container">
				<label class="settings-title">
					Value:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">

					<label>
						{{if values == 'true'}}
						<input name="values" value="true" checked="true" type="checkbox">
						{{else}}
						<input name="values" value="true" type="checkbox">
						{{/if}}
						Checked
					</label>

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

		if ( is_array( $value ) ) {
			return $this->field->values;
		} else {
			return $value;
		}

	}

	public static function data_type_register() { ?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: 'Checkbox (true / false)',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});
			});

		</script>

	<?php }
} ?>
