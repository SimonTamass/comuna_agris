<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class News_Grid extends Base {
	public function get_name(): string { return 'agris-news-grid'; }
	public function get_title(): string { return __( '19 · Dinamikus hírek', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-posts-grid'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'query', array( 'label' => __( 'Lekérdezés', 'comuna-agris' ) ) );
		$this->add_control( 'post_type', array( 'label' => __( 'Tartalomtípus', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => self::post_types(), 'default' => 'post' ) );
		$this->add_control( 'category', array( 'label' => __( 'Kategória slug', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'description' => __( 'Üresen hagyva minden kategória.', 'comuna-agris' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'Elemek száma', 'comuna-agris' ), 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 24 ) );
		$this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( '2' => '2', '3' => '3', '4' => '4' ), 'default' => '3' ) );
		$this->add_control( 'orderby', array( 'label' => __( 'Rendezés', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'date' => 'Dátum', 'title' => 'Cím', 'modified' => 'Módosítás', 'menu_order' => 'Menüsorrend' ), 'default' => 'date' ) );
		$this->add_control( 'show_excerpt', array( 'label' => __( 'Kivonat', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->end_controls_section();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$args = array( 'post_type' => $s['post_type'], 'posts_per_page' => (int) $s['count'], 'post_status' => 'publish', 'orderby' => $s['orderby'], 'order' => 'DESC', 'ignore_sticky_posts' => true );
		if ( $s['category'] && 'post' === $s['post_type'] ) { $args['category_name'] = sanitize_title( $s['category'] ); }
		$q = new WP_Query( $args );
		if ( ! $q->have_posts() ) { echo '<div class="agris-empty">' . esc_html__( 'Nu există articole publicate pentru această selecție.', 'comuna-agris' ) . '</div>'; return; }
		echo '<div class="agris-grid agris-grid-' . esc_attr( $s['columns'] ) . '">';
		while ( $q->have_posts() ) { $q->the_post(); $cats = get_the_category(); ?>
			<article class="agris-news-card"><a href="<?php the_permalink(); ?>"><div class="agris-news-image"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); } else { echo '<span>CA</span>'; } ?></div><div class="agris-news-body"><div class="agris-meta"><span><?php echo esc_html( $cats[0]->name ?? get_post_type_object( get_post_type() )->labels->singular_name ); ?></span><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></div><h3><?php the_title(); ?></h3><?php if ( 'yes' === $s['show_excerpt'] ) : ?><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p><?php endif; ?><strong class="agris-read-more"><?php esc_html_e( 'Citește mai mult →', 'comuna-agris' ); ?></strong></div></a></article>
		<?php }
		echo '</div>'; wp_reset_postdata();
	}
}

final class Document_Grid extends Base {
	public function get_name(): string { return 'agris-document-grid'; }
	public function get_title(): string { return __( '20 · Kézi dokumentumkártyák', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-document-file'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'items', array( 'label' => __( 'Dokumentumok', 'comuna-agris' ) ) );
		$r = new Repeater();
		$r->add_control( 'icon', array( 'label' => __( 'Jel', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'HCL' ) );
		$r->add_control( 'title', array( 'label' => __( 'Cím', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Hotărârea Consiliului Local' ) );
		$r->add_control( 'meta', array( 'label' => __( 'Dátum / leírás', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Publicat recent' ) );
		$r->add_control( 'category', array( 'label' => __( 'Szűrőkategória', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Hotărâri' ) );
		$r->add_control( 'url', array( 'label' => __( 'Fájl vagy oldal', 'comuna-agris' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ icon }}} · {{{ title }}}', 'default' => array( array( 'icon' => 'HCL', 'title' => 'H.C.L. nr. 13–16 / 2026', 'meta' => '18 mai 2026' ), array( 'icon' => 'PDF', 'title' => 'Document public', 'meta' => 'Publicat recent' ) ) ) );
		$this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( '1' => '1', '2' => '2', '3' => '3' ), 'default' => '3' ) );
		$this->add_control( 'filters', array( 'label' => __( 'Kategóriaszűrők', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes' ) );
		$this->end_controls_section();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $cats = array_unique( array_filter( array_column( $s['items_list'], 'category' ) ) ); ?><div class="agris-document-widget"><?php if ( 'yes' === $s['filters'] && $cats ) : ?><div class="agris-filters"><button class="is-active" data-agris-filter="all"><?php esc_html_e( 'Toate', 'comuna-agris' ); ?></button><?php foreach ( $cats as $cat ) : ?><button data-agris-filter="<?php echo esc_attr( sanitize_title( $cat ) ); ?>"><?php echo esc_html( $cat ); ?></button><?php endforeach; ?></div><?php endif; ?><div class="agris-grid agris-grid-<?php echo esc_attr( $s['columns'] ); ?>" data-agris-filter-items><?php foreach ( $s['items_list'] as $item ) : ?><a class="agris-doc-card" data-agris-category="<?php echo esc_attr( sanitize_title( $item['category'] ) ); ?>" <?php echo self::link_attrs( $item['url'] ); ?>><b><?php echo esc_html( $item['icon'] ); ?></b><span><strong><?php echo esc_html( $item['title'] ); ?></strong><small><?php echo esc_html( $item['meta'] ); ?></small></span></a><?php endforeach; ?></div></div><?php }
}

final class Document_Library extends Base {
	public function get_name(): string { return 'agris-document-library'; }
	public function get_title(): string { return __( '21 · Dinamikus dokumentumtár', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-library-open'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Dokumentumtár', 'comuna-agris' ) ) );
		$this->common_heading_controls( 'Documente', 'Bibliotecă publică' );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Adatforrás', 'comuna-agris' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'automatic'      => __( 'Automatikus: meglévő tartalom', 'comuna-agris' ),
					'agris_document' => __( 'Documente tartalomtípus', 'comuna-agris' ),
					'legacy_posts'   => __( 'Meglévő bejegyzések és kategóriák', 'comuna-agris' ),
				),
				'default' => 'automatic',
			)
		);
		$this->add_control( 'legacy_keywords', array( 'label' => __( 'Dokumentumkategória kulcsszavak', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'hotarari,anunt,convocator,proiect,buget,declaratii,document', 'condition' => array( 'source!' => 'agris_document' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'Dokumentumok száma', 'comuna-agris' ), 'type' => Controls_Manager::NUMBER, 'default' => 12, 'min' => 1, 'max' => 100 ) );
		$this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( '2' => '2', '3' => '3' ), 'default' => '3' ) );
		$this->add_control( 'show_filters', array( 'label' => __( 'Szűrők', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'show_search', array( 'label' => __( 'Gyorskereső', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->end_controls_section();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$source = $s['source'] ?? 'automatic';
		if ( 'automatic' === $source ) {
			$counts = wp_count_posts( 'agris_document' );
			$source = $counts && ! empty( $counts->publish ) ? 'agris_document' : 'legacy_posts';
		}

		$args = array(
			'post_type'           => 'agris_document' === $source ? 'agris_document' : 'post',
			'posts_per_page'      => (int) $s['count'],
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		);

		if ( 'legacy_posts' === $source ) {
			$category_ids = $this->legacy_category_ids( (string) ( $s['legacy_keywords'] ?? '' ) );
			if ( $category_ids ) {
				$args['category__in'] = $category_ids;
			}
		}

		$q = new WP_Query( $args );
		$terms = $this->query_terms( $q, $source );
		?><section class="agris-document-library"><?php $this->render_heading( $s ); ?><div class="agris-library-tools"><?php if ( 'yes' === $s['show_filters'] && $terms ) : ?><div class="agris-filters"><button class="is-active" data-agris-filter="all"><?php esc_html_e( 'Toate', 'comuna-agris' ); ?></button><?php foreach ( $terms as $term ) : ?><button data-agris-filter="<?php echo esc_attr( $term['slug'] ); ?>"><?php echo esc_html( $term['name'] ); ?></button><?php endforeach; ?></div><?php endif; ?><?php if ( 'yes' === $s['show_search'] ) : ?><label class="agris-library-search"><span class="screen-reader-text"><?php esc_html_e( 'Caută document', 'comuna-agris' ); ?></span><input type="search" data-agris-doc-search placeholder="<?php esc_attr_e( 'Caută în documente…', 'comuna-agris' ); ?>"></label><?php endif; ?></div><div class="agris-grid agris-grid-<?php echo esc_attr( $s['columns'] ); ?>" data-agris-filter-items><?php
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$post_id = get_the_ID();
				$post_terms = 'agris_document' === $source ? wp_get_post_terms( $post_id, 'agris_document_category' ) : get_the_category( $post_id );
				$post_terms = is_wp_error( $post_terms ) ? array() : $post_terms;
				$slugs = wp_list_pluck( $post_terms, 'slug' );
				$file = 'agris_document' === $source ? get_post_meta( $post_id, 'agris_file_url', true ) : '';
				$url = $file ?: get_permalink( $post_id );
				$icon = 'agris_document' === $source ? get_post_meta( $post_id, 'agris_document_icon', true ) : $this->legacy_icon( get_the_title( $post_id ), $slugs );
				$icon = $icon ?: 'DOC';
				?><a class="agris-doc-card" data-agris-category="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>" data-agris-title="<?php echo esc_attr( strtolower( get_the_title( $post_id ) ) ); ?>" href="<?php echo esc_url( $url ); ?>"><b><?php echo esc_html( $icon ); ?></b><span><strong><?php echo esc_html( get_the_title( $post_id ) ); ?></strong><small><?php echo esc_html( get_the_date( '', $post_id ) ); ?></small></span></a><?php
			}
		} else {
			echo '<div class="agris-empty">' . esc_html__( 'Nu există încă documente publicate pentru această selecție.', 'comuna-agris' ) . '</div>';
		}
		wp_reset_postdata(); ?></div><div class="agris-no-results" hidden><?php esc_html_e( 'Nu am găsit documente.', 'comuna-agris' ); ?></div></section><?php
	}

	private function legacy_category_ids( string $raw_keywords ): array {
		$keywords = array_values( array_filter( array_map( 'sanitize_title', explode( ',', $raw_keywords ) ) ) );
		if ( ! $keywords ) {
			return array();
		}

		$ids = array();
		$categories = get_categories( array( 'hide_empty' => true ) );
		foreach ( $categories as $category ) {
			$haystack = sanitize_title( $category->slug . '-' . $category->name );
			foreach ( $keywords as $keyword ) {
				if ( str_contains( $haystack, $keyword ) ) {
					$ids[] = (int) $category->term_id;
					break;
				}
			}
		}
		return array_values( array_unique( $ids ) );
	}

	private function query_terms( WP_Query $query, string $source ): array {
		$terms = array();
		foreach ( $query->posts as $post ) {
			$post_terms = 'agris_document' === $source ? wp_get_post_terms( $post->ID, 'agris_document_category' ) : get_the_category( $post->ID );
			if ( is_wp_error( $post_terms ) ) {
				continue;
			}
			foreach ( $post_terms as $term ) {
				$terms[ $term->slug ] = array( 'slug' => $term->slug, 'name' => $term->name );
			}
		}
		return array_values( $terms );
	}

	private function legacy_icon( string $title, array $slugs ): string {
		$value = sanitize_title( $title . '-' . implode( '-', $slugs ) );
		if ( str_contains( $value, 'hotarar' ) ) { return 'HCL'; }
		if ( str_contains( $value, 'anunt' ) ) { return 'AN'; }
		if ( str_contains( $value, 'buget' ) ) { return 'BUG'; }
		if ( str_contains( $value, 'convocator' ) || str_contains( $value, 'proiect' ) ) { return 'PH'; }
		if ( str_contains( $value, 'declarat' ) ) { return 'DEC'; }
		return 'DOC';
	}
}
