<?php

// WP_Screen_Tabs::add_tab('server', 'server-tab-1', 'Server tab1', 'Server');


/*
	WP_Screen_Tabs::add_tab('server', 'server-tab-2', 'Server tab2', 'Hellow world 2');
	WP_Screen_Tabs::add_tab('server', 'server-tab-3', 'Server tab3', 'Hellow world 3');

	WP_Screen_Tabs::add_tab('edit-comments.php', 'server-tab-c1', 'Server tabc1', 'Hellow world 1');
	WP_Screen_Tabs::add_tab('edit-comments.php', 'server-tab-c2', 'Server tabc2', 'Hellow world 2');
	WP_Screen_Tabs::add_tab('edit-comments.php', 'server-tab-c3', 'Server tabc3', 'Hellow world 3');

	WP_Screen_Tabs::add_tab('directory', 'directory-tab-c1', 'directory tabc1', 'Hellow world 1');
	WP_Screen_Tabs::add_tab('directory', 'directory-tab-c2', 'directory tabc2', 'Hellow world 2');

	WP_Screen_Tabs::add_tab('*', 'directory-tab-c3', 'directory tabc3', 'All');
	WP_Screen_Tabs::add_tab('theme/*', 'directory-tab-c1233', 'directory tabc3', 'Theme');
	WP_Screen_Tabs::add_tab('wordpress/*', 'directory-tab-c1233', 'directory tabc3', 'wordpress', 'test_call_back_from_help_tabs');

	function test_call_back_from_help_tabs() {
		echo "Callback !!!";
	}
*/


class WP_Screen_Tabs {

	public static $tabs = array();

	public function __construct() {

		add_action( 'admin_head', array( &$this, 'apply_tabs' ) );
	}

	public function apply_tabs() {

		do_action( 'before-apply-tabs' );

		$screen = get_current_screen();

		if ( isset( self::$tabs['*'] ) ) {
			foreach ( self::$tabs['*'] as $tab_key => $tab ) {
				$screen->add_help_tab( $tab );
			}
		}

		if ( $GLOBALS['pagenow'] == 'admin.php' ) {
			foreach ( self::$tabs as $page => $tabs ) {
				if ( $page == 'theme/*' || $screen->id == 'admin_page_'.$page || strstr( $page, 'admin.php?' ) ) {
					foreach ( $tabs as $tab_key => $tab ) {
						$screen->add_help_tab( $tab );
					}
				}
			}
		} else {
			foreach ( self::$tabs as $page => $tabs ) {
				if ( $page == 'wordpress/*' || $GLOBALS['pagenow'] == $page ) {
					foreach ( $tabs as $tab_key => $tab ) {
						$screen->add_help_tab( $tab );
					}
				}
			}
		}

	}

	public static function add_tab( $page_name = '', $id = '', $title = '', $content = '', $callback = '' ) {

		if ( is_array( $page_name ) ) {
			foreach ( $page_name as $name ) {
				self::$tabs[$name][$name.'_'.$id] = array( 'id' => $id, 'title' => $title, 'content' => stripslashes( $content ), 'callback' => $callback );
			}
		} else {
			self::$tabs[$page_name][$name.'_'.$id] = array( 'id' => $id, 'title' => $title, 'content' => stripslashes( $content ), 'callback' => $callback );
		}

	}

} ?>
