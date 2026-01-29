/**
 * PayUNi Settings Page JavaScript
 *
 * Handles webhook reachability testing and URL copying.
 *
 * @since 1.1.0
 */

jQuery(document).ready(function($) {
    // Test webhook reachability
    $('#test-webhook-btn').on('click', function() {
        const $btn = $(this);
        const $result = $('#webhook-test-result');

        $btn.prop('disabled', true).text(payuniSettings.labels.testingWebhook);

        $.ajax({
            url: payuniSettings.restUrl + '/test-webhook',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', payuniSettings.nonce);
            },
            success: function(response) {
                if (response.reachable) {
                    $result.html('<span class="status-success">' + payuniSettings.labels.webhookReachable + '</span>');
                } else {
                    $result.html('<span class="status-error">' + payuniSettings.labels.webhookUnreachable + ' (' + response.message + ')</span>');
                }
            },
            error: function() {
                $result.html('<span class="status-error">測試失敗</span>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('測試連線');
            }
        });
    });

    // Copy to clipboard functionality
    $('.copy-url-btn').on('click', function() {
        const $input = $(this).siblings('input');
        $input.select();
        document.execCommand('copy');

        const $btn = $(this);
        const originalText = $btn.text();
        $btn.text(payuniSettings.labels.copySuccess);
        setTimeout(() => $btn.text(originalText), 2000);
    });

    // Toggle collapsible sections
    $('.section-toggle').on('click', function() {
        const $header = $(this);
        const sectionId = $header.data('section');
        const $content = $('#' + sectionId);
        const $icon = $header.find('.dashicons');

        $content.slideToggle(300);

        if ($icon.hasClass('dashicons-arrow-down')) {
            $icon.removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
        } else {
            $icon.removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
        }
    });
});
