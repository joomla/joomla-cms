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
  cached_instance: null,
  genericErrorMessage: message => {
    const headerDiv = document.getElementById('errorDialogLabel');
    const messageDiv = document.getElementById('errorDialogMessage');
    const progressDiv = document.getElementById('joomlaupdate-progress');
    const errorDiv = document.getElementById('joomlaupdate-error');
    headerDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_GENERIC');
    messageDiv.innerHTML = message;
    if (message.toLowerCase() === 'invalid login') {
      messageDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_INVALIDLOGIN');
    }
    progressDiv.classList.add('d-none');
    errorDiv.classList.remove('d-none');
  },
  handleErrorResponse: xhr => {
    const isForbidden = xhr.status === 403;
    const headerDiv = document.getElementById('errorDialogLabel');
    const messageDiv = document.getElementById('errorDialogMessage');
    const progressDiv = document.getElementById('joomlaupdate-progress');
    const errorDiv = document.getElementById('joomlaupdate-error');
    if (isForbidden) {
      headerDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_FORBIDDEN');
      messageDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_FORBIDDEN');
    } else {
      headerDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_SERVERERROR');
      messageDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_SERVERERROR');
    }
    progressDiv.classList.add('d-none');
    errorDiv.classList.remove('d-none');
  },
  startExtract: () => {
    // Reset variables
    Joomla.Update.stat_files = 0;
    Joomla.Update.stat_inbytes = 0;
    Joomla.Update.stat_outbytes = 0;
    Joomla.Update.cached_instance = null;
    document.getElementById('extbytesin').innerText = Joomla.Update.formatBytes(Joomla.Update.stat_inbytes);
    document.getElementById('extbytesout').innerText = Joomla.Update.formatBytes(Joomla.Update.stat_outbytes);
    document.getElementById('extfiles').innerText = Joomla.Update.formatFiles(Joomla.Update.stat_files);
    const postData = new FormData();
    postData.append('task', 'startExtract');
    postData.append('password', Joomla.Update.password);

    // Make the initial request to the extraction script
    Joomla.request({
      url: Joomla.Update.ajax_url,
      data: postData,
      method: 'POST',
      perform: true,
      onSuccess: rawJson => {
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
  stepExtract: data => {
    // Did we get an error from the ZIP extraction engine?
    if (data.status === false) {
      Joomla.Update.genericErrorMessage(data.message);
      return;
    }
    const progressDiv = document.getElementById('progress-bar');

    // Add data to variables
    Joomla.Update.stat_inbytes = data.bytesIn;
    Joomla.Update.stat_percent = data.percent;
    Joomla.Update.stat_percent = Joomla.Update.stat_percent || 80 * (Joomla.Update.stat_inbytes / Joomla.Update.totalsize);

    // Update GUI
    Joomla.Update.stat_outbytes = data.bytesOut;
    Joomla.Update.stat_files = data.files;
    if (Joomla.Update.stat_percent < 80) {
      progressDiv.classList.remove('bg-success');
      progressDiv.style.width = `${Joomla.Update.stat_percent}%`;
      progressDiv.setAttribute('aria-valuenow', Joomla.Update.stat_percent);
    } else if (Joomla.Update.stat_percent >= 80) {
      progressDiv.style.width = '80%';
      progressDiv.setAttribute('aria-valuenow', 80);
    }
    progressDiv.innerText = `${Joomla.Update.stat_percent.toFixed(1)}%`;
    document.getElementById('extbytesin').innerText = Joomla.Update.formatBytes(Joomla.Update.stat_inbytes);
    document.getElementById('extbytesout').innerText = Joomla.Update.formatBytes(Joomla.Update.stat_outbytes);
    document.getElementById('extfiles').innerText = Joomla.Update.formatFiles(Joomla.Update.stat_files);

    // Are we done extracting?
    if (data.done) {
      progressDiv.style.width = '80%';
      progressDiv.setAttribute('aria-valuenow', 80);
      Joomla.Update.finalizeUpdate();
      return;
    }

    // This is required so we can get outside the scope of the previous XHR's success handler.
    window.setTimeout(() => {
      Joomla.Update.delayedStepExtract(data.instance);
    }, 50);
  },
  delayedStepExtract: instance => {
    Joomla.Update.cached_instance = instance;
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
      onSuccess: rawJson => {
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
        const progressDiv = document.getElementById('progress-bar');
        const titleDiv = document.getElementById('update-title');
        progressDiv.classList.add('bg-success');
        progressDiv.style.width = '100%';
        progressDiv.innerText = '100%';
        progressDiv.setAttribute('aria-valuenow', 100);
        titleDiv.innerText = Joomla.Text._('COM_JOOMLAUPDATE_UPDATING_COMPLETE');

        // Allow people to see the completion message
        window.setTimeout(() => {
          window.location = Joomla.Update.return_url;
        }, 1000);
      },
      onError: Joomla.Update.handleErrorResponse
    });
  },
  formatBytes: (bytes, decimals = 2) => {
    if (bytes === 0) return `0 ${Joomla.Text._('JLIB_SIZE_BYTES')}`;
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = [Joomla.Text._('JLIB_SIZE_BYTES'), Joomla.Text._('JLIB_SIZE_KB'), Joomla.Text._('JLIB_SIZE_MB'), Joomla.Text._('JLIB_SIZE_GB'), Joomla.Text._('JLIB_SIZE_TB'), Joomla.Text._('JLIB_SIZE_PB'), Joomla.Text._('JLIB_SIZE_EB'), Joomla.Text._('JLIB_SIZE_ZB'), Joomla.Text._('JLIB_SIZE_YB')];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / k ** i).toFixed(dm))} ${sizes[i]}`;
  },
  formatFiles: files => `${files} ${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_UPDATE_ITEMS')}`,
  resumeButtonHandler: e => {
    e.preventDefault();
    document.getElementById('joomlaupdate-progress').classList.remove('d-none');
    document.getElementById('joomlaupdate-error').classList.add('d-none');
    if (Joomla.Update.cached_instance === false) {
      Joomla.Update.startExtract();
    } else {
      Joomla.Update.delayedStepExtract(Joomla.Update.cached_instance);
    }
  },
  restartButtonHandler: e => {
    e.preventDefault();
    document.getElementById('joomlaupdate-progress').classList.remove('d-none');
    document.getElementById('joomlaupdate-error').classList.add('d-none');
    Joomla.Update.startExtract();
  }
};

// Add click handlers for the Resume and Restart Update buttons in the error pane.
const elResume = document.getElementById('joomlaupdate-resume');
const elRestart = document.getElementById('joomlaupdate-restart');
if (elResume) {
  elResume.addEventListener('click', Joomla.Update.resumeButtonHandler);
}
if (elRestart) {
  elRestart.addEventListener('click', Joomla.Update.restartButtonHandler);
}

// Start the update
const JoomlaUpdateOptions = Joomla.getOptions('joomlaupdate');
if (JoomlaUpdateOptions && Object.keys(JoomlaUpdateOptions).length) {
  Joomla.Update.password = JoomlaUpdateOptions.password;
  Joomla.Update.totalsize = JoomlaUpdateOptions.totalsize;
  Joomla.Update.ajax_url = JoomlaUpdateOptions.ajax_url;
  Joomla.Update.return_url = JoomlaUpdateOptions.return_url;
  Joomla.Update.startExtract();
}
