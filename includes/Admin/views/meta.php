<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} ?>
<div class="wrap brezngeo-settings">
	<h1><?php esc_html_e( 'Meta Generator', 'brezngeo' ); ?></h1>

	<?php if ( ! $has_ai ) : ?>
	<div class="notice notice-warning inline" style="margin:12px 0;">
		<p>
			<strong><?php esc_html_e( 'No AI provider active.', 'brezngeo' ); ?></strong>
			<?php esc_html_e( 'Meta descriptions will use the fallback method (first paragraph of the post) until an API key is configured and AI generation is enabled.', 'brezngeo' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=brezngeo-provider' ) ); ?>">
				<?php esc_html_e( 'Configure AI Provider →', 'brezngeo' ); ?>
			</a>
		</p>
	</div>
	<?php endif; ?>

	<?php settings_errors( 'brezngeo_meta' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'brezngeo_meta' ); ?>

		<h2><?php esc_html_e( 'Meta Generator', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Auto Mode', 'brezngeo' ); ?></th>
				<td>
					<label>
						<input type="checkbox"
								name="brezngeo_meta_settings[meta_auto_enabled]"
								value="1"
								<?php checked( $settings['meta_auto_enabled'], true ); ?>>
						<?php esc_html_e( 'Automatically generate meta description on publish', 'brezngeo' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Post Types', 'brezngeo' ); ?></th>
				<td>
					<?php foreach ( $post_types as $pt_slug => $pt_obj ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<label style="margin-right:15px;">
						<input type="checkbox"
								name="brezngeo_meta_settings[meta_post_types][]"
								value="<?php echo esc_attr( $pt_slug ); ?>"
								<?php checked( in_array( $pt_slug, $settings['meta_post_types'], true ), true ); ?>>
						<?php echo esc_html( $pt_obj->labels->singular_name ); ?>
					</label>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Token Mode', 'brezngeo' ); ?></th>
				<td>
					<?php if ( ! $has_ai ) : ?>
					<p class="description" style="margin-bottom:6px;color:#996800;">
						<?php esc_html_e( 'Fallback mode active — configure an AI provider to enable AI generation.', 'brezngeo' ); ?>
					</p>
					<?php endif; ?>
					<label>
						<input type="radio" name="brezngeo_meta_settings[token_mode]" value="full"
								<?php checked( $settings['token_mode'], 'full' ); ?>>
						<?php esc_html_e( 'Send full article', 'brezngeo' ); ?>
					</label>
					&nbsp;&nbsp;
					<label>
						<input type="radio" name="brezngeo_meta_settings[token_mode]" value="limit"
								<?php checked( $settings['token_mode'], 'limit' ); ?>>
						<?php esc_html_e( 'Limit to', 'brezngeo' ); ?>
						<input type="number"
								name="brezngeo_meta_settings[token_limit]"
								value="<?php echo esc_attr( $settings['token_limit'] ); ?>"
								min="100" max="8000" style="width:80px;">
						<?php esc_html_e( 'tokens', 'brezngeo' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Prompt', 'brezngeo' ); ?></th>
				<td>
					<textarea name="brezngeo_meta_settings[prompt]"
								rows="8"
								class="large-text code"><?php echo esc_textarea( $settings['prompt'] ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Variables:', 'brezngeo' ); ?>
						<code>{title}</code>, <code>{content}</code>, <code>{excerpt}</code>, <code>{language}</code><br>
						<button type="button" class="button" id="brezngeo-reset-prompt"><?php esc_html_e( 'Reset prompt', 'brezngeo' ); ?></button>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'SEO Widget', 'brezngeo' ); ?></th>
				<td>
					<label>
						<input type="checkbox"
								name="brezngeo_meta_settings[theme_has_h1]"
								value="1"
								<?php checked( array( 'theme_has_h1' ) ?? true, true ); ?>>
						<?php esc_html_e( 'Theme outputs post title as H1 (suppresses "no H1" warning in editor)', 'brezngeo' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Most themes render the post title as an H1 tag on the front end. Enable this to avoid false warnings in the SEO Widget when the content itself contains no H1.', 'brezngeo' ); ?>
					</p>
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
