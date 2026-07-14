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

		$this->register_common_style_controls();
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
