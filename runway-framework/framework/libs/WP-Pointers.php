<?php


//-----------------------------------------------------------------
// Examples
//-----------------------------------------------------------------

// for #menu-comments element on all pages
//-----------------------------------------------------------------
// WP_Pointers::add_pointer( 'all', '#menu-comments', array( 'title' => '!!!', 'body' => '!!!123' ), 'edge: "left", align: "center"' );

// for #menu-pages element only on extensions page
//-----------------------------------------------------------------
// WP_Pointers::add_pointer( 'extensions', '#menu-pages', array( 'title' => '!!!', 'body' => '!!!123' ), 'edge: "left", align: "center"' );

// same but using class name to assign pointer
//-----------------------------------------------------------------
// WP_Pointers::add_pointer( 'all', '.adminTitle', '!!!123', 'edge: "top", align: "left"' );

// WP_Pointers::add_pointer( 'all', '.adminTitle', '!!!123', 'edge: "top", align: "left"' );

class WP_Pointers {

	private static $pointers = array();
	private static $first_time = true;

	public function __construct() {

		//Appearance > Menus : load additional styles and scripts
		add_action( 'admin_init', array( $this , 'add_styles_and_scripts' ) );

		// set handlers
		add_action( 'admin_footer', array( $this, 'render' ) );

		// set vars
		$this->page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : '';

	}

	public function add_styles_and_scripts() {
		// css / js
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
	}

	public function render() {

		if ( self::$first_time ) {
			self::$first_time = false;
		} else {
			return false;
		}

		// Quick error checking (to avoid notices)
		$thisPage = ( isset( self::$pointers[$this->page] ) && is_array( self::$pointers[$this->page] ) ) ? self::$pointers[$this->page] : array();
		$allPages = ( isset( self::$pointers['all'] ) && is_array( self::$pointers['all'] ) ) ? self::$pointers['all'] : array();

		// select pointers assigned as 'all' and for current page
		$pointers = array_merge( (array) $allPages, (array) $thisPage );

		// generating javascript
		$javascript = '';
		// $javascript .= '<script type="text/javascript">';
		$javascript .= 'jQuery(document).ready(function () {';
		$javascript .= 'if(typeof(jQuery().pointer) != "undefined") {';
		foreach ( $pointers as $pointer ) {
			// check if this pointer already declined by user
			if ( !in_array( $pointer['name'], explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) ) ) ) {
				// add pointer
				$javascript .= 'jQuery("'.$pointer['target'].'").pointer({';
				if ( is_array( $pointer['content'] ) ) {
					$javascript .= 'content: "<h3>' . $pointer['content']['title'] . '</h3>'. $pointer['content']['body'] . '"';
				} else {
					$javascript .= 'content: "' . $pointer['content'] . '"';
				}

				// set pointer position options
				if ( isset( $pointer['position'] ) ) {
					$javascript .= ', position: { ';
					$javascript .= $pointer['position'];
					$javascript .= ' }, ';
				}

				// set pointer dismiss call
				$javascript .= 'close: function() {';
				$javascript .= 'jQuery.post( ajaxurl, {';
				$javascript .= 'pointer: "'.$pointer['name'].'",';
				$javascript .= 'action: "dismiss-wp-pointer"';
				$javascript .= '})';
				$javascript .= '}}).pointer("open");';
			}
		}
		$javascript .= '}});';
		// $javascript .= '</script>';

		// render script
		echo '<script type="text/javascript">'. $javascript .'</script>';

	}

	public static function add_pointer( $page = 'all', $target = '', $content = '', $position = array() ) {

		// add pointer
		self::$pointers[$page][] =
			array(
			// set pointer name
			'name' => sanitize_title( $page.$target ),
			// target page element (any jQuery based syntax)
			'target' => $target,
			// content to display
			'content' => $content,
			// position options
			'position' => $position,
		);

	}

} ?>
