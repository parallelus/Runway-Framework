<?php
global $apm, $ApmAdmin, $alias_;

$pages_dir = $apm->pages_dir;

if ( get_stylesheet_directory() == TEMPLATEPATH ) {
	echo '<br>You must create or activate a Runway child theme to add options pages: <a href="'.home_url().'/wp-admin/admin.php?page=themes">Runway Themes</a>';
}
else {
	if ( !isset( $ApmAdmin->navigation ) || empty( $ApmAdmin->navigation ) )
		$ApmAdmin->navigation = 'list-pages';

	switch ( $ApmAdmin->navigation ) {
	case 'new-page': {
			$new_page_id = time();

			$page = array(
				'settings' => array(
					'page_id' => $new_page_id,
					'title' => 'New Options Page',
					'alias' => 'options-page',
					'adminMenuTopItem' => 'current-theme',
					'showPageTitle' => 'true',
				),
				'elements' => array(),
			);

			$page_json = json_encode( $page );

			include_once 'views/page-builder.php';

		} break;

		// edit page
	case 'edit-page': {

			$page_id = $_GET['page_id'];

			if ( file_exists( $pages_dir.$page_id.'.json' ) ) {
				$page_json = file_get_contents( $pages_dir.$page_id.'.json' );
				$page = json_decode( $page_json, true );

				$page = $apm->inputs_decode( $page );
				$page_json = addslashes( $page_json );

				include_once 'views/page-builder.php';
			} else {
				wp_die( 'Page not found' );
			}
		} break;
		// list available pages
	case 'list-pages': {
			$pages = $apm->get_pages_list();
			include_once 'views/list-pages.php';

		} break;

	case 'remove-page': {
			$apm->del_page( $_GET['page_id'], $pages_dir );
			$pages = $apm->get_pages_list();

			include_once 'views/list-pages.php';
		} break;

	case 'duplicate-page': {

			$page_id = $_GET['page_id'];

			if ( file_exists( $pages_dir.$page_id.'.json' ) ) {
				$page_json = file_get_contents( $pages_dir.$page_id.'.json' );
				$page = json_decode( $page_json );

				$page->settings->page_id = time();
				$page->settings->title = $page->settings->title . ' (copy)';
				$new_alias = sanitize_title( $page->settings->title );
				$alias_ = $new_alias;
				get_copy_alias( $new_alias );
				$page->settings->alias = $alias_;

				$page_json = json_encode( $page );
				file_put_contents( $pages_dir.$page->settings->page_id.'.json', $page_json );

				$pages = $apm->get_pages_list();
				include_once 'views/list-pages.php';
			} else {
				wp_die( 'Page not found' );
			}

			include_once 'views/list-pages.php';
		} break;

	case 'reset-fields-page':{
			$page = json_decode( file_get_contents( $pages_dir.$_GET['page_id'].'.json' ) );
			$theme = rw_get_theme_data();
			delete_option( $theme['Folder'].'_'.$page->settings->alias );
			$pages = $apm->get_pages_list();

			include_once 'views/list-pages.php';
		} break;

	default : {
			include_once 'views/list-pages.php';
		} break;
	}
}

function get_copy_alias( $alias ) {
	global $apm, $alias_;
	$pages = $apm->get_pages_list();
	$check_sum = 0;
	foreach ( $pages as $page ) {
		if ( $alias == $page->settings->alias ) {
			$check_sum++;
		}
	}

	if ( $check_sum > 0 ) {
		$alias_ = $alias_.'-copy';
		get_copy_alias( $alias.'-copy' );
	}
}
?>
