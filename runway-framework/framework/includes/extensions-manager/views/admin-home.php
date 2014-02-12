<?php
// Info and Alert Messages
if ( $info_message != '' ) {
	echo '<div id="message" class="updated"><p>'. $info_message .'</p></div>';
}

$ext_inactive_status = '';
switch ( $this->navigation ) {
	case ( 'inactive' ):{
		$ext_inactive_status = 'class="current"';
		
	} break;
	case ( 'upgrade' ):{
		$ext_upgrade_status = 'class="current"';
	} break;
	default:{
		$ext_all_status = 'class="current"';
	}
}
?>
<ul class="subsubsub">
	<li class="all"><a href="admin.php?page=extensions&navigation=all" <?php echo $ext_all_status; ?>>All <span class="count">(<?php echo $ext_all_total; ?>)</span></a> |</li>
	<li class="inactive"><a href="admin.php?page=extensions&navigation=inactive" <?php echo $ext_inactive_status; ?>>Inactive <span class="count">(<?php echo $ext_inactive_total; ?>)</span></a></li>	
</ul>
<form method="post" action="admin.php?page=extensions&navigation=search#add-exts" class="clear">
	<p class="search-box">
		<label class="screen-reader-text" for="exts-search-input">Search Extensions:</label>
		<input type="search" id="exts-search-input" name="exts-search-input" value="<?php echo @$_POST['exts-search-input']; ?>">
		<input type="submit" name="ext-search-submit" id="ext-search-submit" class="button" value="Search Extensions"></p>
</form>

<form action="admin.php?page=extensions&navigation=bulk-actions" method="post">
	<div class="alignleft actions">
		<select name="action">
			<option value="-1" selected="selected">Bulk Actions</option>
			<option value="activate-selected">Activate</option>
			<option value="deactivate-selected">Deactivate</option>
			<option value="delete-selected">Delete</option>
		</select>
		<input type="submit" name="bulk-actions-submit" class="button-secondary action" value="Apply">
	</div>
<br><br>

<table class="wp-list-table widefat">
	<thead>
		<tr>
			<th scope="col" id="cb" class="manage-column column-cb check-column" style="width: 0px;"><input type="checkbox" name="ext_chk[]" /></th>
			<th id="name" class="manage-column column-name">Extension</th>
			<th id="description" class="manage-column column-description">Description</th>
		</tr>
	</thead>
	<tbody id="the-list">
	<?php
if ( !empty( $exts ) ):
	foreach ( $exts as $ext => $ext_info ):
		$ext_cnt = !$extm->is_activated( $ext );
?>
		<tr <?php if ( $ext_cnt ): ?> class="inactive" <?php else:  ?> calss="active" <?php endif; ?> >
			<th class="check-column">
				<input type="checkbox" name="ext_chk[]" value="<?php echo $ext; ?>" />
			</th>
			<td class="plugin-title">
				<strong><?php echo $ext_info['Name']; ?></strong>
				<?php if ( $ext_cnt ): ?>
					<br><a href="admin.php?page=extensions&navigation=extension-activate&ext=<?php echo urlencode( $ext ); ?>">Activate</a> |
					<a style="color: #BC0B0B;" href="admin.php?page=extensions&navigation=del-extension-confirm&ext=<?php echo urlencode( $ext ); ?>">Delete</a>
				<?php elseif ( !$ext_cnt ): ?>
					<br><a class="edit" href="admin.php?page=extensions&navigation=extension-deactivate&ext=<?php echo urlencode( $ext ); ?>">Deactivate</a>
				<?php endif;?>
			</td>
			<td class="column-description desc">
				<?php
// Item description
$description = '<div class="plugin-description"><p>'. $ext_info['Description'] .'</p></div>';
// Item info
$class = ( $ext_cnt ) ? 'inactive' : 'active' ;
$version = ( $ext_info['Version'] ) ? 'Version: '.$ext_info['Version'] : '';
if ( $ext_info['Author'] ) {
	$author = ' | By '. $ext_info['Author'];
	if ( $ext_info['AuthorURI'] ) {
		$author = ' | By <a href="'. $ext_info['AuthorURI'] .'" title="Visit author homepage">'. $ext_info['Author'] .'</a>';
	}
}
else {
	$author = ' | By Unknown';	
}
$plugin_link = ( $ext_info['ExtensionURI'] ) ? ' | <a href="'. $ext_info['ExtensionURI'] .'" title="Visit plugin site">Visit plugin site</a>' : '';
$info = '<div class="'. $class .'second plugin-version-author-uri">'. $version . $author . $plugin_link .'</div>';

// Print details
echo $description;
echo $info;
?>

				<?php if ( count( $ext_info['DepsExts'] ) > 0 && isset( $ext_info['DepsExts'] ) && !empty( $ext_info['DepsExts'] ) ): ?>
					<b>Dependences:</b>
					<?php
	$deps_count = count( $ext_info['DepsExts'] ); $i = 0;
foreach ( $ext_info['DepsExts'] as $dep_ext ):
	$i++;
$dep_ext = explode( '|', $dep_ext );
if ( file_exists( $extm->extensions_dir.$dep_ext[1] ) ) {
	$ext_data = $extm->get_extension_data( $extm->extensions_dir.$dep_ext[1] );
	$active = FALSE;
	if ( !empty( $extm->admin_settings ) )
		foreach ( $extm->admin_settings['extensions'][$extm->theme_name] as $ext_tmp ) {
			if ( $ext_tmp == $dep_ext[1] ) {
				$active = TRUE;
			}
		}
}
else {
	$active = FALSE;
	$ext_data['Name'] = $dep_ext[0];
}
$coma = ( $i == $deps_count ) ? '' : ',';

$active = $active ? '<i style="color: green;">Active</i>' :
'<i style="color: red;">Disabled</i>';
?>
							<?php echo $ext_data['Name']; ?>(<?php echo $active; ?>)<?php echo $coma; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; else: ?>
		<tr calss="active">
			<td class="plugin-title">
				Extensions not found.
			</td>
			<td class="column-description desc"> </td>
		</tr>
	<?php endif; ?>

	</tbody>
</table>
</form>
