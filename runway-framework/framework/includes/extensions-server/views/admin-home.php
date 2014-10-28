<?php if ( isset($info_message) && $info_message != '' ): ?>
	<div id="message" class="updated"><p><?php echo $info_message; ?></p></div>
	<?php endif; ?>
<?php if ( !empty( $this->server_extensions ) ): ?>
	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th id="name" class="manage-column column-name">Extension</th>
				<th id="description" class="manage-column column-description">Description</th>
			</tr>
		</thead>
		<tbody id="the-list">
		<?php foreach ( $this->server_extensions as $ext_slug => $extension ): ?>
			<tr class="inactive">
				<td class="plugin-title">
					<?php echo $extension['Name']; ?>
					 (<a style="color: #BC0B0B;" href="<?php echo admin_url('admin.php?page=server&navigation=del-extension-confirmation&ext='.urlencode( $ext_slug )); ?>">Delete</a>)
				</td>
				<td class="column-description desc"><?php echo $extension['Description']; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
