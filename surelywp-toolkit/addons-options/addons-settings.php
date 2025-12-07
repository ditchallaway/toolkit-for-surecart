<?php
/**
 * Addons Settings
 *
 * @author  Surelywp
 * @package Toolkit For SureCart
 * @version 1.0.0
 */

?>
<table class="form-table surelywp-ric-settings-box">
	<tbody>
		<tr class="surelywp-field-label">
			<td>
				<?php
				$activated_plugins = get_option( 'surelywp_recently_activated' );
				$endpoint_url      = surelywp_api_endpoint_url();
				$url               = $endpoint_url . '/surecart-addons/surelywp-addon.json';
				$res_data          = surelywp_API_response( esc_url( $url ) );
				if ( ! empty( $res_data ) ) {
					?>
					<div class="grid-view row">
						<?php
						foreach ( $res_data as $plugin_data ) {
							$search_file = surelywp_get_plugin_folder_name( $plugin_data->plugin_slug );
							$plugin_path = $search_file . '/' . $plugin_data->plugin_path;
							$installed   = SurelyWP_Plugin_Panel_SureCart::surelywp_check_plugin_installed( $plugin_path );
							$active      = SurelyWP_Plugin_Panel_SureCart::surelywp_check_plugin_active( $plugin_path );
							$image_url   = $endpoint_url . '/surecart-addons/assets/' . $plugin_data->plugin_slug . '/icon.svg';
							?>
							<div class="grid-view-wrap">
								<div class="inner">
									<div class="header-wrap">
										<div class="img-wrap"><img src="<?php echo esc_url( $image_url ); ?>" /></div>

										<div class="right-status-text">
											<?php
											if ( $installed && $active ) {
												?>
												<span class="active-text"> <?php esc_html_e( 'Active', 'surelywp-toolkit' ); ?> </span>
											<?php } else if ( $installed && ! $active ) { ?>
												<span class="in-active-text"> <?php esc_html_e( 'Inactive', 'surelywp-toolkit' ); ?> </span>
											<?php } else { ?>
												<span class="not-install-text"> <?php esc_html_e( 'Not Installed', 'surelywp-toolkit' ); ?> </span>
											<?php } ?>
										</div>
									</div>
									<h3><?php echo esc_html( $plugin_data->product_name ); ?></h3>
									<div class="description"><?php echo esc_html( $plugin_data->description ); ?></div>
									<div class="addons-btn-wrap">
										<?php
										$url          = esc_url( add_query_arg( 'page', $plugin_data->setting_page, get_admin_url() . 'admin.php' ) );
										$request_page = isset( $_REQUEST['page'] ) && ! empty( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
										$request_tab  = isset( $_REQUEST['tab'] ) && ! empty( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : '';
										$url_activate = esc_url(
											add_query_arg(
												array(
													'page' => $request_page,
													'tab'  => $request_tab,
													'plugin' => $plugin_path,
													'_wpnonce' => wp_create_nonce( 'surelywp-activate' ),
												),
												get_admin_url() . 'admin.php'
											)
										);

										if ( $installed && $active ) {
											?>
											<a href="<?php echo esc_url( $url ); ?>" class="button-primary surelywp-ric-settings-save">
												<img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/icon-mangage.svg' ); ?>">
												<?php esc_html_e( 'Manage', 'surelywp-toolkit' ); ?>
											</a>
										<?php } elseif ( $installed && ! $active ) { ?>
											<a href="<?php echo esc_url( $url_activate ); ?>" data-slug="<?php esc_attr( $plugin_path ); ?>" class="button-primary surelywp-active surelywp-ric-settings-save">
												<img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/surelywp-inactive.svg' ); ?>">
												<?php esc_html_e( 'Activate', 'surelywp-toolkit' ); ?>
											</a>

											<img class="hidden" src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/wp-ajax-loader.gif' ); ?>" id="wp_ajax_loader" />

											<?php
										} else {
											$endpoint_url = surelywp_api_endpoint_url();
											?>
											<a href="<?php echo esc_url( $endpoint_url ); ?>" target="_blank" class="button-primary surelywp-ric-settings-save">
												<img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/surelywp-purchase.svg' ); ?>">
												<?php esc_html_e( 'Purchase', 'surelywp-toolkit' ); ?>
											</a>
										<?php } ?>

									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				<?php } ?>
			</td>
		</tr>
	</tbody>
</table>