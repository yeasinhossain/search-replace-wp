<?php
/**
 * Displays the main "Search/Replace" tab.
 *
 * @link       https://yeasin.me
 * @since      1.1
 *
 * @package    Search_Replace_WP
 * @subpackage Search_Replace_WP/templates
 */

// Prevent direct/unauthorized access.
if ( ! defined( 'SRWP_PATH' ) ) exit;

?>

<div id="srwp-search-replace-wrap" class="postbox">

	<div class="inside">

		<?php ?>

		<p><?php _e( 'This tool allows you to search and replace text in your database.', 'search-replace-wp' ); ?></p>
		<p><?php _e( 'To get started, use the feilds below to enter the text to be replaced and select the tables to update.', 'search-replace-wp' ); ?></p>
		<p style="color: red; font-style: italic;"><?php _e( '<strong>WARNING:</strong> Make sure you backup your database before using this plugin!', 'search-replace-wp' ); ?></p>

		<table id="srwp-search-replace-form" class="form-table">

			<tr>
				<td><label for="search_for"><strong><?php _e( 'Search for', 'search-replace-wp' ); ?></strong></label></td>
				<td><input id="search_for" class="regular-text" type="text" name="search_for" value="<?php SRWP_Admin::prefill_value( 'search_for' ); ?>" /></td>
			</tr>

			<tr>
				<td><label for="replace_with"><strong><?php _e( 'Replace with', 'search-replace-wp' ); ?></strong></label></td>
				<td><input id="replace_with" class="regular-text" type="text" name="replace_with" value="<?php SRWP_Admin::prefill_value( 'replace_with' ); ?>" /></td>
			</tr>

			<tr>
				<td><label for="select_tables"><strong><?php _e( 'Select tables', 'search-replace-wp' ); ?></strong></label></td>
				<td>
					<?php SRWP_Admin::load_tables(); ?>
					<p class="description"><?php _e( 'Select multiple tables with Ctrl-Click for Windows or Cmd-Click for Mac.', 'search-replace-wp' ); ?></p>
				</td>
			</tr>

			<tr>
				<td><label for="case_insensitive"><strong><?php _e( 'Case-Insensitive?', 'search-replace-wp' ); ?></strong></label></td>
				<td>
					<input id="case_insensitive" type="checkbox" name="case_insensitive" <?php SRWP_Admin::prefill_value( 'case_insensitive', 'checkbox' ); ?> />
					<label for="case_insensitive"><span class="description"><?php _e( 'Searches are case sensitive by default.', 'search-replace-wp' ); ?></span></label>
				</td>
			</tr>

			<tr>
				<td><label for="replace_guids"><strong><?php _e( 'Replace GUIDs<a href="http://codex.wordpress.org/Changing_The_Site_URL#Important_GUID_Note" target="_blank">?</a>', 'search-replace-wp' ); ?></strong></label></td>
				<td>
					<input id="replace_guids" type="checkbox" name="replace_guids" <?php SRWP_Admin::prefill_value( 'replace_guids', 'checkbox' ); ?> />
					<label for="replace_guids"><span class="description"><?php _e( 'If left unchecked, all database columns titled \'guid\' will be skipped.', 'search-replace-wp' ); ?></span></label>
				</td>
			</tr>

			<tr>
				<td><label for="dry_run"><strong><?php _e( 'Run as dry run?', 'search-replace-wp' ); ?></strong></label></td>
				<td>
					<input id="dry_run" type="checkbox" name="dry_run" checked />
					<label for="dry_run"><span class="description"><?php _e( 'If checked, no changes will be made to the database, allowing you to check the results beforehand.', 'search-replace-wp' ); ?></span></label>
				</td>
			</tr>


		</table>

		<br>

		<div id="srwp-submit-wrap">
			<?php wp_nonce_field( 'process_search_replace', 'srwp_nonce' ); ?>
			<input type="hidden" name="action" value="srwp_process_search_replace" />
			<button id="srwp-submit" type="submit" class="button"><?php _e( 'Run Search/Replace', 'search-replace-wp' ); ?></button>
		</div>

		<?php ?>

	</div><!-- /.inside -->

</div><!-- /#srwp-search-replace-wrap -->
