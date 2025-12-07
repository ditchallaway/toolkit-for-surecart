<?php
/**
 * Fluent CRM Setting.
 *
 * @package Toolkit For Surecart
 * @since   1.0.0
 */

?>
<table class="form-table surelywp-ric-settings-box toolkit-templates-table">
	<tbody>
		<tr class="surelywp-field-label" id="surelywp-tk-fc-settings">
			<td>
				<h4 class="heading-text"><?php echo esc_html_e( 'Direct FluentCRM/SureCart Integration', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable FluentCRM/SureCart Integration', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable the advanced integration between Toolkit for SureCart and FluentCRM. This includes the ability to filter contacts, create dynamic segments, and build automation conditions based on SureCart data such as purchased products, product variants, price types, product collections, total order count, total order value, first and last order dates, and used coupons. Once enabled, these options will appear throughout FluentCRM, including in Contact Filters, Custom Segments, and Automation Check Conditions.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-fc-settings-status" name="<?php echo $addons_option_key . '[status]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['status'] ) ) ? checked( $settings_options['status'], 1, true ) : ''; ?> size="10" />
						<input type="hidden" name="<?php echo $addons_option_key . '[tk_fc_update_options]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'updated' ); //phpcs:ignore ?>" />
						<?php $surelywp_tk_fc_option_nonce = wp_create_nonce( 'surelywp_tk_fc_option_nonce' ); ?>
						<input type="hidden" name="<?php echo $addons_option_key . '[surelywp_tk_fc_option_nonce]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $surelywp_tk_fc_option_nonce ); //phpcs:ignore?>" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-fc-profile-btn-settings">
			<td>
				<hr />
				<h4 class="heading-text"><?php echo esc_html_e( 'FluentCRM Contact Profile Button', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Add FluentCRM Contact Profile Button On Customers', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to add a button in individual customer details pages to open the FluentCRM contact profile page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" name="<?php echo $addons_option_key . '[profile_btn_on_customers]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['profile_btn_on_customers'] ) ) ? checked( $settings_options['profile_btn_on_customers'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Add FluentCRM Contact Profile Button On Orders', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to add a button in individual order details pages to open the FluentCRM contact profile page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" name="<?php echo $addons_option_key . '[profile_btn_on_orders]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['profile_btn_on_orders'] ) ) ? checked( $settings_options['profile_btn_on_orders'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Add FluentCRM Contact Profile Button On Subscriptions', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to add a button in individual subscription details pages to open the FluentCRM contact profile page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" name="<?php echo $addons_option_key . '[profile_btn_on_subscriptions]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['profile_btn_on_subscriptions'] ) ) ? checked( $settings_options['profile_btn_on_subscriptions'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
	</tbody>
</table>