<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Council_Members extends Base {
	public function get_name(): string { return 'agris-council-members'; }
	public function get_title(): string { return __( '11 · Helyi tanács', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-users'; }
	protected function register_controls(): void { $this->start_controls_section( 'members', array( 'label' => __( 'Tagok', 'comuna-agris' ) ) ); $r = new Repeater(); $r->add_control( 'name', array( 'label' => __( 'Név', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Nume consilier' ) ); $r->add_control( 'party', array( 'label' => __( 'Párt / csoport', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Independent' ) ); $r->add_control( 'role', array( 'label' => __( 'Szerepkör', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Consilier local' ) ); $r->add_control( 'email', array( 'label' => __( 'Email', 'comuna-agris' ), 'type' => Controls_Manager::TEXT ) ); $this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ name }}} · {{{ party }}}', 'default' => array( array( 'name' => 'Nume consilier', 'party' => 'UDMR' ), array( 'name' => 'Nume consilier', 'party' => 'PNL' ), array( 'name' => 'Nume consilier', 'party' => 'PSD' ) ) ) ); $this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( '2' => '2', '3' => '3', '4' => '4' ), 'default' => '3' ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><div class="agris-grid agris-grid-<?php echo esc_attr( $s['columns'] ); ?>"><?php foreach ( $s['items_list'] as $item ) : ?><article class="agris-council-card"><span><?php echo esc_html( $item['party'] ); ?></span><h3><?php echo esc_html( $item['name'] ); ?></h3><p><?php echo esc_html( $item['role'] ); ?></p><?php if ( $item['email'] ) : ?><a href="mailto:<?php echo esc_attr( antispambot( $item['email'] ) ); ?>"><?php echo esc_html( antispambot( $item['email'] ) ); ?></a><?php endif; ?></article><?php endforeach; ?></div><?php }
}
