<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Page_Hero extends Base {
	public function get_name(): string { return 'agris-page-hero'; }
	public function get_title(): string { return __( '05 · Hero belső oldal', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-banner'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Tartalom', 'comuna-agris' ) ) );
		$this->common_heading_controls( '', 'Titlul paginii' );
		$this->add_control( 'parent_label', array( 'label' => __( 'Morzsa szülő', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Acasă' ) );
		$this->add_control( 'parent_link', array( 'label' => __( 'Morzsa link', 'comuna-agris' ), 'type' => Controls_Manager::URL, 'default' => array( 'url' => '/' ) ) );
		$this->add_control( 'current_label', array( 'label' => __( 'Aktuális oldal', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Pagina curentă' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><section id="main-content" class="agris-page-hero agris-title-band"><div class="agris-shell agris-title-band-inner"><div class="agris-breadcrumbs"><a <?php echo self::link_attrs( $s['parent_link'] ); ?>><?php echo esc_html( $s['parent_label'] ); ?></a><span>/</span><span><?php echo esc_html( $s['current_label'] ); ?></span></div><?php $this->render_heading( $s, 'h1' ); ?></div></section><?php }
}
