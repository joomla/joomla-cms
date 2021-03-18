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
   * Finds the first field matching a selector inside a form
   *
   * @param   {HTMLFormElement}  form           The FORM element
   * @param   {String}           fieldSelector  The CSS selector to locate the field
   *
   * @returns {Element|null}  NULL when no element is found
   */


  var findField = function findField(form, fieldSelector) {
    var elInputs = form.querySelectorAll(fieldSelector);

    if (!elInputs.length) {
      return null;
    }

    return elInputs[0];
  };
  /**
   * Find a form field described by the CSS selector fieldSelector.
   * The field must be inside a <form> element which is either the
   * outerElement itself or enclosed by outerElement.
   *
   * @param   {Element}  outerElement   The element which is either our form or contains our form.
   * @param   {String}   fieldSelector  The CSS selector to locate the field
   *
   * @returns {null|Element}  NULL when no element is found
   */


  var lookForField = function lookForField(outerElement, fieldSelector) {
    var elElement = outerElement.parentElement;
    var elInput = null;

    if (elElement.nodeName === 'FORM') {
      elInput = findField(elElement, fieldSelector);
      return elInput;
    }

    var elForms = elElement.querySelectorAll('form');

    if (elForms.length) {
      for (var i = 0; i < elForms.length; i += 1) {
        elInput = findField(elForms[i], fieldSelector);

        if (elInput !== null) {
          return elInput;
        }
      }
    }

    return null;
  };
  /**
   * A simple error handler.
   *
   * @param   {String}  message
   */


  var handleLoginError = function handleLoginError(message) {
    Joomla.renderMessages({
      error: [message]
    });
  };
  /**
   * Handles the browser response for the user interaction with the authenticator. Redirects to an
   * internal page which handles the login server-side.
   *
   * @param {  Object}  publicKey     Public key request options, returned from the server
   * @param   {String}  callbackUrl  The URL we will use to post back to the server. Must include
   *   the anti-CSRF token.
   */


  var handleLoginChallenge = function handleLoginChallenge(publicKey, callbackUrl) {
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
    };

    if (!publicKey.challenge) {
      handleLoginError(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_INVALID_USERNAME'));
      return;
    }

    publicKey.challenge = Uint8Array.from(window.atob(base64url2base64(publicKey.challenge)), function (c) {
      return c.charCodeAt(0);
    });

    if (publicKey.allowCredentials) {
      publicKey.allowCredentials = publicKey.allowCredentials.map(function (data) {
        data.id = Uint8Array.from(window.atob(base64url2base64(data.id)), function (c) {
          return c.charCodeAt(0);
        });
        return data;
      });
    }

    navigator.credentials.get({
      publicKey: publicKey
    }).then(function (data) {
      var publicKeyCredential = {
        id: data.id,
        type: data.type,
        rawId: arrayToBase64String(new Uint8Array(data.rawId)),
        response: {
          authenticatorData: arrayToBase64String(new Uint8Array(data.response.authenticatorData)),
          clientDataJSON: arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
          signature: arrayToBase64String(new Uint8Array(data.response.signature)),
          userHandle: data.response.userHandle ? arrayToBase64String(new Uint8Array(data.response.userHandle)) : null
        }
      }; // Send the response to your server

      window.location = "".concat(callbackUrl, "&option=com_ajax&group=system&plugin=webauthn&") + "format=raw&akaction=login&encoding=redirect&data=".concat(btoa(JSON.stringify(publicKeyCredential)));
    }).catch(function (error) {
      // Example: timeout, interaction refused...
      handleLoginError(error);
    });
  };
  /**
   * Initialize the passwordless login, going through the server to get the registered certificates
   * for the user.
   *
   * @param   {string}   formId       The login form's or login module's HTML ID
   * @param   {string}   callbackUrl  The URL we will use to post back to the server. Must include
   *   the anti-CSRF token.
   *
   * @returns {boolean}  Always FALSE to prevent BUTTON elements from reloading the page.
   */
  // eslint-disable-next-line no-unused-vars


  Joomla.plgSystemWebauthnLogin = function (formId, callbackUrl) {
    // Get the username
    var elFormContainer = document.getElementById(formId);
    var elUsername = lookForField(elFormContainer, 'input[name=username]');
    var elReturn = lookForField(elFormContainer, 'input[name=return]');

    if (elUsername === null) {
      Joomla.renderMessages({
        error: [Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_CANNOT_FIND_USERNAME')]
      });
      return false;
    }

    var username = elUsername.value;
    var returnUrl = elReturn ? elReturn.value : null; // No username? We cannot proceed. We need a username to find the acceptable public keys :(

    if (username === '') {
      Joomla.renderMessages({
        error: [Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_EMPTY_USERNAME')]
      });
      return false;
    } // Get the Public Key Credential Request Options (challenge and acceptable public keys)


    var postBackData = {
      option: 'com_ajax',
      group: 'system',
      plugin: 'webauthn',
      format: 'raw',
      akaction: 'challenge',
      encoding: 'raw',
      username: username,
      returnUrl: returnUrl
    };
    Joomla.request({
      url: callbackUrl,
      method: 'POST',
      data: interpolateParameters(postBackData),
      onSuccess: function onSuccess(rawResponse) {
        var jsonData = {};

        try {
          jsonData = JSON.parse(rawResponse);
        } catch (e) {
          /**
           * In case of JSON decoding failure fall through; the error will be handled in the login
           * challenge handler called below.
           */
        }

        handleLoginChallenge(jsonData, callbackUrl);
      },
      onError: function onError(xhr) {
        handleLoginError("".concat(xhr.status, " ").concat(xhr.statusText));
      }
    });
    return false;
  };

  document.addEventListener('DOMContentLoaded', function () {
    var loginButtons = [].slice.call(document.querySelectorAll('.plg_system_webauthn_login_button'));

    if (loginButtons.length) {
      loginButtons.forEach(function (button) {
        button.addEventListener('click', function (_ref) {
          var currentTarget = _ref.currentTarget;
          Joomla.plgSystemWebauthnLogin(currentTarget.getAttribute('data-random-form'), currentTarget.getAttribute('data-random-url'));
        });
      });
    }
  });
})(window, Joomla);