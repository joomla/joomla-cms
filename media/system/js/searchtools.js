(function () {
	"use strict";

	// The actual plugin constructor
	var Searchtools = function(element, options) {
		var defaults = {
			// Form options
			formSelector            : '.js-stools-form',

			// Search
			searchFieldSelector     : '.js-stools-field-search',
			clearBtnSelector        : '.js-stools-btn-clear',

			// Global container
			mainContainerSelector   : '.js-stools',

			// Filter fields
			searchBtnSelector       : '.js-stools-btn-search',
			filterBtnSelector       : '.js-stools-btn-filter',
			filterContainerSelector : '.js-stools-container-filters',
			filtersHidden           : true,

			// List fields
			listBtnSelector         : '.js-stools-btn-list',
			listContainerSelector   : '.js-stools-container-list',
			listHidden              : true,

			// Ordering specific
			orderColumnSelector     : '.js-stools-column-order',
			orderBtnSelector        : '.js-stools-btn-order',
			orderFieldSelector      : '.js-stools-field-order',
			orderFieldName          : 'list[fullordering]',
			limitFieldSelector      : '.js-stools-field-limit',
			defaultLimit            : 20,

			activeOrder             : null,
			activeDirection         : 'ASC',

			// Extra
			clearListOptions        : false
		};

		this.element = element;
		this.options = Joomla.extend(defaults, options);

		// Initialise selectors
		this.theForm        = document.querySelector(this.options.formSelector);

		// Filters
		this.filterButton    = document.querySelector(this.options.formSelector + ' ' + this.options.filterBtnSelector);
		this.filterContainer = document.querySelector(this.options.formSelector + ' ' + this.options.filterContainerSelector) ? document.querySelector(this.options.formSelector + ' ' + this.options.filterContainerSelector) : '';
		this.filtersHidden   = this.options.filtersHidden;

		// List fields
		this.listButton    = document.querySelector(this.options.listBtnSelector);
		this.listContainer = document.querySelector(this.options.formSelector + ' ' + this.options.listContainerSelector);
		this.listHidden    = this.options.listHidden;

		// Main container
		this.mainContainer = document.querySelector(this.options.mainContainerSelector);

		// Search
		this.searchButton = document.querySelector(this.options.formSelector + ' ' + this.options.searchBtnSelector);
		this.searchField  = document.querySelector(this.options.formSelector + ' ' + this.options.searchFieldSelector);
		this.searchString = null;
		this.clearButton  = document.querySelector(this.options.clearBtnSelector);

		// Ordering
		this.orderCols  = Array.prototype.slice.call(document.querySelectorAll(this.options.formSelector + ' ' + this.options.orderColumnSelector));
		this.orderField = document.querySelector(this.options.formSelector + ' ' + this.options.orderFieldSelector);

		// Limit
		this.limitField = document.querySelector(this.options.formSelector + ' ' + this.options.limitFieldSelector);

		// Init trackers
		this.activeColumn    = null;
		this.activeDirection = this.options.activeDirection;
		this.activeOrder     = this.options.activeOrder;
		this.activeLimit     = null;

		// Extra options
		this.clearListOptions = this.options.clearListOptions;

		this.init();
	};

	Searchtools.prototype = {
		init: function () {
			var self = this;

			// IE < 9 - Avoid to submit placeholder value
			if(!document.addEventListener  ) {
				if (this.searchField.value === this.searchField.getAttribute('placeholder')) {
					this.searchField.value = '';
				}
			}

			// Get values
			this.searchString = this.searchField.value;

			if (this.filterContainer && this.filterContainer.classList.contains('js-stools-container-filters-visible')) {
				this.showFilters();
				this.showList();
			} else {
				this.hideFilters();
				this.hideList();
			}

			if (self.filterButton) {
				self.filterButton.addEventListener('click', function(e) {
					self.toggleFilters();
					e.stopPropagation();
					e.preventDefault();				
				});
			}

			if (self.listButton) {
				self.listButton.addEventListener('click', function(e) {
					self.toggleList();
					e.stopPropagation();
					e.preventDefault();
				});
			}

			// Do we need to add to mark filter as enabled?
			if (self.getFilterFields()) {
				self.getFilterFields().forEach(function(i) {
					self.checkFilter(i);
					i.addEventListener('change', function () {
						self.checkFilter(i);
					});
				});
			}

			if (self.clearButton) {
				self.clearButton.addEventListener('click', function () {
					self.clear();
				});
			}

			// Check/create ordering field
			this.createOrderField();

			self.orderCols.forEach(function(item) {
				item.addEventListener('click', function () {

					// Order to set
					var newOrderCol = this.getAttribute('data-order');
					var newDirection = this.getAttribute('data-direction');
					var newOrdering = newOrderCol + ' ' + newDirection;

					// The data-order attrib is required
					if (newOrderCol.length) {
						self.activeColumn = newOrderCol;

						if (newOrdering !== self.activeOrder) {
							self.activeDirection = newDirection;
							self.activeOrder = newOrdering;

							// Update the order field
							self.updateFieldValue(self.orderField, newOrdering);
						}
						else {
							self.toggleDirection();
						}

						self.theForm.submit();
					}

				});
			});

			self.checkActiveStatus(self);

			document.body.addEventListener('click', function(e) {
				if (document.body.classList.contains('filters-shown')) {
					self.hideFilters();
				}
			});
			self.filterContainer.addEventListener('click',function(e) {
			    e.stopPropagation();
			}, true);

		},
		checkFilter: function (element) {
			var self = this;
			var option = element.querySelector('option:checked');
			if (option) {
                if (option.value !== '') {
                    self.activeFilter(element, self);
                } else {
                    self.deactiveFilter(element, self);
                }
            }
		},
		clear: function () {
			var self = this;
			
			if (self.searchField) {
                		self.searchField.value = '';
			}
			
			if (self.getFilterFields()) {
				self.getFilterFields().forEach(function(i) {
					i.value = '';
					self.checkFilter(i);

					if (window.jQuery && jQuery.chosen) {
						jQuery(i).trigger('liszt:updated');
					}
				});
			}

			if (self.clearListOptions) {
				self.getListFields().forEach(function(i) {
					i.value = '';
					self.checkFilter(i);

					if (window.jQuery && jQuery.chosen) {
						jQuery(i).trigger('liszt:updated');
					}
				});

				// Special case to limit box to the default config limit
				document.querySelector('#list_limit').value = self.options.defaultLimit;

				if (window.jQuery && jQuery.chosen) {
					jQuery('#list_limit').trigger('liszt:updated');
				}
			}

			self.theForm.submit();
		},
		checkActiveStatus: function(cont) {
			var el = cont.mainContainer;
			var els = [].slice.call(el.querySelectorAll('.js-stools-field-filter select'));
			els.forEach(function(item) {
				if (item.classList.contains('active')) {
					cont.filterButton.classList.remove('btn-secondary');
					cont.filterButton.classList.add('btn-primary');
					return '';
				}
			});
		},
		activeFilter: function (element, cont) {
			element.classList.add('active');
			var chosenId = '#' + element.getAttribute('id');
			var tmpEl = element.querySelector(chosenId);
			if (tmpEl) {
				tmpEl.classList.add('active');	
			}
		},
		deactiveFilter: function (element, cont) {
			element.classList.remove('active');
			var chosenId = '#' + element.getAttribute('id');
			var tmpEl = element.querySelector(chosenId);
			if (tmpEl) {
				tmpEl.classList.remove('active');
			}
		},
		getFilterFields: function () {
			if (this.filterContainer) {
				return Array.prototype.slice.call(this.filterContainer.querySelectorAll('select,input'));
			}

		},
		getListFields: function () {
			return Array.prototype.slice.call(this.listContainer.querySelectorAll('select'));
		},
		// Common container functions
		hideContainer: function (container) {
			if (container) {
				container.classList.remove('js-filters-show');
				document.body.classList.remove('filters-shown');
			}
		},
		showContainer: function (container) {
			container.classList.add('js-filters-show');
			document.body.classList.add('filters-shown');
		},
		toggleContainer: function (container) {
			if (container.classList.contains('js-filters-show')) {
				this.hideContainer(container);
			} else {
				this.showContainer(container);
			}
		},
		// List container management
		hideList: function () {
			this.hideContainer(this.filterContainer);
		},
		showList: function () {
			this.showContainer(this.filterContainer);
		},
		toggleList: function () {
			this.toggleContainer(this.filterContainer);
		},
		// Filters container management
		hideFilters: function () {
			this.hideContainer(this.filterContainer);
		},
		showFilters: function () {
			this.showContainer(this.filterContainer);
		},
		toggleFilters: function () {
			this.toggleContainer(this.filterContainer);
		},
		toggleDirection: function () {
			var self = this;

			var newDirection = 'ASC';

			if (self.activeDirection.toUpperCase() == 'ASC')
			{
				newDirection = 'DESC';
			}

			self.activeDirection = newDirection;
			self.activeOrder  = self.activeColumn + ' ' + newDirection;

			self.updateFieldValue(self.orderField, self.activeOrder);
		},
		createOrderField: function () {

			var self = this;

			if (!this.orderField)
			{
				this.orderField = document.createElement('input');
				this.orderField.setAttribute('type', 'hidden');
				this.orderField.setAttribute('id', 'js-stools-field-order');
				this.orderField.setAttribute('class', 'js-stools-field-order');
				this.orderField.setAttribute('name', self.options.orderFieldName);
				this.orderField.setAttribute('value', self.activeOrder + ' ' + this.activeDirection);

				this.theForm.innerHTML+= this.orderField.outerHTML;
			}

			// Add missing columns to the order select
			if (this.orderField.tagName.toLowerCase() == 'select')
			{
				var allOptions = this.orderField.options;
				for (var i = 0, l = allOptions.length; l>i; i++) {

					var value     = allOptions[i].getAttribute('data-order');
					var name      = allOptions[i].getAttribute('data-name');
					var direction = allOptions[i].getAttribute('data-direction');

					if (value && value.length)
					{
						value = value + ' ' + direction;

						var $option = self.findOption(self.orderField, value);

						if (!$option.length)
						{
							var $option = document.createElement('option');
							$option.text = name;
							$option.value = value;

							// If it is the active option select it
							if (allOptions[i].classList.contains('active'))
							{
								$option.setAttribute('selected', 'selected');
							}

							// Append the option an repopulate the chosen field
							self.orderFieldName.innerHTML += $option;
						}
					}
				}

				if (window.jQuery && jQuery.chosen) {
					jQuery(this.orderField).trigger('liszt:updated');
				}
			}

			this.activeOrder  = this.orderField.value;
		},
		updateFieldValue: function (field, newValue) {

			var self = this,
				type = field.getAttribute('type');

			if (type === 'hidden' || type === 'text')
			{
				field.setAttribute('value', newValue);
			}
			else if (field.tagName.toLowerCase() === 'select')
			{
				// Select the option result
				var allOptions = field.options;
				for (var i = 0, l = allOptions.length; l>i; i++) {
					if (allOptions[i].value == newValue) {
						var desiredOption = allOptions[i];
					}
				}

				if (desiredOption && desiredOption.length)
				{
					desiredOption.setAttribute('selected', 'selected');
				}
				// If the option does not exist create it on the fly
				else
				{
					var option = document.createElement('option');
					option.text = name;
					option.value = newValue;
					option.setAttribute('selected','selected');

					// Append the option an repopulate the chosen field
					field.appendChild(option);
				}

				field.value = newValue;
				// Trigger the chosen update
				if (window.jQuery && jQuery.chosen) {
					field.trigger('liszt:updated');
				}
			}
		},
		findOption: function(select, value) {
			for (var i = 0, l = select.length; l>i; i++) {
				if (select[i].value == value) {
					return select[i];
				}
			}
		}
	};

	// Execute on DOM Loaded Event
	document.addEventListener('DOMContentLoaded', function(){
		if (Joomla.getOptions('searchtools')) {
			var options = Joomla.getOptions('searchtools'),
				element = document.querySelector(options.selector);

			new Searchtools(element, options);
		}
	});

})();
