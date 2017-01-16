<?php

global $theme_updater_admin, $auth_manager_admin;

if ( ! isset( $auth_manager_admin->token ) ) {
	printf(
		__( 'Please sign in to your %sParallelus Account%s.', 'runway' ),
		'<a href="' . admin_url( 'admin.php?page=accounts' ) . '">', '</a>'
	);
} else {
	printf(
		__( 'You can check for new versions from the %sUpdates%s area of your admin.', 'runway' ),
		'<a href="' . network_admin_url( 'update-core.php' ) . '">', '</a>'
	);
}
