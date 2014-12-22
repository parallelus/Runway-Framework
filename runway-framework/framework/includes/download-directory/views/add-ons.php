<?php
wp_enqueue_script('dropdown_downloads', FRAMEWORK_URL.'framework/includes/download-directory/js/dropdown-downloads.js');

$extensions_addons_search = array();
if(isset($this->extensions_addons) && !empty($this->extensions_addons)) {

	foreach($this->extensions_addons as $key => $item) {
		if( isset($search) && !empty($search) && strstr(strtolower($item->Name), strtolower($search)) === false )
				continue;

		switch ($addons_type) {
			case 'themes':
				$addon_key = $key;
				foreach($item->Files as $file_key => $file_info) {
					$filename = basename($file_info->file);
					$extensions_addons_search[$addon_key] = $item;
					$addon_key = substr($filename, 0, strpos($filename, '-install-'));
					if( substr($filename, 0, strpos($filename, '-install-child-'))) {
						$items_installed_link[$addon_key]['child'] = $extensions_addons_search[$addon_key]->Files[$file_key]->file;
					}
					if( substr($filename, 0, strpos($filename, '-install-standalone-'))) {
						$items_installed_link[$addon_key]['standalone'] = $extensions_addons_search[$key]->Files[$file_key]->file;
					}
					if( substr($filename, 0, strpos($filename, '-install-full-package-'))) {
						$items_installed_link[$addon_key]['full'] = $extensions_addons_search[$addon_key]->Files[$file_key]->file;
					}
				}

				break;

			case 'extensions':
				$filename = basename($item->Files[0]->file);
				$addon_key = substr($filename, 0, strpos($filename, '-extension-'));
				// $extensions_addons_search[$addon_key] = $item;
				break;

			case 'plugins':
				break;
			
			default:
				break;
		}
		$extensions_addons_search[$addon_key] = $item;
	}
}
$total_count = isset($extensions_addons_search)? count($extensions_addons_search) : 0;
?>

<div class="wp-filter">
	<div class="filter-count">
		<span class="count theme-count"><?php echo $total_count; ?></span>
	</div>
	<ul class="filter-links">
		<li><a href="<?php echo admin_url('admin.php?page=directory&addons=themes'); ?>" data-sort="themes" class="<?php echo ($addons_type == 'themes')? 'current' : ''; ?>"><?php echo __('Themes', 'framework'); ?></a></li>
		<li><a href="<?php echo admin_url('admin.php?page=directory&addons=extensions'); ?>" data-sort="extensions" class="<?php echo ($addons_type == 'extensions')? 'current' : ''; ?>"><?php echo __('Extensions', 'framework'); ?></a></li>
		<li><a href="<?php echo admin_url('admin.php?page=directory&addons=plugins'); ?>" data-sort="plugins" class="<?php echo ($addons_type == 'plugins')? 'current' : ''; ?>"><?php echo __('Plugins', 'framework'); ?></a></li>
		<li class="add-ons-item-hidden"><a href="#" data-sort="fields">Fields</a></li>
	</ul>
	<div class="search-form">
		<?php $url = 'admin.php?page=directory&addons='.$addons_type; ?>
		<form id="search-plugins" method="post" action="<?php echo admin_url($url); ?>">
			<input placeholder="<?php echo __('Search ', 'framework') . strtolower(rf__($addons_type)) . '...'; ?>" type="search" name="s" id="wp-filter-search-rf-input" value="<?php echo isset($_REQUEST['s']) ? $_REQUEST['s'] : ''; ?>" class="wp-filter-search-rf">
			<input class="button button-primary" type="submit" name="plugin-search-input" id="plugin-search-input" value="Search">
		</form>		
	</div>
</div>

<div class="theme-browser content-filterable rendered">
	<?php foreach($extensions_addons_search as $key => $item_addons) {
		$item_name = isset($item_addons->Files[0]->name)? str_replace('-', '_', sanitize_key($item_addons->Files[0]->name)) : ''; ?>
	
	<div class="theme" tabindex="0" aria-describedby="<?php echo $item_name; ?>-action <?php echo $item_name; ?>-name">	
		<div class="theme-screenshot">
			<?php if (isset($item_addons->Screenshot) && !empty($item_addons->Screenshot)) : ?>
				<img src="<?php echo $item_addons->Screenshot; ?>" alt="<?php echo $item_addons->Name; ?>">
			<?php else : ?>
				<img src="http://runwaywp.com/sites/main/wp-content/uploads/item-placeholder-preview-1-2197-640x316.png" alt="<?php echo $item_addons->Name; ?>">
			<?php endif; ?>
			<div class="more-details-rf white-gradient"><p><?php echo $item_addons->content; ?></p></div>
		</div>

		<h3 class="theme-name-rf white-gradient-left-right"><?php echo $item_addons->Name; ?></h3>
		<div class="theme-actions add-ons-init">
			<?php if(in_array($key, $items_installed)): 
					switch ($addons_type) {
						case 'themes': ?>
							<a class="button button-secondary add-ons-installed" data-key="<?php echo $key; ?>" href="<?php echo $items_installed_link_base; ?>"><?php echo __('Installed', 'framework'); ?><div class="dashicons dashicons-arrow-down" style="margin-top:2px;"></div></a>
							<div class="<?php echo $key; ?>-installed-item add-ons-installed-menu" style="display:none">
								<ul>
									<?php if(isset($items_installed_link[$key]['child']) && !empty($items_installed_link[$key]['child'])) : ?>
										<li><a href="<?php echo $items_installed_link[$key]['child']; ?>"><?php echo __('Download child theme', 'framework'); ?></a></li>
									<?php endif; ?>
									<?php if(isset($items_installed_link[$key]['standalone']) && !empty($items_installed_link[$key]['standalone'])) : ?>									
										<li><a href="<?php echo $items_installed_link[$key]['standalone']; ?>"><?php echo __('Download standalone theme', 'framework'); ?></a></li>
									<?php endif; ?>
									<?php if(isset($items_installed_link[$key]['full']) && !empty($items_installed_link[$key]['full'])) : ?>									
										<li><a href="<?php echo $items_installed_link[$key]['full']; ?>"><?php echo __('Download full package theme', 'framework'); ?></a></li>
									<?php endif; ?>									
								</ul>
							</div>
							<?php break;

						case 'extensions': ?>
							<a class="button button-secondary" href="<?php echo $items_installed_link; ?>"><?php echo __('Installed', 'framework'); ?></a>
							<?php break;

						case 'plugins':
							break;
						
						default:
							break;
					} ?>			
			<?php else: ?>
				<?php if( (isset($item_addons->isFree) && $item_addons->isFree) || (isset($item_addons->isPaid) && $item_addons->isPaid) ): ?>
					<a class="button button-primary" href="<?php echo admin_url('admin.php?page=directory&amp;action=install&amp;item='.$item_addons->Files[0]->name.'&amp;_wpnonce='); ?>"><?php echo __('Install', 'framework'); ?></a>
				<?php else: ?>
					<a class="button button-secondary" href="<?php echo $item_addons->itemLink; ?>" target="_blank"><?php echo __('Details', 'framework'); ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</div>

	</div>

<?php } ?>

</div>