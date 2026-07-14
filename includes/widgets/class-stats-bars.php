<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Stats_Bars extends Base {
	public function get_name(): string { return 'agris-stats-bars'; }
	public function get_title(): string { return __( '18 · Statisztika / megoszlás', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-skill-bar'; }
	protected function register_controls(): void { $this->start_controls_section( 'stats', array( 'label' => __( 'Adatok', 'comuna-agris' ) ) ); $this->common_heading_controls( 'Componență', 'Distribuția consiliului' ); $r = new Repeater(); $r->add_control( 'label', array( 'label' => __( 'Megnevezés', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Grup' ) ); $r->add_control( 'value', array( 'label' => __( 'Érték', 'comuna-agris' ), 'type' => Controls_Manager::NUMBER, 'default' => 50, 'min' => 0, 'max' => 100 ) ); $r->add_control( 'suffix', array( 'label' => __( 'Utótag', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => '%' ) ); $r->add_control( 'color', array( 'label' => __( 'Szín', 'comuna-agris' ), 'type' => Controls_Manager::COLOR, 'default' => '#1b5e20' ) ); $this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ label }}} · {{{ value }}}{{{ suffix }}}', 'default' => array( array( 'label' => 'UDMR', 'value' => 55, 'color' => '#1b5e20' ), array( 'label' => 'PNL', 'value' => 27, 'color' => '#1d4ed8' ), array( 'label' => 'PSD', 'value' => 18, 'color' => '#b42318' ) ) ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><section class="agris-stats"><?php $this->render_heading( $s ); ?><div class="agris-stats-list"><?php foreach ( $s['items_list'] as $item ) : $value = max( 0, min( 100, (float) $item['value'] ) ); ?><div class="agris-stat"><div><strong><?php echo esc_html( $item['label'] ); ?></strong><span><?php echo esc_html( $item['value'] . $item['suffix'] ); ?></span></div><i><b style="width:<?php echo esc_attr( $value ); ?>%;background:<?php echo esc_attr( $item['color'] ); ?>"></b></i></div><?php endforeach; ?></div></section><?php }
}
