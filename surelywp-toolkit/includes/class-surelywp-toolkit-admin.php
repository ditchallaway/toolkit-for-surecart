<?php
/**
 * Admin init class
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

if ( ! class_exists( 'Surelywp_Toolkit_Admin' ) ) {

	/**
	 * Initiator class. Create and populate admin views.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	class Surelywp_Toolkit_Admin {

		/**
		 * Single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 * @var     \Surelywp_Toolkit_Admin
		 */
		protected static $instance;

		/**
		 * Report panel
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 * @var string Panel hookname
		 */
		protected $panel = null;


		/**
		 * Tab name
		 *
		 * @var   string
		 * @since 1.0.0
		 */
		public $tab;

		/**
		 * Plugin options
		 *
		 * @var   array
		 * @since 1.0.0
		 */
		public $options;


		/**
		 * Plugin model
		 *
		 * @var   object
		 * @since 1.0.0
		 */
		public $model;

		/**
		 * List of available tab for Reports panel
		 *
		 * @var     array
		 * @access  public
		 */
		public $available_tabs = array();

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 * @return \Surelywp_Toolkit_Admin
		 */
		public static function get_instance() {

			if ( is_null( static::$instance ) ) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		 * Constructor of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function __construct() {

			global $surelywp_model;

			$this->model = $surelywp_model;

			// init admin processing.
			add_action( 'init', array( $this, 'surelywp_tk_init' ) );

			// Reset Settings.
			add_action( 'admin_init', array( $this, 'surelywp_tk_handle_settings' ), 9 );

			// Register panel.
			add_action( 'admin_menu', array( $this, 'surelywp_tk_register_panel' ), 5 );

			// Add Settings.
			add_action( 'admin_menu', array( $this, 'set_register_setting' ) );

			// enqueue scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_admin_scripts' ) );

			// Deactive plugins.
			add_action( 'admin_init', array( $this, 'surelywp_tk_deactive_plugins' ) );
			add_filter( 'plugin_action_links', array( $this, 'surelywp_tk_disable_activate_button' ), 10, 4 );
			add_filter( 'after_plugin_row_meta', array( $this, 'surelywp_tk_add_error_notice' ), 10, 2 );

			// toogle surecart event for fluent crm.
			add_action( 'update_option_surelywp_tk_fc_settings_options', array( $this, 'surelywp_tk_update_sc_events' ), 10, 2 );

			// Add the plugin action link.
			add_filter( 'plugin_action_links_' . SURELYWP_TOOLKIT_INIT, array( $this, 'surelywp_tk_add_plugin_action_link' ) );
		}

		/**
		 * Add Update Action Link.
		 *
		 * Will be remove this function after add on the all fw.
		 *
		 * @param array $links The array of the links.
		 * @package SurelyWP\PluginFramework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_tk_add_plugin_action_link( $links ) {

			// If the added by the FW.
			if ( isset( $links['surelywp_toolkit_panel'] ) ) {
				return $links;
			}

			$settings_link                    = '<a href="' . admin_url( 'admin.php?page=surelywp_toolkit_panel' ) . '">' . esc_html__( 'Settings', 'surelywp-toolkit' ) . '</a>';
			$links['surelywp_toolkit_panel'] = $settings_link;
			return $links;
		}

		/**
		 * Function to enqueue style
		 *
		 * @package toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_admin_scripts() {

			$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

			// Backend css.
			wp_register_style( 'surelywp-tk-settings', SURELYWP_TOOLKIT_ASSETS_URL . '/css/surelywp-tk-settings.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_style( 'surelywp-tk-settings-min', SURELYWP_TOOLKIT_ASSETS_URL . '/css/surelywp-tk-settings.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );

			// Register filepond js ans csss.
			wp_register_style( 'filepond-css', SURELYWP_TOOLKIT_ASSETS_URL . '/css/filepond.min.css', array(), SURELYWP_TOOLKIT_VERSION, 'all' );
			wp_register_script( 'filepond-js', SURELYWP_TOOLKIT_ASSETS_URL . '/js/filepond.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'filepond-plugins-js', SURELYWP_TOOLKIT_ASSETS_URL . '/js/filepond-plugins.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$allow_pages = array( 'surelywp_toolkit_panel' );

			if ( in_array( $page, $allow_pages, true ) ) {

				$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
			}
			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
			// Enqueue filepond script and style.
			wp_enqueue_style( 'filepond-css' );
			wp_enqueue_script( 'filepond-js' );
			wp_enqueue_script( 'filepond-plugins-js' );
		}

		/**
		 * Updates the webhook events for SureCart based on the provided value.
		 *
		 * This method checks if the status is set in the given value. If so, it merges
		 * the default webhook events from SureCart with the events retrieved from
		 * the Fluent CRM integration. If the status is not set, it uses only the
		 * default webhook events.
		 *
		 * @param mixed $old_value The old value of the webhook events before the update.
		 * @param array $value An associative array containing the new value.
		 *                     It may include a 'status' key to determine the behavior.
		 *
		 * @return void This method does not return a value. It updates the registered
		 *               webhook events directly.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_update_sc_events( $old_value, $value ) {

			$status = isset( $value['status'] ) ?? 0;
			Surelywp_Toolkit::surelywp_tk_set_sc_events( $status );
		}

		/**
		 * After plugin row meta.
		 *
		 * @param string $plugin_file  The path to the plugin file.
		 * @param array  $plugin_data  An array of plugin data.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_add_error_notice( $plugin_file, $plugin_data ) {

			// Specify the plugin for which you want to add the error notice.
			if ( 'surelywp-user-switching/surelywp-user-switching.php' === $plugin_file ) {

				// Add an error notice.
				echo '<div class="notice notice-error inline notice-alt"><p>' . esc_html__( 'This plugin cannot be activated because all user switching features are now included in the Toolkit plugin.', 'surelywp-toolkit' ) . '</p></div>';

			}

			if ( 'surelywp-lead-magnets/surelywp-lead-magnets.php' === $plugin_file ) {

				// Add an error notice.
				echo '<div class="notice notice-error inline notice-alt"><p>' . esc_html__( 'This plugin cannot be activated because all lead magnets features are now included in the Toolkit plugin.', 'surelywp-toolkit' ) . '</p></div>';

			}
		}

		/**
		 * Modify plugin action links to disable the activate button.
		 *
		 * @param array  $actions      An array of plugin action links.
		 * @param string $plugin_file  The path to the plugin file.
		 * @param array  $plugin_data  An array of plugin data.
		 * @param string $context      The plugin context, such as 'all' or 'active'.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_disable_activate_button( $actions, $plugin_file, $plugin_data, $context ) {

			// Replace 'your-plugin-folder/your-plugin-file.php' with your plugin's path.
			if ( 'surelywp-user-switching/surelywp-user-switching.php' === $plugin_file ) {

				$actions['activate'] = '<span class="activate">' . esc_html__( 'Activate', 'surelywp-toolkit' ) . '</span>';
			}

			if ( 'surelywp-lead-magnets/surelywp-lead-magnets.php' === $plugin_file ) {

				$actions['activate'] = '<span class="activate">' . esc_html__( 'Activate', 'surelywp-toolkit' ) . '</span>';
			}
			return $actions;
		}

		/**
		 * Display admin notice user switching deactivation notice.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_show_us_plugin_deactive_notice() {
			?>
			<div class="error">
				<p><?php echo esc_html__( 'The User Switching plugin has been deactivated because all user switching features are now provided in the Toolkit plugin.', 'surelywp-toolkit' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Display admin notice lead magnets deactivation notice.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.3
		 */
		public function surelywp_tk_show_lm_plugin_deactive_notice() {
			?>
			<div class="error">
				<p><?php echo esc_html__( 'The Lead Magnets For SureCart plugin has been deactivated because it has merged with the Toolkit For SureCart plugin. All features are now located in the Lead Magnets tab of the Toolkit plugin settings. The settings should be migrated fine, but please double check. Once you have verified all the relevant settings are updated in the Toolkit plugin, you may proceed with deleting the Lead Magnets plugin.', 'surelywp-toolkit' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Deactive plugins.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_deactive_plugins() {

			// Deactive User Switching plugin.
			$user_switching_plugin_path = 'surelywp-user-switching/surelywp-user-switching.php';
			if ( is_plugin_active( $user_switching_plugin_path ) ) {
				deactivate_plugins( $user_switching_plugin_path );
				add_action( 'admin_notices', array( $this, 'surelywp_tk_show_us_plugin_deactive_notice' ) );
			}

			$lead_magnets_plugin_path = 'surelywp-lead-magnets/surelywp-lead-magnets.php';
			if ( is_plugin_active( $lead_magnets_plugin_path ) ) {
				deactivate_plugins( $lead_magnets_plugin_path );
				add_action( 'admin_notices', array( $this, 'surelywp_tk_show_lm_plugin_deactive_notice' ) );
			}
		}
		/**
		 * Reset Settings.
		 *
		 * @package Toolkit For SureCart
		 * @since 1.0.0
		 */
		public function surelywp_tk_handle_settings() {

			$tab           = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
			$is_reset      = isset( $_POST['surelywp_ric_settings_reset'] ) ? true : false;
			$vm_setting_id = isset( $_GET['vm_setting_id'] ) && ! empty( $_GET['vm_setting_id'] ) ? sanitize_text_field( wp_unslash( $_GET['vm_setting_id'] ) ) : '';
			$action        = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

			// Reset Settings.
			if ( $is_reset ) {

				$options_key = '';
				if ( 'surelywp_tk_vm_settings' === $tab && $vm_setting_id ) {
					$surelywp_tk_vm_settings_options                   = get_option( 'surelywp_tk_vm_settings_options' );
					$surelywp_tk_vm_settings_options[ $vm_setting_id ] = array( 'vm_title' => $surelywp_tk_vm_settings_options[ $vm_setting_id ]['vm_title'] );
					update_option( 'surelywp_tk_vm_settings_options', $surelywp_tk_vm_settings_options );
				} else {
					$options_key = $tab . '_options';
					delete_option( $options_key );
				}

				// Save default value.
				switch ( $options_key ) {
					case 'surelywp_tk_misc_settings_options':
						SURELYWP_TOOLKIT::surelywp_tk_misc_save_default_options();
						break;
					case 'surelywp_tk_us_settings_options':
						SURELYWP_TOOLKIT::surelywp_tk_us_save_default_options();
						break;
					case 'surelywp_tk_fc_settings_options':
						SURELYWP_TOOLKIT::surelywp_tk_fc_save_default_options();
						break;
					case 'surelywp_tk_lm_settings_options':
						SURELYWP_TOOLKIT::surelywp_tk_lm_save_default_options();
						break;
					default:
						return;
				}
			}

			// Delete the vacation mode.
			if ( 'remove_vm' === $action && $vm_setting_id ) {
				$surelywp_tk_vm_settings_options = get_option( 'surelywp_tk_vm_settings_options' );
				unset( $surelywp_tk_vm_settings_options[ $vm_setting_id ] );
				update_option( 'surelywp_tk_vm_settings_options', $surelywp_tk_vm_settings_options );

				$redirect_to_settings = add_query_arg(
					array(
						'page' => 'surelywp_toolkit_panel',
						'tab'  => 'surelywp_tk_vm_settings',
					),
					admin_url( 'admin.php' )
				);

				wp_safe_redirect( $redirect_to_settings );
				die();
			}
		}

		/**
		 * Function to Set register setting
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function set_register_setting() {

			register_setting( 'surelywp_tk_misc_settings_options', 'surelywp_tk_misc_settings_options', array( $this, 'surelywp_tk_sanitize_options' ) );
			register_setting( 'surelywp_tk_dt_settings_options', 'surelywp_tk_dt_settings_options', array( $this, 'surelywp_tk_sanitize_options' ) );
			register_setting( 'surelywp_tk_us_settings_options', 'surelywp_tk_us_settings_options', array( $this, 'surelywp_tk_sanitize_options' ) );
			register_setting( 'surelywp_tk_fc_settings_options', 'surelywp_tk_fc_settings_options', array( $this, 'surelywp_tk_sanitize_options' ) );
			register_setting( 'surelywp_tk_vm_settings_options', 'surelywp_tk_vm_settings_options', array( $this, 'surelywp_tk_vm_sanitize_options' ) );
			register_setting( 'surelywp_tk_ac_settings_options', 'surelywp_tk_ac_settings_options', array( $this, 'surelywp_tk_sanitize_options' ) );
			register_setting( 'surelywp_tk_pv_settings_options', 'surelywp_tk_pv_settings_options', array( $this, 'surelywp_tk_sanitize_options' ) );
		}

		/**
		 * Function to Sanitize vacarion mode option data.
		 *
		 * @param array $input The option data for sanitize.
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_vm_sanitize_options( $input ) {

			$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

			if ( isset( $input ) && ! empty( $input ) ) {

				// require below for save multiple vacation options.
				if ( 'remove_vm' !== $action ) {

					// Retrieve the existing settings.
					$existing_settings = get_option( 'surelywp_tk_vm_settings_options' );

					if ( ! empty( $existing_settings ) ) {

						foreach ( $existing_settings as $key => $value ) {

							if ( ! isset( $input[ $key ] ) ) {

								$input[ $key ] = $existing_settings[ $key ];
							}
						}
					}

					// Data Sanitization - vacation mode Settings Options.
					foreach ( $input as $vm_setting_id => $vm_option ) {
						foreach ( $vm_option as $key => $value ) {
							$input[ $vm_setting_id ][ $key ] = $this->model->surelywp_escape_slashes_deep( $value, true, true );
						}
					}
				}
			}

			return $input;
		}

		/**
		 * Function to Sanitize option data.
		 *
		 * @param array $input The option data for sanitize.
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public function surelywp_tk_sanitize_options( $input ) {

			if ( isset( $input ) && ! empty( $input ) ) {

				foreach ( $input as $key => $value ) {

					if ( 'sc_ccd_tabs' === $key ) {
						$input[ $key ] = $this->model->surelywp_escape_slashes_deep( $value, true, true );
					} else {

						// Data Sanitization.
						$input[ $key ] = $this->model->surelywp_escape_slashes_deep( $value );
					}
				}
			}

			return $input;
		}

		/* === INITIALIZATION SECTION === */

		/**
		 * Initiator method. Initiate properties.
		 *
		 * @package Toolkit For SureCart
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		public function surelywp_tk_init() {

			/**
			 * APPLY_FILTERS: surelywp_tk_available_admin_tabs
			 *
			 * Filter the available tabs in the plugin panel.
			 *
			 * @package Toolkit For SureCart
			 * @param   array $tabs Admin tabs
			 * @return  array
			 * @since   1.0.0
			 */
			$this->available_tabs = apply_filters(
				'surelywp_tk_available_admin_tabs',
				array(
					'overview'                  =>
					array(
						'title' => esc_html__( 'Overview', 'surelywp-toolkit' ),
						'icon'  => "<div class='image documentation'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/Home.svg' ) . "'></div>",
					),
					'surelywp_tk_misc_settings' =>
					array(
						'title' => esc_html__( 'Miscellaneous', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/setting.svg' ) . "'></div>",
					),
					'surelywp_tk_us_settings'   =>
					array(
						'title' => esc_html__( 'User Switching', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/tk-user-switching.svg' ) . "'></div>",
					),
					'surelywp_tk_fc_settings'   =>
					array(
						'title' => esc_html__( 'FluentCRM', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/fluent-crm.svg' ) . "'></div>",
					),
					'surelywp_tk_vm_settings'   =>
					array(
						'title' => esc_html__( 'Vacation Mode', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/vacation-mode.svg' ) . "'></div>",
					),
					'surelywp_tk_ac_settings'   =>
					array(
						'title' => esc_html__( 'Admin Columns', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/admin-columns.svg' ) . "'></div>",
					),
					'surelywp_tk_dt_settings'   =>
					array(
						'title' => esc_html__( 'Dashboard Tabs', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/customer-tabs.svg' ) . "'></div>",
					),
					'surelywp_tk_lm_settings'   =>
					array(
						'title' => esc_html__( 'Lead Magnets', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/lead-magnets.svg' ) . "'></div>",
					),
					'surelywp_tk_pv_settings'   =>
					array(
						'title' => esc_html__( 'Product Visibility', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/product_visibility.svg' ) . "'></div>",
					),
					'changelog'                 =>
					array(
						'title' => esc_html__( 'Changelog', 'surelywp-toolkit' ),
						'icon'  => "<div class='image changelog'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/changelog.svg' ) . "'></div>",
					),
					'license_key'               =>
					array(
						'title' => esc_html__( 'License Key ', 'surelywp-toolkit' ),
						'icon'  => "<div class='image key'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/license-Key.svg' ) . "'></div>",
					),
					'surelywp_addons_settings'  =>
					array(
						'title' => esc_html__( 'SurelyWP Addons ', 'surelywp-toolkit' ),
						'icon'  => "<div class='image addons'><img src='" . esc_url( SURELYWP_TOOLKIT_ASSETS_URL . '/images/Add.svg' ) . "'></div>",
					),
				)
			);
		}

		/**
		 * Get Current panel name
		 *
		 * @package Toolkit For SureCart
		 * @since   1.0.0
		 */
		public static function get_panel_name() {
			return 'surelywp_toolkit_panel';
		}

		/**
		 * Register Support panel
		 *
		 * @package Toolkit For SureCart
		 * @return  void
		 * @since   1.0.0
		 */
		public function surelywp_tk_register_panel() {

			$args = array(
				'create_menu_page'   => true,
				'parent_slug'        => '',
				'page_title'         => esc_html__( 'Toolkit For SureCart', 'surelywp-toolkit' ),
				'menu_title'         => esc_html__( 'Toolkit', 'surelywp-toolkit' ),
				'plugin_slug'        => 'surelywp-toolkit',
				'plugin_description' => esc_html__( 'This plugin brings together essential admin tools for SureCart store owners, including direct FluentCRM integration, user switching, external products, vacation mode, custom admin columns, customer dashboard customizer, download lists, product visibility overrides, and many more miscellaneous tools.', 'surelywp-toolkit' ),

				/**
				 * APPLY_FILTERS: surelywp_tk_settings_panel_capability
				 *
				 * Filter the capability used to access the plugin panel.
				 *
				 * @param string $capability Capability
				 *
				 * @return string
				 */
				'capability'         => 'manage_options',
				'parent'             => '',
				'parent_page'        => 'surelywp_plugin_panel',
				'page'               => 'surelywp_toolkit_panel',
				'admin-tabs'         => $this->available_tabs,
				'options-path'       => SURELYWP_TOOLKIT_DIR . 'addons-options',
				'help_tab'           => array(),
			);

			$this->panel = new SurelyWP_Plugin_Panel_SureCart( $args );
		}
	}
}

/**
 * Unique access to instance of Surelywp_Toolkit_Admin class
 *
 * @package Toolkit For SureCart
 * @return  \Surelywp_Toolkit_Admin
 * @since   1.0.0
 */
function Surelywp_Toolkit_Admin() { // phpcs:ignore
	$instance = Surelywp_Toolkit_Admin::get_instance();
	return $instance;
}
