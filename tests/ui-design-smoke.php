<?php
$root = dirname( __DIR__ );
$css = file_get_contents( $root . '/assets/css/frontend.css' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );

$checks = array(
	'search input focus'           => '.agris-search-widget .agris-search-dialog input:focus',
	'search close focus'           => '.agris-search-widget .agris-search-dialog > button:hover, .agris-search-widget .agris-search-dialog > button:focus',
	'header icon focus'            => '.agris-header-actions button.agris-icon-button:hover, .agris-header-actions button.agris-icon-button:focus',
	'floating control focus'       => '.agris-a11y .agris-floating button:first-child:hover, .agris-a11y .agris-floating button:first-child:focus',
	'accessibility switch focus'   => '.agris-a11y button.agris-switch.is-on:focus',
	'language control focus'       => '.agris-header-wrap button.agris-lang-trigger:hover, .agris-header-wrap button.agris-lang-trigger:focus',
	'hero search focus'            => '.agris-home-hero .agris-hero-search input:focus',
	'document filter focus'        => '.agris-filters button.is-active, .agris-filters button:hover, .agris-filters button:focus',
	'lightbox control focus'       => '.agris-lightbox button.agris-lightbox-close:hover',
	'keyboard focus ring'          => '.agris-button:focus-visible',
	'modal border-box sizing'      => '.agris-search-dialog { box-sizing: border-box;',
	'mobile modal spacing'         => '.agris-search-widget.is-modal { padding: max(20px,6vh) 14px 14px; }',
	'compact navigation css'       => '@media (max-width: 1320px)',
	'compact navigation behavior'  => "window.matchMedia('(max-width: 1320px)')",
	'closed nested menu exclusion' => '.agris-menu .sub-menu .sub-menu { display: none; }',
	'landscape a11y containment'    => '.agris-a11y-panel { max-height: calc(100vh - 110px); max-height: calc(100dvh - 110px); overflow-y: auto; overscroll-behavior: contain; }',
);

foreach ( $checks as $label => $needle ) {
	$haystack = 'compact navigation behavior' === $label ? $js : $css;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "UI design smoke passed.\n";
