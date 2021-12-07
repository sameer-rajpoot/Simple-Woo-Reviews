<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://codecanyon.net/user/themesjungle
 * @since      1.0.0
 *
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Simple_Woo_Reviews
 * @subpackage Simple_Woo_Reviews/includes
 * @author     ThemesJungle <themesjungle.conatact@gmail.com>
 */
class Simple_Woo_Reviews_i18n {

	/**
	 * Load the plugin text domain for translation.
	 * @since    1.0.0
	 */
	function swr_load_plugin_textdomain() {

		load_plugin_textdomain(
			'simple-woo-reviews',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
