<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
$section_heading = file_get_contents( $root . '/includes/widgets/class-section-heading.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );
$plugin = file_get_contents( $root . '/comuna-agris-elementor.php' );

$checks = array(
	'all-pages admin action' => "admin_post_agris_apply_all",
	'all-pages handler'      => 'function handle_apply_all',
	'generic page builder'   => 'function generic_page_data',
	'original source backup' => "SOURCE_META",
	'URL rollback guard'     => "new \\WP_Error( 'url_changed'",
);

foreach ( $checks as $label => $needle ) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( ! str_contains( $assets, "'agris-fonts'" ) || ! str_contains( $assets, 'fonts.googleapis.com/css2?family=Sora' ) ) {
	fwrite( STDERR, "Font registration is incomplete.\n" );
	exit( 1 );
}

if ( ! str_contains( $assets, "add_filter( 'body_class'" ) || ! str_contains( $assets, "'agris-home-page'" ) ) {
	fwrite( STDERR, "Homepage body-class scoping is incomplete.\n" );
	exit( 1 );
}

if ( ! str_contains( $section_heading, "'theme'" ) || ! str_contains( $section_heading, "' is-dark'" ) ) {
	fwrite( STDERR, "Section heading dark variant is incomplete.\n" );
	exit( 1 );
}

if ( preg_match( '/letter-spacing:\s*-/', $css ) || ! str_contains( $css, 'font-size: 16px; line-height: 1.55' ) ) {
	fwrite( STDERR, "Typography contract does not match the local redesign.\n" );
	exit( 1 );
}

if ( ! str_contains( $plugin, "AGRIS_WIDGETS_VERSION', '1.9.0'" ) ) {
	fwrite( STDERR, "Plugin version was not bumped.\n" );
	exit( 1 );
}

if ( ! str_contains( $applier, 'function normalize_legacy_content' ) || ! str_contains( $applier, 'function gallery_items' ) ) {
	fwrite( STDERR, "Legacy content conversion is incomplete.\n" );
	exit( 1 );
}

foreach ( array( 'Servicii publice transparente pentru Comuna Agriș.', 'Noutăți din portal', 'Anunțuri oficiale', 'Hotărâri recente', 'Istorie, cultură și natură în inima județului Satu Mare', 'Documente publice într-un singur loc', 'Guvernare transparentă, deschisă și participativă', "'category'     => 'anunturi'", "'title' => 'Stare civilă'", "'title' => 'Asistență socială'", "'title' => 'Legea 17'", "'title' => 'APIA'", 'function expand_legacy_link_shortcodes', 'function legacy_post_queries' ) as $needle ) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Missing original-content parity contract: {$needle}.\n" );
		exit( 1 );
	}
}

foreach ( array( "'title'          => 'Bine ați venit'", "'reports-widget'", "'mayor-schedule'", "'mayor-cta'", "'documents-widget'", "'contact-form-widget'" ) as $needle ) {
	if ( str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Unrelated generated content remains: {$needle}.\n" );
		exit( 1 );
	}
}

$services_start = strpos( $applier, "'items_list' => \$this->repeater( 'services'" );
$services_end = false !== $services_start ? strpos( $applier, "\n\t\t\t\t\t\t\t) ),", $services_start ) : false;
$services_block = false !== $services_start && false !== $services_end ? substr( $applier, $services_start, $services_end - $services_start ) : '';
if ( 8 !== substr_count( $services_block, "array( 'icon' =>" ) ) {
	fwrite( STDERR, "Homepage must preserve exactly eight frequent-service cards.\n" );
	exit( 1 );
}

foreach ( array( "'theme'       => \$theme", "'background_color' => \$background", "'monitor-services-widget'", "'show_search'    => 'yes'", "'count'        => 3" ) as $needle ) {
	if ( ! str_contains( $applier, $needle ) ) {
		fwrite( STDERR, "Homepage local-design structure is incomplete: {$needle}.\n" );
		exit( 1 );
	}
}

foreach ( array( '.agris-home-page .agris-content-media', '.agris-home-page .agris-cta', '.agris-home-page .agris-news-image' ) as $needle ) {
	if ( ! str_contains( $css, $needle ) ) {
		fwrite( STDERR, "Homepage local-design styling is incomplete: {$needle}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $applier, "'post_content' => ''" ) ) {
	fwrite( STDERR, "Bulk rebuild must preserve legacy post content.\n" );
	exit( 1 );
}

echo "Template applier smoke passed.\n";
