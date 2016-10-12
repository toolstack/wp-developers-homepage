<?php
use Sunra\PhpSimple\HtmlDomParser;

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://wordpress.org/plugins/wp-dev-dashboard
 * @since      1.0.0
 *
 * @package    WP_Dev_Dashboard
 * @subpackage WP_Dev_Dashboard/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WP_Dev_Dashboard
 * @subpackage WP_Dev_Dashboard/admin
 * @author     Mickey Kay Creative mickey@mickeykaycreative.com
 */
class WP_Dev_Dashboard_Admin {

	/**
	 * The main plugin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      WP_Dev_Dashboard    $plugin    The main plugin instance.
	 */
	private $plugin;

	/**
	 * The slug of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The slug of this plugin.
	 */
	private $plugin_slug;

	/**
	 * The display name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The plugin display name.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The plugin settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $options    The plugin settings.
	 */
	private $options;

	/**
	 * Data to pass to JS.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $js_data    Data to pass to JS.
	 */
	private $js_data;

	/**
	 * The ID of the settings page screen.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $screen_id    The ID of the settings page screen.
	 */
	private $screen_id;

	/**
	 * Fields to fetch via the plugin/theme APIs.
	 *
	 * @since    1.2.0
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
	 * The instance of this class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Dev_Dashboard_Admin    $instance    The instance of this class.
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @return    WP_Dev_Dashboard_Admin    A single instance of this class.
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
	 * @since    1.0.0
	 * @var      string    $plugin_slug       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
		$this->plugin_slug = $this->plugin->get( 'slug' );
		$this->plugin_name = $this->plugin->get( 'name' );
		$this->version = $this->plugin->get( 'version' );
		$this->options = get_option( $this->plugin_slug );
		$this->js_data = array(
			'fetch_messages' => array(
				__( 'Fetching data, thanks for your patience. . .', 'wp-dev-dashboard' ),
				__( 'Fetching data, this can take a bit. . .', 'wp-dev-dashboard' ),
				__( 'Fetching data, patience is a virtue. . .', 'wp-dev-dashboard' ),
				__( 'Fetching data, 3. . . 2. . . 1. . .', 'wp-dev-dashboard' ),
			),
		);

	}

	/**
	 * Get any plugin property.
	 *
	 * @since     1.0.0
	 * @return    mixed    The plugin property.
	 */
	public function get( $property = '' ) {
		return $this->$property;
	}

	/**
	 * Register the scripts for the admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'css/wp-dev-dashboard-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-table-sorter-css', plugin_dir_url( __FILE__ ) . 'css/jquery-table-sorter.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the scripts for the admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'js/wp-dev-dashboard-admin.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-tablesorter-js', plugin_dir_url( __FILE__ ) . 'js/jquery.tablesorter.min.js', array( 'jquery' ), $this->version, true );

		wp_localize_script( $this->plugin_slug, "wpddSettings", $this->js_data );

	}

	/**
	 * Add settings page.
	 *
	 * @since 1.0.0
	 */
	function add_settings_page() {

		$this->screen_id = add_menu_page(
			$this->plugin_name, // Page title
			esc_html__( 'Dev Dashboard', 'wp-dev-dashboard' ), // Menu title
			'manage_options', // Capability
			$this->plugin_slug, // Page ID
			array( $this, 'do_admin_page' ), // Callback
			'dashicons-hammer' // Icon
		);

		add_options_page(
			$this->plugin_name, // Page title
			esc_html__( 'Dev Dashboard', 'wp-dev-dashboard' ), // Menu title
			'manage_options', // Capability
			$this->plugin_slug . '-admin', // Page ID
			array( $this, 'do_settings_page' ) // Callback
		);
	}

	/**
	 * Output contents of the settings page.
	 *
	 * @since 1.0.0
	 */
	function do_settings_page() {

		if ( empty( $this->options['refresh_timeout'] ) ) { $this->options['refresh_timeout'] = 1; }
		if ( empty( $this->options['age_limit'] ) ) { $this->options['age_limit'] = 0; }

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
	 * @since 1.0.0
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
			__( 'WordPress.org username', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'username',
			)
		);

		add_settings_field(
			'exclude_plugin_slugs', // ID
			__( 'Exclude plugins', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'exclude_plugin_slugs',
				'description' => __( 'Comma-separated list of slugs to exclude.', 'wp-dev-dashboard' ),
			)
		);

		add_settings_field(
			'plugin_slugs', // ID
			__( 'Additional plugins', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'plugin_slugs',
				'description' => __( 'Comma-separated list of slugs for additional plugins to include.  Note: Adding a slug here will override an exclusion above.', 'wp-dev-dashboard' ),
			)
		);

		add_settings_field(
			'exclude_theme_slugs', // ID
			__( 'Exclude themes', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'exclude_theme_slugs',
				'description' => __( 'Comma-separated list of slugs to exclude.', 'wp-dev-dashboard' ),
			)
		);

		add_settings_field(
			'theme_slugs', // ID
			__( 'Additional themes', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'theme_slugs',
				'description' => __( 'Comma-separated list of slugs for additional themes to include.  Note: Adding a slug here will override an exclusion above.', 'wp-dev-dashboard' ),
			)
		);

		add_settings_field(
			'show_all_tickets', // ID
			__( 'Show all tickets', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_checkbox' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'show_all_tickets',
				'description' => __( 'Show all tickets, by default only unresolved tickets are shown.', 'wp-dev-dashboard' ),
			)
		);

		add_settings_field(
			'refresh_timeout', // ID
			__( 'Hours before refresh', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'refresh_timeout',
				'description' => __( 'The number of hours before a refresh will be done.  Valid hours are between 1 and 24.  Note: This setting will not take effect until the last data load expires.', 'wp-dev-dashboard' ),
			)
		);

		add_settings_field(
			'age_limit', // ID
			__( 'Age limit', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'age_limit',
				'description' => __( 'Ignore tickets older than this number of days. 0 = unlimited.', 'wp-dev-dashboard' ),
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
	 * @since 1.0.0
	 *
	 * @param array $args Args from add_settings_field().
	 */
	public function render_text_input( $args ) {

		$default = array_key_exists( 'default', $args ) ? $args['default'] : '';
		$option_name = $this->plugin_slug . '[' . $args['id'] . ']';
		$option_value = ! empty( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : $default;
		printf(
            '%s<input type="text" value="%s" id="%s" name="%s" class="regular-text %s"/><br /><p class="description" for="%s">%s</p>',
            ! empty( $args['sub_heading'] ) ? '<b>' . $args['sub_heading'] . '</b><br />' : '',
            $option_value,
            $args['id'],
            $option_name,
            ! empty( $args['class'] ) ? $args['class'] : '',
            $option_name,
            ! empty( $args['description'] ) ? $args['description'] : ''
        );

	}

	/**
	 * Checkbox settings field callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Args from add_settings_field().
	 */
	function render_checkbox( $args ) {

		$option_name = $this->plugin_slug . '[' . $args['id'] . ']';
		$option_value = ! empty( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : '';
		printf(
            '<input type="checkbox" value="1" id="%s" name="%s" %s/><p class="description" for="%s">%s</p>',
            $option_name,
            $option_name,
            checked( 1, $option_value, false ),
            $option_name,
            ! empty( $args['description'] ) ? $args['description'] : ''
        );

	}

	/**
	 * Output refresh button.
	 *
	 * @since 1.0.0
	 */
	public function do_refresh_button() {

		// Set up refresh button atts.
		$refresh_button_atts = array(
			'href'  => '',
		);
		?>
		<div class="wpdd-refresh-button-container">
			<?php submit_button( esc_attr__( 'Reload from wordpress.org', 'wp-dev-dashboard' ), 'button wpdd-button-refresh', '', false, $refresh_button_atts ); ?><span class="spinner"></span>
		</div>
		<?php

	}

	public function do_ajax_container( $object_type = 'tickets', $ticket_type = 'plugins' ) {
		printf( '<div class="wpdd-ajax-container" data-wpdd-object-type="%s" data-wpdd-ticket-type="%s"><div class="wpdd-loading-div"><span class="spinner is-active"></span> <span>%s</span></div></div>', $object_type, $ticket_type, $this->js_data['fetch_messages'][ array_rand( $this->js_data['fetch_messages'] ) ] );
	}

	/**
	 * Output the table for tickets.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ticket_type   Type of ticket to output.
	 * @param bool   $force_refresh Whether or not to force an uncached refresh.
	 */
	public function do_ticket_table( $ticket_type = 'plugins', $force_refresh = false ) {
		?>
		<table class="widefat striped" id="wdd_tickets_table">
			<thead>
				<tr>
					<td><?php _e( 'Status', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( 'Title' ); ?></td>
					<td><?php _e( 'Plugin/Theme' ); ?></td>
					<td><?php _e( 'Type' ); ?></td>
					<td><?php _e( 'Last Post' ); ?></td>
					<td><?php _e( 'Last Poster' ); ?></td>
				</tr>
			</thead>
			<tbody>
		<?php
			$plugins_themes = array_merge( $this->get_plugins_themes( 'plugins', $force_refresh ), $this->get_plugins_themes( 'themes', $force_refresh ) );
			$tickets_data = array();
			$plugin_theme_names = array();
			
			$age_limit = ( empty( $this->options['age_limit'] ) ) ? 0 : (int)$this->options['age_limit'];
			$ctime = time();
			$age_limit_time = strtotime( "{$age_limit} days ago", $ctime );
			
			foreach( $plugins_themes as $plugin_theme ) {
				// Skip if there are no tickets.
				if ( empty ( $plugin_theme->tickets_data ) ) {
					continue;
				}

				$plugin_theme_names[$plugin_theme->slug] = $plugin_theme->name;
				$tickets_data = array_merge( $tickets_data, $plugin_theme->tickets_data );

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
					$icon_class = 'editor-help';
				}

				$icon_html = sprintf( '<span class="dashicons dashicons-%s" title="%s"></span> ', $icon_class, ucfirst( $ticket_data['status'] ) );

				echo '<tr>' . PHP_EOL;
				echo '<td>' . $icon_html . '</td>' . PHP_EOL;
				printf( '<td><a href="%s" target="_blank">%s</a></td>%s', $ticket_data['href'], $ticket_data['text'], PHP_EOL );
				printf( '<td><a href="%s" target="_blank">%s</a></td>%s', "https://wordpress.org/plugins/" . $ticket_data['slug'], $plugin_theme_names[$ticket_data['slug']], PHP_EOL );
				echo '<td>' . $plugin_theme->type . '</td>' . PHP_EOL;
				echo '<td>' . date( 'M d, Y g:m a', $ticket_data['timestamp'] ) . '</td>' . PHP_EOL;
				printf( '<td><a href="%s" target="_blank">%s</a></td>%s', $ticket_data['lastposterhref'], $ticket_data['lastposter'], PHP_EOL );
			}

			?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Helper function to update ticket/stats content via Ajax.
	 *
	 * @since 1.0.0
	 */
	public function get_ajax_content() {

		/**
		 * Include necessary global: hook_suffix. For some reason this
		 * doesn't work by default and must be included manually to
		 * avoid throwing a notice.
		 */
		global $hook_suffix;

		// Get paramters to load correct content.
		$ticket_type = isset( $_POST['ticket_type'] ) ? $_POST['ticket_type'] : 'plugins';
		$force_refresh = isset( $_POST['force_refresh'] ) ? $_POST['force_refresh'] : false;
		$current_url = isset( $_POST['current_url'] ) ? $_POST['current_url'] : false;

		// Output refresh button.
		$this->do_refresh_button();

		?>
		<div class="wpdd-sub-tab-nav nav-tab-wrapper">
        	<a href="#" class="button button-primary" data-wpdd-tab-target="tickets"><span class="dashicons dashicons-editor-help"></span> <?php echo __( 'Tickets', 'wp-dev-dashboard '); ?></a>
        	<a href="#" class="button" data-wpdd-tab-target="info"><span class="dashicons dashicons-list-view" data-wpdd-tab-target="info"></span> <?php echo __( 'Statistics', 'wp-dev-dashboard '); ?></a>
        </div>
        <div class="wpdd-sub-tab-container">
        	<div class="wppd-sub-tab wpdd-sub-tab-tickets active"><?php $this->do_ticket_table( $ticket_type, $force_refresh ); // $this->do_meta_boxes( $ticket_type, $force_refresh ); ?></div>
        	<div class="wppd-sub-tab wpdd-sub-tab-info"><?php $this->output_list_table( $ticket_type, $current_url ); ?></div>
        </div>
        <?php

        // Output refresh button.
		$this->do_refresh_button();

		wp_die(); // this is required to terminate immediately and return a proper response

	}

	/**
	 * Get all plugin or theme data based on the plugin settings.
	 *
	 * @since 1.0.0
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

		// Set transient slug for this specific username and plugin/theme slugs.
		$transient_slug = $ticket_type;

		// Append username to transient.
		if ( $username ) {
			$transient_slug .= "-{$username}";
		}

		// Append plugin slugs to transient.
		if ( 'plugins' == $ticket_type && ! empty( $this->options['plugin_slugs'] ) ) {
			$transient_slug .= '-' . $this->options['plugin_slugs'];
		}

		// Append theme slugs to transient.
		if ( 'themes' == $ticket_type && ! empty( $this->options['theme_slugs'] ) ) {
			$transient_slug .= '-' . $this->options['theme_slugs'];
		}

		$transient_slug = 'wpdd-' . md5( $transient_slug );

		$plugins_themes = get_transient( $transient_slug );

		if( true === $quick ) {
			return $plugins_themes;
		}

		// Get the number of hours we should keep the transient for.
		$timeout = (int)$this->options['refresh_timeout'];

		// Do some sanity checking on the timeout value.
		if ( $timeout < 1 || $timeout > 24 ) { $timeout = 1; }

		if ( $force_refresh || false === $plugins_themes ) {

			$plugins_themes = $this->get_tickets_data( $username, $ticket_type );

			if ( $plugins_themes ) {

				/**
				 * Filter transient expiration time.
				 *
				 * @since 1.0.0
				 *
				 * @param $expiration Expiration in seconds (default 3600 - one hour).
				 */
				$transient_expiration = apply_filters( 'wpdd_transient_expiration', $timeout * HOUR_IN_SECONDS );
				set_transient( $transient_slug, $plugins_themes, $transient_expiration );
			}

		}

		return $plugins_themes;

	}

	/**
	 * Output a list table of plugins/themes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table_type Type of table to output (plugins|themes)
	 */
	public function output_list_table( $table_type = 'plugins', $current_url = null ) {
?>
		<table class="widefat striped wdd-stats-table">
			<thead>
				<tr>
					<td><?php _e( 'Title', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( 'Type', 'wp-dev-dashboard' ); ?>
					<td><?php _e( 'Version', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( 'WP Version Tested', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( 'Rating', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( '# of Reviews', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( 'Active Installs', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( 'Downloads', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( 'Unresolved', 'wp-dev-dashboard' ); ?></td>
					<td><?php _e( 'Resolved', 'wp-dev-dashboard' ); ?></td>
				</tr>
			</thead>
			<tbody>
<?php
			$plugins_themes = $this->get_plugins_themes( $table_type );

			$update_data = get_site_transient( 'update_core' );
			$wp_branches = $update_data->updates;

			$wp_version = '';
			foreach( $wp_branches as $index => $branch ) {
				if ( 'latest' == $branch->response ) {
					$wp_version = $wp_branches[ $index ]->version;
				}
			}
		
			foreach( $plugins_themes as $plugin_theme ) {
				echo '<tr>';
    			printf( '<td><b><a href="%s" target="_blank">%s</a><b>', 'https://wordpress.org/plugins/' . $plugin_theme->slug . '</td>' . PHP_EOL, $plugin_theme->name );
				echo "<td>{$plugin_theme->type}</td>" . PHP_EOL;
				echo "<td>{$plugin_theme->version}</td>" . PHP_EOL;

    			$class = '';

    			if ( $wp_version ) {
    				if ( version_compare( $item->tested, $wp_version ) >= 0 && 'plugins' == $table_type ) {
    					$class = 'wpdd-current';
    				} else {
    					$class = 'wpdd-needs-update';
    				}
    			}

    			printf( '<td><span class="%s">%s</span></td>' . PHP_EOL, $class, ( 'plugins' == $table_type ? $plugin_theme->tested : __( 'N/A', 'wp-dev-dashboard' ) ) );
				echo '<td>' . ( $plugin_theme->rating ? $plugin_theme->rating : __( 'N/A', 'wp-dev-dashboard' ) ) . '</td>' . PHP_EOL;
				echo "<td>{$plugin_theme->num_ratings}</td>" . PHP_EOL;
				echo '<td>' . number_format_i18n( $plugin_theme->active_installs ) . '</td>' . PHP_EOL;
				echo '<td>' . number_format_i18n( $plugin_theme->downloaded ) . '</td>' . PHP_EOL;
				echo '<td>' . number_format_i18n( $plugin_theme->unresolved_count ) . '</td>' . PHP_EOL;
				echo '<td>' . number_format_i18n( $plugin_theme->resolved_count ) . '</td>' . PHP_EOL;
				echo '</tr>';
			}

			?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Get tickets data for a specific user's plugin or themes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $username    WordPress.org username.
	 * @param string $ticket_type Type of ticket to query for.
	 *
	 * @return array $plugins_themes Array of plugins|themes and associated info.
	 */
	public function get_tickets_data( $username, $ticket_type = 'plugins' ) {

		// Get tickets by user.
		$plugins_themes_by_user = $this->get_plugins_themes_by_user( $username, $ticket_type );

		// Get any plugins/themes that are manually set via the plugin settings.
		$plugins_themes_from_setting = $this->get_plugins_themes_from_settings( $ticket_type );

		// Merge plugins/themes for 1. user and 2. manually set in settings.
		$plugins_themes = array_merge( $plugins_themes_by_user, $plugins_themes_from_setting );

		// Loop through all plugins/themes.
		foreach ( $plugins_themes as $index => $plugins_theme ) {

			$plugins_themes[ $index ]->type = ( 'plugins' == $ticket_type ) ? 'Plugin' : 'Theme';

			// Initialize ticket count to zero in case we have to return early.
			$plugins_themes[ $index ]->unresolved_count = 0;
			$plugins_themes[ $index ]->resolved_count = 0;

			$tickets_data = $this->get_unresolved_tickets( $plugins_theme->slug, $ticket_type );

			if ( ! $tickets_data ) {
				continue;
			}

			$plugins_themes[ $index ]->tickets_data = $tickets_data;

			// Add ticket counts.
			foreach ( $tickets_data as $ticket_data ) {

				if ( 'unresolved' == $ticket_data['status'] ) {
					$plugins_themes[ $index ]->unresolved_count++;
				} else {
					$plugins_themes[ $index ]->resolved_count++;
				}

			}

		}

		return $plugins_themes;

	}

	/**
	 * Generate HTML for output for a plugin's/theme's tickets.
	 *
	 * @since 1.0.0
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
				'wpdd-' . $ticket_data['status'],
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
			$html .= apply_filters( 'wpdd_ticket_output', $ticket_output, $ticket_data );

			$i++;

		}

		$html .= '</ul>';

		return $html;

	}

	/**
	 * [Unused] Generate HTML for output for a plugin's/theme's meta data.
	 *
	 * @since 1.0.0
	 *
	 * @param stdClass Object $plugin_theme Plugin/theme object.
	 *
	 * @return string HTML output of plugin/theme meta data.
	 */
	public function get_plugin_theme_data_html( $plugin_theme ) {

		ob_start();
		?>
		<ul class="plugin-theme-data">
			<li class="version"><?php printf( '<h4>%s</h4>%s', esc_html__( 'Version', 'wp-dev-dashboard' ), $plugin_theme->version ); ?></li>
			<li class="wp-versions"><?php printf( '<h4>%s</h4>%s - %s', esc_html__( 'WP Versions', 'wp-dev-dashboard' ), $plugin_theme->requires, $plugin_theme->tested ); ?></li>
			<li class="rating"><?php printf( '<h4>%s</h4>%s', esc_html__( 'Rating', 'wp-dev-dashboard' ), $plugin_theme->rating ); ?></li>
		</ul>
		<?php

		return ob_get_clean();

	}

	/**
	 * Get data for a specific plugin/theme.
	 *
	 * @since 1.0.0
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

		$args = array(
			'author' => $this->options['username'],
			'fields' => $this->api_fields,
		);

		$data = call_user_func( $query_function, $query_action, $args );

		$plugins_themes_by_user = array();

		if ( $data && ! is_wp_error( $data ) ) {
			$plugins_themes_by_user = ( 'plugins' == $ticket_type ) ? $data->plugins : $data->themes;
		}

		// Exclude the slugs the user has told us to.
		foreach( $plugins_themes_by_user as $key => $value ) {
			if ( in_array( $value->slug, $exclude_slugs ) ) {
				unset( $plugins_themes_by_user[$key] );
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
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @param string $plugin_theme_slug Plugin/theme slug.
	 * @param string $ticket_type       plugins|themes
	 *
	 * @return array Array of all unresolved ticket data.
	 */
	public function get_unresolved_tickets( $plugin_theme_slug, $ticket_type = 'plugins' ) {

		$rows_data = array();

		$i = 1;
		while (	$new_rows_data = $this->get_unresolved_tickets_for_page( $plugin_theme_slug, $ticket_type, $i ) ) {
			$rows_data = array_merge( $rows_data, $new_rows_data );
			$i++;
		}

		return $rows_data;

	}

	/**
	 * Get unresolved ticket for a specific page of a plugin/theme support forum.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_theme_slug Plugin/theme slug.
	 * @param string $ticket_type       plugins|themes
	 * @param string $page_num          Support forum page to query.
	 *
	 * @return array Array of ticket data.
	 */
	public function get_unresolved_tickets_for_page( $plugin_theme_slug, $ticket_type = 'plugins', $page_num ) {

		$html = $this->get_page_html( $plugin_theme_slug, $page_num, $ticket_type );

		if( is_wp_error( $html ) ) {
			printf( __( 'WP Dev Dashboard error: %s (%s)<br />', 'wp-dev-dashboard' ), $html->get_error_message(), $plugin_theme_slug );
			return false;
		}

		$html = HtmlDomParser::str_get_html( $html );

		$table = $html->find( 'li[class=bbp-body]', 0 );

		// Return false if no table is found.
		if ( empty ( $table ) ) {
			return false;
		}

		// Generate array of row data.
		$rows = $table->find( 'ul[class=topic]' );
		$rows_data = array();

		foreach ( $rows as $row ) {

			// Get row attributes.
			$title      = $row->find( 'li[class=bbp-topic-title]', 0 );
			$freshness  = $row->find( 'li[class=bbp-topic-freshness]', 0 );
			$link       = $title->find( 'a', 0 );
			$time       = $freshness->find( 'a', 0 );
			$startby    = $title->find( 'a[class=bbp-author-name]', 0 );
			$lastposter = $freshness->find( 'a[class=bbp-author-name]', 0 );

			$row_data['href']           = $link->href;
			$row_data['text']           = $link->innertext;
			$row_data['time']           = $time->innertext;
			$row_data['timestamp']      = strtotime( $row_data['time'] );
			$row_data['status']         = ( strpos( $link->innertext, '[Resolved]') === 0 ) ? 'resolved' : 'unresolved';
			$row_data['sticky']         = ( strpos( $row->class, 'sticky') !== false ) ? true : false;
			$row_data['closed']         = ( strpos( $row->class, 'status-closed') !== false ) ? true : false;
			$row_data['startedby']      = $startby->innertext;
			$row_data['startedbyhref']  = $startby->href;
			$row_data['lastposter']     = $lastposter->innertext;
			$row_data['lastposterhref'] = $lastposter->href;
			$row_data['type']           = ( 'plugins' == $ticket_type ) ? 'Plugin' : 'Theme';
			$row_data['slug']           = $plugin_theme_slug;

			$rows_data[] = $row_data;

		}

		return $rows_data;

	}

	public function get_page_html( $plugin_theme_slug, $page_num, $ticket_type ) {

		if ( ! $page_num ) {
			return false;
		}

		if ( 'plugins' == $ticket_type ) {
			$remote_url = "https://wordpress.org/support/plugin/{$plugin_theme_slug}/active/page/{$page_num}";
		} else {
			$remote_url = "https://wordpress.org/support/theme/{$plugin_theme_slug}/active/page/{$page_num}";
		}

		$response = wp_remote_get( $remote_url );

		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {

			// Decode the API data and grab the versions info.
			$response = wp_remote_retrieve_body( $response );

		} else {
			return new WP_Error( 'error', sprintf( __( 'Attempt to fetch support forums HTML failed (%s)', 'wp-dev-dashboard' ), $plugin_theme_slug ) );
		}

		return $response;

	}

}
