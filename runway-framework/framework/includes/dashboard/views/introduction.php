<div class="intro-panel">

	<div class="intro-panel-content">

		<div class="intro-panel-column-container">
			<div class="intro-panel-column">
				<h4><span class="icon16 icon-appearance"></span> <?php echo __( 'Build Themes', 'runway' ); ?></h4>
				<p>
					<?php echo __( 'Making themes is what Runway is about. Create new child themes, custom designs or download a Runway child theme.', 'runway' ); ?>
				</p>
				<ul class="bullet-list">
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=themes&amp;navigation=new-theme' ); ?>">
							<?php echo __( 'Create a new Runway child theme', 'runway' ); ?>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=themes' ); ?>">
							<?php echo __( 'Activate an existing Runway theme', 'runway' ); ?>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=directory' ); ?>">
							<?php echo __( 'Search for more themes', 'runway' ); ?>
						</a>
					</li>
				</ul>
			</div>

			<div class="intro-panel-column">
				<h4><span class="icon16 icon-settings"></span> <?php echo __( 'Extend and Enhance', 'runway' ); ?></h4>
				<p>
					<?php echo __( 'Extensions quickly add features to your theme, like a plugin integrated with the theme for easy distribution.', 'runway' ); ?>
				</p>
				<ul class="bullet-list">
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=extensions' ); ?>">
							<?php echo __( 'Activate Extensions', 'runway' ); ?>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=directory' ); ?>">
							<?php echo __( 'Browse the directory', 'runway' ); ?>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=directory&amp;tab=search' ); ?>">
							<?php echo __( 'Search all Extensions', 'runway' ); ?>
						</a>
					</li>
				</ul>
			</div>

			<div class="intro-panel-column intro-panel-last">
				<h4><span class="icon16 icon-page"></span> <?php echo __( 'Customize the Admin', 'runway' ); ?></h4>
				<p>
					<?php echo __( 'Create options pages, admin menus and much more. The Options Builder interface allows you to create theme options.', 'runway' ); ?>
				</p>
				<ul class="bullet-list">
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=options-builder' ); ?>">
							<?php echo __( 'Add theme options pages', 'runway' ); ?>
						</a>
					</li>

					<?php
					global $extm;

					$admin_menu_activated = $extm->is_activated('admin-menu-editor/load.php');
					if ( $admin_menu_activated === true ) {
					?>
					
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=admin-menu' ); ?>">
							<?php echo __( 'Manage admin menus', 'runway' ); ?>
						</a>
					</li>

					<?php } ?>
				</ul>
			</div>
		</div>

	</div>

</div>
