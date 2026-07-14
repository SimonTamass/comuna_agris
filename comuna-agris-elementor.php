<?php
/**
 * Plugin Name: Comuna Agriș Elementor Widgets
 * Description: Modular Elementor widget suite and document library for rebuilding the Comuna Agriș municipal website.
 * Version: 1.7.0
 * Author: Comuna Agriș
 * Text Domain: comuna-agris
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Update URI: https://comunaagris.ro/cpanel-git-managed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGRIS_WIDGETS_VERSION', '1.7.0' );
define( 'AGRIS_WIDGETS_FILE', __FILE__ );
define( 'AGRIS_WIDGETS_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGRIS_WIDGETS_URL', plugin_dir_url( __FILE__ ) );

require_once AGRIS_WIDGETS_PATH . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( '\ComunaAgris\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

add_action(
	'plugins_loaded',
	static function (): void {
		\ComunaAgris\Plugin::instance();
	}
);
