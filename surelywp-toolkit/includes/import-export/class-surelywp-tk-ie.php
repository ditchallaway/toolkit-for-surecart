<?php
/**
 * Main class for Import/Export.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.5
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}


if ( ! class_exists( 'Surelywp_Tk_Ie' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.5
	 */
	class Surelywp_Tk_Ie {

		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Ie
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.5
		 * @return  \Surelywp_Tk_Ie
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
		 * @since   1.5
		 */
		public function __construct() {
			// Admin Enqueue scipts.
			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_ie_settings_admin_scripts' ) );

			add_action( 'admin_init', array( $this, 'surelywp_tk_handle_export' ) );
			add_action( 'wp_ajax_surelywp_tk_import_settings', array( $this, 'surelywp_tk_handle_import' ) );
		}

		/**
		 * Function to enqueue scripts and styles for the backend.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.5
		 */
		public function surelywp_tk_ie_settings_admin_scripts() {

			$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			// import/export settings backend js.
			wp_register_script( 'surelywp-tk-backend', SURELYWP_TOOLKIT_ASSETS_URL . '/js/surelywp-tk-backend.js', array(), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-backend-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/surelywp-tk-backend.min.js', array(), SURELYWP_TOOLKIT_VERSION, true );

			$localize = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'admin-ajax-nonce' ),
				'file_types' => array( 'application/json' ),
				'file_size'  => SURELYWP_TOOLKIT_IE_FILE_SIZE,
			);

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			if ( 'surelywp_toolkit_panel' === $page ) {
				// backend script and style.
				wp_enqueue_style( 'surelywp-tk-settings' . $min_file );

				wp_enqueue_script( 'surelywp-tk-backend' . $min_file );
				wp_localize_script( 'surelywp-tk-backend' . $min_file, 'tk_backend_ajax_object', $localize );
			}
			// For Handle language Translation.
			wp_set_script_translations( 'surelywp-tk-backend' . $min_file, 'surelywp-toolkit' );
		}

		/**
		 * Return all option keys we want to manage for import export.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.5
		 */
		public static function get_keys() {
			return array(
				'surelywp_tk_misc_settings_options',
				'surelywp_tk_dt_settings_options',
				'surelywp_tk_us_settings_options',
				'surelywp_tk_fc_settings_options',
				'surelywp_tk_vm_settings_options',
				'surelywp_tk_lm_settings_options',
				'surelywp_tk_pv_settings_options',
				'surelywp_tk_ac_settings_options',
			);
		}

		/**
		 * The Function will export all the Toolkit plugin settings.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.5
		 */
		public function surelywp_tk_handle_export() {
			if ( isset( $_POST['surelywp_tk_export'] ) && check_admin_referer( 'surelywp_tk_export_settings', 'surelywp_export_nonce' ) ) {
				$settings = array();

				foreach ( self::get_keys() as $key ) {
					$settings[ $key ] = get_option( $key );
				}

				header( 'Content-Disposition: attachment; filename=surelywp-toolkit-settings.json' );
				header( 'Content-Type: application/json; charset=utf-8' );
				echo wp_json_encode( $settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
				exit;
			}
		}



		/**
		 * The Function will import all the Toolkit plugin settings.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.5
		 */
		public function surelywp_tk_handle_import() {
			if ( check_admin_referer( 'surelywp_tk_import_settings', 'surelywp_import_nonce' ) ) {
				$import_settings_tk_tmp_name = isset( $_FILES['import_tk_file']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['import_tk_file']['tmp_name'] ) ) : '';
				if ( empty( $import_settings_tk_tmp_name ) ) {
					wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'surelywp-toolkit' ) ) );
					wp_die();
				}
				if ( ! empty( $import_settings_tk_tmp_name ) ) {
					if ( is_uploaded_file( $import_settings_tk_tmp_name ) ) {
						$data     = file_get_contents( $import_settings_tk_tmp_name );
						$settings = json_decode( $data, true );
					} else {
						wp_send_json_error( array( 'message' => __( 'Invalid file upload.', 'surelywp-toolkit' ) ) );
						wp_die();
					}

					if ( is_array( $settings ) ) {
						foreach ( self::get_keys() as $key ) {
							if ( isset( $settings[ $key ] ) ) {
								update_option( $key, $settings[ $key ] );
							} else {
								wp_send_json_error( array( 'message' => __( 'Invalid Toolkit Plugin Settings', 'surelywp-toolkit' ) ) );
								wp_die();
							}
						}
					} else {
						wp_send_json_error( array( 'message' => __( 'Invalid JSON file.', 'surelywp-toolkit' ) ) );
						wp_die();
					}
				}
			}
			wp_die();
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Ie class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.5
	 */
	function Surelywp_Tk_Ie() {  // phpcs:ignore
		$instance = Surelywp_Tk_Ie::get_instance();
		return $instance;
	}

}
