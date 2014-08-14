	<div class="changelog">
		<h3><?php echo __('Credits', 'framework'); ?></h3>

		<?php
		wp_enqueue_script('sort_credits-js', get_template_directory() . '/framework/includes/dashboard/js/sort_credits.js');
		//out($Dashboard_Admin->credits);
		if($Dashboard_Admin->credits['success']) { ?>
			<form id="credits_sort_form" method="post">
				<div class="pull-right tablenav">
					<select class="credits-sort" name="sort" id="credits_sort">
						<option value="achievements_count_desc" <?php echo ($Dashboard_Admin->selectableSort == 'achievements_count_desc')? 'selected' : ''; ?>><?php _e( 'Achievement count (from hight to low)', 'framework' ) ?></option>
						<option value="achievements_count_asc" <?php echo ($Dashboard_Admin->selectableSort == 'achievements_count_asc')? 'selected' : ''; ?>><?php _e( 'Achievement count (from low to hight)', 'framework' ) ?></option>
						<option value="user_name_asc" <?php echo ($Dashboard_Admin->selectableSort == 'user_name_asc')? 'selected' : ''; ?>><?php _e( 'Name (A-Z)', 'framework' ) ?></option>
						<option value="user_name_desc" <?php echo ($Dashboard_Admin->selectableSort == 'user_name_desc')? 'selected' : ''; ?>><?php _e( 'Name (Z-A)', 'framework' ) ?></option>
					</select>
					<input name="request" type="hidden" value="<?php echo $Dashboard_Admin->request; ?>">
					<input name="token" type="hidden" value="<?php echo $Dashboard_Admin->token; ?>">
					<input name="startPage" type="hidden" value="<?php echo $Dashboard_Admin->startPage; ?>">
					<input name="perPage" type="hidden" value="<?php echo $Dashboard_Admin->perPage; ?>">
					
					<div class="tablenav-pages">
						<span class="displaying-num"><?php echo $Dashboard_Admin->credits['totalResults'];?> <?php echo __('items', 'framework'); ?></span>
						<button class="first-page disabled pagination">&laquo;</button>
						<button class="next-page disabled pagination">&lt;</button>
						<span class="paging-input">
							<input type="text" class="current-page" value="1"/>
							<?php echo __('of', 'framework'); ?> <span class="total-pages">2</span>
						</span>
						<button class="prev-page disabled pagination">&gt;</button>
						<button class="last-page disabled pagination">&raquo;</button>
					</div>
				</div>
		
				<table class="wp-list-table widefat fixed">
					<thead>
						<tr>
							<th class="manage-column">
								<?php echo __('Avatar', 'framework'); ?>
							</th>
							<th id="title" class="manage-column" style="" scope="col">
								<a href="http://runway.dev/wp-admin/edit.php?orderby=title&order=asc">
									<?php echo __('Username', 'framework'); ?>
								</a>
							</th>
							<th id="title" class="manage-column" style="" scope="col">
								<a href="http://runway.dev/wp-admin/edit.php?orderby=title&order=asc">
									<?php echo __('Display name', 'framework'); ?>
								</a>
							</th>
							<th id="title" class="manage-column" style="" scope="col">
								<a href="http://runway.dev/wp-admin/edit.php?orderby=title&order=asc">
									<?php echo __('Achievements', 'framework'); ?>
								</a>
							</th>
						</tr>
					</thead>

					<tbody>
				<?php foreach($Dashboard_Admin->credits['result'] as $key => $credit) { ?>
						<tr>
							<td><img src="<?php echo $credit['avatar_url'];?>"/></td>
							<td><?php echo $credit['user_name'];?></td>
							<td><?php echo $credit['displayname'];?></td>
							<td>
								<?php foreach($credit['achievements'] as $achievement) { ?>
								<img src="<?php echo $achievement['achievement_image']; ?>" />
								<?php } ?>
							</td>
						</tr>
				<?php } ?>
					</tbody>
					<tfoot>
						<tr>
							<th class="manage-column">
								<?php echo __('Avatar', 'framework'); ?>
							</th>
							<th id="title" class="manage-column" style="" scope="col">
								<a href="http://runway.dev/wp-admin/edit.php?orderby=title&order=asc">
									<?php echo __('Username', 'framework'); ?>
								</a>
							</th>
							<th id="title" class="manage-column" style="" scope="col">
								<a href="http://runway.dev/wp-admin/edit.php?orderby=title&order=asc">
									<?php echo __('Display name', 'framework'); ?>
								</a>
							</th>
							<th id="title" class="manage-column" style="" scope="col">
								<a href="http://runway.dev/wp-admin/edit.php?orderby=title&order=asc">
									<?php echo __('Achievements', 'framework'); ?>
								</a>
							</th>
						</tr>
					</tfoot>
				</table>
			</form>
		<?php }
		/*if($Dashboard_Admin->credits['success']): ?>
			<div class="pull-right">
				<form id="credits_sort_form" method="post">
					<select class="credits-sort" name="sort" id="credits_sort">
						<option value="achievements_count" <?php echo ($Dashboard_Admin->sort == 'achievements_count')? 'selected' : ''; ?>><?php _e( 'Achievement count', 'framework' ) ?></option>
						<option value="username" <?php echo ($Dashboard_Admin->sort == 'username')? 'selected' : ''; ?>><?php _e( 'Name', 'framework' ) ?></option>
					</select>
					<input name="request" type="hidden" value="<?php echo $Dashboard_Admin->request; ?>">
					<input name="token" type="hidden" value="<?php echo $Dashboard_Admin->token; ?>">
				</form>
			</div>
			<div class="credits">
				<?php echo $Dashboard_Admin->credits['result']; ?>
			</div>
		<?php endif;*/ ?>	
	</div>