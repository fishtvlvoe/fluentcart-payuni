/**
 * PayUNi Webhook Logs Viewer JavaScript
 *
 * @package BuyGoFluentCart\PayUNi
 * @since 1.1.0
 */

(function($) {
    'use strict';

    var PayUNiWebhookLogs = {
        config: null,
        currentPage: 1,
        perPage: 20,
        totalPages: 1,
        filters: {},

        init: function() {
            this.config = window.payuniWebhookLogs || {};
            this.bindEvents();
            this.loadLogs();
        },

        bindEvents: function() {
            var self = this;

            // Filter button
            $('#payuni-logs-filter-btn').on('click', function() {
                self.currentPage = 1;
                self.loadLogs();
            });

            // Enter key in search field
            $('#payuni-logs-search').on('keypress', function(e) {
                if (e.which === 13) {
                    self.currentPage = 1;
                    self.loadLogs();
                }
            });

            // Pagination
            $('#payuni-logs-prev').on('click', function() {
                if (self.currentPage > 1) {
                    self.currentPage--;
                    self.loadLogs();
                }
            });

            $('#payuni-logs-next').on('click', function() {
                if (self.currentPage < self.totalPages) {
                    self.currentPage++;
                    self.loadLogs();
                }
            });

            // Modal close
            $('.payuni-modal-close').on('click', function() {
                self.closeModal();
            });

            // Close modal on backdrop click
            $('#payuni-log-detail-modal').on('click', function(e) {
                if ($(e.target).is('#payuni-log-detail-modal')) {
                    self.closeModal();
                }
            });

            // Close modal on Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    self.closeModal();
                }
            });
        },

        getFilters: function() {
            return {
                search: $('#payuni-logs-search').val() || '',
                status: $('#payuni-logs-status').val() || '',
                webhook_type: $('#payuni-logs-type').val() || '',
                date_from: $('#payuni-logs-date-from').val() || '',
                date_to: $('#payuni-logs-date-to').val() || ''
            };
        },

        loadLogs: function() {
            var self = this;
            var filters = this.getFilters();

            // Show loading
            $('#payuni-logs-body').html(
                '<tr class="payuni-logs-loading"><td colspan="6">' +
                (this.config.labels ? this.config.labels.loading : 'Loading...') +
                '</td></tr>'
            );

            // Build query params
            var params = {
                page: this.currentPage,
                per_page: this.perPage
            };

            if (filters.search) params.search = filters.search;
            if (filters.status) params.status = filters.status;
            if (filters.webhook_type) params.webhook_type = filters.webhook_type;
            if (filters.date_from) params.date_from = filters.date_from;
            if (filters.date_to) params.date_to = filters.date_to;

            $.ajax({
                url: this.config.restUrl,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', self.config.nonce);
                },
                data: params,
                success: function(response) {
                    self.renderTable(response);
                    self.updatePagination(response);
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load webhook logs:', error);
                    $('#payuni-logs-body').html(
                        '<tr class="payuni-logs-empty"><td colspan="6">Error loading logs.</td></tr>'
                    );
                }
            });
        },

        renderTable: function(response) {
            var self = this;
            var logs = response.data || [];
            var labels = this.config.labels || {};
            var tbody = $('#payuni-logs-body');

            if (logs.length === 0) {
                tbody.html(
                    '<tr class="payuni-logs-empty"><td colspan="6">' +
                    (labels.no_logs || 'No webhook logs found.') +
                    '</td></tr>'
                );
                return;
            }

            var html = '';
            logs.forEach(function(log) {
                html += self.renderRow(log);
            });

            tbody.html(html);

            // Bind view detail buttons
            tbody.find('.payuni-view-detail-btn').on('click', function() {
                var logData = $(this).data('log');
                self.showDetail(logData);
            });
        },

        renderRow: function(log) {
            var labels = this.config.labels || {};

            var statusClass = 'payuni-status-' + (log.webhook_status || 'processed');
            var statusLabel = this.getStatusLabel(log.webhook_status);

            var typeClass = 'payuni-type-' + (log.webhook_type || 'notify');
            var typeLabel = log.webhook_type === 'return' ?
                (labels.type_return || 'Return') :
                (labels.type_notify || 'Notify');

            var time = this.formatTime(log.processed_at);
            var tradeNo = log.trade_no || '-';
            var transactionId = log.transaction_id || '-';

            // Escape log data for data attribute
            var logDataStr = this.escapeHtml(JSON.stringify(log));

            return '<tr>' +
                '<td>' + this.escapeHtml(time) + '</td>' +
                '<td><span class="payuni-type-badge ' + typeClass + '">' + typeLabel + '</span></td>' +
                '<td>' + this.escapeHtml(tradeNo) + '</td>' +
                '<td>' + this.escapeHtml(transactionId) + '</td>' +
                '<td><span class="payuni-status-badge ' + statusClass + '">' + statusLabel + '</span></td>' +
                '<td>' +
                    '<button type="button" class="payuni-view-detail-btn" data-log=\'' + logDataStr + '\'>' +
                    (this.config.labels ? this.config.labels.view_details : 'View Details') +
                    '</button>' +
                '</td>' +
                '</tr>';
        },

        getStatusLabel: function(status) {
            var labels = this.config.labels || {};
            switch (status) {
                case 'processed':
                    return labels.status_processed || 'Processed';
                case 'duplicate':
                    return labels.status_duplicate || 'Duplicate (skipped)';
                case 'failed':
                    return labels.status_failed || 'Failed';
                case 'pending':
                    return labels.status_pending || 'Pending';
                default:
                    return status || 'Unknown';
            }
        },

        formatTime: function(datetime) {
            if (!datetime) return '-';
            try {
                var date = new Date(datetime + ' UTC');
                return date.toLocaleString('zh-TW', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            } catch (e) {
                return datetime;
            }
        },

        updatePagination: function(response) {
            this.totalPages = response.total_pages || 1;
            var total = response.total || 0;

            $('#payuni-logs-page-info').text(
                'Page ' + this.currentPage + ' of ' + this.totalPages +
                ' (' + total + ' total)'
            );

            $('#payuni-logs-prev').prop('disabled', this.currentPage <= 1);
            $('#payuni-logs-next').prop('disabled', this.currentPage >= this.totalPages);
        },

        showDetail: function(log) {
            var labels = this.config.labels || {};
            var html = '';

            // Basic info section
            html += '<div class="payuni-detail-section">';
            html += '<h3>Basic Information</h3>';

            html += '<div class="payuni-detail-row">';
            html += '<span class="payuni-detail-label">ID</span>';
            html += '<span class="payuni-detail-value">' + this.escapeHtml(log.id || '-') + '</span>';
            html += '</div>';

            html += '<div class="payuni-detail-row">';
            html += '<span class="payuni-detail-label">Transaction ID</span>';
            html += '<span class="payuni-detail-value">' + this.escapeHtml(log.transaction_id || '-') + '</span>';
            html += '</div>';

            html += '<div class="payuni-detail-row">';
            html += '<span class="payuni-detail-label">Trade No</span>';
            html += '<span class="payuni-detail-value">' + this.escapeHtml(log.trade_no || '-') + '</span>';
            html += '</div>';

            html += '<div class="payuni-detail-row">';
            html += '<span class="payuni-detail-label">Type</span>';
            html += '<span class="payuni-detail-value">' + this.escapeHtml(log.webhook_type || '-') + '</span>';
            html += '</div>';

            html += '<div class="payuni-detail-row">';
            html += '<span class="payuni-detail-label">Status</span>';
            html += '<span class="payuni-detail-value">' +
                '<span class="payuni-status-badge payuni-status-' + (log.webhook_status || 'processed') + '">' +
                this.getStatusLabel(log.webhook_status) +
                '</span></span>';
            html += '</div>';

            html += '<div class="payuni-detail-row">';
            html += '<span class="payuni-detail-label">Processed At</span>';
            html += '<span class="payuni-detail-value">' + this.formatTime(log.processed_at) + '</span>';
            html += '</div>';

            html += '<div class="payuni-detail-row">';
            html += '<span class="payuni-detail-label">Payload Hash</span>';
            html += '<span class="payuni-detail-value">' + this.escapeHtml(log.payload_hash || '-') + '</span>';
            html += '</div>';

            if (log.response_message) {
                html += '<div class="payuni-detail-row">';
                html += '<span class="payuni-detail-label">Response</span>';
                html += '<span class="payuni-detail-value">' + this.escapeHtml(log.response_message) + '</span>';
                html += '</div>';
            }

            html += '</div>';

            // Raw payload section (if available)
            if (log.raw_payload) {
                html += '<div class="payuni-detail-section">';
                html += '<h3>Raw Payload</h3>';
                html += '<div class="payuni-payload-block">';
                try {
                    var payload = JSON.parse(log.raw_payload);
                    html += this.escapeHtml(JSON.stringify(payload, null, 2));
                } catch (e) {
                    html += this.escapeHtml(log.raw_payload);
                }
                html += '</div>';
                html += '</div>';
            }

            $('#payuni-log-detail-content').html(html);
            $('#payuni-log-detail-modal').show();
        },

        closeModal: function() {
            $('#payuni-log-detail-modal').hide();
        },

        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(String(text)));
            return div.innerHTML;
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('#payuni-webhook-logs-app').length) {
            PayUNiWebhookLogs.init();
        }
    });

})(jQuery);
