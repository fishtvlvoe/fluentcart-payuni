/**
 * PayUNi User Guide JavaScript
 *
 * Handles sidebar navigation and section switching.
 *
 * @package BuyGoFluentCart\PayUNi
 * @since 1.1.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        var $nav = $('.guide-nav');
        var $sections = $('.guide-section');

        // Handle navigation clicks
        $nav.on('click', 'a', function(e) {
            e.preventDefault();

            var targetId = $(this).attr('href').substring(1);
            var $targetSection = $('#' + targetId);

            if (!$targetSection.length) {
                return;
            }

            // Update active nav item
            $nav.find('a').removeClass('active');
            $(this).addClass('active');

            // Show target section
            $sections.removeClass('active');
            $targetSection.addClass('active');

            // Update URL hash without scrolling
            if (history.replaceState) {
                history.replaceState(null, null, '#' + targetId);
            } else {
                // Fallback for older browsers
                window.location.hash = targetId;
            }

            // Scroll to top of content area (for mobile)
            if (window.innerWidth <= 782) {
                $('.guide-content').get(0).scrollIntoView({ behavior: 'smooth' });
            }
        });

        // Handle initial hash on page load
        var hash = window.location.hash.substring(1);
        if (hash && $('#' + hash).length) {
            $nav.find('a[href="#' + hash + '"]').trigger('click');
        } else {
            // Default to first section
            $nav.find('a').first().addClass('active');
            $sections.first().addClass('active');
        }

        // Smooth scroll for internal links within content
        $('.guide-content').on('click', 'a[href^="#"]', function(e) {
            var targetId = $(this).attr('href').substring(1);
            if ($('#' + targetId).length) {
                $nav.find('a[href="#' + targetId + '"]').trigger('click');
                e.preventDefault();
            }
        });

        // Handle browser back/forward buttons
        $(window).on('hashchange', function() {
            var hash = window.location.hash.substring(1);
            if (hash && $('#' + hash).length) {
                $nav.find('a[href="#' + hash + '"]').trigger('click');
            }
        });
    });
})(jQuery);
