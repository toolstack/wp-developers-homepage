<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wordpress.org/plugins/wp-developers-homepage
 * @since      0.5.0
 *
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/public
 * @author     Greg Ross
 */
class WP_Developers_Homepage_Public {

	/**
	 * The main plugin instance.
	 *
	 * @since    0.5.0
	 * @access   private
	 * @var      WP_Developers_Homepage    $plugin    The main plugin instance.
	 */
	private $plugin;

	/**
	 * The slug of this plugin.
	 *
	 * @since    0.5.0
	 * @access   private
	 * @var      string    $plugin_slug    The slug of this plugin.
	 */
	private $plugin_slug;

	/**
	 * The display name of this plugin.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      string    $plugin_name    The plugin display name.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.5.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The instance of this class.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      WP_Developers_Homepage_Public    $instance    The instance of this class.
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @return    WP_Developers_Homepage_Public    A single instance of this class.
     */
    public static function get_instance( $plugin ) {
 
        if ( null == self::$instance ) {
            self::$instance = new self( $plugin );
        }
 
        return self::$instance;
 
    }

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.5.0
	 * @var      string    $plugin_slug    The name of the plugin.
	 * @var      string    $version        The version of this plugin.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
		$this->plugin_slug = $this->plugin->get( 'slug' );
		$this->plugin_name = $this->plugin->get( 'name' );
		$this->version = $this->plugin->get( 'version' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.5.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Developers_Homepage_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Developers_Homepage_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'css/wp-developers-homepage-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the scripts for the public-facing side of the site.
	 *
	 * @since    0.5.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Developers_Homepage_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Developers_Homepage_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'js/wp-developers-homepage-public.js', array( 'jquery' ), $this->version, false );

	}

}
