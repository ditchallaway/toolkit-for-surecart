<?php
/**
 * Main class for the SurelyWP Supports plugin.
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
use SureCart\Models\ApiToken;
use SureCart\Models\Order;



if ( ! class_exists( 'Surelywp_Toolkit' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	class Surelywp_Toolkit {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Toolkit
		 */
		protected static $instance;


		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 * @return  \Surelywp_Toolkit
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

			// Check Licence and Reports is enable or not.
			$activation = surelywp_check_license_avtivation( SURELYWP_TOOLKIT_PLUGIN_TITLE, SURELYWP_TOOLKIT_FILE );

			if ( ( isset( $activation['sc_activation_id'] ) && ! empty( $activation ) ) ) {

				// Include files.
				// User switching files.
				require_once 'user-switching/class-surelywp-tk-user-switching.php';
				Surelywp_Tk_User_Switching();

				// User switching files.
				require_once 'misc-settings/class-surelywp-tk-misc.php';
				require_once 'misc-settings/class-surelywp-tk-misc-ajax-handler.php';
				Surelywp_Tk_Misc();
				Surelywp_Tk_Misc_Ajax_Handler();

				// Vacation mode files.
				require_once 'vacation-mode/class-surelywp-tk-vm-admin.php';
				require_once 'vacation-mode/class-surelywp-tk-vm.php';
				Surelywp_Tk_Vm();
				Surelywp_Tk_Vm_Admin();

				require_once 'dashboard-tabs/class-surelywp-tk-dt.php';
				Surelywp_Tk_Dt();

				// Add fluent crm triggers integration file.
				if ( defined( 'FLUENTCRM' ) ) {
					require_once 'fluent-crm/class-surelywp-tk-fluent-crm.php';
					require_once 'fluent-crm/class-surelywp-tk-fc-ajax-handler.php';
					Surelywp_Tk_Fluent_Crm();
					Surelywp_Tk_Fc_Ajax_Handler();
				}

				// Admin Columns Files.
				require_once 'admin-columns/class-surelywp-tk-admin-columns.php';
				require_once 'admin-columns/class-surelywp-tk-ac-ajax-handler.php';
				Surelywp_Tk_Ac_Ajax_Handler();
				Surelywp_Tk_Admin_Columns();

				// Lead Magnets Files.
				require_once 'lead-magnets/class-surelywp-tk-lm.php';
				require_once 'lead-magnets/class-surelywp-tk-lm-ajax-handler.php';
				Surelywp_Tk_Lm();
				Surelywp_Tk_Lm_Ajax_Handler();

				// Import/Export Files.
				require_once 'import-export/class-surelywp-tk-ie.php';
				Surelywp_Tk_Ie();

				// Product visibility.
				require_once 'product-visibility/class-surelywp-tk-pv.php';
				Surelywp_Tk_Pv();
			}

			// Admin Columns Files.
			require_once 'lead-magnets/class-surelywp-tk-lm-admin.php';
			Surelywp_Tk_Lm_Admin();

			// Admin Columns Files.
			require_once 'surecart-checkout-sync/class-surelywp-tk-scs.php';
			Surelywp_Tk_Scs();
		}

		/**
		 * Function to check is services settings enable.
		 *
		 * Display only if the service plugin is active and the service version is 1.3 or higher.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function is_services_us_settings_available() {
			$enable    = false;
			$is_active = is_plugin_active( 'surelywp-services/surelywp-services.php' );
			if ( $is_active ) {
				$desired_sv_version = '1.3';
				$current_sv_version = get_option( 'surelywp_services_db_version' );
				if ( ! empty( $current_sv_version ) ) {
					$is_valid_version = version_compare( $current_sv_version, $desired_sv_version, '>=' );
					if ( $is_valid_version ) {
						$enable = true;
					}
				}
			}
			return $enable;
		}

		/**
		 * Function to check is support settings available.
		 *
		 * Display only if the support portal plugin is active and its version is 1.0.4 or higher.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function is_support_portal_us_settings_available() {
			$enable    = false;
			$is_active = is_plugin_active( 'surelywp-toolkit/surelywp-toolkit.php' );
			if ( $is_active ) {
				$desired_sp_version = '1.0.4';
				$current_sp_version = get_option( 'surelywp_support_portal_db_version' );
				if ( ! empty( $current_sp_version ) ) {
					$is_valid_version = version_compare( $current_sp_version, $desired_sp_version, '>=' );
					if ( $is_valid_version ) {
						$enable = true;
					}
				}
			}
			return $enable;
		}

		/**
		 * Function run for plugin update.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_update_plugin() {

			$plugin_version = get_option( 'surelywp_toolkit_db_version' );

			// Update services settings options for required field.
			if ( version_compare( $plugin_version, '1.2.3', '<' ) ) {
				$this->surelywp_tk_sepate_dashboard_tabs_otpions();
			}

			// Update services settings options for required field.
			if ( version_compare( $plugin_version, '1.3', '<' ) ) {

				if ( ! wp_next_scheduled( 'surelywp_tk_fetch_all_checkouts' ) ) {
					wp_schedule_single_event( time(), 'surelywp_tk_fetch_all_checkouts' ); // runs on plugin active.
				}

				$this->surelywp_tk_update_the_lead_magnets_options();
				$this->surelywp_tk_add_visibility_option_on_dt();
			}

			if ( version_compare( $plugin_version, '1.3.1', '<' ) ) {

				if ( ! wp_next_scheduled( 'surelywp_tk_fetch_all_checkouts' ) ) {
					wp_schedule_single_event( time(), 'surelywp_tk_fetch_all_checkouts' ); // runs on plugin active.
				}
			}

			// Update plugin db version.
			if ( version_compare( $plugin_version, SURELYWP_TOOLKIT_VERSION, '<' ) ) {
				update_option( 'surelywp_toolkit_db_version', SURELYWP_TOOLKIT_VERSION );
			}
		}

		/**
		 * Function to add Tab Visibility Condition hidden for existing user.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function surelywp_tk_add_visibility_option_on_dt() {

			$surelywp_tk_dt_settings_options = get_option( 'surelywp_tk_dt_settings_options' );
			if ( isset( $surelywp_tk_dt_settings_options['sc_ccd_tabs'] ) ) {
				foreach ( $surelywp_tk_dt_settings_options['sc_ccd_tabs'] as $tab => $options ) {
					$surelywp_tk_dt_settings_options['sc_ccd_tabs'][ $tab ]['tab_visibility_condition'] = 'hidden';
				}
				update_option( 'surelywp_tk_dt_settings_options', $surelywp_tk_dt_settings_options );
			}
		}

		/**
		 * Function to Separate the dashboard tab options.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function surelywp_tk_update_the_lead_magnets_options() {

			$surelywp_lm_settings_options = get_option( 'surelywp_lm_settings_options' );
			if ( ! empty( $surelywp_lm_settings_options ) ) {
				$surelywp_lm_settings_options = $surelywp_lm_settings_options['surelywp_lm_settings_options'] ?? '';

				if ( empty( $surelywp_lm_settings_options ) ) {
					return;
				}

				$lm_options = array(
					'tk_lm_update_options'       => '1',
					'lead_magnets_product'       => $surelywp_lm_settings_options['lead_magnets_product'],
					'lm_products'                => $surelywp_lm_settings_options['product'] ?? array(),
					'product_collection'         => $surelywp_lm_settings_options['product_collection'] ?? array(),
					'sub_form_method'            => 'popup_form',
					'popup_btn_text'             => $surelywp_lm_settings_options['email_optin_button_text'] ?? esc_html__( 'Get Your Free Resource!', 'surelywp-toolkit' ),
					'popup_btn_position'         => $surelywp_lm_settings_options['position'] ?? 'description',
					'sub_form_header_text'       => esc_html__( 'Subscribe To Access', 'surelywp-toolkit' ),
					'submit_button_text'         => $surelywp_lm_settings_options['submit_button_text'] ?? esc_html__( 'Submit', 'surelywp-toolkit' ),
					'customer_exists_message'    => $surelywp_lm_settings_options['customer_exists_message'] ?? esc_html__( 'The email address you entered matches an existing order for this resource. Please log in to your customer dashboard to access this resource.', 'surelywp-toolkit' ),
					'sub_form_fields'            => array(
						'email_address'    =>
							array(
								'heading'     => esc_html__( 'Email Address', 'surelywp-toolkit' ),
								'label_value' => esc_html__( 'Email Address', 'surelywp-toolkit' ),
								'position'    => 0,
								'is_show'     => true,
								'is_required' => true,
							),
						'first_name'       =>
							array(
								'heading'     => esc_html__( 'First Name', 'surelywp-toolkit' ),
								'label_value' => esc_html__( 'First Name', 'surelywp-toolkit' ),
								'position'    => 1,
							),
						'last_name'        =>
							array(
								'heading'     => esc_html__( 'Last Name', 'surelywp-toolkit' ),
								'label_value' => esc_html__( 'Last Name', 'surelywp-toolkit' ),
								'position'    => 2,
							),
						'consent_checkbox' =>
							array(
								'heading'             => esc_html__( 'Consent Checkbox', 'surelywp-toolkit' ),
								// translators: %s is replaced with the privacy policy link.
								'label_value'         => sprintf( esc_html__( 'I agree to receive emails and accept the %s', 'surelywp-toolkit' ), '{privacy_policy_link}.' ),
								'privacy_policy_link' => '',
								'position'            => 3,
								'is_required'         => true,
							),
					),
					'email_verification_message' => $surelywp_lm_settings_options['email_verification_message'] ?? esc_html__( 'An email will be sent to the provided address to confirm your subscription and complete your download.', 'surelywp-toolkit' ),
					'verification_email_subject' => $surelywp_lm_settings_options['verification_email_subject'] ?? esc_html__( 'Please confirm your free download!', 'surelywp-toolkit' ),
					'verification_email_body'    => $surelywp_lm_settings_options['email_verification_message'] ?? esc_html__(
						'Dear {customer_name},
You have opted in to receive a free download of the {product_name} from {website_name}. Please click the following link to confirm your subscription and complete your download.

{verification_link}

Enjoy,
The {website_name} Team
)',
						'surelywp-toolkit'
					),
				);

				if ( isset( $surelywp_lm_settings_options['status'] ) ) {
					$lm_options['is_enable_lead_magnets'] = '1';
				}

				if ( isset( $surelywp_lm_settings_options['require_email_verification'] ) ) {
					$lm_options['require_email_verification'] = '1';
				}

				if ( isset( $surelywp_lm_settings_options['collect_name'] ) ) {
					$lm_options['sub_form_fields']['first_name']['is_show'] = true;
					$lm_options['sub_form_fields']['last_name']['is_show']  = true;
				}

				if ( isset( $surelywp_lm_settings_options['require_name'] ) ) {
					$lm_options['sub_form_fields']['first_name']['is_required'] = true;
					$lm_options['sub_form_fields']['last_name']['is_required']  = true;
				}
				update_option( 'surelywp_tk_lm_settings_options', $lm_options );
			}
		}

		/**
		 * Function to Separate the dashboard tab options.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.2.3
		 */
		public function surelywp_tk_sepate_dashboard_tabs_otpions() {

			$misc_options = get_option( 'surelywp_tk_misc_settings_options' );

			$dt_options = array();
			if ( ! empty( $misc_options ) && isset( $misc_options['sc_ccd_tabs'] ) ) {

				if ( isset( $misc_options['enable_ccd_tabs'] ) ) {

					$dt_options['enable_ccd_tabs'] = $misc_options['enable_ccd_tabs'];
				}

				$dt_options['sc_ccd_tabs'] = $misc_options['sc_ccd_tabs'];

				update_option( 'surelywp_tk_dt_settings_options', $dt_options );

				// Remove the options from the misc.
				unset( $misc_options['enable_ccd_tabs'] );
				unset( $misc_options['sc_ccd_tabs'] );
				update_option( 'surelywp_tk_misc_settings_options', $misc_options );
			}
		}


		/**
		 * Function run on plugin active.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_on_plugin_active() {

			// Services Tables.
			Surelywp_Toolkit_Install()->surelwp_tk_init( true );

			if ( ! wp_next_scheduled( 'surelywp_tk_fetch_all_checkouts' ) ) {
				wp_schedule_single_event( time(), 'surelywp_tk_fetch_all_checkouts' ); // runs on plugin active.
			}

			// Update user swching Default options.
			$us_options = get_option( 'surelywp_tk_us_settings_options' );
			if ( ! $us_options ) {
				self::surelywp_tk_us_save_default_options();
			}

			// Update Quick links Default options.
			$ql_options = get_option( 'surelywp_tk_misc_settings_options' );
			if ( ! $ql_options ) {
				self::surelywp_tk_misc_save_default_options();
			}

			// Update Fluent CRM Default options.
			$fc_options = get_option( 'surelywp_tk_fc_settings_options' );
			if ( ! $fc_options ) {
				self::surelywp_tk_fc_save_default_options();
			}

			// Update Lead Magnets options.
			$lm_options = get_option( 'surelywp_tk_lm_settings_options' );
			if ( ! $lm_options ) {
				self::surelywp_tk_lm_save_default_options();
			}

			// For plugin fw header plugin list.
			$surelywp_addons_json = get_option( 'surelywp_addons_json' );
			$search_slug          = SURELYWP_TOOLKIT_SLUG;
			if ( empty( $surelywp_addons_json ) ) {
				surelywp_update_addons_json();
			} else {

				// Extract the plugin slugs into a separate array.
				$plugin_slugs = array_map(
					function ( $plugin ) {
						return $plugin->plugin_slug;
					},
					$surelywp_addons_json
				);

				// Check if the desired plugin slug exists in the array.
				if ( in_array( $search_slug, $plugin_slugs, true ) ) {
					return true;
				} else {
					surelywp_update_addons_json(); // for plugin first installation.
				}
			}
		}


		/**
		 * Function to save user swching default options values.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function surelywp_tk_us_save_default_options() {

			// User Switching Default values.
			$default_options = array(
				'tk_us_update_options'       => 'updated',
				'status'                     => '1',
				'user_role'                  => array( 'administrator', 'sc_shop_manager', 'sc_shop_worker' ),
				'wordpress_user_list_status' => '1',
				'wordpress_user_view_detail' => '1',
				'customer_status'            => '1',
				'customer_detail'            => '1',
				'order_list_status'          => '1',
				'order_status'               => '1',
				'subscriptions_status'       => '1',
				'subscriptions_list_status'  => '1',

			);
			update_option( 'surelywp_tk_us_settings_options', $default_options );
		}


		/**
		 * Function to save Quick links default options values.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function surelywp_tk_misc_save_default_options() {

			// Quick Link Default values.
			$default_options = array(
				'tk_misc_update_options'       => 'updated',
				'surecart_menu'                => 1,
				'surecart_app_link'            => 1,
				'logout_title'                 => esc_html__( 'Customer Login', 'surelywp-toolkit' ),
				'enable_rename_name'           => 0,
				'is_enable_login_redirection'  => 0,
				'login_redirect_url'           => '',
				'is_enable_logout_redirection' => 0,
				'logout_redirect_url'          => '',
			);
			update_option( 'surelywp_tk_misc_settings_options', $default_options );
		}

		/**
		 * Sets the webhook events for SureCart based on the provided status.
		 *
		 * This method updates the registered webhook events by merging the default
		 * events from SureCart with the additional events from the Fluent CRM
		 * integration if the provided status is true. If the status is false,
		 * only the default events are used.
		 *
		 * @param bool $status A boolean value indicating whether to include additional
		 *                     events from the Fluent CRM integration. If true,
		 *                     the events from Fluent CRM are merged with the
		 *                     default events. If false, only the default events
		 *                     are used.
		 *
		 * @return void This method does not return a value. It updates the registered
		 *               webhook events directly.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public static function surelywp_tk_set_sc_events( $status ) {

			$default_events = \SureCart::config()->webhook_events;
			if ( $status ) {
				$sc_events = self::surelywp_tk_fc_get_sc_events();
				$events    = array_merge( $default_events, $sc_events );
			} else {
				$events = $default_events;
			}
			$webhook_events = array(
				'webhook_events' => $events,
			);
			RegisteredWebhook::update( $webhook_events );
		}


		/**
		 * Function to save Quick links default options values.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function surelywp_tk_fc_save_default_options() {

			// Quick Link Default values.
			$default_options = array(
				'tk_fc_update_options' => 'updated',
				'status'               => 1,
			);
			update_option( 'surelywp_tk_fc_settings_options', $default_options );

			// update the surecart events enable triggers.
			self::surelywp_tk_set_sc_events( 1 );
		}


		/**
		 * Function to save Lead Magnets default options values.
		 *
		 * @package Toolkit For SureCart
		 * @since   2.1
		 */
		public static function surelywp_tk_lm_save_default_options() {

			// Quick Link Default values.
			$default_options = array(
				'tk_lm_update_options'       => 'updated',
				'lead_magnets_product'       => 'all',
				'sub_form_method'            => 'popup_form',
				'popup_btn_text'             => esc_html__( 'Get Your Free Resource!', 'surelywp-toolkit' ),
				'popup_btn_position'         => 'description',
				'sub_form_header_text'       => esc_html__( 'Subscribe To Access', 'surelywp-toolkit' ),
				'submit_button_text'         => esc_html__( 'Submit', 'surelywp-toolkit' ),
				'customer_exists_message'    => esc_html__( 'The email address you entered matches an existing order for this resource. Please log in to your customer dashboard to access this resource.', 'surelywp-toolkit' ),
				'sub_form_fields'            => Surelywp_Tk_Lm_Admin::surelywp_tk_get_default_form_fields(),
				'email_verification_message' => esc_html__( 'An email will be sent to the provided address to confirm your subscription and complete your download.', 'surelywp-toolkit' ),
				'verification_email_subject' => esc_html__( 'Please confirm your free download!', 'surelywp-toolkit' ),
				'verification_email_body'    => wp_kses_post(
					'Dear {customer_name},
You have opted in to receive a free download of the {product_name} from {website_name}. Please click the following link to confirm your subscription and complete your download.

{verification_link}

Enjoy,
The {website_name} Team',
					'surelywp-toolkit'
				),
				'tab_name'                   => esc_html__( 'Lead Magnets', 'surelywp-toolkit' ),
				'tab_icon'                   => 'gift',
				'lm_singular_name'           => esc_html__( 'Lead Magnet', 'surelywp-toolkit' ),
				'lm_plural_name'             => esc_html__( 'Lead Magnets', 'surelywp-toolkit' ),
			);
			update_option( 'surelywp_tk_lm_settings_options', $default_options );
		}

		/**
		 * Function to get fluent crm require events.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function surelywp_tk_fc_get_sc_events() {

			$events = array(
				// Order Events.
				'order.created',
				'order.delivered',
				'order.fulfilled',
				'order.made_processing',
				'order.paid',
				'order.partially_fulfilled',
				'order.partially_shipped',
				'order.payment_failed',
				'order.shipped',
				'order.unfulfilled',
				'order.unshipped',
				'order.voided',

				// Subscription Events.
				'subscription.canceled',
				'subscription.created',
				'subscription.completed',
				'subscription.made_active',
				'subscription.made_trialing',
				'subscription.renewed',
				'subscription.set_to_cancel',
				'subscription.updated',

				// Refund Triggers.
				'refund.created',
				'refund.succeeded',
			);

			return $events;
		}
	}

	/**
	 * Unique access to instance of Surelywp_Toolkit class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function Surelywp_Toolkit() {  // phpcs:ignore
		$instance = Surelywp_Toolkit::get_instance();
		return $instance;
	}
}
