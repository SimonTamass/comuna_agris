<?php
$root = dirname( __DIR__ );
$gallery = file_get_contents( $root . '/includes/widgets/class-photo-gallery.php' );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'gallery trigger'        => array( $gallery, 'data-agris-lightbox' ),
	'gallery grouping'       => array( $gallery, 'data-agris-lightbox-group' ),
	'localized open label'   => array( $assets, "'openImage'" ),
	'localized close label'  => array( $assets, "'closeLightbox'" ),
	'lightbox initializer'   => array( $js, 'function initLightbox' ),
	'article image support'  => array( $js, '.agris-single-content img' ),
	'legacy gallery support' => array( $js, '.agris-legacy-gallery img' ),
	'keyboard navigation'    => array( $js, "event.key === 'ArrowLeft'" ),
	'focus restoration'      => array( $js, 'lightboxPreviousFocus?.focus()' ),
	'lightbox overlay'       => array( $css, '.agris-lightbox {' ),
	'mobile lightbox layout' => array( $css, '.agris-lightbox-nav {' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $gallery, 'data-elementor-open-lightbox' ) ) {
	fwrite( STDERR, "Gallery still invokes a second Elementor lightbox.\n" );
	exit( 1 );
}

echo "Lightbox smoke passed.\n";
