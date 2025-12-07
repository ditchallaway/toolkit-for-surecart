<?php
/**
 * Main class for sync surecart checkout data sync.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.3
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\ApiToken;
use SureCart\Models\Subscription;


if ( ! class_exists( 'Surelywp_Tk_Scs' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	class Surelywp_Tk_Scs {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Scs
		 */
		protected static $instance;


		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 * @return  \Surelywp_Tk_Scs
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
		 * @since   1.3
		 */
		public function __construct() {

			// For add the sync all the store checkouts.
			add_action( 'surelywp_tk_fetch_all_checkouts', array( $this, 'surelywp_tk_scs_fetch_all_checkouts' ) );

			// handle checkout update.
			add_action( 'surecart/request/response', array( $this, 'surelywp_tk_scs_update_checkouts' ), 10, 3 );

			// handle checkout update by order.
			add_action( 'surecart/request/response', array( $this, 'surelywp_tk_scs_update_orders' ), 10, 3 );

			// handle insert new checkout.
			add_action( 'surecart/checkout_confirmed', array( $this, 'surelywp_tk_scs_add_new_checkout' ), 10, 2 );

			// add sub renew orders.
			add_action( 'surecart/subscription_renewed', array( $this, 'surelywp_tk_scs_add_subscription_renewed_order' ), 10, 2 );
		}

		/**
		 * Function to add add order on subscription.
		 *
		 * This function is triggered when a subscription is successfully renewed.
		 * It processes the subscription and the associated webhook data.
		 *
		 * @param object $subscription  The subscription object containing details about the renewed subscription.
		 * @param array  $webhook_data The webhook data array received during the subscription renewal process.
		 * @package Surelywp Services
		 * @since   1.5
		 */
		public function surelywp_tk_scs_add_subscription_renewed_order( $subscription, $webhook_data ) {

			global $surelywp_tk_model;

			$subscription_id = $subscription->id ?? '';

			if ( ! $subscription_id ) {
				return;
			}

			$sub_obj = Subscription::with( array( 'purchase', 'purchase.initial_order', 'order.checkout' ) )->find( $subscription_id );

			if ( is_wp_error( $sub_obj ) || empty( $sub_obj ) ) {
				return;
			}

			$checkout_obj = $sub_obj->purchase->initial_order->checkout->toArray() ?? array();

			if ( ! $checkout_obj ) {
				return;
			}

			$surelywp_tk_model->surelywp_tk_insert_checkout_data( array( $checkout_obj ) );
		}

		/**
		 * Function to save the new checkout on db.
		 *
		 * @param object $checkout Reponse data.
		 * @param array  $request    Request arguments.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_scs_add_new_checkout( $checkout, $request ) {

			global $surelywp_tk_model;

			if ( is_wp_error( $checkout ) || empty( $checkout ) ) {
				return;
			}

			$checkout_obj = $checkout->toArray() ?? array();

			if ( ! is_array( $checkout_obj ) || empty( $checkout_obj ) ) {
				return;
			}

			$checkout_obj['customer'] = $checkout_obj['customer']['id'] ?? '';
			$surelywp_tk_model->surelywp_tk_insert_checkout_data( array( $checkout_obj ) );
		}

		/**
		 * Function to save the update checkout on db.
		 *
		 * @param array  $response Reponse data.
		 * @param array  $args    Request arguments.
		 * @param string $endpoint The endpoint to request.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_scs_update_checkouts( $response, $args, $endpoint ) {

			$request_method = $args['method'] ?? '';
			if ( 'PATCH' !== $request_method ) {
				return $response;
			}

			$parts_endpoints = explode( '/', $endpoint );
			$is_checkout     = 'checkouts' === $parts_endpoints[0] ? true : false;
			$is_checkout     = 'orders' === $parts_endpoints[0] ? true : false;

			if ( ! $is_checkout ) {
				return $response;
			}

			$checkout_id = $parts_endpoints[1] ?? '';
			if ( ! $checkout_id ) {
				return $response;
			}

			global $surelywp_tk_model;
			$surelywp_tk_model->surelywp_tk_insert_checkout_data( array( (array) $response ) );

			return $response;
		}

		/**
		 * Function to save the new orders on db.
		 *
		 * @param array  $response Reponse data.
		 * @param array  $args    Request arguments.
		 * @param string $endpoint The endpoint to request.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_scs_update_orders( $response, $args, $endpoint ) {

			$query_context = $args['query']['context'] ?? '';

			if ( 'edit' !== $query_context ) {
				return $response;
			}

			$parts_endpoints = explode( '/', $endpoint );
			$is_order        = 'orders' === $parts_endpoints[0] ? true : false;

			if ( ! $is_order ) {
				return $response;
			}

			$order_id = $parts_endpoints[1] ?? '';
			if ( ! $order_id ) {
				return $response;
			}

			$checkout_obj = $response->checkout ?? array();

			if ( ! $checkout_obj ) {
				return $response;
			}

			global $surelywp_tk_model;
			$surelywp_tk_model->surelywp_tk_insert_checkout_data( array( (array) $checkout_obj ) );

			return $response;
		}

		/**
		 * Fetch all SureCart checkouts with retry and error handling.
		 *
		 * @param int $data_per_page the fetch per page.
		 * @param int $max_retries The max retries.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_scs_fetch_all_checkouts( $data_per_page = 100, $max_retries = 3 ) {

			$is_scs_running = get_option( 'surelywp_surecart_checkout_sync_runnig' );
			if ( $is_scs_running ) {
				return;
			}

			update_option( 'surelywp_surecart_checkout_sync_runnig', true );

			global $surelywp_tk_model;

			$sc_api_token = ApiToken::get();
			if ( empty( $sc_api_token ) ) {
				return;
			}
			$base_url = 'https://api.surecart.com/v1/checkouts';

			// 1. Fetch total count with simple retry logic.
			$total_count = 0;

			for ( $retry = 1; $retry <= $max_retries; $retry++ ) {
				$count_url = $base_url . '?page=1&limit=1';
				$ch        = curl_init();
				curl_setopt_array(
					$ch,
					array(
						CURLOPT_URL            => $count_url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_HTTPHEADER     => array(
							'Authorization: Bearer ' . $sc_api_token,
							'Accept: application/json',
						),
						CURLOPT_TIMEOUT        => 10,
					)
				);

				$resp       = curl_exec( $ch );
				$curl_error = curl_error( $ch );
				$http_code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
				curl_close( $ch );

				if ( $curl_error || $http_code !== 200 ) {
					error_log( "Count fetch retry #$retry failed (HTTP $http_code). Curl error: $curl_error." );
					usleep( 200000 * $retry ); // 200 ms multiplied by retry count.
					continue;
				}

				$data = json_decode( $resp, true );

				if ( ! is_array( $data ) || ! isset( $data['pagination']['count'] ) ) {
					error_log( "Count fetch retry #$retry returned invalid JSON." );
					usleep( 200000 * $retry ); // 200 ms multiplied by retry count.
					continue;
				}

				$total_count = (int) $data['pagination']['count'];
				break;
			}

			if ( $total_count === 0 ) {
				$result = array(
					'success'       => true,
					'total'         => 0,
					'fetched_pages' => 0,
				);
			}

			// 2. Calculate total pages to fetch.
			$total_pages    = (int) ceil( $total_count / $data_per_page );
			$pages_to_fetch = range( 1, $total_pages );
			$fetched_pages  = 0;

			// 3. Fetch pages in parallel with retries.
			for ( $retry = 1; $retry <= $max_retries && ! empty( $pages_to_fetch ); $retry++ ) {
				$multi_handle = curl_multi_init();
				$handles      = array();
				$page_map     = array(); // Maps handle ID to page number.

				// Create curl handles for all pages.
				foreach ( $pages_to_fetch as $page ) {
					$url = $base_url . "?page=$page&limit=$data_per_page&expand[]=discount&expand[]=line_items&expand[]=line_items.price&expand[]=line_items.price.product";
					$ch  = curl_init();
					curl_setopt_array(
						$ch,
						array(
							CURLOPT_URL            => $url,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_HTTPHEADER     => array(
								'Authorization: Bearer ' . $sc_api_token,
								'Accept: application/json',
							),
							CURLOPT_TIMEOUT        => 20,
						)
					);

					curl_multi_add_handle( $multi_handle, $ch );
					$handles[]                                   = $ch;
					$page_map[ self::get_curl_handle_id( $ch ) ] = $page;
				}

				// Execute all curl handles in parallel.
				$running = null;
				do {
					curl_multi_exec( $multi_handle, $running );
					if ( curl_multi_select( $multi_handle, 1.0 ) === -1 ) {
						usleep( 100000 ); // Sleep 100 ms if select fails.
					}
				} while ( $running > 0 );

				// Process each curl response.
				$next_retry_pages = array();

				foreach ( $handles as $ch ) {
					$page       = $page_map[ self::get_curl_handle_id( $ch ) ];
					$body       = curl_multi_getcontent( $ch );
					$curl_error = curl_error( $ch );
					$http_code  = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
					$response   = json_decode( $body, true );

					$has_error = $curl_error || $http_code !== 200 || ! isset( $response['data'] );

					if ( $has_error ) {
						error_log( "Retry #$retry: Page $page failed (HTTP $http_code). Curl error: $curl_error." );

						// Apply exponential backoff for rate limit (429).
						if ( $http_code === 429 ) {
							$retry_after = 1 + ( 1 << $retry ); // 1 + 2^retry.
							usleep( $retry_after * 1000000 ); // Convert seconds to microseconds.
						} else {
							usleep( 200000 * $retry ); // 200 ms multiplied by retry count for other errors.
						}

						$next_retry_pages[] = $page;
					} else {
						// Insert the fetched data.
						$surelywp_tk_model->surelywp_tk_insert_checkout_data( $response['data'] );
						++$fetched_pages;
					}

					curl_multi_remove_handle( $multi_handle, $ch );
					curl_close( $ch );
				}

				curl_multi_close( $multi_handle );
				$pages_to_fetch = $next_retry_pages;
			}

			// 4. Log and return if any pages failed after all retries.
			if ( ! empty( $pages_to_fetch ) ) {

				error_log(
					"SureCart Checkout Fetch: Some pages failed after $max_retries retries: " . implode( ', ', $pages_to_fetch ) . '.'
				);

				$result = array(
					'success'       => false,
					'failed_pages'  => $pages_to_fetch,
					'total'         => $total_count,
					'fetched_pages' => $fetched_pages,
				);
			}

			$result = array(
				'success'       => true,
				'total'         => $total_count,
				'fetched_pages' => $fetched_pages,
			);

			update_option( 'surelywp_surecart_checkout_sync_runnig', false );
		}

		/**
		 * Safely derive a unique ID for a curl handle across PHP versions.
		 *
		 * @param resource|\CurlHandle $ch
		 * @return int|string
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		private static function get_curl_handle_id( $ch ) {

			// PHP >= 8 returns a CurlHandle object.
			if ( is_object( $ch ) ) {
				return spl_object_id( $ch ); // Returns an integer ID.
			}

			// PHP 7.x returns a resource, cast to int.
			return (int) $ch;
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Scs class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	function Surelywp_Tk_Scs() {  // phpcs:ignore
		$instance = Surelywp_Tk_Scs::get_instance();
		return $instance;
	}
}
