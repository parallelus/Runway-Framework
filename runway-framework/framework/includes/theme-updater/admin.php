<?php

global $theme_updater_admin, $auth_manager_admin;

// Empty
?>

<?php
	if(!isset($auth_manager_admin->token)) {
		printf( __('Please sign in to your %sParallelus Account%s.', 'framework'), '<a href="'. network_admin_url("admin.php?page=accounts") .'">', '</a>' );
	}
	else {
		printf( __('You can check for new versions from the %sUpdates%s area of your admin.', 'framework'), '<a href="'. network_admin_url("update-core.php") .'">', '</a>' );
	}
?>

