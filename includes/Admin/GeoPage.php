<?php
namespace BreznGEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BreznGEO\Features\GeoBlock;

class GeoPage {
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_settings(): void {
		register_setting(
			'brezngeo_geo',
			GeoBlock::OPTION_KEY,
			array( 'sanitize_callback' => array( $this, 'sanitize' ) )
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( $hook !== 'brezngeo_page_brezngeo-geo' ) {
			return;
		}
		wp_enqueue_style( 'brezngeo-admin', BREZNGEO_URL . 'assets/admin.css', array(), BREZNGEO_VERSION );
	}

	public function sanitize( mixed $input ): array {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		$clean['enabled']            = ! empty( $input['enabled'] );
		$clean['regen_on_update']    = ! empty( $input['regen_on_update'] );
		$clean['allow_prompt_addon'] = ! empty( $input['allow_prompt_addon'] );

		$allowed_modes = array( 'auto_on_publish', 'manual_only', 'hybrid' );
		$clean['mode'] = in_array( $input['mode'] ?? '', $allowed_modes, true )
			? $input['mode'] : 'auto_on_publish';

		$allowed_positions = array( 'after_first_p', 'top', 'bottom' );
		$clean['position'] = in_array( $input['position'] ?? '', $allowed_positions, true )
			? $input['position'] : 'after_first_p';

		$allowed_styles        = array( 'details_collapsible', 'open_always', 'store_only_no_frontend' );
		$clean['output_style'] = in_array( $input['output_style'] ?? '', $allowed_styles, true )
			? $input['output_style'] : 'details_collapsible';

		$allowed_themes = array( 'light', 'dark', 'minimal', 'brezn' );
		$clean['theme'] = in_array( $input['theme'] ?? '', $allowed_themes, true )
			? $input['theme'] : 'light';

		$clean['accent_color'] = sanitize_hex_color( $input['accent_color'] ?? '' ) ?? '';

		$clean['title']          = sanitize_text_field( $input['title'] ?? 'Quick Overview' );
		$clean['label_summary']  = sanitize_text_field( $input['label_summary'] ?? 'Summary' );
		$clean['label_bullets']  = sanitize_text_field( $input['label_bullets'] ?? 'Key Points' );
		$clean['label_faq']      = sanitize_text_field( $input['label_faq'] ?? 'FAQ' );
		$clean['prompt_default'] = sanitize_textarea_field(
			! empty( $input['prompt_default'] ) ? $input['prompt_default'] : GeoBlock::getDefaultPrompt()
		);
		$clean['word_threshold'] = max( 50, (int) ( $input['word_threshold'] ?? 350 ) );

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

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings   = GeoBlock::getSettings();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$global     = SettingsPage::getSettings();
		$api_key    = $global['api_keys'][ $global['provider'] ] ?? '';
		$has_ai     = ( $global['ai_enabled'] ?? false ) && ! empty( $api_key );
		include BREZNGEO_DIR . 'includes/Admin/views/geo.php';
	}
}
