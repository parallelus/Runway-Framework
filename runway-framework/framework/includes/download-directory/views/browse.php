<?php  //out($response); ?>

<script type="text/javascript">
	(function($){
		$(document).ready(function(){
			$('body').on('click', '.details', function(){
				$(".details-dialog[token='"+$(this).attr("token")+"']").dialog({
						"modal" : true,
						"width": 500,
						"height": 500
					}
				);
			});
		});
	})(jQuery);
</script>

<p><?php _e( 'You can search and install Extensions in this area. Adding new Extensions will allow you to enhance the functionality of your theme.', 'framework' ) ?></p>

<ul class="subsubsub">
	<li class="plugin-install-dashboard"><a href="admin.php?page=directory&amp;tab=browse" <?php if ( $tab != 'search' ) { 
		echo 'class="current"'; }?>><?php _e( 'Browse', 'framework' ) ?></a> |</li>
	<li class="plugin-install-search"><a href="admin.php?page=directory&amp;tab=search" <?php if ( $tab == 'search' ) { 
		echo 'class="current"'; }?>><?php _e( 'Search', 'framework' ) ?></a></li>	
</ul>

<br class="clear">

<div class="tablenav top">
	<div class="alignleft actions">
		<form id="search-plugins" method="post" action="admin.php?page=directory">
			<input type="search" name="s" value="<?php echo isset($_REQUEST['s']) ? $_REQUEST['s'] : ''; ?>" style="width:250px">
			<label class="screen-reader-text" for="plugin-search-input"><?php _e( 'Search Directory', 'framework' ) ?></label>
			<input type="submit" name="plugin-search-input" id="plugin-search-input" class="button" value="Search">
		</form>
	</div>
	<div class="tablenav-pages <?php // if one page, add class 'one-page' ?>">
		<span class="displaying-num"><?php echo $response->total_count ?> <?php _e( 'items', 'framework' ) ?></span>
		<span class="pagination-links">

			<?php if ( $current_page == 1 ) { ?>
				<a class="first-page disabled" title="<?php _e( 'Go to the first page', 'framework' ) ?>" href="">&laquo;</a>
				<a class="prev-page disabled" title="<?php _e( 'Go to the previous page', 'framework' ) ?>" href="#">&lsaquo;</a>
			<?php } 
else { ?>
				<a class="first-page" title="<?php _e( 'Go to the first page', 'framework' ) ?>" href="admin.php?page=directory<?php echo isset( $_REQUEST['s'] ) ? "&s={$_REQUEST['s']}" : '' ?>">&laquo;</a>
				<a class="prev-page" title="<?php _e( 'Go to the previous page', 'framework' ) ?>" href="admin.php?page=directory&current_page=<?php echo $current_page - 1 ?><?php echo isset( $_REQUEST['s'] ) ? "&s={$_REQUEST['s']}" : '' ?>">&lsaquo;</a>
			<?php } ?>

			<span class="paging-input">
				<form method="get" style="display: inline;">
					<input type="hidden" name="s" value="<?php echo $_REQUEST['s']; ?>">
					<input type="hidden" name="page" value="directory">
					<input class="current-page" title="<?php _e( 'Current page', 'framework' ) ?>" type="text" name="_current_page" value="<?php echo $current_page ?>" size="2"> of <span class="total-pages"><?php echo ceil( $response->total_count / $response->on_page ) ?></span>
				</form>
			</span>

			<?php if ( $current_page >= $response->total_count / $response->on_page ) { ?>
				<a class="next-page disabled" title="<?php _e( 'Go to the next page', 'framework' ) ?>" href="">&rsaquo;</a>
				<a class="last-page disabled" title="<?php _e( 'Go to the last page', 'framework' ) ?>" href="">&raquo;</a>
			<?php } 
else { ?>
				<a class="next-page" title="<?php _e( 'Go to the next page', 'framework' ) ?>" href="admin.php?page=directory&current_page=<?php echo $current_page + 1 ?><?php echo isset( $_REQUEST['s'] ) ? "&s={$_REQUEST['s']}" : '' ?>">&rsaquo;</a>
				<a class="last-page" title="<?php _e( 'Go to the last page', 'framework' ) ?>" href="admin.php?page=directory&current_page=<?php echo ceil( $response->total_count / $response->on_page ) ?><?php echo isset( $_REQUEST['s'] ) ? "&s={$_REQUEST['s']}" : '' ?>">&raquo;</a>
			<?php } ?>

		</span>
	</div>

	<img src="images/wpspin_light.gif" class="ajax-loading list-ajax-loading" alt="">

	<br class="clear">
</div>




<table class="wp-list-table widefat plugin-install" cellspacing="0">
	<thead>
	<tr>
		<th scope="col" id="name" class="manage-column column-name" style=""><?php _e( 'Name', 'framework' ) ?></th>
		<th scope="col" id="version" class="manage-column column-version" style=""><?php _e( 'Version', 'framework' ) ?></th>		
		<th scope="col" id="description" class="manage-column column-description" style=""><?php _e( 'Description', 'framework' ) ?></th>
	</tr>
	</thead>

	<tbody id="the-list">

		<?php
if ( $response->extensions ) 
{
	foreach ( $response->extensions as $token => $extension ) {	?>
			<tr>
				<td class="name column-name"><strong><?php echo $extension->Name ?></strong>
					<div class="action-links">
						<a href="#" class="details" token="<?php echo $token; ?>" title="More information">Details</a> |
						<a class="install-now" href="admin.php?page=directory&amp;action=install&amp;item=<?php echo $token; ?>&amp;_wpnonce=<?php //wpnonce ?>" title="Install">
							<?php echo ($extm->is_install($token)) ? "Reinstall" : "Install Now"; ?>
						</a>
					</div>
					<div class = "details-dialog" token="<?php echo $token; ?>" style="display:none">
						<strong><?php echo $extension->Name ?></strong> (Version: <?php echo $extension->Version ?>)<hr>
						<?php echo $extension->Description; ?>
					</div>
				</td>
				<td class="vers column-version"><?php echo $extension->Version ?></td>				
				<td class="desc column-description"><?php echo $extension->Description ?></td>
			</tr>
		<?php } 
	} 
else { 
	echo '<tr><td colspan="3">Extensions not found.</td></tr>'; } ?>

	</tbody>

	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-name" style=""><?php _e( 'Name', 'framework' ) ?></th>
		<th scope="col" class="manage-column column-version" style=""><?php _e( 'Version', 'framework' ) ?></th>		
		<th scope="col" class="manage-column column-description" style=""><?php _e( 'Description', 'framework' ) ?></th>
	</tr>
	</tfoot>
</table>


<div class="tablenav bottom">
	<div class="tablenav-pages <?php // if one page, add class 'one-page' ?>">
		<span class="displaying-num"><?php echo $response->total_count ?> <?php _e( 'items', 'framework' ) ?></span>
		<span class="pagination-links">

			<?php if ( $current_page == 1 ) { ?>
				<a class="first-page disabled" title="<?php _e( 'Go to the first page', 'framework' ) ?>" href="">&laquo;</a>
				<a class="prev-page disabled" title="<?php _e( 'Go to the previous page', 'framework' ) ?>" href="#">&lsaquo;</a>
			<?php } 
else { ?>
				<a class="first-page" title="<?php _e( 'Go to the first page', 'framework' ) ?>" href="admin.php?page=directory<?php echo isset( $_REQUEST['s'] ) ? "&s={$_REQUEST['s']}" : '' ?>">&laquo;</a>
				<a class="prev-page" title="<?php _e( 'Go to the previous page', 'framework' ) ?>" href="admin.php?page=directory&current_page=<?php echo $current_page - 1 ?><?php echo isset( $_REQUEST['s'] ) ? "&s={$_REQUEST['s']}" : '' ?>">&lsaquo;</a>
			<?php } ?>

			<span class="paging-input">
				<form method="get" style="display: inline;">
					<input type="hidden" name="s" value="<?php echo $_REQUEST['s']; ?>">
					<input type="hidden" name="page" value="directory">
					<input class="current-page" title="<?php _e( 'Current page', 'framework' ) ?>" type="text" name="_current_page" value="<?php echo $current_page ?>" size="2"> of <span class="total-pages"><?php echo ceil( $response->total_count / $response->on_page ) ?></span>
				</form>
			</span>

			<?php if ( $current_page >= $response->total_count / $response->on_page ) { ?>
				<a class="next-page disabled" title="<?php _e( 'Go to the next page', 'framework' ) ?>" href="">&rsaquo;</a>
				<a class="last-page disabled" title="<?php _e( 'Go to the last page', 'framework' ) ?>" href="">&raquo;</a>
			<?php } 
else { ?>
				<a class="next-page" title="<?php _e( 'Go to the next page', 'framework' ) ?>" href="admin.php?page=directory&current_page=<?php echo $current_page + 1 ?><?php echo isset( $_REQUEST['s'] ) ? "&s={$_REQUEST['s']}" : '' ?>">&rsaquo;</a>
				<a class="last-page" title="<?php _e( 'Go to the last page', 'framework' ) ?>" href="admin.php?page=directory&current_page=<?php echo ceil( $response->total_count / $response->on_page ) ?><?php echo isset( $_REQUEST['s'] ) ? "&s={$_REQUEST['s']}" : '' ?>">&raquo;</a>
			<?php } ?>

		</span>
	</div>

	<span class="spinner ajax-loading list-ajax-loading" style="display: none;"></span>
	<br class="clear">
</div>
