<?php

class Font_select_type extends Data_Type {

	public $type = 'font-select-type';
	public static $type_slug = 'font-select-type';
	public $label = 'Font select';
	
	public function __construct($page, $field, $wp_customize = null, $alias = null, $params = null) {
		parent::__construct($page, $field, $wp_customize, $alias, $params);
	}

	public function render_content( $vals = null ) {
		?>

		<div style="font-family: <?php echo $this->field->family;?>; 
				font-style: <?php echo ($this->field->style != '') ? $this->field->style: 'normal';?>; 
				font-weight: <?php echo ($this->field->weight != '') ? $this->field->weight : 'normal';?>;
				size: <?php echo ($this->field->size != '') ? $this->field->size : '20px';?>; 
				color: <?php echo ($this->field->color != '') ? $this->field->color : '#000000'; ?>"><?php echo ($this->field->previewText != '') ? $this->field->previewText : ucwords(str_replace('-', ' ', $this->field->family));?></div>
		<div><a href="#" onclick="return false"><?php echo __('Edit Font Options', 'framework'); ?></a></div>

		<?php
	}
	
	public static function assign_actions_and_filters() {
		
	}
	
	public static function include_scripts() {
	}
	
	/*public function get_value() {

	}*/

	public static function render_settings() {?>
		<script id="font-select-type" type="text/x-jquery-tmpl">

		<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('CSS Class', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
			</label>
			<div class="settings-in">

				<input data-set="cssClass" name="cssClass" value="${cssClass}" class="settings-input" type="text">

				<br><span class="settings-field-caption"></span>
			
			</div>

		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Preview text', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<input data-set="previewText" name="previewText" value="${previewText}" type="text" placeholder="<?php echo __('Preview Test'); ?>"/>
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Family', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<select data-set="family" name="family" class="settings-select">
					<option {{if family == '' || family == 'open sans'}} selected="true" {{/if}} value="open sans"><?php echo __('Open Sans', 'framework'); ?></option>
				</select>
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Style', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<select data-set="style" name="style" class="settings-select">
					<option {{if style == '' || style == 'normal'}} selected="true" {{/if}} value="normal"><?php echo __('Normal', 'framework'); ?></option>
					<option {{if style == 'italic'}} selected="true" {{/if}} value="italic"><?php echo __('Italic', 'framework'); ?></option>
				</select>
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Weight', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<input data-set="weight" name="weight" value="{{if weight == ''}}bold{{else}}${weight}{{/if}}" type="text" />
				<br><span class="settings-title-caption"><?php echo __('normal, bold, 300, 600, 800', 'framework'); ?></span>
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Size', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<input data-set="size" name="size" value="{{if size == '' }}32px{{else}}${size}{{/if}}" type="text" />
				<br><span class="settings-title-caption"><?php echo __('12, 24px, 1em, 1.75', 'framework'); ?></span>
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Color', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<input data-set="color" name="color" value="${color}" type="text" class="color-picker-hex"/>
			</div>
		</div><div class="clear"></div>
		
		<?php do_action( self::$type_slug . '_after_render_settings' ); ?>

	    </script>
	<?php }

	public static function data_type_register() { ?>

		<script type="text/javascript">
			function fontSelectColorPickerInit() {

				setTimeout(function () {
					jQuery('.color-picker-hex').wpColorPicker();
					jQuery('.settings-select').on('change', fontSelectColorPickerInit());
				}, 200);

			}

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: '<?php echo __('Font select', 'framework'); ?>',
					separate: 'none',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>',
					onSettingsDialogOpen: function () {
						fontSelectColorPickerInit();
					}
				});
			});

		</script>
		
	<?php }
	
	public function wp_customize_js($double = false) { ?>

	<?php }
} ?>

