<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;}
?>
<div class="wrap brezngeo-settings">
	<h1><?php esc_html_e( 'GEO Quick Overview', 'brezngeo' ); ?></h1>

	<?php settings_errors( 'brezngeo_geo' ); ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'brezngeo_geo' ); ?>

		<h2><?php esc_html_e( 'Activation', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable GEO Block', 'brezngeo' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="brezngeo_geo_settings[enabled]" value="1"
							<?php checked( $settings['enabled'], true ); ?>>
						<?php esc_html_e( 'Output the Quick Overview block on the frontend', 'brezngeo' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Mode', 'brezngeo' ); ?></th>
				<td>
					<select name="brezngeo_geo_settings[mode]">
						<option value="auto_on_publish" <?php selected( $settings['mode'], 'auto_on_publish' ); ?>>
							<?php esc_html_e( 'Auto on publish / update (recommended)', 'brezngeo' ); ?>
						</option>
						<option value="hybrid" <?php selected( $settings['mode'], 'hybrid' ); ?>>
							<?php esc_html_e( 'Hybrid: auto only when fields are empty', 'brezngeo' ); ?>
						</option>
						<option value="manual_only" <?php selected( $settings['mode'], 'manual_only' ); ?>>
							<?php esc_html_e( 'Manual only (editor button)', 'brezngeo' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Post Types', 'brezngeo' ); ?></th>
				<td>
					<?php foreach ( $post_types as $pt_slug => $pt_obj ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<label style="margin-right:15px;">
						<input type="checkbox" name="brezngeo_geo_settings[post_types][]"
							value="<?php echo esc_attr( $pt_slug ); ?>"
							<?php checked( in_array( $pt_slug, $settings['post_types'], true ), true ); ?>>
						<?php echo esc_html( $pt_obj->labels->singular_name ); ?>
					</label>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Regenerate on update', 'brezngeo' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="brezngeo_geo_settings[regen_on_update]" value="1"
							<?php checked( $settings['regen_on_update'], true ); ?>>
						<?php esc_html_e( 'Regenerate on every save of a published post', 'brezngeo' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Word threshold for FAQ', 'brezngeo' ); ?></th>
				<td>
					<input type="number" name="brezngeo_geo_settings[word_threshold]"
						value="<?php echo esc_attr( $settings['word_threshold'] ); ?>"
						min="50" max="2000" style="width:80px;">
					<p class="description">
						<?php esc_html_e( 'Below this word count, no FAQ is generated. Default: 350', 'brezngeo' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Output', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Position', 'brezngeo' ); ?></th>
				<td>
					<select name="brezngeo_geo_settings[position]">
						<option value="after_first_p" <?php selected( $settings['position'], 'after_first_p' ); ?>>
							<?php esc_html_e( 'After first paragraph (default)', 'brezngeo' ); ?>
						</option>
						<option value="top" <?php selected( $settings['position'], 'top' ); ?>>
							<?php esc_html_e( 'Top of post', 'brezngeo' ); ?>
						</option>
						<option value="bottom" <?php selected( $settings['position'], 'bottom' ); ?>>
							<?php esc_html_e( 'Bottom of post', 'brezngeo' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Output style', 'brezngeo' ); ?></th>
				<td>
					<select name="brezngeo_geo_settings[output_style]">
						<option value="details_collapsible" <?php selected( $settings['output_style'], 'details_collapsible' ); ?>>
							<?php esc_html_e( 'Collapsible <details> (default)', 'brezngeo' ); ?>
						</option>
						<option value="open_always" <?php selected( $settings['output_style'], 'open_always' ); ?>>
							<?php esc_html_e( 'Always open', 'brezngeo' ); ?>
						</option>
						<option value="store_only_no_frontend" <?php selected( $settings['output_style'], 'store_only_no_frontend' ); ?>>
							<?php esc_html_e( 'Store only, no frontend output', 'brezngeo' ); ?>
						</option>
					</select>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Labels', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Block title', 'brezngeo' ); ?></th>
				<td>
					<input type="text" name="brezngeo_geo_settings[title]"
						value="<?php echo esc_attr( $settings['title'] ); ?>"
						class="regular-text" placeholder="Quick Overview">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Summary label', 'brezngeo' ); ?></th>
				<td>
					<input type="text" name="brezngeo_geo_settings[label_summary]"
						value="<?php echo esc_attr( $settings['label_summary'] ); ?>"
						class="regular-text" placeholder="Summary">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Key Points label', 'brezngeo' ); ?></th>
				<td>
					<input type="text" name="brezngeo_geo_settings[label_bullets]"
						value="<?php echo esc_attr( $settings['label_bullets'] ); ?>"
						class="regular-text" placeholder="Key Points">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'FAQ label', 'brezngeo' ); ?></th>
				<td>
					<input type="text" name="brezngeo_geo_settings[label_faq]"
						value="<?php echo esc_attr( $settings['label_faq'] ); ?>"
						class="regular-text" placeholder="FAQ">
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Styling', 'brezngeo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Accent color', 'brezngeo' ); ?></th>
				<td>
					<input type="color" id="brezngeo-accent-picker" name="brezngeo_geo_settings[accent_color]"
						value="<?php echo esc_attr( $settings['accent_color'] ?: '#0073aa' ); ?>"
						style="width:60px;height:34px;padding:2px;border:1px solid #ccd0d4;border-radius:3px;cursor:pointer;"
						oninput="document.getElementById('brezngeo-accent-text').value=this.value">
					<input type="text" id="brezngeo-accent-text"
						value="<?php echo esc_attr( $settings['accent_color'] ?: '#0073aa' ); ?>"
						placeholder="#0073aa" maxlength="7" style="width:90px;vertical-align:middle;"
						oninput="document.getElementById('brezngeo-accent-picker').value=this.value">
					<p class="description">
						<?php esc_html_e( 'Left border stripe and expand arrow colour. Leave empty for the default blue. Not used by the Minimal theme.', 'brezngeo' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Theme', 'brezngeo' ); ?></th>
				<td>
					<select name="brezngeo_geo_settings[theme]">
						<option value="light" <?php selected( $settings['theme'] ?? 'light', 'light' ); ?>>
							<?php esc_html_e( 'Light', 'brezngeo' ); ?>
						</option>
						<option value="dark" <?php selected( $settings['theme'] ?? 'light', 'dark' ); ?>>
							<?php esc_html_e( 'Dark', 'brezngeo' ); ?>
						</option>
						<option value="minimal" <?php selected( $settings['theme'] ?? 'light', 'minimal' ); ?>>
							<?php esc_html_e( 'Minimal', 'brezngeo' ); ?>
						</option>
						<option value="brezn" <?php selected( $settings['theme'] ?? 'light', 'brezn' ); ?>>
							<?php esc_html_e( 'Brezn', 'brezngeo' ); ?>
						</option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Light — clean card with a blue accent. Dark — same for dark-mode sites. Minimal — borderless, left stripe only. Brezn — Brezn blue with diamond header pattern.', 'brezngeo' ); ?>
						<br>
						<?php
						printf(
							wp_kses(
								/* translators: %s: URL to the how-to page */
								__( 'Want to customise further? <a href="%s" target="_blank" rel="noopener">Learn how to style the block via your theme &rarr;</a>', 'brezngeo' ),
								array(
									'a' => array(
										'href'   => array(),
										'target' => array(),
										'rel'    => array(),
									),
								)
							),
							esc_url( 'https://brezngeo.com/howto.html#geo-block' )
						);
						?>
					</p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'AI Prompt', 'brezngeo' ); ?></h2>
	<?php if ( ! $has_ai ) : ?>
	<div class="notice notice-warning inline" style="margin:0 0 12px;">
		<p>
			<strong><?php esc_html_e( 'No AI provider active.', 'brezngeo' ); ?></strong>
			<?php esc_html_e( 'The GEO block will not be generated automatically until an API key is configured and AI generation is enabled.', 'brezngeo' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=brezngeo-provider' ) ); ?>"><?php esc_html_e( 'Configure AI Provider →', 'brezngeo' ); ?></a>
		</p>
	</div>
	<?php endif; ?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Default prompt', 'brezngeo' ); ?></th>
				<td>
					<textarea name="brezngeo_geo_settings[prompt_default]" rows="12" class="large-text code">
					<?php
						echo esc_textarea( $settings['prompt_default'] );
					?>
					</textarea>
					<p class="description">
						<?php esc_html_e( 'Variables: {title}, {content}, {language}', 'brezngeo' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Per-post prompt add-on', 'brezngeo' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="brezngeo_geo_settings[allow_prompt_addon]" value="1"
							<?php checked( $settings['allow_prompt_addon'], true ); ?>>
						<?php esc_html_e( 'Authors can enter a prompt add-on per post in the editor', 'brezngeo' ); ?>
					</label>
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
