/**
 * BMLT Enabled Stats - Admin JavaScript
 */

(function($) {
    'use strict';

    var $refreshBtn = $('#blst-refresh-stats');
    var $clearBtn = $('#blst-clear-cache');
    var $status = $('#blst-action-status');

    // Refresh stats button handler
    $refreshBtn.on('click', function(e) {
        e.preventDefault();

        var $btn = $(this);
        $btn.prop('disabled', true).text(blstAdmin.refreshing);
        $status.text('').removeClass('success error');

        $.ajax({
            url: blstAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'blst_refresh_stats',
                nonce: blstAdmin.refreshNonce
            },
            success: function(response) {
                if (response.success) {
                    $status.text(response.data.message).addClass('success');
                    // Reload page after short delay to show updated cache status
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $status.text(response.data || blstAdmin.error).addClass('error');
                }
            },
            error: function() {
                $status.text(blstAdmin.error).addClass('error');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Refresh Stats Now');
            }
        });
    });

    // Clear cache button handler
    $clearBtn.on('click', function(e) {
        e.preventDefault();

        var $btn = $(this);
        $btn.prop('disabled', true).text(blstAdmin.clearing);
        $status.text('').removeClass('success error');

        $.ajax({
            url: blstAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'blst_clear_cache',
                nonce: blstAdmin.clearNonce
            },
            success: function(response) {
                if (response.success) {
                    $status.text(response.data.message + ' Click "Refresh Stats Now" to fetch new data.').addClass('success');
                    // Update cache status indicators without reloading
                    $('.blst-cache-status').removeClass('blst-cached').addClass('blst-not-cached').text('Not cached');
                } else {
                    $status.text(response.data || blstAdmin.error).addClass('error');
                }
            },
            error: function() {
                $status.text(blstAdmin.error).addClass('error');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Clear Cache');
            }
        });
    });

})(jQuery);
