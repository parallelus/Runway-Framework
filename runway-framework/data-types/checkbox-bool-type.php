<?php

class Checkbox_bool_type extends Data_Type {

	public static $type_slug = 'checkbox-bool-type';

	public function __construct( $page, $field, $wp_customize = null, $alias = null, $params = null ) {

		$this->type  = 'checkbox-bool-type';
		$this->label = 'Checkbox (true / false)';

		parent::__construct( $page, $field, $wp_customize, $alias, $params );

	}

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object) $vals;
		}
		$section = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="' . esc_attr( $this->page->section ) . '"' : '';
		if ( isset( $this->field->repeating ) && $this->field->repeating == 'Yes' ) {
			$this->get_value();

			if ( isset( $this->field->value ) && is_array( $this->field->value ) ) {
				foreach ( $this->field->value as $key => $tmp_value ) {
					if ( is_string( $key ) ) {
						unset( $this->field->value[ $key ] );
					}
				}
			}

			$count = isset( $this->field->value ) ? count( (array) $this->field->value ) : 1;
			if ( $count == 0 ) {
				$count = 1;
			}
			?>

			<fieldset>
				<legend class="customize-control-title">
					<span><?php echo stripslashes( $this->field->title ); ?></span>
				</legend>

				<?php for ( $key = 0; $key < $count; $key++ ) { ?>

					<input <?php $this->link(); ?>
						class="input-check custom-data-type" <?php echo rf_string( $section ); // escaped above ?>
						data-type="checkbox-bool-type" type="checkbox" value="true"
						name="<?php echo esc_attr( $this->field->alias ); ?>[]"
						<?php if ( isset( $this->field->value[ $key ] ) && $this->field->value[ $key ] == 'true' ) {
							echo 'checked="checked" ';
						} ?> />
					<span class="field_label"><?php _e( 'Yes', 'runway' ); ?></span>

					<a href="#" class="delete_checkbox_bool_field"><?php echo __( 'Delete', 'runway' ); ?></a><br>

					<?php
				}

				$field = array(
					'field_name'   => $this->field->alias,
					'type'         => 'checkbox',
					'class'        => 'input-check custom-data-type',
					'data_section' => isset( $this->page->section ) ? $this->page->section : '',
					'data_type'    => 'checkbox-bool-type',
					'after_field'  => __( 'Yes', 'runway' )
				);
				$this->enable_repeating( $field );
				$this->wp_customize_js();
				?>

			</fieldset>

			<?php
		} else {
			?>

			<fieldset>
				<legend class="customize-control-title">
					<span><?php echo rf__( stripslashes( $this->field->title ) ); ?></span>
				</legend>
				<input type="hidden" value="false" name="<?php echo esc_attr( $this->field->alias ); ?>"/>
				<label>
					<input <?php $this->link(); ?>
						class="input-check custom-data-type" <?php echo rf_string( $section ); // escaped above ?>
						data-type="checkbox-bool-type" <?php echo parent::add_data_conditional_display( $this->field ); ?>
						type="checkbox" value="true"
						name="<?php echo esc_attr( $this->field->alias ); ?>"
						<?php if ( $this->get_value() == 'true' || $this->get_value() === true || $this->get_value() === '1' ) {
							echo 'checked="checked" ';
						} ?> />
					<?php _e( 'Yes', 'runway' ); ?>
				</label>
			</fieldset>

			<?php
		}

		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public static function render_settings() {
		?>

		<script id="checkbox-bool-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

			<div class="settings-container">
				<label class="settings-title">
					<?php echo __( 'Value', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
				</label>
				<div class="settings-in">

					<label>
						{{if values == 'true'}}
							<input name="values" value="true" checked="true" type="checkbox">
						{{else}}
							<input name="values" value="true" type="checkbox">
						{{/if}}
						<?php echo __( 'Checked', 'runway' ); ?>
					</label>
				</div>
				<div class="clear"></div>
			</div>

			 <!-- Repeating settings -->
		    <div class="settings-container">
		    	<label class="settings-title">
					<?php echo __( 'Repeating', 'runway' ); ?>:
		        </label>
		        <div class="settings-in">
		        	<label>
	                	{{if repeating == 'Yes'}}
		                	<input data-set="repeating" name="repeating" value="Yes" checked="true" type="checkbox">
		                {{else}}
		                	<input data-set="repeating" name="repeating" value="Yes" type="checkbox">
		                {{/if}}
						<?php echo __( 'Yes', 'runway' ); ?>
	                </label>
	                <span class="settings-field-caption"><?php echo __( 'Can this field repeat with multiple values?', 'runway' ); ?></span>
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

	public function get_value() {

		$value = parent::get_value();

		if ( is_array( $value ) ) {
			return $this->field->values;
		} else {

			if ( doing_filter( 'customize_value_' . $this->field->alias ) ) {
				return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			}

			return $value;
		}

	}

	public function sanitize_value( $value ) {

		if ( is_bool( $value ) ) {

			$value = var_export( $value, true );

		} else if ( is_array( $value ) ) {

			if ( isset( $this->field->repeating ) && $this->field->repeating == 'Yes' ) {
				if ( is_array( $value ) && count( $value ) === 1 ) {
					$value = $value[0];
				} else {
					foreach ( $value as &$item ) {
						if ( is_bool( $item ) ) {
							$item = var_export( $item, true );
						}
					}
				}
			} else {
				if ( count( $value ) === 1 ) {
					$value = $value[0];
				}
			}

		}

		return $value;

	}

	public function save( $value = null ) {

		if ( is_a( $value, 'WP_Customize_Settings' ) || is_a( $value, 'WP_Customize_Setting' ) ) {
			$value = null;
		}

		if ( ! isset( $_REQUEST['customized'] ) ) {
			$page_options = get_option( $this->page->option_key );
			if ( is_object( $value ) ) {
				$value = '';
			}

			$page_options[ $this->field->alias ]                = $value;
			$page_options['field_types'][ $this->field->alias ] = $this->type;

			update_option( $this->page->option_key, $page_options );
		} else {
			$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
			$value          = $submited_value->{$this->field->alias};

			if ( is_object( $value ) ) {
				$value = '';
			} else if ( is_bool( $value ) ) {
				$value = var_export( $value, true );
			} else if ( is_array( $value ) ) {
				foreach ( $value as &$item ) {
					if ( is_bool( $item ) ) {
						$item = var_export( $item, true );
					}
				}
			}

			SingletonSaveCusomizeData::getInstance()->set_option( $this->page->option_key );
			SingletonSaveCusomizeData::getInstance()->save_data( $this->field->alias, $value, $this->type );
		}

	}

	public static function data_type_register() {
		?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: '<?php echo __( 'Checkbox (true / false)', 'runway' ); ?>',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});
			});

		</script>

		<?php
	}

	public function enable_repeating( $field = array() ) {

		if ( ! empty( $field ) ) {
			extract( $field );

			$add_id = 'add_' . $field_name;
			?>

			<div id="<?php echo esc_attr( $add_id ); ?>">
				<a href="#"><?php echo __( 'Add Field', 'runway' ); ?></a>
			</div>

			<script type="text/javascript">
				(function ($) {

					$(document).ready(function () {
						var field = $.parseJSON('<?php echo json_encode( $field ); ?>');
						var $container = $('#<?php echo esc_js( $add_id ); ?>').parent();

						$('#<?php echo esc_js( $add_id ); ?>').click(function (e) {
							e.preventDefault();

							var field = $('<input/>', {
								type: '<?php echo esc_js( $type ); ?>',
								class: '<?php echo esc_js( $class ); ?>',
								name: '<?php echo esc_js( $field_name ); ?>[]',
								value: ""
							})
								.attr('data-type', '<?php echo esc_js( $data_type ); ?>')
								.attr('data-section', '<?php echo isset( $data_section ) ? esc_js( $data_section ) : ''; ?>')
								.insertBefore($(this));

							$('#header').focus();
							field.after('<br>');
							field.after('<span class="field_label"> <?php echo esc_js( $after_field ); ?> </span>');
							field.next().after('<a href="#" class="delete_checkbox_bool_field"><?php echo __( 'Delete', 'runway' ); ?></a>');

							if (typeof reinitialize_customize_checkbox_bool_instance == 'function') {
								reinitialize_customize_checkbox_bool_instance('<?php echo esc_js( $field_name ); ?>');
							}
						});

						$('body').on('click', '.delete_checkbox_bool_field', function (e) {
							var $this = $(this);

							e.preventDefault();

							$this.prev('.field_label').remove();
							$this.prev('input').remove();
							$this.next('br').remove();
							$this.remove();

							if (typeof reinitialize_customize_checkbox_bool_instance == 'function') {
								reinitialize_customize_checkbox_bool_instance('<?php echo esc_js( $field_name ); ?>');
							}
						});

						if (wp.customize) {
							if (typeof reinitialize_customize_checkbox_bool_instance == 'function') {
								var api = wp.customize;

								api.bind('ready', function () {
									reinitialize_customize_checkbox_bool_instance('<?php echo esc_js( $field_name ); ?>');
								});
							}
						}

						setTimeout(function() {
							if (typeof check_inputs_amount === 'function') {
								check_inputs_amount($container);
							}
						}, 0);

					});

				})(jQuery);
			</script>

			<?php
		}

	}

	public function wp_customize_js() {
		?>

		<script type="text/javascript">
			(function ($) {
				$('body').on('click', 'input[name^="<?php echo esc_js( $this->field->alias ); ?>"]', function () {
					reinitialize_customize_checkbox_bool_instance('<?php echo esc_js( $this->field->alias ); ?>');
				});
			})(jQuery);

			if (typeof reinitialize_customize_checkbox_bool_instance !== 'function') {
				function reinitialize_customize_checkbox_bool_instance(alias) {
					(function ($) {
						if (wp.customize) {
							var values_array = [];
							var api = wp.customize;

							alias = alias.replace(/(\[\d*\])?\[\d*\]$/, '');
							$('input[name^="' + alias + '"]').each(function () {
								values_array.push($(this).prop("checked") ? $(this).prop("checked") : 'false');
							});

							api.instance(alias).set(values_array);
						}
					})(jQuery);
				}
			}
		</script>

		<?php
	}

}
