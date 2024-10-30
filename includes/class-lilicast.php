<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       app.lilicast.com
 * @since      1.0.0
 *
 * @package    Lilicast
 * @subpackage Lilicast/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Lilicast
 * @subpackage Lilicast/includes
 * @author     Jaakko Karhu <jaakko@26lights.com>
 */

class Lilicast {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Lilicast_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'LILICAST_PLUGIN_NAME_VERSION' ) ) {
			$this->version = LILICAST_PLUGIN_NAME_VERSION;
		}
		
		/* For some reason moving the string declarations
		 * under the function declarations causes them to be
		 * undefined. If anyone knows the reason, please tell me.
		 */
		$this->plugin_name = 'lilicast';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->dependency_checker = new Lilicast_Plugin_Dependency_Checker();
		try {
			$this->dependency_checker->check();
		} catch ( Lilicast_Plugin_Dependency_Exception $e ) {
			// The exception contains the names of missing plugins.
			$this->report_missing_dependencies( $e->get_missing_plugins() );
			return;
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Lilicast_Loader. Orchestrates the hooks of the plugin.
	 * - Lilicast_i18n. Defines internationalization functionality.
	 * - Lilicast_Admin. Defines all hooks for the admin area.
	 * - Lilicast_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-lilicast-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-lilicast-public.php';
		/* Original source: https://waclawjacek.com/check-wordpress-plugin-dependencies/
 		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-exception.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-plugin-dependency-exception.php';

		// Dependency checker
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-plugin-dependency-checker.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-plugin-dependency-reporter.php';

		/* */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-api-wrapper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-import-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-page-templater.php';		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-sync-from-app.php';	
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-video-grid-netflix.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lilicast-video-grid.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/render-helper-functions.php';
		
		$this->loader = new Lilicast_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Lilicast_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Lilicast_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Lilicast_Admin( $this->get_plugin_name(), $this->get_version());
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings');
		$this->loader->add_action( 'admin_notices',  $plugin_admin, 'lilicast_upload_retry_fail' );
		$this->loader->add_action( 'admin_notices',  $plugin_admin, 'lilicast_upload_retry_success' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'delete_attachment', $plugin_admin, 'on_delete_attachment' );
		$this->loader->add_action( 'init', $plugin_admin, 'add_categories_to_attachments' );
		$this->loader->add_action( 'init', $plugin_admin, 'add_tags_to_attachments' );
		$this->loader->add_action( 'init', $plugin_admin, 'add_metaboxes' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'classic_editor_save_metaboxes' );
		$this->loader->add_action( 'is_protected_meta', $plugin_admin, 'remove_lc_custom_fields', 10, 2 );
	}

	private function report_missing_dependencies( $missing_plugins ) {
		$missing_dependency_reporter = new Lilicast_Plugin_Dependency_Reporter( $missing_plugins );
		$missing_dependency_reporter->bind_to_admin_hooks();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Lilicast_Public( $this->get_plugin_name(), $this->get_version());

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_api_endpoints');
		$this->loader->add_action( 'wp', $plugin_public, 'generate_show_all_page');
		$this->loader->add_action('after_setup_theme', $plugin_public, 'remove_filters');
		$this->loader->add_action('wp_head', $plugin_public, 'add_og');


		$this->loader->add_filter( 'the_content', $plugin_public, 'disable_wp_auto_p', 0 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Lilicast_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
