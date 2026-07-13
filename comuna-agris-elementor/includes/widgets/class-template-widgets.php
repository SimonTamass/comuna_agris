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

final class Single_Post extends Base {
	public function get_name(): string { return 'agris-single-post'; }
	public function get_title(): string { return __( '23 · Egyedi blogbejegyzés', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-single-post'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'layout', array( 'label' => __( 'Tartalom', 'comuna-agris' ) ) );
		$this->add_control( 'show_image', array( 'label' => __( 'Kiemelt kép', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->add_control( 'show_author', array( 'label' => __( 'Szerző', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes' ) );
		$this->add_control( 'show_share', array( 'label' => __( 'Megosztás', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->end_controls_section();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$post_id = get_the_ID();
		if ( ! $post_id ) { echo '<div class="agris-empty">' . esc_html__( 'Acest widget se afișează într-un șablon pentru articol individual.', 'comuna-agris' ) . '</div>'; return; }
		$cats = get_the_category( $post_id );
		?><article class="agris-single"><header><div class="agris-breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Acasă', 'comuna-agris' ); ?></a><span>/</span><span><?php echo esc_html( $cats[0]->name ?? __( 'Articol', 'comuna-agris' ) ); ?></span></div><h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1><div class="agris-single-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time><?php if ( 'yes' === $s['show_author'] ) : ?><span><?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ) ); ?></span><?php endif; ?><?php if ( $cats ) : ?><a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a><?php endif; ?></div></header><?php if ( 'yes' === $s['show_image'] && has_post_thumbnail( $post_id ) ) : ?><figure class="agris-single-image"><?php echo get_the_post_thumbnail( $post_id, 'full' ); ?></figure><?php endif; ?><div class="agris-single-layout"><div class="agris-single-content"><?php echo apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ); ?></div><?php if ( 'yes' === $s['show_share'] ) : ?><aside class="agris-share"><strong><?php esc_html_e( 'Distribuie', 'comuna-agris' ); ?></strong><a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink( $post_id ) ); ?>">Facebook</a><a href="mailto:?subject=<?php echo rawurlencode( get_the_title( $post_id ) ); ?>&body=<?php echo rawurlencode( get_permalink( $post_id ) ); ?>">Email</a><button type="button" data-agris-copy="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php esc_html_e( 'Copiază linkul', 'comuna-agris' ); ?></button></aside><?php endif; ?></div></article><?php
	}
}

final class Search_Box extends Base {
	public function get_name(): string { return 'agris-search-box'; }
	public function get_title(): string { return __( '24 · Kereső / keresési modal', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-search'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Kereső', 'comuna-agris' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Cím', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Căutare în portal' ) );
		$this->add_control( 'placeholder', array( 'label' => __( 'Helyőrző', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Căutați documente, anunțuri, servicii…' ) );
		$this->add_control( 'modal', array( 'label' => __( 'Modal módban', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes', 'description' => __( 'A Header keresőgombja ezt a modalt nyitja meg.', 'comuna-agris' ) ) );
		$this->end_controls_section();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $modal = 'yes' === $s['modal']; ?><div class="agris-search-widget <?php echo $modal ? 'is-modal' : ''; ?>" <?php echo $modal ? 'hidden' : ''; ?> data-agris-search-modal><div class="agris-search-dialog" role="<?php echo $modal ? 'dialog' : 'search'; ?>" <?php echo $modal ? 'aria-modal="true"' : ''; ?>><?php if ( $modal ) : ?><button type="button" data-agris-search-close aria-label="<?php esc_attr_e( 'Închide', 'comuna-agris' ); ?>">×</button><?php endif; ?><h2><?php echo esc_html( $s['title'] ); ?></h2><form role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>"><input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $s['placeholder'] ); ?>"><button class="agris-button agris-button-primary" type="submit"><?php esc_html_e( 'Caută', 'comuna-agris' ); ?></button></form></div></div><?php }
}
