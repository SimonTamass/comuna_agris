<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

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
			'kicker',
			array(
				'label'       => __( 'Címke a cím felett', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Hírek és közlemények', 'comuna-agris' ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'title',
			array(
				'label'       => __( 'Bejegyzés címe', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'A bejegyzés címe', 'comuna-agris' ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'intro',
			array(
				'label'   => __( 'Rövid bevezető', 'comuna-agris' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => __( 'Itt röviden összefoglalhatja a bejegyzés legfontosabb információit.', 'comuna-agris' ),
				'rows'    => 4,
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
			'gallery_title',
			array(
				'label'       => __( 'Szakasz címe', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Képgaléria', 'comuna-agris' ),
				'label_block' => true,
			)
		);

		$gallery_item = new Repeater();
		$gallery_item->add_control(
			'image',
			array(
				'label' => __( 'Kép', 'comuna-agris' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$gallery_item->add_control(
			'caption',
			array(
				'label'       => __( 'Képaláírás', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);
		$gallery_item->add_control(
			'alt_text',
			array(
				'label'       => __( 'Alternatív szöveg', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Ha üres, a Médiatár alternatív szövege vagy a képaláírás lesz használva.', 'comuna-agris' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'gallery_items',
			array(
				'label'       => __( 'Képek', 'comuna-agris' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $gallery_item->get_controls(),
				'title_field' => '{{{ caption }}}',
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
				'default' => '3',
			)
		);
		$this->add_control(
			'highlight_first_image',
			array(
				'label'        => __( 'Első kép kiemelése', 'comuna-agris' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
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
			'documents_title',
			array(
				'label'       => __( 'Szakasz címe', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Letölthető dokumentumok', 'comuna-agris' ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'documents_intro',
			array(
				'label' => __( 'Rövid leírás', 'comuna-agris' ),
				'type'  => Controls_Manager::TEXTAREA,
				'rows'  => 3,
			)
		);
		$this->add_control(
			'download_label',
			array(
				'label'       => __( 'Letöltés felirata', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Letöltés', 'comuna-agris' ),
				'label_block' => true,
			)
		);

		$document_item = new Repeater();
		$document_item->add_control(
			'title',
			array(
				'label'       => __( 'Dokumentum címe', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Dokumentum', 'comuna-agris' ),
				'label_block' => true,
			)
		);
		$document_item->add_control(
			'file_url',
			array(
				'label'       => __( 'Fájl URL-címe', 'comuna-agris' ),
				'type'        => Controls_Manager::URL,
				'description' => __( 'Töltse fel a fájlt a WordPress Médiatárba, majd illessze be ide az URL-címét.', 'comuna-agris' ),
				'label_block' => true,
			)
		);
		$document_item->add_control(
			'file_type',
			array(
				'label'       => __( 'Fájltípus jelölése', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'PDF',
				'description' => __( 'Ha üres, a widget az URL-címből állapítja meg.', 'comuna-agris' ),
			)
		);
		$document_item->add_control(
			'meta',
			array(
				'label'       => __( 'Leírás / fájlméret', 'comuna-agris' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'PDF · 1,2 MB', 'comuna-agris' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'document_items',
			array(
				'label'       => __( 'Dokumentumok', 'comuna-agris' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $document_item->get_controls(),
				'title_field' => '{{{ title }}}',
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
				'default' => '2',
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
			)
		);

		$this->end_controls_section();
	}

	protected function render(): void {
		$settings  = $this->get_settings_for_display();
		$gallery   = $this->valid_gallery_items( (array) ( $settings['gallery_items'] ?? array() ) );
		$documents = $this->valid_document_items( (array) ( $settings['document_items'] ?? array() ) );
		?>
		<article class="agris-post-template">
			<header class="agris-post-template-header">
				<div class="agris-post-template-header-inner">
					<?php if ( ! empty( $settings['kicker'] ) ) : ?>
						<div class="agris-kicker"><?php echo esc_html( $settings['kicker'] ); ?></div>
					<?php endif; ?>
					<h1 class="agris-title"><?php echo esc_html( $settings['title'] ); ?></h1>
					<?php if ( ! empty( $settings['intro'] ) ) : ?>
						<p class="agris-post-template-intro"><?php echo wp_kses_post( nl2br( $settings['intro'] ) ); ?></p>
					<?php endif; ?>
				</div>
			</header>

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
		$columns      = in_array( (string) ( $settings['gallery_columns'] ?? '3' ), array( '2', '3' ), true ) ? (string) $settings['gallery_columns'] : '3';
		$is_highlight = 'yes' === ( $settings['highlight_first_image'] ?? '' ) && count( $gallery ) > 2;
		$classes      = 'agris-post-template-gallery agris-post-template-gallery-' . $columns . ( $is_highlight ? ' has-featured-image' : '' );
		$group        = 'agris-post-template-' . $this->get_id();
		?>
		<section class="agris-post-template-section agris-post-template-gallery-section" aria-labelledby="<?php echo esc_attr( $group . '-gallery-title' ); ?>">
			<div class="agris-post-template-section-heading">
				<h2 id="<?php echo esc_attr( $group . '-gallery-title' ); ?>"><?php echo esc_html( $settings['gallery_title'] ); ?></h2>
				<span class="agris-post-template-count" aria-label="<?php echo esc_attr( sprintf( __( '%d kép', 'comuna-agris' ), count( $gallery ) ) ); ?>"><span class="dashicons dashicons-images-alt2" aria-hidden="true"></span><?php echo esc_html( (string) count( $gallery ) ); ?></span>
			</div>
			<div class="<?php echo esc_attr( $classes ); ?>">
				<?php foreach ( $gallery as $item ) : ?>
					<?php
					$image_id = (int) ( $item['image']['id'] ?? 0 );
					$image_url = (string) $item['image']['url'];
					$caption   = (string) ( $item['caption'] ?? '' );
					$alt       = $this->image_alt( $image_id, (string) ( $item['alt_text'] ?? '' ), $caption );
					?>
					<a class="agris-post-template-gallery-item" href="<?php echo esc_url( $image_url ); ?>" data-agris-lightbox data-agris-lightbox-group="<?php echo esc_attr( $group ); ?>" data-agris-lightbox-caption="<?php echo esc_attr( $caption ); ?>" aria-label="<?php echo esc_attr( $alt ?: __( 'Kép megnyitása', 'comuna-agris' ) ); ?>">
						<?php if ( $image_id ) : ?>
							<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'alt' => $alt, 'loading' => 'lazy' ) ); ?>
						<?php else : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
						<?php endif; ?>
						<?php if ( $caption ) : ?><span><?php echo esc_html( $caption ); ?></span><?php endif; ?>
					</a>
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
					$url       = (string) $item['file_url']['url'];
					$file_type = $this->file_type( (string) ( $item['file_type'] ?? '' ), $url );
					$attrs     = self::link_attrs( $item['file_url'] );
					if ( 'yes' === ( $settings['force_download'] ?? '' ) ) {
						$attrs .= ' download';
					}
					?>
					<a class="agris-post-template-document"<?php echo $attrs; ?>>
						<span class="agris-post-template-file-type" aria-hidden="true"><?php echo esc_html( $file_type ); ?></span>
						<span class="agris-post-template-document-body">
							<strong><?php echo esc_html( $item['title'] ); ?></strong>
							<?php if ( ! empty( $item['meta'] ) ) : ?><small><?php echo esc_html( $item['meta'] ); ?></small><?php endif; ?>
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
				static fn( array $item ): bool => ! empty( $item['image']['url'] )
			)
		);
	}

	private function valid_document_items( array $items ): array {
		return array_values(
			array_filter(
				$items,
				static fn( array $item ): bool => ! empty( $item['file_url']['url'] )
			)
		);
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

	private function file_type( string $custom_type, string $url ): string {
		$type = strtoupper( trim( $custom_type ) );
		if ( '' === $type ) {
			$path = (string) wp_parse_url( $url, PHP_URL_PATH );
			$type = strtoupper( (string) pathinfo( $path, PATHINFO_EXTENSION ) );
		}

		$type = preg_replace( '/[^A-Z0-9]/', '', $type );
		return substr( $type ?: 'FILE', 0, 6 );
	}
}
