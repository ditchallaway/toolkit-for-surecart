"use strict";

jQuery(document).ready(function ($) {

    // Admin Column Tab settings.
    // Make Admin Columns Fields Sortable.
    if ($('#admin-columns-fields').length) {
        $('#admin-columns-fields').sortable({
            handle: ".field-drag-handle",
            containment: "#admin-columns-fields",
            items: "> .admin-column-field:not(.no-sort)",
            cancel: ".no-sort",
            cursor: 'row-resize',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            update: function (event, ui) {

                $('.admin-columns-fields .admin-column-field.order').each(function (index, element) {

                    $('.admin-column-field-position', element).attr('value', index);
                });

                $('.admin-columns-fields .admin-column-field.product').each(function (index, element) {

                    $('.admin-column-field-position', element).attr('value', index);
                });
            }
        });
    }

    // Admin Colunms Order settings
    $(document).on('click', '#order-columns-btn', function (e) {

        $(this).addClass('active').closest('.columns-btns').find('a').not(this).removeClass('active');

        $('#product-columns-settings').addClass('hidden');
        $('#order-columns-settings').removeClass('hidden');

        if ($('#surelywp-tk-order-column-status').is(':checked')) {
            $('#admin-columns-fields .admin-column-field.order').removeClass('hidden');
        }
        $('#admin-columns-fields .admin-column-field.product').addClass('hidden');
    });

    $(document).on('change', '#surelywp-tk-order-column-status', function (e) {
        $('#admin-columns-fields .admin-column-field.order').toggleClass('hidden');
    });


    // Admin Colunms Product settings
    $(document).on('click', '#product-columns-btn', function (e) {

        $(this).addClass('active').closest('.columns-btns').find('a').not(this).removeClass('active');

        $('#order-columns-settings').addClass('hidden');
        $('#product-columns-settings').removeClass('hidden');

        if ($('#surelywp-tk-product-column-status').is(':checked')) {
            $('#admin-columns-fields .admin-column-field.product').removeClass('hidden');
        }
        $('#admin-columns-fields .admin-column-field.order').addClass('hidden');
    });

    $(document).on('change', '#surelywp-tk-product-column-status', function (e) {
        $('#admin-columns-fields .admin-column-field.product').toggleClass('hidden');
    });

    // Toggle Admin columns settings.
    $(document).on('click', '.column-close-icon, .admin-column-heading-top.close', function (e) {
        $(this).closest('.admin-column-field').find('.column-open-icon').removeClass('hidden');
        $(this).closest('.admin-column-field').find('.column-close-icon').addClass('hidden');
        $(this).closest('.admin-column-field').find('.admin-column-field-options').slideUp();
        $(this).closest('.admin-column-field').find('.admin-column-heading-top').addClass('open').removeClass('close');
        $(this).closest('.admin-column-field').toggleClass('open');
    });

    $(document).on('click', '.column-open-icon, .admin-column-heading-top.open', function (e) {
        $(this).closest('.admin-column-field').find('.column-open-icon').addClass('hidden');
        $(this).closest('.admin-column-field').find('.column-close-icon').removeClass('hidden');
        $(this).closest('.admin-column-field').find('.admin-column-field-options').slideDown();
        $(this).closest('.admin-column-field').find('.admin-column-heading-top').addClass('close').removeClass('open');
        $(this).closest('.admin-column-field').toggleClass('open');
    });

    // Product name Column settings.
    $(document).on('change', '.admin-column-field-options.product_name .is-show-column', function (e) {
        $('#tk-ac-product-featured-image-setting').toggle();
    });

    if ($('.surecart_page_sc-orders').length) {

        // Order Product Names and  Subsctiption type column.
        surelywpLoadOrderInfo($);

        // Recovery Status Column. 
        surelywpLoadRecoveryStatus($);
    }

    var order_column_status = tk_ac_backend_ajax_object.order_column_status;
    var product_column_status = tk_ac_backend_ajax_object.product_column_status;
    var order_columns = tk_ac_backend_ajax_object.admin_columns.order;
    var product_columns = tk_ac_backend_ajax_object.admin_columns.product;

    // Order Columns
    if (order_column_status) {

        // Order Number
        if (order_columns.order_number.is_show) {
            $('.surecart_page_sc-orders #order').html(order_columns.order_number.label);
        } else {
            $('.surecart_page_sc-orders .column-order').hide();
        }

        // Order Status 
        if (order_columns.order_payment_status.is_show) {
            $('.surecart_page_sc-orders #status').html(order_columns.order_payment_status.label);
        } else {
            $('.surecart_page_sc-orders .column-status').hide();
        }

        // Order fulfillment_status 
        if (order_columns.order_fulfillment_status.is_show) {
            $('.surecart_page_sc-orders #fulfillment_status').html(order_columns.order_fulfillment_status.label);
        } else {
            $('.surecart_page_sc-orders .column-fulfillment_status').hide();
        }

        // Order shipment_status 
        if (order_columns.order_shipping_status.is_show) {
            $('.surecart_page_sc-orders #shipment_status').html(order_columns.order_shipping_status.label);
        } else {
            $('.surecart_page_sc-orders .column-shipment_status').hide();
        }

        // Order method
        if (order_columns.order_payment_method.is_show) {
            $('.surecart_page_sc-orders #method').html(order_columns.order_payment_method.label);
        } else {
            $('.surecart_page_sc-orders .column-method').hide();
        }

        // Order integrations
        if (order_columns.order_integrations.is_show) {
            $('.surecart_page_sc-orders #integrations').html(order_columns.order_integrations.label);
        } else {
            $('.surecart_page_sc-orders .column-integrations').hide();
        }

        // Order total
        if (order_columns.order_total.is_show) {
            $('.surecart_page_sc-orders #total').html(order_columns.order_total.label);
        } else {
            $('.surecart_page_sc-orders .column-total').hide();
        }

        // Order type
        if (order_columns.order_type.is_show) {
            $('.surecart_page_sc-orders #type').html(order_columns.order_type.label);
        } else {
            $('.surecart_page_sc-orders .column-type').hide();
        }

        // Order created
        if (order_columns.order_date.is_show) {
            $('.surecart_page_sc-orders #created').html(order_columns.order_date.label);
        } else {
            $('.surecart_page_sc-orders .column-created').hide();
        }
    }

    // Product Columns
    if (product_column_status) {

        // product name
        if (product_columns.product_name.is_show) {
            $('.surecart_page_sc-products #name').html(product_columns.product_name.label);

            if (!product_columns.product_name.is_show_featured_image) {
                $('.surecart_page_sc-products .sc-product-name img').hide();
                $('.surecart_page_sc-products .sc-product-name .sc-product-image-preview').hide();
            }
        } else {
            $('.surecart_page_sc-products .column-name').hide();
        }

        // product price 
        if (product_columns.product_price.is_show) {
            $('.surecart_page_sc-products #price').html(product_columns.product_price.label);
        } else {
            $('.surecart_page_sc-products .column-price').hide();
        }

        // product commission_amount 
        if (product_columns.product_commission_amount.is_show) {
            $('.surecart_page_sc-products #commission_amount').html(product_columns.product_commission_amount.label);
        } else {
            $('.surecart_page_sc-products .column-commission_amount').hide();
        }

        // product quantity 
        if (product_columns.product_quantity.is_show) {
            $('.surecart_page_sc-products #quantity').html(product_columns.product_quantity.label);
        } else {
            $('.surecart_page_sc-products .column-quantity').hide();
        }

        // product integrations
        if (product_columns.product_integrations.is_show) {
            $('.surecart_page_sc-products #integrations').html(product_columns.product_integrations.label);
        } else {
            $('.surecart_page_sc-products .column-integrations').hide();
        }

        // product product_collections
        if (product_columns.product_collections.is_show) {
            $('.surecart_page_sc-products #product_collections').html(product_columns.product_collections.label);
        } else {
            $('.surecart_page_sc-products .column-product_collections').hide();
        }

        // product status
        if (product_columns.product_page_publish_status.is_show) {
            $('.surecart_page_sc-products #status').html(product_columns.product_page_publish_status.label);
        } else {
            $('.surecart_page_sc-products .column-status').hide();
        }

        // product featured
        if (product_columns.featured_product.is_show) {
            $('.surecart_page_sc-products #featured').html(product_columns.featured_product.label);
        } else {
            $('.surecart_page_sc-products .column-featured').hide();
        }

        // product created date
        if (product_columns.product_date_created.is_show) {
            $('.surecart_page_sc-products #date').html(product_columns.product_date_created.label);
        } else {
            $('.surecart_page_sc-products .column-date').hide();
        }
    }
});


/**
 * Fetch and populate Order Product Names & Subscription Types.
 */
function surelywpLoadOrderInfo($) {

    const checkoutIdMap = {};

    // Collect unique checkout IDs
    $('.surelywp-order-product-names, .surelywp-subscription-type').each(function () {
        const checkoutId = $(this).data('checkout-id');
        if (checkoutId) {
            checkoutIdMap[checkoutId] = true;
        }
    });

    const checkoutIds = Object.keys(checkoutIdMap);
    if (checkoutIds.length === 0) return;

    $.ajax({
        url: tk_ac_backend_ajax_object.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'surelywp_tk_ac_get_order_info',
            checkout_ids: checkoutIds,
            nonce: tk_ac_backend_ajax_object.admin_ajax_nonce
        },
        success: function (response) {
            if (response.success) {
                $.each(response.data, function (checkoutId, data) {
                    $(`.surelywp-order-product-names[data-checkout-id="${checkoutId}"]`)
                        .html(data.product_tag || '-');

                    $(`.surelywp-subscription-type[data-checkout-id="${checkoutId}"]`)
                        .html(data.subscription_tag || '-');
                });
            }
        },
        error: function () {
            console.error('Failed to fetch order info');
        }
    });
}

/**
 * Fetch and populate Recovery Status.
 */
function surelywpLoadRecoveryStatus($) {
    const checkoutIdMap = {};
    const customerIdMap = {};

    $('.surelywp-recovery-status').each(function () {
        const checkoutId = $(this).data('checkout-id');
        const customerId = $(this).data('customer-id');

        if (checkoutId) checkoutIdMap[checkoutId] = true;
        if (customerId) customerIdMap[customerId] = true;
    });

    const checkoutIds = Object.keys(checkoutIdMap);
    const customerIds = Object.keys(customerIdMap);

    if (checkoutIds.length === 0 || customerIds.length === 0) return;

    $.ajax({
        url: tk_ac_backend_ajax_object.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'surelywp_tk_ac_get_recovery_status',
            checkout_ids: checkoutIds,
            customer_ids: customerIds,
            nonce: tk_ac_backend_ajax_object.admin_ajax_nonce
        },
        success: function (response) {
            $('.surelywp-recovery-status').each(function () {
                const $element = $(this);
                const checkoutId = $element.data('checkout-id');

                const tagHtml = response.success && response.data[checkoutId]
                    ? response.data[checkoutId].recovery_tag
                    : '-';

                $element.html(tagHtml);
            });
        },
        error: function () {
            $('.surelywp-recovery-status').each(function () {
                $(this).html('-');
            });
        }
    });
}