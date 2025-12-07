<?php
/**
 * Main class for Misc Settings.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.0
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\Price;
use SureCart\Models\Variant;
use SureCart\Models\Order;
use SureCart\Models\Subscription;
use SureCart\Models\Traits\Period;
use SureCart\Models\Customer;

if ( ! class_exists( 'Surelywp_Tk_Misc' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	class Surelywp_Tk_Misc {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Misc
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 * @return  \Surelywp_Tk_Misc
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

			add_action( 'admin_bar_menu', array( $this, 'surelywp_tk_ql_add_sc_menu' ), 100 );
			add_action( 'admin_bar_menu', array( $this, 'surelywp_tk_ql_add_sc_app_link' ), 100 );

			// Admin Enqueue scipts.
			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_misc_settings_admin_scripts' ) );

			// front Enqueue Scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'surelywp_tk_misc_settings_front_scripts' ) );

			// rename navigation menu name.
			add_filter( 'wp_nav_menu_objects', array( $this, 'surelywp_tk_misc_change_nav_menu_label' ), 9999, 2 );

			// shortcode for display product price.
			add_shortcode( 'surelywp_product_price', array( $this, 'surelywp_product_price_shortcode' ) );
			add_filter( 'surelywp_product_price_shortcode_content', array( $this, 'surelywp_product_price_shortcode_callback' ), 10, 3 );

			// shortcode for display product variant price.
			add_shortcode( 'surelywp_product_variant_price', array( $this, 'surelywp_product_variant_price_shortcode' ) );
			add_filter( 'surelywp_product_variant_price_shortcode_content', array( $this, 'surelywp_product_variant_price_shortcode_callback' ), 10, 3 );

			// Redirection Urls.
			add_action( 'login_redirect', array( $this, 'surelywp_tk_misc_wp_login_redirection' ), 1, 3 );
			add_action( 'sc_login_redirect_url', array( $this, 'surelywp_tk_misc_login_redirection' ), 1, 1 );
			add_action( 'wp_logout', array( $this, 'surelywp_tk_misc_logout_redirection' ), 0, 1 );

			// Back Home Redirection.
			add_filter( 'sc_customer_dashboard_back_home_url', array( $this, 'surelywp_tk_misc_back_home_redirection' ), 10, 1 );

			// Add meta boxes.
			$is_sc_v3_or_higher = surelywp_tk_is_sc_v3_or_higher();
			if ( $is_sc_v3_or_higher ) {
				add_action( 'save_post', array( $this, 'surelywp_tk_misc_save_meta_box_data' ) );
			}

			// Add External Buy now button.
			add_filter( 'render_block', array( $this, 'surelywp_tk_misc_render_blocks' ), 10, 2 );

			// load html.
			add_action( 'init', array( $this, 'surelywp_tk_misc_add_blocks' ), 9 );

			// Add Html blocks on footer.
			add_action( 'admin_footer', array( $this, 'surelywp_tk_misc_add_admin_blocks' ) );
		}

		/**
		 * Add html on footer.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.2
		 */
		public function surelywp_tk_misc_add_admin_blocks() {

			$page   = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$tab    = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$id     = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';

			if ( 'edit' === $action && ! empty( $id ) ) {

				if ( 'sc-products' === $page ) { // Add Price Descriptions enable block on surecart product page.

					$product_id              = $id;
					$enable_external_product = self::get_settings_option( 'enable_external_product' );
					if ( $enable_external_product ) {
						include SURELYWP_TOOLKIT_DIR . '/includes/misc-settings/metaboxes/surelywp-tk-misc-external-products.php';
					}

					$enable_price_desctiption = self::get_settings_option( 'enable_price_desctiption' );

					if ( $enable_price_desctiption ) {

						include SURELYWP_TOOLKIT_DIR . '/includes/misc-settings/metaboxes/surelywp-tk-misc-price-desc.php';
					}
				}
			}
		}

		/**
		 * Individual order inside order again button.
		 * load html.
		 *
		 * @package  Surelywp Toolkit
		 * @since   1.0.2
		 */
		public function surelywp_tk_misc_add_blocks() {

			$model    = isset( $_GET['model'] ) && ! empty( $_GET['model'] ) ? sanitize_text_field( wp_unslash( $_GET['model'] ) ) : '';
			$action   = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$order_id = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';

			if ( 'order' === $model && 'show' === $action && ! empty( $order_id ) ) {
				$enable_order_again_btn = self::get_settings_option( 'enable_order_again_btn' );
				if ( $enable_order_again_btn ) {
					$line_item_data      = array();
					$cart_line_items_url = '';
					$order_obj           = Order::with( array( 'checkout', 'checkout.line_items' ) )->find( $order_id );
					if ( ! is_wp_error( $order_obj ) && ! empty( $order_obj ) ) {
						$line_items_count = $order_obj->checkout->line_items->pagination->count ?? '';
						if ( $line_items_count ) {
							$line_items = $order_obj->checkout->line_items->data ?? '';
							if ( $line_items ) {
								foreach ( $line_items as $item ) {
									$line_item_data[] = array(
										'price_id'   => $item->price ?? '',
										'variant_id' => $item->variant ?? '',
										'quantity'   => $item->quantity ?? 1,
									);
								}
							}
						}

						if ( ! empty( $line_item_data ) ) {
							$last_key = count( $line_item_data ) - 1;
							foreach ( $line_item_data as $key => $data ) {
								$cart_line_items_url .= 'line_items[' . $key . '][price_id]=' . $data['price_id'] . '&line_items[' . $key . '][variant_id]=' . $data['variant_id'] . '&line_items[' . $key . '][quantity]=' . $data['quantity'] . '';
								if ( $key === $last_key ) {
									$cart_line_items_url .= '&no_cart=1';
								} else {
									$cart_line_items_url .= '&';
								}
							}
						}
					}

					$checkout_url = SureCart::pages()->url( 'checkout' );
					$buy_url      = $checkout_url . '?' . $cart_line_items_url;
					?>
					<div class="surelywp-tk-order-again-btn hidden" id="surelywp-tk-order-again-btn" style="display: inline-block; margin-left: 5px;">
						<sc-button type="primary" href="<?php echo esc_url( $buy_url ); ?>"><sc-icon slot="prefix" name="shopping-cart" class="hydrated"></sc-icon><?php esc_html_e( 'Order Again', 'surelywp-toolkit' ); ?></sc-button>
					</div>
					<?php
				}
			}
		}

		/**
		 * Get external buy now button html.
		 *
		 * @param int $post_id The id of the post.
		 * @package  Surelywp Toolkit
		 * @since   1.1.1
		 */
		public static function surelywp_tk_misc_get_external_buy_btn_html( $post_id ) {

			$prefix                   = SURELYWP_TOOLKIT_META_PREFIX;
			$external_product_btn_url = get_post_meta( $post_id, $prefix . 'misc_external_product_url', true );
			$buy_btn_html             = '';

			if ( $external_product_btn_url ) {

				$external_product_btn_text = get_post_meta( $post_id, $prefix . 'misc_external_product_btn_text', true );
				if ( empty( $external_product_btn_text ) ) {
					$external_product_btn_text = esc_html__( 'Buy Now', 'surelywp-toolkit' );
				}
				$external_product_open_new_tab = get_post_meta( $post_id, $prefix . 'misc_external_product_open_new_tab', true );
				ob_start();
				?>
				<div class="surelywp-tk-misc-external-buy-button">
					<sc-button type="primary" target="<?php echo ! empty( $external_product_open_new_tab ) ? '_blank' : ''; ?>" full href="<?php echo esc_url( $external_product_btn_url ); ?>"><?php echo esc_html( $external_product_btn_text ); ?></sc-button>
				</div>
				<?php
				$buy_btn_html = ob_get_clean();
			}

			return $buy_btn_html;
		}

		/**
		 * Get product price description.
		 *
		 * @param int $post_id The post id.
		 * @param int $price_id product object.
		 * @package  Surelywp Toolkit
		 * @since   1.2
		 */
		public static function surelywp_tk_misc_get_price_desc( $post_id, $price_id ) {

			$prefix                       = SURELYWP_TOOLKIT_META_PREFIX;
			$misc_price_desc              = get_post_meta( $post_id, $prefix . 'misc_price_desc', true );
			$misc_price_desc_display_type = get_post_meta( $post_id, $prefix . 'misc_price_desc_display_type', true );
			if ( empty( $misc_price_desc_display_type ) ) {
				$misc_price_desc_display_type = 'display_selected';
			}
			$price_desc = ! empty( $misc_price_desc ) && isset( $misc_price_desc[ $price_id ] ) ? $misc_price_desc[ $price_id ] : '';

			$price_desc_html = '';
			if ( ! empty( $misc_price_desc ) && $price_desc ) {
				ob_start();
				?>
				<div class="surelywp-tk-misc-price-desc hidden" data-display-type="<?php echo esc_attr( $misc_price_desc_display_type ); ?>">
					<?php echo esc_html( $price_desc ); ?>
				</div>
				<?php
				$price_desc_html = ob_get_clean();
			}

			return $price_desc_html;
		}
		/**
		 * Function to display blocks.
		 *
		 * @param string $block_content The Content of block.
		 * @param array  $block The name of block.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.1.1
		 */
		public function surelywp_tk_misc_render_blocks( $block_content, $block ) {

			if ( 'surecart/product-buy-buttons' === $block['blockName'] ) {

				// Add External Buy now button.
				$enable_external_product = self::get_settings_option( 'enable_external_product' );
				if ( $enable_external_product ) {
					global $post;
					$post_id = $post->ID ?? '';
					if ( $post_id ) {
						$buy_btn_html = self::surelywp_tk_misc_get_external_buy_btn_html( $post_id );
						if ( ! empty( $buy_btn_html ) ) {
							return $buy_btn_html;
						}
					}
				}
			} elseif ( 'surecart/product-price-choice-template' === $block['blockName'] ) {

				$enable_price_desctiption = self::get_settings_option( 'enable_price_desctiption' );

				if ( $enable_price_desctiption ) {
					global $post;
					$post_id = $post->ID ?? '';
					if ( $post_id ) {

						preg_match( '/data-wp-context=\'(.*?)\'/s', $block_content, $matches );

						if ( ! empty( $matches[1] ) ) {

							// Decode the JSON string.
							$context  = json_decode( html_entity_decode( $matches[1] ), true );
							$price_id = $context['price']['id'] ?? '';

							if ( $price_id ) {
								$price_desc = self::surelywp_tk_misc_get_price_desc( $post_id, $price_id );
								if ( ! empty( $price_desc ) ) {
									return $block_content . $price_desc;
								}
							}
						}
					}
				}
			} elseif ( 'surecart/product-selected-price-fees' === $block['blockName'] ) {

				$enable_price_desctiption = self::get_settings_option( 'enable_price_desctiption' );

				if ( $enable_price_desctiption ) {

					global $post;
					$post_id = $post->ID ?? '';
					if ( $post_id ) {
						$sc_product = get_post_meta( $post_id, 'product', true );
						$price_id   = $sc_product['prices']['data'][0]['id'] ?? '';
						if ( $price_id ) {
							$price_desc = self::surelywp_tk_misc_get_price_desc( $post_id, $price_id );
							if ( ! empty( $price_desc ) ) {
								return $block_content . $price_desc;
							}
						}
					}
				}
			}
			return $block_content;
		}


		/**
		 * Save meta boxe data.
		 *
		 * @param int $post_id The id of the post.
		 * @package  Surelywp Toolkit
		 * @since   1.1.1
		 */
		public function surelywp_tk_misc_save_meta_box_data( $post_id ) {

			global $post_type, $surelywp_model;

			$prefix = SURELYWP_TOOLKIT_META_PREFIX;

			$post_type_object = get_post_type_object( $post_type );

			// Check for which post type we need to add the meta box.
			$pages = array( 'sc_product' );

			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )                // Check Autosave.
			|| ( ! isset( $_POST['post_ID'] ) || $post_id != $_POST['post_ID'] )        // Check Revision.
			|| ( ! in_array( $post_type, $pages ) )              // Check if current post type is supported.
			|| ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) )
			) { // check nonce.
				return $post_id;
			}

			$enable_external_product = self::get_settings_option( 'enable_external_product' );
			if ( $enable_external_product && wp_verify_nonce( $_POST['surelywp_tk_misc_ep_nonce'], 'surelywp_tk_misc_ep_action' ) ) {

				$external_product_url      = isset( $_POST[ $prefix . 'misc_external_product_url' ] ) && ! empty( $_POST[ $prefix . 'misc_external_product_url' ] ) ? sanitize_url( wp_unslash( $_POST[ $prefix . 'misc_external_product_url' ] ) ) : '';
				$external_product_btn_text = isset( $_POST[ $prefix . 'misc_external_product_btn_text' ] ) && ! empty( $_POST[ $prefix . 'misc_external_product_btn_text' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'misc_external_product_btn_text' ] ) ) : '';
				$external_product_new_tab  = isset( $_POST[ $prefix . 'misc_external_product_open_new_tab' ] ) && ! empty( $_POST[ $prefix . 'misc_external_product_open_new_tab' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'misc_external_product_open_new_tab' ] ) ) : '';

				update_post_meta( $post_id, $prefix . 'misc_external_product_url', $external_product_url );
				update_post_meta( $post_id, $prefix . 'misc_external_product_btn_text', $external_product_btn_text );
				update_post_meta( $post_id, $prefix . 'misc_external_product_open_new_tab', $external_product_new_tab );
			}

			$enable_price_desctiption = self::get_settings_option( 'enable_price_desctiption' );
			if ( $enable_price_desctiption && wp_verify_nonce( $_POST['surelywp_tk_misc_pd_nonce'], 'surelywp_tk_misc_pd_action' ) ) {

				$misc_price_descriptions      = isset( $_POST[ $prefix . 'misc_price_desc' ] ) && ! empty( $_POST[ $prefix . 'misc_price_desc' ] ) ? $surelywp_model->surelywp_escape_slashes_deep( $_POST[ $prefix . 'misc_price_desc' ] ) : '';
				$misc_price_desc_display_type = isset( $_POST[ $prefix . 'misc_price_desc_display_type' ] ) && ! empty( $_POST[ $prefix . 'misc_price_desc_display_type' ] ) ? $surelywp_model->surelywp_escape_attr( $_POST[ $prefix . 'misc_price_desc_display_type' ] ) : '';

				update_post_meta( $post_id, $prefix . 'misc_price_desc', $misc_price_descriptions );
				update_post_meta( $post_id, $prefix . 'misc_price_desc_display_type', $misc_price_desc_display_type );

			}
		}

		/**
		 * Redirection url for back home in customer dashboard.
		 *
		 * @param string $home_url the home page url.
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_misc_back_home_redirection( $home_url ) {

			$is_enable_back_home_redirection = self::get_settings_option( 'is_enable_back_home_redirection' );
			if ( $is_enable_back_home_redirection ) {
				$back_home_redirect_url = self::get_settings_option( 'back_home_redirect_url' );
				if ( $back_home_redirect_url ) {
					return esc_url( $back_home_redirect_url );
				}
			}

			return $home_url;
		}

		/**
		 * Redirect user after login.
		 *
		 * @param string $default_url url for redirect.
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_misc_login_redirection( $default_url ) {

			$user       = wp_get_current_user();
			$user_roles = $user->roles ?? array();

			$redirection_url = self::surelywp_tk_misc_login_redirect( $user_roles );
			if ( ! empty( $redirection_url ) ) {

				return $redirection_url;
			}

			return $default_url;
		}

		/**
		 * Redirect users after login based on specific conditions.
		 *
		 * This function hooks into the 'login_redirect' filter and modifies the
		 * redirection URL after a user logs in.
		 *
		 * @param string           $redirect_to The redirect destination URL.
		 * @param string           $request     The requested redirect URL (if available).
		 * @param WP_User|WP_Error $user WP_User object if login was successful, WP_Error if failed.
		 *
		 * @return string The URL to redirect the user to.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.2
		 */
		public function surelywp_tk_misc_wp_login_redirection( $redirect_to, $request, $user ) {

			$user_roles      = $user->roles ?? array();
			$redirection_url = self::surelywp_tk_misc_login_redirect( $user_roles );
			if ( ! empty( $redirection_url ) ) {
				wp_redirect( $redirection_url );
				exit;
			}

			return $redirect_to;
		}


		/**
		 * Redirect user when login.
		 *
		 * @param array $user_roles logged out user id.
		 * @package Toolkit For SureCart
		 * @since 1.2
		 */
		public static function surelywp_tk_misc_login_redirect( $user_roles ) {

			if ( empty( $user_roles ) ) {
				return '';
			}

			if ( is_array( $user_roles ) && in_array( 'administrator', $user_roles, true ) ) {
				return '';
			}

			$login_redirect_url               = '';
			$is_enable_role_based_redirection = self::get_settings_option( 'is_enable_role_based_redirection' );

			if ( $is_enable_role_based_redirection ) {

				$redirection_urls = self::get_settings_option( 'redirection_urls' );

				if ( ! empty( $redirection_urls ) ) {
					foreach ( $user_roles as $role ) {
						$login_redirect_url = $redirection_urls[ $role ]['login_redirect_url'] ?? '';
						if ( isset( $login_redirect_url ) && ! empty( $login_redirect_url ) ) {
							return $login_redirect_url;
						}
					}
				}
			}

			$login_redirect_url          = self::get_settings_option( 'login_redirect_url' );
			$is_enable_login_redirection = self::get_settings_option( 'is_enable_login_redirection' );

			if ( ! empty( $is_enable_login_redirection ) && ! empty( $login_redirect_url ) ) {

				return $login_redirect_url;
			}
		}


		/**
		 * Redirect user after logout.
		 *
		 * @param int $user_id logged out user id.
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_misc_logout_redirection( $user_id ) {

			$user = get_user_by( 'ID', $user_id );
			if ( ! empty( $user->roles ) && is_array( $user->roles ) && in_array( 'administrator', $user->roles, true ) ) {
				return;
			}

			$logout_redirect_url          = self::get_settings_option( 'logout_redirect_url' );
			$is_enable_logout_redirection = self::get_settings_option( 'is_enable_logout_redirection' );

			if ( ! empty( $is_enable_logout_redirection ) && ! empty( $logout_redirect_url ) ) {

				wp_redirect( esc_url( $logout_redirect_url ) );
				exit();
			}
		}

		/**
		 * Function to render coupon code and timer
		 *
		 * @param string $price_id The price id.
		 * @param bool   $show_strikethrough is display scratch amount.
		 * @param string $sale_text The sale text.
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_product_price_shortcode_callback( $price_id, $show_strikethrough, $sale_text ) {

			if ( empty( $price_id ) ) {
				return;
			}

			$price_obj = Price::find( $price_id );

			if ( is_wp_error( $price_obj ) || empty( $price_obj ) ) {
				return;
			}
			$paid_amount          = $price_obj->amount ?? '';
			$currency             = $price_obj->currency ?? 'usd';
			$scratch_amount       = $price_obj->scratch_amount ?? '';
			$scratch_amount_value = '';
			if ( ! empty( $scratch_amount ) && ! empty( $show_strikethrough ) && 'true' === $show_strikethrough ) {
				$scratch_amount_value = $scratch_amount;
			}
			ob_start();
			?>
			<sc-price amount="<?php echo (float) esc_html( $paid_amount ); ?>" scratch-amount="<?php echo esc_html( $scratch_amount_value ); ?>" currency="<?php echo esc_html( $currency ); ?>" sale-text="<?php echo esc_html( $sale_text ); ?>" class="hydrated"></sc-price>
			<?php
			return ob_get_clean();
		}

		/**
		 * Shortcode for display prodcut price by price id.
		 *
		 * [surelywp_product_price]
		 *
		 * @param string $attr The attributes of shortcode.
		 * @param string $content The content of shortcode.
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_product_price_shortcode( $attr, $content ) {

			$attr = shortcode_atts(
				array(
					'id'                 => '',
					'show_strikethrough' => '',
					'sale_text'          => '',
				),
				$attr
			);

			$id                 = $attr['id'] ?? '';
			$show_strikethrough = $attr['show_strikethrough'] ?? '';
			$sale_text          = $attr['sale_text'] ?? '';

			$content     .= apply_filters( 'surelywp_product_price_shortcode_content', $id, $show_strikethrough, $sale_text );
			$content_main = '<div class="surlywp-tk-product-price">' . do_shortcode( $content ) . '</div>';
			return $content_main;
		}

		/**
		 * Function to render coupon code and timer
		 *
		 * @param string $variant_id The variant id for the view.
		 * @param bool   $show_strikethrough is display scratch amount.
		 * @param string $sale_text The sale text.
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_product_variant_price_shortcode_callback( $variant_id, $show_strikethrough, $sale_text ) {

			if ( empty( $variant_id ) ) {
				return;
			}

			$variant_obj = Variant::with( array( 'product', 'product.price' ) )->find( $variant_id );

			if ( is_wp_error( $variant_obj ) || empty( $variant_obj ) || ( $variant_obj->product->metrics->prices_count > 1 ) ) { // products with a single price only short code work.
				return;
			}
			$variant_amount   = $variant_obj->amount ?? '';
			$variant_currency = $variant_obj->currency ?? 'usd';

			if ( empty( $variant_amount ) ) {
				$variant_amount = $variant_obj->product->prices->data[0]->amount;
			}

			$variant_scratch_amount       = $variant_obj->product->prices->data[0]->scratch_amount ?? '';
			$variant_scratch_amount_value = '';
			if ( ! empty( $variant_scratch_amount ) && ! empty( $show_strikethrough ) && 'true' === $show_strikethrough ) {
				$variant_scratch_amount_value = $variant_scratch_amount;
			}
			ob_start();
			?>
			<sc-price amount="<?php echo (float) esc_html( $variant_amount ); ?>" scratch-amount="<?php echo esc_html( $variant_scratch_amount_value ); ?>" currency="<?php echo esc_html( $variant_currency ); ?>" sale-text="<?php echo esc_html( $sale_text ); ?>" class="hydrated"></sc-price>
			<?php
			return ob_get_clean();
		}

		/**
		 * Shortcode for display prodcut variant by variant id.
		 *
		 * [surelywp_product_variant_price]
		 *
		 * @param string $attr The attributes of shortcode.
		 * @param string $content The content of shortcode.
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_product_variant_price_shortcode( $attr, $content ) {

			$attr = shortcode_atts(
				array(
					'id'                 => '',
					'show_strikethrough' => '',
					'sale_text'          => '',
				),
				$attr
			);

			$id                 = $attr['id'] ?? '';
			$show_strikethrough = $attr['show_strikethrough'] ?? '';
			$sale_text          = $attr['sale_text'] ?? '';

			$content     .= apply_filters( 'surelywp_product_variant_price_shortcode_content', $id, $show_strikethrough, $sale_text );
			$content_main = '<div class="surlywp-tk-product-variant-price">' . do_shortcode( $content ) . '</div>';
			return $content_main;
		}

		/**
		 * Function to change the navigation menu label.
		 *
		 * @param array    $items An array of menu item objects.
		 * @param stdClass $args An object containing the arguments passed to `wp_nav_menu()`.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_misc_change_nav_menu_label( $items, $args ) {

			$enable_rename_name = self::get_settings_option( 'enable_rename_name' );
			$logout_title       = self::get_settings_option( 'logout_title' );

			if ( $enable_rename_name && ! empty( $logout_title ) ) {

				foreach ( $items as $item ) {

					$item_classes = $item->classes ?? array();
					if ( in_array( 'surelywp-rename-menu', $item_classes, true ) ) {
						if ( ! is_user_logged_in() ) {

							$item->title = esc_html( $logout_title );
						}
					}
				}
			}
			return $items;
		}

		/**
		 * Function to enqueue scripts and styles for the front end.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_misc_settings_admin_scripts() {

			$tab    = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			$page   = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$id     = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
			$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

			// register common style.
			wp_register_style( 'surelywp-tk-ql', SURELYWP_TOOLKIT_ASSETS_URL . '/css/quick-links/surelywp-tk-ql.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_style( 'surelywp-tk-ql-min', SURELYWP_TOOLKIT_ASSETS_URL . '/css/quick-links/surelywp-tk-ql.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );

			wp_register_style( 'surelywp-tk-misc-backend', SURELYWP_TOOLKIT_ASSETS_URL . '/css/misc-settings/surelywp-tk-misc-backend.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_style( 'surelywp-tk-misc-backend-min', SURELYWP_TOOLKIT_ASSETS_URL . '/css/misc-settings/surelywp-tk-misc-backend.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );

			// misc settings backend js.
			wp_register_script( 'surelywp-tk-misc-backend', SURELYWP_TOOLKIT_ASSETS_URL . '/js/misc-settings/surelywp-tk-misc-backend.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, false );
			wp_register_script( 'surelywp-tk-misc-backend-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/misc-settings/surelywp-tk-misc-backend.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, false );

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			$is_enable_surecart_menu     = self::get_settings_option( 'surecart_menu' );
			$is_enable_surecart_app_link = self::get_settings_option( 'surecart_app_link' );

			if ( ( $is_enable_surecart_menu || $is_enable_surecart_app_link ) ) {

				wp_enqueue_style( 'surelywp-tk-ql' . $min_file );
			}

			$localize = array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'misc_ajax_nonce' => wp_create_nonce( 'misc-ajax-nonce' ),
			);

			// misc settings js enqueue.
			$allow_pages = array( 'sc-products', 'sc-orders' );
			if ( 'surelywp_tk_misc_settings' === $tab || in_array( $page, $allow_pages, true ) ) {

				if ( 'surelywp_tk_misc_settings' === $tab ) {

					wp_enqueue_editor();
					wp_enqueue_script( 'jquery-ui-core' );
					wp_enqueue_script( 'jquery-ui-sortable' );
				}

				$enable_recovered_badge = self::get_settings_option( 'enable_recovered_badge' );
				$enable_retry_btn       = self::get_settings_option( 'enable_retry_btn' );

				if ( 'sc-orders' === $page && 'edit' === $action ) {
					$localize['order_id']         = $id;
					$localize['enable_retry_btn'] = $enable_retry_btn;
				}
				$localize['enable_recovered_badge'] = $enable_recovered_badge;

				wp_enqueue_script( 'surelywp-tk-misc-backend' . $min_file );
				wp_localize_script( 'surelywp-tk-misc-backend' . $min_file, 'tk_misc_backend_ajax_object', $localize );

			}

			// admin product page.
			if ( ( 'sc-products' === $page ) ) {

				wp_enqueue_style( 'surelywp-tk-misc-backend' . $min_file );
			}
		}

		/**
		 * Function to enqueue scripts and styles for the front end.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_misc_settings_front_scripts() {

			$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
			$model  = isset( $_GET['model'] ) && ! empty( $_GET['model'] ) ? sanitize_text_field( wp_unslash( $_GET['model'] ) ) : '';
			$id     = isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';

			// misc settings front js.
			wp_register_script( 'surelywp-tk-misc-front', SURELYWP_TOOLKIT_ASSETS_URL . '/js/misc-settings/surelywp-tk-misc-front.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-misc-front-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/misc-settings/surelywp-tk-misc-front.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			wp_register_style( 'surelywp-tk-misc-front', SURELYWP_TOOLKIT_ASSETS_URL . '/css/misc-settings/surelywp-tk-misc-front.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_style( 'surelywp-tk-misc-front-min', SURELYWP_TOOLKIT_ASSETS_URL . '/css/misc-settings/surelywp-tk-misc-front.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			$localize = array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'misc_ajax_nonce' => wp_create_nonce( 'misc-ajax-nonce' ),
			);

			// misc settings front js enqueue.
			if ( 'show' === $action && ( 'order' === $model || 'download' === $model ) && ! empty( $id ) ) {

				wp_localize_script( 'surelywp-tk-misc-front' . $min_file, 'tk_misc_front_ajax_object', $localize );

				$enable_order_again_btn = self::get_settings_option( 'enable_order_again_btn' );
				if ( $enable_order_again_btn ) {
					wp_enqueue_script( 'surelywp-tk-misc-front' . $min_file );
				}

				if ( 'download' === $model ) {
					wp_enqueue_script( 'surelywp-tk-misc-front' . $min_file );
				}
			}

			// front styles.
			$product = surelywp_tk_get_current_product();
			if ( $product ) {
				wp_enqueue_style( 'surelywp-tk-misc-front' . $min_file );
				wp_enqueue_script( 'surelywp-tk-misc-front' . $min_file );
			}
		}


		/**
		 * Function to add surecart app link on admin bar.
		 *
		 * @param object $wp_admin_bar The wp admin bar.
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_ql_add_sc_app_link( $wp_admin_bar ) {

			// skip to new "Open SureCart App" menu.
			$is_enable_surecart_menu = self::get_settings_option( 'surecart_menu' );
			if ( $is_enable_surecart_menu ) {
				return;
			}

			$is_enable_surecart_app_link = self::get_settings_option( 'surecart_app_link' );
			if ( $is_enable_surecart_app_link ) {
				// Surecart logo.
				$logo = file_get_contents( plugin_dir_path( SURECART_PLUGIN_FILE ) . 'images/icon.svg' );
				$logo = 'data:image/svg+xml;base64,' . base64_encode( $logo );
				$wp_admin_bar->add_node(
					array(
						'id'    => 'surelywp-tk-ql-surecart',
						'title' => '<img src="' . $logo . '" style="height: 20px; vertical-align: middle; margin-right:6px;filter: invert(1) brightness(0.6);" alt="'.esc_html__('SureCart Icon', 'surelywp-toolkit').'" /> ' . esc_html__( 'Open SureCart App', 'surelywp-toolkit' ) . '</span>', // Menu name (SureCart).
						'href'  => esc_url( surelywp_tk_get_sc_app_url() ),
						'meta'  => array(
							'target' => '_blank',
						),
					)
				);
			}
		}

		/**
		 * Function to add surecart menu on admin bar.
		 *
		 * @param object $wp_admin_bar The wp admin bar.
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_ql_add_sc_menu( $wp_admin_bar ) {

			// Check if the user has access to the admin menu.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$is_enable_surecart_menu = self::get_settings_option( 'surecart_menu' );
			if ( ! $is_enable_surecart_menu ) {
				return;
			}

			global $submenu, $menu;

			// get menu and submenu menu from the transiate.
			if ( empty( $menu ) && empty( $submenu ) ) {

				$admin_menu    = get_transient( 'surelywp_admin_menu' ); //phpcs:ignore
				$admin_submenu = get_transient( 'surelywp_admin_submenu' ); //phpcs:ignore

			} elseif ( ! empty( $menu ) && ! empty( $submenu ) ) {

				$admin_menu    = $menu;
				$admin_submenu = $submenu;

				$is_admin_menu_save    = get_transient( 'surelywp_admin_menu' ); //phpcs:ignore

				// Save the transient if expire.
				if ( ! $is_admin_menu_save ) {

					// save admin on transient.
					set_transient( 'surelywp_admin_menu', $menu, 3600 * 24 ); // for 24 hours.

					// save admin submenu on transient.
					set_transient( 'surelywp_admin_submenu', $submenu, 3600 * 24 ); // for 24 hours.
				}
			}

			// Find SureCart main menu in the global $menu array.
			$surecart_menu_slug = 'sc-dashboard';
			$surecart_main_slug = 'admin.php?page=';

			if ( SureCart::account()->has_checklist && current_user_can( 'manage_options' ) ) {
				$surecart_menu_slug = 'sc-onboarding-checklist';
			}
			// Surecart logo.
			$logo = file_get_contents( plugin_dir_path( SURECART_PLUGIN_FILE ) . 'images/icon.svg' );
			$logo = 'data:image/svg+xml;base64,' . base64_encode( $logo );

			// Loop through the admin menu and find the SureCart menu.
			if ( ! empty( $admin_menu ) ) {

				foreach ( $admin_menu as $item ) {
					if ( strpos( $item[2], $surecart_menu_slug ) !== false ) {
						// Add SureCart main menu to the admin bar.
						$wp_admin_bar->add_node(
							array(
								'id'    => 'surelywp-tk-ql-surecart',
								'title' => '<img src="' . $logo . '" style="height: 20px; vertical-align: middle; margin-right:6px;filter: invert(1) brightness(0.6);" alt="'.esc_html__( 'SureCart Icon', 'surelywp-toolkit' ) .'" /> ' . esc_html( $item[0] ) . '</span>', // Menu name (SureCart).
								'href'  => admin_url( $surecart_main_slug . $item[2] ),
							)
						);
						break;
					}
				}
			}

			$order_sub_menu = array(
				array(
					'id'    => 'sc-abandoned-checkouts',
					'title' => esc_html__( 'Abandoned', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-abandoned-checkouts' ),
				),
				array(
					'id'    => 'sc-invoices',
					'title' => esc_html__( 'Invoices', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-invoices' ),
				),
			);

			$product_sub_menu = array(
				array(
					'id'    => 'sc-product-collections',
					'title' => esc_html__( 'Collections', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-product-collections' ),
				),
				array(
					'id'    => 'sc-bumps',
					'title' => esc_html__( 'Order Bumps', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-bumps' ),
				),
				array(
					'id'    => 'sc-upsell-funnels',
					/* translators: 1: opening <span> tag, 2: closing </span> tag. */
					'title' => sprintf( esc_html__( 'Upsells %1$sBeta%2$s', 'surelywp-toolkit' ), '<span class="awaiting-mod">', '</span>' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-upsell-funnels' ),
				),
				array(
					'id'    => 'sc-product-groups',
					'title' => esc_html__( 'Upgrade Groups', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-product-groups' ),
				),
			);

			$subscriptions_sub_menu = array(
				array(
					'id'    => 'sc-cancellation-insights',
					'title' => esc_html__( 'Cancellations', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-cancellation-insights' ),
				),
			);
			$affiliates_sub_menu    = array(
				array(
					'id'    => 'sc-affiliate-requests',
					'title' => esc_html__( 'Requests', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-affiliate-requests' ),
				),
				array(
					'id'    => 'sc-affiliate-clicks',
					'title' => esc_html__( 'Clicks', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-affiliate-clicks' ),
				),
				array(
					'id'    => 'sc-affiliate-referrals',
					'title' => esc_html__( 'Referrals', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-affiliate-referrals' ),
				),
				array(
					'id'    => 'sc-affiliate-payouts',
					'title' => esc_html__( 'Payouts', 'surelywp-toolkit' ),
					'url'   => admin_url( $surecart_main_slug . 'sc-affiliate-payouts' ),
				),
			);

			$all_sub_menus = array_merge( $order_sub_menu, $product_sub_menu, $subscriptions_sub_menu, $affiliates_sub_menu );

			// Extract all 'id' values from $all_sub_menus.
			$all_sub_menu_ids = array_column( $all_sub_menus, 'id' );

			// Now, add Surecart submenus dynamically.
			if ( isset( $admin_submenu[ $surecart_menu_slug ] ) && ! empty( $admin_submenu[ $surecart_menu_slug ] ) ) {

				// Add surecart app link.
				$is_enable_surecart_app_link = self::get_settings_option( 'surecart_app_link' );
				if ( $is_enable_surecart_app_link ) {
					$wp_admin_bar->add_node(
						array(
							'id'     => 'tk-submenu-surecart-app-link', // Unique ID for submenu.
							'parent' => 'surelywp-tk-ql-surecart', // Parent menu.
							'title'  => esc_html__( 'Open SureCart App', 'surelywp-toolkit' ), // Submenu name.
							'href'   => esc_url( surelywp_tk_get_sc_app_url() ), // Submenu link.
							'meta'   => array(
								'target' => '_blank',
							),
						)
					);
				}

				foreach ( $admin_submenu[ $surecart_menu_slug ] as $key => $sub_item ) {

					// Skip the surecart submenu's submenu.
					if ( in_array( $sub_item[2], $all_sub_menu_ids, true ) ) {
						continue;
					}

					// Determine the submenu link based on the specific conditions.
					if ( strpos( $sub_item[2], '.php' ) !== false ) {
						// Handle URLs that include 'post.php'.
						$submenu_link = admin_url( $sub_item[2] ); // Directly use the provided URL.
					} else {
						// For other URLs, append to the main slug.
						$submenu_link = admin_url( $surecart_main_slug . $sub_item[2] );
					}

					$wp_admin_bar->add_node(
						array(
							'id'     => 'tk-submenu-' . strtolower( str_replace( ' ', '-', wp_strip_all_tags( $sub_item[0] ) ) ), // Unique ID for submenu.
							'parent' => 'surelywp-tk-ql-surecart', // Parent menu.
							'title'  => wp_strip_all_tags( $sub_item[0] ), // Submenu name.
							'href'   => $submenu_link, // Submenu link.
						)
					);
				}

				// Add order submenus inside submenus.
				foreach ( $order_sub_menu as $sub_menu ) {

					$wp_admin_bar->add_node(
						array(
							'id'     => 'tk-submenu-' . $sub_menu['id'], // Unique ID for submenu.
							'parent' => 'tk-submenu-orders', // Parent menu.
							'title'  => $sub_menu['title'], // Submenu name.
							'href'   => $sub_menu['url'], // Submenu link.
						)
					);
				}

				// Add products submenus inside submenus.
				foreach ( $product_sub_menu as $sub_menu ) {

					$wp_admin_bar->add_node(
						array(
							'id'     => 'tk-submenu-' . $sub_menu['id'], // Unique ID for submenu.
							'parent' => 'tk-submenu-products', // Parent menu.
							'title'  => $sub_menu['title'], // Submenu name.
							'href'   => $sub_menu['url'], // Submenu link.
						)
					);
				}

				// Add Subscriptions submemus inside submenus.
				foreach ( $subscriptions_sub_menu as $sub_menu ) {

					$wp_admin_bar->add_node(
						array(
							'id'     => 'tk-submenu-' . $sub_menu['id'], // Unique ID for submenu.
							'parent' => 'tk-submenu-subscriptions', // Parent menu.
							'title'  => $sub_menu['title'], // Submenu name.
							'href'   => $sub_menu['url'], // Submenu link.
						)
					);
				}

				// Add Affiliates submemus inside submenus.
				foreach ( $affiliates_sub_menu as $sub_menu ) {

					$wp_admin_bar->add_node(
						array(
							'id'     => 'tk-submenu-' . $sub_menu['id'], // Unique ID for submenu.
							'parent' => 'tk-submenu-affiliates', // Parent menu.
							'title'  => $sub_menu['title'], // Submenu name.
							'href'   => $sub_menu['url'], // Submenu link.
						)
					);
				}
			}
		}

		/**
		 * Function to Get option by Name.
		 *
		 * @param string $option_name The option name of setting.
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function get_settings_option( $option_name ) {

			$options = get_option( 'surelywp_tk_misc_settings_options' );
			if ( isset( $options[ $option_name ] ) ) {
				$option_value = $options[ $option_name ];
			} else {
				$option_value = '';
			}

			return $option_value;
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Misc class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function Surelywp_Tk_Misc() {  // phpcs:ignore
		$instance = Surelywp_Tk_Misc::get_instance();
		return $instance;
	}
}
