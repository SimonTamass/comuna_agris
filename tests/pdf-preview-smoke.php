<?php
$root = dirname( __DIR__ );
$widget = file_get_contents( $root . '/includes/widgets/class-post-template.php' );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
$javascript = file_get_contents( $root . '/assets/js/frontend.js' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );

$checks = array(
	'PDF-only type check' => array( $widget, "\$is_pdf   = 'PDF' === \$document['type'];" ),
	'preview visibility switch' => array( $widget, "'enable_pdf_preview'" ),
	'eye trigger' => array( $widget, 'data-agris-pdf-preview' ),
	'eye Dashicon' => array( $widget, 'dashicons-visibility' ),
	'Romanian preview label' => array( $widget, "'preview_label'       => 'Vizualizează'" ),
	'Hungarian preview label' => array( $widget, "'preview_label'       => 'Megtekintés'" ),
	'separate download link' => array( $widget, '<a class="agris-post-template-download"' ),
	'PDF viewer initializer' => array( $javascript, 'function initPdfPreview' ),
	'PDF viewer dialog' => array( $javascript, "pdfViewer.setAttribute('aria-modal', 'true')" ),
	'embedded PDF frame' => array( $javascript, 'data-agris-pdf-frame' ),
	'new-tab fallback' => array( $javascript, 'data-agris-pdf-new-tab' ),
	'Escape close support' => array( $javascript, "event.key === 'Escape'" ),
	'viewer initialization' => array( $javascript, 'initPdfPreview(root)' ),
	'localized viewer title' => array( $assets, "'pdfViewer'" ),
	'localized new-tab action' => array( $assets, "'openPdfNewTab'" ),
	'responsive modal styling' => array( $css, '.agris-pdf-viewer-dialog' ),
	'PDF card actions styling' => array( $css, '.agris-post-template-document-actions' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "PDF preview smoke passed.\n";
