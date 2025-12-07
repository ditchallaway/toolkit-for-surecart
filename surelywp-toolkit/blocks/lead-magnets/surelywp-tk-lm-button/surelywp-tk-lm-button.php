<?php
/**
 * Lead Magnets button block Root file.
 *
 * @package Toolkit For SureCart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 *
 * @package Toolkit For SureCart
 * @since 1.4
 */
function create_block_surelywp_tk_lm_button() {

	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_surelywp_tk_lm_button' );


if ( ! function_exists( 'surelywp_add_block_category' ) ) {
	/**
	 * Add New Catagery for Block
	 *
	 * @param array $categories The array of the categories.
	 * 
	 * @package Toolkit For SureCart
	 * @since 1.4
	 */
	function surelywp_add_block_category( $categories ) {

		$surelywp_index = array_search( 'surelywp', array_column( $categories, 'slug' ), true );

		// If already exists surelywp category.
		if ( ! empty( $surelywp_index ) ) {
			return $categories;
		}

		$surelywp_category = array(
			array(
				'slug'  => 'surelywp',
				'title' => esc_html__( 'SurelyWP', 'surelywp-toolkit' ),
			),
		);

		// Find the index of 'surecart' category.
		$index = array_search( 'surecart', array_column( $categories, 'slug' ), true );

		// Insert surelywp category after 'surecart'.
		if ( false !== $index ) {
			array_splice( $categories, $index + 1, 0, $surelywp_category );
		}

		return $categories;
	}
}
add_filter( 'block_categories_all', 'surelywp_add_block_category', 10, 1 );
