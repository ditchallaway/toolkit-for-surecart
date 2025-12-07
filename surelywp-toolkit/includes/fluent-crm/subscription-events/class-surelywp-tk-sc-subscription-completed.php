<?php
/**
 * Surecart subscription completed.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.0
 */

namespace SurelywpToolkit\Includes\FluentCrm;

// Exit if accessed directly.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}


use FluentCrm\App\Services\Funnel\BaseTrigger;
use FluentCrm\App\Services\Funnel\FunnelHelper;
use FluentCrm\Framework\Support\Arr;
use FluentCrm\App\Services\Funnel\FunnelProcessor;

use SureCart\Models\Checkout;
use SureCart\Models\Purchase;


if ( ! class_exists( 'Surelywp_Tk_Sc_Subscription_Completed' ) ) {

	/**
	 * Main class for trigger.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	class Surelywp_Tk_Sc_Subscription_Completed extends BaseTrigger {

		/**
		 * Constructor function.
		 *
		 * Initializes the class.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function __construct() {

			// The triggerName property is the name of the event that will trigger this workflow.
			$this->triggerName = 'surecart/subscription_completed';

			// The priority property is the priority of the action that will be added to the add_action function.
			$this->priority = 11;

			// The actionArgNum property is the number of arguments that will be passed to the callback.
			$this->actionArgNum = 1;

			parent::__construct();
		}


		/**
		 * This method should return an array of the trigger settings.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function getTrigger() {
			return array(
				'category'    => esc_html__( 'SureCart', 'surelywp-toolkit' ),
				'label'       => esc_html__( 'SureCart - Subscription Completed', 'surelywp-toolkit' ),
				'description' => esc_html__( 'Occurs when a subscription\'s status changes to completed', 'surelywp-toolkit' ),
				'icon'        => 'fc-icon-surelywp-logo',
				'ribbon'      => esc_html__( 'SurelyWP', 'surelywp-toolkit' ),
			);
		}

		/**
		 * This method should return an array of the default settings for the workflow.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function getFunnelSettingsDefaults() {
			return array(
				'subscription_status' => 'subscribed',
			);
		}

		/**
		 * This method should return an array of the settings fields that will be displayed in the workflow settings page.
		 *
		 * @param object $funnel The funnel object.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function getSettingsFields( $funnel ) {

			return array(
				'title'     => esc_html__( 'SureCart - Subscription Completed', 'surelywp-toolkit' ),
				'sub_title' => esc_html__( 'Occurs when a subscription\'s status changes to completed', 'surelywp-toolkit' ),
				'fields'    => array(
					'subscription_status' => array(
						'type'        => 'option_selectors',
						'option_key'  => 'editable_statuses',
						'is_multiple' => false,
						'label'       => esc_html__( 'Subscription Status', 'surelywp-toolkit' ),
						'placeholder' => esc_html__( 'Select Status', 'surelywp-toolkit' ),
					),
				),
			);
		}

		/**
		 * Get the conditional fields for the funnel.
		 *
		 * @param object $funnel The funnel object.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function getConditionFields( $funnel ) {

			$products    = array();
			$sc_products = surelywp_tk_get_sc_all_products();
			if ( $sc_products ) {
				foreach ( $sc_products as $product ) {
					$products[] = array(
						'id'    => $product->id,
						'title' => $product->name,
					);
				}
			}

			$conditions = array(
				'update_type'  => array(
					'type'    => 'radio',
					'label'   => esc_html__( 'If Contact Already Exist?', 'surelywp-toolkit' ),
					'help'    => esc_html__( 'Please specify what will happen if the subscriber already exist in the database', 'surelywp-toolkit' ),
					'options' => FunnelHelper::getUpdateOptions(),
				),
				'product_ids'  => array(
					'type'        => 'multi-select',
					'label'       => esc_html__( 'Target Products', 'surelywp-toolkit' ),
					'help'        => esc_html__( 'Select for which products this automation will run', 'surelywp-toolkit' ),
					'options'     => $products,
					'inline_help' => esc_html__( 'Keep it blank to run to any product purchase', 'surelywp-toolkit' ),
				),
				'run_multiple' => array(
					'type'        => 'yes_no_check',
					'label'       => '',
					'check_label' => esc_html__( 'Restart the Automation Multiple times for a contact for this event. (Only enable if you want to restart automation for the same contact)', 'surelywp-toolkit' ),
					'inline_help' => esc_html__( 'If you enable, then it will restart the automation for a contact if the contact already in the automation. Otherwise, It will just skip if already exist', 'surelywp-toolkit' ),
				),
			);

			return $conditions;
		}

		/**
		 * Get the conditional fields default values.
		 *
		 * @param object $funnel The funnel object.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function getFunnelConditionDefaults( $funnel ) {
			return array(
				'update_type'  => 'update', // skip_all_actions, skip_update_if_exist.
				'product_ids'  => array(),
				'run_multiple' => 'no',
			);
		}


		/**
		 * The handle method needs to be defined in order for it to be called when the trigger event occurs.
		 *
		 * @param object $funnel The funnel object.
		 * @param object $original_args The array of the arguments that are passed to the callback.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function handle( $funnel, $original_args ) {

			// separate the arguments.
			$subscription_obj = $original_args[0];
			$purchase_id      = $subscription_obj->purchase;
			$purchase_obj     = Purchase::find( $purchase_id );

			$customer_id = $subscription_obj->customer ?? '';
			$user_id     = surelywp_tk_get_customer_user_id( $customer_id );

			// get the funnel settings and conditions.
			$settings   = $funnel->settings;
			$conditions = $funnel->conditions;

			// prepare the subscriber data.
			$subscriber_data = array(
				'email'      => '', // required.
				'first_name' => '',
				'last_name'  => '',
				'status'     => $settings['subscription_status'],
			);

			// you may use the helper function to prepare the subscriber data.
			$subscriber_data = FunnelHelper::prepareUserData( $user_id );

			// check if this funnel is able to process this order and run the automation.
			if ( ! $this->isProcessable( $funnel, $purchase_obj, $subscriber_data ) ) {
				return false;
			}

			// finally start funnel sequence for this subscriber.
			( new FunnelProcessor() )->startFunnelSequence(
				$funnel,
				$subscriber_data,
				array(
					'source_trigger_name' => $this->triggerName,
					'notes'               => wp_json_encode( $subscription_obj ),
				)
			);
		}

		/**
		 * Check if this funnel is able to process this order and run the automation.
		 *
		 * @param object $funnel The funnel object.
		 * @param object $purchase_obj The object of the arguments that are passed to the callback.
		 * @param object $subscriber_data The subsscriber data.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		private function isProcessable( $funnel, $purchase_obj, $subscriber_data ) {

			// get conditions.
			$conditions = $funnel->conditions;

			// check update_type.
			$update_type = Arr::get( $conditions, 'update_type' );

			if ( ! isset( $subscriber_data['email'] ) ) {
				return false;
			}

			$subscriber = FunnelHelper::getSubscriber( $subscriber_data['email'] );

			if ( $subscriber && 'skip_all_if_exist' === $update_type ) {
				return false;
			}

			// Get the product id.
			$product_id = $purchase_obj->product ?? array();

			// check the products ids.
			if ( $conditions['product_ids'] && ! in_array( $product_id, $conditions['product_ids'], true ) ) {
				return false;
			}

			// check run_only_one.
			if ( $subscriber && FunnelHelper::ifAlreadyInFunnel( $funnel->id, $subscriber->id ) ) {
				$multipleRun = Arr::get( $conditions, 'run_multiple' ) == 'yes';
				if ( $multipleRun ) {
					FunnelHelper::removeSubscribersFromFunnel( $funnel->id, array( $subscriber->id ) );
				} else {
					return false;
				}
			}
			return true;
		}
	}
}
