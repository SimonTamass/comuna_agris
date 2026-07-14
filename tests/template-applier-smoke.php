<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
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

if ( preg_match( '/letter-spacing:\s*-/', $css ) || ! str_contains( $css, 'font-size: 16px; line-height: 1.55' ) ) {
	fwrite( STDERR, "Typography contract does not match the local redesign.\n" );
	exit( 1 );
}

if ( ! str_contains( $plugin, "AGRIS_WIDGETS_VERSION', '1.4.2'" ) ) {
	fwrite( STDERR, "Plugin version was not bumped.\n" );
	exit( 1 );
}

if ( ! str_contains( $applier, 'function normalize_legacy_content' ) || ! str_contains( $applier, 'function gallery_items' ) ) {
	fwrite( STDERR, "Legacy content conversion is incomplete.\n" );
	exit( 1 );
}

if ( str_contains( $applier, "'post_content' => ''" ) ) {
	fwrite( STDERR, "Bulk rebuild must preserve legacy post content.\n" );
	exit( 1 );
}

echo "Template applier smoke passed.\n";
