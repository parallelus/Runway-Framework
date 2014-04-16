<?php

/**
 * text input field
 */
class input extends field {

	function get_html() { ?>
			<tr class="">
				<th scope="row" valign="top">
					<label><?php echo __( 'Post image width', 'framework' ); ?></label><br>
					<em><?php echo __( 'The default post image width. This can also be set from the blog shortcode or in a single post', 'framework' ); ?>.</em>
				</th>
				<td>
					<input class="input-text" type="text" name="" value=""><br>
					<em><?php echo __( 'The default post image width. This can also be set from the blog shortcode or in a single post', 'framework' ); ?>.</em>
				</td>
 			</tr>
	<?php }
}

?>
