/**
 * @package     Joomla.Installation
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
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
  document.getElementById('progress-text').classList.remove('error');
  document.getElementById('progress-text').setAttribute('role', 'status');
  document.getElementById('progress-text').innerText = Joomla.Text._('INSTL_IN_PROGRESS');
  document.getElementById('progressbar').setAttribute('value', 0);

  // Reveal everything
  document.getElementById('installStep1').classList.add('active');
  document.getElementById('installStep2').classList.add('active');
  document.getElementById('installStep3').classList.add('active');

  if (Joomla.checkFormField(['#jform_site_name', '#jform_admin_user', '#jform_admin_email', '#jform_admin_password', '#jform_db_type', '#jform_db_host', '#jform_db_user', '#jform_db_name'])) {
    Joomla.checkDbCredentials();
  }
};

Joomla.checkDbCredentials = function() {
  const progress = document.getElementById('progressbar');
  const progress_text = document.getElementById('progress-text');
  var form = document.getElementById('adminForm'),
    data = Joomla.serialiseForm(form);

  // Remove potential messages
  Joomla.removeMessages();

  // Hide form and show progress bar
  document.getElementById('installStep1').classList.remove('active');
  document.getElementById('installStep2').classList.remove('active');
  document.getElementById('installStep3').classList.remove('active');
  document.getElementById('installStep4').classList.add('active');
  progress_text.innerText = Joomla.Text._('INSTL_IN_PROGRESS');

  Joomla.request({
    method: "POST",
    url : Joomla.installationBaseUrl + '?task=installation.dbcheck&format=json',
    data: data,
    perform: true,
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    onSuccess: function(response, xhr){
      try {
        response = JSON.parse(response);
      } catch (e) {
        progress_text.setAttribute('role', 'alert');
        progress_text.classList.add('error');
        progress_text.innerText = response;
        console.error('Error in DB Check Endpoint');
        console.error(response);
        Joomla.renderMessages({'error': [Joomla.Text._('INSTL_DATABASE_RESPONSE_ERROR')]});

        return false;
      }

      if (response.messages) {
        Joomla.renderMessages(response.messages);
      }

      Joomla.replaceTokens(response.token);

      if (response.error) {
        document.getElementById('installStep1').classList.add('active');
        document.getElementById('installStep2').classList.add('active');
        document.getElementById('installStep3').classList.add('active');
        document.getElementById('installStep4').classList.remove('active');
        progress_text.innerText = Joomla.Text._('INSTL');
      } else if (response.data && response.data.validated === true) {
        // Run the installer - we let this handle the redirect for now
        // @todo: Convert to promises
        progress.setAttribute('value', parseInt(progress.getAttribute('value')) + 1);
        progress_text.innerText = Joomla.Text._('INSTL_IN_PROGRESS');
        Joomla.install(['create', 'populate1', 'populate2', 'populate3', 'custom1', 'custom2', 'config'], form);
      }
    },
    onError:   function(xhr){
      Joomla.renderMessages([['', Joomla.Text._('JLIB_DATABASE_ERROR_DATABASE_CONNECT', 'A Database error occurred.')]]);
      progress_text.setAttribute('role', 'alert');
      progress_text.classList.add('error');
      progress_text.innerText = response.message;
      //Install.goToPage('summary');

      try {
        var r = JSON.parse(xhr.responseText);
        Joomla.replaceTokens(r.token);
        alert(r.message);
      } catch (e) {
      }
    }
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
