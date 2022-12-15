/**
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow selected tab to be remembered after save or page reload
 * keeping state in sessionStorage with better handling of multiple tab widgets per page
 * and not saving the state if there is no id in the url (like on the CREATE page of content)
 */

jQuery(function ($) {
	/**
	 * Tiny jQuery extension to allow getting of url params
	 * @use jQuery.urlParam('param') or $.urlParam('myRegex|anotherRegex')
	 * If no trailing equals sign in name, add one, allows for general reuse
	 */
	$.urlParam = function (name) {
		if (!new RegExp("=$").exec(name)) {
			name = name + '=';
		}
		var results = new RegExp("[\\?&](" + name + ")([^&#]*)").exec(window.location.href);
		return results ? results[1] : null;
	};

	// jQuery extension to get the XPATH of a DOM element
	$.getXpath = function (el) {
		if (typeof el == "string") {
			return document.evaluate(el, document, null, 0, null);
		}
		if (!el || el.nodeType != 1) {
			return "";
		}
		if (el.id) {
			return "//*[@id='" + el.id + "']";
		}
		var a = [];
		var sames = a.filter.call(el.parentNode.children, function (x) {
			return x.tagName == el.tagName;
		});
		var b = [];
		return $.getXpath(el.parentNode) + "/" + el.tagName.toLowerCase() + (sames.length > 1 ? "[" + (b.indexOf.call(sames, el) + 1) + "]" : "");
	};

	// jQuery extension to get the DOM element from an XPATH
	$.findXpath = function (exp, ctxt) {
		var item;
		var coll = [];
		var result = document.evaluate(exp, ctxt || document, null, 5, null);

		while (item = result.iterateNext()) {
			coll.push(item);
		}

		return $(coll);
	};

	var loadTabs = function () {

		/**
		 * Remove an item from an array
		 */
		function remove_item(activeTabsHrefs, tabCollection) {
			for (var i = 0; i < activeTabsHrefs.length; i++) {
				if (activeTabsHrefs[i].indexOf(tabCollection) > -1) {
					activeTabsHrefs.splice(i, 1);
				}
			}

			return activeTabsHrefs;
		}

		/**
		 * Generate the sessionStorage key we will use
		 * This is the URL minus some cleanup
		 */
		function getStorageKey() {
			return window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, "").split('#')[0];
		}

		/**
		 * Save this tab to the storage in the form of a pseudo keyed array
		 */
		function saveActiveTab(event) {

			if (!window.sessionStorage) {
				return;
			}
			// Get a new storage key, normally the full url we are on with some cleanup
			var storageKey = getStorageKey();

			// get this tabs own href
			var href = $(event.target).attr("href");

			// find the collection of tabs this tab belongs to, and calculate the unique xpath to it
			var tabCollection = $.getXpath($(event.target).closest(".nav-tabs").first().get(0));

			// error handling
			if (!tabCollection || typeof href == "undefined") {
				return;
			}

			// Create a dummy keyed array as js doesnt allow keyed arrays
			var storageValue = tabCollection + "|" + href;

			// Get the current array from the storage
			var activeTabsHrefs = JSON.parse(sessionStorage.getItem(storageKey));

			// If none start a new array
			if (!activeTabsHrefs) {
				var activeTabsHrefs = [];
			} else {
				// Avoid Duplicates in the storage
				remove_item(activeTabsHrefs, tabCollection);
			}

			// Save clicked tab, with relationship to tabCollection to the array
			activeTabsHrefs.push(storageValue);

			// Store the selected tabs as an array in sessionStorage
			sessionStorage.setItem(storageKey, JSON.stringify(activeTabsHrefs));
		}

		// Array with active tabs hrefs
		var activeTabsHrefs = JSON.parse(sessionStorage.getItem(getStorageKey()));

		// jQuery object with all tabs links
		var alltabs = $("a[data-bs-toggle='tab']");

		// When a tab is clicked, save its state!
		alltabs.on("click", function (e) {
			saveActiveTab(e);
		});

		// Clean default tabs
		alltabs.parent(".active").removeClass("active");

		// If we cannot find a tab storage for this url, see if we are coming from a save of a new item
		if (!activeTabsHrefs) {
			var unSavedStateUrl = getStorageKey().replace(/\&id=[0-9]*|[a-z]\&{1}_id=[0-9]*/, '');
			activeTabsHrefs = JSON.parse(sessionStorage.getItem(unSavedStateUrl));
			sessionStorage.removeItem(unSavedStateUrl);
		}

		// we have some tab states to restore, if we see a hash then let that trump the saved state
		if (activeTabsHrefs !== null && !window.location.hash) {

			// When moving from tab area to a different view
			$.each(activeTabsHrefs, function (index, tabFakexPath) {

				// Click the tab
				var parts = tabFakexPath.split("|");
				$.findXpath(parts[0]).find("a[data-bs-toggle='tab'][href='" + parts[1] + "']").get(0).click();

			});

		} else { // clean slate start

			// a list of tabs to click
			var tabsToClick = [];

			// If we are passing a hash then this trumps everything
			if (window.location.hash) {

				// for each set of tabs on the page
				alltabs.parents("ul").each(function (index, ul) {

					// If no tabs is saved, activate first tab from each tab set and save it
					var tabToClick = $(ul).find("a[href='" + window.location.hash + "']");

					// If we found some|one
					if (tabToClick.length) {

						// if we managed to locate its selector directly
						if (tabToClick.selector) {

							// highlight tab of the tabs if the hash matches
							tabsToClick.push(tabToClick);
						} else {

							// highlight first tab of the tabs
							tabsToClick.push(tabToClick.first());
						}

						var parentPane = tabToClick.closest('.tab-pane');

						// bubble up for nested tabs (like permissions tabs in the permissions pane)
						if (parentPane) {
							var id = parentPane.attr('id');
							if (id) {
								var parentTabToClick = $(parentPane).find("a[href='#" + id + "']");
								if (parentTabToClick) {
									tabsToClick.push(parentTabToClick);
								}
							}
						}
					}

					// cleanup for another loop
					parentTabToClick = null;
					tabToClick = null;
					parentPane = null;
					id = null;
				});

				// run in the right order bubbling up
				tabsToClick.reverse();

				// for all queued tabs
				for (var i = 0; i < tabsToClick.length; i++) {

					// click the tabs, thus storing them
					jQuery(tabsToClick[i].selector).click();
				}

				// Remove the #hash in the url - with support for older browsers with no flicker
				var scrollV, scrollH, loc = window.location;
				if ("pushState" in history)
					history.pushState("", document.title, loc.pathname + loc.search);
				else {
					// Prevent scrolling by storing the page's current scroll offset
					scrollV = document.body.scrollTop;
					scrollH = document.body.scrollLeft;
					loc.hash = "";
					// Restore the scroll offset, should be flicker free
					document.body.scrollTop = scrollV;
					document.body.scrollLeft = scrollH;
				}

			} else {
				alltabs.parents("ul").each(function (index, ul) {
					// If no tabs is saved, activate first tab from each tab set and save it
					$(ul).find("a").first().click();
				});
			}
		}
	};

	setTimeout(loadTabs, 100);
});
