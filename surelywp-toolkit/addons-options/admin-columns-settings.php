<?php
/**
 * Admin Columns Settings.
 *
 * @package Toolkit For Surecart
 * @since   1.2
 */

$columns = array(
	'order'   => array(
		'order_number'             =>
		array(
			'heading'           => esc_html__( 'Order Number', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Order', 'surelywp-toolkit' ),
			'position'          => 0,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_payment_status'     =>
		array(
			'heading'           => esc_html__( 'Order Payment Status', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Status', 'surelywp-toolkit' ),
			'position'          => 1,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_fulfillment_status' =>
		array(
			'heading'           => esc_html__( 'Order Fulfillment Status', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Fulfillment', 'surelywp-toolkit' ),
			'position'          => 2,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_shipping_status'    =>
		array(
			'heading'           => esc_html__( 'Order Shipping Status', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Shipping', 'surelywp-toolkit' ),
			'position'          => 3,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_payment_method'     =>
		array(
			'heading'           => esc_html__( 'Order Payment Method', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Method', 'surelywp-toolkit' ),
			'position'          => 4,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_integrations'       =>
		array(
			'heading'           => esc_html__( 'Order Integrations', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Integrations', 'surelywp-toolkit' ),
			'position'          => 5,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_total'              =>
		array(
			'heading'           => esc_html__( 'Order Total', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Total', 'surelywp-toolkit' ),
			'position'          => 6,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_type'               =>
		array(
			'heading'           => esc_html__( 'Order Type', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Type', 'surelywp-toolkit' ),
			'position'          => 7,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_date'               =>
		array(
			'heading'           => esc_html__( 'Order Date', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Date', 'surelywp-toolkit' ),
			'position'          => 8,
			'is_default_column' => true,
			'is_show'           => true,
		),
		'order_product_name'       =>
		array(
			'heading'           => esc_html__( 'Product Name', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Product Name', 'surelywp-toolkit' ),
			'position'          => 9,
			'is_default_column' => false,
			'is_show'           => false,
		),
		'subscription_type'        =>
		array(
			'heading'           => esc_html__( 'Subscription Type', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Subscription Type', 'surelywp-toolkit' ),
			'position'          => 10,
			'is_default_column' => false,
			'is_show'           => false,
		),
		'trial'                    =>
		array(
			'heading'           => esc_html__( 'Trial', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Trial', 'surelywp-toolkit' ),
			'position'          => 11,
			'is_default_column' => false,
			'is_show'           => false,
		),
		'recovery_status'          =>
		array(
			'heading'           => esc_html__( 'Recovery Status', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Recovery Status', 'surelywp-toolkit' ),
			'position'          => 12,
			'is_default_column' => false,
			'is_show'           => false,
		),
		'invoice'                  =>
		array(
			'heading'           => esc_html__( 'Invoice', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Invoice', 'surelywp-toolkit' ),
			'position'          => 13,
			'is_default_column' => false,
			'is_show'           => false,
		),
		'lead-magnet'              =>
		array(
			'heading'           => esc_html__( 'Lead Magnet', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Lead Magnet', 'surelywp-toolkit' ),
			'position'          => 14,
			'is_default_column' => false,
			'is_show'           => false,
		),
	),
	'product' => array(
		'product_name'                =>
		array(
			'heading'                => esc_html__( 'Product Name', 'surelywp-toolkit' ),
			'label'                  => esc_html__( 'Name', 'surelywp-toolkit' ),
			'position'               => 0,
			'is_show_featured_image' => true,
			'is_default_column'      => true,
			'is_show'                => true,
		),
		'product_price'               =>
		array(
			'heading'           => esc_html__( 'Product Price', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Price', 'surelywp-toolkit' ),
			'position'          => 1,
			'is_default_column' => true,
			'is_show'           => true,

		),
		'product_commission_amount'   =>
		array(
			'heading'           => esc_html__( 'Product Commission Amount', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Commission Amount', 'surelywp-toolkit' ),
			'position'          => 2,
			'is_default_column' => true,
			'is_show'           => true,

		),
		'product_quantity'            =>
		array(
			'heading'           => esc_html__( 'Product Quantity', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Quantity', 'surelywp-toolkit' ),
			'position'          => 3,
			'is_default_column' => true,
			'is_show'           => true,

		),
		'product_integrations'        =>
		array(
			'heading'           => esc_html__( 'Product Integrations', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Integrations', 'surelywp-toolkit' ),
			'position'          => 4,
			'is_default_column' => true,
			'is_show'           => true,

		),
		'product_collections'         =>
		array(
			'heading'           => esc_html__( 'Product Collections', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Collections', 'surelywp-toolkit' ),
			'position'          => 5,
			'is_default_column' => true,
			'is_show'           => true,

		),
		'product_page_publish_status' =>
		array(
			'heading'           => esc_html__( 'Product Page Publish Status', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Product Page', 'surelywp-toolkit' ),
			'position'          => 6,
			'is_default_column' => true,
			'is_show'           => true,

		),
		'featured_product'            =>
		array(
			'heading'           => esc_html__( 'Featured Product', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Featured', 'surelywp-toolkit' ),
			'position'          => 7,
			'is_default_column' => true,
			'is_show'           => true,

		),
		'product_date_created'        =>
		array(
			'heading'           => esc_html__( 'Product Date Created', 'surelywp-toolkit' ),
			'label'             => esc_html__( 'Date', 'surelywp-toolkit' ),
			'position'          => 8,
			'is_default_column' => true,
			'is_show'           => true,

		),
	),
);

?>
<table class="form-table surelywp-ric-settings-box toolkit-templates-table admin-columns-settings">
	<tbody>
		<tr class="surelywp-field-label">
			<td>
				<div class="form-control">
					<div class="columns-btns">
						<div class="order-column-btn-wrap surelywp-tab">
							<a href="javascript:void(0)" id="order-columns-btn" class="surelywp-btn active"><?php esc_html_e( 'Order Columns', 'surelywp-toolkit' ); ?></a>
						</div>
						<div class="product-column-btn-wrap surelywp-tab">
							<a href="javascript:void(0)" id="product-columns-btn" class="surelywp-btn"><?php esc_html_e( 'Product Columns', 'surelywp-toolkit' ); ?></a>
						</div>
					</div>
				</div>
			</td>
		</tr>
		<tr class="surelywp-field-label" id="surelywp-tk-ac-settings">
			<td>
				<div class="form-control" id="order-columns-settings">
					<div class="input-label"><?php echo esc_html_e( 'Enable Custom Order Columns Feature', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this feature to customize the SureCart orders list columns. You can edit, rearrange, or hide columns to suit your needs.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-order-column-status" name="<?php echo $addons_option_key . '[order_column_status]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['order_column_status'] ) ) ? checked( $settings_options['order_column_status'], 1, true ) : ''; ?> size="10" />
						<input type="hidden" name="<?php echo $addons_option_key . '[tk_ac_update_options]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( 'updated' ); //phpcs:ignore ?>" />
						<?php $surelywp_tk_ac_option_nonce = wp_create_nonce( 'surelywp_tk_ac_option_nonce' ); ?>
						<input type="hidden" name="<?php echo $addons_option_key . '[surelywp_tk_ac_option_nonce]'; ?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $surelywp_tk_ac_option_nonce ); //phpcs:ignore?>" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="form-control hidden" id="product-columns-settings">
					<div class="input-label"><?php echo esc_html_e( 'Enable Custom Product Columns Feature', 'surelywp-toolkit' ); ?></div>
					<label><?php esc_html_e( 'Enable this feature to customize the SureCart product list columns. You can edit, rearrange, or hide columns to suit your needs.', 'surelywp-toolkit' ); ?></label>
					<label class="toggleSwitch xlarge" onclick="">
						<input type="checkbox" id="surelywp-tk-product-column-status" name="<?php echo $addons_option_key . '[product_column_status]'; //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo ( isset( $settings_options['product_column_status'] ) ) ? checked( $settings_options['product_column_status'], 1, true ) : ''; ?> size="10" />
						<span>
							<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
							<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
						</span>
						<a></a>
					</label>
				</div>
				<div class="admin-columns-fields" id="admin-columns-fields">
					<?php

					if ( isset( $settings_options['admin_columns'] ) ) {
						$admin_colums = $settings_options['admin_columns'];
					} else {
						$admin_colums = $columns; // Default settings.
					}

					foreach ( $admin_colums as $column_name => $column_values ) {

						foreach ( $column_values as $key => $column ) {
							$admin_column_key = '[admin_columns][' . $column_name . '][' . $key . ']';
							$column_key       = $addons_option_key . $admin_column_key;
							$values           = $admin_colums[ $column_name ][ $key ] ?? array();

							?>
						<div class="admin-column-field surelywp-list-tab <?php printf( '%s %s %s', esc_html( $column_name ), ( ! isset( $settings_options['order_column_status'] ) || 'order' !== $column_name ? 'hidden' : '' ), $values['is_default_column'] ? 'no-sort' : '' ); ?>">
							<input type="hidden" class="admin-column-field-position" name="<?php echo esc_attr( $column_key. '[position]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $values['position'] ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $column_key. '[is_default_column]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $values['is_default_column'] ); ?>">
							<div class="admin-column-top">
								<div class="top-left">
									<div class="column-toogle-btn">
										<img class="column-open-icon" src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/open-down-icon.svg' ); ?>" alt="<?php esc_attr_e( 'open', 'surelywp-toolkit' ); ?>">
										<img class="column-close-icon hidden" src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/open-up-icon.svg' ); ?>" alt="<?php esc_attr_e( 'close', 'surelywp-toolkit' ); ?>">
									</div>
									<div class="admin-column-heading-top open">
										<?php echo esc_html( $columns[ $column_name ][ $key ]['heading'] ); ?>
									</div>
								</div>
								<?php if ( ! $values['is_default_column'] ) { ?>
								<div class="field-actions">
									<div class="field-drag-handle">
										<img src="<?php echo esc_url( SURELYWP_TOOLKIT_URL . 'assets/images/drag-icon.svg' ); ?>" alt="<?php esc_attr_e( 'drag', 'surelywp-toolkit' ); ?>">
									</div>
								</div>
								<?php } ?>
							</div>
							<div class="admin-column-field-options <?php echo esc_html( $key ); ?> hidden">
								<div class="form-control">
									<div class="input-label"><?php echo esc_html_e( 'Column Label', 'surelywp-toolkit' ); ?></div>
									<label>
										<?php
											// translators: %1$s is the column heading, %2$s is the column type (order/product).
											printf( esc_html__( 'Enter the text to display as the %1$s column header in the SureCart %2$ss list.', 'surelywp-toolkit' ), esc_html( strtolower( $columns[ $column_name ][ $key ]['heading'] ) ), esc_html( $column_name ) );
										?>
										</label>
									<?php $column_label = isset( $values['label'] ) && ! empty( $values['label'] ) ? $values['label'] : $columns[ $column_name ][ $key ]['label'] ?? ''; ?>
									<input type="text" class="widefat" name="<?php echo esc_attr( $column_key. '[label]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( $column_label ); ?>" />
								</div>
								<div class="form-control">
									<div class="input-label"><?php echo esc_html_e( 'Show Column', 'surelywp-toolkit' ); ?></div>
									<label>
										<?php
											// translators: %1$s is the column heading, %2$s is the column type (order/product).
											printf( esc_html__( 'Display the %1$s column in the SureCart %2$ss list.', 'surelywp-toolkit' ), esc_html( strtolower( $columns[ $column_name ][ $key ]['heading'] ) ), esc_html( $column_name ) );
										?>
									</label>
									<?php $is_show = ! isset( $settings_options['tk_ac_update_options'] ) ? $columns[ $column_name ][ $key ]['is_show'] : $values['is_show'] ?? ''; ?>
									<label class="toggleSwitch xlarge" onclick="">
										<input type="checkbox" class="is-show-column" name="<?php echo esc_attr( $column_key. '[is_show]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo checked( $is_show, 1, true ); ?> size="10" />
										<span>
											<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
											<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
										</span>
										<a></a>
									</label>
								</div>
								<?php if ( 'product_name' === $key ) { ?>
									<div class="form-control <?php echo isset( $values['is_show'] ) ? '' : 'hidden'; ?>" id="tk-ac-product-featured-image-setting">
										<div class="input-label"><?php echo esc_html_e( 'Show Product Featured Image', 'surelywp-toolkit' ); ?></div>
										<label><?php esc_html_e( 'Display the product featured image in the product name column in the SureCart products list.', 'surelywp-toolkit' ); ?></label>
										<label class="toggleSwitch xlarge" onclick="">
											<input type="checkbox" name="<?php echo esc_attr( $column_key. '[is_show_featured_image]' ); //phpcs:ignore?>" value="<?php echo $surelywp_model->surelywp_escape_attr( '1' ); ?>" <?php echo checked( isset($values['is_show_featured_image']), 1, true ); ?> size="10" />
											<span>
												<span><?php echo esc_html_e( 'No', 'surelywp-toolkit' ); ?></span>
												<span><?php echo esc_html_e( 'Yes', 'surelywp-toolkit' ); ?></span>
											</span>
											<a></a>
										</label>
									</div>
								<?php } ?>
							</div>
						</div>
							<?php
						}
					}
					?>
						
				</div>
			</td>
		</tr>
	</tbody>
</table>