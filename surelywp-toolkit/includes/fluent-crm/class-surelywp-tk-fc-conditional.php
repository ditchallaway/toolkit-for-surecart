<?php
/**
 * Add Conditional group.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.0
 */

namespace SurelywpToolkit\Includes\FluentCrm;

use SureCart\Models\Product;
use SureCart\Models\Purchase;
use SureCart\Models\Customer;

use FluentCampaign\App\Services\Commerce\Commerce;
use FluentCrm\App\Models\FunnelSubscriber;
use FluentCrm\App\Models\Subscriber;
use FluentCrm\App\Services\Libs\ConditionAssessor;

// Exit if accessed directly.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}


if ( ! class_exists( 'Surelywp_Tk_Fc_Conditional' ) ) {

	/**
	 * Main class for Surecart conditions.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	class Surelywp_Tk_Fc_Conditional {

		/**
		 * Constructor function.
		 *
		 * Initializes the class.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function __construct() {

			// This is a filter hook and this hook will add your custom condition to the automation condition list.
			add_filter( 'fluentcrm_automation_condition_groups', array( $this, 'add_automation_conditions' ), 10, 2 );

			// This is a filter hook and this hook will filter records according to your custom condition logic.
			add_filter( 'fluentcrm_automation_conditions_assess_surecart', array( $this, 'assess_automation_conditions' ), 10, 5 );
		}

		/**
		 * Adds custom automation conditions to the funnel.
		 *
		 * This method appends a custom condition group with a "Purchased Products"
		 * condition. The condition allows selecting multiple products from a product selector.
		 *
		 * @param array  $groups The existing condition groups.
		 * @param object $funnel The funnel object to which the condition is being added.
		 *
		 * @return array The updated groups with the custom condition added.
		 *
		 * @package Toolkit For Surecart.
		 * @since 1.0.0
		 */
		public function add_automation_conditions( $groups, $funnel ) {

			$items = array(
				array(
					'value'             => 'purchased_items',
					'label'             => esc_html__( 'Purchased Products', 'surelywp-toolkit' ),
					'type'              => 'selections',
					'component'         => 'product_selector',
					'is_singular_value' => true,
					'is_multiple'       => true,
					'disabled'          => false,
				),
				array(
					'value'            => 'purchased_price_items',
					'label'            => esc_html__( 'Purchased Product Prices', 'surelywp-toolkit' ),
					'type'             => 'cascade_selections',
					'provider'         => 'surecart_product_prices',
					'is_multiple'      => true,
					'disabled'         => false,
					'custom_operators' => array(
						'exist'     => 'Purchased',
						'not_exist' => 'Not Purchased',
					),
				),
				array(
					'value'            => 'collections_items',
					'label'            => esc_html__( 'Purchased Product Collections', 'surelywp-toolkit' ),
					'type'             => 'cascade_selections',
					'provider'         => 'surecart_product_collections',
					'is_multiple'      => true,
					'disabled'         => false,
					'custom_operators' => array(
						'in'         => esc_html__( 'includes', 'surelywp-toolkit' ),
						'no_in'      => esc_html__( 'Does not includes (in any)', 'surelywp-toolkit' ),
						'in_all'     => esc_html__( 'includes all of', 'surelywp-toolkit' ),
						'not_in_all' => esc_html__( 'Includes none of (match all)', 'surelywp-toolkit' ),
					),
				),
				array(
					'value'            => 'purchased_variations_items',
					'label'            => esc_html__( 'Purchased Products Variants', 'surelywp-toolkit' ),
					'type'             => 'cascade_selections',
					'provider'         => 'surecart_product_variants',
					'is_multiple'      => true,
					'disabled'         => false,
					'custom_operators' => array(
						'exist'     => 'Purchased',
						'not_exist' => 'Not Purchased',
					),
				),
				array(
					'value'            => 'total_order_count',
					'label'            => esc_html__( 'Total Order Count', 'surelywp-toolkit' ),
					'type'             => 'numeric',
					'custom_operators' => array(
						'greater' => esc_html__( 'Greater than', 'surelywp-toolkit' ),
						'less'    => esc_html__( 'Less than', 'surelywp-toolkit' ),
						'equal'   => esc_html__( 'Equal to', 'surelywp-toolkit' ),
					),
				),
				array(
					'value'            => 'total_order_value',
					'label'            => esc_html__( 'Total Order Value', 'surelywp-toolkit' ),
					'type'             => 'numeric',
					'custom_operators' => array(
						'greater' => esc_html__( 'Greater than', 'surelywp-toolkit' ),
						'less'    => esc_html__( 'Less than', 'surelywp-toolkit' ),
						'equal'   => esc_html__( 'Equal to', 'surelywp-toolkit' ),
					),
				),
				array(
					'value'            => 'first_order_date',
					'label'            => esc_html__( 'First Order Date', 'surelywp-toolkit' ),
					'type'             => 'dates',
					'custom_operators' => array(
						'before' => esc_html__( 'Before', 'surelywp-toolkit' ),
						'after'  => esc_html__( 'After', 'surelywp-toolkit' ),
						'equal'  => esc_html__( 'On', 'surelywp-toolkit' ),
					),
				),
				array(
					'value'            => 'last_order_date',
					'label'            => esc_html__( 'Last Order Date', 'surelywp-toolkit' ),
					'type'             => 'dates',
					'custom_operators' => array(
						'before' => esc_html__( 'Before', 'surelywp-toolkit' ),
						'after'  => esc_html__( 'After', 'surelywp-toolkit' ),
						'equal'  => esc_html__( 'On', 'surelywp-toolkit' ),
					),
				),
				array(
					'value'            => 'used_coupons',
					'label'            => esc_html__( 'Used Coupons', 'surelywp-toolkit' ),
					'type'             => 'cascade_selections',
					'provider'         => 'surecart_coupons_promotions',
					'is_multiple'      => true,
					'disabled'         => false,
					'custom_operators' => array(
						'in'         => esc_html__( 'includes', 'surelywp-toolkit' ),
						'no_in'      => esc_html__( 'Does not includes (in any)', 'surelywp-toolkit' ),
						'in_all'     => esc_html__( 'includes all of', 'surelywp-toolkit' ),
						'not_in_all' => esc_html__( 'Includes none of (match all)', 'surelywp-toolkit' ),
					),
				),
			);

			$groups['surecart'] = array(
				'label'    => esc_html__( 'Surecart', 'surelywp-toolkit' ),
				'value'    => 'surecart',
				'children' => $items,
			);

			return $groups;
		}

		/**
		 * Assesses automation conditions for a subscriber.
		 *
		 * This method processes specified conditions against a subscriber
		 * to determine if they meet the criteria. The result of the conditions
		 * evaluation is returned.
		 *
		 * @param bool   $result      The initial result of condition assessment.
		 * @param array  $conditions  The conditions to evaluate.
		 * @param object $subscriber  The subscriber object being evaluated.
		 *
		 * @return bool The result of the condition assessment.
		 * @package Toolkit For Surecart.
		 * @since 1.0.0
		 */
		public function assess_automation_conditions( $result, $conditions, $subscriber, $sequence, $funnelSubscriberId ) {

			global $surelywp_tk_model;

			if ( ! $sequence || ! $funnelSubscriberId ) {
				return $result;
			}

			$funnelSub = FunnelSubscriber::find( $funnelSubscriberId );

			$customer_id = '';
			// Get Customer id.

			$sc_object = isset( $funnelSub->notes ) && ! empty( $funnelSub->notes ) ? json_decode( $funnelSub->notes ) : '';

			$obj_names = array( 'purchase', 'checkout', 'subscription', 'refund' );
			if ( is_object( $sc_object ) && ! is_wp_error( $sc_object ) && in_array( $sc_object->object, $obj_names, true ) ) {
				$customer_id = $sc_object->customer_id;
			} else {

				$user_email                  = $subscriber->email;
				$surecart_checkout_form_mode = \SureCart::cart()->getMode();
				$existing_customer           = Customer::where(
					array(
						'email'     => strtolower( $user_email ),
						'live_mode' => 'test' === $surecart_checkout_form_mode ? false : true,
					)
				)->get();

				if ( is_wp_error( $existing_customer ) || empty( $existing_customer ) ) {
					return false;
				}

				$customer_id = $existing_customer[0]->id;
			}

			// if customer id not found.
			if ( empty( $customer_id ) ) {
				return false;
			}

			foreach ( $conditions as $condition ) {

				$data_key    = $condition['data_key'];
				$data_values = $condition['data_value'];
				$operator    = $condition['operator'];

				switch ( $data_key ) {
					case 'purchased_items':
						$result = $this->check_condition_for_purchased_products( $customer_id, $data_values, $operator );
						break;

					case 'purchased_price_items':
						$result = $this->check_condition_for_purchased_product_price( $customer_id, $data_values, $operator );
						break;

					case 'purchased_variations_items':
						$result = $this->check_condition_for_purchased_product_variant( $customer_id, $data_values, $operator );
						break;

					case 'total_order_count':
						$result = $surelywp_tk_model->check_condition_for_total_order_count( $customer_id, $data_values, $operator );
						break;

					case 'total_order_value':
						$result = $surelywp_tk_model->check_condition_for_total_order_value( $customer_id, ( $data_values * 100 ), $operator );
						break;

					case 'first_order_date':
						$result = $surelywp_tk_model->check_condition_for_order_date( $customer_id, $data_values, $operator );
						break;

					case 'last_order_date':
						$result = $surelywp_tk_model->check_condition_for_order_date( $customer_id, $data_values, $operator, 'last' );
						break;
					case 'used_coupons':
						$result = $surelywp_tk_model->check_condition_for_used_coupons( $customer_id, $data_values, $operator );
						break;
					case 'collections_items':
						$result = $surelywp_tk_model->check_condition_for_collections_items( $customer_id, $data_values, $operator );
						break;
				}

				if ( ! $result ) {
					return false;
				}
			}

			return $result;
		}

		/**
		 * Checks if a customer has purchased specific products.
		 *
		 * This function verifies if a customer has purchased one or more products
		 * specified by the provided product IDs. The check can be customized based on
		 * the operator parameter to determine if any or all of the products have been purchased.
		 *
		 * @param int    $customer_id The ID of the customer to check purchases for.
		 * @param array  $product_ids An array of product IDs to check against the customer's purchase history.
		 * @param string $operator   The operator to use for checking the condition.
		 *                           Accepts 'any' to check if the customer purchased at least one of the products,
		 *                           or 'all' to check if the customer purchased all products.
		 *
		 * @return bool Returns true if the condition specified by the operator is met, false otherwise.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.0.0
		 */
		public function check_condition_for_purchased_products( $customer_id, $product_ids, $operator ) {

			$purchases = Purchase::where(
				array(
					'customer_ids' => array( $customer_id ),
					'product_ids'  => $product_ids,
				)
			)->paginate(
				array(
					'page'     => 1,
					'per_page' => 1,
				)
			);

			if ( ! is_wp_error( $purchases ) && ! empty( $purchases ) ) {

				if ( 'in' === $operator && $purchases->pagination->count ) {
					return true;
				} elseif ( 'not_in' === $operator && ! $purchases->pagination->count ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks if a customer has purchased specific product price.
		 *
		 * This function verifies if a customer has purchased one or more products
		 * specified by the provided product IDs. The check can be customized based on
		 * the operator parameter to determine if any or all of the products have been purchased.
		 *
		 * @param int    $customer_id The ID of the customer to check purchases for.
		 * @param array  $data_values An array of product IDs to check against the customer's purchase history.
		 * @param string $operator   The operator to use for checking the condition.
		 *                           Accepts 'any' to check if the customer purchased at least one of the products,
		 *                           or 'all' to check if the customer purchased all products.
		 *
		 * @return bool Returns true if the condition specified by the operator is met, false otherwise.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.0.0
		 */
		public function check_condition_for_purchased_product_price( $customer_id, $data_values, $operator ) {

			$product_ids = array();
			$price_ids   = array();

			if ( ! empty( $data_values ) ) {
				foreach ( $data_values as $value ) {

					// Split the string by "-surelywp-separate-".
					$parts = explode( '-surelywp-separate-', $value );

					// Ensure you have both parts.
					if ( count( $parts ) === 2 ) {
						$product_ids[] = $parts[0];
						$price_ids[]   = $parts[1];
					}
				}
			} else {
				return false;
			}

			$purchases = Purchase::where(
				array(
					'customer_ids' => array( $customer_id ),
					'product_ids'  => $product_ids,
				)
			)->paginate(
				array(
					'page'     => 1,
					'per_page' => 100,
				)
			);

			$purchase_prices = array();

			if ( ! is_wp_error( $purchases ) && ! empty( $purchases ) && $purchases->pagination->count ) {

				foreach ( $purchases->data as $purchase ) {
					$purchase_prices[] = $purchase->price;
				}

				if ( 'exist' === $operator && array_intersect( $purchase_prices, $price_ids ) ) {
					return true;
				} elseif ( 'not_exist' === $operator && ! array_intersect( $purchase_prices, $price_ids ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks if a customer has purchased specific product price.
		 *
		 * This function verifies if a customer has purchased one or more products
		 * specified by the provided product IDs. The check can be customized based on
		 * the operator parameter to determine if any or all of the products have been purchased.
		 *
		 * @param int    $customer_id The ID of the customer to check purchases for.
		 * @param array  $data_values An array of product IDs to check against the customer's purchase history.
		 * @param string $operator   The operator to use for checking the condition.
		 *                           Accepts 'any' to check if the customer purchased at least one of the products,
		 *                           or 'all' to check if the customer purchased all products.
		 *
		 * @return bool Returns true if the condition specified by the operator is met, false otherwise.
		 *
		 * @package Toolkit For Surecart
		 * @since   1.0.0
		 */
		public function check_condition_for_purchased_product_variant( $customer_id, $data_values, $operator ) {

			$product_ids = array();
			$variant_ids = array();

			if ( ! empty( $data_values ) ) {
				foreach ( $data_values as $value ) {

					// Split the string by "-surelywp-separate-".
					$parts = explode( '-surelywp-separate-', $value );

					// Ensure you have both parts.
					if ( count( $parts ) === 2 ) {
						$product_ids[] = $parts[0];
						$variant_ids[] = $parts[1];
					}
				}
			} else {
				return false;
			}

			$purchases = Purchase::where(
				array(
					'customer_ids' => array( $customer_id ),
					'product_ids'  => $product_ids,
				)
			)->paginate(
				array(
					'page'     => 1,
					'per_page' => 100,
				)
			);

			$purchase_variants = array();

			if ( ! is_wp_error( $purchases ) && ! empty( $purchases ) && $purchases->pagination->count ) {

				foreach ( $purchases->data as $purchase ) {
					$purchase_variants[] = $purchase->variant;
				}

				if ( 'exist' === $operator && array_intersect( $purchase_variants, $variant_ids ) ) {
					return true;
				} elseif ( 'not_exist' === $operator && ! array_intersect( $purchase_variants, $variant_ids ) ) {
					return true;
				}
			}

			return false;
		}
	}
}
