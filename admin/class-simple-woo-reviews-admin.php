<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/admin
 * @author     ThemesJungle <themesjungle.conatact@gmail.com>
 */
class Simple_Woo_Reviews_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Custom posttype of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $swr_posttype    The custom posttype of this plugin.
	 */

	public $swr_posttype = 'swr_reviews';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		//Load admin area dependencies
		$this->swr_load_admin_dependencies();
		
		//Set admin Ajax action for syncing reviews
		add_action('wp_ajax_swr_get_wc_reviews', array( $this,'swr_sync_wc_reviews'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	function swr_admin_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simple_Woo_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simple_Woo_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-woo-reviews-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style('fancybox', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/jquery.fancybox.min.css', array(), $this->version );
		wp_enqueue_style('jquery-raty', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/jquery.raty.css', array(), $this->version );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	function swr_admin_enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Simple_Woo_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Simple_Woo_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$swr_allowed = ['profile.php', 'user-new.php', 'user-edit.php'];
		if ( in_array( $hook, $swr_allowed ) ) {
			wp_enqueue_media();
		}
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-woo-reviews-admin.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(	$this->plugin_name, 'swr_ajax_url', array('admin_url' =>admin_url('admin-ajax.php')));
		wp_enqueue_script( 'jquery-fancybox', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/jquery.fancybox.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-raty', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/jquery.raty.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __FILE__ ) . 'js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $this->version, true );

	}

	/**
	 * Load dependencies for the admin area.
	 *
	 * @since    1.0.0
	 */
	function swr_load_admin_dependencies() {

		/**
		 * The classes are responsible for loading dependencies in the admin area.
		 */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/class-simple-woo-reviews-admin-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/class-simple-woo-reviews-posttype.php';

	}

	/**
	 * Admin Ajax action for syncing WC reviews
	 *
	 * @since    1.0.0
	 */
	function swr_sync_wc_reviews(){
		//Declare variables
		$swr_review_status='';
		$swr_author='';
		$swr_author_email='';
		$swr_sucflag='';
		$swr_posts='';
		$swr_count = 0;

		//Delete old entries if exist in the custom post type
		$swr_posts= get_posts( array('post_type'=> $this->swr_posttype,'numberposts'=>-1) );
		if($swr_posts){
			foreach ($swr_posts as $post_id) {
				if( $post_id->ID > 0 ){
					wp_delete_post( $post_id->ID, true );
				}
			}
		}

		$args = array( 
            'status'      => array('hold','approve'), 
        	'post_type'   => 'product'
        );

		$comments = get_comments( $args );
		if(	$comments ){
			foreach( $comments as $comment ){
				$comment_post = get_post($comment->comment_post_ID);
				if( $comment->comment_approved === '1' ){
					$swr_review_status='publish';
				}else{
					$swr_review_status='pending';
				}
				$post_id = wp_insert_post(array (
					'post_type' => $this->swr_posttype,
					'post_title' => $comment_post->post_title,
					'post_content' => $comment->comment_content,
					'post_status' => $swr_review_status,
					'post_date' =>  $comment->comment_date,
					'comment_status' => 'closed',   
					'ping_status' => 'closed',   
				));
				if($post_id > 0){
					update_post_meta($post_id,'swr_comment_id',$comment->comment_ID);
					$swr_sucflag = true;	
				}
				$swr_count++;
			}
			wp_send_json( array('success' => $swr_sucflag, 'review_count' => $swr_count ) );
		}else{
			wp_send_json( array('success' => false ) );
		}
		die();
		
		
	}

}
