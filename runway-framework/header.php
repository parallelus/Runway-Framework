<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
	<title><?php wp_title( '|', true, 'right' ); ?></title>

	<!-- Favorites and mobile bookmark icons -->
	<link rel="shortcut icon" href="<?php get_stylesheet_directory_uri(); ?>/favicon.ico">
	<link rel="apple-touch-icon-precomposed" href="<?php get_stylesheet_directory_uri(); ?>/apple-touch-icon.png">

	<!-- WP headers -->
	<?php wp_head(); ?>

	<!-- Feed and pingback links -->
	<link rel="alternate" type="application/atom+xml" title="<?php bloginfo( 'name' ); ?> Atom Feed" href="<?php bloginfo( 'atom_url' ); ?>">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

</head>
<body <?php body_class(); ?>>
