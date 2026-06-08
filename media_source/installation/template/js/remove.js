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
    document.getElementById('automatedUpdates')?.classList?.remove('active');
    document.getElementById('installCongrat').classList.remove('active');
    document.getElementById('installFinal').classList.remove('active');
    document.getElementById('installRecommended').classList.remove('active');
  })
}

if (document.getElementById('skipLanguages')) {
	document.getElementById('skipLanguages').addEventListener('click', function(e) {
		e.preventDefault();
		document.getElementById('automatedUpdates')?.classList?.add('active');
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

if (document.getElementById('automatedUpdatesDisableButton')) {
  document.getElementById('automatedUpdatesDisableButton')
    .addEventListener('click', function (e) {
      e.preventDefault();
      let confirm = window.confirm(Joomla.Text._('INSTL_DISABLE_AUTOUPDATE'));
      if (confirm) {
        Joomla.disableAutomatedUpdates();
      }
    });
}

const completeInstallationOptions = document.querySelectorAll('.complete-installation');

completeInstallationOptions.forEach(function(item) {
    item.addEventListener('click', function (e) {
        // In development mode we show the user a pretty button to allow them to choose whether to delete the installation
        // directory or not. In this case or when the installation folder has been deleted or might be partly deleted,
        // the buttons just redirect to the admin or site.
        // In stable release we always try to delete the folder at the first click. Maximum extermination!
        if ('development' in item.dataset || 'installremoved' in item.dataset) {
            window.location.href = item.dataset.href;
        } else {
            Joomla.deleteJoomlaInstallationDirectory(item.dataset.href);
        }

        return false;
    });
});


Joomla.disableAutomatedUpdates = function () {
  Joomla.request({
    method: "POST",
    url: Joomla.installationBaseUrl + '?task=installation.disableAutomatedUpdates&format=json',
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
          Joomla.renderMessages({error:['Unknown error disabling the automated updates.']});
        }
      } else {
        const automatedUpdates = document.getElementById('automatedUpdates');
        automatedUpdates.parentNode.removeChild(automatedUpdates);
      }
    },
    onError: function (xhr) {
      Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
    }
  });
}

Joomla.deleteJoomlaInstallationDirectory = function (redirectUrl) {
    Joomla.request({
        method: "POST",
        url: Joomla.installationBaseUrl + '?task=installation.removeFolder&format=json',
        perform: true,
        token: true,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        onSuccess: function (response) {
            // If the installation folder has been deleted at least partly, i.e. also
            // in case of a failure, we cannot use it anymore.
            // Therefore set a marker in the admin and site buttons so they still work
            // and disable buttons which will not work anymore.
            completeInstallationOptions.forEach(function(item) {
                item.dataset.installremoved = 'true';
            });
            if (document.getElementById('installAddFeatures')) {
                document.getElementById('installAddFeatures').disabled = true;
            }
            if (document.getElementById('automatedUpdatesDisableButton')) {
                document.getElementById('automatedUpdatesDisableButton').disabled = true;
            }
            if (document.getElementById('removeInstallationFolder')) {
                document.getElementById('removeInstallationFolder').disabled = true;
            }
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

                const automatedUpdates = document.getElementById('automatedUpdates');

                // This will only exist if it has not been removed with a previous step
                if (automatedUpdates) {
                    automatedUpdates.parentNode.removeChild(automatedUpdates);
                }

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
      Joomla.removeMessages();
      document.body.appendChild(document.createElement('joomla-core-loader'));

			// Install the extra languages
      try {
        Joomla.install(['languages'], form, true);
      } catch (err) {
        const loader = document.querySelector('joomla-core-loader');
        if (loader) {
          loader.remove();
        }
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
