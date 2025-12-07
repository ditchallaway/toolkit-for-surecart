<?php
/**
 * External products metabox.
 *
 * @package Toolkit For SureCart
 * @author  SurelyWP
 * @version 1.2
 */

// Check if the main plugin constant is defined, exit if not defined.
if ( ! defined( 'SURELYWP_TOOLKIT' ) ) {
	exit;
}

global $post, $surelywp_model;

$prefix = SURELYWP_TOOLKIT_META_PREFIX;

$product_post_id           = surelywp_tk_get_product_post_id( $product_id );
$external_product_btn_url  = get_post_meta( $product_post_id, $prefix . 'misc_external_product_url', true );
$external_product_btn_text = get_post_meta( $product_post_id, $prefix . 'misc_external_product_btn_text', true );
if ( empty( $external_product_btn_text ) ) {
	$external_product_btn_text = esc_html__( 'Buy Now', 'surelywp-toolkit' );
}

$external_product_open_new_tab = get_post_meta( $product_post_id, $prefix . 'misc_external_product_open_new_tab', true );

?>
<div class="surelywp_tk_misc_external_product hidden" id="surelywp_tk_misc_external_product">
	<div data-wp-c16t="true" data-wp-component="Card" class="components-surface components-card css-1st819u css-443a5t e19lxcc00 css-talpf1">
		<div class="css-10klw3m e19lxcc00">
			<div data-wp-c16t="true" data-wp-component="CardHeader" class="components-flex components-card__header components-card-header css-15hyllm css-1w3sexp e19lxcc00 css-1v6oc13"><sc-text tag="h2" class="hydrated" style="--font-size: 15px; --font-weight: var(--sc-font-weight-bold); width: 100%;"><?php echo esc_html__( 'External Products', 'surelywp-toolkit' ); ?></div>
			<div data-wp-c16t="true" data-wp-component="CardBody" class="components-card__body components-card-body css-1xfafpv css-1dzvnua e19lxcc00 css-1pa1sky">
				<div class="surelyp-tk-misc-external-product-meta">
					<div class="css-1nclkq9">
						<input type="hidden" name="post_id" class="surelywp-tk-product-post-id" value="<?php echo esc_attr( $product_post_id ); ?>">
						<sc-input id="surelywp-tk-external-product-btn-url" type="url" value="<?php echo $surelywp_model->surelywp_escape_attr( $external_product_btn_url ); ?>" name="<?php echo esc_attr( $prefix . 'misc_external_product_url' ); ?>" label="<?php esc_html_e( 'Product URL', 'surelywp-toolkit' ); ?>" help="<?php esc_html_e( 'Enter the external or affiliate product link.', 'surelywp-toolkit' ); ?>"></sc-input>
						<sc-input id="surelywp-tk-external-product-btn-text" type="text" value="<?php echo $surelywp_model->surelywp_escape_attr( $external_product_btn_text ); ?>" name="<?php echo esc_attr( $prefix . 'misc_external_product_btn_text' ); ?>" label="<?php esc_html_e( 'Button Text', 'surelywp-toolkit' ); ?>" help="<?php esc_html_e( 'Customize the button text displayed on the product page.', 'surelywp-toolkit' ); ?>"></sc-input>
						<sc-switch id="surelywp_tk-external-product-open-new-tab" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" name="<?php echo esc_attr( $prefix . 'misc_external_product_open_new_tab' ); ?>" checked="<?php echo $external_product_open_new_tab ? 'true' : 'false'; ?>"><?php esc_html_e( 'Open in New Tab', 'surelywp-toolkit' ); ?><span slot="description"><?php esc_html_e( 'If enabled, clicking the button will open the link in a new tab.', 'surelywp-toolkit' ); ?></span></sc-switch>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
