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
  sessionStorage.removeItem('tourToken');
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
  const progress = document.createElement('div');
  progress.classList.add('shepherd-progress');
  progress.setAttribute('role', 'status');
  progress.setAttribute('aria-label', Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_STEP_NUMBER_OF').replace('{number}', index).replace('{total}', total));
  const progressText = document.createElement('span');
  progressText.setAttribute('aria-hidden', true);
  progressText.innerText = `${index}/${total}`;
  progress.appendChild(progressText);
  header.insertBefore(progress, stepElement.querySelector('.shepherd-cancel-icon'));
}

function setFocus(primaryButton, secondaryButton, cancelButton) {
  if (primaryButton && !primaryButton.disabled) {
    primaryButton.focus();
  } else if (secondaryButton && !secondaryButton.disabled) {
    secondaryButton.focus();
  } else {
    cancelButton.focus();
  }
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
      }).catch(() => {
        // Ignore
      });
    },
    when: {
      show() {
        const element = this.getElement();
        const target = this.getTarget();

        // Force the screen reader to only read the content of the popup after a refresh
        element.setAttribute('aria-live', 'assertive');

        sessionStorage.setItem('currentStepId', this.id);
        addProgressIndicator(element, this.id + 1, sessionStorage.getItem('stepCount'));

        if (target && this.options.attachTo.type === 'interactive') {
          const cancelButton = element.querySelector('.shepherd-cancel-icon');
          const primaryButton = element.querySelector('.shepherd-button-primary');
          const secondaryButton = element.querySelector('.shepherd-button-secondary');

          // The 'next' button should always be enabled if the target input field of type 'text' has a value
          if (
            target.tagName.toLowerCase() === 'input'
            && target.hasAttribute('required')
            && (['email', 'password', 'search', 'tel', 'text', 'url'].includes(target.type))
          ) {
            if (target.value.trim().length) {
              primaryButton.removeAttribute('disabled');
              primaryButton.classList.remove('disabled');
            } else {
              primaryButton.setAttribute('disabled', 'disabled');
              primaryButton.classList.add('disabled');
            }
          }

          cancelButton.addEventListener('keydown', (event) => {
            if (event.key === 'Tab') {
              if (target.tagName.toLowerCase() === 'joomla-field-fancy-select') {
                target.querySelector('.choices').click();
                target.querySelector('.choices input').focus();
              } else if (target.parentElement.tagName.toLowerCase() === 'joomla-field-fancy-select') {
                target.click();
                target.querySelector('input').focus();
              } else {
                target.focus();
                event.preventDefault();
              }
            }
          });

          if (target.tagName.toLowerCase() === 'iframe') {
            // Give blur to the content of the iframe, as iframes don't have blur events
            target.contentWindow.document.body.addEventListener('blur', (event) => {
              if (!sessionStorage.getItem('tourId')) {
                return;
              }
              setTimeout(() => {
                setFocus(primaryButton, secondaryButton, cancelButton);
              }, 1);
              event.preventDefault();
            });
          } else if (target.tagName.toLowerCase() === 'joomla-field-fancy-select') {
            target.querySelector('.choices input').addEventListener('blur', (event) => {
              if (!sessionStorage.getItem('tourId')) {
                return;
              }
              setFocus(primaryButton, secondaryButton, cancelButton);
              event.preventDefault();
            });
          } else if (target.parentElement.tagName.toLowerCase() === 'joomla-field-fancy-select') {
            target.querySelector('input').addEventListener('blur', (event) => {
              if (!sessionStorage.getItem('tourId')) {
                return;
              }
              setFocus(primaryButton, secondaryButton, cancelButton);
              event.preventDefault();
            });
          } else {
            target.addEventListener('blur', (event) => {
              if (!sessionStorage.getItem('tourId')) {
                return;
              }
              setFocus(primaryButton, secondaryButton, cancelButton);
              event.preventDefault();
            });
          }
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
        classes: 'btn btn-primary shepherd-button-primary',
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
    classes: 'btn btn-primary shepherd-button-primary',
    action() {
      return this.cancel();
    },
  });
}

function pushNextButton(buttons, step, disabled = false, disabledClass = '') {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_NEXT'),
    classes: `btn btn-primary shepherd-button-primary step-next-button-${step.id} ${disabledClass}`,
    action() {
      return this.next();
    },
    disabled,
  });
}

function addBackButton(buttons, step) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_BACK'),
    classes: 'btn btn-secondary shepherd-button-secondary',
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
                if (!sessionStorage.getItem('tourId')) {
                  return;
                }
                sessionStorage.setItem('currentStepId', obj.steps[index].id + 1);
              });
              break;

            case 'text':
              ele.step_id = index;
              if (ele.hasAttribute('required') && ['email', 'password', 'search', 'tel', 'text', 'url'].includes(ele.type)) {
                ['input', 'focus'].forEach((eventName) => ele.addEventListener(eventName, (event) => {
                  if (!sessionStorage.getItem('tourId')) {
                    return;
                  }
                  if (event.target.value.trim().length) {
                    enableButton(event);
                  } else {
                    disableButton(event);
                  }
                }));
              }
              break;

            case 'button':
              ele.addEventListener('click', () => {
                // the button may submit a form so record the currentStepId in the session storage
                sessionStorage.setItem('currentStepId', obj.steps[index].id + 1);
                tour.next();
              });
              break;

            case 'other':
            default:
              break;
          }
        }
      }
    }

    if (index < len - 1) {
      if (
        (obj && obj.steps[index].type !== 'interactive')
        || (obj && obj.steps[index].interactive_type === 'text')
        || (obj && obj.steps[index].interactive_type === 'other')
      ) {
        pushNextButton(buttons, obj.steps[index]);
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
        // Kill the tour if there is a problem with selector validation
        emptyStorage();

        const messages = { error: [Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_TOUR_ERROR')] };
        Joomla.renderMessages(messages);

        throw new Error(error);
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
    Joomla.renderMessages({ error: [Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR')] });
    return;
  }

  sessionStorage.setItem('tourToken', String(Joomla.getOptions('com_guidedtours.token')));
  loadTour(event.target.getAttribute('data-id'));
});

// Start a given tour
const tourId = sessionStorage.getItem('tourId');

if (tourId > 0 && sessionStorage.getItem('tourToken') === String(Joomla.getOptions('com_guidedtours.token'))) {
  loadTour(tourId);
} else {
  emptyStorage();
}
