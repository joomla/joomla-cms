/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((window, document, Joomla) => {
  'use strict'; // eslint-disable-next-line no-unused-expressions,func-names

  Joomla.SampleData = {
    inProgress: false
  };

  Joomla.sampledataAjax = (type, steps, step) => {
    // Get variables
    const baseUrl = `index.php?option=com_ajax&format=json&group=sampledata&${Joomla.getOptions('csrf.token')}=1`;
    const options = Joomla.getOptions('sample-data'); // Create list

    const list = document.createElement('li');
    list.classList.add(`sampledata-steps-${type}-${step}`); // Create paragraph

    const para = document.createElement('p');
    para.classList.add('loader-image');
    para.classList.add('text-center'); // Create image

    const img = document.createElement('img');
    img.setAttribute('src', options.icon);
    img.setAttribute('width', 30);
    img.setAttribute('height', 30); // Append everything

    para.appendChild(img);
    list.appendChild(para);
    document.querySelector(`.sampledata-progress-${type} ul`).appendChild(list);
    Joomla.request({
      url: `${baseUrl}&type=${type}&plugin=SampledataApplyStep${step}&step=${step}`,
      method: 'GET',
      perform: true,
      onSuccess: resp => {
        // Remove loader image
        const loader = list.querySelector('.loader-image');
        loader.parentNode.removeChild(loader);
        let response = {};

        try {
          response = JSON.parse(resp);
        } catch (e) {
          Joomla.renderMessages({
            error: [Joomla.JText._('MOD_SAMPLEDATA_INVALID_RESPONSE')]
          }, `.sampledata-steps-${type}-${step}`);
          Joomla.SampleData.inProgress = false;
          return;
        }

        let progressClass = '';
        let success;

        if (response.success && response.data && response.data.length > 0) {
          const progress = document.querySelector(`.sampledata-progress-${type} .progress-bar`); // Display all messages that we got

          response.data.forEach(value => {
            if (value === null) {
              return;
            } // eslint-disable-next-line prefer-destructuring


            success = value.success;
            progressClass = success ? 'bg-success' : 'bg-danger'; // Display success alert

            if (success) {
              Joomla.renderMessages({
                message: [value.message]
              }, `.sampledata-steps-${type}-${step}`);
            } else {
              Joomla.renderMessages({
                error: [value.message]
              }, `.sampledata-steps-${type}-${step}`);
            }
          }); // Update progress

          progress.innerText = `${step}/${steps}`;
          progress.style.width = `${step / steps * 100}%`;
          progress.setAttribute('aria-valuemin', 0);
          progress.setAttribute('aria-valuemax', 100);
          progress.setAttribute('aria-valuenow', step / steps * 100);
          progress.classList.add(progressClass); // Move on next step

          if (success && step <= steps) {
            const stepNew = step + 1;

            if (stepNew <= steps) {
              Joomla.sampledataAjax(type, steps, stepNew);
            }
          }
        } else {
          // Display error alert
          Joomla.renderMessages({
            error: [Joomla.JText._('MOD_SAMPLEDATA_INVALID_RESPONSE')]
          }, `.sampledata-steps-${type}-${step}`);
          Joomla.SampleData.inProgress = false;
        }
      },
      onError: () => {
        alert('Something went wrong! Please close and reopen the browser and try again!');
      }
    });
  };

  Joomla.sampledataApply = element => {
    const type = element.getAttribute('data-type');
    const steps = element.getAttribute('data-steps'); // Check whether the work in progress or we already processed with current item

    if (Joomla.SampleData.inProgress) {
      return;
    }

    if (element.getAttribute('data-processed')) {
      alert(Joomla.JText._('MOD_SAMPLEDATA_ITEM_ALREADY_PROCESSED'));
      return;
    } // Make sure that use run this not by random clicking on the page links
    // @todo use the CE Modal here


    if (!window.confirm(Joomla.JText._('MOD_SAMPLEDATA_CONFIRM_START'))) {
      // eslint-disable-next-line consistent-return
      return false;
    } // Turn on the progress container


    const progressElements = [].slice.call(document.querySelectorAll(`.sampledata-progress-${type}`));
    progressElements.forEach(progressElement => {
      progressElement.classList.remove('d-none');
    });
    element.getAttribute('data-processed', true);
    Joomla.SampleData.inProgress = true;
    Joomla.sampledataAjax(type, steps, 1); // eslint-disable-next-line consistent-return

    return false;
  };

  const onBoot = () => {
    const sampleDataWrapper = document.getElementById('sample-data-wrapper');

    if (sampleDataWrapper) {
      const links = [].slice.call(sampleDataWrapper.querySelectorAll('.apply-sample-data'));
      links.forEach(link => {
        link.addEventListener('click', ({
          target
        }) => Joomla.sampledataApply(target));
      });
    } // Cleanup


    document.removeEventListener('DOMContentLoaded', onBoot);
  }; // Initialise


  document.addEventListener('DOMContentLoaded', onBoot);
})(window, document, window.Joomla);