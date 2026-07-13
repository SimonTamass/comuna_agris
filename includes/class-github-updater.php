<?php
namespace ComunaAgris;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Github_Updater {
	private const REPOSITORY = 'SimonTamass/comuna_agris';
	private const CACHE_KEY = 'agris_widgets_github_release';

	private string $plugin_basename;

	public function __construct() {
		$this->plugin_basename = plugin_basename( AGRIS_WIDGETS_FILE );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_information' ), 20, 3 );
		add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );
	}

	public function check_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		$release = $this->release();
		if ( ! $release || version_compare( $release['version'], AGRIS_WIDGETS_VERSION, '<=' ) ) {
			return $transient;
		}

		$transient->response[ $this->plugin_basename ] = (object) array(
			'id'           => 'github.com/' . self::REPOSITORY,
			'slug'         => dirname( $this->plugin_basename ),
			'plugin'       => $this->plugin_basename,
			'new_version'  => $release['version'],
			'url'          => 'https://github.com/' . self::REPOSITORY,
			'package'      => $release['package'],
			'icons'        => array(),
			'banners'      => array(),
			'tested'       => get_bloginfo( 'version' ),
			'requires_php' => '8.0',
		);

		return $transient;
	}

	public function plugin_information( $result, string $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || dirname( $this->plugin_basename ) !== $args->slug ) {
			return $result;
		}

		$release = $this->release();
		if ( ! $release ) {
			return $result;
		}

		return (object) array(
			'name'          => 'Comuna Agriș Elementor Widgets',
			'slug'          => dirname( $this->plugin_basename ),
			'version'       => $release['version'],
			'author'        => '<a href="https://github.com/SimonTamass">SimonTamass</a>',
			'homepage'      => 'https://github.com/' . self::REPOSITORY,
			'requires'      => '6.4',
			'requires_php'  => '8.0',
			'tested'        => get_bloginfo( 'version' ),
			'download_link' => $release['package'],
			'sections'      => array(
				'description' => __( 'Widgeturi Elementor și instrumente sigure pentru reconstruirea site-ului Comuna Agriș fără schimbarea adreselor URL.', 'comuna-agris' ),
				'changelog'   => wp_kses_post( nl2br( $release['notes'] ) ),
			),
		);
	}

	public function clear_cache( $upgrader, array $options ): void {
		if ( 'update' === ( $options['action'] ?? '' ) && 'plugin' === ( $options['type'] ?? '' ) ) {
			delete_site_transient( self::CACHE_KEY );
		}
	}

	private function release(): ?array {
		$cached = get_site_transient( self::CACHE_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . self::REPOSITORY . '/releases/latest',
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'Comuna-Agris-WordPress/' . AGRIS_WIDGETS_VERSION,
				),
			)
		);
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$tag = sanitize_text_field( $data['tag_name'] ?? '' );
		$package = esc_url_raw( $data['zipball_url'] ?? '' );
		if ( ! $tag || ! $package ) {
			return null;
		}

		$release = array(
			'version' => ltrim( $tag, 'vV' ),
			'package' => $package,
			'notes'   => sanitize_textarea_field( $data['body'] ?? '' ),
		);
		set_site_transient( self::CACHE_KEY, $release, 6 * HOUR_IN_SECONDS );
		return $release;
	}
}
