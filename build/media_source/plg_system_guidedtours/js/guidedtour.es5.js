/**
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function checkAndRedirect(redirectUrl) {
  const currentURL = window.location.href;
  if (currentURL !== redirectUrl) {
    window.location.href = redirectUrl;
  }
}

function getTourInstance() {
  const tour = new Shepherd.Tour({
    defaultStepOptions: {
      cancelIcon: {
        enabled: true,
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
}

function addProgressIndicator(stepElement, index, total) {
  const header = stepElement.querySelector('.shepherd-header');
  let progress = document.createElement('span');
  progress.classList.add('shepherd-progress');
  progress.innerText = `${index}/${total}`;
  header.insertBefore(progress, stepElement.querySelector('.shepherd-cancel-icon'));
}

function addStepToTourButton(tour, obj, index, buttons) {
  tour.addStep({
    title: obj.steps[index].title,
    text: obj.steps[index].description,
    classes: 'shepherd-theme-arrows',
    attachTo: {
      element: obj.steps[index].target,
      on: obj.steps[index].position,
      url: obj.steps[index].url,
      type: obj.steps[index].type,
      interactive_type: obj.steps[index].interactive_type,
    },
    buttons: buttons,
    id: obj.steps[index].id,
    arrow: true,
    when: {
      show() {
        sessionStorage.setItem('currentStepId', index);
        const theElement = this.getElement();
        addProgressIndicator(theElement, index + 1, sessionStorage.getItem('stepCount'));

        theElement.focus = () => {

          const tabbed_elements = document.querySelectorAll('[tabindex]');
          tabbed_elements.forEach(function(elt) {
            elt.setAttribute('tabindex', '-1');
          });

          tour.currentStep.getTarget().focus();
          tour.currentStep.getTarget().tabIndex = 1;

          const popup_buttons = tour.currentStep.getElement().querySelectorAll('.shepherd-content button');
          popup_buttons.forEach(function(elt, index) {
            elt.setAttribute('tabindex', popup_buttons.length + 1 - index); // loose tab on 'back'
          });
        }

        if (obj.steps[index].type === 1) {
          checkAndRedirect(Joomla.getOptions('system.paths').rootFull + tour.currentStep.options.attachTo.url);
        }
      },
    },
  });
}

function showTourInfo(tour, obj) {
  tour.addStep({
    title: obj.title,
    text: obj.description,
    classes: 'shepherd-theme-arrows',
    attachTo: {
      on: 'bottom',
    },
    buttons: [
      {
        classes: 'shepherd-button-primary',
        action() {
          return tour.next();
        },
        text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_START'),
      },
    ],
    id: 0,
    when: {
      show() {
        sessionStorage.setItem('currentStepId', '0');

        const stepCount = this.getTour().steps.length;
        sessionStorage.setItem('stepCount', String(stepCount));

        addProgressIndicator(this.getElement(), 1, stepCount);
      },
    },
  });
}

function pushCompleteButton(buttons, tour) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_COMPLETE'),
    classes: 'shepherd-button-primary',
    action: function () {
      return tour.cancel();
    },
  });
}

function pushNextButton(buttons, tour, stepId, disabled = false) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_NEXT'),
    classes: `shepherd-button-primary step-next-button-${stepId}`,
    action: function () {
      return tour.next();
    },
    disabled: disabled,
  });
}

function addBackButton(buttons, tour, prevStep) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_BACK'),
    classes: 'shepherd-button-secondary',
    action: function () {
      if (prevStep) {
        sessionStorage.setItem('currentStepId', prevStep.id);
        if (prevStep.type === 1) {
          checkAndRedirect(Joomla.getOptions('system.paths').rootFull + prevStep.url);
        }
      }
      return tour.back();
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

  // Try to continue
  const currentStepId = sessionStorage.getItem('currentStepId');
  let prevStep = '';

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

    sessionStorage.removeItem('currentStepId');

    // First check for redirect
    const uri = Joomla.getOptions('system.paths').rootFull;
    const currentURL = window.location.href;

    if (currentURL !== uri + obj.url) {
      window.location.href = uri + obj.url;

      return;
    }

    // Show info
    showTourInfo(tour, obj);
    ind = 0;
  }

  // Now let's add all follow up steps
  const len = obj.steps.length;
  let buttons;

  // eslint-disable-next-line no-plusplus
  for (let index = ind; index < len; index++) {
    buttons = [];

    // If we have at least done one step, let's allow a back step
    addBackButton(buttons, tour, prevStep);

    if (
      obj
      && obj.steps[index].target
      && obj.steps[index].type === 2
    ) {
      const ele = document.querySelector(obj.steps[index].target);
      if (ele) {
        if (obj && obj.steps && obj.steps[index] && obj.steps[index].interactive_type) {
          switch (obj.steps[index].interactive_type) {
            case 1:
              ele.addEventListener('click', () => {
                sessionStorage.setItem('currentStepId', index + 1);
              });
              break;

            case 2:
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

            case 3:
              break;

            case 4:
              tour.next();
              break;
          }
        }
      }
    }

    if (index < len) {
      let disabled = false;
      if (obj && obj.steps[index].interactive_type === 2) {
        disabled = true;
      }
      if (
        (obj && obj.steps[index].type !== 2)
        || (obj && obj.steps[index].interactive_type === 2)
        || (obj && obj.steps[index].interactive_type === 3)
      ) {
        pushNextButton(buttons, tour, index, disabled);
      }
    } else {
      pushCompleteButton(buttons, tour);
    }

    addStepToTourButton(tour, obj, index, buttons);
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
    const elements = document.querySelectorAll('.button-start-guidedtour');

    elements.forEach(elem => {
      elem.addEventListener('click', e => {
        if (!e.target || e.target.getAttribute('data-id') <= 0) {
          Joomla.renderMessages([Joomla.Text._('PLG_SYSTEM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR')]);

          return;
        }
        loadTour(e.target.getAttribute('data-id'));
      });
    });

    // Start a given tour
    const tourId = sessionStorage.getItem('tourId');

    if (tourId > 0) {
      loadTour(tourId);
    }
  });
})(Joomla, document);
