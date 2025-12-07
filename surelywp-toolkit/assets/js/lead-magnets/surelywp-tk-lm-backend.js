"use strict";

jQuery(document).ready(function ($) {

    var lm_active_tab_id = localStorage.getItem('lm_active_tab_id')
    if (!lm_active_tab_id) {
        lm_active_tab_id = 'lm-products-tab';
    }

    // set the active tab.
    setTimeout(function () {
        $('#' + lm_active_tab_id).trigger('click');
    }, 100);

    // Manage the tab swtiching.
    $(document).on('click', '.lead-magnets-settings-tabs .surelywp-btn', function (e) {

        $(this).addClass('active').closest('.lead-magnets-settings-tabs').find('.surelywp-btn').not(this).removeClass('active');

        let tab_id = $(this).attr('id');

        // Save the active tab id.
        localStorage.setItem('lm_active_tab_id', tab_id);

        $('.tab-settings').addClass('hidden');
        switch (tab_id) {
            case 'lm-products-tab':
                $('#lm-products-settings').removeClass('hidden');
                break;
            case 'lm-settings-tab':
                $('#subscription-form-settings').removeClass('hidden');
                break;
            case 'lm-fields-tab':
                $('#subscription-form-fields-settings').removeClass('hidden');
                break;
            case 'lm-verification-tab':
                $('#verification-email-settings').removeClass('hidden');
                break;
            case 'lm-customer-dashboard-tab':
                $('.customer-dashboard-settings').removeClass('hidden');
                break;
        }
    });

    $(document).on('change', '#is-enable-lead-magnets', function (e) {
        $('#lead-magnets-product-options').toggle();
    });

    $(document).on('change', '#lm-products-selections', function (e) {
        let lm_product_type = $(this).val();
        switch (lm_product_type) {
            case 'all':
                $('#specific-product-selection-div').addClass('hidden');
                $('#specific-product-collection-selection-div').addClass('hidden');
                break;
            case 'specific':
                $('#specific-product-selection-div').removeClass('hidden');
                $('#specific-product-collection-selection-div').addClass('hidden');
                break;
            case 'specific_collection':
                $('#specific-product-collection-selection-div').removeClass('hidden');
                $('#specific-product-selection-div').addClass('hidden');
                break;
        }
    });

    $(document).on('change', '#sub-form-method', function (e) {
        let sub_form_method = $(this).val();
        switch (sub_form_method) {
            case 'popup_form':
                $('#popup-form-settings').removeClass('hidden');
                $('#inline-form-settings').addClass('hidden');
                break;
            case 'inline_form':
                $('#inline-form-settings').removeClass('hidden');
                $('#popup-form-settings').addClass('hidden');
                break;
        }
    });

    // Make Sub Form Fields Sortable.
    if ($('#sub-form-fields-fields').length) {
        $('#sub-form-fields-fields').sortable({
            handle: ".field-drag-handle",
            containment: "#sub-form-fields-fields",
            items: "> .surelywp-sortable-field:not(.no-sort)",
            cancel: ".no-sort",
            cursor: 'row-resize',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            update: function (event, ui) {

                $('#sub-form-fields-fields .surelywp-sortable-field').each(function (index, element) {
                    $('.sub-form-field-position', element).attr('value', index);
                });
            }
        });
    }


    // Toggle Admin columns settings.
    $(document).on('click', '.column-close-icon, .surelywp-sortable-field-heading-top.close', function (e) {
        $(this).closest('.surelywp-sortable-field').find('.column-open-icon').removeClass('hidden');
        $(this).closest('.surelywp-sortable-field').find('.column-close-icon').addClass('hidden');
        $(this).closest('.surelywp-sortable-field').find('.surelywp-sortable-field-options').slideUp();
        $(this).closest('.surelywp-sortable-field').find('.surelywp-sortable-field-heading-top').addClass('open').removeClass('close');
        $(this).closest('.surelywp-sortable-field').toggleClass('open');
    });

    $(document).on('click', '.column-open-icon, .surelywp-sortable-field-heading-top.open', function (e) {
        $(this).closest('.surelywp-sortable-field').find('.column-open-icon').addClass('hidden');
        $(this).closest('.surelywp-sortable-field').find('.column-close-icon').removeClass('hidden');
        $(this).closest('.surelywp-sortable-field').find('.surelywp-sortable-field-options').slideDown();
        $(this).closest('.surelywp-sortable-field').find('.surelywp-sortable-field-heading-top').addClass('close').removeClass('open');
        $(this).closest('.surelywp-sortable-field').toggleClass('open');
    });

    $(document).on('change', '#sub-form-fields-fields .surelywp-sortable-field .is-show-field', function () {
        $(this).closest('.surelywp-sortable-field').find('.manage-field-is-required').toggle();
    });

    $(document).on('change', '#is-require-email-verification', function () {
        $('.verification-email-options').toggle();
    });

    $(document).on('change', '#is-customer-dashboard-enable', function () {
        $('.customer-dashboard-options').toggle();
    });

    // For Lead Magnet enable block on surecart admin product edit page.
    if ($('#surelywp-lm-product-block').length > 0) {
        var product_lm_block = $('#surelywp-lm-product-block');
        product_lm_block.remove();
        $('.css-wzxb7d > div').eq(0).after(product_lm_block);
    }

    $(document).on("scChange", '#surelywp-lm-enable-switch', function (event) {

        var lm_switch = $('#surelywp-lm-enable-switch');
        lm_switch.addClass('cursor-not-allow');
        var is_lm_enable = event.target.checked ? '1' : '0';
        var product_id = $('#lm-product-id').val();

        $.ajax({
            url: tk_lm_backend_ajax_object.ajax_url,
            dataType: 'json',
            type: 'POST',
            data: {
                action: 'surelywp_tk_lm_toggle_product_lead',
                nonce: tk_lm_backend_ajax_object.nonce,
                is_lm_enable: is_lm_enable,
                product_id: product_id,
            },

            success: function (res) {
                lm_switch.removeClass('cursor-not-allow');
                if (res.status) {
                    $('.css-uksis0').append('<div class="surelywp-product-updated" style="height: auto; opacity: 1;"><div class="components-snackbar-list__notice-container"><div class="components-snackbar" tabindex="0" role="button" aria-label="Dismiss this notice"><div class="components-snackbar__content">Product updated.</div></div></div></div>');
                    setTimeout(function () {
                        $('.surelywp-product-updated').remove();
                    }, 2000);
                }
            }
        }).catch((error) => {
            console.error("Error:", error);
        });
    });
});