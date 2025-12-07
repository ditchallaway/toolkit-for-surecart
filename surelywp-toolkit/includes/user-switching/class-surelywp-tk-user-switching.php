<?php
/**
 * Main class for Toolkit User Switching.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.0
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

if ( ! defined( 'SURELYWP_TK_USER_SWITCHING_COOKIE' ) ) {
	define( 'SURELYWP_TK_USER_SWITCHING_COOKIE', 'wordpress_user_surelywp_' . COOKIEHASH );
}

if ( ! defined( 'SURELYWP_TK_USER_SWITCHING_SECURE_COOKIE' ) ) {
	define( 'SURELYWP_TK_USER_SWITCHING_SECURE_COOKIE', 'wordpress_user_surelywp_secure_' . COOKIEHASH );
}

if ( ! defined( 'SURELYWP_TK_USER_SWITCHING_OLD_USER_COOKIE' ) ) {
	define( 'SURELYWP_TK_USER_SWITCHING_OLD_USER_COOKIE', 'wordpress_user_surelywp_old_user_' . COOKIEHASH );
}

if ( ! defined( 'SURELYWP_TK_USER_REDIRECT_COOKIE' ) ) {
	define( 'SURELYWP_TK_USER_REDIRECT_COOKIE', 'wordpress_user_surelywp_redirect_' . COOKIEHASH );
}

if ( ! class_exists( 'Surelywp_Tk_User_Switching' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	class Surelywp_Tk_User_Switching {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_User_Switching
		 */
		protected static $instance;


		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 * @return  \Surelywp_Tk_User_Switching
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

			$is_enable_us         = self::get_settings_option( 'status' );
			$tk_us_update_options = self::get_settings_option( 'tk_us_update_options' );

			if ( ( $is_enable_us || empty( $tk_us_update_options ) ) ) {

				// Enqueue sctipt and styles.
				add_action( 'wp_enqueue_scripts', array( $this, 'surelywp_tk_us_scripts' ) );
				add_action( 'init', array( $this, 'surelywp_tk_us_action_init' ) );
				add_action( 'wp_footer', array( $this, 'surelywp_tk_us_action_wp_footer' ) );
				add_action( 'admin_init', array( $this, 'surelywp_tk_us_user_action' ) );

				// add user swiching button on service list.
				add_filter( 'surelywp_sv_admin_list_column_user_name', array( $this, 'surelywp_tk_us_add_btn_on_services_list' ), 10, 3 );

				// add user swiching button on service list.
				add_filter( 'surelywp_sp_admin_list_column_user_name', array( $this, 'surelywp_tk_us_add_btn_on_support_tickets_list' ), 10, 3 );

				// add user swiching button on inquiries list.
				add_filter( 'surelywp_cm_admin_list_column_user_name', array( $this, 'surelywp_tk_us_add_btn_on_inquiries_list' ), 10, 3 );

				add_filter( 'user_row_actions', array( $this, 'surelywp_tk_us_add_switch_user_link' ), 10, 2 );

				add_action( 'edit_user_profile', array( $this, 'surelywp_tk_us_add_switch_user_view_detail' ), 10, 2 );

				add_action( 'admin_init', array( $this, 'surelywp_tk_us_add_on_order_subscription_list' ), 9 );
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_us_scripts' ) );
		}

		/**
		 * Function to add user switching link on WordPress user list.
		 *
		 * @param array  $actions the row items.
		 * @param object $user_object The customer object.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_us_add_switch_user_link( $actions, $user_object ) {

			$url = $this->surelywp_tk_us_user_switch_url( $user_object->ID, 'user_list' );
			if ( ! $url ) {
				return $actions;
			}

			$actions['switch_user'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Switch To User', 'surelywp-toolkit' ) . '</a>';
			return $actions;
		}

		/**
		 * Function to add user switching link on WordPress user edit page.
		 *
		 * @param object $user_object The customer object.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_us_add_switch_user_view_detail( $user_object ) {
			$url = $this->surelywp_tk_us_user_switch_url( $user_object->ID, 'user_view' );
			if ( ! $url ) {
				return;
			}
			ob_start();
			?>
			<table class="form-table">
				<tr class="show-admin-bar user-admin-bar-front-wrap">
					<th scope="row"><?php echo esc_html__( 'Switch User', 'surelywp-toolkit' ); ?></th>
					<td>
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html__( 'Switch To This User', 'surelywp-toolkit' ); ?></a><br>
					</td>
				</tr>
			</table>
			<?php
			echo ob_get_clean();
		}

		/**
		 * Function to get user switching link.
		 *
		 * @param int    $user_id the User ID.
		 * @param string $page the page name.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_us_user_switch_url( $user_id, $page ) {

			if ( ! current_user_can( 'edit_users' ) || get_current_user_id() === $user_id ) {
				return false;
			}

			$admin_url = '';
			$args      = array();
			if ( 'user_view' === $page ) {
				$is_enable = self::get_settings_option( 'wordpress_user_view_detail' );
				$admin_url = admin_url( 'user-edit.php' );
				$args      = array(
					'type'    => 'user_view',
					'user_id' => $user_id,
				);
			} elseif ( 'user_list' === $page ) {
				$is_enable = self::get_settings_option( 'wordpress_user_list_status' );
				$admin_url = admin_url( 'users.php' );
				$args      = array( 'type' => 'user_list' );
			}

			if ( ! $is_enable ) {
				return;
			}

			$user_sc_customer_meta = get_user_meta( $user_id, 'sc_customer_ids' );
			if ( empty( $user_sc_customer_meta ) ) {
				return false;
			}

			$customer_id = $user_sc_customer_meta[0]['live'] ?? '';
			if ( ! $customer_id ) {
				$customer_id = $user_sc_customer_meta[0]['test'] ?? '';
			}
			if ( empty( $customer_id ) ) {
				return false;
			}

			$args = array_merge(
				$args,
				array(
					'customerID' => $customer_id,

				)
			);
			$switch_url = add_query_arg( $args, $admin_url );

			return $switch_url;
		}

		/**
		 * Fuction to add user swtich link on surecart customer order list.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.2
		 */
		public function surelywp_tk_us_add_on_order_subscription_list() {

			$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			// Add column to surecart admin order list table.
			if ( 'sc-orders' === $page || 'sc-subscriptions' === $page ) {

				if ( 'sc-orders' === $page ) {
					$order_list_status = self::get_settings_option( 'order_list_status' );
					if ( ! $order_list_status ) {
						return;
					}
				} elseif ( 'sc-subscriptions' === $page ) {
					$subscriptions_list_status = self::get_settings_option( 'subscriptions_list_status' );
					if ( ! $subscriptions_list_status ) {
						return;
					}
				}

				add_filter(
					'manage_' . $page . '_columns',
					function ( $columns ) {
						$columns['customer_id'] = '';
						return $columns;
					}
				);

				add_action(
					'manage_' . $page . '_custom_column',
					function ( $column_name, $data ) use ( $page ) {
						$customer_id = '';
						if ( 'sc-orders' === $page ) {
							$customer_id = $data->checkout->customer->id ?? '';
						} elseif ( 'sc-subscriptions' === $page ) {
							$customer_id = $data->customer->id ?? '';
						}
						if ( $customer_id ) {
							echo '<div class="surelywp-order-customer-id" data-customer-id="' . $customer_id . '"></div>'; //phpcs:ignore.
						}
					},
					10,
					2
				);
			}
		}

		/**
		 * Function to add user switching btn on inquiry list.
		 *
		 * @param string $html the column html.
		 * @param array  $item the row items.
		 * @param object $customer The customer object.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_us_add_btn_on_inquiries_list( $html, $item, $customer ) {

			$btn_html   = '';
			$is_show_on = self::get_settings_option( 'inquiries_list' );
			if ( $is_show_on && ! empty( $html ) && ! is_wp_error( $customer ) && ! empty( $customer ) ) {
				$customer_id = $customer->id ?? $customer[0]->id;
				if ( ! empty( $customer_id ) ) {
					$btn_html = '<div class="row-actions"><span class="view"><a href="' . surelywp_tk_get_current_url() . '&customerID=' . $customer_id . '">' . esc_html__( 'Switch To User', 'surelywp-toolkit' ) . '</span></div>';
				}
			}

			return $html . $btn_html;
		}

		/**
		 * Function to add user switching btn on services list.
		 *
		 * Display only if the service plugin is active and the service version is 1.3 or higher.
		 *
		 * @param string $html the column html.
		 * @param array  $item the row items.
		 * @param object $customer The customer object.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_us_add_btn_on_services_list( $html, $item, $customer ) {

			$btn_html                       = '';
			$is_services_settings_available = Surelywp_Toolkit::is_services_us_settings_available();
			$is_show_on                     = self::get_settings_option( 'services_list' );
			if ( $is_show_on && $is_services_settings_available && ! empty( $html ) && ! is_wp_error( $customer ) && ! empty( $customer ) ) {
				$customer_id = $customer->id ?? $customer[0]->id;
				if ( ! empty( $customer_id ) ) {
					$btn_html = '<div class="row-actions"><span class="view"><a href="' . surelywp_tk_get_current_url() . '&customerID=' . $customer_id . '">' . esc_html__( 'Switch To User', 'surelywp-toolkit' ) . '</span></div>';
				}
			}

			return $html . $btn_html;
		}

		/**
		 * Function to add user switching btn on services list.
		 *
		 * Display only if the support portal plugin is active and its version is 1.0.4 or higher.
		 *
		 * @param string $html the column html.
		 * @param array  $item the row items.
		 * @param object $customer The customer object.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_us_add_btn_on_support_tickets_list( $html, $item, $customer ) {

			$btn_html                             = '';
			$is_support_portal_settings_available = Surelywp_Toolkit::is_support_portal_us_settings_available();
			$is_show_on                           = self::get_settings_option( 'support_tickets_list' );
			if ( $is_show_on && $is_support_portal_settings_available && ! empty( $html ) && ! is_wp_error( $customer ) && ! empty( $customer ) ) {
				$customer_id = $customer->id ?? $customer[0]->id;
				if ( ! empty( $customer_id ) ) {
					$btn_html = '<div class="row-actions"><span class="view"><a href="' . surelywp_tk_get_current_url() . '&customerID=' . $customer_id . '">' . esc_html__( 'Switch To User', 'surelywp-toolkit' ) . '</span></div>';
				}
			}

			return $html . $btn_html;
		}

		/**
		 * Function to enqueue scripts and styles for the front end.
		 *
		 * @param string $hook the page name.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_us_scripts( $hook ) {

			// register common style.
			wp_register_style( 'surelywp-tk-us', SURELYWP_TOOLKIT_ASSETS_URL . '/css/user-switching/surelywp-tk-us.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_style( 'surelywp-tk-us-min', SURELYWP_TOOLKIT_ASSETS_URL . '/css/user-switching/surelywp-tk-us.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );

			wp_register_script( 'surelywp-tk-us', SURELYWP_TOOLKIT_ASSETS_URL . '/js/user-switching/surelywp-tk-us.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-us-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/user-switching/surelywp-tk-us.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$tk_us_update_options      = self::get_settings_option( 'tk_us_update_options' );
			$customer_status           = ! empty( self::get_settings_option( 'customer_status' ) ) ? true : false;
			$customer_detail           = ! empty( self::get_settings_option( 'customer_detail' ) ) ? true : false;
			$order_status              = ! empty( self::get_settings_option( 'order_status' ) ) ? true : false;
			$order_list_status         = ! empty( self::get_settings_option( 'order_list_status' ) ) ? true : false;
			$subscriptions_status      = ! empty( self::get_settings_option( 'subscriptions_status' ) ) ? true : false;
			$subscriptions_list_status = ! empty( self::get_settings_option( 'subscriptions_list_status' ) ) ? true : false;
			$service_view              = ! empty( self::get_settings_option( 'service_view' ) ) ? true : false;
			$support_ticket_view       = ! empty( self::get_settings_option( 'support_ticket_view' ) ) ? true : false;
			$inquiry_view              = ! empty( self::get_settings_option( 'inquiry_view' ) ) ? true : false;

			$enable_user_switch = false;
			$action             = isset( $_REQUEST['action'] ) && ! empty( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
			$tab                = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

			if ( 'surecart_page_sc-customers' === $hook ) {
				if ( $action == 'edit' && $customer_detail ) {
					$enable_user_switch = true;
				} elseif ( $customer_status || empty( $tk_us_update_options ) ) {
						$enable_user_switch = true;
				}
			} elseif ( 'surecart_page_sc-orders' === $hook ) {

				if ( $action == 'edit' && $order_status ) {
					$enable_user_switch = true;
				} elseif ( $order_list_status && ! $action ) {
					$enable_user_switch = true;
				}
			} elseif ( 'surecart_page_sc-subscriptions' === $hook ) {

				if ( $action == 'edit' && $subscriptions_status ) {
					$enable_user_switch = true;
				} elseif ( $subscriptions_list_status && ! $action ) {
					$enable_user_switch = true;
				}

			} elseif ( 'surecart_page_sc-services' === $hook && $service_view ) { // For Service Addon.
				$is_services_settings_available = Surelywp_Toolkit::is_services_us_settings_available();
				if ( $is_services_settings_available ) {
					$enable_user_switch = true;
				}
			} elseif ( 'surecart_page_sc-support' === $hook && $support_ticket_view ) { // For Support Portal Addon.
				$is_support_portal_settings_available = Surelywp_Toolkit::is_support_portal_us_settings_available();
				if ( $is_support_portal_settings_available ) {
					$enable_user_switch = true;
				}
			} elseif ( 'surecart_page_sc-inquiries' === $hook && $inquiry_view ) { // For inquiry Addon.
				$enable_user_switch = true;
			}

			$is_enable_us = self::get_settings_option( 'status' );
			if ( ! $is_enable_us ) {
				$enable_user_switch = false;
			}

			$is_allow_user_switching = self::is_allow_user_switching();
			$user                    = wp_get_current_user();
			$roles                   = (array) $user->roles;
			$localize                = array(
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'ajax-nonce' ),
				'Linktext'          => esc_html__( 'Switch To User', 'surelywp-toolkit' ),
				'switch_url'        => wp_login_url(),
				'role_enable'       => $is_allow_user_switching,
				'current_hook'      => $hook,
				'current_user_role' => $roles,
			);

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
			wp_enqueue_style( 'surelywp-tk-us' . $min_file );

			if ( $enable_user_switch || 'surelywp_tk_us_settings' === $tab ) {
				wp_localize_script( 'surelywp-tk-us' . $min_file, 'tk_us_ajax_object', $localize );
				wp_enqueue_script( 'surelywp-tk-us' . $min_file );
			}

			// For Handle language Translation.
			wp_set_script_translations( 'surelywp-tk-us' . $min_file, 'surelywp-toolkit' );
		}

		/**
		 * Function to Get option by Name.
		 *
		 * @param string $option_name The option name of setting.
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function get_settings_option( $option_name ) {

			$options = get_option( 'surelywp_tk_us_settings_options' );
			if ( isset( $options[ $option_name ] ) ) {
				$option_value = $options[ $option_name ];
			} else {
				$option_value = '';
			}

			return $option_value;
		}

		/**
		 * Function to current user role have access for user swtiching.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function is_allow_user_switching() {

			$user_roles           = self::get_settings_option( 'user_role' );
			$tk_us_update_options = self::get_settings_option( 'tk_us_update_options' );

			// Default options.
			if ( empty( $tk_us_update_options ) ) {
				$user_roles = array( 'administrator', 'sc_shop_manager', 'sc_shop_worker' );
			}

			$user  = wp_get_current_user();
			$roles = (array) $user->roles;
			$allow = false;
			if ( ! empty( $user_roles ) ) {
				foreach ( $roles as $key => $roles ) {
					foreach ( $user_roles as $key => $userrole ) {
						if ( $userrole === $roles ) {
							$allow = true;
							break;
						}
					}
				}
			}
			return $allow;
		}

		/**
		 * Function to get old user cookie.
		 *
		 * @package Toolkit For Surecart
		 * @since 1.0.0
		 */
		public static function surelywp_tk_us_get_old_user_cookie() {
			if ( isset( $_COOKIE[ SURELYWP_TK_USER_SWITCHING_OLD_USER_COOKIE ] ) ) {
				return sanitize_text_field( wp_unslash( $_COOKIE[ SURELYWP_TK_USER_SWITCHING_OLD_USER_COOKIE ] ) );
			} else {
				return false;
			}
		}

		/**
		 * Function to get secure auth cookie.
		 *
		 * @package Toolkit For Surecart
		 * @since 1.0.0
		 */
		public static function surelywp_tk_us_secure_auth_cookie() {
			return ( is_ssl() && ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) ) );
		}

		/**
		 * Function to secure old user cookie.
		 *
		 * @package Toolkit for surecart
		 * @since 1.0.0
		 */
		public static function surelywp_tk_us_secure_olduser_cookie() {
			return ( is_ssl() && ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) ) );
		}

		/**
		 * Function to get old user.
		 *
		 * @package Toolkit For Surecart
		 * @since 1.0.0
		 */
		public static function get_old_user() {
			$cookie = self::surelywp_tk_us_get_old_user_cookie();
			if ( ! empty( $cookie ) ) {
				$old_user_id = wp_validate_auth_cookie( $cookie, 'logged_in' );

				if ( $old_user_id ) {
					return get_userdata( $old_user_id );
				}
			}
			return false;
		}

		/**
		 * Function to get auth cookie.
		 *
		 * @package Toolkit For Surecart
		 * @since 1.0.0
		 */
		public static function surelywp_tk_us_get_auth_cookie() {

			if ( self::surelywp_tk_us_secure_auth_cookie() ) {
				$auth_cookie_name = SURELYWP_TK_USER_SWITCHING_SECURE_COOKIE;
			} else {
				$auth_cookie_name = SURELYWP_TK_USER_SWITCHING_COOKIE;
			}

			if ( isset( $_COOKIE[ $auth_cookie_name ] ) && is_string( $_COOKIE[ $auth_cookie_name ] ) ) {
				$cookie = json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $auth_cookie_name ] ) ) );
			}
			if ( ! isset( $cookie ) || ! is_array( $cookie ) ) {
				$cookie = array();
			}
			return $cookie;
		}

		/**
		 * Authenticate Old user.
		 *
		 * @package Toolkit For Surecart
		 * @since 1.0.0
		 */
		public static function surelywp_us_tk_authenticate_old_user( WP_User $user ) {
			$cookie = self::surelywp_tk_us_get_auth_cookie();

			if ( ! empty( $cookie ) ) {
				if ( self::surelywp_tk_us_secure_auth_cookie() ) {
					$scheme = 'secure_auth';
				} else {
					$scheme = 'auth';
				}

				$old_user_id = wp_validate_auth_cookie( end( $cookie ), $scheme );

				if ( $old_user_id ) {
					return ( $user->ID === $old_user_id );
				}
			}
			return false;
		}

		/**
		 * Init function for user switching.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_us_action_init() {

			if ( ! isset( $_REQUEST['action'] ) ) {
				return;
			}

			$current_user = ( is_user_logged_in() ) ? wp_get_current_user() : null;
			$action       = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			switch ( $action ) {
					// We're attempting to switch to another user.
				case 'switch_surely_olduser':
					$old_user = self::get_old_user();
					if ( ! $old_user ) {
						wp_die( esc_html__( 'Could not switch users.', 'surelywp-toolkit' ), 400 );
					}

					// Check authentication.
					if ( ! self::surelywp_us_tk_authenticate_old_user( $old_user ) ) {
						wp_die( esc_html__( 'Could not switch users.', 'surelywp-toolkit' ), 403 );
					}

					// Check intent.
					check_admin_referer( "switch_to_olduser_{$old_user->ID}" );

					self::surelywp_tk_us_switch_to_user( $old_user->ID, false );

					if ( ! empty( $_REQUEST['interim-login'] ) && function_exists( 'login_header' ) ) {
						$GLOBALS['interim_login'] = 'success'; // @codingStandardsIgnoreLine
						login_header( '', '' );
						exit;
					}

					$args            = array(
						'user_switched' => 'true',
						'switched_back' => 'true',
					);
					$cookie_redirect = isset( $_COOKIE[ SURELYWP_TK_USER_REDIRECT_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ SURELYWP_TK_USER_REDIRECT_COOKIE ] ) ) : '';
					wp_safe_redirect( add_query_arg( $args, $cookie_redirect ) );
					exit;
			}
		}

		/**
		 * Function to set old user cookie.
		 *
		 * @package Toolkit for surecart
		 * @since 1.0.0
		 */
		public function surelywp_tk_us_set_olduser_cookie( $old_user_id, $pop = false, $token = '' ) {
			$secure_auth_cookie    = self::surelywp_tk_us_secure_auth_cookie();
			$secure_olduser_cookie = self::surelywp_tk_us_secure_olduser_cookie();

			$expiration     = time() + 172800; // 48 hours.
			$auth_cookie    = self::surelywp_tk_us_get_auth_cookie();
			$olduser_cookie = wp_generate_auth_cookie( $old_user_id, $expiration, 'logged_in', $token );

			if ( $secure_auth_cookie ) {
				$auth_cookie_name = SURELYWP_TK_USER_SWITCHING_SECURE_COOKIE;
				$scheme           = 'secure_auth';
			} else {
				$auth_cookie_name = SURELYWP_TK_USER_SWITCHING_COOKIE;
				$scheme           = 'auth';
			}

			if ( $pop ) {
				array_pop( $auth_cookie );
			} else {
				array_push( $auth_cookie, wp_generate_auth_cookie( $old_user_id, $expiration, $scheme, $token ) );
			}

			$auth_cookie = wp_json_encode( $auth_cookie );

			if ( false === $auth_cookie ) {
				return;
			}

			$page       = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$action     = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$id         = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
			$user_id    = isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ? sanitize_text_field( wp_unslash( $_GET['user_id'] ) ) : '';
			$type       = isset( $_GET['type'] ) && ! empty( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
			$service_id = isset( $_GET['service_id'] ) && ! empty( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : '';
			$support_id = isset( $_GET['support_id'] ) && ! empty( $_GET['support_id'] ) ? sanitize_text_field( wp_unslash( $_GET['support_id'] ) ) : '';

			$data = array(
				'page'       => $page,
				'action'     => $action,
				'id'         => $id,
				'service_id' => $service_id,
				'support_id' => $support_id,
			);

			if ( 'user_view' === $type ) {
				$current_page = admin_url( sprintf( 'user-edit.php?user_id=%s', $user_id ) );
			} elseif ( 'user_list' === $type ) {
				$current_page = admin_url( 'users.php' );
			} else {
				$current_page = admin_url( sprintf( 'admin.php?%s', http_build_query( $data ) ) );
			}

			// $current_page = admin_url( sprintf( 'admin.php?%s', http_build_query( $data ) ) );

			do_action( 'surelywp_us_set_user_switching_cookie', $auth_cookie, $expiration, $old_user_id, $scheme, $token );

			$scheme = 'logged_in';

			do_action( 'surelywp_us_set_old_user_cookie', $olduser_cookie, $expiration, $old_user_id, $scheme, $token );

			setcookie( SURELYWP_TK_USER_REDIRECT_COOKIE, $current_page, $expiration, SITECOOKIEPATH, COOKIE_DOMAIN );

			setcookie( $auth_cookie_name, $auth_cookie, $expiration, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_auth_cookie, true );
			setcookie( SURELYWP_TK_USER_SWITCHING_OLD_USER_COOKIE, $olduser_cookie, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure_olduser_cookie, true );
		}

		/**
		 * Function to clear old user cookie.
		 *
		 * @package Toolkit for surecart
		 * @since 1.0.0
		 */
		public static function surelywp_tk_us_clear_olduser_cookie( $clear_all = true ) {
			$auth_cookie = self::surelywp_tk_us_get_auth_cookie();

			if ( ! empty( $auth_cookie ) ) {
				array_pop( $auth_cookie );
			}
			if ( $clear_all || empty( $auth_cookie ) ) {

				/**
				 * Fires just before the user switching cookies are cleared.
				 */
				do_action( 'surelywp_clear_olduser_cookie' );

				if ( ! apply_filters( 'surelywp_us__send_auth_cookies', true ) ) {
					return;
				}

				$expire = time() - 31536000;
				setcookie( SURELYWP_TK_USER_SWITCHING_COOKIE, ' ', $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
				setcookie( SURELYWP_TK_USER_SWITCHING_SECURE_COOKIE, ' ', $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
				setcookie( SURELYWP_TK_USER_SWITCHING_OLD_USER_COOKIE, ' ', $expire, COOKIEPATH, COOKIE_DOMAIN );
			}
		}

		/**
		 * Function to switch to user.
		 *
		 * @package Toolkit for surecart
		 * @since 1.0.0
		 */
		public function surelywp_tk_us_switch_to_user( $user_id, $set_old_user = true ) {

			$user = get_userdata( $user_id );

			if ( ! $user ) {
				return false;
			}

			$old_user_id = ( is_user_logged_in() ) ? get_current_user_id() : false;
			$old_token   = function_exists( 'wp_get_session_token' ) ? wp_get_session_token() : '';

			$auth_cookies = self::surelywp_tk_us_get_auth_cookie();
			$auth_cookie  = end( $auth_cookies );
			$cookie_parts = $auth_cookie ? wp_parse_auth_cookie( $auth_cookie ) : false;

			if ( $old_user_id && $set_old_user ) {
				$new_token = '';
				self::surelywp_tk_us_set_olduser_cookie( $old_user_id, false, $old_token );
			} else {
				// Switching back, either after being switched to another user.
				$new_token = ( $cookie_parts && isset( $cookie_parts['token'] ) ) ? $cookie_parts['token'] : '';
				self::surelywp_tk_us_clear_olduser_cookie( false );

				wp_clear_auth_cookie();
				wp_set_auth_cookie( $user_id, '', '', $new_token );
				wp_set_current_user( $user_id );
			}
		}

		/**
		 * Wp footer action.
		 *
		 * @package Toolkit for surecart
		 * @since 1.0.0
		 */
		public function surelywp_tk_us_action_wp_footer() {

			$old_user = self::get_old_user();

			if ( $old_user instanceof WP_User ) {
				$user_login = $old_user->user_login;
				$user_login = ucfirst( $user_login );
				$url        = add_query_arg(
					array(
						'redirect_to' => urlencode( surelywp_tk_get_current_url() ),
					),
					self::surelywp_tk_us_switch_back_url( $old_user )
				);
				echo '<script>
					
					document.addEventListener("DOMContentLoaded", function () {

					function setCookie(name, value, days) {
						let date = new Date();
						date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); // Convert days to milliseconds
						document.cookie = name + "=" + value + "; expires=" + date.toUTCString() + "; path=/";
					}

					function getCookie(name) {
						let cookies = document.cookie.split(";");
						for (let i = 0; i < cookies.length; i++) {
							let cookie = cookies[i].trim();
							if (cookie.startsWith(name + "=")) {
								return cookie.substring(name.length + 1);
							}
						}
						return null;
					}

					var sourceDiv = document.getElementById("surelywp_user_switching_switch_on");
					if( sourceDiv ) {
						var divHeight =  sourceDiv.offsetHeight - 5;
						sourceDiv.style.bottom = `-${divHeight}px`;  
					}
					
					document.querySelector("#surelywp_user_switching_switch_on .down-arrow").addEventListener("click", function() {
    					document.querySelector("#surelywp_user_switching_switch_on").classList.remove("open");
						setCookie("is_closed_us_btn", "true", 1); // Sets a cookie for 1 day

					});

					document.querySelector("#surelywp_user_switching_switch_on").addEventListener("mouseover", function() {
    				this.classList.add("open");
					setCookie("is_closed_us_btn", "false", 1); // Sets a cookie for 1 day
					});
				});
				</script>';

				$btn_class        = 'open';
				$is_closed_us_btn = isset( $_COOKIE['is_closed_us_btn'] ) && ! empty( $_COOKIE['is_closed_us_btn'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['is_closed_us_btn'] ) ) : '';
				if ( 'true' === $is_closed_us_btn ) {
					$btn_class = '';
				}
				printf(
					// translators: 1: Opening HTML for switch back button, 2: Username to switch back to, 3: Closing HTML for switch back button.
					esc_html__( '%1$s Switch Back To %2$s %3$s', 'surelywp-toolkit' ),
					'<p id="surelywp_user_switching_switch_on" class="' . esc_html( $btn_class ) . '"><a href="' . esc_url( $url ) . '">',
					esc_html( $user_login ),
					'</a><img class="down-arrow" src="' . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/down-arrow.svg' ) . '"></p>'
				);
			}
		}

		/**
		 * Switch back url.
		 *
		 * @package Toolkit for surecart
		 * @since 1.0.0
		 */
		public static function surelywp_tk_us_switch_back_url( WP_User $user ) {
			return wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'switch_surely_olduser',
						'nr'     => 1,
					),
					wp_login_url()
				),
				"switch_to_olduser_{$user->ID}"
			);
		}

		/**
		 * Switch User action after click on switch user link from backend.
		 *
		 * @package Toolkit for surecart
		 * @since 1.0.0
		 */
		public function surelywp_tk_us_user_action() {
			if ( is_admin() ) {
				if ( isset( $_GET['customerID'] ) && ! empty( $_GET['customerID'] ) ) {
					$customer_id = sanitize_text_field( wp_unslash( $_GET['customerID'] ) );
					$args        = array(
						'meta_query' => array(
							array(
								'key'     => 'sc_customer_ids',
								'value'   => $customer_id,
								'compare' => 'LIKE',
							),
						),
					);

					$dashboard_id = get_option( 'surecart_dashboard_page_id' );

					$member_arr = get_users( $args );
					if ( ! empty( $member_arr ) ) {
						$roles     = $member_arr[0]->roles;
						$c_user_id = get_current_user_id();

						if ( $c_user_id == $member_arr[0]->ID ) {

							wp_die( esc_html__( 'You cannot switch to this user since you are already logged in as this user.', 'surelywp-toolkit' ), 400 );
						}

						self::surelywp_tk_us_switch_to_user( $member_arr[0]->ID, true );

						$user_id = $member_arr[0]->ID;

						wp_clear_auth_cookie();
						wp_set_current_user( $user_id, $member_arr[0]->user_login );
						wp_set_auth_cookie( $user_id );
						wp_redirect( get_permalink( $dashboard_id ) );
						exit;
					}
				}
			}
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_User_Switching class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function Surelywp_Tk_User_Switching() {  // phpcs:ignore
		$instance = Surelywp_Tk_User_Switching::get_instance();
		return $instance;
	}
}
