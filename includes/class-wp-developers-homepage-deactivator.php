<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://wordpress.org/plugins/wp-developers-homepage
 * @since      1.0.0
 *
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/includes
 * @author     Greg Ross
 */
class WP_Developers_Homepage_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

	$plugin_admin = WP_Developers_Homepage_Admin::get_instance( $this );

		$plugin_admin->clear_wp_cron();

	}

}
