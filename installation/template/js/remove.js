/**
 * @package     Joomla.Installation
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters. All rights reserved.
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
		document.getElementById('installFinal').classList.add('active');
		document.getElementById('installRecommended').classList.add('active');
		document.getElementById('installLanguages').classList.remove('active');
	})
}

if (document.getElementById('removeInstallationFolder')) {
	document.getElementById('removeInstallationFolder')
		.addEventListener('click', function (e) {
			e.preventDefault();
			let confirm = window.confirm(Joomla.Text._('INSTL_REMOVE_INST_FOLDER').replace('%s', 'installation'));
			if (confirm) {
				Joomla.request({
					method: "POST",
					url: Joomla.installationBaseUrl + '?task=installation.removeFolder&format=json',
					perform: true,
					token: true,
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					onSuccess: function () {
						const customInstallation = document.getElementById('customInstallation');
						customInstallation.parentNode.removeChild(customInstallation);
						const removeInstallationTab = document.getElementById('removeInstallationTab');
						removeInstallationTab.parentNode.removeChild(removeInstallationTab);
					},
					onError: function (xhr) {
            Joomla.renderMessages({ error: [xhr] }, '#system-message-container');
					}
					}
				);
			}
		}
		);
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
        },
        onError(xhr) {
          Joomla.renderMessages({ error: [xhr] }, '#system-message-container');
        },
      });

      if (document.getElementById('header')) {
        document.getElementById('header').scrollIntoView();
      }
    });
}
