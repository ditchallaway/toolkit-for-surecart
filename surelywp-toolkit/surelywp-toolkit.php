<?php
/**
 * Plugin Name: Toolkit For SureCart
 * Plugin URI: https://surelywp.com
 * Description: This plugin brings together essential admin tools for SureCart store owners, including direct FluentCRM integration, user switching, external products, vacation mode, custom admin columns, customer dashboard customizer, download lists, product visibility overrides, and many more miscellaneous tools.
 * Version: 1.5
 * Tested up to: 6.8.2
 * Author: SurelyWP
 * Author URI: https://surelywp.com
 * Text Domain: surelywp-toolkit
 * Domain Path: /languages/
 *
 * @package Toolkit For SureCart
 * @author SurelyWP
 * @category Core
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Basic Plugin Definitions
 *
 * @package Toolkit For SureCart
 * @since 1.0.0
 */
if ( ! defined( 'SURELYWP_TOOLKIT_VERSION' ) ) {
	define( 'SURELYWP_TOOLKIT_VERSION', '1.5' );
}
if ( ! defined( 'SURELYWP_TOOLKIT_INIT' ) ) {
	define( 'SURELYWP_TOOLKIT_INIT', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	define( 'SURELYWP_TOOLKIT', true );
}
if ( ! defined( 'SURELYWP_TOOLKIT_FILE' ) ) {
	define( 'SURELYWP_TOOLKIT_FILE', __FILE__ );
}
if ( ! defined( 'SURELYWP_TOOLKIT_URL' ) ) {
	define( 'SURELYWP_TOOLKIT_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'SURELYWP_TOOLKIT_DIR' ) ) {
	define( 'SURELYWP_TOOLKIT_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SURELYWP_TOOLKIT_PLUGIN_TITLE' ) ) {
	define( 'SURELYWP_TOOLKIT_PLUGIN_TITLE', 'Toolkit For SureCart' );
}

if ( ! defined( 'SURELYWP_TOOLKIT_TEMPLATE_PATH' ) ) {
	define( 'SURELYWP_TOOLKIT_TEMPLATE_PATH', SURELYWP_TOOLKIT_DIR . 'templates' );
}
if ( ! defined( 'SURELYWP_TOOLKIT_ASSETS_URL' ) ) {
	define( 'SURELYWP_TOOLKIT_ASSETS_URL', SURELYWP_TOOLKIT_URL . 'assets' );
}
if ( ! defined( 'SURELYWP_TOOLKIT_SLUG' ) ) {
	define( 'SURELYWP_TOOLKIT_SLUG', 'surelywp-toolkit' );
}

if ( ! defined( 'SURELYWP_TOOLKIT_BASENAME' ) ) {
	define( 'SURELYWP_TOOLKIT_BASENAME', basename( SURELYWP_TOOLKIT_DIR ) );
}

if ( ! defined( 'SURELYWP_TOOLKIT_META_PREFIX' ) ) {
	define( 'SURELYWP_TOOLKIT_META_PREFIX', '_surelywp_tk_' );
}
if ( ! defined( 'SURELYWP_TOOLKIT_IE_FILE_SIZE' ) ) {
	define( 'SURELYWP_TOOLKIT_IE_FILE_SIZE', '5' );
}

/**
 * Error message if Surecart is not installed
 *
 * @package Toolkit For SureCart
 * @since 1.0.0
 */
function surelywp_tk_install_admin_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'Toolkit For SureCart For SureCart is enabled but not effective. It requires SureCart in order to work.', 'surelywp-toolkit' ); ?></p>
	</div>
	<?php
}

/**
 * Plugin Framework Version Check
 *
 * @package Toolkit For SureCart
 * @since 1.0.0
 */
if ( ! function_exists( 'surelywp_load_framework' ) && file_exists( SURELYWP_TOOLKIT_DIR . 'framework/framework.php' ) ) {
	require_once SURELYWP_TOOLKIT_DIR . 'framework/framework.php';
}
surelywp_load_framework( SURELYWP_TOOLKIT_DIR );

/**
 * Activation Hook
 *
 * Register plugin activation hook.
 *
 * @package Toolkit For SureCart
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'surelywp_plugin_registration_hook' );

if ( ! function_exists( 'surelywp_plugin_registration_hook' ) ) {
	require_once 'framework/surelywp-plugin-registration-hook.php';
}

/**
 * Deactivation Hook
 *
 * Register plugin deactivation hook.
 *
 * @package Toolkit For SureCart
 * @since 1.0.0
 */
function surelywp_tk_deactivation_hook() {

	surelywp_register_deactivation_hook( plugin_basename( __FILE__ ) );
	if ( ! class_exists( 'SureCart' ) ) {
		return;
	}
	// Reset surecart default events.
	update_option( 'surelywp_tk_plugin_activated', false );
}
register_deactivation_hook( __FILE__, 'surelywp_tk_deactivation_hook' );


/**
 * Initialize global variables
 *
 * @package Toolkit For SureCart
 * @since 1.0.0
 */
global $client_tk;

/**
 * Init plugin
 *
 * @package Toolkit For SureCart
 * @since 1.0.0
 */
function surelywp_tk_constructor() {

	if ( ! class_exists( 'SureCart' ) ) {
		add_action( 'admin_notices', 'surelywp_tk_install_admin_notice' );
		return;
	}

	// Load Plugin TextDomain.
	load_plugin_textdomain( 'surelywp-toolkit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	require_once SURELYWP_TOOLKIT_DIR . 'framework/surelywp-functions.php';
	require_once SURELYWP_TOOLKIT_DIR . 'framework/surelywp-addons.php';

	if ( is_admin() ) {
		require_once 'includes/class-surelywp-toolkit-admin.php';
		$toolkit_admin_obj = new Surelywp_Toolkit_Admin();
	}

	require_once 'includes/surelywp-toolkit-functions.php';
	require_once 'includes/class-surelywp-toolkit.php';
	require_once 'includes/class-surelywp-toolkit-install.php';
	require_once 'includes/class-surelywp-toolkit-model.php';

	// load blocks.
	require_once 'blocks/vacation-mode/surelywp-tk-vm-notice/surelywp-tk-vm-notice.php';
	require_once 'blocks/lead-magnets/surelywp-tk-lm-button/surelywp-tk-lm-button.php';

	$toolkit_obj = Surelywp_Toolkit();

	// updater.
	$toolkit_obj->surelywp_tk_update_plugin();

	// Check if the plugin has been activated before.
	if ( ! get_option( 'surelywp_tk_plugin_activated' ) ) {

		$toolkit_obj->surelywp_tk_on_plugin_active();

		// Set a flag to indicate that the plugin has been activated.
		update_option( 'surelywp_tk_plugin_activated', true );
	}
}
add_action( 'plugins_loaded', 'surelywp_tk_constructor', 11 );

/* Licensing */
if ( ! class_exists( 'SureCart\Licensing\Client' ) ) {
	require_once SURELYWP_TOOLKIT_DIR . 'licensing/src/Client.php';
}

if ( class_exists( 'SureCart\Licensing\Client' ) ) {

	require_once SURELYWP_TOOLKIT_DIR . 'framework/surelywp-functions.php';

	add_action(
		'init',
		function () {
			global $client_tk;

			// Default public API token.
			$sc_public_api_token = '';
			if ( function_exists( 'surelywp_get_public_token' ) ) {
				$sc_public_api_token = surelywp_get_public_token();
			}

			if ( ! empty( $sc_public_api_token ) ) {
				$client_tk = new \SureCart\Licensing\Client( SURELYWP_TOOLKIT_PLUGIN_TITLE, $sc_public_api_token, __FILE__ );
			} else {
				$client_tk = new \SureCart\Licensing\Client( SURELYWP_TOOLKIT_PLUGIN_TITLE, __FILE__ );
			}
		}
	);
}
