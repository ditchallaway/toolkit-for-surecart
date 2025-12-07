<?php
/**
 * Main class for Lead Magnets.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.3
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\Product;
use SureCart\Models\Customer;
use SureCart\Models\Component;
use SureCart\Models\User;
use SureCart\Models\Purchase;

if ( ! class_exists( 'Surelywp_Tk_Lm' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	class Surelywp_Tk_Lm {

		/**
		 * Check page have the surecart product list block.
		 *
		 * @var \SurelyWP_Catalog_Mode
		 */
		public $is_page_have_product_list;

		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Lm
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 * @return  \Surelywp_Tk_Lm
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

			$is_enable_lead_magnets = self::get_settings_option( 'is_enable_lead_magnets' );

			// If Lead Magnets not enable.
			if ( ! $is_enable_lead_magnets ) {
				return;
			}

			add_action( 'wp', array( $this, 'surelywp_tk_lm_wp' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'surelywp_tk_lm_front_script' ) );
			add_filter( 'render_block', array( $this, 'surelywp_tk_lm_button_block_wrapper' ), 10, 2 );
			add_action( 'init', array( $this, 'surelywp_tk_lm_verify_email' ) );
			add_shortcode( 'surelywp_lead_magnet_button', array( $this, 'surelywp_tk_lm_add_optin_button' ) );
			add_filter( 'add_to_lead_magnet_button', array( $this, 'surelywp_tk_lm_render_button' ), 10, 1 );
			add_action( 'surelywp_tk_lm_on_new_order_create', array( $this, 'surelywp_tk_lm_on_new_order_create_callback' ), 10, 3 );
			add_shortcode( 'surelywp_toolkit_downloads_list', array( $this, 'surlywp_tk_sc_download_list' ) );

			// add Lead Magnet menu to surecart navation.
			add_filter( 'surelywp_surecart_customer_dashboard_data', array( $this, 'surelywp_tk_lm_add_lead_magnet_menu' ), 10, 2 );

			// add customer lead-magnet content.
			add_action( 'surelywp_surecart_dashboard_right', array( $this, 'surelywp_tk_lm_surecart_dashboard_right_content' ) );
		}

		/**
		 * Funcation to add Lead Magnet menu.
		 *
		 * @param array $data the data for customer dashboard.
		 * @param array $controller the controller for customer dashboard.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_lm_add_lead_magnet_menu( $data, $controller ) {

			$is_customer_dashboard_enable = self::get_settings_option( 'is_customer_dashboard_enable' );
			if ( ! $is_customer_dashboard_enable ) {
				return $data;
			}

			$tab_name = self::get_settings_option( 'tab_name' );
			if ( empty( $tab_name ) ) {
				$tab_name = esc_html__( 'Downloads', 'surelywp-toolkit' );
			}

			$tab_icon = self::get_settings_option( 'tab_icon' );
			if ( empty( $tab_icon ) ) {
				$tab_icon = 'gift';
			}

			// Add Sevices Tab.
			$dashboard_url   = get_permalink( get_the_ID() );
			$lead_magnet_url = add_query_arg(
				array(
					'action' => 'index',
					'model'  => 'lead-magnet',
				),
				$dashboard_url
			);

			$lead_magnet_tab = array(
				'lead-magnet' => array(
					'icon_name'            => apply_filters( 'surelywp_tk_lead_magnet_tab_icon_name', $tab_icon ),
					'name'                 => apply_filters( 'surelywp_tk_lead_magnet_tab_name', $tab_name ),
					'active'               => $controller->isActive( 'lead-magnet' ),
					'href'                 => $lead_magnet_url,
					'surelywp_custom_menu' => true,
				),
			);

			$orders_tab_index = array_search( 'orders', array_keys( $data['navigation'] ), true );

			if ( false !== $orders_tab_index ) {

				$data['navigation'] = array_merge(
					array_slice( $data['navigation'], 0, $orders_tab_index + 1 ),
					$lead_magnet_tab,
					array_slice( $data['navigation'], $orders_tab_index + 1 )
				);

			} else {

				// Handle the case where 'orders' is not found in the navigation array.
				// For example, you might want to append the services tab at the end.
				$data['navigation'] = array_merge( $data['navigation'], $lead_magnet_tab );
			}

			return $data;
		}

		/**
		 * Funcation to add customer service content.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_lm_surecart_dashboard_right_content() {

			$model             = isset( $_GET['model'] ) && ! empty( $_GET['model'] ) ? sanitize_text_field( wp_unslash( $_GET['model'] ) ) : '';
			$dashboard_page_id = get_option( 'surecart_dashboard_page_id' );
			$dashboard_url     = '#';
			if ( $dashboard_page_id ) {
				$dashboard_url = get_permalink( $dashboard_page_id );
			}

			$lm_plural_name = self::get_settings_option( 'lm_plural_name' );
			if ( 'lead-magnet' === $model ) {?>
			<div class="surelywp-tk-lm-dashboard">
				<div class="breadbcrumbs">
					<sc-breadcrumbs>
						<sc-breadcrumb href="<?php echo esc_url( $dashboard_url ); ?>"><?php esc_html_e( 'Dashboard', 'surelywp-toolkit' ); ?></sc-breadcrumb>
						<sc-breadcrumb><?php echo esc_html( $lm_plural_name ); ?></sc-breadcrumb>
					</sc-breadcrumbs>
				</div>
				<div class="lm-downloads">
				<?php
					echo do_shortcode( '[surelywp_toolkit_downloads_list lead_magnets_only=true]' );
				?>
				</div>
			</div>
				<?php
			}
		}

		/**
		 * Check page have the surecart product list block.
		 *
		 * @package SurelyWP B2B
		 * @since 1.3
		 */
		public function surelywp_tk_lm_wp() {

			// Check page have the surecart product list block.
			$this->is_page_have_product_list = has_block( 'surecart/product-list', get_the_ID() );
		}

		/**
		 * Function to trigger when new lead magents order created.
		 *
		 * @param object $checkout the new checkout object.
		 * @param string $product_id the id of the product.
		 * @param string $user_id the id of the user.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_lm_on_new_order_create_callback( $checkout, $product_id, $user_id ) {

			$order_id = $checkout->order ?? '';
			if ( ! $order_id ) {
				return;
			}

			// Store the order id on the user privary consent data.
			$existing_consents = get_user_meta( $user_id, 'surelywp_lm_order_privacy_consents', true );

			if ( ! is_array( $existing_consents ) ) {
				return;
			}

			foreach ( $existing_consents as $key => $consent ) {
				if ( $product_id === $consent['product_id'] ) {
					$existing_consents[ $key ]['order_id'] = $order_id;
				}
			}

			update_user_meta( $user_id, 'surelywp_lm_order_privacy_consents', $existing_consents );
		}

		/**
		 * Function to render Optin button product page.
		 *
		 * @param string $block_name The name of the block.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function is_useful_block( $block_name ) {

			if ( ! $block_name ) {
				return false;
			}

			$useful_blocks = array(
				'surecart/product-buy-buttons',
				'surecart/product-title',
				'surecart/product-price',
				'surecart/product-selected-price-fees',
				'surecart/product-selected-price-amount',
				'surecart/product-description',
				'surecart/product-quantity',
				'surecart/product-price-choices',
				'surecart/product-price-chooser',
			);

			return in_array( $block_name, $useful_blocks, true );
		}

		/**
		 * Function to get the display postion.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function get_the_sub_form_display_postion() {

			$sub_form_method = self::get_settings_option( 'sub_form_method' );

			$position = '';
			if ( 'popup_form' === $sub_form_method ) {
				$position = self::get_settings_option( 'popup_btn_position' );
			} elseif ( 'inline_form' === $sub_form_method ) {
				$position = self::get_settings_option( 'inline_form_position' );
			}

			return $position;
		}

		/**
		 * Function to render Optin button product page.
		 *
		 * @param array $block_content The Content of block.
		 * @param array $block The name of block.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_lm_button_block_wrapper( $block_content, $block ) {

			// if product list on page.
			if ( $this->is_page_have_product_list ) {
				return $block_content;
			}

			$block_name      = $block['blockName'];
			$is_useful_block = self::is_useful_block( $block_name );

			if ( ! $is_useful_block ) {
				return $block_content;
			}

			$product                                        = surelywp_tk_get_current_product();
			$surelywp_lm_is_product_enable_for_lead_magnets = self::surelywp_tk_lm_is_product_enable_for_lead_magnets( $product );

			// Check plugin activation and enable lead magnets.
			if ( $surelywp_lm_is_product_enable_for_lead_magnets ) {

				switch ( $block_name ) {
					case 'surecart/product-buy-buttons':
						return '';
					case 'surecart/product-title':
						// Get the display postion.
						$position = self::get_the_sub_form_display_postion();
						if ( 'title' === $position ) {
							$content = self::get_html();
							return $block_content . $content;
						}
						break;
					case 'surecart/product-price':
						return '';
					case 'surecart/product-selected-price-fees':
						return '';
					case 'surecart/product-selected-price-amount':
						return '';
					case 'surecart/product-description':
						// Get the display postion.
						$position = self::get_the_sub_form_display_postion();
						if ( 'description' === $position ) {
							$content = self::get_html();
							return $block_content . $content;
						}
						break;
					case 'surecart/product-quantity':
						return '';
					case 'surecart/product-price-choices':
						return '';
					case 'surecart/product-price-chooser':
						return '';
					default:
						return $block_content;
				}
			}

			return $block_content;
		}

		/**
		 * Function to enqueue scripts and styles for the front end.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_lm_front_script() {

			wp_register_style( 'surelywp-tk-lm-front', SURELYWP_TOOLKIT_ASSETS_URL . '/css/lead-magnets/surelywp-tk-lm-front.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_style( 'surelywp-tk-lm-front-min', SURELYWP_TOOLKIT_ASSETS_URL . '/css/lead-magnets/surelywp-tk-lm-front.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_script( 'surelywp-tk-lm-front', SURELYWP_TOOLKIT_ASSETS_URL . '/js/lead-magnets/surelywp-tk-lm-front.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-lm-front-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/lead-magnets/surelywp-tk-lm-front.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$is_user_login    = is_user_logged_in();
			$consent_checkbox = '';
			$sub_form_fields  = self::get_settings_option( 'sub_form_fields' );
			if ( $is_user_login && isset( $sub_form_fields['consent_checkbox']['is_show'] ) ) {

				$privacy_policy_link = $sub_form_fields['consent_checkbox']['privacy_policy_link'] ?? '';
				$consent_text        = $sub_form_fields['consent_checkbox']['label_value'] ?? '';

				if ( $privacy_policy_link ) {
					$privacy_policy_url = '<a href="' . esc_url( $privacy_policy_link ) . '" target="_blank">' . esc_html__( 'privacy policy', 'surelywp-toolkit' ) . '</a>';
					$consent_text       = str_replace( '{privacy_policy_link}', $privacy_policy_url, $sub_form_fields['consent_checkbox']['label_value'] );
				}

				$required = '';
				if ( isset( $sub_form_fields['consent_checkbox']['is_required'] ) ) {
					$required = 'required';
				}
				$consent_checkbox = '<sc-checkbox class="optin_form_consent_checkbox" name="optin_form_consent_checkbox" value="1" ' . esc_attr( $required ) . '>' . wp_kses_post( $consent_text ) . '</sc-checkbox>';
			}

			$localize = array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'nonce'            => wp_create_nonce( 'ajax-nonce' ),
				'is_user_login'    => $is_user_login,
				'consent_checkbox' => $consent_checkbox,
			);

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			wp_enqueue_style( 'surelywp-tk-lm-front' . $min_file );
			wp_enqueue_script( 'surelywp-tk-lm-front' . $min_file );
			wp_localize_script( 'surelywp-tk-lm-front' . $min_file, 'tk_lm_front_ajax_object', $localize );

			// For Handle language Translation.
			wp_set_script_translations( 'surelywp-tk-lm-front' . $min_file, 'surelywp-toolkit' );
		}

		/**
		 * Function to check is valid lead magnet product id.
		 *
		 * @param int $product_id The id of the product.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.2
		 */
		public static function surelywp_tk_lm_validate_product_id( $product_id ) {

			if ( empty( $product_id ) ) {
				return array(
					'status' => false,
					'error'  => esc_html__( 'Product ID not Found', 'surelywp-toolkit' ),
				);
			}

			$product = \SureCart\Models\Product::with( array( 'product_collections' ) )->find( $product_id );

			if ( is_wp_error( $product ) || empty( $product ) ) {
				return array(
					'status' => false,
					'error'  => esc_html__( 'Product not Found', 'surelywp-toolkit' ),
				);
			} else {
				$is_lm_enable = self::surelywp_tk_lm_is_product_enable_for_lead_magnets( $product );
				if ( ! $is_lm_enable ) {
					return array(
						'status' => false,
						/* translators: %s: Product name */
						'error'  => sprintf( esc_html__( '%s is not a lead magnet product', 'surelywp-toolkit' ), $product->name ),
					);
				}
			}

			return array(
				'status' => true,
			);
		}

		/**
		 * Function to check product is enable or not for Lead Magnets.
		 *
		 * @param object $product The Surecart product.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function surelywp_tk_lm_is_product_enable_for_lead_magnets( $product ) {

			if ( ! empty( $product ) ) {

				$lead_magnets_product     = self::get_settings_option( 'lead_magnets_product' );
				$lm_products              = self::get_settings_option( 'lm_products' );
				$lm_update_options        = self::get_settings_option( 'tk_lm_update_options' );
				$product_min_price_amount = $product->metrics->min_price_amount ?? '';

				if ( ( 'all' === $lead_magnets_product || empty( $lm_update_options ) ) && ( 0 === $product_min_price_amount ) ) {
					return true;
				} elseif ( 'specific' === $lead_magnets_product && ( ! empty( $lm_products ) && in_array( $product->id, $lm_products, true ) ) ) {
					return true;
				} elseif ( 'specific_collection' === $lead_magnets_product && $product->product_collections->pagination->count > 0 ) {
					$collection_id = self::get_settings_option( 'product_collection' );
					foreach ( $product->product_collections->data as $key => $collection ) {
						if ( ! empty( $collection_id ) && in_array( $collection->id, $collection_id, true ) && 0 === $product_min_price_amount ) {
							return true;
						}
					}
					return false;
				} else {
					return false;
				}
			}
		}

		/**
		 * Function to give inquiry button and block_content with link.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public static function get_html() {

			$product = surelywp_tk_get_current_product();
			$content = '';
			if ( ! empty( $product ) ) {
				$content        = '<div class="surlywp-lm-optin">';
				$button_or_form = apply_filters( 'add_to_lead_magnet_button', $product->id );
				$content       .= $button_or_form;
				$content       .= '</div>';
				return $content;
			}
		}

		/**
		 * Function to render optin button
		 *
		 * @param string $product_id The id of product.
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function surelywp_tk_lm_render_button( $product_id ) {

			$sub_form_method = self::get_settings_option( 'sub_form_method' );
			if ( ( 'popup_form' === $sub_form_method ) || ( 'inline_form' === $sub_form_method && is_user_logged_in() ) ) {
				$email_optin_button_text = self::get_settings_option( 'popup_btn_text' );
				if ( empty( $email_optin_button_text ) ) {
					$email_optin_button_text = esc_html__( 'Get Your Free Resource!', 'surelywp-toolkit' );
				}
				$optin_button = '<div class="surlywp-lm-optin-button surelywp-lead-magnet-button lm-product-id-' . $product_id . '"><sc-button type="primary" id="surlywp-lm-optin-btn"> ' . $email_optin_button_text . ' </sc-button></div>';
				return $optin_button;
			} elseif ( 'inline_form' === $sub_form_method && ! is_user_logged_in() ) {
				ob_start();
				self::get_sub_form( $product_id );
				return ob_get_clean();
			}
		}

		/**
		 * Shortcode for add to email optin button to display with any page.
		 *
		 * [surelywp_lead_magnet_button]
		 *
		 * @param string $attr The attributes of shortcode.
		 * @param string $content The content of shortcode.
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function surelywp_tk_lm_add_optin_button( $attr, $content ) {
			$attr = shortcode_atts(
				array(
					'product_id' => '',
				),
				$attr
			);

			$content_main = '';
			$product_id   = '';
			if ( isset( $attr['product_id'] ) && ! empty( $attr['product_id'] ) ) {
				$product_id = $attr['product_id'];
			} else {
				$product = surelywp_tk_get_current_product();
				if ( ! empty( $product ) ) {
					$product_id = $product->id;
				}
			}

			if ( $product_id ) {
				$content     .= apply_filters( 'add_to_lead_magnet_button', $product_id );
				$content_main = '<div class="surlywp-lm-optin">' . do_shortcode( $content ) . '</div>';
			}

			return $content_main;
		}

		/**
		 * Function to give modal content
		 *
		 * @param init $product_id The id of the product.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function surelywp_tk_lm_email_optin_form_modal_html( $product_id ) {

			ob_start();

			// Create a new SureCart order model.
			?>
			<div class="surelywp-lm-optin-form-modal">
				<div class="email-optin-modal modal show-modal">
					<div class="modal-content">
						<span class="close-button" id="email-optin-form-modal-close">×</span>
							<?php echo self::get_sub_form( $product_id ); //phpcs:ignore?>
					</div>
				</div>
			</div>	
			<?php
			return ob_get_clean();
		}

		/**
		 * Function to get the subscription form.
		 *
		 * @param string $product_id the id of the product.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function get_sub_form( $product_id ) {

			include SURELYWP_TOOLKIT_TEMPLATE_PATH . '/lead-magnets/subscription-form.php';
		}

		/**
		 * Function to Create Order
		 *
		 * @param array $checkout_data The Checkdata for order.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function surelywp_tk_lm_create_checkout( $checkout_data ) {

			// Get The Checkout Data.
			$product_id         = $checkout_data['product_id'];
			$email              = isset( $checkout_data['email'] ) && ! empty( $checkout_data['email'] ) ? $checkout_data['email'] : '';
			$first_name         = isset( $checkout_data['first_name'] ) && ! empty( $checkout_data['first_name'] ) ? $checkout_data['first_name'] : '';
			$last_name          = isset( $checkout_data['last_name'] ) && ! empty( $checkout_data['last_name'] ) ? $checkout_data['last_name'] : '';
			$name               = isset( $checkout_data['name'] ) && ! empty( $checkout_data['name'] ) ? $checkout_data['name'] : '';
			$customer_id        = isset( $checkout_data['customer_id'] ) && ! empty( $checkout_data['customer_id'] ) ? $checkout_data['customer_id'] : '';
			$user_id            = isset( $checkout_data['user_id'] ) && ! empty( $checkout_data['user_id'] ) ? $checkout_data['user_id'] : '';
			$customer_live_mode = isset( $checkout_data['customer_live_mode'] ) && ! empty( $checkout_data['customer_live_mode'] ) ? true : false;

			if ( empty( $customer_id ) ) {

				$customer_obj = Customer::where(
					array(
						'email'     => strtolower( $email ),
						'live_mode' => $customer_live_mode,
					)
				)->get();

				$customer_id = $customer_obj[0]->id ?? '';
			}

			// Get Product object.
			$product_obj = Product::with(
				array(
					'prices',
				)
			)->find( $product_id );

			if ( is_wp_error( $product_obj ) || empty( $product_obj ) ) {
				return array(
					'status' => false,
					'error'  => esc_html__( 'Product Not Found', 'surelywp-toolkit' ),
				);
			}

			// Get Product Price Id.
			$price_id = $product_obj->prices->data[0]->id;

			// Return If Price Id and Email Id not get.
			if ( empty( $price_id ) || empty( $email ) ) {
				return array(
					'status'          => false,
					'already_created' => false,
				);
			}

			// Check Product is already purchase then return.
			if ( ! empty( $customer_id ) && ! isset( $_GET['token'] ) ) {

				$purchases = self::surelywp_tk_lm_get_customer_orders( $customer_id );
				foreach ( $purchases as $key => $purchase_obj ) {

					if ( $purchase_obj->checkout->line_items->data[0]->price->product->id == $product_id ) {

						return array(
							'already_created' => true,
							'status'          => true,
						);
					}
				}
			}

			// Create Checkout Session.
			$checkout = ( new \SureCart\Models\Checkout(
				array(

					'live_mode'              => $customer_live_mode,
					'email'                  => $email, // customer email.
					'tax_enabled'            => false,
					'name'                   => $name,
					'first_name'             => $first_name,
					'last_name'              => $last_name,
					'customer'               => $customer_id,
					'refresh_price_versions' => true,
					'metadata'               => array(
						'surelywp_lead_magnet_order' => true,
					),
					'line_items'             => array(
						array(
							'ad_hoc_amount' => 0,
							'price_id'      => $price_id,
							'quantity'      => '1',
						),
					),
				)
			) )->save();

			// Return If Checkout Object not get.
			if ( is_wp_error( $checkout ) || empty( $checkout ) ) {
				return array(
					'already_created' => false,
					'status'          => false,
				);
			}

			// Finalize the checkout.
			$checkout->where( array( 'manual_payment' => true ) )->finalize();

			// Make manual payment.
			$checkout->manuallyPay();

			if ( ! is_wp_error( $checkout ) && ! empty( $checkout ) ) {

				do_action( 'surelywp_tk_lm_on_new_order_create', $checkout, $product_id, $user_id );

				return array(
					'already_created' => false,
					'status'          => true,
				);
			} else {
				return array(
					'already_created' => false,
					'status'          => false,
				);
			}
		}

		/**
		 * Function to get the customer orders.
		 *
		 * @param string $customer_id the id of the customer.
		 * @package SurelyWP  Toolkit
		 * @since   1.3
		 */
		public static function surelywp_tk_lm_get_customer_orders( $customer_id ) {

			$orders = \SureCart\Models\Order::where(
				array(
					'customer_ids' => array( $customer_id ),
				)
			)->with(
				array(
					'checkout',
					'checkout.line_items',
					'line_item.price',
					'price.product',
				)
			)->get();

			return $orders;
		}

		/**
		 * Function to Create Customer
		 *
		 * @param array $customer_data The customer data for create customer.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function surelywp_tk_lm_create_customer( $customer_data ) {

			$is_live_mode = true;
			if ( ! empty( $customer_data['mode'] && 'test' === $customer_data['mode'] ) ) {

				$is_live_mode = false;
			}

			// Cretate New Customer.
			$customer_obj = Customer::create(
				array(
					'name'       => $customer_data['name'],
					'email'      => $customer_data['email'],
					'first_name' => $customer_data['first_name'],
					'last_name'  => $customer_data['last_name'],
					'live_mode'  => $is_live_mode,
				),
				$customer_data['create_user'],
			);

			return array(
				'status'       => true,
				'customer_obj' => $customer_obj,
			);
		}

		/**
		 * Function to Cutomer verification email.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surelywp_tk_lm_verify_email() {

			// if user Id, Product Id and Token Set.
			if ( isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) && isset( $_GET['product_id'] ) && ! empty( $_GET['product_id'] ) && isset( $_GET['token'] ) && ! empty( $_GET['token'] ) ) { // Check user ID, product ID and token from url.

				$user_id    = sanitize_text_field( $_GET['user_id'] );
				$product_id = sanitize_text_field( $_GET['product_id'] );
				$token      = sanitize_text_field( $_GET['token'] );

				// Get Token.
				$user_token = get_user_meta( $user_id, '_sureplywp_lm_token', true );
				$user_token = md5( $user_token );

				if ( $user_token == $token ) {

					// Update token.
					update_user_meta( $user_id, '_sureplywp_lm_token', '' );

					// Auto login User.
					wp_clear_auth_cookie();
					wp_set_current_user( $user_id );
					wp_set_auth_cookie( $user_id );

					$user_data = wp_get_current_user();

					// Create Checkout.
					$checkout_data = array(
						'product_id' => $product_id,
						'email'      => $user_data->user_email,
						'name'       => $user_data->display_name,
						'first_name' => isset( $user_data->user_firstname ) ? $user_data->user_firstname : '',
						'last_name'  => isset( $user_data->user_lastname ) ? $user_data->user_lastname : '',
					);

					$customer_live_mode = \SureCart::cart()->getMode();

					$customer_obj = Customer::where(
						array(
							'email'     => strtolower( $checkout_data['email'] ),
							'live_mode' => 'test' === $customer_live_mode ? false : true,
						)
					)->get();

					if ( ! is_wp_error( $customer_obj ) && ! empty( $customer_obj ) && isset( $customer_obj[0]->id ) ) {

						$checkout_data['customer_id']        = $customer_obj[0]->id;
						$checkout_data['user_id']            = $user_id;
						$checkout_data['customer_live_mode'] = ( 'test' === $customer_live_mode ) ? false : true;
						$is_checkout_created                 = self::surelywp_tk_lm_create_checkout( $checkout_data );

						if ( $is_checkout_created ) {

							$dashboard_page_id = get_option( 'surecart_dashboard_page_id' );
							$dashboard_url     = get_permalink( $dashboard_page_id ) . '?action=index&model=download';
							wp_safe_redirect( $dashboard_url );
							exit();

						}
					}
				} elseif ( get_current_user_id() == $user_id ) {
					$dashboard_page_id = get_option( 'surecart_dashboard_page_id' );
					$dashboard_url     = get_permalink( $dashboard_page_id ) . '?action=index&model=download';
					wp_safe_redirect( $dashboard_url );
					exit();
				}
			}
		}

		/**
		 * Function to send email verification mail.
		 *
		 * @param array  $checkout_data The Checkout data.
		 * @param int    $user_id the user id.
		 * @param string $token the user verification token.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function surelywp_tk_lm_send_verification_mail( $checkout_data, $user_id, $token ) {

			$product_id = $checkout_data['product_id'];

			// Send Mail.
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			$subject = self::get_settings_option( 'verification_email_subject' );

			if ( empty( $subject ) ) {
				$subject = esc_html__( 'Enter custom text you want to use for the verification email subject.', 'surelywp-toolkit' );
			}

			$body = self::get_settings_option( 'verification_email_body' );

			if ( empty( $body ) ) {
				$body = esc_html__(
					'Dear {customer_name},
You have opted in to receive a free download of the {product_name} from {website_name}. Please click the following link to confirm your subscription and complete your download.

{verification_link}

Enjoy,
The {website_name} Team',
					'surelywp-toolkit'
				);
			}

			// Replace newline characters with <br> tags to preserve line breaks in HTML.
			$body = nl2br( $body );

			$email_name = '';
			if ( ! empty( $optin_form_first_name ) ) {
				$email_name = $optin_form_first_name;
			} elseif ( ! empty( $optin_form_last_name ) ) {
				$email_name = $optin_form_last_name;
			} else {
				$email_name = $checkout_data['name'];
			}

			$body = str_replace( '{customer_name}', $email_name, $body );

			// get Product Name.
			$product_obj = \SureCart\Models\Product::with(
				array(
					'prices',
				)
			)->find( $product_id );

			$body = str_replace( '{product_name}', $product_obj->name, $body );

			$body = str_replace( '{website_name}', get_bloginfo( 'name' ), $body );

			// Replace Verification Link.
			$token_hash        = md5( $token );
			$link              = get_site_url() . "?user_id=$user_id&product_id=$product_id&token=$token_hash";
			$verification_link = '<a href="' . esc_url( $link ) . '" target="_blank" >' . esc_html__( 'Click Here To Verify', 'surelywp-toolkit' ) . '</a>';
			$body              = str_replace( '{verification_link}', $verification_link, $body );

			// Send the email using wp_mail.
			$is_mail_send = wp_mail( $checkout_data['email'], $subject, $body, $headers );

			return $is_mail_send;
		}

		/**
		 * Function to store user order privary consent.
		 *
		 * @param int   $user_id The id of the user.
		 * @param array $consent_data The user data consent.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function surelywp_tk_lm_store_user_consent( $user_id, $consent_data ) {

			if ( ! $user_id ) {
				return;
			}

			$user_ip_address   = surelywp_tk_get_user_ip();
			$sub_form_fields   = self::get_settings_option( 'sub_form_fields' );
			$policy_url        = $sub_form_fields['consent_checkbox']['privacy_policy_link'] ?? '';
			$policy_statement  = $sub_form_fields['consent_checkbox']['label_value'] ?? '';
			$consent_timestamp = current_time( 'mysql', true );

			$consent_data['ip_address']       = $user_ip_address;
			$consent_data['policy_statement'] = $policy_statement;
			$consent_data['policy_url']       = $policy_url;
			$consent_data['timestamp']        = $consent_timestamp;

			// Get existing consents and append.
			$existing_consents = get_user_meta( $user_id, 'surelywp_lm_order_privacy_consents', true );
			if ( ! is_array( $existing_consents ) ) {
				$existing_consents = array();
			}

			// Remove old same consent if exist.
			if ( ! empty( $existing_consents ) ) {
				foreach ( $existing_consents as $key => $consent ) {
					if ( $consent_data['product_id'] === $consent['product_id'] ) {
						unset( $existing_consents[ $key ] );
					}
				}
			}

			$existing_consents[] = $consent_data;

			update_user_meta( $user_id, 'surelywp_lm_order_privacy_consents', $existing_consents );
		}

		/**
		 * Function to Get option by Name.
		 *
		 * @param string $option_name The option name of setting.
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public static function get_settings_option( $option_name ) {

			$options = get_option( 'surelywp_tk_lm_settings_options' );
			if ( isset( $options[ $option_name ] ) ) {
				$option_value = $options[ $option_name ];
			} else {
				$option_value = '';
			}

			return $option_value;
		}

		/**
		 * Shortcode callback to display a list of downloadable products for the current user.
		 *
		 * This function handles two primary views:
		 * - A single purchase download list if the `model=download` query var is set.
		 * - A general list of downloadable products the customer has access to, optionally filtered to lead magnets.
		 *
		 * @param array $atts {
		 *     Optional. Shortcode attributes.
		 *
		 *     @type string $lead_magnets_only Whether to show only lead magnets. Default 'false'.
		 *     @type string $heading           Custom heading for the download list. Default 'Downloads'.
		 *     @type string $thumbnail         Whether to show thumbnails. Default 'false'.
		 *     @type string $filezone          Whether to show file zone UI. Default 'false'.
		 * }
		 *
		 * @return string Rendered HTML output or message.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.3
		 */
		public function surlywp_tk_sc_download_list( $atts = array() ) {

			// Parse shortcode attributes.
			$atts = shortcode_atts(
				array(
					'lead_magnets_only' => 'false',
					'heading'           => esc_html__( 'Downloads', 'surelywp-toolkit' ),
					'thumbnail'         => 'false',
					'filezone'          => 'false',
				),
				$atts,
				'surelywp_toolkit_downloads_list'
			);

			// Ensure user is logged in.
			if ( ! is_user_logged_in() ) {
				return esc_html__( 'You must be logged in to view downloads.', 'surelywp-toolkit' );
			}
			$output               = '';
			$current_user         = User::current();
			$customer_ids         = array_values( $current_user->customerIds() );
			$lm_products          = self::get_settings_option( 'lm_products' );
			$lead_magnets_product = self::get_settings_option( 'lead_magnets_product' );
			$product_collection   = self::get_settings_option( 'product_collection' );

			// Handle view for a specific purchase's downloads
			if ( isset( $_GET['model'], $_GET['nonce'] ) && 'download' === sanitize_text_field( $_GET['model'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'customer-download' ) ) {

				$id = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';

				$purchase = Purchase::with(
					array(
						'customer',
						'checkout',
						'product',
						'product.downloads',
						'download.media',
					)
				)->find( $id );

				if ( ! $purchase || empty( $purchase->product->downloads->data ) ) {
					return esc_html__( 'No downloads found for this purchase.', 'surelywp-toolkit' );
				}

				// Display back link
				$output .= sprintf(
					'<a style="text-decoration:none;" href="%s">&lt; %s</a><br>',
					esc_url( get_permalink() ),
					esc_html__( 'Back', 'surelywp-toolkit' )
				);

				// Filter out archived downloads
				$downloads = array_filter(
					$purchase->product->downloads->data,
					function ( $download ) {
						return ! $download->archived;
					}
				);

				// Render the download list component $atts['thumbnail'] === 'true'
				$output .= Component::tag( 'sc-downloads-list' )
					->id( 'customer-purchase' )
					->with(
						array(
							'heading'    => esc_html__( 'Downloads', 'surelywp-toolkit' ),
							'customerId' => $purchase->customer->id ?? '',
							'downloads'  => array_values( $downloads ),
							'thumbnail'  => false,
							'filezone'   => true,
						)
					)
					->render( '<span slot="heading">' . esc_html( ! empty( $atts['heading'] ) ? $atts['heading'] : esc_html__( 'Downloads', 'surelywp-toolkit' ) ) . '</span>' );

			} else {
				// Default view: All available downloads
				$query = array(
					'customer_ids' => $customer_ids,
					'page'         => 1,
					'per_page'     => 100,
				);

				// If only lead magnets should be shown
				if ( 'true' === $atts['lead_magnets_only'] ) {
					$products    = Product::where( array( 'product_collection_ids' => $product_collection ) )->with()->get();
					$product_ids = array();

					foreach ( $products as $product ) {
						$product_ids[] = $product->id;
					}

					if ( isset( $lead_magnets_product ) && 'specific' === $lead_magnets_product ) {
						$query['product_ids'] = $lm_products;
					} else {
						$query['product_ids'] = $product_ids;

					}
				}

				// Render dashboard downloads list component
				$output .= Component::tag( 'sc-dashboard-downloads-list' )
					->id( 'customer-downloads-preview' )
					->with(
						array(
							'requestNonce' => wp_create_nonce( 'customer-download' ),
							'isCustomer'   => $current_user->isCustomer(),
							'query'        => $query,
						)
					)
					->render(
						'<span slot="heading">' . esc_html( $atts['heading'] ) . '</span>'
					);
			}

			return wp_kses_post( $output );
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Lm class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	function Surelywp_Tk_Lm() {  // phpcs:ignore
		$instance = Surelywp_Tk_Lm::get_instance();
		return $instance;
	}

}
