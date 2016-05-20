<?php
class Html {
	public $object;

	public function __construct( $object ) {
		$this->object = $object;
	}

	function settings_form_header( $args = array() ) {
		$keys = isset( $_GET['keys'] ) ? $_GET['keys'] : '';
		$defaults = array( 'action' => 'save', 'keys' => $keys );
		$args = wp_parse_args( $args, $defaults ); ?>
			<?php $url = $this->settings_link( false, $args ); ?>
				<form method="post" action='<?php echo esc_url($url); ?>'>
			<?php
	}

	/*
	**
	**
	*/
	function updown_link( $nbr, $total, $args = array() ) {
		$html = '';
		$link = array( 'row' => $nbr, 'navigation' => $this->object->navigation, 'action' => 'move' );

		// Are we adding more stuff to our link?
		if ( !empty( $args ) ) $link = array_merge( $link, $args );

		// Build the links
		if ( $nbr > 0 ) $html .= ' | ' . $this->settings_link( '&uarr;', array_merge( $link, array( 'direction' => 'up' ) ) );
		if ( $nbr < $total - 1 ) $html .= ' | ' . $this->settings_link( '&darr;', array_merge( $link, array( 'direction' => 'down' ) ) );
		return $html;
	}

	/*
	**
	**
	*/
	function table_header( $titles ) { ?>

		<table class="widefat">
			<thead>
				<tr>
		<?php
		foreach ( (array) $titles as $title ) : ?>
			<th><?php echo  $title; ?></th>
		<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
		<?php
	}

	/*
	**
	**
	*/
	function table_footer( $titles ) { ?>

		</tbody><tfoot><tr>
		<?php
		foreach ( (array) $titles as $title ) : ?>
			<th><?php echo  $title; ?></th>
		<?php endforeach; ?>
		</tr></tfoot></table>
		<?php

	}

	/*
	**
	**
	*/
	function table_row( $contents, $nbr, $class = '' ) {
		$class .= ( $nbr++ % 2 ) ? ' alternate ' : '' ; ?>
		<tr class="<?php echo esc_attr($class); ?>">
		<?php
		$count = 1;
		$total = count( $contents );
		foreach ( (array) $contents as $content ) {
			?>
			<td <?php echo ( $count == $total ) ? 'class="last-td"' : ''; ?>><?php echo  $content; ?></td>
		<?php $count++; } ?>
		</tr>
		<?php
	}

	/*
	**
	**
	*/
	function setting_row( $cols, $class = '' ) { ?>

		<tr class="<?php echo esc_attr($class); ?>"><th scope="row" valign="top">
			<?php echo array_shift( $cols ); ?></th>
		<?php
		foreach ( $cols as $col ) { ?>
			<td><?php echo  $col; ?></td>
		<?php
		} ?></tr>
		<?php
	}

	/*
	**
	**
	*/
	function settings_link( $text, $args ) {

		$link = $this->object->options_url;

		foreach ( $args as $key => $value ) {
			if ( $key == 'class' ) continue;
			if ( !$value ) continue;
			if ( is_array( $value ) ) $value = implode( ',', $value );
			$link .= '&' . $key . '=' . urlencode( $value );
		}

		$link = esc_url(wp_nonce_url( $link, $this->object->nonce_action( $args ) ));
		$args['class'] = isset( $args['class'] ) ? $args['class'] : '';
		$class = ( $c = $args['class'] ) ? $c : 'more-common';
		$html = "<a class='$class' href='$link'>$text</a>";

		if ( !$text ) {
			return $link;
		}

		return $html;
	}

	/*
	**
	**
	*/
	function settings_input( $name, $s = null, $additional_options = null ) {

		$cssClass = '';
		if ( !$s || is_array( $s ) ) {
			$value = esc_attr( $this->object->get_val( $name, $s ) );
		} else {
			$value = $s;
		}

		if ( empty( $value ) && isset( $additional_options->values ) ) $value = $additional_options->values;
		if ( isset( $additional_options->cssClass ) ) $cssClass = $additional_options->cssClass;

		$html = '<input class="input-text '.@$cssClass.'" type="text" name="' . $name . '" value="' . $value . '">';
		return $html;

	}
	/*
	**
	**
	*/
	function settings_bool( $name, $set = array() ) {

		$vars = array( true => 'Yes', false => 'No' );
		$set = $this->object->get_val( $name );
		if ( is_array( $set ) ) {
			$set = 0;
		}
		$html = $this->settings_radiobuttons( $name, $vars, array(), $set );
		return $html;

	}

	function settings_radiobuttons( $name, $vars, $comments = array(), $checked = 1 ) {

		$html = '';
		$set = $this->object->get_val( $name );
		if ( !isset( $set ) || empty( $set ) ) {
			$set = $checked;
		}

		foreach ( $vars as $key => $value ) {
			$checked = ( $key == $set ) ? ' checked="checked"' : '';
			$html .= "<label><input class='input-radio' type='radio' name='$name' value='$key' $checked /> $value</label> ";

			if ( isset( $comments[$key] ) && $c == $comments[$key] ) {
				$html .= $this->format_comment( $c );
			}
		}

		return $html;

	}

	function settings_radiobuttons_image( $name, $vars, $comments = array(), $image_size, $checked = 1 ) {

		$html = '';
		$set = $this->object->get_val( $name );

		if ( !isset( $set ) || empty( $set ) ) {
			$set = $checked;
		}

		foreach ( $vars as $key => $value ) {
			$checked = ( $key == $set ) ? ' checked="checked"' : '';
			$html .= "<label><input class='input-radio' type='radio' name='$name' value='$key' $checked /><img src='$value' width='$image_size' height='$image_size'> </label> ";
			if ( isset( $comments[$key] ) ) {
				if ( $c = $comments[$key] ) {
					$html .= $this->format_comment( $c );
				}
			}
		}
		return $html;

	}

	function settings_hidden( $name, $var = 0 ) {

		if ( !$var ) {
			$var = $this->object->get_val( $name );
		}

		// added condition to test for array so hidden can also be used with individual fields
		if ( is_array( $var ) ) {
			$value = ( $var ) ? json_encode( $this->object->slasherize( $var ) ) : '';
		} else {
			$value = $var;
		}

		$html = $typeof ."<input type='hidden' name='$name' value='$value'>";

		return $html;

	}

	/*
	**
	**
	*/
	function checkbox_list( $name, $vars, $options = array(), $checked_list = array() ) {

		$values = (array) $this->object->get_val( $name );

		if ( empty( $values[0] ) ) {
			$values = $checked_list;
		}

		if ( $values[0] == 'Array' ) {
			$values = array( 0 => $options['values'] );
		}

		$html = '';

		foreach ( $vars as $key => $val ) {
			// Options will over-ride values
			$class = ( $a = $options[$key]['class'] ) ? 'class="' . $a . '"' : '';
			$readonly = ( $options[$key]['disabled'] ) ? ' disabled="disabled"' : '';

			if ( array_key_exists( 'value', (array) $options[$key] ) )
				$checked = ( $options[$key]['value'] ) ? ' checked="checked" ' : '';
			else if ( is_array( $values ) )
					$checked = ( in_array( $key, $values ) ) ? ' checked="checked"' : '';

				$html .= "<label><input class='input-check' type='checkbox' value='$key' name='${name}[]' $class $readonly $checked /> $val</label>";
			if ( $t = $options[$key]['text'] ) $html .= format_comment( $t );
		}
		return $html;

	}

	function settings_select( $name, $vars, $values = false ) {

		$values = ( $values ) ? $values : $this->object->get_val( $name );

		$html = "<select class='input-select' name='$name'>";

		if ( !empty( $vars ) ) {
			foreach ( $vars as $key => $val ) {
				$checked = ( $key == $values ) ? ' selected="selected"' : '';
				$html .= "<option value='$key' $checked> $val</option>";
			}
		}

		$html .= '</select>';

		return $html;

	}

	function settings_textarea( $name, $s = null, $additional_options = null ) {

		$cssClass = '';
		if ( $s ) {
			$value = $s;
		} else {
			$value = $this->object->get_val( $name );
		}

		if ( empty( $value ) && isset( $additional_options->values ) ) $value = $additional_options->values;
		if ( isset( $additional_options->cssClass ) ) $cssClass = $additional_options->cssClass;

		$html = "<textarea class='input-textarea ".@$cssClass."' name='$name'>$value</textarea>";

		return $html;

	}

	function settings_colorpicker( $name, $s = null, $additional_options = null ) {

		if ( !$s || is_array( $s ) ) {
			$value = esc_attr( $this->object->get_val( $name, $s ) );
		} else {
			$value = $s;
		}

		if ( empty( $value ) && isset( $additional_options->values ) ) $value = $additional_options->values;
		if ( isset( $additional_options->cssClass ) ) $cssClass = $additional_options->cssClass;

		$html = '<input type="text" id="color" name="'.$name.'" class = "input-text '.$cssClass.'"
		value="'.$value.'" style="background-color:'.$value.'" maxlength="7" />';
		$html .= '<div id="colorpick-dialog" name = "' . $name . '" style="text-align:center;" title="' . $additional_options->title . '">';
		$html .= '<input type="text" id="color-colorpick" maxlength="7" name="'.$name.'" value="'.$value.'" style="background-color:'.$value.'; visibility: hidden; position:absolute;" />';
		$html .= '<div id="colorpicker" align="center" name = "'.$name.'"></div>';
		$html .= '<br><button class="button" id="color-colorpick-done" name="'.$name.'" style="visibility: hidden; position:absolute;">'. __( 'Apply Color', 'runway' ) .'</button>';
		// $html .= '<input type="button" id="color-colorpick-done" name="'.$name.'" style="visibility: hidden; position:absolute;" value="Done pick color" /></div>';

		$html .= '
		<script type="text/javascript">
			(function ($) {
				if($("#color[name=\''.$name.'\']").val() == ""){
					$("#color[name=\''.$name.'\']").val("#ffffff");
					$("#color-colorpick[name=\''.$name.'\']").val("#ffffff");
				}

				$(function () {
					$("#color[name=\''.$name.'\']").focus(function(){
						$("#colorpick-dialog[name=\''.$name.'\']").dialog({
							position: ["center"],
							modal: true,
							resizable: false
						});

						$(".ui-widget-overlay").click(function(){

							$("#colorpick-dialog[name=\''.$name.'\']").dialog("close");
						});

						$("#colorpicker[name=\''.$name.'\']").farbtastic("#color[name=\''.$name.'\'], #color-colorpick[name=\''.$name.'\']");
						$("#color-colorpick[name=\''.$name.'\']").css("visibility", "visible");
						$("#color-colorpick[name=\''.$name.'\']").css("position", "inherit");
						$("#color-colorpick-done[name=\''.$name.'\']").css("visibility", "visible");
						$("#color-colorpick-done[name=\''.$name.'\']").css("position", "inherit");
					});



					 $("#color[name=\''.$name.'\']").change(function(){
						 var picker = $.farbtastic("#colorpicker[name=\''.$name.'\']");  //picker variable
						 picker.setColor($("#color[name=\''.$name.'\']").value); //set initial color
					 });

					 $("#color-colorpick-done[name=\''.$name.'\']").click(function(){
						$("#colorpick-dialog[name=\''.$name.'\']").dialog("close");
					 });
				 })

			})(jQuery);
		</script>';

		return $html;

	}

	function format_comment( $comment ) {
		// return '<em class="howto">' . $comment . '</em>';
		return '<p class="description">' . $comment . '</p>';
	}

	function settings_save_button( $text = 'Save', $class = 'button' ) {
		$this->object->keys = isset( $this->object->keys ) ? $this->object->keys : array();
		$keys = implode( ',', (array) $this->object->keys ); ?>

		<input type="hidden" name='version_key' value='<?php echo esc_attr($this->object->get_version_id()); ?>' />
		<input type="hidden" name='import_key' value='<?php echo esc_attr($this->object->get_val( 'import_key' )); ?>' />
		<input type="hidden" name='ancestor_key' value='<?php echo esc_attr($this->object->get_val( 'ancestor_key' )); ?>' />
		<input type="hidden" name='originating_keys' value='<?php echo esc_attr($keys); ?>' />
		<input type="hidden" name='action' value='save' />
		<p class="submit">
			<input type="submit" class='<?php echo esc_attr($class); ?>' value='<?php rf_e($text); ?>' />
		</p>
		</form>

	<?php
	}

	function permalink_warning() {
		global $wp_rewrite;

		if ( empty( $wp_rewrite->permalink_structure ) ) {
			$html = '<em class="warning">';
			$html .= sprintf( __( 'Permalinks are currently not enabled! To use this feature, enable permalinks in the %sPermalink Settings%s.', 'runway' ), '<a href="options-permalink.php">', '</a>');
			$html .= '</em>';
			return $html;
		} else {
			return '';
		}
	}

	function settings_multicheck( $name, $vars, $values = false, $_name = '' ) {

		global $theme_settings;

		$values = ( $values ) ? $values : $this->object->get_val( $name );
		// $values = ( $values ) ? $values : unserialize( $theme_settings->data_loaded['_framework']['options']['disable_wp_content'] );

		if ( !is_array( $values ) )
			$values = unserialize( $values );

		if ( $_name ) {
			$tvalues = array();
			if ( is_array( $values ) ) {
				foreach ( $values as $tkey => $tval ) {
					if ( is_array( $tval ) ) {
						foreach ( $tval as $ttkey => $ttval ) {
							if ( $ttval == $_name ) {
								$tvalues[] = $tkey;
							}
						}
					}
				}
			}

			$values = $tvalues;
		}

		$html = '';

		foreach ( $vars as $key => $val ) {
			$checked = ( in_array( $key, $values ) ) ? ' checked="checked"' : '';
			$post_type = get_post_type_object( $val );
			$html .= '
					<label>
						<input type="checkbox" name="'. $name.'['.$_name.'][]" value="'. $key .'" '. $checked .'>
						'. ( !empty( $post_type->labels->singular_name ) ? $post_type->labels->singular_name : $post_type->labels->menu_name ) .'
					</label>';
		}

		return $html;
	}

	function settings_multiselect( $name, $vars, $values = false, $_name = '' ) {

		global $theme_settings;
		$values = ( $values ) ? $values : $this->object->get_val( $name );
		// $values = ( $values ) ? $values : unserialize( $theme_settings->data_loaded['_framework']['options']['disable_wp_content'] );

		if ( !is_array( $values ) )
			$values = unserialize( $values );

		if ( $_name ) {
			$tvalues = array();
			if ( is_array( $values ) ) {
				foreach ( $values as $tkey => $tval ) {
					if ( is_array( $tval ) ) {
						foreach ( $tval as $ttkey => $ttval ) {
							if ( $ttval == $_name ) {
								$tvalues[] = $tkey;
							}
						}
					}
				}
			}

			$values = $tvalues;
		}

		$html = '<select multiple class="input-select" name="' . $name . ( $_name ? "[{$_name}]" : '' ) . '[]" size="5" style="height: 103px;">';

		$html .= '<option value="no">'.__('No value', 'runway').'</option>';

		foreach ( $vars as $key => $val ) {
			if ( is_array( $values ) ) {
				$checked = ( in_array( $key, $values ) ) ? ' selected="selected"' : '';
			}

			if ( $val != '' ) {
				$html .= '<option value="'.$key.'"'.$checked.'>'.$val.'</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}





	// MAYBE NEXT FUNCTIONS IS DEPRECATED
	/*
	**
	**
	*/
	function bool_var( $name, $title, $arr ) { ?>

		<tr>
			<th scope="row" valign="top"><?php echo  $title; ?></th>
			<td>
				<?php
		$true = ( $arr[$name] ) ? " checked='checked'" : '';
		$false = ( $true ) ?  '' : " checked='checked'"; ?>
				<label><input type="radio" name="<?php echo esc_attr($name); ?>" value="true" <?php echo  $true; ?>> <?php echo  $title2; ?> Yes</label>
				<label><input type="radio" name="<?php echo esc_attr($name); ?>" value="false" <?php echo  $false; ?>> <?php echo  $title2; ?> No</label>
			</td>
		</tr>
		<?php

	}

	/*
	**
	**
	*/
	function checkboxes( $name, $title, $values, $arr ) { ?>

		<tr>
			<th scope="row" valign="top"><?php echo  $title; ?></th>
			<td>
		<?php
		foreach ( $values as $key => $title2 ) {
			$checked = ( in_array( $key, (array) $arr[$name] ) ) ? " checked='checked'" : ''; ?>
			<label><input type="checkbox" name="<?php echo esc_attr($name); ?>[]" value="<?php echo esc_attr($key); ?>" <?php echo  $checked; ?>> <?php echo  $title2; ?></label>
		<?php } ?>
			</td>
		</tr>
		<?php

	}

	/*
	**
	**
	*/
	function condition( $condition, $message, $type = 'error' ) {

		if ( !isset( $this->object->is_ok ) ) $this->object->is_ok = true;

		// If there is an error already return
		if ( !$this->object->is_ok && $type = 'error' ) return $this->object->is_ok;

		if ( $condition == false && $type != 'silent' ) {
			echo '<div class="updated fade"><p>' . $message . '</p></div>';

			// Don't set the error flag if this is a warning.
			if ( $type == 'error' ) $this->object->is_ok = false;
		}

		return $condition == true;

	}
}
