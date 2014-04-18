<?php

class Reports_Admin_Object extends Runway_Admin_Object {

	public $option_key, $reports;

	function __construct($settings){
		parent::__construct($settings);

		$this->option_key = $settings['option_key'];
		$this->reports = get_option( $this->option_key );
		if ( empty( $this->reports ) ) {
			$this->reports = array();
		}

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_notices', array( $this, 'site_admin_notice' ) );
	}

	// Add hooks & crooks
	function add_actions() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {

		if ( isset( $_REQUEST['navigation'] ) && !empty( $_REQUEST['navigation'] ) ) {
			global $reports;
			$reports->navigation = $_REQUEST['navigation'];
		}

		$this->system_reports();
		do_action( 'add_report', $this );
	}

	function after_settings_init() {
		/* nothing */
	}

	function validate_sumbission() {

		return true;

	}

	function load_objects() {

	}

	function site_admin_notice() {
		global $theme_name, $reports;

		if ( $theme_name != 'runway-framework' ) {
			$fail = 0;
			foreach ( $this->reports as $report_key => $report_info ) {
				if ( $report_info['state'] == 'fail' ) {
					$fail++;
				}
			}

			if ( $fail != 0 ) {
				if ( IS_CHILD && isset( $_GET['activated'] ) && $_GET['activated'] == 'true' )
		           $reports->fix_all_issues();
		        else
        		   echo "<div class='update-nag'>" . sprintf( __( 'You have '.$fail.' failed tests. To have a good time with Runway these should be fixed. See the error details on the <a href="'.$reports->self_url().'">reports page</a>' ) ) . "</div>";				
			}
		}
		else {
			global $developer_tools;
			echo "<div class='update-nag'>" . sprintf( __( 'A Runway child theme has not been activated. You can create or activate one from the <a href="'.$developer_tools->self_url().'">Runway Themes Manager</a>' ) ) . "</div>";
		}
	}	

	public function system_reports() {
		// check php version compare
		$min_php_version = 50310;
		$min_version_display = "5.3.1";

		$settings = array(
			'source' => 'Runway System',
			'report_key' => 'runway_min_php_version_compare',
			'success_message' => __('Your PHP version is good! You are running version', 'framework').' '. $min_version_display,
			'fail_message' => __('Your PHP version is', 'framework').': '.PHP_VERSION.'. '.__('You must have PHP version', 'framework').' '. $min_version_display .' '.__('or later', 'framework').'.',
			'type' => 'SYSTEM',
		);
		if ( $min_php_version <= PHP_VERSION_ID ) {
			$this->set_success( $settings );
		}
		else {
			$this->set_fail( $settings );
		}
	}

	public function have_fails() {
		$fail = false;
		foreach ( $this->reports as $report_key => $report_info ) {
			if ( $report_info['state'] == 'fail' ) {
				$fail = true;
			}
		}

		return $fail;
	}

	public function fix_issue( $issue_key = null ) {
		$report = $this->reports[$issue_key];
		if ( $issue_key != null ) {
			switch ( $report['type'] ) {
			case 'DIR_EXISTS':{
					if ( !file_exists( $report['path'] ) ) {
						return mkdir( $report['path'], 0755 );
					}
					else {
						return true;
					}
				} break;

			case 'FILE_EXISTS':{
					if ( !file_exists( $report['path'] ) ) {
						file_put_contents( $report['path'], '' );
						chmod( $report['path'], 0755 );
						return file_exists( $report['path'] );
					}
					else {
						return true;
					}
				} break;

			case 'IS_WRITABLE':{
					if ( !is_writable( $report['path'] ) ) {
						return chmod( $report['path'], 0755 );
					}
					else {
						return true;
					}
				} break;

			case 'SYSTEM':{
					if ( $report['state'] == 'success' ) {
						return true;
					}
					else {
						return false;
					}
				} break;
			}

		}
	}

	public function fix_all_issues() {
		foreach ( $this->reports as $report_key => $report_info ) {
			$settings = array(
				'source' => $report_info['source'],
				'report_key' => $report_key,
				'path' => $report_info['path'],
				'success_message' => $report_info['success_message'],
				'fail_message' => $report_info['fail_message'],
				'type' => $report_info['type'],
			);
			if ( $this->fix_issue( $report_key ) ) {
				$this->set_success( $settings );
			}
			else {
				$this->set_fail( $settings );
			}

		}
	}

	public function set_success( $settings ) {
		$this->reports[$settings['report_key']]['type']            = ( isset( $settings['type'] ) ) ? $settings['type'] : false;
		$this->reports[$settings['report_key']]['source']          = ( isset( $settings['source'] ) ) ? $settings['source'] : false;
		$this->reports[$settings['report_key']]['state']           = 'success';
		$this->reports[$settings['report_key']]['path']            = ( isset( $settings['path'] ) ) ? $settings['path'] : false;
		$this->reports[$settings['report_key']]['success_message'] = ( isset( $settings['success_message'] ) ) ? $settings['success_message'] : false;
		$this->reports[$settings['report_key']]['fail_message']    = ( isset( $settings['fail_message'] ) ) ? $settings['fail_message'] : false;
		update_option( $this->option_key, $this->reports );
	}

	public function set_fail( $settings ) {
		$this->reports[$settings['report_key']]['type']            = ( isset( $settings['type'] ) ) ? $settings['type'] : false;
		$this->reports[$settings['report_key']]['source']          = ( isset( $settings['source'] ) ) ? $settings['source'] : false;
		$this->reports[$settings['report_key']]['path']            = ( isset( $settings['path'] ) ) ? $settings['path'] : false;
		$this->reports[$settings['report_key']]['state']           = 'fail';
		$this->reports[$settings['report_key']]['success_message'] = ( isset( $settings['success_message'] ) ) ? $settings['success_message'] : false;
		$this->reports[$settings['report_key']]['fail_message']    = ( isset( $settings['fail_message'] ) ) ? $settings['fail_message'] : false;
		update_option( $this->option_key, $this->reports );
	}

	// Function assign new report. It's get two parametrs:
	// $settings (array) - array with settings
	// $flag (string) - flage, that define action to do. This array
	// must have next fields: source, report_key, path, success_message, fail_message
	public function assign_report( $settings = array(), $flag = 'FILE_EXISTS' ) {
		if ( !empty( $settings ) ) {
			$settings['type'] = $flag;
			switch ( $flag ) {
			case 'DIR_EXISTS':{
					if ( file_exists( $settings['path'] ) ) {
						$this->set_success( $settings );
					}
					else {
						$this->set_fail( $settings );
					}
				} break;

			case 'FILE_EXISTS':{
					if ( file_exists( $settings['path'] ) ) {
						$this->set_success( $settings );
					}
					else {
						$this->set_fail( $settings );
					}
				} break;

			case 'IS_WRITABLE':{
					if ( is_writable( $settings['path'] ) ) {
						$this->set_success( $settings );
					}
					else {
						$this->set_fail( $settings );
					}
				} break;

			case 'IS_SET':{
					if ( isset( $settings['path'] ) ) {
						$this->set_success( $settings );
					}
					else {
						$this->set_fail( $settings );
					}
				} break;
			}
		}
	}

}
?>
