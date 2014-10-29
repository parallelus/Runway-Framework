<?php
$extensions_Paid_search = array();
if(isset($this->extensions_Paid) && !empty($this->extensions_Paid))
	foreach($this->extensions_Paid as $key => $item) {
		if( isset($search) && !empty($search) && strstr(strtolower($item->Name), strtolower($search)) === false )
				continue;
		$extensions_Paid_search[$key] = $item;
	}
$total_count = isset($extensions_Paid_search)? count($extensions_Paid_search) : 0;
?>

<div class="wp-filter">
	<div class="filter-count">
		<span class="count theme-count"><?php echo $total_count; ?></span>
		<ul class="filter-links">
			<li><a href="<?php echo admin_url('admin.php?page=directory&addons=Themes'); ?>" data-sort="themes" class="<?php echo ($addons_type == 'Themes')? 'current' : ''; ?>">Themes</a></li>
			<li><a href="<?php echo admin_url('admin.php?page=directory&addons=Extensions'); ?>" data-sort="extensions" class="<?php echo ($addons_type == 'Extensions')? 'current' : ''; ?>">Extensions</a></li>
			<li><a href="<?php echo admin_url('admin.php?page=directory&addons=Plugins'); ?>" data-sort="plugins" class="<?php echo ($addons_type == 'Plugins')? 'current' : ''; ?>">Plugins</a></li>
			<li class="add-ones-item-hidden"><a href="#" data-sort="fields">Fields</a></li>
		</ul>
	</div>
	<div class="search-form">
		<?php $url = 'admin.php?page=directory&addons='.$addons_type; ?>
		<form id="search-plugins" method="post" action="<?php echo admin_url($url); ?>">
			<input placeholder="<?php echo __('Search ', 'framework') . strtolower($addons_type) . '...'; ?>" type="search" name="s" id="wp-filter-search-rf-input" value="<?php echo isset($_REQUEST['s']) ? $_REQUEST['s'] : ''; ?>" class="wp-filter-search-rf">
			<input class="button button-primary" type="submit" name="plugin-search-input" id="plugin-search-input" value="Search">
		</form>		
	</div>
</div>

<div class="theme-browser content-filterable rendered">
	<?php foreach($extensions_Paid_search as $item_shop) {
		$item_name = isset($item_shop->Files[0]->name)? str_replace('-', '_', sanitize_key($item_shop->Files[0]->name)) : ''; ?>
	
	<div class="theme" tabindex="0" aria-describedby="<?php echo $item_name; ?>-action <?php echo $item_name; ?>-name">	
		<div class="theme-screenshot">
			<img src="<?php echo $item_shop->Screenshot; ?>" alt="<?php echo $item_shop->Name; ?>">
		</div>
		
		<div class="more-details-rf white-gradient"><?php echo $item_shop->content; ?></div>
		<h3 class="theme-name-rf white-gradient-left-right"><?php echo $item_shop->Name; ?></h3>
		<div class="theme-actions add-ons-init">
			<?php if( (isset($item_shop->isFree) && $item_shop->isFree) || (isset($item_shop->isPaid) && $item_shop->isPaid) ): ?>
				<a class="button button-primary" href="<?php echo admin_url('admin.php?page=directory&amp;action=install&amp;item='.$item_shop->Files[0]->name.'&amp;_wpnonce='); ?>"><?php echo __('Install', 'framework'); ?></a>
			<?php else: ?>
				<a class="button button-secondary" href="<?php echo $item_shop->itemLink; ?>" target="_blank"><?php echo __('Detail', 'framework'); ?></a>
			<?php endif; ?>
		</div>

	</div>

<?php } ?>

</div>