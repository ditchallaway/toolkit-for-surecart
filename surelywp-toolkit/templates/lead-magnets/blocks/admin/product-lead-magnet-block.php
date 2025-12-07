<?php
/**
 * Surecart admin product page Discount block
 *
 * @author  Surlywp
 * @package SurelyWP Discounts
 * @since 1.0.0
 */

$product                  = SureCart\Models\Product::find( $product_id );
$product_min_price_amount = $product->metrics->min_price_amount ?? '';

if ( is_wp_error( $product ) || empty( $product ) || 0 !== $product_min_price_amount ) {
	return;
}

// Get the options.
$is_lm_enable    = Surelywp_Tk_Lm::get_settings_option( 'is_enable_lead_magnets' );
$lm_product_type = Surelywp_Tk_Lm::get_settings_option( 'lead_magnets_product' );
$lm_products     = Surelywp_Tk_Lm::get_settings_option( 'lm_products' );

// If lead magnets is not enable and product selection is not specific.
if ( '1' !== $is_lm_enable || 'specific' !== $lm_product_type ) {
	return;
}
?>
<div class="surelywp-lm-product-block" id="surelywp-lm-product-block">
	<div data-wp-c16t="true" data-wp-component="Card" class="components-surface components-card css-1st819u css-443a5t e19lxcc00 css-talpf1">
		<div class="css-10klw3m e19lxcc00">
			<div data-wp-c16t="true" data-wp-component="CardHeader" class="components-flex components-card__header components-card-header css-15hyllm css-1w3sexp e19lxcc00 css-1v6oc13"><sc-text tag="h2" class="hydrated" style="--font-size: 15px; --font-weight: var(--sc-font-weight-bold); width: 100%;"><?php echo esc_html__( 'Lead Magnets', 'surelywp-toolkit' ); ?></div>
			<div data-wp-c16t="true" data-wp-component="CardBody" class="components-card__body components-card-body css-1xfafpv css-1dzvnua e19lxcc00 css-1pa1sky">
				<sc-switch checked="<?php echo in_array( $product_id, (array) $lm_products, true ) ? 'true' : 'false'; ?>" value="on" class="hydrated surelywp-lm-enable-switch" id="surelywp-lm-enable-switch"><?php echo esc_html__( 'Enable Lead Magnet', 'surelywp-toolkit' ); ?><span slot="description"><?php echo esc_html__( 'Choose to convert this product into a lead magnet. Note: Only free products with a single pricing option set to zero can be used as lead magnets. The Lead Magnets feature must be enabled in the addon settings and set to Specific Products.', 'surelywp-toolkit' ); ?></span></sc-switch>
				<input type="hidden" id="lm-product-id" value="<?php echo esc_attr( $product_id ); ?>">
			</div>
		</div>
		<div data-wp-c16t="true" data-wp-component="Elevation" class="components-elevation css-7g516l e19lxcc00" aria-hidden="true"></div>
		<div data-wp-c16t="true" data-wp-component="Elevation" class="components-elevation css-7g516l e19lxcc00" aria-hidden="true"></div>
	</div>
</div>
