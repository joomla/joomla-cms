/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Ask the user to link an authenticator using the provided public key (created server-side). Posts the credentials to
 * the URL defined in post_url using AJAX. That URL must re-render the management interface. These contents will replace
 * the element identified by the interface_selector CSS selector.
 *
 * @param   {String}  store_id            CSS ID for the element storing the configuration in its data properties
 * @param   {String}  interface_selector  CSS selector for the GUI container
 */
function plg_system_webauthn_create_credentials(store_id, interface_selector)
{
    // Make sure the browser supports Webauthn
    if (!('credentials' in navigator)) {
        alert(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_NO_BROWSER_SUPPORT'))

        console.log("This browser does not support Webauthn");
        return;
    }

    // Extract the configuration from the store
    let elStore = document.getElementById(store_id);

    if (!elStore)
    {
        return;
    }

    let publicKey = JSON.parse(atob(elStore.dataset.public_key));
    let post_url  = atob(elStore.dataset.postback_url);

    // Utility function to convert array data to base64 strings
    function arrayToBase64String(a)
    {
        return btoa(String.fromCharCode(...a));
    }

    // Convert the public key infomration to a format usable by the browser's credentials managemer
    publicKey.challenge = Uint8Array.from(window.atob(publicKey.challenge), c => c.charCodeAt(0));
    publicKey.user.id   = Uint8Array.from(window.atob(publicKey.user.id), c => c.charCodeAt(0));

    if (publicKey.excludeCredentials)
    {
        publicKey.excludeCredentials = publicKey.excludeCredentials.map(function (data) {
            return {
                ...data,
                "id": Uint8Array.from(window.atob(data.id), c => c.charCodeAt(0))
            };
        });
    }

    // Ask the browser to prompt the user for their authenticator
    navigator.credentials.create({publicKey})
        .then(function (data) {
            let publicKeyCredential = {
                id:       data.id,
                type:     data.type,
                rawId:    arrayToBase64String(new Uint8Array(data.rawId)),
                response: {
                    clientDataJSON:    arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
                    attestationObject: arrayToBase64String(new Uint8Array(data.response.attestationObject))
                }
            };

            let postBackData = {
                "option":   "com_ajax",
                "group":    "system",
                "plugin":   "webauthn",
                "format":   "raw",
                "akaction": "create",
                "encoding": "raw",
                "data":     btoa(JSON.stringify(publicKeyCredential))
            };

            window.jQuery.post(post_url, postBackData)
                .done(function (responseHTML) {
                    let elements = document.querySelectorAll(interface_selector);

                    if (!elements)
                    {
                        return;
                    }

                    let elContainer = elements[0];

                    elContainer.outerHTML = responseHTML;
                })
                .fail(function (data) {
                    plg_system_webauthn_handle_creation_error(data.status + ' ' + data.statusText);
                });


        }, function (error) {
            // An error occurred: timeout, request to provide the authenticator refused, hardware / software error...
            plg_system_webauthn_handle_creation_error(error);
        });
}

/**
 * A simple error handler
 *
 * @param   {String}  message
 */
function plg_system_webauthn_handle_creation_error(message)
{
    alert(message);

    console.log(message);
}

/**
 * Edit label button
 *
 * @param   {Element} that      The button being clicked
 * @param   {String}  store_id  CSS ID for the element storing the configuration in its data properties
 */
function plg_system_webauthn_edit_label(that, store_id)
{
    // Extract the configuration from the store
    let elStore = document.getElementById(store_id);

    if (!elStore)
    {
        return;
    }

    let post_url = atob(elStore.dataset.postback_url);

    // Find the UI elements
    let elTR         = that.parentElement.parentElement;
    let credentialId = elTR.dataset.credential_id;
    let elTDs        = elTR.querySelectorAll("td");
    let elLabelTD    = elTDs[0];
    let elButtonsTD  = elTDs[1];
    let elButtons    = elButtonsTD.querySelectorAll("button");
    let elEdit       = elButtons[0];
    let elDelete     = elButtons[1];

    // Show the editor
    let oldLabel = elLabelTD.innerText;

    let elInput          = document.createElement("input");
    elInput.type         = "text";
    elInput.name         = "label";
    elInput.defaultValue = oldLabel;

    let elSave       = document.createElement("button");
    elSave.className = "btn btn-success btn-sm";
    elSave.innerText = Joomla.JText._("PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_SAVE_LABEL");
    elSave.addEventListener("click", function (e) {
        let elNewLabel = elInput.value;

        if (elNewLabel !== '')
        {
            let postBackData = {
                "option": "com_ajax",
                "group": "system",
                "plugin": "webauthn",
                "format": "json",
                "encoding": "json",
                "akaction": "savelabel",
                "credential_id": credentialId,
                "new_label": elNewLabel
            };

            window.jQuery.post(post_url, postBackData)
                .done(function (result) {
                    if ((result !== true) && (result !== "true"))
                    {
                        plg_system_webauthn_handle_creation_error(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_LABEL_NOT_SAVED'));

                        return;
                    }

                    //alert(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_MSG_SAVED_LABEL'));
                })
                .fail(function (data) {
                    plg_system_webauthn_handle_creation_error(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_LABEL_NOT_SAVED') + ' -- ' + data.status + ' ' + data.statusText);
                });
        }

        elLabelTD.innerText = elNewLabel;
        elEdit.disabled     = false;
        elDelete.disabled   = false;

        return false;
    }, false);

    let elCancel       = document.createElement("button");
    elCancel.className = "btn btn-danger btn-sm";
    elCancel.innerText = Joomla.JText._("PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_CANCEL_LABEL");
    elCancel.addEventListener("click", function (e) {
        elLabelTD.innerText = oldLabel;
        elEdit.disabled     = false;
        elDelete.disabled   = false;

        return false;
    }, false);

    elLabelTD.innerHTML = "";
    elLabelTD.appendChild(elInput);
    elLabelTD.appendChild(elSave);
    elLabelTD.appendChild(elCancel);
    elEdit.disabled   = true;
    elDelete.disabled = true;

    return false;
}

/**
 * Delete button
 *
 * @param   {Element} that      The button being clicked
 * @param   {String}  store_id  CSS ID for the element storing the configuration in its data properties
 */
function plg_system_webauthn_delete(that, store_id)
{
    // Extract the configuration from the store
    let elStore = document.getElementById(store_id);

    if (!elStore)
    {
        return;
    }

    let post_url = atob(elStore.dataset.postback_url);

    // Find the UI elements
    let elTR         = that.parentElement.parentElement;
    let credentialId = elTR.dataset.credential_id;
    let elTDs        = elTR.querySelectorAll("td");
    let elButtonsTD  = elTDs[1];
    let elButtons    = elButtonsTD.querySelectorAll("button");
    let elEdit       = elButtons[0];
    let elDelete     = elButtons[1];

    elEdit.disabled     = true;
    elDelete.disabled   = true;

    // Delete the record
    let postBackData = {
        "option": "com_ajax",
        "group": "system",
        "plugin": "webauthn",
        "format": "json",
        "encoding": "json",
        "akaction": "delete",
        "credential_id": credentialId,
    };

    window.jQuery.post(post_url, postBackData)
        .done(function (result) {
            if ((result !== true) && (result !== "true"))
            {
                plg_system_webauthn_handle_creation_error(Joomla.JText._("PLG_SYSTEM_WEBAUTHN_ERR_NOT_DELETED"));

                return;
            }

            elTR.parentElement.removeChild(elTR);

            //alert(Joomla.JText._("PLG_SYSTEM_WEBAUTHN_MSG_DELETED"));
        })
        .fail(function (data) {
            elEdit.disabled     = false;
            elDelete.disabled   = false;

            plg_system_webauthn_handle_creation_error(Joomla.JText._("PLG_SYSTEM_WEBAUTHN_ERR_NOT_DELETED") + " -- " + data.status + " " + data.statusText);
        });

    return false;
}
