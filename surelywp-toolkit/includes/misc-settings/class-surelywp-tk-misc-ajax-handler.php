<?php
/**
 * Main class for Misc Ajax Handler.
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
use SureCart\Models\Customer;
use SureCart\Models\Subscription;
use SureCart\Models\Order;
use SureCart\Models\ApiToken;
use SureCart\Models\Media;
use SureCart\Models\Download;

if ( ! class_exists( 'Surelywp_Tk_Misc_Ajax_Handler' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2
	 */
	class Surelywp_Tk_Misc_Ajax_Handler {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Misc_Ajax_Handler
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2
		 * @return  \Surelywp_Tk_Misc_Ajax_Handler
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

			add_action( 'wp_ajax_surelywp_tk_misc_add_recovered_tag', array( $this, 'surelywp_tk_misc_add_recovered_tag' ) );
			add_action( 'wp_ajax_surelywp_tk_misc_get_payment_retry_button_tag', array( $this, 'surelywp_tk_misc_get_payment_retry_button_tag' ) );
			add_action( 'wp_ajax_surelywp_tk_misc_retry_sub_payment', array( $this, 'surelywp_tk_misc_retry_sub_payment' ) );

			// Update the product meta options.
			add_action( 'wp_ajax_surelywp_tk_misc_update_product_meta', array( $this, 'surelywp_tk_misc_update_product_meta' ) );

			add_action( 'wp_ajax_surelywp_tk_misc_manual_checkout_sync', array( $this, 'surelywp_tk_misc_manual_checkout_sync' ) );

			// get the downloads media urls.
			add_action( 'wp_ajax_surelywp_tk_misc_get_download_media_url', array( $this, 'surelywp_tk_misc_get_download_media_url' ) );

			// get product downloads.
			add_action( 'wp_ajax_surelywp_tk_misc_get_product_downloads', array( $this, 'surelywp_tk_misc_get_product_downloads' ) );
		}


		/**
		 * Function to get download media urls for front end.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.2
		 */
		public function surelywp_tk_misc_get_download_media_url() {

			check_ajax_referer( 'misc-ajax-nonce', 'nonce' );

			global $surelywp_model;
			$media_id  = isset( $_POST['media_id'] ) && ! empty( $_POST['media_id'] ) ? sanitize_text_field( wp_unslash( $_POST['media_id'] ) ) : '';
			$view_type = isset( $_POST['view_type'] ) && ! empty( $_POST['view_type'] ) ? sanitize_text_field( wp_unslash( $_POST['view_type'] ) ) : '';

			if ( ! $media_id ) {

				echo wp_json_encode(
					array(
						'status'  => false,
						'message' => esc_html__( 'Media Id not Found', 'surelywp-toolkit' ),
					)
				);
				wp_die();
			}

			$is_show = true;
			if ( 'admin' === $view_type ) {
				$is_show = Surelywp_Tk_Misc::get_settings_option( 'is_show_image_on_admin_downloads_list' );
			} elseif ( 'customer' === $view_type ) {
				$is_show = Surelywp_Tk_Misc::get_settings_option( 'is_show_image_on_customer_downloads_list' );
			}

			if ( ! $is_show ) {

				echo wp_json_encode(
					array(
						'status'  => false,
						'message' => esc_html__( 'feature not enable', 'surelywp-toolkit' ),
					)
				);
				wp_die();
			}

			if ( $media_id ) {

				$media = \SureCart::request(
					'medias/' . '/' . $media_id,
					array(
						'method' => 'GET',
						'query'  => array( 'expose_for' => 60 ),
					)
				);

				if ( is_wp_error( $media ) || empty( $media ) ) {
					echo wp_json_encode(
						array(
							'status'  => false,
							'message' => esc_html__( 'media url not found', 'surelywp-toolkit' ),
						)
					);
					wp_die();

				}

				echo wp_json_encode(
					array(
						'status' => true,
						'url'    => $media->url ?? '',
						'id'     => $media->id ?? '',
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

		/**
		 * Function to get product downlods.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.2
		 */
		public function surelywp_tk_misc_get_product_downloads() {

			check_ajax_referer( 'misc-ajax-nonce', 'nonce' );

			global $surelywp_model;
			$product_id = isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
			$view_type  = isset( $_POST['view_type'] ) && ! empty( $_POST['view_type'] ) ? sanitize_text_field( wp_unslash( $_POST['view_type'] ) ) : '';

			if ( ! $product_id ) {

				echo wp_json_encode(
					array(
						'status'  => false,
						'message' => esc_html__( 'Product Id not Found', 'surelywp-toolkit' ),
					)
				);
				wp_die();
			}

			$is_show = true;
			if ( 'admin' === $view_type ) {
				$is_show = Surelywp_Tk_Misc::get_settings_option( 'is_show_image_on_admin_downloads_list' );
			} elseif ( 'customer' === $view_type ) {
				$is_show = Surelywp_Tk_Misc::get_settings_option( 'is_show_image_on_customer_downloads_list' );
			}

			if ( ! $is_show ) {

				echo wp_json_encode(
					array(
						'status'  => false,
						'message' => esc_html__( 'feature not enable', 'surelywp-toolkit' ),
					)
				);
				wp_die();
			}

			if ( $product_id ) {

				$downloads = Download::with( array( 'media' ) )->where( array( 'product_ids' => array( $product_id ) ) )->get();

				if ( is_wp_error( $downloads ) || empty( $downloads ) ) {
					echo wp_json_encode(
						array(
							'status'  => false,
							'message' => esc_html__( 'downloads not found', 'surelywp-toolkit' ),
						)
					);
					wp_die();

				}

				echo wp_json_encode(
					array(
						'status'    => true,
						'downloads' => $downloads,
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

		/**
		 * Function to manual checkout sync.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.2
		 */
		public function surelywp_tk_misc_manual_checkout_sync() {

			check_ajax_referer( 'misc-ajax-nonce', 'nonce' );

			if ( ! wp_next_scheduled( 'surelywp_tk_fetch_all_checkouts' ) ) {
				wp_schedule_single_event( time(), 'surelywp_tk_fetch_all_checkouts' ); // runs on plugin active.
			}

			echo wp_json_encode(
				array(
					'status'  => true,
					'message' => esc_html__( 'Checkout process synchronization has started in the background', 'surelywp-toolkit' ),
				)
			);
			wp_die();
		}

		/**
		 * Function to Payment Retry.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.2
		 */
		public function surelywp_tk_misc_retry_sub_payment() {

			check_ajax_referer( 'misc-ajax-nonce', 'nonce' );

			$period_id = isset( $_POST['period_id'] ) && ! empty( $_POST['period_id'] ) ? sanitize_text_field( wp_unslash( $_POST['period_id'] ) ) : '';
			if ( $period_id ) {

				$sc_api_token = ApiToken::get();

				if ( $sc_api_token ) {

					$headers = array(
						'accept'        => 'application/json',
						'authorization' => 'Bearer ' . $sc_api_token,
					);

					$url = 'https://api.surecart.com/v1/periods/' . $period_id . '/retry_payment';

					$args = array(
						'method'  => 'PATCH',
						'headers' => $headers,
					);

					$response      = wp_remote_request( $url, $args );
					$response_body = json_decode( $response['body'] );

					if ( 'paid' === $response_body->status ?? '' ) {

						echo wp_json_encode(
							array(
								'status'  => true,
								'message' => esc_html__( 'Subscription payment has been retried successfully.', 'surelywp-toolkit' ),
							)
						);
						wp_die();

					} elseif ( 'unprocessable_entity' === $response_body->code ) {

						echo wp_json_encode(
							array(
								'status'  => false,
								'message' => esc_html( $response_body->message ),
							)
						);
						wp_die();
					} else {

						echo wp_json_encode(
							array(
								'status'  => false,
								'message' => esc_html__( 'Something went wrong please try again.', 'surelywp-toolkit' ),
							)
						);
						wp_die();
					}
				}
			}

			echo wp_json_encode(
				array(
					'status'  => false,
					'message' => esc_html__( 'Something went wrong please try again.', 'surelywp-toolkit' ),
				)
			);
			wp_die();
		}

		/**
		 * Function to Payment Retry Button.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.2
		 */
		public function surelywp_tk_misc_get_payment_retry_button_tag() {

			check_ajax_referer( 'misc-ajax-nonce', 'nonce' );

			$order_id = isset( $_POST['order_id'] ) && ! empty( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';

			if ( $order_id ) {

				$order_obj = Order::with( array( 'checkout', 'checkout.line_items' ) )->find( $order_id );

				if ( is_wp_error( $order_obj ) || empty( $order_obj ) ) {

					echo wp_json_encode(
						array(
							'status'  => false,
							'message' => esc_html__( 'Order Not Found.', 'surelywp-toolkit' ),
						)
					);
					wp_die();
				}

				$checkout_id = $order_obj->checkout->id ?? '';
				$price_id    = $order_obj->checkout->line_items->data[0]->price ?? '';

				$sub_obj = Subscription::with( array( 'current_period' ) )->where(
					array(
						'checkout_ids' => array( $checkout_id ),
						'price_ids'    => array( $price_id ),
					)
				)->get();

				if ( is_wp_error( $sub_obj ) || empty( $sub_obj ) ) {

					echo wp_json_encode(
						array(
							'status'  => false,
							'message' => esc_html__( 'Subscription Not Found.', 'surelywp-toolkit' ),
						)
					);
					wp_die();
				}

				$current_period_id     = $sub_obj[0]->current_period->id ?? '';
				$current_period_status = $sub_obj[0]->current_period->status ?? '';
				if ( 'payment_failed' !== $current_period_status ) {

					echo wp_json_encode(
						array(
							'status'  => false,
							'message' => esc_html__( 'Subscription Payment Status not fail', 'surelywp-toolkit' ),
						)
					);
					wp_die();
				}

				$retry_button = '<sc-menu-item class="surelywp-retry-payment-menu" id="surelywp-retry-payment-menu" data-period-id="' . esc_html( $current_period_id ) . '" class="hydrated">' . esc_html__( 'Retry Payment', 'surelywp-toolkit' ) . '</sc-menu-item>';
				$dialog_popup = '<sc-dialog label="Confirm" id="surelywp-retry-payment-confirm-popup" class="hydrated">' . esc_html__( 'Are you sure you want to retry the payment? This will attempt to charge the customer.', 'surelywp-toolkit' ) . '<div slot="footer"><sc-button id="surelywp-retry-payment-cancel" type="text" size="medium" class="hydrated">Cancel</sc-button> <sc-button data-period-id="' . esc_html( $current_period_id ) . '" id="surelywp-retry-payment-btn" type="primary" size="medium" class="hydrated">' . esc_html__( 'Retry Payment', 'surelywp-toolkit' ) . '</sc-button></div></sc-dialog>';

				echo wp_json_encode(
					array(
						'status'       => true,
						'retry_button' => $retry_button,
						'dialog_popup' => $dialog_popup,
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

		/**
		 * Function to manage new service request.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.4
		 */
		public function surelywp_tk_misc_add_recovered_tag() {

			$customer_id = isset( $_POST['customer_id'] ) && ! empty( $_POST['customer_id'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_id'] ) ) : '';
			$order_id    = isset( $_POST['order_id'] ) && ! empty( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';

			if ( $customer_id && $order_id ) {

				$abadon_chheckouts = AbandonedCheckout::where( array( 'customer_ids' => array( $customer_id ) ) )->get();
				$order             = Order::find( $order_id );
				if ( $order && $abadon_chheckouts ) {

					$ab_checkout_ids = array();
					foreach ( $abadon_chheckouts as $ab_checkout ) {
						$ab_checkout_ids[ $ab_checkout->recovered_checkout ] = $ab_checkout->recovery_status ?? '';
					}
					$checkout_id = $order->checkout ?? '';
					if ( isset( $ab_checkout_ids[ $checkout_id ] ) && 'assisted_recovered' === $ab_checkout_ids[ $checkout_id ] ) {
						$tag = '<sc-tag type="success" pill>' . esc_html__( 'Recovered', 'surelywp-toolkit' ) . '</sc-tag>';

						echo wp_json_encode(
							array(
								'status' => true,
								'tag'    => $tag,
							)
						);
						wp_die();
					}
				}

				echo wp_json_encode(
					array(
						'status' => false,
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

		/**
		 * Function to Toolkit Misc Update Product Meta.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.2.3
		 */
		public function surelywp_tk_misc_update_product_meta() {

			check_ajax_referer( 'misc-ajax-nonce', 'nonce' );

			global $surelywp_model;
			$prefix  = SURELYWP_TOOLKIT_META_PREFIX;
			$post_id = isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';

			if ( ! $post_id ) {

				echo wp_json_encode(
					array(
						'status'  => false,
						'message' => esc_html__( 'Product Post Id Not Found', 'surelywp-toolkit' ),
					)
				);
				wp_die();
			}

			$enable_external_product = Surelywp_Tk_Misc::get_settings_option( 'enable_external_product' );
			if ( $enable_external_product ) {

				$external_product_url      = isset( $_POST['external_product_btn_url'] ) && ! empty( $_POST['external_product_btn_url'] ) ? sanitize_url( wp_unslash( $_POST['external_product_btn_url'] ) ) : '';
				$external_product_btn_text = isset( $_POST['external_product_btn_text'] ) && ! empty( $_POST['external_product_btn_text'] ) ? sanitize_text_field( wp_unslash( $_POST['external_product_btn_text'] ) ) : '';
				$external_product_new_tab  = isset( $_POST['is_external_product_link_open_new_tab'] ) && ! empty( $_POST['is_external_product_link_open_new_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['is_external_product_link_open_new_tab'] ) ) : '';

				update_post_meta( $post_id, $prefix . 'misc_external_product_url', $external_product_url );
				update_post_meta( $post_id, $prefix . 'misc_external_product_btn_text', $external_product_btn_text );
				update_post_meta( $post_id, $prefix . 'misc_external_product_open_new_tab', $external_product_new_tab );
			}

			$enable_price_desctiption = Surelywp_Tk_Misc::get_settings_option( 'enable_price_desctiption' );
			if ( $enable_price_desctiption ) {

				$misc_price_descriptions      = isset( $_POST[ $prefix . 'misc_price_desc' ] ) && ! empty( $_POST[ $prefix . 'misc_price_desc' ] ) ? $surelywp_model->surelywp_escape_slashes_deep( $_POST[ $prefix . 'misc_price_desc' ] ) : '';
				$misc_price_desc_display_type = isset( $_POST[ $prefix . 'misc_price_desc_display_type' ] ) && ! empty( $_POST[ $prefix . 'misc_price_desc_display_type' ] ) ? $surelywp_model->surelywp_escape_attr( $_POST[ $prefix . 'misc_price_desc_display_type' ] ) : '';

				update_post_meta( $post_id, $prefix . 'misc_price_desc', $misc_price_descriptions );
				update_post_meta( $post_id, $prefix . 'misc_price_desc_display_type', $misc_price_desc_display_type );

			}

			echo wp_json_encode(
				array(
					'status'  => true,
					'message' => esc_html__( 'Update the toolkit product meta', 'surelywp-toolkit' ),
				)
			);
			wp_die();
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Misc_Ajax_Handler class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2
	 */
	function Surelywp_Tk_Misc_Ajax_Handler() {  // phpcs:ignore
		$instance = Surelywp_Tk_Misc_Ajax_Handler::get_instance();
		return $instance;
	}
}
