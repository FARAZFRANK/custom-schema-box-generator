/**
 * Custom Schema Box Generator - Admin Scripts
 *
 * @package Custom Schema Box Generator
 * @version 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        /**
         * Select All functionality for checkboxes
         * Using event delegation to handle dynamically loaded content
         */
        $(document).on('click', '.csg-select-all-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Don't do anything if button is disabled
            if ($(this).hasClass('csg-disabled')) {
                return false;
            }

            var container = $(this).closest('.csg-items-list-container');
            var checkboxes = container.find('.csg-items-list input[type="checkbox"]');

            // Check all checkboxes
            checkboxes.prop('checked', true).trigger('change');

            // Update button states
            updateButtonStates();

            return false;
        });

        /**
         * Deselect All functionality for checkboxes
         * Using event delegation to handle dynamically loaded content
         */
        $(document).on('click', '.csg-deselect-all-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Don't do anything if button is disabled
            if ($(this).hasClass('csg-disabled')) {
                return false;
            }

            var container = $(this).closest('.csg-items-list-container');
            var checkboxes = container.find('.csg-items-list input[type="checkbox"]');

            // Uncheck all checkboxes
            checkboxes.prop('checked', false).trigger('change');

            // Update button states
            updateButtonStates();

            return false;
        });

        /**
         * Update button states based on checkbox selection
         */
        function updateButtonStates() {
            $('.csg-items-list-container').each(function() {
                var container = $(this);
                var checkboxes = container.find('.csg-items-list input[type="checkbox"]');
                var checkedCount = checkboxes.filter(':checked').length;
                var totalCount = checkboxes.length;

                var selectBtn = container.find('.csg-select-all-btn');
                var deselectBtn = container.find('.csg-deselect-all-btn');

                // Remove disabled class and reset styles first
                selectBtn.removeClass('csg-disabled').css({'opacity': '1', 'cursor': 'pointer', 'pointer-events': 'auto'});
                deselectBtn.removeClass('csg-disabled').css({'opacity': '1', 'cursor': 'pointer', 'pointer-events': 'auto'});

                if (checkedCount === totalCount && totalCount > 0) {
                    // All checked - disable Select All
                    selectBtn.addClass('csg-disabled').css({'opacity': '0.5', 'cursor': 'not-allowed'});
                } else if (checkedCount === 0) {
                    // None checked - disable Deselect All
                    deselectBtn.addClass('csg-disabled').css({'opacity': '0.5', 'cursor': 'not-allowed'});
                }
                // If some are checked, both buttons remain enabled
            });
        }

        // Update button states on page load
        setTimeout(function() {
            updateButtonStates();
        }, 100);

        // Update button states when checkboxes change - using event delegation
        $(document).on('change', '.csg-items-list input[type="checkbox"]', function() {
            updateButtonStates();
        });

        /**
         * Template functionality
         */

        // Copy template to clipboard
        $('.csg-copy-template').on('click', function(e) {
            e.preventDefault();
            var templateId = $(this).data('template-id');
            var templateCode = $('#template-' + templateId).text();

            // Copy to clipboard
            copyToClipboard(templateCode);

            // Show success message
            var button = $(this);
            var originalText = button.text();
            button.text('✓ Copied!').prop('disabled', true);

            setTimeout(function() {
                button.text(originalText).prop('disabled', false);
            }, 2000);
        });

        // View template code in modal
        $('.csg-view-template').on('click', function(e) {
            e.preventDefault();
            var templateId = $(this).data('template-id');
            var templateCode = $('#template-' + templateId).text();
            var templateName = $(this).closest('.csg-template-card').find('h3').text();

            $('#csg-modal-title').text(templateName + ' - Template Code');
            $('#csg-modal-code').text(templateCode);
            $('#csg-template-modal').fadeIn();
        });

        // Close modal
        $('.csg-modal-close').on('click', function() {
            $('#csg-template-modal').fadeOut();
        });

        // Close modal when clicking outside
        $(window).on('click', function(e) {
            if ($(e.target).is('#csg-template-modal')) {
                $('#csg-template-modal').fadeOut();
            }
        });

        // Copy from modal
        $('.csg-modal-copy').on('click', function(e) {
            e.preventDefault();
            var code = $('#csg-modal-code').text();
            copyToClipboard(code);

            var button = $(this);
            var originalText = button.text();
            button.text('✓ Copied!').prop('disabled', true);

            setTimeout(function() {
                button.text(originalText).prop('disabled', false);
            }, 2000);
        });

        /**
         * FAQ accordion functionality
         */
        $('.csg-faq-question').on('click', function() {
            var answer = $(this).next('.csg-faq-answer');
            var allAnswers = $('.csg-faq-answer');

            // Close all other answers
            allAnswers.not(answer).slideUp();

            // Toggle current answer
            answer.slideToggle();
        });

        // Open first FAQ by default
        $('.csg-faq-item:first-child .csg-faq-answer').show();

        /**
         * Toggle between Individual and Dynamic meta box types
         */
        $('.csg-meta-box-type-radio').on('change', function() {
            var postType = $(this).data('post-type');
            var selectedType = $(this).val();

            // Find the containers for this post type
            var container = $(this).closest('.csg-meta-box-type-container');
            var individualList = $('.csg-individual-list[data-post-type="' + postType + '"]');
            var dynamicSchema = $('.csg-dynamic-schema[data-post-type="' + postType + '"]');
            var individualDesc = container.find('.csg-individual-desc');
            var dynamicDesc = container.find('.csg-dynamic-desc');

            if (selectedType === 'individual') {
                individualList.slideDown(300);
                dynamicSchema.slideUp(300);
                individualDesc.fadeIn(200);
                dynamicDesc.fadeOut(200);
            } else if (selectedType === 'dynamic') {
                individualList.slideUp(300);
                dynamicSchema.slideDown(300);
                individualDesc.fadeOut(200);
                dynamicDesc.fadeIn(200);
            }
        });

        /**
         * Toggle meta box type container when enabling/disabling post type
         */
        $('.csg-enable-toggle').on('change', function() {
            var postType = $(this).data('post-type');
            var isEnabled = $(this).val() === '1';
            var container = $('.csg-meta-box-type-container[data-post-type="' + postType + '"]');

            if (isEnabled) {
                container.slideDown(300);
            } else {
                container.slideUp(300);
            }
        });

        /**
         * Click to copy placeholder to clipboard
         */
        $(document).on('click', '.csg-placeholder-list li', function() {
            var placeholder = $(this).text();
            var $this = $(this);

            // Copy to clipboard
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(placeholder).then(function() {
                    // Show feedback
                    var originalBg = $this.css('background-color');
                    $this.css('background-color', '#00a32a');
                    $this.css('color', '#fff');

                    setTimeout(function() {
                        $this.css('background-color', '');
                        $this.css('color', '');
                    }, 500);
                }).catch(function() {
                    console.log('Failed to copy placeholder');
                });
            }
        });

        /**
         * Helper function to copy text to clipboard
         */
        function copyToClipboard(text) {
            // Modern clipboard API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).catch(function(err) {
                    // Fallback to old method
                    fallbackCopyToClipboard(text);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyToClipboard(text);
            }
        }

        /**
         * Fallback copy method for older browsers
         */
        function fallbackCopyToClipboard(text) {
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.top = '0';
            textArea.style.left = '0';
            textArea.style.width = '2em';
            textArea.style.height = '2em';
            textArea.style.padding = '0';
            textArea.style.border = 'none';
            textArea.style.outline = 'none';
            textArea.style.boxShadow = 'none';
            textArea.style.background = 'transparent';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Failed to copy text: ', err);
            }

            document.body.removeChild(textArea);
        }

    });

})(jQuery);

