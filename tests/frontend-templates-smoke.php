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
	'Elementor widget factory' => array( $frontend, 'elements_manager->create_element_instance( $data )' ),
	'isolated widget failure handling' => array( $frontend, 'catch ( \\Throwable $error )' ),
	'functional WordPress fallback' => array( $frontend, 'render_native_content( array $copy, array $routes )' ),
	'transactional global renderer' => array( $frontend, 'function render_safely' ),
	'language-aware shared header' => array( $frontend, "'agris-site-header'" ),
	'explicit search language' => array( $frontend, "\$_GET['lang']" ),
	'explicit query language scope' => array( $frontend, "\$query->set( 'lang', \$language )" ),
	'localized HTML language' => array( $frontend, "'hu-HU' : 'ro-RO'" ),
	'language-aware shared footer' => array( $frontend, "'agris-site-footer'" ),
	'WordPress document shell' => array( $template, 'wp_head();' ),
	'safe template entrypoint' => array( $template, 'render_safely();' ),
	'localized archive labels' => array( $archive, "\$s['read_more_text']" ),
	'localized single labels' => array( $single, "\$settings['share_label']" ),
	'global template layout' => array( $css, '.agris-global-main' ),
);

foreach ( $checks as $label => $check ) {
	if ( ! str_contains( $check[0], $check[1] ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $frontend, "wp_enqueue_script( 'elementor-frontend'" ) ) {
	fwrite( STDERR, "Global templates must not enqueue an unconfigured Elementor frontend runtime.\n" );
	exit( 1 );
}

echo "Frontend templates smoke passed.\n";
