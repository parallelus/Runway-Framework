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

		<div class="<?php echo $this->field->alias; ?>">
			<div style="font-family: <?php echo $this->field->family;?>; 
					font-style: <?php echo ($this->field->style != '') ? $this->field->style: 'normal';?>; 
					font-weight: <?php echo ($this->field->weight != '') ? $this->field->weight : 'normal';?>;
					size: <?php echo ($this->field->size != '') ? $this->field->size : '20px';?>; 
					color: <?php echo ($this->field->color != '') ? $this->field->color : '#000000'; ?>"><?php echo ($this->field->previewText != '') ? $this->field->previewText : ucwords(str_replace('-', ' ', $this->field->family));?></div>
					
			<!--<input type="hidden" name="<?php echo $this->field->alias;?>[family]" value="<?php echo $this->field->family; ?>"/>
			<input type="hidden" name="<?php echo $this->field->alias;?>[style]" value="<?php echo ($this->field->style != '') ? $this->field->style: 'normal'; ?>"/>
			<input type="hidden" name="<?php echo $this->field->alias;?>[weight]" value="<?php echo ($this->field->weight != '') ? $this->field->weight : 'normal'; ?>"/>
			<input type="hidden" name="<?php echo $this->field->alias;?>[size]" value="<?php echo ($this->field->size != '') ? $this->field->size : '20px'; ?>"/>
			<input type="hidden" name="<?php echo $this->field->alias;?>[color]" value="<?php echo ($this->field->color != '') ? $this->field->color : '#000000'; ?>"/>
			<input type="hidden" name="<?php echo $this->field->alias;?>[previewText]" value="<?php echo $this->field->previewText; ?>"/>-->
			
			<div class="toogle-font-select-container">
				<a href="#" onclick="return false" class="edit-font-options-a"><?php echo __('Edit Font Options', 'framework'); ?></a>
				<div class="edit-font-options-inner" style="display: none;">
					
					<div class="settings-container">
						<label class="settings-title">
							<?php echo __('Preview text', 'framework'); ?>:
						</label>
						<div class="settings-in">
							<input data-set="<?php echo $this->field->alias;?>[previewText]" name="<?php echo $this->field->alias;?>[previewText]" value="<?php echo $this->field->previewText; ?>" type="text" placeholder="<?php echo __('Preview Test'); ?>"/>
						</div>
					</div><div class="clear"></div>

					<div class="settings-container">
						<label class="settings-title">
							<?php echo __('Family', 'framework'); ?>:
						</label>
						<div class="settings-in">
							<select data-set="<?php echo $this->field->alias;?>[family]" name="<?php echo $this->field->alias;?>[family]" class="settings-select">
								<option selected="true" value="open sans"><?php echo __('Open Sans', 'framework'); ?></option>
							</select>
						</div>
					</div><div class="clear"></div>

					<div class="settings-container">
						<label class="settings-title">
							<?php echo __('Style', 'framework'); ?>:
						</label>
						<div class="settings-in">
							<select data-set="<?php echo $this->field->alias;?>[style]" name="<?php echo $this->field->alias;?>[style]" class="settings-select">
								<option <?php if($this->field->style  == '' || $this->field->style  == 'normal') { ?>selected="true" <?php } ?>value="normal"><?php echo __('Normal', 'framework'); ?></option>
								<option <?php if($this->field->style  == 'italic') { ?> selected="true" <?php } ?> value="italic"><?php echo __('Italic', 'framework'); ?></option>
							</select>
						</div>
					</div><div class="clear"></div>

					<div class="settings-container">
						<label class="settings-title">
							<?php echo __('Weight', 'framework'); ?>:
						</label>
						<div class="settings-in">
							<input data-set="<?php echo $this->field->alias;?>[weight]" name="<?php echo $this->field->alias;?>[weight]" value="<?php if( $this->field->weight == '') { ?>bold<?php } else { echo $this->field->weight; }?>" type="text" />
							<br><span class="settings-title-caption"><?php echo __('normal, bold, 300, 600, 800', 'framework'); ?></span>
						</div>
					</div><div class="clear"></div>

					<div class="settings-container">
						<label class="settings-title">
							<?php echo __('Size', 'framework'); ?>:
						</label>
						<div class="settings-in">
							<input data-set="<?php echo $this->field->alias;?>[size]" name="<?php echo $this->field->alias;?>[size]" value="<?php if( $this->field->size == '') { ?>32px<?php } else { echo $this->field->size; }?>" type="text" />
							<br><span class="settings-title-caption"><?php echo __('12, 24px, 1em, 1.75', 'framework'); ?></span>
						</div>
					</div><div class="clear"></div>

					<div class="settings-container">
						<label class="settings-title">
							<?php echo __('Color', 'framework'); ?>:
						</label>
						<div class="settings-in">
							<input data-set="<?php echo $this->field->alias;?>[color]" name="<?php echo $this->field->alias;?>[color]" value="<?php echo ($this->field->color != '') ? $this->field->color : '#000000'; ?>" type="text" class="color-picker-hex"/>
						</div>
					</div><div class="clear"></div>
					
					<div class="settings-container">
						<input type="button" value="<?php echo __('Save font select settings', 'framework'); ?>" name="<?php echo $this->field->alias;?>_save"/>
					</div>
					
				</div>
			</div>
			
			<script type="text/javascript">
				jQuery(document).ready(function($){
					jQuery('.color-picker-hex').wpColorPicker();
					
					$('.<?php echo $this->field->alias; ?> a.edit-font-options-a').on('click', function(){
						$('.<?php echo $this->field->alias; ?> .edit-font-options-inner').slideDown();
						$(this).css({'display': 'none'});
					});
					
					$('input[name=<?php echo $this->field->alias;?>_save]').on('click', function(e){
						e.preventDefault();
						e.stopPropagation();
						
						$('.<?php echo $this->field->alias; ?> .edit-font-options-inner').slideUp();
						$('.<?php echo $this->field->alias; ?> a.edit-font-options-a').css({'display': ''});
					});
				});
			</script>
		</div>

		<?php
	}
	
	public static function assign_actions_and_filters() {
		add_action( 'admin_print_scripts', array( 'Font_select_type', 'include_scripts_styles' ) );
		add_action( 'customize_register', array( 'Font_select_type', 'include_scripts_styles' ) );
	}
	
	public static function include_scripts_styles() {
		$data_type_directory = __DIR__;
		$theme_directory = THEME_DIR;
		$framework_directory = FRAMEWORK_DIR;
				
		$data_type_directory = str_replace('\\', '/', $data_type_directory);
		$theme_directory = str_replace('\\', '/', $theme_directory);
		$framework_directory = str_replace('\\', '/', $framework_directory);
		
		if(strstr($data_type_directory, $theme_directory)) {
			$current_data_type_dir = str_replace($theme_directory, '', $data_type_directory);
		}
		else {
			$current_data_type_dir = str_replace($framework_directory, '', $data_type_directory);
		}
		
		wp_register_style('font_select_type_css', FRAMEWORK_URL . $current_data_type_dir . '/css/font-select-type.css');
		wp_enqueue_style('font_select_type_css');
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

