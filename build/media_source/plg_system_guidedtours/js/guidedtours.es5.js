/**
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function checkAndRedirect(redirectUrl) {
  const currentUrl = window.location.href;
  if (currentUrl !== redirectUrl) {
    window.location.href = redirectUrl;
  }
}

function getTourInstance() {
  const tour = new Shepherd.Tour({
    defaultStepOptions: {
      cancelIcon: {
        enabled: true,
        label: Joomla.Text._('JCANCEL'),
      },
      classes: 'shepherd-theme-arrows',
      scrollTo: {
        behavior: 'smooth',
        block: 'center',
      },
    },
    useModalOverlay: true,
    keyboardNavigation: true,
  });

  tour.on('cancel', () => {
    emptyStorage();

    tour.steps = [];
  });

  return tour;
}

function emptyStorage() {
  sessionStorage.removeItem('currentStepId');
  sessionStorage.removeItem('stepCount');
  sessionStorage.removeItem('tourId');
  sessionStorage.removeItem('previousStepUrl');
}

function addProgressIndicator(stepElement, index, total) {
  const header = stepElement.querySelector('.shepherd-header');
  let progress = document.createElement('span');
  progress.classList.add('shepherd-progress');
  progress.innerText = `${index}/${total}`;
  header.insertBefore(progress, stepElement.querySelector('.shepherd-cancel-icon'));
}

function addStepToTourButton(tour, step_obj, buttons) {
  let step = new Shepherd.Step(tour, {
    title: step_obj.title,
    text: step_obj.description,
    classes: 'shepherd-theme-arrows',
    buttons: buttons,
    id: step_obj.id,
    arrow: true,
    beforeShowPromise: function() {
      return new Promise(function(resolve) {
        if (tour.currentStep.options.attachTo.type === 'redirect') {
          const stepUrl = Joomla.getOptions('system.paths').rootFull + tour.currentStep.options.attachTo.url;
          if (window.location.href !== stepUrl) {
            sessionStorage.setItem('currentStepId', tour.currentStep.id);
            sessionStorage.setItem('previousStepUrl', window.location.href);
            window.location.href = stepUrl;
          } else {
            resolve();
          }
        } else {
          resolve();
        }
      });
    },
    when: {
      show() {
        sessionStorage.setItem('currentStepId', tour.currentStep.id);
        addProgressIndicator(this.getElement(), tour.currentStep.id + 1, sessionStorage.getItem('stepCount'));

        this.getElement().focus = () => {

          const tabbed_elements = document.querySelectorAll('[tabindex]');
          tabbed_elements.forEach(function(elt) {
            elt.setAttribute('tabindex', '-1');
          });

          let tabIndex = 0;
          const target = tour.currentStep.getTarget();

          if (target) {
            target.focus();
            target.tabIndex = ++tabIndex;
          }

          const popup_buttons = tour.currentStep.getElement().querySelectorAll('.shepherd-content button');
          popup_buttons.forEach(function(elt, index) {
            elt.setAttribute('tabindex', popup_buttons.length + tabIndex - index); // loose tab on 'back'
          });
        }
      },
    },
  });

  if (step_obj.target) {
    step.updateStepOptions({
      attachTo: {
        element: step_obj.target,
        on: step_obj.position,
        url: step_obj.url,
        type: step_obj.type,
        interactive_type: step_obj.interactive_type,
      },
    });
  } else {
    step.updateStepOptions({
      attachTo: {
        url: step_obj.url,
        type: step_obj.type,
        interactive_type: step_obj.interactive_type,
      },
    });
  }

  if (step_obj.type !== 'next') {
    // Remove stored key to prevent pages to open in the wrong tab
    const storageKey = Joomla.getOptions('system.paths').root + '/' + step_obj.url;
    if (sessionStorage.getItem(storageKey)) {
      sessionStorage.removeItem(storageKey);
    }
  }

  tour.addStep(step);
}

function showTourInfo(tour, step_obj) {
  tour.addStep({
    title: step_obj.title,
    text: step_obj.description,
    classes: 'shepherd-theme-arrows',
    buttons: [
      {
        classes: 'shepherd-button-primary',
        action() {
          return this.next();
        },
        text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_START'),
      },
    ],
    id: 0,
    when: {
      show() {
        sessionStorage.setItem('currentStepId', '0');
        addProgressIndicator(this.getElement(), 1, sessionStorage.getItem('stepCount'));
      },
    },
  });
}

function pushCompleteButton(buttons) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_COMPLETE'),
    classes: 'shepherd-button-primary',
    action: function () {
      return this.cancel();
    },
  });
}

function pushNextButton(buttons, step, disabled = false) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_NEXT'),
    classes: `shepherd-button-primary step-next-button-${step.id}`,
    action: function () {
      return this.next();
    },
    disabled: disabled,
  });
}

function addBackButton(buttons, step) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_BACK'),
    classes: 'shepherd-button-secondary',
    action: function () {
      if (step.type === 'redirect') {
        sessionStorage.setItem('currentStepId', step.id - 1);
        const previousStepUrl = sessionStorage.getItem('previousStepUrl');
        sessionStorage.removeItem('previousStepUrl');
        window.location.href = previousStepUrl;
        }
      return this.back();
    },
  });
}

function enableButton(event) {
  const element = document.querySelector(`.step-next-button-${event.currentTarget.step_id}`);
  element.removeAttribute('disabled');
}
function disableButton(event) {
  const element = document.querySelector(`.step-next-button-${event.currentTarget.step_id}`);
  element.setAttribute('disabled', 'disabled');
}

function startTour(obj) {
  // We store the tour id to restart on site refresh
  sessionStorage.setItem('tourId', obj.id);
  sessionStorage.setItem('stepCount', String(obj.steps.length));

  // Try to continue
  const currentStepId = sessionStorage.getItem('currentStepId');
  let prevStep = null;

  let ind = -1;

  if (currentStepId != null && Number(currentStepId) > -1) {
    ind = typeof obj.steps[currentStepId] != 'undefined' ? Number(currentStepId) : -1;
    // When we have more than one step, we save the previous step
    if (ind > 0) {
      prevStep = obj.steps[ind - 1];
    }
  }

  // Start tour building
  const tour = getTourInstance();

  // No step found, let's start from the beginning
  if (ind < 0) {

    // First check for redirect
    const uri = Joomla.getOptions('system.paths').rootFull;
    const currentUrl = window.location.href;

    if (currentUrl !== uri + obj.steps[0].url) {
      window.location.href = uri + obj.steps[0].url;

      return;
    }

    // Show info
    showTourInfo(tour, obj.steps[0]);
    ind = 1;
  }

  // Now let's add all follow up steps
  const len = obj.steps.length;
  let buttons;

  // eslint-disable-next-line no-plusplus
  for (let index = ind; index < len; index++) {
    buttons = [];

    // If we have at least done one step, let's allow a back step
    // - if after the start step
    // - if not the first step after a form redirect
    // - if after a simple redirect
    if (prevStep === null || index > ind || obj.steps[index].type === 'redirect') {
      addBackButton(buttons, obj.steps[index]);
    }

    if (
      obj
      && obj.steps[index].target
      && obj.steps[index].type === 'interactive'
    ) {
      const ele = document.querySelector(obj.steps[index].target);
      if (ele) {
        if (obj && obj.steps && obj.steps[index] && obj.steps[index].interactive_type) {
          switch (obj.steps[index].interactive_type) {
            case 'submit':
              ele.addEventListener('click', () => {
                sessionStorage.setItem('currentStepId', obj.steps[index].id + 1);
              });
              break;

            case 'text':
              ele.step_id = index;
              ele.addEventListener('input', event => {
                if (event.target.value.trim().length) {
                  enableButton(event);
                }
                else {
                  disableButton(event);
                }
              });
              break;

            case 'button':
              tour.next();
              break;

            case 'other':
              break;
          }
        }
      }
    }

    if (index < len - 1) {
      let disabled = false;
      if (obj && obj.steps[index].interactive_type === 'text') {
        disabled = true;
      }
      if (
        (obj && obj.steps[index].type !== 'interactive')
        || (obj && obj.steps[index].interactive_type === 'text')
        || (obj && obj.steps[index].interactive_type === 'other')
      ) {
        pushNextButton(buttons, obj.steps[index], disabled);
      }
    } else {
      pushCompleteButton(buttons);
    }

    addStepToTourButton(tour, obj.steps[index], buttons);
    prevStep = obj.steps[index];
  }

  tour.start();
}

Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {

    function loadTour(tourId) {
      if (tourId > 0) {
        const url = `${Joomla.getOptions('system.paths').rootFull}administrator/index.php?option=com_ajax&plugin=guidedtours&group=system&format=json&id=${tourId}`;
        fetch(url)
          .then((response) => response.json())
          .then((result) => {
            if (!result.success) {
              if (result.messages) {
                Joomla.renderMessages(result.messages);
              }

              // Kill all tours if we can't find it
              emptyStorage();
            }
            startTour(result.data);
          })
          .catch((error) => console.error(error));
      }
    }

    // Opt-in Start buttons
    document.querySelector('body').addEventListener('click', event => {

      // Click somewhere else
      if (!event.target || !event.target.classList.contains('button-start-guidedtour')) {
        return;
      }

      // Click button but missing data-id
      if (typeof event.target.getAttribute('data-id') == 'undefined' || event.target.getAttribute('data-id') <= 0) {
        Joomla.renderMessages([Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR')]);

        return;
      }

      loadTour(event.target.getAttribute('data-id'));
    });

    // Start a given tour
    const tourId = sessionStorage.getItem('tourId');

    if (tourId > 0) {
      loadTour(tourId);
    }
  });
})(Joomla, document);
