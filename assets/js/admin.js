/**
 * AdminForge - Admin JavaScript
 * @package AdminForge
 * @since 1.0.0
 */

(function($) {
    'use strict';

    const AdminForge = {
        /**
         * Initialize
         */
        init: function() {
            this.tabs();
            this.colorPicker();
            this.mediaUploader();
            this.conditionalLogic();
            this.repeaterField();
        },

        /**
         * Tab system
         */
        tabs: function() {
            $('.adminforge-tab').on('click', function(e) {
                e.preventDefault();

                const $tab = $(this);
                const target = $tab.data('tab');

                // Remove active class from all tabs
                $('.adminforge-tab').removeClass('active');
                $('.adminforge-tab-content').removeClass('active');

                // Add active class to clicked tab
                $tab.addClass('active');
                $('#' + target).addClass('active');

                // Update URL without reload
                if (history.pushState) {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', target);
                    window.history.pushState({}, '', url);
                }
            });

            // Activate tab from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab');

            if (activeTab) {
                // Sanitize tab parameter to prevent XSS
                const sanitizedTab = activeTab.replace(/[^a-zA-Z0-9_-]/g, '');
                if (sanitizedTab) {
                    $('.adminforge-tab[data-tab="' + sanitizedTab + '"]').trigger('click');
                }
            }
        },

        /**
         * Color picker
         */
        colorPicker: function() {
            if ($.fn.wpColorPicker) {
                $('.adminforge-color-picker').wpColorPicker();
            }
        },

        /**
         * Media uploader
         */
        mediaUploader: function() {
            let mediaFrame;

            $(document).on('click', '.adminforge-upload-btn', function(e) {
                e.preventDefault();

                const $button = $(this);
                const $input = $button.siblings('.adminforge-upload-input');
                const $preview = $button.siblings('.adminforge-upload-preview');

                // Create media frame if not exists
                if (!mediaFrame) {
                    mediaFrame = wp.media({
                        title: adminforge.strings.select_image || 'Select Image',
                        button: {
                            text: adminforge.strings.use_image || 'Use Image'
                        },
                        multiple: false
                    });
                }

                // When image is selected
                mediaFrame.on('select', function() {
                    const attachment = mediaFrame.state().get('selection').first().toJSON();
                    $input.val(attachment.url);

                    if ($preview.length) {
                        // Create image element safely to prevent XSS
                        const img = $('<img>', {
                            src: attachment.url,
                            css: { 'max-width': '200px' },
                            alt: attachment.alt || ''
                        });
                        $preview.empty().append(img);
                    }
                });

                mediaFrame.open();
            });

            // Remove image
            $(document).on('click', '.adminforge-remove-upload', function(e) {
                e.preventDefault();
                $(this).siblings('.adminforge-upload-input').val('');
                $(this).siblings('.adminforge-upload-preview').html('');
            });
        },

        /**
         * Conditional logic
         */
        conditionalLogic: function() {
            $('[data-condition]').each(function() {
                const $field = $(this);
                const condition = $field.data('condition');

                if (condition && condition.field && condition.value) {
                    const $trigger = $('#' + condition.field);

                    // Check on load
                    AdminForge.checkCondition($field, $trigger, condition.value);

                    // Check on change
                    $trigger.on('change', function() {
                        AdminForge.checkCondition($field, $trigger, condition.value);
                    });
                }
            });
        },

        /**
         * Check condition and show/hide field
         */
        checkCondition: function($field, $trigger, value) {
            const triggerValue = $trigger.is(':checkbox') ? $trigger.is(':checked') : $trigger.val();

            if (triggerValue == value) {
                $field.slideDown();
            } else {
                $field.slideUp();
            }
        },

        /**
         * Repeater field
         */
        repeaterField: function() {
            let rowIndex = 0;

            // Make repeater sortable (drag & drop)
            if ($.fn.sortable) {
                $('.adminforge-repeater-sortable').sortable({
                    handle: '.adminforge-repeater-handle',
                    placeholder: 'adminforge-repeater-placeholder',
                    start: function(e, ui) {
                        ui.placeholder.height(ui.item.height());
                    }
                });
            }

            // Add row
            $(document).on('click', '.adminforge-repeater-add', function(e) {
                e.preventDefault();

                const $button = $(this);
                const fieldId = $button.data('field-id');
                const $wrapper = $button.closest('.adminforge-repeater-wrapper');
                const $rows = $wrapper.find('.adminforge-repeater-rows');
                const maxRows = parseInt($wrapper.data('max-rows')) || 50;
                const currentRows = $rows.find('.adminforge-repeater-row').length;

                // Check max rows
                if (currentRows >= maxRows) {
                    alert('Maximum rows reached');
                    return;
                }

                // Get template
                const template = $('#' + fieldId + '-template').html();
                if (!template) {
                    return;
                }

                // Replace index placeholder with actual index
                const newRow = template.replace(/\{\{INDEX\}\}/g, rowIndex);
                rowIndex++;

                // Add row
                $rows.append(newRow);

                // Trigger event
                $rows.trigger('adminforge:repeater:added', [fieldId]);
            });

            // Remove row
            $(document).on('click', '.adminforge-repeater-remove', function(e) {
                e.preventDefault();

                const $button = $(this);
                const $row = $button.closest('.adminforge-repeater-row');
                const $rows = $row.closest('.adminforge-repeater-rows');
                const fieldId = $rows.data('field-id');

                // Confirm removal
                if (confirm('Are you sure you want to remove this row?')) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        $rows.trigger('adminforge:repeater:removed', [fieldId]);
                    });
                }
            });
        },

        /**
         * AJAX save
         */
        save: function(data, callback) {
            $.ajax({
                url: adminforge.ajax_url,
                type: 'POST',
                data: {
                    action: 'adminforge_save',
                    nonce: adminforge.nonce,
                    data: data
                },
                success: function(response) {
                    if (callback) {
                        callback(response);
                    }
                },
                error: function() {
                    alert(adminforge.strings.error);
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        AdminForge.init();
    });

    // Expose to global scope
    window.AdminForge = AdminForge;

})(jQuery);
