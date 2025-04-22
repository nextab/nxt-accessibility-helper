/**
 * NXT Accessibility Helper Public JavaScript
 * Handles dynamic ID assignment for target elements
 * 
 * Uses localized data (nxtA11yFrontend) that's passed from PHP via wp_localize_script
 */
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Check if our settings object exists
        if (typeof nxtA11yFrontend === 'undefined') {
            console.error('NXT Accessibility frontend settings not found.');
            return;
        }
        
        // Find the target element
        let targetElement = null;
        
        // Try to find by class name if specified
        if (nxtA11yFrontend.targetClass) {
            targetElement = document.querySelector('.' + nxtA11yFrontend.targetClass);
        }
        
        // If not found by class or no class was specified, try to find by element type
        if (!targetElement && nxtA11yFrontend.targetElement) {
            targetElement = document.querySelector(nxtA11yFrontend.targetElement);
        }
        
        // If we found an element and it doesn't already have an ID, add one
        if (targetElement && !targetElement.id) {
            targetElement.id = nxtA11yFrontend.skipTargetId;
            
            // Update the skip link href if needed
            const skipLink = document.querySelector('.' + nxtA11yFrontend.skipLinkClass);
            if (skipLink) {
                skipLink.setAttribute('href', '#' + nxtA11yFrontend.skipTargetId);
            }
        }
    });
})(); 