<?php
namespace ComunaAgris;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Integration {
	private static ?Elementor_Integration $instance = null;

	public static function instance(): Elementor_Integration {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			'comuna-agris',
			array(
				'title' => esc_html__( 'Comuna Agriș', 'comuna-agris' ),
				'icon'  => 'eicon-site-identity',
			)
		);
	}

	public function register_widgets( $widgets_manager ): void {
		Widget_Registry::register( $widgets_manager );
	}
}
