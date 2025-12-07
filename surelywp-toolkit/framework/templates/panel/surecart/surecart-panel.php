<?php
/**
 * SurelyWP Panel Wrapper Template for SureCart
 *
 * Sets up the main wrapper and displays the panel content if tabs are available.
 *
 * @var string $wrap_class           Additional CSS classes for the wrapper.
 * @var array  $available_tabs       List of available tabs for this panel.
 * @var string $page                 The current admin page slug.
 * @package   SurelyWP\Framework\Templates
 * @since     1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="surelywp-plugin-fw__wrap wrap <?php echo esc_attr( $wrap_class ); ?>">

	<!-- Panel Icon -->
	<div id="icon-users" class="icon32">
		<br />
	</div>

	<!-- Panel Body / Content -->
	<div class="wrap_surelywp_body">
		<?php
		// Render the panel content only if tabs are available.
		if ( ! empty( $available_tabs ) ) {
			$this->render_panel_content();
		}
		?>
	</div>
</div>