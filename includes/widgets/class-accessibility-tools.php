<?php
namespace ComunaAgris\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Accessibility_Tools extends Base {
	public function get_name(): string { return 'agris-accessibility'; }
	public function get_title(): string { return __( '03 · Accesibilitate', 'comuna-agris' ); }
	public function get_icon(): string { return 'eicon-accessibility'; }
	protected function register_controls(): void {
		$this->start_controls_section( 'content', array( 'label' => __( 'Etichete', 'comuna-agris' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Titlu', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Accesibilitate' ) );
		$this->add_control( 'position', array( 'label' => __( 'Poziție', 'comuna-agris' ), 'type' => Controls_Manager::SELECT, 'options' => array( 'right' => 'Dreapta', 'left' => 'Stânga' ), 'default' => 'right' ) );
		$this->add_control( 'text_size_label', array( 'label' => __( 'Szövegméret', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Mărime text' ) );
		$this->add_control( 'contrast_label', array( 'label' => __( 'Nagy kontraszt', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Contrast ridicat' ) );
		$this->add_control( 'grayscale_label', array( 'label' => __( 'Szürkeárnyalat', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Tonuri de gri' ) );
		$this->add_control( 'underline_label', array( 'label' => __( 'Aláhúzott hivatkozások', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Linkuri subliniate' ) );
		$this->add_control( 'reset_label', array( 'label' => __( 'Visszaállítás', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Resetează setările' ) );
		$this->add_control( 'options_label', array( 'label' => __( 'Akadálymentesítési beállítások', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Opțiuni de accesibilitate' ) );
		$this->add_control( 'back_to_top_label', array( 'label' => __( 'Vissza az oldal tetejére', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Înapoi sus' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		?>
		<div class="agris-a11y agris-a11y-<?php echo esc_attr( $s['position'] ); ?>">
			<div class="agris-a11y-panel" hidden><h2><?php echo esc_html( $s['title'] ); ?></h2><div class="agris-a11y-row"><span><?php echo esc_html( $s['text_size_label'] ); ?></span><span><button data-agris-scale="down">−</button><strong data-agris-scale-label>100%</strong><button data-agris-scale="up">+</button></span></div><div class="agris-a11y-row"><span><?php echo esc_html( $s['contrast_label'] ); ?></span><button class="agris-switch" data-agris-a11y="contrast" aria-pressed="false"><i></i></button></div><div class="agris-a11y-row"><span><?php echo esc_html( $s['grayscale_label'] ); ?></span><button class="agris-switch" data-agris-a11y="grayscale" aria-pressed="false"><i></i></button></div><div class="agris-a11y-row"><span><?php echo esc_html( $s['underline_label'] ); ?></span><button class="agris-switch" data-agris-a11y="underline" aria-pressed="false"><i></i></button></div><button class="agris-button agris-button-soft" data-agris-reset><?php echo esc_html( $s['reset_label'] ); ?></button></div>
			<div class="agris-floating"><button data-agris-a11y-toggle aria-expanded="false" aria-label="<?php echo esc_attr( $s['options_label'] ); ?>">A</button><button data-agris-top aria-label="<?php echo esc_attr( $s['back_to_top_label'] ); ?>">↑</button></div>
		</div>
		<?php
	}
}
