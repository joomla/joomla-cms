/**
 * @package     Joomla.Installation
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Init on dom content loaded event
var url = Joomla.getOptions('system.installation').url ? Joomla.getOptions('system.installation').url.replace(/&amp;/g, '&') : 'index.php';

if (document.getElementById('installAddFeatures')) {
  document.getElementById('installAddFeatures').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('installLanguages').classList.add('active');
    document.getElementById('installCongrat').classList.remove('active');
    document.getElementById('installFinal').classList.remove('active');
    document.getElementById('installRecommended').classList.remove('active');
  })
}

if (document.getElementById('skipLanguages')) {
	document.getElementById('skipLanguages').addEventListener('click', function(e) {
		e.preventDefault();
		document.getElementById('installCongrat').classList.add('active');
		document.getElementById('installFinal').classList.add('active');
		document.getElementById('installRecommended').classList.add('active');
		document.getElementById('installLanguages').classList.remove('active');

		if (document.getElementById('installFinal')) {
			document.getElementById('installFinal').focus();
		}
	})
}

if (document.getElementById('removeInstallationFolder')) {
	document.getElementById('removeInstallationFolder')
		.addEventListener('click', function (e) {
			e.preventDefault();
			let confirm = window.confirm(Joomla.Text._('INSTL_REMOVE_INST_FOLDER').replace('%s', 'installation'));
			if (confirm) {
			    Joomla.deleteJoomlaInstallationDirectory();
			}
		});
}

const completeInstallationOptions = document.querySelectorAll('.complete-installation');

completeInstallationOptions.forEach(function(item) {
    item.addEventListener('click', function (e) {
        // Once a button is clicked ensure they can't click it again...
        completeInstallationOptions.forEach(function(nestedItem) {
            nestedItem.disabled = true;
        });

        // In development mode we show the user a pretty button to allow them to choose whether to delete the installation
        // directory or not. In stable release we always delete the folder. Maximum extermination!
        if ('development' in item.dataset) {
            window.location.href = item.dataset.href;
        } else {
            Joomla.deleteJoomlaInstallationDirectory(item.dataset.href);
        }

        return false;
    });
});

Joomla.deleteJoomlaInstallationDirectory = function (redirectUrl) {
    Joomla.request({
        method: "POST",
        url: Joomla.installationBaseUrl + '?task=installation.removeFolder&format=json',
        perform: true,
        token: true,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        onSuccess: function (response) {
            const successresponse = JSON.parse(response);
            if (successresponse.error === true) {
                if (successresponse.messages) {
                    Joomla.renderMessages(successresponse.messages);
                    Joomla.loadOptions({'csrf.token': successresponse.token});
                } else {
                    // Stuff went wrong. No error messages. Just panic bail!
                    Joomla.renderMessages({error:['Unknown error deleting the installation folder.']});
                }
            } else {
                const customInstallation = document.getElementById('customInstallation');
                customInstallation.parentNode.removeChild(customInstallation);
                const removeInstallationTab = document.getElementById('removeInstallationTab');

                // This will only exist in debug mode
                if (removeInstallationTab) {
                    removeInstallationTab.parentNode.removeChild(removeInstallationTab);
                }

                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }
        },
        onError: function (xhr) {
          Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
        }
    });
}

if (document.getElementById('installLanguagesButton')) {
	document.getElementById('installLanguagesButton').addEventListener('click', function(e) {
		e.preventDefault();
		var form = document.getElementById('languagesForm');
		if (form) {
			// Install the extra languages
			if (Joomla.install(['languages'], form)) {
				document.getElementById('installLanguages').classList.remove('active');
				document.getElementById('installFinal').classList.add('active');
			}
		}
	})
}

if (document.getElementById('defaultLanguagesButton')) {
  document.getElementById('defaultLanguagesButton')
    .addEventListener('click', (e) => {
      let frontendlang = 'en-GB';
      if (document.querySelector('input[name="frontendlang"]:checked')) {
        frontendlang = document.querySelector('input[name="frontendlang"]:checked').value;
      }

      let administratorlang = 'en-GB';
      if (document.querySelector('input[name="administratorlang"]:checked')) {
        administratorlang = document.querySelector('input[name="administratorlang"]:checked').value;
      }

      e.preventDefault();

      Joomla.request({
        method: 'POST',
        url: `${Joomla.installationBaseUrl}?view=setup&frontendlang=${frontendlang}&administratorlang=${administratorlang}&task=language.setdefault&format=json`,
        perform: true,
        token: true,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        onSuccess(response) {
          const successresponse = JSON.parse(response);
          if (successresponse.messages) {
            Joomla.renderMessages(successresponse.messages, '#system-message-container');
          }
          Joomla.loadOptions({'csrf.token': successresponse.token});
        },
        onError(xhr) {
          Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
        },
      });

      if (document.getElementById('header')) {
        document.getElementById('header').scrollIntoView();
      }
    });
}
