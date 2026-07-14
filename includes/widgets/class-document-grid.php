<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Document_Grid extends Base {
	public function get_name(): string { return 'agris-document-grid'; }
	public function get_title(): string { return __( '20 · Kézi dokumentumkártyák', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-document-file'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'items', array( 'label' => __( 'Dokumentumok', 'comuna-agris' ) ) );
		$r = new Repeater();
		$r->add_control( 'icon', array( 'label' => __( 'Jel', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'HCL' ) );
		$r->add_control( 'title', array( 'label' => __( 'Cím', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Hotărârea Consiliului Local' ) );
		$r->add_control( 'meta', array( 'label' => __( 'Dátum / leírás', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Publicat recent' ) );
		$r->add_control( 'category', array( 'label' => __( 'Szűrőkategória', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Hotărâri' ) );
		$r->add_control( 'url', array( 'label' => __( 'Fájl vagy oldal', 'comuna-agris' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'items_list', array( 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ icon }}} · {{{ title }}}', 'default' => array( array( 'icon' => 'HCL', 'title' => 'H.C.L. nr. 13–16 / 2026', 'meta' => '18 mai 2026' ), array( 'icon' => 'PDF', 'title' => 'Document public', 'meta' => 'Publicat recent' ) ) ) );
		$this->add_control( 'columns', array( 'label' => __( 'Oszlopok', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( '1' => '1', '2' => '2', '3' => '3' ), 'default' => '3' ) );
		$this->add_control( 'filters', array( 'label' => __( 'Kategóriaszűrők', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $cats = array_unique( array_filter( array_column( $s['items_list'], 'category' ) ) ); ?><div class="agris-document-widget"><?php if ( 'yes' === $s['filters'] && $cats ) : ?><div class="agris-filters"><button class="is-active" data-agris-filter="all"><?php esc_html_e( 'Toate', 'comuna-agris' ); ?></button><?php foreach ( $cats as $cat ) : ?><button data-agris-filter="<?php echo esc_attr( sanitize_title( $cat ) ); ?>"><?php echo esc_html( $cat ); ?></button><?php endforeach; ?></div><?php endif; ?><div class="agris-grid agris-grid-<?php echo esc_attr( $s['columns'] ); ?>" data-agris-filter-items><?php foreach ( $s['items_list'] as $item ) : ?><a class="agris-doc-card" data-agris-category="<?php echo esc_attr( sanitize_title( $item['category'] ) ); ?>" <?php echo self::link_attrs( $item['url'] ); ?>><b><?php echo esc_html( $item['icon'] ); ?></b><span><strong><?php echo esc_html( $item['title'] ); ?></strong><small><?php echo esc_html( $item['meta'] ); ?></small></span></a><?php endforeach; ?></div></div><?php }
}
