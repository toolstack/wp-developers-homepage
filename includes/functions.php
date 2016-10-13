<?php

/**
 * The file that defines the core functions
 *
 * Function definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://wordpress.org/plugins/wp-dev-dashboard
 * @since      2.0.0
 *
 * @package    WP_Dev_Dashboard
 * @subpackage WP_Dev_Dashboard/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define functions that are required for the plugin.
 *
 * @since      2.0.0
 * @package    WP_Dev_Dashboard
 * @subpackage WP_Dev_Dashboard/includes
 * @author     Greg Ross greg@toolstack.com
 */

function wdd_run_wp_cron() {

	$plugin_admin = WP_Dev_Dashboard_Admin::get_instance( $this );

	$plugin_admin->run_wp_cron();
		
}
