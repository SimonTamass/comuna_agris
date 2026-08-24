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
		$this->add_control( 'decrease_text_label', array( 'label' => __( 'Szövegméret csökkentése', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Micșorează textul' ) );
		$this->add_control( 'increase_text_label', array( 'label' => __( 'Szövegméret növelése', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Mărește textul' ) );
		$this->add_control( 'back_to_top_label', array( 'label' => __( 'Vissza az oldal tetejére', 'comuna-agris' ), 'type' => Controls_Manager::TEXT, 'default' => 'Înapoi sus' ) );
		$this->end_controls_section();

		$this->register_common_style_controls();
	}
	protected function render(): void {
		$s = $this->get_settings_for_display();
		?>
		<div class="agris-a11y agris-a11y-<?php echo esc_attr( $s['position'] ); ?>">
			<div class="agris-a11y-panel" aria-label="<?php echo esc_attr( $s['options_label'] ); ?>" hidden>
				<div class="agris-a11y-heading">
					<span class="agris-a11y-title-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="4.5" r="2.2"></circle><path d="M5 8.5c2.1-.7 4.5-1 7-1s4.9.3 7 1"></path><path d="M12 8v4.2M12 12.2l-2.5 7.3M12 12.2l2.5 7.3"></path></svg>
					</span>
					<h2><?php echo esc_html( $s['title'] ); ?></h2>
				</div>
				<div class="agris-a11y-row agris-a11y-scale-row">
					<span><?php echo esc_html( $s['text_size_label'] ); ?></span>
					<span class="agris-scale-controls">
						<button class="agris-scale-button" type="button" data-agris-scale="down" aria-label="<?php echo esc_attr( $s['decrease_text_label'] ); ?>">−</button>
						<strong data-agris-scale-label>100%</strong>
						<button class="agris-scale-button" type="button" data-agris-scale="up" aria-label="<?php echo esc_attr( $s['increase_text_label'] ); ?>">+</button>
					</span>
				</div>
				<div class="agris-a11y-row"><span><?php echo esc_html( $s['contrast_label'] ); ?></span><button class="agris-switch" type="button" data-agris-a11y="contrast" aria-label="<?php echo esc_attr( $s['contrast_label'] ); ?>" aria-pressed="false"><i></i></button></div>
				<div class="agris-a11y-row"><span><?php echo esc_html( $s['grayscale_label'] ); ?></span><button class="agris-switch" type="button" data-agris-a11y="grayscale" aria-label="<?php echo esc_attr( $s['grayscale_label'] ); ?>" aria-pressed="false"><i></i></button></div>
				<div class="agris-a11y-row"><span><?php echo esc_html( $s['underline_label'] ); ?></span><button class="agris-switch" type="button" data-agris-a11y="underline" aria-label="<?php echo esc_attr( $s['underline_label'] ); ?>" aria-pressed="false"><i></i></button></div>
				<button class="agris-button agris-button-soft" type="button" data-agris-reset><?php echo esc_html( $s['reset_label'] ); ?></button>
			</div>
			<div class="agris-floating">
				<button type="button" data-agris-a11y-toggle aria-expanded="false" aria-label="<?php echo esc_attr( $s['options_label'] ); ?>">
					<svg class="agris-accessibility-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="4.5" r="2.2"></circle><path d="M5 8.5c2.1-.7 4.5-1 7-1s4.9.3 7 1"></path><path d="M12 8v4.2M12 12.2l-2.5 7.3M12 12.2l2.5 7.3"></path></svg>
				</button>
				<button type="button" data-agris-top aria-label="<?php echo esc_attr( $s['back_to_top_label'] ); ?>">↑</button>
			</div>
		</div>
		<?php
	}
}
