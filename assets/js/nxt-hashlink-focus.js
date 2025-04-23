/**
 * NXT Accessibility Helper - Focus Management
 * Ensures proper focus handling for all hash links including skip links
 */
(function() {
	'use strict';
	
	document.addEventListener('DOMContentLoaded', function() {
		// Find all links with hash on the page (including skip links)
		const allLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
		
		allLinks.forEach(function(link) {
			link.addEventListener('click', function(e) {
				// Get the target ID from the href attribute
				const targetId = this.getAttribute('href').substring(1);
				const targetElement = document.getElementById(targetId);
				
				if (targetElement) {
					// Prevent the default click behavior temporarily
					e.preventDefault();
					
					// Make the target programmatically focusable if it isn't already
					if (!targetElement.hasAttribute('tabindex')) {
						targetElement.setAttribute('tabindex', '-1');
						
						// Add a class for styling
						targetElement.classList.add('nxt-hash-target');
					}
					
					// Set focus to the target element
					targetElement.focus();
					
					// Update the URL hash (this will scroll to the element)
					window.location.hash = targetId;
				}
			});
		});
		
		// Handle the case where the page loads with a hash in the URL already
		// This can happen if someone refreshes after using a hash link
		if (window.location.hash) {
			// Get the target ID from the URL hash
			const targetId = window.location.hash.substring(1);
			const targetElement = document.getElementById(targetId);
			
			if (targetElement) {
				// Make the target programmatically focusable if it isn't already
				if (!targetElement.hasAttribute('tabindex')) {
					targetElement.setAttribute('tabindex', '-1');
					
					// Add a class for styling
					targetElement.classList.add('nxt-hash-target');
				}
				
				// Set focus after a short delay to make sure the page has fully loaded
				setTimeout(function() {
					targetElement.focus();
				}, 100);
			}
		}
	});
})(); 