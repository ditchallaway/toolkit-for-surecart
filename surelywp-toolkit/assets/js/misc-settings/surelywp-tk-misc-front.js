"use strict";

jQuery(document).ready(function ($) {

    if ($('#customer-purchase').length) {
        (async () => {
            await customElements.whenDefined('sc-downloads-list');
            const component = document.querySelector('sc-downloads-list#customer-purchase');

            if (!component || !Array.isArray(component.downloads)) {
                console.log('Component not found or downloads not available');
                return;
            }

            const shadowRoot = component.shadowRoot;
            if (!shadowRoot) {
                console.log('Shadow root not found');
                return;
            }

            const waitForPreviewElems = () => {
                return new Promise(resolve => {
                    const existingElems = shadowRoot.querySelectorAll('.single-download__preview');
                    if (existingElems.length > 0) {
                        return resolve(existingElems);
                    }

                    const observer = new MutationObserver(() => {
                        const elems = shadowRoot.querySelectorAll('.single-download__preview');
                        if (elems.length > 0) {
                            observer.disconnect();
                            resolve(elems);
                        }
                    });

                    observer.observe(shadowRoot, {
                        childList: true,
                        subtree: true
                    });
                });
            };

            const previewElems = await waitForPreviewElems();

            component.downloads.forEach((download, index) => {
                const media = download?.media;
                const mediaId = media?.id;
                const isImage = media?.content_type?.startsWith('image/');
                const previewEl = previewElems[index];

                if (!mediaId || !isImage || !previewEl) return;

                const originalHTML = previewEl.innerHTML;

                // Show skeleton loader
                previewEl.innerHTML = `<sc-skeleton style="width: 100%; height: 100%; --border-radius: 0;"></sc-skeleton>`;

                // Individual AJAX request for each media ID
                $.ajax({
                    url: tk_misc_front_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'surelywp_tk_misc_get_download_media_url',
                        media_id: mediaId,
                        view_type: 'customer',
                        nonce: tk_misc_front_ajax_object.misc_ajax_nonce,
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status && response.url) {
                            const img = document.createElement('img');
                            img.src = response.url;
                            img.style.maxWidth = '100%';
                            img.style.height = 'auto';
                            previewEl.innerHTML = '';
                            previewEl.appendChild(img);
                        } else {
                            // Restore original HTML on failure
                            previewEl.innerHTML = originalHTML;
                        }
                    },
                    error: function (err) {
                        console.error(`Error fetching media URL for ID ${mediaId}:`, err);
                        // Restore original HTML on error
                        previewEl.innerHTML = originalHTML;
                    }
                });
            });
        })();
    }

    // Add Order Again Button.
    if ($('#surelywp-tk-order-again-btn').length > 0) {
        var add_order_again_btn_interval = setInterval(
            add_order_again_btn, 1000
        );
    }

    function add_order_again_btn() {

        var order_again_btn = $('#surelywp-tk-order-again-btn');

        // Access the shadow root of the custom element
        var scOrderElement = document.querySelector('#sc-customer-order');
        var shadowRoot = scOrderElement.shadowRoot;
        var nestedButton = shadowRoot.querySelector('sc-button');

        if (nestedButton) {

            var order_again_btn_clone = order_again_btn.clone().removeClass('hidden')[0];

            // Append the cloned button after the nested button.
            nestedButton.insertAdjacentElement('afterend', order_again_btn_clone);
            order_again_btn.remove();
            clearInterval(add_order_again_btn_interval);
        }
    }

    // manage hide/show price desctiptions.
    if ($('.surelywp-tk-misc-price-desc').length) {

        var price_desc_display_type = $('.surelywp-tk-misc-price-desc').data('display-type');

        if ('display_selected' === price_desc_display_type) {

            if ($('.sc-choices').length) {
                $('.sc-choice').next('.surelywp-tk-misc-price-desc').first().removeClass('hidden');
            } else {
                $('.surelywp-tk-misc-price-desc').removeClass('hidden');
            }

            $(document).on('click', '.sc-choice', function (e) {
                $('.surelywp-tk-misc-price-desc').slideUp(150);
                // Find and show the description near the selected choice
                var description = $(this).next(".surelywp-tk-misc-price-desc");
                if (description.length) {
                    description.slideDown(150);
                }
            });

        } else { // For display all selection.

            if ($('.sc-choices').length) {
                $('.sc-choice').next('.surelywp-tk-misc-price-desc').removeClass('hidden');
            } else {
                $('.surelywp-tk-misc-price-desc').removeClass('hidden');
            }
        }
    }
});