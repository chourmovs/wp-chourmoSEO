<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} ?>
<div class="wrap brezngeo-settings">
	<h1><?php esc_html_e( 'Schema.org', 'brezngeo' ); ?></h1>

	<?php settings_errors( 'brezngeo_schema' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'brezngeo_schema' ); ?>

		<h2><?php esc_html_e( 'Schema.org Enhancer (GEO)', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enabled Schema Types', 'brezngeo' ); ?></th>
				<td>
					<?php foreach ( $schema_labels as $type => $label ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<label style="display:block;margin-bottom:8px;">
						<input type="checkbox"
								name="brezngeo_schema_settings[schema_enabled][]"
								value="<?php echo esc_attr( $type ); ?>"
								<?php checked( in_array( $type, $settings['schema_enabled'], true ), true ); ?>>
						<?php echo esc_html( $label ); ?>
					</label>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Organization sameAs URLs', 'brezngeo' ); ?></th>
				<td>
					<p class="description"><?php esc_html_e( 'One URL per line (Twitter, LinkedIn, GitHub, Facebook…)', 'brezngeo' ); ?></p>
					<textarea name="brezngeo_schema_settings[schema_same_as][organization]"
								rows="5"
								class="large-text"><?php echo esc_textarea( implode( "\n", $settings['schema_same_as']['organization'] ?? array() ) ); ?></textarea>
				</td>
			</tr>
		</table>

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
