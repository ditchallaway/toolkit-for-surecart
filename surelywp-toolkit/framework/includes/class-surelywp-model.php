<?php
/**
 * Core Model Class for SurelyWP.
 *
 * Provides utility methods for data sanitization, escaping,
 * and array/object handling within the plugin framework.
 *
 * @package SurelyWP\PluginFramework\Classes
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Global Model Variable.
global $surelywp_model;

if ( ! class_exists( 'SurelyWP_Model' ) ) {

	/**
	 * Main Model Class.
	 *
	 * This class implements helper functions for escaping, sanitization,
	 * and conversions. It follows the Singleton pattern to ensure only
	 * one instance is available globally.
	 *
	 * @package SurelyWP\PluginFramework\Classes
	 * @since 1.0.0
	 */
	class SurelyWP_Model {

		/**
		 * Holds the single class instance.
		 *
		 * @var self|null
		 */
		private static $instance;

		/**
		 * Constructor.
		 *
		 * Kept private to enforce Singleton usage.
		 */
		public function __construct() {}

		/**
		 * Retrieve the single instance of the class.
		 *
		 * @return self
		 */
		public static function instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Sanitize data and strip all HTML tags.
		 *
		 * @param mixed $data Input data.
		 * @return mixed Sanitized data.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_nohtml_kses( $data = array() ) {

			if ( is_array( $data ) ) {

				$data = array_map( array( $this, 'surelywp_nohtml_kses' ), $data );

			} elseif ( is_string( $data ) ) {

				$data = wp_filter_nohtml_kses( $data );
			}

			return $data;
		}

		/**
		 * Escape a string attribute safely.
		 *
		 * @param string $data Raw input data.
		 * @return string Sanitized attribute string.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_escape_attr( $data ) {

			return esc_attr( stripslashes( $data ) );
		}

		/**
		 * Recursively strip slashes and sanitize data.
		 *
		 * @param mixed   $data    Data to sanitize.
		 * @param boolean $flag    Allow HTML tags if true.
		 * @param boolean $limited Restrict allowed tags further.
		 * @return mixed Sanitized data.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_escape_slashes_deep( $data = array(), $flag = false, $limited = false ) {

			if ( true !== $flag ) {

				$data = $this->surelywp_nohtml_kses( $data );

			} elseif ( true === $limited ) {

				$data = $this->surelywp_kses_post( $data );
			}
			$data = stripslashes_deep( $data );
			return $data;
		}

		/**
		 * Sanitize data but allow safe post HTML tags.
		 *
		 * @param mixed $data Input data.
		 * @return mixed Sanitized data.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_kses_post( $data = array() ) {

			if ( is_array( $data ) ) {

				$data = array_map( array( $this, 'surelywp_kses_post' ), $data );

			} elseif ( is_string( $data ) ) {

				$data = wp_kses_post( $data );
			}

			return $data;
		}

		/**
		 * Escape a string attribute safely.
		 *
		 * @param string $result Object to convert.
		 *
		 * @package SurelyWP\Framework\Classes
		 * @since   1.0.0
		 */
		public function surelywp_object_to_array( $result ) {
			$array = array();
			foreach ( $result as $key => $value ) {
				if ( is_object( $value ) ) {
					$array[ $key ] = $this->surelywp_object_to_array( $value );
				} else {
					$array[ $key ] = $value;
				}
			}
		}
	}

}

$surelywp_model = SurelyWP_Model::instance();
