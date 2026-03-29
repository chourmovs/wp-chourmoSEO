<?php
/**
 * Plugin Name:       BreznGEO
 * Plugin URI:        https://brezngeo.com/
 * Description:       AI-powered meta descriptions, GEO structured data, and llms.txt for WordPress.
 * Version:           1.2.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            NoSchmarrn.dev
 * Author URI:        https://noschmarrn.dev/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       brezngeo
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BREZNGEO_VERSION', '1.2.1' );
define( 'BREZNGEO_FILE', __FILE__ );
define( 'BREZNGEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'BREZNGEO_URL', plugin_dir_url( __FILE__ ) );

require_once BREZNGEO_DIR . 'includes/Core.php';

add_action( 'plugins_loaded', static function (): void {
	\BreznGEO\Core::instance()->init();
} );

register_activation_hook(
	BREZNGEO_FILE,
	function () {
		require_once BREZNGEO_DIR . 'includes/Features/RobotsTxt.php';
		require_once BREZNGEO_DIR . 'includes/Features/CrawlerLog.php';
		\BreznGEO\Features\CrawlerLog::install();
		add_rewrite_rule( '^llms\.txt$', 'index.php?brezngeo_llms=1', 'top' );
		flush_rewrite_rules();
		if ( ! get_option( 'brezngeo_first_activated' ) ) {
			update_option( 'brezngeo_first_activated', time() );
		}
	}
);
