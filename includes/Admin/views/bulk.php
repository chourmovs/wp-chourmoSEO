<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} ?>
<div class="wrap brezngeo-settings">
	<h1><?php esc_html_e( 'Bulk Generator', 'brezngeo' ); ?></h1>

	<div id="brezngeo-lock-warning" style="display:none;background:#fcf8e3;border:1px solid #faebcc;padding:10px 15px;margin-bottom:15px;border-radius:3px;color:#8a6d3b;"></div>

	<p><?php esc_html_e( 'Generates meta descriptions for posts without an existing meta description.', 'brezngeo' ); ?></p>

	<div id="brezngeo-bulk-stats" style="background:#fff;padding:15px;border:1px solid #ddd;margin-bottom:20px;">
		<em><?php esc_html_e( 'Loading statistics…', 'brezngeo' ); ?></em>
	</div>

	<?php if ( ! $has_ai ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
	<div style="background:#fff3cd;border:1px solid #ffc107;padding:10px 15px;margin-bottom:20px;border-radius:3px;color:#856404;">
		<?php
		printf(
			wp_kses(
				/* translators: %s: URL to provider settings page */
				__( 'No AI provider connected — descriptions will be generated from content without AI (fallback mode). <a href="%s">Configure a provider →</a>', 'brezngeo' ),
				array( 'a' => array( 'href' => array() ) )
			),
			esc_url( admin_url( 'admin.php?page=brezngeo-provider' ) )
		);
		?>
	</div>
	<?php endif; ?>

	<table class="form-table">
		<?php if ( $has_ai ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Active Provider', 'brezngeo' ); ?></th>
			<td>
				<select id="brezngeo-bulk-provider">
					<?php foreach ( $providers as $id => $provider ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<option value="<?php echo esc_attr( $id ); ?>"
						<?php selected( $settings['provider'], $id ); ?>>
						<?php echo esc_html( $provider->getName() ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Model:', 'brezngeo' ); ?></th>
			<td>
				<select id="brezngeo-bulk-model">
					<?php
                    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					$active_provider = $registry->get( $settings['provider'] );
					if ( $active_provider ) :
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						$saved_model = $settings['models'][ $settings['provider'] ] ?? array_key_first( $active_provider->getModels() );
						foreach ( $active_provider->getModels() as $mid => $mlabel ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							?>
					<option value="<?php echo esc_attr( $mid ); ?>"
							<?php selected( $saved_model, $mid ); ?>>
							<?php echo esc_html( $mlabel ); ?>
					</option>
							<?php
					endforeach;
endif;
					?>
				</select>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<th scope="row"><?php esc_html_e( 'Max. posts this run', 'brezngeo' ); ?></th>
			<td>
				<input type="number" id="brezngeo-bulk-limit" value="20" min="1" max="500">
				<p class="description" id="brezngeo-cost-estimate"></p>
			</td>
		</tr>
	</table>

	<p>
		<button id="brezngeo-bulk-start" class="button button-primary"><?php esc_html_e( 'Start Bulk Run', 'brezngeo' ); ?></button>
		<button id="brezngeo-bulk-stop" class="button" style="display:none;"><?php esc_html_e( 'Cancel', 'brezngeo' ); ?></button>
	</p>

	<div id="brezngeo-progress-wrap" style="display:none;margin:15px 0;">
		<div style="background:#ddd;border-radius:3px;height:20px;width:100%;">
			<div id="brezngeo-progress-bar"
				style="background:#0073aa;height:20px;border-radius:3px;width:0;transition:width .3s;"></div>
		</div>
		<p id="brezngeo-progress-text"><?php esc_html_e( '0 / 0 processed', 'brezngeo' ); ?></p>
	</div>

	<div id="brezngeo-bulk-log"
		style="background:#1e1e1e;color:#d4d4d4;padding:15px;font-family:monospace;font-size:12px;max-height:400px;overflow-y:auto;display:none;"></div>

	<div id="brezngeo-failed-summary" style="display:none;background:#fdf2f2;border:1px solid #f5c6cb;padding:10px 15px;margin-top:15px;border-radius:3px;font-size:13px;"></div>

	<p class="brezngeo-footer">
		BreznGEO <?php echo esc_html( BREZNGEO_VERSION ); ?> &mdash;
		<?php esc_html_e( 'developed by', 'brezngeo' ); ?> 🍺
		<a href="https://noschmarrn.dev" target="_blank" rel="noopener">noschmarrn.dev</a>
		<?php esc_html_e( 'for', 'brezngeo' ); ?>
		<a href="https://donau2space.de" target="_blank" rel="noopener">Donau2Space.de</a>
	</p>
</div>
