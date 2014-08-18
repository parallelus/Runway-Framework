	<div class="changelog">
		<h3><?php echo __('Credits', 'framework'); ?></h3>

		<?php
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
						<button class="first-page disabled pagination" name="first_page">&laquo;</button>
						<button class="prev-page disabled pagination" name="prev_page">‹</button>
						<span class="paging-input">
							<input type="text" class="current-page" value="<?php echo $Dashboard_Admin->currentPage; ?>"/>
							<?php echo __('of', 'framework'); ?> <span class="total-pages"><?php echo ceil($Dashboard_Admin->credits['totalResults']/$Dashboard_Admin->perPage); ?></span>
						</span>
						<button class="next-page disabled pagination" name="next_page">›</button>
						<button class="last-page disabled pagination" name="last_page">&raquo;</button>
					</div>
				</div>
				
				<div class="credits">
					<?php foreach($Dashboard_Admin->credits['result'] as $key => $credit) { ?>
					<div class="credits-row">
						<div class="credits-column">
							<div class="credits-user">
								<span class="avatar">
									<img src="<?php echo $credit['avatar_url'];?>" width="60" height="60" alt="<?php echo $credit['displayname'];?>"/>
								</span>
								<span class="user-name">
									<span class="name"><?php echo $credit['displayname'];?></span>
									<span class="user"><?php echo $credit['username'];?></span>
								</span>
							</div>
						</div>
						<div class="credits-column achievements">
							<?php foreach($credit['achievements'] as $achievement) { ?>
							<span class="badge">
								<img src="<?php echo $achievement['achievement_image']; ?>" width="40" height="40" alt="<?php echo $achievement['achievement_name']; ?>"/>
							</span>
							<?php } ?>
						</div>
					</div>
					<?php } ?>
				</div>
			</form>
		<?php } ?>	
	</div>