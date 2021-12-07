<?php
/**
 * Provide a public area view for the WC review form
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/public/partials
 */

class Simple_Woo_Reviews_WC_Review_Form_Manager {

    /**
	 * Save mailchimp (enabled/disabled) admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_enable_mailchimp   Get mailchimp admin settings value.
	 */
    private $swr_enable_mailchimp;

    /**
	 * Save captcha (enabled/disabled) admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_show_captcha   Get captcha admin settings value.
	 */
    private  $swr_show_captcha;

    /**
	 * Save captcha site key admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_gcaptcha_site_key   Get captcha site key admin settings value.
	 */
    private  $swr_gcaptcha_site_key;

    /**
	 * Save captcha site secret key admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_gcaptcha_secret_key   Get captcha site secret key admin settings value.
	 */
    private  $swr_gcaptcha_secret_key;

    /**
	 * Save photo reviews (enable/disable) admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_enable_image_reviews   Get photo reviews admin settings value.
	 */
    private  $swr_enable_image_reviews;

    /**
	 * Save photo reviews limit admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_img_reviews_num   Get allowed limit for photo reviews.
	 */
    private  $swr_img_reviews_num;
  
    /**
	 * Set file upload size for profile field.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_limit_file_size   Set file upload size for photo review field.
	 */
    // 1MB = 1024KB and 1MB = 1024*1024 = 1048576 bytes
    private $swr_limit_file_size = 1048576;
    
    
    /**
	 * Initialize the class and set admin actions and public facing view.
	 * @since    1.0.0
	 * 
	 */
    function __construct() {
     
        add_filter( 'woocommerce_product_review_comment_form_args', array( $this, 'swr_add_wc_review_fields_comment_form' ),999, 1);
        add_action( 'pre_comment_on_post', array( $this,'swr_validate_wc_review_fields_comment_form'), 10, 1 );
        add_action( 'wp_insert_comment', array( $this,'swr_insert_save_comment_review_images'), 10, 2 );
        add_action( 'woocommerce_review_after_comment_text',array( $this,'swr_display_review_comment_images' ), 10, 1);
        //Fetch admin settings
        $this->get_swr_admin_settings();
    }


    /**
	 * Get Captcha and General admin settings of plugin.
	 * @since    1.0.0
	 * 
	 */
    function get_swr_admin_settings(){

        $this->swr_enable_mailchimp = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','enable_mailchimp');  
        $this->swr_show_captcha = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_captcha_opt','show_captcha_field_comm_frm');   
        $this->swr_gcaptcha_site_key = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_captcha_opt','gcaptcha_site_key');   
        $this->swr_enable_image_reviews = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','enable_image_reviews');   
        $this->swr_img_reviews_num = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','image_reviews_num');   
        $this->swr_enable_lightbox = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','enable_photo_review_lightbox');  
        $this->swr_gcaptcha_secret_key = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_captcha_opt','gcaptcha_secret_key');  
    
    }


    /**
     * Add new fields to existing review form WC 
     * @param  array  $review_form WC default review form.
     * @since    1.0.0
     */

    function swr_add_wc_review_fields_comment_form( $review_form ){
        //Declare variables 
        $swr_wc_email_html = '';
        $swr_newsletter_html = '';
        $swr_capctcha_html = '';
        $swr_file_html = '';
        
        if( $this->swr_enable_mailchimp === "yes" ){
            if( is_user_logged_in() ){
                $swr_wc_nl_class = 'class="swr-lu-newsletter"';
            }else{
                $swr_wc_nl_class = 'class="swr-newsletter"';
            }
            if( is_user_logged_in() ){
                $swr_newsletter_html = '<p class="comment-form-swr-newsletter"><label for="swr-newsletter"><input id="swr-newsletter" '.$swr_wc_nl_class.' name="newsletter_check" type="checkbox" value="yes"/>'.esc_html__('Keep me up to date on news and exclusive offers (optional)','simple-woo-reviews').'</label></p>'; 
                $review_form['comment_field'] .= apply_filters( 'swr_wc_review_form_newsletter_html', $swr_newsletter_html );
                $swr_email_html = '<p class="comment-form-email swr-wc-rf-email"><label for="email">'.esc_html__('Email','simple-woo-reviews').'</label><input id="swr_email" name="email" type="email" value="" /></p>'; 
                $review_form['comment_field'] .= apply_filters( 'swr_logged_in_wc_review_form_email_html', $swr_email_html );
            }else{
                $swr_newsletter_html = '<p class="comment-form-swr-newsletter"><label for="swr-newsletter"><input id="swr-newsletter" '.$swr_wc_nl_class.' name="newsletter_check" type="checkbox" value="yes"/>'.esc_html__('Keep me up to date on news and exclusive offers (optional)','simple-woo-reviews').'</label></p>'; 
                $review_form['fields']['swr_newsletter'] ='';
                $review_form['fields']['swr_newsletter'] .= apply_filters( 'swr_wc_review_form_newsletter_html', $swr_newsletter_html );
                if( isset($review_form['fields']['email']) && empty($review_form['fields']['email']) ){
                    $swr_email_html = '<p class="comment-form-email swr-rf-email"><label for="email">'.esc_html__('Email','simple-woo-reviews').'</label><input id="email" name="email" class="swr-email" type="email" value="" /></p>'; 
                    $review_form['fields']['email'] .= apply_filters( 'swr_wc_review_form_email_html', $swr_email_html );
                }else if( !array_key_exists( 'email', $review_form['fields'] ) ){
                    $swr_email_html = '<p class="comment-form-email swr-rf-email"><label for="email">'.esc_html__('Email','simple-woo-reviews').'</label><input id="email" class="swr-email" name="email" type="email" value="" /></p>'; 
                    $review_form['fields']['email'] .= apply_filters( 'swr_wc_review_form_email_html', $swr_email_html );
                }
            }
        }

        if( $this->swr_enable_image_reviews === 'yes' ){
            $swr_file_html = '<p class="comment-form-file swr-wc-rf-file"><label for="swr_review_photo">'.sprintf( wp_kses(__('Upload up to %d images for your review (GIF, PNG, JPG, JPEG) <span class="swr-size">Recommended Size (480X480)</span>','simple-woo-reviews'),array('span'=>array('class'=>array()))), esc_attr($this->swr_img_reviews_num)).'</label><input id="swr_review_photo" name="review_image[]" type="file" multiple="multiple"/></p>'; 
            $review_form['comment_field'] .= apply_filters( 'swr_wc_review_form_image_review_html', $swr_file_html );
        }
        
        if( !is_user_logged_in() ){
            if( $this->swr_show_captcha ==='yes' ){
                if( !empty($this->swr_gcaptcha_site_key) ){
                    $swr_capctcha_html = '<div class="g-recaptcha swr-captcha" data-sitekey="'.sanitize_text_field($this->swr_gcaptcha_site_key).'"></div>';
                    $review_form['fields']['swr_captcha'] ='';
                    $review_form['fields']['swr_captcha'] .= apply_filters( 'swr_wc_review_form_captcha_html', $swr_capctcha_html );
                }
            }

        }
        
        $comment_form = apply_filters( 'swr_review_comment_form_args', $review_form );
        
        return $comment_form;

    }

    /**
     * Validate Google captcha and photo reviews fields before saving comment 
     * @param    int $comment_post_ID Post ID of commented post.
     * @since    1.0.0
     */
  
    function swr_validate_wc_review_fields_comment_form($comment_post_ID){
        //Declare variables
        $swr_valid_files_ext = array('png','jpg','jpeg','gif');
        $swr_uploaded_file = '';
        $swr_uploaded_file_ext = '';
        
        if( is_user_logged_in() ){
            if( isset($_POST['newsletter_check']) && $_POST['newsletter_check'] === 'yes' && empty( $_POST['email'] ) ){ ?>
                <p><?php echo wp_kses(__( '<strong>Error:</strong> Please fill the <strong>email</strong> field for newsletter subscription.', 'simple-woo-reviews' ),array('strong'=>array()));?></p>
            <?php
                echo sprintf( wp_kses(__( '<br>Go Back to <a href="%s">%s</a>', 'simple-woo-reviews' ), array('a'=>array('href'=>array()))), esc_url( get_the_permalink($comment_post_ID)), esc_attr(get_the_title($comment_post_ID))); 
                wp_die();
            } 
        }else{
            if( !empty($this->swr_gcaptcha_secret_key) ){
                $response = json_decode( wp_remote_retrieve_body( wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array( 'body' => array( 'secret' => $this->swr_gcaptcha_secret_key, 'response' => $_POST['g-recaptcha-response'] ) ) ) ), true );
                if( $response["success"] ) {
                        return true;
                }else{
                    wp_die(esc_html__( 'Error: Please fill the captcha before submitting review!', 'simple-woo-reviews' ));
                    return false;
                }
            }
        }

        if( isset($_FILES['review_image']) ){
            if( is_array( $_FILES['review_image']['name'] ) && !empty( $_FILES['review_image']['name'] ) ) {
                $swr_num_files = count( $_FILES['review_image']['name'] );
                if( $swr_num_files > $this->swr_img_reviews_num ){ ?>
                    <p><?php echo sprintf( wp_kses(__( '<strong>Error</strong>: You are trying to upload <strong>%d</strong> files than allowed <strong>%d</strong> files per single review!', 'simple-woo-reviews' ),array('strong'=>array())),esc_html($swr_num_files),esc_html($this->swr_img_reviews_num));?></p>
                <?php
                    echo sprintf( wp_kses(__( '<br>Go Back to <a href="%s">%s</a>', 'simple-woo-reviews' ), array('a'=>array('href'=>array()))), esc_url( get_the_permalink($comment_post_ID)), esc_attr(get_the_title($comment_post_ID))); 
                    wp_die();
                }
                for( $i=0; $i<$swr_num_files; $i++ ){
                    if ( $_FILES['review_image']['size'][$i] > $this->swr_limit_file_size ) {  ?>
                        <p><?php echo sprintf( wp_kses( __( '<strong>Error</strong>: Your uploaded <strong>%s</strong> file is exceeding file size limit of 1MB!', 'simple-woo-reviews' ),array('strong'=>array())), sanitize_text_field($_FILES['review_image']['name'][$i] ));?></p>
                    <?php    
                        echo sprintf( wp_kses(__( '<br>Go Back to <a href="%s">%s</a>', 'simple-woo-reviews' ), array('a'=>array('href'=>array()))), esc_url( get_the_permalink($comment_post_ID)), sanitize_text_field(get_the_title($comment_post_ID))); 
                        wp_die();
                    }
                    $swr_uploaded_file = explode( '.', $_FILES['review_image']['name'][$i] );
                    $swr_uploaded_file_ext = strtolower(end($swr_uploaded_file));
                    if( !in_array( $swr_uploaded_file_ext, $swr_valid_files_ext ) ){ ?>
                        <p><?php echo sprintf( wp_kses( __( '<strong>Error</strong>: Invalid uploaded file <strong>%s</strong> extension. Allowed formats are (GIF, PNG, JPG, JPEG)', 'simple-woo-reviews' ),array('strong'=>array())), sanitize_text_field($_FILES['review_image']['name'][$i]));?></p>
                <?php
                        wp_die();
                    }
                }
            }
        }

    }

    /**
     * Save photo review images for a comment
     * @param    int $comment_id Comment ID of current comment post.
     * @param    int $comment Comment object containing all comment data.
     * @since    1.0.0
     */

    function swr_insert_save_comment_review_images( $comment_id, $comment ){
        //Declare variables
        $swr_img_reviews_arr = array();
        $swr_attachment_id ='';
        if( isset($_FILES['review_image']) ){
            if( is_array( $_FILES['review_image']['name'] ) && !empty( $_FILES['review_image']['name'] ) ) {
                $swr_files = $_FILES['review_image']; 
                /* include related files if function do not exist */
                if( !function_exists('media_handle_upload') ){
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    require_once( ABSPATH . 'wp-admin/includes/media.php' );
                }
                foreach ( $swr_files['name'] as $key => $value ) {
                    if ( $swr_files['name'][$key] ) {
                        $swr_file = array(
                            'name' =>  sanitize_file_name( $swr_files['name'][$key] ),
                            'type' => sanitize_mime_type( $swr_files['type'][$key]),
                            'tmp_name' => sanitize_text_field( $swr_files['tmp_name'][$key] ),
                            'error' => sanitize_text_field( $swr_files['error'][$key] ),
                            'size' => intval( sanitize_text_field( $swr_files['size'][$key] ) )
                        );
                        $_FILES = array("uploaded_file" => $swr_file);
                        $swr_attachment_id = media_handle_upload("uploaded_file", 0);
                        if (is_wp_error($swr_attachment_id)) {
                            return false;
                        } else {
                            $swr_img_reviews_arr[] = $swr_attachment_id;
                        }
                    }
                }
                //Update comment meta for saving customer submitted images for a comment
                update_comment_meta( $comment_id, 'swr_comment_images',$swr_img_reviews_arr );
            }
        }
    }

    /**
     * Display photo review images for a comment
     * @param    int $comment Comment object containing all comment data.
     * @since    1.0.0
     */
    function swr_display_review_comment_images( $comment ){
        //Fetch review comment images
        $swr_review_images_id = get_comment_meta($comment->comment_ID,'swr_comment_images',true);
        if( $swr_review_images_id ){
    ?>
        <div class="swr-review-images">
            <?php
                foreach( $swr_review_images_id as  $swr_review_img_id ){
                    $swr_review_img = wp_get_attachment_image_src( $swr_review_img_id, array(90,90)); 
                    $swr_review_lg_img = wp_get_attachment_image_src( $swr_review_img_id, array(480,480)); 
                    $swr_com_img_alt_text = get_post_meta( $swr_review_img_id, '_wp_attachment_image_alt', true ); ?>
                    <div class="swr-review-img"> 
                    <?php if( $this->swr_enable_lightbox === 'yes' ){ ?>
                        <a href="<?php echo esc_url($swr_review_lg_img[0]);?>" class="swr-photo-review-img">     
                        <img src="<?php echo esc_url($swr_review_img[0]); ?>" alt="<?php echo esc_html($swr_com_img_alt_text); ?>" /></a>
                    <?php }else{ ?>
                        <img src="<?php echo esc_url($swr_review_img[0]); ?>" alt="<?php echo esc_html($swr_com_img_alt_text); ?>" />
                    <?php } ?>
                    </div>
            <?php } ?>
        </div>
<?php
        }
    }

}



new Simple_Woo_Reviews_WC_Review_Form_Manager();