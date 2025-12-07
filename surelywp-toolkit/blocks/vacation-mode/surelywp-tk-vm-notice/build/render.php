<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
$vacation_id = Surelywp_Tk_Vm::surelywp_tk_vm_get_active_vacation_id();
if ( $vacation_id ) {

	// enqueue style.
	Surelywp_Tk_Vm::surelywp_tk_vm_script_enqueue();
	$notice_html = Surelywp_Tk_Vm::get_vacation_notice_html( $vacation_id );
	if ( $notice_html ) {
		?>
	<div class="surelywp-tk-vm-notice-block">
		<?php echo $notice_html; ?>
	</div>
		<?php
	}
}