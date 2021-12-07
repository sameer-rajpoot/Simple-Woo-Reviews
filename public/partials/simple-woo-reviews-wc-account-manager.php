<?php
/**
 * Provide a public area view for WC registeration and user account
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/public/partials
 */

class Simple_Woo_Reviews_WC_Account_Manager {

    /**
	 * Store profile photo field admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_show_photo_field  Get profile photo field admin settings value.
	 */

	private $swr_show_photo_field;

    /**
	 * Save profile photo field type admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_photo_field_type   Get profile photo field type admin settings value.
	 */

    private $swr_photo_field_type;


    /**
	 * Set file upload size for profile field.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_photo_upload_size   Set file upload size for profile field.
	 */

    // 1MB = 1024KB and 1MB = 1024*1024 = 1048576 bytes
    private $swr_photo_upload_size = 1048576;
    
    /**
	 * Enable popup review
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $enable_popup_review  Hide/show review popup
	 */

    private $enable_popup_review;

    /**
	 * Add title for review popup
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $swr_popup_title  Add title for review popup
	 */

    private $swr_popup_title;


    /**
	 * Set order status for showing popup.
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $swr_review_popup_order_status   Order status for showing popup.
	 */

    private $swr_review_popup_order_status;


    /**
	 * Initialize the class and set admin actions and public facing view.
	 * @since    1.0.0
	 * 
	 */
    function __construct() {
       
        /* Frontend actions for custom file upload field, validation and saving user data */
        add_action( 'woocommerce_register_form', array($this, 'swr_add_wc_photo_field_register_callback'), 10 ); 
        add_action( 'woocommerce_register_form_tag', array($this, 'swr_change_wc_register_edit_acc_form_tag'), 10 );
        add_action( 'woocommerce_register_post', array($this, 'swr_validate_wc_photo_field'), 10, 3 );
        add_action( 'woocommerce_created_customer', array($this, 'swr_save_wc_user_profile_img'), 10, 3 );

        /* User profile admin actions for photo upload field and saving user data */
        add_action( 'show_user_profile', array($this, 'swr_add_admin_wc_photo_field_user_profile'), 10, 1 );
        add_action( 'edit_user_profile', array($this, 'swr_add_admin_wc_photo_field_user_profile'), 10, 1 );
        add_action( 'user_new_form', array($this, 'swr_add_admin_wc_photo_field_user_profile'), 10, 1 );
        add_action( 'user_register', array($this, 'swr_save_admin_wc_user_profile_img'), 10, 1 );
        add_action( 'profile_update', array($this, 'swr_save_admin_wc_user_profile_img'), 10, 1 );

        /* User profile dashboard account actions for photo upload field and saving user data */
        add_action('woocommerce_edit_account_form', array($this, 'swr_create_user_profile_field_callback'), 10 );
        add_action( 'woocommerce_save_account_details_errors', array($this, 'swr_validate_user_profile_img_field_callback'), 10, 2 );
        add_action( 'woocommerce_save_account_details', array($this, 'swr_save_user_account_profile_img'), 10, 1 );
        add_action( 'woocommerce_edit_account_form_tag', array($this, 'swr_change_wc_register_edit_acc_form_tag'), 10 );
		add_action('init', array( $this, 'swr_remove_default_commenter_gravater' ), 10 );
		add_action( 'woocommerce_review_before', array($this, 'swr_add_user_profile_img_to_comment'), 999, 1 );
        add_filter('woocommerce_my_account_my_orders_actions', array($this, 'swr_add_product_review_form_popup'), 10, 2);
        add_action("wp_ajax_swr_get_order_products",  array($this, 'swr_get_customer_order_products'), 10);
        add_action("wp_ajax_nopriv_swr_get_order_products",  array($this, 'swr_get_customer_order_products'), 10);
        add_action("wp_ajax_swr_save_order_product_review",  array($this, 'swr_save_order_product_feedback'), 10);
        add_action("wp_ajax_nopriv_swr_save_order_product_review",  array($this, 'swr_save_order_product_feedback'), 10);

        /* Fetch admin settings */       
        $this->swr_get_admin_options();
    }
	
    /**
     * Fetch plugin admin settings options 
     * @since    1.0.0
     */
    function swr_get_admin_options(){

        $this->swr_show_photo_field = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','show_photo_field_reg');   
        $this->swr_photo_field_type = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','photo_field_type');  
        $this->swr_enable_popup_review = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_popup_review_opt','enable_popup_review'); 
        $this->swr_popup_title = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_popup_review_opt','popup_title'); 
        $this->swr_review_popup_order_status = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_popup_review_opt','review_popup_order_status');    
    }

    /**
     * Add custom file upload field Woo registeration form
     * @since    1.0.0
     */
    function swr_add_wc_photo_field_register_callback(){ 

        //Declare variables
        $swr_file_html = '';
        $swr_req_attr = '';

        if( $this->swr_show_photo_field === 'yes' ){
            if( $this->swr_photo_field_type === 'yes' ){
                $swr_file_html .= '<span class="required">*</span>';
                $swr_req_attr = 'required';
            }else{
                $swr_req_attr = '';
            }
    ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" id="swr_img_field">
                <label for="swr_img_field" class="swr_feimg"><?php esc_html_e('Add Profile Picture','simple-woo-reviews'); echo  $swr_file_html; ?></label>
                <input type='file' name='swr_photo_img' accept='.png, .jpg, .jpeg' <?php echo $swr_req_attr; ?>/>
            </p>
            <div class="swr_prof_img_note">
                <p><?php esc_html_e( "Please use 100X100 size image for your profile image.", 'simple-woo-reviews' );?></p>
            </div>
    <?php
        }    
    }

    /**
     * Change WC form enctype to support file upload fields
     * @since    1.0.0
     */
    function swr_change_wc_register_edit_acc_form_tag(){
        if( $this->swr_show_photo_field === 'yes' ){
            echo  "enctype='multipart/form-data'";
        }
    }

    /**
     * Validate file upload fields before saving file
     * @since    1.0.0
     */
    function swr_validate_wc_photo_field( $username, $email, $errors ) {
        //Declare variables
        $swr_uploaded_file ='';
        $swr_valid_ext = array('png','jpg','jpeg');
        if( $this->swr_photo_field_type === 'yes' ){
            if ( $_FILES['swr_photo_img']['size'] === 0 ) {
                $errors->add( 'swr_empty_file_error', esc_html__( 'Please provide a profile image as it is a required field!', 'simple-woo-reviews' ) );
            }else{
                $swr_uploaded_file = explode( '.', sanitize_text_field($_FILES['swr_photo_img']['name']) );
                $swr_file_ext = strtolower(end($swr_uploaded_file));
                if( !in_array($swr_file_ext,$swr_valid_ext) ){
                    $errors->add( 'swr_file_type_error', esc_html__( 'Allowed image format are JPG, PNG and JPEG!', 'simple-woo-reviews' ) );
                }else if( $_FILES['swr_photo_img']['size'] > $this->swr_photo_upload_size ) {
                    $errors->add( 'swr_file_size_error', esc_html__( 'Please use image less than 1MB!', 'simple-woo-reviews' ) );
                }
            }

            return $errors;
        }
      
    }

    /**
     * Save user submitted file for user profile
     * @since    1.0.0
     */
    function swr_save_wc_user_profile_img( $customer_id,  $new_customer_data,  $password_generated ){

        if(  $_FILES['swr_photo_img']['size'] > 0  ){

           if( !function_exists('media_handle_upload') ){
                /* include related files if function do not exist */
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/media.php' );

                $swr_img_id = media_handle_upload( 'swr_photo_img' , 0 );
                if( is_wp_error($swr_img_id) ){
                    return false;
                }else{
                    do_action('swr_before_save_wc_user_image', $customer_id, $swr_img_id );
                    update_user_meta( $customer_id, 'swr_customer_prof_img', $swr_img_id );
                }
            }else{
			    $swr_img_id = media_handle_upload( 'swr_photo_img' , 0 );
                if( is_wp_error($swr_img_id) ){
                    return false;
                }else{
                    do_action('swr_before_save_wc_user_image', $customer_id, $swr_img_id );
                    update_user_meta( $customer_id, 'swr_customer_prof_img', $swr_img_id );
                }
		   }
        }

    }

    /**
     * Add user file upload options admin view for super admin
     * @since    1.0.0
     */
    function swr_add_admin_wc_photo_field_user_profile($user){
        //Declare variables
        $swr_user_img_id = '';
        $swr_user_prof_img ='';

        $swr_user_img_id = get_user_meta( $user->ID, 'swr_customer_prof_img', true );
        if(!empty($swr_user_img_id)){
           $swr_user_prof_img = wp_get_attachment_image_src($swr_user_img_id,'thumbnail');
        }
    ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="swr-profile-media"><?php esc_html_e('Profile Image', 'simple-woo-reviews'); ?></label>
                    </th>
                    <td>
                        <div class="swr-attachment-image">
                        <?php if( !empty( ($swr_user_prof_img[0]) ) ){ ?>
                            <img src="<?php echo esc_url($swr_user_prof_img[0]);?>" class="swr-main-prof-img"/>
                        <?php }else{
                            echo get_avatar( $user->ID, '80' );
                        } ?>
                            <input type="hidden" name="swr_attachment_id" class="swr-attachment-id" value="<?php echo (int)sanitize_text_field($swr_user_img_id); ?>" />
                        </div>
                        <div class="wp-media-buttons swr-admedia-btn">
                            <button class="button swr-add-media" id="swr-add-media"><?php esc_html_e('Select', 'simple-woo-reviews'); ?></button>
                            <button class="button swr-remove-media"><?php esc_html_e('Remove', 'simple-woo-reviews'); ?></button>
                            <p><?php esc_html_e('The profile image will be used by Simple Woo Reviews plugin on product reviews listing.','simple-woo-reviews');?></p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
<?php
   
    }
    
    /**
     * Save user file from admin dashboard for super admin
     * @since    1.0.0
     */
    function swr_save_admin_wc_user_profile_img( $user_id ){

        if( !current_user_can('edit_user', $user_id) ){
            return false;
        }
        if( isset( $_POST['swr_attachment_id'] ) 
            && is_numeric( $_POST['swr_attachment_id'] )
            && $_POST['swr_attachment_id'] > 0
        ) {
            update_user_meta( $user_id, 'swr_customer_prof_img', intval(sanitize_text_field($_POST['swr_attachment_id'])) );
        } else {
            update_user_meta( $user_id, 'swr_customer_prof_img', intval(sanitize_text_field($_POST['swr_attachment_id'])) );
        }
    
        return true;
    }

    /**
     * Create user file upload fields inside user account dashboard
     * @since    1.0.0
     */
    function swr_create_user_profile_field_callback() { 
        //Declare variables
        $swr_file_html = '';
        $swr_req_attr = '';

        if( $this->swr_show_photo_field === 'yes' ){
            if( $this->swr_photo_field_type === 'yes' ){
                $swr_file_html = '<span class="required">*</span>';
                $swr_req_attr = 'required';
            }else{
                $swr_req_attr = '';
            }
    ?>
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide swr_prof_field">
        <?php
            // Get current user id if logged in
            if( is_user_logged_in() ){
                $user_id = get_current_user_id();
            }
            // Get attachment id
            $attachment_id = get_user_meta( $user_id, 'swr_customer_prof_img', true );
            if ( $attachment_id ) { ?>
                <label><?php esc_html_e( 'Current Profile Image', 'simple-woo-reviews' ); ?></label>
                <div class="swr-acc-prof-img">
                    <?php echo wp_get_attachment_image( $attachment_id, array(100,100)); ?>
					<input type="hidden" name="swr_profile_img" value="<?php echo (int)sanitize_text_field($attachment_id); ?>" id="swr_profile_img" />
					<div class="swr_close_btn"><a href="javascript:void(0)">X</a></div>
                </div>
            <?php } ?>
            <div class="swr_upload_field">
				   <label for="swr_uacc_prof_img"><?php esc_html_e( 'Set Profile Photo', 'simple-woo-reviews' ); ?><?php echo $swr_file_html; ?></label>
            <input type="file" class="woocommerce-Input" name="swr_uacc_prof_img" accept='.png, .jpg, .jpeg' <?php echo $swr_req_attr; ?>>
			</div>
         
	    </p>
     
    <?php
        
        }
    }

    /**
     * Validate user submitted file inside user account dashboard
     * @since    1.0.0
     */
    function swr_validate_user_profile_img_field_callback( &$errors, &$user ){
        //Declare variables
        $swr_uploaded_file ='';
        $swr_valid_ext = array('png','jpg','jpeg');

        if( $this->swr_photo_field_type === 'yes' ){
            if ( $_FILES['swr_uacc_prof_img']['size'] === 0 ) {
                $errors->add( 'swr_uacc_empty_file_error', esc_html__( 'Please provide a profile image as it is a required field!', 'simple-woo-reviews' ) );
            }else{
                $swr_uploaded_file = explode( '.', sanitize_text_field($_FILES['swr_uacc_prof_img']['name']) );
                $swr_file_ext = strtolower(end($swr_uploaded_file));
                if( !in_array($swr_file_ext,$swr_valid_ext) ){
                    $errors->add( 'swr_uacc_file_type_error', esc_html__( 'Allowed image format are JPG, PNG and JPEG!', 'simple-woo-reviews' ) );
                }else if( $_FILES['swr_photo_img']['size'] > $this->swr_photo_upload_size ) {
                    $errors->add( 'swr_uacc_file_size_error', esc_html__( 'Please use image less than 1MB!', 'simple-woo-reviews' ) );
                }
            }

            return $errors;
        }

    }

    /**
     * Save and update user submitted file from user account dashboard
     * @since    1.0.0
     */
    function swr_save_user_account_profile_img( $user_id ) {
		if( $_FILES['swr_uacc_prof_img']['size'] > 0  ){
			if( !function_exists('media_handle_upload') ){
				/* include related files if function do not exist */
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				$swr_img_id = media_handle_upload( 'swr_uacc_prof_img' , 0 );
				if( is_wp_error($swr_img_id) ){
					return false;
				}else{
					do_action('swr_before_save_wc_user_profile_account_image', $user_id, $swr_img_id );
					update_user_meta( $user_id, 'swr_customer_prof_img', $swr_img_id );
				}
			}else{
				$swr_img_id = media_handle_upload( 'swr_uacc_prof_img' , 0 );
				if( is_wp_error($swr_img_id) ){
					return false;
				}else{
					do_action('swr_before_save_wc_user_profile_account_image', $user_id, $swr_img_id );
					update_user_meta( $user_id, 'swr_customer_prof_img', $swr_img_id );
				}
			}
		}else{
			update_user_meta( $user_id, 'swr_customer_prof_img', intval(sanitize_text_field($_POST['swr_profile_img'])) );
		}
		
    }

    /**
     * Remove default action user comment gravater
     * @since    1.0.0
     */
    function swr_remove_default_commenter_gravater(){
		remove_action( 'woocommerce_review_before', 'woocommerce_review_display_gravatar', 10 ); 
	}
    
    /**
     * Add user profile image to comment
     * @since    1.0.0
     */
	function swr_add_user_profile_img_to_comment($comment){
		//Declare variables
		$swr_commenter_img = '';
		$swr_user_id = '';
		$swr_img_html= '';
        //Fetch commenter user id
		$swr_user_id = $comment->user_id;
		if(!empty($swr_user_id)){
			$swr_commenter_img_id = get_user_meta( $swr_user_id,'swr_customer_prof_img',true );
			$swr_commenter_img = wp_get_attachment_image_src($swr_commenter_img_id,'thumbnail');
			if( !empty($swr_commenter_img[0]) ){
				$swr_img_html = '<div class="swr_commenter_img"><img src="'.esc_url($swr_commenter_img[0]).'" alt="'.esc_attr($comment->comment_author).'"/></div>';
				echo apply_filters('swr_commenter_image',$swr_img_html);
			}else{
				echo get_avatar( $comment, apply_filters( 'swr_commenter_gravatar_size', '60' ), '' );
			}
		}else{
			echo get_avatar( $comment, apply_filters( 'swr_commenter_gravatar_size', '60' ), '' );
		}
	}

    /**
     * Add review popup button customer account orders page
     * @since    1.0.1
     */
    function swr_add_product_review_form_popup( $actions, $order ){
        if( $this->swr_enable_popup_review === 'yes' ){
            $swr_order = wc_get_order( $order );
            $swr_status = $swr_order->get_status();
            if( $swr_status === $this->swr_review_popup_order_status || $swr_status ===  $this->swr_review_popup_order_status ){
                $actions['swr_review_popup_btn'] = 
                array(
                    'url'  => add_query_arg(array('swr_review_order_id' => $order->get_id(),'action' => 'swr_get_order_products'), esc_url(admin_url('admin-ajax.php'))),
                    'name' => esc_html__( 'Add Review', 'simple-woo-reviews' ),
                );
            }
        }
        return $actions;
    }


    /**
     * Show Ajax based popup review form on orders page
     * @since    1.0.1
     */
    function swr_get_customer_order_products(){
        //Declare variables 
        $swr_order_id = '';
        $swr_order = '';
        $swr_review_text = '';
        $swr_rating = '';
        $swr_review_popup_form_html = '';

        if( isset($_GET['swr_review_order_id']) && !empty($_GET['swr_review_order_id']) ){
            $swr_order_id = $_GET['swr_review_order_id'];
            $swr_order = wc_get_order( $swr_order_id );
            $swr_review_popup_form_html.=
            '<div class="swr_order_review">
                <div class="swr_order_prod_review">
                    <h3>'.sanitize_text_field($this->swr_popup_title).'</h3>';
            foreach ( $swr_order->get_items() as $swr_item_id => $swr_item ) {
                $swr_prod_id = $swr_item->get_product_id();
                $swr_prod_name = $swr_item->get_name();
                $swr_comment_ids = get_post_meta($swr_prod_id,"swr_order_prod_review_id",true);
                if( !empty($swr_comment_ids) && is_array($swr_comment_ids) ){
                    foreach($swr_comment_ids as $swr_comment_id ){
                        $swr_comment = get_comment($swr_comment_id);   
                        $swr_user_id = (int)$swr_comment->user_id;
                        if ( is_user_logged_in() ){ 
                            $swr_curr_user_id = (int)get_current_user_id();
                            if( $swr_curr_user_id === $swr_user_id ){
                                $swr_rating = get_comment_meta($swr_comment->comment_ID,"rating",true);
                                $swr_review_text = $swr_comment->comment_content;
                                break;
                            }
                        } 
                       
                    }
                }
                $swr_item_count = count($swr_order->get_items());
                if($swr_item_count > 1 ){
                    $swr_col_class = 'swr-review-col2';
                }else{
                    $swr_col_class = 'swr-review-col';
                }
                if(!empty($swr_prod_id)){
                    $swr_prod_img = wp_get_attachment_image_src( get_post_thumbnail_id($swr_prod_id), array('90','90'));
                }
                $swr_review_popup_form_html.='
                    <div class="swr-order-prod-review-form '.sanitize_text_field($swr_col_class).'">
                        <div class="swr-order-prod-img">
                            <img src="'.esc_url($swr_prod_img[0]).'" alt="'.sanitize_text_field($swr_prod_name).'"/>
                        </div>
                        <h4 class="swr_order_prod_title">'.sanitize_text_field($swr_prod_name).'</h4>';
                        if( !empty($swr_rating) && !empty($swr_review_text) && $swr_curr_user_id === $swr_user_id ){
                        $swr_review_popup_form_html.='
                            <div class="swr-prodorder-rat" data-score="'.(int)sanitize_text_field($swr_rating).'"></div>
							<div class="swr_order_review_comment">
								<p>'.sanitize_text_field($swr_review_text).'</p>
                         	</div>';
                        }else{
                            $swr_review_popup_form_html.='
                            <form class="swr_order_review_frm" id="swr_prod_'.sanitize_text_field($swr_prod_id).'_frm">
                                <div class="swr-order-prod-rating"></div>
                                <div class="swr-field-wrap">
                                    <input type="hidden" name="swr_review_prod_id" value="'.sanitize_text_field($swr_prod_id).'"/>
                                    <textarea class="swr_review_feedback" name="swr_review_feedback" placeholder="'.esc_html__("Add your comment here","simple-woo-reviews").'"></textarea>
                                </div>
                                <button type="submit" class="prod_order_review_btn" id="prod-'.sanitize_text_field($swr_prod_id).'-btn">'.esc_html__('Save Review','simple-woo-reviews').'</button>
                            </form>';
                        }
                $swr_review_popup_form_html.='
                    </div>';
             
             }
             $swr_review_popup_form_html.=
                '</div>
             </div>';

            if( !empty( $swr_review_popup_form_html ) ){
                echo $swr_review_popup_form_html;
            }
        }

        die();

    }

     /**
     * Save customer review for past orders
     * @since    1.0.1
     */
    function swr_save_order_product_feedback(){
        //Declare variables
        $swr_review_score = '';
        $swr_review_comment = '';
        $swr_review_prod_id = '';
        $swr_user = '';
        $swr_curr_user_id = '';
       
        if ( ! wp_verify_nonce( $_POST['nonce'], 'swr_security_nonce' ) ) {
            echo wp_send_json(array('success' => false, 'message' => esc_html__('Nonce failed, invalid request. Please try again!','simple-woo-reviews')));
        }else{
            if(!empty($_POST['review_score']) && !empty($_POST['review_comment']) && !empty($_POST['review_prod_id'])){
                $swr_review_score = sanitize_text_field($_POST['review_score']);
                $swr_review_comment = sanitize_text_field($_POST['review_comment']);
                $swr_review_prod_id = sanitize_text_field($_POST['review_prod_id']);
                $swr_comment_date = current_time( 'mysql' );
                $swr_comment_date_gmt = get_gmt_from_date( $swr_comment_date );
                $swr_comment_moderation = get_option("comment_moderation");

                if( is_user_logged_in() ){
                    $swr_curr_user_id = (int)get_current_user_id();
                    $swr_user = get_user_by('ID',$swr_curr_user_id);
                    if( $swr_user ){
                        $swr_user_email = is_email($swr_user->user_email);
                        $swr_username = sanitize_text_field($swr_user->username);
                    }
                }
                
                $swr_comment_data = array(
                    'comment_post_ID' => $swr_review_prod_id,
                    'comment_author' => $swr_username,
                    'comment_author_email' => $swr_user_email,
                    'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
                    'comment_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
                    'comment_date' => $swr_comment_date,
                    'comment_date_gmt' => $swr_comment_date_gmt,
                    'comment_content' => $swr_review_comment,
                    'user_id' => $swr_curr_user_id,
                    'comment_type' => 'review'
                );
                if( $swr_comment_moderation === '1' ){
                    $swr_comment_data['comment_approved'] = 1;
                }else{
                    $swr_comment_data['comment_approved'] = 0;
                }
                //hook which is fired before saving the popup review account orders page
                do_action("swr_before_saving_order_product_review",$swr_comment_data);
                $swr_comment_id = wp_insert_comment(wp_filter_comment($swr_comment_data));
          
                if($swr_comment_id > 0){
                    //hook which is fired after saving the popup review account orders page
                    do_action("swr_after_saving_order_product_review",$swr_comment_id,$swr_comment_data);      
                    update_comment_meta( $swr_comment_id,'rating',$swr_review_score );
                    $swr_user_review = get_post_meta($swr_review_prod_id,"swr_order_prod_review_id",true);
                    if( !empty($swr_user_review) && is_array($swr_user_review) ){
                        if( !in_array( $swr_comment_id, $swr_user_review ) ){
                            array_push($swr_user_review,$swr_comment_id);
                            update_post_meta( $swr_review_prod_id, "swr_order_prod_review_id", $swr_user_review );
                        }
                    }else{
                        $swr_review_id[] = $swr_comment_id;
                        update_post_meta( $swr_review_prod_id, "swr_order_prod_review_id", $swr_review_id );
                    }
                    $swr_prod_ids_arr = get_option("swr_order_product_review_ids");
					if( !empty($swr_prod_ids_arr) && is_array($swr_prod_ids_arr) ){
                        if( !in_array( $swr_review_prod_id, $swr_prod_ids_arr ) ){
                            $swr_prod_ids_arr[] = $swr_review_prod_id;
                            update_option("swr_order_product_review_ids",$swr_prod_ids_arr);
                        }
					}else{
						$swr_prod_ids[] = $swr_review_prod_id;
						update_option("swr_order_product_review_ids",$swr_prod_ids);
					}
                    echo wp_send_json(array('success' => true, 'message' => esc_html__('Review is saved successfully','simple-woo-reviews'),'comment' => $swr_review_comment,'rating' => $swr_review_score));
                }
                
                
            }
            
        }

        die();
    }

}


new Simple_Woo_Reviews_WC_Account_Manager();
