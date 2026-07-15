<?php
$root = dirname( __DIR__ );
$plugin = file_get_contents( $root . '/includes/class-plugin.php' );
$frontend = file_get_contents( $root . '/includes/class-frontend-templates.php' );
$template = file_get_contents( $root . '/templates/frontend-elementor.php' );
$archive = file_get_contents( $root . '/includes/widgets/class-post-archive.php' );
$single = file_get_contents( $root . '/includes/widgets/class-single-post.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'frontend service registration' => array( $plugin, 'Frontend_Templates::instance()' ),
	'archive route coverage' => array( $frontend, 'is_archive() || is_home() || is_search()' ),
	'single route coverage' => array( $frontend, "is_singular( array( 'post', 'agris_document' ) )" ),
	'URL-preserving template filter' => array( $frontend, "add_filter( 'template_include'" ),
	'Elementor archive widget' => array( $frontend, "'agris-post-archive'" ),
	'Elementor single widget' => array( $frontend, "'agris-single-post'" ),
	'defensive widget loading' => array( $frontend, "require_once AGRIS_WIDGETS_PATH . 'includes/widgets/class-'" ),
	'language-aware shared header' => array( $frontend, "'agris-site-header'" ),
	'language-aware shared footer' => array( $frontend, "'agris-site-footer'" ),
	'WordPress document shell' => array( $template, 'wp_head();' ),
	'localized archive labels' => array( $archive, "\$s['read_more_text']" ),
	'localized single labels' => array( $single, "\$s['share_label']" ),
	'global template layout' => array( $css, '.agris-global-main' ),
);

foreach ( $checks as $label => $check ) {
	if ( ! str_contains( $check[0], $check[1] ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "Frontend templates smoke passed.\n";
