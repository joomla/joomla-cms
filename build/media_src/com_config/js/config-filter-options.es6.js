/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
document.addEventListener("DOMContentLoaded", () => { 
  	
	function filterOptionsQuote(str) {
		return str.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&" );
	};
	
	function filterOptions(str) {
		
		let hideElements 		= '.tab-pane .alert, .tab-pane .field-spacer, fieldset legend, #sendtestmail, .tab-description';
		let hideElementBoxes	= '.tab-header';
		let tab 				= '#configTabs li a, .nav-tabs li a';
		let tabContent			= '#configContent div.tab-pane, #config-document div.tab-pane';
		let excludeTabs			= '#page-filters, #page-permissions, #permissions';
		let itemParameter		= '.control-group .control-label label';
		let itemParameterBox	= '.control-group';
		let tabRow				= '.tab-pane .row div';
		let tabFieldset			= '.tab-pane .row div fieldset';
		
		/* J39
		tab 					= '#configTabs li, .nav-tabs li';
		tabRow					= '.tab-pane .row-fluid div';
		tabFieldset				= '.tab-pane .row-fluid div fieldset';
		*/
		
		if (str.length > 0) {
			
			/* Make tabs disabled */
			document.querySelectorAll(tab).forEach((elem) => {
				elem.classList.add('disabled'),
				elem.classList.remove('active'),
				elem.classList.remove('show')
			});
			/* Make all tab contents active so they can be searched */
			document.querySelectorAll(tabContent).forEach((elem) => {elem.classList.add('active')});
			/* Hide some specific parts which are not neccessary to display in search results */
			document.querySelectorAll(hideElements).forEach((elem) => {elem.style.display = "none";});
			/* Hide boxes of specific parts which are not neccessary to display in search results */
			document.querySelectorAll(hideElementBoxes).forEach((elem) => {elem.parentElement.parentElement.style.display = "none";});
			/* Hide tab items which cannot be filtered */
			document.querySelectorAll(excludeTabs).forEach((elem) => {
				elem.classList.add('disabled'),
				elem.classList.remove('active'),
				elem.classList.remove('show')
			});
			
			
			/* Cancel columns in Global Configuration */
			document.querySelectorAll(tabRow).forEach((elem) => {elem.classList.add('filter-options-col')});
			document.querySelectorAll(tabFieldset).forEach((elem) => {elem.classList.add('filter-options-fieldset')});
			
			/* Foreach each item and check if it fits criteria. If yes, display it */
			document.querySelectorAll(itemParameter).forEach((elem) => {
				let ePP	= elem.parentElement.parentElement;
				let item = elem.innerHTML;
				ePP.style.display = "none";
			
				if (item && typeof item == "string") {
					let re = new RegExp(filterOptionsQuote(str), "i");
					let res = item.match(re);
					
					if (res) {
						ePP.style.display = "block";
					}
				}
			});
			return;
		} 
		
		/* Remove disabled class from all tabs */
		document.querySelectorAll(tab).forEach((elem) => {
			elem.classList.remove('disabled'),
			elem.classList.remove('active'),
			elem.classList.remove('show')
		});
		
		/* Make first tab in active */
		if(document.querySelector(tab)) {
			document.querySelector(tab).classList.add('active');
			document.querySelector(tab).classList.add('show');
		}
		
		/* Remove active class from all tab contents */
		document.querySelectorAll(tabContent).forEach((elem) => {
			elem.classList.remove('active'),
			elem.classList.remove('show')
		});
		
		/* Make first tab CONTENT in global configuration options active */
		if(document.querySelector(tabContent)) {
			document.querySelector(tabContent).classList.add('active');
			document.querySelector(tabContent).classList.add('show');
		}
		
		/* Display all hidden parts back - undo the changes we've made when searching */
		document.querySelectorAll(hideElements).forEach((elem) => {elem.style.display = "block";});
		
		/* Display all hidden boxex of parts back - undo the changes we've made when searching */ 
		document.querySelectorAll(hideElementBoxes).forEach((elem) => {elem.parentElement.parentElement.style.display = "block";});
		
		/* Display all hidden paremter items back - undo the changes we've made when searching */
		document.querySelectorAll(itemParameterBox).forEach((elem) => {elem.style.display = "block";});
		
		/* Display items which cannot be filtered - undo the changes we've made when searching */
		document.querySelectorAll(excludeTabs).forEach((elem) => {elem.classList.remove('disabled')});
		
		/* Cancel canceling of columns ( :-) ) in Global Configuration */
		document.querySelectorAll(tabRow).forEach((elem) => {elem.classList.remove('filter-options-col')});
		document.querySelectorAll(tabFieldset).forEach((elem) => {elem.classList.remove('filter-options-fieldset')});
		
	}

	
	/* Events */
	document.getElementById("filterOptionsClear").addEventListener("click", (event) => {
		document.getElementById("filterOptionsInput").value = "";
		filterOptions("");
	})
	
	document.getElementById("filterOptionsInput").addEventListener("input", (event) => {
		let eV = event.currentTarget.value;
		filterOptions(eV);
	});
});