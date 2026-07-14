<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
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

	public function get_keywords(): array {
		return array( 'agris', 'comuna', 'primarie', 'municipality', 'elementor' );
	}

	protected function register_common_style_controls(): void {
		$this->start_controls_section(
			'agris_common_style',
			array(
				'label' => __( 'Megjelenés', 'comuna-agris' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'agris_brand_color',
			array(
				'label'     => __( 'Kiemelőszín', 'comuna-agris' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--agris-brand: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'agris_title_color',
			array(
				'label'     => __( 'Címszín', 'comuna-agris' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--agris-ink: {{VALUE}};',
					'{{WRAPPER}} .agris-title, {{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'agris_text_color',
			array(
				'label'     => __( 'Szövegszín', 'comuna-agris' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}}' => '--agris-muted: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'agris_surface_color',
			array(
				'label'     => __( 'Háttérszín', 'comuna-agris' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}}' => '--agris-surface: {{VALUE}};',
					'{{WRAPPER}} > .elementor-widget-container' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'agris_radius',
			array(
				'label'      => __( 'Sarokkerekítés', 'comuna-agris' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
				'selectors'  => array( '{{WRAPPER}}' => '--agris-radius: {{SIZE}}{{UNIT}}; --agris-radius-lg: calc({{SIZE}}{{UNIT}} + 8px);' ),
			)
		);

		$this->add_responsive_control(
			'agris_widget_padding',
			array(
				'label'      => __( 'Belső térköz', 'comuna-agris' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} > .elementor-widget-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'agris_title_typography',
				'label'    => __( 'Cím tipográfia', 'comuna-agris' ),
				'selector' => '{{WRAPPER}} .agris-title, {{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3',
			)
		);

		$this->end_controls_section();
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
