/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

(function () {
  'use strict';

  var Searchtools = function () {
    function Searchtools(elem, options) {
      var _this = this;

      _classCallCheck(this, Searchtools);

      var defaults = {
        // Form options
        formSelector: '.js-stools-form',

        // Search
        searchFieldSelector: '.js-stools-field-search',
        clearBtnSelector: '.js-stools-btn-clear',

        // Global container
        mainContainerSelector: '.js-stools',

        // Filter fields
        searchBtnSelector: '.js-stools-btn-search',
        filterBtnSelector: '.js-stools-btn-filter',
        filterContainerSelector: '.js-stools-container-filters',
        filtersHidden: true,

        // List fields
        listBtnSelector: '.js-stools-btn-list',
        listContainerSelector: '.js-stools-container-list',
        listHidden: true,

        // Ordering specific
        orderColumnSelector: '.js-stools-column-order',
        orderBtnSelector: '.js-stools-btn-order',
        orderFieldSelector: '.js-stools-field-order',
        orderFieldName: 'list[fullordering]',
        limitFieldSelector: '.js-stools-field-limit',
        defaultLimit: 20,

        activeOrder: null,
        activeDirection: 'ASC',

        // Extra
        clearListOptions: false
      };

      this.element = elem;
      this.options = Joomla.extend(defaults, options);

      // Initialise selectors
      this.theForm = document.querySelector(this.options.formSelector);

      // Filters
      this.filterButton = document.querySelector(this.options.formSelector + ' ' + this.options.filterBtnSelector);
      this.filterContainer = document.querySelector(this.options.formSelector + ' ' + this.options.filterContainerSelector) ? document.querySelector(this.options.formSelector + ' ' + this.options.filterContainerSelector) : '';
      this.filtersHidden = this.options.filtersHidden;

      // List fields
      this.listButton = document.querySelector(this.options.listBtnSelector);
      this.listContainer = document.querySelector(this.options.formSelector + ' ' + this.options.listContainerSelector);
      this.listHidden = this.options.listHidden;

      // Main container
      this.mainContainer = document.querySelector(this.options.mainContainerSelector);

      // Search
      this.searchButton = document.querySelector(this.options.formSelector + ' ' + this.options.searchBtnSelector);
      this.searchField = document.querySelector(this.options.formSelector + ' ' + this.options.searchFieldSelector);
      this.searchString = null;
      this.clearButton = document.querySelector(this.options.clearBtnSelector);

      // Ordering
      this.orderCols = Array.prototype.slice.call(document.querySelectorAll(this.options.formSelector + ' ' + this.options.orderColumnSelector));
      this.orderField = document.querySelector(this.options.formSelector + ' ' + this.options.orderFieldSelector);

      // Limit
      this.limitField = document.querySelector(this.options.formSelector + ' ' + this.options.limitFieldSelector);

      // Init trackers
      this.activeColumn = null;
      this.activeDirection = this.options.activeDirection;
      this.activeOrder = this.options.activeOrder;
      this.activeLimit = null;

      // Extra options
      this.clearListOptions = this.options.clearListOptions;

      var self = this;

      // Get values
      this.searchString = this.searchField.value;

      // Do some binding
      this.showFilters = this.showFilters.bind(this);
      this.hideFilters = this.hideFilters.bind(this);
      this.showList = this.showList.bind(this);
      this.hideList = this.hideList.bind(this);
      this.toggleFilters = this.toggleFilters.bind(this);
      this.toggleList = this.toggleList.bind(this);
      this.checkFilter = this.checkFilter.bind(this);
      this.clear = this.clear.bind(this);
      this.createOrderField = this.createOrderField.bind(this);
      this.checkActiveStatus = this.checkActiveStatus.bind(this);
      this.activeFilter = this.activeFilter.bind(this);
      this.deactiveFilter = this.deactiveFilter.bind(this);
      this.getFilterFields = this.getFilterFields.bind(this);
      this.getListFields = this.getListFields.bind(this);
      this.hideContainer = this.hideContainer.bind(this);
      this.showContainer = this.showContainer.bind(this);
      this.toggleContainer = this.toggleContainer.bind(this);
      this.toggleDirection = this.toggleDirection.bind(this);
      this.updateFieldValue = this.updateFieldValue.bind(this);
      this.findOption = this.findOption.bind(this);

      if (this.filterContainer && this.filterContainer.classList.contains('js-stools-container-filters-visible')) {
        this.showFilters();
        this.showList();
      } else {
        this.hideFilters();
        this.hideList();
      }

      if (this.filterButton) {
        this.filterButton.addEventListener('click', function (e) {
          self.toggleFilters();
          e.stopPropagation();
          e.preventDefault();
        });
      }

      if (this.listButton) {
        this.listButton.addEventListener('click', function (e) {
          self.toggleList();
          e.stopPropagation();
          e.preventDefault();
        });
      }

      // Do we need to add to mark filter as enabled?
      if (this.getFilterFields()) {
        this.getFilterFields().forEach(function (i) {
          self.checkFilter(i);
          i.addEventListener('change', function () {
            self.checkFilter(i);
          });
        });
      }

      if (this.clearButton) {
        this.clearButton.addEventListener('click', self.clear);
      }

      // Check/create ordering field
      this.createOrderField();

      this.orderCols.forEach(function (item) {
        item.addEventListener('click', function (event) {
          // Order to set
          var newOrderCol = event.target.getAttribute('data-order');
          var newDirection = event.target.getAttribute('data-direction');
          var newOrdering = newOrderCol + ' ' + newDirection;

          // The data-order attribute is required
          if (newOrderCol.length) {
            self.activeColumn = newOrderCol;

            if (newOrdering !== self.activeOrder) {
              self.activeDirection = newDirection;
              self.activeOrder = newOrdering;

              // Update the order field
              self.updateFieldValue(self.orderField, newOrdering);
            } else {
              self.toggleDirection();
            }

            self.theForm.submit();
          }
        });
      });

      this.checkActiveStatus(this);

      document.body.addEventListener('click', function () {
        if (document.body.classList.contains('filters-shown')) {
          _this.hideFilters();
        }
      });

      this.filterContainer.addEventListener('click', function (e) {
        e.stopPropagation();
      }, true);
    }

    _createClass(Searchtools, [{
      key: 'checkFilter',
      value: function checkFilter(element) {
        var option = element.querySelector('option:checked');
        if (option) {
          if (option.value !== '') {
            this.activeFilter(element, this);
          } else {
            this.deactiveFilter(element, this);
          }
        }
      }
    }, {
      key: 'clear',
      value: function clear() {
        var self = this;

        if (self.searchField) {
          self.searchField.value = '';
        }

        if (self.getFilterFields()) {
          self.getFilterFields().forEach(function (i) {
            i.value = '';
            self.checkFilter(i);

            if (window.jQuery && window.jQuery.chosen) {
              window.jQuery(i).trigger('liszt:updated');
            }
          });
        }

        if (self.clearListOptions) {
          self.getListFields().forEach(function (i) {
            i.value = '';
            self.checkFilter(i);

            if (window.jQuery && window.jQuery.chosen) {
              window.jQuery(i).trigger('liszt:updated');
            }
          });

          // Special case to limit box to the default config limit
          document.querySelector('#list_limit').value = self.options.defaultLimit;

          if (window.jQuery && window.jQuery.chosen) {
            window.jQuery('#list_limit').trigger('liszt:updated');
          }
        }

        self.theForm.submit();
      }

      // eslint-disable-next-line class-methods-use-this

    }, {
      key: 'checkActiveStatus',
      value: function checkActiveStatus(cont) {
        var el = cont.mainContainer;
        var els = [].slice.call(el.querySelectorAll('.js-stools-field-filter select'));
        els.forEach(function (item) {
          if (item.classList.contains('active')) {
            cont.filterButton.classList.remove('btn-secondary');
            cont.filterButton.classList.add('btn-primary');
          }
        });
      }

      // eslint-disable-next-line class-methods-use-this

    }, {
      key: 'activeFilter',
      value: function activeFilter(element) {
        element.classList.add('active');
        var chosenId = '#' + element.getAttribute('id');
        var tmpEl = element.querySelector(chosenId);
        if (tmpEl) {
          tmpEl.classList.add('active');
        }
      }

      // eslint-disable-next-line class-methods-use-this

    }, {
      key: 'deactiveFilter',
      value: function deactiveFilter(element) {
        element.classList.remove('active');
        var chosenId = '#' + element.getAttribute('id');
        var tmpEl = element.querySelector(chosenId);
        if (tmpEl) {
          tmpEl.classList.remove('active');
        }
      }

      // eslint-disable-next-line consistent-return

    }, {
      key: 'getFilterFields',
      value: function getFilterFields() {
        if (this.filterContainer) {
          return Array.prototype.slice.call(this.filterContainer.querySelectorAll('select,input'));
        }
      }
    }, {
      key: 'getListFields',
      value: function getListFields() {
        return Array.prototype.slice.call(this.listContainer.querySelectorAll('select'));
      }

      // Common container functions
      // eslint-disable-next-line class-methods-use-this

    }, {
      key: 'hideContainer',
      value: function hideContainer(container) {
        if (container) {
          container.classList.remove('js-filters-show');
          document.body.classList.remove('filters-shown');
        }
      }

      // eslint-disable-next-line class-methods-use-this

    }, {
      key: 'showContainer',
      value: function showContainer(container) {
        container.classList.add('js-filters-show');
        document.body.classList.add('filters-shown');
      }
    }, {
      key: 'toggleContainer',
      value: function toggleContainer(container) {
        if (container.classList.contains('js-filters-show')) {
          this.hideContainer(container);
        } else {
          this.showContainer(container);
        }
      }

      // List container management

    }, {
      key: 'hideList',
      value: function hideList() {
        this.hideContainer(this.filterContainer);
      }
    }, {
      key: 'showList',
      value: function showList() {
        this.showContainer(this.filterContainer);
      }
    }, {
      key: 'toggleList',
      value: function toggleList() {
        this.toggleContainer(this.filterContainer);
      }

      // Filters container management

    }, {
      key: 'hideFilters',
      value: function hideFilters() {
        this.hideContainer(this.filterContainer);
      }
    }, {
      key: 'showFilters',
      value: function showFilters() {
        this.showContainer(this.filterContainer);
      }
    }, {
      key: 'toggleFilters',
      value: function toggleFilters() {
        this.toggleContainer(this.filterContainer);
      }
    }, {
      key: 'toggleDirection',
      value: function toggleDirection() {
        var self = this;

        var newDirection = 'ASC';

        if (self.activeDirection.toUpperCase() === 'ASC') {
          newDirection = 'DESC';
        }

        self.activeDirection = newDirection;
        self.activeOrder = self.activeColumn + ' ' + newDirection;

        self.updateFieldValue(self.orderField, self.activeOrder);
      }
    }, {
      key: 'createOrderField',
      value: function createOrderField() {
        var _this2 = this;

        var self = this;

        if (!this.orderField) {
          this.orderField = document.createElement('input');
          this.orderField.setAttribute('type', 'hidden');
          this.orderField.setAttribute('id', 'js-stools-field-order');
          this.orderField.setAttribute('class', 'js-stools-field-order');
          this.orderField.setAttribute('name', self.options.orderFieldName);
          this.orderField.setAttribute('value', self.activeOrder + ' ' + this.activeDirection);

          this.theForm.innerHTML += this.orderField.outerHTML;
        }

        // Add missing columns to the order select
        if (this.orderField.tagName.toLowerCase() === 'select') {
          var allOptions = [].slice.call(this.orderField.options);
          allOptions.forEach(function (option) {
            var value = option.getAttribute('data-order');
            var name = option.getAttribute('data-name');
            var direction = option.getAttribute('data-direction');

            if (value && value.length) {
              value = value + ' ' + direction;

              var $option = self.findOption(self.orderField, value);

              if (!$option.length) {
                $option = document.createElement('option');
                $option.text = name;
                $option.value = value;

                // If it is the active option select it
                if (option.classList.contains('active')) {
                  $option.setAttribute('selected', 'selected');
                }

                // Append the option an repopulate the chosen field
                _this2.orderFieldName.innerHTML += $option;
              }
            }
          });

          if (window.jQuery && window.jQuery.chosen) {
            window.jQuery(this.orderField).trigger('liszt:updated');
          }
        }

        this.activeOrder = this.orderField.value;
      }

      // eslint-disable-next-line class-methods-use-this

    }, {
      key: 'updateFieldValue',
      value: function updateFieldValue(field, newValue) {
        var type = field.getAttribute('type');

        if (type === 'hidden' || type === 'text') {
          field.setAttribute('value', newValue);
        } else if (field.tagName.toLowerCase() === 'select') {
          var allOptions = [].slice.call(field.options);
          var desiredOption = void 0;

          // Select the option result
          allOptions.forEach(function (option) {
            if (option.value === newValue) {
              desiredOption = option;
            }
          });

          if (desiredOption && desiredOption.length) {
            desiredOption.setAttribute('selected', 'selected');
          } else {
            // If the option does not exist create it on the fly
            var option = document.createElement('option');
            option.text = newValue;
            option.value = newValue;
            option.setAttribute('selected', 'selected');

            // Append the option an repopulate the chosen field
            field.appendChild(option);
          }

          field.value = newValue;
          // Trigger the chosen update
          if (window.jQuery && window.jQuery.chosen) {
            field.trigger('liszt:updated');
          }
        }
      }

      // eslint-disable-next-line class-methods-use-this,consistent-return

    }, {
      key: 'findOption',
      value: function findOption(select, value) {
        // eslint-disable-next-line no-plusplus
        for (var i = 0, l = select.length; l > i; i++) {
          if (select[i].value === value) {
            return select[i];
          }
        }
      }
    }]);

    return Searchtools;
  }();

  var onBoot = function onBoot() {
    if (Joomla.getOptions('searchtools')) {
      var options = Joomla.getOptions('searchtools');
      var element = document.querySelector(options.selector);

      // eslint-disable-next-line no-new
      new Searchtools(element, options);
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  // Execute on DOM Loaded Event
  document.addEventListener('DOMContentLoaded', onBoot);
})();
