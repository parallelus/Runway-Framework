<p><?php echo __( 'You are about to remove the following packages from server', 'runway' ); ?>:</p>

<ul class="ul-disc">
	<li><?php echo __( 'All Standalone packages', 'runway' ); ?></li>
	<li><?php echo __( 'All Child packages', 'runway' ); ?></li>
</ul>

<p><?php _e( 'Are you sure you wish to delete these files?', 'runway' ); ?></p>
<a href="<?php echo esc_url(
	$developer_tools->self_url( 'do-package' ) .
	'&name=' . $name .
	'&action=delete-package-all&package=all' .
	'&_wpnonce=' .  wp_create_nonce( 'delete-package-all' )
); ?>"
   class="button-secondary"><?php echo __( 'Yes, Delete these files', 'runway' ); ?></a>
<a href="<?php echo esc_url( $developer_tools->self_url( 'do-package' ) . '&name=' . $name ); ?>"
   class="button-secondary"><?php echo __( 'No, Return me to the theme list', 'runway' ); ?></a>
