<?php
namespace BreznGEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SchemaMetaBox {
	public const META_TYPE    = '_brezngeo_schema_type';
	public const META_DATA    = '_brezngeo_schema_data';
	private const VALID_TYPES = array( 'howto', 'review', 'recipe', 'event', '' );

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'addMetaBox' ) );
		add_action( 'save_post', array( $this, 'savePost' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function addMetaBox(): void {
		$settings  = SettingsPage::getSettings();
		$enabled   = $settings['schema_enabled'] ?? array();
		$needs_box = array_intersect( array( 'howto', 'review', 'recipe', 'event' ), $enabled );
		if ( empty( $needs_box ) ) {
			return;
		}
		add_meta_box(
			'brezngeo-schema-meta-box',
			__( 'BreznGEO Schema', 'brezngeo' ),
			array( $this, 'renderMetaBox' ),
			array( 'post', 'page' ),
			'side',
			'default'
		);
	}

	public function renderMetaBox( \WP_Post $post ): void {
		$type     = get_post_meta( $post->ID, self::META_TYPE, true ) ?: '';
		$raw_data = get_post_meta( $post->ID, self::META_DATA, true ) ?: '{}';
		$data     = json_decode( $raw_data, true ) ?: array();
		$settings = SettingsPage::getSettings();
		$enabled  = $settings['schema_enabled'] ?? array();
		wp_nonce_field( 'brezngeo_schema_meta_box', '_brezngeo_schema_nonce' );
		include BREZNGEO_DIR . 'includes/Admin/views/schema-meta-box.php';
	}

	public function enqueue( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		wp_enqueue_script(
			'brezngeo-schema-meta-box',
			BREZNGEO_URL . 'assets/schema-meta-box.js',
			array(),
			BREZNGEO_VERSION,
			true
		);
	}

	public function savePost( int $post_id, \WP_Post $post ): void {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['_brezngeo_schema_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_brezngeo_schema_nonce'] ) ), 'brezngeo_schema_meta_box' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$input = isset( $_POST['brezngeo_schema'] ) && is_array( $_POST['brezngeo_schema'] )
			? map_deep( wp_unslash( $_POST['brezngeo_schema'] ), 'sanitize_textarea_field' )
			: array();
		$clean = self::sanitizeData( $input );
		update_post_meta( $post_id, self::META_TYPE, $clean['schema_type'] );
		update_post_meta(
			$post_id,
			self::META_DATA,
			wp_json_encode( $clean['data'], JSON_UNESCAPED_UNICODE )
		);
	}

	/**
	 * Pure sanitizer — public static for testability.
	 *
	 * @param array $input Raw $_POST['brezngeo_schema'] data.
	 * @return array{schema_type: string, data: array}
	 */
	public static function sanitizeData( array $input ): array {
		$type = sanitize_key( $input['schema_type'] ?? '' );
		if ( ! in_array( $type, self::VALID_TYPES, true ) ) {
			$type = '';
		}
		$data = array();

		if ( $type === 'howto' ) {
			$raw_steps     = sanitize_textarea_field( $input['howto_steps'] ?? '' );
			$steps         = array_values( array_filter( array_map( 'trim', explode( "\n", $raw_steps ) ) ) );
			$data['howto'] = array(
				'name'  => sanitize_text_field( $input['howto_name'] ?? '' ),
				'steps' => $steps,
			);
		}

		if ( $type === 'review' ) {
			$data['review'] = array(
				'item'   => sanitize_text_field( $input['review_item'] ?? '' ),
				'rating' => max( 1, min( 5, (int) ( $input['review_rating'] ?? 3 ) ) ),
			);
		}

		if ( $type === 'recipe' ) {
			$raw_ing        = sanitize_textarea_field( $input['recipe_ingredients'] ?? '' );
			$raw_inst       = sanitize_textarea_field( $input['recipe_instructions'] ?? '' );
			$data['recipe'] = array(
				'name'         => sanitize_text_field( $input['recipe_name'] ?? '' ),
				'prep'         => max( 0, (int) ( $input['recipe_prep'] ?? 0 ) ),
				'cook'         => max( 0, (int) ( $input['recipe_cook'] ?? 0 ) ),
				'servings'     => sanitize_text_field( $input['recipe_servings'] ?? '' ),
				'ingredients'  => array_values( array_filter( array_map( 'trim', explode( "\n", $raw_ing ) ) ) ),
				'instructions' => array_values( array_filter( array_map( 'trim', explode( "\n", $raw_inst ) ) ) ),
			);
		}

		if ( $type === 'event' ) {
			$data['event'] = array(
				'name'     => sanitize_text_field( $input['event_name'] ?? '' ),
				'start'    => sanitize_text_field( $input['event_start'] ?? '' ),
				'end'      => sanitize_text_field( $input['event_end'] ?? '' ),
				'location' => sanitize_text_field( $input['event_location'] ?? '' ),
				'online'   => ! empty( $input['event_online'] ),
			);
		}

		return array(
			'schema_type' => $type,
			'data'        => $data,
		);
	}
}
