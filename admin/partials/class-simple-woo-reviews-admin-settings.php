<?php
/**
 * Provide a admin area settings view for the plugin
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/admin
 */


class Simple_Woo_Reviews_Admin_Settings {

    /**
	 * Initialize the class and set admin menu.
	 * @since    1.0.0
	 * 
	 */
    function __construct() {

        add_action('admin_menu', array($this, 'swr_register_admin_menu'), 10 );
        add_filter('plugin_action_links', array($this, 'swr_add_plugin_settings_link'), 10, 2);
      
    }

    /**
     * Add settings action link in existing plugin action links
     * @since    1.0.0
     */
    function swr_add_plugin_settings_link($actions, $plugin_file){
		if( !is_admin() ){
			return;
		}
        //Add settings action link
        if ( SWR_PLUGIN_BASE === $plugin_file ) {
            $swr_settings_link = '<a href="'.esc_url(admin_url( 'admin.php?page='.SWR_PLUGIN)).'">'.esc_html__('Settings','simple-woo-reviews').'</a>';
            array_unshift($actions, $swr_settings_link);
        }

		return $actions;
    }

    /**
     * Register plugin admin menu and submenu and settings
     * @since    1.0.0
     */
    function swr_register_admin_menu(){
        //register admin menu pages
        add_menu_page( esc_html__('SWR Reviews','simple-woo-reviews'), esc_html__('SWR Reviews','simple-woo-reviews'), 'manage_options', 'simple-woo-reviews', array($this, 'swr_display_settings_page'), 'dashicons-star-filled' );
        add_submenu_page( 'simple-woo-reviews', esc_html__('Settings','simple-woo-reviews'),  esc_html__('Settings','simple-woo-reviews'), 'manage_options','simple-woo-reviews', null );
        add_submenu_page( 'simple-woo-reviews', esc_html__('SWR Reviews','simple-woo-reviews'),  esc_html__('SWR Reviews','simple-woo-reviews'), 'manage_options', 'edit.php?post_type=swr_reviews',null );
      
        //init admin settings for the plugin
        $this->swr_init_sync_settings();
        $this->swr_general_settings_init();
        $this->swr_captcha_settings_init();
        $this->swr_design_settings_init();
        $this->swr_show_shortcodes_info();
        $this->swr_popup_review_settings_init();
    
    }

    /**
     * Register plugin admin sync settings
     * @since    1.0.0
     */

    private function swr_init_sync_settings(){
        
        register_setting('swr_sync_opt_group','swr_sync_opt');
        add_settings_section('swr_sync_opt_section',null, array($this, 'swr_display_sync_options_desc'),'swr-sync-setting');
        add_settings_field('swr_sync_field', esc_html__('Sync Woo Reviews','simple-woo-reviews'), array($this, 'swr_sync_field_callback'),'swr-sync-setting','swr_sync_opt_section');
       
    }
    
    /**
     * Register plugin admin general settings
     * @since    1.0.0
     */

    function swr_general_settings_init(){

        register_setting('swr_general_opt_group','swr_general_opt');
        add_settings_section('swr_general_opt_section',null, array($this, 'swr_display_general_options_desc'),'swr-general-setting');
        add_settings_field('show_photo_field_reg', esc_html__('Enable Upload Field','simple-woo-reviews'), array($this, 'swr_general_show_photo_field_callback'),'swr-general-setting','swr_general_opt_section');
        add_settings_field('photo_field_type', esc_html__('Upload Field Type','simple-woo-reviews'), array($this, 'swr_general_photo_field_type_callback'),'swr-general-setting','swr_general_opt_section');
        add_settings_field('enable_mailchimp', esc_html__('Enable Mailchimp','simple-woo-reviews'), array($this, 'swr_general_enable_mailchimp_callback'),'swr-general-setting','swr_general_opt_section',array('label_for' => 'swr_enable_mailchimp'));
        add_settings_field('mailchimp_list_id', esc_html__('Mailchimp List ID','simple-woo-reviews'), array($this, 'swr_general_mailchimp_list_field_callback'),'swr-general-setting','swr_general_opt_section', array('label_for' => 'swr_mc_list_id','class'=>'swr-hide'));
        add_settings_field('mailchimp_api_key', esc_html__('Mailchimp API Key','simple-woo-reviews'), array($this, 'swr_general_mailchimp_api_field_callback'),'swr-general-setting','swr_general_opt_section', array('label_for' => 'swr_mc_api_key','class'=>'swr-hide'));
        add_settings_field('enable_image_reviews', esc_html__('Enable Photo Reviews','simple-woo-reviews'), array($this, 'swr_general_image_reviews_field_callback'),'swr-general-setting','swr_general_opt_section', array('label_for' => 'swr_img_reviews'));
        add_settings_field('enable_photo_review_lightbox', esc_html__('Enable Lightbox','simple-woo-reviews'), array($this, 'swr_general_photo_review_lightbox_field_callback'),'swr-general-setting','swr_general_opt_section', array('label_for' => 'swr_img_lightbox','class'=>'swr-hide-img-review'));
        add_settings_field('image_reviews_num', esc_html__('Allowed Images Per Review','simple-woo-reviews'), array($this, 'swr_general_image_reviews_num_field_callback'),'swr-general-setting','swr_general_opt_section', array('label_for' => 'swr_img_reviews_num','class'=>'swr-hide-img-review'));
        add_settings_field('review_notification', esc_html__('Send Review Notification','simple-woo-reviews'), array($this, 'swr_general_review_email_field_callback'),'swr-general-setting','swr_general_opt_section', array('label_for' => 'swr_review_email'));
        add_settings_field('delete_plugin_data', esc_html__('Delete Plugin Data','simple-woo-reviews'), array($this, 'swr_delete_plugin_data_callback'),'swr-general-setting','swr_general_opt_section', array('label_for' => 'swr_data_del_label'));

    }

    /**
     * Register plugin admin captcha settings
     * @since    1.0.0
     */
    function swr_captcha_settings_init(){

        register_setting('swr_captcha_opt_group','swr_captcha_opt');
        add_settings_section('swr_captcha_opt_section',null, array($this, 'swr_display_captcha_options_desc'),'swr-captcha-setting');
        add_settings_field('show_captcha_field_comm_frm', esc_html__('Enable Google Captcha V2','simple-woo-reviews'), array($this, 'swr_captcha_show_captcha_field_callback'),'swr-captcha-setting','swr_captcha_opt_section',array('label_for' => 'swr_enable_captcha'));
        add_settings_field('gcaptcha_site_key', esc_html__('Google Captcha V2 Site Key','simple-woo-reviews'), array($this, 'swr_gcaptcha_site_key_field_callback'),'swr-captcha-setting','swr_captcha_opt_section', array('class'=>'swr-hide'));
        add_settings_field('gcaptcha_secret_key', esc_html__('Google Captcha V2 Secret Key','simple-woo-reviews'), array($this, 'swr_gcaptcha_secret_key_field_callback'),'swr-captcha-setting','swr_captcha_opt_section', array('class'=>'swr-hide'));
    }

    /**
     * Register plugin admin design settings
     * @since    1.0.0
     */
    function swr_design_settings_init(){

        register_setting('swr_design_opt_group','swr_design_opt');
        add_settings_section('swr_design_opt_section',null, array($this, 'swr_design_options_desc'),'swr-design-setting');
        add_settings_field('primary_color', esc_html__('Select Primary Color','simple-woo-reviews'), array($this, 'swr_primary_color_field_callback'),'swr-design-setting','swr_design_opt_section');
        add_settings_field('heading_color', esc_html__('Select Heading Color','simple-woo-reviews'), array($this, 'swr_heading_color_field_callback'),'swr-design-setting','swr_design_opt_section');
        add_settings_field('content_color', esc_html__('Select Content Color','simple-woo-reviews'), array($this, 'swr_content_color_field_callback'),'swr-design-setting','swr_design_opt_section');
    }

    /**
     * Register plugin admin popup review settings
     * @since    1.0.1
     */
    function swr_popup_review_settings_init(){

        register_setting('swr_popup_review_opt_group','swr_popup_review_opt');
        add_settings_section('swr_popup_review_opt_section',null, array($this, 'swr_popup_reivew_options_desc'),'swr-popup-review-setting');
        add_settings_field('enable_popup_review', esc_html__('Enable Popup Review','simple-woo-reviews'), array($this,'swr_enable_popup_review_field_callback'),'swr-popup-review-setting','swr_popup_review_opt_section',array('label_for' => 'swr_enable_revpopup'));
        add_settings_field('popup_title', esc_html__('Add Popup Title','simple-woo-reviews'), array($this,'swr_popup_title_field_callback'),'swr-popup-review-setting','swr_popup_review_opt_section');
        add_settings_field('review_popup_order_status', esc_html__('Show Review Popup','simple-woo-reviews'), array($this,'swr_review_order_status_popup_field_callback'),'swr-popup-review-setting','swr_popup_review_opt_section');
    } 

    /**
     * Admin section for displaying shortcodes description
     * @since    1.0.0
     */

    function swr_show_shortcodes_info(){

        add_settings_section('swr_shortcodes_section',null, array($this, 'swr_get_shortcodes_desc'),'swr-shortcodes-description');
    }

    /**
     * Admin settings form for all plugin admin options
     * @since    1.0.0
     */

    function swr_display_settings_page(){ ?>
         <div class="swr_branding">
            <img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'images/swr_logo.jpg'; ?>">
            <div class="swr_branding_caption">
                <h2 class="swr-ptitle"><?php esc_html_e('SWR Reviews Lite Settings','simple-woo-reviews');?></h2>
                <p><?php esc_html_e('Showcase customers feedback in a new way!','simple-woo-reviews'); ?></p>
            </div>
        </div>
        <div class="wrap swr-adfrm-outer">
            <?php settings_errors(); ?>
            <?php $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field($_GET[ 'tab' ]) : 'swr_general_options'; ?>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=simple-woo-reviews&tab=swr_general_options' ));?>" class="nav-tab <?php echo esc_html($active_tab) === 'swr_general_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('General','simple-woo-reviews');?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=simple-woo-reviews&tab=swr_sync_options' ));?>" class="nav-tab <?php echo esc_html($active_tab) === 'swr_sync_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Sync','simple-woo-reviews');?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=simple-woo-reviews&tab=swr_captcha_options' ));?>" class="nav-tab <?php echo esc_html($active_tab) === 'swr_captcha_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Captcha','simple-woo-reviews');?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=simple-woo-reviews&tab=swr_design_options' ));?>" class="nav-tab <?php echo esc_html($active_tab) === 'swr_design_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Design','simple-woo-reviews');?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=simple-woo-reviews&tab=swr_popup_review_options' ));?>" class="nav-tab <?php echo esc_html($active_tab) === 'swr_popup_review_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Popup Review','simple-woo-reviews');?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=simple-woo-reviews&tab=swr_shortcodes_desc' ));?>" class="nav-tab <?php echo esc_html($active_tab) === 'swr_shortcodes_desc' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Shortcodes','simple-woo-reviews');?></a>
            </h2>
            
            <form method="post" action="options.php" class="swr-adoptions">
                <?php 
                if ( $active_tab === 'swr_general_options' ) {
                    settings_fields('swr_general_opt_group');
                    do_settings_sections('swr-general-setting');
                }else if($active_tab === 'swr_sync_options' ){
                    settings_fields('swr_sync_opt_group'); 
                    do_settings_sections('swr-sync-setting');
                }else if($active_tab === 'swr_captcha_options' ){
                    settings_fields('swr_captcha_opt_group'); 
                    do_settings_sections('swr-captcha-setting');
                }else if($active_tab === 'swr_design_options' ){
                    settings_fields('swr_design_opt_group'); 
                    do_settings_sections('swr-design-setting');
                }else if($active_tab === 'swr_popup_review_options' ){
                    settings_fields('swr_popup_review_opt_group'); 
                    do_settings_sections('swr-popup-review-setting');
                }else if($active_tab === 'swr_shortcodes_desc' ){
                    do_settings_sections('swr-shortcodes-description');
                }
                if( $active_tab !== 'swr_shortcodes_desc' && $active_tab !== 'swr_sync_options' ){
                    submit_button();
                }
                ?>
            </form>
        </div>
<?php
    }
     
    /**
     * Admin settings form for all plugin admin options
     * @since    1.0.0
     */

    function swr_display_sync_options_desc(){ ?>
        <div class="swr-adsec-opt-info">
            <h2><?php esc_html_e('Sync Options','simple-woo-reviews'); ?></h2>
            <p><?php esc_html_e('Sync all WC plugin product reviews by clicking the sync button.','simple-woo-reviews'); ?></p>
        </div>
        
<?php
    }

    /**
     * Admin settings sync field callback for syncing Woo reviews Ajax based
     * @since    1.0.0
     */

    function swr_sync_field_callback(){ 
       
?>
        <input type="button" class="button" id="sr_sync_btn" value="<?php esc_html_e('Sync Woo Reviews','simple-woo-reviews');?>" />
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('This action only need for one time - after you activate this plugin. After this all products reviews will be synced automatically. ','simple-woo-reviews')?></p>
            <p><?php esc_html_e('Use this button if you think that plugin not shows all latest WC product reviews.','simple-woo-reviews'); ?></p>
            <strong><?php esc_html_e('CAUTION: this can take some time for syncing reviews data.','simple-woo-reviews'); ?></strong>
        </div>
<?php

    }

    /**
     * Admin general options description for plugin
     * @since    1.0.0
     */
    
    function swr_display_general_options_desc(){ ?>
        <div class="swr-adsec-opt-info">
            <h2><?php esc_html_e('General Settings','simple-woo-reviews'); ?></h2>
            <p><?php esc_html_e('Configure General Settings of the plugin as per your needs. You will find all general configuration settings of the plugin under this tab.','simple-woo-reviews'); ?></p>
        </div>
<?php
    }

    /**
     * Admin settings upload field callback for user profile
     * @since    1.0.0
     */
    function swr_general_show_photo_field_callback(){ 
       $swr_show_photo_field = self::swr_get_option_value('swr_general_opt','show_photo_field_reg');    
    ?>
        <label>
            <input type="checkbox" name="swr_general_opt[show_photo_field_reg]" value="yes" <?php checked( 'yes', $swr_show_photo_field, true );  ?>/>
            <?php esc_html_e('Show Photo Field','simple-woo-reviews');?>
        </label>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Enable picture upload field on WC registeration form. The option will be used in displaying customer profile images in reviews.','simple-woo-reviews'); ?></p>
            <strong><?php esc_html_e('If you uncheck this option no field will display on the registeration form.','simple-woo-reviews'); ?></strong>
        </div>
<?php
    }

    /**
     * Admin settings upload field callback required or optional
     * @since    1.0.0
     */
    function swr_general_photo_field_type_callback(){ 
        $swr_photo_field_type = self::swr_get_option_value('swr_general_opt','photo_field_type');      
        
    ?>
        <label>
            <input type="checkbox" name="swr_general_opt[photo_field_type]" value="yes" <?php checked( 'yes', $swr_photo_field_type, true ); ?>/>
            <?php esc_html_e('Photo Field Type','simple-woo-reviews');?>
        </label>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Make the picture upload field required or optional on WC registeration form.','simple-woo-reviews'); ?></p>
        </div>
<?php
    }

    /**
     * Admin settings enable / disable mailchimp field callback
     * @since    1.0.0
     */
    function swr_general_enable_mailchimp_callback($args){

        $swr_enable_mailchimp = self::swr_get_option_value('swr_general_opt','enable_mailchimp');      
        
        ?>
        <label>
            <input type="checkbox" name="swr_general_opt[enable_mailchimp]" id="<?php echo esc_html($args['label_for']); ?>" value="yes" <?php checked( 'yes', $swr_enable_mailchimp, true ); ?>/>
            <?php esc_html_e('Enable Mailchimp','simple-woo-reviews');?>
        </label>
<?php    
    }

    /**
     * Admin settings mailchimp list id field callback 
     * @since    1.0.0
     */
    function swr_general_mailchimp_list_field_callback($args){
        $swr_mailchimp_list_id = self::swr_get_option_value('swr_general_opt','mailchimp_list_id');      
?>    
        <input type="text" name="swr_general_opt[mailchimp_list_id]" id="<?php echo esc_html($args['label_for']); ?>" value="<?php echo esc_attr( $swr_mailchimp_list_id ); ?>" class="swr-input"/>
        <div class="swr-adfield-desc">
            <p><?php echo wp_kses( __( 'You can get your Mailchimp List ID from the this <a href="https://mailchimp.com/help/find-audience-id/">link</a>', 'simple-woo-reviews' ), array(  'a' => array( 'href' => array() ) ) );?></p>
        </div>
<?php  
    }
    
    /**
     * Admin settings mailchimp API keys field callback 
     * @since    1.0.0
     */
    function swr_general_mailchimp_api_field_callback($args){
        $swr_mailchimp_api_key = self::swr_get_option_value('swr_general_opt','mailchimp_api_key');      
    ?>    
     
        <input type="text" name="swr_general_opt[mailchimp_api_key]" id="<?php echo esc_html($args['label_for']); ?>" value="<?php echo esc_attr( $swr_mailchimp_api_key ); ?>" class="swr-input"/>
        <div class="swr-adfield-desc">
            <p><?php echo wp_kses( __( 'You can get your Mailchimp API key from the this <a href="https://mailchimp.com/help/about-api-keys//">link</a>', 'simple-woo-reviews' ), array(  'a' => array( 'href' => array() ) ) );?></p>
        </div>
     
<?php
    }
    /**
     * Admin settings photo reviews field callback 
     * @since    1.0.0
     */
    function swr_general_image_reviews_field_callback($args){
        $swr_enable_image_reviews = self::swr_get_option_value('swr_general_opt','enable_image_reviews'); 
    ?>
        <label>
            <input type="checkbox" name="swr_general_opt[enable_image_reviews]" id="<?php echo esc_html($args['label_for']); ?>" value="yes" <?php checked( 'yes', $swr_enable_image_reviews, true ); ?>/>
            <?php esc_html_e('Enable Photo Reviews','simple-woo-reviews');?>
        </label>
<?php
    
    }
    /**
     * Admin settings photo reviews limit field callback 
     * @since    1.0.0
     */
    function swr_general_image_reviews_num_field_callback($args){
        $swr_image_reviews_num = self::swr_get_option_value('swr_general_opt','image_reviews_num'); 
    ?>
        <input type="number" name="swr_general_opt[image_reviews_num]" id="<?php echo esc_html($args['label_for']); ?>" min="1" max="10" value="<?php echo esc_attr($swr_image_reviews_num); ?>"/>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Set number of images allowed per single review. Minimum number of images allowed is 1 and maximum allowed limit can be set to 10. This option will only be applied on product pages.','simple-woo-reviews');?></p>
        </div>  

    <?php
    }
    function swr_general_photo_review_lightbox_field_callback($args){
        $swr_enable_img_review_lightbox = self::swr_get_option_value('swr_general_opt','enable_photo_review_lightbox'); ?>

        <label>
            <input type="checkbox" name="swr_general_opt[enable_photo_review_lightbox]" id="<?php echo esc_html($args['label_for']); ?>" value="yes" <?php checked( 'yes', $swr_enable_img_review_lightbox, true ); ?>/>
            <?php esc_html_e('Enable Lightbox on Photo Reviews','simple-woo-reviews');?>
        </label>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('You can enable or disable Lightbox to prevent or show images in popup on product pages. This setting only works for product pages.','simple-woo-reviews');?></p>
        </div>  

    
    <?php
    }

    /**
     * Admin settings review notification field callback for sending review emails to admin 
     * @since    1.0.0
     */
    function swr_general_review_email_field_callback($args){

        $swr_send_review_email = self::swr_get_option_value('swr_general_opt','review_notification'); 
    ?>
        <label>
            <input type="checkbox" name="swr_general_opt[review_notification]" id="<?php echo esc_html($args['label_for']); ?>" value="yes" <?php checked( 'yes', $swr_send_review_email, true ); ?>/>
            <?php esc_html_e('Send Admin Review Notification','simple-woo-reviews');?>
        </label>
<?php
    }

    /**
     * Admin settings delete plugin data upon uninstallation based on user selection
     * @since    1.0.0
     */
    function swr_delete_plugin_data_callback($args){
        
        $swr_delete_plugin_data = self::swr_get_option_value('swr_general_opt','delete_plugin_data'); 
    ?>
        <label>
            <input type="checkbox" name="swr_general_opt[delete_plugin_data]" id="<?php echo esc_html($args['label_for']); ?>" value="yes" <?php checked( 'yes', $swr_delete_plugin_data, true ); ?>/>
            <?php esc_html_e('Delete Plugin Data','simple-woo-reviews');?>
        </label>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Delete all plugin data upon uninstallation. This will delete all data when plugin uninstalled.','simple-woo-reviews');?></p>
        </div>
<?php
    
    }

    /**
     * Admin captcha settings description
     * @since    1.0.0
     */
    function swr_display_captcha_options_desc(){ ?>
        <div class="swr-adsec-opt-info">
            <h2><?php esc_html_e('Captcha Settings','simple-woo-reviews'); ?></h2>
            <p class="swr-mb"><?php echo wp_kses(__('Configure Captcha Settings of the plugin to display Google captcha on the review form. <strong>Note: Captcha will only be displayed to customers giving review without logging into their account to prevent spam reviews on your website.</strong>','simple-woo-reviews'), array('strong' => array())); ?></p>
        </div>
<?php
    }
    
    /**
     * Admin settings enable / disable captcha field callback on review form
     * @since    1.0.0
     */
    function swr_captcha_show_captcha_field_callback($args){
        $swr_show_captcha_field_comm_frm = self::swr_get_option_value('swr_captcha_opt','show_captcha_field_comm_frm');      
    ?>
        <label>
            <input type="checkbox" name="swr_captcha_opt[show_captcha_field_comm_frm]" id="<?php echo esc_html($args['label_for']); ?>" value="yes" <?php checked( 'yes', $swr_show_captcha_field_comm_frm, true ); ?>/>
            <?php esc_html_e('Enable Captcha','simple-woo-reviews');?>
        </label>
<?php    
    }

    /**
     * Admin settings captcha site key field callback
     * @since    1.0.0
     */
    function swr_gcaptcha_site_key_field_callback(){
        $swr_gcaptcha_site_key = self::swr_get_option_value('swr_captcha_opt','gcaptcha_site_key');      
    ?>    
        <input type="text" name="swr_captcha_opt[gcaptcha_site_key]" value="<?php echo esc_attr( $swr_gcaptcha_site_key ); ?>" class="swr-input"/>
        <div class="swr-adfield-desc">
            <p><?php echo wp_kses( __( 'You can get your Google Captcha V2 site key from the this <a href="https://www.google.com/recaptcha/admin/create" target="_blank">link</a>', 'simple-woo-reviews' ), array(  'a' => array( 'href' => array(),'target' => array() ) ) );?></p>
        </div>
    <?php  
    }

    /**
     * Admin settings captcha secret key field callback
     * @since    1.0.0
     */
    function swr_gcaptcha_secret_key_field_callback(){
        $swr_gcaptcha_secret_key = self::swr_get_option_value('swr_captcha_opt', 'gcaptcha_secret_key');      
    ?>    
        <input type="text" name="swr_captcha_opt[gcaptcha_secret_key]" value="<?php echo esc_attr( $swr_gcaptcha_secret_key ); ?>" class="swr-input"/>
        <div class="swr-adfield-desc">
            <p><?php echo wp_kses( __( 'You can get your Google Captcha V2 secret key from the this <a href="https://www.google.com/recaptcha/admin/create" target="_blank">link</a>', 'simple-woo-reviews' ), array(  'a' => array( 'href' => array() ,'target' => array() ) ) );?></p>
        </div>
    <?php  
    }

    /**
     * Admin design settings description
     * @since    1.0.0
     */
    function swr_design_options_desc(){ ?>
        
        <div class="swr-adsec-opt-info">
            <h2><?php esc_html_e('Color Scheme Options','simple-woo-reviews'); ?></h2>
            <p><?php esc_html_e('Configure color scheme settings of the plugin as per your needs. The color scheme selected will be applied on all shortcodes.','simple-woo-reviews'); ?></p>
        </div>
        
<?php
    
    }

    /**
     * Admin settings primary color field callback
     * @since    1.0.0
     */
    function swr_primary_color_field_callback() { 
        //Fetch option value
        $swr_primary_color = self::swr_get_option_value('swr_design_opt', 'primary_color'); 
    ?>
        <input type="text" name="swr_design_opt[primary_color]" value="<?php echo esc_attr($swr_primary_color); ?>" class="swr-color-picker"  data-default-color="#ffc600"/>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Set Primary color as per your requirements. Primary color will be reflected on all shortcodes front views.','simple-woo-reviews');?></p>
        </div>
<?php
    }

    /**
     * Admin settings heading color field callback
     * @since    1.0.0
     */
    function swr_heading_color_field_callback(){
         //Fetch option value
         $swr_heading_color = self::swr_get_option_value('swr_design_opt', 'heading_color');   
    ?>
        <input type="text" name="swr_design_opt[heading_color]" value="<?php echo esc_attr( $swr_heading_color ); ?>" class="swr-color-picker" data-default-color="#444"/>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Set Heading color as per your requirements. Heading color will be reflected on all shortcodes headings with few exceptions.','simple-woo-reviews');?></p>
        </div>
<?php

    }

    /**
     * Admin settings content color field callback
     * @since    1.0.0
     */
    function swr_content_color_field_callback(){
        //Fetch option value
        $swr_content_color = self::swr_get_option_value('swr_design_opt', 'content_color');   
   ?>
        <input type="text" name="swr_design_opt[content_color]" value="<?php echo esc_attr( $swr_content_color ); ?>" class="swr-color-picker" data-default-color="#555"/>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Set Content color as per your requirements. Content color will be reflected on all shortcodes content.','simple-woo-reviews');?></p>
        </div>
<?php

    }
    
    /**
     * Admin settings description for popup review section
     * @since    1.0.1
     */
    function swr_popup_reivew_options_desc(){ ?>
        <div class="swr-adsec-opt-info">
            <h2><?php esc_html_e('Popup Settings','simple-woo-reviews'); ?></h2>
            <p><?php esc_html_e('Configure Popup Settings of the plugin as per your needs. The order popup review will display on registered customer account page under Orders section.','simple-woo-reviews'); ?></p>
        </div>
<?php
    
    }

    /**
     * Admin setting for enable/disable popup review
     * @since    1.0.1
     */
    function swr_enable_popup_review_field_callback($args){
        //Fetch option value
        $swr_enable_popup_review = self::swr_get_option_value('swr_popup_review_opt', 'enable_popup_review'); 
    ?>
        <label>
            <input type="checkbox" name="swr_popup_review_opt[enable_popup_review]" id="<?php echo esc_html($args['label_for']); ?>" value="yes" <?php checked( 'yes', $swr_enable_popup_review, true ); ?>/>
            <?php esc_html_e('Enable Popup Review','simple-woo-reviews');?>
        </label>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Show popup review for customers which have already placed order. The popup form will display on orders page inside customer account page.','simple-woo-reviews');?></p>
        </div>

<?php
    }

    /**
     * Admin setting for showing popup tile for popup review section
     * @since    1.0.1
     */
    function swr_popup_title_field_callback(){
        //Fetch option value
        $swr_popup_title = self::swr_get_option_value('swr_popup_review_opt', 'popup_title'); 
    ?>
        <input type="text" name="swr_popup_review_opt[popup_title]" value="<?php echo esc_attr( $swr_popup_title );?>" class="swr-input"/>
        <div class="swr-adfield-desc">
            <p><?php esc_html_e('Set title for review popup form.','simple-woo-reviews');?></p>
        </div>
<?php  
    }

    /**
     * Admin setting for showing popup review section based on WC order statuses
     * @since    1.0.1
     */
    function swr_review_order_status_popup_field_callback(){
        //Fetch option value
        $swr_review_popup_order_status = self::swr_get_option_value('swr_popup_review_opt', 'review_popup_order_status');
?>
        <label>
            <input type="radio" name="swr_popup_review_opt[review_popup_order_status]" value="processing" <?php checked( 'processing', $swr_review_popup_order_status, true ); ?>/>
            <?php esc_html_e('Processing','simple-woo-reviews');?>
        </label>
        <label>
            <input type="radio" name="swr_popup_review_opt[review_popup_order_status]" value="completed" <?php checked( 'completed', $swr_review_popup_order_status, true ); ?>/>
            <?php esc_html_e('Completed','simple-woo-reviews');?>
        </label>
<?php
    }

    /**
     * Admin section for displaying shortcodes description and usage
     * @since    1.0.0
     */
    function swr_get_shortcodes_desc(){ ?>
        
        <div class="swr-adsec-opt-info">
            <h2><?php esc_html_e('How to use  plugin shortcodes?','simple-woo-reviews'); ?></h2>
            <p><?php esc_html_e('Plugin offers different shortcodes and each of them has a different purpose. Every shortcode has some default values set which you can change as per your needs. Here you will find information about plugin shortcodes.','simple-woo-reviews'); ?></p>
            <div class="swr-shortcodes-desc">
                <h4><?php esc_html_e('Reviews Listing Shortcode','simple-woo-reviews') ?></h4>
                <p><?php esc_html_e('Reviews Listing shortcode consists of 2 layout styles. One is Masonry and the other one is Grid view. The shortcode consist of many other options as described below to meet different requirements and you can configure them accordingly.','simple-woo-reviews') ?></p>
                <pre> [swr-reviews title='Hear From Our Happy Customers' custom_class='custom' show_date='yes' show_thumb='yes' orderby='title' order='ASC' num_posts=-1 show_pagination='yes' layout_style='Masonry' pagination_style='Default'  'show_reviews_count'= 'yes' 'icon_background_color'='#ffc600' 'rating_color'='#ffc600' 'main_heading_color'='#444']</pre>
                <h4><?php esc_html_e('Reviews Slider Shortcode','simple-woo-reviews') ?></h4>
                <p><?php esc_html_e('Reviews slider shortcode has modern and elegant front views developed on the current design trends and can be added on the homepage to showcase your website trusted customer reviews. The shortcode consist of many other options including color scheme and slider options as described below to meet different requirements.','simple-woo-reviews') ?></p>
                <pre> [swr-reviews-slider title='What Our Clients Say' custom_class='custom' post_id='' orderby='title' order='ASC' num_posts=-1 layout_style='Grid' 'show_date'='yes' 'icon_background_color'='#ffc600' 'rating_color'='#ffc600' main_heading_color'='#444' 'slidesShow'=3 'dots'='no' 'speed'=1500 'autoplay'='yes' 'autoplaySpeed'=2500 'arrows'='yes' 'pauseOnFocus'='yes' 'pauseOnHover'='yes']</pre>
                <h4><?php esc_html_e('Reviews Count Shortcode','simple-woo-reviews') ?></h4>
                <p><?php esc_html_e('Reviews count shortcode is another helpful shortcode to establish trust among your customers and promote your site as a trusted brand by showcasing verified customers reviews count. The shortcode has different layouts designed in such a way that can be used in the header, footer and on other sections. The Classic layout displays reviews badge with total reviews and also displays reviews average and is suitable to be added in the footer. The Text layout also displays the reviews badge with total reviews and reviews average and is suitable to be used in the header but can be used in the other sections.','simple-woo-reviews') ?></p>
                <pre> [swr-reviews-count title='Trusted Reviews' custom_class='custom' show_store_average='yes' type='approved' layout_style='Classic' 'rating_color'='#ffc600' 'background_color'='#ffc600']</pre>
                <div class ="swr_thank_msg">
                    <h3><?php esc_html_e('Enjoy our plugin and continue to support us!','simple-woo-reviews') ?></h3>
                </div>
            </div>
        </div>
        
<?php
    
    }

    /**
     * Function used to get plugin admin settings values
     * @since    1.0.0
     */
    static function swr_get_option_value( $swr_opt_group_name = '', $swr_opt_key = '' ){

       //Fetch plugin option name
        $swr_option_name = get_option($swr_opt_group_name);
        if( is_array($swr_option_name) && !empty($swr_option_name) && isset( $swr_option_name[$swr_opt_key]) ){
           return $swr_option_name[$swr_opt_key]; 
        }else{
           return false;
        }

    }


}

new Simple_Woo_Reviews_Admin_Settings();

