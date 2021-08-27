/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!Joomla) {
  throw new Error('Joomla API was not initialised properly');
}

Joomla.Update = window.Joomla.Update || {
  stat_total: 0,
  stat_files: 0,
  stat_inbytes: 0,
  stat_outbytes: 0,
  password: null,
  totalsize: 0,
  ajax_url: null,
  return_url: null,
  genericErrorMessage: (message) => {
    const header = document.getElementById('errorDialogLabel');
    const messageDiv = document.getElementById('errorDialogMessage');
    const elProgress = document.getElementById('progress-bar');

    elProgress.classList.add('bg-danger');
    elProgress.classList.remove('bg-success');

    header.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_GENERIC');
    messageDiv.innerHTML = message;

    if (message.toLowerCase() === 'invalid login') {
      messageDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_INVALIDLOGIN');
    }

    const myModal = new bootstrap.Modal(document.getElementById('errorDialog'), {
      keyboard: true,
      backdrop: true
    });
    myModal.show();
  },
  handleErrorResponse: (xhr) => {
    const isForbidden = xhr.status === 403;
    const header = document.getElementById('errorDialogLabel');
    const message = document.getElementById('errorDialogMessage');
    const elProgress = document.getElementById('progress-bar');

    elProgress.classList.add('bg-danger');
    elProgress.classList.remove('bg-success');

    if (isForbidden) {
      header.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_FORBIDDEN');
      message.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_FORBIDDEN');
    } else {
      header.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_SERVERERROR');
      message.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_SERVERERROR');
    }

    const myModal = new bootstrap.Modal(document.getElementById('errorDialog'), {
      keyboard: true,
      backdrop: true
    });
    myModal.show();
  },
  startExtract: () => {
    // Reset variables
    Joomla.Update.stat_files = 0;
    Joomla.Update.stat_inbytes = 0;
    Joomla.Update.stat_outbytes = 0;

    const postData = new FormData();
    postData.append('task', 'startExtract');
    postData.append('password', Joomla.Update.password);

    // Make the initial request to the extraction script
    Joomla.request({
      url: Joomla.Update.ajax_url,
      data: postData,
      method: 'POST',
      perform: true,
      onSuccess: (rawJson) => {
        try {
          // If we can decode the response as JSON step through the update
          const data = JSON.parse(rawJson);
          Joomla.Update.stepExtract(data);
        } catch (e) {
          // Decoding failed; display an error
          Joomla.Update.genericErrorMessage(e.message);
        }
      },
      onError: Joomla.Update.handleErrorResponse
    });
  },
  stepExtract: (data) => {
    // Did we get an error from the ZIP extraction engine?
    if (data.status === false) {
      Joomla.Update.genericErrorMessage(data.message);

      return;
    }

    const elProgress = document.getElementById('progress-bar');

    // Are we done extracting?
    if (data.done) {
      elProgress.classList.add('bg-success');
      elProgress.style.width = '100%';
      elProgress.setAttribute('aria-valuenow', 100);

      Joomla.Update.finalizeUpdate();

      return;
    }

    // Add data to variables
    Joomla.Update.stat_inbytes = data.bytesIn;
    Joomla.Update.stat_percent = data.percent;
    Joomla.Update.stat_percent = Joomla.Update.stat_percent
      || (100 * (Joomla.Update.stat_inbytes / Joomla.Update.totalsize));

    // Update GUI
    Joomla.Update.stat_outbytes = data.bytesOut;
    Joomla.Update.stat_files = data.files;

    if (Joomla.Update.stat_percent < 100) {
      elProgress.classList.remove('bg-success');
      elProgress.style.width = `${Joomla.Update.stat_percent}%`;
      elProgress.setAttribute('aria-valuenow', Joomla.Update.stat_percent);
    } else if (Joomla.Update.stat_percent >= 100) {
      elProgress.classList.add('bg-success');
      elProgress.style.width = '100%';
      elProgress.setAttribute('aria-valuenow', 100);
    }

    document.getElementById('extpercent').innerText = `${Joomla.Update.stat_percent.toFixed(1)}%`;
    document.getElementById('extbytesin').innerText = Joomla.Update.stat_inbytes;
    document.getElementById('extbytesout').innerText = Joomla.Update.stat_outbytes;
    document.getElementById('extfiles').innerText = Joomla.Update.stat_files;

    // This is required so we can get outside the scope of the previous XHR's success handler.
    window.setTimeout(() => {
      Joomla.Update.delayedStepExtract(data.instance);
    }, 50);
  },
  delayedStepExtract: (instance) => {
    const postData = new FormData();
    postData.append('task', 'stepExtract');
    postData.append('password', Joomla.Update.password);

    if (instance) {
      postData.append('instance', instance);
    }

    Joomla.request({
      url: Joomla.Update.ajax_url,
      data: postData,
      method: 'POST',
      perform: true,
      onSuccess: (rawJson) => {
        try {
          const newData = JSON.parse(rawJson);
          Joomla.Update.stepExtract(newData);
        } catch (e) {
          Joomla.Update.genericErrorMessage(e.message);
        }
      },
      onError: Joomla.Update.handleErrorResponse
    });
  },
  finalizeUpdate: () => {
    const postData = new FormData();
    postData.append('task', 'finalizeUpdate');
    postData.append('password', Joomla.Update.password);
    Joomla.request({
      url: Joomla.Update.ajax_url,
      data: postData,
      method: 'POST',
      perform: true,
      onSuccess: () => {
        window.location = Joomla.Update.return_url;
      },
      onError: Joomla.Update.handleErrorResponse
    });
  }
};

const JoomlaUpdateOptions = Joomla.getOptions('joomlaupdate');

if (JoomlaUpdateOptions && Object.keys(JoomlaUpdateOptions).length) {
  Joomla.Update.password = JoomlaUpdateOptions.password;
  Joomla.Update.totalsize = JoomlaUpdateOptions.totalsize;
  Joomla.Update.ajax_url = JoomlaUpdateOptions.ajax_url;
  Joomla.Update.return_url = JoomlaUpdateOptions.return_url;

  Joomla.Update.startExtract();
}
