<?php
$root     = dirname( __DIR__ );
$plugin   = file_get_contents( $root . '/comuna-agris-elementor.php' );
$frontend = file_get_contents( $root . '/includes/class-frontend-templates.php' );
$template = file_get_contents( $root . '/templates/frontend-elementor.php' );

$checks = array(
	'Elementor editor preview bypass' => array( $frontend, "! isset( \$_GET['elementor-preview'] )" ),
	'plain global wrapper'            => array( $template, '<div class="elementor agris-global-elementor">' ),
	'Elementor compatibility header' => array( $plugin, 'Elementor tested up to: 4.2.3' ),
	'Pro compatibility header'       => array( $plugin, 'Elementor Pro tested up to: 4.2.2' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $template, 'data-elementor-type="agris-global"' ) || str_contains( $template, 'data-elementor-id' ) ) {
	fwrite( STDERR, "Global wrapper still registers a duplicate Elementor document.\n" );
	exit( 1 );
}

echo "Elementor editor compatibility smoke passed.\n";
