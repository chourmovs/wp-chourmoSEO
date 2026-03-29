<?php
namespace BreznGEO\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AdminMenu {
	public const OPTION_KEY_AI_FEATURES = 'brezngeo_ai_features';

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_brezngeo_dismiss_welcome', array( $this, 'ajax_dismiss_welcome' ) );
		add_action( 'admin_post_brezngeo_save_ai_features', array( $this, 'save_ai_features' ) );
	}

	public static function get_ai_features(): array {
		$defaults = array(
			'meta'     => false,
			'geo'      => false,
			'links'    => false,
			'keywords' => false,
		);
		$saved    = get_option( self::OPTION_KEY_AI_FEATURES, array() );
		$saved    = is_array( $saved ) ? $saved : array();
		return array_merge( $defaults, $saved );
	}

	public function save_ai_features(): void {
		check_admin_referer( 'brezngeo_save_ai_features' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'brezngeo' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified via check_admin_referer() above
		$input = isset( $_POST['brezngeo_ai_features'] ) && is_array( $_POST['brezngeo_ai_features'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['brezngeo_ai_features'] ) )
			: array();

		update_option(
			self::OPTION_KEY_AI_FEATURES,
			array(
				'meta'     => ! empty( $input['meta'] ),
				'geo'      => ! empty( $input['geo'] ),
				'links'    => ! empty( $input['links'] ),
				'keywords' => ! empty( $input['keywords'] ),
			)
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'          => 'brezngeo',
					'brezngeo-saved' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public function enqueue_assets( string $hook ): void {
		if ( $hook !== 'toplevel_page_brezngeo' ) {
			return;
		}
		wp_enqueue_style( 'brezngeo-admin', BREZNGEO_URL . 'assets/admin.css', array(), BREZNGEO_VERSION );
		wp_enqueue_script( 'brezngeo-admin', BREZNGEO_URL . 'assets/admin.js', array( 'jquery' ), BREZNGEO_VERSION, true );
		wp_localize_script(
			'brezngeo-admin',
			'brezngeoAdmin',
			array(
				'nonce'        => wp_create_nonce( 'brezngeo_admin' ),
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'testing'      => __( 'Testing…', 'brezngeo' ),
				'networkError' => __( 'Network error', 'brezngeo' ),
				'resetConfirm' => __( 'Really reset the prompt?', 'brezngeo' ),
			)
		);
		wp_localize_script(
			'brezngeo-admin',
			'brezngeoL10n',
			array(
				'analysisError'    => __( 'Analysis error.', 'brezngeo' ),
				'noLinksHeading'   => __( 'Posts without internal links', 'brezngeo' ),
				'allLinked'        => __( 'All posts have internal links.', 'brezngeo' ),
				'manyExternalPre'  => __( 'Posts with many external links (≥', 'brezngeo' ),
				'noExternalIssues' => __( 'No posts with excessive external links.', 'brezngeo' ),
				'pillarHeading'    => __( 'Pillar Pages (Top 5)', 'brezngeo' ),
				'noData'           => __( 'No data.', 'brezngeo' ),
				'connectionError'  => __( 'Connection error.', 'brezngeo' ),
			)
		);
	}

	public function add_menus(): void {
		add_menu_page(
			__( 'BreznGEO', 'brezngeo' ),
			__( 'BreznGEO', 'brezngeo' ),
			'manage_options',
			'brezngeo',
			array( $this, 'render_dashboard' ),
			'dashicons-chart-area',
			80
		);

		// First submenu replaces the parent menu link
		add_submenu_page(
			'brezngeo',
			__( 'Dashboard', 'brezngeo' ),
			__( 'Dashboard', 'brezngeo' ),
			'manage_options',
			'brezngeo',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'brezngeo',
			__( 'AI Provider', 'brezngeo' ),
			__( 'AI Provider', 'brezngeo' ),
			'manage_options',
			'brezngeo-provider',
			array( new ProviderPage(), 'render' )
		);

		add_submenu_page(
			'brezngeo',
			__( 'Meta Generator', 'brezngeo' ),
			__( 'Meta Generator', 'brezngeo' ),
			'manage_options',
			'brezngeo-meta',
			array( new MetaPage(), 'render' )
		);

		add_submenu_page(
			'brezngeo',
			__( 'Schema.org', 'brezngeo' ),
			__( 'Schema.org', 'brezngeo' ),
			'manage_options',
			'brezngeo-schema',
			array( new SchemaPage(), 'render' )
		);

		add_submenu_page(
			'brezngeo',
			__( 'TXT Files', 'brezngeo' ),
			__( 'TXT Files', 'brezngeo' ),
			'manage_options',
			'brezngeo-txt',
			array( new TxtPage(), 'render' )
		);

		add_submenu_page(
			'brezngeo',
			__( 'Bulk Generator', 'brezngeo' ),
			__( 'Bulk Generator', 'brezngeo' ),
			'manage_options',
			'brezngeo-bulk',
			array( new BulkPage(), 'render' )
		);

		add_submenu_page(
			'brezngeo',
			__( 'Link Suggestions', 'brezngeo' ),
			__( 'Link Suggestions', 'brezngeo' ),
			'manage_options',
			'brezngeo-link-suggest',
			array( new LinkSuggestPage(), 'render' )
		);

		add_submenu_page(
			'brezngeo',
			__( 'Keyword Analysis', 'brezngeo' ),
			__( 'Keyword Analysis', 'brezngeo' ),
			'manage_options',
			'brezngeo-keyword',
			array( new KeywordPage(), 'render' )
		);

		add_submenu_page(
			'brezngeo',
			__( 'GEO Quick Overview', 'brezngeo' ),
			__( 'GEO Block', 'brezngeo' ),
			'manage_options',
			'brezngeo-geo',
			array( new GeoPage(), 'render' )
		);
	}

	public function render_dashboard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings     = SettingsPage::getSettings();
		$provider_key = $settings['provider'] ?? 'openai';
		$api_key      = $settings['api_keys'][ $provider_key ] ?? '';
		$ai_enabled   = $settings['ai_enabled'] ?? false;
		$has_ai       = $ai_enabled && ! empty( $api_key );

		if ( ! $ai_enabled ) {
			$provider = __( 'AI disabled', 'brezngeo' );
		} elseif ( empty( $api_key ) ) {
			$provider = __( '— Not configured —', 'brezngeo' );
		} else {
			$prov_obj = \BreznGEO\ProviderRegistry::instance()->get( $provider_key );
			$provider = $prov_obj ? $prov_obj->getName() : $provider_key;
		}

		$post_types = $settings['meta_post_types'] ?? array( 'post', 'page' );
		$meta_stats = $this->get_meta_stats( $post_types );
		$brezngeo_compat = $this->get_compat_info();

		$brezngeo_show_welcome = $this->should_show_welcome();

		$usage_stats  = get_option(
			'brezngeo_usage_stats',
			array(
				'tokens_in'  => 0,
				'tokens_out' => 0,
				'count'      => 0,
			)
		);
		$model        = $settings['models'][ $provider_key ] ?? '';
		$costs_config = $settings['costs'][ $provider_key ][ $model ] ?? array();
		$cost_usd     = null;
		if ( ! empty( $costs_config['input'] ) || ! empty( $costs_config['output'] ) ) {
			$cost_usd = round(
				( (int) ( $usage_stats['tokens_in'] ?? 0 ) / 1_000_000 ) * (float) ( $costs_config['input'] ?? 0 )
				+ ( (int) ( $usage_stats['tokens_out'] ?? 0 ) / 1_000_000 ) * (float) ( $costs_config['output'] ?? 0 ),
				4
			);
		}

		$crawlers = get_transient( 'brezngeo_crawler_summary' );
		if ( false === $crawlers ) {
			$crawlers = \BreznGEO\Features\CrawlerLog::get_recent_summary( 30 );
			set_transient( 'brezngeo_crawler_summary', $crawlers, 5 * MINUTE_IN_SECONDS );
		}

		$ai_features = self::get_ai_features();

		include BREZNGEO_DIR . 'includes/Admin/views/dashboard.php';
	}

	private function should_show_welcome(): bool {
		if ( get_user_meta( get_current_user_id(), 'brezngeo_welcome_dismissed', true ) ) {
			return false;
		}
		$activated = (int) get_option( 'brezngeo_first_activated', 0 );
		if ( ! $activated ) {
			// First admin visit on a legacy install — set timestamp now and show
			update_option( 'brezngeo_first_activated', time() );
			return true;
		}
		return ( time() - $activated ) < DAY_IN_SECONDS;
	}

	public function ajax_dismiss_welcome(): void {
		check_ajax_referer( 'brezngeo_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		update_user_meta( get_current_user_id(), 'brezngeo_welcome_dismissed', 1 );
		wp_send_json_success();
	}

	private function get_meta_stats( array $post_types ): array {
		$cache_key = 'brezngeo_meta_stats';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$stats = array();
		foreach ( $post_types as $pt ) {
			$total        = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
					$pt
				)
			);
			$with_meta    = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
                 WHERE p.post_type = %s AND p.post_status = 'publish'
                 AND pm.meta_key = %s AND pm.meta_value != ''",
					$pt,
					'_brezngeo_meta_description'
				)
			);
			$stats[ $pt ] = array(
				'total'     => $total,
				'with_meta' => $with_meta,
				'pct'       => $total > 0 ? round( ( $with_meta / $total ) * 100 ) : 0,
			);
		}

		set_transient( $cache_key, $stats, 5 * MINUTE_IN_SECONDS );
		return $stats;
	}

	private function get_compat_info(): array {
		$compat = array();
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			$compat[] = array(
				'name'  => 'Rank Math',
				'notes' => array(
					__( 'llms.txt: BreznGEO serves the file with priority — Rank Math is bypassed.', 'brezngeo' ),
					__( 'Schema.org: BreznGEO suppresses its own JSON-LD to avoid duplicates.', 'brezngeo' ),
					__( 'Meta descriptions: BreznGEO writes to the Rank Math meta field.', 'brezngeo' ),
				),
			);
		}
		if ( defined( 'WPSEO_VERSION' ) ) {
			$compat[] = array(
				'name'  => 'Yoast SEO',
				'notes' => array(
					__( 'Schema.org: BreznGEO suppresses its own JSON-LD to avoid duplicates.', 'brezngeo' ),
					__( 'Meta descriptions: BreznGEO writes to the Yoast meta field.', 'brezngeo' ),
				),
			);
		}
		if ( defined( 'AIOSEO_VERSION' ) ) {
			$compat[] = array(
				'name'  => 'All in One SEO',
				'notes' => array(
					__( 'Meta descriptions: BreznGEO writes to the AIOSEO meta field.', 'brezngeo' ),
				),
			);
		}
		if ( class_exists( 'SeoPress_Titles_Admin' ) ) {
			$compat[] = array(
				'name'  => 'SEOPress',
				'notes' => array(
					__( 'Meta descriptions: BreznGEO writes to the SEOPress meta field.', 'brezngeo' ),
				),
			);
		}
		return $compat;
	}
}
