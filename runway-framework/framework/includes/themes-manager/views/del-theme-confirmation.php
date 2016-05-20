<p><?php echo __('You are about to remove the following theme', 'runway'); ?>:</p>

<ul class="ul-disc">
    <li><strong><?php echo wp_kses_post($del_theme_info['Name']); ?></strong> <?php echo __('by', 'runway'); ?> <em><?php echo ( $del_theme_info['Author'] != '' ) ? wp_kses_post($del_theme_info['Author']) : 'Runway Framework'; ?></em></li>
</ul>

<p><?php echo __('Are you sure you wish to delete these files', 'runway'); ?>?</p>
<a href="<?php echo esc_url( admin_url('admin.php?page=themes&navigation=delete-theme&name='.$del_theme_info['Folder'].'&confirm=true') ); ?>" class = "button-secondary"><?php echo __('Yes, Delete these files', 'runway'); ?></a>
<a href="<?php echo esc_url( admin_url('admin.php?page=themes') ); ?>" class = "button-secondary"><?php echo __('No, Return me to the theme list', 'runway'); ?></a>
