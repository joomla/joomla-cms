/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

Joomla.Update = window.Joomla.Update || {
  stat_total: 0,
  stat_files: 0,
  stat_inbytes: 0,
  stat_outbytes: 0,
  password: null,
  totalsize: 0,
  ajax_url: null,
  return_url: null,
  errorHandler: (message) => {
    alert(`ERROR:\n${message}`);
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
          Joomla.Update.errorHandler(e.message);
        }
      },
      onError: () => {
        // A server error has occurred.
        Joomla.Update.errorHandler('AJAX Error');
      },
    });
  },
  stepExtract: (data) => {
    // Did we get an error from the ZIP extraction engine?
    if (data.status === false) {
      Joomla.Update.errorHandler(data.message);

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
    Joomla.Update.stat_inbytes += data.bytesIn;
    Joomla.Update.stat_percent = (Joomla.Update.stat_inbytes * 100) / Joomla.Update.totalsize;

    // Update GUI
    Joomla.Update.stat_outbytes += data.bytesOut;
    Joomla.Update.stat_files += data.files;

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

    const postData = new FormData();
    postData.append('task', 'stepExtract');
    postData.append('password', Joomla.Update.password);

    if (data.instance) {
      postData.append('instance', data.instance);
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
          Joomla.Update.errorHandler(e.message);
        }
      },
      onError: () => {
        Joomla.Update.errorHandler('AJAX Error');
      },
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
      onError: () => {
        Joomla.Update.errorHandler('AJAX Error');
      },
    });
  },
};

const JoomlaUpdateOptions = Joomla.getOptions('joomlaupdate');

if (JoomlaUpdateOptions && Object.keys(JoomlaUpdateOptions).length) {
  Joomla.Update.password = JoomlaUpdateOptions.password;
  Joomla.Update.totalsize = JoomlaUpdateOptions.totalsize;
  Joomla.Update.ajax_url = JoomlaUpdateOptions.ajax_url;
  Joomla.Update.return_url = JoomlaUpdateOptions.return_url;

  Joomla.Update.startExtract();
}
