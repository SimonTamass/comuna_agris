<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Search_Box extends Base {
	public function get_name(): string { return 'agris-search-box'; }
	public function get_title(): string { return __( '24 · Kereső / keresési modal', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-search'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Kereső', 'comuna-agris' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Cím', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Căutare în portal' ) );
		$this->add_control( 'placeholder', array( 'label' => __( 'Helyőrző', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Căutați documente, anunțuri, servicii…' ) );
		$this->add_control( 'button_text', array( 'label' => __( 'Gombfelirat', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Caută' ) );
		$this->add_control( 'close_label', array( 'label' => __( 'Bezárás címkéje', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Închide' ) );
		$this->add_control( 'language', array( 'label' => __( 'Nyelv', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( '' => 'Automatikus', 'ro' => 'Română', 'hu' => 'Magyar' ), 'default' => '' ) );
		$this->add_control( 'modal', array( 'label' => __( 'Modal módban', 'comuna-agris' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes', 'description' => __( 'A Header keresőgombja ezt a modalt nyitja meg.', 'comuna-agris' ) ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void { $s = $this->get_settings_for_display(); $modal = 'yes' === $s['modal']; ?><div class="agris-search-widget <?php echo $modal ? 'is-modal' : ''; ?>" <?php echo $modal ? 'hidden' : ''; ?> data-agris-search-modal><div class="agris-search-dialog" role="<?php echo $modal ? 'dialog' : 'search'; ?>" <?php echo $modal ? 'aria-modal="true"' : ''; ?>><?php if ( $modal ) : ?><button type="button" data-agris-search-close aria-label="<?php echo esc_attr( $s['close_label'] ); ?>">×</button><?php endif; ?><h2><?php echo esc_html( $s['title'] ); ?></h2><form role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>"><?php if ( ! empty( $s['language'] ) ) : ?><input type="hidden" name="lang" value="<?php echo esc_attr( $s['language'] ); ?>"><?php endif; ?><input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $s['placeholder'] ); ?>"><button class="agris-button agris-button-primary" type="submit"><?php echo esc_html( $s['button_text'] ); ?></button></form></div></div><?php }
}
