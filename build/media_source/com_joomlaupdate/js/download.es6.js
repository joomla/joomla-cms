/**
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla, document, window) => {
  'use strict';

  const JoomlaUpdateDownload = {
    ajaxUrl: null,
    returnUrl: null,
    nextFrag: -1,
    minWait: 3000,
  };

  JoomlaUpdateDownload.run = () => {
    const options = Joomla.getOptions('com_joomlaupdate', {});
    JoomlaUpdateDownload.ajaxUrl = options.ajaxUrl;
    JoomlaUpdateDownload.returnUrl = options.returnUrl;

    document.getElementById('download-error').style.display = 'none';

    const progressBar = document.getElementById('progress-bar');
    progressBar.classList.remove('active', 'progress-striped', 'bar-success', 'bar-danger');
    progressBar.style.width = '0%';
    progressBar.setAttribute('aria-valuenow', 0);
    progressBar.innerText = '0%';
    document.getElementById('dlpercent').innerText = '0%';
    document.getElementById('dlbytesin').innerText = '0';
    document.getElementById('dlbytestotal').innerText = '0';

    JoomlaUpdateDownload.nextFrag = -1;

    window.setTimeout(JoomlaUpdateDownload.step, 50);
  };

  JoomlaUpdateDownload.error = (message) => {
    const progressBar = document.getElementById('progress-bar');
    progressBar.classList.remove('active', 'progress-striped', 'bar-success', 'bar-danger');
    progressBar.classList.add('bar-danger');

    document.getElementById('download-error').style.display = '';
    document.getElementById('dlerror').innerHTML = message;
  };

  JoomlaUpdateDownload.step = () => {
    const startTime = new Date();

    Joomla.request({
      url: `${JoomlaUpdateDownload.ajaxUrl}&frag=${JoomlaUpdateDownload.nextFrag}`,
      method: 'GET',
      onSuccess(msg) {
        let message = '';
        const progressBar = document.getElementById('progress-bar');
        let data = null;

        try {
          data = JSON.parse(msg);
        } catch (e) {
          message = `${e.message}
<br>
<pre>
${msg}
</pre>`;

          JoomlaUpdateDownload.error(message);

          return;
        }

        if (data.error) {
          message = data.message ?? 'Error';

          JoomlaUpdateDownload.error(message);
        }

        if (data.done) {
          progressBar.classList.remove('active', 'progress-striped', 'bar-success', 'bar-danger');
          progressBar.classList.add('bar-success');
          progressBar.style.width = '100%';
          progressBar.setAttribute(['aria-valuenow'], 100);
          progressBar.innerText = '100%';

          window.location = JoomlaUpdateDownload.returnUrl;

          return;
        }

        JoomlaUpdateDownload.nextFrag = data.frag;
        const downloaded = data.downloaded * 1;
        const total = data.totalSize * 1;
        const percentage = (total > 0) ? ((100 * downloaded) / total) : null;

        if (percentage) {
          progressBar.style.width = `${percentage.toFixed(1)}%`;
          progressBar.setAttribute('aria-valuenow', `${percentage.toFixed(1)}`);
          progressBar.innerText = `${percentage.toFixed(1)}%`;
          document.getElementById('dlpercent').innerText = `${percentage.toFixed(1)}%`;
          document.getElementById('dlbytestotal').innerText = `${total}`;
        } else {
          progressBar.style.width = '100%';
          progressBar.innerText = '';
          document.getElementById('dlpercent').innerText = '';
          document.getElementById('dlbytestotal').innerText = '';
        }

        document.getElementById('dlbytesin').innerText = `${downloaded}`;

        const endTime = new Date();
        const timeDiff = endTime - startTime;
        const waitFor = Math.max(JoomlaUpdateDownload.minWait - timeDiff, 50);

        setTimeout(JoomlaUpdateDownload.step, waitFor);
      },
      onError(req) {
        JoomlaUpdateDownload.error(`AJAX Loading Error: ${req.statusText}`);
      },
    });
  };

  // Run on document ready
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('dlrestart').addEventListener('click', JoomlaUpdateDownload.run);
    document.getElementById('dlcancel').addEventListener('click', () => {
      window.location = 'index.php?option=com_joomlaupdate';
    });
    JoomlaUpdateDownload.run();
  });
})(Joomla, document, window);
