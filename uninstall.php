<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
delete_option( 'brezngeo_settings' );
delete_option( 'brezngeo_keyword_settings' );
delete_post_meta_by_key( '_brezngeo_meta_description' );
delete_post_meta_by_key( '_brezngeo_keyword_main' );
delete_post_meta_by_key( '_brezngeo_keyword_secondary' );
delete_post_meta_by_key( '_brezngeo_keyword_results' );
