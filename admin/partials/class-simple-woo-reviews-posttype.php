<?php

/**
 * Provide a admin area view for the plugin
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/admin/partials
 */

class Simple_Woo_Reviews_Post_Type {


    /**
	 * Custom posttype of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $swr_posttype    The custom posttype of this plugin.
	 */

	public $swr_posttype = 'swr_reviews';


	/**
	 * Initialize the class and set its post type and admin actions.
	 * @since    1.0.0
	 * 
	 */
	function __construct() {

		//Set custom columns for the post type for admin view
		add_filter( "manage_{$this->swr_posttype}_posts_columns", array($this, 'swr_display_custom_col_head'), 10);
        //Make custom columns sorttable for the post type for admin view
        add_filter( "manage_edit-{$this->swr_posttype}_sortable_columns", array($this, 'swr_set_custom_columns_sortable'), 10 );
        //Make custom submenu to manage pending reviews status for the post type admin view
        add_filter( "views_edit-{$this->swr_posttype}", array($this,'swr_add_woo_reviews_submenu_shortcuts'), 10);
        //Change primary column for the post type admin view
        add_filter( 'list_table_primary_column',  array($this,'swr_change_row_actions_primary_column'), 10, 2 );
        //Set and remove default row actions for the post type admin view
        add_filter( 'post_row_actions', array($this, 'swr_change_posttype_row_actions'), 10, 2 );
        //Set and remove default post link for the post type admin view
        add_filter( 'get_edit_post_link', array($this, 'swr_remove_hyperlink_post_title'), 10, 3);
        //Restore trashed WC review to its previous status when status changes
        add_filter('wp_untrash_post_status',array($this, 'swr_restore_untrash_post_status'), 10, 3);
        
        //Register custom post type for managing WC reviews
        add_action( 'init', array( $this, 'swr_register_woo_reviews_posttype'), 5 );
        //Sort post type columns and display reviews as per desired sorting order
        add_action( 'pre_get_posts',  array($this, 'swr_custom_columns_sortable_query'), 10 );
        //Get and display custom columns data for post type admin view
        add_action( "manage_{$this->swr_posttype}_posts_custom_column", array($this, 'swr_get_custom_column_posttype_data'), 10, 2);
        //Get trashed WC review id for syncing with post type review data
        add_action( "trashed_comment" , array($this,'swr_get_trash_review_comment_id'), 10, 2 );
        //Get untrashed WC review id for syncing with post type review data
        add_action( "untrashed_comment" , array($this,'swr_get_untrash_review_comments_id'), 10, 2 );
        //Get deleted  WC review id for syncing with post type review data
        add_action( "deleted_comment" , array($this,'swr_get_deleted_review_comment_id'), 10, 2 );
        //Get updated WC review id for syncing with post type review data
        add_action('edit_comment', array($this,'swr_get_updated_comment_data'), 10, 2);
        //Get inserted WC review id for syncing with post type review data
        add_action( 'comment_post',array($this, 'swr_get_newly_added_commment'), 10, 3);
        //Get Wocommerce review comment transition status
        add_action( 'transition_comment_status',array($this, 'swr_get_transition_comment_status'), 10, 3);
        //Save Woocommerce customer ordered product review
        add_action("swr_after_saving_order_product_review",array($this,"swr_save_customer_order_prod_review"), 10, 2);
      
        //Set admin Ajax action for displaying product review data in popup
		add_action('wp_ajax_swr_get_review_data', array( $this,'swr_get_wc_product_review_data'));
        

	}

    /**
	 * Register custom post type for managing and syncing Woo reviews.
	 * @since    1.0.0
	 * 
	 */

	function swr_register_woo_reviews_posttype(){

		 	$labels = array(
			  'name'               => esc_html__( 'SWR Reviews', 'simple-woo-reviews' ),
			  'singular_name'      => esc_html__( 'SWR Reviews', 'simple-woo-reviews' ),
			  'add_new'            => esc_html__( 'Add New', 'simple-woo-reviews' ),
			  'add_new_item'       => esc_html__( 'Add New Review','simple-woo-reviews' ),
			  'edit_item'          => esc_html__( 'Edit Review','simple-woo-reviews' ),
			  'new_item'           => esc_html__( 'New Review','simple-woo-reviews' ),
			  'all_items'          => esc_html__( 'All SWR Reviews','simple-woo-reviews'),
			  'view_item'          => esc_html__( 'View Review','simple-woo-reviews' ),
			  'search_items'       => esc_html__( 'Search Review','simple-woo-reviews' ),
			  'not_found'          => esc_html__( 'No Review found','simple-woo-reviews' ),
			  'not_found_in_trash' => esc_html__( 'No Review found in the Trash','simple-woo-reviews' ), 
			  'parent_item_colon'  => '',
			  'menu_name'          => esc_html__( 'SWR Reviews','simple-woo-reviews' ), 
			);
			$args = array(
              'labels'        => $labels,
              'capability_type' => 'post',
              'capabilities' => array(
                  'create_posts' => false,
              ),
              'map_meta_cap' => true,
			  'description'   => esc_html__('Display Woo Reviews description','simple-woo-reviews'),
			  'public'        => true,
              'menu_position' => 80,
              'show_in_admin_bar' => false,
              'show_in_nav_menus' => false,
              'show_in_menu' => false,
			  'has_archive'   => true,
			  'menu_icon'     => 'dashicons-star-filled'
            );
            
			register_post_type( $this->swr_posttype, $args ); 

		}

        /**
         * Remove and add new custom columns for post type.
         * @param  array  $columns  Default post type columns.
         * @since    1.0.0
         */
		function swr_display_custom_col_head($columns){

            //First unset the default columns
            unset($columns);
            //Change sorting order of default columns and registered custom columns
            $columns['cb'] = "<input type=\"checkbox\" />";
            $columns['title'] = esc_html__('Product Name','simple-woo-reviews');
            $columns['prod_comment'] = esc_html__('Comment','simple-woo-reviews');
            $columns['rating'] = esc_html__('Rating','simple-woo-reviews');
            $columns['review_status'] = esc_html__('Status','simple-woo-reviews');
            $columns['customer_name'] = esc_html__('Customer Name','simple-woo-reviews');
            $columns['date'] = esc_html__('Date','simple-woo-reviews');

            return $columns;

        }
        
        /**
         * Remove and add new custom columns for post type.
         * @param  string  $default  Default primary column post type.
         * @param  string  $screen Post type screen admin view.
         * @since    1.0.0
         */
        function swr_change_row_actions_primary_column( $default, $screen ) {

            if ( "edit-{$this->swr_posttype}" === $screen ) {
                $default = 'prod_comment';
            }
            return $default;
        }

        /**
         * Display custom columns data for post type admin view.
         * @param  string  $column  Column names of post type.
         * @param  int  $post_id Current post id of post.
         * @since    1.0.0
         */
        function swr_get_custom_column_posttype_data( $column, $post_id ) {
            //Declare variables
            $swr_comment_id = '';
            
            $swr_comment_id = get_post_meta( $post_id,'swr_comment_id',true );
            $swr_comment = get_comment($swr_comment_id);
           
            switch ( $column ) {

                case 'prod_comment':
                    $swr_review_images_id = get_comment_meta($swr_comment->comment_ID,'swr_comment_images',true); ?> <p><?php echo esc_html($swr_comment->comment_content);?></p>
                <?php
                    if( $swr_review_images_id ){ ?>
                    <div class="ad-comment-images">
                    <?php
                        foreach( $swr_review_images_id as  $swr_review_img_id ){
                        $swr_review_img = wp_get_attachment_image_src( $swr_review_img_id, array(90,90)); 
                        $swr_review_lg_img = wp_get_attachment_image_src( $swr_review_img_id, array(480,480)); 
                        $swr_com_img_alt_text = get_post_meta( $swr_review_img_id, '_wp_attachment_image_alt',true);
                    ?>
                        <div class="ad-comment-img">
                            <a href="<?php echo esc_url($swr_review_lg_img[0]); ?>" class="swr-adcom-img-link"><img src="<?php echo esc_url($swr_review_img[0]); ?>" alt="<?php echo esc_html($swr_com_img_alt_text); ?>" /></a>
                        </div>
                        <?php } ?>
                    </div>
            <?php
                    }
                    break;
                
                case 'rating':
                    echo "<div class='swr-adrating-col' data-score='".get_comment_meta($swr_comment_id, 'rating', true)."'></div>";
                    break;

                case 'review_status':
                    $swr_post_status = get_post_status($post_id);
                    if( $swr_post_status !== 'trash' ){
                        if( $swr_comment->comment_approved === '1' ){
                            esc_html_e('Approved','simple-woo-reviews');
                            wp_update_post(array(
                                'ID' =>  $post_id,
                                'post_status' => 'publish'
                            ));
                        }else{
                            esc_html_e('Pending','simple-woo-reviews');
                            wp_update_post(array(
                                'ID' =>  $post_id,
                                'post_status' => 'pending'
                            ));
                        }   
                    }else{
                        esc_html_e('Not Available','simple-woo-reviews');
                    }
                    break;

                case 'customer_name':
                    echo esc_html($swr_comment->comment_author);
                    break;
        
            }
        }

        /**
         * Change row actions for post type admin view.
         * @param  array  $actions Default row actions of post type.
         * @param  object  $post Post object of post type admin view.
         * @since    1.0.0
         */
        
        function swr_change_posttype_row_actions( $actions, $post ) {

            if( $post->post_type === $this->swr_posttype ) {
                $swr_comment_id = get_post_meta( $post->ID,'swr_comment_id',true );
                unset( $actions['inline hide-if-no-js'] );
                unset( $actions['trash'] );
                $actions['edit'] = sprintf( wp_kses( __( '<a href="%s">Edit</a>', 'simple-woo-reviews' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( get_edit_comment_link( $swr_comment_id ) ) );
                $actions['view'] = sprintf( wp_kses( __( '<a href="%s" target="_blank">View</a>', 'simple-woo-reviews' ), array(  'a' => array( 'href' => array() ,'target' => array() ) ) ), esc_url( get_comment_link( $swr_comment_id ) ) );
                $actions['quick_view'] = sprintf( wp_kses(__( '<a href="'.add_query_arg( array('swr_post_id' =>  $swr_comment_id ,'action' => 'swr_get_review_data' ), esc_url(admin_url('admin-ajax.php'))).'" class="swr_view_popup" data-type="ajax">Quick View</a>', 'simple-woo-reviews' ), array('a' => array( 'href' => array(),'class' => array(),'data-type' => array())) ) );
                $actions['ID'] = sprintf( wp_kses(__( '<strong class="swr_pid">ID: %d</strong>', 'simple-woo-reviews' ), array('strong'=>array('class'=>array() ) )), esc_attr( $post->ID ) ); 
            }

            return $actions;
        }

       /**
         * Change default post title link post type admin view.
         * @param  string  $link Post link post type admin view.
         * @param  int  $post_id Current post id of post post type admin view.
         * @param  string  $context Post link context. 
         * @since    1.0.0
         */
        function swr_remove_hyperlink_post_title($link, $post_id, $context){

            if( !is_admin() ){
                return;
            }
            $screen = get_current_screen();
            $swr_comment_id = get_post_meta( $post_id,'swr_comment_id',true );
            $swr_comment = get_comment( $swr_comment_id );          
            if ($screen->id === "edit-{$this->swr_posttype}") {
                $link =  esc_url( get_permalink( $swr_comment->comment_post_ID ) );
                return $link;
            } else {
                return $link;
            }
        }
        
       /**
         * Change sortable columns post type admin view.
         * @param  array  Default and custom columns names post type admin view.
         * @since   1.0.0
         */
        function swr_set_custom_columns_sortable( $columns ) {

            $columns['rating'] = esc_html__('Rating','simple-woo-reviews');
            $columns['customer_name'] = esc_html__('Customer Name','simple-woo-reviews');

            return $columns;

        }

       /**
         * Make custom columns sortable based on current query post type admin view.
         * @param  object  $query Main query for post type admin view.
         * @since    1.0.0
         */

        function swr_custom_columns_sortable_query( $query ) {
            
            if ( !is_admin() ){
                return;
            }

            $orderby = $query->get( 'orderby');
            if ( 'rating' == $orderby ) {
                
                $query->set( 'meta_key', 'rating' );
                $query->set( 'orderby', 'meta_value_num' );
            
            }else if( 'customer_name' == $orderby ){
                
                $query->set( 'meta_key', 'customer_name' );
                $query->set( 'orderby', 'meta_value' );
            }
        }

        /**
         * Add pending submenu for post type for managing Woo reviews admin view.
         * @param  array  $views Default views for post type admin view.
         * @since    1.0.0
         */

        function swr_add_woo_reviews_submenu_shortcuts($views){

            if( ( is_admin() ) && ( $_GET['post_type'] === $this->swr_posttype ) ) {

                $query_reviews_args = array(
                    'post_type'   => $this->swr_posttype,
                    'post_status' => 'pending',
                    'posts_per_page'=> -1
                );
                $query_reviews = new WP_Query($query_reviews_args);
                if( isset($_GET['post_status']) ){
                    $class_pending = ($_GET['post_status'] == 'pending') ? ' class="current"' : '';
                    $views['pending'] = sprintf( wp_kses(__('<a href="%s"'. $class_pending .'>'. esc_html__('Pending','simple-woo-reviews') .' <span class="count">(%d)</span></a>', '' ),array('a' => array('href' => array(),'class' => array(),'span' => array('class' => array() )))), admin_url("edit.php?&post_status=pending&post_type={$this->swr_posttype}"), $query_reviews->found_posts);
                }

                return $views;

            }
        }

        /**
         * Get trashed comment id for syncing post type reviews data admin view.
         * @param  int  $comment_id Comment id of trashed comment.
         * @param  object  $comment Comment object containing all comment data.
         * @since    1.0.0
         */
        function swr_get_trash_review_comment_id($comment_id, $comment){
            //Declare variables
            $swr_post_id = '';

            $swr_post_id = $this->swr_get_post_id_by_meta($comment_id);
            if( !empty($swr_post_id) ){
                wp_trash_post($swr_post_id);
            }else{
                return false;
            }
                       
        }

        /**
         * Get post id from post meta table associated with comment admin view.
         * @param  int  $comment_id Comment id to find post id of post admin view.
         * @since    1.0.0
         */

        function swr_get_post_id_by_meta($comment_id){

            //Declare variables
            global $wpdb;
            $swr_prepare_query='';

          
            $swr_prepare_query = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta where meta_key ='swr_comment_id' and meta_value ='%s'", $comment_id );
            $swr_get_post_id = $wpdb->get_row( $swr_prepare_query );

            if( !empty($swr_get_post_id->post_id) ){
                return $swr_get_post_id->post_id;
            }
        }

        
        /**
         * Untrash associated post linked with related comment admin view
         * @param  int  $post_id Current post id for post admin view.
         * @since    1.0.0
         */
        function swr_get_untrash_review_comments_id( $post_id ){
            //Declare variables
            $swr_post_id = '';
            $swr_status = '';
            
            $swr_post_id = $this->swr_get_post_id_by_meta($post_id);
           
            if( !empty($swr_post_id) ){
                $swr_status = get_post_meta( $swr_post_id, '_wp_trash_meta_status', true );
                $swr_post_status = update_post_meta( $swr_post_id,'_swr_previous_status', $swr_status );
                if( $swr_post_status ){
                    $swr_prev_status = get_post_meta( $swr_post_id,'_swr_previous_status', $swr_status );
                }
                $swr_post = wp_untrash_post($swr_post_id);
                if( $swr_post ){
                    if( !empty($swr_prev_status) ){
                        $swr_post_update_id = wp_update_post( array(
                            'ID'           =>  $swr_post_id,
                            'post_status'  =>  $swr_prev_status 
                            )
                        );
                        if( $swr_post_update_id > 0 ){
                            delete_post_meta( $swr_post_id,'_swr_previous_status' );
                        }
                    }
                }
        
            }

        }   

        /**
         * Restore previous status of post before trash status
         * @param  string  $new_status New status of post admin view.
         * @param  int  $post_id Current post id for post admin view.
         * @param  string  $previous_status Previous status of post admin view.
         * @since    1.0.0
         */
        function swr_restore_untrash_post_status( $new_status, $post_id, $previous_status ){
            //Check post type by passing post_id
            $swr_post_type = get_post_type($post_id);
            if(  $swr_post_type === $this->swr_posttype ){
                $new_status = $previous_status;
            }
            return $new_status;
        }
        
        /**
         * Get deleted comment id to remove related post admin view
         * @param  int  $comment_id Comment id of deleted post admin view.
         * @param  object  $comment Comment object containing all data.
         * @since    1.0.0
         */

        function swr_get_deleted_review_comment_id($comment_id, $comment){
            //Declare variables
            $swr_post_id = '';

            $swr_post_id = $this->swr_get_post_id_by_meta($comment_id);
            $swr_review_post_id = $this->swr_get_order_prod_id();
          
            $swr_prod_ids = get_option("swr_order_product_review_ids");
            
            if( !empty($swr_post_id) ){
                wp_delete_post( $swr_post_id, true );
                if( !empty($swr_review_post_id) ){
                    $swr_review_arr = get_post_meta($swr_review_post_id,"swr_order_prod_review_id",true);
                    if( is_array($swr_review_arr) && !empty($swr_review_arr) ){
                        if(($swr_del_key = array_search($comment_id, $swr_review_arr)) !== false) {
                            unset($swr_review_arr[$swr_del_key]);
                            update_post_meta( $swr_review_post_id,"swr_order_prod_review_id",$swr_review_arr );
                        }
                    }
                    if(($key = array_search($swr_review_post_id, $swr_prod_ids)) !== false) {
						unset($swr_prod_ids[$key]);
				    	update_option( "swr_order_product_review_ids",$swr_prod_ids );
		            }
                }
            }else{
                return false;
            }
        }

        function swr_get_order_prod_id(){

            //Declare variables
            global $wpdb;

            $swr_get_post_id = $wpdb->get_row( "SELECT post_id FROM $wpdb->postmeta where meta_key ='swr_order_prod_review_id'" );

            if( !empty($swr_get_post_id->post_id) ){
                return $swr_get_post_id->post_id;
            }
        }
        
        /**
         * Update related post linked with the comment
         * @param  int  $comment_id Comment id of updated post admin view.
         * @param  array  $comm_data Comment array containing all comment data.
         * @since    1.0.0
         */

        function swr_get_updated_comment_data( $comment_id, $comm_data ){
             //Declare variables
            $swr_post_id = '';
          
            $swr_post_id = $this->swr_get_post_id_by_meta($comment_id);
            if( !empty($swr_post_id) ){
                $swr_post_update_id = wp_update_post( array(
                        'ID'  =>  $swr_post_id,
                        'post_content'  =>  $comm_data['comment_content'],
                        'post_date' =>  $comm_data['comment_date']
                    )
                );
                if( $swr_post_update_id > 0 ){
                    return true;
                }else{
                    return false;
                }
            }

        }

        /**
         * Add new post when a new comment is inserted, send notification, mailchimp sync admin view
         * @param  int  $comment_id Comment id of updated post admin view.
         * @param  string  $comment_status Comment status of newly added comment admin view.
         * @param  array  $comm_data Comment array containing all comment data.
         * @since    1.0.0
         */
        function swr_get_newly_added_commment( $comment_id, $comment_status, $comm_data ){
            //Declare variables
            $swr_mc_list_id = '';
            $swr_mc_api_key = '';
            $swr_review_status = '';
            $swr_meta_updated = '';
            $swr_site_title = '';
            $swr_prod_avg_rat ='';
            $swr_prod_rat = '';
            $swr_comm_status = '';
            $swr_prod_img = '';
            $swr_send_review_email = '';
            $swr_review_email_html = '';

            $swr_send_review_email = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','review_notification'); 
            //Add email address to mailchimp list if user has opted
            $swr_enable_mailchimp = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','enable_mailchimp');   
            if( $swr_enable_mailchimp === 'yes' ){
                if( !empty($_POST['email']) ){
                    $swr_mc_list_id = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','mailchimp_list_id');   
                    $swr_mc_api_key = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','mailchimp_api_key');   
                    if (!empty($swr_mc_list_id) && !empty($swr_mc_api_key) ){
                        $swr_datacenter = substr($swr_mc_api_key,strpos($swr_mc_api_key,'-')+1);
                        $args = array(
                            'method' => 'POST',
                            'timeout' => 180,
                            'headers' => array(
                                'Authorization' => 'Basic ' . base64_encode( 'api_key:'. $swr_mc_api_key )
                            ),
                            'body' => json_encode(array(
                                'email_address' => sanitize_email($_POST['email']),
                                'status'        => 'subscribed'
                            ))
                        );
                        
                        do_action( 'swr_before_newsletter_subscription', $comment_id, sanitize_email($_POST['email']));
                        $response = wp_remote_post( 'https://'.$swr_datacenter.'.api.mailchimp.com/3.0/lists/' . $swr_mc_list_id . '/members/', $args );
                        $body = json_decode( $response['body'] );
                        if ( $response['response']['code'] === 200 && $body->status === 'subscribed' ) { 
                            do_action( 'swr_after_newsletter_subscription', $comment_id,sanitize_email($_POST['email']),$body);
                        }
                    }
                }
            }
            
            //Add newly added review to the post type for syncing
            if( !empty($comm_data) && $comm_data['comment_type'] === 'review' ){
                $swr_comment_post = get_post($comm_data['comment_post_ID']);
                if( empty($swr_comment_post) ){
                    return false;
                }
                if( $comment_status === 1 ){
					$swr_review_status = 'publish';
             	}else{
            		$swr_review_status = 'pending';
				}
                $post_id = wp_insert_post(array (
                    'post_type' => $this->swr_posttype,
                    'post_title' => $swr_comment_post->post_title,
                    'post_content' => $comm_data['comment_content'],
                    'post_status' =>  $swr_review_status,
                    'post_date' =>   $comm_data['comment_date'],
                    'comment_status' => 'closed',   
					'ping_status' => 'closed',   
                
                ));
                if( $post_id > 0 ){
                    
                    do_action('swr_before_newly_added_review',$post_id,$comment_id);
					$swr_meta_updated = update_post_meta($post_id,'swr_comment_id', $comment_id);
                    do_action('swr_after_newly_added_review',$post_id,$comment_id,$swr_meta_updated);
                    
					if($swr_send_review_email === 'yes'){
                        //Send notification to admin of newly saved review
                        $swr_admin_email = get_option( 'admin_email' );
                        $swr_headers[] = 'Content-Type: text/html; charset=UTF-8';
                        if(!empty($comm_data['comment_author_email'])){
                            $swr_headers[] = 'Reply-To:'. sanitize_email($comm_data['comment_author_email']).''. "\r\n";
                        }
                        $swr_mail_header = apply_filters('swr_change_review_email_headers', $swr_headers );
                        $swr_prod_avg_rat =  get_post_meta( $swr_comment_post->ID, '_wc_average_rating', true );
                        $swr_prod_rat = get_comment_meta($comment_id,'rating',true); 
                        $swr_prod_img = get_the_post_thumbnail_url( $swr_comment_post->ID );
                        $product = wc_get_product($swr_comment_post->ID);
                        if( $product->get_stock_quantity() > 0 || $product->get_stock_status() === 'instock' ){
                            $swr_link_html = '<a target="_blank" href="'.esc_url(esc_url( get_permalink( $swr_comment_post->ID ) )).'">'.esc_html(ucwords($swr_comment_post->post_title)).'</a>';
                        }else{
                            $swr_link_html = '<h4>'.esc_html(ucwords($swr_comment_post->post_title)).'</h4>';
                        }
                        if( $comment_status === 1 ){
							$swr_comm_status = esc_html__('Approved','simple-woo-reviews');
						}else{
							$swr_comm_status = esc_html__('Unapproved','simple-woo-reviews');
						}
             
                        $swr_review_email_html .=
                        '<table width="100%" border="1" align="center" cellpadding="10" cellspacing="0" bgcolor="#f9f9f9" style="background-color:#f9f9f9;">
                            <tr align="center">
                                <th>'.esc_html__('Item','simple-woo-reviews').'</th>
                                <th>'.esc_html__('Product Name','simple-woo-reviews').'</th>
                                <th>'.esc_html__('Rating','simple-woo-reviews').'</th>
                                <th>'.esc_html__('Comments','simple-woo-reviews').'</th>
                                <th>'.esc_html__('Status','simple-woo-reviews').'</th>
                                <th>'.esc_html__('Average Rating','simple-woo-reviews').'</th>
                                <th>'.esc_html__('Username','simple-woo-reviews').'</th>
                                <th>'.esc_html__('Email','simple-woo-reviews').'</th>
                            </tr>
                            <tr align="center">
                                <td align="center"><img src="'.esc_url($swr_prod_img).'" width="45" height="45" alt="'.esc_html(ucwords($swr_comment_post->post_title)).'"/></td>
                                <td align="center">'.$swr_link_html.'</td>
                                <td align="center">'.esc_html($swr_prod_rat).'</td>
                                <td align="center">'.$comm_data['comment_content'].'</td>
                                <td align="center">'.esc_html($swr_comm_status).'</td>
                                <td align="center">'.esc_html($swr_prod_avg_rat).'</td>
                                <td align="center">'.sanitize_text_field($comm_data['comment_author']).'</td>';
                                if( !empty($comm_data['comment_author_email']) ){
                                    $swr_review_email_html .=
                                    '<td align="center" >'.sanitize_email($comm_data['comment_author_email']).'</td>';
                                }else{
                                    '<td align="center">'.esc_html__('Not Available','simple-woo-reviews').'</td>';
                                }
                        $swr_review_email_html .=
                        '</tr>
                        </table>';
                        //Send review email notification to admin
                        wp_mail($swr_admin_email ,'Product Review Notification',$swr_review_email_html,$swr_mail_header);
					}
				}
            }    
        }

        /**
         * Get comment transition status and synced with related post admin view
         * @param  string  Comment new status post type admin view.
         * @param  string  Comment old status post type admin view.
         * @param  object  Comment object containing all comment data.
         * @since    1.0.0
         */
        function swr_get_transition_comment_status( $new_status, $old_status, $comment ){
            //Declare Variables
            $swr_comment_id = '';
            $swr_post_id = '';

            if( $comment->comment_type === 'review' ){
                $swr_comment_id = $comment->comment_ID;
                $swr_post_id = $this->swr_get_post_id_by_meta($swr_comment_id);
                if( $old_status !== $new_status ) {
                    if( $new_status === 'unapproved' ) {
                        $swr_updated_post_id = wp_update_post(array(
                            'ID' =>  $swr_post_id,
                            'post_status' => 'pending'
                        ));

                        if( $swr_updated_post_id > 0 ){
                            return true;
                        }
                    }else if( $new_status === 'approved' ){
                        $swr_updated_post_id = wp_update_post(array(
                            'ID' =>  $swr_post_id,
                            'post_status' => 'publish'
                        ));

                        if( $swr_updated_post_id > 0 ){
                            return true;
                        }
                    }
                }

            }else{
                return false;
            }

        }

        /**
         * Add a popup quick view for post post type admin view
         * @since    1.0.0
         */
        function swr_get_wc_product_review_data(){
            //Declare variables
            $swr_popup_html = '';
            $swr_comm_meta_html = '';
            $swr_prof_img_html = '';
            $swr_username = '';
            $swr_user_register_date = '';
            $swr_user_acc_status = '';
            $swr_author_country = '';
            $swr_show_field = '';
            $swr_field_is_req = '';

            if( empty($_GET['swr_post_id']) ){
                return false;
            }

            $swr_post_id = (int)sanitize_text_field($_GET['swr_post_id']);
            $swr_comment = get_comment($swr_post_id);    
            $swr_post = get_post($swr_comment->comment_post_ID);
            $swr_user = get_userdata($swr_comment->user_id);
            $swr_review_images_id = get_comment_meta($swr_comment->comment_ID,'swr_comment_images',true);
            $swr_show_field = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','show_photo_field_reg');
            $swr_field_is_req = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','photo_field_type');
            $swr_prod_avg_rating =  get_post_meta( $swr_post->ID, '_wc_average_rating', true );

            
            if(class_exists('WC_Geolocation')){
                $swr_geo      = new WC_Geolocation();
                $swr_user_geo = $swr_geo->geolocate_ip( $swr_comment->comment_author_IP  );
                $swr_country  = $swr_user_geo['country'];
                $swr_author_country  =  WC()->countries->countries[ $swr_country ];
            }

            if($swr_user){
                
                if( !empty($swr_user->first_name) && !empty($swr_user->last_name) ){
                    $swr_username = $swr_user->first_name." ".$swr_user->last_name;
                }

                if( !empty($swr_user->user_registered) ){
                    $swr_user_register_date = $swr_user->user_registered;
                    $swr_user_acc_status = date( "M Y", strtotime( $swr_user_register_date ) );
                }

                $swr_user_img_id = get_user_meta( $swr_user->ID, 'swr_customer_prof_img', true );
                if( $swr_field_is_req === 'yes' &&  $swr_show_field === 'yes' ){
                   if( !empty($swr_user_img_id) ){
                        $swr_prof_img = wp_get_attachment_image_src($swr_user_img_id, array('90','90'));  
                        $swr_prof_img_html .= '<img src="'.esc_url($swr_prof_img[0]).'" alt="'.get_the_title($swr_user_img_id).'"/>';
                    }else{
                        $swr_prof_img_html .= get_avatar( $swr_comment->user_id, '80' );
                    }
                }else if( empty( $swr_field_is_req ) && $swr_show_field === 'yes' ){
                    if( !empty($swr_user_img_id) ){
                        $swr_prof_img = wp_get_attachment_image_src($swr_user_img_id, array('90','90'));  
                        $swr_prof_img_html .= '<img src="'.esc_url($swr_prof_img[0]).'" alt="'.get_the_title($swr_user_img_id).'"/>';
                    }else{
                        $swr_prof_img_html .= get_avatar( $swr_comment->user_id, '80' );
                        
                    }
                }else{
                    $swr_prof_img_html .= get_avatar( $swr_comment->user_id, '80' );
                }
                
                $swr_comm_meta_html .='
                <li>Name: <span>'.$swr_username.'</span></li>
                <li>Email:<span>'.$swr_comment->comment_author_email.'</span></li>
                <li>Registered Since: <span>'.$swr_user_acc_status.'</span></li>';
                if( !empty($swr_author_country) ){
                    $swr_comm_meta_html .='
                    <li>Country:<span>'.$swr_author_country.'</span></li>';
                }
                
            }else{
                $swr_comm_meta_html .='
                <li>Name: <span>'.$swr_comment->comment_author.'</span></li>
                <li>Email:<span>'.$swr_comment->comment_author_email.'</span></li>';
                if( !empty($swr_author_country) ){
                    $swr_comm_meta_html .='
                    <li>Country:<span>'.$swr_author_country.'</span></li>';
                }
            }
                
            $swr_popup_html .='
                <div class="swr-adpopup-outer">
                    <div class="swr-adauthor-img">
                        '.$swr_prof_img_html.'
                    </div>
                    <div class="swr-adpopup-content">
                      <h3>'.$swr_post->post_title.'</h3>
                      <strong class="swr-adcomm-date">Date Published : '.$swr_comment->comment_date.'</strong>
                      <div class="swr-adrating-outer">
                          <div class="swr-adcomm-rating" data-score="'.get_comment_meta($swr_comment->comment_ID, 'rating', true).'"></div>
                          <span class="swr-adrat-score">'.number_format(get_comment_meta($swr_comment->comment_ID, 'rating', true),1).'</span>
                      </div>
                      <ul class="swr-adcomm-meta">
                         '. $swr_comm_meta_html.'
                      </ul>
                      <div class="swr-adreview-text">
                          <p>'.$swr_comment->comment_content.'</p>';
                          if( $swr_review_images_id ){
                              $swr_popup_html .= 
                              '<div class="ad-popup-com-images">
                                  <h4>'.esc_html__('Attached Images','simple-woo-reviews').'</h4>';
                                  foreach( $swr_review_images_id as  $swr_review_img_id ){
                                      $swr_review_img = wp_get_attachment_image_src( $swr_review_img_id, array(90,90)); 
                                      $swr_com_img_alt_text = get_post_meta( $swr_review_img_id, '_wp_attachment_image_alt',true);
                                      $swr_popup_html .= '
                                      <div class="ad-popup-com-img">
                                          <img src="'.esc_url($swr_review_img[0]).'" alt="'.esc_html($swr_com_img_alt_text).'" />
                                      </div>';
                                  }
              $swr_popup_html .=   
                          '</div>';
                          }
              $swr_popup_html .=   
                      '</div>';             
            $swr_popup_html .= 
                    '</div>';
                    if( !empty( $swr_prod_avg_rating ) ){
                        $swr_popup_html .='
                        <div class="swr-adavg-prod-rating">
                            <strong class="swr-adavg-rat">'.esc_html__('Average Rating ','simple-woo-reviews').'<span>'.$swr_prod_avg_rating.'</span></strong>
                        </div>';
                    }
            $swr_popup_html .='
                </div>';
            
            if(!empty($swr_popup_html)){
                echo  $swr_popup_html;
            }
            die();
        }

    /**
     * Save customer account orders page product review
     * @since    1.0.1
     */
    function swr_save_customer_order_prod_review( $swr_comment_id, $swr_comment_data ){
        //Declare variables
        $swr_comment = '';
        $comment_status = '';

        if( !empty($swr_comment_id) ){
            $swr_comment = get_comment($swr_comment_id);
            $comment_status = $swr_comment->comment_approved; 
        }
        //Function call to add customer account orders page review to post type
        $this->swr_get_newly_added_commment($swr_comment_id, $comment_status, $swr_comment_data);
    }
}

new Simple_Woo_Reviews_Post_Type();