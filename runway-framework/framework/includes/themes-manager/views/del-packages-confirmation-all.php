<p><?php echo __('You are about to remove all old packages from server. Are you sure?', 'framework'); ?></p>

<a href="<?php echo $developer_tools->self_url('do-package').'&name='.$name.'&action=delete-package-all'; ?>" class = "button-secondary"><?php echo __('Yes, Delete these files', 'framework'); ?></a>
<a href="<?php echo $developer_tools->self_url('do-package').'&name='.$name; ?>" class = "button-secondary"><?php echo __('No, Return me to the theme list', 'framework'); ?></a>
