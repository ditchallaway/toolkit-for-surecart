<?php
/**
 * SureCart Panel integration for the SurelyWP Framework.
 *
 * Provides an admin settings panel specific to SureCart, extending
 * the base SurelyWP plugin panel with additional functionality.
 *
 * @author    SurelyWP
 * @package   SurelyWP\Framework\Classes
 * @since     1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SurelyWP_Plugin_Panel_SureCart' ) ) {

	/**
	 * SureCart integration panel.
	 *
	 * Adds tabbed settings pages, notices, scripts and hooks
	 * to integrate SurelyWP plugins with SureCart.
	 *
	 * @package SurelyWP\Framework\Classes
	 * @since 1.0.0
	 */
	class SurelyWP_Plugin_Panel_SureCart extends SurelyWP_Plugin_Panel {

		/**
		 * Tab file paths.
		 *
		 * @var array
		 */
		protected $tabs_path_files;

		/**
		 * Class version number.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Settings arguments passed to the panel.
		 *
		 * @var array
		 */
		public $settings = array();

		/**
		 * Default body class for panel screens.
		 *
		 * @var string
		 */
		public static $body_class = ' surelywp-framework-panel ';

		/**
		 * Track whether admin actions have already been hooked.
		 *
		 * @var bool
		 */
		protected static $actions_initialized = false;

		/**
		 * Sets up hooks, tabs, options and scripts for the admin panel.
		 *
		 * @param array $args Configuration arguments for the panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function __construct( $args = array() ) {
			$args = $args;

			if ( ! empty( $args ) ) {

				if ( isset( $args['parent_page'] ) && 'surelywp_plugin_panel' === $args['parent_page'] ) {
					$args['parent_page'] = 'surelywp_plugin_panel';
				}

				$this->settings               = $args;
				$this->tabs_path_files        = $this->retrieve_tabs_path_files();
				$this->settings['ui_version'] = $this->settings['ui_version'] ?? 1;
				$this->load_admin_tabs();

				if ( isset( $this->settings['create_menu_page'] ) && $this->settings['create_menu_page'] ) {
					$this->surelywp_create_menu_page();
				}

				if ( ! empty( $this->settings['links'] ) ) {
					$this->links = $this->settings['links'];
				}

				add_filter( 'admin_body_class', array( $this, 'surelywp_framework_body_class' ) );
				add_action( 'admin_menu', array( $this, 'surelywp_add_setting_page' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_framework_admin_assets' ) );
				add_action( 'current_screen', array( $this, 'surelywp_disable_update_nag' ) );
				add_action( 'admin_init', array( $this, 'surelywp_activate_addon' ) );

				$license_status = surelywp_check_license_avtivation( $this->settings['page_title'] );
				if ( ! isset( $license_status['sc_activation_id'] ) && empty( $license_status ) ) {
					add_action( 'admin_notices', array( $this, 'surelywp_admin_notices_handler' ) );
				}

				$plugin_base = $this->settings['plugin_slug'] . '/' . $this->settings['plugin_slug'] . '.php';
				add_filter( 'plugin_action_links_' . $plugin_base, array( $this, 'surelywp_framework_plugin_action_link' ) );
			}
		}

		/**
		 * Add a "Settings" link in the plugins list.
		 *
		 * @param array $links Existing plugin action links.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_framework_plugin_action_link( $links ) {
			$link                             = '<a href="' . admin_url( 'admin.php?page=' . $this->settings['page'] ) . '">' . esc_html__( 'Settings', 'surelywp-framework' ) . '</a>';
			$links[ $this->settings['page'] ] = $link;
			return $links;
		}

		/**
		 * Show activation notice when no license key is set.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_admin_notices_handler() {
			$url      = esc_url(
				add_query_arg(
					array(
						'page' => $this->settings['page'],
						'tab'  => 'license_key',
					),
					get_admin_url() . 'admin.php'
				)
			);
			$link_tag = '<a href="' . esc_url( $url ) . '"> ' . esc_html__( 'license key.', 'surelywp-framework' ) . ' </a>';
			?>
			<div class="error">
				<p>
				<?php
					// translators: %1$s: Plugin name, %2$s: License key link.
					printf( esc_html__( 'Welcome to the %1$s plugin by SurelyWP! To begin using the plugin, please enter your %2$s', 'surelywp-framework' ), $this->settings['page_title'], $link_tag );
				?>
				</p>
			</div>
			<?php
		}

		/**
		 * Suppress WordPress update nags on panel pages.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_disable_update_nag() {
			if ( ! empty( $this->retrieve_active_tab() ) ) {
				remove_action( 'admin_notices', 'update_nag', 3 );
				remove_action( 'network_admin_notices', 'maintenance_nag', 10 );
				remove_all_actions( 'admin_notices' );
			}
		}

		/**
		 * Returns the default SurelyWP API endpoint URL.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function API_endpoint_url() {
			return 'https://surelywp.com/';
		}

		/**
		 * Deprecated: Perform a GET request to a given API endpoint.
		 *
		 * @param string $url The API endpoint URL to fetch data from.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public static function surelywp_API_response( $url ) {
			$remote = wp_remote_get( $url );
			$data   = '';
			if ( is_array( $remote ) && ! is_wp_error( $remote ) ) {
				$body = $remote['body'];
				$data = json_decode( $body );
			}
			return $data;
		}

		/**
		 * Sanitize and return uploaded image option value.
		 *
		 * @param string $img_option_value The image option value to sanitize.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_upload_update( $img_option_value ) {
			return $img_option_value;
		}

		/**
		 * Render an upload field for image options.
		 *
		 * @param array $args Arguments for rendering the upload field.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_upload( $args = array() ) {
			if ( ! empty( $args ) ) {
				$args['value'] = ( get_option( $args['id'] ) ) ? get_option( $args['id'] ) : $args['default'];
				extract( $args );

				include SURELYWP_CORE_PLUGIN_TEMPLATE_PATH . '/panel/surecart/surecart-upload.php';
			}
		}

		/**
		 * Get the current active tab slug.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_active_tab() {
			return apply_filters( 'surelywp_wc_plugin_panel_current_tab', parent::retrieve_active_tab() );
		}

		/**
		 * Get available admin panel tabs.
		 *
		 * @param bool $default Whether to return only the default tab key.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_framework_get_panel_tabs( $default = false ) {
			$tab_keys = array_keys( $this->settings['admin-tabs'] );
			return $default ? $tab_keys[0] : $tab_keys;
		}

		/**
		 * Render the tabbed settings panel in the admin page.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_panel() {
			$panel_info = array(
				'current_tab'     => $this->retrieve_active_tab(),
				'current_sub_tab' => '',
				'available_tabs'  => $this->settings['admin-tabs'],
				'default_tab'     => $this->surelywp_framework_get_panel_tabs( true ),
				'page'            => $this->settings['page'],
				'wrap_class'      => isset( $this->settings['class'] ) ? $this->settings['class'] : '',
			);

			$panel_info                    = $panel_info;
			$panel_info['additional_info'] = $panel_info;

			$this->render_panel_header();

			extract( $panel_info );

			require SURELYWP_CORE_PLUGIN_TEMPLATE_PATH . '/panel/surecart/surecart-panel.php';
		}

		/**
		 * Render the main content of the settings panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function render_panel_main_content() {
			$options_path = $this->settings['options-path'];
			$option_key   = $this->retrieve_active_option_key();

			$this->retrieve_template(
				'surecart/surecart-form.php',
				array(
					'panel'        => $this,
					'option_key'   => $option_key,
					'options_path' => $options_path,
				)
			);
		}

		/**
		 * Enqueue admin styles and scripts for the settings panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_framework_admin_assets() {
			global $surecart, $pagenow;

			if ( 'customize.php' !== $pagenow ) {
				wp_enqueue_style( 'wp-jquery-ui-dialog' );
			}

			$screen                      = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$assets_screen_ids           = array();
			$surelywp_check_active_panel = $this->surelywp_check_active_panel( false );

			if ( $surelywp_check_active_panel || in_array( $screen->id, $assets_screen_ids, true ) ) {

				$surecart_settings_deps   = array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris' );
				$surecart_settings_deps[] = 'jquery-ui-dialog';
				$surecart_settings_deps[] = 'chosen';

				wp_enqueue_media();

				if ( $surelywp_check_active_panel ) {
					if ( 1 === $this->retrieve_ui_version() ) {
						wp_enqueue_style( 'surelywp-plugin-style' );
					} else {
						// Set the old plugin framework style to be empty, to prevent issues if any plugin is enqueueing it directly.
						wp_deregister_style( 'surelywp-plugin-style' );
						wp_register_style( 'surelywp-plugin-style', false, array(), SURELYWP_CORE_PLUGIN_FRAMEWORK_VERSION );
					}
				}

				wp_enqueue_style( 'surelywp-addons-select2' );
				wp_enqueue_style( 'surelywp-poppins-fonts' );
				wp_enqueue_style( 'surelywp-framework-fields' );
				wp_enqueue_style( 'surelywp-plugin-panel' );
				wp_enqueue_script( 'surelywp-framework-fields' );
				wp_enqueue_script( 'surelywp-ui' );
				wp_enqueue_script( 'surelywp-select2' );
			}
		}

		/**
		 * Render the content of the settings panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function render_panel_content() {
			$option_key = $this->retrieve_active_option_key();
			$content_id = $this->settings['page'] . '_' . $option_key;

			$this->retrieve_template(
				'panel-content.php',
				array(
					'panel'      => $this,
					'content_id' => $content_id,
				)
			);
		}

		/**
		 * Activate addon from panel request.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_activate_addon() {
			if ( isset( $_GET['page'] ) && $this->settings['parent_page'] == 'surelywp_plugin_panel' && isset( $_GET['plugin'] ) ) {
				$nonce_val = isset( $_REQUEST['_wpnonce'] ) && ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

				if ( wp_verify_nonce( $nonce_val, 'surelywp-activate' ) ) {
					$plugin_slug = isset( $_REQUEST['plugin'] ) && ! empty( $_REQUEST['plugin'] ) ? sanitize_text_field( $_REQUEST['plugin'] ) : '';
					$result      = activate_plugin( $plugin_slug );
				}
			}
		}

		/**
		 * Render tabs navigation.
		 *
		 * @param array $tab_navgation_args Arguments for rendering tab navigation.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function render_tabs_navigation( $tab_navgation_args = array() ) {
			$defaults = array(
				'premium_class' => 'surelywp-premium',
				'parent_page'   => '',
			);

			if ( 1 === $this->retrieve_ui_version() ) {
				$defaults['wrapper_class'] = 'nav-tab-wrapper surelywp-nav-tab-wrapper';
			}

			$tab_navgation_args = wp_parse_args( $tab_navgation_args, $defaults );

			parent::render_tabs_navigation( $tab_navgation_args );
		}

		/**
		 * Add SureCart body class in plugin panel pages.
		 *
		 * @param string $admin_body_classes Existing admin body classes.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public static function admin_body_class( $admin_body_classes ) {
			global $pagenow;

			$assets_screen_ids = array();

			if ( ( 'admin.php' === $pagenow && ( strpos( get_current_screen()->id, 'surelywp-plugins_page' ) !== false || in_array( get_current_screen()->id, $assets_screen_ids, true ) ) ) ) {
				$admin_body_classes = ! substr_count( $admin_body_classes, self::$body_class ) ? $admin_body_classes . self::$body_class : $admin_body_classes;
				$admin_body_classes = ! substr_count( $admin_body_classes, 'surecart' ) ? $admin_body_classes . ' surecart ' : $admin_body_classes;
			}

			return $admin_body_classes;
		}

		/**
		 * Check if a plugin is installed.
		 *
		 * @param string $plugin_slug The slug of the plugin to check.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public static function surelywp_check_plugin_installed( $plugin_slug ) {
			$plugins = get_plugins();
			if ( ! empty( $plugins ) ) {
				foreach ( $plugins as $key => $value ) {
					if ( strpos( $key, $plugin_slug ) !== false ) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Add body classes in plugin panel pages.
		 *
		 * @param string $classes Existing body classes.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_framework_body_class( $classes ) {
			global $pagenow;

			$is_panel = $this->surelywp_check_active_panel();

			if ( $is_panel || in_array( get_current_screen()->id, array(), true ) ) {
				$is_options_panel = $this->surelywp_check_active_panel( false );
				$is_options_panel = $is_options_panel ? 'surecart' : '';
				$is_panel         = $is_panel ? ( 'surelywp-framework-panel--version-' . $this->retrieve_ui_version() ) : '';

				$add_classes = array_filter(
					array(
						'surelywp-framework-panel',
						$is_options_panel,
						$is_panel,
					)
				);

				foreach ( $add_classes as $class_name ) {
					$classes = ! substr_count( $classes, " $class_name " ) ? $classes . " $class_name " : $classes;
				}
			}

			return $classes;
		}

		/**
		 * Check if a plugin is active.
		 *
		 * @param string $plugin_slug The slug of the plugin to check.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public static function surelywp_check_plugin_active( $plugin_slug ) {
			return is_plugin_active( $plugin_slug );
		}
	}
}
