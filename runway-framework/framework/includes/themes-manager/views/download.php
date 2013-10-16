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

	// Display success message
	if ($alone_package_download_url && $child_package_download_url && $most_recent)
		echo '<div id="message" class="updated below-h2"><p>New download package created.</p></div>';
} else {
	// Use most recent package from history
	$most_recent = array_shift($history);
}

// Breadcrumbs
$navName = ($name) ? ': '.$name : '';
$Themes_Manager_Admin->navigation_bar( array('Download'.$navName) );


// Show the current package (most recent)
// -----------------------------------------
 
$current_package = $most_recent;

?>
<h3><?php _e('Most Recent', 'framework') ?></h3>

<table class="widefat">
	<thead>
		<tr>
			<th><?php _e('Date', 'framework') ?></th>
			<th><?php _e('Standalone Theme', 'framework') ?></th>
			<th><?php _e('Child Theme', 'framework') ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><p><?php echo $current_package['date'] .", ". $current_package['time']; ?></p></td>
			<td>
<?php 
if ( $current_package['a_hash'] ) { 
	?>

				<p><a href="<?php echo get_bloginfo( 'url' ) . '/wp-content/themes/' . $_REQUEST['name'] . '/download/' . $current_package['a_file']?>" class="button-primary"><?php _e('Download', 'framework') ?></a></p>
				<p class="checksum"><?php echo __('Checksum', 'framework') .": <span class='code'>". $current_package['a_hash']; ?></span></p>
				<?php 
			} 
else { 
	?>
	Not found<?php 
} ?>
			</td>
			<td>
<?php 
if ( $current_package['c_hash'] ) { ?>
				<p><a href="<?php echo get_bloginfo( 'url' ) . '/wp-content/themes/' . $_REQUEST['name'] . '/download/' . $current_package['c_file']?>" class="button-primary"><?php _e('Download', 'framework') ?></a></p>
				<p class="checksum"><?php echo __('Checksum', 'framework') .": <span class='code'>". $current_package['c_hash']; ?></span></p>
<?php 
} 
else { ?>Not found<?php } ?>
			</td>
		</tr>
	</tbody>

	<tfoot>
		<tr>
			<th><?php _e('Date', 'framework') ?></th>
			<th><?php _e('Standalone Theme', 'framework') ?></th>
			<th><?php _e('Child Theme', 'framework') ?></th>
		</tr>
	</tfoot>
</table>


<?php
// Rebuild package button
$rebuild_button = $Themes_Manager_Admin->settings_link('Rebuild Download Packages', array('class'=>'button-primary rebuild-package','action'=>'rebuild','navigation'=>'do-package','name'=>$nameKey));
?>

<p><?php echo $rebuild_button; ?></p>


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
				<th><?php _e('Delete', 'framework') ?></th>
			</tr>
		</thead>
		<tbody><?php
		$history = $developer_tools->get_history( $_REQUEST['name'] );

		if ( !$history ) { ?>
				<tr>
					<th colspan="3">

						<div style="text-align: center;">Have no packages to download</div>

					</th>
				</tr>
			<?php 
		} else {
			foreach ( $history as $package ) { 
				if ( $current_package['exp'] == $package['exp'] ) 
					// Skip the one already shown in "Most Recent"
					continue; 
				?>
				<tr>
					<td><p><?php echo $package['date'] .", ". $package['time']; ?></p></td>
					<td>
				<?php 
				if ( $package['a_hash'] ) { ?>
						<p><a href="<?php echo get_bloginfo( 'url' ) . '/wp-content/themes/' . $_REQUEST['name'] . '/download/' . $package['a_file']?>"><?php _e('Download', 'framework') ?></a> &nbsp;(<span class="code"><?php echo $package['a_hash']; ?></span>)</p>
						<?php 
				} else { 
					_e('Package Not Found', 'framework');
				} ?>
					</td>
					<td>
				<?php 
				if ( $package['c_hash'] ) { ?>
						<p><a href="<?php echo get_bloginfo( 'url' ) . '/wp-content/themes/' . $_REQUEST['name'] . '/download/' . $package['c_file']?>"><?php _e('Download', 'framework') ?></a> &nbsp;(<span class="code"><?php echo $package['c_hash']; ?></span>)</p>
				<?php 
				} else { 
					_e('Package Not Found', 'framework');
				} ?>
					</td>
					<td>
						<!--.'&name=liftoff&action=delete-package&package='.$package['exp']-->
						<p><a href="<?php echo $Themes_Manager_Admin->self_url('confirm-del-package').'&name='.$_REQUEST['name'].'&package='.$package['exp']; ?>"><?php _e('Delete', 'framework'); ?></a></p>
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
				<th><?php _e('Delete', 'framework') ?></th>
			</tr>
		</tfoot>
	</table>
	<?php } ?>
