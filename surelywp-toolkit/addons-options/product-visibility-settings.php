<?php
/**
 * Product visibility settings.
 *
 * @package Toolkit For Surecart
 * @since   1.3.1
 */

?>
<table class="form-table surelywp-ric-settings-box toolkit-templates-table">
	<tbody>
	<tr class="surelywp-field-label" id="surelywp-tk-pv-settings">
			<td>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable Product Visibility Override Rules', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to manually control who can see the shop and product pages based on each product\'s current Publish Status and Availability setting in SureCart. These visibility rules apply to all products except those managed by a Scheduled Product window, which have their own separate visibility options.', 'surelywp-toolkit' ); ?></label>
					<br>
					<label><?php esc_html_e( 'By default, SureCart hides Draft products from both the shop and product pages. If a product is marked Unavailable for Purchase, SureCart will also hide the product from the shop and return a 404 error on the product page, even if the product is published. These rules allow you to override that behavior and show or hide products to specific audiences such as admins or customers.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-pv-status" name="<?php echo $addons_option_key . '[status]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['status'] ) ) ? checked( $settings_options['status'], 1, true ) : ''; ?> size="10" />
						<input type="hidden" name="<?php echo $addons_option_key . '[tk_pv_update_options]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'updated' ); ?>" />
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
		if ( isset( $settings_options['status'] ) && '1' === $settings_options['status'] ) {
			$hidden_class = '';
		} else {
			$hidden_class = 'hidden';
		}
		?>
		<tr class="surelywp-field-label <?php echo esc_html( $hidden_class ); ?>" id="surelywp-tk-pv-product-settings">
			<td>
				<hr>
				<h4 class="heading-text product-page-visibility-heading"><?php echo esc_html_e( 'Product Page Visibility', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<label class="product-page-visibility"><?php esc_html_e( 'These settings control whether visitors can view the individual product page URL when the product is in a specific combination of status and availability.', 'surelywp-toolkit' ); ?></label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Draft + Available For Purchase', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose who can access the product page URL when the product is set to Draft and Available For Purchase. SureCart returns a 404 for customers by default, but admins can still access the page.', 'surelywp-toolkit' ); ?></label>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="draft-available-on-product-page-admins" name="<?php echo $addons_option_key . '[product][draft_available][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_admins' ); ?>" <?php echo ( isset( $settings_options['product']['draft_available'] ) && in_array( 'visible_to_admins', $settings_options['product']['draft_available'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="draft-available-on-product-page-admins"><?php esc_html_e( 'Visible To Admins', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="draft-available-on-product-page-customers" name="<?php echo $addons_option_key . '[product][draft_available][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_customers' ); ?>" <?php echo ( isset( $settings_options['product']['draft_available'] ) && in_array( 'visible_to_customers', $settings_options['product']['draft_available'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="draft-available-on-product-page-customers"><?php esc_html_e( 'Visible To Customers', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Draft + Unavailable For Purchase', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose who can access the product page URL when the product is set to Draft and Unavailable For Purchase. This also results in a 404 for customers by default, though admins can view the page.', 'surelywp-toolkit' ); ?></label>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="draft-unavailable-on-product-page-admins" name="<?php echo $addons_option_key . '[product][draft_unavailable][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_admins' ); ?>" <?php echo ( isset( $settings_options['product']['draft_unavailable'] ) && in_array( 'visible_to_admins', $settings_options['product']['draft_unavailable'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="draft-unavailable-on-product-page-admins"><?php esc_html_e( 'Visible To Admins', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="draft-unavailable-on-product-page-customers" name="<?php echo $addons_option_key . '[product][draft_unavailable][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_customers' ); ?>" <?php echo ( isset( $settings_options['product']['draft_unavailable'] ) && in_array( 'visible_to_customers', $settings_options['product']['draft_unavailable'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="draft-unavailable-on-product-page-customers"><?php esc_html_e( 'Visible To Customers', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Published + Available For Purchase', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose who can access the product page URL when the product is Published and Available For Purchase. SureCart loads the page normally for both admins and customers in this state.', 'surelywp-toolkit' ); ?></label>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="published-available-on-product-page-admins" name="<?php echo $addons_option_key . '[product][published_available][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_admins' ); ?>" <?php echo ( isset( $settings_options['product']['published_available'] ) && in_array( 'visible_to_admins', $settings_options['product']['published_available'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="published-available-on-product-page-admins"><?php esc_html_e( 'Visible To Admins', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="published-available-on-product-page-customers" name="<?php echo $addons_option_key . '[product][published_available][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_customers' ); ?>" <?php echo ( isset( $settings_options['product']['published_available'] ) && in_array( 'visible_to_customers', $settings_options['product']['published_available'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="published-available-on-product-page-customers"><?php esc_html_e( 'Visible To Customers', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Published + Unavailable For Purchase', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose who can access the product page URL when the product is Published but Unavailable For Purchase. SureCart shows a 404 to customers in this case, but admins can still view the page.', 'surelywp-toolkit' ); ?></label>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="published-unavailable-on-product-page-admins" name="<?php echo $addons_option_key . '[product][published_unavailable][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_admins' ); ?>" <?php echo ( isset( $settings_options['product']['published_unavailable'] ) && in_array( 'visible_to_admins', $settings_options['product']['published_unavailable'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="published-unavailable-on-product-page-admins"><?php esc_html_e( 'Visible To Admins', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="published-unavailable-on-product-page-customers" name="<?php echo $addons_option_key . '[product][published_unavailable][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_customers' ); ?>" <?php echo ( isset( $settings_options['product']['published_unavailable'] ) && in_array( 'visible_to_customers', $settings_options['product']['published_unavailable'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="published-unavailable-on-product-page-customers"><?php esc_html_e( 'Visible To Customers', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label <?php echo esc_html( $hidden_class ); ?>" id="surelywp-tk-pv-shop-settings">
			<td>
				<hr>
				<h4 class="heading-text shop-page-visibility-heading"><?php echo esc_html_e( 'Shop Page Visibility', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<label class="shop-page-visibility"><?php esc_html_e( 'These settings control whether products appear in the shop or product list pages when they are in a specific combination of status and availability.', 'surelywp-toolkit' ); ?></label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Draft + Available For Purchase', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose who can see the product in the shop or product list pages when it is set to Draft and Available For Purchase. By default, SureCart hides products in this state from all users.', 'surelywp-toolkit' ); ?></label>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="draft-available-on-shop-page-admins" name="<?php echo $addons_option_key . '[shop][draft_available][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_admins' ); ?>" <?php echo ( isset( $settings_options['shop']['draft_available'] ) && in_array( 'visible_to_admins', $settings_options['shop']['draft_available'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="draft-available-on-shop-page-admins"><?php esc_html_e( 'Visible To Admins', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="draft-available-on-shop-page-customers" name="<?php echo $addons_option_key . '[shop][draft_available][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_customers' ); ?>" <?php echo ( isset( $settings_options['shop']['draft_available'] ) && in_array( 'visible_to_customers', $settings_options['shop']['draft_available'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="draft-available-on-shop-page-customers"><?php esc_html_e( 'Visible To Customers', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Draft + Unavailable For Purchase', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose who can see the product in the shop or product list pages when it is set to Draft and Unavailable For Purchase. SureCart normally hides these products completely.', 'surelywp-toolkit' ); ?></label>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="draft-unavailable-on-shop-page-admins" name="<?php echo $addons_option_key . '[shop][draft_unavailable][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_admins' ); ?>" <?php echo ( isset( $settings_options['shop']['draft_unavailable'] ) && in_array( 'visible_to_admins', $settings_options['shop']['draft_unavailable'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="draft-unavailable-on-shop-page-admins"><?php esc_html_e( 'Visible To Admins', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="draft-unavailable-on-shop-page-customers" name="<?php echo $addons_option_key . '[shop][draft_unavailable][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_customers' ); ?>" <?php echo ( isset( $settings_options['shop']['draft_unavailable'] ) && in_array( 'visible_to_customers', $settings_options['shop']['draft_unavailable'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="draft-unavailable-on-shop-page-customers"><?php esc_html_e( 'Visible To Customers', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Published + Available For Purchase', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose who can see the product in the shop or product list pages when it is set to Published and Available For Purchase. This is the default live state and is normally visible to all users.', 'surelywp-toolkit' ); ?></label>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="published-available-on-shop-page-admins" name="<?php echo $addons_option_key . '[shop][published_available][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_admins' ); ?>" <?php echo ( isset( $settings_options['shop']['published_available'] ) && in_array( 'visible_to_admins', $settings_options['shop']['published_available'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="published-available-on-shop-page-admins"><?php esc_html_e( 'Visible To Admins', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="published-available-on-shop-page-customers" name="<?php echo $addons_option_key . '[shop][published_available][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_customers' ); ?>" <?php echo ( isset( $settings_options['shop']['published_available'] ) && in_array( 'visible_to_customers', $settings_options['shop']['published_available'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="published-available-on-shop-page-customers"><?php esc_html_e( 'Visible To Customers', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Published + Unavailable For Purchase', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose who can see the product in the shop or product list pages when it is set to Published and Unavailable For Purchase. SureCart hides products in this state by default, even though they are published.', 'surelywp-toolkit' ); ?></label>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="published-unavailable-on-shop-page-admins" name="<?php echo $addons_option_key . '[shop][published_unavailable][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_admins' ); ?>" <?php echo ( isset( $settings_options['shop']['published_unavailable'] ) && in_array( 'visible_to_admins', $settings_options['shop']['published_unavailable'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="published-unavailable-on-shop-page-admins"><?php esc_html_e( 'Visible To Admins', 'surelywp-toolkit' ); ?></label>
					</div>
					<div class="checkbox-option">
						<input type="checkbox" class="surelywp-checkbox" id="published-unavailable-on-shop-page-customers" name="<?php echo $addons_option_key . '[shop][published_unavailable][]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible_to_customers' ); ?>" <?php echo ( isset( $settings_options['shop']['published_unavailable'] ) && in_array( 'visible_to_customers', $settings_options['shop']['published_unavailable'], true ) ) ? 'checked' : ''; ?> size="10" />
						<label for="published-unavailable-on-shop-page-customers"><?php esc_html_e( 'Visible To Customers', 'surelywp-toolkit' ); ?></label>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>