<?php
/**
 * Navigation Panel Header Template
 *
 * Displays the navigation panel header for SurelyWP.
 *
 * @var SurelyWP_Plugin_Panel $panel
 * @var array                  $tabs_nav_args
 * @var string                 $wrap_class
 * @var bool                   $has_child_tabs
 * @var string                 $page_wrapper_classes
 * @package                    SurelyWP\Framework\Templates
 * @since                      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- Panel Wrapper -->
<div class="<?php echo esc_attr( $page_wrapper_classes ); ?>">
	<div class="<?php echo esc_attr( $wrap_class ); ?>">
		<?php
		// Render Tabs Navigation.
		$panel->render_tabs_navigation( $tabs_nav_args );
		?>
	</div>

	<?php if ( $has_child_tabs ) { ?>
		<!-- Sub-Tab Wrapper -->
		<div class="surelywp-plugin-fw-wp-page__sub-tab-wrap">
	<?php } ?>
