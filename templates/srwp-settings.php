<?php
/**
 * Displays the main "Settings" tab.
 *
 * @link       https://yeasin.me
 * @since      1.1
 *
 * @package    Search_Replace_WP
 * @subpackage Search_Replace_WP/templates
 */

// Prevent direct/unauthorized access.
if ( ! defined( 'SRWP_PATH' ) ) exit;


// Other settings.
$page_size 		= get_option( 'srwp_page_size' ) ? absint( get_option( 'srwp_page_size' ) ) : 18000;
$max_results 	= get_option( 'srwp_max_results' ) ? absint( get_option( 'srwp_max_results' ) ) : 65;

if ( '' === get_option( 'srwp_enable_gzip' ) ) {
	$srwp_enable_gzip = false;
} else {
	$srwp_enable_gzip = true;
}

 ?>

<?php settings_fields( 'srwp_settings_fields' ); ?>

<table class="form-table">

	<tbody>

		<tr valign="top">
			<th scope="row" valign="top">
				<?php _e( 'Max Page Size', 'search-replace-wp' ); ?>
			</th>
			<td>
				<div id="srwp-page-size-slider" class="srwp-slider"></div>
				<br><span id="srwp-page-size-info"><?php _e( 'Current Setting: ', 'search-replace-wp' ); ?></span><span id="srwp-page-size-value"><?php echo absint( $page_size ); ?></span>
				<input id="srwp_page_size" type="hidden" name="srwp_page_size" value="<?php echo $page_size; ?>" />
				<p class="description"><?php _e( 'If you notice timeouts or are unable to backup/import the database, try decreasing this value using the slide above.', 'search-replace-wp' ); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" valign="top">
				<?php _e( 'Max Results', 'search-replace-wp' ); ?>
			</th>
			<td>
				<div id="srwp-max-results-slider" class="srwp-slider"></div>
				<br><span id="srwp-max-results-info"><?php _e( 'Current Setting: ', 'search-replace-wp' ); ?></span><span id="srwp-max-results-value"><?php echo absint( $max_results ); ?></span>
				<input id="srwp_max_results" type="hidden" name="srwp_max_results" value="<?php echo $max_results; ?>" />
				<p class="description"><?php _e( 'The maximum amount of results to store when running a search or replace.', 'search-replace-wp' ); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" valign="top">
				<?php _e( 'Enable Gzip?', 'search-replace-wp' ); ?>
			</th>
			<td>
				<label for="srwp-enable-gzip">
					<input id="srwp-enable-gzip" type="checkbox" name="srwp_enable_gzip" <?php checked( $srwp_enable_gzip, true ); ?> />
					<?php _e( 'If enabled, your created backups will be compressed to reduce file size.', 'search-replace-wp' ); ?>
				</label>
			</td>
		</tr>

	</tbody>

</table>
<?php submit_button(); ?>
