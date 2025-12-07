<?php
/**
 * Main class for Lead Magnets Ajax Handler.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.3
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\Customer;

if ( ! class_exists( 'Surelywp_Tk_Lm_Ajax_Handler' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	class Surelywp_Tk_Lm_Ajax_Handler {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Lm_Ajax_Handler
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 * @return  \Surelywp_Tk_Lm_Ajax_Handler
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

			// Login User Handler.
			add_action( 'wp_ajax_surelywp_tk_lm_login_user_handler', array( $this, 'surelywp_tk_lm_login_user_handler' ) );

			// Render Modal.
			add_action( 'wp_ajax_nopriv_surelywp_tk_lm_modal_render', array( $this, 'surelywp_tk_lm_modal_render' ) );

			// After Submit Action.
			add_action( 'wp_ajax_nopriv_surelywp_tk_lm_optin_form_submit_callback', array( $this, 'surelywp_tk_lm_optin_form_submit_callback' ) );
		}

		/**
		 * Function to render modal on screen
		 *
		 * @package SurelyWP Lead Magnets
		 * @since   1.0.0
		 */
		public function surelywp_tk_lm_login_user_handler() {

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {

				$product_id               = isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
				$is_customer_give_consent = isset( $_POST['is_customer_give_consent'] ) ? sanitize_text_field( wp_unslash( $_POST['is_customer_give_consent'] ) ) : 'false';

				$is_valid_product_id = Surelywp_Tk_Lm::surelywp_tk_lm_validate_product_id( $product_id );

				if ( ! $is_valid_product_id['status'] ) {
					echo wp_json_encode( $is_valid_product_id );
					wp_die();
				}

				if ( $is_customer_give_consent !== 'true' ) {
					$sub_form_fields = Surelywp_Tk_Lm::get_settings_option( 'sub_form_fields' );
					if ( isset( $sub_form_fields['consent_checkbox']['is_show'] ) && isset( $sub_form_fields['consent_checkbox']['is_required'] ) ) {
						echo wp_json_encode(
							array(
								'status' => false,
								'error'  => esc_html__( 'Please check the consent checkbox.', 'surelywp-toolkit' ),
							)
						);
						wp_die();
					}
				}

				// Access user data.
				$current_user      = wp_get_current_user();
				$user_email        = $current_user->user_email;
				$user_display_name = $current_user->display_name;

				// prepare consent data.
				$consent_data = array(
					'product_id' => $product_id,
					'agreed'     => 'true' === $is_customer_give_consent ? true : false,
				);

				// Save the consent data.
				Surelywp_Tk_Lm::surelywp_tk_lm_store_user_consent( $current_user->ID, $consent_data );

				$surecart_checkout_form_mode = \SureCart::cart()->getMode();

				$checkout_data = array(
					'product_id'         => $product_id,
					'user_id'            => $current_user->ID,
					'email'              => $user_email,
					'name'               => $user_display_name,
					'customer_live_mode' => 'test' === $surecart_checkout_form_mode ? false : true,
				);

				// Get Surecart Checkout Form Mode.
				$customer          = array();
				$existing_customer = Customer::where(
					array(
						'email'     => strtolower( $user_email ),
						'live_mode' => 'test' === $surecart_checkout_form_mode ? false : true,
					)
				)->get();

				$is_customer_exist = ! is_wp_error( $existing_customer ) && ! empty( $existing_customer ) && isset( $existing_customer[0]->id ) ? true : false;

				// Get Existing Customer Id and customer mode.
				if ( $is_customer_exist ) {

					$checkout_data['customer_id'] = $existing_customer[0]->id;

				} else { // Create Surecart Customer.

					$customer_data = array(
						'email'       => $user_email,
						'name'        => $user_display_name,
						'first_name'  => isset( $current_user->first_name ) ?? ! empty( $current_user->first_name ) ? $current_user->first_name : '',
						'last_name'   => isset( $current_user->last_name ) ?? ! empty( $current_user->last_name ) ? $current_user->last_name : '',
						'mode'        => $surecart_checkout_form_mode,
						'create_user' => false,
					);

					$new_customer = Surelywp_Tk_Lm::surelywp_tk_lm_create_customer( $customer_data );

					if ( $new_customer['status'] ) {

						// Get Current User Surecart Object.
						$user = SureCart\Models\User::current();

						// Link Customer and User.
						if ( $user->ID && ! $user->hasCustomerId( $new_customer['customer_obj']->id ) ) {

							$user->setCustomerId( $new_customer['customer_obj']->id, $surecart_checkout_form_mode );
						}

						$checkout_data['customer_id'] = $new_customer['customer_obj']->id;

					} else {

						echo wp_json_encode( array( 'status' => false ) );
						wp_die();
					}
				}

				if ( $is_customer_exist || $new_customer['status'] ) {

					$is_checkout_created = Surelywp_Tk_Lm::surelywp_tk_lm_create_checkout( $checkout_data );

					if ( isset( $is_checkout_created['status'] ) && $is_checkout_created['status'] ) {

						$dashboard_page_id = get_option( 'surecart_dashboard_page_id' );
						$dashboard_url     = get_permalink( $dashboard_page_id ) . '?action=index&model=download';

						$response = array(
							'status'          => true,
							'dashboard_url'   => $dashboard_url,
							'already_created' => $is_checkout_created['already_created'],
						);

						echo wp_json_encode( $response );
						wp_die();

					} else {
						echo wp_json_encode(
							array(
								'status' => false,
								'error'  => $is_checkout_created['error'] ?? '',
							)
						);
						wp_die();
					}
				} else {
					echo wp_json_encode( array( 'status' => false ) );
					wp_die();
				}
			}
		}

		/**
		 * Function to render modal on screen
		 *
		 * @package SurelyWP Lead Magnets
		 * @since   1.0.0
		 */
		public function surelywp_tk_lm_modal_render() {

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {

				$product_id          = isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
				$is_valid_product_id = Surelywp_Tk_Lm::surelywp_tk_lm_validate_product_id( $product_id );

				if ( ! $is_valid_product_id['status'] ) {
					echo wp_json_encode( $is_valid_product_id );
					wp_die();
				}

				$form_html = Surelywp_Tk_Lm::surelywp_tk_lm_email_optin_form_modal_html( $product_id );

				echo wp_json_encode(
					array(
						'status'    => true,
						'form_html' => $form_html,
					)
				);
				wp_die();
			}
		}

		/**
		 * Lead Magnets Form submit call Back
		 *
		 * @package SurelyWP Lead Magnets
		 * @since   1.0.0
		 */
		public function surelywp_tk_lm_optin_form_submit_callback() {

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'optin_form_submit_action' ) ) {

				$optin_form_email            = isset( $_POST['form_data']['optin_form_email'] ) && ! empty( $_POST['form_data']['optin_form_email'] ) ? sanitize_email( wp_unslash( $_POST['form_data']['optin_form_email'] ) ) : '';
				$optin_form_first_name       = isset( $_POST['form_data']['optin_form_first_name'] ) && ! empty( $_POST['form_data']['optin_form_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_data']['optin_form_first_name'] ) ) : '';
				$optin_form_last_name        = isset( $_POST['form_data']['optin_form_last_name'] ) && ! empty( $_POST['form_data']['optin_form_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_data']['optin_form_last_name'] ) ) : '';
				$optin_form_consent_checkbox = isset( $_POST['form_data']['optin_form_consent_checkbox'] ) && ! empty( $_POST['form_data']['optin_form_consent_checkbox'] ) ? sanitize_text_field( wp_unslash( $_POST['form_data']['optin_form_consent_checkbox'] ) ) : false;
				$product_id                  = isset( $_POST['product_id'] ) && ! empty( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';

				if ( ! filter_var( $optin_form_email, FILTER_VALIDATE_EMAIL ) ) {

					echo wp_json_encode(
						array(
							'status' => false,
							'error'  => esc_html__( 'Invalid Email ID', 'surelywp-toolkit' ),
						)
					);
					wp_die();
				}

				// Set Username Form Email.
				if ( empty( $optin_form_name ) ) {
					$name_part       = explode( '@', $optin_form_email );
					$optin_form_name = $name_part[0];
				}

				// Check the consent checkbox is checked.
				$sub_form_fields = Surelywp_Tk_Lm::get_settings_option( 'sub_form_fields' );
				if ( isset( $sub_form_fields['consent_checkbox']['is_show'] ) && isset( $sub_form_fields['consent_checkbox']['is_required'] ) && ! $optin_form_consent_checkbox ) {

					echo wp_json_encode(
						array(
							'status' => false,
							'error'  => esc_html__( 'Consent Checkbox Must Be Checked', 'surelywp-toolkit' ),
						)
					);
					wp_die();
				}

				$surecart_checkout_form_mode = \SureCart::cart()->getMode();

				$checkout_data = array(
					'product_id'  => $product_id,
					'email'       => $optin_form_email,
					'name'        => $optin_form_name,
					'first_name'  => $optin_form_first_name,
					'last_name'   => $optin_form_last_name,
					'mode'        => $surecart_checkout_form_mode,
					'create_user' => true,
				);

				$require_email_verification = Surelywp_Tk_Lm::get_settings_option( 'require_email_verification' );
				$customer_obj               = Customer::where(
					array(
						'email'     => strtolower( $checkout_data['email'] ),
						'live_mode' => 'test' === $checkout_data['mode'] ? false : true,
					)
				)->get();

				$customer = array();

				if ( ! is_wp_error( $customer_obj ) && ! empty( $customer_obj ) && isset( $customer_obj[0]->id ) ) {

					$customer_orders = Surelywp_Tk_Lm::surelywp_tk_lm_get_customer_orders( $customer_obj[0]->id );

					// If customer have order means customer is already verified exist customer.
					if ( ! empty( $customer_orders ) ) {

						$customer_exists_message = Surelywp_Tk_Lm::get_settings_option( 'customer_exists_message' );
						if ( empty( $customer_exists_message ) ) {
							$customer_exists_message = esc_html__( 'The email address you entered matches an existing customer. Please log in to your customer dashboard to access this resource.', 'surelywp-toolkit' );
						}

						echo wp_json_encode(
							array(
								'status' => false,
								'error'  => $customer_exists_message,
							)
						);
						wp_die();

					} else {
						$customer = array(
							'status'       => true,
							'customer_obj' => $customer_obj,
						);
					}
				} else {
					$customer = Surelywp_Tk_Lm::surelywp_tk_lm_create_customer( $checkout_data );
				}

				// All for new Customer.
				if ( $customer['status'] ) {

					// Get User.
					$user = $customer['customer_obj']->getUser();

					if ( $user ) {

						$user_id = $user->ID;

						// Update First name last name.
						if ( ! empty( $checkout_data['first_name'] ) || ! empty( $checkout_data['last_name'] ) ) {

							$user_data = array(
								'ID'         => $user_id,
								'first_name' => $checkout_data['first_name'],
								'last_name'  => $checkout_data['last_name'],
							);

							wp_update_user( $user_data );
						}

						$customer_id        = $customer['customer_obj']->id;
						$customer_live_mode = $customer['customer_obj']->live_mode;
						$customer_orders    = Surelywp_Tk_Lm::surelywp_tk_lm_get_customer_orders( $customer_id );

					} else {

						echo wp_json_encode( array( 'status' => false ) );
						wp_die();
					}

					// prepare consent data.
					$consent_data = array(
						'product_id' => $product_id,
						'agreed'     => $optin_form_consent_checkbox ? true : false,
					);

					// Save the consent data.
					Surelywp_Tk_Lm::surelywp_tk_lm_store_user_consent( $user_id, $consent_data );

					if ( ! $require_email_verification || ! empty( $customer_orders ) ) {

						$checkout_data['customer_id']        = $customer_id;
						$checkout_data['customer_live_mode'] = $customer_live_mode;
						$checkout_data['user_id']            = $user_id;

						$is_checkout_created = Surelywp_Tk_Lm::surelywp_tk_lm_create_checkout( $checkout_data );

						if ( $is_checkout_created['status'] ) {

							// Autologin User.
							wp_clear_auth_cookie();
							wp_set_current_user( $user_id );
							wp_set_auth_cookie( $user_id );

							if ( get_current_user_id() == $user_id ) {

								$dashboard_page_id = get_option( 'surecart_dashboard_page_id' );
								$dashboard_url     = get_permalink( $dashboard_page_id ) . '?action=index&model=download';

								$response = array(
									'status'          => true,
									'dashboard_url'   => $dashboard_url,
									'already_created' => $is_checkout_created['already_created'],
								);

								echo wp_json_encode( $response );
								wp_die();
							}
						} else {

							echo wp_json_encode( array( 'status' => false ) );
							wp_die();
						}
					} elseif ( $require_email_verification ) {

						$email_verification_message = Surelywp_Tk_Lm::get_settings_option( 'email_verification_message' );
						if ( empty( $email_verification_message ) ) {
							$email_verification_message = esc_html__( 'An email will be sent to the provided address to confirm your subscription and complete your download.', 'surelywp-toolkit' );
						}

						$token = surelywp_tk_generate_random_id();

						if ( $token ) {

							// Upate token in User meta.
							update_user_meta( $user_id, '_sureplywp_lm_token', $token );

							$is_mail_send = Surelywp_Tk_Lm::surelywp_tk_lm_send_verification_mail( $checkout_data, $user_id, $token );

							if ( $is_mail_send ) {

								$response = array(
									'email_status' => true,
									'email_verification_message' => $email_verification_message,
								);

								echo wp_json_encode( $response );
								wp_die();
							} else {

								echo wp_json_encode( array( 'status' => false ) );
								wp_die();
							}
						} else {

							echo wp_json_encode( array( 'status' => false ) );
							wp_die();
						}
					}
				} else {

					echo wp_json_encode( $customer );
					wp_die();
				}
			} else {
				echo wp_json_encode( array( 'status' => false ) );
				wp_die();
			}
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Lm_Ajax_Handler class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	function Surelywp_Tk_Lm_Ajax_Handler() {  // phpcs:ignore
		$instance = Surelywp_Tk_Lm_Ajax_Handler::get_instance();
		return $instance;
	}
}
