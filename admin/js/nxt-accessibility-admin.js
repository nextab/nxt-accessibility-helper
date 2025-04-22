/**
 * NXT Accessibility Helper Admin JavaScript
 * Handles admin-specific functionality using vanilla JavaScript
 * 
 * Uses localized data (nxtA11ySettings) that's passed from PHP via wp_localize_script
 */
document.addEventListener('DOMContentLoaded', function() {
	'use strict';
	
	// Check if our settings object exists
	if (typeof nxtA11ySettings === 'undefined') {
		console.error('NXT Accessibility settings not found.');
		return;
	}
	
	// Initialize all color picker fields
	// We still need jQuery for WordPress' built-in color picker
	if (typeof jQuery !== 'undefined') {
		jQuery('.nxt-color-picker').wpColorPicker();
	}
	
	// Show/hide notes for custom style option - vanilla JS with localized data
	const linkStyleSelect = document.querySelector(nxtA11ySettings.selectors.linkStyle);
	
	if (linkStyleSelect) {
		function handleCustomStyleOption() {
			const selectedStyle = linkStyleSelect.value;
			let customStyleNote = document.getElementById(nxtA11ySettings.selectors.customStyleNoteId);
			
			if (selectedStyle === 'custom') {
				if (!customStyleNote) {
					customStyleNote = document.createElement('p');
					customStyleNote.id = nxtA11ySettings.selectors.customStyleNoteId;
					customStyleNote.className = 'description';
					customStyleNote.style.color = nxtA11ySettings.errorColor;
					customStyleNote.textContent = nxtA11ySettings.customStyleMessage;
					
					// Insert after the select element
					linkStyleSelect.parentNode.insertBefore(customStyleNote, linkStyleSelect.nextSibling);
				} else {
					customStyleNote.style.display = 'block';
				}
			} else if (customStyleNote) {
				customStyleNote.style.display = 'none';
			}
		}
		
		// Run on page load and when the select changes
		handleCustomStyleOption();
		linkStyleSelect.addEventListener('change', handleCustomStyleOption);
	}
}); 