<?php
global $developer_tools, $Themes_Manager;

$popup_message = '<h2>'. __( 'Activate new theme?', 'framework' ) .'</h2>';

$themesActivate = admin_url('themes.php');
$httpReferer = isset( $_SERVER['HTTP_REFERER'] )? str_replace( '?activated=true', '', $_SERVER['HTTP_REFERER'] ) : '';

if ( isset( $_GET['activate-default'] ) && $_GET['activate-default'] == 'activate' && ($httpReferer == $themesActivate || strstr($httpReferer, 'action=upload-theme') ) ) {
	$popup_message = '<a href="#" class="screenshot"><img src="../wp-content/themes/runway-framework/framework/images/screenshot-transparent.png" width="265" height="199"></a>';
	$popup_message .= '<h2>'. __( 'Welcome to Runway!', 'framework' ). '</h2><p>'. __( 'We recommend using Runway with an active child theme. Would you like to automatically activate the default child theme?', 'framework' ) .'</p>';
	if ( !file_exists( $developer_tools->themes_path.'/liftoff' ) ) {
		$zip = new ZipArchive();
		$zip->open( $developer_tools->default_theme_package_path );
		$zip->extractTo( $developer_tools->themes_path );
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

				Enable default theme for a network?

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
                    jQuery('#adminmenuwrap').css({'z-index':0});
                },
                close: function(event, ui) {
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
	                        $('#adminmenuwrap').css({'z-index':0});
	                    },
	                    close: function(event, ui) {
	                        $('#adminmenuwrap').css({'z-index':'auto'});
	                    },							
						position: "center",
						modal: true,
						resizable: false,
						dialogClass: 'duplicateThemePopup'
					});
					$(".ui-dialog-titlebar").hide();

					var currunt_theme_folder = $(this).data("theme-folder");
					var regex = RegExp(/["+currunt_theme_folder+"-copy-\d]$/g);

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
								var url = "admin.php?page=themes&navigation=duplicate-theme&name=" + name + "&new_name=" + new_name;
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
						document.location = "admin.php?page=themes";
					}})
					return false;
				});
			});
		})(jQuery);
	</script>

	<p>
		<?php echo __( 'A child theme can be run on any WordPress install with the Runway framework active. You can use Runway to setup custom theme options, menus and many other features of a child theme. Completed themes can be downloaded as a child or standalone version. A standalone theme may be installed on any WordPress install regardless of having Runway active', 'framework' ); ?>.
	</p>

	<h3><?php _e( 'Current Theme', 'framework' ); ?></h3>

	<div class="active-runway-theme">

		<?php

$themes_list = $developer_tools->search_themes();
$current_theme = rw_get_theme_data();

// Set the variables
$t = runway_admin_themes_list_prepare( $current_theme );
unset( $themes_list[$current_theme['Folder']] );
?>

		<div class="postbox runway-theme" id="current-theme">

			<?php echo $t['screenshot']; ?>

			<div class="theme-inside">

				<h4><?php echo $t['name'] ?></h4>

				<?php echo $t['themeInfo']; ?>

				<div class="theme-controlls theme-options">
					<?php if ( strtolower( $t['name'] ) != 'runway' ) { ?>
						<ul>
							<li><?php echo $t['editLink']; ?></li>
							<li><?php echo $t['duplicateLink']; ?></li>
							<!-- <li><?php echo $t['deleteLink']; ?></li> -->
							<li><?php echo $t['downloadLink']; ?></li>
						</ul>
					<?php } ?>
				</div>

			</div>

			<div class="clear"></div>

		</div>

	</div>



	<h3><?php _e( 'Available Runway themes', 'framework' ); ?></h3>
	<div class="avalible-runway-themes">


		<?php foreach ( $themes_list as $theme ) { 
	// Set the variables
	$t = runway_admin_themes_list_prepare( $theme );
	?>

			<div class="runway-theme available-theme">

				<?php echo $t['screenshot']; ?>

				<div class="theme-inside">

					<h4><?php echo $t['name'] ?></h4>

					<?php echo $t['themeInfo']; ?>

					<div class="theme-controlls theme-options">
						<?php

	if ( !isset( $theme['Template'] ) || empty( $theme['Template'] ) ) {
		$theme['Template'] = strtolower( $theme['Folder'] );
	}

	$theme_obj = wp_get_theme($theme['Folder']);
	$allowed = $theme_obj->is_allowed( 'network' );

	if(is_multisite() && is_admin()){		
		$url = 'themes.php?';
		$s = '';
		$ms_enable_theme_link = admin_url('network/').esc_url(wp_nonce_url($url . 'action=enable&amp;theme=' . $theme['Folder'] . '&amp;paged=' . 1 . '&amp;s=' . $s, 'enable-theme_' . $theme['Folder'] ));		
	}
	// class="activate-theme"
	?>

						<ul>
							<li>
								<?php
									if($allowed){
										echo $t['activateLink'];
										?>
							</li>
							<li><?php echo $t['previewLink']; ?></li>
							<?php 
								if ( strtolower( $theme['Name'] ) != 'runway' ) { 
							?>
								<li><?php echo $t['editLink']; ?></li>
								<li><?php echo $t['duplicateLink']; ?></li>
								<li><?php echo $t['deleteLink']; ?></li>
								<li><?php echo $t['downloadLink']; ?></li>
							<?php } 
									}
									else{
										if(is_admin()){
											$link = '<a '; 
											if(SUBDOMAIN_INSTALL != true){
												$link .= 'class="activate-theme" ';
											}
											// else{
											// 	$link .= 'target="_blank" ';
											// }
											$link .= 'href="'. $ms_enable_theme_link .'">'. __( 'Network Enable', 'framework' ) .'</a>';
											echo $link;
											?>
							</li>
							<li><?php echo $t['previewLink']; ?></li>
							<?php 
								if ( strtolower( $theme['Name'] ) != 'runway' ) { 
							?>
								<li><?php echo $t['editLink']; ?></li>
								<li><?php echo $t['duplicateLink']; ?></li>
								<li><?php echo $t['deleteLink']; ?></li>
								<li><?php echo $t['downloadLink']; ?></li>
							<?php } 
										}
										else{
											echo __('Please wait until an administrator activates theme for the network', 'framework') . '</li>';
										}
									}
								?>							
						</ul>
					</div>

				</div>

				<div class="clear"></div>

			</div>

		<?php } ?>
	</div> <!-- / .avalible-runway-themes -->
</div>
