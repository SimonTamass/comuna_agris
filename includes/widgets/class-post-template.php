<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A self-contained, manually editable article layout for Elementor pages.
 */
final class Post_Template extends Base {
	public function get_name(): string {
		return 'agris-post-template';
	}

	public function get_title(): string {
		return __( '25 · Bejegyzés sablon', 'comuna-agris' );
	}

	public function get_icon(): string {
		return 'eicon-post-content';
	}

	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'bejegyzés', 'sablon', 'galéria', 'letöltés', 'dokumentum' ) );
	}

	protected function register_controls(): void {
		$this->register_article_controls();
		$this->register_gallery_controls();
		$this->register_document_controls();
		$this->register_common_style_controls();
	}

	private function register_article_controls(): void {
		$this->start_controls_section(
			'article',
			array(
				'label' => __( 'Cím és tartalom', 'comuna-agris' ),
			)
		);

		$this->add_control(
			'show_title_section',
			array(
				'label'        => __( 'Címes rész megjelenítése', 'comuna-agris' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Be', 'comuna-agris' ),
				'label_off'    => __( 'Ki', 'comuna-agris' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'kicker',
			array(
				'label'       => __( 'Címke a cím felett', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Hírek és közlemények', 'comuna-agris' ),
				'label_block' => true,
				'condition'   => array( 'show_title_section' => 'yes' ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'       => __( 'Bejegyzés címe', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'A bejegyzés címe', 'comuna-agris' ),
				'label_block' => true,
				'condition'   => array( 'show_title_section' => 'yes' ),
			)
		);
		$this->add_control(
			'intro',
			array(
				'label'     => __( 'Rövid bevezető', 'comuna-agris' ),
				'type'      => Controls_Manager::TEXTAREA,
				'default'   => __( 'Itt röviden összefoglalhatja a bejegyzés legfontosabb információit.', 'comuna-agris' ),
				'rows'      => 4,
				'condition' => array( 'show_title_section' => 'yes' ),
			)
		);
		$this->add_control(
			'content',
			array(
				'label'   => __( 'Bejegyzés szövege', 'comuna-agris' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => '<p>' . __( 'Adja hozzá itt a bejegyzés részletes tartalmát. A galéria és a letölthető dokumentumok külön szakaszban jelennek meg alatta.', 'comuna-agris' ) . '</p>',
			)
		);

		$this->end_controls_section();
	}

	private function register_gallery_controls(): void {
		$this->start_controls_section(
			'gallery',
			array(
				'label' => __( 'Képgaléria', 'comuna-agris' ),
			)
		);

		$this->add_control(
			'show_gallery',
			array(
				'label'        => __( 'Galéria megjelenítése', 'comuna-agris' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Be', 'comuna-agris' ),
				'label_off'    => __( 'Ki', 'comuna-agris' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'gallery_title',
			array(
				'label'       => __( 'Szakasz címe', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Képgaléria', 'comuna-agris' ),
				'label_block' => true,
				'condition'   => array( 'show_gallery' => 'yes' ),
			)
		);

		$this->add_control(
			'gallery_items',
			array(
				'label'       => __( 'Galéria képei', 'comuna-agris' ),
				'type'        => Controls_Manager::GALLERY,
				'default'     => array(),
				'description' => __( 'Egyszerre több képet is kijelölhet a Médiatárban, majd húzással rendezheti a sorrendjüket.', 'comuna-agris' ),
				'condition'   => array( 'show_gallery' => 'yes' ),
			)
		);
		$this->add_control(
			'gallery_columns',
			array(
				'label'   => __( 'Oszlopok', 'comuna-agris' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'2' => '2',
					'3' => '3',
				),
				'default'   => '3',
				'condition' => array( 'show_gallery' => 'yes' ),
			)
		);
		$this->add_control(
			'highlight_first_image',
			array(
				'label'        => __( 'Első kép kiemelése', 'comuna-agris' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_gallery' => 'yes' ),
			)
		);
		$this->add_control(
			'enable_gallery_lightbox',
			array(
				'label'        => __( 'Lightboxos képnézegető', 'comuna-agris' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Be', 'comuna-agris' ),
				'label_off'    => __( 'Ki', 'comuna-agris' ),
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_gallery' => 'yes' ),
				'description'  => __( 'Bekapcsolva a képek nagy méretben, előző/következő navigációval nyílnak meg.', 'comuna-agris' ),
			)
		);

		$this->end_controls_section();
	}

	private function register_document_controls(): void {
		$this->start_controls_section(
			'documents',
			array(
				'label' => __( 'Letölthető dokumentumok', 'comuna-agris' ),
			)
		);

		$this->add_control(
			'show_documents',
			array(
				'label'        => __( 'Dokumentumok megjelenítése', 'comuna-agris' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Be', 'comuna-agris' ),
				'label_off'    => __( 'Ki', 'comuna-agris' ),
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'documents_title',
			array(
				'label'       => __( 'Szakasz címe', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Letölthető dokumentumok', 'comuna-agris' ),
				'label_block' => true,
				'condition'   => array( 'show_documents' => 'yes' ),
			)
		);
		$this->add_control(
			'documents_intro',
			array(
				'label'     => __( 'Rövid leírás', 'comuna-agris' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 3,
				'condition' => array( 'show_documents' => 'yes' ),
			)
		);
		$this->add_control(
			'download_label',
			array(
				'label'       => __( 'Letöltés felirata', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Letöltés', 'comuna-agris' ),
				'label_block' => true,
				'condition'   => array( 'show_documents' => 'yes' ),
			)
		);

		$this->add_control(
			'document_items',
			array(
				'label'       => __( 'Letölthető fájlok', 'comuna-agris' ),
				'type'        => 'agris_media_files',
				'default'     => array(),
				'description' => __( 'A Médiatárból egyszerre több PDF, kép, Word-, Excel- vagy más engedélyezett fájl is kijelölhető.', 'comuna-agris' ),
				'condition'   => array( 'show_documents' => 'yes' ),
			)
		);
		$this->add_control(
			'document_columns',
			array(
				'label'   => __( 'Oszlopok', 'comuna-agris' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'1' => '1',
					'2' => '2',
				),
				'default'   => '2',
				'condition' => array( 'show_documents' => 'yes' ),
			)
		);
		$this->add_control(
			'force_download',
			array(
				'label'        => __( 'Letöltés indítása kattintáskor', 'comuna-agris' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'A böngésző az azonos domainen tárolt fájlokat közvetlenül letölti.', 'comuna-agris' ),
				'condition'    => array( 'show_documents' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings           = $this->get_settings_for_display();
		$show_title_section = 'yes' === ( $settings['show_title_section'] ?? 'yes' );
		$show_gallery       = 'yes' === ( $settings['show_gallery'] ?? 'yes' );
		$show_documents     = 'yes' === ( $settings['show_documents'] ?? 'yes' );
		$gallery            = $show_gallery ? $this->valid_gallery_items( (array) ( $settings['gallery_items'] ?? array() ) ) : array();
		$documents          = $show_documents ? $this->valid_document_items( (array) ( $settings['document_items'] ?? array() ) ) : array();
		?>
		<article class="agris-post-template">
			<?php if ( $show_title_section ) : ?>
				<header class="agris-post-template-header">
					<div class="agris-post-template-header-inner">
						<?php if ( ! empty( $settings['kicker'] ) ) : ?>
							<div class="agris-kicker"><?php echo esc_html( $settings['kicker'] ); ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $settings['title'] ) ) : ?>
							<h1 class="agris-title"><?php echo esc_html( $settings['title'] ); ?></h1>
						<?php endif; ?>
						<?php if ( ! empty( $settings['intro'] ) ) : ?>
							<p class="agris-post-template-intro"><?php echo wp_kses_post( nl2br( $settings['intro'] ) ); ?></p>
						<?php endif; ?>
					</div>
				</header>
			<?php endif; ?>

			<?php if ( ! empty( $settings['content'] ) ) : ?>
				<div class="agris-post-template-content agris-richtext">
					<?php echo wp_kses_post( do_shortcode( wpautop( (string) $settings['content'] ) ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $gallery ) : ?>
				<?php $this->render_gallery( $settings, $gallery ); ?>
			<?php endif; ?>

			<?php if ( $documents ) : ?>
				<?php $this->render_documents( $settings, $documents ); ?>
			<?php endif; ?>
		</article>
		<?php
	}

	private function render_gallery( array $settings, array $gallery ): void {
		$columns          = in_array( (string) ( $settings['gallery_columns'] ?? '3' ), array( '2', '3' ), true ) ? (string) $settings['gallery_columns'] : '3';
		$is_highlight     = 'yes' === ( $settings['highlight_first_image'] ?? '' ) && count( $gallery ) > 2;
		$lightbox_enabled = 'yes' === ( $settings['enable_gallery_lightbox'] ?? 'yes' );
		$classes          = 'agris-post-template-gallery agris-post-template-gallery-' . $columns . ( $is_highlight ? ' has-featured-image' : '' );
		$group            = 'agris-post-template-' . $this->get_id();
		?>
		<section class="agris-post-template-section agris-post-template-gallery-section" aria-labelledby="<?php echo esc_attr( $group . '-gallery-title' ); ?>">
			<div class="agris-post-template-section-heading">
				<h2 id="<?php echo esc_attr( $group . '-gallery-title' ); ?>"><?php echo esc_html( $settings['gallery_title'] ); ?></h2>
				<span class="agris-post-template-count" aria-label="<?php echo esc_attr( sprintf( __( '%d kép', 'comuna-agris' ), count( $gallery ) ) ); ?>"><span class="dashicons dashicons-images-alt2" aria-hidden="true"></span><?php echo esc_html( (string) count( $gallery ) ); ?></span>
			</div>
			<div class="<?php echo esc_attr( $classes ); ?>">
				<?php foreach ( $gallery as $item ) : ?>
					<?php
					$image     = isset( $item['image'] ) && is_array( $item['image'] ) ? $item['image'] : $item;
					$image_id  = (int) ( $image['id'] ?? 0 );
					$image_url = (string) ( $image['url'] ?? '' );
					$caption   = $this->image_caption( $image_id, (string) ( $item['caption'] ?? '' ) );
					$alt       = $this->image_alt( $image_id, (string) ( $item['alt_text'] ?? '' ), $caption );
					?>
					<?php if ( $lightbox_enabled ) : ?>
						<a class="agris-post-template-gallery-item has-lightbox" href="<?php echo esc_url( $image_url ); ?>" data-agris-lightbox data-agris-lightbox-group="<?php echo esc_attr( $group ); ?>" data-agris-lightbox-caption="<?php echo esc_attr( $caption ); ?>" aria-label="<?php echo esc_attr( $alt ?: __( 'Kép megnyitása', 'comuna-agris' ) ); ?>">
					<?php else : ?>
						<figure class="agris-post-template-gallery-item is-static">
					<?php endif; ?>
						<?php if ( $image_id ) : ?>
							<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'alt' => $alt, 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
						<?php endif; ?>
						<?php if ( $caption ) : ?><span><?php echo esc_html( $caption ); ?></span><?php endif; ?>
					<?php if ( $lightbox_enabled ) : ?>
						</a>
					<?php else : ?>
						</figure>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}

	private function render_documents( array $settings, array $documents ): void {
		$columns = in_array( (string) ( $settings['document_columns'] ?? '2' ), array( '1', '2' ), true ) ? (string) $settings['document_columns'] : '2';
		$group   = 'agris-post-template-' . $this->get_id();
		?>
		<section class="agris-post-template-section agris-post-template-documents-section" aria-labelledby="<?php echo esc_attr( $group . '-documents-title' ); ?>">
			<div class="agris-post-template-section-heading">
				<div>
					<h2 id="<?php echo esc_attr( $group . '-documents-title' ); ?>"><?php echo esc_html( $settings['documents_title'] ); ?></h2>
					<?php if ( ! empty( $settings['documents_intro'] ) ) : ?><p><?php echo esc_html( $settings['documents_intro'] ); ?></p><?php endif; ?>
				</div>
				<span class="agris-post-template-count" aria-label="<?php echo esc_attr( sprintf( __( '%d dokumentum', 'comuna-agris' ), count( $documents ) ) ); ?>"><span class="dashicons dashicons-media-document" aria-hidden="true"></span><?php echo esc_html( (string) count( $documents ) ); ?></span>
			</div>
			<div class="agris-post-template-documents agris-post-template-documents-<?php echo esc_attr( $columns ); ?>">
				<?php foreach ( $documents as $item ) : ?>
					<?php
					$document = $this->document_data( $item );
					$attrs    = self::link_attrs( $document['link'] );
					if ( 'yes' === ( $settings['force_download'] ?? 'yes' ) ) {
						$attrs .= ' download';
					}
					?>
					<a class="agris-post-template-document"<?php echo $attrs; ?> aria-label="<?php echo esc_attr( sprintf( __( '%s letöltése', 'comuna-agris' ), $document['title'] ) ); ?>">
						<span class="agris-post-template-file-type" aria-hidden="true"><?php echo esc_html( $document['type'] ); ?></span>
						<span class="agris-post-template-document-body">
							<strong><?php echo esc_html( $document['title'] ); ?></strong>
							<?php if ( $document['meta'] ) : ?><small><?php echo esc_html( $document['meta'] ); ?></small><?php endif; ?>
						</span>
						<span class="agris-post-template-download"><span class="dashicons dashicons-download" aria-hidden="true"></span><span><?php echo esc_html( $settings['download_label'] ); ?></span></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}

	private function valid_gallery_items( array $items ): array {
		return array_values(
			array_filter(
				$items,
				static fn( array $item ): bool => ! empty( $item['url'] ) || ! empty( $item['image']['url'] )
			)
		);
	}

	private function valid_document_items( array $items ): array {
		return array_values(
			array_filter(
				$items,
				static fn( array $item ): bool => ! empty( $item['url'] ) || ! empty( $item['file_url']['url'] )
			)
		);
	}

	private function document_data( array $item ): array {
		$attachment_id  = (int) ( $item['id'] ?? 0 );
		$attachment_url = $attachment_id ? (string) wp_get_attachment_url( $attachment_id ) : '';
		$legacy_link    = isset( $item['file_url'] ) && is_array( $item['file_url'] ) ? $item['file_url'] : array();
		$url            = $attachment_url ?: (string) ( $item['url'] ?? $legacy_link['url'] ?? '' );
		$filename       = $this->document_filename( $attachment_id, (string) ( $item['filename'] ?? '' ), $url );
		$stored_title   = trim( (string) ( $item['title'] ?? '' ) );
		$media_title    = $attachment_id ? trim( (string) get_the_title( $attachment_id ) ) : '';
		$title          = $stored_title ?: $media_title;

		if ( '' === $title || __( 'Dokumentum', 'comuna-agris' ) === $title ) {
			$title = pathinfo( $filename, PATHINFO_FILENAME ) ?: __( 'Letölthető fájl', 'comuna-agris' );
		}

		$mime      = $attachment_id ? (string) get_post_mime_type( $attachment_id ) : (string) ( $item['mime'] ?? '' );
		$file_type = $this->file_type( (string) ( $item['file_type'] ?? '' ), $url, $mime );
		$file_size = $this->document_file_size( $attachment_id, (int) ( $item['filesize'] ?? 0 ) );
		$meta      = trim( (string) ( $item['meta'] ?? '' ) );

		if ( '' === $meta ) {
			$meta = $file_type . ( $file_size > 0 ? ' · ' . size_format( $file_size, 1 ) : '' );
		}

		return array(
			'link'  => array_merge( $legacy_link, array( 'url' => $url ) ),
			'meta'  => $meta,
			'title' => $title,
			'type'  => $file_type,
		);
	}

	private function document_filename( int $attachment_id, string $stored_filename, string $url ): string {
		if ( '' !== trim( $stored_filename ) ) {
			return wp_basename( trim( $stored_filename ) );
		}

		if ( $attachment_id ) {
			$attached_file = (string) get_attached_file( $attachment_id );
			if ( '' !== $attached_file ) {
				return wp_basename( $attached_file );
			}
		}

		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		return rawurldecode( wp_basename( $path ) );
	}

	private function document_file_size( int $attachment_id, int $stored_size ): int {
		if ( $attachment_id ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			if ( is_array( $metadata ) && ! empty( $metadata['filesize'] ) ) {
				return (int) $metadata['filesize'];
			}

			$attached_file = (string) get_attached_file( $attachment_id );
			if ( '' !== $attached_file ) {
				$file_size = wp_filesize( $attached_file );
				if ( false !== $file_size ) {
					return (int) $file_size;
				}
			}
		}

		return max( 0, $stored_size );
	}

	private function image_alt( int $image_id, string $custom_alt, string $caption ): string {
		if ( '' !== trim( $custom_alt ) ) {
			return trim( $custom_alt );
		}

		if ( $image_id ) {
			$media_alt = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
			if ( '' !== $media_alt ) {
				return $media_alt;
			}
		}

		return trim( $caption );
	}

	private function image_caption( int $image_id, string $custom_caption ): string {
		if ( '' !== trim( $custom_caption ) ) {
			return trim( $custom_caption );
		}

		return $image_id ? trim( (string) wp_get_attachment_caption( $image_id ) ) : '';
	}

	private function file_type( string $custom_type, string $url, string $mime = '' ): string {
		$type = strtoupper( trim( $custom_type ) );
		if ( '' === $type ) {
			$path = (string) wp_parse_url( $url, PHP_URL_PATH );
			$type = strtoupper( (string) pathinfo( $path, PATHINFO_EXTENSION ) );
		}
		if ( '' === $type && '' !== $mime ) {
			$mime_types = array(
				'application/msword' => 'DOC',
				'application/pdf' => 'PDF',
				'application/rtf' => 'RTF',
				'application/vnd.ms-excel' => 'XLS',
				'application/vnd.ms-powerpoint' => 'PPT',
				'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PPTX',
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
				'application/zip' => 'ZIP',
				'image/jpeg' => 'JPG',
				'image/png' => 'PNG',
				'text/csv' => 'CSV',
				'text/plain' => 'TXT',
			);
			$type = $mime_types[ strtolower( $mime ) ] ?? '';
		}

		$type = preg_replace( '/[^A-Z0-9]/', '', $type );
		return substr( $type ?: 'FILE', 0, 6 );
	}
}
