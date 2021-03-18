/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (Joomla) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var modals = document.getElementsByClassName('changelogModal');
    Array.from(modals).forEach(function (element) {
      element.addEventListener('click', function (modal) {
        Joomla.loadChangelog(modal.target.dataset.jsExtensionid, modal.target.dataset.jsView);
      });
    });
  });
  /**
   * Load the changelog data
   *
   * @param extensionId The extension ID to load the changelog for
   * @param view The view the changelog is for,
   *             this is used to determine which version number to show
   *
   * @since   4.0.0
   */

  Joomla.loadChangelog = function (extensionId, view) {
    var modal = document.querySelector("#changelogModal".concat(extensionId, " .modal-body"));
    Joomla.request({
      url: "index.php?option=com_installer&task=manage.loadChangelog&eid=".concat(extensionId, "&source=").concat(view, "&format=json"),
      onSuccess: function onSuccess(response) {
        var message = '';

        try {
          var result = JSON.parse(response);

          if (result.error) {
            var _result = _slicedToArray(result, 1);

            message = _result[0];
          } else {
            message = result.data;
          }
        } catch (exception) {
          message = exception;
        }

        modal.innerHTML = message;
      },
      onError: function onError(xhr) {
        modal.innerHTML = xhr.statusText;
      }
    });
  };
})(Joomla);