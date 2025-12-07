<?php
/**
 * Uninstall
 *
 * Does delete the created tables and all the plugin options
 * when uninstalling the plugin
 *
 * @package Toolkit For SureCart
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// check if the plugin really gets uninstalled.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// global varibale.
global $wpdb;

// Remove toolkit setting options.
delete_option( 'surelywp_tk_misc_settings_options' );
delete_option( 'surelywp_tk_dt_settings_options' );
delete_option( 'surelywp_tk_us_settings_options' );
delete_option( 'surelywp_tk_fc_settings_options' );
delete_option( 'surelywp_tk_vm_settings_options' );
delete_option( 'surelywp_tk_lm_settings_options' );

// Remove licensce.
delete_option( 'toolkitforsurecart_license_options' );
delete_option( 'surelywp_toolkit_db_version' );

// global varibale.
global $wpdb;

$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'surelywp_surecart_checkouts' );