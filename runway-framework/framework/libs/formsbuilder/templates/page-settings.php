<script id="page-settings-template" type="text/x-jquery-tmpl">

    <form class="page-settings-form">
        <div class="page-global-settings-wrapper">

			<!-- Test of table based form -->
			<h3 class="container-title"><?php _e( 'Page Display and Access Options', 'runway' ); ?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<?php _e( 'Show page title', 'runway' ); ?>
						</th>
						<td>
							<label>
								<?php
									$checked = '';
									if ( isset( $page['settings']['showPageTitle'] ) && $page['settings']['showPageTitle'] == 'true' ) {
										$checked = 'checked="checked"';
									} ?>
								<input class="input-check" type="checkbox" name="showPageTitle" <?php echo rf_string($checked); ?> value="true">
								<?php _e('Yes', 'runway'); ?>
							</label>
							<p class="description"><?php _e( 'Removing the title can produce a nice result on pages with multiple tabs.', 'runway' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top">
							<?php _e( 'Access level', 'runway' ); ?>
							<em><?php _e( 'Permissions', 'runway' ); ?></em>
						</th>
						<td>
							<select name="access">
							<?php

								$capabilities = array(
									'Administrator' => array(
										'edit_theme_options',
										'edit_themes',
										'install_themes',
										'switch_themes',
										'manage_options',
										'install_plugins'
									),
									'Editor' => array(
										'edit_pages',
										'publish_pages',
										'delete_pages',
										'edit_private_posts',
										'manage_categories',
										'moderate_comments'
									),
									'Author' => array(
										'edit_published_posts',
										'upload_files',
										'publish_posts',
										'delete_published_posts'
									),
									'Contributor' => array(
										'edit_posts',
										'delete_posts'
									),
									'Subscriber' => array(
										'read'
									)
								);
								foreach ( $capabilities as $roll => $capability ) {
									echo '<optgroup label="'.$roll.'">';
									foreach ( $capability as $level ) {
										$name = ($level == 'edit_theme_options') ? 'edit_theme_options (default)' : $level; // set custom name for default
										$access = isset( $page['settings']['access'] ) ? selected( $page['settings']['access'], $level, false ) : '';
										echo '<option '. $access .' value="'. $level .'">'. $name .'</option>';
									}
								}
							?>
							</select>
							<p class="description"><?php _e( 'Set the access permissions needed to view and edit the page.', 'runway' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top">
							<?php _e( 'Parent menu', 'runway' ); ?>
						</th>
						<td>
							<select name="adminMenuTopItem">
								<?php
									global $menu;

									foreach ( $menu as $key => $value ) {
										if ( !empty( $value[0] ) ) {
											$selected = ( $page['settings']['adminMenuTopItem'] == $value[2] ) ? 'selected="true"' : '';
											echo '<option '. $selected .' value="'. $value[2] .'">'. $value[0] .'</option>';
										}
									}
								?>
							</select>
							<p class="description"><?php _e( 'Select the menu where this page should be added.', 'runway' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top">
							<?php _e( 'Menu Order', 'runway' ); ?>
						</th>
						<td>
							<input name="menu_order" value="<?php echo (!isset($page['settings']['menu_order']) || empty($page['settings']['menu_order']))? 0 : $page['settings']['menu_order']; ?>">
							<p class="description"><?php _e( 'Position of the current page in the Option Builder list.', 'runway' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row" valign="top">
							<?php _e( 'Description', 'runway' ); ?>
						</th>
						<td>
							<textarea name="pageDescription" ID="pageDescription"><?php echo isset( $page['settings']['pageDescription'] ) ? $page['settings']['pageDescription'] : ''; ?></textarea>
							<p class="description"><?php _e( 'An optional description or help text. This will appear at the top of the page.', 'runway' ); ?></p>
						</td>
					</tr>


					<tr>
						<th></th>
						<td>

							<select name="icon" class="icon-select">
								<?php
									$icons = array(
										'' => 'Default Generic icon',
										'menu-icon-dashboard' => 'Dashboard icon',
										'menu-icon-post' => 'Posts icon',
										'menu-icon-media' => 'Media icon',
										'menu-icon-links' => 'Links icon',
										'menu-icon-page' => 'Page icon',
										'menu-icon-comments' => 'Comments icon',
										'menu-icon-appearance' => 'Appearance icon',
										'menu-icon-plugins' => 'Plugins icon',
										'menu-icon-users' => 'Users icon',
										'menu-icon-tools' => 'Tools icon',
										'menu-icon-settings' => 'Settings icon',
										'custom-icon' => 'Custom icon',
									);

									foreach ( $icons as $icon_type => $icon_name ) { ?>
							        	<option value="<?php echo esc_attr($icon_type); ?>"
							        	    <?php selected( isset( $page['settings']['icon'] ) ? $page['settings']['icon'] : '', $icon_type ); ?>>
							        	    <?php echo rf_string($icon_name); ?>
							        	</option>
							    <?php } ?>

							</select>

							<div class="custom-icon-ob" style="margin-top: 4px; ">
								<?php if ( isset( $page['settings']['icon_file'] ) ) { ?>
									<input type="hidden" name="icon_file" value="<?php echo esc_attr($page['settings']['icon_file']); ?>" />
									<img style="width: 28px; height: 28px; float: left; margin-top: 1px; margin-right: 5px;" src="<?php echo get_stylesheet_directory_uri() . '/data/icons/' . $page['settings']['icon_file']; ?>" />
								<?php } ?>
								<div class="file-upload"><input type="file" name="icon_url" /></div>
							</div>
						</td>
					</tr>

				</tbody>
			</table>

		</div>
    </form>

</script>
