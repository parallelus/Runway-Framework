<?php
// Info and Alert Messages
if ( $info_message != '' ) {
	echo '<div id="message" class="updated"><p>' . $info_message . '</p></div>';
}

$ext_inactive_status = '';
switch ( $this->navigation ) {
	case ( 'inactive' ): {
		$ext_inactive_status = 'class="current"';

	}
		break;
	case ( 'upgrade' ): {
		$ext_upgrade_status = 'class="current"';
	}
		break;
	default: {
		$ext_all_status = 'class="current"';
	}
}
?>

<ul class="subsubsub">
	<li class="all">
		<a href="<?php echo admin_url( 'admin.php?page=extensions&navigation=all' ); ?>" <?php echo isset( $ext_all_status ) ? $ext_all_status : ''; ?>>
			<?php echo __( 'All', 'runway' ); ?> <span class="count">(<?php echo wp_kses_post( $ext_all_total ); ?>)</span>
		</a> |
	</li>
	<li class="inactive">
		<a href="<?php echo admin_url( 'admin.php?page=extensions&navigation=inactive' ); ?>" <?php echo isset( $ext_inactive_status ) ? $ext_inactive_status : ''; ?>>
			<?php echo __( 'Inactive', 'runway' ); ?> <span class="count">(<?php echo wp_kses_post( $ext_inactive_total ); ?>)</span>
		</a>
	</li>
</ul>
<form method="post" action="<?php echo admin_url( 'admin.php?page=extensions&navigation=search#add-exts' ); ?>" class="clear">
	<p class="search-box">
		<label class="screen-reader-text" for="exts-search-input"><?php echo __( 'Search Extensions', 'runway' );?>:</label>
		<input type="search" id="exts-search-input" name="exts-search-input"
		       value="<?php echo isset( $_POST['exts-search-input'] ) ? $_POST['exts-search-input'] : ''; ?>">
		<input type="submit" name="ext-search-submit" id="ext-search-submit" class="button"
		       value="<?php echo __( 'Search Extensions', 'runway' ); ?>">
	</p>
</form>

<form action="<?php echo admin_url( 'admin.php?page=extensions&navigation=bulk-actions' ); ?>" method="post">
	<div class="alignleft actions">
		<select name="action">
			<option value="-1" selected="selected"><?php echo __( 'Bulk Actions', 'runway' ); ?></option>
			<option value="activate-selected"><?php echo __( 'Activate', 'runway' ); ?></option>
			<option value="deactivate-selected"><?php echo __( 'Deactivate', 'runway' ); ?></option>
			<option value="delete-selected"><?php echo __( 'Delete', 'runway' ); ?></option>
		</select>
		<input type="submit" name="bulk-actions-submit" class="button-secondary action" value="Apply">
	</div>
	<br><br>

	<table class="wp-list-table widefat">
		<thead>
		<tr>
			<th scope="col" id="cb" class="manage-column column-cb check-column" style="width: 0px;">
				<input type="checkbox" name="ext_chk[]"/>
			</th>
			<th id="name" class="manage-column column-name">
				<?php echo __( 'Extension', 'runway' ); ?>
			</th>
			<th id="description" class="manage-column column-description">
				<?php echo __( 'Description', 'runway' ); ?>
			</th>
		</tr>
		</thead>
	<tbody id="the-list">

	<?php
	if ( ! empty( $exts ) ) {
		foreach ( $exts as $ext => $ext_info ) {
			$ext_cnt = ! $extm->is_activated( $ext );
			?>

			<tr <?php if ( $ext_cnt ) { ?> class="inactive" <?php } else { ?> class="active" <?php } ?>>
				<th class="check-column">
					<input type="checkbox" name="ext_chk[]" value="<?php echo esc_attr( $ext ); ?>"/>
				</th>
				<td class="plugin-title">
					<strong><?php echo wp_kses_post( $ext_info['Name'] ); ?></strong>
					<?php if ( $ext_cnt ) { ?>
						<br>
						<a href="<?php echo admin_url( 'admin.php?page=extensions&navigation=extension-activate&ext=' . urlencode( $ext ) ); ?>">
							<?php echo __( 'Activate', 'runway' ); ?>
						</a> |
						<a style="color: #BC0B0B;"
						   href="<?php echo admin_url( 'admin.php?page=extensions&navigation=del-extension-confirm&ext=' . urlencode( $ext ) ); ?>">
							<?php echo __( 'Delete', 'runway' ); ?>
						</a>
					<?php } elseif ( ! $ext_cnt ) { ?>
						<br>
						<a class="edit"
						   href="<?php echo admin_url( 'admin.php?page=extensions&navigation=extension-deactivate&ext=' . urlencode( $ext ) ); ?>">
							<?php echo __( 'Deactivate', 'runway' ); ?>
						</a>
					<?php } ?>

				</td>
				<td class="column-description desc">

					<?php
					// Item description
					$description = '<div class="plugin-description"><p>' . wp_kses_post( $ext_info['Description'] ) . '</p></div>';
					// Item info
					$class   = $ext_cnt ? 'inactive' : 'active';
					$version = $ext_info['Version'] ? sprintf( __( 'Version: %s', 'runway' ), $ext_info['Version'] ) : '';
					if ( $ext_info['Author'] ) {
						$author = ' | ' . sprintf( __( 'By %s', 'runway' ), $ext_info['Author'] );
						if ( $ext_info['AuthorURI'] ) {
							$author = ' | ' . __( 'By', 'runway' ) .
							          ' <a href="' . $ext_info['AuthorURI'] . '" title="' . __( 'Visit author homepage', 'runway' ) . '">' .
							          $ext_info['Author'] .
							          '</a>';
						}
					} else {
						$author = ' | ' . __( 'By Unknown', 'runway' );
					}
					$plugin_link = $ext_info['ExtensionURI'] ? ' | <a href="' . esc_url( $ext_info['ExtensionURI'] ) . '" title="' .
					                                           __( 'Visit plugin site', 'runway' ) . '">' .
					                                           __( 'Visit plugin site', 'runway' ) . '</a>' : '';
					$info        = '<div class="' . esc_attr( $class ) . 'second plugin-version-author-uri">' . $version . $author . $plugin_link . '</div>';

					// Print details
					echo rf_string( $description );
					echo rf_string( $info ); // escaped above

					if ( count( $ext_info['DepsExts'] ) > 0 && isset( $ext_info['DepsExts'] ) && ! empty( $ext_info['DepsExts'] ) ) { ?>
						<b><?php echo __( 'Dependencies', 'runway' ); ?>:</b>
						<?php
						$deps_count = count( $ext_info['DepsExts'] );
						$i          = 0;
						foreach ( $ext_info['DepsExts'] as $dep_ext ) {
							$i ++;
							$dep_ext = explode( '|', $dep_ext );
							if ( file_exists( $extm->extensions_dir . $dep_ext[1] ) ) {
								$ext_data = $extm->get_extension_data( $extm->extensions_dir . $dep_ext[1] );
								$active   = false;
								if ( ! empty( $extm->admin_settings ) ) {
									foreach ( $extm->admin_settings['extensions'][ $extm->theme_name ] as $ext_tmp ) {
										if ( $ext_tmp == $dep_ext[1] ) {
											$active = true;
										}
									}
								}
							} else {
								$active           = false;
								$ext_data['Name'] = $dep_ext[0];
							}
							$coma = ( $i == $deps_count ) ? '' : ',';

							$active = $active ?
								'<i style="color: green;">' . __( 'Active', 'runway' ) . '</i>' :
								'<i style="color: red;">' . __( 'Disabled', 'runway' ) . '</i>';

							echo wp_kses_post( $ext_data['Name'] ); ?>(<?php echo wp_kses_post( $active ); ?>)<?php echo wp_kses_post( $coma );

						}
					} ?>
				</td>
			</tr>
		<?php }
	} else { ?>

		<tr calss="active">
			<td class="plugin-title">
				<?php echo __( 'Extensions not found', 'runway' ); ?>.
			</td>
			<td class="column-description desc"></td>
		</tr>
		
	<?php } ?>

	</tbody>
	</table>
</form>
