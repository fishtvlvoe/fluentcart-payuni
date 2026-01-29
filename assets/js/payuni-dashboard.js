(function($) {
    'use strict';

    // Chart instances for updating
    let paymentChart = null;
    let renewalChart = null;

    // Color palette (Element Plus colors)
    const colors = {
        credit: '#409EFF',  // Primary blue
        atm: '#67C23A',     // Success green
        cvs: '#E6A23C',     // Warning orange
        other: '#909399'    // Info gray
    };

    // Initialize on DOM ready
    $(document).ready(function() {
        loadDashboardStats();

        // Refresh button handler
        $('#refresh-stats').on('click', function() {
            loadDashboardStats(true);
        });
    });

    /**
     * Show error message to user (visible in admin notice)
     * @param {string} message - Error message to display
     */
    function showError(message) {
        const $errorContainer = $('#dashboard-error');
        const $errorMessage = $('#dashboard-error-message');

        $errorMessage.text(message);
        $errorContainer.show();

        // Auto-hide after 10 seconds
        setTimeout(function() {
            $errorContainer.fadeOut();
        }, 10000);
    }

    /**
     * Hide error message
     */
    function hideError() {
        $('#dashboard-error').hide();
    }

    /**
     * Load dashboard statistics from REST API
     * @param {boolean} refresh - Force cache refresh
     */
    function loadDashboardStats(refresh = false) {
        const $refreshBtn = $('#refresh-stats');
        $refreshBtn.prop('disabled', true).text(payuniDashboard.labels.loading);

        // Hide any previous error
        hideError();

        const url = refresh
            ? payuniDashboard.restUrl + '?refresh=true'
            : payuniDashboard.restUrl;

        $.ajax({
            url: url,
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', payuniDashboard.nonce);
            },
            success: function(response) {
                renderPaymentDistribution(response.payment_distribution);
                renderRenewalSuccessRate(response.renewal_success_rate);
                renderRecentWebhooks(response.recent_webhooks);
                $('#generated-at').text(response.generated_at || '-');
            },
            error: function(xhr, status, error) {
                // Log detailed error to console for debugging
                console.error('Dashboard stats load failed:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });

                // Show user-visible error message
                let userMessage = payuniDashboard.labels.loadError;

                // Add specific error info if available
                if (xhr.status === 403) {
                    userMessage = '權限不足,請確認您有管理員權限';
                } else if (xhr.status === 0) {
                    userMessage = '網路連線失敗,請檢查網路狀態';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    userMessage = xhr.responseJSON.message;
                }

                showError(userMessage);

                // Set default "no data" state for all sections
                $('#generated-at').text('-');
                $('#payment-distribution-legend').html('<p>' + payuniDashboard.labels.noData + '</p>');
                $('#average-success-rate').text('-');
                $('#recent-webhooks-tbody').html('<tr><td colspan="3">' + payuniDashboard.labels.noData + '</td></tr>');
            },
            complete: function() {
                $refreshBtn.prop('disabled', false).text(payuniDashboard.labels.refresh);
            }
        });
    }

    /**
     * Render payment distribution pie chart (DASH-02)
     */
    function renderPaymentDistribution(data) {
        if (!data || data.length === 0) {
            $('#payment-distribution-legend').html('<p>' + payuniDashboard.labels.noData + '</p>');
            return;
        }

        const ctx = document.getElementById('payment-distribution-chart').getContext('2d');
        const labels = [];
        const values = [];
        const bgColors = [];

        data.forEach(function(item) {
            const label = payuniDashboard.labels[item.type] || item.type;
            labels.push(label);
            values.push(item.count);
            bgColors.push(colors[item.type] || colors.other);
        });

        // Destroy existing chart if any
        if (paymentChart) {
            paymentChart.destroy();
        }

        paymentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: bgColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // We use custom legend
                    }
                }
            }
        });

        // Render custom legend with amounts
        let legendHtml = '';
        data.forEach(function(item) {
            const label = payuniDashboard.labels[item.type] || item.type;
            const color = colors[item.type] || colors.other;
            legendHtml += '<div class="legend-item">' +
                '<span><span class="legend-color" style="background:' + color + '"></span>' +
                '<span class="legend-label">' + label + '</span></span>' +
                '<span class="legend-value">' + item.count + ' 筆 / NT$' + formatNumber(item.amount) + '</span>' +
                '</div>';
        });
        $('#payment-distribution-legend').html(legendHtml);
    }

    /**
     * Render renewal success rate line chart (DASH-03)
     */
    function renderRenewalSuccessRate(data) {
        // Update average rate display
        const avgRate = data && data.average_rate ? data.average_rate.toFixed(1) + '%' : '-';
        $('#average-success-rate').text(avgRate);

        if (!data || !data.data || data.data.length === 0) {
            return;
        }

        const ctx = document.getElementById('renewal-success-chart').getContext('2d');
        const labels = data.data.map(function(item) {
            return item.date.substring(5); // Show MM-DD only
        });
        const values = data.data.map(function(item) {
            return item.success_rate;
        });

        // Destroy existing chart if any
        if (renewalChart) {
            renewalChart.destroy();
        }

        renewalChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: payuniDashboard.labels.successRate,
                    data: values,
                    borderColor: '#67C23A',
                    backgroundColor: 'rgba(103, 194, 58, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Render recent webhooks table (DASH-04)
     */
    function renderRecentWebhooks(data) {
        const $tbody = $('#recent-webhooks-tbody');

        if (!data || data.length === 0) {
            $tbody.html('<tr><td colspan="3">' + payuniDashboard.labels.noData + '</td></tr>');
            return;
        }

        let html = '';
        data.forEach(function(item) {
            const time = item.processed_at ? item.processed_at.substring(5, 16) : '-';
            const type = item.webhook_type || '-';
            const status = item.webhook_status || 'pending';
            const statusLabel = item.status_label || status;

            html += '<tr>' +
                '<td>' + time + '</td>' +
                '<td>' + type + '</td>' +
                '<td><span class="webhook-status ' + status + '">' + statusLabel + '</span></td>' +
                '</tr>';
        });

        $tbody.html(html);
    }

    /**
     * Format number with thousand separators
     */
    function formatNumber(num) {
        if (!num) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

})(jQuery);
