/**
 * Bootstrap initialization script to ensure all components work correctly
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize Bootstrap components
     */
    function initBootstrapComponents() {
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Enable popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Fix Bootstrap modal backdrop
        $(document).on('show.bs.modal', '.modal', function () {
            var zIndex = 1040 + (10 * $('.modal:visible').length);
            $(this).css('z-index', zIndex);
            setTimeout(function() {
                $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
            }, 0);
        });
        
        // Fix Bootstrap dropdown positioning
        $(document).on('shown.bs.dropdown', '.dropdown', function () {
            var $menuDropdown = $(this).find('.dropdown-menu');
            var windowHeight = $(window).height();
            var dropdownOffset = $menuDropdown.offset();
            var dropdownHeight = $menuDropdown.outerHeight();
            var dropdownBottomPos = dropdownOffset.top + dropdownHeight;
            
            if (dropdownBottomPos > windowHeight) {
                $menuDropdown.css({
                    top: 'auto',
                    bottom: '100%',
                    transform: 'translateY(-2px)'
                });
            }
        });
        
        // Ensure Bootstrap tabs work
        $(document).on('click', '[data-bs-toggle="tab"]', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
        
        console.log('Bootstrap components initialized successfully');
    }
    
    /**
     * Fix styling for WordPress admin elements
     */
    function fixWordPressStyle() {
        // Fix button styling
        $('.page-title-action').addClass('btn btn-sm');
        
        // Add Bootstrap container class to admin notices if missing
        $('.notice:not(.container)').addClass('container');
        
        // Ensure tables have Bootstrap styling
        $('table:not(.table)').addClass('table table-bordered');
        
        console.log('WordPress styling fixed');
    }
    
    /**
     * Document ready handler
     */
    $(document).ready(function() {
        // Initialize Bootstrap components
        initBootstrapComponents();
        
        // Fix WordPress admin styling
        fixWordPressStyle();
    });

})(jQuery);