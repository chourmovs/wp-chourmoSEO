<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap brezngeo-settings">
	<h1><?php esc_html_e( 'Keyword Analysis', 'brezngeo' ); ?></h1>

	<?php settings_errors( 'brezngeo_keyword' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'brezngeo_keyword' ); ?>

		<h2><?php esc_html_e( 'Analysis Settings', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Update Mode', 'brezngeo' ); ?></th>
				<td>
					<select name="brezngeo_keyword_settings[update_mode]">
						<option value="manual" <?php selected( $settings['update_mode'], 'manual' ); ?>>
							<?php esc_html_e( 'Manual — click "Analyze" button', 'brezngeo' ); ?>
						</option>
						<option value="live" <?php selected( $settings['update_mode'], 'live' ); ?>>
							<?php esc_html_e( 'Live — auto-analyze while typing', 'brezngeo' ); ?>
						</option>
						<option value="save" <?php selected( $settings['update_mode'], 'save' ); ?>>
							<?php esc_html_e( 'On Save — analyze when post is saved', 'brezngeo' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Target Keyword Density (%)', 'brezngeo' ); ?></th>
				<td>
					<input type="number" name="brezngeo_keyword_settings[target_density]"
						value="<?php echo esc_attr( $settings['target_density'] ); ?>"
						min="0.1" max="5.0" step="0.1" style="width:80px;">
					<p class="description"><?php esc_html_e( 'Recommended: 1.0–2.0%. Pass range is ±0.5% around the target.', 'brezngeo' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Min. Occurrences (Primary)', 'brezngeo' ); ?></th>
				<td>
					<input type="number" name="brezngeo_keyword_settings[min_occurrences_primary]"
						value="<?php echo esc_attr( $settings['min_occurrences_primary'] ); ?>"
						min="1" max="50" style="width:80px;">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Min. Occurrences (Secondary)', 'brezngeo' ); ?></th>
				<td>
					<input type="number" name="brezngeo_keyword_settings[min_occurrences_secondary]"
						value="<?php echo esc_attr( $settings['min_occurrences_secondary'] ); ?>"
						min="1" max="50" style="width:80px;">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Live Mode Debounce (ms)', 'brezngeo' ); ?></th>
				<td>
					<input type="number" name="brezngeo_keyword_settings[live_debounce_ms]"
						value="<?php echo esc_attr( $settings['live_debounce_ms'] ); ?>"
						min="300" max="3000" step="100" style="width:100px;">
					<p class="description"><?php esc_html_e( 'Delay in milliseconds before live analysis triggers after typing stops.', 'brezngeo' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Post Types', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Show keyword meta box on', 'brezngeo' ); ?></th>
				<td>
					<?php foreach ( $post_types as $brezngeo_pt ) : ?>
					<label style="display:block;margin-bottom:4px;">
						<input type="checkbox" name="brezngeo_keyword_settings[post_types][]"
							value="<?php echo esc_attr( $brezngeo_pt->name ); ?>"
							<?php checked( in_array( $brezngeo_pt->name, $settings['post_types'], true ) ); ?>>
						<?php echo esc_html( $brezngeo_pt->labels->name ); ?>
					</label>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>

	<p class="brezngeo-footer">
		BreznGEO <?php echo esc_html( BREZNGEO_VERSION ); ?> &mdash;
		<?php esc_html_e( 'developed by', 'brezngeo' ); ?>
		<a href="https://noschmarrn.dev" target="_blank" rel="noopener">noschmarrn.dev</a>
		<?php esc_html_e( 'for', 'brezngeo' ); ?>
		<a href="https://donau2space.de" target="_blank" rel="noopener">Donau2Space.de</a>
	</p>
</div>
