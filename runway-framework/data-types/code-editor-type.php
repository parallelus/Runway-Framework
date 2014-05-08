<?php
class Code_editor_type extends Data_Type {

	public $type = 'code-editor-type';
	public static $type_slug = 'code-editor-type';
	public $label = 'Code editor';
	
	public function __construct($page, $field, $wp_customize = null, $alias = null, $params = null) {
		parent::__construct($page, $field, $wp_customize, $alias, $params);
	}

	public function render_content( $vals = null ) {

		do_action(self::$type_slug . '_before_render_content', $this);

		if ($vals != null) {
			$this->field = (object) $vals;
		}

		$value = ( $vals != null ) ? $this->field->saved : $this->get_value();
		$section = ( isset($this->page->section) && $this->page->section != '' ) ? 'data-section="' . $this->page->section . '"' : '';
		$customize_title = stripslashes($this->field->title);
		?>

		<legend class='customize-control-title'><span><?php echo $customize_title; ?></span></legend>
		<div id="<?php echo $this->field->alias; ?>" class="code-editor<?php echo " " . $this->field->cssClass; ?> custom-data-type"
			<?php $this->link() ?>
			name="<?php echo $this->field->alias; ?>"
			<?php echo $section; ?>
			data-type='code-editor'><?php echo is_string( $value )? $value : ''; ?></div>
			<script>
				jQuery(document).ready(function() {
					var editor = ace.edit("<?php echo $this->field->alias; ?>");
					editor.setTheme("ace/theme/monokai");
					editor.getSession().setMode("ace/mode/javascript");
				});
			</script>
		<?php
					
		do_action( self::$type_slug . '_after_render_content', $this );
	}

	public function get_value() {

		$value = parent::get_value();

		if (is_string($value)) {  // because string is array always
			return $value;
		} else {
			return ( isset($this->field->values) ) ? $this->field->values : '';
		}
	}

	public static function render_settings() { ?>

		<script id="code-editor-type" type="text/x-jquery-tmpl">

		    <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		<div class="settings-container">
		    <label class="settings-title">
				<?php echo __('Values', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">

			<textarea data-set="values" name="values" class="settings-textarea">${values}</textarea>

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
			    {{if required == 'true'}}
			    <input data-set="required" name="required" value="true" checked="true" type="checkbox">
			    {{else}}
			    <input data-set="required" name="required" value="true" type="checkbox">
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
				<?php echo __('CSS Class', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">

			<input data-set="cssClass" name="cssClass" value="${cssClass}" class="settings-input" type="text">

			<br><span class="settings-field-caption"></span>

		    </div>

		</div><div class="clear"></div>

		<!-- Repeating settings -->
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
					name: '<?php echo __('Code editor', 'framework'); ?>',
					separate: 'none',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});
			});

		</script>

	<?php }
} ?>
