<?php
/**
 * Add advance filter group.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.0
 */

namespace SurelywpToolkit\Includes\FluentCrm;

use SureCart\Models\Product;
use SureCart\Models\Purchase;
use SureCart\Models\Customer;
use SureCart\Models\Order;

use FluentCampaign\App\Services\Commerce\Commerce;
use FluentCampaign\App\Services\Commerce\ContactRelationModel;
use FluentCrm\App\Models\Subscriber;
use FluentCrm\Framework\Support\Arr;

// Exit if accessed directly.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}


if ( ! class_exists( 'Surelywp_Tk_Fc_Advance_Filter' ) ) {

	/**
	 * Main class for Surecart conditions.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	class Surelywp_Tk_Fc_Advance_Filter {

		/**
		 * Constructor function.
		 *
		 * Initializes the class.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function __construct() {

			// add new filter.
			add_filter( 'fluentcrm_deep_integration_providers', array( $this, 'add_deep_integration_provider' ), 10, 1 );
			add_filter( 'fluentcrm_deep_integration_sync_surelywp_surecart', array( $this, 'sync_surecart_customers' ), 10, 2 );
			add_filter( 'fluentcrm_deep_integration_save_surelywp_surecart', array( $this, 'save_settings' ), 10, 2 );

			add_filter( 'fluentcrm_advanced_filter_options', array( $this, 'add_advanced_filter_options' ), 10, 1 );
			add_action( 'fluentcrm_contacts_filter_surecart', array( $this, 'add_advanced_filter' ), 10, 2 );
		}

		/**
		 * Function to save settings.
		 *
		 * @param array $returnData the array of the returnData.
		 * @param array $config the array of the config.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function save_settings( $returnData, $config ) {

			$tags          = Arr::get( $config, 'tags', array() );
			$lists         = Arr::get( $config, 'lists', array() );
			$contactStatus = Arr::get( $config, 'contact_status', 'subscribed' );

			$settings = array(
				'tags'           => $tags,
				'lists'          => $lists,
				'contact_status' => $contactStatus,
			);

			if ( Arr::get( $config, 'action' ) == 'disable' ) {
				Commerce::disableModule( 'surelywp_surecart' );
				$settings['disabled_at'] = current_time( 'mysql' );
			}

			fluentcrm_update_option( '_surelywp_sc_sync_settings', $settings );

			return array(
				'message'  => 'Settings have been saved',
				'settings' => $this->get_sync_settings(),
			);
		}

		/**
		 * Function to sync surecart customers.
		 *
		 * @param array $returnData the array of the returnData.
		 * @param array $config the array of the config.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function sync_surecart_customers( $returnData, $config ) {

			$tags          = Arr::get( $config, 'tags', array() );
			$lists         = Arr::get( $config, 'lists', array() );
			$contactStatus = Arr::get( $config, 'contact_status', 'subscribed' );

			$settings = array(
				'tags'           => $tags,
				'lists'          => $lists,
				'contact_status' => $contactStatus,
			);

			fluentcrm_update_option( '_surelywp_sc_sync_settings', $settings );

			$status = $this->sync_customers(
				array(
					'tags'               => $tags,
					'lists'              => $lists,
					'new_status'         => $contactStatus,
					'double_optin_email' => ( $contactStatus == 'pending' ) ? 'yes' : 'no',
					'import_silently'    => 'yes',
				),
				$config['syncing_page']
			);

			return array(
				'syncing_status' => $status,
			);
		}

		/**
		 * Function to sync surecart customers.
		 *
		 * @param array $config the array of the config.
		 * @param int   $page the array of the page.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function sync_customers( $config, $page ) {

			$inputs = Arr::only(
				$config,
				array(
					'lists',
					'tags',
					'new_status',
					'double_optin_email',
					'import_silently',
				)
			);

			$inputs = wp_parse_args(
				$inputs,
				array(
					'lists'              => array(),
					'tags'               => array(),
					'new_status'         => 'subscribed',
					'double_optin_email' => 'no',
					'import_silently'    => 'yes',
				)
			);

			if ( Arr::get( $inputs, 'import_silently' ) == 'yes' ) {
				if ( ! defined( 'FLUENTCRM_DISABLE_TAG_LIST_EVENTS' ) ) {
					define( 'FLUENTCRM_DISABLE_TAG_LIST_EVENTS', true );
				}
			}

			$sendDoubleOptin = Arr::get( $inputs, 'double_optin_email' ) == 'yes';
			$contactStatus   = Arr::get( $inputs, 'new_status', 'subscribed' );

			$startTime = time();

			$runTime = 5;
			if ( $page == 1 ) {

				if ( ! Commerce::isMigrated( true ) ) {
					Commerce::migrate();
				} else {
					Commerce::resetModuleData( 'surelywp_surecart' );
				}

				fluentcrm_update_option( '_surelywp_sc_customer_sync_page', 1 );
				$runTime = 2;
			}

			$run              = true;
			$last_customer_id = false;
			while ( $run ) {

				$current_page = fluentcrm_get_option( '_surelywp_sc_customer_sync_page', 1 );
				$customers    = Customer::paginate(
					array(
						'page'     => $current_page,
						'per_page' => 10,
					)
				);

				if ( ! empty( $customers ) && ! is_wp_error( $customers ) && $customers->pagination->count ) {

					foreach ( $customers->data as $customer ) {

						$this->sync_customer( $customer, $contactStatus, $inputs['tags'], $inputs['lists'], $sendDoubleOptin );

						$last_customer_id = $customer->id;

						if ( time() - $startTime > $runTime ) {
							Commerce::cacheStoreAverage( 'surelywp_surecart' );
							return $this->get_customer_sync_status( $customers, $last_customer_id );
						}
					}

					fluentcrm_update_option( '_surelywp_sc_customer_sync_page', $current_page + 1 );

				} else {

					$run = false;
				}
			}

			Commerce::cacheStoreAverage( 'surelywp_surecart' );

			return $this->get_customer_sync_status( $customers, $last_customer_id );
		}

		/**
		 * Function to get customer sync status.
		 *
		 * @param object $customers the object of the customers.
		 * @param int|bool $last_customer_id the last customer id.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function get_customer_sync_status( $customers, $last_customer_id = false ) {

			$total          = $customers->pagination->count ?? 0;
			$limit_per_page = $customers->pagination->limit ?? 1;
			$total_page     = ceil( $total / $limit_per_page );

			$completedCount = fluentcrm_get_option( '_surelywp_sc_customer_sync_page', 1 );

			$hasMore = $total_page > $completedCount;

			if ( ! $hasMore ) {
				Commerce::enableModule( 'surelywp_surecart' );
			}

			$result = array(
				'page_total'   => $total,
				'record_total' => $total,
				'has_more'     => $hasMore,
				'current_page' => (int) $completedCount * 10,
				'next_page'    => $completedCount + 1,
				'reload_page'  => ! $hasMore,
				'last_sync_id' => $last_customer_id,
			);

			return $result;
		}

		/**
		 * Syncs a customer with the specified contact status, tags, and lists.
		 *
		 * @param array  $customer       An associative array containing customer details (e.g., name, email).
		 * @param string $contactStatus  The contact status for the customer, default is 'subscribed'.
		 * @param array  $tags           Optional. An array of tags to associate with the customer.
		 * @param array  $lists          Optional. An array of list IDs to associate the customer with.
		 * @param bool   $sendDoubleOptin Optional. Whether to send a double opt-in confirmation email. Default true.
		 * @param bool   $forceSync      Optional. Whether to force synchronization even if the customer is already synced. Default false.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function sync_customer( $customer, $contactStatus = 'subscribed', $tags = array(), $lists = array(), $sendDoubleOptin = true, $forceSync = false ) {

			$user_email  = $customer->email ?? '';
			$customer_id = $customer->id ?? '';
			$user        = get_user_by( 'email', $user_email );

			if ( ! $user ) {
				return false;
			}

			$contactData = \FluentCrm\App\Services\Helper::getWPMapUserInfo( $user );
			$subscriber  = FluentCrmApi( 'contacts' )->getContact( $contactData['email'] );

			if ( $subscriber ) {
				$subscriber->fill( $contactData )->save();
			} else {
				$contactData['status'] = $contactStatus;
				$subscriber            = FluentCrmApi( 'contacts' )->createOrUpdate( $contactData );
			}

			if ( ! $subscriber ) {
				return false;
			}

			if ( $contactStatus == 'pending' && $subscriber->status == 'pending' && $sendDoubleOptin ) {
				$subscriber->sendDoubleOptinEmail();
			}

			if ( $tags ) {
				$subscriber->attachTags( $tags );
			}

			if ( $lists ) {
				$subscriber->attachLists( $lists );
			}

			$relationData = array(
				'subscriber_id' => $subscriber->id,
				'provider'      => 'surelywp_surecart',
				'provider_id'   => $customer_id,
			);

			$contactRelation = ContactRelationModel::updateOrCreate(
				array(
					'subscriber_id' => $subscriber->id,
					'provider'      => 'surelywp_surecart',
				),
				$relationData
			);

			if ( ! $contactRelation ) {
				return false;
			}

			return array(
				'relation'   => $contactRelation,
				'subscriber' => $subscriber,
			);
		}

		/**
		 * Function to add new deep integration.
		 *
		 * @param array $providers the array of the providers.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function add_deep_integration_provider( $providers ) {
			$providers['surelywp_surecart'] = array(
				'title'       => esc_html__( 'Surecart', 'surelywp-toolkit' ),
				'sub_title'   => esc_html__( 'With Surecart deep integration with FluentCRM, you easily segment your purchases and target your customers more efficiently.', 'surelywp-toolkit' ),
				'sync_title'  => esc_html__( 'Surecart customers are not synced with FluentCRM yet.', 'surelywp-toolkit' ),
				'sync_desc'   => esc_html__( 'To sync and enable deep integration with Surecart customers with FluentCRM, please configure and enable sync.', 'surelywp-toolkit' ),
				'sync_button' => esc_html__( 'Sync Surecart Customers', 'surelywp-toolkit' ),
				'settings'    => $this->get_sync_settings(),
			);

			return $providers;
		}

		/**
		 * Function to Get Sync Settings.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function get_sync_settings() {

			$defaults = array(
				'tags'           => array(),
				'lists'          => array(),
				'contact_status' => 'subscribed',
			);

			$settings = fluentcrm_get_option( '_surelywp_sc_sync_settings', array() );

			$settings = wp_parse_args( $settings, $defaults );

			$settings['is_enabled'] = Commerce::isEnabled( 'surelywp_surecart' );

			$settings['tags']  = array_map( 'intval', $settings['tags'] );
			$settings['lists'] = array_map( 'intval', $settings['lists'] );

			return $settings;
		}

		/**
		 * Function to add new filter.
		 *
		 * @param array $groups the array of the group.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function add_advanced_filter_options( $groups ) {

			$groups['surelywp_surecart'] = array(
				'label'    => esc_html__( 'Surecart', 'surelywp-toolkit' ),
				'value'    => 'surecart',
				'children' => array(
					array(
						'value'       => 'purchased_items',
						'label'       => esc_html__( 'Purchased Products', 'surelywp-toolkit' ),
						'type'        => 'selections',
						'component'   => 'product_selector',
						'is_multiple' => true,
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
				),
			);

			return $groups;
		}

		/**
		 * Function to handle filter.
		 *
		 * @param object $query the query for contact.
		 * @param array  $filters the all filters.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function add_advanced_filter( $query, $filters ) {

			foreach ( $filters as $filter ) {
				$query = $this->apply_filter( $query, $filter );
			}

			return $query;
		}

		/**
		 * Function to get contacts by filter.
		 *
		 * @param object $query the query for contact.
		 * @param array  $filter the array of filter.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		private function apply_filter( $query, $filter ) {

			global $surelywp_tk_model;

			$emails   = array();
			$property = Arr::get( $filter, 'property', '' );
			$values   = Arr::get( $filter, 'value', '' );
			$operator = Arr::get( $filter, 'operator', '' );

			if ( '' === $values || ! $property || ! $operator ) {
				return $query;
			}

			$order_filters = array( 'total_order_count', 'total_order_value', 'first_order_date', 'last_order_date', 'used_coupons', 'collections_items' );
			if ( in_array( $property, $order_filters, true ) ) {

				switch ( $property ) {
					case 'total_order_count':
						$emails = $surelywp_tk_model->get_emails_by_order_count( $values, $operator );
						break;
					case 'total_order_value':
						$emails = $surelywp_tk_model->get_emails_by_total_order_value( ( $values * 100 ), $operator );
						break;
					case 'first_order_date':
						$emails = $surelywp_tk_model->get_emails_by_order_date( strtotime( $values ), $operator );
						break;
					case 'last_order_date':
						$emails = $surelywp_tk_model->get_emails_by_order_date( strtotime( $values ), $operator, 'last' );
						break;
					case 'used_coupons':
						$emails = $surelywp_tk_model->get_emails_by_used_coupons( $values, $operator );
						break;
					case 'collections_items':
						$emails = $surelywp_tk_model->get_emails_by_product_collection( $values, $operator );
						break;
				}
			} else {
				$product_ids = array();
				$price_ids   = array();
				$variant_ids = array();

				if ( 'purchased_items' === $property ) {
					$product_ids = $values;
				} else {

					foreach ( $values as $value ) {

						// Split the string by "-surelywp-separate-".
						$parts = explode( '-surelywp-separate-', $value );

						// Ensure you have both parts.
						if ( count( $parts ) === 2 ) {

							$product_ids[] = $parts[0];

							if ( 'purchased_price_items' === $property ) {
								$price_ids[] = $parts[1]; // get price ids.
							} elseif ( 'purchased_variations_items' === $property ) {
								$variant_ids[] = $parts[1]; // get variant ids.

							}
						}
					}
				}

				$emails = $this->get_sc_customer_emails( $product_ids, $price_ids, $variant_ids, $operator, $property );
			}

			// Get Contact by emails.
			$result = $query->whereIn( 'email', $emails );

			return $result;
		}

		/**
		 * Retrieves customer emails based on their purchased products.
		 *
		 * @param array  $product_ids Array of product IDs to filter by.
		 * @param array  $price_ids Array of price IDs to filter by.
		 * @param array  $variant_ids Array of variant IDs to filter by.
		 * @param string $operator    Operator for filtering the product IDs (e.g., 'IN', 'NOT IN').
		 * @param string $property    The property to filter the customers by (e.g., 'email').
		 * @param int    $page        The page number for pagination (default is 1).
		 * @param int    $per_page    The number of results per page (default is 100).
		 * @param array  $results     An array to store the accumulated results (default is an empty array).
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function get_sc_customer_emails( $product_ids, $price_ids, $variant_ids, $operator, $property, $page = 1, $per_page = 100, $results = array() ) {

			$query_product_ids = '';
			if ( 'in' === $operator || 'in_all' === $operator || 'exist' === $operator ) {
				$query_product_ids = $product_ids;
			}

			// Fetch purchases for the given customer and product IDs.
			$purchases = Purchase::where(
				array(
					'product_ids' => $query_product_ids,
				)
			)->with( array( 'customer' ) )->paginate(
				array(
					'page'     => $page,
					'per_page' => $per_page,
				)
			);

			// Check if the purchases retrieval is successful and not empty.
			if ( ! is_wp_error( $purchases ) && ! empty( $purchases ) ) {

				foreach ( $purchases->data as $data ) {
					$results[ $data->customer->email ]['product_ids'][]  = $data->product;
					$results[ $data->customer->email ]['price_ids'][]    = $data->price;
					$results[ $data->customer->email ]['variants_ids'][] = $data->variant;
				}

				// Check for pagination and recursively call the function if there are more pages.
				if ( $purchases->pagination->count ) {

					$total_page = ceil( $purchases->pagination->count / $purchases->pagination->limit );
					if ( $page < $total_page ) {
						return $this->get_sc_customer_emails( $product_ids, $price_ids, $variant_ids, $operator, $property, $page + 1, $per_page, $results );
					}
				}
			}

			$emails = array();

			// For Purchased Products.
			if ( 'purchased_items' === $property ) {

				if ( 'in' === $operator ) {
					$emails = array_keys( $results );
				} elseif ( 'not_in' === $operator ) {
					foreach ( $results as $email => $purchase ) {
						$purchase_product_ids = $purchase['product_ids'];
						if ( ! array_intersect( $product_ids, $purchase_product_ids ) ) {
							$emails[] = $email;
						}
					}
				} elseif ( 'in_all' === $operator ) {
					foreach ( $results as $email => $purchase ) {
						$purchase_product_ids = $purchase['product_ids'];
						if ( empty( array_diff( $product_ids, $purchase_product_ids ) ) && empty( array_diff( $purchase_product_ids, $product_ids ) ) ) {
							$emails[] = $email;
						}
					}
				} elseif ( 'not_in_all' === $operator ) {
					foreach ( $results as $email => $purchase ) {
						$purchase_product_ids = $purchase['product_ids'];
						$diff_ids             = array_diff( $product_ids, $purchase_product_ids );
						if ( count( $diff_ids ) === count( $product_ids ) ) { // All values in array1 are not in array2.
							$emails[] = $email;
						}
					}
				}
			} elseif ( 'purchased_price_items' === $property ) { // For Purchased Product prices.

				if ( 'exist' === $operator ) {
					foreach ( $results as $email => $purchase ) {
						$purchase_price_ids = $purchase['price_ids'];
						if ( array_intersect( $price_ids, $purchase_price_ids ) ) {
							$emails[] = $email;
						}
					}
				} elseif ( 'not_exist' === $operator ) {
					foreach ( $results as $email => $purchase ) {
						$purchase_price_ids = $purchase['price_ids'];
						if ( ! array_intersect( $price_ids, $purchase_price_ids ) ) {
							$emails[] = $email;
						}
					}
				}
			} elseif ( 'purchased_variations_items' === $property ) { // For Purchased Product variant.

				if ( 'exist' === $operator ) {
					foreach ( $results as $email => $purchase ) {
						$purchase_variants_ids = $purchase['variants_ids'];
						if ( array_intersect( $variant_ids, $purchase_variants_ids ) ) {
							$emails[] = $email;
						}
					}
				} elseif ( 'not_exist' === $operator ) {
					foreach ( $results as $email => $purchase ) {
						$purchase_variants_ids = $purchase['variants_ids'];
						if ( ! array_intersect( $variant_ids, $purchase_variants_ids ) ) {
							$emails[] = $email;
						}
					}
				}
			}

			return $emails;
		}
	}
}
