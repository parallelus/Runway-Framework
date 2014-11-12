<?php
$extensions_addons_search = array();
if(isset($this->extensions_addons) && !empty($this->extensions_addons))
	foreach($this->extensions_addons as $key => $item) {
		if( isset($search) && !empty($search) && strstr(strtolower($item->Name), strtolower($search)) === false )
				continue;
		$extensions_addons_search[$key] = $item;
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
	<?php foreach($extensions_addons_search as $item_addons) {
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
			<?php if( (isset($item_addons->isFree) && $item_addons->isFree) || (isset($item_addons->isPaid) && $item_addons->isPaid) ): ?>
				<a class="button button-primary" href="<?php echo admin_url('admin.php?page=directory&amp;action=install&amp;item='.$item_addons->Files[0]->name.'&amp;_wpnonce='); ?>"><?php echo __('Install', 'framework'); ?></a>
			<?php else: ?>
				<a class="button button-secondary" href="<?php echo $item_addons->itemLink; ?>" target="_blank"><?php echo __('Detail', 'framework'); ?></a>
			<?php endif; ?>
		</div>

	</div>

<?php } ?>

</div>