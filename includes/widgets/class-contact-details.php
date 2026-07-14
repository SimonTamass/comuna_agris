<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Contact_Details extends Base {
	public function get_name(): string { return 'agris-contact-details'; }
	public function get_title(): string { return __( '13 · Kapcsolati adatok', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-map-pin'; }
	protected function register_controls(): void { $this->start_controls_section( 'content', array( 'label' => __( 'Kapcsolat', 'comuna-agris' ) ) ); $this->common_heading_controls( 'Contact', 'Primăria Comunei Agriș' ); $this->add_control( 'address', array( 'label' => __( 'Cím', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Agriș, str. Csury Balint, nr. 68, Satu Mare' ) ); $this->add_control( 'phone', array( 'label' => __( 'Telefon', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => '0261 878 112' ) ); $this->add_control( 'email', array( 'label' => __( 'Email', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'primaria@comunaagris.ro' ) ); $this->add_control( 'hours', array( 'label' => __( 'Nyitvatartás', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Luni–Vineri: 8:00–16:00' ) ); $this->add_control( 'map_embed', array( 'label' => __( 'Google Maps beágyazási URL', 'comuna-agris' ), 'type' => Controls_Manager::URL, 'description' => __( 'A Google Maps „Térkép beágyazása” iframe src értéke.', 'comuna-agris' ) ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><div class="agris-contact-layout"><div class="agris-contact-card"><?php $this->render_heading( $s ); ?><div class="agris-detail-list"><div><b>LOC</b><span><?php echo nl2br( esc_html( $s['address'] ) ); ?></span></div><a href="tel:<?php echo esc_attr( preg_replace( '/\D+/', '', $s['phone'] ) ); ?>"><b>TEL</b><span><?php echo esc_html( $s['phone'] ); ?></span></a><a href="mailto:<?php echo esc_attr( antispambot( $s['email'] ) ); ?>"><b>@</b><span><?php echo esc_html( antispambot( $s['email'] ) ); ?></span></a><div><b>ORĂ</b><span><?php echo nl2br( esc_html( $s['hours'] ) ); ?></span></div></div></div><?php if ( $s['map_embed']['url'] ) : ?><iframe class="agris-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?php echo esc_url( $s['map_embed']['url'] ); ?>" title="<?php esc_attr_e( 'Hartă', 'comuna-agris' ); ?>"></iframe><?php endif; ?></div><?php }
}
