/**
 * @package     Joomla.Installation
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Method to set the language for the installation UI via AJAX
 *
 * @return {Boolean}
 */
Joomla.setlanguage = function(form) {
  var data = Joomla.serialiseForm(form);
  Joomla.removeMessages();
  document.body.appendChild(document.createElement('joomla-core-loader'));

  Joomla.request({
    url: Joomla.baseUrl,
    method: 'POST',
    data: data,
    perform: true,
    onSuccess: function(response, xhr){
      response = JSON.parse(response);
      Joomla.replaceTokens(response.token);
      var loaderElement = document.querySelector('joomla-core-loader');

      if (response.messages) {
        Joomla.renderMessages(response.messages);
      }

      if (response.error) {
        loaderElement.parentNode.removeChild(loaderElement);
        Joomla.renderMessages({'error': [response.message]});
      } else {
        loaderElement.parentNode.removeChild(loaderElement);
        Joomla.goToPage(response.data.view, true);
      }
    },
    onError:   function(xhr){
      var loaderElement = document.querySelector('joomla-core-loader');
      loaderElement.parentNode.removeChild(loaderElement);
      try {
        var r = JSON.parse(xhr.responseText);
        Joomla.replaceTokens(r.token);
        alert(r.message);
      } catch (e) {}
    }
  });

  return false;
};

Joomla.checkInputs = function() {
  document.getElementById('jform_admin_password2').value = document.getElementById('jform_admin_password').value;

  var inputs = [].slice.call(document.querySelectorAll('input[type="password"], input[type="text"], input[type="email"], select')),
    state = true;
  inputs.forEach(function(item) {
    if (!item.valid) state = false;
  });

  const form = document.getElementById('adminForm');

  Promise.resolve()
      .then(() => {
        if (Joomla.checkFormField(['#jform_db_type', '#jform_db_host', '#jform_db_user', '#jform_db_name'])) {
          return Joomla.checkDbCredentials(form);
        }

        return false;
      })
      .then((result) => {
        if (!result) {
          return;
        }

        // Reveal everything
        document.getElementById('installStep1').classList.add('active');
        document.getElementById('installStep2').classList.add('active');
        document.getElementById('installStep3').classList.add('active');

        // Run the installer - we let this handle the redirect for now
        Joomla.install(['config'], form);
      })
      .catch((error) => {
        Joomla.renderMessages({'error': [error]});
      });
};


Joomla.checkDbCredentials = (form) => {
  document.body.appendChild(document.createElement('joomla-core-loader'));

  const fetchData = {
    method: 'POST',
    body: Joomla.serialiseForm(form),
    headers: new Headers({'Content-Type': 'application/x-www-form-urlencoded'}),
  };

  return fetch(`${Joomla.installationBaseUrl}?task=installation.dbcheck&format=json`, fetchData)
      .then((response) => response.json())
      .then((responseData) => {
        if (responseData.messages) {
          Joomla.renderMessages(responseData.messages);
        }

        Joomla.replaceTokens(responseData.token);

        if (responseData.error) {
          Joomla.renderMessages({'error': [responseData.message]});
        } else if (responseData.data && responseData.data.validated) {
          return responseData.data.validated;
        }

        return false;
      })
      .catch((error) => {
        console.log(error);
        throw Joomla.JText._('JLIB_DATABASE_ERROR_DATABASE_CONNECT', 'A Database error occurred.');
      })
      .finally(() => {
        document.querySelector('joomla-core-loader').remove();
      });
};


(function() {
  // Merge options from the session storage
  if (sessionStorage && sessionStorage.getItem('installation-data')) {
    Joomla.extend(this.options, sessionStorage.getItem('installation-data'));
  }

  Joomla.pageInit();
  var el = document.querySelector('.nav-steps.hidden');
  if (el) {
    el.classList.remove('hidden');
  }

  // Focus to the next field
  if (document.getElementById('jform_site_name')) {
    document.getElementById('jform_site_name').focus();
  }

  // Select language
  var languageEl = document.getElementById('jform_language');

  if (languageEl) {
    languageEl.addEventListener('change', function(e) {
      var form = document.getElementById('languageForm');
      Joomla.setlanguage(form)
    })
  }

  if (document.getElementById('step1')) {
    document.getElementById('step1').addEventListener('click', function(e) {
      e.preventDefault();
      if (Joomla.checkFormField(['#jform_site_name'])) {
        if (document.getElementById('languageForm')) {
          document.getElementById('languageForm').classList.add('hidden');
        }
        if (document.getElementById('installStep2')) {
          document.getElementById('installStep2').classList.add('active');
          document.getElementById('installStep1').classList.remove('active');

          // Focus to the next field
          if (document.getElementById('jform_admin_user')) {
            document.getElementById('jform_admin_user').focus();
          }
        }
      }
    })
  }

  if (document.getElementById('step2')) {
    document.getElementById('step2').addEventListener('click', function(e) {
      e.preventDefault();
      if (Joomla.checkFormField(['#jform_admin_user', '#jform_admin_email', '#jform_admin_password'])) {
        if (document.getElementById('installStep3')) {
          document.getElementById('installStep3').classList.add('active');
          document.getElementById('installStep2').classList.remove('active');
          document.getElementById('setupButton').classList.remove('hidden');

          Joomla.makeRandomDbPrefix();

          // Focus to the next field
          if (document.getElementById('jform_db_type')) {
            document.getElementById('jform_db_type').focus();
          }
        }
      }
    });

    document.getElementById('setupButton').addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      Joomla.checkInputs();
    })
  }

})();
