<?php

/**
 * Abovethefold optimization core class.
 *
 * This class provides the functionality for admin dashboard and WordPress hooks.
 *
 * @since      1.0
 * @package    abovethefold
 * @subpackage abovethefold/includes
 * @author     Optimalisatie.nl <info@optimalisatie.nl>
 */
class Abovethefold {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      Abovethefold_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	public $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Development environment
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      bool    Loaded via development / testing environment.
	 */
	public $devenv = false;

	/**
	 * Disable abovethefold (public testing)
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      bool
	 */
	public $noop = false;

	/**
	 * Construct and initiated Abovethefold class.
	 *
	 * @since    1.0
	 */
	public function __construct() {

		$this->plugin_name = 'abovethefold';
		$this->version = '1.0';

		/**
		 * Disable plugin in admin or for testing
		 */
		if (
			is_admin()
			|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			|| in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))
		) {
			$this->noop = true;
		}

		/**
		 * Register Activate / Deactivate hooks.
		 */
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

		if ( !is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			$this->define_optimization_hooks();
		}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Optimization_Loader. Orchestrates the hooks of the plugin.
	 * - Optimization_i18n. Defines internationalization functionality.
	 * - Optimization_Admin. Defines all hooks for the dashboard.
	 * - Optimization_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/loader.class.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/i18n.class.php';

		/**
		 * The class responsible for defining all actions related to optimization.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/optimization.class.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin.class.php';

		$this->loader = new Abovethefold_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Abovethefold_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Abovethefold_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Abovethefold_Admin( $this->get_plugin_name(), $this->get_version(), $this->config );

		if (is_admin()) {
			// Hook in the admin options page
        	$this->loader->add_action('admin_menu', $plugin_admin, 'admin_menu',1);
		}

		// Register settings (data storage)
		$this->loader->add_action('admin_init', $plugin_admin, 'register_settings');

	}

	/**
	 * Register all of the hooks related to optimization.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_optimization_hooks() {

		$plugin_optimization = new Abovethefold_Optimization( $this );

		$this->loader->add_action('init', $plugin_optimization, 'init');
		$this->loader->add_action('wp_head', $plugin_optimization, 'header', 1);
		$this->loader->add_action('wp_foot', $plugin_optimization, 'bufferend', 99999);

		$this->loader->add_action('wp_print_footer_scripts', $plugin_optimization, 'footer',99999);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0
	 * @return    Optimization_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Fired during plugin activation.
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function activate() {

	}

	/**
	 * Fired during plugin deactivation.
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function deactivate() {

	}

}
