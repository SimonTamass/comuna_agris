<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$form = file_get_contents( $root . '/includes/widgets/class-contact-form.php' );
$details = file_get_contents( $root . '/includes/widgets/class-contact-details.php' );
$plugin = file_get_contents( $root . '/includes/class-plugin.php' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );

$checks = array(
	'contact page recognition' => array( $applier, "preg_match( '/contact|elerhet/'" ),
	'contact details placement' => array( $applier, "'agris-contact-details'" ),
	'contact form placement' => array( $applier, "'agris-contact-form'" ),
	'Romanian contact copy' => array( $applier, "'Trimiteți-ne un mesaj'" ),
	'Hungarian contact copy' => array( $applier, "'Írjon nekünk'" ),
	'embedded office map' => array( $applier, '47.8816707,23.0048293' ),
	'preserved fax control' => array( $details, "add_control( 'fax'" ),
	'localized field labels' => array( $form, "add_control( 'name_label'" ),
	'contact language field' => array( $form, 'name="language"' ),
	'localized server response' => array( $plugin, "'hu' === \$language" ),
	'client-side form initialization' => array( $js, "all('.agris-contact-form:not([data-ready])'" ),
	'native validity check' => array( $js, 'form.reportValidity()' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "Contact page smoke passed.\n";
