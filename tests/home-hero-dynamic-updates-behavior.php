<?php

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ );
	$fixture_query_args = array();
	$fixture_posts = array(
		(object) array( 'ID' => 30, 'title' => 'PV, Comunicare încheierii', 'date' => '2026-07-27', 'language' => 'ro' ),
		(object) array( 'ID' => 20, 'title' => 'ANUNȚ INDIVIDUAL', 'date' => '2026-07-07', 'language' => 'ro' ),
		(object) array( 'ID' => 10, 'title' => 'Anunț individual pentru comunicare', 'date' => '2026-07-03', 'language' => 'ro' ),
	);

	function __( string $text, string $domain = '' ): string { return $text; }
	function get_the_ID(): int { return 100; }
	function pll_get_post_language( int $post_id, string $field = 'slug' ): string {
		global $fixture_posts;
		if ( 100 === $post_id ) {
			return 'ro';
		}
		foreach ( $fixture_posts as $post ) {
			if ( $post->ID === $post_id ) {
				return $post->language;
			}
		}
		return '';
	}
	function pll_current_language( string $field = 'slug' ): string { return 'hu'; }
	function determine_locale(): string { return 'hu_HU'; }
	function get_posts( array $args ): array {
		global $fixture_query_args, $fixture_posts;
		$fixture_query_args = $args;
		return $fixture_posts;
	}
	function get_the_date( string $format, object $post ): string {
		return ( new \DateTimeImmutable( $post->date ) )->format( $format );
	}
	function get_the_title( object $post ): string { return $post->title; }
	function get_permalink( object $post ): string { return 'https://example.test/ro/post-' . $post->ID . '/'; }
}

namespace Elementor {
	abstract class Widget_Base {}
	final class Controls_Manager {}
	final class Repeater {}
	final class Group_Control_Typography {
		public static function get_type(): string { return 'typography'; }
	}
}

namespace {
	$root = dirname( __DIR__ );
	require_once $root . '/includes/widgets/class-base.php';
	require_once $root . '/includes/widgets/class-home-hero.php';

	$reflection = new \ReflectionClass( \ComunaAgris\Widgets\Home_Hero::class );
	$widget = $reflection->newInstanceWithoutConstructor();
	$updates_method = $reflection->getMethod( 'updates_for_display' );

	$updates = $updates_method->invoke( $widget, array( 'updates_source' => 'dynamic', 'updates_count' => 3 ) );
	if ( 3 !== count( $updates ) || 'PV, Comunicare încheierii' !== $updates[0]['title'] || '27' !== $updates[0]['day'] ) {
		throw new \RuntimeException( 'The newest Romanian post was not rendered first.' );
	}

	if ( 'ro' !== ( $fixture_query_args['lang'] ?? '' ) || 'date' !== ( $fixture_query_args['orderby'] ?? '' ) || 'DESC' !== ( $fixture_query_args['order'] ?? '' ) ) {
		throw new \RuntimeException( 'The dynamic update query is not date-ordered or language-aware.' );
	}

	if ( 'Publicat: 27.07.2026' !== $updates[0]['meta'] || 'https://example.test/ro/post-30/' !== $updates[0]['url']['url'] ) {
		throw new \RuntimeException( 'The Romanian update metadata or link is incorrect.' );
	}

	$manual = array( array( 'day' => '01', 'title' => 'Manual', 'meta' => 'Fix', 'url' => array( 'url' => '#' ) ) );
	$manual_updates = $updates_method->invoke( $widget, array( 'updates_source' => 'manual', 'updates_items' => $manual ) );
	if ( $manual !== $manual_updates ) {
		throw new \RuntimeException( 'The explicit manual update mode was not preserved.' );
	}

	echo "Home hero dynamic updates behavior passed.\n";
}
