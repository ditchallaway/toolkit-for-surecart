<?php
/**
 * Subscription Form
 *
 * @author  Surelywp
 * @package Toolkit For SureCart
 * @since 1.3
 */

global $surelywp_model;

$sub_form_fields = self::get_settings_option( 'sub_form_fields' );
if ( ! $sub_form_fields ) {
	return;
}

$submit_button_text = self::get_settings_option( 'submit_button_text' );
if ( empty( $submit_button_text ) ) {
	$submit_button_text = esc_html__( 'Submit', 'surelywp-toolkit' );
}

?>
<div class="surelywp-subscription-form">
	<?php
	$sub_form_header_text = self::get_settings_option( 'sub_form_header_text' );
	if ( ! empty( $sub_form_header_text ) ) {
		echo '<h4 class="lm-form-title">' . esc_html( $sub_form_header_text ) . '</h4>';
	}
	?>
	<sc-form class="surelywp-tk-lm-form" id="surelywp-tk-lm-form">
		<?php wp_nonce_field( 'optin_form_submit_action', 'optin_form_submit_nonce' ); ?>
		<input type="hidden" id="surelywp-lm-product-id" name="product_id" value="<?php echo $surelywp_model->surelywp_escape_attr( $product_id ); ?>">
		<?php
		foreach ( $sub_form_fields as $field_key => $field ) {

			if ( 'email_address' === $field_key ) {
				?>
				<sc-input class="optin-form-email" label="<?php echo esc_attr( $field['label_value'] ); ?>" type="email" inputmode="email" name="optin_form_email" required></sc-input>
				<?php
			} elseif ( isset( $field['is_show'] ) && 'first_name' === $field_key ) {
				?>
				<sc-input label="<?php echo esc_attr( $field['label_value'] ); ?>" class="optin_form_first_name" type="text" name="optin_form_first_name" <?php echo isset( $field['is_required'] ) ? 'required' : ''; ?>></sc-input>
				<?php
			} elseif ( isset( $field['is_show'] ) && 'last_name' === $field_key ) {
				?>
				<sc-input label="<?php echo esc_attr( $field['label_value'] ); ?>" class="optin_form_last_name" type="text" name="optin_form_last_name" <?php echo isset( $field['is_required'] ) ? 'required' : ''; ?>></sc-input>
				<?php
			} elseif ( isset( $field['is_show'] ) && 'consent_checkbox' === $field_key ) {
				$privacy_policy_link = $field['privacy_policy_link'] ?? '';
				$consent_text        = $field['label_value'] ?? '';

				if ( $privacy_policy_link ) {
					$privacy_policy_url = '<a href="' . esc_url( $privacy_policy_link ) . '" target="_blank">' . esc_html__( 'privacy policy', 'surelywp-toolkit' ) . '</a>';
					$consent_text       = str_replace( '{privacy_policy_link}', $privacy_policy_url, $field['label_value'] );
				}
				?>
				<sc-checkbox class="optin_form_consent_checkbox" name="optin_form_consent_checkbox" value="1" <?php echo isset( $field['is_required'] ) ? 'required' : ''; ?>><?php echo wp_kses_post( $consent_text ); ?></sc-checkbox>
				<?php
			}
		}
		?>
		<sc-button class="optin-submit-btn" id="optin-submit-btn" type="primary" submit="true"><?php echo esc_html( $submit_button_text ); ?></sc-button>
	</sc-form>
</div>