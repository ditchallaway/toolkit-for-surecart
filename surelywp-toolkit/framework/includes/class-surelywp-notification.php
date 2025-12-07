<?php
/**
 * Notifications Manager.
 *
 * Handles fetching, storing, and rendering plugin notifications.
 *
 * @package SurelyWP\Framework\Classes
 * @since   1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SurelyWP_Notifications' ) ) {

	/**
	 * Class SurelyWP_Notifications
	 *
	 * Provides notification dropdown rendering, AJAX handlers,
	 * and scheduled fetching of remote notifications.
	 *
	 * @package SurelyWP\Framework\Classes
	 * @since 1.0.0
	 */
	class SurelyWP_Notifications extends SurelyWP_Plugin_Panel_SureCart {

		/**
		 * Class instance.
		 *
		 * @var SurelyWP_Notifications
		 */
		private static $instance;

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Panel data arguments.
		 *
		 * @var array
		 */
		public static $panel_data;

		/**
		 * Retrieve class instance.
		 *
		 * @return SurelyWP_Notifications
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since 1.0.0
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor.
		 *
		 * Kept private to enforce Singleton usage.
		 *
		 * @param array $args Optional arguments.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since 1.0.0
		 */
		private function __construct( $args = array() ) {
			add_action( 'wp_ajax_surlywp_single_read_notification', array( $this, 'surlywp_single_read_notification_callback' ) );
			add_action( 'wp_ajax_surlywp_read_notification', array( $this, 'surlywp_read_notification_callback' ) );
			add_action( 'surelywp_notification_daily', array( $this, 'surelywp_notification_daily_callback' ) );
		}

		/**
		 * Set panel data options.
		 *
		 * @param array $args Panel arguments.
		 * @return void
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public static function panel_data_options( $args ) {
			self::$panel_data = $args;
		}

		/**
		 * Render individual notification item.
		 *
		 * @param object $notifications Notification data.
		 * @return void
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public static function surelywp_html_view_notifications( $notifications ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$activation = '';
			if ( isset( $notifications->plugin_slug ) ) {
				$installed = SurelyWP_Plugin_Panel_SureCart::surelywp_check_plugin_installed( $notifications->plugin_slug );
				if ( $installed ) {
					$path        = ABSPATH . '/wp-content/plugins/' . $notifications->plugin_slug;
					$plugin_data = get_plugin_data( $path );

					if ( ! empty( $plugin_data ) && isset( $plugin_data['Name'] ) ) {
						$activation = surelywp_check_license_avtivation( $plugin_data['Name'] );
					}
				}
			}
			?>
			<li>
				<div class="surelywp-icon">
					<img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/surely-icon.svg' ); ?>" alt="<?php echo esc_attr__( 'SurelyWP Icon', 'surelywp-framework' ); ?>">
				</div>
				<div class="inner-text">
					<div class="inner-text-wrap">
						<p><?php echo esc_html( $notifications->news_title ); ?></p>
						<i>
							<?php
							echo wp_date( 'd F Y', strtotime( $notifications->news_date ) );
							?>
						</i>	
					</div>
					<?php
					if ( ! isset( $notifications->plugin_slug ) ) {
						if ( ! empty( $notifications->button_text ) && isset( $notifications->button_text ) ) {
							$endpoint_url = surelywp_api_endpoint_url();
							?>
							<a target="_blank" href="<?php echo ( $notifications->button_url ) ? esc_url( $notifications->button_url ) : esc_url( $endpoint_url ); ?>">
								<button class="purchase-btn <?php echo ( 'unread' === $notifications->status ) ? 'single-read-notification' : ''; ?>" data-id="<?php echo ( $notifications->news_id ) ? esc_attr( $notifications->news_id ) : ''; ?>">
									<img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/open-new.svg' ); ?>">
									<?php echo esc_html( $notifications->button_text ); ?>
								</button>
							</a>
							<?php
						}
					}
					?>
				</div>
			</li>
			<?php
		}

		/**
		 * Get notification statistics.
		 *
		 * @return array
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public static function surelywp_get_notification_stats() {
			$notification_data = get_option( 'surelywp_notification' );
			$res_data          = json_decode( $notification_data );

			$count_unread         = 0;
			$count_read           = 0;
			$latest_notifications = array();
			$i                    = 1;

			if ( ! empty( $res_data ) ) {
				foreach ( $res_data as $key => $notifications ) {
					if ( 'unread' === $notifications->status ?? '' ) {
						++$count_unread;
					}

					if ( 'read' === $notifications->status ?? '' ) {
						++$count_read;
					}

					if ( $i <= 5 ) {
						$latest_notifications[] = $notifications;
					} else {
						break;
					}

					++$i;
				}
			}

			return array(
				'count_unread' => $count_unread,
				'count_read'   => $count_read,
				'latest'       => $latest_notifications,
			);
		}

		/**
		 * Handle AJAX: Mark a single notification as read.
		 *
		 * @return void
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surlywp_read_notification_callback() {
			$notification_data = get_option( 'surelywp_notification' );

			$notification_data = json_decode( $notification_data );
			foreach ( $notification_data as $key => $data ) {
				$notification_data[ $key ]->status = 'read';
			}

			$notification_data = wp_json_encode( $notification_data );

			update_option( 'surelywp_notification', $notification_data );

			$html = $this->surelywp_render_notification_dropdown();
			wp_send_json_success(
				array(
					'html' => $html,
				)
			);

			wp_die();
		}


		/**
		 * Render the notification dropdown HTML.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public static function surelywp_render_notification_dropdown() {

			ob_start();

			$notification_stats   = self::surelywp_get_notification_stats();
			$count_unread         = $notification_stats['count_unread'];
			$count_read           = $notification_stats['count_read'];
			$latest_notifications = $notification_stats['latest'];
			?>
			
			<a href="javascript:void(0)" class="dropdown-toggle">
				<?php
				if ( $count_unread > 0 ) {
					?>
					<span class="unread-count"><?php echo esc_html( $count_unread ); ?></span><?php } ?>
				<span class="dashicons dashicons-bell"></span>
			</a>
			<?php if ( ! empty( $latest_notifications ) ) { ?>
				<div class="dropdown-menu">
					<div class="top-sec">
						<?php if ( $count_unread > 0 ) { ?>
							<h3><?php echo esc_html__( 'Unread', 'surelywp-framework' ); ?></h3>
							<img class="hidden" src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL ) . '/assets/images/wp-ajax-loader.gif'; ?>" id="wp_ajax_loader" />
							<button class="marked-read"><img src="<?php echo esc_url( SURELYWP_CORE_PLUGIN_URL ) . '/assets/images/mark-as-read.svg'; ?>" alt=""><?php echo esc_html__( 'Mark All As Read', 'surelywp-framework' ); ?> </button>
						<?php } ?>
					</div>

					<?php
					if ( ! empty( $latest_notifications ) && $count_unread > 0 ) {
						?>
						<ul class="notify-list">
							<?php
							foreach ( $latest_notifications as $key => $notifications ) {
								if ( 'unread' === $notifications->status ?? '' ) {
									self::surelywp_html_view_notifications( $notifications );
								}
							}
							?>
						</ul>
					<?php } ?>

					<?php if ( $count_read > 0 ) { ?>
						<div class="earlier-top-border"></div>
						<div class="top-sec">
							<h3><?php echo esc_html__( 'Earlier', 'surelywp-framework' ); ?></h3>
						</div>
					<?php } ?>

					<?php
					if ( ! empty( $latest_notifications ) && $count_read > 0 ) {
						?>
						<ul class="notify-list earlier">
							<?php
							foreach ( $latest_notifications as $key => $notifications ) {
								if ( 'read' === $notifications->status ?? '' ) {
									self::surelywp_html_view_notifications( $notifications );
								}
							}
							?>
						</ul>
					<?php } ?>
				</div>
			<?php } else { ?>
				<div class="dropdown-menu">
					<div class="top-sec">
						<h3><?php echo esc_html__( 'No Notifications', 'surelywp-framework' ); ?></h3>
					</div>
				</div>
				<?php
			}
			return ob_get_clean();
		}

		/**
		 * Cron: Fetch notifications from remote API daily.
		 *
		 * @return void
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_notification_daily_callback() {
			$endpoint_url       = surelywp_api_endpoint_url();
			$news_url           = $endpoint_url . '/wp-json/wp/v2/posts?categories=22';
			$notifications_json = surelywp_API_response( $news_url );

			$notification_data = get_option( 'surelywp_notification' );

			$items         = ! empty( $notifications_json ) && is_array( $notifications_json ) ? array_slice( $notifications_json, 0, 5, true ) : array();
			$selected_data = array();

			if ( ! empty( $items ) ) {
				foreach ( $items as $key => $news_data ) {
					$date = isset( $news_data->date ) ? wp_date( 'Y-m-d', strtotime( $news_data->date ) ) : '';

					$selected_data[ $key ] = array(
						'news_id'     => isset( $news_data->id ) ? (int) $news_data->id : 0,
						'news_title'  => isset( $news_data->title->rendered ) ? wp_kses_post( $news_data->title->rendered ) : '',
						'news_date'   => $date,
						'status'      => 'unread',
						'button_text' => esc_html__( 'Read More', 'surelywp-framework' ),
						'button_url'  => isset( $news_data->link ) ? esc_url_raw( $news_data->link ) : '',
					);
				}
			}

			$new_data = array();

			if ( ! empty( $notification_data ) ) {
				$notification_data = json_decode( $notification_data );

				if ( is_array( $notification_data ) ) {
					foreach ( $selected_data as $notification ) {
						if (
							isset( $notification['news_id'] ) &&
							! in_array( $notification['news_id'], array_column( $notification_data, 'news_id' ), true )
						) {
							$new_data[] = $notification;
						}
					}
				}
			}

			if ( ! empty( $new_data ) ) {
				foreach ( $new_data as $data ) {
					array_unshift( $notification_data, $data );
				}
			}

			if ( empty( $notification_data ) ) {
				$data = wp_json_encode( $selected_data );
				add_option( 'surelywp_notification', $data );
			} elseif ( ! empty( $new_data ) ) {
				$data = wp_json_encode( $notification_data );
				update_option( 'surelywp_notification', $data );
			}
		}

		/**
		 * Handle AJAX callback to mark all notifications as read.
		 *
		 * @return void
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surlywp_single_read_notification_callback() {
			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'surlywp_notification_nonce' ) ) {
				wp_send_json_error( __( 'Invalid nonce', 'surelywp-framework' ) );
			}

			$single_read_notification = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			if ( empty( $single_read_notification ) ) {
				wp_send_json_error( __( 'Notification ID missing', 'surelywp-framework' ) );
			}

			$notification_id   = (int) $single_read_notification;
			$notification_data = get_option( 'surelywp_notification' );
			$notification_data = json_decode( $notification_data );

			foreach ( $notification_data as $key => $data ) {
				if ( $data->news_id === $notification_id ) {

					$notification_data[ $key ]->status = 'read';
					break;
				}
			}

			update_option( 'surelywp_notification', wp_json_encode( $notification_data ) );

			$html  = $this->surelywp_render_notification_dropdown();
			$stats = $this->surelywp_get_notification_stats();

			wp_send_json_success(
				array(
					'html' => $html,
				)
			);
			wp_die();
		}
	}
}

SurelyWP_Notifications::instance();
