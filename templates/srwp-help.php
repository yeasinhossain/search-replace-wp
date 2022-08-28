<?php
/**
 * Displays the "System Info" tab.
 *
 * @link       https://yeasin.me
 * @since      1.1
 *
 * @package    Search_Replace_WP
 * @subpackage Search_Replace_WP/templates
 */

// Prevent direct access.
if ( ! defined( 'SRWP_PATH' ) ) exit;

$srwp_docs_url    = 'https://yeasin.me/docs/';
$srwp_support_url = 'https://yeasin.me/contact-me/';

?>

<h3><?php _e( 'Help & Troubleshooting', 'search-replace-wp' ); ?></h3>

<p><?php _e( 'Need some help, found a bug, or just have some feedback?', 'search-replace-wp' ); ?></p>

<p>
<?php
	printf( wp_kses( __( 'Check out the <a href="%s" target="_blank">documentation</a> or <a href="%s" target="_blank">contact to get support</a>.', 'search-replace-wp' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
		esc_url( $srwp_docs_url ),
		esc_url( $srwp_support_url )
	);
?>
</p>

<textarea readonly="readonly" onclick="this.focus(); this.select()" style="width:750px;height:500px;font-family:Menlo,Monaco,monospace; margin-top: 15px;" name='srwp-sysinfo'><?php echo SRWP_Compatibility::get_sysinfo(); ?></textarea>

<p class="submit">
	<input type="hidden" name="action" value="srwp_download_sysinfo" />
	<?php wp_nonce_field( 'srwp_download_sysinfo', 'srwp_sysinfo_nonce' ); ?>
	<?php submit_button( __( 'Download System Info', 'search-replace-wp' ), 'primary', 'srwp-download-sysinfo', false ); ?>
</p>
