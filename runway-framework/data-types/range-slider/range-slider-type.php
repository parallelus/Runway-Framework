<?php

class Range_slider_type extends Data_Type {

	public $type = 'range-slider-type';
	public static $type_slug = 'range-slider-type';
	public $label = 'Range slider';
	
	public function __construct($page, $field, $wp_customize = null, $alias = null, $params = null) {
		parent::__construct($page, $field, $wp_customize, $alias, $params);
	}

	public function render_content( $vals = null ) {

		do_action(self::$type_slug . '_before_render_content', $this);
		
		$customize_title = stripslashes($this->field->title);
		$section = ( isset($this->page->section) && $this->page->section != '' ) ? 'data-section="' . $this->page->section . '"' : '';
		$value = ( $vals != null ) ? $this->field->saved : $this->get_value();
		
		$start = "0";
		if($this->field->startFirstEntry != '' && $this->field->startSecondEntry != '')
			$start = "[".$this->field->startFirstEntry.", ".$this->field->startSecondEntry."]";
		else if($this->field->startFirstEntry != '')
			$start = "[".$this->field->startFirstEntry."]";
		if($value !== null && $value !== false && $value !== "")
			$start = $value;
		
		$connect = 'false';
		if($this->field->connect == 'false' || $this->field->connect == 'true')
			$connect = $this->field->connect;
		else
			$connect = '"'.$this->field->connect.'"'
		?>

		<legend class='customize-control-title'><span><?php echo $customize_title; ?></span></legend>
		
		<div id="<?php echo $this->field->alias; ?>" class="range-slider">
			<div id="slider-<?php echo $this->field->alias;?>" <?php if($this->field->orientation == 'vertical') { ?>style="height: 250px;"<?php } ?>></div>
			<div class="slider-values">
				Slider values: <?php if($this->field->startFirstEntry != '' && $this->field->startSecondEntry != '') { ?>
					<span class="slider-start-<?php echo $this->field->alias;?> slider-value"><?php echo $this->field->startFirstEntry; ?></span>
					<span class="slider-end-<?php echo $this->field->alias;?> slider-value"><?php echo $this->field->startSecondEntry; ?></span>
					
					<input type="hidden" 
					       class="custom-data-type"
					       name="<?php echo $this->field->alias;?>" 
					       value="" 
					       <?php $this->link() ?>
					       <?php echo $section; ?>
					       id="hidden-<?php echo $this->field->alias; ?>"
					       data-type="range-slider"/>
					
					<?php } else { ?>
					<span class="slider-start-<?php echo $this->field->alias;?> slider-value"><?php echo $this->field->startFirstEntry; ?></span>
					
					<input type="hidden" 
					       class="custom-data-type"
					       name="<?php echo $this->field->alias;?>" 
					       value="" 
					       <?php $this->link() ?>
					       <?php echo $section; ?>
					       id="hidden-<?php echo $this->field->alias; ?>"
					       data-type="range-slider"/>	
					
					<?php } ?>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('#slider-<?php echo $this->field->alias; ?>').noUiSlider({
						start: <?php echo $start; ?>, 
						range: {
							'min': [<?php echo ($this->field->rangeMin != "") ? $this->field->rangeMin : 0; ?>],
							'max': [<?php echo ($this->field->rangeMax != "") ? $this->field->rangeMax : 100; ?>]
						}, 
						connect: <?php echo $connect; ?><?php if($this->field->margin != "") { ?>,
						margin: <?php echo $this->field->margin; ?><?php } ?><?php if($this->field->step != "") { ?>, 
						step: <?php echo $this->field->step; ?><?php } ?>, 
						orientation: "<?php echo $this->field->orientation; ?>", 
						direction: "<?php echo $this->field->direction; ?>",
						serialization: {
							lower: [
								$.Link({
									target: $(".slider-start-<?php echo $this->field->alias;?>"),
									method: "html"
								})
							],
							upper: [
								$.Link({
									target: $(".slider-end-<?php echo $this->field->alias;?>"),
									method: "html"
								})
							]
						}
					});
				});
			</script>
			
		</div>
		
		<?php
		
		do_action( self::$type_slug . '_after_render_content', $this );
	}
	
	public static function assign_actions_and_filters() {
		
		add_action( 'admin_print_scripts', array( 'Range_slider_type', 'include_nouislider' ) );
		add_action( 'customize_register', array( 'Range_slider_type', 'include_nouislider' ) );
	}
	
	public static function include_nouislider() {
		
		if(strstr(__DIR__, THEME_DIR)) {
			$current_data_type_dir = str_replace(THEME_DIR, '', __DIR__);
		}
		else {
			$current_data_type_dir = str_replace(FRAMEWORK_DIR, '', __DIR__);
		}
		
		wp_register_script('rw_nouislider', FRAMEWORK_URL . $current_data_type_dir . '/js/jquery.nouislider.min.js');
		wp_register_style('rw_nouislider_css', FRAMEWORK_URL . $current_data_type_dir . '/css/jquery.nouislider.css');
	}

	public function get_value() {

		$value = parent::get_value();

		if (is_string($value)) {  // because string is array always
			return $value;
		} else {
			return ( isset($this->field->values) ) ? $this->field->values : '';
		}
	}

	public static function render_settings() {?>

		<script id="range-slider-type" type="text/x-jquery-tmpl">

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
				<?php echo __('Start', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
			</label>
			<div class="settings-in">
				<input data-set="startFirstEntry" name="startFirstEntry" value="${startFirstEntry}" type="text" />
				<br>
				<input data-set="startSecondEntry" name="startSecondEntry" value="${startSecondEntry}" type="text" />
				<br><span class="settings-title-caption"><?php echo __('Set second field empty for one-handle slider', 'framework'); ?></span>
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Range min', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
			</label>
			<div class="settings-in">
				<input data-set="rangeMin" name="rangeMin" value="${rangeMin}" type="text" />
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Range max', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
			</label>
			<div class="settings-in">
				<input data-set="rangeMax" name="rangeMax" value="${rangeMax}" type="text" />
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Connect', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<select data-set="connect" name="connect" class="settings-select">
					<option {{if connect == '' || connect == 'false'}} selected="true" {{/if}} value="false"><?php echo __('False', 'framework'); ?></option>
					<option {{if connect == 'true'}} selected="true" {{/if}} value="true"><?php echo __('True', 'framework'); ?></option>
				</select>
			</div>
		</div><div class="clear"></div>

		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Margin', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
			</label>
			<div class="settings-in">
				<input data-set="margin" name="margin" value="${margin}" type="text" />
				<br><span class="settings-title-caption"><?php echo __('When using two handles, the minimum distance between the handles can be set using the margin option', 'framework'); ?></span>
			</div>
		</div><div class="clear"></div>

		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Step', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
			</label>
			<div class="settings-in">
				<input data-set="step" name="step" value="${step}" type="text" />
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Orientation', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<select data-set="orientation" name="orientation" class="settings-select">
					<option {{if orientation == '' || orientation == 'horizontal'}} selected="true" {{/if}} value="horizontal"><?php echo __('Horizontal', 'framework'); ?></option>
					<option {{if orientation == 'vertical'}} selected="true" {{/if}} value="vertical"><?php echo __('Vertical', 'framework'); ?></option>
				</select>
			</div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
			<label class="settings-title">
				<?php echo __('Direction', 'framework'); ?>:
			</label>
			<div class="settings-in">
				<select data-set="direction" name="direction" class="settings-select">
					<option {{if direction == '' || direction == 'ltr'}} selected="true" {{/if}} value="ltr"><?php echo __('Left-to-right', 'framework'); ?></option>
					<option {{if direction == 'rtl'}} selected="true" {{/if}} value="rtl"><?php echo __('Right-to-left', 'framework'); ?></option>
				</select>
			</div>
		</div><div class="clear"></div>
		
		<?php do_action( self::$type_slug . '_after_render_settings' ); ?>

	    </script>

	<?php }

	public static function data_type_register() { ?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: '<?php echo __('Range slider', 'framework'); ?>',
					separate: 'none',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});
			});

		</script>

	<?php }
} ?>