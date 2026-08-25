<?php
namespace ComunaAgris\Controls;

use Elementor\Base_Data_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor control for selecting multiple attachments of any allowed type.
 */
final class Media_Files extends Base_Data_Control {
	public function get_type(): string {
		return 'agris_media_files';
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
			'agris-media-files-control',
			AGRIS_WIDGETS_URL . 'assets/js/editor-media-files-control.js',
			array( 'jquery', 'media-editor' ),
			AGRIS_WIDGETS_VERSION,
			true
		);
		wp_localize_script(
			'agris-media-files-control',
			'agrisMediaFilesControl',
			array(
				'chooseTitle'   => __( 'Letölthető fájlok kiválasztása', 'comuna-agris' ),
				'chooseButton'  => __( 'Kijelölt fájlok használata', 'comuna-agris' ),
				'chooseFiles'   => __( 'Fájlok kiválasztása', 'comuna-agris' ),
				'changeFiles'   => __( 'Kijelölés módosítása', 'comuna-agris' ),
				'clearFiles'    => __( 'Összes eltávolítása', 'comuna-agris' ),
				'removeFile'    => __( 'Fájl eltávolítása', 'comuna-agris' ),
				'empty'         => __( 'Nincs kiválasztott fájl.', 'comuna-agris' ),
				'selectedCount' => __( '%d fájl kiválasztva', 'comuna-agris' ),
			)
		);
	}

	public function content_template(): void {
		?>
		<div class="elementor-control-field agris-media-files-control">
			<# if ( data.label ) { #>
				<label class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper">
				<div class="agris-media-files-status" data-agris-media-files-status aria-live="polite"></div>
				<div class="agris-media-files-list" data-agris-media-files-list role="list"></div>
				<div class="agris-media-files-actions">
					<button type="button" class="elementor-button elementor-button-default agris-media-files-select">
						<i class="eicon-library-open" aria-hidden="true"></i>
						<span data-agris-media-files-button><?php echo esc_html__( 'Fájlok kiválasztása', 'comuna-agris' ); ?></span>
					</button>
					<button type="button" class="elementor-button elementor-button-default agris-media-files-clear">
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
