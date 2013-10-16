<p>You are about to remove the following packages from server:</p>

<ul class="ul-disc">
    <li>Standalone package from: <strong><?php echo $package_info['date'].' '.$package_info['time'].' ('.$package_info['a_file'].')'; ?></strong></li>
    <li>Child package from: <strong><?php echo $package_info['date'].' '.$package_info['time'].' ('.$package_info['c_file'].')'; ?></strong></li>
</ul>

<p>Are you sure you wish to delete these files?</p>
<a href="<?php echo $Themes_Manager_Admin->self_url('do-package').'&name='.$name.'&action=delete-package&package='.$package; ?>" class = "button-secondary">Yes, Delete these files</a>
<a href="<?php echo $Themes_Manager_Admin->self_url('do-package').'&name='.$name; ?>" class = "button-secondary">No, Return me to the theme list</a>
