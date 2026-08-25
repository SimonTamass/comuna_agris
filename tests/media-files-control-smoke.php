<?php
$root        = dirname( __DIR__ );
$integration = file_get_contents( $root . '/includes/class-elementor.php' );
$control     = file_get_contents( $root . '/includes/controls/class-media-files.php' );
$javascript  = file_get_contents( $root . '/assets/js/editor-media-files-control.js' );
$editor_css  = file_get_contents( $root . '/assets/css/editor.css' );
$widget      = file_get_contents( $root . '/includes/widgets/class-post-template.php' );

$checks = array(
	'control registration hook' => array( $integration, "add_action( 'elementor/controls/register'" ),
	'control registration'      => array( $integration, 'new \\ComunaAgris\\Controls\\Media_Files()' ),
	'Elementor data control'    => array( $control, 'extends Base_Data_Control' ),
	'custom control type'       => array( $control, "return 'agris_media_files';" ),
	'WordPress media assets'    => array( $control, 'wp_enqueue_media();' ),
	'custom editor script'      => array( $control, 'editor-media-files-control.js' ),
	'generic media frame'       => array( $javascript, 'wp.media({' ),
	'multiple selection'        => array( $javascript, 'multiple: true' ),
	'Elementor value update'    => array( $javascript, 'this.setValue(files);' ),
	'attachment ID storage'     => array( $javascript, 'id: Number(data.id)' ),
	'attachment URL storage'    => array( $javascript, "url: data.url || ''" ),
	'attachment MIME storage'   => array( $javascript, "mime: data.mime || ''" ),
	'file control styling'      => array( $editor_css, '.agris-media-files-item' ),
	'widget control usage'      => array( $widget, "'type'        => 'agris_media_files'" ),
	'legacy URL support'        => array( $widget, "\$item['file_url']['url']" ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( preg_match( '/library\s*:\s*\{[^}]*type\s*:/s', $javascript ) ) {
	fwrite( STDERR, "The media frame must not filter out non-image attachment types.\n" );
	exit( 1 );
}

echo "Media files control smoke passed.\n";
