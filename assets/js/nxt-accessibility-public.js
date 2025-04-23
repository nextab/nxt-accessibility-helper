/**
 * NXT Accessibility Helper Public JavaScript
 * Handles dynamic ID assignment for target elements
 * 
 * Uses localized data (nxtA11yFrontend) that's passed from PHP via wp_localize_script
 */
(function() {
	'use strict';
	
	/**
	 * Check if the browser supports MutationObserver
	 */
	const supportsMutationObserver = typeof MutationObserver !== 'undefined';
	
	// Create a dedicated style element to handle framework conflicts
	function createStyleElement() {
		// Check if our style element already exists
		let styleElem = document.getElementById('nxt-a11y-styles');
		
		if (!styleElem) {
			styleElem = document.createElement('style');
			styleElem.id = 'nxt-a11y-styles';
			document.head.appendChild(styleElem);
		}
		
		return styleElem;
	}
	
	// Create a dedicated storage element for our selectors
	function createSelectorStorage() {
		// Create invisible element to store our target info
		let storage = document.getElementById('nxt-a11y-selector-storage');
		
		if (!storage) {
			storage = document.createElement('div');
			storage.id = 'nxt-a11y-selector-storage';
			storage.style.display = 'none';
			storage.setAttribute('aria-hidden', 'true');
			document.body.appendChild(storage);
		}
		
		return storage;
	}
	
	document.addEventListener('DOMContentLoaded', function() {
		// Check if our settings object exists
		if (typeof nxtA11yFrontend === 'undefined') {
			console.error('NXT Accessibility frontend settings not found.');
			return;
		}
		
		// Log the settings we received for debugging
		console.log('NXT Accessibility: Skip link settings', {
			targetId: nxtA11yFrontend.targetId,
			targetClass: nxtA11yFrontend.targetClass,
			targetElement: nxtA11yFrontend.targetElement,
			skipTargetId: nxtA11yFrontend.skipTargetId
		});
		
		// Find all skip links on the page
		const skipLinks = document.querySelectorAll('.' + nxtA11yFrontend.skipLinkClass);
		if (skipLinks.length === 0) {
			console.warn('NXT Accessibility: No skip links found with class ' + nxtA11yFrontend.skipLinkClass);
			return;
		}
		
		console.log('NXT Accessibility: Found ' + skipLinks.length + ' skip links');
		
		// Find the target element
		let targetElement = null;
		let targetId = nxtA11yFrontend.skipTargetId;
		let targetSelector = '';
		
		// First check if we already have a target with the ID
		if (nxtA11yFrontend.targetId) {
			console.log('NXT Accessibility: Looking for element with ID: ' + nxtA11yFrontend.targetId);
			targetElement = document.getElementById(nxtA11yFrontend.targetId);
			if (targetElement) {
				targetId = nxtA11yFrontend.targetId;
				targetSelector = '#' + targetId;
				console.log('NXT Accessibility: Found element with ID: ' + targetId);
			}
		}
		
		// If no element found by ID, try class name if specified
		if (!targetElement && nxtA11yFrontend.targetClass) {
			console.log('NXT Accessibility: Looking for element with class: ' + nxtA11yFrontend.targetClass);
			targetElement = document.querySelector('.' + nxtA11yFrontend.targetClass);
			if (targetElement) {
				targetSelector = '.' + nxtA11yFrontend.targetClass;
				console.log('NXT Accessibility: Found element with class: ' + nxtA11yFrontend.targetClass);
				// Log the element to inspect it
				console.log('Element found:', targetElement);
			}
		}
		
		// If still not found, try by element type
		if (!targetElement && nxtA11yFrontend.targetElement) {
			// Handle semantic elements properly (main, article, section, etc.)
			console.log('NXT Accessibility: Looking for element type: ' + nxtA11yFrontend.targetElement);
			
			// For semantic elements, we need to make sure we have a valid selector
			// targetElement might just be 'main' or 'article', which is a valid selector itself
			let elementSelector = nxtA11yFrontend.targetElement;
			
			// Check if the selector already has special characters that would indicate it's not just a tag name
			if (!/[#.:\[\]]/.test(elementSelector)) {
				// It's likely just a tag name like 'main', so ensure it's a valid selector
				console.log('NXT Accessibility: Using tag name selector: ' + elementSelector);
			}
			
			try {
				targetElement = document.querySelector(elementSelector);
				if (targetElement) {
					targetSelector = elementSelector;
					console.log('NXT Accessibility: Found element of type: ' + nxtA11yFrontend.targetElement);
					// Log the element to inspect it
					console.log('Element found:', targetElement);
				}
			} catch (e) {
				console.error('NXT Accessibility: Invalid selector: ' + elementSelector, e);
			}
		}
		
		// If we found an element
		if (targetElement) {
			// Store the selector information for future use
			const selectorStorage = createSelectorStorage();
			selectorStorage.setAttribute('data-target-selector', targetSelector);
			selectorStorage.setAttribute('data-skip-target-id', nxtA11yFrontend.skipTargetId);
			
			// Check if the element already has an ID
			if (targetElement.id) {
				// Use the existing ID
				targetId = targetElement.id;
				console.log('NXT Accessibility: Using existing ID: ' + targetId);
			} else {
				// Add our skip target ID - ensure the element doesn't have an ID first
				console.log('NXT Accessibility: Element before ID assignment:', targetElement);
				console.log('NXT Accessibility: Current ID: "' + targetElement.id + '"');
				
				try {
					// Create a CSS rule that specifically targets this element and adds the ID via an attribute selector
					if (targetSelector) {
						const styleElem = createStyleElement();
						styleElem.textContent += targetSelector + ' { scroll-margin-top: 60px; }';
						console.log('NXT Accessibility: Added CSS scroll-margin-top to target element');
					}
					
					// Force set the ID using setAttribute which is more reliable than setting the property directly
					targetElement.setAttribute('id', nxtA11yFrontend.skipTargetId);
					targetId = nxtA11yFrontend.skipTargetId;
					
					// Verify the ID was set
					console.log('NXT Accessibility: Element after ID assignment:', targetElement);
					console.log('NXT Accessibility: New ID: "' + targetElement.id + '"');
					
					// Set a data attribute as a backup identifier in case the ID gets removed
					targetElement.setAttribute('data-nxt-skip-target', 'true');
					
					if (targetElement.id === nxtA11yFrontend.skipTargetId) {
						console.log('NXT Accessibility: ID successfully set to: ' + targetId);
					} else {
						console.error('NXT Accessibility: Failed to set ID. Element ID is still: "' + targetElement.id + '"');
						
						// Try one more time with a different approach
						setTimeout(function() {
							targetElement.id = nxtA11yFrontend.skipTargetId;
							console.log('NXT Accessibility: Retry ID assignment - new ID: "' + targetElement.id + '"');
						}, 100);
					}
					
					// Set up a MutationObserver to ensure the ID stays set
					if (supportsMutationObserver) {
						setupMutationObserver(targetElement, targetId);
					}
				} catch (e) {
					console.error('NXT Accessibility: Error setting ID:', e);
				}
			}
			
			// Update all skip links to point to this target
			skipLinks.forEach(function(skipLink) {
				let oldHref = skipLink.getAttribute('href');
				skipLink.setAttribute('href', '#' + targetId);
				console.log('NXT Accessibility: Updated skip link href from', oldHref, 'to #' + targetId);
			});
			
			// Verify that we can find the element by ID after setting it
			let verifyElement = document.getElementById(targetId);
			if (verifyElement) {
				console.log('NXT Accessibility: Verified - can find element by ID: #' + targetId);
			} else {
				console.error('NXT Accessibility: Cannot find element by ID: #' + targetId + ' after setting it');
				
				// Set up a retry mechanism
				setTimeout(function() {
					verifyAndRepairTarget(targetElement, targetId, skipLinks);
				}, 200);
			}
			
			console.log('NXT Accessibility: Skip link targeting element #' + targetId);
			
			// Set up a periodic check to make sure the ID stays set
			setInterval(function() {
				periodicCheckAndRepair(targetId, targetSelector, skipLinks);
			}, 3000); // Check every 3 seconds
		} else {
			console.warn('NXT Accessibility: Could not find target element for skip link. Please check your settings.');
		}
	});
	
	/**
	 * Set up a MutationObserver to monitor changes to the ID attribute
	 * and ensure it stays set to our target ID
	 */
	function setupMutationObserver(element, targetId) {
		if (!element || !supportsMutationObserver) return;
		
		const observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				if (mutation.type === 'attributes' && mutation.attributeName === 'id') {
					if (element.id !== targetId) {
						console.warn('NXT Accessibility: Target ID changed from', targetId, 'to', element.id);
						// Reset the ID
						element.id = targetId;
						console.log('NXT Accessibility: Restored ID to:', element.id);
					}
				}
			});
		});
		
		// Start observing the element for attribute changes
		observer.observe(element, { attributes: true, attributeFilter: ['id'] });
		console.log('NXT Accessibility: MutationObserver set up to monitor ID changes');
	}
	
	/**
	 * Verify the target element has the correct ID and repair if needed
	 */
	function verifyAndRepairTarget(element, targetId, skipLinks) {
		if (!element) return;
		
		// Check if the element still has the ID
		if (element.id !== targetId) {
			console.warn('NXT Accessibility: Target ID missing or changed. Repairing...');
			
			// Reset the ID
			element.id = targetId;
			
			// Check if successful
			if (element.id === targetId) {
				console.log('NXT Accessibility: Successfully restored ID to:', targetId);
				
				// Update skip links again just to be safe
				if (skipLinks && skipLinks.length) {
					skipLinks.forEach(function(skipLink) {
						skipLink.setAttribute('href', '#' + targetId);
					});
				}
			} else {
				console.error('NXT Accessibility: Failed to restore ID. Using data attribute fallback.');
				
				// Try to find the element by the data attribute we set earlier
				const fallbackElement = document.querySelector('[data-nxt-skip-target="true"]');
				if (fallbackElement) {
					fallbackElement.id = targetId;
					console.log('NXT Accessibility: Used data attribute to find and repair target element');
				}
			}
		} else {
			console.log('NXT Accessibility: ID verification successful - element has correct ID:', targetId);
		}
	}
	
	/**
	 * Periodically check if our target element still has the correct ID
	 * and repair if needed - this helps with frameworks that might be removing our ID
	 */
	function periodicCheckAndRepair(targetId, targetSelector, skipLinks) {
		// Check if element exists with the target ID
		let element = document.getElementById(targetId);
		
		if (!element && targetSelector) {
			console.log('NXT Accessibility: Periodic check - target ID missing, trying to repair');
			
			// Try to find the element using the original selector
			try {
				element = document.querySelector(targetSelector);
				
				if (element) {
					// Found the element using the selector, now set the ID
					element.id = targetId;
					console.log('NXT Accessibility: Periodic check - restored ID:', targetId);
					
					// Update skip links if needed
					if (skipLinks && skipLinks.length) {
						skipLinks.forEach(function(skipLink) {
							skipLink.setAttribute('href', '#' + targetId);
						});
					}
				} else {
					// Try to find using data attribute
					element = document.querySelector('[data-nxt-skip-target="true"]');
					if (element) {
						element.id = targetId;
						console.log('NXT Accessibility: Periodic check - used data attribute to restore ID');
					} else {
						console.warn('NXT Accessibility: Periodic check - could not find target element');
					}
				}
			} catch (e) {
				console.error('NXT Accessibility: Periodic check - error finding element:', e);
			}
		}
	}
})(); 