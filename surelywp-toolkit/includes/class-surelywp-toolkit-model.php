<?php
/**
 * SurelyWP Toolkit Model Class.
 *
 * @package Toolkit For SureCart
 * @since   1.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureCart\Models\Product;
use SureCart\Models\ProductCollection;

// Global Model Variable.
global $surelywp_tk_model;

if ( ! class_exists( 'Surelywp_Toolkit_Model' ) ) {

	/**
	 * Plugin Model Class
	 *
	 * Handles generic functionailties
	 *
	 * Surelywp Toolkit
	 *
	 * @since 1.3
	 */
	class Surelywp_Toolkit_Model {

		/**
		 *
		 * Class constructor
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function __construct() {
		}


		/**
		 * The single instance of the class.
		 *
		 * @var SurelyWP_Assets
		 */
		private static $instance;

		/**
		 * Singleton implementation.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Insert SureCart Checkout Data.
		 *
		 * Surelywp Toolkit
		 *
		 * @param array $checkouts The data of the checkout.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_insert_checkout_data( $checkouts ) {
			global $wpdb;

			if ( empty( $checkouts ) || ! is_array( $checkouts ) ) {
				return false;
			}

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';

			$filtered_checkouts = array();

			// Step 1: Get existing updated_at values from DB.
			$ids          = array_column( $checkouts, 'id' );
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%s' ) );

			$existing = $wpdb->get_results(
				$wpdb->prepare( "SELECT checkout_id, updated_at FROM $table_name WHERE checkout_id IN ($placeholders)", ...$ids ),
				OBJECT_K
			);

			// Step 2: Only keep records where updated_at is newer or doesn't exist.
			foreach ( $checkouts as $data ) {
				$id = $data['id'];
				if (
					! isset( $existing[ $id ] ) ||
					strtotime( $data['updated_at'] ) > strtotime( $existing[ $id ]->updated_at )
				) {
					$filtered_checkouts[] = $data;
				}
			}

			if ( empty( $filtered_checkouts ) ) {
				return 0;
			}

			// Step 3: Batch insert with ON DUPLICATE KEY UPDATE.
			$rows_sql     = array();
			$placeholders = array();
			$values       = array();

			foreach ( $filtered_checkouts as $data ) {

				$is_array = is_array( $data );
				
				// Ensure $data is an array for consistent access.
				$data = surelywp_tk_convert_std_class_to_array( $data );
				
				$promotion_id = null;
				if ( $is_array && isset( $data['discount']['promotion']['id'] ) ) {
					$promotion_id = $data['discount']['promotion']['id'];
				} elseif ( $is_array && isset( $data['discount']['promotion'] ) && is_string( $data['discount']['promotion'] ) ) {
					$promotion_id = $data['discount']['promotion'];
				} elseif ( isset( $data->discount->promotion->id ) ) {
					$promotion_id = $data->discount->promotion->id;
				}

				$purchased_product_ids = array();

				if ( $is_array && isset( $data['line_items']['data'] ) ) {
					foreach ( $data['line_items']['data'] as $item ) {
						if ( isset( $item['price']['product']['id'] ) ) {
							$purchased_product_ids[] = $item['price']['product']['id'];
						} elseif ( isset( $item['price']['product'] ) && is_string( $item['price']['product'] ) ) {
							$purchased_product_ids[] = $item['price']['product'];
						}
					}
				} elseif ( isset( $data->line_items->data ) ) {
					foreach ( $data->line_items->data as $item ) {
						if ( isset( $item->price->product->id ) ) {
							$purchased_product_ids[] = $item->price->product->id;
						} elseif ( isset( $item->price->product ) && is_string( $item->price->product ) ) {
							$purchased_product_ids[] = $item->price->product;
						}
					}
				}

				// Minimal example - replace with full field list as needed.
				$fields = array(
					'checkout_id'             => $data['id'],
					'amount_due'              => $data['amount_due'],
					'applied_balance_amount'  => $data['applied_balance_amount'],
					'credited_balance_amount' => $data['credited_balance_amount'],
					'currency'                => $data['currency'],
					'discount_amount'         => $data['discount_amount'],
					'email'                   => $data['email'],
					'first_name'              => $data['first_name'],
					'full_amount'             => $data['full_amount'],
					'last_name'               => $data['last_name'],
					'live_mode'               => $data['live_mode'],
					'manual_payment'          => $data['manual_payment'],
					'metadata'                => maybe_serialize( $data['metadata'] ),
					'name'                    => $data['name'],
					'net_paid_amount'         => $data['net_paid_amount'],
					'paid_amount'             => $data['paid_amount'],
					'g_weight'                => $data['g_weight'],
					'paid_at'                 => $data['paid_at'],
					'phone'                   => $data['phone'],
					'proration_amount'        => $data['proration_amount'],
					'refunded_amount'         => $data['refunded_amount'],
					'remaining_amount_due'    => $data['remaining_amount_due'],
					'shipping_amount'         => $data['shipping_amount'],
					'shipping_tax_amount'     => $data['shipping_tax_amount'],
					'shipping_tax_rate'       => $data['shipping_tax_rate'],
					'status'                  => $data['status'],
					'subtotal_amount'         => $data['subtotal_amount'],
					'tax_amount'              => $data['tax_amount'],
					'total_amount'            => $data['total_amount'],
					'total_savings_amount'    => $data['total_savings_amount'],
					'trial_amount'            => $data['trial_amount'],
					'current_payment_intent'  => $data['current_payment_intent'],
					'current_upsell'          => maybe_serialize( $data['current_upsell'] ?? '' ),
					'invoice'                 => maybe_serialize( $data['invoice'] ?? '' ),
					'customer'                => $data['customer'],
					'purchased_product_ids'   => maybe_serialize( $purchased_product_ids ),
					'discount'                => maybe_serialize( $data['discount'] ),
					'promotion_id'            => $promotion_id,
					'order_id'                => $data['order'],
					'payment_method'          => $data['payment_method'] ?? '',
					'referral'                => maybe_serialize( $data['referral'] ),
					'shipping_address'        => $data['shipping_address'],
					'tax_identifier'          => maybe_serialize( $data['tax_identifier'] ),
					'upsell_funnel'           => maybe_serialize( $data['upsell_funnel'] ?? '' ),
					'bump_amount'             => $data['bump_amount'],
					'number'                  => $data['number'],
					'pdf_url'                 => $data['pdf_url'],
					'payment_intent'          => $data['payment_intent'] ?? '',
					'charge'                  => $data['charge'],
					'checkout_data'           => wp_json_encode( $data ),
					'created_at'              => $data['created_at'],
					'updated_at'              => $data['updated_at'],
				);

				$placeholders[] = '(' . implode( ', ', array_fill( 0, count( $fields ), '%s' ) ) . ')';
				$values         = array_merge( $values, array_values( $fields ) );
			}

			$columns = implode( ', ', array_keys( $fields ) );
			$sql     = "
				INSERT INTO $table_name ( $columns )
				VALUES " . implode( ', ', $placeholders ) . '
				ON DUPLICATE KEY UPDATE 
					email = VALUES(email),
					status = VALUES(status),
					updated_at = VALUES(updated_at)
			';

			$result = $wpdb->query( $wpdb->prepare( $sql, ...$values ) );

			return $result;
		}

		/**
		 * Retrieves customer emails that match a specified order count condition,
		 * considering only checkouts with status 'paid'.
		 *
		 * @param int    $values   The number of orders to compare against.
		 * @param string $operator The comparison operator: 'equal', 'greater', or 'less'.
		 *
		 * @return array           List of email addresses matching the condition.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function get_emails_by_order_count( $values, $operator ) {

			global $wpdb;

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';
			$count      = intval( $values );

			// Determine the SQL comparison operator.
			switch ( $operator ) {
				case 'equal':
					$comparison = '=';
					break;
				case 'greater':
					$comparison = '>';
					break;
				case 'less':
					$comparison = '<';
					break;
				default:
					return array(); // Invalid operator.
			}

			// Prepare and execute the SQL query.
			$query = $wpdb->prepare(
				"SELECT email 
				 FROM {$table_name}
				 WHERE email IS NOT NULL 
				   AND email != '' 
				   AND status = %s
				 GROUP BY email
				 HAVING COUNT(*) {$comparison} %d",
				'paid',
				$count
			);
			return $wpdb->get_col( $query );
		}

		/**
		 * Retrieves customer emails whose total paid order value meets a specified condition,
		 * considering only checkouts with status 'paid'.
		 *
		 * @param int    $values   The total order value (e.g. in cents) to compare against.
		 * @param string $operator The comparison operator: 'equal', 'greater', or 'less'.
		 *
		 * @return array           List of email addresses matching the condition.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function get_emails_by_total_order_value( $values, $operator ) {
			global $wpdb;

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';
			$total      = intval( $values );

			// Determine the SQL comparison operator.
			switch ( $operator ) {
				case 'equal':
					$comparison = '=';
					break;
				case 'greater':
					$comparison = '>';
					break;
				case 'less':
					$comparison = '<';
					break;
				default:
					return array(); // Invalid operator.
			}

			// Prepare and execute the SQL query.
			$query = $wpdb->prepare(
				"SELECT email
		 		FROM {$table_name}
		 		WHERE email IS NOT NULL 
		   		AND email != ''
		   		AND status = %s
		 		GROUP BY email
		 		HAVING SUM(paid_amount) {$comparison} %d",
					'paid',
					$total
			);

			return $wpdb->get_col( $query );
		}

		/**
		 * Retrieves customer emails based on the date of their first or last paid order.
		 *
		 * @param int    $timestamp   The target timestamp (UNIX time) to compare against.
		 * @param string $operator    Comparison operator: 'equal', 'before', or 'after'.
		 * @param string $which       Which date to use: 'first' (MIN) or 'last' (MAX).
		 *
		 * @return array              List of email addresses matching the condition.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function get_emails_by_order_date( $timestamp, $operator, $which = 'first' ) {

			global $wpdb;

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';
			$timestamp  = intval( $timestamp );

			// Choose MIN or MAX based on 'first' or 'last'.
			$date_func = $which === 'last' ? 'MAX' : 'MIN';

			// Default to false query.
			$query  = '';
			$params = array();

			switch ( $operator ) {
				case 'equal':
					$start_of_day = strtotime( date( 'Y-m-d 00:00:00', $timestamp ) );
					$end_of_day   = strtotime( date( 'Y-m-d 23:59:59', $timestamp ) );

					$query  = "
						SELECT email
						FROM {$table_name}
						WHERE email IS NOT NULL
						  AND email != ''
						  AND status = %s
						GROUP BY email
						HAVING {$date_func}(created_at) BETWEEN %d AND %d
					";
					$params = array( 'paid', $start_of_day, $end_of_day );
					break;

				case 'before':
				case 'after':
					$comparison = $operator === 'before' ? '<' : '>';
					$query      = "
						SELECT email
						FROM {$table_name}
						WHERE email IS NOT NULL
						  AND email != ''
						  AND status = %s
						GROUP BY email
						HAVING {$date_func}(created_at) {$comparison} %d
					";
					$params     = array( 'paid', $timestamp );
					break;

				default:
					return array(); // Invalid operator.
			}

			// Run the prepared query.
			$sql = $wpdb->prepare( $query, ...$params );
			return $wpdb->get_col( $sql );
		}

		/**
		 * Checks if a specific customer's total number of paid orders meets the given condition.
		 *
		 * @param string $customer_id  The customer ID to check.
		 * @param int    $data_values  The target order count.
		 * @param string $operator     Comparison operator: 'equal', 'greater', or 'less'.
		 *
		 * @return bool  True if the condition is met, false otherwise.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function check_condition_for_total_order_count( $customer_id, $data_values, $operator ) {
			global $wpdb;

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';
			$count      = intval( $data_values );

			// Determine SQL comparison operator.
			switch ( $operator ) {
				case 'equal':
					$comparison = '=';
					break;
				case 'greater':
					$comparison = '>';
					break;
				case 'less':
					$comparison = '<';
					break;
				default:
					return false; // Invalid operator.
			}

			// Get the customer's paid order count.
			$query = $wpdb->prepare(
				"SELECT COUNT(*) 
		 FROM {$table_name}
		 WHERE customer = %s
		   AND status = %s",
				$customer_id,
				'paid'
			);

			$order_count = (int) $wpdb->get_var( $query );

			// Compare with provided value.
			switch ( $comparison ) {
				case '=':
					return $order_count === $count;
				case '>':
					return $order_count > $count;
				case '<':
					return $order_count < $count;
			}

			return false;
		}

		/**
		 * Checks if a specific customer's total paid order value meets the given condition.
		 *
		 * @param string $customer_id  The customer ID to check.
		 * @param int    $data_values  The target total value (in smallest currency unit, e.g., cents).
		 * @param string $operator     Comparison operator: 'equal', 'greater', or 'less'.
		 *
		 * @return bool  True if the condition is met, false otherwise.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function check_condition_for_total_order_value( $customer_id, $data_values, $operator ) {
			global $wpdb;

			$table_name  = $wpdb->prefix . 'surelywp_surecart_checkouts';
			$total_value = intval( $data_values );

			// Determine SQL comparison operator.
			switch ( $operator ) {
				case 'equal':
					$comparison = '=';
					break;
				case 'greater':
					$comparison = '>';
					break;
				case 'less':
					$comparison = '<';
					break;
				default:
					return false; // Invalid operator
			}

			// Get the total paid amount for the customer.
			$query = $wpdb->prepare(
				"SELECT SUM(paid_amount) 
				FROM {$table_name}
				WHERE customer = %s
		   		AND status = %s",
					$customer_id,
					'paid'
			);

			$total_paid = (int) $wpdb->get_var( $query );

			// Compare with provided value.
			switch ( $comparison ) {
				case '=':
					return $total_paid === $total_value;
				case '>':
					return $total_paid > $total_value;
				case '<':
					return $total_paid < $total_value;
			}

			return false;
		}

		/**
		 * Checks if a specific customer's first or last paid order date meets the given condition.
		 *
		 * @param string $customer_id  The customer ID to check.
		 * @param string $date         Target date in 'Y-m-d' format.
		 * @param string $operator     Comparison operator: 'equal', 'before', or 'after'.
		 * @param string $which        Either 'first' or 'last' order date.
		 *
		 * @return bool  True if the condition is met, false otherwise.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function check_condition_for_order_date( $customer_id, $date, $operator, $which = 'first' ) {

			global $wpdb;

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';

			// Convert to UNIX timestamps.
			$target_start = strtotime( $date . ' 00:00:00' );
			$target_end   = strtotime( $date . ' 23:59:59' );

			// Choose aggregate function
			$date_func = $which === 'last' ? 'MAX' : 'MIN';

			// Query to get the customer's first/last paid order date.
			$query = $wpdb->prepare(
				"SELECT {$date_func}(created_at) 
		 		FROM {$table_name}
		 		WHERE customer = %s
		   		AND status = %s",
					$customer_id,
					'paid'
			);

			$order_timestamp = (int) $wpdb->get_var( $query );

			if ( ! $order_timestamp ) {
				return false; // No paid orders found.
			}

			// Evaluate condition.
			switch ( $operator ) {
				case 'equal':
					return ( $order_timestamp >= $target_start && $order_timestamp <= $target_end );
				case 'before':
					return ( $order_timestamp < $target_start );
				case 'after':
					return ( $order_timestamp > $target_end );
				default:
					return false;
			}
		}

		/**
		 * Retrieves customer emails that match a specified order count condition,
		 * considering only checkouts with status 'paid'.
		 *
		 * @param int    $values   The number of orders to compare against.
		 * @param string $operator The comparison operator: 'equal', 'greater', or 'less'.
		 *
		 * @return array           List of email addresses matching the condition.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function get_emails_by_used_coupons( $values, $operator ) {
			global $wpdb;

			if ( empty( $values ) || ! is_array( $values ) ) {
				return array();
			}

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';

			// Group coupon promotion IDs.
			$groups = array_map(
				function ( $val ) {
					return array_map( 'trim', explode( ',', $val ) );
				},
				$values
			);

			switch ( $operator ) {

				case 'in':
				case 'no_in': {
					$flat         = array_unique( call_user_func_array( 'array_merge', $groups ) );
					$placeholders = implode( ',', array_fill( 0, count( $flat ), '%s' ) );
					$params       = array_merge( array( 'paid' ), $flat );

					$sql = "
						SELECT DISTINCT email
						FROM {$table_name}
						WHERE email IS NOT NULL AND email != ''
						AND status = %s
						AND promotion_id " . ( $operator === 'in' ? 'IN' : 'NOT IN' ) . " ($placeholders)
					";

					return $wpdb->get_col( $wpdb->prepare( $sql, $params ) );
				}

				case 'in_all': {
					$matched_sets = array();

					foreach ( $groups as $id_group ) {
						$placeholders = implode( ',', array_fill( 0, count( $id_group ), '%s' ) );
						$params       = array_merge( array( 'paid' ), $id_group );

						$sql            = "
							SELECT DISTINCT email
							FROM {$table_name}
							WHERE email IS NOT NULL AND email != ''
							AND status = %s
							AND promotion_id IN ($placeholders)
						";
						$emails         = $wpdb->get_col( $wpdb->prepare( $sql, $params ) );
						$matched_sets[] = $emails;
					}

					// Return intersection (emails present in all groups).
					if ( count( $matched_sets ) > 1 ) {
						return array_values( call_user_func_array( 'array_intersect', $matched_sets ) );
					} else {
						return $matched_sets[0] ?? array();
					}
				}

				case 'not_in_all': {
					// All paid emails.
					$sql_all    = "
						SELECT DISTINCT email
						FROM {$table_name}
						WHERE email IS NOT NULL AND email != ''
						AND status = %s
					";
					$all_emails = $wpdb->get_col( $wpdb->prepare( $sql_all, 'paid' ) );

					// Union of all emails who matched *any* group.
					$used_emails = array();
					foreach ( $groups as $id_group ) {
						$placeholders = implode( ',', array_fill( 0, count( $id_group ), '%s' ) );
						$params       = array_merge( array( 'paid' ), $id_group );

						$sql         = "
							SELECT DISTINCT email
							FROM {$table_name}
							WHERE email IS NOT NULL AND email != ''
							AND status = %s
							AND promotion_id IN ($placeholders)
						";
						$used_emails = array_merge( $used_emails, $wpdb->get_col( $wpdb->prepare( $sql, $params ) ) );
					}

					return array_values( array_diff( $all_emails, array_unique( $used_emails ) ) );
				}

				default:
					return array();
			}
		}


		/**
		 * Retrieves customer emails that match a specified order count condition,
		 * considering only checkouts with status 'paid'.
		 *
		 * @param int    $values   The number of orders to compare against.
		 * @param string $operator The comparison operator: 'equal', 'greater', or 'less'.
		 *
		 * @return array           List of email addresses matching the condition.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function get_emails_by_product_collection( $values, $operator ) {
			global $wpdb;

			if ( empty( $values ) ) {
				return array();
			}

			// Step 1: Get product IDs grouped by collection.
			$collection_products = array();
			foreach ( $values as $collection_id ) {
				$collection_obj = ProductCollection::with( array( 'products' ) )->find( $collection_id );
				if ( ! is_wp_error( $collection_obj ) && ! empty( $collection_obj ) ) {
					$product_ids = array_map( fn( $product ) => $product->id, $collection_obj->products->data );
					if ( ! empty( $product_ids ) ) {
						$collection_products[] = $product_ids;
					}
				}
			}

			if ( empty( $collection_products ) ) {
				return array();
			}

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';

			switch ( $operator ) {
				case 'in':
				case 'no_in': {
					$flat_ids     = array_unique( array_merge( ...$collection_products ) );
					$conditions   = array_fill( 0, count( $flat_ids ), 'purchased_product_ids LIKE %s' );
					$params       = array_merge( array( 'paid' ), array_map( fn( $id ) => '%' . $id . '%', $flat_ids ) );
					$sql_operator = $operator === 'in' ? implode( ' OR ', $conditions ) : 'NOT (' . implode( ' OR ', $conditions ) . ')';

					$sql = "
						SELECT DISTINCT email
						FROM {$table_name}
						WHERE email IS NOT NULL AND email != ''
						AND status = %s
						AND ( {$sql_operator} )
					";

					return $wpdb->get_col( $wpdb->prepare( $sql, $params ) );
				}

				case 'in_all': {
					$email_sets = array();

					foreach ( $collection_products as $product_ids ) {
						$conditions = array_fill( 0, count( $product_ids ), 'purchased_product_ids LIKE %s' );
						$params     = array_merge( array( 'paid' ), array_map( fn( $id ) => '%' . $id . '%', $product_ids ) );

						$sql = "
							SELECT DISTINCT email
							FROM {$table_name}
							WHERE email IS NOT NULL AND email != ''
							AND status = %s
							AND ( " . implode( ' OR ', $conditions ) . ' )
						';

						$emails       = $wpdb->get_col( $wpdb->prepare( $sql, $params ) );
						$email_sets[] = $emails;
					}

					return count( $email_sets ) > 1
						? array_values( array_intersect( ...$email_sets ) )
						: ( $email_sets[0] ?? array() );
				}

				case 'not_in_all': {
					$sql_all    = "
						SELECT DISTINCT email
						FROM {$table_name}
						WHERE email IS NOT NULL AND email != ''
						AND status = %s
					";
					$all_emails = $wpdb->get_col( $wpdb->prepare( $sql_all, 'paid' ) );

					$used_emails = array();
					foreach ( $collection_products as $product_ids ) {
						$conditions = array_fill( 0, count( $product_ids ), 'purchased_product_ids LIKE %s' );
						$params     = array_merge( array( 'paid' ), array_map( fn( $id ) => '%' . $id . '%', $product_ids ) );

						$sql = "
							SELECT DISTINCT email
							FROM {$table_name}
							WHERE email IS NOT NULL AND email != ''
							AND status = %s
							AND ( " . implode( ' OR ', $conditions ) . ' )
						';

						$used_emails = array_merge( $used_emails, $wpdb->get_col( $wpdb->prepare( $sql, $params ) ) );
					}

					return array_values( array_diff( $all_emails, array_unique( $used_emails ) ) );
				}

				default:
					return array(); // Invalid operator
			}
		}

		/**
		 * Checks if a specific customer's used coupons match the given condition.
		 *
		 * @param string $customer_id  The customer ID to check.
		 * @param array  $values       Array of promotion ID sets.
		 * @param string $operator     Comparison operator: 'in', 'no_in', 'in_all', or 'not_in_all'.
		 *
		 * @return bool  True if the condition is met, false otherwise.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function check_condition_for_used_coupons( $customer_id, $values, $operator ) {

			global $wpdb;

			if ( empty( $customer_id ) || empty( $values ) ) {
				return false;
			}

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';

			$groups = array_map(
				function ( $item ) {
					return array_map( 'trim', explode( ',', $item ) );
				},
				$values
			);

			switch ( $operator ) {
				case 'in': {
					$promotion_ids = array_unique( call_user_func_array( 'array_merge', $groups ) );
					$placeholders  = implode( ',', array_fill( 0, count( $promotion_ids ), '%s' ) );
					$params        = array_merge( array( $customer_id, 'paid' ), $promotion_ids );

					$sql = "
				SELECT COUNT(*)
				FROM {$table_name}
				WHERE customer = %s
				AND status = %s
				AND promotion_id IN ($placeholders)
			";

					return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) > 0;
				}

				case 'no_in': {
					$promotion_ids = array_unique( call_user_func_array( 'array_merge', $groups ) );
					$placeholders  = implode( ',', array_fill( 0, count( $promotion_ids ), '%s' ) );
					$params        = array_merge( array( $customer_id, 'paid' ), $promotion_ids );

					$sql = "
				SELECT COUNT(*)
				FROM {$table_name}
				WHERE customer = %s
				AND status = %s
				AND promotion_id IN ($placeholders)
			";

					return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) === 0;
				}

				case 'in_all': {
					foreach ( $groups as $promotion_ids ) {
						$placeholders = implode( ',', array_fill( 0, count( $promotion_ids ), '%s' ) );
						$params       = array_merge( array( $customer_id, 'paid' ), $promotion_ids );

						$sql = "
					SELECT COUNT(*)
					FROM {$table_name}
					WHERE customer = %s
					AND status = %s
					AND promotion_id IN ($placeholders)
				";

						if ( (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) === 0 ) {
							return false; // One group not matched
						}
					}
					return true; // All groups matched
				}

				case 'not_in_all': {
					foreach ( $groups as $promotion_ids ) {
						$placeholders = implode( ',', array_fill( 0, count( $promotion_ids ), '%s' ) );
						$params       = array_merge( array( $customer_id, 'paid' ), $promotion_ids );

						$sql = "
					SELECT COUNT(*)
					FROM {$table_name}
					WHERE customer = %s
					AND status = %s
					AND promotion_id IN ($placeholders)
				";

						if ( (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) > 0 ) {
							return false;
						}
					}
					return true;
				}

				default:
					return false;
			}
		}

		/**
		 * Checks if a specific customer's purchased products match selected collections.
		 *
		 * @param string $customer_id  The customer ID to check.
		 * @param array  $values       Array of product collection IDs.
		 * @param string $operator     Custom operator: 'in', 'no_in', 'in_all', 'not_in_all'.
		 *
		 * @return bool  True if the condition is met, false otherwise.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function check_condition_for_collections_items( $customer_id, $values, $operator ) {
			global $wpdb;

			if ( empty( $customer_id ) || empty( $values ) ) {
				return false;
			}

			// Step 1: Resolve collection IDs to product ID groups
			$collection_products = array();
			foreach ( $values as $collection_id ) {
				$collection = ProductCollection::with( array( 'products' ) )->find( $collection_id );
				if ( ! is_wp_error( $collection ) && ! empty( $collection ) ) {
					$product_ids = array_map( fn( $product ) => $product->id, $collection->products->data );
					if ( ! empty( $product_ids ) ) {
						$collection_products[] = $product_ids;
					}
				}
			}

			if ( empty( $collection_products ) ) {
				return false;
			}

			$table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';

			// Normalize where clause builder
			$build_like_conditions = function ( $product_ids ) {
				return array_map( fn( $id ) => '%' . $id . '%', $product_ids );
			};

			switch ( $operator ) {
				case 'in': {
					$flat_ids   = array_unique( array_merge( ...$collection_products ) );
					$likes      = $build_like_conditions( $flat_ids );
					$conditions = implode( ' OR ', array_fill( 0, count( $likes ), 'purchased_product_ids LIKE %s' ) );

					$params = array_merge( array( $customer_id, 'paid' ), $likes );

					$sql = "
				SELECT COUNT(*)
				FROM {$table_name}
				WHERE customer = %s
				AND status = %s
				AND ( {$conditions} )
			";

					return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) > 0;
				}

				case 'no_in': {
					$flat_ids   = array_unique( array_merge( ...$collection_products ) );
					$likes      = $build_like_conditions( $flat_ids );
					$conditions = implode( ' OR ', array_fill( 0, count( $likes ), 'purchased_product_ids LIKE %s' ) );

					$params = array_merge( array( $customer_id, 'paid' ), $likes );

					$sql = "
				SELECT COUNT(*)
				FROM {$table_name}
				WHERE customer = %s
				AND status = %s
				AND ( {$conditions} )
			";

					return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) === 0;
				}

				case 'in_all': {
					foreach ( $collection_products as $product_ids ) {
						$likes      = $build_like_conditions( $product_ids );
						$conditions = implode( ' OR ', array_fill( 0, count( $likes ), 'purchased_product_ids LIKE %s' ) );
						$params     = array_merge( array( $customer_id, 'paid' ), $likes );

						$sql = "
					SELECT COUNT(*)
					FROM {$table_name}
					WHERE customer = %s
					AND status = %s
					AND ( {$conditions} )
				";

						if ( (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) === 0 ) {
							return false; // Did not purchase any product from this collection
						}
					}
					return true; // Purchased from all collections
				}

				case 'not_in_all': {
					foreach ( $collection_products as $product_ids ) {
						$likes      = $build_like_conditions( $product_ids );
						$conditions = implode( ' OR ', array_fill( 0, count( $likes ), 'purchased_product_ids LIKE %s' ) );
						$params     = array_merge( array( $customer_id, 'paid' ), $likes );

						$sql = "
					SELECT COUNT(*)
					FROM {$table_name}
					WHERE customer = %s
					AND status = %s
					AND ( {$conditions} )
				";

						if ( (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ) > 0 ) {
							return false; // Found match, so fail
						}
					}
					return true; // None of the collections matched
				}

				default:
					return false; // Invalid operator
			}
		}
	}

}

$surelywp_tk_model = Surelywp_Toolkit_Model::instance();
