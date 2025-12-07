<?php
/**
 * Main class for Misc Settings.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.1
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

if ( ! class_exists( 'Surelywp_Tk_Vm_Admin' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.1
	 */
	class Surelywp_Tk_Vm_Admin {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Vm_Admin
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 * @return  \Surelywp_Tk_Vm_Admin
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor function for the Surelywp Supports class.
		 *
		 * Initializes the class and sets up various actions and filters.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public function __construct() {

			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_vm_settings_scripts' ) );
		}

		/**
		 * Function to enqueue scripts and styles for the front end.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public function surelywp_tk_vm_settings_scripts() {

			$tab  = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			// misc settings backend js.
			wp_register_script( 'surelywp-tk-vm-backend', SURELYWP_TOOLKIT_ASSETS_URL . '/js/vacation-mode/surelywp-tk-vm-backend.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-vm-backend-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/vacation-mode/surelywp-tk-vm-backend.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			// misc settings js enqueue.
			if ( 'surelywp_tk_vm_settings' === $tab ) {
				wp_enqueue_script( 'surelywp-tk-vm-backend' . $min_file );
			}
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Vm_Admin class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.1
	 */
	function Surelywp_Tk_Vm_Admin() {  // phpcs:ignore
		$instance = Surelywp_Tk_Vm_Admin::get_instance();
		return $instance;
	}
}
