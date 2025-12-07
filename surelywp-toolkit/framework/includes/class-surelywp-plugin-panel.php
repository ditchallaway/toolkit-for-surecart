<?php
/**
 * SurelyWP Plugin Panel Class.
 *
 * @class   SurelyWP_Plugin_Panel
 * @package SurelyWP\Framework\Classes
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'SurelyWP_Plugin_Panel' ) ) {
	/**
	 * Class SurelyWP_Plugin_Panel
	 *
	 * @package SurelyWP\Framework\Classes
	 * @since   1.0.0
	 */
	class SurelyWP_Plugin_Panel {
		/**
		 * Tab Path Files.
		 *
		 * @var array
		 */
		protected $tabs_path_files;

		/**
		 * Tabs hierarchy.
		 *
		 * @var array
		 */
		protected $tabs_hierarchy;

		/**
		 * Main array of options.
		 *
		 * @var array
		 */
		protected $main_array_options;

		/**
		 * Array of links.
		 *
		 * @var array
		 */
		public $links;

		/**
		 * Are the actions initialized?
		 *
		 * @var bool
		 */
		protected static $actions_initialized = false;

		/**
		 * Tabs in WP Pages.
		 *
		 * @var array
		 */
		protected static $panel_tabs_in_wp_pages = array();

		/**
		 * Notices to be shown in the panel.
		 *
		 * @var array
		 */
		protected $notices = array();

		/**
		 * List of settings parameters.
		 *
		 * @var array
		 */
		public $settings = array();

		/**
		 * Version of the class.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * Set up panel settings and enqueue admin scripts.
		 *
		 * @param array $args Array of settings for the panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function __construct( $args = array() ) {
			if ( ! empty( $args ) ) {
				$default_args = array(
					'parent_slug' => 'edit.php?',
					'menu_title'  => esc_html__( 'Settings', 'surelywp-framework' ),
					'icon_url'    => '',
					'capability'  => 'manage_options',
					'position'    => null,
					'page_title'  => esc_html__( 'Plugin Settings', 'surelywp-framework' ),
				);
			}
			// Hook only once.
			static $hooked = false;
			if ( ! $hooked ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_admin_enqueue_scripts' ) );
				$hooked = true;
			}
		}

		/**
		 * Get all notices for the panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_notices() {

			$notices = $this->notices;
			return $notices;
		}

		/**
		 * Add custom body classes to panel pages.
		 *
		 * @param array $body_classes Existing body classes.
		 *
		 * @package SurelyWP\Framework
		 * @since   1.0.0
		 */
		public function surelywp_framework_body_class( $body_classes ) {
			global $pagenow;

			if ( ( 'admin.php' === $pagenow && strpos( get_current_screen()->id, $this->settings['page'] ) !== false ) || $this->retrieve_active_tab() ) {
				$to_add_class = array(
					'surelywp-framework-panel',
					'surelywp-framework-panel--version-' . $this->retrieve_ui_version(),
				);

				foreach ( $to_add_class as $class_to_add ) {
					$body_classes = ! substr_count( $body_classes, " $class_to_add " ) ? $body_classes . " $class_to_add " : $body_classes;
				}
			}

			return $body_classes;
		}

		/**
		 * Add the main menu page for SurelyWP.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_create_menu_page() {
			global $admin_page_hooks;

			if ( ! isset( $admin_page_hooks['surelywp_plugin_panel'] ) ) {
				$position   = '62.32';
				$capability = 'manage_options';
				$show       = true;

				// SurelyWP text must NOT be translated.
				if ( (bool) $show ) {
					add_menu_page( 'surelywp_plugin_panel', 'SurelyWP', $capability, 'surelywp_plugin_panel', null, surelywp_plugin_fw_get_default_logo(), $position );
					// Prevent issues for backward compatibility.
					$admin_page_hooks['surelywp_plugin_panel'] = 'surelywp-plugins';
				}
			}
		}

		/**
		 * Enqueue admin scripts and styles for the panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_admin_enqueue_scripts() {
			global $pagenow;

			$surelywp_check_active_panel = $this->surelywp_check_active_panel( false );

			if ( $surelywp_check_active_panel ) {
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

				wp_enqueue_style( 'surelywp-framework-fields' );
				wp_enqueue_style( 'jquery-ui-style' );
				wp_enqueue_style( 'surelywp-plugin-panel' );

				wp_enqueue_script( 'jquery-ui' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'surelywp-framework-fields' );

				wp_enqueue_media();
				wp_enqueue_script( 'surelywp-plugin-panel' );
			}
		}

		/**
		 * Add Setting SubPage.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_add_setting_page() {
			$this->settings['icon_url'] = isset( $this->settings['icon_url'] ) ? $this->settings['icon_url'] : '';
			$this->settings['position'] = isset( $this->settings['position'] ) ? $this->settings['position'] : null;
			$parent                     = $this->settings['parent_slug'] . $this->settings['parent_page'];

			if ( ! empty( $parent ) ) {
				add_submenu_page( $parent, $this->settings['page_title'], $this->settings['menu_title'], $this->settings['capability'], $this->settings['page'], array( $this, 'surelywp_panel' ) );
			} else {
				add_menu_page( $this->settings['page_title'], $this->settings['menu_title'], $this->settings['capability'], $this->settings['page'], array( $this, 'surelywp_panel' ), $this->settings['icon_url'], $this->settings['position'] );
			}
			// Duplicate Items Hack.
			$this->remove_duplicate_submenu_page();
			do_action( 'surelywp_after_add_settings_page' );
		}

		/**
		 * Add a notice to the panel.
		 *
		 * @param string $message The notice message.
		 * @param string $type    The notice type.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function surelywp_framework_add_notice( string $message, string $type = 'info' ) {
			$notices_arr     = array(
				'type'    => $type,
				'message' => $message,
			);
			$this->notices[] = $notices_arr;
		}

		/**
		 * Render the tabs navigation.
		 *
		 * @param array $nav_args Navigation arguments.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function render_tabs_navigation( $nav_args = array() ) {
			$defaults = array(
				'wrapper_class'   => $this->retrieve_ui_version() > 1 ? '' : 'nav-tab-wrapper',
				'current_sub_tab' => $this->retrieve_active_sub_tab(),
				'premium_class'   => isset( $this->settings['class'] ) ? 'surelywp-premium' : 'premium',
				'current_tab'     => $this->retrieve_active_tab(),
				'parent_page'     => $this->settings['parent_page'],
				'page'            => $this->settings['page'],
			);

			$nav_args = wp_parse_args( $nav_args, $defaults );

			$this->retrieve_template(
				'panel-nav.php',
				array(
					'panel'    => $this,
					'nav_args' => $nav_args,
				)
			);
		}

		/**
		 * Render the panel content page.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function render_panel_main_content() {
			$option_key           = $this->retrieve_active_option_key();
			$surelywp_all_options = $this->retrieve_main_array_options();
			$cus_tab_options      = $this->retrieve_custom_tab_options( $surelywp_all_options, $option_key );

			if ( $this->surelywp_check_premium_tab() && $this->has_premium_tab() ) {
				$this->render_premium_tab();
			} elseif ( $cus_tab_options ) {
				$this->surelywp_render_custom_tab( $cus_tab_options );
			} else {
				$this->retrieve_template(
					'panel-content-page.php',
					array(
						'panel'               => $this,
						'form_method'         => 'POST',
						'panel_content_class' => 'surelywp-admin-panel-content-wrap',
						'option_key'          => $option_key,
					)
				);
			}
		}

		/**
		 * Get the Nav URL.
		 *
		 * @param string $page The page slug.
		 * @param string $tab The tab slug.
		 * @param string $child_tab The child tab slug.
		 * @param string $parent_page The parent page slug.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_get_navigation_url( $page, $tab, $child_tab = '', $parent_page = '' ) {
			$tab_key       = ! empty( $child_tab ) ? $child_tab : $tab;
			$tab_hierarchy = $this->retrieve_tabs_hierarchy();

			if ( isset( $tab_hierarchy[ $tab_key ], $tab_hierarchy[ $tab_key ]['type'], $tab_hierarchy[ $tab_key ]['post_type'] ) && 'post_type' === $tab_hierarchy[ $tab_key ]['type'] ) {
				$tab_url = admin_url( "edit.php?post_type={$tab_hierarchy[$tab_key]['post_type']}" );
			} elseif ( isset( $tab_hierarchy[ $tab_key ], $tab_hierarchy[ $tab_key ]['type'], $tab_hierarchy[ $tab_key ]['taxonomy'] ) && 'taxonomy' === $tab_hierarchy[ $tab_key ]['type'] ) {
				$tab_url = admin_url( "edit-tags.php?taxonomy={$tab_hierarchy[$tab_key]['taxonomy']}" );
			} else {
				$tab_url  = ! empty( $parent_page ) ? "?{$parent_page}&" : '?';
				$tab_url .= "page={$page}&tab={$tab}";
				$tab_url .= ! empty( $child_tab ) ? "&sub_tab={$child_tab}" : '';
				$tab_url  = admin_url( "admin.php{$tab_url}" );
			}

			$final_tab_url = $tab_url;

			return $final_tab_url;
		}

		/**
		 * Remove duplicate submenu for SurelyWP.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function remove_duplicate_submenu_page() {
			remove_submenu_page( 'surelywp_plugin_panel', 'surelywp_plugin_panel' );
		}

		/**
		 * Check if is a custom tab.
		 *
		 * @param array  $tab_options The tab options.
		 * @param string $option_key  The option key.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_check_custom_tab( $tab_options, $option_key ) {
			$option        = $this->retrieve_custom_tab_options( $tab_options, $option_key );
			$option_action = false;

			if ( ! empty( $option ) && isset( $option['action'] ) ) {
				$option_action = $option['action'];
			} else {
				$option_action = false;
			}

			return $option_action;
		}

		/**
		 * Get the custom tab options.
		 *
		 * @param array  $all_options The all options.
		 * @param string $option_key  The option key.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_custom_tab_options( $all_options, $option_key ) {
			$option = ! empty( $all_options[ $option_key ] ) ? current( $all_options[ $option_key ] ) : false;

			if ( $option && isset( $option['type'], $option['action'] ) && 'custom_tab' === $option['type'] && ! empty( $option['action'] ) ) {
				// Inherit values for title and description, if it's a sub-tab/sub-page with show_container set to true.
				if ( $this->retrieve_ui_version() > 1 ) {
					$tab_hierarchy           = $this->retrieve_tabs_hierarchy();
					$tab_hierarchy_page_info = $tab_hierarchy[ $option_key ] ?? array();
					$tabparent               = $tab_hierarchy_page_info['parent'] ?? '';
					$hierarchy_parent_info   = $tab_hierarchy[ $tabparent ] ?? array();
					$inherited_values        = array( 'title', 'description' );

					if ( $tab_hierarchy_page_info ) {
						foreach ( $inherited_values as $inherited_value ) {
							if ( ! isset( $option[ $inherited_value ] ) && isset( $tab_hierarchy_page_info[ $inherited_value ] ) ) {
								$option[ $inherited_value ] = $tab_hierarchy_page_info[ $inherited_value ];
							}
						}
					}
				}

				return $option;
			} else {
				return false;
			}
		}

		/**
		 * Get the tab type by its options.
		 *
		 * @param array $tab_options The tab options.
		 * @return string The tab type.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_tab_type_by_options( $tab_options ) {
			$first = ( ! empty( $tab_options ) && is_array( $tab_options ) ) ? current( $tab_options ) : array();

			$core_types = array(
				'post_type',
				'taxonomy',
				'custom_tab',
				'multi_tab',
			);

			$type     = isset( $first['type'] ) ? $first['type'] : 'options';
			$tab_type = in_array( $type, $core_types, true ) ? $type : 'options';

			return $tab_type;
		}

		/**
		 * Get the tab info by its options.
		 *
		 * @param array $all_tab_options The tab options.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_tab_info_by_options( $all_tab_options ) {
			$first    = ! empty( $all_tab_options ) && is_array( $all_tab_options ) ? current( $all_tab_options ) : array();
			$tab_type = $this->retrieve_tab_type_by_options( $all_tab_options );
			$tab_info = $first;

			$tab_info['type'] = $tab_type;
			if ( 'post_type' === $tab_type ) {
				$tab_info['post_type'] = $first['post_type'] ?? '';
			} elseif ( 'taxonomy' === $tab_type ) {
				$tab_info['taxonomy'] = $first['taxonomy'] ?? '';
			}

			return $tab_info;
		}

		/**
		 * Checks whether current tab is Premium Tab.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function surelywp_check_premium_tab() {
			$active_tab     = $this->retrieve_active_tab();
			$is_premium_tab = false;

			if ( 'premium' === $active_tab ) {
				$is_premium_tab = true;
			} else {
				$is_premium_tab = false;
			}

			return $is_premium_tab;
		}

		/**
		 * Check if panel has premium tab.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function has_premium_tab() {
			if ( ! empty( $this->settings['premium_tab'] ) ) {
				if ( $this->surelywp_check_free() || $this->surelywp_check_extended() ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Trigger the action to render the custom tab.
		 *
		 * @param array|string $options The custom tab options or action name.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_render_custom_tab( $options ) {
			if ( is_string( $options ) ) {
				// Backward compatibility.
				$options = array( 'action' => $options );
			}
			$current_tab     = $this->retrieve_active_tab();
			$current_sub_tab = $this->retrieve_active_sub_tab();

			$this->retrieve_template( 'custom-tab.php', compact( 'options', 'current_tab', 'current_sub_tab' ) );
		}

		/**
		 * Render Premium Tab.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function render_premium_tab() {
			$options = array();
			if ( isset( $this->settings['premium_tab'] ) ) {
				$options = $this->settings['premium_tab'];
			}

			$surelywp_check_extended = $this->surelywp_check_extended();

			$defaults = array(
				'show_premium_landing_link' => $surelywp_check_extended,
				'main_image_url'            => '',
				'premium_features'          => array(),
				'show_free_vs_premium_link' => true,
			);

			$options     = wp_parse_args( $options, $defaults );
			$plugin_slug = '';
			if ( ! empty( $this->settings['plugin_slug'] ) ) {
				$plugin_slug = $this->settings['plugin_slug'];
			}

			$premium_url = '';
			if ( $plugin_slug != '' ) {
				if ( ! empty( $options['landing_page_url'] ) ) {
					$premium_url = $options['landing_page_url'];
				} else {
					$premium_url = $plugin_slug;
				}
			}

			include SURELYWP_CORE_PLUGIN_TEMPLATE_PATH . '/panel/premium-tab.php';
		}

		/**
		 * Get active tab.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_active_tab() {
			$admin_tabs = array_keys( $this->settings['admin-tabs'] );
			global $post_type, $taxonomy;
			$wp_tabs = array();

			if ( $wp_tabs && isset( $wp_tabs['tab'] ) ) {
				return $wp_tabs['tab'];
			}

			if ( ! isset( $_GET['page'] ) || $_GET['page'] !== $this->settings['page'] ) {
				return false;
			}

			if ( isset( $_REQUEST['surelywp_tab_options'] ) ) {
				return sanitize_key( wp_unslash( $_REQUEST['surelywp_tab_options'] ) );
			} elseif ( isset( $_GET['tab'] ) ) {
				return sanitize_key( wp_unslash( $_GET['tab'] ) );
			} elseif ( isset( $admin_tabs[0] ) ) {
				return $admin_tabs[0];
			} else {
				return 'general';
			}
		}

		/**
		 * Return true if the current page is rendered by this panel.
		 *
		 * @param bool $include_wp_pages Whether to include WP pages with panel tabs.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function surelywp_check_active_panel( $include_wp_pages = true ) {
			global $plugin_page;

			$is_panel = false;
			if ( $plugin_page === $this->settings['page'] ) {
				$is_panel = true;
			}

			if ( false === $is_panel && true === $include_wp_pages ) {
				$active_tab = $this->retrieve_active_tab();
				if ( $active_tab ) {
					$is_panel = true;
				} else {
					$is_panel = false;
				}
			}

			return $is_panel;
		}

		/**
		 * Get the current active Child-tab.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_active_sub_tab() {
			global $post_type, $taxonomy;
			$wp_tabs = array();

			if ( ! empty( $wp_tabs ) ) {
				if ( isset( $wp_tabs['sub_tab'] ) ) {
					return $wp_tabs['sub_tab'];
				}
			}

			$sub_tabs = $this->retrieve_sub_tabs();
			$sub_tab  = '';
			if ( isset( $_REQUEST['sub_tab'] ) ) {
				$sub_tab = sanitize_key( wp_unslash( $_REQUEST['sub_tab'] ) );
			}

			if ( $sub_tabs ) {
				if ( $sub_tab && ! isset( $sub_tabs[ $sub_tab ] ) || ! $sub_tab ) {
					$sub_tab = current( array_keys( $sub_tabs ) );
				}
			} else {
				$sub_tab = '';
			}

			return $sub_tab;
		}

		/**
		 * Return the option key related to the current page.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_active_option_key() {
			$current_tab     = $this->retrieve_active_tab();
			$current_sub_tab = $this->retrieve_active_sub_tab();

			if ( ! empty( $current_sub_tab ) ) {
				return $current_sub_tab;
			} else {
				return $current_tab;
			}
		}

		/**
		 * Get the tab path of the files.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_tabs_path_files() {
			$option_files_path = $this->settings['options-path'] . '/';
			$tabs              = array();

			foreach ( (array) glob( $option_files_path . '*.php' ) as $filename ) {
				preg_match( '/(.*)-options\.(.*)/', basename( $filename ), $filename_parts );

				if ( ! isset( $filename_parts[1] ) ) {
					continue;
				}

				$tab          = $filename_parts[1];
				$tabs[ $tab ] = $filename;
			}

			return $tabs;
		}

		/**
		 * Get the main array options.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_main_array_options() {
			$this->maybe_load_vars();
			$main_array_options = $this->main_array_options;

			return $main_array_options;
		}

		/**
		 * Get the tab hierarchy.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_tabs_hierarchy() {
			$this->maybe_load_vars();
			$tabs_hierarchy = $this->tabs_hierarchy;

			return $tabs_hierarchy;
		}

		/**
		 * Return the sub-tabs array of a specific tab.
		 *
		 * @param bool|string $_tab The tab key. If false, will use the active tab.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_sub_tabs( $_tab = false ) {
			if ( false === $_tab ) {
				$_tab = $this->retrieve_active_tab();
			}

			if ( is_string( $_tab ) ) {
				$main_array_options = $this->retrieve_main_array_options();

				if ( isset( $main_array_options[ $_tab ] ) ) {
					$current_tab_options = $main_array_options[ $_tab ];
				} else {
					$current_tab_options = array();
				}

				if ( ! empty( $current_tab_options ) ) {
					$_tab = array(
						$_tab => $current_tab_options,
					);
				}
			}

			$_tab_options = (bool) $_tab && is_array( $_tab ) ? current( $_tab ) : false;
			$_first       = (bool) $_tab_options && is_array( $_tab_options ) ? current( $_tab_options ) : false;
			if ( $_first && is_array( $_first ) && isset( $_first['type'] ) && 'multi_tab' === $_first['type'] && ! empty( $_first['sub-tabs'] ) ) {
				return $_first['sub-tabs'];
			}

			return array();
		}

		/**
		 * Get the first sub-tab key.
		 *
		 * @param bool|string $_tab The tab key. If false, will use the active tab.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_first_sub_tab_key( $_tab = false ) {
			$key = false;

			if ( is_string( $_tab ) ) {
				$main_array_options = $this->retrieve_main_array_options();

				// Check if the tab exists in main options.
				if ( isset( $main_array_options[ $_tab ] ) ) {
					$current_tab_options = $main_array_options[ $_tab ];
				} else {
					$current_tab_options = array();
				}

				// If current tab has some options, re-assign $_tab as array.
				if ( ! empty( $current_tab_options ) ) {
					$_tab = array(
						$_tab => $current_tab_options,
					);
				}
			}

			$sub_tabs = $this->retrieve_sub_tabs( $_tab );
			if ( $sub_tabs ) {
				$key = current( array_keys( $sub_tabs ) );
			}

			return $key;
		}

		/**
		 * Set an array with all default options.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_default_options() {
			$surelywp_options = $this->retrieve_main_array_options();
			$default_options  = array();

			foreach ( $surelywp_options as $tab => $sections ) {
				foreach ( $sections as $section ) {
					foreach ( $section as $id => $value ) {
						if ( isset( $value['std'] ) && isset( $value['id'] ) ) {
							$default_options[ $value['id'] ] = $value['std'];
						}
					}
				}
			}

			unset( $surelywp_options );

			return $default_options;
		}

		/**
		 * Get the title of the tab.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_tab_title() {
			$surelywp_options = $this->retrieve_main_array_options();
			$option_key       = $this->retrieve_active_option_key();

			foreach ( $surelywp_options[ $option_key ] as $sections => $data ) {
				foreach ( $data as $option ) {
					if ( isset( $option['type'] ) && 'title' === $option['type'] ) {
						return $option['name'];
					}
				}
			}

			return '';
		}

		/**
		 * Get the title of the section.
		 *
		 * @param string $section The section key.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_section_title( $section ) {
			$surelywp_options = $this->retrieve_main_array_options();
			$option_key       = $this->retrieve_active_option_key();
			$title_types      = $this->retrieve_ui_version() > 1 ? array( 'title', 'section' ) : array( 'section' );

			foreach ( $surelywp_options[ $option_key ][ $section ] as $option ) {
				if ( isset( $option['type'] ) && in_array( $option['type'], $title_types, true ) ) {
					return $option['name'];
				}
			}

			return '';
		}

		/**
		 * Get the description of the section.
		 *
		 * @param string $section The section key.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_section_description( $section ) {
			$surelywp_options = $this->retrieve_main_array_options();
			$option_key       = $this->retrieve_active_option_key();
			$title_types      = $this->retrieve_ui_version() > 1 ? array( 'title', 'section' ) : array( 'section' );

			foreach ( $surelywp_options[ $option_key ][ $section ] as $option ) {
				if ( isset( $option['type'] ) && in_array( $option['type'], $title_types, true ) && isset( $option['desc'] ) ) {
					return $option['desc'];
				}
			}

			return '';
		}

		/**
		 * Check if the form is showform.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_check_show_form() {
			$surelywp_options = $this->retrieve_main_array_options();
			$option_key       = $this->retrieve_active_option_key();

			foreach ( $surelywp_options[ $option_key ] as $sections => $data ) {
				foreach ( $data as $option ) {
					if ( ! isset( $option['type'] ) || 'title' !== $option['type'] ) {
						continue;
					}

					if ( isset( $option['showform'] ) ) {
						return $option['showform'];
					} else {
						return true;
					}
				}
			}
		}

		/**
		 * Get name of the field.
		 *
		 * @param string $name The field name.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_name_field( $name = '' ) {
			$field_name = 'surelywp_' . $this->settings['parent'] . '_options[' . $name . ']';
			return $field_name;
		}

		/**
		 * Get id field return a string with the id of the input field.
		 *
		 * @param string $id The field id.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_id_field( $id ) {
			$field_id = 'surelywp_' . $this->settings['parent'] . '_options_' . $id;
			return $field_id;
		}

		/**
		 * Check if inside the admin tab there's the premium tab to.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_check_free() {
			$has_fw_premium_tab = false;
			$result             = false;
			if ( isset( $this->settings['premium_tab'] ) && ! empty( $this->settings['premium_tab'] ) ) {
				$has_fw_premium_tab = true;
			}

			$has_old_premium_tab = false;
			if ( isset( $this->settings['admin-tabs']['premium'] ) && ! empty( $this->settings['admin-tabs']['premium'] ) ) {
				$has_old_premium_tab = true;
			}

			if ( $has_fw_premium_tab === true || $has_old_premium_tab === true ) {
				if ( ! $this->surelywp_check_extended() && ! $this->surelywp_check_premium() ) {
					$result = true;
				}
			}

			return $result;
		}

		/**
		 * Checks whether current panel is for extended version of the plugin.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_check_extended() {
			$is_extended = false;

			if ( isset( $this->settings['is_extended'] ) && ! empty( $this->settings['is_extended'] ) ) {
				$is_extended = true;
			} else {
				$is_extended = false;
			}

			return $is_extended;
		}

		/**
		 * Checks whether current panel is for primium plugin.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_check_premium() {
			$is_premium = false;

			if ( isset( $this->settings['is_premium'] ) && ! empty( $this->settings['is_premium'] ) ) {
				$is_premium = true;
			} else {
				$is_premium = false;
			}

			return $is_premium;
		}

		/**
		 * Render the header of panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function render_panel_header() {
			$this->retrieve_template(
				'surelywp-panel-header.php',
				array(
					'panel'        => $this,
					'surelywpLogo' => SURELYWP_CORE_PLUGIN_URL . '/assets/images/surelywp-header-icon.svg',
					'title'        => $this->settings['page_title'],
					'is_free'      => $this->surelywp_check_free(),
				)
			);
		}

		/**
		 * Set the parent page to handle menu for WP Pages.
		 *
		 * @param string $parent_file The parent file.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function set_parent_file_to_handle_menu_for_wp_pages( $parent_file ) {
			if ( self::$panel_tabs_in_wp_pages ) {
				return 'surelywp_plugin_panel';
			}
			return $parent_file;
		}

		/**
		 * Set the submenu page to handle menu for WP Pages.
		 *
		 * @param string $submenu_file The submenu file.
		 * @param string $parent_file The parent file.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function set_submenu_file_to_handle_menu_for_wp_pages( $submenu_file, $parent_file ) {
			if ( self::$panel_tabs_in_wp_pages ) {
				return $this->settings['page'];
			}
			return $submenu_file;
		}

		/**
		 * Save the toggle element options.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function save_toggle_element_options() {
			$result = true;
			return $result;
		}

		/**
		 * Get the data of the current page.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function retrieve_page_data() {
			$tab       = $this->retrieve_active_tab();
			$sub_tab   = $this->retrieve_active_sub_tab();
			$hierarchy = $this->retrieve_tabs_hierarchy();
			$result    = array();

			if ( isset( $hierarchy[ $sub_tab ] ) && ! empty( $hierarchy[ $sub_tab ] ) ) {
				$result = $hierarchy[ $sub_tab ];
			} elseif ( isset( $hierarchy[ $tab ] ) && ! empty( $hierarchy[ $tab ] ) ) {
				$result = $hierarchy[ $tab ];
			} else {
				$result = array();
			}

			return $result;
		}

		/**
		 * Get the title of the current page.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_page_title() {
			$data  = $this->retrieve_page_data();
			$title = '';

			if ( isset( $data['title'] ) && ! empty( $data['title'] ) ) {
				$title = $data['title'];
			} else {
				$title = '';
			}

			return $title;
		}

		/**
		 * Get the description of the current page.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_page_description() {
			$data        = $this->retrieve_page_data();
			$description = '';

			if ( isset( $data['description'] ) && ! empty( $data['description'] ) ) {
				$description = $data['description'];
			} else {
				$description = '';
			}

			return $description;
		}

		/**
		 * Get options based on the path.
		 *
		 * @param string $path The path to the options file.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_options_from_path( $path ) {
			if ( ! empty( $path ) ) {
				if ( file_exists( $path ) ) {
					$result = include $path;
					return $result;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		/**
		 * Get the name of the plugin from setting.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function retrieve_plugin_name() {
			$result = '';

			if ( isset( $this->settings['plugin_name'] ) && ! empty( $this->settings['plugin_name'] ) ) {
				$result = $this->settings['plugin_name'];
			} elseif ( isset( $this->settings['page_title'] ) && ! empty( $this->settings['page_title'] ) ) {
				$result = $this->settings['page_title'];
			} else {
				$result = '';
			}

			return $result;
		}

		/**
		 * Get the title shown in the header of the panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function retrieve_header_title() {
			$stop_words = array(
				'SurelyWP',
				'for SureCart',
				'for SureCart',
				'for WordPress',
				'for SureCart',
				'SureCart',
			);

			$plugin_name = $this->retrieve_plugin_name();

			$cleaned_name = str_replace( $stop_words, '', $plugin_name );

			$final_name = trim( $cleaned_name );

			if ( isset( $this->settings['menu_title'] ) ) {
				return $this->settings['menu_title'];
			} else {
				return $final_name;
			}
		}

		/**
		 * Get a template based on the ui_version.
		 *
		 * @param string $template The template file name.
		 * @param array  $args The arguments to pass to the template.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function retrieve_template( $template, $args = array() ) {
			$_template_args = array(
				'template'      => $template,
				'base_path'     => SURELYWP_CORE_PLUGIN_TEMPLATE_PATH . '/panel/',
				'ui_version'    => $this->retrieve_ui_version(),
				'template_path' => '',
			);

			if ( isset( $args['_template_args'] ) ) {
				unset( $args['_template_args'] );
			}

			if ( $_template_args['ui_version'] > 1 ) {
				$versioned_base_path = $_template_args['base_path'] . 'v' . $_template_args['ui_version'] . '/';
				$versioned_path      = $versioned_base_path . $_template_args['template'];
				if ( file_exists( $versioned_path ) ) {
					$_template_args['template_path'] = $versioned_path;
				}
			}

			if ( ! $_template_args['template_path'] ) {
				$_template_args['template_path'] = $_template_args['base_path'] . $_template_args['template'];
			}

			if ( file_exists( $_template_args['template_path'] ) ) {
				extract( $args );
				include $_template_args['template_path'];
			}
		}

		/**
		 * Initialize main options and tab hierarchy if not already set.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function maybe_load_vars() {
			if ( ! isset( $this->main_array_options ) && ! isset( $this->tabs_hierarchy ) ) {
				$options_path             = $this->settings['options-path'];
				$this->main_array_options = array();
				$this->tabs_hierarchy     = array();

				foreach ( $this->settings['admin-tabs'] as $item => $tab ) {
					$path = trailingslashit( $options_path ) . $item . '-options.php';

					$path = $path;

					if ( file_exists( $path ) ) {

						$_tab = $this->retrieve_options_from_path( $path );

						$this->main_array_options = array_merge( $this->main_array_options, $_tab );

						$sub_tabs        = $this->retrieve_sub_tabs( $_tab );
						$current_tab_key = array_keys( $_tab )[0];

						$this->tabs_hierarchy[ $current_tab_key ] = array_merge(
							array(
								'parent'       => '',
								'has_sub_tabs' => (bool) $sub_tabs,
							),
							$this->retrieve_tab_info_by_options( $_tab[ $current_tab_key ] ),
							array(
								'title'       => $tab['title'],
								'description' => $tab['description'] ?? '',
							)
						);

						foreach ( $sub_tabs as $sub_item => $sub_options ) {

							if ( strpos( $sub_item, $item . '-' ) === 0 ) {
								$sub_item = substr( $sub_item, strlen( $item ) + 1 );
							}
							$sub_tab_path = $sub_options['options_path'] ?? ( $options_path . '/' . $item . '/' . $sub_item . '-options.php' );
							$sub_tab_path = $sub_tab_path;

							if ( file_exists( $sub_tab_path ) ) {

								$_sub_tab                 = $this->retrieve_options_from_path( $sub_tab_path );
								$this->main_array_options = array_merge( $this->main_array_options, $_sub_tab );

								$current_sub_tab_key                          = array_keys( $_sub_tab )[0];
								$this->tabs_hierarchy[ $current_sub_tab_key ] = array_merge(
									array( 'parent' => $current_tab_key ),
									$this->retrieve_tab_info_by_options( $_sub_tab[ $current_sub_tab_key ] ),
									array(
										'title'       => $sub_options['title'],
										'description' => $sub_options['description'] ?? '',
									)
								);
							}
						}
					}
				}
			}
		}

		/**
		 * Initialize admin tabs, ensuring each tab is an array with title, description, and icon.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function load_admin_tabs() {
			foreach ( $this->settings['admin-tabs'] as $key => $tab ) {
				if ( ! is_array( $tab ) ) {
					$this->settings['admin-tabs'][ $key ] = array(
						'title'       => $tab,
						'description' => '',
						'icon'        => $tab,
					);
				}
			}
		}

		/**
		 * Get the UI version for the panel.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		protected function retrieve_ui_version() {
			$version = $this->settings['ui_version'];
			return absint( $version );
		}
	}
}
