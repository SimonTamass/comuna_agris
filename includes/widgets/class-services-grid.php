<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Services_Grid extends Base {
	public function get_name(): string { return 'agris-services-grid'; }
	public function get_title(): string { return __( '07 · Szolgáltatások rács', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-gallery-grid'; }
	protected function register_controls(): void { $this->start_controls_section( 'items', array( 'label' => __( 'Szolgáltatások', 'comuna-agris' ) ) ); $r = new Repeater(); $r->add_control( 'icon', array( 'label' => __( 'Rövid jel', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'TAX' ) ); $r->add_control( 'image', array( 'label' => __( 'Kép', 'comuna-agris' ), 'type' => Controls_Manager::MEDIA ) ); $r->add_control( 'title', array( 'label' => __( 'Cím', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Taxe și impozite' ) ); $r->add_control( 'description', array( 'label' => __( 'Leírás', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => 'Informații și servicii pentru cetățeni.' ) ); $r->add_control( 'url', array( 'label' => __( 'Link', 'comuna-agris' ), 'type' => Controls_Manager::URL ) ); $this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ icon }}} · {{{ title }}}', 'default' => array( array( 'icon' => 'TAX', 'title' => 'Taxe și impozite' ), array( 'icon' => 'PDF', 'title' => 'Formulare tipizate' ), array( 'icon' => 'URB', 'title' => 'Urbanism' ), array( 'icon' => 'AGR', 'title' => 'Registru agricol' ) ) ) ); $this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( '2' => '2', '3' => '3', '4' => '4' ), 'default' => '4' ) ); $this->end_controls_section();
		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); ?><div class="agris-grid agris-grid-<?php echo esc_attr( $s['columns'] ); ?>"><?php foreach ( $s['items_list'] as $item ) : $image = (string) ( $item['image']['url'] ?? '' ); ?><a class="agris-service-card<?php echo $image ? ' has-image' : ''; ?>" <?php echo self::link_attrs( $item['url'] ); ?>><?php if ( $image ) : ?><span class="agris-service-image"><img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy"></span><?php else : ?><span class="agris-icon-box"><?php echo esc_html( $item['icon'] ); ?></span><?php endif; ?><h3><?php echo esc_html( $item['title'] ); ?></h3><?php if ( $item['description'] ) : ?><p><?php echo esc_html( $item['description'] ); ?></p><?php endif; ?><i aria-hidden="true">→</i></a><?php endforeach; ?></div><?php }
}
