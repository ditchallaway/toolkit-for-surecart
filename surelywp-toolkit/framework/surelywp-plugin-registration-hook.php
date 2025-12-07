<?php
/**
 * Callbacks for plugin activation and deactivation hooks.
 *
 * @package SurelyWP\Framework
 * @since   1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Handle plugin activation: save default settings and schedule notifications.
if ( ! function_exists( 'surelywp_plugin_registration_hook' ) ) {

	/**
	 * Runs on plugin activation.
	 * Stores the plugin as recently activated and schedules daily notifications.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_plugin_registration_hook() {
		// Get the list of recently activated plugins.
		$options  = get_option( 'surelywp_recently_activated', array() );
		$hookname = str_replace( 'activate_', '', current_filter() );

		// Extract the plugin file name.
		$name_item = explode( '/', $hookname );
		$file_name = $name_item[1];

		// Remove any previous entries for this plugin.
		foreach ( $options as $key => $plugin_file ) {

			if ( strpos( $plugin_file, $file_name ) !== false ) {

				unset( $options[ $key ] );
			}
		}

		$options[] = $hookname;
		update_option( 'surelywp_recently_activated', $options );

		if ( ! wp_next_scheduled( 'surelywp_notification_daily' ) ) {
			wp_schedule_event( mktime( 0, 0, 0 ), 'daily', 'surelywp_notification_daily' );
		}
	}
}

// Handle plugin deactivation: remove from recently activated list.
if ( ! function_exists( 'surelywp_register_deactivation_hook' ) ) {

	/**
	 * Runs on plugin deactivation.
	 * Removes the plugin from the recently activated list.
	 *
	 * Use this function with register_deactivation_hook.
	 *
	 * @param string $file The plugin file path.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_register_deactivation_hook( $file ) {

		$options = get_option( 'surelywp_recently_activated' );

		foreach ( $options as $key => $plugin_file ) {
			if ( $file == $plugin_file ) {
				unset( $options[ $key ] );
			}
		}
		update_option( 'surelywp_recently_activated', $options );
	}
}
