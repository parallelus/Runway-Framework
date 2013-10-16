<?php

// Breadcrumbs
//................................................................
$Extm_Admin->navigation_bar( array( __( 'Upload New', 'framework' ) ) );

?>

<?php if ( $info_message != '' ): ?>
	<div id="message" class="updated">
		<p>
			<?php echo $info_message; ?>
		</p>
	</div>
	<?php endif; ?>
<h4><?php _e( 'Install a extension in .zip format', 'framework' ) ?></h4>
<p class="install-help"><?php _e( 'If you have a extension in a .zip format, you may install it by uploading it here.', 'framework' ) ?></p>
<form method="post" enctype="multipart/form-data" action="<?php echo self_admin_url( 'admin.php?page=extensions&navigation=add-extension' ) ?>">
	<?php wp_nonce_field( 'extension-upload-action', 'extension-upload-field' ) ?>
	<label class="screen-reader-text" for="extzip"><?php _e( 'Plugin zip file', 'framework' ); ?></label>
	<input type="file" id="extzip" name="extzip" />
	<input type="submit" class="button" value="<?php esc_attr_e( 'Install Now', 'framework' ) ?>" name="ext-submit" />
</form>	
