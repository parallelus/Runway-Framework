<?php

// Look up theme data
if ( isset( $_REQUEST['name'] ) && !isset( $options ) ) {
	$nameKey = $_REQUEST['name'];
	$options = $developer_tools->load_settings( $nameKey );
}

// A few defaults and some error checking
$action  = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
$name    = (isset($options['Name'])) ? $options['Name'] : '';
$history = $developer_tools->get_history( $nameKey );
$ts      = time();

// If history is disabled, delete old versions (better to just let user manually create and delete)
// $history = $developer_tools->clear_old_packages( $_REQUEST['name'] );

// Get most recent package (or create it)
if (!count($history) || $action == 'rebuild') {
	// No packages exist or user requested new build 
	$alone_package_download_url = $developer_tools->build_alone_theme( $nameKey, $ts );
	$child_package_download_url = $developer_tools->build_child_package( $nameKey, $ts );
	$most_recent = $developer_tools->make_package_info_from_ts( $nameKey, $ts );
	$tags = array('id' => $most_recent['exp'],
				  'tags_show' => isset($_REQUEST['tags_show'])? $_REQUEST['tags_show'] : '',
				  'tags_edit' => isset($_REQUEST['tags_edit'])? $_REQUEST['tags_edit'] : ''
				  );
	$developer_tools->update_package_tags( $tags );

	// Display success message
	if ($alone_package_download_url && $child_package_download_url && $most_recent)
		echo '<div id="message" class="updated below-h2"><p>'. __('New download package created', 'framework').'.</p></div>';
} else {
	// Use most recent package from history
	$most_recent = array_shift($history);
}

// Breadcrumbs
$navName = ($name) ? ': '.$name : '';
$developer_tools->navigation_bar( array('Download'.$navName) );


// Show the current package (most recent)
// -----------------------------------------
 
$current_package = $most_recent;
$current_data = json_decode( $developer_tools->get_package_tags( $current_package['exp'] ) );
$current_tag = ($current_data && $current_data->tags_show == "true" )? $current_data->tags_edit : '';
?>
<h3><?php _e('Most Recent', 'framework') ?></h3>

<table class="widefat">
	<thead>
		<tr>
			<th><?php _e('Date', 'framework') ?></th>
			<th><?php _e('Standalone Theme', 'framework') ?></th>
			<th><?php _e('Child Theme', 'framework') ?></th>
			<th><?php _e('Tags', 'framework') ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><p><?php echo $current_package['date'] .", ". $current_package['time']; ?></p></td>
			<td>
<?php 
if ( $current_package['a_hash'] ) { 
	?>

				<p><a href="<?php echo home_url() . '/wp-content/themes/' . $_REQUEST['name'] . '/download/' . $current_package['a_file']?>" class="button-primary"><?php _e('Download', 'framework') ?></a></p>
				<p class="checksum"><?php echo __('Checksum', 'framework') .": <span class='code'>". $current_package['a_hash']; ?></span></p>
				<?php 
			} 
else { 

	_e('Not found', 'framework');
} ?>
			</td>
			<td>
<?php 
if ( $current_package['c_hash'] ) { ?>
				<p><a href="<?php echo home_url() . '/wp-content/themes/' . $_REQUEST['name'] . '/download/' . $current_package['c_file']?>" class="button-primary"><?php _e('Download', 'framework') ?></a></p>
				<p class="checksum"><?php echo __('Checksum', 'framework') .": <span class='code'>". $current_package['c_hash']; ?></span></p>
<?php 
} 
else { ?>Not found<?php } ?>
			</td>
			<td>
                <span class="text-display" title="<?php  echo substr($current_tag, 0, 50); if(strlen($current_tag) > 50) echo '...'; ?>">
                    <?php
                    if(strlen($current_tag) > 12) echo substr($current_tag, 0, 12) . '...';
                    else echo $current_tag;
                    ?>
                </span>
			</td>
		</tr>
	</tbody>

	<tfoot>
		<tr>
			<th><?php _e('Date', 'framework') ?></th>
			<th><?php _e('Standalone Theme', 'framework') ?></th>
			<th><?php _e('Child Theme', 'framework') ?></th>
			<th><?php _e('Tags', 'framework') ?></th>
		</tr>
	</tfoot>
</table>


<?php
// Rebuild package button
$rebuild_button = $html->settings_link(__('Rebuild Download Packages', 'framework'), array('class'=>'button-primary rebuild-package','action'=>'rebuild','navigation'=>'do-package','name'=>$nameKey));
?>

<p><?php echo $rebuild_button; ?></p>

<div class="tags-dialog">
	<fieldset>
	    <label for="tags-edit"><?php echo __('Add tags to this package? (optional)','framework'); ?></label><br/>
		<textarea id="tags-edit" name="tags-edit" class="settings-textarea" cols=40 rows=5></textarea>
	    <input type="hidden" id="package-id" value='' >
	</fieldset>
	<br>
	<button id="tags-save" class="button accept-changes button-primary"><?php _e( 'Update', 'framework' ); ?></button>
</div>

<?php 

// Show history (old packages)
// -----------------------------------------

if ( $history ) { ?>

	<br>
	<h3><?php _e('History', 'framework') ?></h3>
	
	<table class="widefat">
		<thead>
			<tr>
				<th><?php _e('Date', 'framework') ?></th>
				<th><?php _e('Standalone Theme', 'framework') ?></th>
				<th><?php _e('Child Theme', 'framework') ?></th>
				<th><?php _e('Tags', 'framework') ?></th>
				<th><?php _e('Edit', 'framework') ?></th>				
				<th><?php _e('Delete', 'framework') ?></th>
			</tr>
		</thead>
		<tbody><?php
		$history = $developer_tools->get_history( $_REQUEST['name'] );

		if ( !$history ) { ?>
				<tr>
					<th colspan="3">

						<div style="text-align: center;"><?php _e('Have no packages to download', 'framework'); ?></div>

					</th>
				</tr>
			<?php 
		} else {
			foreach ( $history as $package ) { 
				if ( $current_package['exp'] == $package['exp'] ) 
					// Skip the one already shown in "Most Recent"
					continue; 
				else {
					$data = json_decode( $developer_tools->get_package_tags( $package['exp'] ) );
					$tag = ($data && $data->tags_show == "true" )? $data->tags_edit : '';
				}
				?>
				<tr>
					<td><p><?php echo $package['date'] .", ". $package['time']; ?></p></td>
					<td>
				<?php 
				if ( $package['a_hash'] ) { ?>
						<p><a href="<?php echo home_url() . '/wp-content/themes/' . $_REQUEST['name'] . '/download/' . $package['a_file']?>"><?php _e('Download', 'framework') ?></a> &nbsp;(<span class="code"><?php echo $package['a_hash']; ?></span>)</p>
						<?php 
				} else { 
					_e('Package Not Found', 'framework');
				} ?>
					</td>
					<td>
				<?php 
				if ( $package['c_hash'] ) { ?>
						<p><a href="<?php echo home_url() . '/wp-content/themes/' . $_REQUEST['name'] . '/download/' . $package['c_file']?>"><?php _e('Download', 'framework') ?></a> &nbsp;(<span class="code"><?php echo $package['c_hash']; ?></span>)</p>
				<?php 
				} else { 
					_e('Package Not Found', 'framework');
				} ?>
					</td>
					<td>
                        <p title="<?php echo substr($tag, 0, 50); if(strlen($tag) > 50) echo '...'; ?>">
                            <?php
                                if(strlen($tag) > 12) echo substr($tag, 0, 12) . '...';
                                else echo $tag;
                            ?>
						</p>
					</td>
					<td>
						<p><a href="<?php echo $developer_tools->self_url('edit-tags-package').'&name='.$_REQUEST['name'].'&package='.$package['exp']; ?>" class="link-tags-edit"><?php _e('Edit', 'framework'); ?></a></p>
					</td>	
					<td>
						<!--.'&name=liftoff&action=delete-package&package='.$package['exp']-->
						<p><a href="<?php echo $developer_tools->self_url('confirm-del-package').'&name='.$_REQUEST['name'].'&package='.$package['exp']; ?>" ><?php _e('Delete', 'framework'); ?></a></p>
					</td>
				</tr>
			<?php }
		}?>
		</tbody>

		<tfoot>
			<tr>
				<th><?php _e('Date', 'framework') ?></th>
				<th><?php _e('Standalone Theme', 'framework') ?></th>
				<th><?php _e('Child Theme', 'framework') ?></th>
				<th><?php _e('Tags', 'framework') ?></th>				
				<th><?php _e('Edit', 'framework') ?></th>				
				<th><?php _e('Delete', 'framework') ?></th>
			</tr>
		</tfoot>
	</table>
    
    <?php
    // Delete all button
    $rebuild_button = $html->settings_link(__('Delete All Old Download Packages', 'framework'), array('class'=>'button-primary','navigation'=>'confirm-del-packages-all','name'=>$_REQUEST['name']));
    ?>

    <p><?php echo $rebuild_button; ?></p>

	<?php } ?>