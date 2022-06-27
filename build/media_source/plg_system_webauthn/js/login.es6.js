/**
 * @package     Joomla.Plugin
 * @subpackage  System.webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

window.Joomla = window.Joomla || {};

((Joomla, document) => {
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
  const interpolateParameters = (object, prefix = '') => {
    let encodedString = '';

    Object.keys(object).forEach((prop) => {
      if (typeof object[prop] !== 'object') {
        if (encodedString.length > 0) {
          encodedString += '&';
        }

        if (prefix === '') {
          encodedString += `${encodeURIComponent(prop)}=${encodeURIComponent(object[prop])}`;
        } else {
          encodedString
            += `${encodeURIComponent(prefix)}[${encodeURIComponent(prop)}]=${encodeURIComponent(
              object[prop],
            )}`;
        }

        return;
      }

      // Objects need special handling
      encodedString += `${interpolateParameters(object[prop], prop)}`;
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
  const findField = (form, fieldSelector) => {
    const elInputs = form.querySelectorAll(fieldSelector);

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
  const lookForField = (outerElement, fieldSelector) => {
    let elInput = null;

    if (!outerElement) {
      return elInput;
    }

    const elElement = outerElement.parentElement;

    if (elElement.nodeName === 'FORM') {
      elInput = findField(elElement, fieldSelector);

      return elInput;
    }

    const elForms = elElement.querySelectorAll('form');

    if (elForms.length) {
      for (let i = 0; i < elForms.length; i += 1) {
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
  const handleLoginError = (message) => {
    Joomla.renderMessages({ error: [message] });
  };

  /**
   * Handles the browser response for the user interaction with the authenticator. Redirects to an
   * internal page which handles the login server-side.
   *
   * @param {  Object}  publicKey     Public key request options, returned from the server
   */
  const handleLoginChallenge = (publicKey) => {
    const arrayToBase64String = (a) => btoa(String.fromCharCode(...a));

    const base64url2base64 = (input) => {
      let output = input
        .replace(/-/g, '+')
        .replace(/_/g, '/');
      const pad = output.length % 4;
      if (pad) {
        if (pad === 1) {
          throw new Error('InvalidLengthError: Input base64url string is the wrong length to determine padding');
        }
        output += new Array(5 - pad).join('=');
      }
      return output;
    };

    if (!publicKey.challenge) {
      handleLoginError(Joomla.Text._('PLG_SYSTEM_WEBAUTHN_ERR_INVALID_USERNAME'));

      return;
    }

    publicKey.challenge = Uint8Array.from(window.atob(base64url2base64(publicKey.challenge)), (c) => c.charCodeAt(0));

    if (publicKey.allowCredentials) {
      publicKey.allowCredentials = publicKey.allowCredentials.map((data) => {
        data.id = Uint8Array.from(window.atob(base64url2base64(data.id)), (c) => c.charCodeAt(0));
        return data;
      });
    }

    navigator.credentials.get({ publicKey })
      .then((data) => {
        const publicKeyCredential = {
          id: data.id,
          type: data.type,
          rawId: arrayToBase64String(new Uint8Array(data.rawId)),
          response: {
            authenticatorData: arrayToBase64String(new Uint8Array(data.response.authenticatorData)),
            clientDataJSON: arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
            signature: arrayToBase64String(new Uint8Array(data.response.signature)),
            userHandle: data.response.userHandle ? arrayToBase64String(
              new Uint8Array(data.response.userHandle),
            ) : null,
          },
        };

        // Send the response to your server
        const paths = Joomla.getOptions('system.paths');
        window.location = `${paths ? `${paths.base}/index.php` : window.location.pathname}?${Joomla.getOptions('csrf.token')}=1&option=com_ajax&group=system&plugin=webauthn&`
          + `format=raw&akaction=login&encoding=redirect&data=${
            btoa(JSON.stringify(publicKeyCredential))}`;
      })
      .catch((error) => {
        // Example: timeout, interaction refused...
        handleLoginError(error);
      });
  };

  /**
   * Initialize the passwordless login, going through the server to get the registered certificates
   * for the user.
   *
   * @param   {string}   formId       The login form's or login module's HTML ID
   *
   * @returns {boolean}  Always FALSE to prevent BUTTON elements from reloading the page.
   */
  // eslint-disable-next-line no-unused-vars
  Joomla.plgSystemWebauthnLogin = (formId) => {
    // Get the username
    const elFormContainer = document.getElementById(formId);
    const elUsername = lookForField(elFormContainer, 'input[name=username]');
    const elReturn = lookForField(elFormContainer, 'input[name=return]');

    if (elUsername === null) {
      Joomla.renderMessages({ error: [Joomla.Text._('PLG_SYSTEM_WEBAUTHN_ERR_CANNOT_FIND_USERNAME')] });

      return false;
    }

    const username = elUsername.value;
    const returnUrl = elReturn ? elReturn.value : null;

    // No username? We cannot proceed. We need a username to find the acceptable public keys :(
    if (username === '') {
      Joomla.renderMessages({ error: [Joomla.Text._('PLG_SYSTEM_WEBAUTHN_ERR_EMPTY_USERNAME')] });

      return false;
    }

    // Get the Public Key Credential Request Options (challenge and acceptable public keys)
    const postBackData = {
      option: 'com_ajax',
      group: 'system',
      plugin: 'webauthn',
      format: 'raw',
      akaction: 'challenge',
      encoding: 'raw',
      username,
      returnUrl,
    };
    postBackData[Joomla.getOptions('csrf.token')] = 1;

    const paths = Joomla.getOptions('system.paths');

    Joomla.request({
      url: `${paths ? `${paths.base}/index.php` : window.location.pathname}?${Joomla.getOptions(
        'csrf.token',
      )}=1`,
      method: 'POST',
      data: interpolateParameters(postBackData),
      onSuccess(rawResponse) {
        let jsonData = {};

        try {
          jsonData = JSON.parse(rawResponse);
        } catch (e) {
          /**
           * In case of JSON decoding failure fall through; the error will be handled in the login
           * challenge handler called below.
           */
        }

        handleLoginChallenge(jsonData);
      },
      onError: (xhr) => {
        handleLoginError(`${xhr.status} ${xhr.statusText}`);
      },
    });

    return false;
  };

  // Initialization. Runs on DOM content loaded since this script is always loaded deferred.
  const loginButtons = [].slice.call(document.querySelectorAll('.plg_system_webauthn_login_button'));
  if (loginButtons.length) {
    loginButtons.forEach((button) => {
      button.addEventListener('click', ({ currentTarget }) => {
        Joomla.plgSystemWebauthnLogin(currentTarget.getAttribute('data-webauthn-form'));
      });
    });
  }
})(Joomla, document);
