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
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		global $wp_query;
		$s = $this->get_settings_for_display();
		$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$query = $wp_query;
		if ( $is_editor || ! is_archive() && ! is_home() && ! is_search() ) { $query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 6, 'post_status' => 'publish' ) ); }
		$title = is_search() ? sprintf( __( 'Rezultate pentru: %s', 'comuna-agris' ), get_search_query() ) : ( is_archive() ? get_the_archive_title() : $s['fallback_title'] );
		if ( 'yes' === $s['show_archive_header'] ) : ?><header class="agris-archive-header"><div class="agris-kicker"><?php esc_html_e( 'Actualități', 'comuna-agris' ); ?></div><h1><?php echo wp_kses_post( $title ); ?></h1><?php if ( is_archive() ) : ?><div><?php the_archive_description(); ?></div><?php endif; ?></header><?php endif;
		if ( $query->have_posts() ) : ?><div class="agris-grid agris-grid-<?php echo esc_attr( $s['columns'] ); ?>"><?php while ( $query->have_posts() ) : $query->the_post(); ?><article class="agris-news-card"><a href="<?php the_permalink(); ?>"><div class="agris-news-image"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } else { echo '<span>CA</span>'; } ?></div><div class="agris-news-body"><div class="agris-meta"><span><?php echo esc_html( get_the_category()[0]->name ?? __( 'Articol', 'comuna-agris' ) ); ?></span><time><?php echo esc_html( get_the_date() ); ?></time></div><h2><?php the_title(); ?></h2><?php if ( $s['excerpt_words'] ) : ?><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), (int) $s['excerpt_words'] ) ); ?></p><?php endif; ?><strong class="agris-read-more"><?php esc_html_e( 'Citește articolul →', 'comuna-agris' ); ?></strong></div></a></article><?php endwhile; ?></div><?php if ( ! $is_editor ) : ?><nav class="agris-pagination" aria-label="<?php esc_attr_e( 'Navigare pagini', 'comuna-agris' ); ?>"><?php echo wp_kses_post( paginate_links( array( 'type' => 'list', 'prev_text' => '←', 'next_text' => '→' ) ) ); ?></nav><?php endif; else : ?><div class="agris-empty"><?php esc_html_e( 'Nu există articole.', 'comuna-agris' ); ?></div><?php endif;
		if ( $query !== $wp_query ) { wp_reset_postdata(); }
	}
}
