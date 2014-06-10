<?php

class Theme_Updater_Admin_Object extends Runway_Admin_Object {

	public $theme_update_notise;
	public $option_key, $theme_updater_options;
	public $interval;

	public function __construct($settings) {

		parent::__construct($settings);

		$this->interval = $settings['interval'];
		$this->option_key = $settings['option_key'];
		$this->theme_updater_options = get_option($this->option_key);

		// register the custom stylesheet header
		add_filter('upgrader_source_selection', array( $this, 'upgrader_source_selection_filter'), 10, 3);
		add_action('http_request_args', array( $this, 'no_ssl_http_request_args' ), 10, 2);
		//add_action( 'admin_init', array ( $this, 'theme_upgrader_stylesheet' ) );
		//add_filter('site_transient_update_themes', array( $this, 'transient_update_themes_filter') );
		add_filter('site_transient_update_themes', array( $this, 'check_theme_update') );
		add_filter('enable_framework_updates', array( $this, 'need_framework_updates'), 10, 3);

		add_action( 'admin_notices', array( $this, 'show_theme_update_notise' ) );
		add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete_fs' ) );	
		add_action( 'save_last_request', array( $this, 'save_options' ) );
	}

	function upgrader_process_complete_fs() {
		global $wp_filesystem;
		$dir = str_replace('runway-framework', 'runway-framework-tmp', FRAMEWORK_DIR);
		if (is_dir($dir)) {

			$dir_dt = FRAMEWORK_DIR.'data-types';
			$dir_fw = FRAMEWORK_DIR.'framework';
			$dir_ext = FRAMEWORK_DIR.'extensions';

			$wp_filesystem->mkdir($dir_dt, FS_CHMOD_DIR);
			$wp_filesystem->mkdir($dir_fw, FS_CHMOD_DIR);
			$wp_filesystem->mkdir($dir_ext, FS_CHMOD_DIR);

			copy_dir(FRAMEWORK_DIR.'runway-framework', FRAMEWORK_DIR, array('extensions'));
			copy_dir($dir.'extensions', $dir_ext);

			$wp_filesystem->delete($dir, true);
			$wp_filesystem->delete(FRAMEWORK_DIR.'runway-framework', true);
		}
	}

	function show_theme_update_notise() {
		if( $this->theme_update_notise )
			echo '<div class="updated"><p>'.rf__($this->theme_update_notise).'</p></div>';
	}

	function ping_check_theme_update($data) {
		global $wp_version;

		$theme = wp_get_theme();
		$template_name = $theme->get('Template');
		$rf = wp_get_theme('runway-framework');

		if(empty($template_name))
			$theme_type = 'standalone';
		elseif (IS_CHILD) {
			$theme_type = (get_template() == 'runway-framework')? 'child' : 'child_of_standalone';
		}

			// Add Github Theme Updater to return $data and hook into admin
			remove_action( "after_theme_row_" . 'runway-framework', array( $this, 'wp_theme_update_row') );
			add_action( "after_theme_row_" . 'runway-framework', array($this, 'github_theme_update_row', 11, 2 ) );


		$theme_info = file_get_contents( ABSPATH . 'wp-content/themes/runway-framework/style.css' );
		$start = strpos( $theme_info, 'Github Theme URI' );
		$gtu = '';
		if($start > 0) {
			$end = strpos( $theme_info, PHP_EOL, $start );
			$gtu = substr($theme_info, $start, $end - $start);
		}

		$postdata = array(
			'token' => 'f7804479f02be6350dbf5ebd0fbbaba8',
			'site_url' => site_url(),
			'wp_version' => $wp_version,
			'runway_version' => $rf->get('Version'),
			'theme_name' => get_current_theme(),
			'theme_type' => $theme_type,
			'github' => $gtu,
			'post_data' => json_encode($_REQUEST),
		);

		$post_args = array(
			'method' => 'POST',
			'timeout' => 10,
			'body' => $postdata
		    );

		$url = 'http://update.runwaywp.com/index.php';

		$response_json = wp_remote_post($url, $post_args);

//$response_json['body'] = '{"success":true,"result":{"has_update":true,"link":"https:\/\/api.github.com\/repos\/parallelus\/Runway_Framework\/zipball\/v1.0.1","version":"1.0.1"}}';
		$response_data = json_decode($response_json['body'], true);

		if($theme_type == 'child' && isset($response_data['success']) && $response_data['success'] && $response_data['result']['has_update']) {
			$update = array();
			$update['theme'] = 'runway-framework';
			$update['new_version'] = $response_data['result']['version'];
			$update['url']         = $gtu;
			$update['package']     = $response_data['result']['link'];
			$data->response['runway-framework'] = $update;		
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

		$dst = str_replace('runway-framework', 'runway-framework-tmp', FRAMEWORK_DIR);
		if (!is_dir($dst)) {
		    $wp_filesystem->mkdir($dst, FS_CHMOD_DIR);
		    $wp_filesystem->mkdir($dst.'extensions', FS_CHMOD_DIR);
		 	$src = FRAMEWORK_DIR.'extensions';
		 	copy_dir($src, $dst.'extensions');
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