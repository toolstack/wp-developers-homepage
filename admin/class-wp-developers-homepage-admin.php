<?php
use simplehtmldom\HtmlWeb;

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://wordpress.org/plugins/wp-developers-homepage
 * @since      0.5.0
 *
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WP_Developers_Homepage
 * @subpackage WP_Developers_Homepage/admin
 * @author     Greg Ross
 */
class WP_Developers_Homepage_Admin {

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
	 * The plugin settings.
	 *
	 * @since    0.5.0
	 * @access   private
	 * @var      string    $options    The plugin settings.
	 */
	private $options;

	/**
	 * Data to pass to JS.
	 *
	 * @since    0.5.0
	 * @access   private
	 * @var      string    $js_data    Data to pass to JS.
	 */
	private $js_data;

	/**
	 * The ID of the settings page screen.
	 *
	 * @since    0.5.0
	 * @access   private
	 * @var      string    $screen_id    The ID of the settings page screen.
	 */
	private $screen_id;

	/**
	 * Fields to fetch via the plugin/theme APIs.
	 *
	 * @since    0.5.0
	 * @access   private
	 * @var      string    $api_fields    Fields to fetch via the plugin/theme APIs
	 */
	private $api_fields = array(
		'active_installs' => true,
		'compatibility'   => false,
		'description'     => false,
		'downloaded'      => true,
		'homepage'        => false,
		'icons'           => false,
		'last_updated'    => false,
		'num_ratings'     => false,
		'ratings'         => false,
	);

	/**
	 * The slug to use to save the wordpress.org data.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      WP_Developers_Homepage_Admin    $instance    The instance of this class.
	 */
	private $data_slug = 'wdh_wordpress_data';

	/**
	 * The time stamp of the last themes ticekts update.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      WP_Developers_Homepage_Admin    $instance    The instance of this class.
	 */
	private $last_data_update = 0;

	/**
	 * The timezone offset for the local display time.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      WP_Developers_Homepage_Admin    $instance    The instance of this class.
	 */
	private $tz_offset = 0;

	/**
	 * An array of slugs that failed to retrieve data from wordpress.org.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      WP_Developers_Homepage_Admin    $instance    The instance of this class.
	 */
	private $error_slugs;

	/**
	 * The github api instance.
	 *
	 * @since    0.9.0
	 * @access   protected
	 * @var      WP_Developers_Homepage_Admin    $githubapi    The github api instance.
	 */
	private $githubapi;

	/**
	 * The github authentication token.
	 *
	 * @since    0.9.0
	 * @access   protected
	 * @var      WP_Developers_Homepage_Admin    $githubtoken    The github authentication token.
	 */
	private $githubtoken;

	/**
	 * The instance of this class.
	 *
	 * @since    0.5.0
	 * @access   protected
	 * @var      WP_Developers_Homepage_Admin    $instance    The instance of this class.
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @return    WP_Developers_Homepage_Admin    A single instance of this class.
     */
    public static function get_instance( $plugin = null ) {

        if ( null == self::$instance ) {
            self::$instance = new self( $plugin );
        }

        return self::$instance;

    }

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.5.0
	 * @var      string    $plugin_slug       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
		$this->plugin_slug = $this->plugin->get( 'slug' );
		$this->plugin_name = $this->plugin->get( 'name' );
		$this->version = $this->plugin->get( 'version' );
		$this->options = (array)get_option( $this->plugin_slug );
		$this->error_slugs = array();
		$this->js_data = array(
			'fetch_messages' => array(
				__( 'Fetching ticket and statistical data...', 'wp-developers-homepage' ),
			),
		);

		if( get_option( 'timezone_string' ) ) {
			$this->tz_offset = timezone_offset_get( timezone_open( get_option( 'timezone_string' ) ), new DateTime() );
		} else if( get_option( 'gmt_offset' ) ) {
			$this->tz_offset = get_option( 'gmt_offset' ) * 60 * 60;
		}

		$this->githubapi = new \Github\Client();
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

	/**
	 * Register the scripts for the admin.
	 *
	 * @since    0.5.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'css/wp-developers-homepage-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-table-sorter-css', plugin_dir_url( __FILE__ ) . 'css/jquery-table-sorter.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the scripts for the admin.
	 *
	 * @since    0.5.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'js/wp-developers-homepage-admin.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-tablesorter', plugin_dir_url( __FILE__ ) . 'js/jquery.tablesorter.min.js', array( 'jquery' ), $this->version, true );

		wp_localize_script( $this->plugin_slug, "wdhSettings", $this->js_data );

	}

	/**
	 * Add settings page.
	 *
	 * @since 0.5.0
	 */
	function add_settings_page() {

		$this->screen_id = add_menu_page(
			$this->plugin_name, // Page title
			esc_html__( 'WP Developers Homepage', 'wp-developers-homepage' ), // Menu title
			'manage_options', // Capability
			$this->plugin_slug, // Page ID
			array( $this, 'do_admin_page' ), // Callback
			'dashicons-hammer' // Icon
		);

		add_options_page(
			$this->plugin_name, // Page title
			esc_html__( 'WP Developers Homepage', 'wp-developers-homepage' ), // Menu title
			'manage_options', // Capability
			$this->plugin_slug . '-admin', // Page ID
			array( $this, 'do_settings_page' ) // Callback
		);
	}

	/**
	 * Output contents of the settings page.
	 *
	 * @since 0.5.0
	 */
	function do_settings_page() {

		if ( empty( $this->options['refresh_timeout'] ) ) { $this->options['refresh_timeout'] = 1; }
		if ( empty( $this->options['age_limit'] ) ) { $this->options['age_limit'] = 0; }

		// Make sure the cron job is set.
		$this->set_wp_cron();

		?>
		<?php screen_icon(); ?>
        <div id="<?php echo "{$this->plugin_slug}-settings"; ?>" class="wrap">
	        <h1><?php echo $this->plugin_name; ?></h1><br />
			<div id="poststuff">
				<form action="options.php" method="post">
					<?php

					// Set up settings fields.
					settings_fields( $this->plugin_slug );

					$this->output_settings_fields();
					submit_button( '', 'primary', '', false );
					?>
				</form>
			</div><!-- #poststuff -->
		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Output contents of the admin page.
	 *
	 * @since 1.4.0
	 */
	function do_admin_page() {

		if ( empty( $this->options['refresh_timeout'] ) ) { $this->options['refresh_timeout'] = 1; }
		if ( empty( $this->options['age_limit'] ) ) { $this->options['age_limit'] = '0'; }

		?>
		<?php screen_icon(); ?>
        <div id="<?php echo "{$this->plugin_slug}-settings"; ?>" class="wrap">
	        <h1><?php echo $this->plugin_name; ?></h1><br />
			<div id="poststuff">
				<form action='options.php' method='post'>
					<?php
					// Do main table output.
					$this->do_ajax_container( 'tickets' );
					?>
				</form>
			</div><!-- #poststuff -->
		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Add settings fields to the settings page.
	 *
	 * @since 0.5.0
	 */
	function add_settings_fields() {

		register_setting(
			$this->plugin_slug, // Option group
			$this->plugin_slug, // Option name
			null // Sanitization
		);

		add_settings_section(
			'main-settings', // Section ID
			null, // Title
			null, // Callback
			$this->plugin_slug // Page
		);

		add_settings_field(
			'username', // ID
			__( 'WordPress.org username', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'username',
				'description' => 'Your WordPress.org username.'
			)
		);

		add_settings_field(
			'githubname', // ID
			__( 'GitHub username', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'githubname',
				'description' => __( 'Your GitHub username, note that the public GitHub API has a ratelimit of 60 requests per hour without authentication.', 'wp-developers-homepage' )
			)
		);

		add_settings_field(
			'githubtoken', // ID
			__( 'GitHub Auth Token', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'githubtoken',
				'description' => __( sprintf( 'Your GitHub Auth Token (optional).  Warning, this will be stored in the database is clear text, it should be a unique token that has no permissions.  Go to %sGitHub tokens page%s to create one.', '<a target="_blank" href="https://github.com/settings/tokens/?type=beta">', '</a>' ), 'wp-developers-homepage' )
			)
		);

		add_settings_field(
			'exclude_plugin_slugs', // ID
			__( 'Exclude plugins', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'exclude_plugin_slugs',
				'description' => __( 'Comma-separated list of slugs to exclude.', 'wp-developers-homepage' ),
			)
		);

		add_settings_field(
			'plugin_slugs', // ID
			__( 'Additional plugins', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'plugin_slugs',
				'description' => __( 'Comma-separated list of slugs for additional plugins to include.  Note: Adding a slug here will override an exclusion above.', 'wp-developers-homepage' ),
			)
		);

		add_settings_field(
			'exclude_theme_slugs', // ID
			__( 'Exclude themes', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'exclude_theme_slugs',
				'description' => __( 'Comma-separated list of slugs to exclude.', 'wp-developers-homepage' ),
			)
		);

		add_settings_field(
			'theme_slugs', // ID
			__( 'Additional themes', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'theme_slugs',
				'description' => __( 'Comma-separated list of slugs for additional themes to include.  Note: Adding a slug here will override an exclusion above.', 'wp-developers-homepage' ),
			)
		);

		add_settings_field(
			'show_all_tickets', // ID
			__( 'Show all tickets', 'wp-developers-homepage' ), // Title
			array( $this, 'render_checkbox' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'show_all_tickets',
				'description' => __( 'Show all tickets, by default only unresolved tickets are shown.', 'wp-developers-homepage' ),
			)
		);

		add_settings_field(
			'retrieve_all_tickets', // ID
			__( 'Retrieve all tickets', 'wp-developers-homepage' ), // Title
			array( $this, 'render_checkbox' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'retrieve_all_tickets',
				'description' => __( 'Retrieve all tickets, by default only active tickets are retrieved from wordpress.org to save on processing/data (data reload required to take effect), however this means the statistics page for resolved/unresolved is incorrect as it reflects only active tickets.  Likewise, if you select "Show all tickets" above, without enabling this option you will only see the active tickets and not all tickets for the plugins/themes.', 'wp-developers-homepage' ),
			)
		);

		add_settings_field(
			'refresh_timeout', // ID
			__( 'Hours before refresh', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'refresh_timeout',
				'description' => __( 'The number of hours before a refresh will be done.  Valid hours are between 1 and 24.  Note: This setting will not take effect until the last data load expires.', 'wp-developers-homepage' ),
			)
		);

		add_settings_field(
			'schedule_updates', // ID
			__( 'Schedule updates', 'wp-developers-homepage' ), // Title
			array( $this, 'render_checkbox' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'schedule_updates',
				'description' => __( 'Create a WP Cron job to update the data when it becomes stale (based on the refresh timeout above).  Note: The update will be executed at the top of the hour and will update data that is out of date or due to expire in the next 30 minutes.', 'wp-developers-homepage' ),
			)
		);

		add_settings_field(
			'age_limit', // ID
			__( 'Age limit', 'wp-developers-homepage' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'age_limit',
				'description' => __( 'Ignore tickets older than this number of days. 0 = unlimited.', 'wp-developers-homepage' ),
				'default' => 0,
			)
		);

	}

	public function output_settings_fields( $hidden = false ) {

		ob_start();
		do_settings_sections( $this->plugin_slug );
		$settings = ob_get_clean();

		if ( $hidden ) {
			$settings = '<div class="hidden">' . $settings .'</div>';
		}

		echo $settings;

	}

	/**
	 * Text input settings field callback.
	 *
	 * @since 0.5.0
	 *
	 * @param array $args Args from add_settings_field().
	 */
	public function render_text_input( $args ) {

		$default = array_key_exists( 'default', $args ) ? $args['default'] : '';
		$option_name = $this->plugin_slug . '[' . $args['id'] . ']';
		$option_value = ! empty( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : $default;
		printf(
            '%s<input type="text" value="%s" id="%s" name="%s" class="regular-text %s"/><br /><p class="description" for="%s">%s</p>',
            ! empty( $args['sub_heading'] ) ? '<b>' . esc_html( $args['sub_heading'] ) . '</b><br />' : '',
            esc_attr( $option_value ),
            esc_attr( $args['id'] ),
            esc_attr( $option_name ),
            ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '',
            $option_name,
            ! empty( $args['description'] ) ? $args['description'] : ''
        );

	}

	/**
	 * Checkbox settings field callback.
	 *
	 * @since 0.5.0
	 *
	 * @param array $args Args from add_settings_field().
	 */
	function render_checkbox( $args ) {

		$option_name = $this->plugin_slug . '[' . $args['id'] . ']';
		$option_value = ! empty( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : '';
		$desc = ! empty( $args['description'] ) ? $args['description'] : '';

		echo '<label class="wpdh-switch">' . PHP_EOL;
		echo '<input type="checkbox" value="1" id="' . esc_attr( $option_name ) . '" name="' . esc_attr( $option_name )  . '" ' . checked( 1, $option_value, false ) . '>' . PHP_EOL;
		echo '<span class="wpdh-slider wpdh-round"></span>' . PHP_EOL;
		echo '</label>' . PHP_EOL;
		echo '<p>' . $desc . '</p>';

	}

	/**
	 * Output refresh button.
	 *
	 * @since 0.5.0
	 */
	public function do_refresh_button() {

		// Set up refresh button atts.
		$refresh_button_atts = array(
			'href'  => '',
		);
		?>
		<div class="wdh-refresh-button-container">
			<?php submit_button( esc_attr__( 'Reload from wordpress.org', 'wp-developers-homepage' ), 'button wdh-button-refresh', '', false, $refresh_button_atts ); ?><span class="spinner"></span>
		</div>
		<?php

	}

	public function do_ajax_container( $object_type = 'tickets', $ticket_type = 'plugins' ) {
		printf( '<div class="wdh-ajax-container" data-wdh-object-type="%s" data-wdh-ticket-type="%s"><div class="wdh-loading-div"><span class="spinner is-active"></span> <span>%s</span></div></div>', $object_type, $ticket_type, $this->js_data['fetch_messages'][ array_rand( $this->js_data['fetch_messages'] ) ] );
	}

	/**
	 * Generate the table for tickets and return it.
	 *
	 * @since 0.5.0
	 *
	 * @param bool   $force_refresh Whether or not to force an uncached refresh.
	 */
	public function generate_tickets_table( $force_refresh = false ) {
		$result = '';

		$result .= "\t\t<table class=\"widefat striped wdh-tickets-table\" id=\"wdh_tickets_table\">" . PHP_EOL;
		$result .= "\t\t\t<thead>" . PHP_EOL;
		$result .= "\t\t\t\t<tr>" . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Status', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Title', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Plugin/Theme', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Type', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Last Post', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Last Poster', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t</tr>" . PHP_EOL;
		$result .= "\t\t\t</thead>" . PHP_EOL;
		$result .= "\t\t<tbody>" . PHP_EOL;

		$plugins_themes = array_merge( $this->get_plugins_themes( 'plugins', $force_refresh ), $this->get_plugins_themes( 'themes', $force_refresh ) );
		$tickets_data = array();
		$plugin_theme_names = array();

		$age_limit = ( empty( $this->options['age_limit'] ) ) ? 0 : (int)$this->options['age_limit'];

		if( $age_limit == 0 ) {
			$age_limit_time = 0;
		} else {
			$age_limit_time = strtotime( '-' . $age_limit . ' days' );
		}

		foreach( $plugins_themes as $plugin_theme ) {
			// Skip if there are no tickets.
			if ( empty ( $plugin_theme['tickets_data'] ) ) {
				continue;
			}

			$plugin_theme_names[$plugin_theme['slug']] = $plugin_theme['name'];
			$tickets_data = array_merge( $tickets_data, $plugin_theme['tickets_data'] );
		}

		uasort( $tickets_data, function( $plugin_1, $plugin_2 ) {
			return $plugin_1['timestamp'] < $plugin_2['timestamp'];
		});

		foreach ( $tickets_data as $ticket_data ) {

			if ( empty ( $this->options['show_all_tickets'] ) ) {
				if ( 'unresolved' != $ticket_data['status'] || true == $ticket_data['closed'] || true == $ticket_data['sticky']) {
					continue;
				}
			}

			if ( $age_limit > 0 ) {
				if ( $ticket_data['timestamp'] < $age_limit_time ) {
					continue;
				}
			}

			// Generate status icons.
			if ( 'resolved' == $ticket_data['status'] ) {
				$icon_class = 'yes';
			} else {
				$icon_class = 'admin-comments';
			}

			$icon_html = sprintf( '<span class="dashicons dashicons-%s" title="%s"></span> ', $icon_class, ucfirst( $ticket_data['status'] ) );

			// Generate closed icon.
			if ( true == $ticket_data['closed'] ) {
				$closed_icon_html = sprintf( '<span class="dashicons dashicons-lock" title="%s"></span> ', __( 'Locked', 'wp-developers-homepage' ) );
			} else {
				$closed_icon_html = '';
			}

			$result .= '<tr>' . PHP_EOL;
			$result .= '<td>' . $icon_html . $closed_icon_html . '</td>' . PHP_EOL;
			$result .= sprintf( '<td><a href="%s" target="_blank">%s</a></td>%s', $ticket_data['href'], $ticket_data['text'], PHP_EOL );
			$result .= sprintf( '<td><a href="%s" target="_blank">%s</a></td>%s', "https://wordpress.org/plugins/" . $ticket_data['slug'], $plugin_theme_names[$ticket_data['slug']], PHP_EOL );
			$result .= '<td>' . $plugin_theme['type'] . '</td>' . PHP_EOL;
			$result .= '<td>' . date( 'M d, Y g:m a', $ticket_data['timestamp'] + $this->tz_offset ) . '</td>' . PHP_EOL;
			$result .= sprintf( '<td><a href="%s" target="_blank">%s</a></td>%s', $ticket_data['lastposterhref'], $ticket_data['lastposter'], PHP_EOL );
		}

		$result .= "\t\t\t</tbody>" . PHP_EOL;
		$result .= "\t\t</table>" . PHP_EOL;

		return $result;
	}

	/**
	 * Helper function to update ticket/stats content via Ajax.
	 *
	 * @since 0.5.0
	 */
	public function get_ajax_content() {

		// Get parameters to load correct content.
		$force_refresh = isset( $_POST['force_refresh'] ) ? $_POST['force_refresh'] : false;
		$current_url = isset( $_POST['current_url'] ) ? $_POST['current_url'] : false;

		$tickets_table = $this->generate_tickets_table( $force_refresh );
		$stats_table = $this->generate_stats_table( $force_refresh );

		if ( count( $this->error_slugs ) > 0 ) {
			printf( '<div class="error"><p>%s %s</p></div>', __( 'WP Developers Homepage Error: The following items could not be retrieved from wordpress.org;', 'wp-developers-homepage' ), implode( ', ', $this->error_slugs ) );
		}

		// Output refresh button.
		$this->do_refresh_button();

		?>
		<div class="wdh-sub-tab-nav nav-tab-wrapper">
        	<a href="#" class="button button-primary" data-wdh-tab-target="tickets"><span class="dashicons dashicons-editor-help"></span> <?php echo __( 'Tickets', 'wp-developers-homepage '); ?></a>
        	<a href="#" class="button" data-wdh-tab-target="info"><span class="dashicons dashicons-list-view" data-wdh-tab-target="info"></span> <?php echo __( 'Statistics', 'wp-developers-homepage '); ?></a>
        </div>
        <div class="wdh-sub-tab-container">
        	<div class="wdh-sub-tab wdh-sub-tab-tickets active"><?php echo $tickets_table; ?></div>
        	<div class="wdh-sub-tab wdh-sub-tab-info"><?php echo $stats_table; ?></div>
        </div>
        <?php

        // Output refresh button.
		$this->do_refresh_button();

		echo $this->generate_last_data_update();

		wp_die(); // this is required to terminate immediately and return a proper response

	}

	/**
	 * Helper function to display the last date/time the data was updated.
	 *
	 * @since 0.9.0
	 */
	public function generate_last_data_update() {

		$content  = '<p>';
		$content .= __( 'Data last loaded from wordpress.org on: ' );
		$content .= date( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $this->last_data_update + $this->tz_offset );
		$content .= '</p>';

		return $content;
	}

	/**
	 * Get all plugin or theme data based on the plugin settings.
	 *
	 * @since 0.5.0
	 *
	 * @param string $ticket_type   Plugins or themes.
	 * @param bool   $force_refresh Whether or not to force cache-busting refresh.
	 * @param bool   $quick         Don't refresh the data if it doesn't exist and return quickly.
	 *
	 * @return array Array of all plugin|theme data.
	 */
	public function get_plugins_themes( $ticket_type = 'plugins', $force_refresh = false, $quick = false ) {

		// Get username to pull plugin data.
		$username = ! empty( $this->options['username'] ) ? $this->options['username'] : '';

		// Get githubname to pull git tickets.
		$githubname = ! empty( $this->options['githubname'] ) ? $this->options['githubname'] : '';

		$data = get_option( $this->data_slug, false );

		if ( false === $data ) {
			$data = array();
			$data['plugins'] = array();
			$data['themes'] = array();
			$data['plugins_timestamp'] = 0;
			$data['themes_timestamp'] = 0;
		} else {
			$this->last_data_update = $data['plugins_timestamp'] > $data['themes_timestamp'] ? $data['plugins_timestamp'] : $data['themes_timestamp'];
		}

		$plugins_themes = $data[ $ticket_type ];

		if( true === $quick ) {
			return $plugins_themes;
		}

		// Make sure the cron job is set.
		$this->set_wp_cron();

		// Get the number of hours we should keep the transient for.
		$timeout = (int)$this->options['refresh_timeout'];

		// Do some sanity checking on the timeout value.
		if ( $timeout < 1 || $timeout > 24 ) { $timeout = 1; }

		// Calculate the expiry time of the current data.
		$expiry_time = $data[ $ticket_type . '_timestamp' ];
		$expiry_time = $expiry_time + ( $timeout * 60 * 60 );

		if ( $force_refresh || time() > $expiry_time ) {

			$plugins_themes = $this->get_tickets_data( $username, $githubname, $ticket_type );

			foreach ( $this->error_slugs as $slug ) {
				if ( array_key_exists( $slug, $data[ $ticket_type ] ) ) {
					$plugins_themes[ $slug ] = $data[ $ticket_type ][ $slug ];
				}
			}

			if ( $plugins_themes ) {

				/**
				 * Filter transient expiration time.
				 *
				 * @since 0.5.0
				 *
				 * @param $expiration Expiration in seconds (default 3600 - one hour).
				 */
				$transient_expiration = apply_filters( 'wdh_transient_expiration', $timeout * HOUR_IN_SECONDS );

				$data[ $ticket_type ] = $plugins_themes;
				$data[ $ticket_type . '_timestamp'] = time();
				update_option( $this->data_slug, $data );
				$this->last_data_update = $data[ $ticket_type . '_timestamp'];
			}

		}

		return $plugins_themes;

	}

	/**
	 * Output a list table of plugins/themes stats.
	 *
	 * @since 0.5.0
	 */
	public function generate_stats_table( $force_refresh = false ) {
		$result = '';

		$result .= "\t\t<table class=\"widefat striped wdh-stats-table\" id=\"wdh_stats_table\">" . PHP_EOL;
		$result .= "\t\t\t<thead>" . PHP_EOL;
		$result .= "\t\t\t\t<tr>" . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Title', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Type', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Version', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'WP Version Tested', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Rating', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( '# of Reviews', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Active Installs', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Downloads', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Unresolved', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Resolved', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t</tr>" . PHP_EOL;
		$result .= "\t\t\t</thead>" . PHP_EOL;
		$result .= "\t\t<tbody>" . PHP_EOL;

		$plugins_themes = array_merge( $this->get_plugins_themes( 'plugins', $force_refresh ), $this->get_plugins_themes( 'themes', $force_refresh ) );

		$update_data = get_site_transient( 'update_core' );
		$wp_branches = $update_data->updates;

		$wp_version = '';
		foreach( $wp_branches as $index => $branch ) {
			if ( 'latest' == $branch->response ) {
				$wp_version = $wp_branches[ $index ]->version;
			}
		}

		$rating_sum = 0;
		$installs = 0;
		$downloads = 0;
		$unresolved = 0;
		$resolved = 0;
		$rating_count = 0;

		foreach( $plugins_themes as $plugin_theme ) {
			$result .= '<tr>' . PHP_EOL;

			if( $this->options['githubname'] ) {
				$result .= sprintf( '<td><b><a href="%s" target="_blank">%s</a></b> <a href="%s" target="_blank">(GitHub)</a>', 'https://wordpress.org/plugins/' . $plugin_theme['slug'], $plugin_theme['name'], 'https://github.com/' . $this->options['githubname'] . '/' . $plugin_theme['slug'] );
			} else {
				$result .= sprintf( '<td><b><a href="%s" target="_blank">%s</a><b>', 'https://wordpress.org/plugins/' . $plugin_theme['slug'], $plugin_theme['name'] );

			}
			$result .= "<td>$plugin_theme[type]</td>" . PHP_EOL;
			$result .= "<td>$plugin_theme[version]</td>" . PHP_EOL;

			$class = '';

			if ( $wp_version ) {
				if ( version_compare( $plugin_theme['tested'], $wp_version ) >= 0 && 'Plugin' == $plugin_theme['type'] ) {
					$class = 'wdh-current';
				} else {
					$class = 'wdh-needs-update';
				}
			}

			$rating = intval( $plugin_theme['rating'] );
			$rating_sum += $rating;
			if( $rating > 0 ) { $rating_count++; }
			$installs += $plugin_theme['active_installs'];
			$downloads += $plugin_theme['downloaded'];
			$unresolved += $plugin_theme['unresolved_count'];
			$resolved += $plugin_theme['resolved_count'];

			$result .= sprintf( '<td><span class="%s">%s</span></td>' . PHP_EOL, $class, ( 'Plugin' == $plugin_theme['type'] ? $plugin_theme['tested'] : __( 'N/A', 'wp-developers-homepage' ) ) );
			$result .= '<td>' . ( $plugin_theme['rating'] ? $plugin_theme['rating'] : __( 'N/A', 'wp-developers-homepage' ) ) . '</td>' . PHP_EOL;
			$result .= "<td>{$plugin_theme['num_ratings']}</td>" . PHP_EOL;
			$result .= '<td>' . number_format_i18n( $plugin_theme['active_installs'] ) . '</td>' . PHP_EOL;
			$result .= '<td>' . number_format_i18n( $plugin_theme['downloaded'] ) . '</td>' . PHP_EOL;
			$result .= '<td>' . number_format_i18n( $plugin_theme['unresolved_count'] ) . '</td>' . PHP_EOL;
			$result .= '<td>' . number_format_i18n( $plugin_theme['resolved_count'] ) . '</td>' . PHP_EOL;
			$result .= '</tr>' . PHP_EOL;
		}

		$result .= "\t\t\t</tbody>" . PHP_EOL;

		// Make sure we don't divide by zero...
		if( $rating_count < 1 ) { $rating_count = 1; }

		$result .= "\t\t\t<tfoot>" . PHP_EOL;
		$result .= "\t\t\t\t<tr>" . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . __( 'Totals/Averages', 'wp-developers-homepage' ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td></td>" . PHP_EOL;
		$result .= "\t\t\t\t\t<td></td>" . PHP_EOL;
		$result .= "\t\t\t\t\t<td></td>" . PHP_EOL;
		$result .= "\t\t\t\t\t<td></td>" . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . round( $rating_sum / $rating_count ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . number_format_i18n( $installs ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . number_format_i18n( $downloads ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . number_format_i18n( $unresolved ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t\t<td>" . number_format_i18n( $resolved ) . '</td>' . PHP_EOL;
		$result .= "\t\t\t\t</tr>" . PHP_EOL;
		$result .= "\t\t\t</tfoot>" . PHP_EOL;
		$result .= "\t\t<tbody>" . PHP_EOL;

		$result .= "\t\t</table>" . PHP_EOL;

		return $result;
	}

	/**
	 * Get tickets data for a specific user's plugin or themes.
	 *
	 * @since 0.5.0
	 *
	 * @param string $username    WordPress.org username.
	 * @param string $ticket_type Type of ticket to query for.
	 *
	 * @return array $plugins_themes Array of plugins|themes and associated info.
	 */
	public function get_tickets_data( $username, $githubname, $ticket_type = 'plugins' ) {

		// Get tickets by user.
		$plugins_themes_by_user = $this->get_plugins_themes_by_user( $username, $ticket_type );

		// Get any plugins/themes that are manually set via the plugin settings.
		$plugins_themes_from_setting = $this->get_plugins_themes_from_settings( $ticket_type );

		// Merge plugins/themes for 1. user and 2. manually set in settings.
		$plugins_themes = array_merge( $plugins_themes_by_user, $plugins_themes_from_setting );

		// Loop through all plugins/themes.
		foreach ( $plugins_themes as $index => $plugins_theme ) {

			$plugins_themes[ $index ]['type'] = ( 'plugins' == $ticket_type ) ? 'Plugin' : 'Theme';

			// Initialize ticket count to zero in case we have to return early.
			$plugins_themes[ $index ]['unresolved_count'] = 0;
			$plugins_themes[ $index ]['resolved_count'] = 0;

			$tickets_data = $this->get_unresolved_tickets( $plugins_theme['slug'], $githubname, $ticket_type );

			if ( ! $tickets_data ) {
				continue;
			}

			$plugins_themes[ $index ]['tickets_data'] = $tickets_data;

			// Add ticket counts.
			foreach ( $tickets_data as $ticket_data ) {

				if ( 'unresolved' == $ticket_data['status'] ) {
					$plugins_themes[ $index ]['unresolved_count']++;
				} else {
					$plugins_themes[ $index ]['resolved_count']++;
				}

			}

		}

		return $plugins_themes;

	}

	/**
	 * Generate HTML for output for a plugin's/theme's tickets.
	 *
	 * @since 0.5.0
	 *
	 * @param array $tickets_data Array of tickets data.
	 *
	 * @return string $html HTML output.
	 */
	public function get_tickets_html( $tickets_data ) {

		$html = '<ul>';

		// Get output for all tickets for this plugin.
		$i = 0;
		foreach ( $tickets_data as $ticket_data ) {

			// Generate status icons.
			if ( 'resolved' == $ticket_data['status'] ) {
				$icon_class = 'yes';
			} else {
				$icon_class = 'editor-help';
			}

			$icon_html = sprintf( '<span class="dashicons dashicons-%s" title="%s"></span> ', $icon_class, ucfirst( $ticket_data['status'] ) );

			$ticket_output = sprintf( '<li class="%s">%s<a href="%s" target="_blank">%s</a> (%s)</li>',
				'wdh-' . $ticket_data['status'],
				$icon_html,
				$ticket_data['href'],
				$ticket_data['text'],
				$ticket_data['time']
			);

			/**
			 * Filter ticket output.
			 *
			 * @param string $ticket_output The <li> ticket output.
			 * @param array $ticket_data The ticket data array.
			 */
			$html .= apply_filters( 'wdh_ticket_output', $ticket_output, $ticket_data );

			$i++;

		}

		$html .= '</ul>';

		return $html;

	}

	/**
	 * Get data for a specific plugin/theme.
	 *
	 * @since 0.5.0
	 *
	 * @param string $username    WordPress.org username.
	 * @param string $ticket_type Type of ticket to check for.
	 *
	 * @return stdClass Object Plugin/theme object.
	 */
	public function get_plugins_themes_by_user( $username, $ticket_type = 'plugins' ) {

		// Return empty array if no username is specified.
		if ( ! $username ) {
			return array();
		}

		$exclude_slugs = array();

		// Require file that includes plugin API functions.
		if ( 'plugins' == $ticket_type ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$query_function = 'plugins_api';
			$query_action = 'query_plugins';

			if ( ! empty( $this->options['exclude_plugin_slugs'] ) ) {
				$exclude_slugs = explode( ',', $this->options['exclude_plugin_slugs'] );
			}
		} else {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			$query_function = 'themes_api';
			$query_action = 'query_themes';

			if ( ! empty( $this->options['exclude_theme_slugs'] ) ) {
				$exclude_slugs = explode( ',', $this->options['exclude_theme_slugs'] );
			}
		}

		// Trim all the elements in the exclusion list.
		$exclude_slugs = array_map( 'trim', $exclude_slugs );

		// Lowercase all the elements in the exclusion list.
		$exclude_slugs = array_map( 'strtolower', $exclude_slugs );

		$args = array(
			'author' => $this->options['username'],
			'fields' => $this->api_fields,
			'per_page' => 1000,
		);

		$data = call_user_func( $query_function, $query_action, $args );

		$plugins_themes_by_user = array();

		if ( $data && ! is_wp_error( $data ) ) {
			$slug_data = ( 'plugins' == $ticket_type ) ? $data->plugins : $data->themes;

			// Use the slug as the array index to make it easier later and handle the exclusions.
			foreach ( $slug_data as $item ) {
				if ( in_array( $item['slug'], $exclude_slugs ) ) {
					continue;
				}

				$plugins_themes_by_user[ $item['slug'] ] = $item;
			}
		}

		return $plugins_themes_by_user;

	}

	public function get_plugin_theme_data_by_slug( $slug, $ticket_type = 'plugins' ) {

		// Require file that includes plugin API functions.
		if ( 'plugins' == $ticket_type ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$query_function = 'plugins_api';
			$query_action = 'plugin_information';
		} else {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			$query_function = 'themes_api';
			$query_action = 'theme_information';
		}

		$args = array(
			'slug' => $slug,
			'fields' => $this->api_fields,
		);

		$data = call_user_func( $query_function, $query_action, $args );

		return $data;

	}

	/**
	 * Get array of plugin/theme slugs manually specified in the plugin settings.
	 *
	 * @since 0.5.0
	 *
	 * @param string $ticket_type Type of ticket to fetch.
	 *
	 * @return array Array of plugin/theme slugs.
	 */
	public function get_plugins_themes_from_settings( $ticket_type = 'plugins' ) {

		// Get manually added plugin/theme slugs from settings.
		if ( 'plugins' == $ticket_type ) {
			$plugins_themes_string = ( ! empty( $this->options['plugin_slugs'] ) ) ? $this->options['plugin_slugs'] : '';
		} else {
			$plugins_themes_string = ( ! empty( $this->options['theme_slugs'] ) ) ? $this->options['theme_slugs'] : '';
		}

		// Remove whitespace from string.
		$plugins_themes_string = str_replace( ' ', '', $plugins_themes_string );

		// Return empty array if there is no settings data to parse.
		if ( empty( $plugins_themes_string ) ) {
			return array();
		}

		// Convert to array from comma-separated list.
		$plugins_themes_array = explode( ',', $plugins_themes_string );

		// Create array of objects to match that returned by the plugin/theme API.
		$plugins_themes = array();
		foreach ( $plugins_themes_array as $plugin_theme_slug ) {
			$plugin_theme_data = $this->get_plugin_theme_data_by_slug( $plugin_theme_slug, $ticket_type );

			if ( $plugin_theme_data && ! is_wp_error( $plugin_theme_data ) ) {
				$plugins_themes[] = $this->get_plugin_theme_data_by_slug( $plugin_theme_slug, $ticket_type );
			}

		}

		return $plugins_themes;
	}

	/**
	 * Get unresolved ticket data for a specific plugin/theme.
	 *
	 * @since 0.5.0
	 *
	 * @param string $plugin_theme_slug Plugin/theme slug.
	 * @param string $ticket_type       plugins|themes
	 *
	 * @return array Array of all unresolved ticket data.
	 */
	public function get_unresolved_tickets( $plugin_theme_slug, $githubname, $ticket_type = 'plugins' ) {
		$rows_data = array();

		$i = 1;
		while (	$new_rows_data = $this->get_unresolved_tickets_for_page( $plugin_theme_slug, $ticket_type, $i ) ) {
			$rows_data = array_merge( $rows_data, $new_rows_data );
			$i++;
		}

		$gitissues = $this->get_unresolved_tickets_from_github( $plugin_theme_slug, $githubname, $ticket_type );

		return $rows_data;

	}

	/**
	 * Get unresolved ticket for a specific page of a plugin/theme support forum.
	 *
	 * @since 0.5.0
	 *
	 * @param string $plugin_theme_slug Plugin/theme slug.
	 * @param string $ticket_type       plugins|themes
	 * @param string $page_num          Support forum page to query.
	 *
	 * @return array Array of ticket data.
	 */
	public function get_unresolved_tickets_for_page( $plugin_theme_slug, $ticket_type = 'plugins', $page_num ) {
		$age_limit = (int)$this->options['age_limit'];

		if( $age_limit == 0 ) {
			$age_limit_time = 0;
		} else {
			$age_limit_time = strtotime( '-' . $age_limit . ' days' );
		}

		$html = $this->get_page_link( $plugin_theme_slug, $page_num, $ticket_type );

		if( $html === false ) {
			$this->error_slugs[ $plugin_theme_slug ] = $plugin_theme_slug;

			return false;
		}

		$client = new HtmlWeb();
		$dom = $client->load( $html );

		$table = $dom->find( 'li.bbp-body', 0 );

		if( is_null( $table ) ) {
			return false;
		}

		// Returns the page title
		$rows = $table->find( 'ul.topic', 0 );
		$rows_data = array();

		foreach ( $table->find('ul.topic') as $row ) {

			if( is_null( $row ) ) { continue; }

			// Get row attributes.
			$title         = $row->find( 'li.bbp-topic-title', 0 );
			$freshness     = $row->find( 'li.bbp-topic-freshness', 0 );
			$link          = $title->find( 'a', 0 );
			$time          = $freshness->find( 'a', 0 );
			$startby       = $title->find( 'a.bbp-author-name', 0 );
			$lastposter    = $freshness->find( 'a.bbp-author-link', 0 );
			$resolved_span = $row->find( 'span.resolved', 0 );

			$row_data['href']           = ( ! is_null( $link ) ? $link->href : '' );
			$row_data['text']           = ( ! is_null( $link ) ? $link->innertext : '' );
			$row_data['time']           = ( ! is_null( $time ) ? $time->innertext : '' );
			$row_data['timestamp']      = strtotime( $row_data['time'] );
			$row_data['status']         = ( $resolved_span !== null ) ? 'resolved' : 'unresolved';
			$row_data['sticky']         = ( strpos( $row->class, 'sticky') !== false ) ? true : false;
			$row_data['closed']         = ( strpos( $row->class, 'status-closed') !== false ) ? true : false;
			$row_data['startedby']      = ( ! is_null( $startby ) ? $startby->innertext : '' );
			$row_data['startedbyhref']  = ( ! is_null( $startby ) ? $startby->href : '' );
			$row_data['lastposter']     = ( ! is_null( $lastposter ) ? $lastposter->innertext : '' );
			$row_data['lastposterhref'] = ( ! is_null( $lastposter ) ? $lastposter->href : '' );
			$row_data['type']           = ( 'plugins' == $ticket_type ) ? 'Plugin' : 'Theme';
			$row_data['slug']           = $plugin_theme_slug;

			// Discard any tickets older than our desired age limit.
			if( $row_data['timestamp'] >= $age_limit_time ) {
				$rows_data[] = $row_data;
			}
		}

		// If the first issue we retrieved is older than our age limit, we're done.
		if( $rows_data[0]['timestamp'] < $age_limit_time ) {
			return false;
		}

		return $rows_data;

	}

	public function get_page_link( $plugin_theme_slug, $page_num, $ticket_type ) {

		if ( ! $page_num ) {
			return false;
		}

		if( array_key_exists( 'retrieve_all_tickets', $this->options ) && $this->options['retrieve_all_tickets'] ) { $active = ''; } else { $active = 'active/'; }
		if( 'plugins' == $ticket_type ) { $type = 'plugin'; } else { $type = 'theme'; }

		return "https://wordpress.org/support/{$type}/{$plugin_theme_slug}/{$active}page/{$page_num}";

	}

	public function get_page_html( $plugin_theme_slug, $page_num, $ticket_type ) {

		if ( ! $page_num ) {
			return false;
		}

		$remote_url = $this->get_page_link( $plugin_theme_slug, $page_num, $ticket_type );

		$response = wp_remote_get( $remote_url );

		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {

			// Decode the API data and grab the versions info.
			$response = wp_remote_retrieve_body( $response );

		} else {
			return new WP_Error( 'error', sprintf( __( 'Attempt to fetch support forums HTML failed (%s)', 'wp-developers-homepage' ), $plugin_theme_slug ), $plugin_theme_slug );
		}

		return $response;

	}

	public function get_unresolved_tickets_from_github( $plugin_theme_slug, $githubname, $ticket_type ) {
		$age_limit = (int)$this->options['age_limit'];

		if( $age_limit == 0 ) {
			$age_limit_time = 0;
		} else {
			$age_limit_time = strtotime( '-' . $age_limit . ' days' );
		}

		// If there is no github user set, just return.
		if( ! array_key_exists( 'githubname', $this->options ) || ( array_key_exists( 'githubname', $this->options ) && $this->options['githubname'] == '' ) ) {
			return array();
		}

		// If we have a GitHub Auth Token, use it now.
		if( array_key_exists( 'githubtoken', $this->options ) && $this->options['githubtoken'] != '' ) {
			$this->githubapi->authenticate( $this->options['githubtoken'], '', Github\AuthMethod::ACCESS_TOKEN );
		}

		$page = 1;

		try {
			$issues = $this->githubapi->api('issue')->all( $githubname, $plugin_theme_slug, array('state' => 'all', 'per_page' => 100, 'page' => $page ) );
		} catch (Exception $ex) {
			return array();
		}

		$rows_data = array();

		while( is_array( $issues ) && count( $issues ) > 0 ) {
			$page++;

			foreach ( $issues as $row ) {

				// Issues from GitHub include pull requests as well, we don't want them so skip this
				// row if the pull_request key exists.
				if( array_key_exists( 'pull_request', $row) ) { continue; }

				$row_data['href']           = $row['url'];
				$row_data['text']           = $row['title'];
				$row_data['time']           = $row['updated_at'];
				$row_data['timestamp']      = strtotime( $row['updated_at'] );
				$row_data['status']         = $row['state'];
				$row_data['sticky']         = false;
				$row_data['closed']         = ( $row['state'] == 'closed' ) ? true : false;
				$row_data['startedby']      = $row['user']['login'];
				$row_data['startedbyhref']  = 'https://github.com/' . $row['user']['login'];
				$row_data['lastposter']     = 'Unknown';
				$row_data['lastposterhref'] = '';
				$row_data['type']           = ( 'plugins' == $ticket_type ) ? 'Plugin' : 'Theme';
				$row_data['slug']           = $plugin_theme_slug;

				// Discard any tickets older than our desired age limit.
				if( $row_data['timestamp'] >= $age_limit_time ) {
					$rows_data[] = $row_data;
				}
			}

			// If the last issue we retrieved is still newer than our age limit, keep going, otherwise we're done.
			if( $row_data['timestamp'] > $age_limit_time ) {
				try {
					$issues = $this->githubapi->api('issue')->all( $githubname, $plugin_theme_slug, array('state' => 'all', 'per_page' => 100, 'page' => $page ) );
				} catch (Exception $ex) {
					return $rows_data;
				}
			} else  {
				$issues = array();
			}
		}

		return $rows_data;
	}

	public function set_wp_cron() {
		$timestamp = wp_next_scheduled( 'wdh_run_wp_cron' );
		$update = array_key_exists( 'schedule_updates', $this->options ) ? $this->options['schedule_updates'] : false;

		if ( ! $timestamp && $update ) {
			$starthour = date( 'H' ) + 1;
			$starttime = strtotime( "{$starthour}:00 today" );

			wp_schedule_event( $starttime, 'hourly', 'wdh_run_wp_cron' );
		}
	}

	public function clear_wp_cron() {
		wp_clear_scheduled_hook( 'wdh_run_wp_cron' );
	}

	public function run_wp_cron() {
		$data = get_option( $this->data_slug, false );

		// Get the number of hours we should keep the data for.
		$timeout = (int)$this->options['refresh_timeout'];

		// Do some sanity checking on the timeout value.
		if ( $timeout < 1 || $timeout > 24 ) { $timeout = 1; }

		// Calculate the expiry time of the current data.
		$expiry_time = $timeout * 60 * 60;

		// The expiry time is the timestamp + # hours - 30 minutes (1800)
		// The minus thrity minutes ensures that the longest wait period is 30 minutes for an update to happen.
		$plugins_expiry_time = $data['plugins_timestamp'] + $expiry_time - 1800;
		$themes_expiry_time = $data['themes_timestamp'] + $expiry_time - 1800;

		if ( time() > $plugins_expiry_time ) {
			get_plugins_themes( 'plugins', true, false );
		}

		if ( time() > $themes_expiry_time ) {
			get_plugins_themes( 'themes', true, false );
		}
	}
}
