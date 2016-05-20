<?php

// Breadcrumbs
//................................................................
$this->navigation_bar( array( __( 'Upload New', 'runway' ) ) );

?>

<?php if ( $info_message != '' ): ?>
	<div id="message" class="updated">
		<p>
			<?php echo  $info_message; ?>
		</p>
	</div>
	<?php endif; ?>
<h4><?php _e( 'Install a extension in .zip format', 'runway' ) ?></h4>
<p class="install-help"><?php _e( 'If you have a extension in a .zip format, you may install it by uploading it here.', 'runway' ) ?></p>

<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=extensions&navigation=add-extension' ) ?>">
	<?php wp_nonce_field( 'extension-upload-action', 'extension-upload-field' ) ?>
	<label class="screen-reader-text" for="extzip"><?php _e( 'Plugin zip file', 'runway' ); ?></label>
	<input type="file" id="extzip" name="extzip" />
	<input type="submit" class="button" value="<?php esc_attr_e( 'Install Now', 'runway' ) ?>" name="ext-submit" />
</form>	
