<?php
/**
 * Framework Name: SurelyWP Plugin Framework
 * Version: 1.0.0
 * Author: SurelyWP
 * Text Domain: surelywp-framework
 * Domain Path: /languages/
 *
 * @author  SurelyWP
 * @version 1.0.0
 * @package SurelyWP\Framework
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'surelywp_load_framework' ) ) {

	/**
	 * Handle to load the framework file if not loaded.
	 *
	 * @param string $path The plugin path.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_load_framework( $path ) {

		$plugin_fw_main_file = $path . 'framework/surelywp-addons.php';
	}
}
