<?php
/**
 * Main class for Toolkit Fluent Crm.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.0
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\RegisteredWebhook;
use SureCart\Models\ProductCollection;
use SureCart\Models\Product;
use SureCart\Models\Promotion;
use SureCart\Models\Coupon;
use SureCart\Models\Purchase;
use FluentCrm\App\Models\Subscriber;

if ( ! class_exists( 'Surelywp_Tk_Fluent_Crm' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	class Surelywp_Tk_Fluent_Crm {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Fluent_Crm
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 * @return  \Surelywp_Tk_Fluent_Crm
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
		 * @since   1.0.0
		 */
		public function __construct() {

			$is_enable_fc = self::get_settings_option( 'status' );

			if ( $is_enable_fc ) {

				// Order Triggers.
				require_once 'order-events/class-surelywp-tk-sc-order-created.php'; // Order Created Trigger.
				require_once 'order-events/class-surelywp-tk-sc-order-delivered.php'; // Order Delivered Trigger.
				require_once 'order-events/class-surelywp-tk-sc-order-fulfilled.php'; // Order fulfilled Trigger.
				require_once 'order-events/class-surelywp-tk-sc-order-processing.php'; // Order processing Trigger.
				require_once 'order-events/class-surelywp-tk-sc-order-paid.php'; // Order paid Trigger.
				require_once 'order-events/class-surelywp-tk-sc-order-partially-fulfilled.php'; // Order Partially fulfilled.
				require_once 'order-events/class-surelywp-tk-sc-order-partially-shipped.php'; // Order Partially partially fulfilled.
				require_once 'order-events/class-surelywp-tk-sc-order-payment-failed.php'; // Order payment fail.
				require_once 'order-events/class-surelywp-tk-sc-order-shipped.php'; // Order shipped.
				require_once 'order-events/class-surelywp-tk-sc-order-unfulfilled.php'; // Order unfulfilled.
				require_once 'order-events/class-surelywp-tk-sc-order-unshipped.php'; // Order unshipped.
				require_once 'order-events/class-surelywp-tk-sc-order-voided.php'; // Order Voided(Cancel).

				// Subscription Triggers.
				require_once 'subscription-events/class-surelywp-tk-sc-subscription-canceled.php'; // Subscritption canceled.
				require_once 'subscription-events/class-surelywp-tk-sc-subscription-created.php'; // Subsctiption Created.
				require_once 'subscription-events/class-surelywp-tk-sc-subscription-completed.php'; // Subsctiption Completed.
				require_once 'subscription-events/class-surelywp-tk-sc-subscription-made-active.php'; // Subscription Made Active.
				require_once 'subscription-events/class-surelywp-tk-sc-subscription-made-trialing.php'; // Subsctiption Made trialing.
				require_once 'subscription-events/class-surelywp-tk-sc-subscription-renewed.php'; // Subsctiption renewed.
				require_once 'subscription-events/class-surelywp-tk-sc-subscription-set-to-cancel.php'; // Subsctiption set to cancel.
				require_once 'subscription-events/class-surelywp-tk-sc-subscription-updated.php'; // Subsctiption updated.

				// Refund Triggers.
				require_once 'refund-events/class-surelywp-tk-sc-refund-created.php'; // refund created.
				require_once 'refund-events/class-surelywp-tk-sc-refund-succeeded.php'; // refund created.

				// Purchase Triggers.
				// Purchase revoked trigger.
				// require_once 'purchase-events/class-surelywp-tk-sc-purchase-revoked.php';

				// add surecart check condtions groups.
				require_once 'class-surelywp-tk-fc-conditional.php';

				// Add Advance filter and customer sync integration settings.
				require_once 'class-surelywp-tk-fc-advance-filter.php';

				// get product selection options.
				add_filter( 'fluentcrm_ajax_options_product_selector_surecart', array( $this, 'get_sc_products_options' ), 10, 2 );

				// get product prices selections.
				add_filter( 'fluent_crm/cascade_selection_options_surecart_product_prices', array( $this, 'get_sc_products_prices_options' ), 10, 2 );

				// get product variations selections.
				add_filter( 'fluent_crm/cascade_selection_options_surecart_product_variants', array( $this, 'get_sc_products_variations_options' ), 10, 2 );

				// get all the promotion codes.
				add_filter( 'fluent_crm/cascade_selection_options_surecart_coupons_promotions', array( $this, 'get_surecart_coupons_promotions_options' ), 10, 2 );

				// get product Collections selections.
				add_filter( 'fluent_crm/cascade_selection_options_surecart_product_collections', array( $this, 'get_sc_products_collections_options' ), 10, 2 );

				// load classes.
				add_action( 'fluent_crm/after_init', array( $this, 'surelywp_tk_fc_load' ) );

			}

			// Enqueue scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_fc_enqueue_scripts' ) );
		}

		/**
		 * Function to add triggers and conditinals.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_fc_load() {

			// Order Triggers.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Created();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Delivered();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Fulfilled();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Processing();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Paid();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Partially_Fulfilled();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Partially_Shipped();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Payment_Failed();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Shipped();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Unfulfilled();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_unshipped();
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Order_Voided();

			// Subsctiption Triggers.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Subscription_Canceled(); // Subsctiption Canceled.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Subscription_Created(); // Subscription Created.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Subscription_Completed(); // Subscription Completed.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Subscription_Made_Active(); // Subsctiption Made Active.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Subscription_Made_Trialing(); // Subsctiption Made trialing.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Subscription_Renewed(); // Subsctiption renewed.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Subscription_Set_To_Cancel(); // Subsctiption set to cancel.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Subscription_Updated(); // Subsctiption updated.

			// Refund Triggers.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Refund_Created(); // Refund created.
			new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Refund_Succeeded(); // Refund succeeded.

			// Purchased Triggers.
			// new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Sc_Purchase_Revoked(); // Purchase Revoked.

			// Surecart conditinals.
			if ( defined( 'FLUENTCAMPAIGN_DIR_FILE' ) ) {
				new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Fc_Conditional();
				new SurelywpToolkit\Includes\FluentCrm\Surelywp_Tk_Fc_advance_filter();
			}
		}

		/**
		 * Retrieves SureCart product options based on search criteria and included IDs.
		 *
		 * This function fetches a list of product options from SureCart, filtered by the provided
		 * search term and specific product IDs. Results are returned in an associative array format.
		 *
		 * @param array  $options      Optional. Array of existing options to append to. Default empty array.
		 * @param string $search       Optional. Search term to filter products by name or other criteria.
		 *                             Default empty string.
		 *
		 * @return array Filtered list of product options, each as an associative array with 'id' and 'label' keys.
		 *
		 * @package Toolkit For Surecart.
		 * @since 1.0.0
		 */
		public function get_sc_products_variations_options( $options = array(), $search = '' ) {

			$sc_products = Product::with(
				array( 'variants' )
			)->where(
				array(
					'archived' => false,
					'query'    => $search,
				)
			)->get();

			$options_data = array();
			if ( ! is_wp_error( $sc_products ) && ! empty( $sc_products ) ) {
				if ( $sc_products ) {
					foreach ( $sc_products as $product ) {

						if ( isset( $product->variants ) && $product->variants->pagination->count ) {

							$childrens = array();
							foreach ( $product->variants->data as $variant ) {
								$childrens[] = array(
									'value' => $product->id . '-surelywp-separate-' . $variant->id,
									'label' => $product->name . ' - ' . ( $variant->option_1 ?? '' ) . ( isset( $variant->option_2 ) ? ', ' . $variant->option_2 : '' ) . ( isset( $variant->option_3 ) ? ', ' . $variant->option_3 : '' ),
								);

							}

							$options_data[] = array(
								'value'    => $product->id,
								'label'    => $product->name,
								'children' => $childrens,
							);
						}
					}
				}
			}

			$result = array(
				'options'  => $options_data,
				'has_more' => true,
			);

			return $result;
		}

		/**
		 * Retrieves SureCart Promotions options based on search criteria and included IDs.
		 *
		 * This function fetches a list of product options from SureCart, filtered by the provided
		 * search term and specific product IDs. Results are returned in an associative array format.
		 *
		 * @param array  $options      Optional. Array of existing options to append to. Default empty array.
		 * @param string $search       Optional. Search term to filter products by name or other criteria.
		 *                             Default empty string.
		 *
		 * @return array Filtered list of product options, each as an associative array with 'id' and 'label' keys.
		 *
		 * @package Toolkit For Surecart.
		 * @since 1.0.0
		 */
		public function get_surecart_coupons_promotions_options( $options = array(), $search = '' ) {

			$coupons = Coupon::with( array( 'promotion' ) )->where(
				array(
					'archived' => false,
					'query'    => $search['search'],
				)
			)->find();

			$options_data = array();
			$children     = array();

			if ( ! is_wp_error( $coupons ) && ! empty( $coupons ) ) {

				if ( $coupons ) {

					foreach ( $coupons->data as $coupon ) {
						$promotions_ids = array();
						if ( isset( $coupon->promotions->data ) && ! empty( $coupon->promotions->data ) ) {
							foreach ( $coupon->promotions->data as $key => $promotion ) {
								$promotions_ids[] = $promotion->id;
							}
						}

						if ( $promotions_ids ) {
							$promotion_id = implode( ',', $promotions_ids );

							$children[] = array(
								'value' => $promotion_id ?? '',
								'label' => $coupon->name ?? '',
							);
						}
					}
				}
			}

			$options_data[] = array(
				'children' => $children,
			);

			$result = array(
				'options'  => $options_data,
				'has_more' => true,
			);

			return $result;
		}

		/**
		 * Retrieves SureCart product options based on search criteria and included IDs.
		 *
		 * This function fetches a list of product options from SureCart, filtered by the provided
		 * search term and specific product IDs. Results are returned in an associative array format.
		 *
		 * @param array  $options      Optional. Array of existing options to append to. Default empty array.
		 * @param string $search       Optional. Search term to filter products by name or other criteria.
		 *                             Default empty string.
		 *
		 * @return array Filtered list of product options, each as an associative array with 'id' and 'label' keys.
		 *
		 * @package Toolkit For Surecart.
		 * @since 1.0.0
		 */
		public function get_sc_products_prices_options( $options = array(), $search = '' ) {

			$sc_products = Product::with(
				array( 'price' )
			)->where(
				array(
					'archived' => false,
					'query'    => $search['search'] ?? '',
				)
			)->get();

			$options_data = array();
			if ( ! is_wp_error( $sc_products ) && ! empty( $sc_products ) ) {
				if ( $sc_products ) {
					foreach ( $sc_products as $product ) {

						if ( isset( $product->prices ) && $product->prices->pagination->count ) {

							$childrens = array();
							foreach ( $product->prices->data as $price ) {
								$childrens[] = array(
									'value' => $product->id . '-surelywp-separate-' . $price->id,
									'label' => $product->name . ' - ' . $price->name,
								);

							}

							$options_data[] = array(
								'value'    => $product->id,
								'label'    => $product->name,
								'children' => $childrens,
							);
						}
					}
				}
			}

			$result = array(
				'options'  => $options_data,
				'has_more' => true,
			);

			return $result;
		}

		/**
		 * Retrieves SureCart product options based on search criteria and included IDs.
		 *
		 * This function fetches a list of product options from SureCart, filtered by the provided
		 * search term and specific product IDs. Results are returned in an associative array format.
		 *
		 * @param array  $options      Optional. Array of existing options to append to. Default empty array.
		 * @param string $search       Optional. Search term to filter products by name or other criteria.
		 *                             Default empty string.
		 *
		 * @return array Filtered list of product options, each as an associative array with 'id' and 'label' keys.
		 *
		 * @package Toolkit For Surecart.
		 * @since 1.0.0
		 */
		public function get_sc_products_options( $options = array(), $search = '' ) {

			$sc_products = Product::where(
				array(
					'archived' => false,
					'query'    => $search,
				)
			)->get();

			if ( ! is_wp_error( $sc_products ) && ! empty( $sc_products ) ) {
				if ( $sc_products ) {
					foreach ( $sc_products as $product ) {
						$options[] = array(
							'id'    => $product->id,
							'title' => $product->name,
						);
					}
				}
			}

			return $options;
		}

		/**
		 * Function to Enqueue Scripts.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function surelywp_tk_fc_enqueue_scripts() {

			$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			// fluent crm css.
			wp_register_style( 'surelywp-tk-fc', SURELYWP_TOOLKIT_ASSETS_URL . '/css/fluent-crm/surelywp-tk-fc.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_style( 'surelywp-tk-fc-min', SURELYWP_TOOLKIT_ASSETS_URL . '/css/fluent-crm/surelywp-tk-fc.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );

			// fluent crm js.
			wp_register_script( 'surelywp-tk-fc', SURELYWP_TOOLKIT_ASSETS_URL . '/js/fluent-crm/surelywp-tk-fc.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-fc-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/fluent-crm/surelywp-tk-fc.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$min_file    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
			$allow_pages = array( 'sc-orders', 'sc-customers', 'sc-subscriptions' );

			$localize = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			);

			if ( 'fluentcrm-admin' === $page || in_array( $page, $allow_pages, true ) ) {

				$localize['profile_btn_on_customers']     = self::get_settings_option( 'profile_btn_on_customers' );
				$localize['profile_btn_on_orders']        = self::get_settings_option( 'profile_btn_on_orders' );
				$localize['profile_btn_on_subscriptions'] = self::get_settings_option( 'profile_btn_on_subscriptions' );

				wp_enqueue_style( 'surelywp-tk-fc' . $min_file );
				wp_enqueue_script( 'surelywp-tk-fc' . $min_file );
				wp_localize_script( 'surelywp-tk-fc' . $min_file, 'tk_fc_backend_ajax_object', $localize );
			}
		}

		/**
		 * Retrieves SureCart collection options based on search criteria and included IDs.
		 *
		 * This function fetches a list of collection options from SureCart, filtered by the provided
		 * search term and specific collection IDs. Results are returned in an associative array format.
		 *
		 * @param array  $options      Optional. Array of existing options to append to. Default empty array.
		 * @param string $search       Optional. Search term to filter collection by name or other criteria.
		 *                             Default empty string.
		 *
		 * @return array Filtered list of collection options, each as an associative array with 'id' and 'label' keys.
		 *
		 * @package Toolkit For Surecart.
		 * @since 1.0.0
		 */
		public function get_sc_products_collections_options( $options = array(), $search = '' ) {

			$sc_collections = ProductCollection::with()->where(
				array(
					'query' => $search['search'],
				)
			)->get();

			$childrens    = array();
			$options_data = array();
			if ( ! is_wp_error( $sc_collections ) && ! empty( $sc_collections ) ) {

				if ( $sc_collections ) {
					foreach ( $sc_collections as $sc_collection ) {

						$childrens[] = array(
							'value' => $sc_collection->id ?? '',
							'label' => $sc_collection->name ?? '',
						);
					}
				}
			}
			$options_data[] = array(
				'children' => $childrens,
			);
			$result         = array(
				'options'  => $options_data,
				'has_more' => true,
			);

			return $result;
		}

		/**
		 * Function to Get option by Name.
		 *
		 * @param string $option_name The option name of setting.
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function get_settings_option( $option_name ) {

			$options = get_option( 'surelywp_tk_fc_settings_options' );
			if ( isset( $options[ $option_name ] ) ) {
				$option_value = $options[ $option_name ];
			} else {
				$option_value = '';
			}

			return $option_value;
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Fluent_Crm class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function Surelywp_Tk_Fluent_Crm() {  // phpcs:ignore
		$instance = Surelywp_Tk_Fluent_Crm::get_instance();
		return $instance;
	}
}
