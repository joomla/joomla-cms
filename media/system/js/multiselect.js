/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
(function (Joomla) {
  'use strict';

  var JMultiSelect = /*#__PURE__*/function () {
    function JMultiSelect(formElement) {
      var _this = this;

      _classCallCheck(this, JMultiSelect);

      this.tableEl = document.querySelector(formElement);

      if (this.tableEl) {
        this.boxes = [].slice.call(this.tableEl.querySelectorAll('input[type=checkbox]'));
        this.rows = [].slice.call(document.querySelectorAll('tr[class^="row"]'));
        this.checkallToggle = document.querySelector('[name="checkall-toggle"]');
        this.onCheckallToggleClick = this.onCheckallToggleClick.bind(this);
        this.onRowClick = this.onRowClick.bind(this);

        if (this.checkallToggle) {
          this.checkallToggle.addEventListener('click', this.onCheckallToggleClick);
        }

        if (this.rows.length) {
          this.rows.forEach(function (row) {
            row.addEventListener('click', _this.onRowClick);
          });
        }
      }
    } // Changes the background-color on every cell inside a <tr>
    // eslint-disable-next-line class-methods-use-this


    _createClass(JMultiSelect, [{
      key: "changeBg",
      value: function changeBg(row, isChecked) {
        // Check if it should add or remove the background colour
        if (isChecked) {
          [].slice.call(row.querySelectorAll('td, th')).forEach(function (elementToMark) {
            elementToMark.classList.add('row-selected');
          });
        } else {
          [].slice.call(row.querySelectorAll('td, th')).forEach(function (elementToMark) {
            elementToMark.classList.remove('row-selected');
          });
        }
      }
    }, {
      key: "onCheckallToggleClick",
      value: function onCheckallToggleClick(_ref) {
        var _this2 = this;

        var target = _ref.target;
        var isChecked = target.checked;
        this.rows.forEach(function (row) {
          _this2.changeBg(row, isChecked);
        });
      }
    }, {
      key: "onRowClick",
      value: function onRowClick(_ref2) {
        var target = _ref2.target,
            shiftKey = _ref2.shiftKey;

        // Do not interfere with links or buttons
        if (target.tagName && (target.tagName.toLowerCase() === 'a' || target.tagName.toLowerCase() === 'button')) {
          return;
        }

        if (!this.boxes.length) {
          return;
        }

        var currentRowNum = this.rows.indexOf(target.closest('tr'));
        var currentCheckBox = this.checkallToggle ? currentRowNum + 1 : currentRowNum;
        var isChecked = this.boxes[currentCheckBox].checked;

        if (currentCheckBox >= 0) {
          if (!(target.id === this.boxes[currentCheckBox].id)) {
            // We will prevent selecting text to prevent artifacts
            if (shiftKey) {
              document.body.style['-webkit-user-select'] = 'none';
              document.body.style['-moz-user-select'] = 'none';
              document.body.style['-ms-user-select'] = 'none';
              document.body.style['user-select'] = 'none';
            }

            this.boxes[currentCheckBox].checked = !this.boxes[currentCheckBox].checked;
            isChecked = this.boxes[currentCheckBox].checked;
            Joomla.isChecked(this.boxes[currentCheckBox].checked, this.tableEl.id);
          }

          this.changeBg(this.rows[currentCheckBox - 1], isChecked); // Restore normality

          if (shiftKey) {
            document.body.style['-webkit-user-select'] = 'none';
            document.body.style['-moz-user-select'] = 'none';
            document.body.style['-ms-user-select'] = 'none';
            document.body.style['user-select'] = 'none';
          }
        }
      }
    }]);

    return JMultiSelect;
  }();

  var onBoot = function onBoot() {
    if (!Joomla) {
      // eslint-disable-next-line no-new
      new JMultiSelect('#adminForm');
    } else if (Joomla.getOptions && typeof Joomla.getOptions === 'function' && Joomla.getOptions('js-multiselect')) {
      if (Joomla.getOptions('js-multiselect').formName) {
        // eslint-disable-next-line no-new
        new JMultiSelect("#".concat(Joomla.getOptions('js-multiselect').formName));
      } else {
        // eslint-disable-next-line no-new
        new JMultiSelect('#adminForm');
      }
    }
  };

  document.addEventListener('DOMContentLoaded', onBoot);
})(Joomla);