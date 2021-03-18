/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/**
 * @package     Joomla.Plugin
 * @subpackage  System.webauthn
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
window.Joomla = window.Joomla || {};

(function (Joomla, document) {
  'use strict';
  /**
   * Converts a simple object containing query string parameters to a single, escaped query string.
   * This method is a necessary evil since Joomla.request can only accept data as a string.
   *
   * @param    object   {object}  A plain object containing the query parameters to pass
   * @param    prefix   {string}  Prefix for array-type parameters
   *
   * @returns  {string}
   */

  var interpolateParameters = function interpolateParameters(object) {
    var prefix = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    var encodedString = '';
    Object.keys(object).forEach(function (prop) {
      if (_typeof(object[prop]) !== 'object') {
        if (encodedString.length > 0) {
          encodedString += '&';
        }

        if (prefix === '') {
          encodedString += "".concat(encodeURIComponent(prop), "=").concat(encodeURIComponent(object[prop]));
        } else {
          encodedString += "".concat(encodeURIComponent(prefix), "[").concat(encodeURIComponent(prop), "]=").concat(encodeURIComponent(object[prop]));
        }

        return;
      } // Objects need special handling


      encodedString += "".concat(interpolateParameters(object[prop], prop));
    });
    return encodedString;
  };
  /**
   * A simple error handler
   *
   * @param   {String}  message
   */


  var handleCreationError = function handleCreationError(message) {
    Joomla.renderMessages({
      error: [message]
    });
  };
  /**
   * Ask the user to link an authenticator using the provided public key (created server-side).
   * Posts the credentials to the URL defined in post_url using AJAX.
   * That URL must re-render the management interface.
   * These contents will replace the element identified by the interface_selector CSS selector.
   *
   * @param   {String}  storeID            CSS ID for the element storing the configuration in its
   *                                        data properties
   * @param   {String}  interfaceSelector  CSS selector for the GUI container
   */
  // eslint-disable-next-line no-unused-vars


  Joomla.plgSystemWebauthnCreateCredentials = function (storeID, interfaceSelector) {
    // Make sure the browser supports Webauthn
    if (!('credentials' in navigator)) {
      Joomla.renderMessages({
        error: [Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_NO_BROWSER_SUPPORT')]
      });
      return;
    } // Extract the configuration from the store


    var elStore = document.getElementById(storeID);

    if (!elStore) {
      return;
    }

    var publicKey = JSON.parse(atob(elStore.dataset.public_key));
    var postURL = atob(elStore.dataset.postback_url);

    var arrayToBase64String = function arrayToBase64String(a) {
      return btoa(String.fromCharCode.apply(String, _toConsumableArray(a)));
    };

    var base64url2base64 = function base64url2base64(input) {
      var output = input.replace(/-/g, '+').replace(/_/g, '/');
      var pad = output.length % 4;

      if (pad) {
        if (pad === 1) {
          throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding');
        }

        output += new Array(5 - pad).join('=');
      }

      return output;
    }; // Convert the public key information to a format usable by the browser's credentials manager


    publicKey.challenge = Uint8Array.from(window.atob(base64url2base64(publicKey.challenge)), function (c) {
      return c.charCodeAt(0);
    });
    publicKey.user.id = Uint8Array.from(window.atob(publicKey.user.id), function (c) {
      return c.charCodeAt(0);
    });

    if (publicKey.excludeCredentials) {
      publicKey.excludeCredentials = publicKey.excludeCredentials.map(function (data) {
        data.id = Uint8Array.from(window.atob(base64url2base64(data.id)), function (c) {
          return c.charCodeAt(0);
        });
        return data;
      });
    } // Ask the browser to prompt the user for their authenticator


    navigator.credentials.create({
      publicKey: publicKey
    }).then(function (data) {
      var publicKeyCredential = {
        id: data.id,
        type: data.type,
        rawId: arrayToBase64String(new Uint8Array(data.rawId)),
        response: {
          clientDataJSON: arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
          attestationObject: arrayToBase64String(new Uint8Array(data.response.attestationObject))
        }
      }; // Send the response to your server

      var postBackData = {
        option: 'com_ajax',
        group: 'system',
        plugin: 'webauthn',
        format: 'raw',
        akaction: 'create',
        encoding: 'raw',
        data: btoa(JSON.stringify(publicKeyCredential))
      };
      Joomla.request({
        url: postURL,
        method: 'POST',
        data: interpolateParameters(postBackData),
        onSuccess: function onSuccess(responseHTML) {
          var elements = document.querySelectorAll(interfaceSelector);

          if (!elements) {
            return;
          }

          var elContainer = elements[0];
          elContainer.outerHTML = responseHTML;
        },
        onError: function onError(xhr) {
          handleCreationError("".concat(xhr.status, " ").concat(xhr.statusText));
        }
      });
    }).catch(function (error) {
      // An error occurred: timeout, request to provide the authenticator refused, hardware /
      // software error...
      handleCreationError(error);
    });
  };
  /**
   * Edit label button
   *
   * @param   {Element} that      The button being clicked
   * @param   {String}  storeID  CSS ID for the element storing the configuration in its data
   *                              properties
   */
  // eslint-disable-next-line no-unused-vars


  Joomla.plgSystemWebauthnEditLabel = function (that, storeID) {
    // Extract the configuration from the store
    var elStore = document.getElementById(storeID);

    if (!elStore) {
      return false;
    }

    var postURL = atob(elStore.dataset.postback_url); // Find the UI elements

    var elTR = that.parentElement.parentElement;
    var credentialId = elTR.dataset.credential_id;
    var elTDs = elTR.querySelectorAll('td');
    var elLabelTD = elTDs[0];
    var elButtonsTD = elTDs[1];
    var elButtons = elButtonsTD.querySelectorAll('button');
    var elEdit = elButtons[0];
    var elDelete = elButtons[1]; // Show the editor

    var oldLabel = elLabelTD.innerText;
    var elInput = document.createElement('input');
    elInput.type = 'text';
    elInput.name = 'label';
    elInput.defaultValue = oldLabel;
    var elSave = document.createElement('button');
    elSave.className = 'btn btn-success btn-sm';
    elSave.innerText = Joomla.JText._('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_SAVE_LABEL');
    elSave.addEventListener('click', function () {
      var elNewLabel = elInput.value;

      if (elNewLabel !== '') {
        var postBackData = {
          option: 'com_ajax',
          group: 'system',
          plugin: 'webauthn',
          format: 'json',
          encoding: 'json',
          akaction: 'savelabel',
          credential_id: credentialId,
          new_label: elNewLabel
        };
        Joomla.request({
          url: postURL,
          method: 'POST',
          data: interpolateParameters(postBackData),
          onSuccess: function onSuccess(rawResponse) {
            var result = false;

            try {
              result = JSON.parse(rawResponse);
            } catch (exception) {
              result = rawResponse === 'true';
            }

            if (result !== true) {
              handleCreationError(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_LABEL_NOT_SAVED'));
            }
          },
          onError: function onError(xhr) {
            handleCreationError("".concat(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_LABEL_NOT_SAVED'), " -- ").concat(xhr.status, " ").concat(xhr.statusText));
          }
        });
      }

      elLabelTD.innerText = elNewLabel;
      elEdit.disabled = false;
      elDelete.disabled = false;
      return false;
    }, false);
    var elCancel = document.createElement('button');
    elCancel.className = 'btn btn-danger btn-sm';
    elCancel.innerText = Joomla.JText._('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_CANCEL_LABEL');
    elCancel.addEventListener('click', function () {
      elLabelTD.innerText = oldLabel;
      elEdit.disabled = false;
      elDelete.disabled = false;
      return false;
    }, false);
    elLabelTD.innerHTML = '';
    elLabelTD.appendChild(elInput);
    elLabelTD.appendChild(elSave);
    elLabelTD.appendChild(elCancel);
    elEdit.disabled = true;
    elDelete.disabled = true;
    return false;
  };
  /**
   * Delete button
   *
   * @param   {Element} that      The button being clicked
   * @param   {String}  storeID  CSS ID for the element storing the configuration in its data
   *                              properties
   */
  // eslint-disable-next-line no-unused-vars


  Joomla.plgSystemWebauthnDelete = function (that, storeID) {
    // Extract the configuration from the store
    var elStore = document.getElementById(storeID);

    if (!elStore) {
      return false;
    }

    var postURL = atob(elStore.dataset.postback_url); // Find the UI elements

    var elTR = that.parentElement.parentElement;
    var credentialId = elTR.dataset.credential_id;
    var elTDs = elTR.querySelectorAll('td');
    var elButtonsTD = elTDs[1];
    var elButtons = elButtonsTD.querySelectorAll('button');
    var elEdit = elButtons[0];
    var elDelete = elButtons[1];
    elEdit.disabled = true;
    elDelete.disabled = true; // Delete the record

    var postBackData = {
      option: 'com_ajax',
      group: 'system',
      plugin: 'webauthn',
      format: 'json',
      encoding: 'json',
      akaction: 'delete',
      credential_id: credentialId
    };
    Joomla.request({
      url: postURL,
      method: 'POST',
      data: interpolateParameters(postBackData),
      onSuccess: function onSuccess(rawResponse) {
        var result = false;

        try {
          result = JSON.parse(rawResponse);
        } catch (e) {
          result = rawResponse === 'true';
        }

        if (result !== true) {
          handleCreationError(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_NOT_DELETED'));
          return;
        }

        elTR.parentElement.removeChild(elTR);
      },
      onError: function onError(xhr) {
        elEdit.disabled = false;
        elDelete.disabled = false;
        handleCreationError("".concat(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_NOT_DELETED'), " -- ").concat(xhr.status, " ").concat(xhr.statusText));
      }
    });
    return false;
  };

  document.addEventListener('DOMContentLoaded', function () {
    var addButton = document.getElementById('plg_system_webauthn-manage-add');

    if (addButton) {
      addButton.addEventListener('click', function (_ref) {
        var currentTarget = _ref.currentTarget;
        Joomla.plgSystemWebauthnCreateCredentials(currentTarget.getAttribute('data-random-id'), '#plg_system_webauthn-management-interface');
      });
    }

    var editLabelButtons = [].slice.call(document.querySelectorAll('.plg_system_webauthn-manage-edit'));

    if (editLabelButtons.length) {
      editLabelButtons.forEach(function (button) {
        button.addEventListener('click', function (_ref2) {
          var currentTarget = _ref2.currentTarget;
          Joomla.plgSystemWebauthnEditLabel(currentTarget, currentTarget.getAttribute('data-random-id'));
        });
      });
    }

    var deleteButtons = [].slice.call(document.querySelectorAll('.plg_system_webauthn-manage-delete'));

    if (deleteButtons.length) {
      deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (_ref3) {
          var currentTarget = _ref3.currentTarget;
          Joomla.plgSystemWebauthnDelete(currentTarget, currentTarget.getAttribute('data-random-id'));
        });
      });
    }
  });
})(window, Joomla);