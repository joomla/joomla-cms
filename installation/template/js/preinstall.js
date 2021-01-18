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

/**
 * Method to detect the FTP root via AJAX request.
 */
Joomla.detectFtpRoot = function() {
  document.body.appendChild(document.createElement('joomla-core-loader'));
  var form = document.getElementById('ftpForm'),
    data = Joomla.serialiseForm(form);

  Joomla.request({
    method: "POST",
    url : Joomla.installationBaseUrl + '?task=installation.detectftproot&format=json',
    data: data,
    perform: true,
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    onSuccess: function(response, xhr){
      var loaderElement = document.querySelector('joomla-core-loader');
      try {
        response = JSON.parse(response);
      } catch (e) {
        loaderElement.parentNode.removeChild(loaderElement);
        console.error('Error in FTP folder detection Endpoint');
        console.error(response);
        Joomla.renderMessages({'error': [Joomla.JText._('INSTL_FTPDETECT_RESPONSE_ERROR')]});

        return false;
      }

      if (response.messages) {
        Joomla.renderMessages(response.messages);
      }

      Joomla.replaceTokens(response.token);
      loaderElement.parentNode.removeChild(loaderElement);

      if (response.error) {
        Joomla.renderMessages({'error': [response.message]});
      } else if (response.data && response.data.root) {
        document.getElementById('jform_ftp_root').value = response.data.root;
      }
    },
    onError:   function(xhr){
      Joomla.renderMessages([['', Joomla.JText._('JLIB_FTP_ERROR_FTP_CONNECT', 'A FTP error occurred.')]]);
      //Install.goToPage('summary');
      var loaderElement = document.querySelector('joomla-core-loader');
      loaderElement.parentNode.removeChild(loaderElement);

      try {
        var r = JSON.parse(xhr.responseText);
        Joomla.replaceTokens(r.token);
        alert(r.message);
      } catch (e) {
      }
    }
  });
};

/**
 * Method to detect the FTP root via AJAX request.
 */
Joomla.verifyFtp = function() {
  document.body.appendChild(document.createElement('joomla-core-loader'));
  var form = document.getElementById('ftpForm'),
    data = Joomla.serialiseForm(form);

  Joomla.request({
    method: "POST",
    url : Joomla.installationBaseUrl + '?task=installation.verifyftp&format=json',
    data: data,
    perform: true,
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    onSuccess: function(response, xhr){
      var loaderElement = document.querySelector('joomla-core-loader');
      try {
        response = JSON.parse(response);
      } catch (e) {
        loaderElement.parentNode.removeChild(loaderElement);
        console.error('Error in FTP verification Endpoint');
        console.error(response);
        Joomla.renderMessages({'error': [Joomla.JText._('INSTL_FTPVERIFY_RESPONSE_ERROR')]});

        return false;
      }

      if (response.messages) {
        Joomla.renderMessages(response.messages);
      }

      Joomla.replaceTokens(response.token);
      loaderElement.parentNode.removeChild(loaderElement);

      if (response.error) {
        Joomla.renderMessages({'error': [response.message]});
      } else if (response.data && response.data.valid) {
        Joomla.goToPage('setup');
      }
    },
    onError:   function(xhr){
      Joomla.renderMessages([['', Joomla.JText._('JLIB_FTP_ERROR_FTP_CONNECT', 'A FTP error occurred.')]]);
      //Install.goToPage('summary');
      var loaderElement = document.querySelector('joomla-core-loader');
      loaderElement.parentNode.removeChild(loaderElement);

      try {
        var r = JSON.parse(xhr.responseText);
        Joomla.replaceTokens(r.token);
        alert(r.message);
      } catch (e) {
      }
    }
  });
};

/**
 * Method to detect the FTP root via AJAX request.
 */
Joomla.skipFtp = function() {
  document.body.appendChild(document.createElement('joomla-core-loader'));

  Joomla.request({
    method: "POST",
    url : Joomla.installationBaseUrl + '?task=installation.skipftp&format=json',
    perform: true,
    onSuccess: function(response, xhr){
      Joomla.goToPage('setup');
    },
    onError:   function(xhr){
      Joomla.renderMessages([['', Joomla.JText._('JLIB_FTP_ERROR_FTP_CONNECT', 'A FTP error occurred.')]]);
      var loaderElement = document.querySelector('joomla-core-loader');
      loaderElement.parentNode.removeChild(loaderElement);

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

  // Select language
  var languageEl = document.getElementById('jform_language');

  if (languageEl) {
    languageEl.addEventListener('change', function(e) {
      var form = document.getElementById('languageForm');
      Joomla.setlanguage(form)
    })
  }

  document.getElementById('findbutton').addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    Joomla.detectFtpRoot();
  })

  document.getElementById('verifybutton').addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    Joomla.verifyFtp();
  })

  document.getElementById('skipFTPbutton').addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    Joomla.skipFtp();
  })
})();
