<?php
/**
 * Handles loading and managing plugin assets (CSS/JS).
 *
 * @since   1.0.0
 * @package SurelyWP\Framework\Classes
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SurelyWP_Assets' ) ) {

	/**
	 * Class for handling plugin assets (CSS/JS).
	 *
	 * @package SurelyWP\Framework\Classes
	 * @since   1.0.0
	 */
	class SurelyWP_Assets {

		/**
		 * The object of the class.
		 *
		 * @var SurelyWP_Assets
		 */
		private static $instance;

		/**
		 * The version
		 *
		 * @var string
		 */
		public $version = '1.4';

		/**
		 * Class constructor.
		 *
		 * Initializes the assets manager by hooking into WordPress actions
		 * to register and enqueue styles and scripts for the admin area.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since 1.0.0
		 */
		private function __construct() {
			// call admin assets.
			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_setup_admin_styles_scripts' ) );
		}

		/**
		 * Retrieve the singleton instance of the class.
		 *
		 * Ensures that only one instance of the class is created
		 * throughout the application lifecycle.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since 1.0.0
		 *
		 * @return SurelyWP_Assets The single, shared instance of the class.
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Register plugin styles and scripts.
		 *
		 * Handles the registration of CSS and JavaScript assets
		 * required for the SurelyWP framework. Depending on whether
		 * `SCRIPT_DEBUG` is enabled, it will load either the minified
		 * or unminified versions of the assets.
		 *
		 * Also localizes script data for AJAX requests in the admin area.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since 1.0.0
		 */
		public function surelywp_setup_admin_styles_scripts() {

			// Register Styles.

			// Fonts.
			wp_register_style( 'surelywp-poppins-fonts', SURELYWP_CORE_PLUGIN_URL . '/assets/css/poppins.min.css', '', $this->version );

			// Framework & Plugin Styles.
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				wp_register_style( 'surelywp-framework-fields', SURELYWP_CORE_PLUGIN_URL . '/assets/css/surelywp-fields.css', '', $this->version );

				wp_register_style( 'surelywp-plugin-style', SURELYWP_CORE_PLUGIN_URL . '/assets/css/surelywp-plugin-panel.css', '', $this->version );
			} else {
				wp_register_style( 'surelywp-framework-fields', SURELYWP_CORE_PLUGIN_URL . '/assets/css/surelywp-fields.min.css', '', $this->version );
				wp_register_style( 'surelywp-plugin-style', SURELYWP_CORE_PLUGIN_URL . '/assets/css/surelywp-plugin-panel.min.css', '', $this->version );
			}

			// Select2.
			wp_register_style( 'surelywp-addons-select2', SURELYWP_CORE_PLUGIN_URL . '/assets/css/select2.min.css', '', $this->version );

			// Register Scripts.

			// Select2.
			wp_register_script( 'surelywp-select2', SURELYWP_CORE_PLUGIN_URL . '/assets/js/select2.min.js', array( 'jquery' ), $this->version, true );

			// UI Script.
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				wp_register_script( 'surelywp-ui', SURELYWP_CORE_PLUGIN_URL . '/assets/js/surelywp-ui.js', array( 'jquery' ), $this->version, true );
			} else {
				wp_register_script( 'surelywp-ui', SURELYWP_CORE_PLUGIN_URL . '/assets/js/surelywp-ui.min.js', array( 'jquery' ), $this->version, true );
			}

			// Localize Scripts.
			wp_localize_script(
				'surelywp-ui',
				'ajax_obj',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'surlywp_notification_nonce' ),
				)
			);
		}
	}
}

SurelyWP_Assets::instance();
