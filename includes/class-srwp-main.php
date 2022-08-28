<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0
 * @package    Search_Replace_WP
 * @subpackage Search_Replace_WP/includes
 */

// Prevent direct access.
if ( ! defined( 'SRWP_PATH' ) ) exit;

class Search_Replace_WP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      SRWP_Loader   $loader   Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0
	 */
	public function __construct() {
		$this->plugin_name 	= 'search-replace-wp';
		$this->version 		= SRWP_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once SRWP_PATH . 'includes/class-srwp-loader.php';
		require_once SRWP_PATH . 'includes/class-srwp-i18n.php';
		require_once SRWP_PATH . 'includes/class-srwp-admin.php';
		require_once SRWP_PATH . 'includes/class-srwp-ajax.php';
		require_once SRWP_PATH . 'includes/class-srwp-db.php';
		require_once SRWP_PATH . 'includes/class-srwp-compatibility.php';

		$this->loader = new SRWP_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the SRWP_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new SRWP_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );
	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// Initialize the admin class.
		$plugin_admin = new SRWP_Admin( $this->get_plugin_name(), $this->get_version() );

		// Register the admin pages and scripts.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'srwp_menu_pages' );

		// Other admin actions.
		$this->loader->add_action( 'admin_post_srwp_process_load_profile', $plugin_admin, 'process_load_profile' );
		$this->loader->add_action( 'admin_post_srwp_process_delete_profile', $plugin_admin, 'process_delete_profile' );
		$this->loader->add_action( 'admin_post_srwp_view_details', $plugin_admin, 'load_details' );
		$this->loader->add_action( 'admin_post_srwp_download_sysinfo', $plugin_admin, 'download_sysinfo' );
		$this->loader->add_action( 'admin_post_srwp_download_backup', $plugin_admin, 'download_backup' );
		$this->loader->add_action( 'load-tools_page_search-replace-wp', $plugin_admin, 'upgrade_profiles' );


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
	 * @return    Search_Replace_WP_Loader    Orchestrates the hooks of the plugin.
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

}
