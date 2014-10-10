<?php 
if ( __FILE__ == $_SERVER['SCRIPT_FILENAME'] ) { 
	die(); 
} ?>
<html>
<head>
	<title><?php echo __('Welcome to Runway!', 'framework'); ?></title>
	<style type="text/css" media="screen">
		body {
			background: #fcfcfc;
			padding: 0;
			margin: 0; }
		.content {
			margin: 150px auto;
			text-align: center;
		}
		h3 { 
			font: 300 32px "Open Sans", Helvetica, Arial, sans-serif;
			color: #333; }
	</style>
</head>
<body>

<?php global $theme_name;
	  $theme_current = wp_get_theme();
	  if( ! IS_CHILD): ?>
		<div class="content">
			<h3><?php printf( __( 'Please activate a Runway %1$schild theme%2$s.', 'framework' ), '<a href="'.admin_url('admin.php?page=themes').'">', '</a>' ); ?></h3>
		</div>
<?php elseif($theme_name == $theme_current->Name): ?>
		<div class="content">
			<h3><?php printf( __( "Time to start adding theme files and options to your child theme!<br><br>You're seeing this message because you have not yet added any default theme<br> files to your new child theme, such as index.php, single.php, archive.php, etc.", 'framework' ) ); ?></h3>
		</div>
<?php endif ?>
</body></html>