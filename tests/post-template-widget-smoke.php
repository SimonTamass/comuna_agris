<?php
$root     = dirname( __DIR__ );
$widget   = file_get_contents( $root . '/includes/widgets/class-post-template.php' );
$registry = file_get_contents( $root . '/includes/class-widget-registry.php' );
$css      = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'widget registration'       => array( $registry, "'Post_Template'       => 'post-template'" ),
	'unique widget name'        => array( $widget, "return 'agris-post-template';" ),
	'article title control'     => array( $widget, "'title'" ),
	'editable rich content'     => array( $widget, 'Controls_Manager::WYSIWYG' ),
	'gallery repeater'          => array( $widget, "'gallery_items'" ),
	'accessible lightbox group' => array( $widget, 'data-agris-lightbox-group' ),
	'document repeater'         => array( $widget, "'document_items'" ),
	'download behavior'         => array( $widget, "\$attrs .= ' download'" ),
	'responsive article shell'  => array( $css, '.agris-post-template {' ),
	'featured gallery layout'   => array( $css, '.agris-post-template-gallery.has-featured-image' ),
	'document card layout'      => array( $css, '.agris-post-template-document {' ),
	'mobile gallery reset'      => array( $css, '.agris-post-template-gallery-item:first-child' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "Post template widget smoke passed.\n";
