<script type="text/javascript">

	// page remove
	jQuery(function () {
		jQuery('.remove-page').on('click', function () {

			jQuery.ajax({
				url:jQuery(this).prop('href'),
				success:function () {
					document.location.reload();
				}
			});

			return false;
		});
	});

</script>

<?php if ( $pages ) { ?>

	<table class="wp-list-table widefat" style="margin-top: 15px;">
	<thead>
	<tr>
		<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style="width: 0px;"><input type="checkbox" name="ext_chk[]" value=""></th> -->
		<th id="name_head" class="manage-column column-name"><?php _e( 'Page name', 'framework' ); ?></th>
		<th id="alias_head" class="manage-column column-alias"><?php _e( 'Alias', 'framework' ); ?></th>
		<th id="description_head"
			class="manage-column column-description"><?php _e( 'Description', 'framework' ); ?></th>
	</tr>
	</thead>
	<tbody id="the-list">

	<?php

	$row = 0;

	foreach ( $pages as $page ) {
		$alias = $page->settings->alias;
		$trClass = ( $row++ % 2 ) ? '' : 'alternate';
		?>

		<tr class="<?php echo $trClass; ?>">
			<td class="plugin-title">
				<strong><a href="<?php echo admin_url('admin.php?page=options-builder&navigation=edit-page&page_id='.$page->settings->page_id); ?>"><?php echo $page->settings->title ?></a></strong>

				<div class="row-actions">
					<span class="edit"><a
							href="<?php echo admin_url('admin.php?page=options-builder&navigation=edit-page&page_id='.$page->settings->page_id); ?>"
							title="<?php _e( 'Edit this item', 'framework' ); ?>"><?php _e( 'Edit', 'framework' ); ?></a> | </span>
					<span class="view"><a href="<?php echo admin_url('admin.php?page='.$alias); ?>"
										  title="<?php _e( 'View this page', 'framework' ); ?>" 
										  rel="permalink"><?php _e( 'View', 'framework' ); ?></a> | </span>
					<span class="edit"><a class="submitdelete" title="<?php _e( 'Duplicate this item', 'framework' ); ?>"
										   href="<?php echo admin_url('admin.php?page=options-builder&navigation=duplicate-page&page_id='.$page->settings->page_id); ?>"><?php _e( 'Duplicate', 'framework' ); ?></a> | </span>
					<span class="edit"><a
							href="<?php echo admin_url('admin.php?page=options-builder&navigation=reset-fields-page&page_id='.$page->settings->page_id); ?>"
							title="<?php _e( 'Reset default field values. This will clear any data added while testing the page.', 'framework' ); ?>"><?php _e( 'Reset Defaults', 'framework' ); ?></a> | </span>
					<span class="trash"><a class="submitdelete" title="<?php _e( 'Delete this item', 'framework' ); ?>"
										   href="<?php echo admin_url('admin.php?page=options-builder&navigation=confirm-remove-page&page_id='.$page->settings->page_id); ?>"><?php _e( 'Delete', 'framework' ); ?></a></span>
				</div>
			</td>
			<td class="column-alias">
				<?php echo $page->settings->alias ?>
			</td>
			<td class="column-description desc">
				<?php echo $page->settings->pageDescription ?>
			</td>

		</tr>

			<?php } ?>

	</tbody>
	<tfoot>
	<tr>
		<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style="width: 0px;"><input type="checkbox" name="ext_chk[]" value=""></th> -->
		<th id="name_foot" class="manage-column column-name"><?php _e( 'Page name', 'framework' ); ?></th>
		<th id="alias_foot" class="manage-column column-alias"><?php _e( 'Alias', 'framework' ); ?></th>
		<th id="description_foot"
			class="manage-column column-description"><?php _e( 'Description', 'framework' ); ?></th>
	</tr>
	</tfoot>
	</table>
	<?php } else { ?>
		<div>
		<h3><?php _e( 'No pages have been created.', 'framework' ); ?></h3>

		<p>
			<a class="button" href="<?php echo admin_url('admin.php?page=options-builder&navigation=new-page'); ?>"><?php _e( 'Create new admin page', 'framework' ); ?></a>
		</p>
		</div>
	<?php } ?>
