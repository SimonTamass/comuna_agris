<?php
define( 'ABSPATH', __DIR__ );

function wp_strip_all_tags( string $content ): string {
	return strip_tags( $content );
}

function sanitize_title( string $title ): string {
	$title = strtolower( trim( $title ) );
	return preg_replace( '/[^a-z0-9-]+/', '-', $title );
}

require_once dirname( __DIR__ ) . '/includes/class-template-applier.php';

$reflection = new ReflectionClass( '\ComunaAgris\Template_Applier' );
$applier = $reflection->newInstanceWithoutConstructor();
$normalize = $reflection->getMethod( 'normalize_legacy_content' );
$category = $reflection->getMethod( 'legacy_category_slug' );

$legacy = '[vc_row][vc_column_text]<h1>Galeria Foto</h1><p>Text păstrat.</p>[/vc_column_text][masonry_blog order="DESC" category="galeria-foto-2018"][/vc_row]';
$normalized = $normalize->invoke( $applier, $legacy );

if ( str_contains( $normalized, '[' ) || ! str_contains( $normalized, '<h2>Galeria Foto</h2>' ) || ! str_contains( $normalized, 'Text păstrat.' ) ) {
	fwrite( STDERR, "Legacy layout shortcode normalization failed.\n" );
	exit( 1 );
}

if ( 'galeria-foto-2018' !== $category->invoke( $applier, $legacy ) ) {
	fwrite( STDERR, "Legacy masonry category detection failed.\n" );
	exit( 1 );
}

$nested = '<div class="agris-header-wrap">Duplicated header</div><div class="agris-richtext"><div class="agris-richtext"><p>Conținut real.</p></div></div><footer class="agris-footer">Duplicated footer</footer>';
$normalized_nested = $normalize->invoke( $applier, $nested );
if ( str_contains( $normalized_nested, 'Duplicated header' ) || str_contains( $normalized_nested, 'Duplicated footer' ) || ! str_contains( $normalized_nested, 'Conținut real.' ) ) {
	fwrite( STDERR, "Nested Elementor content extraction failed.\n" );
	exit( 1 );
}

echo "Legacy content smoke passed.\n";
