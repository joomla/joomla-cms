;(function ($, window, document, undefined) {

	// Create the defaults once
	var pluginName = "searchtools";

	var defaults = {
		mainContainerSelector   : '.js-stools',
		clearBtnSelector        : '.js-stools-btn-clear',
		searchBtnSelector       : '.js-stools-btn-search',
		filterBtnSelector       : '.js-stools-btn-filter',
		containerSelector       : '.js-stools-container',
		filterContainerSelector : '.stools-filters',
		orderBtnSelector        : '.js-stools-btn-order',
		orderContainerSelector  : '.stools-list',
		searchInputSelector     : '.js-stools-search-string',
		filtersApplied          : false,
		searchString            : null,
		orderColSelector        : '.js-order-col',
		orderingFieldSelector   : '#list_fullordering',
		orderingFieldName       : 'list[fullordering]',
		defaultLimit            : 20
	};

	// The actual plugin constructor
	function Plugin(element, options) {
		this.element = element;
		this.options = $.extend({}, defaults, options);
		this._defaults = defaults;

		// Initialise selectors
		this.theForm        = this.element;

		// Filters
		this.filterButton    = $(this.options.filterBtnSelector);
		this.filterContainer = $(this.options.filterContainerSelector);

		// Orders
		this.orderButton     = $(this.options.orderBtnSelector);
		this.orderContainer  = $(this.options.orderContainerSelector);

		// Main container
		this.mainContainer = $(this.options.mainContainerSelector);
		this.container     = $(this.options.containerSelector);

		// Search
		this.searchButton    = $(this.options.searchBtnSelector);
		this.searchInput     = $(this.options.searchInputSelector);

		this.searchString = $(this.options.searchString);

		this.clearButton     = $(this.options.clearBtnSelector);

		// Ordering
		this.orderCols     = $(this.options.orderColSelector);
		this.orderingField = $(this.options.orderingFieldSelector);

		// Init trackers
		this.activeCol       = null;
		this.activeDirection = 'ASC';
		this.activeOrdering  = null;

		// Selector values
		this._name = pluginName;

		this.init();
	}

	Plugin.prototype = {
		init: function () {
			var self = this;

			self.filterButton.click(function(e) {
				self.toggleFilters();
				e.stopPropagation();
				e.preventDefault();
			});

			self.orderButton.click(function(e) {
				self.toggleOrder();
				e.stopPropagation();
				e.preventDefault();
			});

			// Do we need to add to mark filter as enabled?
			self.getFilters().each(function(i, element) {
				self.checkFilter(element);
				$(element).change(function () {
					self.checkFilter(element);
				});
			});

			self.clearButton.click(function(e) {
				self.clear();
			});

			if (this.options.filtersApplied) {
				self.toggleFilters();
			}

			// Check/create ordering field
			this.createOrderingField();

			this.orderCols.click(function() {

				// Order to set
				var newOrderCol = $(this).attr('data-order');
				var newDirection = 'ASC';
				var newOrdering = newOrderCol + ' ' + newDirection;

				// The data-order attrib is required
				if (newOrderCol.length)
				{
					self.activeCol = newOrderCol;

					if (newOrdering !== self.activeOrdering)
					{
						self.activeDirection = newDirection;
						self.activeOrdering  = newOrdering;

						// Update the order field
						self.updateFieldValue(self.orderingField, newOrdering);
					}
					else
					{
						self.toggleDirection();
					}

					self.theForm.submit();
				}

			});
		},
		checkFilter: function (element) {
			var self = this;

			var option = $(element).find('option:selected');
			if (option.val() != '') {
				self.activeFilter(element);
			} else {
				self.deactiveFilter(element);
			}
		},
		clear: function () {
			var self = this;

			self.getFilters().each(function(i, element) {
				$(element).val('');
				self.checkFilter(element);
				$(element).trigger('liszt:updated');
			});

			// Special case to limit box to the default config limit
			$('#list_limit').val(self.options.defaultLimit).trigger('liszt:updated');

			self.searchInput.val('');
			self.theForm.submit();
		},
		activeFilter: function (element) {
			var self = this;

			$(element).addClass('active');
			var chosenId = '#' + $(element).attr('id') + '_chzn';
			$(chosenId).addClass('active');
		},
		deactiveFilter: function (element) {
			var self = this;

			$(element).removeClass('active');
			var chosenId = '#' + $(element).attr('id') + '_chzn';
			$(chosenId).removeClass('active');
		},
		getFilters: function () {
			var self = this;

			return self.mainContainer.find('select');
		},
		hideContainer: function () {
			var self = this;

			self.container.hide('fast');
			self.container.removeClass('shown');
		},
		showContainer: function () {
			var self = this;

			self.container.show('fast');
			self.container.addClass('shown');
		},
		hideFilters: function () {
			var self = this;

			self.filterContainer.hide('fast');
			self.filterContainer.removeClass('shown');
		},
		showFilters: function () {
			var self = this;

			self.filterContainer.show('fast');
			self.filterContainer.addClass('shown');
		},
		toggleFilters: function () {
			var self = this;

			if (self.container.hasClass('shown')) {
				self.hideContainer();
				self.filterButton.removeClass('btn-primary');
			} else {
				self.showContainer();
				self.filterButton.addClass('btn-primary');
			}
		},
		hideOrder: function () {
			var self = this;

			self.orderContainer.hide('fast');
			self.orderContainer.removeClass('shown');
		},
		showOrder: function () {
			var self = this;

			self.orderContainer.show('fast');
			self.orderContainer.addClass('shown');
		},
		toggleOrder: function () {
			var self = this;

			if (self.container.hasClass('shown')) {
				self.hideOrder();
				self.orderButton.removeClass('btn-inverse');
			} else {
				self.showOrder();
				self.orderButton.addClass('btn-inverse');
			}
		},
		toggleDirection: function () {
			var self = this;

			var newDirection = 'ASC';

			if (self.activeDirection.toUpperCase() == 'ASC')
			{
				newDirection = 'DESC';
			}

			self.activeDirection = newDirection;
			self.activeOrdering  = self.activeCol + ' ' + newDirection;

			self.updateFieldValue(self.orderingField, self.activeOrdering);
		},
		createOrderingField: function () {

			var self = this;

			if (!this.orderingField.length)
			{
				this.orderingField = $('<input>').attr({
				    type: 'hidden',
				    id: 'js-ordering-field',
				    class: 'js-ordering-field',
				    name: self.options.orderingFieldName
				});

				this.orderingField.appendTo(this.theForm);
			}

			// Add missing columns to the order select
			if (this.orderingField.is('select'))
			{
				this.orderCols.each(function(){
					var value     = $(this).attr('data-order');
					var name      = $(this).attr('data-name');
					var direction = $(this).attr('data-direction');

					if (value.length)
					{
						value = value + ' ' + direction;

						var option = self.findOption(self.orderingField, value);

						if (!option.length)
						{
							var option = $('<option>');
							option.text(name).val(value);

							// If it is the active option select it
							if ($(this).hasClass('active'))
							{
								option.attr('selected', 'selected');
							}

							// Append the option an repopulate the chosen field
							self.orderingField.append(option);
						}
					}

				});

				this.orderingField.trigger('liszt:updated');
			}

			this.activeOrdering  = this.orderingField.val();
		},
		updateFieldValue: function (field, newValue) {

			var type = field.attr('type');

			if (type === 'hidden' || type === 'text')
			{
				field.attr('value', newValue);
			}
			else if (field.is('select'))
			{
				// Select the option result
				var desiredOption = field.find('option').filter(function () { return $(this).val() == newValue; });

				if (desiredOption.length)
				{
					desiredOption.attr('selected', 'selected');
				}
				// If the option does not exist create it on the fly
				else
				{
					var option = $('<option>');
					option.text(newValue).val(newValue);
					option.attr('selected','selected');

					// Append the option an repopulate the chosen field
					field.append(option);
				}
				// Trigger the chosen update
				field.trigger('liszt:updated');

			}
		},
		findOption: function(select, value) {
			return select.find('option').filter(function () { return $(this).val() == value; });
		}
	};

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, "plugin_" + pluginName)) {
				$.data(this, "plugin_" + pluginName, new Plugin(this, options));
			}
		});
	};

})(jQuery, window, document);
