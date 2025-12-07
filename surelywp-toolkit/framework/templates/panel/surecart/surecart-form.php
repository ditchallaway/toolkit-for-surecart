<?php
/**
 * SurelyWP Settings Form Template for SureCart
 *
 * Renders the settings form for the SureCart integration in the SurelyWP admin panel.
 *
 * @var string                $option_key  The option key name.
 * @var SurelyWP_Plugin_Panel $panel       The SurelyWP Settings Panel instance.
 * @package                   SurelyWP\Framework\Templates
 * @since                     1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Content Wrapper Class.
$content_class = 'surelywp-admin-panel-content-wrap';

// Generate unique container ID for the settings form.
$container_id = $panel->settings['page'] . '_' . $option_key;

// Prepare global variable for current tab options.
$global = $option_key . '_options';
global $$global;

// Get the title for the current tab.
$tab_title = ( 'surelywp_import_export' !== $option_key ) ? $panel->settings['admin-tabs'][ $option_key ] : array( 'title' => '' );

// Load the settings options for the current tab.
require_once $options_path . '/settings-options.php';
