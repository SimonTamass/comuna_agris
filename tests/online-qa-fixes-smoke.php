<?php
$root = dirname( __DIR__ );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$form = file_get_contents( $root . '/includes/widgets/class-contact-form.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'Legea 17 category route' => array( $applier, "/ro/category/legea17/" ),
	'2024 sale category route' => array( $applier, "/ro/category/legea17/oferta-de-vanzare-2024/" ),
	'APIA category route' => array( $applier, "/ro/category/apia/" ),
	'2024 budget category route' => array( $applier, "/ro/category/buget/buget-2024/" ),
	'2023 budget category route' => array( $applier, "/ro/category/buget/buget-2023/" ),
	'gallery category route' => array( $applier, "/ro/category/galeria-foto/" ),
	'executive decisions route' => array( $applier, "/ro/dispozitii-autoritatii-executive/" ),
	'meeting minutes route' => array( $applier, "/ro/procese-verbale-ale-sedintelor-autoritatiilor-deliberative/" ),
	'direct child-benefit PDF' => array( $applier, "/wp-content/uploads/2016/11/acordarea-indemizatiei-de-crestere-a-copilului.pdf" ),
	'full-bleed overflow containment' => array( $css, '.agris-global-main { overflow-x: hidden; overflow-x: clip; }' ),
	'contact POST fallback' => array( $form, 'method="post"' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

$forbidden = array(
	"home_url( '/ro/legea-17/' )",
	"home_url( '/ro/oferta-de-vanzare-2024/' )",
	"home_url( '/ro/apia/' )",
	"home_url( '/ro/buget-2024/' )",
	"home_url( '/ro/buget-2023/' )",
	"home_url( '/ro/galeria-foto/' )",
	"home_url( '/ro/dispozitii-autoritatea-executiva/' )",
	"home_url( '/ro/procese-verbale-ale-sedintelor-autoritatilor-deliberative/' )",
);

foreach ( $forbidden as $route ) {
	if ( str_contains( $applier, $route ) ) {
		fwrite( STDERR, "Legacy broken route remains: {$route}.\n" );
		exit( 1 );
	}
}

echo "Online QA fixes smoke passed.\n";
