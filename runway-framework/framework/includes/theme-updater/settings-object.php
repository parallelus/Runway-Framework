<?php

class Theme_Updater_Admin_Object extends Runway_Admin_Object {

	public $theme_update_notise;
	public $option_key, $theme_updater_options;
	public $interval, $loaded;
	public $url_update_fw, $url_update_exts;

	public function __construct($settings) {

		parent::__construct($settings);

		$this->interval = $settings['interval'];
		$this->option_key = $settings['option_key'];
		$this->url_update_fw = 'http://update.runwaywp.com/index.php';
		$this->url_update_exts = 'http://runwaywp.com/sites/main';

		$this->theme_updater_options = get_option($this->option_key);
		$this->loaded = false;

		// register the custom stylesheet header
		add_filter('upgrader_source_selection', array( $this, 'upgrader_source_selection_filter'), 10, 3);
		add_filter('upgrader_pre_download', array($this, 'upgrader_extension_pre_download_filter'), 10, 3);
		add_action('http_request_args', array( $this, 'no_ssl_http_request_args' ), 10, 2);
		
		add_filter('site_transient_update_themes', array( $this, 'check_theme_update') );
		add_filter('enable_framework_updates', array( $this, 'need_framework_updates'), 10, 3);

		add_action( 'admin_notices', array( $this, 'show_theme_update_notise' ) );
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete_fs' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete_extensions' ), 20, 2 );
		add_action( 'save_last_request', array( $this, 'save_options' ) );
		
		add_action( 'update-core-custom_do-runway-extension-upgrade', array( $this, 'upgrader_custom_extension_function' ) );
		add_action( 'update-custom_do-extension-custom-upgrade', array( $this, 'do_extension_custom_upgrade' ) );
		add_action( 'core_upgrade_preamble', array( $this, 'update_extensions_block' ) );
	}

	function upgrader_process_complete_fs($upgrader, $current_theme) {
		global $wp_filesystem;
		$theme_info = $this->get_theme_info('theme');
		
		if($theme_info['type'] !== 'child' || 
			(isset($current_theme['type']) && $current_theme['type'] == 'plugin') ||
			(isset($current_theme['theme']) && $current_theme['theme'] != 'runway-framework')) {
			return;
		}
		
		$found = false;
		if(isset($current_theme['type']) && $current_theme['type'] == 'theme') {
			if(isset($current_theme['themes']) && is_array($current_theme['themes'])) {       			// manual update
				foreach($current_theme['themes'] as $key => $value) {
					if($value == 'runway-framework') {
						$found = true;
						break;
					}
				}
			}
      		if(isset($current_theme['theme']) && $current_theme['theme'] == 'runway-framework') {		// auto update
				$found = true;
			}
		}
		
		if($found) {
			$dir = str_replace('themes/runway-framework', 'themes/runway-framework-tmp', FRAMEWORK_DIR);
			if (is_dir($dir)) {

				$dir_dt = FRAMEWORK_DIR.'data-types';
				$dir_fw = FRAMEWORK_DIR.'framework';
				$dir_ext = FRAMEWORK_DIR.'extensions';
				$dir_data = FRAMEWORK_DIR.'data';

				$wp_filesystem->mkdir($dir_dt, FS_CHMOD_DIR);
				$wp_filesystem->mkdir($dir_fw, FS_CHMOD_DIR);
				$wp_filesystem->mkdir($dir_ext, FS_CHMOD_DIR);
				$wp_filesystem->mkdir($dir_data, FS_CHMOD_DIR);

				copy_dir(FRAMEWORK_DIR.'runway-framework', FRAMEWORK_DIR, array('extensions', 'data'));
				copy_dir($dir.'extensions', $dir_ext);
				copy_dir($dir.'data', $dir_data);

				$wp_filesystem->delete($dir, true);
                $wp_filesystem->delete(FRAMEWORK_DIR.'ChangeLog.md');
				$wp_filesystem->delete(FRAMEWORK_DIR.'README.md');
				$wp_filesystem->delete(FRAMEWORK_DIR.'LICENSE');
				$wp_filesystem->delete(FRAMEWORK_DIR.'runway-framework', true);
			}
			unset($this->theme_updater_options['data']['response']['runway-framework']);
		}
		else {
			if(isset($current_theme['themes']) && is_array($current_theme['themes'])) {
				foreach($current_theme['themes'] as $key => $value) {
					unset($this->theme_updater_options['data']['response'][$value]);
				}
			}
		}
		update_option($this->option_key, $this->theme_updater_options);
	}
	
	function upgrader_process_complete_extensions($upgrader, $current_theme) {
		global $wp_filesystem;

		$option = get_option($this->option_key);
		$current_extension_name = "";
		if(isset($upgrader->skin->theme_info))
			$current_extension_name = $upgrader->skin->theme_info->get('Name');
		else
			return false;
		
		$themes_dir = $wp_filesystem->wp_content_dir() . "themes/";
		
		if(!is_dir($themes_dir.$current_extension_name))
			return false;
		
		if(is_array($option) && isset($option['data']['extensions']) && is_array($option['data']['extensions'])) {
			foreach($option['data']['extensions'] as $key => $response) {
				//find extensions in updater
				if($key == $current_extension_name && isset($response['rw_extension']) && $response['rw_extension'] == true) {
					
					$extension_path = FRAMEWORK_DIR.'extensions/'.preg_replace('/\/.*/', '', $response['rw_extension_core']);
					copy_dir($themes_dir.$current_extension_name, $extension_path);
					$wp_filesystem->delete($themes_dir.$current_extension_name, true);
					
					unset($option['data']['extensions'][$key]);
					update_option($this->option_key, $option);
					break;
				}
			}
		}
	}

	function show_theme_update_notise() {
		if( $this->theme_update_notise )
			echo '<div class="updated"><p>'.rf__($this->theme_update_notise).'</p></div>';
	}

	function get_theme_info($item = 'theme') {
		global $wp_version, $wp_filesystem;

		$theme_info = array();

		$theme = wp_get_theme();
		$template_name = $theme->get('Template');
		$rf = wp_get_theme('runway-framework');

		if(empty($template_name))
			$theme_info['type'] = 'standalone';
		elseif (IS_CHILD) {
			$theme_info['type'] = (get_template() == 'runway-framework')? 'child' : 'child_of_standalone';
		}

		if($item == 'theme') {
            $info = $wp_filesystem->get_contents(get_template_directory() . '/style.css');
			$start = strpos( $info, 'Github Theme URI' );
			$gtu = '';
			if($start > 0) {
				$end = strpos( $info, PHP_EOL, $start );
				$gtu = substr($info, $start, $end - $start);
			}

			$postdata = array(
				'token' => 'f7804479f02be6350dbf5ebd0fbbaba8',
				'site_url' => site_url(),
				'wp_version' => $wp_version,
				'runway_version' => $rf->get('Version'),
				'theme_name' => wp_get_theme()->get('Name'),
				'theme_type' => $theme_info['type'],
				'github' => $gtu,
				'post_data' => json_encode($_REQUEST),
			);
		}
		else {
			global $extm, $auth_manager_admin;

			$postdata = array();
			$postdata['extensions'] = $extm->extensions_List;
			if(isset($auth_manager_admin->token))
				$postdata['runway_token'] = $auth_manager_admin->token;
		}
		
		$theme_info['post_args'] = array(
			'method' => 'POST',
			'timeout' => 20,
			'body' => $postdata
		    );

		return $theme_info;
	}

	function ping_check_theme_update($data) {

		$theme_info = $this->get_theme_info('theme');

		$response = wp_remote_post($this->url_update_fw, $theme_info['post_args']);

		if(is_a($response, 'WP_Error'))
			return $data;
		$response_data = json_decode($response['body'], true);

		if($theme_info['type'] == 'child' && isset($response_data['success']) && $response_data['success'] && $response_data['result']['has_update']) {
			$update = array();
			$update['theme'] = 'runway-framework';
			$update['new_version'] = $response_data['result']['version'];
			$update['url']         = str_replace("Github Theme URI: ", "", $theme_info['post_args']['body']['github']);
			$update['package']     = $response_data['result']['link'];
			$data->response['runway-framework'] = $update;		

		}

		return $data;
	}

	function ping_check_extensions_update( $data ) {

		$theme_info = $this->get_theme_info('extensions');
		$theme = wp_get_theme();

 		$response = wp_remote_post($this->url_update_exts.'/wp-admin/admin-ajax.php?action=sync_downloads', $theme_info['post_args']);
		
		if(is_a($response, 'WP_Error'))
			return $data;
		
		if($response['response']['code'] != '200' || $theme_info['type'] != 'child')
			return $data;

		$response_json = json_decode($response['body']);
		
		if(is_array($response_json) && !empty($response_json)) {
			foreach($theme_info['post_args']['body']['extensions'] as $key => $current_extension) {
				foreach($response_json as $response_extension) {
					if($current_extension['Name'] == $response_extension->Name/* && $current_extension['Version'] != $response_extension->Version*/) {
						
						$has_update = runway_check_versions($response_extension->Version, $current_extension['Version']);
						
						if(!$has_update)
							break;
						
						$package = "";
						foreach($response_extension->Files as $response_package) {
							$package = $response_package->file;
							break;
						}
						
						$update = array();
						$update['theme'] = $response_extension->Name;
						$update['name'] = $response_extension->Name;
						$update['rw_extension'] = true;
						$update['rw_extension_core'] = $key;
						$update['screenshot'] = isset($response_extension->Screenshot) ? $response_extension->Screenshot : esc_url( $theme->get_screenshot() );
						$update['new_version'] = $response_extension->Version;
						$update['old_version'] = $current_extension['Version'];
						$update['package']     = $package;
						$data->extensions[$response_extension->Name] = $update;
					}
				}
			}
		}
		
		return $data;
	}

	function save_options($data) {
		if(!$this->loaded) {
			$this->theme_updater_options['data'] = (array)$data;
			$this->theme_updater_options['last_request'] = time();
			$this->loaded = true;
			
			update_option( $this->option_key, $this->theme_updater_options );
		}
	}

	function check_theme_update($data) {
		$check_for_updates = apply_filters( 'enable_framework_updates', true );
		if ( $check_for_updates ) {
			$new_data = $this->ping_check_theme_update($data);
			$new_data = $this->ping_check_extensions_update($new_data);

			do_action("save_last_request", $new_data);
			return $new_data;
		}
		else {
			
			if(empty($this->theme_updater_options))
			{
				$this->theme_updater_options['data'] = $data;
				$this->theme_updater_options['last_request'] = time();
			}
			else {
				if(isset($data->response) && is_array($data->response)) {
					if(!isset($this->theme_updater_options['data']['response']) || (isset($this->theme_updater_options['data']['response']) && !is_array($this->theme_updater_options['data']['response']))) {
						$not_equals = true;
						$this->theme_updater_options['data']['response'] = array();
					}
					else {
						foreach($data->response as $key => $value) {
							if(!isset($this->theme_updater_options['data']['response'][$key]))
								$this->theme_updater_options['data']['response'][$key] = $value;
						}
					}
				}
			}
			
			update_option( $this->option_key, $this->theme_updater_options );
			
			if(is_array($this->theme_updater_options['data'])) {
				$returned = new stdClass();
				$returned->last_checked = isset($this->theme_updater_options['data']['last_checked'])? $this->theme_updater_options['data']['last_checked'] : 0;
				$returned->checked = isset($this->theme_updater_options['data']['checked'])? $this->theme_updater_options['data']['checked'] : array();
				$returned->response = $this->theme_updater_options['data']['response'];
				$returned->translations = isset($this->theme_updater_options['data']['translations'])? $this->theme_updater_options['data']['translations'] : array();
				return $returned;
			}
			else
				return $this->theme_updater_options['data'];
			
			/*if(empty($this->theme_updater_options))
				return $data;
			else
				return (object)$this->theme_updater_options['data'];*/
		}
	}

	function need_framework_updates() {
		if( empty($this->theme_updater_options) || ((time() - $this->theme_updater_options['last_request']) > $this->interval) )
			return true;
		else
			return false;
	}

	function upgrader_source_selection_filter($source, $remote_source=NULL, $upgrader=NULL) {
		/*
			Github delivers zip files as <Username>-<TagName>-<Hash>.zip
			must rename this zip file to the accurate theme folder
		*/
		global $wp_filesystem;

		if(isset($upgrader->skin->theme)) 
			$correct_theme_name = $upgrader->skin->theme;
		elseif(isset($upgrader->skin->theme_info->stylesheet))
			$correct_theme_name = $upgrader->skin->theme_info->stylesheet;
		elseif(isset($upgrader->skin->theme_info->template))
			$correct_theme_name = $upgrader->skin->theme_info->template;

		if(isset($correct_theme_name) && $correct_theme_name == 'runway-framework') {
			$dst = str_replace('themes/runway-framework', 'themes/runway-framework-tmp', FRAMEWORK_DIR);
			if (!is_dir($dst)) {
			    $wp_filesystem->mkdir($dst, FS_CHMOD_DIR);
			    
			    $wp_filesystem->mkdir($dst.'extensions', FS_CHMOD_DIR);
			 	$src = FRAMEWORK_DIR.'extensions';
			 	copy_dir($src, $dst.'extensions');

			    $wp_filesystem->mkdir($dst.'data', FS_CHMOD_DIR);
			 	$src = FRAMEWORK_DIR.'data';
			 	copy_dir($src, $dst.'data');		 	
			}
				
			$upgrader->skin->feedback(__("Executing upgrader_source_selection_filter function...", 'framework'));
			if(isset($source, $remote_source, $correct_theme_name)){				
				$corrected_source = $remote_source . '/' . $correct_theme_name . '/';
				if(@rename($source, $corrected_source)){
					$upgrader->skin->feedback(__("Renamed theme folder successfully.", 'framework'));
					return $corrected_source;
				} else {
					$upgrader->skin->feedback(__("**Unable to rename downloaded theme.", 'framework'));
					return new WP_Error();
				}
			}
			else
				$upgrader->skin->feedback(__('**Source or Remote Source is unavailable.', 'framework'));
		}
		return $source;
	}

	function upgrader_extension_pre_download_filter($reply, $package, $upgrader) {
		global $wp_filesystem;
		
		$option = get_option($this->option_key);
		$current_theme_name = "";
		if(isset($upgrader->skin->theme_info))
			$current_theme_name = $upgrader->skin->theme_info->get('Name');
		else
			return false;
		
		if(is_array($option) && isset($option['data']['extensions']) && is_array($option['data']['extensions'])) {
			foreach($option['data']['extensions'] as $key => $response) {
				//find extensions in updater
				if($key == $current_theme_name && isset($response['rw_extension']) && $response['rw_extension'] == true) {
					
					$upgrade_folder = $wp_filesystem->wp_content_dir() . 'upgrade/';
					if(is_array($package)) {
						$package = $package[0];
					}
					
					$http = _wp_http_get_object();
					$http_response = $http->get($package);
					if($http_response['response']['code'] == '500') {
						return false;
					}
					
					$fname = $wp_filesystem->wp_content_dir().basename($package);
					
					$wp_filesystem->put_contents($fname, $http_response['body'], FS_CHMOD_FILE);
					return $fname;
						
				}
			}
		}
		
		return false;
	}

	function upgrader_custom_extension_function() {
		if ( ! current_user_can( 'update_themes' ) )
			wp_die( __( 'You do not have sufficient permissions to update this site.', 'framework' ) );

		check_admin_referer('upgrade-core');

		if ( isset( $_GET['extensions'] ) ) {
			$extensions = explode( ',', $_GET['extensions'] );
		} elseif ( isset( $_POST['checked'] ) ) {
			$extensions = (array) $_POST['checked'];
		} else {
			wp_redirect( network_admin_url('update-core.php') );
			exit;
		}

		$url = 'update.php?action=do-extension-custom-upgrade&extensions=' . urlencode(implode(',', $extensions));
		$url = wp_nonce_url($url, 'bulk-update-themes');

		$title = __('Update Extension', 'framework');

		require_once(ABSPATH . 'wp-admin/admin-header.php');
		echo '<div class="wrap">';
		echo '<h2>' . esc_html__('Update Extensions', 'framework') . '</h2>';
		echo "<iframe src='$url' style='width: 100%; height: 100%; min-height: 750px;' frameborder='0'></iframe>";
		echo '</div>';
		include(ABSPATH . 'wp-admin/admin-footer.php');
	}
	
	function do_extension_custom_upgrade() {
		
		if ( ! current_user_can( 'update_themes' ) )
			wp_die( __( 'You do not have sufficient permissions to update extensions for this site.', 'framework' ) );

		check_admin_referer( 'bulk-update-themes' );

		if ( isset( $_GET['extensions'] ) )
			$extensions = explode( ',', stripslashes($_GET['extensions']) );
		elseif ( isset( $_POST['checked'] ) )
			$extensions = (array) $_POST['checked'];
		else
			$extensions = array();

		$extensions = array_map('urldecode', $extensions);
		
		require_once 'extension_upgrader.php';

		$url = 'update.php?action=update-selected-extensions&amp;extensions=' . urlencode(implode(',', $extensions));
		$nonce = 'bulk-update-themes';

		wp_enqueue_script('jquery');
		iframe_header();

		$upgrader = new Extension_Upgrader(new Bulk_Extension_Upgrader_Skin( compact( 'nonce', 'url' ) ));
		$upgrader->bulk_upgrade( $extensions );
		
		iframe_footer();
	}
	
	function update_extensions_block() {

		$theme_info = $this->get_theme_info();
		if($theme_info['type'] == 'child') {

			$option = get_option($this->option_key);

			$extensions_to_update = isset($option['data']['extensions']) ? $option['data']['extensions'] : array();

			if(empty($extensions_to_update)) {
				echo '<h3>' . __( 'Runway Extensions', 'framework' ) . '</h3>';
				echo '<p>' . __( 'Your extensions are all up to date.', 'framework' ) . '</p>';
				return;
			}

			$form_action = 'update-core.php?action=do-runway-extension-upgrade';
			?>

			<h3><?php _e( 'Extensions', 'framework' ); ?></h3>
			<p><?php _e( 'The following extensdions have new versions available. Check the ones you want to update and then click &#8220;Update Extensions&#8221;.', 'framework' ); ?></p>
			<p><?php printf( __( '<strong>Please Note:</strong> Any customizations you have made to extension files will be lost.', 'framework' ) ); ?></p>

			<form method="post" action="<?php echo esc_url( $form_action ); ?>" name="upgrade-extensions" class="upgrade">
			<?php wp_nonce_field('upgrade-core'); ?>
				<p><input id="upgrade-themes" class="button" type="submit" value="<?php esc_attr_e('Update Extensions', 'framework'); ?>" name="upgrade" /></p>
				<table class="widefat" cellspacing="0" id="update-themes-table">
					<thead>
					<tr>
						<th scope="col" class="manage-column check-column"><input type="checkbox" id="extensions-select-all" /></th>
						<th scope="col" class="manage-column"><label for="extensions-select-all"><?php _e('Select All', 'framework'); ?></label></th>
					</tr>
					</thead>

					<tfoot>
					<tr>
						<th scope="col" class="manage-column check-column"><input type="checkbox" id="extensions-select-all-2" /></th>
						<th scope="col" class="manage-column"><label for="extensions-select-all-2"><?php _e('Select All', 'framework'); ?></label></th>
					</tr>
					</tfoot>
					<tbody class="plugins">
					<?php
						foreach ( $extensions_to_update as $stylesheet => $extension ) {
							echo "
						<tr>
							<th scope='row' class='check-column'><input type='checkbox' name='checked[]' value='" . esc_attr( $stylesheet ) . "' /></th>
							<td class='plugin-title'><img src='" . $extension['screenshot'] . "' width='85' height='64' style='float:left; padding: 0 5px 5px' /><strong>" . $extension['name'] . '</strong> ' . sprintf( __( 'You have version %1$s installed. Update to %2$s.', 'framework' ), $extension['old_version'], $extension['new_version'] ) . "</td>
						</tr>";
						}
					?>
					</tbody>
				</table>
				<p><input id="upgrade-themes-2" class="button" type="submit" value="<?php esc_attr_e('Update Extensions', 'framework'); ?>" name="upgrade" /></p>
			</form>

	<?php }
	}

	/*
	   Function to address the issue that users in a standalone WordPress installation
	   were receiving SSL errors and were unable to install themes.
	   https://github.com/UCF/Theme-Updater/issues/3
	*/

	function no_ssl_http_request_args($args, $url) {
		$args['sslverify'] = false;
		return $args;
	}

	function theme_upgrader_stylesheet() {
		$style_url  = FRAMEWORK_URL.'extensions/theme-updater/css/admin-style.css';
		wp_register_style('theme_updater_style', $style_url);
		wp_enqueue_style( 'theme_updater_style');
	}

}
?>
