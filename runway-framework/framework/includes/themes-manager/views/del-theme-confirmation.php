<p>You are about to remove the following theme:</p>

<ul class="ul-disc">
    <li><strong><?php echo $del_theme_info['Name']; ?></strong> by <em><?php echo ( $del_theme_info['Author'] != '' ) ? $del_theme_info['Author'] : 'Runway Framewok'; ?></em></li>
</ul>

<p>Are you sure you wish to delete these files?</p>
<a href="admin.php?page=themes&navigation=delete-theme&name=<?php echo $del_theme_info['Folder']; ?>&confirm=true" class = "button-secondary">Yes, Delete these files</a>
<a href="admin.php?page=themes" class = "button-secondary">No, Return me to the theme list</a>
