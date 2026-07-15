<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Post_Archive extends Base {
	public function get_name(): string { return 'agris-post-archive'; }
	public function get_title(): string { return __( '22 · Blog / kategória archívum', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-archive-posts'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'layout', array( 'label' => __( 'Elrendezés', 'comuna-agris' ) ) );
		$this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( '1' => '1', '2' => '2', '3' => '3' ), 'default' => '3' ) );
		$this->add_control( 'show_archive_header', array( 'label' => __( 'Archívum fejléc', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'fallback_title', array( 'label' => __( 'Szerkesztői előnézet címe', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Noutăți și anunțuri' ) );
		$this->add_control( 'excerpt_words', array( 'label' => __( 'Kivonat szavai', 'comuna-agris' ), 'type' => Controls_Manager::NUMBER, 'default' => 24, 'min' => 0, 'max' => 80 ) );
		$this->add_control( 'archive_kicker', array( 'label' => __( 'Archívum felirata', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Actualități' ) );
		$this->add_control( 'search_prefix', array( 'label' => __( 'Keresési cím', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Rezultate pentru: %s' ) );
		$this->add_control( 'read_more_text', array( 'label' => __( 'Tovább felirat', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Citește articolul →' ) );
		$this->add_control( 'pagination_label', array( 'label' => __( 'Lapozó címkéje', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Navigare pagini' ) );
		$this->add_control( 'empty_text', array( 'label' => __( 'Üres állapot', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Nu există articole.' ) );
		$this->add_control( 'article_label', array( 'label' => __( 'Bejegyzés felirata', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Articol' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		global $wp_query;
		$s = $this->get_settings_for_display();
		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$query = $wp_query;
		if ( $is_editor || ! is_archive() && ! is_home() && ! is_search() ) { $query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 6, 'post_status' => 'publish' ) ); }
		$title = is_search() ? str_replace( '%s', get_search_query(), $s['search_prefix'] ) : ( is_archive() ? get_the_archive_title() : $s['fallback_title'] );
		if ( 'yes' === $s['show_archive_header'] ) : ?><header class="agris-archive-header"><div class="agris-kicker"><?php echo esc_html( $s['archive_kicker'] ); ?></div><h1><?php echo wp_kses_post( $title ); ?></h1><?php if ( is_archive() ) : ?><div><?php the_archive_description(); ?></div><?php endif; ?></header><?php endif;
		if ( $query->have_posts() ) : ?><div class="agris-grid agris-grid-<?php echo esc_attr( $s['columns'] ); ?>"><?php while ( $query->have_posts() ) : $query->the_post(); ?><article class="agris-news-card"><a href="<?php the_permalink(); ?>"><div class="agris-news-image"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } else { echo '<span>CA</span>'; } ?></div><div class="agris-news-body"><div class="agris-meta"><span><?php echo esc_html( get_the_category()[0]->name ?? $s['article_label'] ); ?></span><time><?php echo esc_html( get_the_date() ); ?></time></div><h2><?php the_title(); ?></h2><?php if ( $s['excerpt_words'] ) : ?><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), (int) $s['excerpt_words'] ) ); ?></p><?php endif; ?><strong class="agris-read-more"><?php echo esc_html( $s['read_more_text'] ); ?></strong></div></a></article><?php endwhile; ?></div><?php if ( ! $is_editor ) : ?><nav class="agris-pagination" aria-label="<?php echo esc_attr( $s['pagination_label'] ); ?>"><?php echo wp_kses_post( paginate_links( array( 'type' => 'list', 'prev_text' => '←', 'next_text' => '→' ) ) ); ?></nav><?php endif; else : ?><div class="agris-empty"><?php echo esc_html( $s['empty_text'] ); ?></div><?php endif;
		if ( $query !== $wp_query ) { wp_reset_postdata(); }
	}
}
