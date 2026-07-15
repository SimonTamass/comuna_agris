<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Section_Heading extends Base {
	public function get_name(): string { return 'agris-section-heading'; }
	public function get_title(): string { return __( '06 · Szekció fejléc', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-heading'; }
	protected function register_controls(): void { $this->start_controls_section( 'content', array( 'label' => __( 'Tartalom', 'comuna-agris' ) ) ); $this->common_heading_controls( 'Secțiune', 'Titlul secțiunii' ); $this->add_control( 'theme', array( 'label' => __( 'Megjelenés', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'light' => __( 'Világos', 'comuna-agris' ), 'dark' => __( 'Sötét', 'comuna-agris' ) ), 'default' => 'light' ) ); $this->add_control( 'button_text', array( 'label' => __( 'Gomb szövege', 'comuna-agris' ), 'type' => Controls_Manager::TEXT ) ); $this->add_control( 'button_link', array( 'label' => __( 'Gomb linkje', 'comuna-agris' ), 'type' => Controls_Manager::URL ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><div class="agris-section-heading<?php echo 'dark' === ( $s['theme'] ?? 'light' ) ? ' is-dark' : ''; ?>"><div><?php $this->render_heading( $s ); ?></div><?php if ( $s['button_text'] ) : ?><a class="agris-button agris-button-outline" <?php echo self::link_attrs( $s['button_link'] ); ?>><?php echo esc_html( $s['button_text'] ); ?></a><?php endif; ?></div><?php }
}
