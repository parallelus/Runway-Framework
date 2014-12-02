<?php
global $developer_tools, $Themes_Manager;

$popup_message = '<h2>'. __( 'Activate new theme?', 'framework' ) .'</h2>';

$themesActivate = admin_url('themes.php');
$httpReferer = isset( $_SERVER['HTTP_REFERER'] )? str_replace( '?activated=true', '', $_SERVER['HTTP_REFERER'] ) : '';

if ( isset( $_GET['activate-default'] ) && $_GET['activate-default'] == 'activate' && ($httpReferer == $themesActivate || strstr($httpReferer, 'action=upload-theme') ) ) {
	$popup_message = '<a href="#" class="screenshot"><img src="../wp-content/themes/runway-framework/framework/images/screenshot-transparent.png" width="265" height="199"></a>';
	$popup_message .= '<h2>'. __( 'Welcome to Runway!', 'framework' ). '</h2><p>'. __( 'We recommend using Runway with an active child theme. Would you like to automatically activate the default child theme?', 'framework' ) .'</p>';
	if ( !file_exists( $developer_tools->themes_path.'/liftoff' ) ) {
		unzip_file($developer_tools->default_theme_package_path, $developer_tools->themes_path);
	}
	$options = array();
	$options['Folder'] = 'liftoff';

	if(is_multisite() && is_admin() && isset($options)){		
		$url = 'themes.php?';
		$s = '';
		$ms_enable_theme_link = admin_url('network/').esc_url(wp_nonce_url($url . 'action=enable&amp;theme=' . $options['Folder'] . '&amp;paged=' . 1 . '&amp;s=' . $s, 'enable-theme_' . $options['Folder'] ));		
		?>
			<script type="text/javascript">
				(function ($) {
					$(function () {
						var $dlg = $(".enable-theme-popup").dialog({
							position: "center",
							modal: true,
							resizable: false,
							dialogClass: 'activateThemePopup'
						});
						$(".ui-dialog-titlebar").hide();

						$(".enable-theme-popup a").on("click", function () {
							$dlg.dialog("close");
						});
					});
				})(jQuery);
			</script>

			<div class="enable-theme-popup themeActionsPupup">

				<?php echo __('Enable default theme for a network', 'framework'); ?>?

				<p class="bottom-panel">
					<a href="<?php echo $ms_enable_theme_link; ?>" class="enable-theme button"><?php _e( 'Enable', 'framework' ); ?></a>
					&nbsp;<a href="javascript: void(0);" class="button"><?php _e( 'Cancel', 'framework' ); ?></a>
				</p>

			</div>		
		<?php
	}
}

if ( isset( $options ) ) {
	$theme = wp_get_theme($options['Folder']);
	if(isset($_REQUEST['activate-default'])){
		$allowed = $theme->is_allowed( 'network' );	
		$activate = ($this->navigation == 'new-theme' || $_REQUEST['activate-default'] == 'activate') ? true : false;
		if($allowed && $activate){
			$activate_link = wp_nonce_url( 'themes.php?action=activate&template='.$options['Folder'].'&stylesheet='.$options['Folder'], 'switch-theme_' . $options['Folder'] );
			?>

			<script type="text/javascript">
				(function ($) {
					$(function () {
						var $dlg = $(".activate-theme-popup").dialog({
			                open: function(event, ui) {
			                    jQuery('#adminmenuwrap').css({'z-index':0});
			                },
			                close: function(event, ui) {
			                    jQuery('#adminmenuwrap').css({'z-index':'auto'});
			                },							
							position: "center",
							modal: true,
							resizable: false,
							dialogClass: 'activateThemePopup'
						});
						$(".ui-dialog-titlebar").hide();

						$(".activate-theme-popup a").on("click", function () {
							$dlg.dialog("close");
						});
					});
				})(jQuery);
			</script>

			<div class="activate-theme-popup themeActionsPupup">
				<?php echo $popup_message; ?>
				<p class="bottom-panel">
					<a href="<?php echo $activate_link; ?>" class="activate-theme button"><?php _e( 'Activate', 'framework' ); ?></a>
					&nbsp;<a href="javascript: void(0);" class="button"><?php _e( 'Cancel', 'framework' ); ?></a>
				</p>
			</div> <?php
		}
	}
} 

?>

	<div class="duplicate-theme-popup themeActionsPupup">

		<p><?php _e( 'Enter a new folder name:', 'framework' ); ?></p>
		<p>
			<input type="text" id="duplicate" value="" /> <a href="#" class="submit-theme-new-folder button-primary"><?php _e( 'Submit', 'framework' ); ?></a> <a href="#" class="button" title="<?php _e( 'Cancel', 'framework' ); ?>"><?php _e( 'X', 'framework' ); ?></a>
		</p>

	</div>

	<div class="loader themeActionsPupup" style="display: none;">
		<p><?php _e( 'Loading ...', 'framework' ); ?></p>
		<img src="<?php
if ( file_exists( get_stylesheet_directory() . '/framework/images/ajax-loader.gif' ) ) {
	echo home_url() . '/wp-content/themes/' . str_replace( $developer_tools->themes_path . '/', '', get_stylesheet_directory() ) . '/framework/images/ajax-loader.gif';
} else {
	echo home_url() . '/wp-content/themes/runway-framework/framework/images/ajax-loader.gif';
}
?>" />


	</div>

	<script type="text/javascript">
		function in_array(what, where) {
			for(var i=0; i<where.length; i++)
				if(what == where[i])
					return true;
			return false;
		}

		function popup_loader() {
			var $dlg = jQuery(".loader").dialog({
                open: function(event, ui) {
                    jQuery('html,body').css('overflow', 'hidden');
                    jQuery('#adminmenuwrap').css({'z-index':0});
                },
                close: function(event, ui) {
                    jQuery('html,body').css('overflow', 'auto');
                    jQuery('#adminmenuwrap').css({'z-index':'auto'});
                },				
				position: "center",
				modal: true,
				resizable: false,
				dialogClass: 'loaderPopup'
			});
			jQuery(".ui-dialog-titlebar").hide();
		}

		(function ($) {
			$(function () {

				$(".duplicate-theme").on("click", function () {

					var name = $(this).data("theme-folder");

					var $dlg = $(".duplicate-theme-popup").dialog({
	                    open: function(event, ui) {
                            $('html,body').css('overflow', 'hidden');
	                        $('#adminmenuwrap').css({'z-index':0});
	                    },
	                    close: function(event, ui) {
                            $('html,body').css('overflow', 'auto');
	                        $('#adminmenuwrap').css({'z-index':'auto'});
	                    },							
						position: "center",
						modal: true,
						resizable: false,
						dialogClass: 'duplicateThemePopup'
					});
					$(".ui-dialog-titlebar").hide();

					var currunt_theme_folder = $(this).data("theme-folder");
					
					var regex = new RegExp(""+currunt_theme_folder+"-copy-\d$", "g");

					var last_index = 0;
					
					if(currunt_theme_folder.match(regex)) {
						var tmp = currunt_theme_folder.split("-copy-");
						currunt_theme_folder = tmp[0];
					}

					var themes_list = [];

					$(".duplicate-theme").each(function () {

						themes_list.push($(this).data("theme-folder"));

						if($(this).data("theme-folder").match(regex)) {
							var tmp = $(this).data("theme-folder").split("-copy-");
							if(tmp.length >= 2) {
								tmp = tmp[tmp.length-1];
								if(last_index < tmp) {
									last_index = tmp;
								}
							}
						}

					});

					$(".duplicate-theme-popup").find("input").val(currunt_theme_folder + "-copy-" + (++last_index));

					$(".submit-theme-new-folder").on("click", function () {

						var new_name = $(".duplicate-theme-popup").find("input").val();
						if(new_name.length) {
							if(in_array(new_name, themes_list)) {
								alert("<?php _e( 'This theme folder name is already in use. Please choose a different name.', 'framework' ); ?>");
							} else {
								var url = "<?php echo admin_url('admin.php?page=themes&navigation=duplicate-theme&name='); ?>" + name + "&new_name=" + new_name;
								document.location = url;
							}
						}

					});

					$(".duplicate-theme-popup a").on("click", function () {
						$dlg.dialog("close");
					});
				});

			});
		})(jQuery);

	</script>

<div class="wrap">

	<script type="text/javascript">

		(function ($) {
			$(function () {
				$(".activate-theme").on("click", function () {

					popup_loader();
					$.ajax({url: $(this).attr("href"), success: function (responce) {
						document.location = "<?php echo admin_url('admin.php?page=themes'); ?>";
					}});
					return false;
				});
			});
		})(jQuery);
	</script>

	<p>
		<?php echo __( 'A child theme can be run on any WordPress install with the Runway framework active. You can use Runway to setup custom theme options, menus and many other features of a child theme. Completed themes can be downloaded as a child or standalone version. A standalone theme may be installed on any WordPress install regardless of having Runway active', 'framework' ); ?>.
	</p>
	<br>

	<?php
	
		$themes_list = $developer_tools->search_themes();

		$tmp_themes = array();
		foreach($themes_list as $key => $theme) {
			$tmp_themes[] = wp_get_theme($theme['Folder']);
		}
		$js_themes = wp_prepare_themes_for_js( $tmp_themes );
		
		wp_localize_script( 'themes-manager-themes', '_wpThemeSettings', array(
			'themes'   => $js_themes,
			'settings' => array(
				'canInstall'    => ( ! is_multisite() && current_user_can( 'install_themes' ) ),
				'installURI'    => ( ! is_multisite() && current_user_can( 'install_themes' ) ) ? admin_url( 'theme-install.php' ) : null,
				'confirmDelete' => __( "Are you sure you want to delete this theme?\n\nClick 'Cancel' to go back, 'OK' to confirm the delete." ),
				'adminUrl'      => parse_url( admin_url(), PHP_URL_PATH ),
			),
			'l10n' => array(
				'addNew' => __( 'Add New Theme' ),
				'search'  => __( 'Search Installed Themes' ),
				'searchPlaceholder' => __( 'Search installed themes...' ), // placeholder (no ellipsis)
			),
		) );
		
		$current_theme = rw_get_theme_data();
		
		// Set the variables
		$t = runway_admin_themes_list_prepare( $current_theme );
		unset( $themes_list[$current_theme['Folder']] );
	?>
	
	<div class="theme-browser rendered">
		<div class="themes">
			<div class="theme active" tabindex="0" data-themeid="<?php echo $current_theme['Folder'];?>">
				<div class="theme-screenshot">
					<img alt="" src="<?php echo (isset($t['image']) && $t['image'] != "") ? $t['image'] : FRAMEWORK_URL.'framework/images/runway-child-theme-default-background.png';?>" />
				</div>
				<span id="<?php echo strtolower( $t['name'] );?>-action" class="more-details"><span class="primary-text"><?php echo __('Theme Details', 'framework'); ?></span><span class="action-text" style="display: none"></span></span>
				<div class="theme-author"><?php echo __('By', 'framework'); ?> <?php echo $current_theme['AuthorName']; ?></div>
				<h3 id="<?php echo strtolower( $t['name'] );?>-name" class="theme-name">
					<span><?php echo __('Active', 'framework'); ?>:</span> <?php echo $t['name']; ?>
				</h3>
				<div class="runway-theme-actions">
					<?php if ( strtolower( $t['name'] ) != 'runway' ) { ?>
					<div class="dashicons-container dashicons-container-edit" data-action-text="<?php _e('Edit', 'framework'); ?>">
						<div class="dashicons dashicons-edit" data-code="f105"><?php echo $t['editLink']; ?></div>
					</div>
					<div class="dashicons-container dashicons-container-duplicate" data-action-text="<?php _e('Duplicate', 'framework'); ?>">
						<div class="dashicons dashicons-admin-page" data-code="f464"><?php echo $t['duplicateLink']; ?></div>
					</div>
					<div class="dashicons-container dashicons-container-download" data-action-text="<?php _e('Standalone Theme', 'framework'); ?>">
						<div class="dashicons dashicons-download" data-code="f316"><?php echo $t['downloadLink']; ?></div>
					</div>
					<?php } ?>
				</div>
			</div>
			
			<?php foreach ( $themes_list as $theme ) { ?>
			<?php 
				$t = runway_admin_themes_list_prepare( $theme ); 
				if ( !isset( $theme['Template'] ) || empty( $theme['Template'] ) ) {
					$theme['Template'] = strtolower( $theme['Folder'] );
				}
				
				$theme_obj = wp_get_theme($theme['Folder']);
				$allowed = $theme_obj->is_allowed( 'network' );
				
				if(is_multisite() && is_admin()){		
					$url = 'themes.php?';
					$s = '';
					$ms_enable_theme_link = network_admin_url().esc_url(wp_nonce_url($url . 'action=enable&amp;theme=' . $theme['Folder'] . '&amp;paged=' . 1 . '&amp;s=' . $s, 'enable-theme_' . $theme['Folder'] ));		
				}
			?>
			<div class="theme" tabindex="0" data-themeid="<?php echo $theme['Folder'];?>">
				<div class="theme-screenshot">
					<img alt="" src="<?php echo (isset($t['image']) && $t['image'] != "") ? $t['image'] : FRAMEWORK_URL.'framework/images/runway-child-theme-default-background.png';?>" />
				</div>
				<span id="<?php echo strtolower( $t['name'] );?>-action" class="more-details"><span class="primary-text"><?php echo __('Theme Details', 'framework'); ?></span><span class="action-text" style="display: none"></span></span>
				<div class="theme-author"><?php echo __('By', 'framework'); ?> <?php echo $theme['AuthorName']; ?></div>
				<h3 id="<?php echo strtolower( $t['name'] );?>-name" class="theme-name">
					<?php echo $t['name']; ?>
				</h3>
				<div class="runway-theme-notactive-actions">
					<div class="runway-theme-actions">
						<?php if($allowed){ ?>
						<div class="dashicons-container dashicons-container-yes" data-action-text="<?php _e('Activate', 'framework'); ?>">
							<div class="dashicons dashicons-yes" data-code="f147"><?php echo $t['activateLink']; ?></div>
						</div>
						<?php } else { ?>
						<?php
							if(is_admin()) {
								$link = '<a '; 
								if(SUBDOMAIN_INSTALL != true){
									$link .= 'class="activate-theme" ';
								}
								$link .= 'href="'. $ms_enable_theme_link .'">'. __( 'Network Enable', 'framework' ) .'</a>';
						?>
						<div class="dashicons-container dashicons-container-yes" data-action-text="<?php _e('Activate', 'framework'); ?>">
							<div class="dashicons dashicons-yes" data-code="f147"><?php echo $link; ?></div>
						</div>
						<?php	} ?>
						<?php } ?>
						
						<?php if($allowed || (!$allowed && is_admin())) { ?>
						<div class="dashicons-container dashicons-container-visibility" data-action-text="<?php _e('Preview', 'framework'); ?>">
							<div class="dashicons dashicons-visibility" data-code="f177"><?php echo $t['previewLink']; ?></div>
						</div>
						<?php } ?>
					</div>
					<?php if($allowed || (!$allowed && is_admin())) { ?>
					<?php	if(strtolower( $theme['Name'] ) != 'runway') { ?>
					<div class="runway-theme-actions-bottom">
						<div class="dashicons-container dashicons-container-edit" data-action-text="<?php _e('Edit', 'framework'); ?>">
							<div class="dashicons dashicons-edit" data-code="f105"><?php echo $t['editLink']; ?></div>
						</div>
						<div class="dashicons-container dashicons-container-duplicate" data-action-text="<?php _e('Duplicate', 'framework'); ?>">
							<div class="dashicons dashicons-admin-page" data-code="f464"><?php echo $t['duplicateLink']; ?></div>
						</div>
						<div class="dashicons-container dashicons-container-no" data-action-text="<?php _e('Delete', 'framework'); ?>">
							<div class="dashicons dashicons-no" data-code="f158"><?php echo $t['deleteLink']; ?></div>
						</div>
						<div class="dashicons-container dashicons-container-download" data-action-text="<?php _e('Standalone Theme', 'framework'); ?>">
							<div class="dashicons dashicons-download" data-code="f316"><?php echo $t['downloadLink']; ?></div>
						</div>
					</div>
					<?php	} ?>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
			<div class="theme add-new-theme">
				<a href="<?php echo admin_url('admin.php?page=themes&navigation=new-theme'); ?>">
					<div class="theme-screenshot"><span></span></div>
					<h3 class="theme-name"><?php echo __('Create New Theme', 'framework'); ?></h3>
				</a>
			</div>
		</div>
		<br class="clear">
	</div>

	<hr>
	<br>

	<h3 class="adminTitle"><?php _e('Other Runway Themes', 'framework'); ?> &nbsp; <a class="add-new-h2" href="<?php echo admin_url('admin.php?page=directory'); ?>"><?php _e('More Themes', 'framework'); ?></a></h3>
	<?php
		$other_themes = $Themes_Manager->get_other_runway_themes();
	?>

	<br>
	
	<div class="theme-browser rendered">
		<div class="themes">
			<?php $i = 0; foreach ( $other_themes as $theme ) { ?>
				<div class="theme runway-theme-other" tabindex="0">
					<a href="<?php echo $theme->itemLink; ?>" target="_blank">
						<div class="theme-screenshot">
							<img alt="" src="<?php echo (isset($theme->Screenshot) && $theme->Screenshot != "") ? $theme->Screenshot : FRAMEWORK_URL.'framework/images/runway-child-theme-default-background.png';?>" />
						</div>
						<h3 id="<?php echo strtolower($theme->Name );?>-name" class="theme-name"><?php echo $theme->Name; ?></h3>
						<div class="runway-theme-other-actions"></div>
					</a>
				</div>
			<?php 
				$i++;
				if($i >= 3) break;
			} 
			?>
			<div class="theme add-new-theme runway-find-more-themes">
				<a href="<?php echo admin_url('admin.php?page=directory&addons=themes'); ?>">
					<div class="theme-screenshot"><span></span></div>
					<h3 class="theme-name"><?php echo __('Find More Themes', 'framework'); ?></h3>
				</a>
			</div>
		</div>
		<br class="clear">
	</div>
	
	<div class="theme-overlay"></div>
	
	<script id="tmpl-theme-modal" type="text/x-jquery-tmpl">
		<div class="theme-overlay {{if active == true}}active{{/if}}">
		<div class="theme-backdrop"></div>
		<div class="theme-wrap">
			<div class="theme-header">
				<button class="left dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show previous theme' ); ?></span></button>
				<button class="right dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show next theme' ); ?></span></button>
				<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close overlay' ); ?></span></button>
			</div>
			<div class="theme-about">
				<div class="theme-screenshots">
				{{if screenshot[0] != null}}
					<div class="screenshot"><img src="${screenshot[0]}" alt="" /></div>
				{{else}}
					<div class="screenshot blank"><img src="<?php echo FRAMEWORK_URL.'framework/images/runway-child-theme-default-background.png'; ?>" alt="" /></div>
				{{/if}}
				</div>
			
				<div class="theme-info">
					{{if active == true }}
						<span class="current-label"><?php _e( 'Current Theme' ); ?></span>
					{{/if}}
					<h3 class="theme-name">${name}<span class="theme-version"><?php printf( __( 'Version: %s' ), '${version}' ); ?></span></h3>
					<h4 class="theme-author"><?php printf( __( 'By %s' ), '{{html authorAndUri}}' ); ?></h4>

					{{if hasUpdate == true}}
					<div class="theme-update-message">
						<h4 class="theme-update"><?php _e( 'Update Available' ); ?></h4>
						${update}
					</div>
					{{/if}}
					<p class="theme-description">{{html description}}</p>

					{{if parent != null && parent != false}}
						<p class="parent-theme"><?php printf( __( 'This is a child theme of %s.' ), '<strong>{{html parent}}</strong>' ); ?></p>
					{{/if}}

					{{if tags != null && tags != false}}
						<p class="theme-tags"><span><?php _e( 'Tags:' ); ?></span> ${tags}</p>
					{{/if}}
				</div>
			</div>
		
			<div class="theme-actions">
				<div class="active-theme">
					<a href="${actions.customize}" class="button button-primary customize load-customize hide-if-no-customize"><?php _e( 'Customize' ); ?></a>
				</div>
				<div class="inactive-theme">
					{{if actions.activate != false && actions != ""}}
						<a href="${actions.activate}" class="button button-primary activate"><?php _e( 'Activate' ); ?></a>
					{{/if}}
					<a href="${actions.customize}" class="button button-secondary load-customize hide-if-no-customize"><?php _e( 'Live Preview' ); ?></a>
					<a href="${actions.preview}" class="button button-secondary hide-if-customize"><?php _e( 'Preview' ); ?></a>
				</div>

				{{if active == false && actions.delete != false && actions.delete != "" }}
					<a href="${actions.delete}" class="button button-secondary delete-theme"><?php _e( 'Delete' ); ?></a>
				{{/if}}
			</div>
		</div>
		</div>
	</script>
</div>
