<?php

class Font_select_type extends Data_Type {

	public $type = 'font-select-type';
	public static $type_slug = 'font-select-type';
	public $label = 'Font select';
	
	public function __construct($page, $field, $wp_customize = null, $alias = null, $params = null) {
		
		parent::__construct($page, $field, $wp_customize, $alias, $params);
	}
	
	private function wp_get_google_webfonts_list($key = '', $sort = 'alpha') {
		/*
		  $key = Web Fonts Developer API
		  $sort=
		  alpha: Sort the list alphabetically
		  date: Sort the list by date added (most recent font added or updated first)
		  popularity: Sort the list by popularity (most popular family first)
		  style: Sort the list by number of styles available (family with most styles first)
		  trending: Sort the list by families seeing growth in usage (family seeing the most growth first)
		 */

		global $wp_filesystem;
		$font_list = array();
		
		$google_api_url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . $key . '&sort=' . $sort;
		//lets fetch it
		$response = wp_remote_retrieve_body(wp_remote_get($google_api_url, array('sslverify' => false)));
		if (is_wp_error($response)) {
			$response = $wp_filesystem->get_contents(__DIR__.'/data/web_fonts.json');
		}

		if($response !== false) {
			$data = json_decode($response, true);
			if(!isset($data['items'])) {
				$response = $wp_filesystem->get_contents(__DIR__.'/data/web_fonts.json');
				$data = json_decode($response, true);
			}
			if($response !== false) {
				$items = $data['items'];
				foreach ($items as $item) {
					$font_list[] .= $item['family'];
				}
			}
		}
	
		//Return the saved lit of Google Web Fonts
		return $font_list;
	}

	public function render_content( $vals = null ) {
		$input_value = ( $vals != null ) ? $this->field->saved : $this->get_value();
		$font_family = isset($this->field->family)? $this->field->family : 'Open Sans';
		$font_weight = ($this->field->weight != '') ? $this->field->weight : 'normal';
		$font_size = ($this->field->size != '') ? $this->field->size : '20px';
		$font_style = (isset($this->field->style) && $this->field->style != '') ? $this->field->style: 'normal';
		$font_color = (isset($this->field->color) && $this->field->color != '') ? $this->field->color : '#000000';
		$previewText = isset($this->field->previewText)? $this->field->previewText : '';
		
		global $developer_tools;
		$current_theme = rw_get_theme_data();
		$t = runway_admin_themes_list_prepare( $current_theme );
		$options = $developer_tools->load_settings( $t['folder'] );

		$google_fonts = $this->wp_get_google_webfonts_list(isset($options['WebFontAPIKey']) ? $options['WebFontAPIKey'] : '');

		if(is_array($input_value)) {
			if(isset($input_value['family']))
				$font_family = $input_value['family'];
			if(isset($input_value['style']))
				$font_style = $input_value['style'];
			if(isset($input_value['weight']))
				$font_weight = $input_value['weight'];
			if(isset($input_value['size']))
				$font_size = $input_value['size'];
			if(isset($input_value['color']))
				$font_color = $input_value['color'];
			if(isset($input_value['previewText']))
				$previewText = $input_value['previewText'];
			
		}
		?>

		<div class="<?php echo $this->field->alias; ?> custom-data-type">

			<div style="font-family: 
					<?php echo $font_family;?>; 
					<?php if(isset($font_style) && !empty($font_style)): ?>
						font-style: <?php echo $font_style;?>; 
					<?php endif; ?>
					font-weight: <?php echo $font_weight;?>;
					font-size: <?php echo $font_size;?>; 
					<?php if(isset($font_color) && !empty($font_color)): ?>
						color: <?php echo $font_color; ?>
					<?php endif; ?>">
					<?php echo ($previewText != '') ? $previewText : ucwords(str_replace('-', ' ', $font_family));?>
			</div>

			<input class="custom-data-type" <?php echo parent::add_data_conditional_display($this->field); ?> data-set="<?php echo $this->field->alias;?>[previewText]" name="<?php echo $this->field->alias;?>[previewText]" value="<?php echo $previewText; ?>" type="hidden"/>
			<input class="custom-data-type" <?php echo parent::add_data_conditional_display($this->field); ?> data-set="<?php echo $this->field->alias;?>[family]" name="<?php echo $this->field->alias;?>[family]" value="<?php echo $font_family; ?>" type="hidden"/>
			<input class="custom-data-type" <?php echo parent::add_data_conditional_display($this->field); ?> data-set="<?php echo $this->field->alias;?>[style]" name="<?php echo $this->field->alias;?>[style]" value="<?php echo $font_style; ?>" type="hidden"/>
			<input class="custom-data-type" <?php echo parent::add_data_conditional_display($this->field); ?> data-set="<?php echo $this->field->alias;?>[weight]" name="<?php echo $this->field->alias;?>[weight]" value="<?php echo $font_weight; ?>" type="hidden"/>
			<input class="custom-data-type" <?php echo parent::add_data_conditional_display($this->field); ?> data-set="<?php echo $this->field->alias;?>[size]" name="<?php echo $this->field->alias;?>[size]" value="<?php echo $font_size; ?>" type="hidden"/>
			<input class="custom-data-type" <?php echo parent::add_data_conditional_display($this->field); ?> data-set="<?php echo $this->field->alias;?>[color]" name="<?php echo $this->field->alias;?>[color]" value="<?php echo $font_color; ?>" type="hidden"/>

			<div class="<?php echo $this->field->alias; ?>">
				<a href="#" onclick="return false" class="edit-font-options-a"><?php echo __('Edit Font Options', 'framework'); ?></a>
				<div class="font-options-container pop" style="display:none">
				<div class="settings-font-options-dialog">
					<div class="toogle-font-select-container">
							
							<div class="settings-container">
								<label class="settings-title">
									<?php echo __('Preview text', 'framework'); ?>:
								</label>
								<div class="settings-in">
									<input data-set="<?php echo $this->field->alias;?>[_previewText]" name="<?php echo $this->field->alias;?>[_previewText]" value="<?php echo $previewText; ?>" type="text" placeholder="<?php echo __('Preview Test', 'framework'); ?>"/>
								</div>
							</div><div class="clear"></div>

							<div class="settings-container">
								<label class="settings-title">
									<?php echo __('Family', 'framework'); ?>:
								</label>
								<div class="settings-in">
									<select data-set="<?php echo $this->field->alias;?>[_family]" name="<?php echo $this->field->alias;?>[_family]" data-type="font-select" class="settings-select">
										<?php if(is_array($google_fonts) && !empty($google_fonts)) { ?>
										<?php foreach($google_fonts as $font) { ?>
										<option <?php if($font_family == $font) echo "selected='true'"; ?>value="<?php echo $font; ?>"><?php echo $font; ?></option>
										<?php } ?>
										<?php } else { ?>
										<option selected="true" value="open sans"><?php echo __('Open Sans', 'framework'); ?></option>
										<?php } ?>
									</select>
								</div>
							</div><div class="clear"></div>

							<div class="settings-container">
								<label class="settings-title">
									<?php echo __('Style', 'framework'); ?>:
								</label>
								<div class="settings-in">
									<select data-set="<?php echo $this->field->alias;?>[_style]" name="<?php echo $this->field->alias;?>[_style]" data-type="font-select" class="settings-select">
										<option <?php if($font_style  == '' || $font_style  == 'normal') { ?>selected="true" <?php } ?>value="normal"><?php echo __('Normal', 'framework'); ?></option>
										<option <?php if($font_style  == 'italic') { ?> selected="true" <?php } ?> value="italic"><?php echo __('Italic', 'framework'); ?></option>
									</select>
								</div>
							</div><div class="clear"></div>

							<div class="settings-container">
								<label class="settings-title">
									<?php echo __('Weight', 'framework'); ?>:
								</label>
								<div class="settings-in">
									<input data-set="<?php echo $this->field->alias;?>[_weight]" name="<?php echo $this->field->alias;?>[_weight]" value="<?php if( $this->field->weight == '') { ?>bold<?php } else { echo $font_weight; }?>" type="text" data-type="font-select"  />
									<br><span class="settings-title-caption"><?php echo __('normal, bold, 300, 600, 800', 'framework'); ?></span>
								</div>
							</div><div class="clear"></div>

							<div class="settings-container">
								<label class="settings-title">
									<?php echo __('Size', 'framework'); ?>:
								</label>
								<div class="settings-in">
									<input data-set="<?php echo $this->field->alias;?>[_size]" name="<?php echo $this->field->alias;?>[_size]" value="<?php if( $this->field->size == '') { ?>32px<?php } else { echo $font_size; }?>" type="text" data-type="font-select" />
									<br><span class="settings-title-caption"><?php echo __('12, 24px, 1em, 1.75', 'framework'); ?></span>
								</div>
							</div><div class="clear"></div>

							<div class="settings-container">
								<label class="settings-title">
									<?php echo __('Color', 'framework'); ?>:
								</label>
								<div class="settings-in">
									<input data-set="<?php echo $this->field->alias;?>[_color]" name="<?php echo $this->field->alias;?>[_color]" value="<?php echo ($font_color != '') ? $font_color : '#000000'; ?>" type="text" class="color-picker-hex" data-type="font-select" />
								</div>
							</div><div class="clear"></div>

							<div class="settings-container">
								<input type="button" value="<?php echo __('Save font select settings', 'framework'); ?>" name="<?php echo $this->field->alias;?>_save"/>
								<input type="button" value="<?php echo __('Cancel', 'framework'); ?>" name="<?php echo $this->field->alias;?>_cancel"/>
							</div>

					</div>
					<script type="text/javascript">
						var alias = '<?php echo $this->field->alias; ?>';
						jQuery(document).ready(function($){

							function deselect(e) {
							  $('.<?php echo $this->field->alias; ?> .pop').slideFadeToggle(function() {
							    e.removeClass('font-edit');
							  });    
							}

							$.fn.slideFadeToggle = function(easing, callback) {
							  return this.animate({ opacity: 'toggle', height: 'toggle' }, 'fast', easing, callback);
							};

							jQuery('.<?php echo $this->field->alias; ?> .toogle-font-select-container .color-picker-hex').wpColorPicker({ change: function () {
									var hexcolor = jQuery( this ).wpColorPicker( 'color' );
									
									//setTimeout(function () {
										$('.<?php echo $this->field->alias; ?> .toogle-font-select-container .color-picker-hex').attr('value', hexcolor).val(hexcolor).trigger('change');
									//}, 50);

								}});
							//);

							$('input[name=<?php echo $this->field->alias;?>_save]').on('click', function(e){
								e.preventDefault();
								e.stopPropagation();

								var alias = "<?php echo $this->field->alias;?>";

								$('input[name="'+alias+'[previewText]"]').val($('input[name="'+alias+'[_previewText]"]').val());
								$('input[name="'+alias+'[family]"]').val($('select[name="'+alias+'[_family]"]').val());
								$('input[name="'+alias+'[style]"]').val($('select[name="'+alias+'[_style]"]').val());
								$('input[name="'+alias+'[weight]"]').val($('input[name="'+alias+'[_weight]"]').val());
								$('input[name="'+alias+'[size]"]').val($('input[name="'+alias+'[_size]"]').val());
								$('input[name="'+alias+'[color]"]').val($('input[name="'+alias+'[_color]"]').val());

								if ( wp.customize ) {
									var api = wp.customize;
									var values_array = {};
									
									$('.<?php echo $this->field->alias; ?> .settings-font-options-dialog .toogle-font-select-container input[type=text]').each(function(){
										var name = $(this).attr('name').replace(alias, '').replace("[", "").replace("]", "");
										
										values_array[name] = $(this).val();

										//api.instance($(this).attr('name')).set($(this).val());
									});
									$('.<?php echo $this->field->alias; ?> .toogle-font-select-container select option:selected').each(function(){
										var name = $(this).parent('select').attr('name').replace(alias, '').replace("[", "").replace("]", "");
										values_array[name] = $(this).attr('value');

										//api.instance($(this).parent('select').attr('name')).set($(this).attr('value'));
									});
									var name = $('.<?php echo $this->field->alias; ?> .toogle-font-select-container .color-picker-hex').attr('name').replace(alias, '').replace("[", "").replace("]", "");
									values_array[name] = $('.<?php echo $this->field->alias; ?> .toogle-font-select-container .color-picker-hex').val();

									api.instance(alias).set(values_array);
									//api.instance($('.<?php echo $this->field->alias; ?> .edit-font-options-inner .color-picker-hex').attr('name')).set($('.<?php echo $this->field->alias; ?> .edit-font-options-inner .color-picker-hex').val());
								}
								deselect($(this));
							});

							$('.<?php echo $this->field->alias; ?> .toogle-font-select-container .color-picker-hex').wpColorPicker({ change: function () {
								var hexcolor = jQuery( this ).wpColorPicker( 'color' );
						
									//setTimeout(function () {
										$('.<?php echo $this->field->alias; ?> .toogle-font-select-container .color-picker-hex').attr('value', hexcolor).val(hexcolor).trigger('change');
									//}, 50);

							}});


							$('.<?php echo $this->field->alias; ?> a.edit-font-options-a').on('click', function(e){console.log('11111');
								e.preventDefault();
								e.stopPropagation();
 	
    							if($(this).hasClass('font-edit')) {
      								deselect($(this));               
    							} else {
      								$(this).addClass('font-edit');
      								$('.<?php echo $this->field->alias; ?> .pop').slideFadeToggle();
    							}
  							});

						    $('input[name=<?php echo $this->field->alias; ?>_cancel]').on('click', function(e) {
						    	e.preventDefault();
								e.stopPropagation();
						    	deselect($(this));
						    //return false;
						  	});
						});
					</script>					
				</div>
			</div>
			</div>	

		</div>
		

		</div>

		<?php
	}
	
	public static function assign_actions_and_filters() {
		add_action( 'admin_print_scripts', array( 'Font_select_type', 'include_scripts_styles' ) );
		add_action( 'customize_register', array( 'Font_select_type', 'include_scripts_styles' ) );
	}
	
	public static function include_scripts_styles() {
		$data_type_directory = __DIR__;
		$framework_dir = basename(FRAMEWORK_DIR);
		$framework_pos = strlen($data_type_directory) - strlen($framework_dir) - strrpos($data_type_directory, $framework_dir) - 1;
		$current_data_type_dir = str_replace('\\', '/', substr($data_type_directory, - $framework_pos));
		
		/*
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
		*/
		
		wp_register_style('font_select_type_css', FRAMEWORK_URL . $current_data_type_dir . '/css/font-select-type.css');
		wp_enqueue_style('font_select_type_css');
	}
	
	public function get_value() {
		$this->field->value = $this->page->get_val( $this->field->alias );		

		if ( is_array($this->field->value) && empty( $this->field->value ) ) {
			$this->field->value = ( isset( $this->field->values ) ) ? $this->field->values : ''; // error check for notice "Undefined property: stdClass::$values"
		}

		if(is_object($this->field->value)) {
			$this->field->value = "";
		}
		
		$this->field = apply_filters( self::$type_slug . '_get_value_filter', $this->field );
		
		return $this->field->value;
	}
	
	public function save( $value = '' ) {
		if(is_a($value, 'WP_Customize_Settings') || is_a($value, 'WP_Customize_Setting'))
			$value = null;
		
		if(!isset($_REQUEST['customized'])) {
			$page_options = get_option( $this->page->option_key );
			if(is_object($value)) {
				$value = "";
			}

			$page_options[$this->field->alias] = $value;

			$page_options['field_types'][$this->field->alias] = $this->type;
			update_option( $this->page->option_key, $page_options );
		}
		else {
			$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
			$value = $submited_value->{$this->field->alias};
			
			if(is_object($value)) {
				$arr = array();
				foreach($value as $k => $v) {
					$arr[$k] = $v;
				}
				$value = $arr;
			}			
						
			SingletonSaveCusomizeData::getInstance()->set_option($this->page->option_key);
			SingletonSaveCusomizeData::getInstance()->save_data($this->field->alias, $value, $this->type);
		}
	}

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
				<?php echo __('Family', 'framework'); ?>:
			</label>
			<div class="settings-in">

				<input data-set="family" name="family" value="{{if family == ''}}Open Sans{{else}}${family}{{/if}}" type="text" />
				<br><span class="settings-title-caption"></span>				
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
		
		<?php parent::render_conditional_display(); ?>
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