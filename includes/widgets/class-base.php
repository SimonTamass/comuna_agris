<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
abstract class Base extends Widget_Base {
	public function get_categories(): array {
		return array( 'comuna-agris' );
	}

	public function get_style_depends(): array {
		return array( 'agris-widgets' );
	}

	public function get_script_depends(): array {
		return array( 'agris-widgets' );
	}

	protected function common_heading_controls( string $default_kicker = '', string $default_title = '' ): void {
		$this->add_control( 'kicker', array( 'label' => __( 'Supratitlu', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => $default_kicker, 'label_block' => true ) );
		$this->add_control( 'title', array( 'label' => __( 'Titlu', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => $default_title, 'label_block' => true ) );
		$this->add_control( 'description', array( 'label' => __( 'Descriere', 'comuna-agris' ), 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
	}

	protected function render_heading( array $settings, string $level = 'h2' ): void {
		$level = in_array( $level, array( 'h1', 'h2', 'h3' ), true ) ? $level : 'h2';
		if ( ! empty( $settings['kicker'] ) ) {
			echo '<div class="agris-kicker">' . esc_html( $settings['kicker'] ) . '</div>';
		}
		if ( ! empty( $settings['title'] ) ) {
			echo '<' . esc_attr( $level ) . ' class="agris-title">' . esc_html( $settings['title'] ) . '</' . esc_attr( $level ) . '>';
		}
		if ( ! empty( $settings['description'] ) ) {
			echo '<p class="agris-lead">' . wp_kses_post( nl2br( $settings['description'] ) ) . '</p>';
		}
	}

	protected static function link_attrs( array $link ): string {
		if ( empty( $link['url'] ) ) {
			return '';
		}
		$attrs = ' href="' . esc_url( $link['url'] ) . '"';
		if ( ! empty( $link['is_external'] ) ) {
			$attrs .= ' target="_blank"';
		}
		$rel = array();
		if ( ! empty( $link['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}
		if ( ! empty( $link['is_external'] ) ) {
			$rel[] = 'noopener';
		}
		if ( $rel ) {
			$attrs .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
		}
		return $attrs;
	}

	protected static function menus(): array {
		$options = array( '' => __( '— Selectați meniul —', 'comuna-agris' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$options[ (string) $menu->term_id ] = $menu->name;
		}
		return $options;
	}

	protected static function post_types(): array {
		$options = array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			$options[ $type->name ] = $type->labels->singular_name;
		}
		return $options;
	}
}
