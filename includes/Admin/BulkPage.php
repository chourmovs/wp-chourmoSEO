<?php
namespace BreznGEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BreznGEO\ProviderRegistry;

class BulkPage {
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets( string $hook ): void {
		if ( $hook !== 'brezngeo_page_brezngeo-bulk' ) {
			return;
		}
		wp_enqueue_style( 'brezngeo-admin', BREZNGEO_URL . 'assets/admin.css', array(), BREZNGEO_VERSION );
		wp_enqueue_script( 'brezngeo-bulk', BREZNGEO_URL . 'assets/bulk.js', array( 'jquery' ), BREZNGEO_VERSION, true );
		$settings = SettingsPage::getSettings();
		wp_localize_script(
			'brezngeo-bulk',
			'brezngeoBulk',
			array(
				'nonce'     => wp_create_nonce( 'brezngeo_admin' ),
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'isLocked'  => \BreznGEO\Helpers\BulkQueue::isLocked(),
				'lockAge'   => \BreznGEO\Helpers\BulkQueue::lockAge(),
				'rateDelay' => 6000,
				'costs'     => $settings['costs'] ?? array(),
				'i18n'      => array(
					'lockWarning'      => __( 'A bulk process is already running', 'brezngeo' ),
					'since'            => __( 'since', 'brezngeo' ),
					'postsWithoutMeta' => __( 'Posts without meta description:', 'brezngeo' ),
					'total'            => __( 'Total:', 'brezngeo' ),
					'inputTokens'      => __( 'Input tokens', 'brezngeo' ),
					'outputTokens'     => __( 'Output tokens', 'brezngeo' ),
					'logStart'         => __( '▶ Start — max {limit} posts, Provider: {provider}', 'brezngeo' ),
					'stopRequested'    => __( 'Stop requested…', 'brezngeo' ),
					'logProcess'       => __( '↻ Processing {count} posts… ({remaining} remaining)', 'brezngeo' ),
					'unknownError'     => __( 'Unknown error', 'brezngeo' ),
					'attempt'          => __( 'attempt', 'brezngeo' ),
					'networkError'     => __( 'Network error', 'brezngeo' ),
					'processed'        => __( 'processed', 'brezngeo' ),
					'done'             => __( '— Done —', 'brezngeo' ),
					'postsFailed'      => __( 'posts failed:', 'brezngeo' ),
				),
			)
		);
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings  = SettingsPage::getSettings();
		$registry  = ProviderRegistry::instance();
		$providers = $registry->all();
		$has_ai    = ! empty( $settings['ai_enabled'] )
					&& ! empty( $settings['api_keys'][ $settings['provider'] ] );
		include BREZNGEO_DIR . 'includes/Admin/views/bulk.php';
	}
}
