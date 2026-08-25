<?php
$root     = dirname( __DIR__ );
$widget   = file_get_contents( $root . '/includes/widgets/class-post-template.php' );
$registry = file_get_contents( $root . '/includes/class-widget-registry.php' );
$css      = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'widget registration'       => array( $registry, "'Post_Template'       => 'post-template'" ),
	'unique widget name'        => array( $widget, "return 'agris-post-template';" ),
	'article title control'     => array( $widget, "'title'" ),
	'title visibility switch'   => array( $widget, "'show_title_section'" ),
	'editable rich content'     => array( $widget, 'Controls_Manager::WYSIWYG' ),
	'multi-select gallery'      => array( $widget, "'type'        => 'agris_media_images'" ),
	'gallery control key'       => array( $widget, "'gallery_items'" ),
	'legacy gallery support'    => array( $widget, "! empty( \$item['image']['url'] )" ),
	'Media Library captions'    => array( $widget, 'wp_get_attachment_caption' ),
	'gallery visibility switch' => array( $widget, "'show_gallery'" ),
	'gallery lightbox switch'   => array( $widget, "'enable_gallery_lightbox'" ),
	'accessible lightbox group' => array( $widget, 'data-agris-lightbox-group' ),
	'multi-file document control' => array( $widget, "'type'        => 'agris_media_files'" ),
	'document control key'      => array( $widget, "'document_items'" ),
	'legacy document support'   => array( $widget, "! empty( \$item['file_url']['url'] )" ),
	'automatic filename'        => array( $widget, 'document_filename' ),
	'automatic file size'       => array( $widget, 'wp_filesize' ),
	'MIME type fallback'        => array( $widget, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ),
	'document visibility switch' => array( $widget, "'show_documents'" ),
	'download behavior'         => array( $widget, "\$attrs .= ' download'" ),
	'download accessibility'    => array( $widget, "'download_aria'" ),
	'automatic Polylang language' => array( $widget, "pll_get_post_language( \$post_id, 'slug' )" ),
	'language override control' => array( $widget, "'content_language'" ),
	'Romanian gallery label'    => array( $widget, "'gallery_title'       => 'Galerie foto'" ),
	'Hungarian gallery label'   => array( $widget, "'gallery_title'       => 'Képgaléria'" ),
	'responsive article shell'  => array( $css, '.agris-post-template {' ),
	'featured gallery layout'   => array( $css, '.agris-post-template-gallery.has-featured-image' ),
	'document card layout'      => array( $css, '.agris-post-template-document {' ),
	'mobile gallery reset'      => array( $css, '.agris-post-template-gallery-item:first-child' ),
);

foreach ( array( 'show_title_section', 'show_gallery', 'show_documents' ) as $control ) {
	if ( ! str_contains( $widget, "'yes' === ( \$settings['{$control}'] ?? 'yes' )" ) ) {
		fwrite( STDERR, "Missing enabled-by-default render guard for {$control}.\n" );
		exit( 1 );
	}
}

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "Post template widget smoke passed.\n";
