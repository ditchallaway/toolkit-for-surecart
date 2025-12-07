<?php
/**
 * Settings options
 *
 * @author  Surelywp
 * @package Toolkit For SureCart
 * @version 1.0.0
 */

global $surelywp_model;

$tab_name = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';
// Get Addons all options.
$settings_options  = get_option( $option_key . '_options', array() );
$addons_option_key = $option_key . '_options';

?>
<div id="<?php echo esc_attr( $container_id ); ?>" class="surelywp-plugin-fw surelywp-admin-panel-container <?php echo esc_html( $tab_name ); ?> <?php echo ( 'license_key' === $option_key ) ? 'surelywp-licensekey-wrap' : ''; ?>">
	<div class="<?php echo esc_attr( $content_class ); ?>">
		<div class="reset-modal modal">
			<div class="modal-content">
				<span class="close-button">×</span>
				<h4><?php esc_html_e( 'Reset Settings To Default?', 'surelywp-toolkit' ); ?></h4>
				<div class="modal-btn-wrap">
					<div class="text">
						<?php esc_html_e( 'You are about to reset the plugin settings to their default state. Any change you have made will be permanently erased. Are you sure you want to do this?', 'surelywp-toolkit' ); ?>
					</div>
					<form method="post" action="">
						<a href="javascript:void(0)" class="btn-primary button-2 close-modal-button"><?php echo esc_html__( 'Cancel', 'surelywp-toolkit' ); ?></a>
						<input id="surelywp_reset" type="submit" class="button-primary " name="surelywp_ric_settings_reset" class="" value="<?php echo esc_html__( 'Confirm Reset', 'surelywp-toolkit' ); ?>" />
					</form>
				</div>
			</div>
		</div>
		<?php
		$activation        = surelywp_check_license_avtivation( SURELYWP_TOOLKIT_PLUGIN_TITLE );
		$is_licence_active = ! isset( $activation['sc_activation_id'] ) && empty( $activation ) ? false : true;
		if ( ! $is_licence_active ) {
			?>
			<div class="licence-notice">
				<div class="licence-notice-icon">
					<img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/licence-notice-icon.svg' ); ?>" alt="surelywp">	
				</div>
				<div class="licence-notice-text-wrap">
					<div class="licence-notice-heading"><?php printf( esc_html__( 'Welcome to the %s plugin by SurelyWP!', 'surelywp-toolkit' ), SURELYWP_TOOLKIT_PLUGIN_TITLE ); // phpcs:ignore?></div>
					<?php
					$licence_page_url = add_query_arg(
						array(
							'page' => $panel->settings['page'] ?? '',
							'tab'  => 'license_key',
						),
						admin_url( 'admin.php' )
					);
					echo '<div class="licence-notice-sub-heading">' .
					sprintf(
						/* translators: %s: Link to license input */
						esc_html__( 'To begin using the plugin, %s', 'surelywp-toolkit' ),
						'<a href="' . esc_url( $licence_page_url ) . '">' . esc_html__( 'please enter your license key.', 'surelywp-toolkit' ) . '</a>',
					) .
					'</div>';
					?>
				</div>
				<div class="licence-notice-close">
					<img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/close-icon.svg' ); ?>" alt="surelywp">
				</div>
			</div>
		<?php } ?>
		<?php if ( 'license_key' !== $option_key ) { ?>
			<form id="toolkit-settings-form" class="toolkit-settings" method="post" action="options.php" enctype='multipart/form-data'>
				<div class="surelywp-body-content-header ">
					<div class="surelywp-content-title">
						<h2>
						<?php
						$vm_setting_id = isset( $_GET['vm_setting_id'] ) && ! empty( $_GET['vm_setting_id'] ) ? sanitize_text_field( wp_unslash( $_GET['vm_setting_id'] ) ) : '';
						if ( $vm_setting_id ) {
							$option_title = isset( $settings_options[ $vm_setting_id ]['vm_title'] ) && ! empty( $settings_options[ $vm_setting_id ]['vm_title'] ) ? esc_html( $settings_options[ $vm_setting_id ]['vm_title'] ) : '';
							$back_url     = admin_url( 'admin.php' ) . '?page=surelywp_toolkit_panel&tab=surelywp_tk_vm_settings';
							echo '<a href="' . esc_url( $back_url ) . '"><img src="' . esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/Back.svg' ) . '" alt="back-icon" ></a>' . esc_html( $option_title );
						} elseif ( 'Vacation Mode' === $tab_title['title'] ) {
							esc_html_e( 'Vacations', 'surelywp-toolkit' );
						} elseif ( isset( $_GET['tab'] ) && 'surelywp_import_export' === sanitize_text_field( $_GET['tab'] ) ) {
							esc_html_e( 'Import/Export Settings', 'surelywp-toolkit' );
						} else {
							echo esc_html( $tab_title['title'] );
						}
						?>
						</h2>
					</div>
					<div class="header-button-wrap">
						<?php $allow_btn_page = array( 'surelywp_tk_us_settings', 'surelywp_tk_misc_settings', 'surelywp_tk_fc_settings', 'surelywp_tk_ac_settings', 'surelywp_tk_dt_settings', 'surelywp_tk_lm_settings', 'surelywp_tk_pv_settings' ); ?>
						<?php if ( in_array( $option_key, $allow_btn_page, true ) || ! empty( $vm_setting_id ) ) { ?>
							<div class="surelywp-options-reset">
								<a href="javascript:void(0)" class="surelywp-ric-settings-reset reset-trigger">
									<img id="surelywp-er-reset-settings" src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . '/assets/images/reset.svg' ); ?>">
									<?php esc_html_e( 'Reset Settings', 'surelywp-toolkit' ); ?>
								</a>
							</div>
							<div class="surelywp-options-save">
								<input id="surelywp_save" type="submit" class="button-primary surelywp-ric-settings-save" name="surelywp_ric_settings_save" value="<?php esc_html_e( 'Save Changes', 'surelywp-toolkit' ); ?>" />
							</div>
						<?php } ?>
					</div>
					<?php
					if ( 'surelywp_addons_settings' !== $option_key && '_overview' === $option_key ) {
						$endpoint_url = surelywp_api_endpoint_url();
						?>
						<div class="surelywp-options-save">
							<a href="<?php echo esc_url( $endpoint_url ); ?>" target="_blank" class="button-primary surelywp-ric-settings-save"><?php esc_html_e( 'Documantation', 'surelywp-toolkit' ); ?></a>
						</div>
					<?php } ?>

				</div>
				<div class="surelywp-ric-settings-box-wrap">
					<?php

					settings_fields( $option_key . '_options' );

					switch ( $option_key ) :
						case 'overview':
							require_once 'overview.php';
							break;
						case 'surelywp_tk_us_settings':
							require_once 'user-switching-settings.php';
							break;
						case 'surelywp_tk_misc_settings':
							require_once 'misc-settings.php';
							break;
						case 'surelywp_tk_fc_settings':
							require_once 'fluent-crm-settings.php';
							break;
						case 'surelywp_tk_vm_settings':
							require_once 'vacation-mode-settings.php';
							break;
						case 'surelywp_tk_ac_settings':
							require_once 'admin-columns-settings.php';
							break;
						case 'surelywp_tk_dt_settings':
							require_once 'dashboard-tabs-settings.php';
							break;
						case 'surelywp_tk_lm_settings':
							require_once 'lead-magnets-settings.php';
							break;
						case 'surelywp_import_export':
							require_once 'import-export-tab.php';
							break;
						case 'surelywp_tk_pv_settings':
							require_once 'product-visibility-settings.php';
							break;
						case 'changelog':
							require_once 'changelog.php';
							break;
						case 'surelywp_addons_settings':
							require_once 'addons-settings.php';
							break;
						default:
							?>
							<table>

							</table>
							<?php
							break;

					endswitch;
					?>
				</div>
			</form>
		<?php } ?>
		<?php
		if ( 'license_key' === $option_key ) {
			?>
			<div class="license_key_main">
				<?php
				global $client_tk;
				$client_tk->set_textdomain( $panel->settings['plugin_slug'] );
				$endpoint_url   = surelywp_api_endpoint_url();
				$url_activate   = add_query_arg(
					array(
						'page' => $panel->settings['page'],
						'tab'  => 'license_key',
					),
					admin_url( 'admin.php' )
				);
				$url_deactivate = add_query_arg(
					array(
						'page'   => $panel->settings['page'],
						'tab'    => 'license_key',
						'status' => 'deactivate',
					),
					admin_url( 'admin.php' )
				);
				$client_tk->settings()->add_page(
					array(
						'type'                 => 'submenu',                        // Can be: menu, options, submenu.
						'parent_slug'          => $panel->settings['plugin_slug'],  // add your plugin menu slug.
						'page_title'           => esc_html__( 'License Key', 'surelywp-toolkit' ),
						'menu_title'           => esc_html__( 'Licensing', 'surelywp-toolkit' ),
						'capability'           => 'manage_options',
						'menu_slug'            => $panel->settings['page'],
						'icon_url'             => esc_url( $endpoint_url . 'surecart-addons/assets/surelywp-toolkit/icon-128x128.png' ),
						'position'             => null,
						'activated_redirect'   => $url_activate,
						'deactivated_redirect' => $url_deactivate,
						'plugin_name'          => $panel->settings['page_title'],
					)
				);
				$client_tk->settings()->settings_output();
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>