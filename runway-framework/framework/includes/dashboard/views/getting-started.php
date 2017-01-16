<?php include_once __DIR__ . '/introduction.php'; ?>

<div class="changelog">

	<h3><?php _e( 'Creating a Theme', 'runway' ); ?></h3>

	<div class="feature-section col two-col">
		<div class="col-1">
			<h4><?php _e( '1. Make a New Child Theme', 'runway' ); ?></h4>
			<p>
				<?php
				echo sprintf(
					__( 'Runway was built for creating WordPress themes. The first step is a new child theme. Go to %s and click %s. Fill in the details and Runway will create the new child theme.', 'runway' ),
					'<a href="' . admin_url( 'admin.php?page=themes' ) . '">' . __( 'Runway Themes', 'runway' ) . '</a>',
					'<a href="' . admin_url( 'admin.php?page=themes&navigation=new-theme' ) . '">' . __( 'New Theme', 'runway' ) . '</a>' );
				?>
			</p>
			<h4><?php _e( '2. Design Your Theme', 'runway' ); ?></h4>
			<p>
				<?php _e( 'You can upload files to your child theme by going to the default themes directory', 'runway' ); ?> <code>wp-content/themes</code>
				<?php _e( 'in your WordPress install', 'runway' ); ?>.
			</p>
			<h4><?php _e( '3. Create Options Pages', 'runway' ); ?></h4>
			<p><?php
				echo sprintf(
					__( 'Setup theme options and admin pages using the %s.', 'runway' ),
					'<a href="' . admin_url( 'admin.php?page=options-builder' ) . '">' . __( 'Options Builder', 'runway' ) . '</a>' );
				?>
			</p>
			<h4><?php _e( '4. Download and Distribute', 'runway' ); ?></h4>
			<p>
				<?php _e( 'Distribute as child or standalone installs. Child themes require Runway, standalone themes don\'t and install directly on WordPress', 'runway' ); ?>.
			</p>
		</div>
		<div class="col-2 last-feature">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/child-theme-screen.jpg" class="image-50">
		</div>
	</div>


	<hr>


	<h3><?php _e( 'Options Builder', 'runway' ); ?></h3>

	<h4><?php _e( 'Admin Pages', 'runway' ); ?></h4>
	<p>
		<?php
		echo sprintf(
			__( 'Create theme options directly in the admin using the %s. This generates admin pages directly in your theme\'s admin menu. The new admin pages can be populated with inputs selected by you from the Options Builder interface.', 'runway' ),
			'<a href="' . admin_url( 'admin.php?page=options-builder' ) . '">' . __( 'Options Builder', 'runway' ) . '</a>' );
		?>
	</p>

	<div class="feature-section col three-col">
		<div class="col-1">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-1.jpg"
			     class="image-30 first-feature">
		</div>
		<div class="col-2">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-2.jpg"
			     class="image-30">
		</div>
		<div class="col-3 last-feature">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-3.jpg"
			     class="image-30 last-feature">
		</div>
	</div>


	<hr>


	<h3><?php _e( 'Theme Options', 'runway' ); ?></h3>

	<div class="feature-section col two-col">
		<div class="col-1">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/theme-options-screen.jpg"
			     class="image-50">
		</div>
		<div class="col-2 last-feature">
			<h4><?php _e( 'Options Pages', 'runway' ); ?></h4>
			<p>
				<?php
				echo sprintf(
					__( 'Add theme options and custom inputs to admin pages using the %s. Input types include: Text input, Textarea, Rich text editor (WYSIWYG), Checkbox and multi-checkbox, Radio buttons, Select drop down and multi-select, Upload, Date picker, Color picker...', 'runway' ),
					'<a href="' . admin_url( 'admin.php?page=options-builder' ) . '">' . __( 'Options Builder', 'runway' ) . '</a>' );
				?>
			</p>
			<h4><?php _e( 'Custom Inputs', 'runway' ); ?></h4>
			<p>
				<?php _e( 'You can make custom inputs (data types) for your themes. Add your custom inputs to the "data-types" folder and they will be instantly available for use in the Options Builder', 'runway' ); ?>.
			</p>
			<h4><?php _e( 'Organized Options', 'runway' ); ?></h4>
			<p><?php _e( 'Group the content of admin pages with containers or split the fields across tabs', 'runway' ); ?>.</p>
		</div>
	</div>


	<div class="feature-section col two-col">
		<div class="col-1">
		</div>
		<div class="col-2 last-feature">
		</div>
	</div>

</div>
