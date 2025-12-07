<?php
/**
 * All commmon Functions SurelyWP Framework.
 *
 * @package SurelyWP\Framework
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'surelywp_plugin_fw_get_default_logo' ) ) {
	/**
	 * Get the default plugin logo URL.
	 *
	 * @return string The URL of the default plugin logo.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_plugin_fw_get_default_logo() {

		return esc_url( SURELYWP_CORE_PLUGIN_URL . '/assets/images/surely-icon.png' );
	}
}

if ( ! function_exists( 'surelywp_framework_extract_variables' ) ) {
	/**
	 * Extract multiple keys from an array as variables.
	 *
	 * @param array $array The array to extract values from.
	 * @param mixed ...$key_args The keys to extract from the array.
	 * @return array An array of extracted values corresponding to the provided keys.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_framework_extract_variables( $array, ...$key_args ) {

		return array_map(
			function ( $key ) use ( $array ) {
				return isset( $array[ $key ] ) ? $array[ $key ] : null;
			},
			$key_args
		); // Return multiple keys as varible.
	}
}

if ( ! function_exists( 'surelywp_check_license_avtivation' ) ) {
	/**
	 * Check if a plugin license is activated.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @return mixed The license option data if found, otherwise false.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_check_license_avtivation( $plugin_name ) {

		$option_key  = strtolower( preg_replace( '/\s+/', '', $plugin_name ) );
		$key         = $option_key . '_license_options';
		$option_data = get_option( $key );
		return $option_data;
	}
}

if ( ! function_exists( 'surelywp_create_page' ) ) {
	/**
	 * Create a WordPress page if it doesn't exist, or return the existing one.
	 *
	 * @param string $slug The slug for the page.
	 * @param string $option The option name to store the page ID.
	 * @param string $page_title The title of the page.
	 * @param string $page_content The content of the page (can include shortcodes).
	 * @param int    $post_parent The parent post ID (default is 0).
	 * @param string $post_status The status of the post (default is 'publish').
	 * @return int The ID of the created or existing page.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0, $post_status = 'publish' ) {

		global $wpdb;

		$option_value = get_option( $option );

		if ( $option_value > 0 ) {
			$page_object = get_post( $option_value );

			if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
				// Valid page is already in place.
				return $page_object->ID;
			}
		}

		// Try to find an existing page by content or slug.
		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$shortcode        = str_replace( array( '<!-- wp:shortcode -->', '<!-- /wp:shortcode -->' ), '', $page_content );
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{ $shortcode }%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
		}

		$valid_page_found = $valid_page_found;

		if ( $valid_page_found ) {
			if ( $option ) {
				update_option( $option, $valid_page_found );
			}
			return $valid_page_found;
		}

		// Search for a matching valid trashed page.
		if ( strlen( $page_content ) > 0 ) {
			// Search for an existing page with the specified page content (typically a shortcode).
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{ $page_content }%" ) );
		} else {
			// Search for an existing page with the specified page slug.
			$trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
		}

		if ( $trashed_page_found ) {
			$page_id   = $trashed_page_found;
			$page_data = array(
				'ID'          => $page_id,
				'post_status' => $post_status,
			);
			wp_update_post( $page_data );
		} else {
			$page_data = array(
				'post_status'    => $post_status,
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => $slug,
				'post_title'     => $page_title,
				'post_content'   => $page_content,
				'post_parent'    => $post_parent,
				'comment_status' => 'closed',
			);
			$page_id   = wp_insert_post( $page_data );
		}

		if ( $option ) {
			update_option( $option, $page_id );
		}

		return $page_id;
	}
}

if ( ! function_exists( 'surelywp_get_plugin_folder_name' ) ) {
	/**
	 * Get the plugin folder name from a file name.
	 *
	 * @param string $file_name The name of the plugin file.
	 * @return string|null The plugin folder name if found, otherwise null.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_get_plugin_folder_name( $file_name ) {

		// Ensure get_plugins() is available.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$file_name = $file_name . '.php';
		$plugins   = get_plugins();

		if ( ! empty( $plugins ) ) {

			foreach ( $plugins as $plugin_path => $string ) {

				$plugin_item = explode( '/', $plugin_path );

				if ( isset( $plugin_item[1] ) && $plugin_item[1] === $file_name ) {
					return $plugin_item[0];
				}
			}
		}
	}
}

if ( ! function_exists( 'surelywp_api_endpoint_url' ) ) {
	/**
	 * Get the SurelyWP API endpoint URL.
	 *
	 * @return string The API endpoint URL.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_api_endpoint_url() {

		return 'https://surelywp.com/';
	}
}

if ( ! function_exists( 'surelywp_API_response' ) ) {
	/**
	 * Get a decoded API response from a URL.
	 *
	 * @param string $url The API endpoint URL.
	 * @return mixed The decoded API response data.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_API_response( $url ) {

		$response = wp_remote_get( $url );
		$res_data = '';
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$body     = $response['body']; // use the content.
			$res_data = json_decode( $body );
		}
		return $res_data;
	}
}

if ( ! function_exists( 'surelywp_surecart_public_token' ) ) {
	/**
	 * Get SurelyWP SureCart public token from the API.
	 *
	 * @return string The public token.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_surecart_public_token() {

		$endpoint_url    = surelywp_api_endpoint_url();
		$url             = $endpoint_url . 'surecart-addons/surelywp-sc-public-token.json';
		$res_data        = surelywp_API_response( $url );
		$sc_public_token = '';
		if ( ! empty( $res_data ) ) {
			$sc_public_token = $res_data[0]->public_token;
		}
		return $sc_public_token;
	}
}

if ( ! function_exists( 'surelywp_get_public_token' ) ) {
	/**
	 * Get a hardcoded SurelyWP public token.
	 *
	 * @return string The hardcoded public token.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_get_public_token() {

		$sc_public_token = 'pt_2ELsAHF9wTtik9BkXtQjGMr2';
		return $sc_public_token;
	}
}

if ( ! function_exists( 'surelywp_update_addons_json' ) ) {
	/**
	 * Update the SurelyWP addons JSON from the API.
	 *
	 * @package SurelyWP\Framework
	 * @since   1.0.0
	 */
	function surelywp_update_addons_json() {

		$endpoint_url = surelywp_api_endpoint_url();
		$url          = $endpoint_url . 'surecart-addons/surelywp-addon.json';
		$res_data     = surelywp_API_response( esc_url( $url ) );
		if ( ! empty( $res_data ) ) {
			update_option( 'surelywp_addons_json', $res_data );
		}
	}
}
