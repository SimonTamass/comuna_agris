<?php
namespace ComunaAgris;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Assets {
	private static ?Assets $instance = null;

	public static function instance(): Assets {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
	}

	public function body_classes( array $classes ): array {
		if ( is_page( 'home-ro' ) ) {
			$classes[] = 'agris-home-page';
		}

		return $classes;
	}

	public function register(): void {
		wp_register_style(
			'agris-fonts',
			'https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Source+Sans+3:wght@400;500;600;700;800;900&display=swap',
			array(),
			null
		);
		wp_register_style( 'agris-widgets', AGRIS_WIDGETS_URL . 'assets/css/frontend.css', array( 'agris-fonts', 'dashicons' ), AGRIS_WIDGETS_VERSION );
		wp_register_script( 'agris-widgets', AGRIS_WIDGETS_URL . 'assets/js/frontend.js', array(), AGRIS_WIDGETS_VERSION, true );
		wp_localize_script(
			'agris-widgets',
			'agrisWidgets',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'agris-contact' ),
				'i18n'    => array(
					'error'   => esc_html__( 'A apărut o eroare. Încercați din nou.', 'comuna-agris' ),
					'sending' => esc_html__( 'Se trimite…', 'comuna-agris' ),
				),
			)
		);
	}

	public function enqueue_editor_styles(): void {
		wp_enqueue_style( 'agris-editor', AGRIS_WIDGETS_URL . 'assets/css/editor.css', array(), AGRIS_WIDGETS_VERSION );
	}
}
