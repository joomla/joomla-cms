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
   * A simple error handler
   *
   * @param   {String}  message
   */
  const handleCreationError = (message) => {
    Joomla.renderMessages({ error: [message] });
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
  Joomla.plgSystemWebauthnCreateCredentials = (storeID, interfaceSelector) => {
    // Make sure the browser supports Webauthn
    if (!('credentials' in navigator)) {
      Joomla.renderMessages({ error: [Joomla.Text._('PLG_SYSTEM_WEBAUTHN_ERR_NO_BROWSER_SUPPORT')] });

      return;
    }

    // Extract the configuration from the store
    const elStore = document.getElementById(storeID);

    if (!elStore) {
      return;
    }

    const publicKey = JSON.parse(atob(elStore.dataset.public_key));
    const postURL = atob(elStore.dataset.postback_url);

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

    // Convert the public key information to a format usable by the browser's credentials manager
    publicKey.challenge = Uint8Array.from(window.atob(base64url2base64(publicKey.challenge)), (c) => c.charCodeAt(0));

    publicKey.user.id = Uint8Array.from(window.atob(publicKey.user.id), (c) => c.charCodeAt(0));

    if (publicKey.excludeCredentials) {
      publicKey.excludeCredentials = publicKey.excludeCredentials.map((data) => {
        data.id = Uint8Array.from(window.atob(base64url2base64(data.id)), (c) => c.charCodeAt(0));
        return data;
      });
    }

    // Ask the browser to prompt the user for their authenticator
    navigator.credentials.create({ publicKey })
      .then((data) => {
        const publicKeyCredential = {
          id: data.id,
          type: data.type,
          rawId: arrayToBase64String(new Uint8Array(data.rawId)),
          response: {
            clientDataJSON: arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
            attestationObject: arrayToBase64String(new Uint8Array(data.response.attestationObject)),
          },
        };

        // Send the response to your server
        const postBackData = {
          option: 'com_ajax',
          group: 'system',
          plugin: 'webauthn',
          format: 'raw',
          akaction: 'create',
          encoding: 'raw',
          data: btoa(JSON.stringify(publicKeyCredential)),
        };

        Joomla.request({
          url: postURL,
          method: 'POST',
          data: interpolateParameters(postBackData),
          onSuccess(responseHTML) {
            const elements = document.querySelectorAll(interfaceSelector);

            if (!elements) {
              return;
            }

            const elContainer = elements[0];

            elContainer.outerHTML = responseHTML;

            Joomla.plgSystemWebauthnInitialize();
          },
          onError: (xhr) => {
            handleCreationError(`${xhr.status} ${xhr.statusText}`);
          },
        });
      })
      .catch((error) => {
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
  Joomla.plgSystemWebauthnEditLabel = (that, storeID) => {
    // Extract the configuration from the store
    const elStore = document.getElementById(storeID);

    if (!elStore) {
      return false;
    }

    const postURL = atob(elStore.dataset.postback_url);

    // Find the UI elements
    const elTR = that.parentElement.parentElement;
    const credentialId = elTR.dataset.credential_id;
    const elTDs = elTR.querySelectorAll('td');
    const elLabelTD = elTDs[0];
    const elButtonsTD = elTDs[1];
    const elButtons = elButtonsTD.querySelectorAll('button');
    const elEdit = elButtons[0];
    const elDelete = elButtons[1];

    // Show the editor
    const oldLabel = elLabelTD.innerText;

    const elInput = document.createElement('input');
    elInput.type = 'text';
    elInput.name = 'label';
    elInput.defaultValue = oldLabel;

    const elSave = document.createElement('button');
    elSave.className = 'btn btn-success btn-sm';
    elSave.innerText = Joomla.Text._('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_SAVE_LABEL');
    elSave.addEventListener('click', () => {
      const elNewLabel = elInput.value;

      if (elNewLabel !== '') {
        const postBackData = {
          option: 'com_ajax',
          group: 'system',
          plugin: 'webauthn',
          format: 'json',
          encoding: 'json',
          akaction: 'savelabel',
          credential_id: credentialId,
          new_label: elNewLabel,
        };

        Joomla.request({
          url: postURL,
          method: 'POST',
          data: interpolateParameters(postBackData),
          onSuccess(rawResponse) {
            let result = false;

            try {
              result = JSON.parse(rawResponse);
            } catch (exception) {
              result = (rawResponse === 'true');
            }

            if (result !== true) {
              handleCreationError(
                Joomla.Text._('PLG_SYSTEM_WEBAUTHN_ERR_LABEL_NOT_SAVED'),
              );
            }
          },
          onError: (xhr) => {
            handleCreationError(
              `${Joomla.Text._('PLG_SYSTEM_WEBAUTHN_ERR_LABEL_NOT_SAVED')
              } -- ${xhr.status} ${xhr.statusText}`,
            );
          },
        });
      }

      elLabelTD.innerText = elNewLabel;
      elEdit.disabled = false;
      elDelete.disabled = false;

      return false;
    }, false);

    const elCancel = document.createElement('button');
    elCancel.className = 'btn btn-danger btn-sm';
    elCancel.innerText = Joomla.Text._('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_CANCEL_LABEL');
    elCancel.addEventListener('click', () => {
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
  Joomla.plgSystemWebauthnDelete = (that, storeID) => {
    // Extract the configuration from the store
    const elStore = document.getElementById(storeID);

    if (!elStore) {
      return false;
    }

    const postURL = atob(elStore.dataset.postback_url);

    // Find the UI elements
    const elTR = that.parentElement.parentElement;
    const credentialId = elTR.dataset.credential_id;
    const elTDs = elTR.querySelectorAll('td');
    const elButtonsTD = elTDs[1];
    const elButtons = elButtonsTD.querySelectorAll('button');
    const elEdit = elButtons[0];
    const elDelete = elButtons[1];

    elEdit.disabled = true;
    elDelete.disabled = true;

    // Delete the record
    const postBackData = {
      option: 'com_ajax',
      group: 'system',
      plugin: 'webauthn',
      format: 'json',
      encoding: 'json',
      akaction: 'delete',
      credential_id: credentialId,
    };

    Joomla.request({
      url: postURL,
      method: 'POST',
      data: interpolateParameters(postBackData),
      onSuccess(rawResponse) {
        let result = false;

        try {
          result = JSON.parse(rawResponse);
        } catch (e) {
          result = (rawResponse === 'true');
        }

        if (result !== true) {
          handleCreationError(
            Joomla.Text._('PLG_SYSTEM_WEBAUTHN_ERR_NOT_DELETED'),
          );

          return;
        }

        elTR.parentElement.removeChild(elTR);
      },
      onError: (xhr) => {
        elEdit.disabled = false;
        elDelete.disabled = false;
        handleCreationError(
          `${Joomla.Text._('PLG_SYSTEM_WEBAUTHN_ERR_NOT_DELETED')
          } -- ${xhr.status} ${xhr.statusText}`,
        );
      },
    });

    return false;
  };

  /**
   * Add New Authenticator button click handler
   *
   * @param   {MouseEvent} event  The mouse click event
   *
   * @returns {boolean} Returns false to prevent the default browser button behavior
   */
  Joomla.plgSystemWebauthnAddOnClick = (event) => {
    event.preventDefault();

    Joomla.plgSystemWebauthnCreateCredentials(event.currentTarget.getAttribute('data-random-id'), '#plg_system_webauthn-management-interface');

    return false;
  };

  /**
   * Edit Name button click handler
   *
   * @param   {MouseEvent} event  The mouse click event
   *
   * @returns {boolean} Returns false to prevent the default browser button behavior
   */
  Joomla.plgSystemWebauthnEditOnClick = (event) => {
    event.preventDefault();

    Joomla.plgSystemWebauthnEditLabel(event.currentTarget, event.currentTarget.getAttribute('data-random-id'));

    return false;
  };

  /**
   * Remove button click handler
   *
   * @param   {MouseEvent} event  The mouse click event
   *
   * @returns {boolean} Returns false to prevent the default browser button behavior
   */
  Joomla.plgSystemWebauthnDeleteOnClick = (event) => {
    event.preventDefault();

    Joomla.plgSystemWebauthnDelete(event.currentTarget, event.currentTarget.getAttribute('data-random-id'));

    return false;
  };

  /**
   * Initialization on page load.
   */
  Joomla.plgSystemWebauthnInitialize = () => {
    const addButton = document.getElementById('plg_system_webauthn-manage-add');
    if (addButton) {
      addButton.addEventListener('click', Joomla.plgSystemWebauthnAddOnClick);
    }

    const editLabelButtons = [].slice.call(document.querySelectorAll('.plg_system_webauthn-manage-edit'));
    if (editLabelButtons.length) {
      editLabelButtons.forEach((button) => {
        button.addEventListener('click', Joomla.plgSystemWebauthnEditOnClick);
      });
    }

    const deleteButtons = [].slice.call(document.querySelectorAll('.plg_system_webauthn-manage-delete'));
    if (deleteButtons.length) {
      deleteButtons.forEach((button) => {
        button.addEventListener('click', Joomla.plgSystemWebauthnDeleteOnClick);
      });
    }
  };

  // Initialization. Runs on DOM content loaded since this script is always loaded deferred.
  Joomla.plgSystemWebauthnInitialize();
})(Joomla, document);
