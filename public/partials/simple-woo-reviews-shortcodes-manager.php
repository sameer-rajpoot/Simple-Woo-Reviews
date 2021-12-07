<?php
/**
 * Provide a public area view for the shortcodes of plugin
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/public/partials
 */

class Simple_Woo_Reviews_Shortcodes_Manager {

    /**
	 * Store profile photo field admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_show_prof_pic_field  Get profile photo field admin settings value.
	 */

	private $swr_show_prof_pic_field;

    /**
	 * Store profile photo field type admin settings value.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_prof_pic_field_type   Get profile photo field type admin settings value.
	 */

    private $swr_prof_pic_field_type;

    /**
	 * Custom posttype of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $swr_posttype    The custom posttype of this plugin.
	 */

	public $swr_posttype = 'swr_reviews';

    /**
	 * Plugin admin settings to hold dyanmic primary color.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_primary_color   Primary color admin settings.
	 */
    private $swr_primary_color;

    /**
	 * Plugin admin settings to hold dyanmic heading color.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_heading_color  Headings color admin settings.
	 */
    private $swr_heading_color;

    /**
	 * Plugin admin settings to hold dyanmic content color.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $swr_content_color    Content color admin settings.
	 */
    private $swr_content_color;

    
    function __construct(){

        //All registered shortcodes of plugin to render front views
        add_shortcode( 'swr-reviews', array($this, 'swr_get_all_reviews') );
        add_shortcode( 'swr-reviews-slider', array($this, 'swr_get_slider_reviews') );
        add_shortcode( 'swr-reviews-count', array($this, 'swr_get_total_reviews_count') );
     
        //Body filter to add custom class for plugin to use
        add_filter( 'body_class', array($this, 'swr_add_custom_body_class'), 10 );
        
        //Ajax load more feature to display posts without refreshing page
        add_action('wp_ajax_swr_load_more_reviews', array( $this, 'swr_load_more_reviews') );
        add_action('wp_ajax_nopriv_swr_load_more_reviews', array( $this, 'swr_load_more_reviews') );
        
        //Fetch profile photo admin settings
        $this->swr_get_profile_photo_admin_settings();
        //Render dynamic styles on the frontend
        add_action( 'wp_enqueue_scripts', array( $this, 'swr_render_dynamic_styles'), 20 );  

    }

    /**
     * Get plugin admin settings
     * @since    1.0.0
     */
    function swr_get_profile_photo_admin_settings(){
        //Fetch admin settings
        $this->swr_show_prof_pic_field = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','show_photo_field_reg');
        $this->swr_prof_pic_field_type = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_general_opt','photo_field_type');
        $this->swr_primary_color = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_design_opt','primary_color');
        $this->swr_heading_color = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_design_opt','heading_color');
        $this->swr_content_color = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_design_opt','content_color');
    }

    /**
     * Reviews listing to display all WC reviews
     * @param  array  $atts Attributes for a shortcode.
     * @param  string  $content Post content for shortcode.
     * @since    1.0.0
     */
    function swr_get_all_reviews( $atts = array(), $content = null ){

        //Set default parameters
        extract(shortcode_atts(array(
            'title' => 'Hear From Our Happy Customers',
            'custom_class' => 'custom',
            'show_date' => 'yes',
            'show_thumb' => 'yes',
            'orderby' => 'title',
            'order' => 'ASC',
            'num_posts' => -1,
            'show_reviews_count' => 'yes',
            'show_pagination' => 'yes',
            'icon_background_color' => '#ffc600',
            'rating_color' => '#ffc600',
            'main_heading_color' => '#444',
            'layout_style' => 'Masonry',
            'pagination_style' => 'Default',
            
        ), $atts));
        
        // Set query arguments
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $swr_args = array(  
            'post_type' => $this->swr_posttype,
            'post_status' => 'publish',
            'posts_per_page' => $num_posts, 
            'orderby' => $orderby, 
            'order' => $order, 
            'paged' => $paged,
        );
        //Declare variables
        $shortcode_html = '';
        $user_prof_img_html = '';
        $swr_custom_styles = '';

        $swr_query = new WP_Query( $swr_args ); 
        if ( $swr_query->have_posts() ) {
        $shortcode_html .= 
            '<section class="swr-reviews-listing '.esc_attr($custom_class).'">
                <div class="swr-container-fluid">
                    <div class="swr-row">';
                        if( !empty($title) ){
                            $shortcode_html .=
                            '<div class="swr-col-md-12">
                                <div class="swr-hstyle-outer">
                                    <div class="swr-hstyle">
                                        <h1>'.esc_attr($title).'</h1>
                                    </div>';
                                    if( $show_reviews_count === 'yes' ){
                                        $swr_reivew_avg = $this->swr_get_products_average_rating();
                                        $swr_review_count = get_comments( array(
                                            'status'   => 'approve',
                                            'type' => 'review',
                                            'count' => true
                                        ));
                                        //Filter for changing text and reviews count
                                        $swr_rating_text = sprintf(esc_html__(' Based on %d Reviews','simple-woo-reviews'), esc_attr($swr_review_count));
                                        $shortcode_html .=
                                        '<div class="swr-total-rat-count">';
                                            if( !empty( $swr_reivew_avg['avg_rating']) ){
                                                $shortcode_html .=
                                                '<div class="swr-prating" data-score="'.esc_attr($swr_reivew_avg['avg_rating']).'"></div>
                                                <strong class="swr-listing-count">'.apply_filters('swr_change_rating_text', $swr_rating_text,$swr_review_count).'</strong>';
                                            
                                            }else{
                                                $shortcode_html .=
                                                '<span class="icon-star-full swr-list-icon"></span>
                                                <span class="icon-star-full swr-list-icon"></span>
                                                <span class="icon-star-full swr-list-icon"></span>
                                                <span class="icon-star-full swr-list-icon"></span>
                                                <span class="icon-star-full swr-list-icon"></span>
                                                <strong class="swr-revlist-count">'.apply_filters('swr_change_rating_text', $swr_rating_text,$swr_review_count).'</strong>';
                                            }
                                        $shortcode_html .=   
                                        '</div>
                                </div>';
                                }
                            $shortcode_html .=
                            '</div>';
                        }
                        if( $layout_style === 'Masonry' ){
                            $shortcode_html .=
                                '<div class="swr-masonry-grid">';
                                    while ( $swr_query->have_posts() ){ 
                                        $swr_query->the_post(); 
                                        $swr_review_com_id = get_post_meta( get_the_ID(),'swr_comment_id',true );
                                        $swr_comment = get_comment($swr_review_com_id);
                                        $swr_prod_thumb = wp_get_attachment_image_src( get_post_thumbnail_id($swr_comment->comment_post_ID), array( '80','80' ) ); 
                                        $swr_img_alt_text = get_post_meta( get_post_thumbnail_id($swr_comment->comment_post_ID), '_wp_attachment_image_alt', true ) ;
                                        $swr_verified_user = get_comment_meta( $swr_comment->comment_ID, 'verified', true ); 
                                        $swr_date = get_comment_date(  get_option( 'date_format' ), $swr_comment->comment_ID );
                                        $swr_user = get_userdata($swr_comment->user_id);
                                        $product = wc_get_product($swr_comment->comment_post_ID);
                                        if( $pagination_style === "Load More" ){ 
                                            $swr_pager_class = 'swr-lm-pager';
                                        }else{
                                            $swr_pager_class = '';
                                        } 
                                        if($swr_user){
											if( !empty($swr_user->first_name) && !empty($swr_user->last_name) ){
                                                $swr_username = $swr_user->first_name." ".$swr_user->last_name;
											}else{
											    $swr_username = $swr_comment->comment_author;	
											}
                                        }else{
                                            $swr_username = $swr_comment->comment_author;
                                        }
                                        if( $this->swr_show_prof_pic_field === 'yes' ){
                                            if( $this->swr_prof_pic_field_type === 'yes' ){
                                                $swr_user_img_id = get_user_meta( $swr_comment->user_id,'swr_customer_prof_img', true);
                                                if( !empty( $swr_user_img_id ) ){
                                                   $swr_user_prof_img = wp_get_attachment_image_src(  $swr_user_img_id, array( '50','50' ) );
                                                   $user_prof_img_html = '<div class="swr-prof-img-wrap"><img src="'.esc_url($swr_user_prof_img[0]).'" alt="" class="swr_prof_pic" /></div>';
                                                }else{
                                                   $user_prof_img_html = get_avatar( $swr_comment->user_id, '50' );
                                                }
                                            }else{
                                                $swr_user_img_id = get_user_meta( $swr_comment->user_id,'swr_customer_prof_img', true);
                                                if( !empty( $swr_user_img_id ) ){
                                                   $swr_user_prof_img = wp_get_attachment_image_src(  $swr_user_img_id, array( '50','50' ) );
                                                   $user_prof_img_html = '<div class="swr-prof-img-wrap"><img src="'.esc_url($swr_user_prof_img[0]).'" alt="" class="swr_prof_pic" /></div>';
                                                }else{
                                                   $user_prof_img_html = get_avatar( $swr_comment->user_id, '50' );
                                                }
                                            }
                                        }
                                    $shortcode_html .=
                                        '<div class="swr-grid-item">
                                            <div class="swr-review-box">
                                                <figure class="swr-img">
                                                    <img src="'.esc_url($swr_prod_thumb[0]).'" alt="'.esc_attr($swr_img_alt_text).'"/>
                                                </figure>
                                                <div class="swr-review-content">
                                                    <div class="swr-prating" data-score="'.get_comment_meta($swr_comment->comment_ID, 'rating', true).'"></div>';
                                                    if( $show_date ==='yes' ){
                                                        $shortcode_html .=
                                                        '<strong class="swr-review-date">'.esc_attr($swr_date).'</strong>';
                                                    }
                                                    if( !empty( get_the_title() ) ){
                                                        if( $product || $product !== null ){
                                                            if( $product->get_stock_quantity() > 0 || $product->get_stock_status() === 'instock' ){
                                                                $shortcode_html .=
                                                                '<h3 class="swr-ptitle"><a href="'.esc_url(get_the_permalink($swr_comment->comment_post_ID)).'">'.esc_attr(get_the_title()).'</a></h3>';
                                                            }else{
                                                                $shortcode_html .=
                                                                '<h3 class="swr-ptitle">'.esc_attr(get_the_title()).'</h3>';
                                                            }
                                                        }
                                                    }   
                                                    $shortcode_html .= 
                                                    '<p>'.get_the_content().'</p>
                                                    <div class="swr-other-info">';
                                                    if( $show_thumb === 'yes'){  
                                                        $shortcode_html .= $user_prof_img_html;
                                                    }
                                                    $shortcode_html .= 
                                                        '<div class="swr-user-info">';
                                                            if( $swr_verified_user ){
                                                                $shortcode_html .= 
                                                                '<span class="icon-security swr-im-icon"></span><strong class="swr-verified">'. esc_html__(' Verified Buyer','simple-woo-reviews').'</strong>';
                                                            } 
                                                            $shortcode_html .=
                                                            '<strong class="swr-cname">'.esc_attr($swr_username).'</strong>
                                                        </div>
                                                    </div>        
                                                </div>'; 
                                        $shortcode_html .=
                                            '</div>
                                        </div>';
                                        }
                                $shortcode_html .= 
                                '</div>';
                            } else if ( $layout_style === 'Grid' ){
                            $shortcode_html .=
                            '<div class="swr-2col-grid">';
                                while ( $swr_query->have_posts() ){ 
                                    $swr_query->the_post(); 
                                    $swr_review_com_id = get_post_meta( get_the_ID(),'swr_comment_id',true );
                                    $swr_comment = get_comment($swr_review_com_id);
                                    $swr_prod_thumb = wp_get_attachment_image_src( get_post_thumbnail_id($swr_comment->comment_post_ID), array( '80','80' ) ); 
                                    $swr_img_alt_text = get_post_meta( get_post_thumbnail_id($swr_comment->comment_post_ID), '_wp_attachment_image_alt', true ) ;
                                    $swr_verified_user = get_comment_meta( $swr_comment->comment_ID, 'verified', true ); 
                                    $swr_date = get_comment_date(  get_option( 'date_format' ), $swr_comment->comment_ID );
                                    $swr_user = get_userdata($swr_comment->user_id);
                                    $product = wc_get_product($swr_comment->comment_post_ID);
                                    if( $pagination_style === "Load More" ){ 
                                        $swr_pager_class = 'swr-lm-pager';
                                    }else{
                                        $swr_pager_class = '';
                                    } 
                                    if($swr_user){
                                        if( !empty($swr_user->first_name) && !empty($swr_user->last_name) ){
                                            $swr_username = $swr_user->first_name." ".$swr_user->last_name;
                                        }else{
                                            $swr_username = $swr_comment->comment_author;	
                                        }
                                    }else{
                                        $swr_username = $swr_comment->comment_author;	
                                    }
                                    if(class_exists('WC_Geolocation')){
                                        $swr_geo      = new WC_Geolocation();
                                        $swr_user_geo = $swr_geo->geolocate_ip( $swr_comment->comment_author_IP  );
                                        $swr_country  = $swr_user_geo['country'];
                                        if(!empty($swr_country)){
                                            $swr_author_country  =  WC()->countries->countries[ $swr_country ];
                                        }
                                    } 
                                $shortcode_html .=
                                    '<div class="swr-col-md-6">
                                        <div class="swr-grid-box">
                                            <figure class="swr-grid-img"> 
                                                <img src="'.esc_url($swr_prod_thumb[0]).'" alt="'.esc_attr($swr_img_alt_text).'"/>
                                            </figure>
                                            <div class="swr-grid-content">';
                                               if( !empty( get_the_title() ) ){
                                                    if( $product || $product !== null ){
                                                        if( $product->get_stock_quantity() > 0 || $product->get_stock_status() === 'instock' ){
                                                            $shortcode_html .=
                                                            '<h3><a href="'.esc_url(get_the_permalink($swr_comment->comment_post_ID)).'">'.esc_attr(get_the_title()).'</a></h3>';
                                                        }else{
                                                            $shortcode_html .=
                                                            '<h3>'.esc_attr(get_the_title()).'</h3>';
                                                        }
                                                    }
                                                }
                                                $shortcode_html .=
                                                '<div class="swr-prating" data-score="'.get_comment_meta($swr_comment->comment_ID, 'rating', true).'"></div>
                                                <p>'.get_the_content().'</p>
                                                <div class="swr-review-user-info">
                                                    <ul class="swr-review-meta">';
                                                        if( $swr_verified_user ){ 
                                                            $shortcode_html .=
                                                            '<li><span class="icon-security"></span>'.esc_html__(' Verified Buyer','simple-woo-reviews').'</li>';
                                                        }
                                                        $shortcode_html .=
                                                        '<li><span class="icon-person"></span>'.esc_attr($swr_username).'</li>';
                                                        if( !empty($swr_author_country) ){
                                                            $shortcode_html .=
                                                            '<li><span class="icon-earth"></span>'.esc_attr($swr_author_country).'</li>';
                                                        }
                                        $shortcode_html .=
                                                    '</ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                                    } 
                        $shortcode_html .=
                            '</div>';  
                            } 
                        $shortcode_html .=
                        '<div class="swr-col-md-12">
                            <nav class="swr-pager '.$swr_pager_class.'">';
                                if( $show_pagination === "yes" ){
                                    if( $pagination_style === "Default" ){
                                        $shortcode_html .= $this->swr_get_pagination_links($swr_query); 
                                    }
                                }
                    $shortcode_html .=
                            '</nav>
                        </div>';
            $shortcode_html .=
                    '</div>
                </div>
            </section>';       
        }else{
            $shortcode_html .=
            '<div class="swr-nf-msg">
                <h4>'.esc_html__('No Reviews Found!','simple-woo-reviews').'</h4>
            </div>';
        }
        //Reset post data
        wp_reset_postdata(); 

        if( !empty($icon_background_color) || !empty($main_heading_color) || !empty($rating_color) ){
            if( $layout_style === 'Masonry' ){           
                wp_register_style( 'swr-slider-styles', false );
                wp_enqueue_style( 'swr-slider-styles' );
                $swr_custom_styles .= ".swr-review-box:after{ background-color:{$icon_background_color};}";
                $swr_custom_styles .= ".swr-hstyle h1{ color:{$main_heading_color};}";
                $swr_custom_styles .= ".swr-review-box .swr-prating,.swr-review-box .swr-prod-rat{ color:{$rating_color};}";
                wp_add_inline_style( 'swr-slider-styles', $swr_custom_styles);
            
            }else if( $layout_style === 'Grid' ){
                wp_register_style( 'swr-slider-styles', false );
                wp_enqueue_style( 'swr-slider-styles' );
                $swr_custom_styles .= ".swr-grid-img:before{ background-color:{$icon_background_color};}";
                $swr_custom_styles .= ".swr-review-meta li span{ color:{$icon_background_color};}";
                $swr_custom_styles .= ".swr-hstyle h1{ color:{$main_heading_color};}";
                $swr_custom_styles .= ".swr-grid-box .swr-prating,.swr-grid-box .swr-prod-rat{ color:{$rating_color};}";
                wp_add_inline_style( 'swr-slider-styles', $swr_custom_styles);
            }
        }

        return $shortcode_html;
    }

    /**
     * Display default pagination for reviews listing
     * @param  instance  $swr_query Instance of main query.
     * @since    1.0.0
     */
    function swr_get_pagination_links( $swr_query = '' ){
        
        $paged = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;	
        $pagenum_link = html_entity_decode( get_pagenum_link() );
        $query_args   = array();
        $url_parts    = explode( '?', $pagenum_link );

        if ( isset( $url_parts[1] ) ) {
            wp_parse_str( $url_parts[1], $query_args );
        }
        $pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
        $pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

        // Setup paginated links
        $swr_pager_links = paginate_links( array(
            'base'     => $pagenum_link,
            'current'  => $paged,
            'mid_size' => 1,
            'total' => $swr_query->max_num_pages,
            'add_args' => array_map( 'urlencode', $query_args ),
            'type' => 'list',
            'prev_text' => esc_html__('&larr; Prev','simple-woo-reviews'),
            'next_text' => esc_html__('Next &rarr;','simple-woo-reviews'),
        ) );

        if( $swr_pager_links ){
            return str_replace( "<ul class='page-numbers'>", '<ul class="swr-pagination">', $swr_pager_links );
        }

    }

    /**
     * Add body classes for plugin
     * @param  array  $classes Add new class in existing body classes
     * @since    1.0.0
     */
    function swr_add_custom_body_class($classes){
        //Add custom body class on frontend
        $classes[] = 'swr-reviews';
        return $classes;
    }

    /**
     * Reviews Slider to display reviews based on user defined criteria
     * @param  array  $atts Attributes for a shortcode.
     * @param  string  $content Post content for shortcode.
     * @since    1.0.0
     */
    function swr_get_slider_reviews( $atts = array(), $content = null ){
       
        //Set default parameters
        extract(shortcode_atts(array(
            'title' => '',
            'custom_class' => 'custom',
            'orderby' => 'title',
            'post_id' => '',
            'order' => 'ASC',
            'num_posts' => 9,
            'show_date' => 'yes',
            'icon_background_color' => '#ffc600',
            'rating_color' => '#ffc600',
            'main_heading_color' => '#444',
            'layout_style' => 'Grid',
            'slidesShow' => 3,
            'dots' => 'no',
            'speed' => 1500,
            'autoplay' => 'yes',
            'autoplaySpeed' => 2500,
            'arrows' => 'yes',
            'pauseOnFocus' => 'yes',
            'pauseOnHover' => 'yes',
         
        ), $atts));
        
        $swr_args = array(  
            'post_type' => $this->swr_posttype,
            'post_status' => 'publish',
            'posts_per_page' => $num_posts, 
            'orderby' => $orderby, 
            'order' => $order, 
        );

        if( !empty($post_id) ){
            $review_id = explode(',', $post_id); 
            $swr_args['post__in'] = $review_id;
        }

        //Declare variables
        $shortcode_html = '';
        $swr_custom_styles = '';
        $swr_rand_id = wp_rand(10,1000);
        $swr_rand_slider_id = 'swr-review-slider-'.$swr_rand_id;
        $swr_query = new WP_Query( $swr_args ); 
        if ( $swr_query->have_posts() ) {
            $shortcode_html .= 
            '<section id='.$swr_rand_slider_id.' class="swr-reviews-slider '.$custom_class.'">
                <div class="swr-container-fluid">
                    <div class="swr-row">';
                    if( !empty($title) ){
                        $shortcode_html .=
                        '<div class="swr-col-md-12">
                            <div class="swr-hstyle-2">
                                <h1>'.esc_attr($title).'</h1>
                            </div>
                        </div>';
                    }
                    if( $layout_style === 'Grid' ){
                        $shortcode_html .= 
                        '<div class="swr-grid-slider" data-slidesShow='.esc_attr($slidesShow).' data-dots='.esc_attr($dots).' data-speed='.esc_attr($speed).' data-autoplay="'.esc_attr($autoplay).'" data-autoplaySpeed='.esc_attr($autoplaySpeed).' data-arrows="'.esc_attr($arrows).'" data-pauseOnFocus="'.esc_attr($pauseOnFocus).'" data-pauseOnHover="'.esc_attr($pauseOnHover).'">';
                            while ( $swr_query->have_posts() ){ 
                                $swr_query->the_post(); 
                                $swr_review_com_id = get_post_meta( get_the_ID(),'swr_comment_id',true );
                                $swr_comment = get_comment($swr_review_com_id);
                                $swr_prod_thumb = wp_get_attachment_image_src( get_post_thumbnail_id($swr_comment->comment_post_ID), array( '80','80' ) ); 
                                $swr_img_alt_text = get_post_meta( get_post_thumbnail_id($swr_comment->comment_post_ID), '_wp_attachment_image_alt', true ) ;
                                $swr_verified_user = get_comment_meta( $swr_comment->comment_ID, 'verified', true ); 
                                $swr_date = get_comment_date(  get_option( 'date_format' ), $swr_comment->comment_ID );
                                $product = wc_get_product($swr_comment->comment_post_ID);

                                $shortcode_html .= 
                                '<div class="swr-slide-item">
                                    <div class="swr-rquote-icon">
                                        <span class="icon-quotes-left"></span>
                                    </div>
                                    <div class="swr-slide-box">
                                        <div class="swr-slide-content">
                                            <div class="swr-review-slide-meta">';
                                            if($show_date === 'yes'){
                                                $shortcode_html .= 
                                                    '<strong class="swr-slide-review-date"><span class="icon-calendar"></span> '.esc_attr($swr_date).'</strong>';
                                            }
                                            if( $swr_verified_user ){
                                                $shortcode_html .= 
                                                '<strong class="swr-slide-ver-review "><span class="icon-security"></span>'.esc_html__(' Verified Buyer','simple-woo-reviews').'</strong>';
                                            }
                                            $shortcode_html .= 
                                            '</div>';
                                            if( !empty(get_the_content()) ){
                                                $shortcode_html .= 
                                                '<p class="swr-excerpt">'.wp_trim_words( get_the_content(), 25, '<a href="javascript:void(0)" class="swr_content_link">'.esc_html__(' Read More','simple-woo-reviews').'</a>').'</p>';
                                                $shortcode_html .= 
                                                '<p class="swr-show-detail">'.get_the_content().'</p>';
                                            }
                                            $shortcode_html .= 
                                                '<div class="swr-slide-prod-info">
                                                    <figure class="swr-slide-img">
                                                        <img src="'.esc_url( $swr_prod_thumb[0]).'" alt="'.esc_attr($swr_img_alt_text).'"/>
                                                    </figure>';
                                                if( !empty( get_the_title() ) ){
                                                    if( $product || $product !== null ){
                                                        if( $product->get_stock_quantity() > 0 || $product->get_stock_status() === 'instock' ){
                                                            $shortcode_html .=
                                                            '<div class="review-prod-name">
                                                                <h3><a href="'.esc_url(get_the_permalink($swr_comment->comment_post_ID)).'">'.esc_attr(get_the_title()).'</a></h3>
                                                                <div class="swr-prating" data-score="'.get_comment_meta($swr_comment->comment_ID, 'rating', true).'"></div>
                                                            </div>';
                                                        }else{
                                                            $shortcode_html .=
                                                            '<div class="review-prod-name">
                                                                <h3>'.esc_attr(get_the_title()).'</h3>
                                                                <div class="swr-prating" data-score="'.get_comment_meta($swr_comment->comment_ID, 'rating', true).'"></div>
                                                            </div>';
                                                        }
                                                    }
                                                }
                                        $shortcode_html .= 
                                            '</div>
                                        </div>
                                    </div>
                                </div>';
                            }
            $shortcode_html .= 
                        '</div>';
                        }

            $shortcode_html .=         
                    '</div>
                </div>
            </section>';
        }else{
            $shortcode_html .=
            '<div class="swr-nf-msg">
                <h4>'.esc_html__('No Reviews Found!','simple-woo-reviews').'</h4>
            </div>';
        }

        //Reset post data
        wp_reset_postdata(); 

        
        if( !empty($icon_background_color) || !empty($main_heading_color) || !empty($rating_color) ){
            if( $layout_style === 'Grid' ){
                wp_register_style( 'swr-slider-styles', false );
                wp_enqueue_style( 'swr-slider-styles' );
                $swr_custom_styles .= ".swr-slide-item .swr-rquote-icon{ background-color:{$icon_background_color};}";
                $swr_custom_styles .= "#{$swr_rand_slider_id} .swr-hstyle-2 h1{ color:{$main_heading_color};}";
                $swr_custom_styles .= ".review-prod-name .swr-prating{ color:{$rating_color};}";
                wp_add_inline_style( 'swr-slider-styles', $swr_custom_styles);
            
            }
        }
       

        return  $shortcode_html;
                  
   }

  
    /**
     * Display all or verified or approved reviews count 
     * @param  array  $atts Attributes for a shortcode.
     * @param  string  $content Post content for shortcode.
     * @since    1.0.0
     */
    function swr_get_total_reviews_count( $atts = array(), $content = null ){

         //Set default parameters
         extract(shortcode_atts(array(
            'title' => 'Trusted Reviews',
            'custom_class' => 'custom',
            'show_store_average' => 'yes',
            'type' => 'approved',
            'layout_style'=> 'Classic',
            'rating_color' => '#ffc600',
            'background_color'=> '#ffc600'
        
        ), $atts));
        
        if( $type === 'approved' ){
            $swr_review_count = get_comments( array(
                'status'   => 'approve',
                'type' => 'review',
                'count' => true
            ));
        }else if( $type === 'all' ){
            $swr_review_count = get_comments( array(
                'type' => 'review',
                'count' => true
            ));
        }else if( $type === 'verified' ){
            $swr_review_count = get_comments( array(
                'type' => 'review',
                'meta_key' => 'verified',
                'meta_value' => 1,
                'count' => true
            ));
        }

        //Declare variables
        $shortcode_html = '';
        $swr_prod_score = '';
        $swr_custom_styles = '';
        //Fetch products average and success score
        $swr_prod_score = $this->swr_get_products_average_rating();

            if( !empty($swr_review_count) ){
                if( $layout_style === 'Classic' ){
                    $shortcode_html.='
                        <section class="swr-reviews-count '.esc_attr($custom_class).'">
                            <div class="swr-review-counter">
                                <div class="swr-review-counter">
                                    <div class="swr-rev-count">
                                        <strong class="swr-count">'.esc_attr($swr_review_count).'</strong>';     
                                        if( $show_store_average === 'yes' ){
                                            $shortcode_html.='
                                            <div class="swr-avg-info">
                                                <div class="swr-prating" data-score="'.esc_attr($swr_prod_score['avg_rating']).'"></div>
                                                <strong class="swr-reviews-avg-rat">'.esc_attr($swr_prod_score['avg_rating']).'</strong>
                                            </div>';
                                        }else{
                                            $shortcode_html.='
                                            <span class="icon-star-full swr-rat-icon"></span>
                                            <span class="icon-star-full swr-rat-icon"></span>
                                            <span class="icon-star-full swr-rat-icon"></span>
                                            <span class="icon-star-full swr-rat-icon"></span>
                                            <span class="icon-star-full swr-rat-icon"></span>';
                                        }
                                    $shortcode_html.='    
                                    </div>
                                    <div class="swr-review-ribbon-wrap">
                                        <h4 class="swr-review-ribbon">
                                            <strong class="swr-badge-title">'.esc_attr($title).'</strong>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </section>';
                }else if( $layout_style === 'Text' ){
                    $shortcode_html.='
                        <section class="swr-text-rcount '.esc_attr($custom_class).'">
                            <div class="swr-text-count-wrap">';
                            if( $show_store_average === 'yes' ){
                                $shortcode_html.='
                                <strong class="text-rev-count">'.esc_attr($swr_review_count).'</strong>
                                <div class="swr-text-avg-outer">
                                    <div class="swr-prating" data-score="'.esc_attr($swr_prod_score['avg_rating']).'"></div>
                                    <strong class="swr-text-avg-rat">'.esc_attr($swr_prod_score['avg_rating']).'</strong>
                                </div>';
                            }else{
                                $shortcode_html.='
                                <span class="icon-star-full swr-rat-icon"></span>
                                <span class="icon-star-full swr-rat-icon"></span>
                                <span class="icon-star-full swr-rat-icon"></span>
                                <span class="icon-star-full swr-rat-icon"></span>
                                <span class="icon-star-full swr-rat-icon"></span>
                                <strong class="text-reviews-num">'.esc_attr($swr_review_count).'</strong>';
                            }
                            $shortcode_html.=
                                '<div class="swr-text-counter">
                                     <strong class="swr-badge-text"><span class="icon-security"></span>'.esc_attr($title).'</strong>
                                </div>
                            </div>
                        </section>';
                }

            }else{
                $shortcode_html .=
                '<div class="swr-nf-msg">
                    <h4>'.esc_html__('No Reviews Found!','simple-woo-reviews').'</h4>
                </div>';
            }                 
            
            if( !empty($background_color) || !empty($rating_color) ){
                if( $layout_style === 'Classic' ){
                    wp_register_style( 'swr-revcount-styles', false );
                    wp_enqueue_style( 'swr-revcount-styles' );
                    $swr_custom_styles .= ".swr-review-counter .swr-review-ribbon,.swr-review-ribbon:before, .swr-review-ribbon:after{ background-color:{$background_color };}";
                    $swr_custom_styles .= ".swr-review-ribbon:before, .swr-review-ribbon:after{ border-color:{$background_color };}";
                    $swr_custom_styles .= ".swr-avg-info .swr-prating,.swr-rev-count span{ color:{$rating_color};}";
                    wp_add_inline_style( 'swr-revcount-styles', $swr_custom_styles);
                
                }else if( $layout_style === 'Text' ){
                    wp_register_style( 'swr-revcount-styles', false );
                    wp_enqueue_style( 'swr-revcount-styles' );
                    $swr_custom_styles .= ".swr-text-count-wrap{ background-color:{$background_color};}";
                    $swr_custom_styles .= ".swr-text-avg-outer .swr-prating,.swr-text-count-wrap .swr-rat-icon{ color:{$rating_color};}";
                    wp_add_inline_style( 'swr-revcount-styles', $swr_custom_styles);
               
                }
            }

            return $shortcode_html;
    }

    /**
     * Get total reviews 5 star count to calculate average and success score 
     * @since    1.0.0
     */
    function swr_get_products_average_rating(){

        //Declare variables
        global $wpdb;
        $swr_total_ratings = '';
        $swr_rating_count = 0;
        
        $swr_prod_rat_res = $wpdb->get_results( "SELECT meta_value FROM $wpdb->commentmeta as commentmeta JOIN $wpdb->comments as comments ON comments.comment_id = commentmeta.comment_id
        WHERE commentmeta.meta_key = 'rating' AND comments.comment_approved = 1 ORDER BY commentmeta.meta_value", ARRAY_A );
    
        if( is_array($swr_prod_rat_res) && !empty($swr_prod_rat_res) ){
            foreach( $swr_prod_rat_res  as $swr_rating_val ){
                $swr_rating_count = $swr_rating_count + $swr_rating_val['meta_value'];
            }
            $swr_total_ratings = count($swr_prod_rat_res)*5;
            $swr_average_rating = number_format( $swr_rating_count / count($swr_prod_rat_res), 1 );
            $swr_success_score = round( ($swr_rating_count / $swr_total_ratings )*100 );
            $swr_prod_score = array('avg_rating' => $swr_average_rating,'success_score'=> $swr_success_score);
            
            return  $swr_prod_score;
        }
    }

    /**
     * Render dynamic styles admin settings for shortcodes
     * @since    1.0.0
     */
    function swr_render_dynamic_styles(){
        //Declare variables
        $swr_custom_styles = '';

        if( !empty( $this->swr_primary_color ) ){
            
            $swr_custom_styles .= ".swr-reviews .swr-hstyle h1:before,.swr-hstyle-2 h1:before,.swr-hstyle-2 h1:after,.swr-grid-slider .slick-prev, .swr-grid-slider .slick-next,.swr-fw-slider .slick-prev, .swr-fw-slider .slick-next,.swr-loadmore,.swr-fw-slider .slick-dots li button:hover:before,.swr-grid-slider .slick-dots li.slick-active button:before{ background-color:{$this->swr_primary_color};}";
            
            $swr_custom_styles .= ".swr-fw-slider .slick-dots li button:hover:before, .swr-fw-slider .slick-dots li.slick-active button:before,.swr-fw-slider .slick-dots li.slick-active button:focus:before{ border-color:{$this->swr_primary_color};}";
        }
        
        if( !empty( $this->swr_heading_color ) ){
            
            $swr_custom_styles .= ".swr-grid-content h3,.swr-ptitle a, .swr-grid-content h3 a, .review-prod-name h3 a,.swr-ptitle,.review-prod-name h3,.swr-fw-prod-name h3,.swr-fw-prod-name a{ color:{$this->swr_heading_color};}";
        }

        if( !empty( $this->swr_content_color ) ){

            $swr_custom_styles .= ".swr-review-content p,.swr-grid-content p,.swr-cname,.swr-review-date,.swr-reviews .swr-review-user-info .swr-review-meta li,.swr-slide-content p,.swr-review-slide-meta .swr-slide-review-date,.swr-fw-slide-content p,.swr-fw-other-info .swr-fw-uname{ color:{$this->swr_content_color};}";
        }

        wp_register_style( 'swr-dynamic-styles', false );
        wp_enqueue_style( 'swr-dynamic-styles' );
        wp_add_inline_style( 'swr-dynamic-styles', $swr_custom_styles);

    }

}


new Simple_Woo_Reviews_Shortcodes_Manager();