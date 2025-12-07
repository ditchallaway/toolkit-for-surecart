<?php
/**
 * Dashboard Tabs Setting.
 *
 * @package Toolkit For Surecart
 * @since   1.2.3
 */

$sc_cutomer_dashboard_default_tabs = array(
	'dashboard'     => array(
		'heading'   => esc_html__( 'Dashboard', 'surelywp-toolkit' ),
		'is_show'   => true,
		'tab_name'  => esc_html__( 'Dashboard', 'surelywp-toolkit' ),
		'tab_icon'  => 'server',
		'is_sc_tab' => true,
	),
	'orders'        => array(
		'heading'   => esc_html__( 'Orders', 'surelywp-toolkit' ),
		'is_show'   => true,
		'tab_name'  => esc_html__( 'Orders', 'surelywp-toolkit' ),
		'tab_icon'  => 'shopping-bag',
		'is_sc_tab' => true,
	),
	'invoices'      => array(
		'heading'   => esc_html__( 'Invoices', 'surelywp-toolkit' ),
		'is_show'   => true,
		'tab_name'  => esc_html__( 'Invoices', 'surelywp-toolkit' ),
		'tab_icon'  => 'inbox',
		'is_sc_tab' => true,
	),
	'subscriptions' => array(
		'heading'   => esc_html__( 'Plans', 'surelywp-toolkit' ),
		'is_show'   => true,
		'tab_name'  => esc_html__( 'Plans', 'surelywp-toolkit' ),
		'tab_icon'  => 'repeat',
		'is_sc_tab' => true,
	),
	'downloads'     => array(
		'heading'   => esc_html__( 'Downloads', 'surelywp-toolkit' ),
		'is_show'   => true,
		'tab_name'  => esc_html__( 'Downloads', 'surelywp-toolkit' ),
		'tab_icon'  => 'download-cloud',
		'is_sc_tab' => true,
	),
);

$is_suremembers_plugin_active = defined( 'SUREMEMBERS_ACCESS_GROUPS' ) ? true : false;
$all_sm_access_groups         = surelywp_tk_get_all_sm_access_groups();

$roles      = wp_roles();
$role_names = $roles->get_names();

?>
<table class="form-table surelywp-ric-settings-box toolkit-templates-table">
	<tbody>
		<tr class="surelywp-field-label" id="surelywp-tk-misc-ccd-tabs-settings">
			<td>
				<h4 class="heading-text"><?php echo esc_html_e( 'Custom Customer Dashboard Tabs', 'surelywp-toolkit' ); ?></h4>
				<div class="form-control">
					<div class="input-label"><?php echo esc_html_e( 'Enable Custom Tabs', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this feature to customize the SureCart Customer Dashboard tabs. You can edit, rearrange, or hide columns to suit your needs.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-misc-enable-ccd-tabs" name="<?php echo $addons_option_key . '[enable_ccd_tabs]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['enable_ccd_tabs'] ) ) ? checked( $settings_options['enable_ccd_tabs'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="surelywp-tk-misc-ccd-tabs <?php echo ! isset( $settings_options['enable_ccd_tabs'] ) ? 'hidden' : ''; ?>" id="surelywp-tk-misc-ccd-tabs">
					<?php
					$sc_ccd_tabs = array();
					if ( isset( $settings_options['sc_ccd_tabs'] ) ) {
						$sc_ccd_tabs = $settings_options['sc_ccd_tabs'];
					} else {
						$sc_ccd_tabs = $sc_cutomer_dashboard_default_tabs;
					}
					foreach ( $sc_ccd_tabs as $key => $tab ) {

						$name_key  = $addons_option_key . '[sc_ccd_tabs][' . $key . ']';
						$heading   = '';
						$is_sc_tab = false;
						if ( isset( $tab['is_sc_tab'] ) && ! empty( $tab['is_sc_tab'] ) ) {
							$heading   = $tab['heading'];
							$is_sc_tab = true;
						} else {
							$heading = $tab['tab_name'];
						}
						?>
						<div class="surelywp-tk-misc-ccd-tab surelywp-list-tab" id="surelywp-tk-misc-ccd-tab">
							<div class="surelywp-tk-misc-ccd-tab-top">
								<div class="top-left">
									<div class="tab-toogle-btn">
										<img class="tab-open-icon" src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/open-down-icon.svg' ); ?>" alt="<?php esc_attr_e( 'open', 'surelywp-toolkit' ); ?>">
										<img class="tab-close-icon hidden" src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/open-up-icon.svg' ); ?>" alt="<?php esc_attr_e( 'close', 'surelywp-toolkit' ); ?>">
									</div>
									<div class="surelywp-tk-misc-ccd-tab-heading-top open">
										<?php echo esc_html( $heading ); ?>
									</div>
								</div>
								<div class="field-actions">
									<div class="surelywp-tk-misc-ccd-tab-remove <?php echo $is_sc_tab ? 'hidden' : ''; ?>">
										<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . '/assets/images/remove-icon.svg' ); ?>" alt="<?php esc_attr_e( 'close', 'surelywp-toolkit' ); ?>">
									</div>
									<div class="field-drag-handle">
										<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/drag-icon.svg' ); ?>" alt="<?php esc_attr_e( 'drag', 'surelywp-toolkit' ); ?>">
									</div>
								</div>
							</div>
							<div class="surelywp-tk-misc-ccd-tab-options hidden">
								<div class="form-control">
									
									<!-- hidden fields -->
									<input type="hidden" class="tab-heading" name="<?php echo esc_attr( $name_key. '[heading]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $heading ); ?>">
									<input type="hidden" class="is-sc-tab" name="<?php echo esc_attr( $name_key. '[is_sc_tab]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $is_sc_tab ); ?>">

									<div class="input-label surelywp-misc-ccd-is-show-tab-label">
										<?php
										// translators: %s: Tab heading.
										printf( esc_html__( 'Show %s Tab', 'surelywp-toolkit' ), esc_html( $heading ) );
										?>
									</div>
									<label><?php esc_html_e( 'Choose whether to display this tab in the Customer Dashboard menu.', 'surelywp-toolkit' ); ?></label>
									<label class="toggleSwitch xlarge" onclick="">
										<input type="checkbox" class="surelywp-misc-ccd-is-show-tab" name="<?php echo esc_attr( $name_key. '[is_show]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo checked( isset($tab['is_show']), 1, true ); ?> size="10" />
										<span>
											<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
											<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
										</span>
										<a></a>
									</label>
								</div>
								<div class="tab-options <?php echo ! isset( $tab['is_show'] ) ? 'hidden' : ''; ?>">
									<div class="form-control">
										<div class="input-label"><?php echo esc_html_e( 'Tab Name', 'surelywp-toolkit' ); ?></div>
										<label><?php esc_html_e( 'Enter a custom name for this tab in the Customer Dashboard menu.', 'surelywp-toolkit' ); ?></label>
										<input type="text" class="widefat tab-name" name="<?php echo esc_attr( $name_key. '[tab_name]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $tab['tab_name']??'' ); ?>" />
									</div>
									<div class="form-control">
										<div class="input-label"><?php echo esc_html_e( 'Tab Icon', 'surelywp-toolkit' ); ?></div>
										<label><?php esc_html_e( 'Enter the name of an icon from https://feathericons.com/ to display as the icon in the Customer Dashboard menu for this tab.', 'surelywp-toolkit' ); ?></label>
										<input type="text" class="widefat tab-icon" name="<?php echo esc_attr( $name_key. '[tab_icon]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $tab['tab_icon']??'' ); ?>" />
									</div>
									<div class="tab-behavior-setting <?php echo $is_sc_tab ? 'hidden' : ''; ?>">
										<div class="form-control">
											<div class="input-label"><?php esc_html_e( 'Tab Behavior', 'surelywp-toolkit' ); ?></div>
											<label><?php esc_html_e( 'Choose whether to display content directly in the tab or turn the tab into a link that redirects to another page.', 'surelywp-toolkit' ); ?></label>
											<select class="tab-behavior-option" name="<?php echo esc_attr( $name_key . '[tab_behavior]' ); ?>">
												<option <?php echo ( isset( $tab['tab_behavior'] ) && 'display_content' === $tab['tab_behavior'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'display_content' ); ?>"><?php esc_html_e( 'Display Content', 'surelywp-toolkit' ); ?></option>
												<option <?php echo ( isset( $tab['tab_behavior'] ) && 'link_to_url' === $tab['tab_behavior'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'link_to_url' ); ?>"><?php esc_html_e( 'Link To URL', 'surelywp-toolkit' ); ?></option>
											</select>
										</div>
										<?php
										if ( ! isset( $tab['tab_behavior'] ) ) {
											$tab['tab_behavior'] = 'display_content';
										}
										?>
										<div class="form-control tab-content-setting <?php echo 'display_content' !== $tab['tab_behavior'] ?? '' ? 'hidden' : ''; ?>">
											<div class="input-label"><?php echo esc_html_e( 'Tab Content', 'surelywp-toolkit' ); ?></div>
											<label class="tab-content-input-label-desc"><?php esc_html_e( 'Enter the content to display inside the tab. You can use plain text, custom HTML, or shortcodes to dynamically pull in data.', 'surelywp-toolkit' ); ?></label>
											<?php
											// Add the TinyMCE editor script.
											wp_editor(
												$tab['tab_content'] ?? '', // Initial content, you can fetch saved content here.
												'ccd-tab-' . $key, // Editor ID, must be unique.
												array(
													'textarea_name' => $name_key . '[tab_content]', // Name attribute of the textarea.
													'editor_class' => 'tab-content',
													'textarea_rows' => 5, // Number of rows.
													'media_buttons' => false, // Show media button in the editor.
													'default_editor' => 'visual',
													'tinymce' => array(
														'toolbar1' => 'bold,italic,underline,|,bullist,numlist,|,link,|,undo,redo',
														'toolbar2' => '', // Leave empty if you don't want a second toolbar.
														'content_style' => 'body, p, div { font-family: Poppins, sans-serif; color: #4c5866;}', // Properly escape font-family.
													),
													'quicktags' => array(
														'buttons' => 'strong,em,link,ul,ol,li,quote',
													),
												)
											);
											?>
										</div>
										<div class="form-control tab-link-setting <?php echo 'link_to_url' !== $tab['tab_behavior'] ?? '' ? 'hidden' : ''; ?>">
											<div class="input-label"><?php echo esc_html_e( 'Tab Link', 'surelywp-toolkit' ); ?></div>
											<label><?php esc_html_e( 'Enter the URL where customers clicking this tab should be redirected.', 'surelywp-toolkit' ); ?></label>
											<input type="url" class="widefat tab-link" name="<?php echo esc_attr( $name_key. '[tab_link]' ); //phpcs:ignore?>" value="<?php echo esc_url( $tab['tab_link']??'' ); ?>" />
										</div>
									</div>
									<div class="tab-restriction-setting">
										<?php
										$tab_restrict_criteria = $tab['tab_restrict_criteria'] ?? 'based_on_user_roles';
										if ( ! $is_suremembers_plugin_active && 'based_on_sm_access_groups' === $tab_restrict_criteria ) {
											$tab_restrict_criteria = 'based_on_user_roles';
											unset( $tab['is_restrict_tab'] );
										}
										?>
										<div class="form-control role-wise">
											<div class="input-label"><?php esc_html_e( 'Hide Tab Based On User Roles Or SureMembers Access Groups', 'surelywp-toolkit' ); ?></div>
											<label><?php esc_html_e( 'Enable this option to hide the tab for users who have a specific user role or belong to an active SureMembers Access Group.', 'surelywp-toolkit' ); ?></label>
											<label class="toggleSwitch xlarge" onclick="">
												<input type="checkbox" class="surelywp-misc-ccd-is-restrict-tab" name="<?php echo esc_attr( $name_key. '[is_restrict_tab]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo checked( isset($tab['is_restrict_tab']), 1, true ); ?> size="10" />
												<span>
													<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
													<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
												</span>
												<a></a>
											</label>
										</div>
										<div class="tab-restriction-options <?php echo ! isset( $tab['is_restrict_tab'] ) ? 'hidden' : ''; ?>">
											<div class="form-control <?php echo ! $is_suremembers_plugin_active ? 'hidden' : ''; ?>">
												<div class="input-label"><?php esc_html_e( 'Tab Hiding Criteria', 'surelywp-toolkit' ); ?></div>
												<label><?php esc_html_e( 'Choose whether to hide the tab based on user role or SureMembers Access Group. The SureMembers Access Group option will only be available if SureMembers is installed and active.', 'surelywp-toolkit' ); ?></label>
												<select class="tab-restrict-criteria" name="<?php echo esc_attr( $name_key . '[tab_restrict_criteria]' ); ?>">
													<option <?php echo ( isset( $tab['tab_restrict_criteria'] ) && 'based_on_user_roles' === $tab['tab_restrict_criteria'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'based_on_user_roles' ); ?>"><?php esc_html_e( 'User Role', 'surelywp-toolkit' ); ?></option>
													<option <?php echo ( isset( $tab['tab_restrict_criteria'] ) && 'based_on_sm_access_groups' === $tab['tab_restrict_criteria'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'based_on_sm_access_groups' ); ?>"><?php esc_html_e( 'SureMembers Access Groups', 'surelywp-toolkit' ); ?></option>
												</select>
											</div>
											<div class="form-control tab-visibility-condition-fc">
												<div class="input-label"><?php esc_html_e( 'Tab Visibility Condition', 'surelywp-toolkit' ); ?></div>
												<label class="tab-visibility-condition-ur-label <?php echo 'based_on_user_roles' === $tab_restrict_criteria ? '' : 'hidden-important'; ?>"><?php esc_html_e( 'Choose whether this tab should be visible or hidden for the selected users based on their user role.', 'surelywp-toolkit' ); ?></label>
												<label class="tab-visibility-condition-sm-label <?php echo 'based_on_sm_access_groups' === $tab_restrict_criteria ? '' : 'hidden-important'; ?>"><?php esc_html_e( 'Choose whether this tab should be visible or hidden for the selected users based on their SureMembers access group.', 'surelywp-toolkit' ); ?></label>
												<select class="tab-visibility-condition" name="<?php echo esc_attr( $name_key . '[tab_visibility_condition]' ); ?>">
													<option <?php echo ( isset( $tab['tab_visibility_condition'] ) && 'hidden' === $tab['tab_visibility_condition'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'hidden' ); ?>"><?php esc_html_e( 'Hidden', 'surelywp-toolkit' ); ?></option>
													<option <?php echo ( isset( $tab['tab_visibility_condition'] ) && 'visible' === $tab['tab_visibility_condition'] ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( 'visible' ); ?>"><?php esc_html_e( 'Visible', 'surelywp-toolkit' ); ?></option>
												</select>
											</div>
											<?php if ( $is_suremembers_plugin_active ) { ?>

												<div class="form-control sm-access-group-selection <?php echo 'based_on_sm_access_groups' !== $tab_restrict_criteria ? 'hidden' : ''; ?>">
													<div class="input-label"><?php esc_html_e( 'Select SureMembers Access Groups To Hide Tab', 'surelywp-toolkit' ); ?></div>
													<label><?php esc_html_e( 'Select one or more SureMembers Access Groups with an active status. The tab will be hidden from logged-in users who belong to at least one of the selected access groups. If no access group is selected or the selected groups are inactive, the tab will be visible to all users.', 'surelywp-toolkit' ); ?></label>
													<?php
													$tab_restriction_sm_access_groups = $tab['tab_restriction_sm_access_groups'] ?? array();
													?>
													<select multiple="multiple" class="tab-restriction-sm-access-groups" name="<?php echo esc_attr( $name_key . '[tab_restriction_sm_access_groups][]' ); ?>">
														<?php
														if ( ! empty( $all_sm_access_groups ) ) {
															foreach ( $all_sm_access_groups as $access_group ) {
																$access_group_id         = $access_group->ID ?? '';
																$access_group_post_title = $access_group->post_title ?? '';
																?>
																<option <?php echo in_array( $access_group_id, (array) $tab_restriction_sm_access_groups ) ? 'selected' : ''; ?> value="<?php echo $surelywp_model->surelywp_escape_attr( $access_group_id ); ?>">
																	<?php echo $surelywp_model->surelywp_escape_attr( $access_group_post_title ); ?>
																</option>
																<?php
															}
														}
														?>
													</select>
												</div>

											<?php } ?>
											<div class="form-control user-roles-selection <?php echo ( 'based_on_user_roles' !== $tab_restrict_criteria && $is_suremembers_plugin_active ) ? 'hidden' : ''; ?>">
												<div class="input-label"><?php esc_html_e( 'Select User Roles To Hide Tab', 'surelywp-toolkit' ); ?></div>
												<label><?php esc_html_e( 'Select one or more WordPress user roles. The tab will be hidden from logged-in users assigned to at least one of the selected roles. If no user role is selected, the tab will be visible to all users.', 'surelywp-toolkit' ); ?></label>
												<?php
												$tab_restriction_user_roles = $tab['tab_restriction_user_roles'] ?? array();
												?>
												<select multiple="multiple" class="tab-restriction-user-roles" name="<?php echo esc_attr( $name_key . '[tab_restriction_user_roles][]' ); ?>">
													<?php
													if ( ! empty( $role_names ) ) {
														foreach ( $role_names as $role_key => $role_name ) {
															?>
															<option value="<?php echo $surelywp_model->surelywp_escape_attr( $role_key ); //phpcs:ignore?>" <?php echo isset( $tab['tab_restriction_user_roles'] ) && in_array( $role_key, (array)$tab[ 'tab_restriction_user_roles'], true ) ? 'selected' : ''; ?>><?php echo esc_html( $role_name ); ?></option>
															<?php
														}
													}
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="form-control addons-btn-wrap no-sort">
						<a href="javascript:void(0)" id="add-new-ccd-tab-btn" class="button-primary">
							<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . '/assets/images/Add_new.svg' ); ?>" alt="<?php esc_attr_e( 'add_new', 'surelywp-toolkit' ); ?>"><?php esc_html_e( 'Add New Tab', 'surelywp-toolkit' ); ?>
						</a>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>