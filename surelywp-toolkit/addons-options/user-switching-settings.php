<?php
/**
 * User Switching Setting.
 *
 * @package Toolkit For Surecart
 * @since   1.0.0
 */

?>
<table class="form-table surelywp-ric-settings-box toolkit-templates-table">
	<tbody>
		<tr class="surelywp-field-label" id="surelywp-tk-us-role-settings">
			<td>
				<h4 class="heading-text"><?php echo esc_html_e( 'General', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable User Switching', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to enable or disable the using switching features.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $is_enable_us = isset( $settings_options['status'] ) && '1' === $settings_options['status'] ? true : false; ?>
						<input type="checkbox" id="surelywp-tk-us-settings-status" name="<?php echo $addons_option_key . '[status]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['status'] ) ) ? checked( $settings_options['status'], 1, true ) : ''; ?> size="10" />
						<input type="hidden" name="<?php echo $addons_option_key . '[tk_us_update_options]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'updated' ); //phpcs:ignore ?>" />
						<?php $surelywp_tk_us_option_nonce = wp_create_nonce( 'surelywp_tk_us_option_nonce' ); ?>
						<input type="hidden" name="surelywp_tk_us_option_nonce" value="<?php echo $surelywp_model->surelywp_escape_attr( $surelywp_tk_us_option_nonce ); //phpcs:ignore?>" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control <?php echo $is_enable_us ? '' : 'hidden'; ?>" id="surelywp-tk-allowed-user-roles">
					<div class="input-label"><?php esc_html_e( 'Allowed User Roles For User Switching', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Select any SureCart or WordPress user roles you want to allow access the user switching features.', 'surelywp-toolkit' ); ?></label>
					<?php
					$editable_roles = get_editable_roles();
					$roles          = array();
					if ( ! empty( $editable_roles ) ) {
						foreach ( $editable_roles as $user_role => $details ) {
							$sub['role'] = esc_attr( $user_role );
							$sub['name'] = translate_user_role( $details['name'] );
							$roles[]     = $sub;
						}
					}
					if ( isset( $settings_options['user_role'] ) ) {
						$user_roles_res = $settings_options['user_role'];
					}
					?>
					<select multiple="multiple" class="customer-role" id="surelywp-tk-us-user-roles" name="<?php echo $surelywp_model->surelywp_escape_attr( $addons_option_key . '[user_role][]' ); //phpcs:ignore?>">
						<?php
						foreach ( $roles as $key => $user_role ) {
							?>
							<option <?php echo ( isset( $user_roles_res ) && in_array( $user_role['role'], $user_roles_res ) ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $user_role['role'] ); ?>"><?php echo $surelywp_model->surelywp_escape_attr( $user_role['name'] ); //phpcs:ignore?></option>
						<?php } ?>
					</select>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label <?php echo $is_enable_us ? '' : 'hidden'; ?>" id="surelywp-tk-us-wordpress-page-settings">
			<td>
				<hr />
				<h4 class="heading-text"><?php esc_html_e( 'WordPress Users', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Link In All Users List', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide using switching links on each user in the WordPress All Users list.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-wordpress-user-list-status" name="<?php echo $addons_option_key . '[wordpress_user_list_status]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['wordpress_user_list_status'] ) ) ? checked( $settings_options['wordpress_user_list_status'], 1, false ) : ''; //phpcs:ignore?> size="10" >
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Link In Edit User Profile Page', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide a using switching button in each WordPress user edit profile page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-wordpress-user-view-detail" name="<?php echo $addons_option_key . '[wordpress_user_view_detail]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['wordpress_user_view_detail'] ) ) ? checked( $settings_options['wordpress_user_view_detail'], 1, true ) : ''; //phpcs:ignore?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label <?php echo $is_enable_us ? '' : 'hidden'; ?>" id="surelywp-tk-us-customers-page-settings">
			<td>
				<hr />
				<h4 class="heading-text"><?php esc_html_e( 'Customers', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Link In Customer List', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide using switching links on each customer in the customer list page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-customer-status" name="<?php echo $addons_option_key . '[customer_status]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['customer_status'] ) ) ? checked( $settings_options['customer_status'], 1, false ) : ''; //phpcs:ignore?> size="10" >
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Button In Customer Details Page', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide a using switching button in individual customer details pages.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-customer-detail" name="<?php echo $addons_option_key . '[customer_detail]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['customer_detail'] ) ) ? checked( $settings_options['customer_detail'], 1, true ) : ''; //phpcs:ignore?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label <?php echo $is_enable_us ? '' : 'hidden'; ?>" id="surelywp-tk-us-orders-page-settings">
			<td>
				<hr />
				<h4 class="heading-text"><?php esc_html_e( 'Orders', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Link In Orders List', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide using switching links on each order in the orders list page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-order-list-status" name="<?php echo $addons_option_key . '[order_list_status]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['order_list_status'] ) ) ? checked( $settings_options['order_list_status'], 1, false ) : ''; //phpcs:ignore?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Button In Order Details Page', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide a using switching button in individual order details pages.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-order-status" name="<?php echo $addons_option_key . '[order_status]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['order_status'] ) ) ? checked( $settings_options['order_status'], 1, false ) : ''; //phpcs:ignore?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label <?php echo $is_enable_us ? '' : 'hidden'; ?>" id="surelywp-tk-us-subscriptions-page-settings">
			<td>
				<hr />
				<h4 class="heading-text"><?php esc_html_e( 'Subscriptions', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Link In Subscriptions List', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide using switching links on each subscription in the subscriptions list page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-subscriptions-status" name="<?php echo $addons_option_key . '[subscriptions_list_status]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['subscriptions_list_status'] ) ) ? checked( $settings_options['subscriptions_list_status'], 1, false ) : ''; //phpcs:ignore?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Button In Subscription Details Page', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide a using switching button in individual subscription details pages.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-subscriptions-status" name="<?php echo $addons_option_key . '[subscriptions_status]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['subscriptions_status'] ) ) ? checked( $settings_options['subscriptions_status'], 1, false ) : ''; //phpcs:ignore?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<?php
		$is_services_settings_available = Surelywp_Toolkit::is_services_us_settings_available();
		if ( $is_services_settings_available ) {
			?>
		<tr class="surelywp-field-label <?php echo $is_enable_us ? '' : 'hidden'; ?>" id="surelywp-tk-us-service-page-settings">
			<td>
				<hr />
				<h4 class="heading-text"><?php esc_html_e( 'Services', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Link In Services List', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide using switching links on each service in the services list page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-services-list" name="<?php echo $addons_option_key . '[services_list]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['services_list'] ) ) ? checked( $settings_options['services_list'], 1, false ) : ''; //phpcs:ignore?> size="10" >
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Button In Service Details Page', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide a using switching button in individual services pages.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-service-view" name="<?php echo $addons_option_key . '[service_view]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['service_view'] ) ) ? checked( $settings_options['service_view'], 1, true ) : ''; //phpcs:ignore?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<?php } ?>
		<?php
		$is_support_portal_settings_available = Surelywp_Toolkit::is_support_portal_us_settings_available();
		if ( $is_support_portal_settings_available ) {
			?>
		<tr class="surelywp-field-label <?php echo $is_enable_us ? '' : 'hidden'; ?>" id="surelywp-tk-us-support-tickets-page-settings">
			<td>
				<hr />
				<h4 class="heading-text"><?php esc_html_e( 'Support Tickets', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Link In Support Tickets List', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide using switching links on each ticket in the support tickes list page.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-support-tickets-list" name="<?php echo $addons_option_key . '[support_tickets_list]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['support_tickets_list'] ) ) ? checked( $settings_options['support_tickets_list'], 1, false ) : ''; //phpcs:ignore?> size="10" >
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php esc_html_e( 'Add User Switching Button In Support Ticket Details Page', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to show or hide a using switching button in individual support ticket pages.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge">
						<input type="checkbox" id="surelywp-tk-us-support-ticket-view" name="<?php echo $addons_option_key . '[support_ticket_view]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['support_ticket_view'] ) ) ? checked( $settings_options['support_ticket_view'], 1, true ) : ''; //phpcs:ignore?> size="10" />
						<span>
							<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<?php } ?>
		<?php
		if ( defined( 'SURELYWP_CATALOG_MODE' ) ) {
			?>
			<tr class="surelywp-field-label <?php echo $is_enable_us ? '' : 'hidden'; ?>" id="surelywp-tk-us-inquiries-page-settings">
				<td>
					<hr />
					<h4 class="heading-text"><?php esc_html_e( 'Inquiries', 'surelywp-toolkit' ); ?></h4>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Add User Switching Link In Inquiries List', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose to show or hide using switching links on each inquiry in the inquiries list page in the Messaging System in the B2B for SureCart addon.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge">
							<input type="checkbox" name="<?php echo $addons_option_key . '[inquiries_list]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['inquiries_list'] ) ) ? checked( $settings_options['inquiries_list'], 1, false ) : ''; //phpcs:ignore?> size="10" >
							<span>
								<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
					<div class="form-control">
						<div class="input-label"><?php esc_html_e( 'Add User Switching Button In Inquiry Details Page', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Choose to show or hide a using switching button in individual inquiry pages in the Messaging System in the B2B for SureCart addon.', 'surelywp-toolkit' ); ?></label>
						<label class="toggleSwitch xlarge">
							<input type="checkbox" name="<?php echo $addons_option_key . '[inquiry_view]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['inquiry_view'] ) ) ? checked( $settings_options['inquiry_view'], 1, true ) : ''; //phpcs:ignore?> size="10" />
							<span>
								<span><?php esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
								<span><?php esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
							</span>
							<a></a>
						</label>
					</div>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>