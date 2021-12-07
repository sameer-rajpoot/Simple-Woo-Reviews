<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/public
 * @author     ThemesJungle <themesjungle.conatact@gmail.com>
 */
class Simple_Woo_Reviews_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		//Load public dependencies
		$this->swr_load_public_dependencies();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	function swr_public_enqueue_styles() {

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
		wp_enqueue_style('slick-theme', plugin_dir_url( __FILE__ ) . 'css/slick-theme.css', array(), $this->version, 'all' );
		wp_enqueue_style('slick', plugin_dir_url( __FILE__ ) . 'css/slick.css', array(), $this->version, 'all' );
		wp_enqueue_style('jquery-raty', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/jquery.raty.css', array(), $this->version, 'all' );
		wp_enqueue_style('icomoon', plugin_dir_url( __FILE__ ) . 'css/icomoon.css', array(), $this->version, 'all' );
		wp_enqueue_style('fancybox', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/jquery.fancybox.min.css', array(), $this->version );
		wp_enqueue_style('grid-custom', plugin_dir_url( __FILE__ ) . 'css/grid.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-woo-reviews-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	function swr_public_enqueue_scripts() {

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

		$swr_show_gcaptcha = Simple_Woo_Reviews_Admin_Settings::swr_get_option_value('swr_captcha_opt','show_captcha_field_comm_frm');
		if( $swr_show_gcaptcha ==='yes' && !empty($swr_show_gcaptcha) ){
			wp_enqueue_script( 'gcaptcha', 'https://www.google.com/recaptcha/api.js', array(), $this->version,true );
		}
		wp_enqueue_script( 'slick', plugin_dir_url( __FILE__ ) . 'js/slick.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-raty', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/jquery.raty.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'masonry', plugin_dir_url( __FILE__ ) . 'js/masonry.pkgd.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-fancybox', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/jquery.fancybox.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-woo-reviews-public.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( $this->plugin_name, 'swr_params', array('swr_ajax_url' => admin_url( 'admin-ajax.php'), 'swr_nonce' => wp_create_nonce('swr_security_nonce')) );
		
	}

	/**
	 * Load dependencies for the frontend views.
	 *
	 * @since    1.0.0
	 */
	function swr_load_public_dependencies() {
		
		/**
		 * The classes are responsible for loading dependencies for the public view.
		 */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/simple-woo-reviews-wc-account-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/simple-woo-reviews-wc-review-form-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/simple-woo-reviews-shortcodes-manager.php';

	}


}
