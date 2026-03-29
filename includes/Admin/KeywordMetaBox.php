<?php
/**
 * Keyword analysis meta box for the post editor.
 *
 * @package BreznGEO
 */

namespace BreznGEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BreznGEO\Features\KeywordAnalysis;
use BreznGEO\Helpers\KeywordVariants;
use BreznGEO\Helpers\TokenEstimator;
use BreznGEO\ProviderRegistry;

/**
 * Registers and renders the keyword analysis meta box with AJAX handlers.
 */
class KeywordMetaBox {

	public const META_MAIN      = '_brezngeo_keyword_main';
	public const META_SECONDARY = '_brezngeo_keyword_secondary';
	public const META_RESULTS   = '_brezngeo_keyword_results';

	/**
	 * Register hooks for the keyword meta box.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_brezngeo_keyword_analyze', array( $this, 'ajax_analyze' ) );
		add_action( 'wp_ajax_brezngeo_keyword_ai_suggest', array( $this, 'ajax_ai_suggest' ) );
		add_action( 'wp_ajax_brezngeo_keyword_ai_optimize', array( $this, 'ajax_ai_optimize' ) );
		add_action( 'wp_ajax_brezngeo_keyword_ai_semantic', array( $this, 'ajax_ai_semantic' ) );
	}

	/**
	 * Add the keyword analysis meta box to configured post types.
	 *
	 * @return void
	 */
	public function add_boxes(): void {
		$settings   = self::get_settings();
		$post_types = $settings['post_types'] ?? array( 'post', 'page' );

		foreach ( $post_types as $pt ) {
			add_meta_box(
				'brezngeo_keyword_box',
				__( 'Keyword Analysis (BreznGEO)', 'brezngeo' ),
				array( $this, 'render' ),
				$pt,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Render the keyword analysis meta box.
	 *
	 * @param \WP_Post $post The current post object.
	 * @return void
	 */
	public function render( \WP_Post $post ): void {
		$main_keyword    = get_post_meta( $post->ID, self::META_MAIN, true );
		$secondary_json  = get_post_meta( $post->ID, self::META_SECONDARY, true );
		$secondary       = ! empty( $secondary_json ) ? json_decode( $secondary_json, true ) : array();
		$cached_results  = get_post_meta( $post->ID, self::META_RESULTS, true );
		$settings        = self::get_settings();
		$ai_features     = AdminMenu::get_ai_features();
		$global_settings = SettingsPage::getSettings();
		$has_ai          = ! empty( $global_settings['ai_enabled'] )
			&& ! empty( $global_settings['api_keys'][ $global_settings['provider'] ] ?? '' );
		$ai_keywords     = $has_ai && ! empty( $ai_features['keywords'] );

		wp_nonce_field( 'brezngeo_keyword_save_' . $post->ID, 'brezngeo_keyword_nonce' );

		include BREZNGEO_DIR . 'includes/Admin/views/keyword-meta-box.php';
	}

	/**
	 * Save keyword meta data on post save.
	 *
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 * @return void
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['brezngeo_keyword_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brezngeo_keyword_nonce'] ) ), 'brezngeo_keyword_save_' . $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$main = sanitize_text_field( wp_unslash( $_POST['brezngeo_keyword_main'] ?? '' ) );
		update_post_meta( $post_id, self::META_MAIN, $main );

		$raw_secondary = isset( $_POST['brezngeo_keyword_secondary'] ) && is_array( $_POST['brezngeo_keyword_secondary'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['brezngeo_keyword_secondary'] ) )
			: array();
		$secondary     = array_values(
			array_filter(
				$raw_secondary,
				function ( $kw ) {
					return '' !== trim( $kw );
				}
			)
		);
		update_post_meta( $post_id, self::META_SECONDARY, wp_json_encode( $secondary, JSON_UNESCAPED_UNICODE ) );
	}

	/**
	 * Enqueue scripts and styles for the keyword meta box.
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$settings        = self::get_settings();
		$ai_features     = AdminMenu::get_ai_features();
		$global_settings = SettingsPage::getSettings();
		$has_ai          = ! empty( $global_settings['ai_enabled'] )
			&& ! empty( $global_settings['api_keys'][ $global_settings['provider'] ] ?? '' );

		wp_enqueue_script(
			'brezngeo-keyword-analysis',
			BREZNGEO_URL . 'assets/keyword-analysis.js',
			array( 'jquery' ),
			BREZNGEO_VERSION,
			true
		);
		wp_localize_script(
			'brezngeo-keyword-analysis',
			'brezngeoKeyword',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'brezngeo_admin' ),
				'postId'     => get_the_ID(),
				'updateMode' => $settings['update_mode'] ?? 'manual',
				'debounceMs' => (int) ( $settings['live_debounce_ms'] ?? 800 ),
				'aiEnabled'  => $has_ai && ! empty( $ai_features['keywords'] ),
				'i18n'       => array(
					'analyzing'  => __( 'Analyzing…', 'brezngeo' ),
					'error'      => __( 'Analysis error.', 'brezngeo' ),
					'noKeyword'  => __( 'Please enter a main keyword.', 'brezngeo' ),
					'suggesting' => __( 'Getting suggestions…', 'brezngeo' ),
					'optimizing' => __( 'Getting optimization tips…', 'brezngeo' ),
					'semantic'   => __( 'Running semantic analysis…', 'brezngeo' ),
				),
			)
		);
	}

	/**
	 * AJAX handler for keyword analysis.
	 *
	 * @return void
	 */
	public function ajax_analyze(): void {
		check_ajax_referer( 'brezngeo_admin', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'brezngeo' ) );
		}

		$post_id       = absint( wp_unslash( $_POST['post_id'] ?? 0 ) );
		$post_content  = wp_kses_post( wp_unslash( $_POST['post_content'] ?? '' ) );
		$main_keyword  = sanitize_text_field( wp_unslash( $_POST['main_keyword'] ?? '' ) );
		$raw_secondary = isset( $_POST['secondary_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['secondary_keywords'] ) ) : '';
		$secondary     = ! empty( $raw_secondary ) ? json_decode( $raw_secondary, true ) : array();
		if ( ! is_array( $secondary ) ) {
			$secondary = array();
		}
		$secondary = array_map( 'sanitize_text_field', $secondary );

		if ( '' === $main_keyword ) {
			wp_send_json_error( __( 'No keyword provided.', 'brezngeo' ) );
		}

		$settings   = self::get_settings();
		$locale     = function_exists( 'get_locale' ) ? get_locale() : 'en_US';
		$data       = KeywordAnalysis::extract_content_data( $post_content, $post_id );
		$thresholds = array(
			'target_density'            => (float) ( $settings['target_density'] ?? 1.5 ),
			'density_margin'            => 0.5,
			'min_occurrences'           => (int) ( $settings['min_occurrences_primary'] ?? 3 ),
			'min_occurrences_secondary' => (int) ( $settings['min_occurrences_secondary'] ?? 1 ),
		);

		$main_results      = KeywordAnalysis::analyze( $main_keyword, $data, $thresholds, true, $locale );
		$secondary_results = array();
		foreach ( $secondary as $kw ) {
			$kw = trim( $kw );
			if ( '' === $kw ) {
				continue;
			}
			$secondary_results[ $kw ] = KeywordAnalysis::analyze( $kw, $data, $thresholds, false, $locale );
		}

		$response = array(
			'main'      => array(
				'keyword' => $main_keyword,
				'checks'  => $main_results,
			),
			'secondary' => $secondary_results,
		);

		// Cache results as post meta.
		if ( $post_id > 0 ) {
			update_post_meta( $post_id, self::META_RESULTS, wp_json_encode( $response, JSON_UNESCAPED_UNICODE ) );
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX handler for AI keyword suggestions.
	 *
	 * @return void
	 */
	public function ajax_ai_suggest(): void {
		check_ajax_referer( 'brezngeo_admin', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'brezngeo' ) );
		}

		$ai_features = AdminMenu::get_ai_features();
		if ( empty( $ai_features['keywords'] ) ) {
			wp_send_json_error( __( 'AI keyword features are not activated.', 'brezngeo' ) );
		}

		$post_content = wp_kses_post( wp_unslash( $_POST['post_content'] ?? '' ) );
		$post_id      = absint( wp_unslash( $_POST['post_id'] ?? 0 ) );
		$title        = $post_id > 0 ? get_the_title( $post_id ) : '';
		$content      = wp_strip_all_tags( $post_content );
		$content      = TokenEstimator::truncate( $content, 2000 );
		$language     = self::detect_language();

		$prompt = "Analyze the following article and suggest the best SEO keywords.\n" .
			"Title: {$title}\n" .
			"Content: {$content}\n\n" .
			"Language: {$language}\n" .
			"Respond ONLY with valid JSON in this format: {\"main\": \"primary keyword\", \"secondary\": [\"keyword1\", \"keyword2\", \"keyword3\"]}\n" .
			'Suggest 1 main keyword and up to 3 secondary keywords. Keep them concise and relevant.';

		$result = self::call_ai( $prompt );
		if ( null === $result ) {
			wp_send_json_error( __( 'AI generation failed. Check provider settings.', 'brezngeo' ) );
		}

		$parsed = json_decode( $result, true );
		if ( ! is_array( $parsed ) || empty( $parsed['main'] ) ) {
			// Try to extract JSON from response.
			if ( preg_match( '/\{[^}]+\}/', $result, $json_match ) ) {
				$parsed = json_decode( $json_match[0], true );
			}
		}

		if ( is_array( $parsed ) && ! empty( $parsed['main'] ) ) {
			wp_send_json_success(
				array(
					'main'      => sanitize_text_field( $parsed['main'] ),
					'secondary' => array_map( 'sanitize_text_field', (array) ( $parsed['secondary'] ?? array() ) ),
				)
			);
		} else {
			wp_send_json_error( __( 'Could not parse AI response.', 'brezngeo' ) );
		}
	}

	/**
	 * AJAX handler for AI optimization tips.
	 *
	 * @return void
	 */
	public function ajax_ai_optimize(): void {
		check_ajax_referer( 'brezngeo_admin', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'brezngeo' ) );
		}

		$ai_features = AdminMenu::get_ai_features();
		if ( empty( $ai_features['keywords'] ) ) {
			wp_send_json_error( __( 'AI keyword features are not activated.', 'brezngeo' ) );
		}

		$post_content = wp_kses_post( wp_unslash( $_POST['post_content'] ?? '' ) );
		$main_keyword = sanitize_text_field( wp_unslash( $_POST['main_keyword'] ?? '' ) );
		$content      = wp_strip_all_tags( $post_content );
		$content      = TokenEstimator::truncate( $content, 2000 );
		$language     = self::detect_language();

		$prompt = "You are an SEO expert. The target keyword is: \"{$main_keyword}\".\n" .
			"Article content: {$content}\n\n" .
			"Language: {$language}\n" .
			"Provide 3-5 concrete, actionable optimization tips to improve this article's ranking for the target keyword.\n" .
			'Respond ONLY with a JSON array of strings, e.g.: ["Tip 1", "Tip 2", "Tip 3"]';

		$result = self::call_ai( $prompt );
		if ( null === $result ) {
			wp_send_json_error( __( 'AI generation failed. Check provider settings.', 'brezngeo' ) );
		}

		$parsed = json_decode( $result, true );
		if ( ! is_array( $parsed ) ) {
			if ( preg_match( '/\[.*\]/s', $result, $json_match ) ) {
				$parsed = json_decode( $json_match[0], true );
			}
		}

		if ( is_array( $parsed ) && ! empty( $parsed ) ) {
			wp_send_json_success( array_map( 'sanitize_text_field', array_values( $parsed ) ) );
		} else {
			wp_send_json_error( __( 'Could not parse AI response.', 'brezngeo' ) );
		}
	}

	/**
	 * AJAX handler for AI semantic analysis.
	 *
	 * @return void
	 */
	public function ajax_ai_semantic(): void {
		check_ajax_referer( 'brezngeo_admin', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'brezngeo' ) );
		}

		$ai_features = AdminMenu::get_ai_features();
		if ( empty( $ai_features['keywords'] ) ) {
			wp_send_json_error( __( 'AI keyword features are not activated.', 'brezngeo' ) );
		}

		$post_content = wp_kses_post( wp_unslash( $_POST['post_content'] ?? '' ) );
		$main_keyword = sanitize_text_field( wp_unslash( $_POST['main_keyword'] ?? '' ) );
		$content      = wp_strip_all_tags( $post_content );
		$content      = TokenEstimator::truncate( $content, 2000 );
		$language     = self::detect_language();

		$prompt = "You are an SEO expert. Perform a semantic analysis of the following article for the keyword: \"{$main_keyword}\".\n" .
			"Article content: {$content}\n\n" .
			"Language: {$language}\n" .
			"Analyze:\n" .
			"1. Topic coverage — which related subtopics are present?\n" .
			"2. Related terms — which relevant terms are present, which are missing?\n" .
			"3. Content gaps — what topics should be added?\n\n" .
			'Respond in plain text, structured with clear headings. Keep it concise (max 200 words). Respond in the article language.';

		$result = self::call_ai( $prompt );
		if ( null === $result ) {
			wp_send_json_error( __( 'AI generation failed. Check provider settings.', 'brezngeo' ) );
		}

		wp_send_json_success( sanitize_textarea_field( $result ) );
	}

	/**
	 * Call AI provider with a prompt.
	 *
	 * @param string $prompt The prompt to send.
	 * @return string|null AI response text or null on failure.
	 */
	private static function call_ai( string $prompt ): ?string {
		$settings = SettingsPage::getSettings();
		$registry = ProviderRegistry::instance();
		$provider = $registry->get( $settings['provider'] );
		$api_key  = $settings['api_keys'][ $settings['provider'] ] ?? '';

		if ( ! $provider || empty( $api_key ) || empty( $settings['ai_enabled'] ) ) {
			return null;
		}

		$model = $settings['models'][ $settings['provider'] ] ?? array_key_first( $provider->getModels() );

		try {
			$result     = $provider->generateText( $prompt, $api_key, $model, 500 );
			$tokens_in  = TokenEstimator::estimate( $prompt );
			$tokens_out = TokenEstimator::estimate( $result );
			\BreznGEO\Features\MetaGenerator::record_usage( $tokens_in, $tokens_out );
			return $result;
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * Detect language for AI prompts.
	 */
	private static function detect_language(): string {
		$locale     = function_exists( 'get_locale' ) ? get_locale() : 'en_US';
		$locale_map = array(
			'de' => 'German',
			'en' => 'English',
		);
		$lang       = mb_strtolower( mb_substr( $locale, 0, 2 ) );
		return $locale_map[ $lang ] ?? 'English';
	}

	/**
	 * Get keyword analysis settings with defaults.
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$defaults = array(
			'update_mode'               => 'manual',
			'target_density'            => 1.5,
			'min_occurrences_primary'   => 3,
			'min_occurrences_secondary' => 1,
			'post_types'                => array( 'post', 'page' ),
			'live_debounce_ms'          => 800,
		);
		$saved    = get_option( 'brezngeo_keyword_settings', array() );
		$saved    = is_array( $saved ) ? $saved : array();
		return array_merge( $defaults, $saved );
	}
}
