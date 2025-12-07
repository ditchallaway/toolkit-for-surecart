<?php
/**
 * Main class for Dashboard Tasbs Settings.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.2.3
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

if ( ! class_exists( 'Surelywp_Tk_Dt' ) ) {

	/**
	 * Main Supports Class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2.3
	 */
	class Surelywp_Tk_Dt {


		/**
		 * Single instance of the class
		 *
		 * @var \Surelywp_Tk_Dt
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2.3
		 * @return  \Surelywp_Tk_Dt
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
		 * @since   1.2.3
		 */
		public function __construct() {

			// Admin Enqueue scipts.
			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_dt_settings_admin_scripts' ) );

			// Override Surecart Customer Dashboard.
			add_filter( 'template_include', array( $this, 'surelywp_tk_dt_override_template' ) );

			// Add Tabs.
			add_filter( 'surelywp_surecart_customer_dashboard_data', array( $this, 'surelywp_tk_dt_add_tabs' ), 12, 2 );
			add_action( 'surelywp_surecart_dashboard_right', array( $this, 'surelywp_tk_dt_sc_dashboard_right_content' ) );
			add_action( 'surecart_template_dashboard_body_open', array( $this, 'surelywp_tk_restrict_sc_default_tab' ) );
		}

		/**
		 * Restrict Surecart Default tab.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2.3
		 */
		public function surelywp_tk_restrict_sc_default_tab() {

			$enable_ccd_tabs = self::get_settings_option( 'enable_ccd_tabs' );
			if ( ! $enable_ccd_tabs ) {
				return;
			}

			$model       = isset( $_GET['model'] ) && ! empty( $_GET['model'] ) ? sanitize_text_field( wp_unslash( $_GET['model'] ) ) : '';
			$sc_ccd_tabs = self::get_settings_option( 'sc_ccd_tabs' );
			$tab         = $sc_ccd_tabs[ $model . 's' ] ?? false;
			$is_sc_tab   = isset( $tab['is_sc_tab'] ) ? true : false;

			if ( ! $is_sc_tab ) {
				return;
			}

			$is_show         = isset( $tab['is_show'] ) ? true : false;
			$is_restrict_tab = isset( $tab['is_restrict_tab'] ) ? true : false;

			$is_have_access = true;
			if ( $is_show && $is_restrict_tab ) {

				$tab_restrict_criteria = $tab['tab_restrict_criteria'] ?? 'based_on_user_roles';
				if ( 'based_on_user_roles' === $tab_restrict_criteria ) {
					$is_have_access = self::check_tab_access_by_user_roles( $tab ?? array() );
				} elseif ( 'based_on_sm_access_groups' === $tab_restrict_criteria ) {
					$is_have_access = self::check_tab_access_by_suremembers( $tab ?? array() );
				}
			}

			if ( ! $is_show || ! $is_have_access ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'surelywp-toolkit' ), esc_html__( 'Error', 'surelywp-toolkit' ), array( 'response' => 403 ) );
			}
		}

		/**
		 * Check tab access by the user roles.
		 *
		 * @param array $tab The tab options values.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2.2
		 */
		public static function check_tab_access_by_user_roles( $tab ) {

			$allowed_roles = $tab['tab_restriction_user_roles'] ?? array();
			$visibility    = $tab['tab_visibility_condition'] ?? 'hidden';

			// No restrictions, allow access.
			if ( empty( $allowed_roles ) ) {
				return true;
			}

			$user_roles = wp_get_current_user()->roles ?? array();

			$has_common_role = ! empty( array_intersect( $allowed_roles, $user_roles ) );

			if ( 'visible' === $visibility ) {
				return $has_common_role;
			}

			// Default to 'hidden' behavior.
			return ! $has_common_role;
		}



		/**
		 * Check tab access by the SureMembers groups.
		 *
		 * @param array $tab The tab options values.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2.2
		 */
		public static function check_tab_access_by_suremembers( $tab ) {

			$restricted_groups = $tab['tab_restriction_sm_access_groups'] ?? array();
			$visibility        = $tab['tab_visibility_condition'] ?? 'hidden';

			// No restriction applied, allow access.
			if ( empty( $restricted_groups ) ) {
				return true;
			}

			$user_id     = get_current_user_id();
			$user_groups = get_user_meta( $user_id, 'suremembers_user_access_group', true );
			$user_groups = is_array( $user_groups ) ? $user_groups : array();

			// Find intersecting groups between tab restriction and user groups.
			$intersecting_groups = array_intersect( $restricted_groups, $user_groups );

			foreach ( $intersecting_groups as $group_id ) {
				$status          = get_post_status( $group_id );
				$user_group_meta = get_user_meta( $user_id, "suremembers_user_access_group_{$group_id}", true );
				$user_status     = $user_group_meta['status'] ?? '';

				$is_active_group = ( 'publish' === $status && 'active' === $user_status );

				if ( 'hidden' === $visibility && $is_active_group ) {
					return false; // Restrict access if group is active and tab is set to hidden.
				}

				if ( 'visible' === $visibility && $is_active_group ) {
					return true; // Allow access if group is active and tab is visible to them.
				}
			}

			// Fallback access decisions.
			return 'hidden' === $visibility;
		}



		/**
		 * Function to enqueue scripts and styles for the front end.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2.3
		 */
		public function surelywp_tk_dt_settings_admin_scripts() {

			$tab = isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

			// misc settings backend js.
			wp_register_script( 'surelywp-tk-dt-backend', SURELYWP_TOOLKIT_ASSETS_URL . '/js/dashboard-tabs/surelywp-tk-dt-backend.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-dt-backend-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/dashboard-tabs/surelywp-tk-dt-backend.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			$localize = array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'admin_ajax_nonce' => wp_create_nonce( 'admin-ajax-nonce' ),
			);

			if ( 'surelywp_tk_dt_settings' === $tab ) {

				wp_enqueue_editor();
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-sortable' );

				wp_localize_script( 'surelywp-tk-dt-backend' . $min_file, 'tk_dt_backend_ajax_object', $localize );
				wp_enqueue_script( 'surelywp-tk-dt-backend' . $min_file );
			}
		}

		/**
		 * Funcation to add customer service content.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2.2
		 */
		public function surelywp_tk_dt_sc_dashboard_right_content() {

			$enable_ccd_tabs = self::get_settings_option( 'enable_ccd_tabs' );
			if ( ! $enable_ccd_tabs ) {
				return '';
			}

			$model       = isset( $_GET['model'] ) && ! empty( $_GET['model'] ) ? sanitize_text_field( wp_unslash( $_GET['model'] ) ) : '';
			$sc_ccd_tabs = self::get_settings_option( 'sc_ccd_tabs' );
			$tab         = $sc_ccd_tabs[ $model ] ?? false;

			if ( $tab ) {
				$content = $tab['tab_content'] ?? '';
				echo do_shortcode( wp_kses_post( $content ) );
			}
		}


		/**
		 * Funcation to add customer service menu.
		 *
		 * @param array $data the data for customer dashboard.
		 * @param array $controller the controller for customer dashboard.
		 *
		 * @package Toolkit For SureCart
		 * @since   1.2.2
		 */
		public function surelywp_tk_dt_add_tabs( $data, $controller ) {

			$is_suremembers_plugin_active = defined( 'SUREMEMBERS_ACCESS_GROUPS' ) ? true : false;

			$enable_ccd_tabs = self::get_settings_option( 'enable_ccd_tabs' );
			if ( ! $enable_ccd_tabs ) {
				return $data;
			}

			$sc_ccd_tabs    = self::get_settings_option( 'sc_ccd_tabs' );
			$new_navigation = array();
			if ( ! empty( $sc_ccd_tabs ) ) {

				foreach ( $sc_ccd_tabs as $key => $tab ) {

					$is_have_access  = true;
					$is_sc_tab       = $tab['is_sc_tab'] ?? '';
					$is_show         = isset( $tab['is_show'] ) ? true : false;
					$is_restrict_tab = isset( $tab['is_restrict_tab'] ) ? true : false;

					if ( $is_show && $is_restrict_tab ) {

						$tab_restrict_criteria = $tab['tab_restrict_criteria'] ?? 'based_on_user_roles';
						if ( 'based_on_user_roles' === $tab_restrict_criteria ) {
							$is_have_access = self::check_tab_access_by_user_roles( $tab ?? array() );
						} elseif ( 'based_on_sm_access_groups' === $tab_restrict_criteria ) {
							if ( ! $is_suremembers_plugin_active ) {
								$is_show = false;
							}
							$is_have_access = self::check_tab_access_by_suremembers( $tab ?? array() );
						}
					}

					if ( $is_sc_tab ) {

						// remove the tab if show tab is off or tab is restrict.
						if ( ! $is_show || ! $is_have_access ) {
							unset( $data['navigation'][ $key ] );
							continue;
						}

						$new_navigation[ $key ] = array(
							'icon_name' => $tab['tab_icon'] ?? '',
							'name'      => $tab['tab_name'] ?? '',
							'active'    => $data['navigation'][ $key ]['active'] ?? '',
							'href'      => $data['navigation'][ $key ]['href'] ?? '',
						);

					} else {

						// remove the tab if show tab is off or tab is restrict.
						if ( ! $is_show || ! $is_have_access ) {
							continue;
						}

						$content_link = '';
						if ( 'display_content' === $tab['tab_behavior'] ) {
							$dashboard_url = get_permalink( get_the_ID() );
							$content_link  = add_query_arg(
								array(
									'action' => 'index',
									'model'  => $key,
								),
								$dashboard_url
							);

						} elseif ( 'link_to_url' === $tab['tab_behavior'] ) {
							$content_link = $tab['tab_link'] ?? '';
						}
						$new_navigation[ $key ] = array(
							'icon_name'            => $tab['tab_icon'] ?? '',
							'name'                 => $tab['tab_name'] ?? '',
							'active'               => $controller->isActive( $key ),
							'href'                 => $content_link,
							'surelywp_custom_menu' => true,

						);
					}
				}
			}

			$data['navigation'] = $new_navigation + $data['navigation'];

			if ( $data['navigation'] ) {

				$active_tab    = $data['active_tab'] ?? '';
				$first_tab_key = array_key_first( $data['navigation'] );
				if ( 'dashboard' !== $first_tab_key && 'dashboard' === $active_tab ) {
					$first_tab_url = $data['navigation'][ $first_tab_key ]['href'] ?? '';
					if ( $first_tab_url ) {
						wp_redirect( $first_tab_url );
						exit;
					}
				}
			}

			return $data;
		}


		/**
		 * Function for override customer dashboard.
		 *
		 * @param string $template The path of the template.
		 *
		 * @package  Surelywp Toolkit
		 * @since   1.2.2
		 */
		public function surelywp_tk_dt_override_template( $template ) {

			$enable_ccd_tabs = self::get_settings_option( 'enable_ccd_tabs' );
			if ( ! $enable_ccd_tabs ) {
				return $template;
			}

			// Get the template name.
			$template_name = basename( $template );

			// Override surecart customer template.
			if ( 'template-surecart-dashboard.php' === $template_name ) {

				$customer_dashboard_path = SURELYWP_TOOLKIT_TEMPLATE_PATH . '/surecart-customer-template/' . $template_name;
				if ( file_exists( $customer_dashboard_path ) ) {
					return $customer_dashboard_path;
				}
			}
			return $template;
		}

		/**
		 * Function to Get option by Name.
		 *
		 * @param string $option_name The option name of setting.
		 * @package Toolkit For SureCart
		 * @since   1.2.3
		 */
		public static function get_settings_option( $option_name ) {

			$options = get_option( 'surelywp_tk_dt_settings_options' );
			if ( isset( $options[ $option_name ] ) ) {
				$option_value = $options[ $option_name ];
			} else {
				$option_value = '';
			}

			return $option_value;
		}
	}

	/**
	 * Unique access to instance of Surelywp_Tk_Dt class
	 *
	 * @package Toolkit For SureCart
	 * @since   1.2.3
	 */
	function Surelywp_Tk_Dt() {  // phpcs:ignore
		$instance = Surelywp_Tk_Dt::get_instance();
		return $instance;
	}
}
