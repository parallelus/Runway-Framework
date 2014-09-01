
	<?php include_once 'introduction.php'; ?>

	<div class="changelog">

		<h3><?php _e('Creating a Theme', 'framework'); ?></h3>

		<div class="feature-section col two-col">
			<div class="col-1">
				<h4><?php _e('1. Make a New Child Theme', 'framework'); ?></h4>
				<p><?php _e('Runway was built for creating WordPress themes. The first step is a new child theme. Go to', 'framework'); ?> <a href="<?php echo admin_url('admin.php?page=themes'); ?>"><?php _e('Runway Themes', 'framework'); ?></a> <?php _e('and click', 'framework'); ?> <a href="<?php echo admin_url('admin.php?page=themes&navigation=new-theme'); ?>"><?php _e('New Theme', 'framework'); ?></a>. <?php _e('Fill in the details and Runway will create the new child theme', 'framework'); ?>.</p>
				<h4><?php _e('2. Design Your Theme', 'framework'); ?></h4>
				<p><?php _e('You can upload files to your child theme by going to the default themes directory', 'framework'); ?> <code>wp-content/themes</code> <?php _e('in your WordPress install', 'framework'); ?>.</p>
				<h4><?php _e('3. Create Options Pages', 'framework'); ?></h4>
				<p><?php _e('Setup theme options and admin pages using the', 'framework'); ?> <a href="<?php echo admin_url('admin.php?page=options-builder'); ?>"><?php _e('Options Builder', 'framework'); ?></a>.</p>
				<h4><?php _e('4. Download and Distribute', 'framework'); ?></h4>
				<p><?php _e('Distribute as child or standalone installs. Child themes require Runway, standalone themes don\'t and install directly on WordPress', 'framework'); ?>.</p>
			</div>
			<div class="col-2 last-feature">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/child-theme-screen.jpg" class="image-50">
			</div>
		</div>


		<hr>


		<h3><?php _e('Options Builder', 'framework'); ?></h3>

		<h4><?php _e('Admin Pages', 'framework'); ?></h4>
		<p><?php _e('Create theme options directly in the admin using the', 'framework'); ?> <a href="<?php echo admin_url('admin.php?page=options-builder'); ?>"><?php _e('Options Builder', 'framework'); ?></a>. <?php _e('This generates admin pages directly in your theme\'s admin menu. The new admin pages can be populated with inputs selected by you from the Options Builder interface', 'framework'); ?>.</p>

		<div class="feature-section col three-col">
			<div class="col-1">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-1.jpg" class="image-30 first-feature">
			</div>
			<div class="col-2">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-2.jpg" class="image-30">
			</div>
			<div class="col-3 last-feature">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-3.jpg" class="image-30 last-feature">
			</div>
		</div>


		<hr>


		<h3><?php _e('Theme Options', 'framework'); ?></h3>

		<div class="feature-section col two-col">
			<div class="col-1">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/theme-options-screen.jpg" class="image-50">
			</div>
			<div class="col-2 last-feature">
				<h4><?php _e('Options Pages', 'framework'); ?></h4>
				<p><?php _e('Add theme options and custom inputs to admin pages using the', 'framework'); ?> <a href="<?php echo admin_url('admin.php?page=options-builder'); ?>"><?php _e('Options Builder', 'framework'); ?></a>. <?php echo __('Input types include:', 'framework') .' '. __('Text input', 'framework') .', '. __('Textarea', 'framework') .', '. __('Rich text editor (WYSIWYG)', 'framework') .', '. __('Checkbox and multi-checkbox', 'framework') .', '. __('Radio buttons', 'framework') .', '. __('Select drop down and multi-select', 'framework') .', '. __('Upload', 'framework') .', '. __('Date picker', 'framework') .', '. __('Color picker', 'framework') .'...'; ?></p>
				<h4><?php _e('Custom Inputs', 'framework'); ?></h4>
				<p><?php _e('You can make custom inputs (data types) for your themes. Add your custom inputs to the "data-types" folder and they will be instantly available for use in the Options Builder', 'framework'); ?>.</p>
				<h4><?php _e('Organized Options', 'framework'); ?></h4>
				<p><?php _e('Group the content of admin pages with containers or split the fields across tabs', 'framework'); ?>.</p>
			</div>
		</div>


		<div class="feature-section col two-col">
			<div class="col-1">
			</div>
			<div class="col-2 last-feature">
			</div>
		</div>

	</div>