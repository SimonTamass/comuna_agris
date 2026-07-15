<?php
$root = dirname( __DIR__ );
$home_hero = file_get_contents( $root . '/includes/widgets/class-home-hero.php' );
$page_hero = file_get_contents( $root . '/includes/widgets/class-page-hero.php' );
$single = file_get_contents( $root . '/includes/widgets/class-single-post.php' );
$archive = file_get_contents( $root . '/includes/widgets/class-post-archive.php' );
$frontend = file_get_contents( $root . '/includes/class-frontend-templates.php' );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'page title band markup' => array( $page_hero, 'agris-page-hero agris-title-band' ),
	'single title band markup' => array( $single, '<header class="agris-title-band">' ),
	'archive title band markup' => array( $archive, 'agris-archive-header agris-title-band' ),
	'native fallback title band' => array( $frontend, 'agris-title-band-inner' ),
	'shared title background' => array( $css, '--agris-title-bg: #163c38;' ),
	'forced image-free title bands' => array( $css, '.agris-title-band { background: var(--agris-title-bg); background-image: none !important;' ),
	'full-width single title band' => array( $css, '.agris-single > .agris-title-band, .agris-archive-header.agris-title-band' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

foreach ( array( $home_hero, $page_hero ) as $hero ) {
	if ( str_contains( $hero, "add_control( 'background'" ) || str_contains( $hero, '--agris-hero-image' ) || str_contains( $hero, '--agris-page-image' ) ) {
		fwrite( STDERR, "A top hero still exposes a background image.\n" );
		exit( 1 );
	}
}

if ( str_contains( $applier, "'background' =>" ) || str_contains( $applier, "'background'     =>" ) || str_contains( $applier, "'background'    =>" ) ) {
	fwrite( STDERR, "The automatic Elementor rebuild still assigns a hero background image.\n" );
	exit( 1 );
}

if ( str_contains( $css, '--agris-hero-image' ) || str_contains( $css, '--agris-page-image' ) ) {
	fwrite( STDERR, "Legacy hero image CSS variables remain.\n" );
	exit( 1 );
}

echo "Title band smoke passed.\n";
