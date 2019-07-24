/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Finds the first field matching a selector inside a form
 *
 * @param   {HTMLFormElement}  elForm         The FORM element
 * @param   {String}           fieldSelector  The CSS selector to locate the field
 *
 * @returns {Element|null}  NULL when no element is found
 */
function plg_system_webauthn_findField(elForm, fieldSelector)
{
    let elInputs = elForm.querySelectorAll(fieldSelector);

    if (!elInputs.length)
    {
        return null;
    }

    return elInputs[0];
}

/**
 * Find a form field described the CSS selector fieldSelector. The field must be inside a <form> element which is either
 * the outerElement itself or enclosed by outerElement.
 *
 * @param   {Element}  outerElement   The element which is either our form or contains our form.
 * @param   {String}   fieldSelector  The CSS selector to locate the field
 *
 * @returns {null|Element}  NULL when no element is found
 */
function plg_system_webauthn_lookForField(outerElement, fieldSelector)
{
    var elElement = outerElement.parentElement;
    var elInput   = null;

	if (elElement.nodeName === "FORM")
	{
		elInput = plg_system_webauthn_findField(elElement, fieldSelector);

		return elInput;
	}

	var elForms = elElement.querySelectorAll("form");

	if (elForms.length)
	{
		for (var i = 0; i < elForms.length; i++)
		{
			elInput = plg_system_webauthn_findField(elForms[i], fieldSelector);

			if (elInput !== null)
			{
				return elInput;
			}
		}
	}

    return null;
}

/**
 * Initialize the passwordless login, going through the server to get the registered certificates for the user.
 *
 * @param   {string}   form_id       The login form's or login module's HTML ID
 * @param   {string}   callback_url  The URL we will use to post back to the server. Must include the anti-CSRF token.
 *
 * @returns {boolean}  Always FALSE to prevent BUTTON elements from reloading the page.
 */
function plg_system_webauthn_login(form_id, callback_url)
{
	// Get the username
	let elFormContainer = document.getElementById(form_id);
	let elUsername      = plg_system_webauthn_lookForField(elFormContainer, "input[name=username]");
	let elReturn        = plg_system_webauthn_lookForField(elFormContainer, "input[name=return]");

	if (elUsername === null)
	{
		alert(Joomla.JText._("PLG_SYSTEM_WEBAUTHN_ERR_CANNOT_FIND_USERNAME"));

		return false;
	}

	let username  = elUsername.value;
	let returnUrl = elReturn ? elReturn.value : null;

	// No username? We cannot proceed. We need a username to find the acceptable public keys :(
	if (username === "")
	{
		alert(Joomla.JText._("PLG_SYSTEM_WEBAUTHN_ERR_EMPTY_USERNAME"));

		return false;
	}

	// Get the Public Key Credential Request Options (challenge and acceptable public keys)
	let postBackData = {
		"option":    "com_ajax",
		"group":     "system",
		"plugin":    "webauthn",
		"format":    "raw",
		"akaction":  "challenge",
		"encoding":  "raw",
		"username":  username,
		"returnUrl": returnUrl,
	};

	Joomla.request({
		url: callback_url,
		method: 'POST',
		data: plg_system_webauthn_interpolate_parameters(postBackData),
		onSuccess(rawResponse) {
			let jsonData = {};

			try
			{
				jsonData = JSON.parse(rawResponse);
			}
			catch (e)
			{
				/**
				 * In case of JSON decoding failure fall through; the error will be handled in the login challenge
				 * handler called below.
				 */
			}

			plg_system_webauthn_handle_login_challenge(jsonData, callback_url);
		},
		onError: (xhr) => {
			plg_system_webauthn_handle_login_error(xhr.status + " " + xhr.statusText);
		}
	});

	return false;
}

/**
 * Handles the browser response for the user interaction with the authenticator. Redirects to an internal page which
 * handles the login server-side.
 *
 * @param {  Object}  publicKey     Public key request options, returned from the server
 * @param   {String}  callback_url  The URL we will use to post back to the server. Must include the anti-CSRF token.
 */
function plg_system_webauthn_handle_login_challenge(publicKey, callback_url)
{
    function arrayToBase64String(a)
    {
        return btoa(String.fromCharCode(...a));
    }

    if (!publicKey.challenge)
    {
        plg_system_webauthn_handle_login_error(Joomla.JText._('PLG_SYSTEM_WEBAUTHN_ERR_INVALID_USERNAME'));

        return;
    }

    publicKey.challenge        = Uint8Array.from(window.atob(publicKey.challenge), c => c.charCodeAt(0));
    publicKey.allowCredentials = publicKey.allowCredentials.map(function (data) {
        return {
            ...data,
            "id": Uint8Array.from(atob(data.id), c => c.charCodeAt(0))
        };
    });

    navigator.credentials.get({publicKey})
        .then(data => {
            let publicKeyCredential = {
                id:       data.id,
                type:     data.type,
                rawId:    arrayToBase64String(new Uint8Array(data.rawId)),
                response: {
                    authenticatorData: arrayToBase64String(new Uint8Array(data.response.authenticatorData)),
                    clientDataJSON:    arrayToBase64String(new Uint8Array(data.response.clientDataJSON)),
                    signature:         arrayToBase64String(new Uint8Array(data.response.signature)),
                    userHandle:        data.response.userHandle ? arrayToBase64String(
                        new Uint8Array(data.response.userHandle)) : null
                }
            };

            window.location = callback_url + '&option=com_ajax&group=system&plugin=webauthn&format=raw&akaction=login&encoding=redirect&data=' +
                btoa(JSON.stringify(publicKeyCredential));

        }, error => {
            // Example: timeout, interaction refused...
            console.log(error);
            plg_system_webauthn_handle_login_error(error);
        });
}

/**
 * A simple error handler.
 *
 * @param   {String}  message
 */
function plg_system_webauthn_handle_login_error(message)
{
    alert(message);

    console.log(message);
}

/**
 * Converts a simple object containing query string parameters to a single, escaped query string. This method is a
 * necessary evil since Joomla.request can only accept data as a string.
 *
 * @param    object   {object}  A plain object containing the query parameters to pass
 * @param    prefix   {string}  Prefix for array-type parameters
 *
 * @returns  {string}
 */
function plg_system_webauthn_interpolate_parameters(object, prefix)
{
	prefix            = prefix || "";
	var encodedString = "";

	for (var prop in object)
	{
		if (object.hasOwnProperty(prop))
		{
			if (encodedString.length > 0)
			{
				encodedString += "&";
			}

			if (typeof object[prop] !== "object")
			{
				if (prefix === "")
				{
					encodedString += encodeURIComponent(prop) + "=" + encodeURIComponent(object[prop]);
				}
				else
				{
					encodedString +=
						encodeURIComponent(prefix) + "[" + encodeURIComponent(prop) + "]=" + encodeURIComponent(
						object[prop]);
				}

				continue;
			}

			// Objects need special handling
			encodedString += plg_system_webauthn_interpolate_parameters(object[prop], prop);
		}
	}
	return encodedString;
}
