/**
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

import Shepherd from 'shepherd.js';

if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

function emptyStorage() {
  sessionStorage.removeItem('currentStepId');
  sessionStorage.removeItem('stepCount');
  sessionStorage.removeItem('tourId');
  sessionStorage.removeItem('previousStepUrl');
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

function addProgressIndicator(stepElement, index, total) {
  const header = stepElement.querySelector('.shepherd-header');
  const progress = document.createElement('span');
  progress.classList.add('shepherd-progress');
  progress.innerText = `${index}/${total}`;
  header.insertBefore(progress, stepElement.querySelector('.shepherd-cancel-icon'));
}

function addStepToTourButton(tour, stepObj, buttons) {
  const step = new Shepherd.Step(tour, {
    title: stepObj.title,
    text: stepObj.description,
    classes: 'shepherd-theme-arrows',
    buttons,
    id: stepObj.id,
    arrow: true,
    beforeShowPromise() {
      return new Promise((resolve) => {
        // Set graceful fallbacks in case there is an issue with the target.
        // Possibility to use comma-separated selectors.
        if (tour.currentStep.options.attachTo.element) {
          const targets = tour.currentStep.options.attachTo.element.split(',');
          const position = tour.currentStep.options.attachTo.on;
          tour.currentStep.options.attachTo.element = '';
          tour.currentStep.options.attachTo.on = 'center';

          for (let i = 0; i < targets.length; i += 1) {
            const t = document.querySelector(targets[i]);
            if (t != null) {
              // Use TinyMCE in code source to keep the step accessible
              if (t.parentElement.querySelector('.js-tiny-toggler-button') != null) {
                t.parentElement.querySelector('.js-tiny-toggler-button').click();
                tour.currentStep.options.attachTo.element = targets[i];
                tour.currentStep.options.attachTo.on = position;
                break;
              }
              if (!t.disabled && !t.readonly && t.style.display !== 'none') {
                tour.currentStep.options.attachTo.element = targets[i];
                tour.currentStep.options.attachTo.on = position;
                break;
              }
            }
          }
        }
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
      }).catch((error) => {
        console.log(`Exception error - ${error.message} - Bypass Shepherd target`);
      });
    },
    when: {
      hide() {
        if (this.getTarget()) {
          const toggleButton = this.getTarget().parentElement.querySelector('.js-tiny-toggler-button');
          if (toggleButton != null) {
            // Switch back to the full TinyMCE editor
            toggleButton.click();
          }
        }
      },
      show() {
        sessionStorage.setItem('currentStepId', this.id);
        addProgressIndicator(this.getElement(), this.id + 1, sessionStorage.getItem('stepCount'));

        if (this.getTarget()) {
          this.getElement().querySelector('.shepherd-cancel-icon').addEventListener('keydown', (event) => {
            if (event.key === 'Tab') {
              this.getTarget().focus();
              event.preventDefault();
            }
          });
          this.getTarget().addEventListener('blur', (event) => {
            const cancelButton = this.getElement().querySelector('.shepherd-cancel-icon');
            const primaryButton = this.getElement().querySelector('.shepherd-button-primary');
            const secondaryButton = this.getElement().querySelector('.shepherd-button-secondary');
            if (primaryButton && !primaryButton.disabled) {
              primaryButton.focus();
            } else if (secondaryButton && !secondaryButton.disabled) {
              secondaryButton.focus();
            } else {
              cancelButton.focus();
            }
            event.preventDefault();
          });
        }
      },
    },
  });

  if (stepObj.target) {
    step.updateStepOptions({
      attachTo: {
        element: stepObj.target,
        on: stepObj.position,
        url: stepObj.url,
        type: stepObj.type,
        interactive_type: stepObj.interactive_type,
      },
    });
  } else {
    step.updateStepOptions({
      attachTo: {
        url: stepObj.url,
        type: stepObj.type,
        interactive_type: stepObj.interactive_type,
      },
    });
  }

  if (stepObj.type !== 'next') {
    // Remove stored key to prevent pages to open in the wrong tab
    const storageKey = `${Joomla.getOptions('system.paths').root}/${stepObj.url}`;
    if (sessionStorage.getItem(storageKey)) {
      sessionStorage.removeItem(storageKey);
    }
  }

  tour.addStep(step);
}

function showTourInfo(tour, stepObj) {
  tour.addStep({
    title: stepObj.title,
    text: stepObj.description,
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
    id: 'tourinfo',
    when: {
      show() {
        sessionStorage.setItem('currentStepId', 'tourinfo');
        addProgressIndicator(this.getElement(), 1, sessionStorage.getItem('stepCount'));
      },
    },
  });
}

function pushCompleteButton(buttons) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_COMPLETE'),
    classes: 'shepherd-button-primary',
    action() {
      return this.cancel();
    },
  });
}

function pushNextButton(buttons, step, disabled = false, disabledClass = '') {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_NEXT'),
    classes: `shepherd-button-primary step-next-button-${step.id} ${disabledClass}`,
    action() {
      return this.next();
    },
    disabled,
  });
}

function addBackButton(buttons, step) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_BACK'),
    classes: 'shepherd-button-secondary',
    action() {
      if (step.type === 'redirect') {
        sessionStorage.setItem('currentStepId', step.id - 1);
        const previousStepUrl = sessionStorage.getItem('previousStepUrl');
        if (previousStepUrl) {
          sessionStorage.removeItem('previousStepUrl');
          window.location.href = previousStepUrl;
        }
      }
      return this.back();
    },
  });
}

function enableButton(event) {
  const element = document.querySelector(`.step-next-button-${event.currentTarget.step_id}`);
  element.removeAttribute('disabled');
  element.classList.remove('disabled');
}
function disableButton(event) {
  const element = document.querySelector(`.step-next-button-${event.currentTarget.step_id}`);
  element.setAttribute('disabled', 'disabled');
  element.classList.add('disabled');
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
    ind = typeof obj.steps[currentStepId] !== 'undefined' ? Number(currentStepId) : -1;
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

  for (let index = ind; index < len; index += 1) {
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
              ele.addEventListener('input', (event) => {
                if (event.target.value.trim().length) {
                  enableButton(event);
                } else {
                  disableButton(event);
                }
              });
              break;

            case 'button':
              tour.next();
              break;

            case 'other':
            default:
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
        pushNextButton(buttons, obj.steps[index], disabled, disabled ? 'disabled' : '');
      }
    } else {
      pushCompleteButton(buttons);
    }

    addStepToTourButton(tour, obj.steps[index], buttons);
    prevStep = obj.steps[index];
  }

  tour.start();
}

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
      .catch((error) => {
        throw new Error(error)
      });
  }
}

// Opt-in Start buttons
document.querySelector('body').addEventListener('click', (event) => {
  // Click somewhere else
  if (!event.target || !event.target.classList.contains('button-start-guidedtour')) {
    return;
  }

  // Click button but missing data-id
  if (typeof event.target.getAttribute('data-id') === 'undefined' || event.target.getAttribute('data-id') <= 0) {
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
