<?php
/**
 * Main class for Fluent Crm Ajax Handler.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.2
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use FluentCrm\App\Models\Subscriber;

if ( ! class_exists( 'Surelywp_Tk_Fc_Ajax_Handler' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2
	 */
	class Surelywp_Tk_Fc_Ajax_Handler {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Fc_Ajax_Handler
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2
		 * @return  \Surelywp_Tk_Fc_Ajax_Handler
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
		 * @since   1.2
		 */
		public function __construct() {

			add_action( 'wp_ajax_surelywp_tk_fc_get_profile_btn', array( $this, 'surelywp_tk_fc_get_profile_btn' ) );
		}

		/**
		 * Function to manage new service request.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.4
		 */
		public function surelywp_tk_fc_get_profile_btn() {

			$customer_email = isset( $_POST['customer_email'] ) && ! empty( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';

			if ( $customer_email ) {

				$fc_url_base = fluentcrm_menu_url_base();
				$subscriber  = Subscriber::where( 'email', $customer_email )->first();

				if ( ! $subscriber ) {

					echo wp_json_encode(
						array(
							'status'  => false,
							'message' => esc_html__( 'Subscriber Not Fount', 'surelywp-toolkit' ),
						)
					);
					wp_die();
				}

				$fc_profile_url = $fc_url_base . 'subscribers/' . $subscriber->id ?? '';

				$fc_profile_btn = '<div class="surelywp-fc-profile-btn" style="width:100%"><sc-button outline type="primary" size="small" href="' . esc_url( $fc_profile_url ) . '">' . esc_html__( 'FluentCRM Contact Profile', 'surelywp-toolkit' ) . '</sc-button></div>';

				echo wp_json_encode(
					array(
						'status'         => true,
						'fc_profile_btn' => $fc_profile_btn,
					)
				);
				wp_die();
			}

			echo wp_json_encode(
				array(
					'status'  => false,
					'message' => esc_html__( 'Something went wrong please try again.', 'surelywp-toolkit' ),
				)
			);
			wp_die();
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Fc_Ajax_Handler class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2
	 */
	function Surelywp_Tk_Fc_Ajax_Handler() {  // phpcs:ignore
		$instance = Surelywp_Tk_Fc_Ajax_Handler::get_instance();
		return $instance;
	}
}
