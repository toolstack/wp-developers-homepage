<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
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
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.5.0
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/includes
 * @author     Greg Ross
 */
class WP_Developers_Homepage {

	/**
	 * The main plugin file.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      string    $plugin_file    The main plugin file.
	 */
	protected $plugin_file;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      WP_Developers_Homepage_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      string    $slug    The string used to uniquely identify this plugin.
	 */
	protected $slug;

	/**
	 * The display name of this plugin.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      string    $name    The plugin display name.
	 */
	protected $name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The instance of this class.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      WP_Developers_Homepage    $instance    The instance of this class.
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @return    WP_Developers_Homepage    A single instance of this class.
     */
    public static function get_instance( $args = array() ) {

        if ( null == self::$instance ) {
            self::$instance = new self( $args );
        }

        return self::$instance;

    }

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    0.5.0
	 */
	public function __construct( $args ) {

		$this->plugin_file = $args['plugin_file'];

		$this->slug = 'wp-developers-homepage';
		$this->name = __( 'WP Developers Homepage', 'wp-developers-homepage' );
		$this->version = '0.8.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shared_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Developers_Homepage_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Developers_Homepage_i18n. Defines internationalization functionality.
	 * - WP_Developers_Homepage_Admin. Defines all hooks for the dashboard.
	 * - WP_Developers_Homepage_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.5.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-developers-homepage-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-developers-homepage-i18n.php';

		/**
		 * Common functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-developers-homepage-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-developers-homepage-public.php';

		/**
		 * PHP Simple HTML DOM Parser.
		 *
		 * @see  https://github.com/sunra/php-simple-html-dom-parser
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

		$this->loader = new WP_Developers_Homepage_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Developers_Homepage_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.5.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Developers_Homepage_i18n();
		$plugin_i18n->set_domain( $this->slug );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    0.5.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = WP_Developers_Homepage_Admin::get_instance( $this );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add settings page.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'add_settings_fields' );

		$this->loader->add_action( 'wp_ajax_refresh_wdh', $plugin_admin, 'get_ajax_content' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.5.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = WP_Developers_Homepage_Public::get_instance( $this );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to both the admin and public-facing
	 * functionality of the plugin.
	 *
	 * @since    0.5.0
	 * @access   private
	 */
	private function define_shared_hooks() {

		$plugin_shared = $this;

		// Define actions that are shared by both the public and admin.

		// Skip block registration if Gutenberg is not enabled/merged.
		if (!function_exists('register_block_type')) {
			return;
		}

		$dir = dirname(__FILE__);
		$block_js = '../block/block.js';

		wp_register_script(
			'wp-developers-homepage-blocks',
			plugins_url($block_js, __FILE__),
			array(
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-components',
				'wp-block-editor',
				'wp-editor',
			),
			$this->version
		);

		register_block_type('wp-developers-homepage/tickets-block', array(
			'api_version' => 2,
			'editor_script' => 'wp-developers-homepage-blocks',
			'render_callback' => [ $this, 'wp_developers_homepage_tickets_block_handler' ],
			'attributes' => [
			]
		));

		register_block_type('wp-developers-homepage/stats-block', array(
			'api_version' => 2,
			'editor_script' => 'wp-developers-homepage-blocks',
			'render_callback' => [ $this, 'wp_developers_homepage_stats_block_handler' ],
			'attributes' => [
			]
		));

		add_filter( 'block_categories_all', [ $this, 'filter_block_categories_when_post_provided' ], 10, 2 );

	}

	public function filter_block_categories_when_post_provided( $block_categories, $editor_context ) {
    if ( ! empty( $editor_context->post ) ) {
        array_push(
            $block_categories,
            array(
                'slug'  => 'wp-developers-homepage',
                'title' => __( 'WP Developers Homepage', 'custom-plugin' ),
                'icon'  => '',
            )
        );
    }
    return $block_categories;
}


	public function wp_developers_homepage_tickets_block_handler( $atts ) {
		$plugin_admin = WP_Developers_Homepage_Admin::get_instance( $this );

		$content = $plugin_admin->generate_tickets_table();

		// We have to run the update *after* we generate the table, otherwise the date is not yet set.
		$last_update = $plugin_admin->generate_last_data_update() . PHP_EOL;

		// Put everything together now.
		$content  = '<div class="wdh-public-shortcode-container">' . PHP_EOL . $last_update . $content . '<br>' . PHP_EOL . $last_update . '</div>' . PHP_EOL;

        return $content;
	}

	public function wp_developers_homepage_stats_block_handler( $atts ) {
		$plugin_admin = WP_Developers_Homepage_Admin::get_instance( $this );

		$content = $plugin_admin->generate_stats_table();

		// We have to run the update *after* we generate the table, otherwise the date is not yet set.
		$last_update = $plugin_admin->generate_last_data_update() . PHP_EOL;

		// Put everything together now.
		$content  = '<div class="wdh-public-shortcode-container">' . PHP_EOL . $last_update . $content . '<br>' . PHP_EOL . $last_update . '</div>' . PHP_EOL;

        return $content;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.5.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.5.0
	 * @return    WP_Developers_Homepage_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Get any plugin property.
	 *
	 * @since     0.5.0
	 * @return    mixed    The plugin property.
	 */
	public function get( $property = '' ) {
		return $this->$property;
	}

}
