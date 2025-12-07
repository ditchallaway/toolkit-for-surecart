<?php
/**
 * SurelyWP Panel Tab Navigation Item Template
 *
 * Renders a navigation tab (and its sub-tabs, if any) for the SurelyWP admin panel.
 *
 * @var SurelyWP_Plugin_Panel  $panel
 * @var array                  $nav_args
 * @var array                  $tab_data
 * @var string                 $tab_key
 * @package                    SurelyWP\Framework\Templates
 * @since                      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Extract Navigation Variables.
list(
	$selected_tab,
	$selected_sub_tab,
	$premium_tab_class,
	$current_page,
	$parent_page_slug
) = surelywp_framework_extract_variables(
	$nav_args,
	'current_tab',
	'current_sub_tab',
	'premium_class',
	'page',
	'parent_page'
);

// Determine Active Tab Class.
$active_class = '';
if ( $selected_tab === $tab_key ) {
	$active_class = 'nav-tab-active';
}

if ( 'premium' === $tab_key ) {
	$active_class .= ' ' . $premium_tab_class;
}

// Allow plugins/themes to modify the active tab class.
$active_class = $active_class;

// Get sub-tabs for the current tab.
$sub_tabs = $panel->retrieve_sub_tabs( $tab_key );

$first_sub_tab = false;
$has_submenu   = false;

if ( ! empty( $sub_tabs ) ) {
	$has_submenu   = true;
	$first_sub_tab = array_key_first( $sub_tabs );
}

// Generate navigation URL for the tab.
$url = $panel->surelywp_get_navigation_url(
	$current_page,
	$tab_key,
	$first_sub_tab,
	$parent_page_slug
);

// Check if the tab is currently opened.
$is_opened      = $selected_tab === $tab_key;
$active_sub_tab = isset( $_REQUEST['sub_tab'] ) && ! empty( $_REQUEST['sub_tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sub_tab'] ) ) : '';
?>
<li class="surelywp-plugin-fw-tab-element">
	<a class="nav-tab <?php echo esc_attr( $active_class ); ?>" href="<?php echo esc_url( $url ); ?>">
		<?php echo $tab_data['icon']; ?>
		<label><?php echo wp_kses_post( $tab_data['title'] ); ?></label>
	</a>
	<?php if ( $has_submenu ) { ?>
		<div class="nav-subtab-wrap">
			<ul class="nav-subtab">
				<?php foreach ( $sub_tabs as $sub_tab_key => $sub_tab_data ) { ?>
					<?php
					// Generate URL and active class for sub-tab.
					$url                  = $panel->surelywp_get_navigation_url( $current_page, $tab_key, $sub_tab_key );
					$sub_tab_active_class = $active_sub_tab === $sub_tab_key ? 'nav-sub-tab-active' : '';
					?>
					<li class="nav-subtab-item <?php echo esc_attr( $sub_tab_active_class ); ?>">
						<a href="<?php echo esc_url( $url ); ?>">
							<?php
							if ( isset( $sub_tab_data['icon'] ) && ! empty( $sub_tab_data['icon'] ) ) {
								echo $sub_tab_data['icon'];
							}
							?>
							<label><?php echo wp_kses_post( $sub_tab_data['title'] ); ?></label>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
</li>
