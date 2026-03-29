<?php
/**
 * BreznGEO Schema Metabox view.
 *
 * Variables available: $type (string), $data (array), $enabled (array).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="brezngeo-schema-metabox">
	<p>
		<label for="brezngeo-schema-type"><strong><?php esc_html_e( 'Schema Type', 'brezngeo' ); ?></strong></label><br>
		<select name="brezngeo_schema[schema_type]" id="brezngeo-schema-type">
			<option value="" <?php selected( $type, '' ); ?>><?php esc_html_e( '— No Schema —', 'brezngeo' ); ?></option>
			<?php if ( in_array( 'howto', $enabled, true ) ) : ?>
			<option value="howto" <?php selected( $type, 'howto' ); ?>><?php esc_html_e( 'HowTo Guide', 'brezngeo' ); ?></option>
			<?php endif; ?>
			<?php if ( in_array( 'review', $enabled, true ) ) : ?>
			<option value="review" <?php selected( $type, 'review' ); ?>><?php esc_html_e( 'Review / Rating', 'brezngeo' ); ?></option>
			<?php endif; ?>
			<?php if ( in_array( 'recipe', $enabled, true ) ) : ?>
			<option value="recipe" <?php selected( $type, 'recipe' ); ?>><?php esc_html_e( 'Recipe', 'brezngeo' ); ?></option>
			<?php endif; ?>
			<?php if ( in_array( 'event', $enabled, true ) ) : ?>
			<option value="event" <?php selected( $type, 'event' ); ?>><?php esc_html_e( 'Event', 'brezngeo' ); ?></option>
			<?php endif; ?>
		</select>
	</p>
	<!-- HowTo fields -->
	<div class="brezngeo-schema-fields" data-brezngeo-type="howto" style="display:none;">
		<p>
			<label><strong><?php esc_html_e( 'Guide Name', 'brezngeo' ); ?></strong><br>
			<input type="text" name="brezngeo_schema[howto_name]"
				value="<?php echo esc_attr( $data['howto']['name'] ?? '' ); ?>"
				class="widefat"></label>
		</p>
		<p>
			<label><strong><?php esc_html_e( 'Steps (one line = one step)', 'brezngeo' ); ?></strong><br>
			<textarea name="brezngeo_schema[howto_steps]" rows="5" class="widefat">
			<?php
				echo esc_textarea( implode( "\n", $data['howto']['steps'] ?? array() ) );
			?>
			</textarea></label>
		</p>
	</div>
	<!-- Review fields -->
	<div class="brezngeo-schema-fields" data-brezngeo-type="review" style="display:none;">
		<p>
			<label><strong><?php esc_html_e( 'Reviewed Product / Service', 'brezngeo' ); ?></strong><br>
			<input type="text" name="brezngeo_schema[review_item]"
				value="<?php echo esc_attr( $data['review']['item'] ?? '' ); ?>"
				class="widefat"></label>
		</p>
		<p>
			<label><strong><?php esc_html_e( 'Rating (1–5)', 'brezngeo' ); ?></strong><br>
			<input type="number" name="brezngeo_schema[review_rating]" min="1" max="5" step="1"
				value="<?php echo esc_attr( $data['review']['rating'] ?? 3 ); ?>"
				style="width:60px;"></label>
		</p>
	</div>
	<!-- Recipe fields -->
	<div class="brezngeo-schema-fields" data-brezngeo-type="recipe" style="display:none;">
		<p>
			<label><strong><?php esc_html_e( 'Recipe Name', 'brezngeo' ); ?></strong><br>
			<input type="text" name="brezngeo_schema[recipe_name]"
				value="<?php echo esc_attr( $data['recipe']['name'] ?? '' ); ?>"
				class="widefat"></label>
		</p>
		<p style="display:flex;gap:8px;">
			<label style="flex:1;"><?php esc_html_e( 'Prep Time (min)', 'brezngeo' ); ?><br>
			<input type="number" name="brezngeo_schema[recipe_prep]" min="0"
				value="<?php echo esc_attr( $data['recipe']['prep'] ?? '' ); ?>"
				style="width:100%;"></label>
			<label style="flex:1;"><?php esc_html_e( 'Cook Time (min)', 'brezngeo' ); ?><br>
			<input type="number" name="brezngeo_schema[recipe_cook]" min="0"
				value="<?php echo esc_attr( $data['recipe']['cook'] ?? '' ); ?>"
				style="width:100%;"></label>
		</p>
		<p>
			<label><?php esc_html_e( 'Servings', 'brezngeo' ); ?><br>
			<input type="text" name="brezngeo_schema[recipe_servings]"
				value="<?php echo esc_attr( $data['recipe']['servings'] ?? '' ); ?>"
				class="widefat"></label>
		</p>
		<p>
			<label><strong><?php esc_html_e( 'Ingredients (one per line)', 'brezngeo' ); ?></strong><br>
			<textarea name="brezngeo_schema[recipe_ingredients]" rows="4" class="widefat">
			<?php
				echo esc_textarea( implode( "\n", $data['recipe']['ingredients'] ?? array() ) );
			?>
			</textarea></label>
		</p>
		<p>
			<label><strong><?php esc_html_e( 'Instructions (one step per line)', 'brezngeo' ); ?></strong><br>
			<textarea name="brezngeo_schema[recipe_instructions]" rows="5" class="widefat">
			<?php
				echo esc_textarea( implode( "\n", $data['recipe']['instructions'] ?? array() ) );
			?>
			</textarea></label>
		</p>
	</div>
	<!-- Event fields -->
	<div class="brezngeo-schema-fields" data-brezngeo-type="event" style="display:none;">
		<p>
			<label><strong><?php esc_html_e( 'Event Name', 'brezngeo' ); ?></strong><br>
			<input type="text" name="brezngeo_schema[event_name]"
				value="<?php echo esc_attr( $data['event']['name'] ?? '' ); ?>"
				class="widefat"></label>
		</p>
		<p>
			<label><?php esc_html_e( 'Start Date', 'brezngeo' ); ?><br>
			<input type="date" name="brezngeo_schema[event_start]"
				value="<?php echo esc_attr( $data['event']['start'] ?? '' ); ?>"></label>
		</p>
		<p>
			<label><?php esc_html_e( 'End Date (optional)', 'brezngeo' ); ?><br>
			<input type="date" name="brezngeo_schema[event_end]"
				value="<?php echo esc_attr( $data['event']['end'] ?? '' ); ?>"></label>
		</p>
		<p>
			<label><?php esc_html_e( 'Location or URL', 'brezngeo' ); ?><br>
			<input type="text" name="brezngeo_schema[event_location]"
				value="<?php echo esc_attr( $data['event']['location'] ?? '' ); ?>"
				class="widefat"></label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="brezngeo_schema[event_online]" value="1"
					<?php checked( ! empty( $data['event']['online'] ) ); ?>>
				<?php esc_html_e( 'Online Event', 'brezngeo' ); ?>
			</label>
		</p>
	</div>
</div>
