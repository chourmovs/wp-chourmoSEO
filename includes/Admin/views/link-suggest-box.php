<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; } ?>
<div id="brezngeo-link-suggest-box">
	<div style="display:flex;align-items:center;justify-content:space-between;">
		<span style="color:#888;font-size:12px;" id="brezngeo-ls-status">
			<?php esc_html_e( 'Click Analyse to find internal link opportunities.', 'brezngeo' ); ?>
		</span>
		<button type="button" id="brezngeo-ls-analyse" class="button">
			<?php esc_html_e( 'Analyse', 'brezngeo' ); ?>
		</button>
	</div>

	<div id="brezngeo-ls-results" style="display:none;margin-top:10px;">
		<div id="brezngeo-ls-list"></div>
		<div id="brezngeo-ls-actions" style="display:none;margin-top:8px;align-items:center;gap:8px;flex-wrap:wrap;">
			<button type="button" id="brezngeo-ls-select-all" class="button button-small">
				<?php esc_html_e( 'All', 'brezngeo' ); ?>
			</button>
			<button type="button" id="brezngeo-ls-select-none" class="button button-small">
				<?php esc_html_e( 'None', 'brezngeo' ); ?>
			</button>
			<button type="button" id="brezngeo-ls-apply" class="button button-primary" style="margin-left:auto;" disabled>
				<?php esc_html_e( 'Apply (0 links)', 'brezngeo' ); ?>
			</button>
		</div>
	</div>

	<div id="brezngeo-ls-applied" style="display:none;color:#46b450;margin-top:8px;font-size:12px;"></div>
</div>
