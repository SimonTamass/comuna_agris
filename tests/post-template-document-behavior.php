<?php

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ );

	function __( string $text, string $domain = '' ): string { return $text; }
	function wp_get_attachment_url( int $id ): string|false { return 42 === $id ? 'https://example.test/uploads/budget-2026.docx' : false; }
	function get_attached_file( int $id ): string|false { return 42 === $id ? '/uploads/budget-2026.docx' : false; }
	function get_the_title( int $id ): string { return 42 === $id ? 'Budget 2026' : ''; }
	function get_post_mime_type( int $id ): string|false { return 42 === $id ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : false; }
	function wp_get_attachment_metadata( int $id ): array|false { return 42 === $id ? array( 'filesize' => 2097152 ) : false; }
	function wp_filesize( string $file ): int|false { return false; }
	function wp_parse_url( string $url, int $component = -1 ): string|array|false|null { return parse_url( $url, $component ); }
	function wp_basename( string $path ): string { return basename( $path ); }
	function size_format( int $bytes ): string { return 2097152 === $bytes ? '2 MB' : (string) $bytes; }
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

	$reflection = new \ReflectionClass( \ComunaAgris\Widgets\Post_Template::class );
	$widget     = $reflection->newInstanceWithoutConstructor();
	$method     = $reflection->getMethod( 'document_data' );

	$docx = $method->invoke(
		$widget,
		array(
			'id'       => 42,
			'url'      => 'https://old.example.test/file.docx',
			'title'    => 'Budget 2026',
			'filename' => 'budget-2026.docx',
			'mime'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		)
	);

	if ( 'DOCX' !== $docx['type'] || 'Budget 2026' !== $docx['title'] || 'https://example.test/uploads/budget-2026.docx' !== $docx['link']['url'] || 'DOCX · 2 MB' !== $docx['meta'] ) {
		throw new \RuntimeException( 'DOCX attachment metadata was not resolved correctly.' );
	}

	$image = $method->invoke(
		$widget,
		array(
			'url'      => 'https://example.test/uploads/meeting-photo.jpg?download=1',
			'filename' => 'meeting-photo.jpg',
			'mime'     => 'image/jpeg',
		)
	);

	if ( 'JPG' !== $image['type'] || 'meeting-photo' !== $image['title'] || 'https://example.test/uploads/meeting-photo.jpg?download=1' !== $image['link']['url'] ) {
		throw new \RuntimeException( 'Image attachment metadata was not resolved correctly.' );
	}

	$legacy = $method->invoke(
		$widget,
		array(
			'title'    => 'Korábbi határozat',
			'file_url' => array( 'url' => 'https://example.test/uploads/hatarozat.pdf' ),
		)
	);

	if ( 'PDF' !== $legacy['type'] || 'Korábbi határozat' !== $legacy['title'] || 'https://example.test/uploads/hatarozat.pdf' !== $legacy['link']['url'] ) {
		throw new \RuntimeException( 'Legacy document data was not preserved.' );
	}

	echo "Post template document behavior passed.\n";
}
