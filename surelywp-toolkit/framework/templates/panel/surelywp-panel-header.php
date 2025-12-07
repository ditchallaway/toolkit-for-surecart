<?php
/**
 * Addons Panel Header Template
 *
 * Displays the header of the SurelyWP Addons Panel, including:
 * - Logo
 * - Addons dropdown
 * - Notifications
 *
 * @var bool   $is_free
 * @var string $title
 * @package    SurelyWP\Framework\Templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Panel & Plugin Settings.
$addons_page        = $panel->settings['page'];
$current_page_title = $panel->settings['page_title'];
$plugin_slug        = $panel->settings['plugin_slug'];

$available_addons = get_option( 'surelywp_addons_json' );

if ( empty( $available_addons ) ) {
	$endpoint_url     = surelywp_api_endpoint_url() . '/surecart-addons/surelywp-addon.json';
	$available_addons = surelywp_API_response( $endpoint_url );
}

$recent_plugins = get_option( 'surelywp_recently_activated' );
$plugin_folder  = surelywp_get_plugin_folder_name( $plugin_slug );
?>

<div class="surelywp-addons-header-panel" id="surelywp-addons-header-panel">

	<!-- Panel Header Left -->
	<div class="surelywp-addons-header-l">
		<span class="surelywp-addons-icon">
			<img src="<?php echo esc_url( $surelywpLogo ); ?>" class="surelywp-logo-image" alt="<?php esc_attr_e( 'SurelyWP Logo', 'surelywp-framework' ); ?>" />
		</span>

		<div class="custom-select sources">
			<span class="custom-select-trigger">
				<img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_ROOT . $plugin_folder . '/assets/images/' . $plugin_slug . '.svg' ); ?>" />
				<strong><?php echo esc_html( $current_page_title ); ?></strong>
				<img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/Dropdown.svg' ); ?>" alt="<?php esc_attr_e( 'Dropdown Icon', 'surelywp-framework' ); ?>" />
			</span>

			<div class="custom-options">
				<h3><?php esc_html_e( 'My Addons', 'surelywp-framework' ); ?></h3>

				<?php if ( ! empty( $available_addons ) ) : ?>
					<?php foreach ( $available_addons as $plugin_data ) : ?>
						<?php
							$addon_folder     = surelywp_get_plugin_folder_name( $plugin_data->plugin_slug );
							$plugin_file_path = $addon_folder . '/' . $plugin_data->plugin_path;
							$is_active        = SurelyWP_Plugin_Panel_SureCart::surelywp_check_plugin_active( $plugin_file_path );
						?>

						<?php if ( $is_active ) : ?>
							<div class="custom-option <?php echo ( $current_page_title === $plugin_data->product_name ) ? 'active' : ''; ?>" data-value="<?php echo esc_attr( $plugin_data->setting_page ); ?>">
								<span class="surelywp-icon">
									<img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_ROOT . $addon_folder . '/assets/images/' . $plugin_data->plugin_slug . '.svg' ); ?>" alt="<?php esc_attr_e( 'SurelyWP Icon', 'surelywp-framework' ); ?>" />
								</span>
								<span class="addon-label"><?php echo esc_html( $plugin_data->product_name ); ?></span>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php
				$request_page      = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
				$install_addon_url = add_query_arg(
					array(
						'page' => $request_page,
						'tab'  => 'surelywp_addons_settings',
					),
					admin_url( 'admin.php' )
				);
				?>

				<div class="custom-option">
					<img class="drop-down-plus-icon" src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/plus-icon.svg' ); ?>" alt="plus" />
					<a href="<?php echo esc_url( $install_addon_url ); ?>">
						<span class="addon-label"><?php esc_html_e( 'Install New Addon', 'surelywp-framework' ); ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Panel Header Middle -->
	<div class="surelywp-addons-header-m">
		<div class="surelywp-addons-notify">
			<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) : ?>
				<div id="success_msg">
					<p><strong><?php esc_html_e( 'Changes Saved Successfully.', 'surelywp-framework' ); ?></strong></p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Panel Header Right -->
	<div class="surelywp-addons-header-r">
		<div class="notify-menu">
			<?php echo SurelyWP_Notifications::surelywp_render_notification_dropdown(); ?>
		</div>
		<?php
			$request_page = isset( $_REQUEST['page'] ) && ! empty( $_REQUEST['page'] )
				? sanitize_text_field( $_REQUEST['page'] )
				: '';

			$import_export_url = 'javascript:void(0);';

		if ( ! empty( $request_page ) ) {
			$import_export_url = add_query_arg(
				array(
					'page' => $request_page,
					'tab'  => 'surelywp_import_export', // global tab slug.
				),
				admin_url( 'admin.php' )
			);
		}
		?>

		<div class="addons-btn-wrap surelywp-import-export-btn">
			<a href="<?php echo esc_url( $import_export_url ); ?>" class="button-primary surelywp-active surelywp-ric-settings-save">
				<img class="btn-right-arrow" src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/import-export.svg' ); ?>">
				<?php echo esc_html__( 'Import/Export', 'surelywp-framework' ); ?>
			</a>
		</div>
	</div>
</div>
