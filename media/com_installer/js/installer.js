/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function (Joomla) {
  'use strict';

  var installPackageButtonId = 'installbutton_package';
  document.addEventListener('DOMContentLoaded', function () {
    Joomla.submitbuttonpackage = function () {
      var form = document.getElementById('adminForm'); // do field validation

      if (form.install_package.value === '') {
        Joomla.renderMessages({
          warning: [Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE')]
        });
      } else if (form.install_package.files[0].size > form.max_upload_size.value) {
        Joomla.renderMessages({
          warning: [Joomla.JText._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG')]
        });
      } else {
        Joomla.displayLoader();
        form.installtype.value = 'upload';
        form.submit();
      }
    };

    Joomla.submitbuttonfolder = function () {
      var form = document.getElementById('adminForm'); // do field validation

      if (form.install_directory.value === '') {
        Joomla.renderMessages({
          warning: [Joomla.JText._('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH')]
        });
      } else {
        Joomla.displayLoader();
        form.installtype.value = 'folder';
        form.submit();
      }
    };

    Joomla.submitbuttonurl = function () {
      var form = document.getElementById('adminForm'); // do field validation

      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        Joomla.renderMessages({
          warning: [Joomla.JText._('PLG_INSTALLER_URLINSTALLER_NO_URL')]
        });
      } else {
        Joomla.displayLoader();
        form.installtype.value = 'url';
        form.submit();
      }
    };

    Joomla.submitbutton4 = function () {
      var form = document.getElementById('adminForm'); // do field validation

      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        Joomla.renderMessages({
          warning: [Joomla.JText._('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL')]
        });
      } else {
        Joomla.displayLoader();
        form.installtype.value = 'url';
        form.submit();
      }
    };

    Joomla.submitbutton5 = function () {
      var form = document.getElementById('adminForm'); // do field validation

      if (form.install_url.value !== '' || form.install_url.value !== 'http://' || form.install_url.value !== 'https://') {
        Joomla.submitbutton4();
      } else if (form.install_url.value === '') {
        Joomla.renderMessages({
          warning: [Joomla.apps.options.btntxt]
        });
      } else {
        document.querySelector('#appsloading').classList.remove('hidden');
        form.installtype.value = 'web';
        form.submit();
      }
    };

    Joomla.submitbuttonUpload = function () {
      var form = document.getElementById('uploadForm'); // do field validation

      if (form.install_package.value === '') {
        Joomla.renderMessages({
          warning: [Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE')]
        });
      } else if (form.install_package.files[0].size > form.max_upload_size.value) {
        Joomla.renderMessages({
          warning: [Joomla.JText._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG')]
        });
      } else {
        Joomla.displayLoader();
        form.submit();
      }
    };

    Joomla.displayLoader = function () {
      var loading = document.getElementById('loading');

      if (loading) {
        loading.classList.remove('hidden');
      }
    };

    var loading = document.getElementById('loading');
    var installer = document.getElementById('installer-install');

    if (loading && installer) {
      loading.style.top = parseInt(installer.offsetTop - window.pageYOffset, 10);
      loading.style.left = 0;
      loading.style.width = '100%';
      loading.style.height = '100%';
      loading.classList.add('hidden');
      loading.style.marginTop = '-10px';
    }

    document.getElementById(installPackageButtonId).addEventListener('click', function (event) {
      event.preventDefault();
      Joomla.submitbuttonpackage();
    });
  });
})(Joomla);

document.addEventListener('DOMContentLoaded', function () {
  if (typeof FormData === 'undefined') {
    document.querySelector('#legacy-uploader').classList.remove('hidden');
    document.querySelector('#uploader-wrapper').classList.add('hidden');
    return;
  }

  var uploading = false;
  var dragZone = document.querySelector('#dragarea');
  var fileInput = document.querySelector('#install_package');
  var fileSizeMax = document.querySelector('#max_upload_size').value;
  var button = document.querySelector('#select-file-button');
  var returnUrl = document.querySelector('#installer-return').value;
  var progress = document.getElementById('upload-progress');
  var progressBar = progress.querySelectorAll('.bar')[0];
  var percentage = progress.querySelectorAll('.uploading-number')[0];
  var uploadUrl = 'index.php?option=com_installer&task=install.ajax_upload';

  function showError(res) {
    dragZone.setAttribute('data-state', 'pending');

    var message = Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_UNKNOWN');

    if (res == null) {
      message = Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_EMPTY');
    } else if (typeof res === 'string') {
      // Let's remove unnecessary HTML
      message = res.replace(/(<([^>]+)>|\s+)/g, ' ');
    } else if (res.message) {
      message = res.message;
    }

    Joomla.renderMessages({
      error: [message]
    });
  }

  if (returnUrl) {
    uploadUrl += "&return=".concat(returnUrl);
  }

  button.addEventListener('click', function () {
    fileInput.click();
  });
  fileInput.addEventListener('change', function () {
    if (uploading) {
      return;
    }

    Joomla.submitbuttonpackage();
  });
  dragZone.addEventListener('dragenter', function (event) {
    event.preventDefault();
    event.stopPropagation();
    dragZone.classList.add('hover');
    return false;
  }); // Notify user when file is over the drop area

  dragZone.addEventListener('dragover', function (event) {
    event.preventDefault();
    event.stopPropagation();
    dragZone.classList.add('hover');
    return false;
  });
  dragZone.addEventListener('dragleave', function (event) {
    event.preventDefault();
    event.stopPropagation();
    dragZone.classList.remove('hover');
    return false;
  });
  dragZone.addEventListener('drop', function (event) {
    event.preventDefault();
    event.stopPropagation();

    if (uploading) {
      return;
    }

    dragZone.classList.remove('hover');
    var files = event.target.files || event.dataTransfer.files;

    if (!files.length) {
      return;
    }

    var file = files[0];
    var data = new FormData();

    if (file.size > fileSizeMax) {
      Joomla.renderMessages({
        warning: [Joomla.JText._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG')]
      });
      return;
    }

    data.append('install_package', file);
    data.append('installtype', 'upload');
    dragZone.setAttribute('data-state', 'uploading');
    progressBar.setAttribute('aria-valuenow', 0);
    uploading = true;
    progressBar.style.width = 0;
    percentage.textContent = '0'; // Upload progress

    var progressCallback = function progressCallback(evt) {
      if (evt.lengthComputable) {
        var percentComplete = evt.loaded / evt.total;
        var number = Math.round(percentComplete * 100);
        progressBar.css('width', "".concat(number, "%"));
        progressBar.setAttribute('aria-valuenow', number);
        percentage.textContent = "".concat(number);

        if (number === 100) {
          dragZone.setAttribute('data-state', 'installing');
        }
      }
    };

    Joomla.request({
      url: uploadUrl,
      method: 'POST',
      perform: true,
      data: data,
      headers: {
        'Content-Type': 'false'
      },
      uploadProgressCallback: progressCallback,
      onSuccess: function onSuccess(response) {
        if (!response) {
          showError(response);
          return;
        }

        var res;

        try {
          res = JSON.parse(response);
        } catch (e) {
          showError(e);
          return;
        }

        if (!res.success && !res.data) {
          showError(res);
          return;
        } // Always redirect that can show message queue from session


        if (res.data.redirect) {
          window.location.href = res.data.redirect;
        } else {
          window.location.href = 'index.php?option=com_installer&view=install';
        }
      },
      onError: function onError(error) {
        uploading = false;

        if (error.status === 200) {
          var res = error.responseText || error.responseJSON;
          showError(res);
        } else {
          showError(error.statusText);
        }
      }
    });
  });
});