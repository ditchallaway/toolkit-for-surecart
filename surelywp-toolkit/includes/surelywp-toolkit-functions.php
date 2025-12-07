<?php
/**
 * Main class for the SurelyWP Supports plugin.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.0.0
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Models\Product;
use SureCart\Models\Customer;
use SureCart\Models\Purchase;
use SureCart\Models\Order;
use SureCart\Models\ApiToken;
use SureCart\Models\ProductCollection;

if ( ! function_exists( 'surelywp_tk_get_user_ip' ) ) {

	/**
	 * Function to get visitor user ip.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.3
	 */
	function surelywp_tk_get_user_ip() {

		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// Check for shared internet/ISP IP.
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// Check for IPs passing through proxies.
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			// Default case use REMOTE_ADDR.
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip;
	}
}

if ( ! function_exists( 'surelywp_tk_api_request' ) ) {

	/**
	 * Function for surecart api request.
	 *
	 * @param string $url The url of the api.
	 * @param string $method The request method of the api.
	 * @param array  $arg The arguments for the api.
	 * @param array  $other_args other The arguments for the api.
	 * @param array  $headers The  headers for the api.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function surelywp_tk_api_request( $url, $method = 'GET', $arg = null, $other_args = null, $headers = null ) {

		$default_hearders = array(
			'Accept'       => 'application/json',
			'content-type' => 'application/json',
		);

		if ( $headers == null ) {

			$headers = $default_hearders;
		}

		$args = array(
			'method'  => $method,
			'headers' => $headers,
		);

		if ( $arg != null ) {
			$args['body'] = json_encode( $arg );
		}

		if ( $other_args != null ) {
			$args = array_merge( $args, $other_args );
		}
		$response = wp_remote_request( $url, $args );
		if ( ( ! is_wp_error( $response ) ) && ( 200 === wp_remote_retrieve_response_code( $response ) ) ) {

			$response = json_decode( $response['body'] );
			return $response;
		} else {

			return false;
		}
	}

}

if ( ! function_exists( 'surelywp_tk_set_cron' ) ) {

	/**
	 * Function to Set Cron.
	 *
	 * @param   array  $args The arguments for cron.
	 * @param   string $recurrence The cron recurrence time.
	 * @param   string $hook The cron hook name.
	 * @param   string $time The time for cron.
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function surelywp_tk_set_cron( $args, $recurrence, $hook, $time ) {

		// Get local time zone.
		surelywp_tk_set_timezone();

		// clear old cron if schedule.
		surelywp_tk_unset_cron( $hook, $args );

		// Schedule new cron.
		wp_schedule_event( $time, $recurrence, $hook, $args );
	}

}

if ( ! function_exists( 'surelywp_tk_unset_cron' ) ) {

	/**
	 * Function to unset Cron.
	 *
	 * @param   string $hook The cron hook name.
	 * @param   array  $args The arguments for cron.
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function surelywp_tk_unset_cron( $hook, $args ) {

		// Clear old cron if exist.
		if ( wp_next_scheduled( $hook, $args ) ) {

			wp_clear_scheduled_hook( $hook, $args );
		}
	}
}

if ( ! function_exists( 'surelywp_tk_set_timezone' ) ) {

	/**
	 * Set user timezone with ip.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function surelywp_tk_set_timezone() {

		$ip = surelywp_tk_get_user_ip();

		if ( ! empty( $ip ) ) {

			$data_obj = surelywp_tk_api_request( 'http://www.geoplugin.net/json.gp?ip=' . $ip );

			if ( ! empty( $data_obj ) ) {

				// If Server ip not work for get country code.
				if ( isset( $data_obj->geoplugin_status ) && 404 === $data_obj->geoplugin_status ) {
					$user_ip_obj = surelywp_tk_api_request( 'https://api.ipify.org?format=json' );
					if ( ! empty( $user_ip_obj ) ) {
						$user_ip = $user_ip_obj->ip ?? '';
						if ( ! empty( $user_ip ) ) {
							$data_obj = surelywp_tk_api_request( 'http://www.geoplugin.net/json.gp?ip=' . $user_ip );
						}
					}
				}

				// Get the country code.
				$country_code = $data_obj->geoplugin_countryCode ?? '';

				if ( ! empty( $country_code ) ) {
					$timezone = \DateTimeZone::listIdentifiers( \DateTimeZone::PER_COUNTRY, $country_code );
					if ( ! empty( $timezone ) && isset( $timezone[0] ) ) {
						date_default_timezone_set( $timezone[0] );
						return true;
					}
				}
			}
		}

		return false;
	}
}


if ( ! function_exists( 'surelywp_tk_get_timezone' ) ) {

	/**
	 * Get user timezone with ip.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function surelywp_tk_get_timezone() {

		$ip = surelywp_tk_get_user_ip();

		if ( ! empty( $ip ) ) {

			$data_obj = surelywp_tk_api_request( 'http://www.geoplugin.net/json.gp?ip=' . $ip );

			if ( ! empty( $data_obj ) ) {

				// If Server ip not work for get country code.
				if ( isset( $data_obj->geoplugin_status ) && 404 === $data_obj->geoplugin_status ) {
					$user_ip_obj = surelywp_tk_api_request( 'https://api.ipify.org?format=json' );
					if ( ! empty( $user_ip_obj ) ) {
						$user_ip = $user_ip_obj->ip ?? '';
						if ( ! empty( $user_ip ) ) {
							$data_obj = surelywp_tk_api_request( 'http://www.geoplugin.net/json.gp?ip=' . $user_ip );
						}
					}
				}
				return $data_obj;
			}
		}

		return false;
	}
}


if ( ! function_exists( 'surelywp_tk_get_admin_emails' ) ) {

	/**
	 * Get admin emails.
	 *
	 * @package Toolkit For SureCart
	 * @since   1.0.0
	 */
	function surelywp_tk_get_admin_emails() {

		$admin_emails = array();

		// Query users with the specified role using WP_User_Query.
		$user_query = new WP_User_Query(
			array(
				'role'   => array( 'administrator' ),
				'fields' => 'user_email', // Return only user IDs.
			)
		);

		if ( ! empty( $user_query ) ) {

			// Get the results.
			$admin_emails = $user_query->get_results();
		}

		return $admin_emails;
	}
}

if ( ! function_exists( 'surelywp_tk_get_current_url' ) ) {
	/**
	 * Function to get current url.
	 *
	 * @package Toolkit For Surecart.
	 * @since 1.0.0
	 */
	function surelywp_tk_get_current_url() {
		$http_host   = isset( $_SERVER['HTTP_HOST'] ) && ! empty( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_url = isset( $_SERVER['REQUEST_URI'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		return ( is_ssl() ? 'https://' : 'http://' ) . $http_host . $request_url;
	}
}


if ( ! function_exists( 'surelywp_tk_get_sc_app_url' ) ) {

	/**
	 * Function to get current url.
	 *
	 * @package Toolkit For Surecart.
	 * @since 1.0.0
	 */
	function surelywp_tk_get_sc_app_url() {

		return 'https://app.surecart.com/dashboard';
	}
}

if ( ! function_exists( 'surelywp_tk_get_sc_all_products' ) ) {

	/**
	 * Function to get all surecart all product.
	 *
	 * @package Toolkit For Surecart.
	 * @since 1.0.0
	 */
	function surelywp_tk_get_sc_all_products() {

		$products    = array();
		$sc_products = Product::where(
			array(
				'archived' => false,
			)
		)->get();

		if ( ! is_wp_error( $sc_products ) && ! empty( $sc_products ) ) {
			$products = $sc_products;
		}

		return $products;
	}
}

if ( ! function_exists( 'surelywp_tk_get_customer_user_id' ) ) {

	/**
	 * Function to get Customer user id.
	 *
	 * @param string $customer_id The surecart customer id.
	 *
	 * @package Toolkit For Surecart.
	 * @since 1.0.0
	 */
	function surelywp_tk_get_customer_user_id( $customer_id ) {

		$customer = Customer::find( $customer_id );

		$customer_email = $customer->email ?? '';

		$customer_user_id = '';
		if ( ! empty( $customer_email ) ) {

			// Retrieve the user by email.
			$user = get_user_by( 'email', $customer_email );

			if ( ! empty( $user ) ) {
				$customer_user_id = $user->ID;
			}
		}

		return $customer_user_id;
	}
}

if ( ! function_exists( 'surelywp_tk_get_purchase_product_ids' ) ) {
	/**
	 * Retrieves all purchased product IDs for a given customer.
	 *
	 * This function fetches purchased product IDs associated with a specific customer ID,
	 * handling pagination by recursively calling itself to gather all product IDs.
	 *
	 * @param int   $customer_id The ID of the customer whose purchases are being queried.
	 * @param array $products_ids An array to collect product IDs (used in recursive calls).
	 * @param int   $page        The current page number for pagination (default is 1).
	 * @param int   $per_page    The number of items per page for pagination (default is 100).
	 * @param array $result_ids    The resuld ids.
	 *
	 * @return array An array of product IDs associated with the customer's purchases.
	 *
	 * @package Toolkit For Surecart
	 * @since 1.0.0
	 */
	function surelywp_tk_get_purchase_product_ids( $customer_id = '', $products_ids = array(), $page = 1, $per_page = 100, $result_ids = array() ) {

		// Fetch purchases for the given customer and product IDs.
		$purchases = Purchase::where(
			array(
				'customer_ids' => array( $customer_id ),
				'product_ids'  => $products_ids,
			)
		)->paginate(
			array(
				'page'     => $page,
				'per_page' => $per_page,
			)
		);

		// Check if the purchases retrieval is successful and not empty.
		if ( ! is_wp_error( $purchases ) && ! empty( $purchases ) ) {

			foreach ( $purchases->data as $data ) {
				$result_ids[] = $data->product; // Collect product IDs.
			}

			// Check for pagination and recursively call the function if there are more pages.
			if ( $purchases->pagination->count ) {

				$total_page = ceil( $purchases->pagination->count / $purchases->pagination->limit );
				if ( $page < $total_page ) {
					return surelywp_tk_get_purchase_product_ids( $customer_id, $products_ids, $page + 1, $per_page, $result_ids );
				}
			}
		}

		return $result_ids; // Return the collected product IDs.
	}

}


if ( ! function_exists( 'surelywp_tk_generate_random_id' ) ) {

	/**
	 * Function to generate random id.
	 *
	 * @param  int $length The length of the id.
	 * @package Toolkit For Surecart
	 * @since   1.0.1
	 */
	function surelywp_tk_generate_random_id( $length = 8 ) {

		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random_id  = '';

		for ( $i = 0; $i < $length; $i++ ) {
			$random_id .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $random_id;
	}
}

if ( ! function_exists( 'surelywp_tk_collection_product_ids' ) ) {

	/**
	 * Function to get products ids from collection id.
	 *
	 * @param String $collection_id The Id of the collection.
	 *
	 * @package Toolkit For Surecart
	 * @since   1.0.0
	 */
	function surelywp_tk_collection_product_ids( $collection_id ) {

		$collection_obj = ProductCollection::with( array( 'products' ) )->find( $collection_id );
		$product_ids    = array();
		if ( ! is_wp_error( $collection_obj ) && ! empty( $collection_obj ) ) {

			if ( $collection_obj->products_count ) {
				foreach ( $collection_obj->products->data as $key => $product_obj ) {
					$product_ids[] = $product_obj->id;
				}
			}
		}
		return $product_ids;
	}
}


if ( ! function_exists( 'surelywp_tk_get_current_product' ) ) {

	/**
	 * Function to get product.
	 *
	 * @package Toolkit For Surecart
	 * @since   1.0.1
	 */
	function surelywp_tk_get_current_product() {

		// backwards compatibility.
		if ( get_query_var( 'surecart_current_product' ) ) {
			return get_query_var( 'surecart_current_product' );
		}

		global $post;

		// allow getting the product by sc_id.
		if ( is_string( $post ) ) {
			$posts = get_posts(
				array(
					'post_type'  => 'sc_product',
					'meta_query' => array(
						'key'   => 'sc_id',
						'value' => $post,
					),
				)
			);
			$post  = count( $posts ) > 0 ? $posts[0] : get_post( $post );
		} else {
			$post = get_post( $post );
		}

		// no post.
		if ( ! $post ) {
			return null;
		}

		// get the product.
		$product = get_post_meta( $post->ID, 'product', true );
		if ( empty( $product ) ) {
			return null;
		}

		if ( is_array( $product ) ) {
			$decoded = json_decode( wp_json_encode( $product ) );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				wp_trigger_error( '', 'JSON decode error: ' . json_last_error_msg() );
			}
			$product = new Product( $decoded );
			return $product;
		}

		// decode the product.
		if ( is_string( $product ) ) {
			$decoded = json_decode( $product );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				wp_trigger_error( '', 'JSON decode error: ' . json_last_error_msg() );
			}
			$product = new Product( $decoded );
			return $product;
		}

		// return the product.
		return $product;
	}
}

if ( ! function_exists( 'surelywp_tk_is_sc_v3_or_higher' ) ) {

	/**
	 * Checking if SureCart version is 3 or higher).
	 *
	 * @package SurelyWP  Toolkit
	 * @since   1.0.0
	 */
	function surelywp_tk_is_sc_v3_or_higher() {

		$current_sc_version = \SureCart::plugin()->version();
		$result             = version_compare( $current_sc_version, '3.0.0', '>=' );
		return $result;
	}
}

if ( ! function_exists( 'surelywp_tk_week_days' ) ) {

	/**
	 * Get Week days.
	 *
	 * @package SurelyWP  Toolkit
	 * @since   1.0.0
	 */
	function surelywp_tk_week_days() {

		return array(
			'0' => esc_html__( 'Sunday', 'surelywp-toolkit' ),
			'1' => esc_html__( 'Monday', 'surelywp-toolkit' ),
			'2' => esc_html__( 'Tuesday', 'surelywp-toolkit' ),
			'3' => esc_html__( 'Wednesday', 'surelywp-toolkit' ),
			'4' => esc_html__( 'Thursday', 'surelywp-toolkit' ),
			'5' => esc_html__( 'Friday', 'surelywp-toolkit' ),
			'6' => esc_html__( 'Saturday', 'surelywp-toolkit' ),
		);
	}
}


if ( ! function_exists( 'surelywp_tk_get_all_sm_access_groups' ) ) {

	/**
	 * Get Week days.
	 *
	 * @package SurelyWP  Toolkit
	 * @since   1.0.0
	 */
	function surelywp_tk_get_all_sm_access_groups() {

		$args          = array(
			'post_type'   => 'wsm_access_group', // Replace with your custom post type.
			'numberposts' => -1,                     // -1 to get all posts
			'post_status' => 'publish',
		);
		$access_groups = get_posts( $args );

		return $access_groups;
	}
}

if ( ! function_exists( 'surelywp_tk_get_product_post_id' ) ) {

	/**
	 * Function to get product post id by surecart product id.
	 *
	 * @param string $product_id the id of the product.
	 * @package SurelyWP  Toolkit
	 * @since   1.3
	 */
	function surelywp_tk_get_product_post_id( $product_id ) {

		$args = array(
			'post_type'      => 'sc_product',
			'meta_key'       => 'sc_id',
			'meta_value'     => $product_id,
			'fields'         => 'ids',
			'posts_per_page' => 1,
		);

		$product_post_ids = get_posts( $args );
		$product_post_id  = ! empty( $product_post_ids ) ? $product_post_ids[0] : null;

		return $product_post_id;
	}
}

if ( ! function_exists( 'surelywp_tk_convert_std_class_to_array' ) ) {

	/**
	 * Function to convert stdClass to array.
	 *
	 * Surelywp Toolkit
	 *
	 * @param mixed $data The data to convert.
	 * @package Toolkit For SureCart
	 * @since   1.4
	 */
	function surelywp_tk_convert_std_class_to_array( $data ) {

		if ( is_object( $data ) ) {
			$data = get_object_vars( $data ); // Convert object to array.
		}

		if ( is_array( $data ) ) {
			return array_map( 'surelywp_tk_convert_std_class_to_array', $data ); // Recurse.
		}

		return $data; // Base case.
	}
}