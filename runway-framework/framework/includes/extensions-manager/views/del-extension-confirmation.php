<p>You are about to remove the following extension from server:</p>

<ul class="ul-disc">
    <li><strong><?php echo $extm->extensions_List[$_GET['ext']]['Name'] ?></strong> by <em><?php echo ( $extm->extensions_List[$_GET['ext']]['Author'] != '' ) ? $extm->extensions_List[$_GET['ext']]['Author'] : 'Parallelus'; ?></em></li>
</ul>

<p>Are you sure you wish to delete these files?</p>
<a href="admin.php?page=extensions&navigation=del-extension&ext=<?php echo $_GET['ext']; ?>&confirm=true" class = "button-secondary">Yes, Delete these files</a>
<a href="admin.php?page=extensions" class = "button-secondary">No, Return me to the theme list</a>
