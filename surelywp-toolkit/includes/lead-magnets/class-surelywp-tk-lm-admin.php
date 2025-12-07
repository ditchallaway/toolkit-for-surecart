<?php
/**
 * Admin init class
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @since 1.3
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}


if ( ! class_exists( 'Surelywp_Tk_Lm_Admin' ) ) {

	/**
	 * Initiator class. Create and populate admin views.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	class Surelywp_Tk_Lm_Admin {

		/**
		 * Single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 * @var     \Surelywp_Tk_Lm_Admin
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 * @return \Surelywp_Tk_Lm_Admin
		 */
		public static function get_instance() {

			if ( is_null( static::$instance ) ) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		 * Constructor of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function __construct() {

			// Enqueue admin scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_lm_admin_scripts' ) );

			// Add Html blocks on footer.
			add_action( 'admin_footer', array( $this, 'surelywp_tk_lm_add_blocks' ) );
		}

		/**
		 * Add html on footer.
		 *
		 * @package SurelyWP Lead Magnets
		 * @since   1.0.2
		 */
		public function surelywp_tk_lm_add_blocks() {

			$page   = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$id     = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';

			if ( 'edit' === $action && ! empty( $id ) ) {

				if ( 'sc-products' === $page ) { // add lead magnet enable block on surecart product page.
					$product_id = $id;
					include SURELYWP_TOOLKIT_TEMPLATE_PATH . '/lead-magnets/blocks/admin/product-lead-magnet-block.php';
				}
			}
		}

		/**
		 * Function to enqueue style and script.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_lm_admin_scripts() {

			$tab  = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			wp_register_script( 'surelywp-tk-lm-backend', SURELYWP_TOOLKIT_ASSETS_URL . '/js/lead-magnets/surelywp-tk-lm-backend.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-lm-backend-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/lead-magnets/surelywp-tk-lm-backend.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$localize = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'admin-ajax-nonce' ),
			);

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			if ( 'sc-products' === $page || 'surelywp_tk_lm_settings' === $tab ) {

				// Tinymce scripts.
				wp_enqueue_editor();

				// For language transate in javascript files.
				wp_enqueue_script( 'wp-i18n' );

				wp_enqueue_script( 'surelywp-tk-lm-backend' . $min_file );
				wp_enqueue_style( 'surelywp-tk-lm-backend' . $min_file );
				wp_localize_script( 'surelywp-tk-lm-backend' . $min_file, 'tk_lm_backend_ajax_object', $localize );

				// For Handle language Translation.
				wp_set_script_translations( 'surelywp-tk-lm-backend' . $min_file, 'surelywp-toolkit' );
			}
		}

		/**
		 * Function to default sub form field.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function surelywp_tk_get_default_form_fields() {
			return array(
				'email_address'    =>
					array(
						'heading'     => esc_html__( 'Email Address', 'surelywp-toolkit' ),
						'label_value' => esc_html__( 'Email Address', 'surelywp-toolkit' ),
						'position'    => 0,
						'is_show'     => true,
						'is_required' => true,
					),
				'first_name'       =>
					array(
						'heading'     => esc_html__( 'First Name', 'surelywp-toolkit' ),
						'label_value' => esc_html__( 'First Name', 'surelywp-toolkit' ),
						'position'    => 1,
						'is_show'     => true,
						'is_required' => true,
					),
				'last_name'        =>
					array(
						'heading'     => esc_html__( 'Last Name', 'surelywp-toolkit' ),
						'label_value' => esc_html__( 'Last Name', 'surelywp-toolkit' ),
						'position'    => 2,
						'is_show'     => true,
						'is_required' => true,
					),
				'consent_checkbox' =>
					array(
						'heading'             => esc_html__( 'Consent Checkbox', 'surelywp-toolkit' ),
						// translators: %s: Privacy policy link.
						'label_value'         => sprintf( esc_html__( 'I agree to receive emails and accept the %s', 'surelywp-toolkit' ), '{privacy_policy_link}.' ),
						'privacy_policy_link' => '',
						'position'            => 3,
						'is_show'             => true,
						'is_required'         => true,
					),
			);
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Lm_Admin class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	function Surelywp_Tk_Lm_Admin() {  // phpcs:ignore
		$instance = Surelywp_Tk_Lm_Admin::get_instance();
		return $instance;
	}
}
