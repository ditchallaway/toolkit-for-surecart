"use strict";

jQuery(document).ready(function ($) {
    $(document).on('change', '#surelywp-tk-pv-status', function(e){
        $('#surelywp-tk-pv-product-settings').toggle();
        $('#surelywp-tk-pv-shop-settings').toggle();
    });
});