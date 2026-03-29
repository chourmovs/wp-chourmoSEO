<?php
/**
 * Keyword analysis settings page.
 *
 * @package BreznGEO
 */

namespace BreznGEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles registration and rendering of keyword analysis settings.
 */
class KeywordPage {

	public const OPTION_KEY = 'brezngeo_keyword_settings';

	/**
	 * Register hooks for the keyword settings page.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register the keyword settings with WordPress.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'brezngeo_keyword',
			self::OPTION_KEY,
			array( 'sanitize_callback' => array( $this, 'sanitize' ) )
		);
	}

	/**
	 * Enqueue assets for the keyword settings page.
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( 'brezngeo_page_brezngeo-keyword' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'brezngeo-admin', BREZNGEO_URL . 'assets/admin.css', array(), BREZNGEO_VERSION );
	}

	/**
	 * Sanitize keyword settings input.
	 *
	 * @param mixed $input Raw settings input.
	 * @return array Sanitized settings.
	 */
	public function sanitize( mixed $input ): array {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		$allowed_modes        = array( 'live', 'manual', 'save' );
		$clean['update_mode'] = in_array( $input['update_mode'] ?? '', $allowed_modes, true )
			? $input['update_mode'] : 'manual';

		$clean['target_density']            = max( 0.1, min( 5.0, (float) ( $input['target_density'] ?? 1.5 ) ) );
		$clean['min_occurrences_primary']   = max( 1, (int) ( $input['min_occurrences_primary'] ?? 3 ) );
		$clean['min_occurrences_secondary'] = max( 1, (int) ( $input['min_occurrences_secondary'] ?? 1 ) );
		$clean['live_debounce_ms']          = max( 300, min( 3000, (int) ( $input['live_debounce_ms'] ?? 800 ) ) );

		$all_post_types      = array_keys( get_post_types( array( 'public' => true ) ) );
		$clean['post_types'] = array_values(
			array_intersect(
				array_map( 'sanitize_key', (array) ( $input['post_types'] ?? array() ) ),
				$all_post_types
			)
		);
		if ( empty( $clean['post_types'] ) ) {
			$clean['post_types'] = array( 'post', 'page' );
		}

		return $clean;
	}

	/**
	 * Render the keyword settings page.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings   = KeywordMetaBox::get_settings();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		include BREZNGEO_DIR . 'includes/Admin/views/keyword-settings.php';
	}
}
