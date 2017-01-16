<?php if ( $pages ) { ?>

	<table class="wp-list-table widefat" style="margin-top: 15px;">
		<thead>
		<tr>
			<!-- <th scope="col" id="cb" class="manage-column column-cb check-column" style="width: 0px;"><input type="checkbox" name="ext_chk[]" value=""></th> -->
			<th id="name_head" class="manage-column column-name">
				<?php _e( 'Page name', 'runway' ); ?>
			</th>
			<th id="alias_head" class="manage-column column-alias">
				<?php _e( 'Alias', 'runway' ); ?>
			</th>
			<th id="description_head" class="manage-column column-description">
				<?php _e( 'Description', 'runway' ); ?>
			</th>
			<th id="order_head" class="manage-column column-order">
				<?php _e( 'Order', 'runway' ); ?>
			</th>
		</tr>
		</thead>
		<tbody id="the-list">

		<?php
		$row = 0;

		foreach ( $pages as $page ) {
			$alias   = $page->settings->alias;
			$trClass = ( $row++ % 2 ) ? '' : 'alternate';
			?>

			<tr class="<?php echo esc_attr( $trClass ); ?>">
				<td class="plugin-title">
					<strong>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=options-builder&navigation=edit-page&page_id=' . $page->settings->page_id ) ); ?>">
							<?php echo wp_kses_post( $page->settings->title ); ?>
						</a>
					</strong>

					<div class="row-actions">
						<span class="edit">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=options-builder&navigation=edit-page&page_id=' . $page->settings->page_id ) ); ?>"
							   title="<?php _e( 'Edit this item', 'runway' ); ?>">
								<?php _e( 'Edit', 'runway' ); ?>
							</a> |
						</span>
						<span class="view">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $alias ) ); ?>"
							   title="<?php _e( 'View this page', 'runway' ); ?>" rel="permalink">
								<?php _e( 'View', 'runway' ); ?>
							</a> |
						</span>
						<span class="edit">
							<a class="submitdelete" title="<?php _e( 'Duplicate this item', 'runway' ); ?>"
							   href="<?php echo esc_url( admin_url( 'admin.php?page=options-builder&navigation=duplicate-page&page_id=' . $page->settings->page_id . '&_wpnonce=' . wp_create_nonce( 'duplicate-page' ) ) ); ?>">
								<?php _e( 'Duplicate', 'runway' ); ?>
							</a> |
						</span>
						<span class="edit">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=options-builder&navigation=reset-fields-page&page_id=' . $page->settings->page_id ) . '&_wpnonce=' . wp_create_nonce( 'reset-fields-page' ) ); ?>"
							   title="<?php _e( 'Reset default field values. This will clear any data added while testing the page.', 'runway' ); ?>">
								<?php _e( 'Reset Defaults', 'runway' ); ?>
							</a> |
						</span>
						<span class="trash">
							<a class="submitdelete" title="<?php _e( 'Delete this item', 'runway' ); ?>"
							   href="<?php echo esc_url( admin_url( 'admin.php?page=options-builder&navigation=confirm-remove-page&page_id=' . $page->settings->page_id ) ); ?>">
								<?php _e( 'Delete', 'runway' ); ?>
							</a>
						</span>
					</div>
				</td>
				<td class="column-alias">
					<?php echo wp_kses_post( $page->settings->alias ); ?>
				</td>
				<td class="column-description desc">
					<?php echo wp_kses_post( $page->settings->pageDescription ); ?>
				</td>
				<td class="column-order">
					<?php echo ( ! isset( $page->settings->menu_order ) || empty( $page->settings->menu_order ) ) ? 0 : $page->settings->menu_order; ?>
				</td>
			</tr>

		<?php } ?>

		</tbody>
		<tfoot>
		<tr>
			<th id="name_foot" class="manage-column column-name">
				<?php _e( 'Page name', 'runway' ); ?>
			</th>
			<th id="alias_foot" class="manage-column column-alias">
				<?php _e( 'Alias', 'runway' ); ?>
			</th>
			<th id="description_foot" class="manage-column column-description">
				<?php _e( 'Description', 'runway' ); ?>
			</th>
			<th id="alias_foot" class="manage-column column-order">
				<?php _e( 'Order', 'runway' ); ?>
			</th>
		</tr>
		</tfoot>
	</table>

<?php } else { ?>

	<div>
		<h3><?php _e( 'No pages have been created.', 'runway' ); ?></h3>

		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=options-builder&navigation=new-page' ) ); ?>">
				<?php _e( 'Create new admin page', 'runway' ); ?>
			</a>
		</p>
	</div>

<?php }
