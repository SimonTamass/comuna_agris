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
	'editable home hero image' => array( $home_hero, "add_control( 'background'" ),
	'editable page hero image' => array( $page_hero, "add_control( 'background'" ),
	'home hero image variable' => array( $home_hero, '--agris-hero-image' ),
	'page hero image variable' => array( $page_hero, '--agris-page-image' ),
	'single title band markup' => array( $single, '<header class="agris-title-band">' ),
	'archive title band markup' => array( $archive, 'agris-archive-header agris-title-band' ),
	'native fallback title band' => array( $frontend, 'agris-title-band-inner' ),
	'shared title background' => array( $css, '--agris-title-bg: #163c38;' ),
	'image-free shared title bands' => array( $css, '.agris-title-band { background: var(--agris-title-bg); background-image: none !important;' ),
	'local homepage image composition' => array( $css, 'var(--agris-hero-image,none)' ),
	'local page image composition' => array( $css, 'var(--agris-page-image,none)' ),
	'full-width single title band' => array( $css, '.agris-single > .agris-title-band, .agris-archive-header.agris-title-band' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $applier, "'background' => \$this->design_media" ) && ! str_contains( $applier, "'background'     => \$hero_image" ) ) {
	fwrite( STDERR, "The automatic Elementor rebuild does not assign local hero imagery.\n" );
	exit( 1 );
}

echo "Title band smoke passed.\n";
