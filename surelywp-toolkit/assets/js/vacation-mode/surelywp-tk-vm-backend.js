"use strict";

jQuery(document).ready(function ($) {

    $(document).on('change', '#surelywp-tk-vm-vm-settings-status', function (e) {
        $('#surelywp-tk-vm-product-selection-settings').toggle();
        $('#surelywp-tk-vm-notice-settings').toggle();
        $('#surelywp-tk-vm-purchase-limittations-settings').toggle();
        $('#surelywp-tk-vm-vacation-schedule-settings').toggle();
        $('#surelywp-tk-vm-priority').toggle();
        $("#vm-schedule-day-of-week").select2();
    });


    // For Product selection
    $(document).on("change", "#toolkit-product-type", function () {
        var selectedOption = $(this).val();
        if (selectedOption == "all") {
            $("#specific-product-selection-div").hide();
            $("#specific-product-collection-selection-div").hide();
        } else if (selectedOption == "specific") {
            $("#specific-product-collection-selection-div").hide();
            $("#specific-product-selection-div").show();
        } else if (selectedOption == "specific_collection") {
            $("#specific-product-collection-selection-div").show();
            $("#specific-product-selection-div").hide();
        }
    });

    // notice messgae settings
    $(document).on("change", "#surelywp-tk-vm-is-show-notice", function () {
        $('#vm-notice-options').toggle();
    });

    // Vacation Schedule.
    $(document).on('change', '#surelywp-tk-vm-schedule-status', function (e) {
        $('#vm-schedule-options').toggle();
        $("#vm-schedule-day-of-week").select2();
    });

    // For schedule type selection
    $(document).on("change", "#vacation-schedule-type-selection", function () {
        var selectedOption = $(this).val();
        if ('fixed_time' === selectedOption) {
            $("#vm-fixed-time-settings").show();
            $("#vm-recurring-time-settings").hide();
        } else if ('recurring_time' === selectedOption) {
            $("#vm-recurring-time-settings").show();
            $("#vm-fixed-time-settings").hide();
            $("#vm-schedule-day-of-week").select2();
        }
    });


    // Delete Associaltive vacation modal.
    $(document).on('click', '#remove-associate-vacation-mode', function (e) {
        e.preventDefault();
        var delete_url = $(this).attr('href');
        $('.associative-vacation-delete').addClass('show-modal');
        $('#confirm-as-vacation-delete').attr('href', delete_url);
    });
    
    // close model
    $(document).on('click', '.close-modal-button', function (e) {
        $(this).closest('.modal').removeClass('show-modal');
    });
});