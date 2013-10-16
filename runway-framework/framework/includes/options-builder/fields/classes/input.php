<?php

/**
 * text input field
 */
class input extends field {

	function get_html() { ?>
			<tr class="">
				<th scope="row" valign="top">
					<label>Post image width</label><br>
					<em>The default post image width. This can also be set from the blog shortcode or in a single post.</em>
				</th>
				<td>
					<input class="input-text" type="text" name="" value=""><br>
					<em>The default post image width. This can also be set from the blog shortcode or in a single post.</em>
				</td>
 			</tr>
	<?php }
}

?>
