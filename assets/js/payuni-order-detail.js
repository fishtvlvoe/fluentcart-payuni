(function($) {
    'use strict';

    // Wait for FluentCart admin hooks to be available
    if (typeof window.fluentCartAdminHooks === 'undefined') {
        // Fallback: poll for order data in the page
        $(document).ready(function() {
            initPayUNiInfoPanel();
        });
        return;
    }

    // Use FluentCart admin hooks if available
    window.fluentCartAdminHooks.addAction('fluent_cart_order_loaded', 'payuni', function(order) {
        renderPayUNiInfoPanel(order);
    });

    function initPayUNiInfoPanel() {
        // Check for order data in FluentCart's Vue app or REST response
        var checkInterval = setInterval(function() {
            // Look for order detail page indicators
            var orderDetailEl = document.querySelector('.fct_order_single, [data-order-id], .fc-order-view');
            if (orderDetailEl) {
                clearInterval(checkInterval);
                // Try to get order data from page or make API call
                fetchAndRenderPayUNiInfo();
            }
        }, 500);

        // Stop checking after 10 seconds
        setTimeout(function() {
            clearInterval(checkInterval);
        }, 10000);
    }

    function fetchAndRenderPayUNiInfo() {
        // Extract order ID from URL (FluentCart uses hash routing: #/orders/{id})
        var hash = window.location.hash;
        var orderIdMatch = hash.match(/orders\/(\d+)/);
        if (!orderIdMatch) return;

        var orderId = orderIdMatch[1];

        // Fetch order data via FluentCart REST API
        $.ajax({
            url: wpApiSettings.root + 'fluent-cart/v2/orders/' + orderId,
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            },
            success: function(response) {
                if (response && response.order && response.order.payuni_info) {
                    renderPayUNiInfoPanel(response.order);
                }
            }
        });
    }

    function renderPayUNiInfoPanel(order) {
        if (!order || !order.payuni_info) return;

        var info = order.payuni_info;
        var labels = window.payuniOrderDetail ? window.payuniOrderDetail.labels : {};

        // Remove existing panel if any
        $('.payuni-info-panel').remove();

        // Build panel HTML
        var html = '<div class="payuni-info-panel">';
        html += '<h4 class="payuni-info-title">' + (labels.title || 'PayUNi 付款資訊') + '</h4>';
        html += '<div class="payuni-info-content">';

        // Basic info
        if (info.trade_no) {
            html += '<div class="payuni-info-row">';
            html += '<span class="payuni-info-label">' + (labels.trade_no || '交易編號') + '</span>';
            html += '<span class="payuni-info-value">' + info.trade_no + '</span>';
            html += '</div>';
        }

        html += '<div class="payuni-info-row">';
        html += '<span class="payuni-info-label">' + (labels.status || '交易狀態') + '</span>';
        html += '<span class="payuni-info-value payuni-status-' + info.status + '">' + info.status_label + '</span>';
        html += '</div>';

        html += '<div class="payuni-info-row">';
        html += '<span class="payuni-info-label">' + (labels.payment_type || '付款方式') + '</span>';
        html += '<span class="payuni-info-value">' + info.payment_type_label + '</span>';
        html += '</div>';

        // ATM info
        if (info.atm) {
            html += '<div class="payuni-info-section payuni-atm-section">';
            html += '<div class="payuni-info-row">';
            html += '<span class="payuni-info-label">' + (labels.atm_bank || '轉帳銀行') + '</span>';
            html += '<span class="payuni-info-value">' + info.atm.bank_name + ' (' + info.atm.bank_code + ')</span>';
            html += '</div>';
            html += '<div class="payuni-info-row">';
            html += '<span class="payuni-info-label">' + (labels.atm_account || '虛擬帳號') + '</span>';
            html += '<span class="payuni-info-value payuni-highlight">' + info.atm.virtual_account + '</span>';
            html += '</div>';
            if (info.atm.expire_formatted) {
                html += '<div class="payuni-info-row">';
                html += '<span class="payuni-info-label">' + (labels.atm_expire || '繳費期限') + '</span>';
                html += '<span class="payuni-info-value">' + info.atm.expire_formatted + '</span>';
                html += '</div>';
            }
            html += '</div>';
        }

        // CVS info
        if (info.cvs) {
            html += '<div class="payuni-info-section payuni-cvs-section">';
            html += '<div class="payuni-info-row">';
            html += '<span class="payuni-info-label">' + (labels.cvs_store || '繳費超商') + '</span>';
            html += '<span class="payuni-info-value">' + info.cvs.store_name + '</span>';
            html += '</div>';
            html += '<div class="payuni-info-row">';
            html += '<span class="payuni-info-label">' + (labels.cvs_code || '繳費代碼') + '</span>';
            html += '<span class="payuni-info-value payuni-highlight">' + info.cvs.payment_no + '</span>';
            html += '</div>';
            if (info.cvs.expire_formatted) {
                html += '<div class="payuni-info-row">';
                html += '<span class="payuni-info-label">' + (labels.cvs_expire || '繳費期限') + '</span>';
                html += '<span class="payuni-info-value">' + info.cvs.expire_formatted + '</span>';
                html += '</div>';
            }
            html += '</div>';
        }

        // Credit card info
        if (info.credit) {
            html += '<div class="payuni-info-section payuni-credit-section">';
            if (info.credit.card_last4) {
                html += '<div class="payuni-info-row">';
                html += '<span class="payuni-info-label">' + (labels.credit_card || '信用卡') + '</span>';
                html += '<span class="payuni-info-value">' + info.credit.card_brand + ' **** ' + info.credit.card_last4 + '</span>';
                html += '</div>';
            }
            if (info.credit.card_expiry) {
                html += '<div class="payuni-info-row">';
                html += '<span class="payuni-info-label">' + (labels.credit_expiry || '有效期限') + '</span>';
                html += '<span class="payuni-info-value">' + info.credit.card_expiry + '</span>';
                html += '</div>';
            }
            html += '<div class="payuni-info-row">';
            html += '<span class="payuni-info-label">' + (labels.credit_3d || '3D 驗證') + '</span>';
            html += '<span class="payuni-info-value ' + (info.credit.is_3d_verified ? 'payuni-3d-verified' : '') + '">' + info.credit['3d_label'] + '</span>';
            html += '</div>';
            html += '</div>';
        }

        html += '</div></div>';

        // Insert panel into FluentCart order detail page
        // Try multiple selectors for FluentCart order detail layout
        var targetSelectors = [
            '.fct_order_single .fct_order_sidebar',  // Sidebar area
            '.fc-order-sidebar',                     // Alternative sidebar
            '.fct_order_single .fct_order_main',     // Main content area
            '.fc-order-view',                        // Order view wrapper
        ];

        var inserted = false;
        for (var i = 0; i < targetSelectors.length; i++) {
            var target = $(targetSelectors[i]);
            if (target.length) {
                target.first().prepend(html);
                inserted = true;
                break;
            }
        }

        // Fallback: append to body as floating panel
        if (!inserted) {
            $('body').append(html);
            $('.payuni-info-panel').addClass('payuni-floating-panel');
        }
    }

    // Re-render on hash change (SPA navigation)
    $(window).on('hashchange', function() {
        setTimeout(initPayUNiInfoPanel, 500);
    });

})(jQuery);
