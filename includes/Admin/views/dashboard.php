<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} ?>
<div class="wrap brezngeo-settings">
	<h1><?php esc_html_e( 'BreznGEO — Dashboard', 'brezngeo' ); ?></h1>

<?php if ( $brezngeo_show_welcome ) : ?>
<div class="brezngeo-welcome-notice" id="brezngeo-welcome-notice">
	<button type="button" class="brezngeo-dismiss" id="brezngeo-dismiss-welcome"
			aria-label="<?php esc_attr_e( 'Dismiss', 'brezngeo' ); ?>">&#215;</button>
	<p style="margin:0 0 6px;font-size:15px;">
		&#127866; <strong><?php esc_html_e( 'Servus! Welcome to BreznGEO.', 'brezngeo' ); ?></strong>
	</p>
	<p style="margin:0;color:#444;">
		<?php esc_html_e( 'No Lederhosen required — your SEO is already in good hands.', 'brezngeo' ); ?>
		<a href="<?php echo esc_url( 'https://brezngeo.com/howto.html' ); ?>" target="_blank" rel="noopener">
			<?php esc_html_e( 'Read the setup guide and be running in five minutes →', 'brezngeo' ); ?>
		</a>
	</p>
</div>
<?php endif; ?>

	<div class="brezngeo-dashboard-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:20px;">

		<div class="postbox">
			<div class="postbox-header"><h2><?php esc_html_e( 'Meta Coverage', 'brezngeo' ); ?></h2></div>
			<div class="inside">
				<?php if ( empty( $meta_stats ) ) : ?>
					<p><?php esc_html_e( 'No post types configured.', 'brezngeo' ); ?></p>
				<?php else : ?>
					<?php foreach ( $meta_stats as $pt => $stat ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
				<div class="brezngeo-coverage-row">
					<div class="brezngeo-coverage-label">
						<strong><?php echo esc_html( $pt ); ?></strong>
						<span class="brezngeo-coverage-stat">
							<?php echo esc_html( $stat['with_meta'] ); ?>/<?php echo esc_html( $stat['total'] ); ?>
							&mdash; <?php echo esc_html( $stat['pct'] ); ?>%
						</span>
					</div>
					<div class="brezngeo-progress-bar">
						<div class="brezngeo-progress-fill <?php echo esc_attr( $stat['pct'] >= 80 ? 'brezngeo-ok' : ( $stat['pct'] >= 40 ? 'brezngeo-warn' : 'brezngeo-bad' ) ); ?>"
							style="width:<?php echo esc_attr( $stat['pct'] ); ?>%"></div>
					</div>
				</div>
				<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="postbox">
			<div class="postbox-header"><h2><?php esc_html_e( 'Quick Links', 'brezngeo' ); ?></h2></div>
			<div class="inside">
				<ul class="brezngeo-quick-links-list">
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=brezngeo-provider' ) ); ?>">
						&#x1F511; <?php esc_html_e( 'AI Provider Settings', 'brezngeo' ); ?>
					</a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=brezngeo-meta' ) ); ?>">
						&#x270F;&#xFE0F; <?php esc_html_e( 'Meta Generator Settings', 'brezngeo' ); ?>
					</a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=brezngeo-txt&amp;tab=llms' ) ); ?>">
						&#x1F4C4; llms.txt
					</a></li>
					<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=brezngeo-bulk' ) ); ?>">
						&#x26A1; <?php esc_html_e( 'Bulk Generator', 'brezngeo' ); ?>
					</a></li>
					<li><a href="<?php echo esc_url( 'https://brezngeo.com/howto.html' ); ?>" target="_blank" rel="noopener">
						&#x1F4D6; <?php esc_html_e( 'Documentation &amp; How To', 'brezngeo' ); ?>
					</a></li>
				</ul>
			</div>
		</div>

		<div class="postbox">
			<div class="postbox-header"><h2><?php esc_html_e( 'Status', 'brezngeo' ); ?></h2></div>
			<div class="inside">
				<table style="width:100%;border-collapse:collapse;">
					<tr>
						<td class="brezngeo-stat-label"><?php esc_html_e( 'Version', 'brezngeo' ); ?></td>
						<td class="brezngeo-stat-value"><?php echo esc_html( BREZNGEO_VERSION ); ?></td>
					</tr>
					<tr>
						<td class="brezngeo-stat-label"><?php esc_html_e( 'Active Provider', 'brezngeo' ); ?></td>
						<td class="brezngeo-stat-value"><?php echo esc_html( $provider ); ?></td>
					</tr>
					<tr>
						<td class="brezngeo-stat-label"><?php esc_html_e( 'AI metas generated', 'brezngeo' ); ?></td>
						<td class="brezngeo-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $usage_stats['count'] ?? 0 ) ) ); ?></td>
					</tr>
					<tr>
						<td class="brezngeo-stat-label"><?php esc_html_e( 'Tokens used (est.)', 'brezngeo' ); ?></td>
						<td class="brezngeo-stat-value">
							~<?php echo esc_html( number_format_i18n( (int) ( $usage_stats['tokens_in'] ?? 0 ) + (int) ( $usage_stats['tokens_out'] ?? 0 ) ) ); ?>
						</td>
					</tr>
					<?php if ( null !== $cost_usd ) : ?>
					<tr>
						<td class="brezngeo-stat-label"><?php esc_html_e( 'Est. cost (USD)', 'brezngeo' ); ?></td>
						<td class="brezngeo-stat-value">~$<?php echo esc_html( number_format( $cost_usd, 4 ) ); ?></td>
					</tr>
					<?php endif; ?>
				</table>
				<p style="margin:12px 0 0;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=brezngeo-provider' ) ); ?>" class="button button-secondary" style="font-size:12px;">
						<?php esc_html_e( 'Configure AI Provider', 'brezngeo' ); ?>
					</a>
				</p>
			</div>
		</div>

		<?php if ( $has_ai ) : ?>
		<div class="postbox">
			<div class="postbox-header"><h2><?php esc_html_e( 'AI Features', 'brezngeo' ); ?></h2></div>
			<div class="inside">
				<?php if ( isset( $_GET['brezngeo-saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success inline" style="margin:0 0 12px;"><p><?php esc_html_e( 'Settings saved.', 'brezngeo' ); ?></p></div>
				<?php endif; ?>
				<p style="color:#666;margin-top:0;">
					<?php esc_html_e( 'Choose which features may use your connected AI provider. All options are opt-in and disabled by default.', 'brezngeo' ); ?>
				</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="brezngeo_save_ai_features">
					<?php wp_nonce_field( 'brezngeo_save_ai_features' ); ?>
					<table style="width:100%;border-collapse:collapse;">
						<tr>
							<td style="padding:6px 0;">
								<label>
									<input type="checkbox" name="brezngeo_ai_features[meta]" value="1"
										<?php checked( $ai_features['meta'] ); ?>>
									<strong><?php esc_html_e( 'Meta Descriptions', 'brezngeo' ); ?></strong>
								</label>
								<p style="margin:2px 0 0 22px;color:#777;font-size:12px;">
									<?php esc_html_e( 'Generate meta descriptions with AI when editing or using the Bulk Generator.', 'brezngeo' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<td style="padding:6px 0;">
								<label>
									<input type="checkbox" name="brezngeo_ai_features[links]" value="1"
										<?php checked( $ai_features['links'] ); ?>>
									<strong><?php esc_html_e( 'Internal Link Suggestions', 'brezngeo' ); ?></strong>
								</label>
								<p style="margin:2px 0 0 22px;color:#777;font-size:12px;">
									<?php esc_html_e( 'Let AI pick the most natural anchor phrases and rank candidates semantically.', 'brezngeo' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<td style="padding:6px 0;">
								<label>
									<input type="checkbox" name="brezngeo_ai_features[geo]" value="1"
										<?php checked( $ai_features['geo'] ); ?>>
									<strong><?php esc_html_e( 'GEO Block', 'brezngeo' ); ?></strong>
								</label>
								<p style="margin:2px 0 0 22px;color:#777;font-size:12px;">
									<?php esc_html_e( 'Use AI to generate GEO-optimised content blocks for LLM visibility.', 'brezngeo' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<td style="padding:6px 0;">
								<label>
									<input type="checkbox" name="brezngeo_ai_features[keywords]" value="1"
										<?php checked( $ai_features['keywords'] ); ?>>
									<strong><?php esc_html_e( 'Keyword Analysis', 'brezngeo' ); ?></strong>
								</label>
								<p style="margin:2px 0 0 22px;color:#777;font-size:12px;">
									<?php esc_html_e( 'AI-powered keyword suggestions, optimization tips, and semantic analysis.', 'brezngeo' ); ?>
								</p>
							</td>
						</tr>
					</table>
					<p style="margin-top:12px;">
						<?php submit_button( __( 'Save', 'brezngeo' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $brezngeo_compat ) ) : ?>
		<div class="postbox">
			<div class="postbox-header"><h2><?php esc_html_e( 'Plugin Compatibility', 'brezngeo' ); ?></h2></div>
			<div class="inside">
				<p style="color:#666;margin-top:0;"><?php esc_html_e( 'The following SEO plugins were detected. BreznGEO adapts automatically.', 'brezngeo' ); ?></p>
				<?php foreach ( $brezngeo_compat as $plugin ) : ?>
				<p style="margin-bottom:4px;"><strong><?php echo esc_html( $plugin['name'] ); ?></strong></p>
				<ul style="margin:0 0 12px 20px;">
					<?php foreach ( $plugin['notes'] as $note ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<li><?php echo esc_html( $note ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<div class="postbox">
			<div class="postbox-header"><h2><?php esc_html_e( 'Internal Link Analysis', 'brezngeo' ); ?></h2></div>
			<div class="inside" id="brezngeo-link-analysis-content">
				<em><?php esc_html_e( 'Loading…', 'brezngeo' ); ?></em>
			</div>
		</div>

		<div class="postbox">
			<div class="postbox-header"><h2><?php esc_html_e( 'AI Crawlers — Last 30 Days', 'brezngeo' ); ?></h2></div>
			<div class="inside">
								<?php if ( empty( $crawlers ) ) : ?>
					<p><?php esc_html_e( 'No AI crawlers recorded yet.', 'brezngeo' ); ?></p>
				<?php else : ?>
				<table class="widefat striped">
					<thead><tr>
						<th><?php esc_html_e( 'Bot', 'brezngeo' ); ?></th>
						<th><?php esc_html_e( 'Visits', 'brezngeo' ); ?></th>
						<th><?php esc_html_e( 'Last Seen', 'brezngeo' ); ?></th>
					</tr></thead>
					<tbody>
					<?php foreach ( $crawlers as $row ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<tr>
						<td><span class="brezngeo-bot-dot"></span><code><?php echo esc_html( $row['bot_name'] ); ?></code></td>
						<td><?php echo esc_html( $row['visits'] ); ?></td>
						<td><?php echo esc_html( $row['last_seen'] ); ?></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
			</div>
		</div>

	</div>

	<p class="brezngeo-footer">
		BreznGEO <?php echo esc_html( BREZNGEO_VERSION ); ?> &mdash;
		<?php esc_html_e( 'developed by', 'brezngeo' ); ?> 🍺
		<a href="https://noschmarrn.dev" target="_blank" rel="noopener">noschmarrn.dev</a>
		<?php esc_html_e( 'for', 'brezngeo' ); ?>
		<a href="https://donau2space.de" target="_blank" rel="noopener">Donau2Space.de</a>
	</p>
</div>
