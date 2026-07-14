<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Contact_Form extends Base {
	public function get_name(): string { return 'agris-contact-form'; }
	public function get_title(): string { return __( '14 · Kapcsolati űrlap', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-form-horizontal'; }
	protected function register_controls(): void { $this->start_controls_section( 'content', array( 'label' => __( 'Űrlap', 'comuna-agris' ) ) ); $this->common_heading_controls( 'Mesaj', 'Trimiteți-ne un mesaj' ); $this->add_control( 'recipient', array( 'label' => __( 'Címzett email', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => get_option( 'admin_email' ) ) ); $this->add_control( 'button_text', array( 'label' => __( 'Gomb', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Trimite mesajul' ) ); $this->add_control( 'privacy_text', array( 'label' => __( 'Adatvédelmi szöveg', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Datele sunt folosite exclusiv pentru a răspunde solicitării.' ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $recipient = sanitize_email( $s['recipient'] ) ?: get_option( 'admin_email' ); ?><section class="agris-form-card"><?php $this->render_heading( $s ); ?><form class="agris-contact-form" novalidate><input type="hidden" name="recipient" value="<?php echo esc_attr( $recipient ); ?>"><input type="hidden" name="recipient_hash" value="<?php echo esc_attr( wp_hash( $recipient . '|agris_contact' ) ); ?>"><div class="agris-form-two"><label><?php esc_html_e( 'Nume și prenume', 'comuna-agris' ); ?> *<input name="name" required autocomplete="name"></label><label><?php esc_html_e( 'Email', 'comuna-agris' ); ?> *<input name="email" type="email" required autocomplete="email"></label></div><label><?php esc_html_e( 'Subiect', 'comuna-agris' ); ?> *<input name="subject" required></label><label><?php esc_html_e( 'Mesaj', 'comuna-agris' ); ?> *<textarea name="message" rows="6" required></textarea></label><label class="agris-honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label><p class="agris-form-privacy"><?php echo esc_html( $s['privacy_text'] ); ?></p><button class="agris-button agris-button-primary" type="submit"><?php echo esc_html( $s['button_text'] ); ?></button><div class="agris-form-status" role="status" aria-live="polite"></div></form></section><?php }
}
