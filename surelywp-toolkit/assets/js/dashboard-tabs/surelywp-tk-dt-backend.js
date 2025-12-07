"use strict";

jQuery(document).ready(function ($) {

    // Fix the select dropdown not show proper inside hidden element.
    $('.tab-restriction-user-roles, .tab-restriction-sm-access-groups').select2({
        dropdownParent: $('.toolkit-templates-table'), // some visible wrapper
        width: '100%'
    });

    function generateRandomKey(length = 4) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let key = '';
        for (let i = 0; i < length; i++) {
            key += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return key;
    }

    // Custom Customer Dashboard Tabs.
    $(document).on('change', '#surelywp-tk-misc-enable-ccd-tabs', function (e) {
        $('#surelywp-tk-misc-ccd-tabs').toggle();
    });

    $(document).on('change', '.surelywp-misc-ccd-is-show-tab', function (e) {
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-options').toggle();
    });

    // Custom Customer Dashboard Tabs settings.
    // Make Custom Customer Dashboard Tabs Sortable.
    if ($('#surelywp-tk-misc-ccd-tabs').length) {
        $('#surelywp-tk-misc-ccd-tabs').sortable({
            handle: ".field-drag-handle",
            containment: "#surelywp-tk-misc-ccd-tabs",
            items: "> .surelywp-tk-misc-ccd-tab:not(.no-sort)",
            cancel: ".no-sort",
            cursor: 'row-resize',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
        });
    }

    $(document).on('click', '.tab-close-icon, .surelywp-tk-misc-ccd-tab-heading-top.close', function (e) {
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-open-icon').removeClass('hidden');
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-close-icon').addClass('hidden');
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.surelywp-tk-misc-ccd-tab-options').slideUp();
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.surelywp-tk-misc-ccd-tab-heading-top').addClass('open').removeClass('close');
        $(this).closest('.surelywp-tk-misc-ccd-tab').toggleClass('open');
    });

    $(document).on('click', '.tab-open-icon, .surelywp-tk-misc-ccd-tab-heading-top.open', function (e) {
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-open-icon').addClass('hidden');
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-close-icon').removeClass('hidden');
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.surelywp-tk-misc-ccd-tab-options').slideDown();
        $(this).closest('.surelywp-tk-misc-ccd-tab').find('.surelywp-tk-misc-ccd-tab-heading-top').addClass('close').removeClass('open');
        $(this).closest('.surelywp-tk-misc-ccd-tab').toggleClass('open');
    });


    // Custom Customer Tab Repeater.
    $(document).on('click', '#add-new-ccd-tab-btn', function (e) {

        e.preventDefault();

        var randomKey = generateRandomKey();
        var field_key = 'surelywp_tk_dt_settings_options[sc_ccd_tabs][' + randomKey + ']';
        var editor_id = 'tab-content-' + randomKey;
        $('.tab-restriction-user-roles, .tab-restriction-sm-access-groups').select2('destroy');
        var new_field = $('.surelywp-tk-misc-ccd-tab:first').clone();


        new_field.find('.surelywp-tk-misc-ccd-tab-remove').removeClass('hidden');
        new_field.find('.tab-behavior-setting').removeClass('hidden');
        new_field.find('.tab-restriction-setting').removeClass('hidden');

        new_field.find('.surelywp-misc-ccd-is-show-tab-label').html('Show Tab');
        new_field.find('.surelywp-tk-misc-ccd-tab-heading-top').html('');
        new_field.find('.tab-heading').val('').attr('name', field_key + '[heading]');
        new_field.find('.surelywp-misc-ccd-is-show-tab').attr('name', field_key + '[is_show]');
        new_field.find('.is-sc-tab').val('').attr('name', field_key + '[is_sc_tab]');
        new_field.find('.tab-name').val('').attr('name', field_key + '[tab_name]');
        new_field.find('.tab-icon').val('').attr('name', field_key + '[tab_icon]');

        new_field.find('.tab-behavior-option').attr('name', field_key + '[tab_behavior]');
        new_field.find('.tab-visibility-condition').attr('name', field_key + '[tab_visibility_condition]');
        new_field.find('.tab-content').val('').attr('name', field_key + '[tab_content]');
        new_field.find('.tab-link').val('').attr('name', field_key + '[tab_link]');

        new_field.find('.surelywp-misc-ccd-is-restrict-tab').val('1').attr('name', field_key + '[is_restrict_tab]');
        new_field.find('.tab-restrict-criteria').val('based_on_user_roles').attr('name', field_key + '[tab_restrict_criteria]');
        new_field.find('.tab-restriction-sm-access-groups').val('').attr('name', field_key + '[tab_restriction_sm_access_groups][]');
        new_field.find('.tab-restriction-user-roles').val('').attr('name', field_key + '[tab_restriction_user_roles][]');

        $('.surelywp-tk-misc-ccd-tab:last').after(new_field);

        // Remove existing editor.
        new_field.find('.wp-editor-wrap').remove();

        // add Editor.
        addEditor(new_field, editor_id, field_key);

        new_field.find('.tab-open-icon').click();
        if (!new_field.find('.surelywp-misc-ccd-is-show-tab').prop('checked')) {
            new_field.find('.surelywp-misc-ccd-is-show-tab').prop('checked', true).trigger('change');

        }

        // Fix the select dropdown not show proper inside hidden element.
        new_field.find('.surelywp-misc-ccd-is-restrict-tab').prop('checked', false).trigger('change');
        new_field.find('.tab-restrict-criteria').trigger('change');

        $('.tab-restriction-user-roles, .tab-restriction-sm-access-groups').select2({
            dropdownParent: $('.toolkit-templates-table'), // some visible wrapper
            width: '100%'
        });

    });


    function addEditor(new_field, editor_id, field_key) {

        // Create a new textarea and append it to the container
        var newTextarea = $('<textarea>', {
            id: editor_id,
            class: 'tab-content wp-editor-area',
            name: field_key + '[tab_content]',
            rows: '5'
        });

        new_field.find('.tab-content-input-label-desc').after(newTextarea);

        // Initialize TinyMCE editor
        wp.editor.initialize(editor_id, {
            default_editor: 'visual',
            tinymce: {
                toolbar1: 'bold,italic,underline,|,bullist,numlist,|,link,|,undo,redo',
                toolbar2: '',
                content_style: 'body, p, div { font-family: "Poppins", sans-serif; color: #4c5866; }' // Enclose Poppins in quotes
            },
            quicktags: {
                buttons: 'strong,em,link,ul,ol,li,quote',
            }
        });
    }


    // Remove the tab setting.
    $(document).on('click', '.surelywp-tk-misc-ccd-tab-remove', function () {
        $(this).closest('.surelywp-tk-misc-ccd-tab').remove();
    });

    $(document).on("change", ".tab-behavior-option", function () {

        var behavior = $(this).val();

        if ('display_content' == behavior) {
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-content-setting').removeClass('hidden');
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-link-setting').addClass('hidden');
        } else if ('link_to_url' == behavior) {
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-link-setting').removeClass('hidden');
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-content-setting').addClass('hidden');
        }
    });

    $(document).on("change", ".tab-restrict-criteria", function () {

        var criteria = $(this).val();
        if ('based_on_user_roles' == criteria) {

            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.sm-access-group-selection').addClass('hidden');
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.user-roles-selection').removeClass('hidden');

            // For Visibility Condition
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-visibility-condition-ur-label').removeClass('hidden-important');
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-visibility-condition-sm-label').addClass('hidden-important');

        } else if ('based_on_sm_access_groups' == criteria) {
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.sm-access-group-selection').removeClass('hidden');
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.user-roles-selection').addClass('hidden');

            // For Visibility Condition
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-visibility-condition-sm-label').removeClass('hidden-important');
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-visibility-condition-ur-label').addClass('hidden-important');
        }

    });


    $(document).on("change", ".surelywp-misc-ccd-is-restrict-tab", function () {

        if ($(this).is(':checked')) {
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-restriction-options').removeClass('hidden');
        } else {
            $(this).closest('.surelywp-tk-misc-ccd-tab').find('.tab-restriction-options').addClass('hidden');
        }
    });
});