<?php
/**
 * Main class for Misc Settings.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.1
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\Product;
use SureCart\Models\ProductCollection;
use SureCart\Models\Price;
use SureCart\Models\Variant;

use function PHPSTORM_META\map;

if ( ! class_exists( 'Surelywp_Tk_Vm' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.1
	 */
	class Surelywp_Tk_Vm {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Vm
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 * @return  \Surelywp_Tk_Vm
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
		 * @since   1.0.1
		 */
		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'surelywp_tk_vm_front_script' ) );
			add_filter( 'render_block', array( $this, 'surelywp_tk_vm_display_notice' ), 11, 2 );
			add_action( 'wp_footer', array( $this, 'surelywp_tk_vm_display_sitewide_banner' ) );
			add_action( 'template_redirect', array( $this, 'surelywp_tk_vm_redirect_checkout_page' ) );
			add_filter( 'surecart/request/args', array( $this, 'surelywp_tk_vm_manage_add_to_cart' ), 10, 2 );
			add_shortcode( 'surelywp_vacation_notice', array( $this, 'surelywp_tk_vm_notice_shortcode' ) );
			add_action( 'surelywp_sv_customer_view_after_heading', array( $this, 'surelywp_tk_vm_add_sv_notice' ), 10, 1 );
			add_action( 'surelywp_sp_customer_view_after_heading', array( $this, 'surelywp_tk_vm_add_sp_notice' ), 10, 1 );
		}

		/**
		 * Add notice on customer service view.
		 *
		 * @param array $service The service data.
		 * @package Toolkit For SureCart
		 * @since 1.2
		 */
		public function surelywp_tk_vm_add_sv_notice( $service ) {

			$product_id = $service->product_id ?? '';

			if ( $product_id ) {

				$vacation_id = self::surelywp_tk_vm_get_active_vacation_id();

				if ( $vacation_id ) {

					$is_product_have_vacation = self::surelywp_tk_vm_is_product_have_vacation( $vacation_id, $product_id );

					if ( $is_product_have_vacation ) {

						// is show notice enable.
						$show_notice_on_service = self::get_settings_option( $vacation_id, 'show_notice_on_service' );
						if ( $show_notice_on_service ) {
							$notice_message = apply_filters( 'surelywp_tk_vacation_mode_notice_message', self::get_vacation_notice_message( $vacation_id ) );
							ob_start();
							?>
							<div class="note">
								<sc-alert open type="primary">
									<?php echo esc_html( $notice_message ); ?>
								</sc-alert>
							</div>
							<?php
							$notice_message_html = ob_get_clean();
							echo wp_kses_post( $notice_message_html );
						}
					}
				}
			}
		}

		/**
		 * Add notice on customer service view.
		 *
		 * @param array $support The service data.
		 * @package Toolkit For SureCart
		 * @since 1.2
		 */
		public function surelywp_tk_vm_add_sp_notice( $support ) {

			$product_id = $support->product_id ?? '';

			if ( $product_id ) {

				$vacation_id = self::surelywp_tk_vm_get_active_vacation_id();

				if ( $vacation_id ) {

					$is_product_have_vacation = self::surelywp_tk_vm_is_product_have_vacation( $vacation_id, $product_id );

					if ( $is_product_have_vacation ) {

						// is show notice enable.
						$show_notice_on_support = self::get_settings_option( $vacation_id, 'show_notice_on_support' );
						if ( $show_notice_on_support ) {
							$notice_message = apply_filters( 'surelywp_tk_vacation_mode_notice_message', self::get_vacation_notice_message( $vacation_id ) );
							ob_start();
							?>
							<div class="note">
								<sc-alert open type="primary">
									<?php echo esc_html( $notice_message ); ?>
								</sc-alert>
							</div>
							<?php
							$notice_message_html = ob_get_clean();
							echo wp_kses_post( $notice_message_html );
						}
					}
				}
			}
		}

		/**
		 * Shortcode for Vacation Notice to display on any page.
		 *
		 * [surelywp_vacation_notice]
		 *
		 * @param string $attr The attributes of shortcode.
		 * @param string $content The content of shortcode.
		 * @package Toolkit For SureCart
		 * @since 1.0.3
		 */
		public function surelywp_tk_vm_notice_shortcode( $attr, $content ) {

			$vacation_id = self::surelywp_tk_vm_get_active_vacation_id();

			$content_main = '';
			if ( $vacation_id ) {

				// enqueue style.
				self::surelywp_tk_vm_script_enqueue();

				$content .= apply_filters( 'surelywp_tk_vm_notice_shortcode_content', self::get_vacation_notice_html( $vacation_id ) );
				if ( $content ) {
					$content_main = '<div class="surlywp-tk-vm-notice-shortcode">' . do_shortcode( $content ) . '</div>';
				}
			}

			return $content_main;
		}

		/**
		 * Function to auto apply discount coupon.
		 *
		 * @param array  $args The request args.
		 * @param string $endpoint The request endponint.
		 * @package Toolkit For SureCart
		 * @since 1.0.3
		 */
		public function surelywp_tk_vm_manage_add_to_cart( $args, $endpoint ) {

			if ( 'checkouts' === $endpoint && isset( $args['body']['checkout'] ) ) { // For New Checkout.
				$price_id = $args['body']['checkout']['line_items'][0]['price'] ?? '';
			} elseif ( 'line_items' === $endpoint && isset( $args['body']['line_item'] ) ) { // For add new line item on checkout.
				$price_id = $args['body']['line_item']['price'] ?? '';
			}

			if ( ! empty( $price_id ) ) {
				$price_obj  = Price::find( $price_id );
				$product_id = $price_obj->product ?? '';
				if ( ! is_wp_error( $price_obj ) && ! empty( $price_obj ) ) {
					$vacation_id              = self::surelywp_tk_vm_get_active_vacation_id();
					$is_product_have_vacation = self::surelywp_tk_vm_is_product_have_vacation( $vacation_id, $product_id );
					if ( $is_product_have_vacation ) {
						unset( $args['query'] );
					}
				}
			}

			return $args;
		}

		/**
		 * Function to redirect checkout page to home when vacation is active and prodcut type is all.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public function surelywp_tk_vm_redirect_checkout_page() {

			// For Checkout page.
			$surecart_checkout_page_id = get_option( 'surecart_checkout_page_id' );
			if ( is_page( $surecart_checkout_page_id ) ) {
				$is_redirect = false;
				$vacation_id = self::surelywp_tk_vm_get_active_vacation_id();
				if ( $vacation_id ) {
					$is_disable_checkout = self::get_settings_option( $vacation_id, 'is_disable_checkout' );
					$vm_product_type     = self::get_settings_option( $vacation_id, 'vm_product_type' );
					if ( $is_disable_checkout ) {
						if ( 'all' === $vm_product_type ) {
							$is_redirect = true;
						} else {
							$price_id = isset( $_REQUEST['line_items'][0]['price'] ) && ! empty( $_REQUEST['line_items'][0]['price'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['line_items'][0]['price'] ) ) : '';
							if ( $price_id ) {
								$price_obj = Price::find( $price_id );
								if ( ! is_wp_error( $price_obj ) && ! empty( $price_obj ) ) {
									$product_id               = $price_obj->product ?? '';
									$is_product_have_vacation = self::surelywp_tk_vm_is_product_have_vacation( $vacation_id, $product_id );
									if ( $is_product_have_vacation ) {
										$is_redirect = true;
									}
								}
							}
						}
					}
				}

				if ( $is_redirect ) {
					// redirect checkout page.
					wp_safe_redirect( get_site_url() );
					exit;

				}
			}
		}

		/**
		 * Function to display sitewide banner.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public function surelywp_tk_vm_display_sitewide_banner() {

			$vacation_id = self::surelywp_tk_vm_get_active_vacation_id();

			if ( $vacation_id ) {

				$is_show_banner = self::get_settings_option( $vacation_id, 'is_show_banner' );

				if ( $is_show_banner ) {

					$notice_message = apply_filters( 'surelywp_tk_vm_banner_notice_msg', self::get_vacation_notice_message( $vacation_id ) );
					if ( $notice_message ) {

						$styles = '<style>
									body { margin-top: 50px; }
									.surelywp-tk-vm-sitewide-banner {
										background-color: var(--sc-color-primary-500); 
										color: #FFFFFF; 
										padding: 15px; 
										text-align: center; 
										font-size: 16px; 
										position: fixed; 
										top: 0; 
										width: 100%; 
										z-index: 9999; 
									}
									.surelywp-tk-vm-sitewide-banner p { margin: 0; }
									.surelywp-tk-vm-sitewide-banner p a { color: #FFFFFF; text-decoration: underline; }
									.admin-bar .surelywp-tk-vm-sitewide-banner { top: 32px; }
								</style>';

						// the notice content and styles.
						$sitewide_banner_html = '<div class="surelywp-tk-vm-sitewide-banner" id="surelywp-tk-vm-sitewide-banner">
							<p>' . wp_kses_post( $notice_message ) . '</p>
						</div>' . $styles;

						echo apply_filters( 'surelywp_tk_vm_sitewide_banner', $sitewide_banner_html ); //phpcs:ignore.
					}
				}
			}
		}

		/**
		 * Function to get active vacation id.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function surelywp_tk_vm_get_active_vacation_id() {

			$vacations = self::get_all_settings_option_by_priority();

			if ( ! empty( $vacations ) ) {

				foreach ( $vacations as $vacation_id => $vacation ) {

					// Check shedhule time.
					$schedule_status = self::get_settings_option( $vacation_id, 'schedule_status' );

					if ( ! empty( $schedule_status ) ) {
						$is_scheduled_time = self::surelywp_tk_is_scheduled_time( $vacation_id );
						if ( ! $is_scheduled_time ) {
							continue;
						}
					}

					if ( isset( $vacation['status'] ) ) {

						return $vacation_id;
					}
				}
			}

			return false;
		}

		/**
		 * Function to display vacation notice.
		 *
		 * @param array $block_content The Content of block.
		 * @param array $block The name of block.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public function surelywp_tk_vm_display_notice( $block_content, $block ) {

			if ( 'surecart/product-buy-buttons' === $block['blockName'] ) {
				$vacation_id = self::is_show_notice_on_product();
				if ( $vacation_id ) {

					// Hide/show buy buttons.
					$is_hide_buy_buttons = $this->get_settings_option( $vacation_id, 'is_hide_buy_buttons' );
					if ( $is_hide_buy_buttons ) {
						$block_content = '';
					}

					$notice_position = $this->get_settings_option( $vacation_id, 'notice_position' );
					if ( 'buy-button' === $notice_position ) {
						$notice_message_html = $this->get_vacation_notice_html( $vacation_id );
						$block_content       = $block_content . $notice_message_html;
					}
				}
			} elseif ( 'surecart/product-quantity' === $block['blockName'] ) {
				$vacation_id = self::is_show_notice_on_product();
				if ( $vacation_id ) {
					$notice_position = $this->get_settings_option( $vacation_id, 'notice_position' );
					if ( 'quantity-selector' === $notice_position ) {
						$notice_message_html = $this->get_vacation_notice_html( $vacation_id );
						$block_content       = $block_content . $notice_message_html;
					}
				}
			} elseif ( ( 'surecart/product-price-choices' === $block['blockName'] && ! surelywp_tk_is_sc_v3_or_higher() ) || 'surecart/product-price-chooser' === $block['blockName'] ) {
				$vacation_id = self::is_show_notice_on_product();
				if ( $vacation_id ) {
					$notice_position = $this->get_settings_option( $vacation_id, 'notice_position' );
					if ( 'price-choice' === $notice_position ) {
						$notice_message_html = $this->get_vacation_notice_html( $vacation_id );
						$block_content       = $block_content . $notice_message_html;
					}
				}
			} elseif ( 'surecart/product-title' === $block['blockName'] ) {
				$vacation_id = self::is_show_notice_on_product();
				if ( $vacation_id ) {
					$notice_position = $this->get_settings_option( $vacation_id, 'notice_position' );
					if ( 'title' === $notice_position ) {
						$notice_message_html = $this->get_vacation_notice_html( $vacation_id );
						$block_content       = $block_content . $notice_message_html;
					}
				}
			} elseif ( ( 'surecart/product-price' === $block['blockName'] && ! surelywp_tk_is_sc_v3_or_higher() ) || 'surecart/product-selected-price-fees' === $block['blockName'] ) {
				$vacation_id = self::is_show_notice_on_product();
				if ( $vacation_id ) {
					$notice_position = $this->get_settings_option( $vacation_id, 'notice_position' );
					if ( 'price' === $notice_position ) {
						$notice_message_html = $this->get_vacation_notice_html( $vacation_id );
						$block_content       = $block_content . $notice_message_html;
					}
				}
			} elseif ( 'surecart/product-description' === $block['blockName'] ) {
				$vacation_id = self::is_show_notice_on_product();
				if ( $vacation_id ) {
					$notice_position = $this->get_settings_option( $vacation_id, 'notice_position' );
					if ( 'description' === $notice_position ) {
						$notice_message_html = $this->get_vacation_notice_html( $vacation_id );
						$block_content       = $block_content . $notice_message_html;
					}
				}
			}

			return $block_content;
		}

		/**
		 * Function to check is show notice on product.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function is_show_notice_on_product() {

			// If shop page then return.
			$sc_shop_page_id = SureCart::pages()->getId( 'shop' );
			if ( is_page( $sc_shop_page_id ) ) {
				return false;
			}

			$product = surelywp_tk_get_current_product();
			if ( empty( $product ) || is_wp_error( $product ) ) {
				return false;
			}

			$vacation_id = self::surelywp_tk_vm_get_active_vacation_id();
			if ( $vacation_id ) {

				$product_id = $product->id ?? '';

				$is_product_have_vacation = self::surelywp_tk_vm_is_product_have_vacation( $vacation_id, $product_id );

				if ( $is_product_have_vacation ) {

					// is show notice enable.
					$is_show_notice = self::get_settings_option( $vacation_id, 'is_show_notice' );
					if ( $is_show_notice ) {

						return $vacation_id;
					}
				}
			}

			return false;
		}

		/**
		 * Function to get vacation notice html.
		 *
		 * @param init $vacation_id the id of the vacation.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function get_vacation_notice_html( $vacation_id ) {
			ob_start();
			$notice_message      = apply_filters( 'surelywp_tk_vacation_mode_notice_message', self::get_vacation_notice_message( $vacation_id ) );
			$notice_message_html = '';
			if ( $notice_message ) {
				?>
				<div class="surelywp-vacation-mode-notice">
					<p class="vacation-mode-message"><?php echo wp_kses_post( $notice_message ); ?></p>
				</div>
				<?php
			}
			$notice_message_html = ob_get_clean();
			return apply_filters( 'surelywp_tk_vacation_mode_notice_html', $notice_message_html );
		}

		/**
		 * Function to get vacation notice message.
		 *
		 * @param init $vacation_id the id of the vacation.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function get_vacation_notice_message( $vacation_id ) {

			$notice_message  = self::get_settings_option( $vacation_id, 'notice_message' );
			$schedule_status = self::get_settings_option( $vacation_id, 'schedule_status' );

			if ( ! empty( $schedule_status ) && ! empty( $notice_message ) ) {

				$notice_message   = self::get_settings_option( $vacation_id, 'notice_message' );
				$vm_schedule_type = self::get_settings_option( $vacation_id, 'vm_schedule_type' );

				if ( ! empty( $vm_schedule_type ) ) {

					$current_time = current_time( 'timestamp' );

					if ( 'fixed_time' === $vm_schedule_type ) { // For Fixed Based On Date/Time.

						$fixed_start_time = self::get_settings_option( $vacation_id, 'fixed_start_time' );
						$fixed_end_time   = self::get_settings_option( $vacation_id, 'fixed_end_time' );

						if ( $fixed_start_time && $fixed_end_time ) {
							$notice_message = str_replace( '{vacation_start_date}', date( 'F j, Y, g:i A', strtotime( $fixed_start_time, $current_time ) ), $notice_message );
							$notice_message = str_replace( '{vacation_end_date}', date( 'F j, Y, g:i A', strtotime( $fixed_end_time, $current_time ) ), $notice_message );
						}
					} elseif ( 'recurring_time' === $vm_schedule_type ) { // Recurring Based On Days Of The Week.

						$vacation_start_day   = self::get_settings_option( $vacation_id, 'vacation_start_day' );
						$vacation_end_day     = self::get_settings_option( $vacation_id, 'vacation_end_day' );
						$recurring_start_time = self::get_settings_option( $vacation_id, 'recurring_start_time' );
						$recurring_end_time   = self::get_settings_option( $vacation_id, 'recurring_end_time' );

						if ( $recurring_start_time && $recurring_end_time ) {

							$vacation_days      = surelywp_tk_week_days();
							$vacation_start_day = $vacation_days[ $vacation_start_day ] ?? '';
							$vacation_end_day   = $vacation_days[ $vacation_end_day ] ?? '';

							$notice_message = str_replace( '{vacation_start_date}', $vacation_start_day . ' ' . date( 'g:i A', strtotime( $recurring_start_time, $current_time ) ), $notice_message );
							$notice_message = str_replace( '{vacation_end_date}', $vacation_end_day . ' ' . date( 'g:i A', strtotime( $recurring_end_time, $current_time ) ), $notice_message );
						}
					}
				}
			}

			return $notice_message;
		}

		/**
		 * Function to register scripts and styles for the front end.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public function surelywp_tk_vm_front_script() {

			// register front style.
			wp_register_style( 'surelywp-tk-vm-front', SURELYWP_TOOLKIT_ASSETS_URL . '/css/vacation-mode/surelywp-tk-vm-front.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_style( 'surelywp-tk-vm-front-min', SURELYWP_TOOLKIT_ASSETS_URL . '/css/vacation-mode/surelywp-tk-vm-front.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );

			$product = surelywp_tk_get_current_product();

			// misc settings js enqueue.
			if ( ! empty( $product ) ) {
				self::surelywp_tk_vm_script_enqueue();
			}
		}

		/**
		 * Function to enqueue scripts and styles.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function surelywp_tk_vm_script_enqueue() {

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
			wp_enqueue_style( 'surelywp-tk-vm-front' . $min_file );
		}




		/**
		 * Function to Get all settings options.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function get_all_settings_option_by_priority() {

			$options = get_option( 'surelywp_tk_vm_settings_options', array() );

			// Sorting the array by `vm_priority`.
			uasort(
				$options,
				function ( $a, $b ) {
					$priority_a = $a['vm_priority'] ?? 1; // Default to 1 if 'vm_priority' is missing.
					$priority_b = $b['vm_priority'] ?? 1; // Default to 1 if 'vm_priority' is missing.
					return $priority_b <=> $priority_a;
				}
			);

			return $options;
		}

		/**
		 * Function to check product have vacation.
		 *
		 * @param init $vacation_id the id of the vacation.
		 * @param init $product_id the id of the product.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function surelywp_tk_vm_is_product_have_vacation( $vacation_id, $product_id = '' ) {

			$vm_product_type = self::get_settings_option( $vacation_id, 'vm_product_type' );
			if ( 'all' === $vm_product_type ) {
				return true;
			} elseif ( ! empty( $product_id ) ) {
				$vm_products             = self::get_settings_option( $vacation_id, 'vm_products' );
				$vm_products_collections = self::get_settings_option( $vacation_id, 'vm_products_collections' );
				if ( 'specific' === $vm_product_type && ! empty( $vm_products ) && in_array( $product_id, $vm_products, true ) ) {
					return true;
				} elseif ( 'specific_collection' === $vm_product_type && ! empty( $vm_products_collections ) ) {
					$collections = $vm_products_collections;
					foreach ( $collections as $collection_id ) {
						$product_ids = surelywp_tk_collection_product_ids( $collection_id );
						if ( ! empty( $product_ids ) && in_array( $product_id, $product_ids, true ) ) {
							return true;
						}
					}
				}
			}

			return false;
		}


		/**
		 * Function to check vacation schedule time.
		 *
		 * @param init $vacation_id the id of the vacation.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function surelywp_tk_is_scheduled_time( $vacation_id ) {

			$vm_schedule_type = self::get_settings_option( $vacation_id, 'vm_schedule_type' );

			if ( ! empty( $vm_schedule_type ) ) {

				$current_timestamp = current_time( 'timestamp' ); // Current WordPress timestamp.

				if ( 'fixed_time' === $vm_schedule_type ) { // For Fixed Based On Date/Time.
					$fixed_start_time = self::get_settings_option( $vacation_id, 'fixed_start_time' );
					$fixed_end_time   = self::get_settings_option( $vacation_id, 'fixed_end_time' );
					if ( $fixed_start_time && $fixed_end_time ) {
						$start_time = strtotime( $fixed_start_time, $current_timestamp );
						$end_time   = strtotime( $fixed_end_time, $current_timestamp );
						if ( $current_timestamp >= $start_time && $current_timestamp <= $end_time ) {
							return true;
						}
					}
				} elseif ( 'recurring_time' === $vm_schedule_type ) { // Recurring Based On Days Of The Week.

					return self::is_current_time_in_recurring_vacation( $vacation_id );
				}
			}
			return false;
		}

		/**
		 * Function to Get all settings options.
		 *
		 * @param int $vacation_id The id of the vacation.
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function is_current_time_in_recurring_vacation( $vacation_id ) {

			$vacation_start_day   = self::get_settings_option( $vacation_id, 'vacation_start_day' );
			$vacation_end_day     = self::get_settings_option( $vacation_id, 'vacation_end_day' );
			$recurring_start_time = self::get_settings_option( $vacation_id, 'recurring_start_time' );
			$recurring_end_time   = self::get_settings_option( $vacation_id, 'recurring_end_time' );

			if ( $recurring_start_time && $recurring_end_time ) {

				// Get WordPress local time.
				$current_time = current_time( 'timestamp' );

				// Get the current day of the week (0 = Sunday, 1 = Monday, ..., 6 = Saturday).
				$current_day = date( 'w', $current_time );

				// Check if current day falls within the vacation days.
				$is_day_in_range = false;
				if ( $vacation_start_day <= $vacation_end_day ) {
					// Standard range, e.g., Sunday (0) to Saturday (6) .
					$is_day_in_range = ( $current_day >= $vacation_start_day && $current_day <= $vacation_end_day );
				} else {
					// Wrapping around the week, e.g., Friday (5) to Monday (1).
					$is_day_in_range = ( $current_day >= $vacation_start_day || $current_day <= $vacation_end_day );
				}

				if ( $is_day_in_range ) { // if the day in range.

					// Convert time strings to timestamps for comparison.
					$start_time = strtotime( $recurring_start_time, $current_time );
					$end_time   = strtotime( $recurring_end_time, $current_time );

					$is_time_in_range = true;
					if ( $vacation_start_day == $current_day && $current_time < $start_time ) { // start day and current day same.
						$is_time_in_range = false;
					}

					if ( $vacation_end_day == $current_day && $current_time > $end_time ) { // end day and current day same.
						$is_time_in_range = false;
					}
				}

				return $is_day_in_range && $is_time_in_range;
			}

			return false;
		}


		/**
		 * Function to Get all settings options.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function get_all_settings_option() {

			$options = get_option( 'surelywp_tk_vm_settings_options', array() );

			return $options;
		}

		/**
		 * Function to Get option by Name.
		 *
		 * @param string $vm_id The option name of setting.
		 * @param string $option_name The option name of setting.
		 * @package Toolkit For SureCart
		 * @since   1.0.1
		 */
		public static function get_settings_option( $vm_id, $option_name ) {

			$options = get_option( 'surelywp_tk_vm_settings_options' );
			if ( isset( $options[ $vm_id ][ $option_name ] ) ) {
				$option_value = $options[ $vm_id ][ $option_name ];
			} else {
				$option_value = '';
			}

			return $option_value;
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Vm class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.1
	 */
	function Surelywp_Tk_Vm() {  // phpcs:ignore
		$instance = Surelywp_Tk_Vm::get_instance();
		return $instance;
	}
}
