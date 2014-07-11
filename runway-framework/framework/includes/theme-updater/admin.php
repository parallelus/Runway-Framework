<?php

global $theme_updater_admin, $auth_manager_admin;

// Empty
?>

<?php
	if(!isset($auth_manager_admin->token)) {
		echo __('Please sign in with ', 'framework'); ?><a href="<?php echo network_admin_url('admin.php?page=accounts'); ?>"><?php echo __('Accounts', 'framework'); ?></a><?php 
	}
	else {
		echo __('You can get updates in ', 'framework'); ?><a href="<?php echo network_admin_url('update-core.php'); ?>"><?php echo __('Update core', 'framework'); ?></a><?php 
	}
?>

