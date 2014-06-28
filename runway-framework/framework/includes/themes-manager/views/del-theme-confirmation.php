<p><?php echo __('You are about to remove the following theme', 'framework'); ?>:</p>

<ul class="ul-disc">
    <li><strong><?php echo $del_theme_info['Name']; ?></strong> by <em><?php echo ( $del_theme_info['Author'] != '' ) ? $del_theme_info['Author'] : 'Runway Framework'; ?></em></li>
</ul>

<p><?php echo __('Are you sure you wish to delete these files', 'framework'); ?>?</p>
<a href="<?php echo network_admin_url('admin.php?page=themes&navigation=delete-theme&name='.$del_theme_info['Folder'].'&confirm=true'); ?>" class = "button-secondary"><?php echo __('Yes, Delete these files', 'framework'); ?></a>
<a href="<?php echo network_admin_url('admin.php?page=themes'); ?>" class = "button-secondary"><?php echo __('No, Return me to the theme list', 'framework'); ?></a>
