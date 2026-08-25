<?php

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ );
	$fixture_language = 'ro';

	function __( string $text, string $domain = '' ): string { return $text; }
	function get_the_ID(): int { return 8580; }
	function pll_get_post_language( int $post_id, string $field = 'slug' ): string {
		global $fixture_language;
		return 8580 === $post_id ? $fixture_language : '';
	}
	function pll_current_language( string $field = 'slug' ): string { return 'hu'; }
	function determine_locale(): string { return 'hu_HU'; }
}

namespace Elementor {
	abstract class Widget_Base {}
	final class Controls_Manager {}
	final class Group_Control_Typography {
		public static function get_type(): string { return 'typography'; }
	}
}

namespace {
	$root = dirname( __DIR__ );
	require_once $root . '/includes/widgets/class-base.php';
	require_once $root . '/includes/widgets/class-post-template.php';

	$reflection       = new \ReflectionClass( \ComunaAgris\Widgets\Post_Template::class );
	$widget           = $reflection->newInstanceWithoutConstructor();
	$language_method  = $reflection->getMethod( 'content_language' );
	$localized_method = $reflection->getMethod( 'localized_setting' );
	$text_method      = $reflection->getMethod( 'translations' );

	if ( 'ro' !== $language_method->invoke( $widget, array() ) ) {
		throw new \RuntimeException( 'The Romanian Polylang post language was not detected.' );
	}

	$romanian_texts = $text_method->invoke( $widget, 'ro' );
	if ( 'Galerie foto' !== $romanian_texts['gallery_title'] || 'Documente descărcabile' !== $romanian_texts['documents_title'] || 'Descarcă' !== $romanian_texts['download_label'] || '%d imagini' !== $romanian_texts['image_count_many'] ) {
		throw new \RuntimeException( 'The Romanian widget labels are incomplete.' );
	}

	$localized_gallery = $localized_method->invoke( $widget, array( 'gallery_title' => 'Képgaléria' ), 'gallery_title', 'ro' );
	if ( 'Galerie foto' !== $localized_gallery ) {
		throw new \RuntimeException( 'A saved Hungarian default was not localized for the Romanian post.' );
	}

	$custom_gallery = $localized_method->invoke( $widget, array( 'gallery_title' => 'Fotografii de la eveniment' ), 'gallery_title', 'ro' );
	if ( 'Fotografii de la eveniment' !== $custom_gallery ) {
		throw new \RuntimeException( 'Custom Romanian content was overwritten.' );
	}

	$fixture_language = 'hu';
	if ( 'hu' !== $language_method->invoke( $widget, array() ) ) {
		throw new \RuntimeException( 'The Hungarian Polylang post language was not detected.' );
	}

	$hungarian_texts = $text_method->invoke( $widget, 'hu' );
	if ( 'Képgaléria' !== $hungarian_texts['gallery_title'] || 'Letölthető dokumentumok' !== $hungarian_texts['documents_title'] || 'Letöltés' !== $hungarian_texts['download_label'] ) {
		throw new \RuntimeException( 'The Hungarian widget labels are incomplete.' );
	}

	if ( 'ro' !== $language_method->invoke( $widget, array( 'content_language' => 'ro' ) ) ) {
		throw new \RuntimeException( 'The explicit language override was ignored.' );
	}

	echo "Post template language behavior passed.\n";
}
