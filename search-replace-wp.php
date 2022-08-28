<?php
/**
 *  Search & Replace WP
 *
 * This plugin improves upon the database search/replace functionality offered
 * by some other plugins- offering serialization support, the ability to
 * select specific tables, and the ability to run a dry run.
 *
 * @since             1.0.0
 * @package           Search_Replace_WP
 *
 * @wordpress-plugin
 * Plugin Name:       Search & Replace WP
 * Plugin URI:        https://yeasin.me
 * Description:       A tiny tool for running a search/replace on your WordPress database.
 * Version:           1.0.2
 * Author:            Yeasin
 * Author URI:        https://yeasin.me
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       search-replace-wp
 * Domain Path:       /languages
 * Network:			  true
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// If this file was called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_srwp_search_replace() {

	// Allows for overriding the capability required to run the plugin.
	$cap = apply_filters( 'srwp_capability', 'install_plugins' );

	// Only load for admins.
	if ( current_user_can( $cap ) ) {

		/**
		 * The core plugin class that is used to define internationalization,
		 * dashboard-specific hooks, and public-facing site hooks.
		 */
		if ( ! function_exists( 'run_better_search_replace' ) ) {

			// Defines the path to the main plugin file.
			define( 'SRWP_FILE', __FILE__ );

			// Defines the path to be used for includes.
			define( 'SRWP_PATH', plugin_dir_path( SRWP_FILE ) );

			// Defines the URL to the plugin.
			define( 'SRWP_URL', plugin_dir_url( SRWP_FILE ) );

			// Defines the current version of the plugin.
			define( 'SRWP_VERSION', '1.3.7' );

			// Defines the name of the plugin.
			define( 'SRWP_NAME', ' Search & Replace WP' );

			// Defines the API url for the plugin.
			define( 'SRWP_API_URL', 'https://yeasin.me' );

			require SRWP_PATH . 'includes/class-srwp-main.php';
			$plugin = new Search_Replace_WP();
			$plugin->run();

		} else {
			add_action( 'admin_notices', 'srwp_conflict_notice' );
		}

	}

}
add_action( 'after_setup_theme', 'run_srwp_search_replace' );


// Adding Plugin's Settings Link 
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'srwp_add_plugin_page_settings_link');
function srwp_add_plugin_page_settings_link( $links ) {
    array_unshift(
        $links,
        '<a href="' .
        admin_url('tools.php?page=search-replace-wp') .
        '">' . __('Search & Replace Now') . '</a>'
    );
    return $links;
}

/**
 * Used to notify users that the fSearch Replace WP of the plugin
 * should be deactivated - the Search & Replace WP is standalone & Conflicting and
 * will likely error out otherwise!
 *
 * @since 1.1.1
 */
function srwp_conflict_notice() {
	?>

	<div class="error"><p><?php _e( ' Search & Replace WP has been installed successfully, but requires Search Replace WP to be deactivated to make this plugin works properly.', 'search-replace-wp' ); ?></p></div>

	<?php
}