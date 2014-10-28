<p>You are about to remove the following extension from server:</p>
<ul class="ul-disc">
    <li><strong><?php echo $this->server_extensions[$_GET['ext']]['Name'] ?></strong> by <em><?php echo ( $this->server_extensions[$_GET['ext']]['Author'] != '' ) ? $this->server_extensions[$_GET['ext']]['Author'] : 'Parallelus'; ?></em></li>
</ul>

<p>Are you sure you wish to delete these files?</p>
<a href="<?php echo admin_url('admin.php?page=server&navigation=del-extension&ext='.$_GET['ext'].'&confirm=true'); ?>" class = "button-secondary">Yes, Delete these files</a>
<a href="<?php echo admin_url('admin.php?page=server'); ?>" class = "button-secondary">No, Return me to the theme list</a>
