/**
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbuttonpackage = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_package.value === '') {
        Joomla.renderMessages({ warning: [Joomla.Text._('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE')] });
      } else if (form.install_package.files[0].size > form.max_upload_size.value) {
        Joomla.renderMessages({ warning: [Joomla.Text._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG')] });
      } else {
        const loading = document.getElementById('loading');
        if (loading) {
          loading.classList.remove('hidden');
        }

        form.installtype.value = 'upload';
        form.submit();
      }
    };

    if (typeof FormData === 'undefined') {
      document.querySelector('#legacy-uploader').classList.remove('hidden');
      document.querySelector('#uploader-wrapper').classList.add('hidden');
      return;
    }

    let uploading = false;
    const dragZone = document.querySelector('#dragarea');
    const fileInput = document.querySelector('#install_package');
    const fileSizeMax = document.querySelector('#max_upload_size').value;
    const button = document.querySelector('#select-file-button');
    const returnUrl = document.querySelector('#installer-return').value;
    const progress = document.getElementById('upload-progress');
    const progressBar = progress.querySelector('.progress-bar');
    const percentage = progress.querySelector('.uploading-number');
    let uploadUrl = 'index.php?option=com_installer&task=install.ajax_upload';

    function showError(res) {
      dragZone.setAttribute('data-state', 'pending');
      let message = Joomla.Text._('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_UNKNOWN');
      if (res == null) {
        message = Joomla.Text._('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_EMPTY');
      } else if (typeof res === 'string') {
        // Let's remove unnecessary HTML
        message = res.replace(/(<([^>]+)>|\s+)/g, ' ');
      } else if (res.message) {
        ({ message } = res);
      }
      Joomla.renderMessages({ error: [message] });
    }

    if (returnUrl) {
      uploadUrl += `&return=${returnUrl}`;
    }

    button.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', () => {
      if (uploading) {
        return;
      }
      Joomla.submitbuttonpackage();
    });

    dragZone.addEventListener('dragenter', (event) => {
      event.preventDefault();
      event.stopPropagation();

      dragZone.classList.add('hover');

      return false;
    });

    // Notify user when file is over the drop area
    dragZone.addEventListener('dragover', (event) => {
      event.preventDefault();
      event.stopPropagation();

      dragZone.classList.add('hover');

      return false;
    });

    dragZone.addEventListener('dragleave', (event) => {
      event.preventDefault();
      event.stopPropagation();
      dragZone.classList.remove('hover');

      return false;
    });

    dragZone.addEventListener('drop', (event) => {
      event.preventDefault();
      event.stopPropagation();

      if (uploading) {
        return;
      }

      dragZone.classList.remove('hover');

      const files = event.target.files || event.dataTransfer.files;

      if (!files.length) {
        return;
      }

      const file = files[0];
      const data = new FormData();

      if (!file.type) {
        Joomla.renderMessages({ error: [Joomla.Text._('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE')] });
        return;
      }

      if (file.size > fileSizeMax) {
        Joomla.renderMessages({ warning: [Joomla.Text._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG')] });
        return;
      }

      data.append('install_package', file);
      data.append('installtype', 'upload');
      dragZone.setAttribute('data-state', 'uploading');
      progressBar.setAttribute('aria-valuenow', 0);

      uploading = true;
      progressBar.style.width = 0;
      percentage.textContent = '0';

      // Upload progress
      const progressCallback = (evt) => {
        if (evt.lengthComputable) {
          const percentComplete = evt.loaded / evt.total;
          const number = Math.round(percentComplete * 100);
          progressBar.style.width = `${number}%`;
          progressBar.setAttribute('aria-valuenow', number);
          percentage.textContent = `${number}`;
          if (number === 100) {
            dragZone.setAttribute('data-state', 'installing');
          }
        }
      };

      Joomla.request({
        url: uploadUrl,
        method: 'POST',
        perform: true,
        data,
        onBefore: (xhr) => {
          xhr.upload.addEventListener('progress', progressCallback);
        },
        onSuccess: (response) => {
          if (!response) {
            showError(response);
            return;
          }

          let res;

          try {
            res = JSON.parse(response);
          } catch (e) {
            showError(e);

            return;
          }

          if (!res.success && !res.data) {
            showError(res);

            return;
          }

          // Always redirect that can show message queue from session
          if (res.data.redirect) {
            window.location.href = res.data.redirect;
          } else {
            window.location.href = 'index.php?option=com_installer&view=install';
          }
        },
        onError: (error) => {
          uploading = false;
          if (error.status === 200) {
            const res = error.responseText || error.responseJSON;
            showError(res);
          } else {
            showError(error.statusText);
          }
        },
      });
    });

    document.getElementById('installbutton_package').addEventListener('click', (event) => {
      event.preventDefault();
      Joomla.submitbuttonpackage();
    });
  });
})(Joomla);
