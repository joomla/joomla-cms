const modal = document.getElementById('ModalInstallTemplate');
const dragZone = document.querySelector('#dragarea');
const fileInput = document.querySelector('#install_package');
const button = document.querySelector('#select-file-button');
const progress = document.getElementById('upload-progress');
const progressBar = progress.querySelectorAll('.bar')[0];
const percentage = progress.querySelectorAll('.uploading-number')[0];
const uploadUrl = 'index.php?option=com_templates&task=styles.ajax_upload';

button.addEventListener('click', () => {
  fileInput.click();
}); // If upload file manually

fileInput.addEventListener('change', () => {
  const form = document.getElementById('templateForm'); // do field validation

  if (form.install_package.value === '') {
    alert(Joomla.Text._('COM_TEMPLATES_PACKAGEINSTALLER_NO_PACKAGE'), true);
  } else {
    const loading = document.getElementById('loading');

    if (loading) {
      loading.style.display = 'block';
    }

    form.installtype.value = 'upload';
    form.submit();
  }
});

modal.addEventListener('show.bs.modal', () => {
  let uploading = false;
  const showError = (res) => {
    dragZone.setAttribute('data-state', 'pending');

    let message = Joomla.Text._('COM_TEMPLATES_PACKAGEINSTALLER_UPLOAD_ERROR_UNKNOWN');

    if (res == null) {
      message = Joomla.Text._('COM_TEMPLATES_PACKAGEINSTALLER_UPLOAD_ERROR_EMPTY');
    } else if (typeof res === 'string') {
      // Let's remove unnecessary HTML
      message = res.replace(/(<([^>]+)>|\s+)/g, ' ');
    } else if (res.message) {
      message = res.message;
    }

    Joomla.renderMessages({
      error: [message],
    });
  };

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
        progressBar.css(`width, ${number}%`);
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
      headers: {
        'Content-Type': 'false',
      },
      uploadProgressCallback: progressCallback,
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
});
