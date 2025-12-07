<?php
/**
 * Import Export Settings
 *
 * @author  Surelywp
 * @package Toolkit For SureCart
 * @since 1.5
 */

?>
<table class="form-table surelywp-ric-settings-box ">
	<tbody>
		<tr class="surelywp-field-label import-export-tab tab-settings" id="import-export-tab">
			<td>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Export Settings', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Download a file containing all of your current plugin settings. This file can be used as a backup or as a template to apply the same configuration on another site.', 'surelywp-toolkit' ); ?></label>
					<div class="form-control addons-btn-wrap no-sort">
						<form method="post" action="">
							<?php wp_nonce_field( 'surelywp_tk_export_settings', 'surelywp_export_nonce' ); ?>
							<button type="submit" name="surelywp_tk_export" 
								class="button button-primary"><img class="surelywp-export-icon" src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/export.svg' ); ?>"><?php esc_html_e( 'Export All Settings', 'surelywp-toolkit' ); ?></button>
						</form>
					</div>
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Import Settings', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Upload a settings file previously exported from this plugin. Importing will overwrite your current settings with the imported configuration. This action cannot be undone. Consider exporting your currents settings first as a backup.', 'surelywp-toolkit' ); ?></label>
					<form id="import-settings-form" method="post" enctype="multipart/form-data" action="">
						<?php wp_nonce_field( 'surelywp_tk_import_settings', 'surelywp_import_nonce' ); ?>
						<div class="attachment-file">
							<input  type="file"  class="messages-tk-filepond"  name="import_tk_file"  accept="application/json" data-max-file-size="<?php echo esc_attr( SURELYWP_TOOLKIT_IE_FILE_SIZE . 'MB' ); ?>" data-max-files="1" required>
						</div>
						<button type="submit" name="surelywp_import" class="button button-secondary"><img class="surelywp-import-icon" src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/import.svg' ); ?>"><?php esc_html_e( 'Import All Settings', 'surelywp-toolkit' ); ?></button>
					</form>
				</div>
			</td>
		</tr>
	</tbody>
</table>