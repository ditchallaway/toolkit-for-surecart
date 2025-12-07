<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

$is_enable_lead_magnets = Surelywp_Tk_Lm::get_settings_option( 'is_enable_lead_magnets' );

	// If Lead Magnets not enable.
if ( ! $is_enable_lead_magnets ) {
	return;
}

$product_id = $attributes['product_id'] ?? '';
if ( ! $product_id ) {
	return;
}

$form_or_button = apply_filters( 'add_to_lead_magnet_button', $product_id );

?>
<div class="surelyp-tk-lm-form-block">
	<?php echo $form_or_button; // phpcs:ignore ?>
</div>
