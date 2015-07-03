<p><?php echo __( 'You are about to remove the following extension from server', 'framework' );?>:</p>

<ul class="ul-disc">
    <li><strong><?php echo  $extm->extensions_List[$_GET['ext']]['Name'] ?></strong> <?php echo __('by', 'framework'); ?> <em><?php echo ( $extm->extensions_List[$_GET['ext']]['Author'] != '' ) ? $extm->extensions_List[$_GET['ext']]['Author'] : 'Parallelus'; ?></em></li>
</ul>

<p><?php echo __( 'Are you sure you wish to delete these files', 'framework' );?>?</p>
<a href="<?php echo esc_url( admin_url('admin.php?page=extensions&navigation=del-extension&ext='.$_GET['ext'].'&confirm=true') ); ?>" class = "button-secondary"><?php echo __( 'Yes, Delete these files', 'framework' );?></a>
<a href="<?php echo esc_url( admin_url('admin.php?page=extensions') ); ?>" class = "button-secondary"><?php echo __( 'No, Return me to the theme list', 'framework' );?></a>
