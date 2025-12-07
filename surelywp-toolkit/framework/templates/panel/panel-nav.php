<?php
/**
 * Panel Sidebar Navigation Template
 *
 * Used for displaying the sidebar navigation for the SurelyWP panel.
 *
 * @var array                  $nav_args
 * @var SurelyWP_Plugin_Panel $panel
 * @package                    SurelyWP\Framework\Templates
 * @since                      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Extract Wrapper Class.
list( $sidebar_title_class ) = surelywp_framework_extract_variables(
	$nav_args,
	'wrapper_class'
);
?>

<div class="sidebar-wrap">
	<div class="inner">
		<h2 class="sidebar <?php echo esc_attr( $sidebar_title_class ); ?>">
			<ul class="surelywp-plugin-fw-tabs surelywp-plugin-panel-nav">
				<?php
				// Loop Through Admin Tabs.
				foreach ( $panel->settings['admin-tabs'] as $tab_key => $tab_data ) {
					$panel->retrieve_template(
						'panel-nav-item.php',
						array(
							'tab_key'  => $tab_key,
							'tab_data' => $tab_data,
							'nav_args' => $nav_args,
							'panel'    => $panel,
						)
					);
				}
				?>
			</ul>
		</h2>
	</div>
</div>
