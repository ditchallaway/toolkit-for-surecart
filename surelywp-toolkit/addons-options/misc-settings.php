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
	<tr class="surelywp-field-label" id="surelywp-tk-misc-ep-settings">
			<td>
				<h4 class="heading-text"><?php echo esc_html_e( 'External/Affiliate Products', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable External/Affiliate Products', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to add external product options to individual SureCart product pages.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-misc-enable-external-product" name="<?php echo $addons_option_key . '[enable_external_product]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['enable_external_product'] ) ) ? checked( $settings_options['enable_external_product'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-pd-settings">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Price Descriptions', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable Price Descriptions', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to add price description options to individual SureCart product pages.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-misc-enable-pd" name="<?php echo $addons_option_key . '[enable_price_desctiption]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['enable_price_desctiption'] ) ) ? checked( $settings_options['enable_price_desctiption'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-settings">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Customer Dashboard Menu Text', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Rename Customer Dashboard Menu Text', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this feature to rename the Customer Dashboard menu text when users are logged out. To set it up, simply add the CSS class surelywp-rename-menu to the desired WordPress navigation menu item. This class will replace the default menu text with the custom text you enter in the input field below, visible only when users are logged out.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $is_enable_rename_name = isset( $settings_options['enable_rename_name'] ) && '1' === $settings_options['enable_rename_name'] ? true : false; ?>
						<input type="checkbox" id="surelywp-tk-misc-enable-rename-name" name="<?php echo $addons_option_key . '[enable_rename_name]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['enable_rename_name'] ) ) ? checked( $settings_options['enable_rename_name'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control <?php echo $is_enable_rename_name ? '' : 'hidden'; ?>" id="customer-dashboard-nav-menu-title">
					<div class="input-label"><?php esc_html_e( 'Customer Dashboard Menu Text When Logged Out', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enter the text to display in the Customer Dashboard menu when users are logged out. This custom text will replace the default label, making it clear for visitors who aren\'t logged in.', 'surelywp-toolkit' ); ?></label>
					<input type="text" class="misc-logout-title widefat" name="<?php echo $addons_option_key . '[logout_title]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['logout_title'] ); //phpcs:ignore?>">
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-redirection-setting">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Login/Logout Redirection', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable Custom Login Redirect', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to activate custom login redirects for your SureCart store. When enabled, customers logging into the SureCart Customer Dashboard will be redirected to your specified URL.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $is_enable_login_redirection = isset( $settings_options['is_enable_login_redirection'] ) && '1' === $settings_options['is_enable_login_redirection'] ? true : false; ?>
						<input type="checkbox" id="surelywp-tk-misc-enable-login-redirection" name="<?php echo $addons_option_key . '[is_enable_login_redirection]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['is_enable_login_redirection'] ) ) ? checked( $settings_options['is_enable_login_redirection'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control <?php echo $is_enable_login_redirection ? '' : 'hidden'; ?>" id="surelywp-tk-misc-login-redirect-url">
					<div class="input-label"><?php esc_html_e( 'Customer Login Redirect URL', 'surelywp-toolkit' ); ?></div>
					<label class="input-label-desc"><?php esc_html_e( 'Enter the URL where customers logging into the SureCart Customer Dashboard should be redirected.', 'surelywp-toolkit' ); ?></label>
					<input type="url" id="urelywp-tk-misc-login-redirect-url-input" class="widefat" name="<?php echo $addons_option_key . '[login_redirect_url]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['login_redirect_url'] ??'' ); //phpcs:ignore?>">
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable Role Based Login Redirect', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to activate user role-based redirects for your SureCart store. When enabled, customers logging into the SureCart Customer Dashboard will be redirected to the specified URL based on their WordPress user role. This setting takes priority over custom login redirections.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $is_enable_role_based_redirection = isset( $settings_options['is_enable_role_based_redirection'] ) && '1' === $settings_options['is_enable_role_based_redirection'] ? true : false; ?>
						<input type="checkbox" id="surelywp-tk-misc-enable-role-based-login-redirection" name="<?php echo $addons_option_key . '[is_enable_role_based_redirection]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['is_enable_role_based_redirection'] ) ) ? checked( $settings_options['is_enable_role_based_redirection'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="role-based-redirection-option" <?php echo $is_enable_role_based_redirection ? '' : 'hidden'; ?> id="role-based-redirection-option">
					<div class="form-control" id="surelywp-tk-misc-user-roles-options">
						<div class="input-label"><?php esc_html_e( 'Select User Roles For Redirection', 'surelywp-toolkit' ); ?></div>
						<label><?php esc_html_e( 'Select any SureCart or WordPress user roles you want to allow redirection.', 'surelywp-toolkit' ); ?></label>
						<?php
						$editable_roles = get_editable_roles();
						$roles          = array();
						if ( ! empty( $editable_roles ) ) {
							foreach ( $editable_roles as $user_role => $details ) {

								$sub['role'] = esc_attr( $user_role );
								if ( 'administrator' === $sub['role'] ) {
									continue;
								}
								$sub['name']           = translate_user_role( $details['name'] );
								$roles[ $sub['role'] ] = $sub['name'];
							}
						}
						if ( isset( $settings_options['login_redirection_user_roles'] ) ) {
							$user_roles_res = $settings_options['login_redirection_user_roles'];
						}
						?>
						<select multiple="multiple" id="surelywp-tk-misc-login-redirection-user-roles" class="customer-role" name="<?php echo $surelywp_model->surelywp_escape_attr( $addons_option_key . '[login_redirection_user_roles][]' ); //phpcs:ignore?>">
							<?php
							foreach ( $roles as $role_key => $user_role ) {
								?>
								<option <?php echo ( isset( $user_roles_res ) && in_array( $role_key, $user_roles_res ) ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $role_key ); ?>"><?php echo $surelywp_model->surelywp_escape_attr( $user_role ); //phpcs:ignore?></option>
							<?php } ?>
						</select>
					</div>
					<?php
					if ( isset( $settings_options['login_redirection_user_roles'] ) && ! empty( $settings_options['login_redirection_user_roles'] ) ) {
						foreach ( $settings_options['login_redirection_user_roles'] as $user_role_key ) {
							?>
					<div class="form-control" id="<?php
						// translators: %s: user role key.
						printf( esc_html__( '%s-login-redirection', 'surelywp-toolkit' ), esc_html( $user_role_key ) ); ?>">
						<div class="input-label"><?php
						// translators: %s: user role name.
						printf( esc_html__( '%s Login Redirect URL', 'surelywp-toolkit' ), esc_html( $roles[ $user_role_key ] ?? '' ) ); ?></div>
						<label><?php
						// translators: %s: user role name.
						printf( esc_html__( 'Enter the URL where users with the %s user role should be redirected when logging into the SureCart Customer Dashboard.', 'surelywp-toolkit' ), esc_html( $roles[ $user_role_key ] ?? '' ) ); ?></label>
						<input type="url" class="widefat" name="<?php echo $addons_option_key . '[redirection_urls]['.$user_role_key.'][login_redirect_url]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['redirection_urls'][$user_role_key]['login_redirect_url'] ??'' ); //phpcs:ignore?>">
					</div>
							<?php
						}
					}
					?>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable Custom Logout Redirect', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to activate custom logout redirects for your SureCart store. When enabled, customers logging out of the SureCart Customer Dashboard will be redirected to your specified URL.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $is_enable_logout_redirection = isset( $settings_options['is_enable_logout_redirection'] ) && '1' === $settings_options['is_enable_logout_redirection'] ? true : false; ?>
						<input type="checkbox" id="surelywp-tk-misc-enable-logout-redirection" name="<?php echo $addons_option_key . '[is_enable_logout_redirection]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['is_enable_logout_redirection'] ) ) ? checked( $settings_options['is_enable_logout_redirection'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control  <?php echo $is_enable_logout_redirection ? '' : 'hidden'; ?>" id="surelywp-tk-misc-logout-redirect-url">
					<div class="input-label"><?php esc_html_e( 'Customer Logout Redirect URL', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enter the URL where customers logging out of the SureCart Customer Dashboard should be redirected.', 'surelywp-toolkit' ); ?></label>
					<input type="url" class="widefat" name="<?php echo $addons_option_key . '[logout_redirect_url]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['logout_redirect_url']??'' ); //phpcs:ignore?>">
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-bh-redirection-setting">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Back Home Button Redirection', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable “Back Home” Custom Redirect', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to activate a custom redirect URL for the “Back Home” button in the SureCart Customer Dashboard.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $is_enable_back_home_redirection = isset( $settings_options['is_enable_back_home_redirection'] ) && '1' === $settings_options['is_enable_back_home_redirection'] ? true : false; ?>
						<input type="checkbox" id="surelywp-tk-misc-enable-bh-redirection" name="<?php echo $addons_option_key . '[is_enable_back_home_redirection]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['is_enable_back_home_redirection'] ) ) ? checked( $settings_options['is_enable_back_home_redirection'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control <?php echo $is_enable_back_home_redirection ? '' : 'hidden'; ?>" id="surelywp-tk-misc-bh-redirect-url">
					<div class="input-label"><?php esc_html_e( '“Back Home” Button Redirect URL', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enter the URL where customers should be redirected when clicking the “Back Home” button.', 'surelywp-toolkit' ); ?></label>
					<input type="url" class="widefat" name="<?php echo $addons_option_key . '[back_home_redirect_url]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $settings_options['back_home_redirect_url'] ??'' ); //phpcs:ignore?>">
				</div>
			</td>
		</tr>
		<!-- Order Again Buttons -->
		<tr class="surelywp-field-label" id="surelywp-tk-misc-oa-btn-setting">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Order Again Buttons', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Show Order Again Button On Orders', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to add an "Order Again" button on each individual order in the SureCart Customer Dashboard. When clicked, it will initiate the "Buy Now" feature, placing the same product configuration into the checkout for quick reordering.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $enable_order_again_btn = isset( $settings_options['enable_order_again_btn'] ) && '1' === $settings_options['enable_order_again_btn'] ? true : false; ?>
						<input type="checkbox" id="surelywp-tk-misc-enable-oa-btn" name="<?php echo $addons_option_key . '[enable_order_again_btn]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['enable_order_again_btn'] ) ) ? checked( $settings_options['enable_order_again_btn'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<!-- Admin Tools -->
		<tr class="surelywp-field-label" id="surelywp-tk-misc-at-setting">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Admin Tools', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Show "Recovered" Badge for Recovered Orders', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this setting to display a "Recovered" badge on the order details page for any orders recovered from the abandoned checkout system. This provides clear visibility that the order was completed through the recovery process.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $enable_order_again_btn = isset( $settings_options['enable_recovered_badge'] ) && '1' === $settings_options['enable_recovered_badge'] ? true : false; ?>
						<input type="checkbox" id="surelywp-tk-misc-enable-recovered-badge" name="<?php echo $addons_option_key . '[enable_recovered_badge]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['enable_recovered_badge'] ) ) ? checked( $settings_options['enable_recovered_badge'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable Retry Payment Option For Failed Orders', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enabling this option adds a "Retry Payment" action to the Actions menu of any order with a failed payment, allowing admins to manually retry the payment without navigating to the associated subscription.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<?php $enable_order_again_btn = isset( $settings_options['enable_retry_btn'] ) && '1' === $settings_options['enable_retry_btn'] ? true : false; ?>
						<input type="checkbox" name="<?php echo $addons_option_key . '[enable_retry_btn]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['enable_retry_btn'] ) ) ? checked( $settings_options['enable_retry_btn'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-admin-bar-links-settings">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Admin Bar Links', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Show SureCart Menu In Admin Bar', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to add the SureCart menu to the WordPress admin bar.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-misc-settings-surecart-menu" name="<?php echo $addons_option_key . '[surecart_menu]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['surecart_menu'] ) ) ? checked( $settings_options['surecart_menu'], 1, true ) : ''; ?> size="10" />
						<input type="hidden" name="<?php echo $addons_option_key . '[tk_misc_update_options]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'updated' ); //phpcs:ignore ?>" />
						<?php $surelywp_tk_misc_option_nonce = wp_create_nonce( 'surelywp_tk_misc_option_nonce' ); ?>
						<input type="hidden" name="<?php echo $addons_option_key . '[surelywp_tk_misc_option_nonce]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $surelywp_tk_misc_option_nonce ); //phpcs:ignore?>" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Show SureCart App Link In Admin Bar', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Choose to add a link in the WordPress admin bar to open the SureCart app in a new tab.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-misc-settings-sc-app-link" name="<?php echo $addons_option_key . '[surecart_app_link]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['surecart_app_link'] ) ) ? checked( $settings_options['surecart_app_link'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-product-price">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Product Price Display', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Product Price ID Shortcode', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'To display the price of a specific SureCart product pricing option, use the shortcode [surelywp_product_price id="123"] in your content, replacing the id attribute with the Price ID.', 'surelywp-toolkit' ); ?></label>
					<br>
					<label><?php esc_html_e( 'To find the Price ID, navigate to your SureCart product settings. Locate the desired pricing option, click the clipboard icon, and copy the Price ID from the popup. Paste the copied ID into the shortcode as shown above.', 'surelywp-toolkit' ); ?></label>
					<br>
					<label><?php esc_html_e( 'You can also customize how the price is displayed by adding optional attributes. Use show_strikethrough="true" to display the original price with a strikethrough next to the sale price, if applicable. For sale prices, you can modify the sale badge with the sale_text attribute. For example, you could use sale_text="Black Friday Sale!". If this attribute is not included, the badge will display "Sale" by default.', 'surelywp-toolkit' ); ?></label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Product Variant Price ID Shortcode', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'To display the price of a specific SureCart product variant, use the shortcode [surelywp_product_variant_price id="123"] in your content, replacing the id attribute with the Variant Price ID.', 'surelywp-toolkit' ); ?></label>
					<br>
					<label><?php esc_html_e( 'To find the Variant Price ID, navigate to your SureCart product settings. Locate the desired pricing option, click the clipboard icon, select the desired variant from the dropdown, and copy the Variant Price ID from the popup. Paste the copied ID into the shortcode as shown above.', 'surelywp-toolkit' ); ?></label>
					<br>
					<label><?php esc_html_e( 'You can also customize how the price is displayed by adding optional attributes. Use show_strikethrough="true" to display the original price with a strikethrough next to the sale price, if applicable. For sale prices, you can modify the sale badge with the sale_text attribute. For example, you could use sale_text="Black Friday Sale!". If this attribute is not included, the badge will display "Sale" by default.', 'surelywp-toolkit' ); ?></label>
					<br>
					<label><?php esc_html_e( 'Note that variants should only be used on products with one price option. Otherwise, there is a limitation preventing the shortcode from displaying the variant price.', 'surelywp-toolkit' ); ?></label>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-scs-settings">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Syncing', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'SureCart Data Sync Fallback', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Some of our plugin’s features require access to SureCart order and subscription data. This data is automatically synced when the plugin is installed, so in most cases, no action is required. However, if something interferes with that automatic sync, such as a timeout, plugin conflict, or delayed connection, this setting provides a manual method to reprocess and import that data as needed.', 'surelywp-toolkit' ); ?></label>
					<div class="checkout-sync-btn-wrap" bis_skin_checked="1">
						<a href="javascript:void(0)" id="sc-checkout-sync-btn" class="sc-checkout-sync-btn surelywp-btn active">
							<img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/sync.svg' ); ?>" alt="sync">
							<?php esc_html_e( 'Manually Sync Past Orders', 'surelywp-toolkit' ); ?>
						</a>
					</div>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-product-price">
			<td>
				<hr>
				<h4 class="heading-text"><?php echo esc_html_e( 'Downloads List', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Downloads Shortcode', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'You can use the [surelywp_toolkit_downloads_list] shortcode to display a list of downloadable files from any products the logged-in user has purchased. This shortcode works with any page builder and is ideal for creating a custom dashboard or downloads page. By default, it shows all available downloads, but you can limit it to lead magnet products using lead_magnets_only="true". Additional attributes include heading="Your Downloads" to set a custom heading and thumbnail="true" to show product thumbnails. Example: [surelywp_toolkit_downloads_list heading="Your Downloads" thumbnail="true"].', 'surelywp-toolkit' ); ?></label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Show Thumbnails In Customer Download List', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this option to enhance the customer-facing download list in the SureCart Customer Dashboard by displaying image previews for image file downloads. By default, SureCart shows a generic file-type icon for each file. When this option is enabled, actual image thumbnails will be shown instead for supported image file types, providing a more visual and helpful layout.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-misc-is-show-image-on-customer-downloads-list" name="<?php echo $addons_option_key . '[is_show_image_on_customer_downloads_list]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['is_show_image_on_customer_downloads_list'] ) ) ? checked( $settings_options['is_show_image_on_customer_downloads_list'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Show Thumbnails In Product Admin Download List', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this option to enhance the download list in the SureCart product editor by replacing the default file-type icons with actual image thumbnails for image-based files. This helps store owners more easily identify image downloads at a glance when managing files in the admin area.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-misc-is-show-image-on-admin-downloads-list" name="<?php echo $addons_option_key . '[is_show_image_on_admin_downloads_list]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['is_show_image_on_admin_downloads_list'] ) ) ? checked( $settings_options['is_show_image_on_admin_downloads_list'], 1, true ) : ''; ?> size="10" />
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