<?php
class Input_text extends Data_Type {

	public $type = 'input-text';
	public static $type_slug = 'input-text';
	public $label = 'Input text';

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );
		if ( $vals != null ) {
			$this->field = (object)$vals;
		}
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="'.$this->page->section.'"' : '';
?>

		<label>
			<span class="customize-control-title"><?php echo $this->field->title ?></span>
			<div class="customize-control-content">
				<input type="text" class="input-text custom-data-type" <?php echo $section; ?> data-type="input-text" <?php $this->link(); ?> name="<?php echo $this->field->alias; ?>" value="<?php echo ( $vals != null ) ? $this->field->saved : $this->get_value(); ?>"/>
			</div>
		</label><?php

		do_action( self::$type_slug . '_after_render_content', $this );
	}

	public static function render_settings() { ?>

		<script id="input-text" type="text/x-jquery-tmpl">

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

		    <div class="settings-container">
		        <label class="settings-title">
		            Validation:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <select data-set="validation" name="validation" class="settings-select">
		                <option {{if validation == ''}} selected="true" {{/if}} value="">None</option>
		                <option {{if validation == 'url'}} selected="true" {{/if}} value="url">Url</option>
		                <option {{if validation == 'email'}} selected="true" {{/if}} value="email">Email</option>
		                <option {{if validation == 'alpha_only'}} selected="true" {{/if}} value="alpha_only">Alpha</option>
		                <option {{if validation == 'alpha_num_only'}} selected="true" {{/if}} value="alpha_num_only">Alpha num</option>
		                <option {{if validation == 'num_only'}} selected="true" {{/if}} value="num_only">Numeric</option>
		            </select>

		            <br><span class="settings-field-caption"></span>

		        </div>

		    </div><div class="clear"></div>

		    <div class="settings-container">
		        <label class="settings-title">
		            Validation error message:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <input type="text" name="validationMessage" value="${validationMessage}" />

		            <br><span class="settings-field-caption"></span>

		        </div>

		    </div><div class="clear"></div>

		    <?php do_action( self::$type_slug . '_after_render_settings' ); ?>

		</script>

	<?php }

	public function get_value() {

		$value = parent::get_value();

		if ( is_string( $value ) ) {  // because strimg is array always
			return $value;
		} else {
			return ( isset( $this->field->values ) ) ? $this->field->values : '';
		}
	}

	public static function data_type_register() { ?>

        <script type="text/javascript">

            jQuery(document).ready(function ($) {
                builder.registerDataType({
		            name: 'Input text',
		            alias: '<?php echo self::$type_slug ?>',
                    settingsFormTemplateID: '<?php echo self::$type_slug ?>'
	        	});
            });

        </script>

    <?php }
} ?>
