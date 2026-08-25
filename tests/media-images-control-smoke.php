<?php
$root        = dirname( __DIR__ );
$integration = file_get_contents( $root . '/includes/class-elementor.php' );
$control     = file_get_contents( $root . '/includes/controls/class-media-images.php' );
$javascript  = file_get_contents( $root . '/assets/js/editor-media-images-control.js' );
$editor_css  = file_get_contents( $root . '/assets/css/editor.css' );
$widget      = file_get_contents( $root . '/includes/widgets/class-post-template.php' );

$checks = array(
	'control registration hook' => array( $integration, "add_action( 'elementor/controls/register'" ),
	'control registration'      => array( $integration, 'new \\ComunaAgris\\Controls\\Media_Images()' ),
	'Elementor data control'    => array( $control, 'extends Base_Data_Control' ),
	'custom control type'       => array( $control, "return 'agris_media_images';" ),
	'WordPress media assets'    => array( $control, 'wp_enqueue_media();' ),
	'custom editor script'      => array( $control, 'editor-media-images-control.js' ),
	'direct media frame'        => array( $javascript, 'wp.media({' ),
	'image-only library'        => array( $javascript, "library: { type: 'image' }" ),
	'multiple selection'        => array( $javascript, 'multiple: true' ),
	'Elementor value update'    => array( $javascript, 'this.setValue(images);' ),
	'attachment ID storage'     => array( $javascript, 'id: Number(data.id)' ),
	'attachment URL storage'    => array( $javascript, "url: data.url || ''" ),
	'legacy gallery values'     => array( $javascript, 'item?.image || item || {}' ),
	'image control styling'     => array( $editor_css, '.agris-media-images-item' ),
	'widget control usage'      => array( $widget, "'type'        => 'agris_media_images'" ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "Media images control smoke passed.\n";
