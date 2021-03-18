/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document) {
  'use strict';
  /*
   * JField 'showon' class
   */

  var Showon = /*#__PURE__*/function () {
    /*
     * Constructor
     *
     * @param {HTMLElement} cont Container element
     */
    function Showon(cont) {
      var _this = this;

      _classCallCheck(this, Showon);

      var self = this;
      this.container = cont || document;
      this.fields = {// origin-field-name: {
        //   origin:  ['collection of all the trigger nodes'],
        //   targets: ['collection of nodes to be controlled control']
        // }
      };
      this.showonFields = [].slice.call(this.container.querySelectorAll('[data-showon]')); // Populate the fields data

      if (this.showonFields.length) {
        // @todo refactor this, dry
        this.showonFields.forEach(function (field) {
          var jsondata = field.getAttribute('data-showon') || '';
          var showonData = JSON.parse(jsondata);
          var localFields;

          if (showonData.length) {
            localFields = [].slice.call(self.container.querySelectorAll("[name=\"".concat(showonData[0].field, "\"], [name=\"").concat(showonData[0].field, "[]\"]")));

            if (!_this.fields[showonData[0].field]) {
              _this.fields[showonData[0].field] = {
                origin: [],
                targets: []
              };
            } // Add trigger elements


            localFields.forEach(function (cField) {
              if (_this.fields[showonData[0].field].origin.indexOf(cField) === -1) {
                _this.fields[showonData[0].field].origin.push(cField);
              }
            }); // Add target elements

            _this.fields[showonData[0].field].targets.push(field); // Data showon can have multiple values


            if (showonData.length > 1) {
              showonData.forEach(function (value, index) {
                if (index === 0) {
                  return;
                }

                localFields = [].slice.call(self.container.querySelectorAll("[name=\"".concat(value.field, "\"], [name=\"").concat(value.field, "[]\"]")));

                if (!_this.fields[showonData[0].field]) {
                  _this.fields[showonData[0].field] = {
                    origin: [],
                    targets: []
                  };
                } // Add trigger elements


                localFields.forEach(function (cField) {
                  if (_this.fields[showonData[0].field].origin.indexOf(cField) === -1) {
                    _this.fields[showonData[0].field].origin.push(cField);
                  }
                }); // Add target elements

                if (_this.fields[showonData[0].field].targets.indexOf(field) === -1) {
                  _this.fields[showonData[0].field].targets.push(field);
                }
              });
            }
          }
        }); // Do some binding

        this.linkedOptions = this.linkedOptions.bind(this); // Attach events to referenced element, to check condition on change and keyup

        Object.keys(this.fields).forEach(function (key) {
          if (_this.fields[key].origin.length) {
            _this.fields[key].origin.forEach(function (elem) {
              // Initialise
              self.linkedOptions(key); // Setup listeners

              elem.addEventListener('change', function () {
                self.linkedOptions(key);
              });
              elem.addEventListener('keyup', function () {
                self.linkedOptions(key);
              });
            });
          }
        });
      }
    }
    /**
     *
     * @param key
     */


    _createClass(Showon, [{
      key: "linkedOptions",
      value: function linkedOptions(key) {
        var _this2 = this;

        // Loop through the elements that need to be either shown or hidden
        this.fields[key].targets.forEach(function (field) {
          var elementShowonDatas = JSON.parse(field.getAttribute('data-showon')) || [];
          var showfield = true;
          var itemval; // Check if target conditions are satisfied

          elementShowonDatas.forEach(function (elementShowonData, index) {
            var condition = elementShowonData || {};
            condition.valid = 0; // Test in each of the elements in the field array if condition is valid

            _this2.fields[key].origin.forEach(function (originField) {
              if (originField.name !== elementShowonData.field) {
                return;
              }

              var originId = originField.id; // If checkbox or radio box the value is read from properties

              if (originField.getAttribute('type') && ['checkbox', 'radio'].indexOf(originField.getAttribute('type').toLowerCase()) !== -1) {
                if (!originField.checked) {
                  // Unchecked fields will return a blank and so always match
                  // a != condition so we skip them
                  return;
                }

                itemval = document.getElementById(originId).value;
              } else {
                // Select lists, text-area etc. Note that multiple-select list returns
                // an Array here s0 we can always treat 'itemval' as an array
                itemval = document.getElementById(originId).value; // A multi-select <select> $field  will return null when no elements are
                // selected so we need to define itemval accordingly

                if (itemval === null && originField.tagName.toLowerCase() === 'select') {
                  itemval = [];
                }
              } // Convert to array to allow multiple values in the field (e.g. type=list multiple)
              // and normalize as string


              if (!(_typeof(itemval) === 'object')) {
                itemval = JSON.parse("[\"".concat(itemval, "\"]"));
              } // Test if any of the values of the field exists in showon conditions


              itemval.forEach(function (val) {
                // ":" Equal to one or more of the values condition
                if (condition.sign === '=' && condition.values.indexOf(val) !== -1) {
                  condition.valid = 1;
                } // "!:" Not equal to one or more of the values condition


                if (condition.sign === '!=' && condition.values.indexOf(val) === -1) {
                  condition.valid = 1;
                }
              });
            }); // Verify conditions
            // First condition (no operator): current condition must be valid


            if (condition.op === '') {
              if (condition.valid === 0) {
                showfield = false;
              }
            } else {
              // Other conditions (if exists)
              // AND operator: both the previous and current conditions must be valid
              if (condition.op === 'AND' && condition.valid + elementShowonDatas[index - 1].valid < 2) {
                showfield = false;
                condition.valid = 0;
              } // OR operator: one of the previous and current conditions must be valid


              if (condition.op === 'OR' && condition.valid + elementShowonDatas[index - 1].valid > 0) {
                showfield = true;
                condition.valid = 1;
              }
            }
          }); // If conditions are satisfied show the target field(s), else hide

          if (field.tagName !== 'option') {
            if (showfield) {
              field.classList.remove('hidden');
            } else {
              field.classList.add('hidden');
            }
          } else {
            // TODO: If chosen or choices.js is active we should update them
            field.disabled = !showfield;
          }
        });
      }
    }]);

    return Showon;
  }();
  /**
   * Initialize 'showon' feature at an initial page load
   */


  document.addEventListener('DOMContentLoaded', function () {
    // eslint-disable-next-line no-new
    new Showon(document);
  });
  /**
   * Initialize 'showon' feature when part of the page was updated
   */

  document.addEventListener('joomla:updated', function (_ref) {
    var target = _ref.target;

    // Check is it subform, then wee need to fix some "showon" config
    if (target.classList.contains('subform-repeatable-group')) {
      var elements = [].slice.call(target.querySelectorAll('[data-showon]'));
      var baseName = target.getAttribute('data-baseName');
      var group = target.getAttribute('data-group');
      var search = new RegExp("\\[".concat(baseName, "\\]\\[").concat(baseName, "X\\]"), 'g');
      var replace = "[".concat(baseName, "][").concat(group, "]"); // Fix showon field names in a current group

      elements.forEach(function (element) {
        var showon = element.getAttribute('data-showon').replace(search, replace);
        element.setAttribute('data-showon', showon);
      });
    } // eslint-disable-next-line no-new


    new Showon(target);
  });
})(document);