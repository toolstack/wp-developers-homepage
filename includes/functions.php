<?php

/**
 * The file that defines the core functions
 *
 * Function definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://wordpress.org/plugins/wp-developers-homepage
 * @since      0.5.0
 *
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define functions that are required for the plugin.
 *
 * @since      0.5.0
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/includes
 * @author     Greg Ross
 */

function wdh_run_wp_cron() {

	$plugin_admin = WP_Developers_Homepage_Admin::get_instance( $this );

	$plugin_admin->run_wp_cron();
		
}
