<?php
/**
 * Registered actions:
 * 1. before_delete_page
 * 2. after_delete_page
 */

class Apm_Admin extends Runway_Admin_Object {
	
	public $pages_dir, $data_dir;

	function __construct($settings){
		parent::__construct($settings);
		$this->pages_dir = get_stylesheet_directory() . '/data/pages/';
		$this->data_dir = get_stylesheet_directory() . '/data/';

		add_action( 'wp_ajax_save_option_page', array( $this, 'save_option_page' ) );
	}

	// Add hooks & crooks
	function add_actions() {

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $apm;
			$apm->navigation = $_REQUEST['navigation'];
		}
	}

	function after_settings_init() {

	}

	function validate_sumbission() {

		// If all is OKq
		return true;

	}

	function load_objects() {
		/*global $apm;
		$this->data = $apm->load_objects();
		return $this->data;*/
	}

	public function save_option_page() {
		$json_page = $_REQUEST['json_form'];
		// $new_page_id = $_REQUEST['page_id'];
		$pages_dir = $this->pages_dir;
		$message = '';
		// echo "save_option_page\n";

		if ( isset( $json_page ) && $json_page != '' ) {
			// out($_POST['page']);
			$page_post = $this->inputs_decode( json_decode( stripslashes( $json_page ), true ) );
			$page_id = $page_post['settings']['page_id'];
			if ( $page_id ) {
				$page = $page_post;
				$pages = $this->get_pages_list();

				// Prepare the Alias
				//................................................................

				$page['settings']['alias'] = sanitize_title( $page['settings']['alias'] );

				// Find out if this alias already exists
				$matches = array(); $same_alias = FALSE;
				// check the same alias
				foreach ( $pages as $own_page ) {
					if ( trim( $own_page->settings->alias ) == trim( $page['settings']['alias'] ) &&
						$page['settings']['page_id'] != $own_page->settings->page_id ) {
						$same_alias = TRUE;
					}
				}

				// if no same alias
				if ( $same_alias ) {
					foreach ( $pages as $own_page ) {
						if ( preg_match( '/'.trim( $page['settings']['alias'] ).'/', $own_page->settings->alias )&&
							$page['settings']['page_id'] != $own_page->settings->page_id ) {
							$matches[] = $own_page->settings->alias;
						}
					}
				}

				// A closer look at matching aliases to get totals
				if ( count( $matches ) != 0 && $same_alias ) {
					$total = 0;
					foreach ( $matches as $alias ) {
						$tmp = array();
						if ( ( preg_match( '/(.+)-[0-9+]/', $alias, $tmp ) && $tmp[1] == $page['settings']['alias'] ) || $alias == $page['settings']['alias'] ) {
							// if(isset($tmp[0])){
							$num = preg_replace( '/(.+)-/', '', @$tmp[0] ); // capture the number at end of string
							$numbers[] = $num; // gives the number found at the end of the match
							$total++;   // keeps count of the number of matches
							// }
						}
					}
					// Total previous uses of same alias
					if ( $total && $same_alias ) {
						$attach = ( count( $numbers ) ) ? max( $numbers ) + 1 : $total; // Set to total, for first duplicate, or next highest number
						$page['settings']['alias'] = $page['settings']['alias'].'-'.$attach;
					}
				}

				if ( file_exists( "{$pages_dir}{$page_id}.json" ) )
					$old = file_get_contents( "{$pages_dir}{$page_id}.json" );
				else
					$old = '';

				$old_page = $this->inputs_decode( json_decode( $old, true ) );
				$old_elements = array_intersect_key( (array) $old_page['elements'], (array) $page['elements'] );
				$edited_elements = array_intersect_key( (array) $page['elements'], (array) $old_page['elements'] );

				$changed = array();

				foreach ( $edited_elements as $element_key => $element_values ) {
					if ( $element_key != 'none' && ( isset( $element_values['alias'] ) && $element_values['alias'] != $old_elements[$element_key]['alias'] ||
							isset( $element_values['type'] ) && $element_values['type'] != $old_elements[$element_key]['type'] ) ) {
						$diff = array_diff( $element_values, $old_elements[$element_key] );
						// out($element_values);
						// echo "\n\n";
						if ( !empty( $diff ) ) {
							$changed[] = ( isset( $element_values['alias'] ) ) ? $element_values['alias'] : '';
						}
					}
				}

				$page = $this->inputs_encode( $page );
				$new = str_replace( '\r\n', '\\r\\n', json_encode( $page ) );
				//    $new = str_replace( '\\r\\n', '\\\\r\\\\n', json_encode( $page ) );

				// check is have changes in page conf
				if ( md5( $old ) != md5( $new ) ) {
					// mdp new conf
					if ( is_writable( $pages_dir ) ) {
						if ( isset( $_FILES['icon_url'] ) && file_exists( $_FILES['icon_url']['tmp_name'] ) ) {
							$icons_dir = THEME_DIR . 'data/icons';

							if ( !file_exists( $icons_dir ) && !mkdir( $icons_dir, 2775, true ) ) {
								$message =  '<div id="message" class="updated below-h2"><p>I can not save icon</div>';
							}

							$file_extension = pathinfo( $_FILES['icon_url']['name'], PATHINFO_EXTENSION );
							if ( isset( $file_extension ) ) {
								$uploadedfile = $_FILES['icon_url']['tmp_name'];

								switch ( $file_extension ) {
								case 'jpeg':
								case 'jpg': {
										$src = imagecreatefromjpeg( $uploadedfile );
									} break;
								case 'png': {
										$src = imagecreatefrompng( $uploadedfile );
									} break;
								case 'gif': {
										$src = imagecreatefromgif( $uploadedfile );
									} break;
								default: {
										wp_die( 'Unsupported file type.' );
									} break;
								}

								list( $width, $height ) = getimagesize( $uploadedfile );

								$tmp = imagecreatetruecolor( 16, 16 );

								imagecopyresampled( $tmp, $src, 0, 0, 0, 0, 16, 16, $width, $height );
								$page_icon_file_name = "{$page['settings']['page_id']}_icon." . $file_extension;
								imagejpeg( $tmp, $icons_dir . "/{$page_icon_file_name}", 100 );

								$page['settings']['icon_file'] = $page_icon_file_name;
								//$new = str_replace( '\\r\\n', '\\\\r\\\\n', json_encode( $page ) );
								$new = str_replace( '\r\n', '\\r\\n', json_encode( $page ) );
							}
						}
						if( IS_CHILD && get_template() == 'runway-framework')
							file_put_contents( "{$pages_dir}{$page_id}.json", $new );

						$message = '<div id="message" class="updated below-h2"><p>'. __( 'Page saved.', 'framework' ) .'</div>';
					} else {
						$message = '<p>Saving error: '.$pages_dir.' most be a writable directory.</p>';
					}

					// reset page data if it have changes
					global $shortname;

					$old = json_decode( $old );
					$option_key = ( isset( $old ) ) ? $shortname.$old->settings->alias : $shortname;
					$tmp = get_option( $option_key );
					$to_write = array();

					if ( count( $tmp ) ) {
						foreach ( $tmp as $key => $value ) {
							if ( !in_array( $key, $changed ) ) {
								$to_write[$key] = $value;
							}
						}
					}

					update_option( $option_key, $to_write );
				}
				else {
					$message = '<p>'. __( 'The page has not changed.', 'framework' ) .'</p>';
				}

				$link = home_url().'/wp-admin/admin.php?page=options-builder&navigation=edit-page&page_id='.$page_id;

				$return = array(
					'message' => $message,
					'page_id' => $page_id,
					'reload_url' => $link,
				);

				echo json_encode( $return );
			}
		}

		die();
	}

	/**
	 * Delete page function
	 *
	 * @param unknown $page_id
	 * @param unknown $pages_dir
	 */
	function del_page( $page_id, $pages_dir ) {

		do_action( 'before_delete_page' );
		$page_path = $pages_dir.$page_id.'.json';

		if ( file_exists( $page_path ) ) {
			unlink( $page_path );
		}
		do_action( 'after_delete_page' );
	}

	/**
	 * Duplicate page function
	 *
	 * @param unknown $page_id
	 * @param unknown $pages_dir
	 */
	function duplicate_page( $page_id, $pages_dir ) {

		//do_action( 'before_delete_page' );
		$page_path = $pages_dir.$page_id.'.json';

		//do_action( 'after_delete_page' );
	}

	function get_pages_list() {
		$error_flag = true;
		$error_message = '<b>Error:</b> The child theme folder "data" and it\'s sub-folder "pages" must both exists and be writable. Please check these folders and their permissions in your child theme.';
		if ( !file_exists( $this->data_dir ) && !file_exists( $this->pages_dir ) ) {
			if ( mkdir( $this->data_dir, 0777, true ) && mkdir( $this->pages_dir, 0777, true ) ) {
				$error_flag = true;
			}
			else {
				$error_flag = false;
			}
		}

		if ( !is_writable( $this->pages_dir ) && !is_writable( $this->data_dir ) && $error_flag ) {
			if ( chmod( $this->data_dir, 0777 ) && chmod( $this->pages_dir, 0777 ) ) {
				$error_flag = true;
			}
			else {
				$error_flag = false;
			}
		}

		if ( !$error_flag ) {
			wp_die( $error_message );
		}

		$page_files = scandir( $this->pages_dir );

		$pages = array();

		foreach ( $page_files as $page_file ) {
			if ( $page_file != '.' && $page_file != '..' ) {
				$json = file_get_contents( $this->pages_dir . $page_file );
				$pages[] = json_decode( $json );
			}
		}

		return $pages;
	}

	public function inputs_encode( $page ) {
		foreach ( (array) $page['elements'] as $field_id => $element ) {
			if ( $field_id != 'none' /*&& $field_id > 0*/ && $field_id != 'n' ) {
				if ( isset( $page['elements'][$field_id]['title'] ) ) {
					$page['elements'][$field_id]['title'] = addslashes( htmlspecialchars( $element['title'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['values'] ) ) {
					$page['elements'][$field_id]['values'] = addslashes( htmlspecialchars( $element['values'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['requiredMessage'] ) ) {
					$page['elements'][$field_id]['requiredMessage'] = addslashes( htmlspecialchars( $element['requiredMessage'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['validationMessage'] ) ) {
					$page['elements'][$field_id]['validationMessage'] = addslashes( htmlspecialchars( $element['validationMessage'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['cssClass'] ) ) {
					$page['elements'][$field_id]['cssClass'] = addslashes( htmlspecialchars( $element['cssClass'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['titleCaption'] ) ) {
					$page['elements'][$field_id]['titleCaption'] = addslashes( htmlspecialchars( $element['titleCaption'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['fieldCaption'] ) ) {
					$page['elements'][$field_id]['fieldCaption'] = addslashes( htmlspecialchars( $element['fieldCaption'], ENT_QUOTES ) );
				}
			}
		}
		return $page;
	}

	public function inputs_decode( $page ) {
		foreach ( (array) $page['elements'] as $field_id => $element ) {
			if ( $field_id != 'none' /*&& $field_id > 0*/ && $field_id != 'n' ) {
				if ( isset( $page['elements'][$field_id]['title'] ) ) {
					$page['elements'][$field_id]['title'] = stripslashes( htmlspecialchars_decode( $element['title'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['values'] ) ) {
					$page['elements'][$field_id]['values'] = stripslashes( htmlspecialchars_decode( $element['values'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['requiredMessage'] ) ) {
					$page['elements'][$field_id]['requiredMessage'] = stripslashes( htmlspecialchars_decode( $element['requiredMessage'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['validationMessage'] ) ) {
					$page['elements'][$field_id]['validationMessage'] = stripslashes( htmlspecialchars_decode( $element['validationMessage'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['cssClass'] ) ) {
					$page['elements'][$field_id]['cssClass'] = stripslashes( htmlspecialchars_decode( $element['cssClass'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['titleCaption'] ) ) {
					$page['elements'][$field_id]['titleCaption'] = stripslashes( htmlspecialchars_decode( $element['titleCaption'], ENT_QUOTES ) );
				}
				if ( isset( $page['elements'][$field_id]['fieldCaption'] ) ) {
					$page['elements'][$field_id]['fieldCaption'] = stripslashes( htmlspecialchars_decode( $element['fieldCaption'] , ENT_QUOTES ) );
				}
			}
		}
		return $page;
	}
}
?>
