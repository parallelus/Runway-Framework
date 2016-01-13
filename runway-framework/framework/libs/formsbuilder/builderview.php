<html>
	<head>

		<script type="text/javascript">
		// output the page in JSON format
		var pageJSON = '<?php echo  $page_json; ?>';

		// output the page in JSON format
		var SAVE_ACTION = '<?php echo  $this->save_action ?>';   
		</script>
		<style type="text/css">
			#new-item-added, #new-item-duplicated{
				position:fixed;
				top:70px;
				left:30%;
				right:30%;
				background-color:#fff;
				font-size: 12pt;    
				text-align: center;
				/*color:#7F7F7F;*/
				padding:20px;
				border:2px solid green;
				-moz-border-radius: 20px;
				-webkit-border-radius:20px;
				-khtml-border-radius:20px;
				-moz-box-shadow: 0 1px 5px #333;
				-webkit-box-shadow: 0 1px 5px #333;
				z-index:101;
				display:none;
			}   
		</style>
	</head>
	<body>
		<div id="new-item-added">
			<span class="message"><?php _e('New element added', 'framework'); ?></span>
		</div>
		<div id="new-item-duplicated">
			<span class="message"><?php _e('New element duplicated', 'framework'); ?></span>
		</div>
		
		<?php if ( isset( $message ) ): ?>
		<div id="message" class="updated below-h2">
			<p>
				<?php echo  $message; ?>
			</p>
		</div><?php else: ?>
		<div id="message2" class="updated below-h2" style="display: none;"></div><?php endif; ?>
		<form class="save-page" method="post" action="<?php echo esc_url(admin_url('admin.php?page=options-builder&amp;navigation=save-page')); ?>" enctype="multipart/form-data" style="display: none;">
			<input type="hidden" name="action" value="save"> <input type="hidden" class="page" name="page" value="">
		</form>
		<div class="settings-dialog">
			<div class="settings-dialog-inside"></div><button class="button accept-changes button-primary"><?php _e( 'Update', 'framework' ); ?></button>
		</div>
		<div id="titlediv">
			<div id="titlewrap" <?php if(!$this->resolutions['title']){ echo 'style="display:none;"'; } ?>>
				<label class="hide-if-no-js" style="visibility: hidden;" id="title-prompt-text" for="title"><?php _e( 'Enter title here', 'framework' ); ?></label> <input type="text" name="post_title" size="30" tabindex="1" value="<?php echo esc_attr($page['settings']['title']) ?>" id="title" autocomplete="off">
			</div>
			<div class="inside" style="min-height: auto !important;">
				<div id="edit-slug-box" <?php if(!$this->resolutions['alias']){ echo 'style="display:none;"'; } ?>> <input type="hidden" name="primary-page-slug" value="<?php echo esc_attr($page['settings']['alias']); ?>">
					<strong><?php _e( 'Alias:', 'framework' ); ?></strong> <span id="slug-static"><span id="editable-post-name" class="dynamic-page-title edit-slug" title="<?php _e( 'Click to edit the page alias.', 'framework' ); ?>"><?php echo  $page['settings']['alias'] ?></span> <span id="edit-slug-buttons"><a href="#post_name" class="edit-slug button hide-if-no-js"><?php _e( 'Edit', 'framework' ); ?></a></span> <button class="button make-from-title"><?php _e( 'Make slug from title', 'framework' ); ?></button></span> <span id="slug-editor" style="display:none;"><input class="slug-editor-input" id="slug-editor-input" value=""><button class="button slug-editor-save"><span id="slug-editor2"><?php _e( 'Ok', 'framework' ); ?></span></button> <button class="button slug-editor-cancel"><?php _e( 'Cancel', 'framework' ); ?></button> <button class="button make-from-title"><?php _e( 'Make slug from title', 'framework' ); ?></button> <button class="button get-primary-slug"><?php _e('Reset', 'framework'); ?></button></span> <span id="editable-post-name-full">???</span>
				<span id="alias-error-text"></span>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<div class="page-builder-wrapper">
			<div id="menu-management-liquid">
				<div id="">
					<div class="nav-tabs-nav">
						<div class="nav-tab-wrapper nav-tabs-wrapper">
							<div class="nav-tabs">
								<a href="javascript:void(0);" class="nav-tab nav-tab-active" data-switchto="elements-list"><?php _e( 'Options', 'framework' ); ?></a> <a href="javascript:void(0);" class="nav-tab" data-switchto="page-global-settings" <?php if(!$this->resolutions['settings']){ echo 'style="display:none;"'; } ?>><?php _e( 'Settings', 'framework' ); ?></a> <?php if(isset($help_tabs_admin)) { ?> <a href="javascript:void(0);" class="nav-tab" data-switchto="page-help-tabs"><?php _e( 'Help tabs', 'framework' ); ?></a> <?php } ?>
							</div>
						</div>
					</div>
					<div class="menu-edit">
						<div id="nav-menu-header">
							<div id="submitpost" class="submitbox">
								<div class="major-publishing-actions">
									<div class="elements-list tabIn">
										<div id="add-tab" class="new-element ntab button" data-type='tab' <?php if(!$this->resolutions['options-tabs']){ echo 'style="display:none;"'; } ?>>
											<img src="<?php echo FRAMEWORK_URL ?>framework/images/add-icon.png"> <?php _e( 'Tab', 'framework' ); ?>
										</div>
										<div id="add-container" class="new-element ncontainer button" data-type='container' <?php if(!$this->resolutions['options-containers']){ echo 'style="display:none;"'; } ?>>
											<img src="<?php echo FRAMEWORK_URL ?>framework/images/add-icon.png"> <?php _e( 'Container', 'framework' ); ?>
										</div>
										<div id="add-field" class="new-element nfield button" data-type='field' <?php if(!$this->resolutions['options-fields']){ echo 'style="display:none;"'; } ?>>
											<img src="<?php echo FRAMEWORK_URL ?>framework/images/add-icon.png"> <?php _e( 'Field', 'framework' ); ?>
										</div>
										<p class="info">
											<?php _e( 'Drag or click to add elements to the page below.', 'framework' ); ?>
										</p>
										<p class="info">
											<a class="expand-all" href="#">Expand all</a> / <a class="collapse-all" href="#"><?php _e('Collapse all', 'framework'); ?></a>
										</p>
									</div>
									<div class="major-publishing-actions">
										<div class="publishing-action">
											<input type="submit" class="button-primary save-button" value="<?php _e( 'Save Settings', 'framework' ); ?>">
										</div>
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
						<div id="post-body">
							<div id="post-body-content">
								<div class="elements-list page-layer accept-tab tabIn">
									<div class="clear"></div>
								</div>
								<div class="page-global-settings tabIn">
									<?php _e( 'Page settings', 'framework' ); ?>
								</div><?php if(isset($help_tabs_admin)) { ?>
								<div class="page-help-tabs tabIn" style="display: none">
									<?php echo  $help_tabs_admin->render_options_builder_page("admin.php?page={$page['settings']['alias']}"); ?>
								</div><?php } ?>
							</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
						<div id="nav-menu-footer">
							<div class="major-publishing-actions">
								<div class="publishing-action">
									<input type="submit" class="button-primary save-button" value="<?php _e( 'Save Settings', 'framework' ); ?>">
								</div>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>