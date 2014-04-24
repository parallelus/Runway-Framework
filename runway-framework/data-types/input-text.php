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

		$this->get_value();

		$section = ( isset($this->page->section) && $this->page->section != '' ) ? 'data-section="' . $this->page->section . '"' : '';
		if (isset($this->field->repeating) && $this->field->repeating == 'Yes') {
		?>
			<label>
				<span class="customize-control-title"><?php echo $this->field->title ?></span>
				<div class="customize-control-content">				
			<?php
			if (isset($this->field->value) && is_array($this->field->value)) {
				foreach ($this->field->value as $key => $tmp_value) {
					if (is_string($key))
						unset($this->field->value[$key]);
				}
			}

			$count = isset($this->field->value) ? count((array) $this->field->value) : 1;
			if ($count == 0)
				$count = 1;
			for ($key = 0; $key < $count; $key++):
				if (isset($this->field->value) && is_array($this->field->value))
					$repeat_value = isset($this->field->value[$key]) ? $this->field->value[$key] : '';
				else
					$repeat_value = '';
			?>
				<input 
					type="text" 
					class="input-text custom-data-type" 
					<?php echo $section; ?> 
					data-type="input-text" 
					<?php $this->link(); ?> 
					name="<?php echo $this->field->alias; ?>[]" 
					accept=""value="<?php echo ( isset($repeat_value) && $repeat_value != '' ) ? $repeat_value : '' ?>"
				/>
					<a href="#" class="delete_field"><?php echo __('Delete', 'framework'); ?></a><br>
				<?php
			endfor;

			if (isset($this->field->repeating) && $this->field->repeating == 'Yes') {
				$field = array(
					'field_name' => $this->field->alias,
					'type' => 'text',
					'class' => 'input-text custom-data-type',
					'data_section' => isset($this->page->section) ? $this->page->section : '',
					'data_type' => 'input-text',
					'after_field' => '',
					'value' => 'aaa'
				);
				$this->enable_repeating($field);
			}
			?>
				</div>
			</label>
			<?php
			$this->wp_customize_js();
		}
		else{
			?>
			<label>
				<span class="customize-control-title"><?php echo $this->field->title ?></span>
				<?php
					$input_value = ( $vals != null ) ? $this->field->saved : $this->get_value();
					if(!is_string($input_value) && !is_numeric($input_value)) {
						if(is_array($input_value) && isset($input_value[0]))
							$input_value = $input_value[0];
						else
							$input_value = "";
					}
				?>
                            
				<div class="customize-control-content">
					<input type="text" 
						class="input-text custom-data-type" <?php echo $section; ?> data-type="input-text" <?php $this->link(); ?> name="<?php echo $this->field->alias; ?>" value="<?php echo $input_value; ?>"/>
				</div>
			</label>
			<?php
		}		
		do_action( self::$type_slug . '_after_render_content', $this );
	}
        
	public static function render_settings() { ?>

		<script id="input-text" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Values', 'framework'); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">
		            <input name="values" value="${values}" class="settings-input" type="text">
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
		                {{if required == 'Yes'}}
		                <input data-set="required" name="required" value="Yes" checked="true" type="checkbox">
		                {{else}}
		                <input data-set="required" name="required" value="Yes" type="checkbox">
		                {{/if}}
		                <?php echo __('Yes', 'framework'); ?>
		            </label>

		            <br><span class="settings-field-caption"><?php echo __('Is this a required field', 'framework'); ?>.</span><br>

		            <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

		            <br><span class="settings-field-caption"><?php echo __('Optional. Enter a custom error message', 'framework'); ?>.</span>
		        </div>
		    </div><div class="clear"></div>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Validation', 'framework'); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <select data-set="validation" name="validation" class="settings-select">
		                <option {{if validation == ''}} selected="true" {{/if}} value=""><?php echo __('None', 'framework'); ?></option>
		                <option {{if validation == 'url'}} selected="true" {{/if}} value="url"><?php echo __('Url', 'framework'); ?></option>
		                <option {{if validation == 'email'}} selected="true" {{/if}} value="email"><?php echo __('Email', 'framework'); ?></option>
		                <option {{if validation == 'alpha_only'}} selected="true" {{/if}} value="alpha_only"><?php echo __('Alpha', 'framework'); ?></option>
		                <option {{if validation == 'alpha_num_only'}} selected="true" {{/if}} value="alpha_num_only"><?php echo __('Alpha num', 'framework'); ?></option>
		                <option {{if validation == 'num_only'}} selected="true" {{/if}} value="num_only"><?php echo __('Numeric', 'framework'); ?></option>
		            </select>

		            <br><span class="settings-field-caption"></span>

		        </div>
		    </div>
		    <div class="clear"></div>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __('Validation error message', 'framework'); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <input type="text" name="validationMessage" value="${validationMessage}" />

		            <br><span class="settings-field-caption"></span>

		        </div>

		    </div><div class="clear"></div>
		    
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
	                <br><span class="settings-title-caption"><?php echo __('Can this field repeat with multiple values', 'framework'); ?>.</span>
		        </div>
		    </div><div class="clear"></div>

		    <?php do_action( self::$type_slug . '_after_render_settings' ); ?>

		</script>

	<?php }

	public static function data_type_register() { ?>

	<script type="text/javascript">

		jQuery(document).ready(function ($) {
			builder.registerDataType({
				name: '<?php echo __('Input text', 'framework'); ?>', 
				alias: '<?php echo self::$type_slug ?>',
				settingsFormTemplateID: '<?php echo self::$type_slug ?>'
			});
		});

	</script>

    <?php }
} ?>
