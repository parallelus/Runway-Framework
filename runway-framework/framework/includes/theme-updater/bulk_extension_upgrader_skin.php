<?php

class Bulk_Extension_Upgrader_Skin extends Bulk_Upgrader_Skin {

	public $theme_info = array(); // Theme_Upgrader::bulk() will fill this in.

	public function __construct( $args = array() ) {

		parent::__construct( $args );

	}

	public function add_strings() {

		parent::add_strings();
		$this->upgrader->strings['skin_before_update_header'] = __( 'Updating Extension %1$s (%2$d/%3$d)', 'runway' );

	}

	public function before( $title = '' ) {

		parent::before( $this->theme_info->display( 'Name' ) );

	}

	public function after( $title = '' ) {

		parent::after( $this->theme_info->display( 'Name' ) );

	}

	public function bulk_footer() {

		parent::bulk_footer();
		$update_actions =  array(
			'themes_page' => '<a href="' . self_admin_url( 'themes.php' ) . '" title="' . esc_attr__( 'Go to themes page', 'runway' ) .
			                 '" target="_parent">' . __( 'Return to Themes page', 'runway' ) . '</a>',
			'updates_page' => '<a href="' . self_admin_url( 'update-core.php' ) . '" title="' . esc_attr__( 'Go to WordPress Updates page', 'runway ') .
			                  '" target="_parent">' . __('Return to WordPress Updates', 'runway') . '</a>'
		);
		if ( ! current_user_can( 'switch_themes' ) && ! current_user_can( 'edit_theme_options' ) ) {
			unset( $update_actions['themes_page'] );
		}

		$update_actions = apply_filters( 'update_bulk_theme_complete_actions', $update_actions, $this->theme_info );
		if ( ! empty( $update_actions ) ) {
			$this->feedback( implode( ' | ', (array) $update_actions ) );
		}

	}

}
