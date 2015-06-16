<?php
// Get the page id/alias
$alias = $_GET['page'];
global $page_options, ${$page_options[$alias]['object']}, ${$page_options[$alias]['admin_object']}, $libraries;

$object = ${$page_options[$alias]['object']};
$admin_object = ${$page_options[$alias]['admin_object']};
$form_builder = $libraries['FormsBuilder'];

$current = $page_options[$alias]['builder_page'];
if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'save' ) {
	$link = admin_url('admin.php?page='.$alias);
   	echo '<script type="text/javascript">window.location = "'.esc_url($link).'";</script>';
}

$form_builder->render_form( $current, true, $object, $admin_object );
?>
