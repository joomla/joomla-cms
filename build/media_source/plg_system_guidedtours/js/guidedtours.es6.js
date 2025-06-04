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
  sessionStorage.removeItem('skipTour');
  sessionStorage.removeItem('autoTourId');
}

/**
  Synchronize tour state for this user in their account/profile
  tid = tour ID
  sid = step number (the step the user is on)
  state = state of the tour (completed, skipped, cancelled)
*/
function fetchTourState(tid, sid, context) {
  const fetchUrl = 'index.php?option=com_guidedtours&task=ajax.fetchUserState&format=json';
  Joomla.request({
    url: `${fetchUrl}&tid=${tid}&sid=${sid}&context=${context}`,
    method: 'GET',
    perform: true,
    onSuccess: (response) => {
      try {
        JSON.parse(response);
      } catch (e) {
        Joomla.renderMessages({ error: [Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_TOUR_INVALID_RESPONSE')] }, 'gt');
        return false;
      }
      return true;
    },
    onError: () => {
      Joomla.renderMessages({ error: [Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_TOUR_ERROR_RESPONSE')] });
      return false;
    },
  });
}

/**
 Stop tour on some specific context
  - tour.complete
  - tour.cancel
  - tour.skip       Only autostart tours, to never display again
*/
function stopTour(tour, context) {
  const tid = sessionStorage.getItem('tourId');
  let sid = sessionStorage.getItem('currentStepId');

  if (sid === 'tourinfo') {
    sid = 1;
  } else {
    sid = Number(sid) + 1;
  }

  let trueContext = context;
  if (context === 'tour.cancel' && sessionStorage.getItem('skipTour') === 'true') {
    trueContext = 'tour.skip';
  }

  if (trueContext === 'tour.cancel' || trueContext === 'tour.skip' || trueContext === 'tour.complete') {
    // ajax call to set the user state
    fetchTourState(tid, sid, trueContext);

    // close the tour
    emptyStorage();
    tour.steps = [];

    return true; // cf. https://docs.shepherdpro.com/api/tour/classes/tour/#cancel
  }

  return false; // wrong context
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
    // Test that a tour exists still, it may have already been emptied when skipping the tour
    if (sessionStorage.getItem('tourId')) {
      stopTour(tour, 'tour.cancel');
    }
  });

  return tour;
}

function addProgressIndicator(stepElement, index, total) {
  const header = stepElement.querySelector('.shepherd-header');
  const progress = document.createElement('div');
  progress.classList.add('shepherd-progress', 'badge', 'bg-secondary', 'px-2');
  progress.setAttribute('role', 'status');
  const progressText = document.createElement('span');
  progressText.classList.add('m-0');
  progressText.innerText = Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_STEP_NUMBER_OF').replace('{number}', index).replace('{total}', total);
  progress.appendChild(progressText);
  header.insertBefore(progress, header.querySelector('.shepherd-title'));
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

function enableButton(eventElement) {
  const element = eventElement instanceof Event ? document.querySelector(`.step-next-button-${eventElement.currentTarget.step_id}`) : eventElement;
  element.removeAttribute('disabled');
  element.classList.remove('disabled');
}

function disableButton(eventElement) {
  const element = eventElement instanceof Event ? document.querySelector(`.step-next-button-${eventElement.currentTarget.step_id}`) : eventElement;
  element.setAttribute('disabled', 'disabled');
  element.classList.add('disabled');
}

function addStepToTourButton(tour, stepObj, buttons) {
  const step = new Shepherd.Step(tour, {
    title: stepObj.title,
    text: stepObj.description,
    classes: 'shepherd-theme-arrows',
    buttons,
    id: stepObj.id,
    arrow: true,
    params: stepObj.params,
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

        // if target element doesn't exist e.g. because we have navigated to a new page mid-tour then end the tour here!
        // Take care though since some steps have no target to we check for these too
        if (!target && this.options.attachTo.element) {
          emptyStorage();
          this.cancel();
          return;
        }

        // Force the screen reader to only read the content of the popup after a refresh
        element.setAttribute('aria-live', 'assertive');

        sessionStorage.setItem('currentStepId', this.id);
        addProgressIndicator(element, this.id + 1, sessionStorage.getItem('stepCount'));

        if (target && this.options.attachTo.type === 'interactive') {
          const cancelButton = element.querySelector('.shepherd-cancel-icon');
          const primaryButton = element.querySelector('.shepherd-button-primary');
          const secondaryButton = element.querySelector('.shepherd-button-secondary');

          // Check to see if the 'next' button should be enabled before showing the step based on being required or
          // matching the required value
          switch (this.options.attachTo.interactive_type) {
            case 'text':
              if (
                (target.hasAttribute('required') || (this.options.params.required || 0))
                && (
                  (target.tagName.toLowerCase() === 'input' && ['email', 'password', 'search', 'tel', 'text', 'url'].includes(target.type))
                    || target.tagName.toLowerCase() === 'textarea'
                )
              ) {
                if ((this.options.params.requiredvalue || '') !== '') {
                  if (target.value.trim() === this.options.params.requiredvalue) {
                    enableButton(primaryButton);
                  } else {
                    disableButton(primaryButton);
                  }
                } else if (target.value.trim().length) {
                  enableButton(primaryButton);
                } else {
                  disableButton(primaryButton);
                }
              }
              break;

            case 'checkbox_radio':
              if (
                target.tagName.toLowerCase() === 'input'
                && (target.hasAttribute('required') || (this.options.params.required || 0))
                && ['checkbox', 'radio'].includes(target.type)
              ) {
                if (target.checked) {
                  enableButton(primaryButton);
                } else {
                  disableButton(primaryButton);
                }
              }
              break;

            case 'select':
              if (
                target.tagName.toLowerCase() === 'select'
                && (target.hasAttribute('required') || (this.options.params.required || 0))
              ) {
                if ((this.options.params.requiredvalue || '') !== '') {
                  if (target.value.trim() === this.options.params.requiredvalue) {
                    enableButton(primaryButton);
                  } else {
                    disableButton(primaryButton);
                  }
                } else if (target.value.trim().length) {
                  enableButton(primaryButton);
                } else {
                  disableButton(primaryButton);
                }
              }
              break;

            default:
              break;
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
        params: stepObj.params,
      },
    });
  } else {
    step.updateStepOptions({
      attachTo: {
        url: stepObj.url,
        type: stepObj.type,
        interactive_type: stepObj.interactive_type,
        params: stepObj.params,
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

function addStartButton(tour, buttons, label) {
  buttons.push({
    text: label,
    classes: 'btn btn-primary shepherd-button-primary',
    action() {
      return this.next();
    },
  });
}

function addSkipButton(tour, buttons) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_HIDE_FOREVER'),
    classes: 'btn btn-secondary shepherd-button-secondary',
    action() {
      sessionStorage.setItem('skipTour', 'true');
      return this.cancel();
    },
  });
}

function showTourInfo(tour, stepObj) {
  const buttons = [];
  if (sessionStorage.getItem('autoTourId') === sessionStorage.getItem('tourId')) {
    addSkipButton(tour, buttons);
  }
  addStartButton(tour, buttons, stepObj.start_label);

  tour.addStep({
    title: stepObj.title,
    text: stepObj.description,
    classes: 'shepherd-theme-arrows',
    buttons,
    id: 'tourinfo',
    when: {
      show() {
        sessionStorage.setItem('currentStepId', 'tourinfo');
        sessionStorage.setItem('skipTour', 'false');
        addProgressIndicator(this.getElement(), 1, sessionStorage.getItem('stepCount'));
      },
    },
  });
}

function pushCompleteButton(tour, buttons) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_COMPLETE'),
    classes: 'btn btn-primary shepherd-button-primary',
    action() {
      stopTour(tour, 'tour.complete');
      return this.complete();
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

  // Now let's add all followup steps
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
      if (typeof obj.steps[index].params === 'string' && obj.steps[index].params !== '') {
        obj.steps[index].params = JSON.parse(obj.steps[index].params);
      } else {
        obj.steps[index].params = [];
      }

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
              if (
                (ele.hasAttribute('required') || (obj.steps[index].params.required || 0))
                && (
                  (ele.tagName.toLowerCase() === 'input' && ['email', 'password', 'search', 'tel', 'text', 'url'].includes(ele.type))
                  || ele.tagName.toLowerCase() === 'textarea'
                )
              ) {
                ['input', 'focus'].forEach((eventName) => ele.addEventListener(eventName, (event) => {
                  if (!sessionStorage.getItem('tourId')) {
                    return;
                  }
                  if ((obj.steps[index].params.requiredvalue || '') !== '') {
                    if (event.target.value.trim() === obj.steps[index].params.requiredvalue) {
                      enableButton(event);
                    } else {
                      disableButton(event);
                    }
                  } else if (event.target.value.trim().length) {
                    enableButton(event);
                  } else {
                    disableButton(event);
                  }
                }));
              }
              break;

            case 'checkbox_radio':
              ele.step_id = index;
              if (
                ele.tagName.toLowerCase() === 'input'
                && (ele.hasAttribute('required') || (obj.steps[index].params.required || 0))
                && ['checkbox', 'radio'].includes(ele.type)
              ) {
                ['click'].forEach((eventName) => ele.addEventListener(eventName, (event) => {
                  if (event.target.checked) {
                    enableButton(event);
                  } else {
                    disableButton(event);
                  }
                }));
              }
              break;

            case 'select':
              ele.step_id = index;
              if (
                ele.tagName.toLowerCase() === 'select'
                && (ele.hasAttribute('required') || (obj.steps[index].params.required || 0))
              ) {
                ['change'].forEach((eventName) => ele.addEventListener(eventName, (event) => {
                  if ((obj.steps[index].params.requiredvalue || '') !== '') {
                    if (event.target.value.trim() === obj.steps[index].params.requiredvalue) {
                      enableButton(event);
                    } else {
                      disableButton(event);
                    }
                  } else if (event.target.value.trim().length) {
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
        || (obj && ['text', 'checkbox_radio', 'select', 'other'].includes(obj.steps[index].interactive_type))
      ) {
        pushNextButton(buttons, obj.steps[index]);
      }
    } else {
      pushCompleteButton(obj, buttons);
    }

    addStepToTourButton(tour, obj.steps[index], buttons);
    prevStep = obj.steps[index];
  }

  tour.start();
}

function loadTour(tourId) {
  const tourUid = Number.parseInt(tourId, 10) > 0 ? '' : encodeURI(tourId);
  const tourNumber = Number.parseInt(tourId, 10) > 0 ? Number.parseInt(tourId, 10) : 0;

  if (tourNumber > 0 || tourUid !== '') {
    let url = `${Joomla.getOptions('system.paths').rootFull}administrator/index.php?option=com_ajax&plugin=guidedtours&group=system&format=json`;
    if (tourNumber > 0) {
      url += `&id=${tourNumber}`;
    } else {
      url += `&uid=${tourUid}`;
    }
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
  if (
    (!event.target.hasAttribute('data-id') || event.target.getAttribute('data-id') <= 0)
  && (!event.target.hasAttribute('data-gt-uid') || event.target.getAttribute('data-gt-uid') === '')
  ) {
    Joomla.renderMessages({ error: [Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR')] });
    return;
  }

  sessionStorage.setItem('tourToken', String(Joomla.getOptions('com_guidedtours.token')));
  loadTour(event.target.getAttribute('data-id') || event.target.getAttribute('data-gt-uid'));
});

// Start a given tour
let tourId = sessionStorage.getItem('tourId');

// Autostart tours have priority
if (Joomla.getOptions('com_guidedtours.autotour', '') !== '') {
  sessionStorage.setItem('tourToken', String(Joomla.getOptions('com_guidedtours.token')));
  sessionStorage.setItem('autoTourId', String(Joomla.getOptions('com_guidedtours.autotour')));
  tourId = Joomla.getOptions('com_guidedtours.autotour');
}

if ((Number.parseInt(tourId, 10) > 0 || tourId !== '') && sessionStorage.getItem('tourToken') === String(Joomla.getOptions('com_guidedtours.token'))) {
  loadTour(tourId);
} else {
  emptyStorage();
}
