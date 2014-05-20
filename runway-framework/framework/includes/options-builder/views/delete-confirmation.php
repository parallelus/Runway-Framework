<form method="post" action="<?php echo $action_url_yes; ?>" style="display:inline;">
	<p>
		<?php echo __( 'You are about to remove the following', 'framework').' '.rf__($item_confirm).':'; ?>
		<ul class="ul-disc">
			<li><?php rf_e($item_title); ?></li>
		</ul>
		<?php echo __( 'Are you sure you want to delete this', 'framework').' '.rf__($item_confirm).'?'; ?>
	</p>
	<?php submit_button( __( 'Yes, Delete this', 'framework').' '.rf__($item_confirm), 'button', 'submit', false ); ?>
</form>
<form method="post" action="<?php echo $action_url_no; ?>" style="display:inline;">
	<?php submit_button( __( 'No, Return me to the', 'framework' ).' '.rf__($item_confirm).' '. __( 'list', 'framework' ), 'button', 'submit', false ); ?>
</form>