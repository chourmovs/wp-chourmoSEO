<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} ?>
<div class="wrap brezngeo-settings">
	<h1><?php esc_html_e( 'AI Provider', 'brezngeo' ); ?></h1>

	<?php settings_errors( 'brezngeo_provider' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'brezngeo_provider' ); ?>

		<div class="brezngeo-ai-toggle-wrap">
			<label style="font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
				<input type="checkbox" name="brezngeo_settings[ai_enabled]" value="1" id="brezngeo-ai-enabled"
						<?php checked( $settings['ai_enabled'] ?? false, true ); ?>>
				<?php esc_html_e( 'Enable AI generation', 'brezngeo' ); ?>
			</label>
			<p class="brezngeo-ai-cost-notice">
				&#9888; <?php esc_html_e( 'This feature will incur costs with your AI provider. Make sure you understand the pricing before entering an API key.', 'brezngeo' ); ?>
			</p>
		</div>
		<div id="brezngeo-ai-fields">

		<h2><?php esc_html_e( 'AI Provider', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Active Provider', 'brezngeo' ); ?></th>
				<td>
					<select name="brezngeo_settings[provider]" id="brezngeo-provider">
						<?php foreach ( $providers as $id => $provider ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
						<option value="<?php echo esc_attr( $id ); ?>"
							<?php selected( $settings['provider'], $id ); ?>>
							<?php echo esc_html( $provider->getName() ); ?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<?php foreach ( $providers as $id => $provider ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
			<tr class="brezngeo-provider-row" data-provider="<?php echo esc_attr( $id ); ?>">
				<th scope="row"><?php echo esc_html( $provider->getName() ); ?> <?php esc_html_e( 'API Key', 'brezngeo' ); ?></th>
				<td>
					<?php if ( ! empty( $masked_keys[ $id ] ) ) : ?>
					<span class="brezngeo-key-saved">
						<?php esc_html_e( 'Saved:', 'brezngeo' ); ?> <code><?php echo esc_html( $masked_keys[ $id ] ); ?></code>
					</span><br>
					<?php endif; ?>
					<input type="password"
							name="brezngeo_settings[api_keys][<?php echo esc_attr( $id ); ?>]"
							value=""
							placeholder="<?php echo ! empty( $masked_keys[ $id ] ) ? esc_attr__( 'Enter new key to overwrite', 'brezngeo' ) : esc_attr__( 'Enter API key', 'brezngeo' ); ?>"
							class="regular-text"
							autocomplete="new-password">
					<button type="button" class="button brezngeo-test-btn" data-provider="<?php echo esc_attr( $id ); ?>">
						<?php esc_html_e( 'Test connection', 'brezngeo' ); ?>
					</button>
					<span class="brezngeo-test-result" id="test-result-<?php echo esc_attr( $id ); ?>"></span>
					<br><br>
					<label><?php esc_html_e( 'Model:', 'brezngeo' ); ?></label>
					<select name="brezngeo_settings[models][<?php echo esc_attr( $id ); ?>]">
						<?php
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$saved_model = $settings['models'][ $id ] ?? array_key_first( $provider->getModels() );
						foreach ( $provider->getModels() as $model_id => $model_label ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							?>
						<option value="<?php echo esc_attr( $model_id ); ?>"
							<?php selected( $saved_model, $model_id ); ?>>
							<?php echo esc_html( $model_label ); ?>
						</option>
						<?php endforeach; ?>
					</select>
				<?php
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$pricing_url = $pricing_urls[ $id ] ?? '';
				if ( $pricing_url ) :
					?>
				<p style="margin-top:8px;">
					<a href="<?php echo esc_url( $pricing_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'View current pricing →', 'brezngeo' ); ?>
					</a>
				</p>
<?php endif; ?>
				<p style="margin-top:12px;"><strong><?php esc_html_e( 'Cost per 1 million tokens (for the Bulk cost overview):', 'brezngeo' ); ?></strong></p>
				<?php
				foreach ( $provider->getModels() as $model_id => $model_label ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					$saved_costs = $settings['costs'][ $id ][ $model_id ] ?? array();
					?>
				<div style="margin-bottom:6px;display:flex;align-items:center;gap:12px;">
					<label style="min-width:180px;font-size:12px;"><?php echo esc_html( $model_label ); ?>:</label>
					<span>Input $<input type="number" step="0.0001" min="0"
						name="brezngeo_settings[costs][<?php echo esc_attr( $id ); ?>][<?php echo esc_attr( $model_id ); ?>][input]"
						value="<?php echo esc_attr( $saved_costs['input'] ?? '' ); ?>"
						placeholder="z.B. 0.15" style="width:75px;"> / 1M</span>
					<span>Output $<input type="number" step="0.0001" min="0"
						name="brezngeo_settings[costs][<?php echo esc_attr( $id ); ?>][<?php echo esc_attr( $model_id ); ?>][output]"
						value="<?php echo esc_attr( $saved_costs['output'] ?? '' ); ?>"
						placeholder="z.B. 0.60" style="width:75px;"> / 1M</span>
				</div>
<?php endforeach; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>

		</div><!-- /#brezngeo-ai-fields -->

		<?php submit_button( __( 'Save Settings', 'brezngeo' ) ); ?>
	</form>

	<p class="brezngeo-footer">
		BreznGEO <?php echo esc_html( BREZNGEO_VERSION ); ?> &mdash;
		<?php esc_html_e( 'developed by', 'brezngeo' ); ?> 🍺
		<a href="https://noschmarrn.dev" target="_blank" rel="noopener">noschmarrn.dev</a>
		<?php esc_html_e( 'for', 'brezngeo' ); ?>
		<a href="https://donau2space.de" target="_blank" rel="noopener">Donau2Space.de</a>
	</p>
</div>
