<?php

class Multi_select_type extends Data_Type {

	public static $type_slug = 'multi-select-type';

	public function __construct( $page, $field, $wp_customize = null, $alias = null, $params = null ) {

		$this->type  = 'multi-select-type';
		$this->label = 'Multiselect';

		parent::__construct( $page, $field, $wp_customize, $alias, $params );

	}

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field         = (object) $vals;
			$this->field->values = preg_replace( "/\\r\\n|\\n|\\r/", '\\n\\r', $this->field->values );
		}

		$key_values = array();
		if ( isset( $this->field->values ) && ! empty( $this->field->values ) ) {

			if ( false !== strpos( $this->field->values, "\r\n" ) ) {

				$rows = explode( "\r\n", $this->field->values );
			} else {
				$rows = explode( "\\r\\n", $this->field->values );
			}

			foreach ( $rows as $v ) {
				if ( $v != '' ) {
					$v                   = htmlspecialchars_decode( $v );
					$this->field->values = explode( '=>', $v );
					if ( count( $this->field->values ) == 1 ) {
						$key                = str_replace( ' ', '-', trim( strtolower( $this->field->values[0] ) ) );
						$key_values[ $key ] = $this->field->values[0];
					} else {
						$key                = str_replace( ' ', '-', trim( strtolower( $this->field->values[0] ) ) );
						$key_values[ $key ] = $this->field->values[1];
					}
				}
			}

		}

		$section         = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="' . esc_attr( $this->page->section ) . '"' : '';
		$customize_title = stripslashes( $this->field->title );

		if ( isset( $this->field->repeating ) && $this->field->repeating == 'Yes' ) {
			$vals = ( $vals != null ) ? $this->field->saved : $this->get_value();

			if ( isset( $vals ) && is_array( $vals ) ) {
				foreach ( $vals as $key => $tmp_value ) {
					if ( ! is_array( $tmp_value ) || is_string( $key ) ) {
						unset( $vals[ $key ] );
					}
				}
			}

			$count = isset( $vals ) ? count( (array) $vals ) : 1;
			if ( $count == 0 ) {
				$count = 1;
			}
			?>

			<legend class='customize-control-title'>
				<span><?php echo wp_kses_post( $customize_title ); ?></span>
			</legend>

			<?php
			for ( $key = 0; $key < $count; $key++ ) {
				?>

				<select multiple
				        class="input-select custom-data-type"
						<?php echo rf_string( $section ); // escaped above ?>
				        data-type="multi-select-type"
				        name="<?php echo esc_attr( $this->field->alias ); ?>[<?php echo esc_attr( $key ); ?>][]"
				        size="5" style="height: 103px;"
						<?php $this->link(); ?>>
					<option value="no"
						<?php if ( isset( $vals[ $key ][0] ) && $vals[ $key ][0] == 'no' ) { ?> selected="selected" <?php } ?>>
						<?php echo __( 'No value', 'runway' ); ?>
					</option>
					<?php foreach ( $key_values as $select_value_key => $val ) {
						if ( array_key_exists( $key, $vals ) && is_array( $vals[ $key ] ) ) {
							$checked = in_array( $select_value_key, $vals[ $key ] ) ? ' selected="selected" checked="checked"' : '';
						} else {
							$checked = '';
						}
						?>
						<option value='<?php echo esc_attr( $select_value_key ); ?>' <?php echo esc_attr( $checked ); ?>>
							<?php echo stripslashes( $val ); ?>
						</option>
					<?php } ?>
				</select>
				<a href="#" class="delete_multiselect_field"><?php echo __( 'Delete', 'runway' ); ?></a><br>

				<?php
			}

			$field = array(
				'field_name'   => $this->field->alias,
				'start_number' => $count,
				'type'         => 'select',
				'class'        => 'input-select custom-data-type',
				'data_section' => isset( $this->page->section ) ? $this->page->section : '',
				'data_type'    => 'multi-select-type',
				'after_field'  => '',
				'value'        => '#'
			);
			$this->enable_repeating( $field, $key_values );
			$this->wp_customize_js();
		} else {
			$html = '<legend class="customize-control-title"><span>' . stripslashes( $this->field->title ) . '</span></legend>';
			$html .= '<select multiple class="input-select custom-data-type" ' . $section . ' data-type="multi-select-type" ' .
			         parent::add_data_conditional_display( $this->field ) . ' name="' . $this->field->alias . '[]" size="5" style="height: 103px;">';

			$value = ( $vals != null ) ? $this->field->saved : $this->get_value();

			if (
				isset( $this->field->value[ $this->field->alias ], $this->field->value )
				&& array_key_exists( 'field_types', $this->field->value )
			) {
				$value = $this->field->value[ $this->field->alias ];
			}

			$key_values = apply_filters( $this->field->alias . '_data_options', $key_values ); // allow filters to alter values

			$html .= '<option value="no">' . __( 'No value', 'runway' ) . '</option>';
			foreach ( $key_values as $key => $val ) {
				if ( is_array( $value ) ) {
					$checked = in_array( $key, $value ) ? ' selected="selected"' : '';
				} else {
					$checked = '';
				}

				if ( $val != '' ) {
					$html .= '<option value="' . esc_attr( $key ) . '"' . $checked . '>' . stripslashes( $val ) . '</option>';
				}
			}
			$html .= '</select>';

			echo rf_string( $html );

			/* dirty hack to make multiple elms on customize.php page */
			if ( $this->is_customize_theme_page ) { ?>

				<script type="text/javascript">

					(function ($) {
						$('body').on('click', 'select[name^="<?php echo esc_js( $this->field->alias ); ?>"] option', function () {
							var values_array = [];
							$(this).parent().children("option:selected").each(function () {
								values_array.push($(this).val());
							});

							var api = wp.customize;
							api.instance('<?php echo esc_js( $this->field->alias ); ?>').set(values_array);
						});
					})(jQuery);

				</script>
				<?php
			}
			/* dirty hack to make multiple elms on customize.php page */
		}

		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public function save( $value = null ) {

		/* dirty hack to make multiple elms on customize.php page */
		$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
		$value          = $submited_value->{$this->field->alias};

		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}

		if ( is_object( $value ) ) {
			$value = '';
		}

		SingletonSaveCusomizeData::getInstance()->set_option( $this->page->option_key );
		SingletonSaveCusomizeData::getInstance()->save_data( $this->field->alias, $value, $this->type );

	}

	public function sanitize_value( $value ) {

		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}

		if ( is_object( $value ) ) {
			$value = '';
		}

		if ( is_array( $value ) ) {
			$first = reset($value);
			if ( isset( $this->field->repeating ) && $this->field->repeating == 'Yes' ) {
				if ( is_array( $first ) && count( $value ) === 1 ) {
					$value = $first;
				}
			} else {
				if ( count( $value ) === 1 ) {
					$value = $first;
				}
			}
		}

		return $value;

	}

	public static function render_settings() {
		?>

		<script id="multi-select-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

			<div class="settings-container">
			    <label class="settings-title">
					<?php echo __( 'Values', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

				<textarea data-set="values" name="values" class="settings-textarea multi-select-type">${values}</textarea>

			    </div>
			    <div class="clear"></div>
			</div>

			<div class="settings-container">
			    <label class="settings-title">
					<?php echo __( 'Required', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

					<label>
					    {{if required == 'true'}}
					        <input data-set="required" name="required" value="true" checked="true" type="checkbox">
					    {{else}}
					        <input data-set="required" name="required" value="true" type="checkbox">
					    {{/if}}
					    <?php echo __( 'Yes', 'runway' ); ?>
					</label>

					<span class="settings-field-caption"><?php echo __( 'Is this a required field?', 'runway' ); ?></span><br>

					<input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

					<span class="settings-field-caption"><?php echo __( 'Optional. Enter a custom error message.', 'runway' ); ?></span>

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

	public static function data_type_register() {
		?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: '<?php echo __( 'Multiselect', 'runway' ); ?>',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});

				function render_values(selector) {
					var str_render = new String($(selector).val());
					var result_string = new Array();
					str_render = str_render.split('\n');
					for (var key in str_render) {
						if (str_render[key] != '') {
							str_render[key] = new String(str_render[key]);
							str_render[key] = str_render[key].split('=>');
							if (str_render[key].length == 1) {
								result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-') + '=>' + $.trim(str_render[key][0]));
							}
							else if (str_render[key].length == 2) {
								if (str_render[key][1] != '') {
									result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-') + '=>' + $.trim(str_render[key][1]));
								}
								else {
									result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-') + '=>' + $.trim(str_render[key][0]));
								}
							}
						}
					}
					$(selector).val(result_string.join('\n') + '\n');
				}

				$('body').on('keyup', '.multi-select-type', function (e) {
					if (e.keyCode == 13) {
						render_values(this);
					}
				});

				$('body').on('blur', '.multi-select-type', function (e) {
					render_values(this);
				});
			});

		</script>

		<?php
	}

	public function enable_repeating( $field = array(), $default_values = array() ) {

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
						var start_radio_groups_index = <?php echo esc_js( $start_number );?>;

						$('#<?php echo esc_js( $add_id ); ?>').click(function (e) {
							e.preventDefault();
							var field = $('<select>', {
								type: '<?php echo esc_js( $type ); ?>',
								class: '<?php echo esc_js( $class ); ?>',
								name: '<?php echo esc_js( $field_name ); ?>[' + start_radio_groups_index + '][]',
								value: '',
								multiple: ''
							})
								.attr('data-type', '<?php echo esc_js( $data_type ); ?>')
								.attr('data-section', '<?php echo isset( $data_section ) ? $data_section : ''; ?>')
								.css({'height': '103px'});
							start_radio_groups_index++;

							field.append('<option value="no"><?php echo __( 'No value', 'runway' ); ?></option>');
							<?php foreach($default_values as $val_key=>$val) {
							$html = '<option value="' . esc_attr( $val_key ) . '" >' . esc_js( stripslashes( $val ) ) . '</option>';
							?>
							field.append('<?php echo $html; ?>');
							<?php } ?>

							field.insertBefore($(this));

							field.click(function (e) {
								e.preventDefault();
							});

							$('#header').focus();
							field.after('<br>');
							field.after('<span class="field_label"> <?php echo esc_js( $after_field ); ?> </span>');
							field.next().after('<a href="#" class="delete_multiselect_field"><?php echo __( 'Delete', 'runway' ); ?></a>');

							if (typeof reinitialize_customize_multiselect_instance == 'function') {
								reinitialize_customize_multiselect_instance('<?php echo esc_js( $field_name ) ?>');
							}
						});

						$('body').on('click', '.delete_multiselect_field', function (e) {
							var $this = $(this);

							e.preventDefault();

							$this.prev('.field_label').remove();
							$this.prev().remove();
							$this.next('br').remove();
							$this.remove();

							if (typeof reinitialize_customize_multiselect_instance == 'function') {
								reinitialize_customize_multiselect_instance('<?php echo esc_js( $field_name ); ?>');
							}
						});

						if (wp.customize) {
							if (typeof reinitialize_customize_multiselect_instance == 'function') {
								var api = wp.customize;
								api.bind('ready', function () {
									reinitialize_customize_multiselect_instance('<?php echo esc_js( $field_name ); ?>');
								});
							}
						}
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
				$('body').on('click', 'select[name^="<?php echo esc_js( $this->field->alias ); ?>"] option', function () {
					reinitialize_customize_multiselect_instance('<?php echo esc_js( $this->field->alias ); ?>');
				});
			})(jQuery);

			if (typeof reinitialize_customize_multiselect_instance !== 'function') {
				function reinitialize_customize_multiselect_instance(alias) {
					(function ($) {
						if (wp.customize) {
							var values_array = [];
							alias = alias.replace(/(\[\d*\])?\[\d*\]$/, '');
							$('select[name^="' + alias + '"]').each(function () {
								var tmp_array = [];
								$(this).children('option:selected').each(function () {
									tmp_array.push($(this).val());
								});
								values_array.push(tmp_array);
								if (values_array[values_array.length - 1].length == 0)
									values_array[values_array.length - 1].push('no');
							});

							var api = wp.customize;
							api.instance(alias).set(values_array);
						}
					})(jQuery);
				}
			}
		</script>

		<?php
	}

}
