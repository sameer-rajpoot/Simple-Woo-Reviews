<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function swr_delete_plugin_data(){
	//Declare variables
	$swr_del_option = '';

	if( !is_admin() ){
		return;
	}
	//Check if deletion option checked during uninstall
	$swr_del_option = get_option('swr_general_opt');
	if( $swr_del_option['delete_plugin_data'] === 'yes' ){
		//Delete meta attached with product posts by id
		$swr_prod_ids = get_option("swr_order_product_review_ids");
		if( is_array($swr_prod_ids) && !empty($swr_prod_ids) ){
			foreach( $swr_prod_ids as $swr_prod_id ){
				delete_post_meta( $swr_prod_id,"swr_order_prod_review_id" );
			}
		}
		//Delete entries if exist in the custom post type on plugin uninstall
		$swr_posts= get_posts( array('post_type'=>'swr_reviews','numberposts'=>-1) );
		if($swr_posts){
			foreach ($swr_posts as $post_id) {
				if( $post_id->ID > 0 ){
					wp_delete_post( $post_id->ID, true );
				}
			}
		}
		//Delete registered option names
		delete_option('swr_general_opt');
		delete_option('swr_sync_opt');
		delete_option('swr_captcha_opt');
		delete_option('swr_design_opt');
		delete_option('swr_popup_review_opt');
		delete_option('swr_order_product_review_ids');
	}

}

//Function call to delete data plugin data
swr_delete_plugin_data();
