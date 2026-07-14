<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Home_Hero extends Base {
	public function get_name(): string { return 'agris-home-hero'; }
	public function get_title(): string { return __( '04 · Hero nyitóoldal', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-slider-push'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Tartalom', 'comuna-agris' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Állapot', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Ghișeul este deschis · Luni–Vineri 8:00–16:00', 'label_block' => true ) );
		$this->add_control( 'title', array( 'label' => __( 'Főcím', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Servicii publice transparente pentru Comuna Agriș.' ) );
		$this->add_control( 'description', array( 'label' => __( 'Leírás', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Portal modern pentru documente, formulare, hotărâri, anunțuri oficiale și informații utile pentru cetățeni.' ) );
		$this->add_control( 'primary_text', array( 'label' => __( 'Elsődleges gomb', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Vezi documentele' ) );
		$this->add_control( 'primary_link', array( 'label' => __( 'Elsődleges link', 'comuna-agris' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'secondary_text', array( 'label' => __( 'Másodlagos gomb', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Contact rapid' ) );
		$this->add_control( 'secondary_link', array( 'label' => __( 'Másodlagos link', 'comuna-agris' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'background', array( 'label' => __( 'Háttérkép / kollázs', 'comuna-agris' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_group_control( Group_Control_Image_Size::get_type(), array( 'name' => 'background', 'default' => 'full' ) );
		$this->add_control( 'show_search', array( 'label' => __( 'Kereső megjelenítése', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'updates', array( 'label' => __( 'Kiemelt újdonságok', 'comuna-agris' ) ) );
		$this->add_control( 'updates_title', array( 'label' => __( 'Panel címe', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Noutăți din portal' ) );
		$r = new Repeater();
		$r->add_control( 'day', array( 'label' => __( 'Nap / jel', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => '08' ) );
		$r->add_control( 'title', array( 'label' => __( 'Cím', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Anunț important' ) );
		$r->add_control( 'meta', array( 'label' => __( 'Meta', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Publicat recent' ) );
		$r->add_control( 'url', array( 'label' => __( 'Link', 'comuna-agris' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'updates_items', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ day }}} · {{{ title }}}', 'default' => array( array( 'day' => '08', 'title' => 'ANUNȚ INDIVIDUAL', 'meta' => 'Publicat recent' ), array( 'day' => '18', 'title' => 'H.C.L. nr. 13–16 / 2026', 'meta' => 'Hotărâri publicate' ), array( 'day' => '24', 'title' => 'H.C.L. nr. 4–9 / 2026', 'meta' => 'Arhivă Consiliul Local' ) ) ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		$bg = ! empty( $s['background']['url'] ) ? ' style="--agris-hero-image:url(' . esc_url( $s['background']['url'] ) . ')"' : '';
		?><section class="agris-home-hero"<?php echo $bg; ?>><div class="agris-shell agris-hero-grid"><div><div class="agris-eyebrow"><i></i><?php echo esc_html( $s['eyebrow'] ); ?></div><h1><?php echo esc_html( $s['title'] ); ?></h1><p><?php echo esc_html( $s['description'] ); ?></p><div class="agris-actions"><a class="agris-button agris-button-primary" <?php echo self::link_attrs( $s['primary_link'] ); ?>><?php echo esc_html( $s['primary_text'] ); ?></a><a class="agris-button agris-button-light" <?php echo self::link_attrs( $s['secondary_link'] ); ?>><?php echo esc_html( $s['secondary_text'] ); ?></a></div><?php if ( 'yes' === $s['show_search'] ) : ?><form class="agris-hero-search" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label class="screen-reader-text" for="agris-s-<?php echo esc_attr( $this->get_id() ); ?>"><?php esc_html_e( 'Caută', 'comuna-agris' ); ?></label><input id="agris-s-<?php echo esc_attr( $this->get_id() ); ?>" name="s" type="search" placeholder="<?php esc_attr_e( 'Căutați formulare, hotărâri, anunțuri…', 'comuna-agris' ); ?>"><button><?php esc_html_e( 'Caută', 'comuna-agris' ); ?></button></form><?php endif; ?></div><aside class="agris-hero-panel"><h2><?php echo esc_html( $s['updates_title'] ); ?></h2><?php foreach ( $s['updates_items'] as $item ) : ?><a class="agris-update" <?php echo self::link_attrs( $item['url'] ); ?>><b><?php echo esc_html( $item['day'] ); ?></b><span><strong><?php echo esc_html( $item['title'] ); ?></strong><small><?php echo esc_html( $item['meta'] ); ?></small></span></a><?php endforeach; ?></aside></div></section><?php
	}
}
