<?php
// Get the page id/alias
$alias = $_GET['page'];
global $page_options, ${$page_options[ $alias ]['object']}, ${$page_options[ $alias ]['admin_object']}, $libraries;

$object       = ${$page_options[ $alias ]['object']};
$admin_object = ${$page_options[ $alias ]['admin_object']};
$form_builder = $libraries['FormsBuilder'];
$current      = $page_options[ $alias ]['builder_page'];

$form_builder->render_form( $current, true, $object, $admin_object );
