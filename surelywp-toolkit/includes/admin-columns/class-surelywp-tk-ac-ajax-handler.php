<?php
/**
 * Main class for Admin Column Ajax Handler.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.2
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\AbandonedCheckout;
use SureCart\Models\Subscription;
use SureCart\Models\Checkout;


if ( ! class_exists( 'Surelywp_Tk_Ac_Ajax_Handler' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2
	 */
	class Surelywp_Tk_Ac_Ajax_Handler {



		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Ac_Ajax_Handler
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2
		 * @return  \Surelywp_Tk_Ac_Ajax_Handler
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
			// Register AJAX handler for fetching recovery status.
			add_action( 'wp_ajax_surelywp_tk_ac_get_recovery_status', array( $this, 'surelywp_tk_ac_get_recovery_status' ) );

			// Register AJAX handler for fetching order product name and subscription type.
			add_action( 'wp_ajax_surelywp_tk_ac_get_order_info', array( $this, 'surelywp_tk_ac_get_order_info' ) );
		}


		/**
		 * Function to get Order Product Name and Subscription type.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.2
		 */
		public function surelywp_tk_ac_get_order_info() {

			if ( ! check_ajax_referer( 'admin-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce.', 'surelywp-toolkit' ) ) );
			}

			global $surelywp_model;

			$checkout_ids = isset( $_POST['checkout_ids'] ) && ! empty( $_POST['checkout_ids'] ) && is_array( $_POST['checkout_ids'] )
				? $surelywp_model->surelywp_escape_slashes_deep( $_POST['checkout_ids'] )
				: array();

			// Validate that at least one checkout ID exists.
			if ( empty( $checkout_ids ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Missing checkout IDs.', 'surelywp-toolkit' ) ) );
			}

			$response_data                              = array();
			$surelywp_tk_ac_order_product_name_sub_type = get_transient( 'surelywp_tk_ac_order_product_name_sub_type' );
			if ( $surelywp_tk_ac_order_product_name_sub_type ) {
				$chached_ids = array_keys( $surelywp_tk_ac_order_product_name_sub_type );
				if ( ! array_diff( $chached_ids, $checkout_ids ) ) {
					wp_send_json_success( $surelywp_tk_ac_order_product_name_sub_type );
				}
			}

			$checkout_with = array();
			$admin_columns = Surelywp_Tk_Admin_Columns::get_settings_option( 'admin_columns' );

			if ( isset( $admin_columns['order']['order_product_name']['is_show'] ) ) {
				$checkout_with = array( 'line_items', 'line_items.price', 'price.product' );
			}

			if ( isset( $admin_columns['order']['subscription_type']['is_show'] ) ) {
				$checkout_with = array_merge( $checkout_with, array( 'purchases', 'purchases.subscription' ) );
			}

			// Fetch checkouts with necessary relations.
			$checkout_collection = Checkout::where( array( 'ids' => $checkout_ids ) )
				->with( $checkout_with )
				->get();

			if ( ! empty( $checkout_collection ) ) {

				foreach ( $checkout_collection as $checkout ) {

					$checkout_id      = $checkout->id;
					$product_tag      = '';
					$subscription_tag = '-';

					$line_items       = $checkout->line_items->data ?? array();
					$total_line_items = count( $line_items );

					if ( ! empty( $line_items ) ) {
						foreach ( $line_items as $key => $line_item ) {
							$product_id   = $line_item->price->product->id ?? '';
							$product_name = $line_item->price->product->name ?? '';

							if ( $product_id && $product_name ) {
								$product_url = add_query_arg(
									array(
										'page'   => 'sc-products',
										'action' => 'edit',
										'id'     => $product_id,
									),
									admin_url( 'admin.php' )
								);

								// Append product name with link.
								$product_tag .= sprintf(
									'<a href="%s">%s</a>',
									esc_url( $product_url ),
									esc_html( $product_name )
								);

								if ( ( $total_line_items - 1 ) !== $key ) {
									$product_tag .= '<br />';
								}
							}
						}
					}

					// === Generate Subscription Tag ===
					$subscription = $checkout->purchases->data[0]->subscription ?? null;
					if ( $subscription ) {
						$subscription_finite = ! empty( $subscription->finite );

						if ( $subscription_finite ) {
							$subscription_tag = '<sc-tag type="success" size="medium" class="hydrated">' . esc_html__( 'Installments', 'surelywp-toolkit' ) . '</sc-tag>';
						} else {
							$subscription_tag = '-';
						}
					}

					// Add formatted data to response if checkout ID is valid.
					if ( ! empty( $checkout_id ) ) {
						$response_data[ $checkout_id ] = array(
							'product_tag'      => ! empty( $product_tag ) ? $product_tag : '-',
							'subscription_tag' => $subscription_tag,
						);
					}
				}
			}

			set_transient( 'surelywp_tk_ac_order_product_name_sub_type', $response_data, 10 * MINUTE_IN_SECONDS );

			// Return successful JSON response with data.
			wp_send_json_success( $response_data );
		}

		/**
		 * Function to get Recovery status.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.2
		 */
		public function surelywp_tk_ac_get_recovery_status() {
			// Verify the AJAX request nonce.
			if ( ! check_ajax_referer( 'admin-ajax-nonce', 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce.', 'surelywp-toolkit' ) ) );
			}

			// Sanitize and extract customer IDs from POST request.
			$customer_ids = isset( $_POST['customer_ids'] ) && is_array( $_POST['customer_ids'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['customer_ids'] ) )
				: array();

			// Sanitize and extract checkout IDs from POST request.
			$checkout_ids = isset( $_POST['checkout_ids'] ) && is_array( $_POST['checkout_ids'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['checkout_ids'] ) )
				: array();

			if ( empty( $checkout_ids ) || empty( $customer_ids ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Missing checkout or customer IDs.', 'surelywp-toolkit' ) ) );
			}
			$response_data = array();

			$surelywp_tk_ac_recovery_status = get_transient( 'surelywp_tk_ac_recovery_status' );
			if ( $surelywp_tk_ac_recovery_status ) {
				$chached_ids = array_keys( $surelywp_tk_ac_recovery_status );
				if ( ! array_diff( $chached_ids, $checkout_ids ) ) {
					wp_send_json_success( $surelywp_tk_ac_recovery_status );
				}
			}

			// Fetch abandoned checkouts matching customer IDs.
			$abandoned_checkouts = AbandonedCheckout::where( array( 'customer_ids' => $customer_ids ) )->get();

			// Build response for each valid recovered checkout.
			if ( ! empty( $abandoned_checkouts ) && ! is_wp_error( $abandoned_checkouts ) ) {
				foreach ( $abandoned_checkouts as $checkout ) {
					$checkout_id = $checkout->recovered_checkout;
					$status      = $checkout->recovery_status ?? '';

					if ( in_array( $checkout_id, $checkout_ids, true ) && 'assisted_recovered' === $status ) {
						$response_data[ $checkout_id ] = array(
							'recovery_tag' => '<sc-tag type="success">' . esc_html__( 'Recovered', 'surelywp-toolkit' ) . '</sc-tag>',
						);
					}
				}
			}

			set_transient( 'surelywp_tk_ac_recovery_status', $response_data, 10 * MINUTE_IN_SECONDS );

			// Return success response with recovery status data.
			wp_send_json_success( $response_data );
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Ac_Ajax_Handler class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2
	 */
	function Surelywp_Tk_Ac_Ajax_Handler()
	{  // phpcs:ignore
		$instance = Surelywp_Tk_Ac_Ajax_Handler::get_instance();
		return $instance;
	}
}
