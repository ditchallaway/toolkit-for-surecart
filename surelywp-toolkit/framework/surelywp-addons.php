<?php
/**
 * SurlyWP Plugin Framework Loader
 *
 * Sets up constants, loads core files, and initializes translations.
 *
 * @package SurlyWP\Framework
 * @since   1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define plugin constants if not already set.
if ( ! defined( 'SURELYWP_CORE_PLUGIN' ) ) {
	define( 'SURELYWP_CORE_PLUGIN', true );
}
if ( ! defined( 'SURELYWP_CORE_PLUGIN_FRAMEWORK_VERSION' ) ) {
	define( 'SURELYWP_CORE_PLUGIN_FRAMEWORK_VERSION', '1.0.0' );
}
if ( ! defined( 'SURELYWP_CORE_PLUGIN_PATH' ) ) {
	define( 'SURELYWP_CORE_PLUGIN_PATH', dirname( __FILE__ ) );
}

if ( ! defined( 'SURELYWP_CORE_PLUGIN_URL' ) ) {
	define( 'SURELYWP_CORE_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
}

if ( ! defined( 'SURELYWP_CORE_PLUGIN_TEMPLATE_PATH' ) ) {
	define( 'SURELYWP_CORE_PLUGIN_TEMPLATE_PATH', SURELYWP_CORE_PLUGIN_PATH . '/templates' );
}

if ( ! defined( 'SURELYWP_CORE_PLUGIN_SLUG' ) ) {
	define( 'SURELYWP_CORE_PLUGIN_SLUG', 'surelywp-framework' );
}

if ( ! defined( 'SURELYWP_CORE_PLUGIN_ROOT' ) ) {
	define( 'SURELYWP_CORE_PLUGIN_ROOT', plugin_dir_url( '/', __FILE__ ) );
}

// Includes all class files.
require_once 'includes/class-surelywp-plugin-panel.php';
require_once 'includes/class-surelywp-plugin-panel-surecart.php';
require_once 'includes/class-surelywp-assets.php';
require_once 'includes/class-surelywp-notification.php';
require_once 'includes/class-surelywp-model.php';

// Load plugin translations.
load_textdomain( 'surelywp-framework', dirname( __FILE__ ) . '/languages/surelywp-framework-' . apply_filters( 'plugin_locale', determine_locale(), 'surelywp-framework' ) . '.mo' );

if ( ! function_exists( 'surelywp_plugin_framework_row_meta' ) ) {
	/**
	 * Add custom links to the plugin row in the plugins list.
	 *
	 * @param array  $links_array Existing plugin links.
	 * @param string $file_name   Plugin file name.
	 * @param array  $plugin_data Plugin data.
	 * @param string $status      Plugin status.
	 * @return array Modified links array.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_plugin_framework_row_meta( $links_array, $file_name, $plugin_data, $status ) {

		if ( false === strstr( $file_name, 'surelywp' ) ) {
			return $links_array;
		}

		return $links_array;
	}
}
add_filter( 'plugin_row_meta', 'surelywp_plugin_framework_row_meta', 20, 4 );
