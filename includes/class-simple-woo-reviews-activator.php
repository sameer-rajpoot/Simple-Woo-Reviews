<?php
/**
 * Fired during plugin activation
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/includes
 */

/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/includes
 * @author     ThemesJungle <themesjungle.conatact@gmail.com>
 */
class Simple_Woo_Reviews_Activator {

	/**
	 * Admin settings defaults plugin activation
	 * @since    1.0.0
	 */
	static function activate() {
		// Default admin settings when plugin is activated
		$swr_design_opt_default = array(
            'primary_color' => '#ffc600',
            'heading_color' => '#444444',
            'content_color' => '#555555',
        );
        update_option('swr_design_opt', $swr_design_opt_default);

		$swr_captcha_opt_default = array(
            'show_captcha_field_comm_frm' => '',
            'gcaptcha_site_key' => '',
            'gcaptcha_secret_key' => '',
        );
        update_option('swr_captcha_opt', $swr_captcha_opt_default);

        $swr_popup_review_opt_default = array(
            'enable_popup_review' => '',
            'popup_title' => '',
            'review_popup_order_status' => 'completed',
        );
        update_option('swr_popup_review_opt', $swr_popup_review_opt_default);

		$swr_general_opt_default = array(
            'show_photo_field_reg' => 'yes',
            'photo_field_type' => '',
            'enable_mailchimp' => '',
			'mailchimp_list_id' => '',
			'mailchimp_api_key' => '',
            'enable_image_reviews'=> '',
            'enable_photo_review_lightbox'=> '',
            'image_reviews_num'=> 1,
			'review_notification' => 'yes',
			'delete_plugin_data' => '',
        );
        update_option('swr_general_opt', $swr_general_opt_default);
	}

	

}
