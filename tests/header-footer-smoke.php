<?php
$root = dirname( __DIR__ );
$assets = file_get_contents( $root . '/includes/class-assets.php' );
$header = file_get_contents( $root . '/includes/widgets/class-site-header.php' );
$footer = file_get_contents( $root . '/includes/widgets/class-site-footer.php' );
$applier = file_get_contents( $root . '/includes/class-template-applier.php' );
$css = file_get_contents( $root . '/assets/css/frontend.css' );
$js = file_get_contents( $root . '/assets/js/frontend.js' );

$checks = array(
	'dashicons dependency'        => array( $assets, "array( 'agris-fonts', 'dashicons' )" ),
	'accessible menu connection' => array( $header, 'aria-controls="<?php echo esc_attr( $nav_id ); ?>"' ),
	'accessible submenu walker'  => array( $header, 'class Header_Menu_Walker' ),
	'submenu toggle control'     => array( $header, 'data-agris-submenu-toggle' ),
	'four-level menu rendering'  => array( $header, "'depth' => 4" ),
	'search icon'                => array( $header, 'dashicons-search' ),
	'menu icon'                  => array( $header, 'dashicons-menu-alt3' ),
	'language flags'             => array( $header, 'agris-flag-<?php echo esc_attr' ),
	'footer contact link'        => array( $footer, "'contact_url'" ),
	'footer monitor link'        => array( $footer, "'monitor_url'" ),
	'footer utility navigation'  => array( $footer, 'Linkuri subsol' ),
	'footer back-to-top icon'    => array( $footer, 'dashicons-arrow-up-alt2' ),
	'dynamic footer routes'      => array( $applier, "'contact_url' => \$this->link( \$routes['contact'] )" ),
	'header height'              => array( $css, 'min-height: 78px;' ),
	'local footer grid'          => array( $css, 'grid-template-columns: 1.2fr repeat(3, 1fr);' ),
	'deduplicated language item' => array( $css, '.agris-menu > .lang-item { display: none; }' ),
	'theme-safe menu sizing'     => array( $css, '.agris-menu a { box-sizing: border-box; }' ),
	'local mobile breakpoint'    => array( $css, '@media (max-width: 1040px)' ),
	'hover bridge'               => array( $css, '.agris-menu .sub-menu::before' ),
	'nested desktop flyout'      => array( $css, '.agris-menu .sub-menu .sub-menu' ),
	'nested overflow reversal'   => array( $css, '.menu-item-has-children.opens-left > .sub-menu' ),
	'mobile accordion'           => array( $css, '.agris-menu li.is-submenu-open > .sub-menu { display: grid; }' ),
	'all-level menu discovery'   => array( $js, "all('.agris-menu .menu-item-has-children', header)" ),
	'forgiving close delay'      => array( $js, 'window.setTimeout(() => closeSubmenu(item), 220)' ),
	'keyboard submenu opening'   => array( $js, "['ArrowDown', 'ArrowUp', 'ArrowRight', 'ArrowLeft']" ),
	'escape focus restoration'   => array( $js, 'closeSubmenu(openItem, true)' ),
);

foreach ( $checks as $label => $check ) {
	list( $haystack, $needle ) = $check;
	if ( ! str_contains( $haystack, $needle ) ) {
		fwrite( STDERR, "Missing {$label}.\n" );
		exit( 1 );
	}
}

echo "Header and footer smoke passed.\n";
