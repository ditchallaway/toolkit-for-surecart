<?php
/**
 * Prices Descriptions metabox.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.2
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

use SureCart\Support\Currency;

global $post, $surelywp_model, $sc_product;

$prices_counts = $sc_product->prices->pagination->count ?? 0;

if ( ! $sc_product && ! $prices_counts ) {
	return;
}

$prefix                       = SURELYWP_TOOLKIT_META_PREFIX;
$product_post_id              = surelywp_tk_get_product_post_id( $product_id );
$misc_price_desc              = get_post_meta( $product_post_id, $prefix . 'misc_price_desc', true );
$misc_price_desc_display_type = get_post_meta( $product_post_id, $prefix . 'misc_price_desc_display_type', true );

if ( empty( $misc_price_desc_display_type ) ) {
	$misc_price_desc_display_type = 'display_selected';
}

$product_prices = $sc_product->prices->data ?? '';

?>
<div class="surelywp_tk_misc_price_desc hidden" id="surelywp_tk_misc_price_desc">
	<div data-wp-c16t="true" data-wp-component="Card" class="components-surface components-card css-1st819u css-443a5t e19lxcc00 css-talpf1">
		<div class="css-10klw3m e19lxcc00">
			<div data-wp-c16t="true" data-wp-component="CardHeader" class="components-flex components-card__header components-card-header css-15hyllm css-1w3sexp e19lxcc00 css-1v6oc13"><sc-text tag="h2" class="hydrated" style="--font-size: 15px; --font-weight: var(--sc-font-weight-bold); width: 100%;"><?php echo esc_html__( 'Price Descriptions', 'surelywp-toolkit' ); ?></div>
			<div data-wp-c16t="true" data-wp-component="CardBody" class="components-card__body components-card-body css-1xfafpv css-1dzvnua e19lxcc00 css-1pa1sky">
				<div class="surelyp-tk-misc-price-desc-meta">
					<div class="css-1nclkq9">
						<input type="hidden" name="post_id" class="surelywp-tk-product-post-id" value="<?php echo esc_attr( $product_post_id ); ?>">
						<?php if ( $prices_counts > 1 ) { ?>
							<sc-select id="surelywp-price-desc-display-type" class="surelywp-price-desc-display-type" label="<?php esc_html_e( 'Price Description Display', 'surelywp-toolkit' ); ?>" name="<?php echo esc_attr( $prefix . 'misc_price_desc_display_type' ); ?>" help="<?php esc_html_e( 'Choose how price descriptions are shown.', 'surelywp-toolkit' ); ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $misc_price_desc_display_type ); ?>"></sc-select>
						<?php } ?>
						<?php
						if ( $product_prices ) {

							usort(
								$product_prices,
								function ( $a, $b ) {
									return $a['position'] <=> $b['position'];
								}
							);

							foreach ( $product_prices as $price ) {

								$price_id       = $price->id ?? '';
								$name           = $price->name ?? '';
								$amount         = $price->amount ?? '';
								$currency       = $price->currency ?? '';
								$formated_price = Currency::format( $amount, $currency );
								$value          = ! empty( $misc_price_desc ) && isset( $misc_price_desc[ $price_id ] ) ? $misc_price_desc[ $price_id ] : '';
								?>
								<sc-textarea class="surelyp-tk-misc-price-desc-input" value="<?php echo $surelywp_model->surelywp_escape_attr( $value ); ?>" type="text" label="
								<?php
								printf(
									// translators: %1$s is the price name, %2$s is the formatted price.
									esc_html__( '%1$s %2$s Price Description', 'surelywp-toolkit' ),
									esc_html( $name ),
									esc_html( $formated_price )
								);
								?>
									"
									name="<?php echo esc_attr( $prefix . 'misc_price_desc[' . $price_id . ']' ); ?>"></sc-textarea>
								<?php
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
