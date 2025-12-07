<?php
/**
 * Main class for product visibility.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.3.2
 */

if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

if ( ! class_exists( 'Surelywp_Tk_Pv' ) ) {

	/**
	 * Main Product Visibility Class.
	 *
	 * @since 1.3.2
	 */
	class Surelywp_Tk_Pv {

		/**
		 * Instance.
		 *
		 * @var \Surelywp_Tk_Pv
		 */
		protected static $instance;

		/**
		 * Check page have the surecart product list block.
		 *
		 * @var \Surelywp_Tk_Sp
		 */
		protected static $is_page_have_product_list;

		/**
		 * Get single instance.
		 *
		 * @return \Surelywp_Tk_Pv
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'surelywp_tk_pv_enqueue_scripts' ) );

			$is_enable = self::get_settings_option( 'status' );
			if ( $is_enable ) {

				// For product page.
				add_action( 'pre_get_posts', array( $this, 'surelywp_tk_pv_fix_archived_visibility' ), 5 );
				add_action( 'template_redirect', array( $this, 'surelywp_tk_pv_maybe_block_single_product' ), 1 );

				// For Shop Page.
				add_action( 'wp', array( $this, 'surelywp_tk_pv_wp' ) );
				add_action( 'pre_get_posts', array( $this, 'surelywp_tk_pv_fix_product_on_shop' ), 1 );
				add_filter( 'render_block_data', array( $this, 'surelywp_tk_pv_modify_surecart_product_list_include_ids' ), 1, 2 );
			}
		}

		/**
		 * Modify SureCart product list block to include specific product IDs,
		 * including those with 'draft' or 'sc_archived' status, if they match visibility settings.
		 *
		 * This function hooks into the parsed block rendering process to customize
		 * the SureCart product list block. It collects all product IDs with specific
		 * post statuses and filters them based on visibility settings configured in
		 * the plugin. It ensures that draft and archived products can still be shown
		 * if allowed by the visibility settings.
		 *
		 * @param array $parsed_block  The block content after being parsed.
		 * @param array $source_block  The original source block array before rendering.
		 *
		 * @return array Modified block with updated query settings for product IDs.
		 */
		public function surelywp_tk_pv_modify_surecart_product_list_include_ids( $parsed_block, $source_block ) {

			// Apply only to SureCart product list block .
			if ( isset( $parsed_block['blockName'] ) && 'surecart/product-list' === $parsed_block['blockName'] || 'surecart/product-list-related' === $parsed_block['blockName'] ) {

				$sc_product_ids = get_posts(
					array(
						'post_type'   => 'sc_product',
						'post_status' => array( 'publish', 'draft', 'sc_archived' ),
						'numberposts' => -1,
						'fields'      => 'ids',
					)
				);

				if ( ! $sc_product_ids ) {
					return $parsed_block;
				}

				$settings    = self::get_settings_option( 'shop' );
				$include_ids = array( 0 );
				$exclude_ids = array( 0 );

				if ( ! empty( $settings ) ) {
					foreach ( $sc_product_ids as $post_id ) {

						$key        = self::get_product_visibility_key( $post_id );
						$visibility = $settings[ $key ] ?? array();

						$is_admin   = current_user_can( 'edit_posts' );
						$is_allowed = ( $is_admin && in_array( 'visible_to_admins', $visibility, true ) ) || ( ! $is_admin && in_array( 'visible_to_customers', $visibility, true ) );

						if ( true === $is_allowed ) {
							$include_ids[] = $post_id;
						} else {
							$exclude_ids[] = $post_id;
						}
					}
				}

				$include_ids = array_unique( $include_ids );
				$exclude_ids = array_unique( $exclude_ids );

				if ( 'surecart/product-list' === $parsed_block['blockName'] ) {

					// Set query type to custom( required by SureCart to respect IDs ).
					$parsed_block['attrs']['type']                         = 'custom';
					$parsed_block['context']['surecart/product-list/type'] = 'custom';

					// Inject your product IDs.
					$parsed_block['attrs']['query']['include']            = $include_ids;
					$parsed_block['context']['surecart/product-list/ids'] = $include_ids;

				} elseif ( 'surecart/product-list-related' === $parsed_block['blockName'] ) {

					add_filter(
						'surecart_related_products_query_args',
						function ( $args ) use ( $exclude_ids ) {

							// Remove the exclusion if it's set.
							if ( isset( $args['post__not_in'] ) ) {
								$args['post__not_in'] = array_merge( $args['post__not_in'], $exclude_ids );
							}

							return $args;
						},
						10
					);

				}
			}

			return $parsed_block;
		}


		/**
		 * Check page have the surecart product list block.
		 *
		 * @package Toolkit For SureCart
		 * @since 2.0
		 */
		public function surelywp_tk_pv_wp() {

			// Check page have the surecart product list block.
			self::$is_page_have_product_list = has_block( 'surecart/product-list', get_the_ID() );
		}

		/**
		 * Fix visibility of shop page products.
		 *
		 * @param WP_Query $query The WP query object.
		 * @return void
		 */
		public function surelywp_tk_pv_fix_product_on_shop( $query ) {

			if ( is_admin() || is_singular( 'sc_product' ) ) {
				return;
			}

			$query->set( 'post_status', array( 'publish', 'sc_archived', 'draft' ) );
		}
		/**
		 * Get settings option.
		 *
		 * @param string $option_name Option key.
		 * @return mixed
		 */
		public static function get_settings_option( $option_name ) {
			$options = get_option( 'surelywp_tk_pv_settings_options' );
			return isset( $options[ $option_name ] ) ? $options[ $option_name ] : '';
		}

		/**
		 * Get visibility key based on SureCart product state.
		 *
		 * @param int $post_id the Post id.
		 * @return string
		 */
		private static function get_product_visibility_key( $post_id ) {

			if ( ! function_exists( 'sc_get_product' ) || ! $post_id ) {
				return 'published_unavailable';
			}

			$product = sc_get_product( $post_id );

			if ( ! $product || ! isset( $product->status ) || ! isset( $product->archived ) ) {
				return 'published_unavailable';
			}

			$status       = ( 'published' === $product->status ) ? 'published' : 'draft';
			$availability = ( true === $product->archived ) ? 'unavailable' : 'available';

			return "{$status}_{$availability}";
		}

		/**
		 * Fix visibility of archived products in main query.
		 *
		 * @param WP_Query $query The WP query object.
		 * @return void
		 */
		public function surelywp_tk_pv_fix_archived_visibility( $query ) {
			if ( is_admin() || ! $query->is_main_query() ) {
				return;
			}

			if ( ! isset( $query->query_vars['post_type'] ) || 'sc_product' !== $query->query_vars['post_type'] ) {
				return;
			}

			if ( ! isset( $query->query_vars['name'] ) ) {
				return;
			}

			$post = get_page_by_path( $query->query_vars['name'], OBJECT, 'sc_product' );
			if ( empty( $post ) ) {
				return;
			}

			if ( ! function_exists( 'sc_get_product' ) ) {
				return;
			}

			$product = sc_get_product( $post->ID );

			if ( empty( $product ) ) {
				return;
			}

			$status       = ( 'publish' === $product->status ) ? 'published' : 'draft';
			$availability = ( true === $product->archived ) ? 'unavailable' : 'available';
			$key          = "{$status}_{$availability}";

			$settings   = self::get_settings_option( 'product' );
			$visibility = $settings[ $key ] ?? array();

			$is_admin   = current_user_can( 'edit_posts' );
			$is_allowed = ( $is_admin && in_array( 'visible_to_admins', $visibility, true ) )
				|| ( ! $is_admin && in_array( 'visible_to_customers', $visibility, true ) );

			if ( true === $is_allowed ) {
				$query->set( 'post_status', array( 'publish', 'sc_archived', 'draft' ) );
				$GLOBALS['wp_query']->is_404 = false;
			}
		}

		/**
		 * Restrict single product page if not visible.
		 *
		 * @return void
		 */
		public function surelywp_tk_pv_maybe_block_single_product() {

			global $wp_query;

			$post = $wp_query->post ?? get_queried_object();
			if ( empty( $post ) || 'sc_product' !== get_post_type( $post ) ) {
				return;
			}

			$settings = self::get_settings_option( 'product' );
			if ( empty( $settings ) ) {

				// Trigger WordPress native 404 page.
				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();
				include get_404_template();
				exit;
			}

			$key = self::get_product_visibility_key( $post->ID );

			if ( 'sc_product' !== $post->post_type ) {
				$key = 'published_unavailable';
			}

			$visibility = $settings[ $key ] ?? array();

			$is_admin   = current_user_can( 'edit_posts' );
			$is_allowed = ( $is_admin && in_array( 'visible_to_admins', $visibility, true ) )
				|| ( ! $is_admin && in_array( 'visible_to_customers', $visibility, true ) );

			if ( true === $is_allowed ) {
				$wp_query->is_404 = false;
				status_header( 200 );
				return;
			}

			// Trigger WordPress native 404 page.
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			include get_404_template();
			exit;
		}


		/**
		 * Enqueue admin JS.
		 *
		 * @return void
		 */
		public static function surelywp_tk_pv_enqueue_scripts() {

			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			$tab  = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

			wp_register_script( 'surelywp-tk-pv', SURELYWP_TOOLKIT_ASSETS_URL . '/js/product-visibility/surelywp-tk-pv.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );
			wp_register_script( 'surelywp-tk-pv-min', SURELYWP_TOOLKIT_ASSETS_URL . '/js/product-visibility/surelywp-tk-pv.min.js', array( 'jquery' ), SURELYWP_TOOLKIT_VERSION, true );

			$min_file = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

			if ( 'surelywp_tk_pv_settings' === $tab ) {
				wp_enqueue_script( 'surelywp-tk-pv' . $min_file );
				wp_localize_script(
					'surelywp-tk-pv' . $min_file,
					'tk_pv_backend_ajax_object',
					array(
						'admin_ajax_nonce' => wp_create_nonce( 'admin-ajax-nonce' ),
						'ajax_url'         => admin_url( 'admin-ajax.php' ),
					)
				);
			}
		}
	}

	/**
	 * Init.
	 *
	 * @return \Surelywp_Tk_Pv
	 */
	function Surelywp_Tk_Pv() {
		return Surelywp_Tk_Pv::get_instance();
	}
}
