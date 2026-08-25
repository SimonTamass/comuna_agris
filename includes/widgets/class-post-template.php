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
			'content_language',
			array(
				'label'       => __( 'Tartalom nyelve / Limba conținutului', 'comuna-agris' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					''   => __( 'Automatikus (Polylang) / Automat', 'comuna-agris' ),
					'ro' => 'Română',
					'hu' => 'Magyar',
				),
				'default'     => '',
				'description' => __( 'Automatikus módban a widget a bejegyzés Polylang-nyelvét használja. Szükség esetén itt felülbírálható.', 'comuna-agris' ),
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
				'type'        => 'agris_media_images',
				'default'     => array(),
				'description' => __( 'A Médiatár egyetlen ablakában egyszerre tetszőleges számú kép jelölhető ki.', 'comuna-agris' ),
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
		$this->add_control(
			'enable_pdf_preview',
			array(
				'label'        => __( 'PDF előnézet szem ikonnal', 'comuna-agris' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Be', 'comuna-agris' ),
				'label_off'    => __( 'Ki', 'comuna-agris' ),
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'A PDF-fájlok a letöltés mellett egy felugró, beépített dokumentumnézőben is megnyithatók.', 'comuna-agris' ),
				'condition'    => array( 'show_documents' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings           = $this->get_settings_for_display();
		$language           = $this->content_language( $settings );
		$texts              = $this->translations( $language );
		$localized_settings = array( 'kicker', 'title', 'intro', 'content', 'gallery_title', 'documents_title', 'download_label' );

		foreach ( $localized_settings as $setting_name ) {
			$settings[ $setting_name ] = $this->localized_setting( $settings, $setting_name, $language );
		}

		$show_title_section = 'yes' === ( $settings['show_title_section'] ?? 'yes' );
		$show_gallery       = 'yes' === ( $settings['show_gallery'] ?? 'yes' );
		$show_documents     = 'yes' === ( $settings['show_documents'] ?? 'yes' );
		$gallery            = $show_gallery ? $this->valid_gallery_items( (array) ( $settings['gallery_items'] ?? array() ) ) : array();
		$documents          = $show_documents ? $this->valid_document_items( (array) ( $settings['document_items'] ?? array() ) ) : array();
		?>
		<article class="agris-post-template" lang="<?php echo esc_attr( $language ); ?>">
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
				<?php $this->render_gallery( $settings, $gallery, $texts ); ?>
			<?php endif; ?>

			<?php if ( $documents ) : ?>
				<?php $this->render_documents( $settings, $documents, $texts ); ?>
			<?php endif; ?>
		</article>
		<?php
	}

	private function render_gallery( array $settings, array $gallery, array $texts ): void {
		$columns          = in_array( (string) ( $settings['gallery_columns'] ?? '3' ), array( '2', '3' ), true ) ? (string) $settings['gallery_columns'] : '3';
		$is_highlight     = 'yes' === ( $settings['highlight_first_image'] ?? '' ) && count( $gallery ) > 2;
		$lightbox_enabled = 'yes' === ( $settings['enable_gallery_lightbox'] ?? 'yes' );
		$classes          = 'agris-post-template-gallery agris-post-template-gallery-' . $columns . ( $is_highlight ? ' has-featured-image' : '' );
		$group            = 'agris-post-template-' . $this->get_id();
		$count_label      = sprintf( 1 === count( $gallery ) ? $texts['image_count_one'] : $texts['image_count_many'], count( $gallery ) );
		?>
		<section class="agris-post-template-section agris-post-template-gallery-section" aria-labelledby="<?php echo esc_attr( $group . '-gallery-title' ); ?>">
			<div class="agris-post-template-section-heading">
				<h2 id="<?php echo esc_attr( $group . '-gallery-title' ); ?>"><?php echo esc_html( $settings['gallery_title'] ); ?></h2>
				<span class="agris-post-template-count" aria-label="<?php echo esc_attr( $count_label ); ?>"><span class="dashicons dashicons-images-alt2" aria-hidden="true"></span><?php echo esc_html( (string) count( $gallery ) ); ?></span>
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
						<a class="agris-post-template-gallery-item has-lightbox" href="<?php echo esc_url( $image_url ); ?>" data-agris-lightbox data-agris-lightbox-group="<?php echo esc_attr( $group ); ?>" data-agris-lightbox-caption="<?php echo esc_attr( $caption ); ?>" aria-label="<?php echo esc_attr( $alt ?: $texts['image_open'] ); ?>">
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

	private function render_documents( array $settings, array $documents, array $texts ): void {
		$columns             = in_array( (string) ( $settings['document_columns'] ?? '2' ), array( '1', '2' ), true ) ? (string) $settings['document_columns'] : '2';
		$group               = 'agris-post-template-' . $this->get_id();
		$count_label         = sprintf( 1 === count( $documents ) ? $texts['document_count_one'] : $texts['document_count_many'], count( $documents ) );
		$pdf_preview_enabled = 'yes' === ( $settings['enable_pdf_preview'] ?? 'yes' );
		?>
		<section class="agris-post-template-section agris-post-template-documents-section" aria-labelledby="<?php echo esc_attr( $group . '-documents-title' ); ?>">
			<div class="agris-post-template-section-heading">
				<div>
					<h2 id="<?php echo esc_attr( $group . '-documents-title' ); ?>"><?php echo esc_html( $settings['documents_title'] ); ?></h2>
					<?php if ( ! empty( $settings['documents_intro'] ) ) : ?><p><?php echo esc_html( $settings['documents_intro'] ); ?></p><?php endif; ?>
				</div>
				<span class="agris-post-template-count" aria-label="<?php echo esc_attr( $count_label ); ?>"><span class="dashicons dashicons-media-document" aria-hidden="true"></span><?php echo esc_html( (string) count( $documents ) ); ?></span>
			</div>
			<div class="agris-post-template-documents agris-post-template-documents-<?php echo esc_attr( $columns ); ?>">
				<?php foreach ( $documents as $item ) : ?>
					<?php
					$document = $this->document_data( $item, $texts );
					$attrs    = self::link_attrs( $document['link'] );
					$is_pdf   = 'PDF' === $document['type'];
					if ( 'yes' === ( $settings['force_download'] ?? 'yes' ) ) {
						$attrs .= ' download';
					}
					?>
					<article class="agris-post-template-document">
						<span class="agris-post-template-file-type" aria-hidden="true"><?php echo esc_html( $document['type'] ); ?></span>
						<span class="agris-post-template-document-body">
							<strong><?php echo esc_html( $document['title'] ); ?></strong>
							<?php if ( $document['meta'] ) : ?><small><?php echo esc_html( $document['meta'] ); ?></small><?php endif; ?>
						</span>
						<span class="agris-post-template-document-actions">
							<?php if ( $is_pdf && $pdf_preview_enabled ) : ?>
								<button type="button" class="agris-post-template-preview" data-agris-pdf-preview data-agris-pdf-url="<?php echo esc_url( $document['link']['url'] ); ?>" data-agris-pdf-title="<?php echo esc_attr( $document['title'] ); ?>" aria-label="<?php echo esc_attr( sprintf( $texts['preview_aria'], $document['title'] ) ); ?>" title="<?php echo esc_attr( $texts['preview_label'] ); ?>">
									<span class="dashicons dashicons-visibility" aria-hidden="true"></span><span class="screen-reader-text"><?php echo esc_html( $texts['preview_label'] ); ?></span>
								</button>
							<?php endif; ?>
							<a class="agris-post-template-download"<?php echo $attrs; ?> data-agris-download-ready="true" aria-label="<?php echo esc_attr( sprintf( $texts['download_aria'], $document['title'] ) ); ?>"><span class="dashicons dashicons-download" aria-hidden="true"></span><span><?php echo esc_html( $settings['download_label'] ); ?></span></a>
						</span>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}

	private function content_language( array $settings ): string {
		$manual_language = $this->normalize_language( (string) ( $settings['content_language'] ?? '' ) );
		if ( in_array( $manual_language, array( 'ro', 'hu' ), true ) ) {
			return $manual_language;
		}

		$post_id = function_exists( 'get_the_ID' ) ? (int) get_the_ID() : 0;
		if ( ! $post_id && function_exists( 'get_queried_object_id' ) ) {
			$post_id = (int) get_queried_object_id();
		}

		if ( $post_id && function_exists( 'pll_get_post_language' ) ) {
			$post_language = $this->normalize_language( (string) pll_get_post_language( $post_id, 'slug' ) );
			if ( in_array( $post_language, array( 'ro', 'hu' ), true ) ) {
				return $post_language;
			}
		}

		if ( function_exists( 'pll_current_language' ) ) {
			$current_language = $this->normalize_language( (string) pll_current_language( 'slug' ) );
			if ( in_array( $current_language, array( 'ro', 'hu' ), true ) ) {
				return $current_language;
			}
		}

		$locale = function_exists( 'determine_locale' ) ? (string) determine_locale() : ( function_exists( 'get_locale' ) ? (string) get_locale() : '' );
		return 'hu' === $this->normalize_language( $locale ) ? 'hu' : 'ro';
	}

	private function normalize_language( string $language ): string {
		$language = strtolower( trim( str_replace( '-', '_', $language ) ) );
		return (string) strtok( $language, '_' );
	}

	private function localized_setting( array $settings, string $key, string $language ): string {
		$texts = $this->translations( $language );
		if ( ! array_key_exists( $key, $settings ) ) {
			return (string) ( $texts[ $key ] ?? '' );
		}

		$value       = (string) $settings[ $key ];
		$known_value = array();
		foreach ( array( 'ro', 'hu' ) as $known_language ) {
			$known_texts = $this->translations( $known_language );
			if ( isset( $known_texts[ $key ] ) ) {
				$known_value[] = trim( (string) $known_texts[ $key ] );
			}
		}

		return in_array( trim( $value ), $known_value, true ) ? (string) ( $texts[ $key ] ?? $value ) : $value;
	}

	private function translations( string $language ): array {
		$translations = array(
			'hu' => array(
				'kicker'              => 'Hírek és közlemények',
				'title'               => 'A bejegyzés címe',
				'intro'               => 'Itt röviden összefoglalhatja a bejegyzés legfontosabb információit.',
				'content'             => '<p>Adja hozzá itt a bejegyzés részletes tartalmát. A galéria és a letölthető dokumentumok külön szakaszban jelennek meg alatta.</p>',
				'gallery_title'       => 'Képgaléria',
				'documents_title'     => 'Letölthető dokumentumok',
				'download_label'      => 'Letöltés',
				'image_open'          => 'Kép megnyitása',
				'image_count_one'     => '%d kép',
				'image_count_many'    => '%d kép',
				'document_count_one'  => '%d dokumentum',
				'document_count_many' => '%d dokumentum',
				'download_aria'       => '%s letöltése',
				'downloadable_file'   => 'Letölthető fájl',
				'preview_label'       => 'Megtekintés',
				'preview_aria'        => '%s megtekintése',
			),
			'ro' => array(
				'kicker'              => 'Știri și comunicate',
				'title'               => 'Titlul articolului',
				'intro'               => 'Prezentați pe scurt cele mai importante informații ale articolului.',
				'content'             => '<p>Adăugați aici conținutul detaliat al articolului. Galeria și documentele descărcabile apar mai jos, în secțiuni separate.</p>',
				'gallery_title'       => 'Galerie foto',
				'documents_title'     => 'Documente descărcabile',
				'download_label'      => 'Descarcă',
				'image_open'          => 'Deschide imaginea',
				'image_count_one'     => '%d imagine',
				'image_count_many'    => '%d imagini',
				'document_count_one'  => '%d document',
				'document_count_many' => '%d documente',
				'download_aria'       => 'Descarcă %s',
				'downloadable_file'   => 'Fișier descărcabil',
				'preview_label'       => 'Vizualizează',
				'preview_aria'        => 'Vizualizează %s',
			),
		);

		return $translations[ 'hu' === $language ? 'hu' : 'ro' ];
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

	private function document_data( array $item, array $texts ): array {
		$attachment_id  = (int) ( $item['id'] ?? 0 );
		$attachment_url = $attachment_id ? (string) wp_get_attachment_url( $attachment_id ) : '';
		$legacy_link    = isset( $item['file_url'] ) && is_array( $item['file_url'] ) ? $item['file_url'] : array();
		$url            = $attachment_url ?: (string) ( $item['url'] ?? $legacy_link['url'] ?? '' );
		$filename       = $this->document_filename( $attachment_id, (string) ( $item['filename'] ?? '' ), $url );
		$stored_title   = trim( (string) ( $item['title'] ?? '' ) );
		$media_title    = $attachment_id ? trim( (string) get_the_title( $attachment_id ) ) : '';
		$title          = $stored_title ?: $media_title;

		if ( '' === $title || in_array( $title, array( 'Dokumentum', 'Document' ), true ) ) {
			$title = pathinfo( $filename, PATHINFO_FILENAME ) ?: $texts['downloadable_file'];
		}

		$mime      = $attachment_id ? (string) get_post_mime_type( $attachment_id ) : (string) ( $item['mime'] ?? '' );
		$file_type = $this->file_type( (string) ( $item['file_type'] ?? '' ), $url, $mime );
		$file_size = $this->document_file_size( $attachment_id, (int) ( $item['filesize'] ?? 0 ) );
		$meta      = trim( (string) ( $item['meta'] ?? '' ) );

		if ( '' === $meta ) {
			$meta = $file_type . ( $file_size > 0 ? ' · ' . $this->file_size_label( $file_size ) : '' );
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

	private function file_size_label( int $bytes ): string {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$power = 0;
		$value = max( 0, $bytes );

		while ( $value >= 1024 && $power < count( $units ) - 1 ) {
			$value /= 1024;
			++$power;
		}

		$decimals = abs( $value - round( $value ) ) < 0.05 ? 0 : 1;
		return number_format_i18n( $value, $decimals ) . ' ' . $units[ $power ];
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
