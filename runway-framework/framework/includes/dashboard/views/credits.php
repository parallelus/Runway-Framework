	<div class="changelog">
		<h3><?php echo __('Credits', 'framework'); ?></h3>

		<?php
		wp_enqueue_script('sort_credits-js', get_template_directory() . '/framework/includes/dashboard/js/sort_credits.js');
		if($Dashboard_Admin->credits['success']): ?>
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
		<?php endif; ?>	
	</div>