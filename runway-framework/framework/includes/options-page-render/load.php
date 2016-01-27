<?php
/*
    Extension Name: Options Page Render
    Extension URI:
    Version: 0.8
    Description: Output the content of pages generated using the Options Builder.
    Author: Parallelus
    Author URI:
    Text Domain:
    Domain Path:
    Network:
    Site Wide Only:
*/
if(!function_exists('WP_Filesystem'))
	require_once(ABSPATH . 'wp-admin/includes/file.php');
WP_Filesystem();
global $wp_filesystem;	
global $libraries, $page_options;
$form_builder = $libraries['FormsBuilder'];
$page_options = array();

// Create each admin page
$pages_dir = get_stylesheet_directory() . '/data/pages/';
$page_files = array();
if ( is_dir( $pages_dir ) ) {
	$page_files = runway_scandir( $pages_dir );
}

$pages = array();
foreach ( $page_files as $page_file ) {
	$json = $wp_filesystem->get_contents( $pages_dir . $page_file );
	$pages[] = json_decode( $json );
}

if ( !empty( $pages ) ) {
	$pages = sort_pages_list($pages);
	foreach ( $pages as $page ) {

		if(!empty($page)) {
			$alias = $page->settings->alias;
			$page_options[$alias] = $form_builder->prepare_form( $page );
			$settings = $form_builder->make_settings( $page_options[$alias] );

			global ${$page_options[$alias]['object']}, ${$page_options[$alias]['admin_object']};

			// Using a variable variabel, ${$options['obj_name']}, we can assign the new ojbect on the fly
			require_once 'object.php';
			${$page_options[$alias]['object']} = new Generic_Settings_Object( $settings );

			if ( is_admin() ) {
				// Setup admin object
				require_once 'settings-object.php';
				${$page_options[$alias]['admin_object']} = new Generic_Admin_Object( $settings );
				${$page_options[$alias]['admin_object']}->dir = plugin_dir_path( __FILE__ );
			}

			$formsbilder_option = get_option($form_builder->option_key);
			if(!isset($formsbilder_option) || $formsbilder_option == false) {
				$form_builder->add_page_to_pages_list( $page );
			}

			do_action( 'options_page_render_is_load' );
		}
	}

	// Add an "Edit" button in the title
	if ( is_admin() ) {
		function title_button_edit_page( $title ) {
			global $page_options, $developerMode;

			// Get the current page info
			$alias = $_GET['page'];
			$current = ( isset( $page_options[$alias] ) ) ? $page_options[$alias] : '';
			$template = get_template(); // The parent theme

			// If this is a child theme and $page_options[$alias] exists...
			// - Only when still working on child thme, could later test against a "developer mode" variable.
			if ( $template == 'runway-framework' && $current ) {
				// Append the button ot the title
				$title .= ' <a href="'.admin_url('admin.php?page=options-builder&navigation=edit-page&page_id='. $current['id']) .'" title="'. __( 'Edit this page', 'framework' ) .'" class="add-new-h2">'. __( 'Edit Page', 'framework' ) .'</a>';
			}
			if ( IS_CHILD && $developerMode && $current ) {

				// Reset defaults
				$title .= ' <a href="'.esc_url(admin_url('admin.php?page=options-builder&navigation=reset-fields-page&page_id='. $current['id'])) .'" onclick="return confirm(\''. __( 'This will delete all saved settings on this page.\nAre you sure you want to to continue?', 'framework' ) .'\')" title="'. __( 'Reset all fields to defaults values.', 'framework' ) .'" class="add-new-h2">'. __( 'Reset Defaults', 'framework' ) .'</a>';

				// Toggle Developer Info
				$title .= ' <a href="#" title="'. __( 'Show or hide the developer information.', 'framework' ) .'" class="add-new-h2" id="ToggleDevMode">'. __( 'Toggle Developer Info', 'framework' ) .'</a>';
				// Add a pointer describing the function of the developer toggle
				WP_Pointers::add_pointer( 'all', '#ToggleDevMode', array( 'title' => __( 'Developer Functions', 'framework' ), 'body' => '<p>'.__('Show PHP references used to output options in theme files', 'framework').'.</p>' ), 'edge: "top", align: "left"' );

			}

			return $title;
		}
		add_filter( 'framework_admin_title', 'title_button_edit_page' );
	}
}
?>
