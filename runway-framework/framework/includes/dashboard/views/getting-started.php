
	<?php include_once 'introduction.php'; ?>

	<div>
		<h3><?php echo __('Creating a Theme', 'framework'); ?></h3>

		<div class="feature-section images-stagger-right">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/child-theme-screen.jpg" class="image-50">
			<h4><?php echo __('Make a New Child Theme', 'framework'); ?></h4>
			<p><?php echo __('Runway was built for creating WordPress themes. The first step is a new child theme. Go to', 'framework'); ?> <a href="<?php echo network_admin_url('admin.php?page=themes'); ?>"><?php echo __('Runway Themes', 'framework'); ?></a> <?php echo __('and click', 'framework'); ?> <a href="<?php echo network_admin_url('admin.php?page=themes&navigation=new-theme'); ?>"><?php echo __('New Theme', 'framework'); ?></a>. <?php echo __('Fill in the details and Runway will create the new child theme', 'framework'); ?>.</p>
			<!-- <p class="more"><a href="#runway_themes">About Runway Child Themes &raquo;</a></p> -->

			<h4><?php echo __('Design Your Theme', 'framework'); ?></h4>
			<p><?php echo __('You can upload files to your child theme by going to the default themes directory', 'framework'); ?> <code>wp-content/themes</code> <?php echo __('in your WordPress install', 'framework'); ?>.</p>

			<h4><?php echo __('Create Options Pages', 'framework'); ?></h4>
			<p><?php echo __('Setup theme options and admin pages using the', 'framework'); ?> <a href="<?php echo network_admin_url('admin.php?page=options-builder'); ?>"><?php echo __('Options Builder', 'framework'); ?></a>.</p>
			<!-- <p class="more"><a href="#options_builder">Options Builder Details &raquo;</a></p> -->

			<h4><?php echo __('Download and Distribute', 'framework'); ?></h4>
			<p><?php echo __('Distribute your themes as child or standalone installs. Child themes require Runway, standalone themes don\'t and install directly on WordPress', 'framework'); ?>.</p>
			<!-- <p class="more"><a href="#theme_packages">Theme Package Information &raquo;</a></p> -->
		</div>
	</div>

	<div>
		<h3><?php echo __('Options Builder', 'framework'); ?></h3>

		<div class="feature-section">
			<h4><?php echo __('Admin Pages', 'framework'); ?></h4>
			<p><?php echo __('Create theme options directly in the admin using the', 'framework'); ?> <a href="<?php echo network_admin_url('admin.php?page=options-builder'); ?>"><?php echo __('Options Builder', 'framework'); ?></a>. <?php echo __('This generates admin pages directly in your theme\'s admin menu. The new admin pages can be populated with inputs selected by you from the Options Builder interface', 'framework'); ?>.</p>
			<div class="three-col-images">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-1.jpg" class="image-30 first-feature">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-2.jpg" class="image-30">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-3.jpg" class="image-30 last-feature">
			</div>
		</div>
	</div>

	<div>
		<h3><?php echo __('Theme Options', 'framework'); ?></h3>

		<div class="feature-section images-stagger-right">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/theme-options-screen.jpg" class="image-50">
			<h4><?php echo __('Options Pages', 'framework'); ?></h4>
			<p><?php echo __('Add theme options and custom inputs to admin pages using the', 'framework'); ?> <a href="<?php echo network_admin_url('admin.php?page=options-builder'); ?>"><?php echo __('Options Builder', 'framework'); ?></a>. <?php echo __('Input types include', 'framework'); ?>:</p>
			<ul class="bullet-list list-w-indent smaller-font">
				<li><?php echo __('Text input', 'framework'); ?></li>
				<li><?php echo __('Textarea', 'framework'); ?></li>
				<li><?php echo __('Rich text editor (WYSIWYG)', 'framework'); ?></li>
				<li><?php echo __('Checkbox and multi-checkbox', 'framework'); ?></li>
				<li><?php echo __('Radio buttons', 'framework'); ?></li>
				<li><?php echo __('Select drop down and multi-select', 'framework'); ?></li>
				<li><?php echo __('Upload', 'framework'); ?></li>
				<li><?php echo __('Date piker', 'framework'); ?></li>
				<li><?php echo __('Color picker', 'framework'); ?></li>
			</ul>
			<h4><?php echo __('Custom Inputs', 'framework'); ?></h4>
			<p><?php echo __('You can make custom inputs (data types) for your themes. Add your custom inputs to the "data-types" folder and they will be instantly available for use in the Options Builder', 'framework'); ?>.</p>
			<h4><?php echo __('Organized Options', 'framework'); ?></h4>
			<p><?php echo __('Group the content of admin pages with containers or split the fields across tabs', 'framework'); ?>.</p>
		</div>
	</div>