<?php

class Datepicker_type extends Data_Type {

	public static $type_slug = 'datepicker-type';

	public function __construct( $page, $field, $wp_customize = null, $alias = null, $params = null ) {

		$this->type  = 'datepicker-type';
		$this->label = 'Datepicker';

		parent::__construct( $page, $field, $wp_customize, $alias, $params );

	}

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( null != $vals ) {
			$this->field = (object) $vals;
			extract( $vals );
			$value = $vals['saved'];
		} else {
			$value = $this->get_value();
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

			<legend class="customize-control-title">
				<span><?php echo stripslashes($this->field->title); ?></span>
			</legend>

			<?php
			for ( $key = 0; $key < $count; $key++ ) {
				if ( isset( $this->field->value ) && is_array( $this->field->value ) ) {
					$repeat_value = isset( $this->field->value[ $key ] ) ? $this->field->value[ $key ] : '';
				} else {
					$repeat_value = '';
				}
				?>

				<script type="text/javascript">
					(function ($) {

						$(function () {
							var $inputs = $('[name="<?php echo esc_attr( $this->field->alias ); ?>[]"]');

							$inputs.datepicker({
								autoSize: false,
								dateFormat: "<?php echo stripslashes( str_replace( '"', "'", $this->field->format ) ); ?>",
								changeMonth: $inputs.data('changemonth'),
								changeYear: $inputs.data('changeyear'),

								onSelect: function (date) {
									if (typeof reinitialize_customize_instance == 'function') {
										reinitialize_customize_instance('<?php echo esc_js( $this->field->alias ); ?>');
									}
								}
							});
						});

					})(jQuery);
				</script>

				<input <?php $this->link(); ?> type="text" class="datepicker custom-data-type"
                    name="<?php echo esc_attr( $this->field->alias ); ?>[]"
                    value="<?php echo esc_attr( $repeat_value ); ?>"
					<?php echo rf_string( $section ); // escaped above ?>
                    data-format="<?php echo esc_attr( stripslashes( $this->field->format ) ); ?>"
                    data-changeMonth="<?php echo esc_attr( stripslashes( $this->field->changeMonth ) ); ?>"
                    data-changeYear="<?php echo esc_attr( stripslashes( $this->field->changeYear ) ); ?>"
                    data-type="datepicker-type"/>
				<a href="#" class="delete_datepicker_field"><?php echo __( 'Delete', 'runway' ); ?></a><br>

				<?php
			}

			$field = array(
				'field_name'   => $this->field->alias,
				'type'         => 'text',
				'class'        => 'datepicker custom-data-type',
				'data_section' => isset( $this->page->section ) ? $this->page->section : '',
				'data_type'    => 'datepicker-type',
				'after_field'  => '',
				'value'        => '#'
			);
			$this->enable_repeating( $field );
			$this->wp_customize_js();
		} else {
			$input_value = ( isset( $value ) && is_string( $value ) ) ? stripslashes( $value ) : '';
			//$input_value_unformatted =date_format(date_create($input_value), 'm/d/Y') );
			if ( ! is_string( $input_value ) && ! is_numeric( $input_value ) ) {
				if ( is_array( $input_value ) && isset( $input_value[0] ) ) {
					$input_value = $input_value[0];
				} else {
					$input_value = '';
				}
			}
			?>

			<script type="text/javascript">
				(function ($) {

					$(function () {
						var $input = $('[name="<?php echo esc_js( $this->field->alias ); ?>"]');

						$input.datepicker({
							autoSize: false,
							dateFormat: "<?php echo stripslashes( str_replace( '"', "'", $this->field->format ) ); ?>",
							changeMonth: $input.data('changemonth'),
							changeYear: $input.data('changeyear'),

							onSelect: function (date) {
								$input.attr('value', date).val(date);
								if (wp.customize) {
									var api = wp.customize;
									console.log(api);
									api.instance('<?php echo esc_js( $this->field->alias ); ?>').set(date);
								}
							}
						});
					});

				})(jQuery);
			</script>

			<legend class="customize-control-title">
				<span><?php echo esc_attr( stripslashes( $this->field->title ) ); ?></span>
			</legend>

			<input <?php $this->link(); ?> type="text" class="datepicker custom-data-type"
				name="<?php echo esc_attr( $this->field->alias ); ?>"
				value="<?php echo esc_attr( $input_value ); ?>"
				<?php echo rf_string($section); // escaped above ?>
				data-format="<?php echo esc_attr( stripslashes( $this->field->format ) ); ?>"
				<?php echo parent::add_data_conditional_display( $this->field ); ?>
				data-changeMonth="<?php echo esc_attr( stripslashes( $this->field->changeMonth ) ); ?>"
				data-changeYear="<?php echo esc_attr( stripslashes( $this->field->changeYear ) ); ?>"
				data-type="datepicker-type" />
			<div id="datapicker-dialog" name="<?php echo isset( $alias ) ? esc_attr( $alias ) : ''; ?>"
			     title="<?php echo isset( $title ) ? esc_attr( stripslashes( $title ) ) : ''; ?>" style="display:none;">
			</div>

			<?php
		}

		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public static function render_settings() {
		?>

		<script id="datepicker-type" type="text/x-jquery-tmpl">

			<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __( 'Date format', 'runway' ); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">
		            <select name="format" class="format">
		                <option {{if format == "mm/dd/yy"}} selected="true" {{/if}} value="mm/dd/yy"><?php echo __( 'Default', 'runway' ); ?> - mm/dd/yy</option>
		                <option {{if format == "yy-mm-dd"}} selected="true" {{/if}} value="yy-mm-dd">ISO 8601 - yy-mm-dd</option>
		                <option {{if format == "dd M, y"}} selected="true" {{/if}} value="dd M, y"><?php echo __( 'Short', 'runway' ); ?> - dd M, y</option>
		                <option {{if format == "dd MM, y"}} selected="true" {{/if}} value="dd MM, y"><?php echo __( 'Medium', 'runway' ); ?> - dd MM, y</option>
		                <option {{if format == "DD, dd MM, yy"}} selected="true" {{/if}} value="DD, dd MM, yy"><?php echo __( 'Full', 'runway' ); ?> - DD, dd MM, yy</option>
		                <option {{if format == "'day' d 'of' MM 'in the year' yy"}} selected="true" {{/if}} value="'day' d 'of' MM 'in the year' yy">
		                    <?php echo __( 'With text', 'runway' ); ?> - '<?php echo __( 'day', 'runway' ); ?>' d '<?php echo __( 'of', 'runway' ); ?>' MM '<?php echo __( 'in the year', 'runway' ); ?>' yy
		                </option>
		            </select>
		        </div>
		        <div class="clear"></div>
		    </div>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __( 'Values', 'runway' ); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">
		            <div id="datapicker-dialog"  title="title">
		                <input type="text" data-set="values" name="values" class="datepicker" id="datepicker" size=30
		                       value="${values}"
		                       data-format="mm/dd/yy"
		                       data-changeMonth="true"
		                       data-changeYear="true"/>
		                <input type="hidden" id="datepicker-custom-format" value="${values}" />
		            </div>
		            <span class="settings-field-caption"><?php echo __( 'You can input default value respectively with selected in format field.', 'runway' ); ?></span>
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
		                Yes
		            </label>

		            <span class="settings-field-caption"><?php echo __( 'Is this a required field?', 'runway' ); ?></span><br>

		            <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

		            <span class="settings-field-caption"><?php echo __( 'Optional. Enter a custom error message.', 'runway' ); ?></span>

		        </div>
		        <div class="clear"></div>

		    </div>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __( 'Month changer', 'runway' ); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <label>
		                {{if changeMonth == 'true'}}
		                    <input data-set="changeMonth" name="changeMonth" value="true" checked="true" type="checkbox">
		                {{else}}
		                    <input data-set="changeMonth" name="changeMonth" value="true" type="checkbox">
		                {{/if}}
		                <?php echo __( 'Yes', 'runway' ); ?>
		            </label>

		        </div>
		        <div class="clear"></div>
		    </div>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __( 'Year changer', 'runway' ); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <label>
		                {{if changeYear == 'true'}}
		                    <input data-set="changeYear" name="changeYear" value="true" checked="true" type="checkbox">
		                {{else}}
		                    <input data-set="changeYear" name="changeYear" value="true" type="checkbox">
		                {{/if}}
		                <?php echo __( 'Yes', 'runway' ); ?>
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
			return isset( $this->field->values ) ? $this->field->values : '';
		} else {
			return $value;
		}

	}

	public function sanitize_value( $value ) {

		if ( is_array( $value ) ) {

			if ( isset( $this->field->repeating ) && $this->field->repeating == 'Yes' ) {
				if ( is_array( $value ) && count( $value ) === 1 ) {
					$value = $value[0];
				}
			} else {
				if ( count( $value ) === 1 ) {
					$value = $value[0];
				}
			}

		}

		return $value;

	}

	public static function data_type_register() {
		?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: '<?php echo __( 'Datepicker', 'runway' ); ?>',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});

				function convertCustomFormatDate(date) {
					date = date.replace("<?php echo __( 'day', 'runway' ); ?>", "");
					date = date.replace("<?php echo __( 'of', 'runway' ); ?>", "");
					date = date.replace("<?php echo __( 'in the year', 'runway' ); ?>", "");

					return date;
				}

				function isCustomFormat(date) {     // check custom format of date
					if ($('.format').val() == "'<?php echo __('day', 'runway'); ?>' d '<?php echo __('of', 'runway'); ?>' MM '<?php echo __('in the year', 'runway'); ?>' yy" ) {
						date = convertCustomFormatDate(date);
					}

				    return date;
				}

				function isCustomDate(date) {       // check custom value of date field
					if (date.indexOf("<?php echo __( 'in the year', 'runway' ); ?>") >= 0) {
						date = convertCustomFormatDate(date);
						$('#datepicker-custom-format').val(date);
					}
				}

				$('body').on('focus', '#datepicker', function () {
					$(this).datepicker({
						autoSize: false,
						dateFormat: $('.format').val(),
						changeMonth: true,
						changeYear: true,
						onSelect: function (date) {
							date = isCustomFormat(date);
							$('#datepicker-custom-format').val(date);
						}
					});
				});

				$('body').on('change', '.format', function () {
					var date = $('#datepicker-custom-format').val();
					var $datepicker = $('#datepicker');

					isCustomDate(date);
					$datepicker.val($('#datepicker-custom-format').val());

					$datepicker.attr('data-format', $('.format').val());
					$datepicker.datepicker('destroy');
					$datepicker.datepicker({
						autoSize: false,
						dateFormat: $('.format').val(),
						changeMonth: true,
						changeYear: false,
						onSelect: function (date) {
							console.log("ff=" + $('.format').val());
							date = isCustomFormat(date);
							$('#datepicker-custom-format').val(date);
						}
					});

					$datepicker.datepicker(
						'setDate',
						$.datepicker.formatDate(
							$('.format').val(),
							new Date($('#datepicker-custom-format').val())
						)
					);
					$datepicker.datepicker('option', 'dateFormat', $('.format').val());
					$datepicker.datepicker('refresh');
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
								.attr('data-section', '<?php echo isset( $data_section ) ? $data_section : ''; ?>')
								.attr('data-changeMonth', '<?php echo esc_js( stripslashes( $this->field->changeMonth ) ); ?>')
								.attr('data-changeYear', '<?php echo esc_js( stripslashes( $this->field->changeYear ) ); ?>')
								.insertBefore($(this));

							field.click(function (e) {
								e.preventDefault();
							});

							$('#header').focus();
							field.after('<br>');
							field.after('<span class="field_label"> <?php echo esc_js( $after_field ); ?> </span>');
							field.next().after('<a href="#" class="delete_datepicker_field"><?php echo __( 'Delete', 'runway' ); ?></a>');

							field.datepicker({
								autoSize: false,
								dateFormat: "<?php echo stripslashes( str_replace( '"', "'", $this->field->format ) ); ?>",
								changeMonth: field.data('changemonth'),
								changeYear: field.data('changeyear'),

								onSelect: function (date) {
									if (typeof reinitialize_customize_instance == 'function') {
										reinitialize_customize_instance('<?php echo esc_js( $field_name ); ?>');
									}
								}
							});

							if (typeof reinitialize_customize_instance == 'function') {
								reinitialize_customize_instance('<?php echo esc_js( $field_name ); ?>');
							}
						});

						$('body').on('click', '.delete_datepicker_field', function (e) {
							var $this = $(this);

							e.preventDefault();

							$this.prev('.field_label').remove();
							$this.prev().remove();
							$this.next('br').remove();
							$this.remove();

							if (typeof reinitialize_customize_instance == 'function') {
								reinitialize_customize_instance('<?php echo esc_js( $field_name ); ?>');
							}
						});

						if (wp.customize) {
							if (typeof reinitialize_customize_instance == 'function') {
								var api = wp.customize;
								api.bind('ready', function () {
									reinitialize_customize_instance('<?php echo esc_js( $field_name ); ?>');
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

}
