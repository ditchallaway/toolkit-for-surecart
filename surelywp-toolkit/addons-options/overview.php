<?php
/**
 * Plugin Overview
 *
 * @author  Surelywp
 * @package Toolkit For SureCart
 * @version 1.0.0
 */

$endpoint_url = surelywp_api_endpoint_url();

$contact_us_url = $endpoint_url . '/contact';
?>
<div class="overview-page">
	<div class="con">
		<div class="overview-icon"><img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/toolkit-overview.svg' ); ?>" alt="toolkit-overview"></div>
		<div>
			<div class="title">
				<h3>
				<?php
					// translators: %s is the plugin title.
					printf( esc_html__( 'Welcome to the %s Plugin!', 'surelywp-toolkit' ), esc_html( SURELYWP_TOOLKIT_PLUGIN_TITLE ) );
				?>
				</h3>
			</div>
			<div class="description">
				<?php esc_html_e( 'This plugin brings together essential admin tools for SureCart store owners, including direct FluentCRM integration, user switching, external products, vacation mode, custom admin columns, customer dashboard customizer, download lists, product visibility overrides, and many more miscellaneous tools.', 'surelywp-toolkit' ); ?>
			</div>
		</div>
	</div>
	<div class="plugin-reso">
		<div class="plugin-reso-head">
			<h2><?php esc_html_e( 'Plugin Resources', 'surelywp-toolkit' ); ?></h2>
		</div>
		<table class="form-table surelywp-ric-settings-box">
			<tbody>
				<tr class="surelywp-field-label">
					<td>
						<div class="grid-view row">
							<div class="grid-view-wrap">
								<div class="inner">
									<div class="header-wrap">
										<img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/license-Key-icon.svg' ); ?>" alt="">
									</div>
									<h3><?php esc_html_e( 'Add Your License Key', 'surelywp-toolkit' ); ?></h3>
									<div class="description"><?php esc_html_e( 'To begin using the plugin, be sure to add your license key to enable the features.', 'surelywp-toolkit' ); ?></div>
									<div class="addons-btn-wrap">
									<?php
										$licence_page_url = add_query_arg(
											array(
												'page' => $panel->settings['page'] ?? '',
												'tab'  => 'license_key',
											),
											admin_url( 'admin.php' )
										);
										?>
									<a href="<?php echo esc_url( $licence_page_url ); ?>" class="button-primary surelywp-active surelywp-ric-settings-save">
										<?php esc_html_e( 'License Key', 'surelywp-toolkit' ); ?>
										<img class="btn-right-arrow" src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/right-arrow.svg' ); ?>" />
									</a>
									</div>
								</div>
							</div>
							<div class="grid-view-wrap">
								<div class="inner">
								<div class="header-wrap">
									<img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/list-icon.svg' ); ?>" alt="">
								</div>
									<h3><?php esc_html_e( 'View The Changelog', 'surelywp-toolkit' ); ?></h3>
									<div class="description"><?php esc_html_e( 'Stay up to date with the latest features, improvements, and fixes.', 'surelywp-toolkit' ); ?></div>
									<div class="addons-btn-wrap">
									<?php
										$changelog_url = add_query_arg(
											array(
												'page' => $panel->settings['page'] ?? '',
												'tab'  => 'changelog',
											),
											admin_url( 'admin.php' )
										);
										?>
									<a href="<?php echo esc_url( $changelog_url ); ?>" class="button-primary surelywp-active surelywp-ric-settings-save">
										<?php esc_html_e( 'View Changelog', 'surelywp-toolkit' ); ?>
										<img class="btn-right-arrow" src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/right-arrow.svg' ); ?>" />
									</a>
									</div>
								</div>
							</div>
							<div class="grid-view-wrap">
								<div class="inner">
								<div class="header-wrap">
									<img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/tutorial-icon.svg' ); ?>" alt="">
								</div>
									<h3><?php esc_html_e( 'Feature Requests', 'surelywp-toolkit' ); ?></h3>
									<div class="description"><?php esc_html_e( 'Share your ideas and help shape the future of this plugin.', 'surelywp-toolkit' ); ?></div>
									<div class="addons-btn-wrap">
									<a target="_blank" href="<?php echo esc_url( $contact_us_url ); ?>" class="button-primary surelywp-active surelywp-ric-settings-save">
										<?php esc_html_e( 'Share Ideas', 'surelywp-toolkit' ); ?>
										<img class="btn-goto-arrow" src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/goto-arrow.svg' ); ?>" />
									</a>
									</div>
								</div>
							</div>
							<div class="grid-view-wrap">
								<div class="inner">
								<div class="header-wrap">
									<img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/support-icon.svg' ); ?>" alt="">
								</div>
									<h3><?php esc_html_e( 'Get Support', 'surelywp-toolkit' ); ?></h3>
									<div class="description"><?php esc_html_e( 'Need help? Browse troubleshooting tips or contact our support team.', 'surelywp-toolkit' ); ?></div>
									<div class="addons-btn-wrap">
									<a target="_blank" href="<?php echo esc_url( $contact_us_url ); ?>" class="button-primary surelywp-active surelywp-ric-settings-save">
										<?php esc_html_e( 'Contact Us', 'surelywp-toolkit' ); ?>
										<img class="btn-goto-arrow" src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/goto-arrow.svg' ); ?>" />
									</a>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="cta">
		<div class="cta-main">
			<div class="cta-main-img"><img src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/surelywp-cta-icon.svg' ); ?>" alt=""></div>
			<div class="cta-main-cont">
				<h3><?php esc_html_e( 'Explore Our Other SurelyWP Addons', 'surelywp-toolkit' ); ?></h3>
				<div><?php esc_html_e( 'Be sure to  check out our other helpful addons for SureCart admins and store owners!', 'surelywp-toolkit' ); ?></div>
					<?php
					$surelywp_addons_url = add_query_arg(
						array(
							'page' => $panel->settings['page'] ?? '',
							'tab'  => 'surelywp_addons_settings',
						),
						admin_url( 'admin.php' )
					);
					?>
				<a href="<?php echo esc_url( $surelywp_addons_url ); ?>" class="btn"><?php esc_html_e( 'View All Addons', 'surelywp-toolkit' ); ?><img class="btn-right-arrow" src="<?php echo esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/right-arrow-black.svg' ); ?>" /></a>
			</div>
		</div>
	</div>
</div>