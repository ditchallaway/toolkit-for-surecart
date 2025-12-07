<?php
/**
 * Main class for Toolkit Admin Columns.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.2
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\Product;

if ( ! class_exists( 'Surelywp_Tk_Admin_Columns' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2
	 */
	class Surelywp_Tk_Admin_Columns {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Admin_Columns
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2
		 * @return  \Surelywp_Tk_Admin_Columns
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

			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_ac_enqueue_scripts' ) );

			// add columnn in surecart admin order list table.
			add_action( 'admin_init', array( $this, 'surelywp_tk_ac_admin_init' ), 9 );

			add_action( 'update_option_surelywp_tk_ac_settings_options', array( $this, 'surelywp_tk_on_ac_option_update' ), 10, 2 );
		}

		/**
		 * On admin column option update.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function surelywp_tk_on_ac_option_update() {
			delete_transient( 'surelywp_tk_ac_order_product_name_sub_type' );
			delete_transient( 'surelywp_tk_ac_recovery_status' );
		}

		/**
		 * Add column on admin order list table.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.2
		 */
		public function surelywp_tk_ac_admin_init() {

			$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			// Add column to surecart admin order list table.
			if ( 'sc-orders' === $page ) {

				$order_column_status = self::get_settings_option( 'order_column_status' );

				if ( $order_column_status ) {

					// filter to register a new column .
					add_filter(
						'manage_' . $page . '_columns',
						function ( $columns ) {

							$admin_columns = self::get_settings_option( 'admin_columns' );

							if ( ! empty( $admin_columns ) ) {

								foreach ( $admin_columns['order'] as $key => $values ) {

									if ( ! $values['is_default_column'] && isset( $values['is_show'] ) ) {
										$columns[ $key ] = esc_html( $values['label'] );
									}
								}
							}
							return $columns;
						}
					);

					add_action(
						'manage_' . $page . '_custom_column',
						function ( $column_name, $data ) {

							if ( 'order_product_name' === $column_name ) { // Product Name Column.

								echo '<div class="surelywp-order-product-names" data-checkout-id="' . esc_html( $data->checkout->id ?? '' ) . '"><sc-skeleton class="surelywp-row-skeleton"></sc-skeleton><div>';

							} elseif ( 'subscription_type' === $column_name ) { // Subscription Type Column.

								if ( ( $data->order_type ?? '' ) === 'subscription' ) {

									$subscription_id = $data->checkout->purchases->data[0]->subscription ?? '';
									echo '<div class="surelywp-subscription-type" data-checkout-id="' . esc_html( $data->checkout->id ) . '"><sc-skeleton class="surelywp-row-skeleton"></sc-skeleton><div>';

								} else {
									echo '-';
								}
							} elseif ( 'trial' === $column_name ) { // Trial Column.

								$is_trail_order = $data->checkout->trial_amount ?? false;
								if ( $is_trail_order ) {
									echo '<sc-tag aria-label="Plan Status - Trialing" type="info" size="medium" class="hydrated">' . esc_html__( 'Trialing', 'surelywp-toolkit' ) . '</sc-tag>';
								} else {
									echo '-';
								}
							} elseif ( 'recovery_status' === $column_name ) {
								echo '<div class="surelywp-recovery-status" data-checkout-id="' . esc_html( $data->checkout->id ?? '' ) . '" data-customer-id="' . esc_html( $data->checkout->customer->id ?? '' ) . '"><sc-skeleton class="surelywp-row-skeleton"></sc-skeleton><div>';
							} elseif ( 'invoice' === $column_name ) {
								echo '<sc-button style="font-size: 11px;" type="primary" href="' . esc_url( $data->pdf_url ) . '" target="_blank">' . esc_html__( 'Download', 'surelywp-toolkit' ) . '</sc-button>';
							} elseif ( 'lead-magnet' === $column_name ) {
								$is_lm_order = $data->checkout->metadata->surelywp_lead_magnet_order ?? '';
								if ( $is_lm_order ) {
									echo '<sc-tag type="success" size="medium" class="hydrated">' . esc_html__( 'Lead Magnet', 'surelywp-toolkit' ) . '</sc-tag>';
								} else {
									echo '-';
								}
							}
						},
						10,
						2
					);
				}
			}
		}

		/**
		 * Function to Enqueue Scripts.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2
		 */
		public static function surelywp_tk_ac_enqueue_scripts() {

			$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$tab  = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

			// fluent crm js.
			wp_register_script( 'surelywp-tk-ac', SURELYWP_TOOLKIT_ASSETS_URL . '/js/admin-columns/surelywp-tk-ac.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-ac-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/admin-columns/surelywp-tk-ac.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$min_file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
			$allow_page = array( 'sc-products', 'sc-orders' );

			if ( 'surelywp_tk_ac_settings' === $tab || in_array( $page, $allow_page, true ) ) {

				if ( 'surelywp_tk_ac_settings' === $tab ) {

					wp_enqueue_script( 'jquery-ui-core' );
					wp_enqueue_script( 'jquery-ui-sortable' );
				}

				$order_column_status   = self::get_settings_option( 'order_column_status' );
				$product_column_status = self::get_settings_option( 'product_column_status' );

				$admin_columns = self::get_settings_option( 'admin_columns' );

				$localize = array(
					'order_column_status'   => $order_column_status,
					'product_column_status' => $product_column_status,
					'admin_columns'         => $admin_columns,
					'admin_ajax_nonce'      => wp_create_nonce( 'admin-ajax-nonce' ),
					'ajax_url'              => admin_url( 'admin-ajax.php' ),
				);

				wp_enqueue_script( 'surelywp-tk-ac' . $min_file );
				wp_localize_script( 'surelywp-tk-ac' . $min_file, 'tk_ac_backend_ajax_object', $localize );
			}
		}

		/**
		 * Function to Get option by Name.
		 *
		 * @param string $option_name The option name of setting.
		 * @package Toolkit For SureCart
		 * @since   1.2
		 */
		public static function get_settings_option( $option_name ) {

			$options = get_option( 'surelywp_tk_ac_settings_options' );
			if ( isset( $options[ $option_name ] ) ) {
				$option_value = $options[ $option_name ];
			} else {
				$option_value = '';
			}

			return $option_value;
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Admin_Columns class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2
	 */
	function Surelywp_Tk_Admin_Columns() {  // phpcs:ignore
		$instance = Surelywp_Tk_Admin_Columns::get_instance();
		return $instance;
	}
}
