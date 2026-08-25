<?php
namespace ComunaAgris\Controls;

use Elementor\Base_Data_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor control for selecting multiple images directly from one media frame.
 */
final class Media_Images extends Base_Data_Control {
	public function get_type(): string {
		return 'agris_media_images';
	}

	public function get_default_value(): array {
		return array();
	}

	protected function get_default_settings(): array {
		return array(
			'label_block' => true,
		);
	}

	public function enqueue(): void {
		wp_enqueue_media();
		wp_enqueue_script(
			'agris-media-images-control',
			AGRIS_WIDGETS_URL . 'assets/js/editor-media-images-control.js',
			array( 'jquery', 'media-editor' ),
			AGRIS_WIDGETS_VERSION,
			true
		);
		wp_localize_script(
			'agris-media-images-control',
			'agrisMediaImagesControl',
			array(
				'chooseTitle'   => __( 'Galériaképek kiválasztása', 'comuna-agris' ),
				'chooseButton'  => __( 'Kijelölt képek használata', 'comuna-agris' ),
				'chooseImages'  => __( 'Több kép kiválasztása', 'comuna-agris' ),
				'changeImages'  => __( 'Kijelölés módosítása', 'comuna-agris' ),
				'clearImages'   => __( 'Összes eltávolítása', 'comuna-agris' ),
				'removeImage'   => __( 'Kép eltávolítása', 'comuna-agris' ),
				'empty'         => __( 'Nincs kiválasztott kép.', 'comuna-agris' ),
				'selectedCount' => __( '%d kép kiválasztva', 'comuna-agris' ),
			)
		);
	}

	public function content_template(): void {
		?>
		<div class="elementor-control-field agris-media-images-control">
			<# if ( data.label ) { #>
				<label class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper">
				<div class="agris-media-images-status" data-agris-media-images-status aria-live="polite"></div>
				<div class="agris-media-images-list" data-agris-media-images-list role="list"></div>
				<div class="agris-media-images-actions">
					<button type="button" class="elementor-button elementor-button-default agris-media-images-select">
						<i class="eicon-gallery-grid" aria-hidden="true"></i>
						<span data-agris-media-images-button><?php echo esc_html__( 'Több kép kiválasztása', 'comuna-agris' ); ?></span>
					</button>
					<button type="button" class="elementor-button elementor-button-default agris-media-images-clear">
						<i class="eicon-trash-o" aria-hidden="true"></i>
						<span><?php echo esc_html__( 'Összes eltávolítása', 'comuna-agris' ); ?></span>
					</button>
				</div>
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
