<div class="wp-filter">
	<div class="filter-count">
		<span class="count theme-count"><?php echo $total_count; ?></span>
		<ul class="filter-links">
			<li><a href="#" data-sort="themes">Themes</a></li>
			<li><a href="#" data-sort="extensions" class="current">Extensions</a></li>
			<li><a href="#" data-sort="plugins">Plugins</a></li>
			<li class="add-ones-item-hidden"><a href="#" data-sort="fields">Fields</a></li>
		</ul>
	</div>
	<div class="search-form">
		<label class="screen-reader-text" for="wp-filter-search-input">Search Themes</label>
		<input placeholder="Search extensions..." type="search" id="wp-filter-search-rf-input" class="wp-filter-search-rf">
	</div>
</div>

<div class="theme-browser content-filterable rendered">
	<?php foreach($this->extensions_Paid as $item_shop) {
		$item_name = str_replace('-', '_', sanitize_key($item_shop->Files[0]->name)); ?>
	
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