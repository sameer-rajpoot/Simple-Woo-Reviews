<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WP to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://codecanyon.net/user/themesjungle
 * @since             1.0.0
 * @package           Simple_Woo_Reviews
 *
 * Plugin Name:       Simple Woo Reviews Lite
 * Description:       Showcase your store product reviews to your customers and increase store conversion. A new way to display customers feedback which is easy to manage and integrate and helps in boosting sales of your store.
 * Version:           1.0.2
 * Author:            ThemesJungle
 * Author URI:        https://codecanyon.net/user/themesjungle
 * Text Domain:       simple-woo-reviews
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version and other plugin metadata.
 * Rename this for plugin and update it as when release new versions.
 */
define( 'SWR_PLUGIN', 'simple-woo-reviews' );
define( 'SWR_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'SWR_VERSION', '1.0.1' );
define( 'SWR_REQUIRED_PHP_VERSION', '7.0' );
define( 'SWR_REQUIRED_WP_VERSION',  '5.0' );
define( 'SWR_REQUIRED_WC_VERSION',  '4.0' );


/**
 * Checks if the system requirements are met
 * @return bool True if system requirements are met, false if not
 */
function swr_requirements_met () {

    global $wp_version ;
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' ) ;  // to get is_plugin_active()

    if ( version_compare ( PHP_VERSION, SWR_REQUIRED_PHP_VERSION, '<' ) ) {
        return false ;
    }

    if ( version_compare ( $wp_version, SWR_REQUIRED_WP_VERSION, '<' ) ) {
        return false ;
    }

    if ( ! is_plugin_active ( 'woocommerce/woocommerce.php' ) ) {
        return false ;
    }

    $woocommerce_data = get_plugin_data(WP_PLUGIN_DIR .'/woocommerce/woocommerce.php', false, false);

    if (version_compare ($woocommerce_data['Version'] , SWR_REQUIRED_WC_VERSION, '<')){
        return false;
    }

    return true;
}

/**
 * Plugin requirements check and deactivation and admin notice
 * @since    1.0.0
 */
function swr_requirements_error () {?>
	<div class="error">
		<p><?php esc_html_e('Requirements for plugin to work are minimum WP version 5.0, minimum PHP version 7.0 and minimum version of WC plugin installed and activated should be atleast 4.0!','simple-woo-reviews'); ?></p>
	</div>
<?php
	// Deactivate the plugin main file if requirements are not met
	deactivate_plugins(plugin_basename(__FILE__));
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

if ( swr_requirements_met() ) {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-woo-reviews.php';

   	if ( class_exists( 'Simple_Woo_Reviews' ) ) {
   		//Include plugin activation and deactivation hooks
		register_activation_hook( __FILE__, 'swr_activate' );
		register_deactivation_hook( __FILE__, 'swr_deactivate' );
		//Begin Simple Reviews execution
		$swr_plugin = new Simple_Woo_Reviews();
		$swr_plugin->run();
   	}
} else {
	// show admin notice inside admin dashboard if requirements are not met
    add_action( 'admin_notices', 'swr_requirements_error' );
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-simple-reviews-deactivator.php
 */
function swr_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-woo-reviews-deactivator.php';
	Simple_Woo_Reviews_Deactivator::deactivate();
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-simple-reviews-activator.php
 */

function swr_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simple-woo-reviews-activator.php';
	Simple_Woo_Reviews_Activator::activate();

}
