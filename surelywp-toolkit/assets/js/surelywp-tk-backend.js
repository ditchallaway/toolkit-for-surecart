"use strict";


jQuery(document).ready(function($) {

    FilePond.registerPlugin(

        // validates the size of the file
        FilePondPluginFileValidateSize,

        // validates the type of the file
        FilePondPluginFileValidateType
    );

    // Turn input into FilePond instance (single file only)
    const surelywp_tk_import_settings = FilePond.create(document.querySelector('.messages-tk-filepond'), {
        acceptedFileTypes: tk_backend_ajax_object.file_types,
        allowFileEncode: false,
        allowMultiple: true,
        labelIdle: wp.i18n.__('Click to upload', 'surelywp-toolkit') + ' <span class="filepond--label-action">' + wp.i18n.__('or', 'surelywp-toolkit') + '</span>' + wp.i18n.__(' drag and drop', 'surelywp-toolkit') + ' <span class="filepond--label-action">' + wp.i18n.__(' Only JSON File is allowed', 'surelywp-toolkit') + '</span>'
    });

    $('#import-settings-form').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this); // grab nonce + hidden fields

        // Append single FilePond file (if exists)
        if (surelywp_tk_import_settings.getFiles().length > 0) {
            let fileItem = surelywp_tk_import_settings.getFiles()[0];
            if (fileItem.status === 2) { // success
                formData.append('import_tk_file', fileItem.file);
            }
        }

        // Required for WordPress AJAX
        formData.append('action', 'surelywp_tk_import_settings');

        $.ajax({
            url: ajaxurl, // Provided by WP in admin
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success===false) {
                    $('#surelywp_toolkit_panel_surelywp_import_export').prepend('<div class="notice notice-error is-dismissible surelywp_tk_ie_settings_notice"><p>' + response.data.message + '</p></div>');
                    surelywp_tk_import_settings.removeFiles();
                } else {
                    $('#surelywp_toolkit_panel_surelywp_import_export').prepend('<div class="notice notice-success is-dismissible surelywp_tk_ie_settings_notice"><p>' + wp.i18n.__('All Settings Of Toolkit Plugin Imported Successfully.', 'surelywp-toolkit') + '</p></div>');
                    surelywp_tk_import_settings.removeFiles();
                }
                setTimeout(function() {
                    $('.surelywp_tk_ie_settings_notice').fadeOut(400, function() {
                        $(this).remove();
                    });
                }, 5000);
            },
            error: function(xhr) {
                $('#surelywp_toolkit_panel_surelywp_import_export').prepend('<div class="notice notice-error is-dismissible"><p>' + xhr.responseText + '</p></div>');
                surelywp_tk_import_settings.removeFiles();
            }
        });
    });

});
