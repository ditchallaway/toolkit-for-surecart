<?php
/**
 * Vacation Mode Settings
 *
 * @author  Surlywp
 * @package Toolkit For SureCart
 * @version 1.0.1
 */

global $surelywp_model;

$vacation_days = surelywp_tk_week_days();
?>
<?php if ( ! isset( $_GET['action'] ) ) { ?>
	<table class="form-table surelywp-ric-settings-box toolkit-templates-table">
		<tbody>
			<tr class="surelywp-field-label">
				<td>
					<?php
					$vm_setting_id = 0;
					if ( ! empty( $settings_options ) ) {

						foreach ( $settings_options as $key => $vm_settings ) {
							?>
							<div class="form-control toolkit-templates">
								<div class="image key">
									<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . '/assets/images/vacation-mode-list.svg' ); ?>" alt="<?php esc_attr_e( 'vacation-mode', 'surelywp-toolkit' ); ?>">
								</div>
								<div class="input-label">
									<?php echo esc_html( $vm_settings['vm_title'] ); ?>
								</div>
								<?php if ( isset( $vm_settings['vm_priority'] ) && ! empty( $vm_settings['vm_priority'] ) ) { ?>
									<div class="surelywp-tk-vm-priority-wrap">
										<span class="surelywp-tk-vm-priority"><?php echo esc_html( 'Priority: ' . $vm_settings['vm_priority'] ); ?></span>
									</div>
								<?php } ?>
								<div class="image edit-icon">
									<?php
									$vm_edit_url = add_query_arg(
										array(
											'page'   => 'surelywp_toolkit_panel',
											'tab'    => 'surelywp_tk_vm_settings',
											'action' => 'edit_vm',
											'vm_setting_id' => $key,
										),
										admin_url( 'admin.php' )
									);
									?>
									<a href="<?php echo esc_url( $vm_edit_url ); ?>">
										<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . '/assets/images/list_edit.svg' ); ?>" alt="<?php esc_attr_e( 'list-edit', 'surelywp-toolkit' ); ?>">
									</a>
								</div>
								<div class="image remove-icon">
									<?php
									$vm_remove_url = add_query_arg(
										array(
											'page'   => 'surelywp_toolkit_panel',
											'tab'    => 'surelywp_tk_vm_settings',
											'action' => 'remove_vm',
											'vm_setting_id' => $key,
										),
										admin_url( 'admin.php' )
									);
									?>
									<a id="remove-associate-vacation-mode" href="<?php echo esc_url( $vm_remove_url ); ?>">
										<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . '/assets/images/remove-icon.svg' ); ?>" alt="<?php esc_attr_e( 'list_edit', 'surelywp-toolkit' ); ?>">
									</a>
								</div>
							</div>
							<?php
						}
					}
					?>
				</td>
			</tr>
			<tr class="surelywp-field-label add-new-toolkit">
				<td>
					<div class="addons-btn-wrap">
						<?php
						$vm_setting_id = surelywp_tk_generate_random_id();
						$vm_add_url    = add_query_arg(
							array(
								'page'          => 'surelywp_toolkit_panel',
								'tab'           => 'surelywp_tk_vm_settings',
								'action'        => 'add_new_vm',
								'vm_setting_id' => $vm_setting_id,
							),
							admin_url( 'admin.php' )
						);
						?>
						<a href="<?php echo esc_url( $vm_add_url ); ?>" class="button-primary">
							<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . '/assets/images/Add_new.svg' ); ?>" alt="<?php esc_attr_e( 'add-new', 'surelywp-toolkit' ); ?>"><?php esc_html_e( 'Add New Vacation', 'surelywp-toolkit' ); ?>
						</a>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="associative-vacation-delete modal">
		<div class="modal-content">
			<span class="close-button close-modal-button">×</span>
			<h4><?php esc_html_e( 'Are you sure you want to delete this vacation configuration?', 'surelywp-toolkit' ); ?></h4>
			<div class="modal-btn-wrap">
				<div class="text">
					<?php esc_html_e( 'This will permanently remove the vacation and all of its settings and configuration. This action cannot be undone.', 'surelywp-toolkit' ); ?>
				</div>
				<div class="modal-btns">
					<a href="javascript:void(0)" id="cancel-as-vacation-delete" class="btn-primary button-2 close-modal-button"><?php esc_html_e( 'Cancel', 'surelywp-toolkit' ); ?></a>
					<a href="javascript:void(0)" id="confirm-as-vacation-delete" class="confirm-as-vacation-delete btn-secondary button-1"><?php esc_html_e( 'Delete Vacation', 'surelywp-toolkit' ); ?></a>				
				</div>
			</div>
		</div>
	</div>
	<?php
} elseif ( isset( $_GET['action'] ) && ( 'add_new_vm' === $_GET['action'] || 'edit_vm' === $_GET['action'] ) ) {

	$vm_setting_id = 0;
	if ( isset( $_GET['vm_setting_id'] ) && ! empty( $_GET['vm_setting_id'] ) ) {
		$vm_setting_id = sanitize_text_field( wp_unslash( $_GET['vm_setting_id'] ) );
		if ( ! empty( $settings_options ) ) {
			$vm_option = $settings_options[ $vm_setting_id ] ?? array();
		}
		$vm_option_key = $addons_option_key . '[' . $vm_setting_id . ']';
	}
	?>
	<table id="toolkit-settings-tab" class="form-table surelywp-ric-settings-box toolkit-templates-table">
		<tbody>
			<tr class="surelywp-field-label">
				<td>
					<h4 class="heading-text"><?php echo esc_html_e( 'Vacation Title', 'surelywp-toolkit' ); ?></h4>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Vacation Title', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter a title for the vacation.', 'surelywp-toolkit' ); ?></label>
						<?php $vm_title = ( isset( $vm_option['vm_title'] ) ) ? $vm_option['vm_title'] : ''; ?>
						<input type="text" class="widefat" name="<?php echo esc_attr( $vm_option_key . '[vm_title]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $vm_title ); ?>">
						<input type="hidden" id="surelywp-tk-vm-vm-setting-id" class="widefat" name="<?php echo esc_attr( $vm_option_key . '[vm_setting_id]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $vm_setting_id ); ?>">
					</div>
				</td>
			</tr>
			<tr class="surelywp-field-label">
				<td>
					<h4 class="heading-text"><?php echo esc_html_e( 'Vacation Status', 'surelywp-toolkit' ); ?></h4>
					<div class="form-control">
						<div class="input-label"><?php echo esc_html_e( 'Enable Vacation', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose enable or disable this vacation.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge" onclick="">
							<input type="checkbox" id="surelywp-tk-vm-vm-settings-status" name="<?php echo esc_attr( $vm_option_key . '[status]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $vm_option['status'] ) ) ? checked( $vm_option['status'], 1, true ) : ''; ?> size="10" />
							<input type="hidden" name="<?php echo esc_attr( $vm_option_key . '[vm_update_options]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'updated' ); ?>" />
							<?php $surelywp_vm_option_nonce = wp_create_nonce( 'surelywp_vm_option_nonce' ); ?>
							<input type="hidden" name="surelywp_vm_option_nonce" value="<?php echo $surelywp_model->surelywp_escape_attr( $surelywp_vm_option_nonce ); ?>" />
							<span>
								<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
				</td>
			</tr>
			<?php
			if ( isset( $vm_option['status'] ) && '1' === $vm_option['status'] ) {
				$hidden_class = '';
			} else {
				$hidden_class = 'hidden';
			}
			?>
			<tr class="surelywp-field-label <?php echo esc_html( $hidden_class ); ?>" id="surelywp-tk-vm-product-selection-settings">
				<td>
					<hr>
					<h4 class="heading-text"><?php echo esc_html_e( 'Products For Vacation', 'surelywp-toolkit' ); ?></h4>
					<div id="surelywp-tk-vm-product-selection-settings-div">
						<div class="form-control">
							<div class="input-label"><?php esc_html_e( 'Choose Products For Vacation', 'surelywp-toolkit' ); ?></div>
							<label><?php esc_html_e( 'Choose which products to include in this vacation.', 'surelywp-toolkit' ); ?></label>
							<select id="toolkit-product-type" name="<?php echo esc_attr( $vm_option_key . '[vm_product_type]' ); ?>">
								<option <?php echo ( isset( $vm_option['vm_product_type'] ) && 'all' === $vm_option['vm_product_type'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'all' ); ?>"><?php esc_html_e( 'All Products', 'surelywp-toolkit' ); ?></option>
								<option <?php echo ( isset( $vm_option['vm_product_type'] ) && 'specific' === $vm_option['vm_product_type'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'specific' ); ?>"><?php esc_html_e( 'Specific Products', 'surelywp-toolkit' ); ?>
								</option>
								<option <?php echo ( isset( $vm_option['vm_product_type'] ) && 'specific_collection' === $vm_option['vm_product_type'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'specific_collection' ); ?>"><?php esc_html_e( 'Specific Product Collections', 'surelywp-toolkit' ); ?></option>
							</select>
						</div>
						<div class="form-control multi-selection <?php echo ( isset( $vm_option['vm_product_type'] ) && 'specific' === $vm_option['vm_product_type'] ) ? esc_html( $hidden_class ) : 'hidden'; ?>" id="specific-product-selection-div">
							<div class="input-label"><?php esc_html_e( 'Select Specific Products', 'surelywp-toolkit' ); ?></div>
							<label><?php esc_html_e( 'Select the SureCart products you want to associate with this service.', 'surelywp-toolkit' ); ?></label>
							<select multiple="multiple" id="surelywp-tk-vm-specific-products" class="customer-role" name="<?php echo esc_attr( $vm_option_key . '[vm_products][]' ); ?>">
								<?php
								$products = surelywp_tk_get_sc_all_products();
								if ( ! empty( $products ) && ! is_wp_error( $products ) ) {
									foreach ( $products as $product ) {
										?>
										<option <?php echo isset( $vm_option['vm_products'] ) && in_array( $product->id, (array) $vm_option['vm_products'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $product->id ); ?>">
										<?php echo $surelywp_model->surelywp_escape_attr( $product->name ); ?>
										</option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<?php
							$product_collection_obj = SureCart\Models\ProductCollection::get();
						?>
						<div class="form-control multi-selection <?php echo ( isset( $vm_option['vm_product_type'] ) && 'specific_collection' === $vm_option['vm_product_type'] ) ? esc_html( $hidden_class ) : 'hidden'; ?>" id="specific-product-collection-selection-div">
							<div class="input-label"><?php esc_html_e( 'Select Specific Product Collections', 'surelywp-toolkit' ); ?></div>
							<label><?php esc_html_e( 'Select the SureCart product collection you want to associate all of its products with this service.', 'surelywp-toolkit' ); ?></label>
							<select multiple="multiple" class="customer-role" name="<?php echo esc_attr( $vm_option_key . '[vm_products_collections][]' ); ?>">
								<?php
								if ( ! is_wp_error( $product_collection_obj ) && ! empty( $product_collection_obj ) ) {
									foreach ( $product_collection_obj as $collection ) {
										?>
										<option <?php echo isset( $vm_option['vm_products_collections'] ) && in_array( $collection->id, (array) $vm_option['vm_products_collections'], true ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $collection->id ); ?>"><?php echo esc_html( $collection->name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					</div>
				</td>
			</tr>
			<tr class="surelywp-field-label <?php echo esc_html( $hidden_class ); ?>" id="surelywp-tk-vm-priority">
				<td>
					<hr>
					<h4 class="heading-text"><?php echo esc_html_e( 'Vacation Priority', 'surelywp-toolkit' ); ?></h4>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Priority', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Set the priority for the vacation. A higher priority ensures it is applied if there are multiple vacations active at the same time.', 'surelywp-toolkit' ); ?></label>
						<?php
						$vacation_priority = $vm_option['vm_priority'] ?? '';
						?>
						<input type="number" class="widefat" min="1" max="<?php echo esc_html( PHP_INT_MAX ); ?>" name="<?php echo esc_attr( $vm_option_key . '[vm_priority]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $vacation_priority ); ?>">
					</div>
				</td>
			</tr>
			<tr class="surelywp-field-label <?php echo esc_html( $hidden_class ); ?>" id="surelywp-tk-vm-notice-settings">
				<td>
					<hr>
					<h4 class="heading-text"><?php echo esc_html_e( 'Vacation Notice', 'surelywp-toolkit' ); ?></h4>
					<div class="form-control" id="vm-notice-message">
							<div class="input-label"><?php esc_html_e( 'Vacation Notice Message', 'surelywp-toolkit' ); ?></div>
							<label><?php esc_html_e( 'Write a custom message to display during vacation mode. You can use the {vacation_start_date} and {vacation_end_date} variables to automatically insert the dates into your notice. NOTE: If the vacation schedule feature is not used, please remember to remove the variables since they would not be relevant.', 'surelywp-toolkit' ); ?></label>
							<?php
							$notice_message = ( isset( $vm_option['notice_message'] ) ) ? $vm_option['notice_message'] : esc_html__( 'Our store will be closed from {vacation_start_date} to {vacation_end_date}. Orders placed during this time will be processed when we return. Thank you for your patience and understanding!', 'surelywp-toolkit' );
							wp_editor(
								wp_kses_post( $notice_message ), // Initial content, you can fetch saved content here.
								'vm-notice-msg', // Editor ID, must be unique.
								array(
									'textarea_name' => esc_attr( $vm_option_key . '[notice_message]' ), // Name attribute of the textarea.
									'textarea_rows' => 5, // Number of rows.
									'media_buttons' => false, // Show media button in the editor.
									'tinymce'       => array(
										'toolbar1'      => 'bold,italic,underline,|,bullist,numlist,|,link,|,undo,redo',
										'toolbar2'      => '', // Leave empty if you don't want a second toolbar.
										'content_style' => 'body, p, div { font-family: Poppins, sans-serif; color: #4c5866;}', // Properly escape font-family.
									),
									'quicktags'     => array(
										'buttons' => 'strong,em,link,ul,ol,li,quote',
									),
								)
							);
							?>
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Show Sitewide Banner Notice During Vacation', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose to display a sitewide banner across the top of your website when the vacation is active.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-vm-is-show-banner" name="<?php echo esc_attr( $vm_option_key . '[is_show_banner]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $vm_option['is_show_banner'] ) ) ? checked( $vm_option['is_show_banner'], 1, true ) : ''; ?> size="10" />
							<span>
								<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Show Notice On Product Pages During Vacation', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose to display a message on all SureCart product pages for selected products whe the vacation is active.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge" onclick="">
						<?php
						if ( ! isset( $vm_option['vm_update_options'] ) ) {
							$vm_option['is_show_notice'] = '1';
						}
						?>
						<input type="checkbox" id="surelywp-tk-vm-is-show-notice" name="<?php echo esc_attr( $vm_option_key . '[is_show_notice]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $vm_option['is_show_notice'] ) ) ? checked( $vm_option['is_show_notice'], 1, true ) : ''; ?> size="10" />
							<span>
								<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
					<div class="vm-notice-options <?php echo isset( $vm_option['is_show_notice'] ) ? '' : 'hidden'; ?>" id="vm-notice-options">
						<div class="form-control">
							<div class="input-label"><?php esc_html_e( 'Vacation Notice Display Location On Product Pages', 'surelywp-toolkit' ); ?></div>
							<label><?php esc_html_e( 'Choose where to show the vacation notice on default SureCart product pages based on relative position to the other elements.', 'surelywp-toolkit' ); ?></label>
							<select id="toolkit-vm-notice-location" name="<?php echo esc_attr( $vm_option_key . '[notice_position]' ); ?>">
								
								<option <?php echo ( isset( $vm_option['notice_position'] ) && $vm_option['notice_position'] == 'price' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'price' ); ?>"><?php esc_html_e( 'After Price', 'surelywp-toolkit' ); ?></option>

								<option <?php echo ( isset( $vm_option['notice_position'] ) && $vm_option['notice_position'] == 'price-choice' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'price-choice' ); ?>"><?php esc_html_e( 'After Pricing Options', 'surelywp-toolkit' ); ?></option>

								<option <?php echo ( isset( $vm_option['notice_position'] ) && $vm_option['notice_position'] == 'title' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'title' ); ?>"><?php esc_html_e( 'After Title', 'surelywp-toolkit' ); ?></option>

								<option <?php echo ( isset( $vm_option['notice_position'] ) && $vm_option['notice_position'] == 'description' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'description' ); ?>"><?php esc_html_e( 'After Description', 'surelywp-toolkit' ); ?></option>

								<option <?php echo ( isset( $vm_option['notice_position'] ) && $vm_option['notice_position'] == 'quantity-selector' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'quantity-selector' ); ?>"><?php esc_html_e( 'After Quantity Selector', 'surelywp-toolkit' ); ?></option>

								<option <?php echo ( isset( $vm_option['notice_position'] ) && $vm_option['notice_position'] == 'buy-button' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'buy-button' ); ?>"><?php esc_html_e( 'After Add To Cart And Buy Now Buttons', 'surelywp-toolkit' ); ?></option>
							</select>
						</div>
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Vacation Notice Shortcode', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'If you are using a page builder or a custom layout instead of the default SureCart product pages, you can add the vacation notice by placing the [surelywp_vacation_notice] shortcode in your content. The vacation notice will dynamically show or hide based on whether vacation mode is enabled.', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Vacation Notice Block', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'If you are using the WordPress block editor, you can display the vacation notice by adding the Vacation Notice block to any page. The vacation notice will dynamically show or hide based on whether vacation mode is enabled.', 'surelywp-toolkit' ); ?></label>
					</div>
					<?php if ( defined( 'SURELYWP_SERVICES' ) ) { ?>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Show Vacation Notice On Individual Services', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'When using the Services for SureCart addon, enable this setting to display the vacation notice on individual Services linked to products included in vacation mode. Customers viewing a Service tied to a vacation-enabled product will see the vacation notice, ensuring clear communication to keep them informed of the active vacation status.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" name="<?php echo esc_attr( $vm_option_key . '[show_notice_on_service]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $vm_option['show_notice_on_service'] ) ) ? checked( $vm_option['show_notice_on_service'], 1, true ) : ''; ?> size="10" />
							<span>
								<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
					<?php } ?>
					<?php if ( defined( 'SURELYWP_SUPPORT_PORTAL' ) ) { ?>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Show Vacation Notice On Individual Support Tickets', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'When using the Support Portal for SureCart addon, enable this setting to display the vacation notice on individual support tickets linked to products included in vacation mode. Customers viewing a support ticket tied to a vacation-enabled product will see the vacation notice, ensuring clear communication to keep them informed of the active vacation status.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" name="<?php echo esc_attr( $vm_option_key . '[show_notice_on_support]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $vm_option['show_notice_on_support'] ) ) ? checked( $vm_option['show_notice_on_support'], 1, true ) : ''; ?> size="10" />
							<span>
								<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
					<?php } ?>
				</td>
			</tr>
			<tr class="surelywp-field-label <?php echo esc_html( $hidden_class ); ?>" id="surelywp-tk-vm-purchase-limittations-settings">
				<td>
					<hr>
					<h4 class="heading-text"><?php echo esc_html_e( 'Purchase Limitations During Vacation', 'surelywp-toolkit' ); ?></h4>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Hide “Add To Cart” And “Buy Now” Buttons During Vacation Mode', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose to hide the add to cart button and buy now button on SureCart product pages when vacation mode is enabled.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge" onclick="">
						<?php
						if ( ! isset( $vm_option['vm_update_options'] ) ) {
							$vm_option['is_hide_buy_buttons'] = '1';
						}
						?>
						<input type="checkbox" id="surelywp-tk-vm-is-hide-buy-buttons" name="<?php echo esc_attr( $vm_option_key . '[is_hide_buy_buttons]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $vm_option['is_hide_buy_buttons'] ) ) ? checked( $vm_option['is_hide_buy_buttons'], 1, true ) : ''; ?> size="10" />
							<span>
								<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Disable Checkout During Vacation Mode', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose to disable the entire checkout process when Vacation Mode is enabled. If "Choose Products For Vacation" is set to all products, customers attempting to access the checkout page will be redirected to the home page. When Vacation Mode is applied to specific products or collections, purchases for those items are disabled. Customers cannot add them to the cart, and clicking the "Buy Now" button redirects them to the home page. This feature ensures that purchases are paused in line with your Vacation Mode settings.', 'surelywp-toolkit' ); ?></label>
						<?php
						if ( ! isset( $vm_option['vm_update_options'] ) ) {
							$vm_option['is_disable_checkout'] = '1';
						}
						?>
						<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-vm-is-disable-checkout" name="<?php echo esc_attr( $vm_option_key . '[is_disable_checkout]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $vm_option['is_disable_checkout'] ) ) ? checked( $vm_option['is_disable_checkout'], 1, true ) : ''; ?> size="10" />
							<span>
								<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
				</td>
			</tr>
			<tr class="surelywp-field-label <?php echo esc_html( $hidden_class ); ?>" id="surelywp-tk-vm-vacation-schedule-settings">
				<td>
					<hr>
					<h4 class="heading-text"><?php echo esc_html_e( 'Vacation Schedule', 'surelywp-toolkit' ); ?></h4>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Schedule Vacation', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Use a schedule to start and end the vacation at specific dates and times.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-vm-schedule-status" name="<?php echo esc_attr( $vm_option_key . '[schedule_status]' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $vm_option['schedule_status'] ) ) ? checked( $vm_option['schedule_status'], 1, true ) : ''; ?> size="10" />
							<span>
								<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
					<div class="vm-schedule-options <?php echo ( isset( $vm_option['schedule_status'] ) && '1' === $vm_option['schedule_status'] ) ? '' : 'hidden'; ?>" id="vm-schedule-options"> 
						<div class="form-control">
							<div class="input-label"><?php esc_html_e( 'Vacation Schedule Type', 'surelywp-toolkit' ); ?></div>
							<label><?php esc_html_e( 'Choose the type of schedule for this vacation.', 'surelywp-toolkit' ); ?></label>
							<select id="vacation-schedule-type-selection" name="<?php echo esc_attr( $vm_option_key . '[vm_schedule_type]' ); ?>">
								<option <?php echo ( isset( $vm_option['vm_schedule_type'] ) && 'fixed_time' === $vm_option['vm_schedule_type'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'fixed_time' ); ?>"><?php esc_html_e( 'Fixed Based On Date/Time', 'surelywp-toolkit' ); ?></option>
								<option <?php echo ( isset( $vm_option['vm_schedule_type'] ) && 'recurring_time' === $vm_option['vm_schedule_type'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'recurring_time' ); ?>"><?php esc_html_e( 'Recurring Based On Days Of The Week', 'surelywp-toolkit' ); ?></option>
							</select>
						</div>
						<?php
						if ( ! isset( $vm_option['vm_update_options'] ) ) {
							$vm_option['vm_schedule_type'] = 'fixed_time';
						}
						?>
						<div class="vm-fixed-time-settings <?php echo ( isset( $vm_option['vm_schedule_type'] ) && 'fixed_time' === $vm_option['vm_schedule_type'] ) ? '' : 'hidden'; ?>" id="vm-fixed-time-settings">
							<div class="form-control">
								<div class="input-label"><?php esc_html_e( 'Vacation Start Date/Time', 'surelywp-toolkit' ); ?></div>
								<label><?php printf( esc_html__( 'Select a date and time for the vacation to start.', 'surelywp-toolkit' ), date( 'jS \of F Y h:i:s A' ) ); ?></label>
								<?php
								$fixed_start_time = ( isset( $vm_option['fixed_start_time'] ) ) ? $vm_option['fixed_start_time'] : '';
								?>
								<input type="datetime-local" class="widefat schedule-time" name="<?php echo esc_attr( $vm_option_key . '[fixed_start_time]' ); ?>" value="<?php echo esc_attr( $fixed_start_time ); ?>">
							</div>
							<div class="form-control">
								<div class="input-label"><?php esc_html_e( 'Vacation End Date/Time', 'surelywp-toolkit' ); ?></div>
								<label><?php printf( esc_html__( 'Select a date and time for the vacation to end.', 'surelywp-toolkit' ) ); ?></label>
								<?php
									$fixed_end_time = ( isset( $vm_option['fixed_end_time'] ) ) ? $vm_option['fixed_end_time'] : '';
								?>
								<input type="datetime-local" class="widefat schedule-time" name="<?php echo esc_attr( $vm_option_key . '[fixed_end_time]' ); ?>" value="<?php echo esc_attr( $fixed_end_time ); ?>">
							</div>
						</div>
						<div class="vm-recurring-time-settings <?php echo ( isset( $vm_option['vm_schedule_type'] ) && 'recurring_time' === $vm_option['vm_schedule_type'] ) ? '' : 'hidden'; ?>" id="vm-recurring-time-settings">
							<div class="form-control">
								<div class="input-label"><?php esc_html_e( 'Vacation Start Day', 'surelywp-toolkit' ); ?></div>
								<label><?php esc_html_e( 'Select a day of the week for the vacation to start.', 'surelywp-toolkit' ); ?></label>
								<select id="vm-schedule-start-day" name="<?php echo esc_attr( $vm_option_key . '[vacation_start_day]' ); ?>">
									<?php foreach ( $vacation_days as $key => $value ) { ?>
										<option <?php echo ( isset( $vm_option['vacation_start_day'] ) && (string) $key === $vm_option['vacation_start_day'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="form-control">
								<div class="input-label"><?php esc_html_e( 'Vacation Start Time', 'surelywp-toolkit' ); ?></div>
								<label><?php printf( esc_html__( 'Select a time on the selected day of the week for the vacation to start.', 'surelywp-toolkit' ) ); ?></label>
								<?php
								$recurring_start_time = ( isset( $vm_option['recurring_start_time'] ) ) ? $vm_option['recurring_start_time'] : '';
								?>
								<input type="time" class="widefat schedule-time" name="<?php echo esc_attr( $vm_option_key . '[recurring_start_time]' ); ?>" value="<?php echo esc_attr( $recurring_start_time ); ?>">
							</div>
							<div class="form-control">
								<div class="input-label"><?php esc_html_e( 'Vacation End Day', 'surelywp-toolkit' ); ?></div>
								<label><?php esc_html_e( 'Select a day of the week for the vacation to end.', 'surelywp-toolkit' ); ?></label>
								<select id="vm-schedule-start-end" name="<?php echo esc_attr( $vm_option_key . '[vacation_end_day]' ); ?>">
									<?php foreach ( $vacation_days as $key => $value ) { ?>
										<option <?php echo ( isset( $vm_option['vacation_end_day'] ) && (string) $key === $vm_option['vacation_end_day'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="form-control">
								<div class="input-label"><?php esc_html_e( 'Vacation End Time', 'surelywp-toolkit' ); ?></div>
								<label><?php printf( esc_html__( 'Select a time on the selected day of the week for the vacation to end.', 'surelywp-toolkit' ) ); ?></label>
								<?php
									$recurring_end_time = ( isset( $vm_option['recurring_end_time'] ) ) ? $vm_option['recurring_end_time'] : '';
								?>
								<input type="time" class="widefat schedule-time" name="<?php echo esc_attr( $vm_option_key . '[recurring_end_time]' ); ?>" value="<?php echo esc_attr( $recurring_end_time ); ?>">
							</div>
						</div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
<?php } ?>