<p><?php echo __( 'You are about to remove the following packages from server', 'runway' ); ?>:</p>

<ul class="ul-disc">
	<li><?php echo __( 'Standalone package from', 'runway' ); ?>:
		<strong><?php echo wp_kses_post( $package_info['date'] . ' ' . $package_info['time'] . ' (' . $package_info['a_file'] . ')' ); ?></strong>
	</li>
	<li><?php echo __( 'Child package from', 'runway' ); ?>:
		<strong><?php echo wp_kses_post( $package_info['date'] . ' ' . $package_info['time'] . ' (' . $package_info['c_file'] . ')' ); ?></strong>
	</li>
</ul>

<p><?php _e( 'Are you sure you wish to delete these files?', 'runway' ); ?></p>
<a href="<?php echo esc_url( $developer_tools->self_url( 'do-package' ) . '&name=' . $name . '&action=delete-package&package=' . $package ); ?>"
   class="button-secondary"><?php echo __( 'Yes, Delete these files', 'runway' ); ?></a>
<a href="<?php echo esc_url( $developer_tools->self_url( 'do-package' ) . '&name=' . $name ); ?>"
   class="button-secondary"><?php echo __( 'No, Return me to the theme list', 'runway' ); ?></a>
