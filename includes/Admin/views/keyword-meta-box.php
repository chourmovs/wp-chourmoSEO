<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="brezngeo-keyword-box"
	data-post-id="<?php echo esc_attr( $post->ID ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'brezngeo_admin' ) ); ?>">

	<p style="margin-bottom:4px;">
		<label for="brezngeo-keyword-main"><strong><?php esc_html_e( 'Main Keyword', 'brezngeo' ); ?></strong></label>
	</p>
	<input type="text" id="brezngeo-keyword-main" name="brezngeo_keyword_main"
		value="<?php echo esc_attr( $main_keyword ); ?>"
		style="width:100%;box-sizing:border-box;"
		placeholder="<?php esc_attr_e( 'e.g. Passau travel guide', 'brezngeo' ); ?>">

	<p style="margin-bottom:4px;margin-top:12px;">
		<strong><?php esc_html_e( 'Secondary Keywords', 'brezngeo' ); ?></strong>
	</p>
	<div id="brezngeo-keyword-secondary-list">
		<?php if ( ! empty( $secondary ) ) : ?>
			<?php foreach ( $secondary as $brezngeo_kw ) : ?>
			<div class="brezngeo-keyword-secondary-row" style="display:flex;gap:6px;margin-bottom:4px;">
				<input type="text" name="brezngeo_keyword_secondary[]"
					value="<?php echo esc_attr( $brezngeo_kw ); ?>"
					style="flex:1;box-sizing:border-box;">
				<button type="button" class="button brezngeo-keyword-remove-secondary">&times;</button>
			</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<p>
		<button type="button" class="button" id="brezngeo-keyword-add-secondary">
			+ <?php esc_html_e( 'Add Keyword', 'brezngeo' ); ?>
		</button>
	</p>

	<p style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
		<button type="button" class="button button-primary" id="brezngeo-keyword-analyze">
			<?php esc_html_e( 'Analyze', 'brezngeo' ); ?>
		</button>
		<?php if ( $ai_keywords ) : ?>
		<button type="button" class="button" id="brezngeo-keyword-ai-suggest">
			<?php esc_html_e( 'Suggest Keywords', 'brezngeo' ); ?> &#10024;
		</button>
		<?php endif; ?>
		<span id="brezngeo-keyword-status" style="align-self:center;font-size:12px;"></span>
	</p>

	<div id="brezngeo-keyword-results" style="margin-top:16px;">
		<?php if ( ! empty( $cached_results ) ) : ?>
		<p style="font-size:11px;color:#999;"><?php esc_html_e( 'Showing cached results. Click "Analyze" to refresh.', 'brezngeo' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $ai_keywords ) : ?>
	<div id="brezngeo-keyword-ai-actions" style="margin-top:12px;display:none;">
		<button type="button" class="button" id="brezngeo-keyword-ai-optimize">
			<?php esc_html_e( 'Optimization Tips', 'brezngeo' ); ?> &#10024;
		</button>
		<button type="button" class="button" id="brezngeo-keyword-ai-semantic">
			<?php esc_html_e( 'Semantic Analysis', 'brezngeo' ); ?> &#10024;
		</button>
	</div>
	<div id="brezngeo-keyword-ai-results" style="margin-top:12px;"></div>
	<?php endif; ?>
</div>
