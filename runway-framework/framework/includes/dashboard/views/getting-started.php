
	<?php include_once 'introduction.php'; ?>

	<div>
		<h3>Creating a Theme</h3>

		<div class="feature-section images-stagger-right">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/child-theme-screen.jpg" class="image-50">
			<h4>Make a New Child Theme</h4>
			<p>Runway was built for creating WordPress themes. The first step is a new child theme. Go to <a href="admin.php?page=themes">Runway Themes</a> and click <a href="admin.php?page=themes&navigation=new-theme">New Theme</a>. Fill in the details and Runway will create the new child theme.</p>
			<!-- <p class="more"><a href="#runway_themes">About Runway Child Themes &raquo;</a></p> -->

			<h4>Design Your Theme</h4>
			<p>You can upload files to your child theme by going to the default themes directory <code>wp-content/themes</code> in your WordPress install.</p>

			<h4>Create Options Pages</h4>
			<p>Setup theme options and admin pages using the <a href="admin.php?page=options-builder">Options Builder</a>.</p>
			<!-- <p class="more"><a href="#options_builder">Options Builder Details &raquo;</a></p> -->

			<h4>Download and Distribute</h4>
			<p>Distribute your thems as child or standalone installs. Child themes require Runway, standalone themes don't and install directly on WordPress.</p>
			<!-- <p class="more"><a href="#theme_packages">Theme Package Information &raquo;</a></p> -->
		</div>
	</div>

	<div>
		<h3>Options Builder</h3>

		<div class="feature-section">
			<h4>Admin Pages</h4>
			<p>Create theme options directly in the admin using the <a href="admin.php?page=options-builder">Options Builder</a>. This generates admin pages directly in your theme's admin menu. The new admin pages can be populated with inputs selected by you from the Options Builder interface.</p>
			<div class="three-col-images">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-1.jpg" class="image-30 first-feature">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-2.jpg" class="image-30">
				<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/options-builder-screen-3.jpg" class="image-30 last-feature">
			</div>
		</div>
	</div>

	<div>
		<h3>Theme Options</h3>

		<div class="feature-section images-stagger-right">
			<img src="<?php echo get_template_directory_uri() ?>/framework/images/welcome/theme-options-screen.jpg" class="image-50">
			<h4>Options Pages</h4>
			<p>Add theme options and custom inputs to admin pages using the <a href="admin.php?page=options-builder">Options Builder</a>. Input types include:</p>
			<ul class="bullet-list list-w-indent smaller-font">
				<li>Text input</li>
				<li>Textarea</li>
				<li>Rich text editor (WYSIWYG)</li>
				<li>Checkbox and multi-checkbox</li>
				<li>Radio buttons</li>
				<li>Select drop down and multi-select</li>
				<li>Upload</li>
				<li>Date piker</li>
				<li>Color picker</li>
			</ul>
			<h4>Custom Inputs</h4>
			<p>You can make custom inputs (data types) for your themes. Add your custom inputs to the "data-types" folder and they will be instantly available for use in the Options Builder.</p>
			<h4>Organized Options</h4>
			<p>Group the content of admin pages with containers or split the fields across tabs.</p>
		</div>
	</div>