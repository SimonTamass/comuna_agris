<?php
$root = dirname( __DIR__ );
$widget = file_get_contents( $root . '/includes/widgets/class-accessibility-tools.php' );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );

$checks = array(
	'panel heading layout'       => array( $widget, 'class="agris-a11y-heading"' ),
	'panel heading icon'         => array( $widget, 'class="agris-a11y-title-icon"' ),
	'floating accessibility icon' => array( $widget, 'class="agris-accessibility-icon"' ),
	'approved accessibility glyph' => array( $widget, 'M12 8v4.2M12 12.2l-2.5 7.3M12 12.2l2.5 7.3' ),
	'grouped scale controls'     => array( $widget, 'class="agris-scale-controls"' ),
	'decrease accessible name'  => array( $widget, "\$s['decrease_text_label']" ),
	'increase accessible name'  => array( $widget, "\$s['increase_text_label']" ),
	'contrast accessible name'  => array( $widget, "data-agris-a11y=\"contrast\" aria-label=\"<?php echo esc_attr( \$s['contrast_label'] ); ?>\"" ),
	'grayscale accessible name' => array( $widget, "data-agris-a11y=\"grayscale\" aria-label=\"<?php echo esc_attr( \$s['grayscale_label'] ); ?>\"" ),
	'underline accessible name' => array( $widget, "data-agris-a11y=\"underline\" aria-label=\"<?php echo esc_attr( \$s['underline_label'] ); ?>\"" ),
	'localized decrease label'  => array( $applier, "'decrease_text' => 'Micșorează textul'" ),
	'localized increase label'  => array( $applier, "'increase_text' => 'Mărește textul'" ),
	'heading badge styling'      => array( $css, '.agris-a11y-title-icon {' ),
	'floating icon styling'      => array( $css, '.agris-floating .agris-accessibility-icon' ),
	'grouped scale styling'      => array( $css, '.agris-scale-controls {' ),
	'preserved panel toggle'     => array( $js, "toggle?.addEventListener('click'" ),
	'preserved accessibility state' => array( $js, "localStorage.setItem('agris-a11y'" ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

if ( str_contains( $widget, '>A</button>' ) ) {
	fwrite( STDERR, "The floating accessibility control still uses the placeholder letter.\n" );
	exit( 1 );
}

echo "Accessibility design smoke passed.\n";
