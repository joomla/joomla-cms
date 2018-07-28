/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};
((Joomla, document) => {
  'use strict';

  let inProgress = false;

  Joomla.sampledataAjax = (type, steps, step) => {
    // Get variables
    const baseUrl = 'index.php?option=com_ajax&format=json&group=sampledata';
    const options = Joomla.getOptions('sample-data');

    // Create list
    const list = document.createElement('li');
    list.classList.add(`sampledata-steps-${type}-${step}`);

    // Create paragraph
    const para = document.createElement('p');
    para.classList.add('loader-image');
    para.classList.add('text-center');

    // Create image
    const img = document.createElement('img');
    img.setAttribute('src', options.icon);
    img.setAttribute('width', 30);
    img.setAttribute('height', 30);

    // Append everything
    para.appendChild(img);
    list.appendChild(para);
    document.querySelector(`.sampledata-progress-${type} ul`).appendChild(list);

    Joomla.request({
      url: `${baseUrl}&type=${type}&plugin=SampledataApplyStep${step}&step=${step}`,
      method: 'GET',
      perform: true,
      onSuccess: (resp) => {
        const response = JSON.parse(resp);
        // Remove loader image
        const loader = list.querySelector('.loader-image');
        loader.parentNode.removeChild(loader);

        if (response.success && response.data && response.data.length > 0) {
          let success;
          let value;
          let progressClass;
          const progress = document.querySelector(`.sampledata-progress-${type} .progress-bar`);

          // Display all messages that we got
          [].slice.call(response.data).forEach((entry) => {
            value = entry;

            if (value === null) {
              return;
            }

            success = value.success;
            progressClass = success ? 'bg-success' : 'bg-danger';

            // Display success alert
            if (success) {
              Joomla.renderMessages({ success: [value.message] }, `.sampledata-steps-${type}-${step}`);
            } else {
              Joomla.renderMessages({ error: [value.message] }, `.sampledata-steps-${type}-${step}`);
            }
          });

          // Update progress
          progress.innerText = `${step}/${steps}`;
          progress.style.width = `${(step / steps) * 100}%`;
          progress.classList.add(progressClass);

          // Move on next step
          if (success && (step <= steps)) {
            // eslint-disable-next-line no-param-reassign
            step += 1;
            if (step <= steps) {
              Joomla.sampledataAjax(type, steps, step);
            }
          }
        } else {
          // Display error alert
          Joomla.renderMessages({ error: [Joomla.JText._('MOD_SAMPLEDATA_INVALID_RESPONSE')] }, `.sampledata-steps-${type}-${step}`);

          inProgress = false;
        }
      },
      onError: () => {
        alert('Something went wrong! Please close and reopen the browser and try again!');
      },
    });
  };

  Joomla.sampledataApply = (el) => {
    const type = el.getAttribute('data-type');
    const steps = el.getAttribute('data-steps');

    // Check whether the work in progress or we already processed with current item
    if (inProgress) {
      return;
    }

    if (el.getAttribute('data-processed')) {
      alert(Joomla.JText._('MOD_SAMPLEDATA_ITEM_ALREADY_PROCESSED'));
      return;
    }

    // Make sure that use run this not by random clicking on the page links
    if (!confirm(Joomla.JText._('MOD_SAMPLEDATA_CONFIRM_START'))) {
      // eslint-disable-next-line consistent-return
      return false;
    }

    // Turn on the progress container
    const progress = [].slice.call(document.querySelectorAll(`.sampledata-progress-${type}`));
    progress.forEach((element) => {
      element.classList.remove('d-none');
    });

    el.getAttribute('data-processed', true);

    inProgress = true;
    Joomla.sampledataAjax(type, steps, 1);
    // eslint-disable-next-line consistent-return
    return false;
  };

  document.addEventListener('DOMContentLoaded', () => {
    const sampleDataWrapper = document.getElementById('sample-data-wrapper');
    if (sampleDataWrapper) {
      const links = [].slice.call(sampleDataWrapper.querySelectorAll('.apply-sample-data'));
      links.forEach((link) => {
        link.addEventListener('click', (e) => {
          Joomla.sampledataApply(e.target);
        });
      });
    }
  });
})(Joomla, document);
