<?php
namespace BreznGEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BreznGEO\Features\GeoBlock;

class GeoEditorBox {
	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_brezngeo_geo_generate', array( $this, 'ajax_generate' ) );
		add_action( 'wp_ajax_brezngeo_geo_clear', array( $this, 'ajax_clear' ) );
	}

	public function add_boxes(): void {
		$settings = GeoBlock::getSettings();
		foreach ( $settings['post_types'] as $pt ) {
			add_meta_box(
				'brezngeo_geo_box',
				__( 'GEO Quick Overview (BreznGEO)', 'brezngeo' ),
				array( $this, 'render' ),
				$pt,
				'normal',
				'default'
			);
		}
	}

	public function render( \WP_Post $post ): void {
		$settings     = GeoBlock::getSettings();
		$meta         = GeoBlock::getMeta( $post->ID );
		$enabled      = get_post_meta( $post->ID, GeoBlock::META_ENABLED, true );
		$lock         = (bool) get_post_meta( $post->ID, GeoBlock::META_LOCK, true );
		$generated_at = get_post_meta( $post->ID, GeoBlock::META_GENERATED, true );
		$prompt_addon = get_post_meta( $post->ID, GeoBlock::META_ADDON, true ) ?: '';
		$global       = SettingsPage::getSettings();
		$has_api_key  = ! empty( $global['api_keys'][ $global['provider'] ] ?? '' );

		wp_nonce_field( 'brezngeo_geo_save_' . $post->ID, 'brezngeo_geo_nonce' );
		?>
		<div id="brezngeo-geo-box" data-post-id="<?php echo esc_attr( $post->ID ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'brezngeo_admin' ) ); ?>">

			<p style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
				<label>
					<input type="checkbox" name="brezngeo_geo_enabled" value="1"
						<?php checked( $enabled, '1' ); ?>>
					<?php esc_html_e( 'Enable GEO block for this post', 'brezngeo' ); ?>
				</label>
				<label>
					<input type="checkbox" name="brezngeo_geo_lock" value="1" id="brezngeo-geo-lock"
						<?php checked( $lock, true ); ?>>
					<?php esc_html_e( 'Lock auto-regeneration', 'brezngeo' ); ?>
				</label>
				<?php if ( $generated_at ) : ?>
				<span style="font-size:11px;color:#666;">
					<?php
					// translators: %s = human-readable date
					printf( esc_html__( 'Generated: %s', 'brezngeo' ), esc_html( date_i18n( get_option( 'date_format' ) . ' H:i', (int) $generated_at ) ) );
					?>
				</span>
				<?php endif; ?>
			</p>

			<?php if ( $has_api_key ) : ?>
			<p>
				<button type="button" class="button" id="brezngeo-geo-generate">
					<?php
					empty( $meta['summary'] )
						? esc_html_e( 'Generate now', 'brezngeo' )
						: esc_html_e( 'Regenerate', 'brezngeo' );
					?>
				</button>
				<?php if ( ! empty( $meta['summary'] ) ) : ?>
				<button type="button" class="button" id="brezngeo-geo-clear" style="margin-left:6px;">
					<?php esc_html_e( 'Clear', 'brezngeo' ); ?>
				</button>
				<?php endif; ?>
				<span id="brezngeo-geo-status" style="margin-left:10px;font-size:12px;"></span>
			</p>
			<?php endif; ?>

			<p style="margin-bottom:4px;">
				<label for="brezngeo-geo-summary"><strong><?php esc_html_e( 'Summary', 'brezngeo' ); ?></strong></label>
			</p>
			<textarea id="brezngeo-geo-summary" name="brezngeo_geo_summary" rows="3"
				style="width:100%;box-sizing:border-box;"><?php echo esc_textarea( $meta['summary'] ); ?></textarea>

			<p style="margin-bottom:4px;margin-top:10px;">
				<label for="brezngeo-geo-bullets"><strong><?php esc_html_e( 'Key Points', 'brezngeo' ); ?></strong></label>
				<span style="font-size:11px;color:#666;margin-left:8px;"><?php esc_html_e( '(one per line)', 'brezngeo' ); ?></span>
			</p>
			<textarea id="brezngeo-geo-bullets" name="brezngeo_geo_bullets" rows="5"
				style="width:100%;box-sizing:border-box;"><?php echo esc_textarea( implode( "\n", $meta['bullets'] ) ); ?></textarea>

			<p style="margin-bottom:4px;margin-top:10px;">
				<label for="brezngeo-geo-faq"><strong><?php esc_html_e( 'FAQ', 'brezngeo' ); ?></strong></label>
				<span style="font-size:11px;color:#666;margin-left:8px;"><?php esc_html_e( '(Format: Question? | Answer — one per line)', 'brezngeo' ); ?></span>
			</p>
			<textarea id="brezngeo-geo-faq" name="brezngeo_geo_faq" rows="4"
				style="width:100%;box-sizing:border-box;">
				<?php
				$faq_lines = array_map(
					function ( $item ) {
						return ( $item['q'] ?? '' ) . ' | ' . ( $item['a'] ?? '' );
					},
					$meta['faq']
				);
								echo esc_textarea( implode( "\n", $faq_lines ) );
				?>
			</textarea>

			<?php if ( $settings['allow_prompt_addon'] ) : ?>
			<p style="margin-bottom:4px;margin-top:10px;">
				<label for="brezngeo-geo-addon"><strong><?php esc_html_e( 'Prompt add-on (optional)', 'brezngeo' ); ?></strong></label>
			</p>
			<textarea id="brezngeo-geo-addon" name="brezngeo_geo_prompt_addon" rows="2"
				style="width:100%;box-sizing:border-box;"><?php echo esc_textarea( $prompt_addon ); ?></textarea>
			<?php endif; ?>
		</div>
		<?php
	}

	public function save( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['brezngeo_geo_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brezngeo_geo_nonce'] ) ), 'brezngeo_geo_save_' . $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Per-post enabled flag ('' = follow global, '1' = on, '0' = off)
		$enabled = isset( $_POST['brezngeo_geo_enabled'] ) ? '1' : '0';
		update_post_meta( $post_id, GeoBlock::META_ENABLED, $enabled );

		$lock = isset( $_POST['brezngeo_geo_lock'] ) ? '1' : '';
		update_post_meta( $post_id, GeoBlock::META_LOCK, $lock );

		// Manual field edits
		$summary = sanitize_text_field( wp_unslash( $_POST['brezngeo_geo_summary'] ?? '' ) );
		update_post_meta( $post_id, GeoBlock::META_SUMMARY, $summary );

		$raw_bullets = sanitize_textarea_field( wp_unslash( $_POST['brezngeo_geo_bullets'] ?? '' ) );
		$bullets     = array_values( array_filter( array_map( 'trim', explode( "\n", $raw_bullets ) ) ) );
		update_post_meta( $post_id, GeoBlock::META_BULLETS, wp_json_encode( $bullets, JSON_UNESCAPED_UNICODE ) );

		$raw_faq = sanitize_textarea_field( wp_unslash( $_POST['brezngeo_geo_faq'] ?? '' ) );
		$faq     = array();
		foreach ( array_filter( array_map( 'trim', explode( "\n", $raw_faq ) ) ) as $line ) {
			$parts = explode( '|', $line, 2 );
			if ( count( $parts ) === 2 ) {
				$faq[] = array(
					'q' => trim( $parts[0] ),
					'a' => trim( $parts[1] ),
				);
			}
		}
		update_post_meta( $post_id, GeoBlock::META_FAQ, wp_json_encode( $faq, JSON_UNESCAPED_UNICODE ) );

		if ( isset( $_POST['brezngeo_geo_prompt_addon'] ) ) {
			update_post_meta( $post_id, GeoBlock::META_ADDON, sanitize_textarea_field( wp_unslash( $_POST['brezngeo_geo_prompt_addon'] ) ) );
		}
	}

	public function enqueue( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		wp_enqueue_script(
			'brezngeo-geo-editor',
			BREZNGEO_URL . 'assets/geo-editor.js',
			array( 'jquery' ),
			BREZNGEO_VERSION,
			true
		);
	}

	public function ajax_generate(): void {
		check_ajax_referer( 'brezngeo_admin', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'brezngeo' ) );
		}

		$post_id = absint( wp_unslash( $_POST['post_id'] ?? 0 ) );
		if ( ! $post_id || ! get_post( $post_id ) ) {
			wp_send_json_error( __( 'Post not found.', 'brezngeo' ) );
		}

		$geo = new GeoBlock();
		if ( $geo->generate( $post_id, true ) ) {
			$meta = GeoBlock::getMeta( $post_id );
			wp_send_json_success(
				array(
					'summary' => $meta['summary'],
					'bullets' => $meta['bullets'],
					'faq'     => $meta['faq'],
				)
			);
		} else {
			wp_send_json_error( __( 'Generation failed. Check API key and provider settings.', 'brezngeo' ) );
		}
	}

	public function ajax_clear(): void {
		check_ajax_referer( 'brezngeo_admin', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'brezngeo' ) );
		}

		$post_id = absint( wp_unslash( $_POST['post_id'] ?? 0 ) );
		if ( ! $post_id ) {
			wp_send_json_error( 'Invalid post ID' );
		}

		delete_post_meta( $post_id, GeoBlock::META_SUMMARY );
		delete_post_meta( $post_id, GeoBlock::META_BULLETS );
		delete_post_meta( $post_id, GeoBlock::META_FAQ );
		delete_post_meta( $post_id, GeoBlock::META_GENERATED );
		wp_send_json_success();
	}
}
