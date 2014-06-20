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
		
		<?php if(isset($this->field->editorType) && $this->field->editorType === 'ace') { ?>
		
		<div id="<?php echo $this->field->alias; ?>" class="code-editor" 
		     style="<?php echo isset($this->field->editorWidth) ? 'width: '.$this->field->editorWidth.'px; ': ' '; ?> 
			    <?php echo isset($this->field->editorHeight) ? 'height: '.$this->field->editorHeight.'px; ': ''; ?>"><?php echo is_string( $value )? $value : ''; ?></div>
		<input  type="hidden" 
			class="code-editor<?php echo " " . $this->field->cssClass; ?> custom-data-type ace_editor"
			name="<?php echo $this->field->alias; ?>" 
			<?php $this->link() ?>
			id="hidden-<?php echo $this->field->alias; ?>"
			name="<?php echo $this->field->alias; ?>"
			<?php echo $section; ?>
			value="<?php echo is_string( $value )? $value : ''; ?>"
			data-type='code-editor'/>
			<script>
				jQuery(document).ready(function() {
					var editor = ace.edit("<?php echo $this->field->alias; ?>");
					
					<?php if(isset($this->field->enableVim) && ($this->field->enableVim === 'true' || $this->field->enableVim === true)) { ?>
					ace.require("ace/lib/net").loadScript("https://rawgithub.com/ajaxorg/ace-builds/master/src-min-noconflict/keybinding-vim.js", 
					function() { 
					    e = document.querySelector("#<?php echo $this->field->alias; ?>").env.editor; 
					    e.setKeyboardHandler(ace.require("ace/keyboard/vim").handler); 
					});
					<?php } ?>
					
					editor.setTheme("ace/theme/chrome");
					editor.getSession().setMode("ace/mode/<?php echo (isset($this->field->editorLanguage)) ? strtolower($this->field->editorLanguage) : 'javascript'; ?>");
					editor.getSession().on('change', function(e) {
						var editor = ace.edit("<?php echo $this->field->alias; ?>");
						var code = editor.getSession().getValue();
						jQuery('#hidden-<?php echo $this->field->alias; ?>').val(code);
					}); 
				});
			</script>
		<?php } else { ?>
			<textarea id="<?php echo $this->field->alias; ?>" class="code-editor<?php echo " " . $this->field->cssClass; ?> custom-data-type"
			<?php $this->link() ?>
			name="<?php echo $this->field->alias; ?>"
			<?php echo $section; ?>
			data-type='code-editor'><?php echo is_string( $value )? $value : ''; ?></textarea>
		<?php } ?>
		<?php
					
		do_action( self::$type_slug . '_after_render_content', $this );
	}
	
	public static function assign_actions_and_filters() {
		add_filter( 'get_options_data_type_' . self::$type_slug,  array('Code_editor_type', 'code_editor_filter'), 5, 10 );
	}
	
	public static function code_editor_filter($val) {
		$val = stripslashes($val);
		$val = htmlspecialchars_decode($val, ENT_QUOTES);
		return $val;
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
		
		<div class="settings-container">
		    <label class="settings-title">
				<?php echo __('Width', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">

			<input data-set="editorWidth" name="editorWidth" value="${editorWidth}" class="settings-input" type="text">

			<br><span class="settings-field-caption"></span>

		    </div>

		</div><div class="clear"></div>
		
		<div class="settings-container">
		    <label class="settings-title">
				<?php echo __('Height', 'framework'); ?>:
				<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">

			<input data-set="editorHeight" name="editorHeight" value="${editorHeight}" class="settings-input" type="text">

			<br><span class="settings-field-caption"></span>

		    </div>

		</div><div class="clear"></div>

		<div class="settings-container">
		    <label class="settings-title">
			<?php echo __('Code editor type', 'framework'); ?>:
			<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">
			
			<select name="editorType">
				<option {{if editorType == "default"}} selected="true" {{/if}} value="default"><?php echo __('Default', 'framework'); ?></option>
				<option {{if editorType == "ace"}} selected="true" {{/if}} value="ace"><?php echo __('ACE', 'framework'); ?></option>
			</select>
			
		    </div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
		    <label class="settings-title">
			<?php echo __('Editor language', 'framework'); ?>:
			<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">
			
			<select name="editorLanguage">
				<option {{if editorLanguage == "javascript"}} selected="true" {{/if}} value="javascript">JavaScript</option>
				<option {{if editorLanguage == "css"}} selected="true" {{/if}} value="css">CSS</option>
			</select>
			
		    </div>
		</div><div class="clear"></div>
		
		<div class="settings-container">
		    <label class="settings-title">
			<?php echo __('Enable Vim keys', 'framework'); ?>:
			<br><span class="settings-title-caption"></span>
		    </label>
		    <div class="settings-in">
			
			<label>
			    {{if enableVim == 'true'}}
			    <input data-set="enableVim" name="enableVim" value="true" checked="true" type="checkbox">
			    {{else}}
			    <input data-set="enableVim" name="enableVim" value="true" type="checkbox">
			    {{/if}}
			    <?php echo __('Yes', 'framework'); ?>
			</label>
			
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
