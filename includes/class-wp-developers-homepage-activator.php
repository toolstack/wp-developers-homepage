<?php

/**
 * Fired during plugin activation
 *
 * @link       http://wordpress.org/plugins/wp-developers-homepage
 * @since      1.0.0
 *
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/includes
 * @author     Greg Ross
 */
class WP_Developers_Homepage_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$plugin_admin = WP_Developers_Homepage_Admin::get_instance();

		$plugin_admin->set_wp_cron();
		
	}

}
