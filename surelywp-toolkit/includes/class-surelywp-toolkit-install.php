<?php
/**
 * Install file
 *
 * @author Surlywp
 * @package Toolkit For SureCart
 * @since 1.3
 */

if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'Surelywp_Toolkit_Install' ) ) {

	/**
	 * Install plugin table and create the wishlist page
	 */
	class Surelywp_Toolkit_Install {

		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Toolkit_Install
		 */
		protected static $instance;

		/**
		 * Discount table name
		 *
		 * @var string
		 * @access private
		 */
		private $table_name;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function __construct() {

			global $wpdb;

			// define local private attribute.
			$this->table_name = $wpdb->prefix . 'surelywp_surecart_checkouts';

			// add custom field to global $wpdb.
			$wpdb->surelywp_surecart_checkouts = $this->table_name;
		}

		/**
		 * Init db structure of the plugin.
		 *
		 * @param bool $flag the flag manage for check table creation.
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function surelwp_tk_init( $flag = false ) {
			if ( $this->needs_db_update() || $flag ) {
				$this->surelywp_tk_add_tables();
			}
		}

		/**
		 * The DB needs to be updated?
		 *
		 * @return bool
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function needs_db_update() {

			$current_db_version = get_option( 'surelywp_toolkit_db_version', null );

			if ( is_null( $current_db_version ) ) {
				return true;
			} elseif ( version_compare( $current_db_version, SURELYWP_TOOLKIT_VERSION, '<' ) ) {
				return true;
			} else {
				return false;
			}
		}


		/**
		 * Check if the table of the plugin already exists.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function is_installed() {
			global $wpdb;
			$number_of_tables = $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$this->table_name}%" ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return (bool) ( 1 === (int) $number_of_tables );
		}

		/**
		 * Add tables for a fresh installation
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		private function surelywp_tk_add_tables() {
			$this->surelywp_tk_add_surecart_checkouts_table();
		}

		/**
		 * Add the wishlists table to the database.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		private function surelywp_tk_add_surecart_checkouts_table() {
			if ( ! $this->is_installed() ) {
				$sql = "CREATE TABLE {$this->table_name} (
							checkout_id VARCHAR(50) NOT NULL,
							amount_due INT,
							applied_balance_amount INT,
							credited_balance_amount INT,
							currency VARCHAR(10),
							discount_amount INT,
							email VARCHAR(100),
							first_name VARCHAR(100),
							full_amount INT,
							last_name VARCHAR(100),
							live_mode BOOLEAN,
							manual_payment BOOLEAN,
							metadata TEXT,
							name VARCHAR(100),
							net_paid_amount INT,
							paid_amount INT,
							g_weight INT,
							paid_at INT,
							phone VARCHAR(50),
							proration_amount INT,
							refunded_amount INT,
							remaining_amount_due INT,
							shipping_amount INT,
							shipping_tax_amount INT,
							shipping_tax_rate FLOAT,
							status VARCHAR(50),
							subtotal_amount INT,
							tax_amount INT,
							total_amount INT,
							total_savings_amount INT,
							trial_amount INT,
							current_payment_intent VARCHAR(100),
							current_upsell TEXT,
							invoice TEXT,
							customer VARCHAR(100),
							purchased_product_ids TEXT,
							discount TEXT,
							promotion_id VARCHAR(100),
							order_id VARCHAR(100),
							payment_method VARCHAR(100),
							referral TEXT,
							shipping_address VARCHAR(100),
							tax_identifier TEXT,
							upsell_funnel TEXT,
							bump_amount INT,
							number VARCHAR(50),
							pdf_url TEXT,
							payment_intent VARCHAR(100),
							charge VARCHAR(100),
							checkout_data longtext NOT NULL,
							created_at INT,
							updated_at INT,
							PRIMARY KEY (checkout_id)
						) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );
			}
		}
	}
}

/**
 * Unique access to instance of Surelywp_Toolkit_Install class.
 *
 * @package Toolkit For SureCart
 * @since 1.3
 */
function Surelywp_Toolkit_Install() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return Surelywp_Toolkit_Install::get_instance();
}
