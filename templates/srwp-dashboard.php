<?php

/**
 * Displays the main Search Replace WP page under Tools -> Search Replace WP.
 *
 * @link       https://yeasin.me
 * @since      1.0.0
 *
 * @package    Search_Replace_WP
 * @subpackage Search_Replace_WP/templates
 */

// Prevent direct access.
if ( ! defined( 'SRWP_PATH' ) ) exit;

// Determines which tab to display.
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'srwp_search_replace';

// Bail if not on a SRWP page
if ( ! in_array( $active_tab, array( 'srwp_search_replace', 'srwp_backup_import', 'srwp_settings', 'srwp_help' ) ) ) {
	wp_die( 'The requested tab was not found.', 'search-replace-wp' );
}

if ( 'srwp_settings' === $active_tab ) {
	$action = get_admin_url() . 'options.php';
} else {
	$action = get_admin_url() . 'admin-post.php';
}

?>

<div class="wrap">

	<h1><?php _e( ' Search & Replace WP', 'search-replace-wp' ); ?></h1>
	<?php settings_errors(); ?>

	<div id="srwp-error-wrap"></div>

	<?php SRWP_Admin::render_result(); ?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=search-replace-wp&tab=srwp_search_replace" class="nav-tab <?php echo $active_tab == 'srwp_search_replace' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Search/Replace', 'search-replace-wp' ); ?></a>
		<a href="?page=search-replace-wp&tab=srwp_settings" class="nav-tab <?php echo $active_tab == 'srwp_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'search-replace-wp' ); ?></a>
		<a href="?page=search-replace-wp&tab=srwp_backup_import" class="nav-tab <?php echo $active_tab == 'srwp_backup_import' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Backup/Import', 'search-replace-wp' ); ?></a>
		<a href="?page=search-replace-wp&tab=srwp_help" class="nav-tab <?php echo $active_tab == 'srwp_help' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Help', 'search-replace-wp' ); ?></a>
	</h2>

	<form class="srwp-action-form" action="<?php echo $action; ?>" method="POST">

		<?php
		// Include the correct tab template.
		$srwp_template = str_replace( '_', '-', sanitize_file_name( $active_tab ) ) . '.php';
		if ( file_exists( SRWP_PATH . 'templates/' . $srwp_template ) ) {
			include SRWP_PATH . 'templates/' . $srwp_template;
		} else {
			include SRWP_PATH . 'templates/srwp-search-replace.php';
		}
		?>

	</form>

</div><!-- /.wrap -->
