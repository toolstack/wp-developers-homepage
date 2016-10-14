<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wordpress.org/plugins/wp-developers-homepage
 * @since             1.0.0
 * @package           WP_Developers_Homepage
 *
 * @wordpress-plugin
 * Plugin Name:       WP Developers Homepage
 * Plugin URI:        http://wordpress.org/plugins/wp-developers-homepage
 * Description:       Easily see all of your unresolved plugin & theme support requests.
 * Version:           1.0.0
 * Author:            Greg Ross
 * Author URI:        http://toolstackc.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-developers-homepage
 * Domain Path:       /languages
 */

/**
 * TO DO
 *
 * Integrate github issues.
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-developers-homepage-activator.php
 */
function activate_wp_developers_homepage() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-developers-homepage-activator.php';
	
	WP_Developers_Homepage_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-developers-homepage-deactivator.php
 */
function deactivate_wp_developers_homepage() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-developers-homepage-deactivator.php';
	
	WP_Developers_Homepage_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_developers_homepage' );
register_deactivation_hook( __FILE__, 'deactivate_wp_developers_homepage' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-developers-homepage.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_developers_homepage() {

	// Pass main plugin file through to plugin class for later use.
	$args = array(
		'plugin_file' => __FILE__,
	);

	$plugin = WP_Developers_Homepage::get_instance( $args );
	$plugin->run();

}
run_wp_developers_homepage();
