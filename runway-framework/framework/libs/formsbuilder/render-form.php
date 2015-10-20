<?php
// Assign a variable for object reference
$options_object = ${$current['admin_object']};
?>
<input type="hidden" id="page-slug" value="<?php echo esc_attr($options_object->slug); ?>">
<div class="dynamic-page-wrapper" id="<?php echo esc_attr($current['builder_page']->settings->alias); ?>">
<?php
global $developerMode, $contentTypeMetaBox;
$current = apply_filters( 'before-dynamic-page-render_' . $current['builder_page']->settings->alias, $current );

if ( isset( $options_object->keys ) ) {
	$keys = $options_object->keys;
}

if ( isset( $options_object->data ) ) {
	$data = $options_object->data;
}

// out($data);

$elements = $current['elements'];
$sortOrder = $current['sortOrder'];


// ==========================================
// Setup the page options
// ==========================================

// Output the Tabs
// ------------------------------------------
if ( is_object( $sortOrder ) ) :
	$form_link = array(
		'navigation' => $options_object->option_key,
		'action' => 'save',
		'keys' => "_framework,{$options_object->option_key}",
		'action_keys' => "_framework,{$options_object->option_key}",
	);

	$options_object->keys  = '_framework,'.$options_object->option_key;

	$options_object->html->settings_form_header( $form_link );

	$tabs_count = count( (array)$sortOrder );
	$first_tab = true;


	foreach ( $sortOrder as $tab => $containers ) :
		if ( $tabs_count > 1 ) {
			if ( $first_tab ) {
				echo '<div id="tabs-'.$tab.'" class="tab tab-active">';
			}
			else {
				echo '<div id="tabs-'.$tab.'" class="tab">';
			}

			$first_tab = false;
		}

		// Output the Containers
		// ------------------------------------------
		if ( !empty( $containers ) ) :
			foreach ( $containers as $container => $fields ) :
				if ( $container == 'none' )  // temporary fix for '[none] => none' values showing up in JSON files.
					continue;


				echo '<a name="'. sanitize_title( $elements->$container->title ) .'"></a>';

				if ( $elements->$container->type == 'invisible' ) {
					echo '<h3 class="container-title">'. rf__($elements->$container->title) .'</h3>';
				}

				if ( $elements->$container->type == 'visible' ) {
					echo '<div class="meta-box-not-sortables metabox-holder">';
					echo '<div class="postbox">';
					echo '<h3 class="no-move"><span>'. rf__($elements->$container->title) .'</span></h3>';
					echo '<div class="inside">';
				}

				if ( $elements->$container->type == 'show-hide' ) {
					// echo '<a name="'. $elements->$container->title .'"></a>';
					echo '<div class="meta-box-sortables metabox-holder">';
					echo '<div class="postbox show-hide-section">';
					echo '<div class="handlediv" title="'.__( 'Click to toggle', 'framework' ).'"><br></div><h3 class="hndle"><span>'.rf__($elements->$container->title).'</span></h3>';
					echo '<div class="inside" style="display: none;">';
				}

				echo '<table class="form-table">';

				// Output the Fields
				// ------------------------------------------
				if ( !empty( $fields ) ) :
					// out($options_object);
					foreach ( $fields as $field ) :
						// temporary fix for '[none] => none' values showing up in JSON files.
						if ( $container == 'none' || $field == 'none' ) {
							continue;
						}

						$title = stripslashes( rf__( htmlspecialchars_decode( $elements->$field->title ) ) );

						$titleCaption = ( isset( $elements->$field->titleCaption ) ) ? stripslashes( $options_object->html->format_comment( rf__( htmlspecialchars_decode( $elements->$field->titleCaption ) ) ) ) : '';
						$fieldCaption = ( isset( $elements->$field->fieldCaption ) ) ? stripslashes( $options_object->html->format_comment( rf__( htmlspecialchars_decode( $elements->$field->fieldCaption ) ) ) ) : '';

						if ( $developerMode ) {

							$field_alias = $elements->$field->alias;
							if ( $custom_alias != null ) {
								$alias = $custom_alias;
							}
							$title = apply_filters('formsbuilder_name_attr_title', '', $field_alias, $title, $alias);
							$fieldCaption = apply_filters('formsbuilder_dev_description', '', $fieldCaption, $field_alias, $alias);
						}

						$fieldType = 'dynamic_'.$elements->$field->type;

						if ( !$elements->$field->type ) {
							continue;
						}

						if ( method_exists( $options_object, $fieldType ) ) {
							$field = $options_object->$fieldType( $elements->$field );
						} else {
							$elements->$field = apply_filters( 'before-dynamic-field-render_' . $elements->$field->alias, $elements->$field );
							$field = $options_object->dynamic_template_field( $elements->$field );
						}

						$row = array(
							$title . $titleCaption,
							$field . $fieldCaption,
						);

						$options_object->html->setting_row( $row );
					endforeach;
				endif;

				// End of container
				echo '</table>';

				if ( $elements->$container->type == 'show-hide' || $elements->$container->type == 'visible' ) {
					echo '</div></div></div>';
				}
			endforeach;
		endif;

		if ( $tabs_count > 1 ) {
			// End of tab
			echo '</div>';
		}

	endforeach;

	if ( $default_save ) {
		$options_object->settings_save_button( __( 'Save Settings', 'framework' ), 'button-primary' );
	}
endif;


?>
</div>
