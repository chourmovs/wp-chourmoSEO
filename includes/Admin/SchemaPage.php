<?php
namespace BreznGEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SchemaPage {
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_settings(): void {
		register_setting(
			'brezngeo_schema',
			SettingsPage::OPTION_KEY_SCHEMA,
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
			)
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( $hook !== 'brezngeo_page_brezngeo-schema' ) {
			return;
		}
		wp_enqueue_style( 'brezngeo-admin', BREZNGEO_URL . 'assets/admin.css', array(), BREZNGEO_VERSION );
	}

	public function sanitize( mixed $input ): array {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		$schema_types            = array(
			'organization',
			'author',
			'speakable',
			'article_about',
			'breadcrumb',
			'ai_meta_tags',
			'faq_schema',
			'blog_posting',
			'image_object',
			'video_object',
			'howto',
			'review',
			'recipe',
			'event',
		);
		$clean['schema_enabled'] = array_values(
			array_intersect(
				array_map( 'sanitize_key', (array) ( $input['schema_enabled'] ?? array() ) ),
				$schema_types
			)
		);

		$org_raw = $input['schema_same_as']['organization'] ?? '';
		if ( is_array( $org_raw ) ) {
			$org_raw = implode( "\n", $org_raw );
		}
		$clean['schema_same_as'] = array(
			'organization' => array_values(
				array_filter(
					array_map(
						'esc_url_raw',
						array_map( 'trim', explode( "\n", $org_raw ) )
					)
				)
			),
		);

		return $clean;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings      = SettingsPage::getSettings();
		$schema_labels = array(
			'organization'  => __( 'Organization (sameAs Social Profiles)', 'brezngeo' ),
			'author'        => __( 'Author (sameAs Profile Links)', 'brezngeo' ),
			'speakable'     => __( 'Speakable (for AI assistants)', 'brezngeo' ),
			'article_about' => __( 'Article about/mentions', 'brezngeo' ),
			'breadcrumb'    => __( 'BreadcrumbList', 'brezngeo' ),
			'ai_meta_tags'  => __( 'AI-optimized Meta Tags (max-snippet etc.)', 'brezngeo' ),
			'faq_schema'    => __( 'FAQPage (from GEO Quick Overview — automatic)', 'brezngeo' ),
			'blog_posting'  => __( 'BlogPosting / Article (with embedded Author + Image)', 'brezngeo' ),
			'image_object'  => __( 'ImageObject (Featured Image)', 'brezngeo' ),
			'video_object'  => __( 'VideoObject (auto-detect YouTube/Vimeo)', 'brezngeo' ),
			'howto'         => __( 'HowTo (Metabox in Post Editor)', 'brezngeo' ),
			'review'        => __( 'Review with Rating (Metabox in Post Editor)', 'brezngeo' ),
			'recipe'        => __( 'Recipe (Metabox in Post Editor)', 'brezngeo' ),
			'event'         => __( 'Event (Metabox in Post Editor)', 'brezngeo' ),
		);
		include BREZNGEO_DIR . 'includes/Admin/views/schema.php';
	}
}
