<?php

class Theme_Updater_Admin_Object extends Runway_Admin_Object {

	public $theme_update_notise;
	public $option_key, $theme_updater_options;
	public $interval;
	public $url_update_fw, $url_update_exts;

	public function __construct($settings) {

		parent::__construct($settings);

		$this->interval = $settings['interval'];
		$this->option_key = $settings['option_key'];
		$this->url_update_fw = 'http://update.runwaywp.com/index.php';
//		$this->url_update_fw = 'http://wptest.loc/upd/index.php';
		$this->url_update_exts = 'http://runwaywp.com/sites/main';
//		$this->url_update_exts = 'http://wordpresstest.dev';

		$this->theme_updater_options = get_option($this->option_key);

		// register the custom stylesheet header
		add_filter('upgrader_source_selection', array( $this, 'upgrader_source_selection_filter'), 10, 3);
		add_filter('upgrader_pre_download', array($this, 'upgrader_extension_pre_download_filter'), 10, 3);
		add_action('http_request_args', array( $this, 'no_ssl_http_request_args' ), 10, 2);
		
		add_filter('site_transient_update_themes', array( $this, 'check_theme_update') );
		add_filter('enable_framework_updates', array( $this, 'need_framework_updates'), 10, 3);

		add_action( 'admin_notices', array( $this, 'show_theme_update_notise' ) );
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete_fs' ), 10 );
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete_extensions' ), 20, 2 );
		add_action( 'save_last_request', array( $this, 'save_options' ) );
	}

	function upgrader_process_complete_fs() {
		global $wp_filesystem;
		
		$dir = str_replace('runway-framework', 'runway-framework-tmp', FRAMEWORK_DIR);
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
			$wp_filesystem->delete(FRAMEWORK_DIR.'runway-framework', true);
		}
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
		
		if(is_array($option) && isset($option['data']['response']) && is_array($option['data']['response'])) {
			foreach($option['data']['response'] as $key => $response) {
				//find extensions in updater
				if($key == $current_extension_name && isset($response['rw_extension']) && $response['rw_extension'] == true) {
					
					$extension_path = FRAMEWORK_DIR.'extensions/'.preg_replace('/\/.*/', '', $response['rw_extension_core']);
					copy_dir($themes_dir.$current_extension_name, $extension_path);
					$wp_filesystem->delete($themes_dir.$current_extension_name, true);
					
					unset($option['data']['response'][$key]);
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
		global $wp_version;

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
			$info = file_get_contents( ABSPATH . 'wp-content/themes/runway-framework/style.css' );
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

			$postdata = array(
				'login' => $auth_manager_admin->login,
				'psw' => $auth_manager_admin->psw,
				'extensions' => $extm->extensions_List
			);			
		}
		
		$theme_info['post_args'] = array(
			'method' => 'POST',
			'timeout' => 10,
			'body' => $postdata
		    );

		return $theme_info;
	}

	function ping_check_theme_update($data) {

		$theme_info = $this->get_theme_info('theme');

		$response_json = wp_remote_post($this->url_update_fw, $theme_info['post_args']);
		$response_data = json_decode($response_json['body'], true);

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

 		$response = wp_remote_post($this->url_update_exts.'/wp-admin/admin-ajax.php?action=sync_downloads', $theme_info['post_args']);

		if($response['response']['code'] != '200' || $theme_info['type'] != 'child')
			return $data;
		
		$response_json = json_decode($response['body']);
		if(is_array($response_json) && !empty($response_json)) {
			foreach($theme_info['post_args']['body']['extensions'] as $key => $current_extension) {
				foreach($response_json as $response_extension) {
					if($current_extension['Name'] == $response_extension->Name && $current_extension['Version'] != $response_extension->Version) {
												
						$package = "";
						foreach($response_extension->Files as $response_package) {
							$package = $response_package->file;
							break;
						}
						
						$name = $response_extension->Name;
						if(!strstr($name, "Extension") && !strstr($name, "extension"))
							$name .= " Extension";
						
						$update = array();
						$update['theme'] = $response_extension->Name;
						$update['rw_extension'] = true;
						$update['rw_extension_core'] = $key;
						$update['new_version'] = $response_extension->Version;
						$update['package']     = $package;
						$data->response[$name] = $update;	
					}
				}
			}
		}
		
		return $data;
	}

	function save_options($data) {
		$this->theme_updater_options['data'] = (array)$data;
		$this->theme_updater_options['last_request'] = time();
		
		update_option( $this->option_key, $this->theme_updater_options );
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
				return $data;
			else
				return (object)$this->theme_updater_options['data'];
		}
	}

	function need_framework_updates() {
		if( (empty($this->theme_updater_options) || ((time() - $this->theme_updater_options['last_request']) > $this->interval) ) )
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

		$dst = str_replace('runway-framework', 'runway-framework-tmp', FRAMEWORK_DIR);
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
		if(isset($upgrader->skin->theme)) 
			$correct_theme_name = $upgrader->skin->theme;
		elseif(isset($upgrader->skin->theme_info->stylesheet))
			$correct_theme_name = $upgrader->skin->theme_info->stylesheet;
		elseif(isset($upgrader->skin->theme_info->template))
			$correct_theme_name = $upgrader->skin->theme_info->template;
		else 
			$upgrader->skin->feedback(__('Theme name not found. Unable to rename downloaded theme.', 'framework'));
				
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
		
		if(is_array($option) && isset($option['data']['response']) && is_array($option['data']['response'])) {
			foreach($option['data']['response'] as $key => $response) {
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
					
					$file = fopen($fname, 'w+'); // Create a new file, or overwrite the existing one.
					fwrite($file, $http_response['body']);
					fclose($file);
					
					return $fname;
						
				}
			}
		}
		
		return false;
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