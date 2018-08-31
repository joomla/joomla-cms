/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla) => {
  'use strict';

  const installPackageButtonId = 'installbutton_package';

  document.addEventListener('DOMContentLoaded', () => {
    Joomla.submitbuttonpackage = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_package.value === '') {
        alert(Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE'), true);
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'upload';
        form.submit();
      }
    };

    Joomla.submitbuttonfolder = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_directory.value === '') {
        alert(Joomla.JText._('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH'), true);
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'folder';
        form.submit();
      }
    };

    Joomla.submitbuttonurl = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        alert(Joomla.JText._('PLG_INSTALLER_URLINSTALLER_NO_URL'), true);
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'url';
        form.submit();
      }
    };

    Joomla.submitbutton4 = () => {
      const form = document.getElementById('adminForm');

      // do field validation
      if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
        alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), true);
      } else {
        Joomla.displayLoader();

        form.installtype.value = 'url';
        form.submit();
      }
    };

    Joomla.submitbuttonUpload = () => {
      const form = document.getElementById('uploadForm');

      // do field validation
      if (form.install_package.value === '') {
        alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
      } else {
        Joomla.displayLoader();

        form.submit();
      }
    };

    Joomla.displayLoader = () => {
      const loading = document.getElementById('loading');
      if (loading) {
        loading.style.display = 'block';
      }
    };

    const loading = document.getElementById('loading');
    const installer = document.getElementById('installer-install');

    if (loading && installer) {
      loading.style.top = parseInt(installer.offsetTop - window.pageYOffset, 10);
      loading.style.left = 0;
      loading.style.width = '100%';
      loading.style.height = '100%';
      loading.style.display = 'none';
      loading.style.marginTop = '-10px';
    }

    document.getElementById(installPackageButtonId).addEventListener('click', (event) => {
      event.preventDefault();
      Joomla.submitbuttonpackage();
    });
  });
})(Joomla);

document.addEventListener('DOMContentLoaded', () => {
  if (typeof FormData === 'undefined') {
    document.querySelector('#legacy-uploader').style.display = 'block';
    document.querySelector('#uploader-wrapper').style.display = 'none';
    return;
  }

  const dragZone = document.querySelector('#dragarea');
  const fileInput = document.querySelector('#install_package');
  const loading = document.querySelector('#loading');
  const button = document.querySelector('#select-file-button');
  const returnUrl = document.querySelector('#installer-return').value;
  const token = document.querySelector('#installer-token').value;
  let uploadUrl = 'index.php?option=com_installer&task=install.ajax_upload';

  if (returnUrl) {
    uploadUrl += `&return=${returnUrl}`;
  }

  button.addEventListener('click', () => {
    fileInput.click();
  });

  fileInput.addEventListener('change', () => {
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

    dragZone.classList.remove('hover');

    const files = event.target.files || event.dataTransfer.files;

    if (!files.length) {
      return;
    }

    const file = files[0];
    const data = new FormData();

    data.append('install_package', file);
    data.append('installtype', 'upload');
    data.append(token, 1);

    loading.style.display = 'block';

    Joomla.request({
      url: uploadUrl,
      method: 'POST',
      perform: true,
      headers: { 'Content-Type': 'false' },
      onSuccess: (response) => {
        const res = JSON.parse(response);
        if (!res.success) {
          // eslint-disable-next-line no-console
          console.log(res.message, res.messages);
        }
        // Always redirect that can show message queue from session
        if (res.data.redirect) {
          window.location.href = res.data.redirect;
        } else {
          window.location.href = 'index.php?option=com_installer&view=install';
        }
      },
      onError: (error) => {
        loading.style.display = 'none';
        alert(error.statusText);
      },
    });
  });
});
