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
				if (isset($item->Files[0]->file)) {
					$filename = basename($item->Files[0]->file);
					$addon_key = '';
					// File naming: theme-name-install-v0.0.0.zip or {theme name slug}-{type of package}-{version number}.zip
					$file_keys = array('-install-', '-full-package-', '-v', '.zip');
					// find the key
					foreach ($file_keys as $file_key) {
						$addon_key = substr($filename, 0, strpos($filename, $file_key));
						if (!empty($addon_key)) {
							break;
						}
					}
				}
				$file_count = count($item->Files);
				for ($i = 0; $i < $file_count; $i++) {
				// foreach($item->Files as $file_key => $file_info) {
					$filename = basename($item->Files[$i]->file);
					// $addon_key = substr($filename, 0, strpos($filename, '-install-'));
					$package_id = 'full';
					if( substr($filename, 0, strpos($filename, '-install-child-'))) {
						$package_id = 'child';
					}
					if( substr($filename, 0, strpos($filename, '-install-standalone-'))) {
						$package_id = 'standalone';
					}
					// if( substr($filename, 0, strpos($filename, '-full-package-'))) {
					// 	$package_id = 'full';
					// }
					$item->Files[$i]->package_id = $package_id;
				}

				break;

			case 'extensions':
				$filename = basename($item->Files[0]->file);
				$addon_key = '';
				// File naming: name-extensions-v0.0.0.zip or {extnsion name slug}-extensions-{version number}.zip
				$file_keys = array('-extension-', '-v', '.zip');
				// find the key
				foreach ($file_keys as $file_key) {
					$addon_key = substr($filename, 0, strpos($filename, $file_key));
					if (!empty($addon_key)) {
						break;
					}
				}
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
		<span class="count theme-count"><?php echo  $total_count; ?></span>
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
			<input placeholder="<?php echo __('Search ', 'framework') . strtolower(rf__($addons_type)) . '...'; ?>" type="search" name="s" id="wp-filter-search-rf-input" value="<?php echo isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : ''; ?>" class="wp-filter-search-rf">
			<input class="button button-primary" type="submit" name="plugin-search-input" id="plugin-search-input" value="Search">
		</form>		
	</div>
</div>


<div class="theme-browser content-filterable rendered">
	<?php foreach($extensions_addons_search as $key => $item_addons) {
		$item_name = isset($item_addons->Files[0]->name)? str_replace('-', '_', sanitize_key($item_addons->Files[0]->name)) : ''; ?>
	
	<div class="theme" tabindex="0" aria-describedby="<?php echo esc_attr($item_name.'-action'. $item_name); ?>-name">	
		<div class="theme-screenshot">
			<?php if (isset($item_addons->Screenshot) && !empty($item_addons->Screenshot)) : ?>
				<img src="<?php echo esc_url($item_addons->Screenshot); ?>" alt="<?php echo esc_attr($item_addons->Name); ?>">
			<?php else : ?>)
				<img src="http://runwaywp.com/sites/main/wp-content/uploads/item-placeholder-preview-1-2197-640x316.png" alt="<?php echo esc_attr($item_addons->Name); ?>">
			<?php endif; ?>
			<div class="more-details-rf white-gradient"><p><?php echo  $item_addons->content; ?></p></div>
		</div>

		<h3 class="theme-name-rf white-gradient-left-right"><?php echo  $item_addons->Name; ?></h3>
		<div class="theme-actions add-ons-init">
			<?php if( (isset($item_addons->isFree) && $item_addons->isFree) || (isset($item_addons->isPaid) && $item_addons->isPaid) ): 
					switch ($addons_type) {
						case 'themes':
							if( in_array($key, $items_installed) ): ?>
								<?php if( isset($item_addons->Files) && count($item_addons->Files) ): ?>
									<a class="button button-primary add-ons-installed" data-key="<?php echo esc_attr($key); ?>" href="#"><?php echo __('Install', 'framework'); ?><div class="dashicons dashicons-arrow-down dashicons-position"></div></a>
									<div class="<?php echo esc_attr($key); ?>-installed-item add-ons-installed-menu" style="display:none">
										<ul>
										<?php 
										foreach ($item_addons->Files as $file_key => $file_info) {
											$download_link = admin_url('admin.php?page=directory&amp;addons=themes&amp;action=install&amp;item='.$file_info->name.'&amp;_wpnonce=');
											?>
											<?php if(!empty($file_info->package_id) && $file_info->package_id == 'child') : ?>
												<li><a href="<?php echo esc_url($download_link); ?>"><?php echo __('Install child theme', 'framework'); ?></a></li>
											<?php endif; ?>
											<?php if(!empty($file_info->package_id) && $file_info->package_id == 'standalone') : ?>									
												<li><a href="<?php echo esc_url($download_link); ?>"><?php echo __('Install standalone theme', 'framework'); ?></a></li>
											<?php endif; ?>
											<?php if(!empty($file_info->package_id) && $file_info->package_id == 'full') : 
												$download_link = admin_url('admin.php?page=directory&amp;addons=themes&amp;action=download&amp;item='.$file_info->name.'&amp;_wpnonce='); ?>
												<li><a href="<?php echo esc_url($download_link); ?>"><?php echo __('Download full theme package', 'framework'); ?></a></li>
											<?php endif; ?>									
										<?php } ?>
										</ul>
									</div>
								<?php endif; ?>
							<?php else: ?>
								<a class="button button-secondary" href="<?php echo esc_url($item_addons->itemLink); ?>" target="_blank"><?php echo __('Details', 'framework'); ?></a>
							<?php endif;

							break;

						case 'extensions':
							if( in_array($key, $items_installed) ): ?>
								<a class="button button-secondary" href="<?php echo esc_url($items_installed_link); ?>"><?php echo __('Installed', 'framework'); ?></a>
							<?php else: ?>
								<a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=directory&amp;addons=extensions&amp;action=install&amp;item='.$item_addons->Files[0]->name.'&amp;_wpnonce=')); ?>"><?php echo __('Install', 'framework'); ?></a>
							<?php endif;								
							break;

						case 'plugins':
							break;
						
						default:
							break;
					} ?>			
			<?php else: ?>
					<a class="button button-secondary" href="<?php echo esc_url($item_addons->itemLink); ?>" target="_blank"><?php echo __('Details', 'framework'); ?></a>
			<?php endif; ?>
		</div>

	</div>

<?php } ?>

</div>