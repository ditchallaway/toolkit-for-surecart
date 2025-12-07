<?php
/**
 * The Template is used for displaying the navigation tabs for the panel
 *
 * @var SurelyWP_Plugin_Panel $panel
 * @package         SurelyWP\Framework\Templates
 * @since           1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Display the navigation tabs for the panel.
$panel->render_tabs_navigation();

// Render the main content area of the panel page.
$panel->render_panel_main_content();
