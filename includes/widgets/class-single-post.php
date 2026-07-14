<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

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

		$this->register_common_style_controls();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$post_id = get_the_ID();
		if ( ! $post_id ) { echo '<div class="agris-empty">' . esc_html__( 'Acest widget se afișează într-un șablon pentru articol individual.', 'comuna-agris' ) . '</div>'; return; }
		$cats = get_the_category( $post_id );
		?><article class="agris-single"><header><div class="agris-breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Acasă', 'comuna-agris' ); ?></a><span>/</span><span><?php echo esc_html( $cats[0]->name ?? __( 'Articol', 'comuna-agris' ) ); ?></span></div><h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1><div class="agris-single-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time><?php if ( 'yes' === $s['show_author'] ) : ?><span><?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ) ); ?></span><?php endif; ?><?php if ( $cats ) : ?><a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a><?php endif; ?></div></header><?php if ( 'yes' === $s['show_image'] && has_post_thumbnail( $post_id ) ) : ?><figure class="agris-single-image"><?php echo get_the_post_thumbnail( $post_id, 'full' ); ?></figure><?php endif; ?><div class="agris-single-layout"><div class="agris-single-content"><?php echo apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ); ?></div><?php if ( 'yes' === $s['show_share'] ) : ?><aside class="agris-share"><strong><?php esc_html_e( 'Distribuie', 'comuna-agris' ); ?></strong><a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink( $post_id ) ); ?>">Facebook</a><a href="mailto:?subject=<?php echo rawurlencode( get_the_title( $post_id ) ); ?>&body=<?php echo rawurlencode( get_permalink( $post_id ) ); ?>">Email</a><button type="button" data-agris-copy="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php esc_html_e( 'Copiază linkul', 'comuna-agris' ); ?></button></aside><?php endif; ?></div></article><?php
	}
}
