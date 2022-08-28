<?php
/**
 * Displays the "Backup" tab.
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


<div class="metabox-holder">

	<div class="postbox">
		 <h3><?php _e( 'Backup Database', 'search-replace-wp' ); ?></h3>
		 <div class="inside">

			<p><?php _e( 'Click the button below to take a backup of your database, which can then be imported into another instance of Search Replace WP.', 'search-replace-wp' ); ?></p>

			<div id="srwp-backup-form">
				<?php wp_nonce_field( 'srwp_process_backup', 'srwp_nonce' ); ?>
				<input type="hidden" name="action" value="srwp_process_backup" />
				<button id="srwp-backup-submit" type="submit" class="button"><?php _e( 'Backup Database', 'search-replace-wp' ); ?></button>
			</div>

		</div>
	</div>

	<div class="postbox">
		<h3><?php _e( 'Import Database', 'search-replace-wp' ); ?></h3>

		<div class="inside">

			<div id="srwp-import-form">

				<p><?php _e( 'Use the form below to import a database backup, Choose the file you want to import and click "Import Database".', 'search-replace-wp' ); ?></p>

				<input id="srwp-file-import" type="file" name="srwp_import_file">
				<br>
				<br>
				<?php wp_nonce_field( 'srwp_process_import', 'srwp_nonce' ); ?>
				<input type="hidden" name="action" value="srwp_process_import" />
				<button id="srwp-import-submit" type="submit" class="button"><?php _e( 'Import Database', 'search-replace-wp' ); ?></button>

			</div>
		</div>

	</div>


</div>
