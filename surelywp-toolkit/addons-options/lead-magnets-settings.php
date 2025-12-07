<?php
/**
 * Lead Magnets Settings
 *
 * @author  Surelywp
 * @package Toolkit For SureCart
 * @since 1.3
 */

global $surelywp_model;

$default_sub_form_fields = Surelywp_Tk_Lm_Admin::surelywp_tk_get_default_form_fields();

?>
<table class="form-table surelywp-ric-settings-box lead-magnets-settings">
	<tbody>
	<tr class="surelywp-field-label">
			<td>
				<div class="form-control">
					<div class="lead-magnets-settings-tabs settings-tabs">
						<div class="surelywp-tab">
							<a href="javascript:void(0)" id="lm-products-tab" class="surelywp-btn"><?php esc_html_e( 'Products', 'surelywp-toolkit' ); ?></a>
						</div>
						<div class="surelywp-tab">
							<a href="javascript:void(0)" id="lm-settings-tab" class="surelywp-btn"><?php esc_html_e( 'Settings', 'surelywp-toolkit' ); ?></a>
						</div>
						<div class="surelywp-tab">
							<a href="javascript:void(0)" id="lm-fields-tab" class="surelywp-btn"><?php esc_html_e( 'Fields', 'surelywp-toolkit' ); ?></a>
						</div>
						<div class="surelywp-tab">
							<a href="javascript:void(0)" id="lm-verification-tab" class="surelywp-btn"><?php esc_html_e( 'Verification Email', 'surelywp-toolkit' ); ?></a>
						</div>
						<div class="surelywp-tab">
							<a href="javascript:void(0)" id="lm-customer-dashboard-tab" class="surelywp-btn"><?php esc_html_e( 'Customer Dashboard', 'surelywp-toolkit' ); ?></a>
						</div>
					</div>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label lm-products-settings tab-settings  hidden" id="lm-products-settings">
			<td>
				<h4 class="heading-text first-heading"><?php esc_html_e( 'Lead Magnet Products', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Enable Lead Magnets', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose enable or disable the lead magnets feature.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="is-enable-lead-magnets" name="<?php echo esc_attr( $addons_option_key ) . '[is_enable_lead_magnets]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['is_enable_lead_magnets'] ) ) ? checked( $settings_options['is_enable_lead_magnets'], 1, true ) : ''; ?> size="10" />
						<input type="hidden" name="<?php echo $addons_option_key . '[tk_lm_update_options]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'updated' ); //phpcs:ignore ?>" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="lead-magnets-product-options <?php echo ! isset( $settings_options['is_enable_lead_magnets'] ) ? 'hidden' : ''; ?>" id="lead-magnets-product-options">
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Choose Products For Lead Magnets', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Select which products you want to convert into lead magnets. Note: Only free products with a single pricing option set to zero can be used as lead magnets. Products designated as lead magnets will hide the "Add to Cart" and "Buy Now" buttons, replacing them with an email opt-in button.', 'surelywp-toolkit' ); ?></label>
						<select name="<?php echo esc_attr( $addons_option_key . '[lead_magnets_product]' ); ?>" id="lm-products-selections">
							<option <?php echo ( isset( $settings_options['lead_magnets_product'] ) && $settings_options['lead_magnets_product'] == 'all' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'all' ); ?>"><?php esc_html_e( 'All Products', 'surelywp-toolkit' ); ?></option>

							<option <?php echo ( isset( $settings_options['lead_magnets_product'] ) && $settings_options['lead_magnets_product'] == 'specific' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'specific' ); ?>"><?php esc_html_e( 'Specific Products', 'surelywp-toolkit' ); ?></option>

							<option <?php echo ( isset( $settings_options['lead_magnets_product'] ) && $settings_options['lead_magnets_product'] == 'specific_collection' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'specific_collection' ); ?>"><?php esc_html_e( 'Specific Product Collections', 'surelywp-toolkit' ); ?></option>
						</select>
					</div>
					<div class="form-control multi-selection <?php echo 'specific' === $settings_options['lead_magnets_product'] ? '' : 'hidden'; ?>" id="specific-product-selection-div">
						<div class="input-label"><?php esc_html_e( 'Select Specific Products', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Select the SureCart products that you want to change to lead magnets. Only SureCart products that have a price of $0 will appear here as available selections.', 'surelywp-toolkit' ); ?></label>
						<select multiple="multiple" class="customer-role" name="<?php echo esc_attr( $addons_option_key . '[lm_products][]' ); ?>">
							<?php
							$products = SureCart\Models\Product::where(
								array(
									'archived' => false,
								)
							)->get();
							if ( ! empty( $products ) && ! is_wp_error( $products ) ) {
								foreach ( $products as $product ) {

									if ( $product->metrics->min_price_amount || null === $product->metrics->min_price_amount ) {
										continue;
									}
									?>
									<option <?php echo isset( $settings_options['lm_products'] ) && in_array( $product->id ?? '', (array) $settings_options['lm_products'], true ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $product->id ?? '' ); ?>"><?php echo esc_html( $product->name ?? '' ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<?php
					$product_collection_obj = SureCart\Models\ProductCollection::get();
					?>
					<div class="form-control multi-selection <?php echo 'specific_collection' === $settings_options['lead_magnets_product'] ? '' : 'hidden'; ?>" id="specific-product-collection-selection-div">
						<div class="input-label"><?php esc_html_e( 'Select Specific Product Collections', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Select the SureCart product collections that you want to change all of its products to lead magnets.', 'surelywp-toolkit' ); ?></label>
						<select multiple="multiple" class="customer-role" name="<?php echo esc_attr( $addons_option_key . '[product_collection][]' ); ?>">
							<?php
							if ( ! empty( $product_collection_obj ) ) {
								foreach ( $product_collection_obj as $collection ) {
									?>
									<option <?php echo isset( $settings_options['product_collection'] ) && in_array( $collection->id, (array) $settings_options['product_collection'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $collection->id ?? '' ); ?>"><?php echo esc_html( $collection->name ?? '' ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label subscription-form-settings tab-settings hidden" id="subscription-form-settings">
			<td>
				<h4 class="heading-text first-heading"><?php esc_html_e( 'Subscription Form Method', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Subscription Form Method', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose how the subscription form is displayed on the product page. Select Popup Form to show a button that opens the form in a popup. Select Inline Form to display the form directly on the page.', 'surelywp-toolkit' ); ?></label>
					<select id="sub-form-method" name="<?php echo esc_attr( $addons_option_key . '[sub_form_method]' ); ?>">
						<option <?php echo ( isset( $settings_options['sub_form_method'] ) && 'popup_form' === $settings_options['sub_form_method'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'popup_form' ); ?>"><?php esc_html_e( 'Popup Form', 'surelywp-toolkit' ); ?></option>
						<option <?php echo ( isset( $settings_options['sub_form_method'] ) && 'inline_form' === $settings_options['sub_form_method'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'inline_form' ); ?>"><?php esc_html_e( 'Inline Form', 'surelywp-toolkit' ); ?></option>
					</select>
				</div>
				<div class="popup-form-settings <?php echo 'popup_form' !== $settings_options['sub_form_method'] ? 'hidden' : ''; ?>" id="popup-form-settings">
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Popup Button Text', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter the text to display on the popup trigger button.', 'surelywp-toolkit' ); ?></label>
						<input type="text" class="widefat" name="<?php echo $addons_option_key . '[popup_btn_text]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['popup_btn_text'] ); //phpcs:ignore?>">
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Popup Button Location', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose where to display the popup subscription button on the product page.', 'surelywp-toolkit' ); ?></label>
						<select name="<?php echo esc_attr( $addons_option_key . '[popup_btn_position]' ); ?>">
							<option <?php echo ( isset( $settings_options['popup_btn_position'] ) && $settings_options['popup_btn_position'] == 'title' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'title' ); ?>"><?php esc_html_e( 'After Title', 'surelywp-toolkit' ); ?></option>
							<option <?php echo ( isset( $settings_options['popup_btn_position'] ) && $settings_options['popup_btn_position'] == 'description' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'description' ); ?>"><?php esc_html_e( 'After Description', 'surelywp-toolkit' ); ?></option>
						</select>
					</div>
				</div>
				<div class="inline-form-settings <?php echo 'inline_form' !== $settings_options['sub_form_method'] ? 'hidden' : ''; ?>" id="inline-form-settings">
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Inline Form Location', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose where to display the inline subscription form on the product page.', 'surelywp-toolkit' ); ?></label>
						<select name="<?php echo esc_attr( $addons_option_key . '[inline_form_position]' ); ?>">
							<option <?php echo ( isset( $settings_options['inline_form_position'] ) && $settings_options['inline_form_position'] == 'description' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'description' ); ?>"><?php esc_html_e( 'After Description', 'surelywp-toolkit' ); ?></option>
							<option <?php echo ( isset( $settings_options['inline_form_position'] ) && $settings_options['inline_form_position'] == 'title' ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'title' ); ?>"><?php esc_html_e( 'After Title', 'surelywp-toolkit' ); ?></option>
						</select>
					</div>		
				</div>
				<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Subscription Button/Form Shortcode', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'If you are not using the default SureCart product pages and are displaying your products on other pages or post types, you can place the shortcode [surelywp_lead_magnet_button] anywhere in your content. This shortcode will automatically display either the popup trigger button or the full inline form based on your selected form method.', 'surelywp-toolkit' ); ?></label>
						<br />
						<label><?php esc_html_e( 'If used on a non-product page, the shortcode requires the product_id attribute to be included. You can find the product ID by going to SureCart > Products and checking the URL of the individual product page. The product ID is visible in the URL slug in your browser address bar. An example of the shortcode would be: [surelywp_lead_magnet_button product_id="5420a0f8-06d0-491f-9fcf-987b838c406c"]', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Subscription Button/Form Block', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'If you are using the WordPress block editor, you can display the subscription form or popup button by adding the Lead Magnet Subscription block to any page. The block will automatically show either a popup trigger button or the full inline form, depending on the selected Subscription Form Method in the settings.', 'surelywp-toolkit' ); ?></label>
						<br />
						<label><?php esc_html_e( 'If you\'re using this block on a page that is not a SureCart product page, remember to include the Product ID. You can find the product ID by going to SureCart > Products and checking the URL of the product page.', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Subscription Button CSS Class For Page Builder Integration', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'When using the block editor or any other page builder, you can easily create a subscription button by utilizing the default Button block or button element in your favorite page builder. To trigger the lead magnet popup form, simply add the class surelywp-lead-magnet-button in the Additional CSS Class field within the block settings. This will enable the button to function as a trigger to open the subscription form related to the product.', 'surelywp-toolkit' ); ?></label>
						<br />
						<label><?php esc_html_e( 'If you\'re placing the button on a non-product page, you\'ll need to include an additional class that specifies the product ID. In this case, add both surelywp-lead-magnet-button and lm-product-id-{product_id}, where {product_id} is replaced with the ID of the specific product for which you want to trigger the subscription form. This ensures the correct product\'s subscription form will appear when visitors click the button. You can find the product ID by going to SureCart > Products and checking the URL of the product page.', 'surelywp-toolkit' ); ?></label>
					</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Subscription Form Header Text', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enter the heading text to display at the top of the subscription form.', 'surelywp-toolkit' ); ?></label>
					<input type="text" class="widefat" name="<?php echo $addons_option_key . '[sub_form_header_text]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['sub_form_header_text'] ); //phpcs:ignore?>">
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Submit Button Text', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enter the heading text to display at the top of the subscription form.', 'surelywp-toolkit' ); ?></label>
					<input type="text" class="widefat" name="<?php echo $addons_option_key . '[submit_button_text]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['submit_button_text'] ); //phpcs:ignore?>">
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Existing Customer Message', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enter the message to show if the email entered already matches an existing order for the product.', 'surelywp-toolkit' ); ?></label>
					<?php
					// Add the TinyMCE editor script.
					wp_editor(
						$settings_options['customer_exists_message'] ?? '', // Initial content, you can fetch saved content here.
						'existing-customer-msg', // Editor ID, must be unique.
						array(
							'textarea_name'  => $addons_option_key . '[customer_exists_message]', // Name attribute of the textarea.
							'editor_class'   => 'existing-customer-msg',
							'textarea_rows'  => 5, // Number of rows.
							'media_buttons'  => false, // Show media button in the editor.
							'default_editor' => 'visual',
							'tinymce'        => array(
								'toolbar1'      => 'bold,italic,underline,|,bullist,numlist,|,link,|,undo,redo',
								'toolbar2'      => '', // Leave empty if you don't want a second toolbar.
								'content_style' => 'body, p, div { font-family: Poppins, sans-serif; color: #4c5866;}', // Properly escape font-family.
							),
							'quicktags'      => array(
								'buttons' => 'strong,em,link,ul,ol,li,quote',
							),
						)
					);
					?>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label subscription-form-fields-settings tab-settings hidden" id="subscription-form-fields-settings">
			<td>
				<h4 class="heading-text first-heading"><?php esc_html_e( 'Subscription Form Fields', 'surelywp-toolkit' ); ?></h4>
				<div class="surelywp-sortable-fields" id="sub-form-fields-fields">
					<?php

					if ( isset( $settings_options['sub_form_fields'] ) ) {
						$sub_form_fields = $settings_options['sub_form_fields'];
					} else {
						$sub_form_fields = $default_sub_form_fields; // Default settings.
					}

					foreach ( $sub_form_fields as $field_key => $field ) {

						$field_option_key = $addons_option_key . '[sub_form_fields][' . $field_key . ']';
						?>
						<div class="surelywp-sortable-field surelywp-list-tab <?php echo esc_html( $field_key ); ?>">
							<input type="hidden" class="sub-form-field-position" name="<?php echo esc_attr( $field_option_key. '[position]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $field['position'] ); ?>">
							<input type="hidden" class="sub-form-field-heading" name="<?php echo esc_attr( $field_option_key. '[heading]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $field['heading'] ); ?>">
							<div class="surelywp-sortable-field-top">
								<div class="top-left">
									<div class="surelywp-sortable-field-toogle-btn">
										<img class="column-open-icon" src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/open-down-icon.svg' ); ?>" alt="<?php esc_attr_e( 'open', 'surelywp-toolkit' ); ?>">
										<img class="column-close-icon hidden" src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/open-up-icon.svg' ); ?>" alt="<?php esc_attr_e( 'close', 'surelywp-toolkit' ); ?>">
									</div>
									<div class="surelywp-sortable-field-heading-top open">
										<?php echo esc_html( $field['heading'] ); ?>
									</div>
								</div>
								<div class="field-actions">
									<div class="field-drag-handle">
										<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/drag-icon.svg' ); ?>" alt="<?php esc_attr_e( 'drag', 'surelywp-toolkit' ); ?>">
									</div>
								</div>
							</div>
							<div class="surelywp-sortable-field-options hidden">
								<?php if ( 'consent_checkbox' !== $field_key ) { ?>
								<div class="form-control">
									<div class="input-label">
										<?php
											// translators: %1$s is the price name, %2$s is the formatted price.
											printf( esc_html__( '%s Label', 'surelywp-toolkit' ), esc_html( $field['heading'] ) );
										?>
									</div>
									<label>
										<?php
											// translators: %1$s is the field name, %2$s is the email address (only for email field).
											printf( esc_html__( 'Enter the label text to display for the %1$s field in the subscription form. %2$s', 'surelywp-toolkit' ), esc_html( strtolower( $field['heading'] ) ), 'email_address' === $field_key ? esc_html__( 'This field is always required.', 'surelywp-toolkit' ) : '' );
										?>
										</label>
									<input type="text" class="widefat" name="<?php echo esc_attr( $field_option_key. '[label_value]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $field['label_value'] ); ?>" />
								</div>
								<?php } else { ?>
									<div class="form-control">
										<div class="input-label">
											<?php
												// translators: %1$s is the price name, %2$s is the formatted price.
												printf( esc_html__( '%s Text', 'surelywp-toolkit' ), esc_html( $field['heading'] ) );
											?>
										</div>
										<label>
											<?php
												// translators: %s is the field name.
												printf( esc_html__( 'Enter the text to display next to the %s in the subscription form. Use the {privacy_policy_link} variable to insert a link to your privacy policy.', 'surelywp-toolkit' ), esc_html( strtolower( $field['heading'] ) ) );
											?>
											</label>
										<input type="text" class="widefat" name="<?php echo esc_attr( $field_option_key. '[label_value]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $field['label_value'] ); ?>" />
									</div>
									<div class="form-control">
										<div class="input-label"><?php esc_html_e( 'Consent Checkbox Privacy Policy URL (Optional)', 'surelywp-toolkit' ); ?></div>
										<label><?php esc_html_e( 'Enter the URL to your privacy policy. This will be used for the {privacy_policy_link} variable in the checkbox text.', 'surelywp-toolkit' ); ?></label>
										<input type="text" class="widefat" name="<?php echo esc_attr( $field_option_key. '[privacy_policy_link]' ); //phpcs:ignore?>" value="<?php echo esc_url( $field['privacy_policy_link'] ); ?>" />
									</div>
								<?php } ?>
								<div class="manage-show-or-require <?php echo 'email_address' === $field_key ? 'hidden' : ''; ?>">
									<div class="form-control">
										<div class="input-label">
											<?php
												// translators: %1$s is the field name, %2$s is empty for all fields except consent_checkbox where it is 'Field'.
												printf( esc_html__( 'Show %1$s %2$s', 'surelywp-toolkit' ), esc_html( $field['heading'] ), 'consent_checkbox' !== $field_key ? esc_html__( 'Field', 'surelywp-toolkit' ) : '' );
											?>
											</div>
										<label>
											<?php
												// translators: %s is the field name.
												printf( esc_html__( 'Enable or disable the %s field in the subscription form.', 'surelywp-toolkit' ), esc_html( strtolower( $field['heading'] ) ) );
											?>
											</label>
										<label class="toggleSwitch xlarge" onclick="">
											<input type="checkbox" class="is-show-field" name="<?php echo esc_attr( $field_option_key. '[is_show]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo checked( $field['is_show']??'', 1, true ); ?> size="10" />
											<span>
												<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
												<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
											</span>
											<a></a>
										</label>
									</div>
									<div class="form-control manage-field-is-required <?php echo ! isset( $field['is_show'] ) ? 'hidden' : ''; ?>">
										<div class="input-label">
											<?php
												// translators: %s is the field name.
												printf( esc_html__( 'Require %s', 'surelywp-toolkit' ), esc_html( $field['heading'] ) );
											?>
												</div>
										<label>
											<?php
											if ( 'consent_checkbox' === $field_key ) {
												esc_html_e( 'Require the checkbox to be checked before the form can be submitted.', 'surelywp-toolkit' );
											} else {
												// translators: %s is the field name.
												printf( esc_html__( 'Require the %s field to be filled out before the form can be submitted.', 'surelywp-toolkit' ), esc_html( strtolower( $field['heading'] ) ) );
											}
											?>
										</label>
										<label class="toggleSwitch xlarge" onclick="">
											<input type="checkbox" class="is-required-field" name="<?php echo esc_attr( $field_option_key. '[is_required]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo checked( $field['is_required']??'', 1, true ); ?> size="10" />
											<span>
												<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
												<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
											</span>
											<a></a>
										</label>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>	
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label verification-email-settings tab-settings hidden" id="verification-email-settings">
			<td>
				<h4 class="heading-text first-heading"><?php esc_html_e( 'Verification Email', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Require Double-Optin Verification Email', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to require users to receive a verification email and click a link in order to complete their download. The verification email will send immediately after submitting the email optin form. This works as a double-optin method to confirm adding the customer to your email marketing list (which you can do using SureTriggers).', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="is-require-email-verification" name="<?php echo esc_attr( $addons_option_key ) . '[require_email_verification]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['require_email_verification'] ) ) ? checked( $settings_options['require_email_verification'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="verification-email-options <?php echo ! isset( $settings_options['require_email_verification'] ) ? 'hidden' : ''; ?>">
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Email Verification Message', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter custom text you want to show in the subscription form to let them know they will receive an email to verify their download.', 'surelywp-toolkit' ); ?></label>
						<input type="text" class="widefat" name="<?php echo $addons_option_key . '[email_verification_message]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['email_verification_message'] ); //phpcs:ignore?>">
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Verification Email Subject', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter custom text you want to use for the verification email subject.', 'surelywp-toolkit' ); ?></label>
						<input type="text" class="widefat" name="<?php echo $addons_option_key . '[verification_email_subject]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['verification_email_subject'] ); //phpcs:ignore?>">
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Verification Email Body', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter custom text you want to use for the verification email body. You can optionally use variables like {customer_name}, {product_name}, and {website_name} to personalize the message by automatically inserting real values.', 'surelywp-toolkit' ); ?></label>
						<?php
						// Add the TinyMCE editor script.
						wp_editor(
							$settings_options['verification_email_body'] ?? '', // Initial content, you can fetch saved content here.
							'verification-email-body', // Editor ID, must be unique.
							array(
								'textarea_name'  => $addons_option_key . '[verification_email_body]', // Name attribute of the textarea.
								'editor_class'   => 'verification-email-body',
								'textarea_rows'  => 5, // Number of rows.
								'media_buttons'  => false, // Show media button in the editor.
								'default_editor' => 'visual',
								'tinymce'        => array(
									'toolbar1'      => 'bold,italic,underline,|,bullist,numlist,|,link,|,undo,redo',
									'toolbar2'      => '', // Leave empty if you don't want a second toolbar.
									'content_style' => 'body, p, div { font-family: Poppins, sans-serif; color: #4c5866;}', // Properly escape font-family.
								),
								'quicktags'      => array(
									'buttons' => 'strong,em,link,ul,ol,li,quote',
								),
							)
						);
						?>
					</div>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label customer-dashboard-settings tab-settings hidden" id="customer-dashboard-settings">
			<td>
				<h4 class="heading-text first-heading"><?php esc_html_e( 'Customer Dashboard Tab', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Enable Lead Magnets Tab In Customer Dashboard', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose whether to add a "Lead Magnets" tab in the SureCart Customer Dashboard. When enabled, customers will see a dedicated tab where they can view any lead magnet downloads associated with their account. Each available lead magnet includes a “Download” button just like in the SureCart interface.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="is-customer-dashboard-enable" name="<?php echo esc_attr( $addons_option_key ) . '[is_customer_dashboard_enable]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['is_customer_dashboard_enable'] ) ) ? checked( $settings_options['is_customer_dashboard_enable'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="customer-dashboard-options <?php echo ! isset( $settings_options['is_customer_dashboard_enable'] ) ? 'hidden' : ''; ?>">
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Rename Customer Dashboard Tab Text', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter custom text to display in the Customer Dashboard menu for the Lead Magnets tab. ', 'surelywp-toolkit' ); ?></label>
						<input type="text" class="widefat" name="<?php echo $addons_option_key . '[tab_name]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['tab_name'] ); //phpcs:ignore?>">
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Replace Customer Dashboard Tab Icon', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter the name of an icon from https://feathericons.com/ to display as the icon in the Customer Dashboard menu for the Lead Magnets tab. ', 'surelywp-toolkit' ); ?></label>
						<input type="text" class="widefat" name="<?php echo $addons_option_key . '[tab_icon]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['tab_icon'] ); //phpcs:ignore?>">
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Rename Lead Magnet Singular Name', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter the singular term you want to use instead of "Lead Magnet." This will replace the word "Lead Magnet" across the interface where a single lead magnet is referenced. ', 'surelywp-toolkit' ); ?></label>
						<input type="text" class="widefat" name="<?php echo $addons_option_key . '[lm_singular_name]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['lm_singular_name'] ); //phpcs:ignore?>">
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Rename Lead Magnets Plural Name', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Enter the plural term you want to use instead of "Lead Magnets." This will replace the word "Lead Magnets" across the interface where multiple lead magnets are referenced.', 'surelywp-toolkit' ); ?></label>
						<input type="text" class="widefat" name="<?php echo $addons_option_key . '[lm_plural_name]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['lm_plural_name'] ); //phpcs:ignore?>">
					</div>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label customer-dashboard-settings tab-settings hidden">
			<td>
				<div class="customer-dashboard-options <?php echo ! isset( $settings_options['is_customer_dashboard_enable'] ) ? 'hidden' : ''; ?>">
					<h4 class="heading-text first-heading"><?php esc_html_e( 'Lead Magnet Downloads List', 'surelywp-toolkit' ); ?></h4>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Lead Magnet Downloads Shortcode', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'You can use the [surelywp_toolkit_downloads_list] shortcode to display a list of downloadable files from any products the logged-in user has purchased. This shortcode works with any page builder and is ideal for creating a custom dashboard or downloads page. By default, it shows all available downloads, but you can limit it to lead magnet products using lead_magnets_only="true". Additional attributes include heading="Your Downloads" to set a custom heading and thumbnail="true" to show product thumbnails. Example: [surelywp_toolkit_downloads_list heading="Your Downloads"].', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>