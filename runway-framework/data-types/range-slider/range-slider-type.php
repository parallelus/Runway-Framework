<?php

class Range_slider_type extends Data_Type {

	public static $type_slug = 'range-slider-type';

	public function __construct( $page, $field, $wp_customize = null, $alias = null, $params = null ) {

		$this->type  = 'range-slider-type';
		$this->label = 'Range slider';

		parent::__construct( $page, $field, $wp_customize, $alias, $params );

	}

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		$customize_title = stripslashes( $this->field->title );
		$section         = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="' . esc_attr( $this->page->section ) . '"' : '';
		$value           = ( $vals != null ) ? $this->field->saved : $this->get_value();

		$start = '0';

		$values_string = ( $value !== null && $value !== false && $value !== '' ) ? $value : '';
		$values_string = preg_replace( "/[\[\]\s]*/", '', $values_string );
		$values_array  = explode( ',', $values_string );

		$double = false;
		if (
			isset( $this->field->startFirstEntry )
			&& strlen( $this->field->startFirstEntry ) > 0
			&& isset( $this->field->startSecondEntry )
			&& strlen( $this->field->startSecondEntry ) > 0
		) {
			$double = true;
		}

		if ( count( $values_array ) > 1 && strlen( $values_array[0] ) > 0 && strlen( $values_array[1] ) > 0 ) {
			$double = true;
		}

		if ( $double ) {
			if ( isset( $values_array[0] ) && strlen( $values_array[0] ) > 0 ) {
				$this->field->startFirstEntry = $values_array[0];
			}

			if ( isset( $values_array[1] ) && strlen( $values_array[1] ) > 0 ) {
				$this->field->startSecondEntry = $values_array[1];
			}

			$start = '[' . $this->field->startFirstEntry . ', ' . $this->field->startSecondEntry . ']';
		} else if ( $values_array[0] != '' ) {
			$this->field->startFirstEntry = $values_array[0];
			$start                        = '[' . $this->field->startFirstEntry . ']';
		}

		$connect = 'false';
		if ( $double && ( $this->field->connect == 'false' || $this->field->connect == 'true' ) ) {
			$connect = $this->field->connect;
		}

		$escaped_attr_alias = esc_attr( $this->field->alias );
		$escaped_js_alias   = esc_js( $this->field->alias );
		?>

		<legend class='customize-control-title'>
			<span><?php echo wp_kses_post( $customize_title ); ?></span>
		</legend>

		<div id="<?php echo $escaped_attr_alias; ?>" class="range-slider <?php echo esc_attr( $this->field->cssClass ); ?>">
			<div id="slider-<?php echo $escaped_attr_alias; ?>"
			     <?php if ( $this->field->orientation == 'vertical' ) { ?>style="height: 250px;"<?php } ?>>
			</div>
			<div class="slider-values">
				Slider values:
				<?php if ( $double ) { ?>
					<span class="slider-start-<?php echo $escaped_attr_alias; ?> slider-value">
						<?php echo rf_string( $this->field->startFirstEntry ); ?>
					</span>
					<span class="slider-end-<?php echo $escaped_attr_alias; ?> slider-value">
						<?php echo rf_string( $this->field->startSecondEntry ); ?>
					</span>
				<?php } else { ?>
					<span class="slider-start-<?php echo $escaped_attr_alias; ?> slider-value">
						<?php echo rf_string( $this->field->startFirstEntry ); ?>
					</span>
				<?php } ?>

				<input type="hidden"
				       class="custom-data-type"
					   <?php echo parent::add_data_conditional_display( $this->field ); ?>
					   name="<?php echo $escaped_attr_alias; ?>"
					   value="<?php echo esc_attr( $value ); ?>"
					   <?php $this->link(); ?>
					   <?php echo rf_string( $section ); // escaped above ?>
					   id="hidden-<?php echo $escaped_attr_alias; ?>"
					   data-type="range-slider"/>
			</div>

			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$('#slider-<?php echo $escaped_js_alias; ?>').noUiSlider({
						start: <?php echo esc_js( $start ); ?>,
						range: {
							'min': [<?php echo ( $this->field->rangeMin != '' ) ? esc_js( $this->field->rangeMin ) : 0; ?>],
							'max': [<?php echo ( $this->field->rangeMax != '' ) ? esc_js( $this->field->rangeMax ) : 100; ?>]
						},
						connect: <?php echo esc_js( $connect );
						if ( $this->field->margin != '' ) { ?>,
						margin: <?php echo esc_js( $this->field->margin ); ?>
						<?php }
						if ( $this->field->step != '') { ?>,
						step: <?php echo esc_js( $this->field->step );
						} ?>,
						orientation: "<?php echo esc_js( $this->field->orientation ); ?>",
						direction: "<?php echo esc_js( $this->field->direction ); ?>",
						serialization: {
							lower: [
								$.Link({
									target: $(".slider-start-<?php echo $escaped_js_alias; ?>"),
									method: "html"
								})
							],
							upper: [
								$.Link({
									target: $(".slider-end-<?php echo $escaped_js_alias; ?>"),
									method: "html"
								})
							]
						}
					});
				});
			</script>

		</div>

		<?php
		$this->wp_customize_js( $double );

		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public static function assign_actions_and_filters() {

		add_action( 'admin_print_scripts', array( 'Range_slider_type', 'include_nouislider' ) );
		add_action( 'customize_register', array( 'Range_slider_type', 'include_nouislider' ) );

	}

	public static function include_nouislider() {

		$data_type_directory   = __DIR__;
		$framework_dir         = basename( FRAMEWORK_DIR );
		$framework_pos         = strlen( $data_type_directory ) - strlen( $framework_dir ) - strrpos( $data_type_directory,	$framework_dir ) - 1;
		$current_data_type_dir = str_replace( '\\', '/', substr( $data_type_directory, - $framework_pos ) );

		wp_enqueue_script( 'rw_nouislider', FRAMEWORK_URL . $current_data_type_dir . '/js/jquery.nouislider.min.js' );
		wp_enqueue_style( 'rw_nouislider_css', FRAMEWORK_URL . $current_data_type_dir . '/css/jquery.nouislider.css' );

	}

	public function get_value() {

		$value = parent::get_value();

		if ( is_string( $value ) ) {  // because string is array always
			return $value;
		} else {
			return isset( $this->field->values ) ? $this->field->values : '';
		}

	}

	public static function render_settings() {
		?>

		<script id="range-slider-type" type="text/x-jquery-tmpl">

		    <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'CSS Class', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">

					<input data-set="cssClass" name="cssClass" value="${cssClass}" class="settings-input" type="text">

				</div>
				<div class="clear"></div>

			</div>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Start', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">
					<input data-set="startFirstEntry" name="startFirstEntry" value="${startFirstEntry}" type="text" />
					<span class="settings-field-caption"><?php echo __( 'First handle start position.', 'runway' ); ?></span>
					<input data-set="startSecondEntry" name="startSecondEntry" value="${startSecondEntry}" type="text" />
					<span class="settings-field-caption"><?php echo __( 'Second handle start position. (optional)', 'runway' ); ?></span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Range min', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">
					<input data-set="rangeMin" name="rangeMin" value="${rangeMin}" type="text" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Range max', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">
					<input data-set="rangeMax" name="rangeMax" value="${rangeMax}" type="text" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Connect', 'runway' ); ?>:
				</label>
				<div class="settings-in">
					<select data-set="connect" name="connect" class="settings-select">
						<option {{if connect == '' || connect == 'false'}} selected="true" {{/if}} value="false"><?php echo __( 'False', 'runway' ); ?></option>
						<option {{if connect == 'true'}} selected="true" {{/if}} value="true"><?php echo __( 'True', 'runway' ); ?></option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Margin', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">
					<input data-set="margin" name="margin" value="${margin}" type="text" />
					<span class="settings-field-caption">
						<?php echo __( 'When using two handles, the minimum distance between the handles can be set using the margin option', 'runway' ); ?>
					</span>
				</div>
				<div class="clear"></div>
			</div>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Step', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">
					<input data-set="step" name="step" value="${step}" type="text" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Orientation', 'runway' ); ?>:
				</label>
				<div class="settings-in">
					<select data-set="orientation" name="orientation" class="settings-select">
						<option {{if orientation == '' || orientation == 'horizontal'}} selected="true" {{/if}} value="horizontal">
							<?php echo __( 'Horizontal', 'runway' ); ?>
						</option>
						<option {{if orientation == 'vertical'}} selected="true" {{/if}} value="vertical">
							<?php echo __( 'Vertical', 'runway' ); ?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Direction', 'runway' ); ?>:
				</label>
				<div class="settings-in">
					<select data-set="direction" name="direction" class="settings-select">
						<option {{if direction == '' || direction == 'ltr'}} selected="true" {{/if}} value="ltr">
							<?php echo __( 'Left-to-right', 'runway' ); ?>
						</option>
						<option {{if direction == 'rtl'}} selected="true" {{/if}} value="rtl">
							<?php echo __( 'Right-to-left', 'runway' ); ?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<?php
			parent::render_conditional_display();
			do_action( self::$type_slug . '_after_render_settings' );
			?>

		</script>

		<?php
	}

	public static function data_type_register() {
		?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: '<?php echo __( 'Range slider', 'runway' ); ?>',
					separate: 'none',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});
			});

		</script>

		<?php
	}

	public function wp_customize_js( $double = false ) {

		$escaped_js_alias = esc_js( $this->field->alias );
		?>

		<script type="text/javascript">
			(function ($) {
				$('#slider-<?php echo $escaped_js_alias; ?>').change(function () {
					var hidden_elem = $('#hidden-<?php echo $escaped_js_alias; ?>');

					<?php if ( $double ) { ?>
						hidden_elem.val("[" + $(".slider-start-<?php echo $escaped_js_alias; ?>").text() + ", " + $(".slider-end-<?php echo $escaped_js_alias; ?>").text() + "]");
					<?php } else {?>
						hidden_elem.val("[" + $(".slider-start-<?php echo $escaped_js_alias; ?>").text() + "]");
					<?php } ?>

					if (wp.customize) {
						var alias = "<?php echo $escaped_js_alias; ?>";
						var api = wp.customize;

						api.instance(alias).set(hidden_elem.val());
					}
				});
			})(jQuery);
		</script>

		<?php
	}

}
