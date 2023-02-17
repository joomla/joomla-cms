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

function instantiateTour() {
  return new Shepherd.Tour({
    defaultStepOptions: {
      cancelIcon: {
        enabled: true,
      },
      classes: 'class-1 class-2 shepherd-theme-arrows',
      scrollTo: {
        behavior: 'smooth',
        block: 'center',
      },
    },
    useModalOverlay: true,
    keyboardNavigation: true,
  });
}

function addStepToTourButton(tour, obj, index, buttons) {
  tour.addStep({
    title: obj.steps[index].title,
    text: obj.steps[index].description,
    classes: 'intro-step shepherd-theme-arrows',
    attachTo: {
      element: obj.steps[index].target,
      on: obj.steps[index].position,
      url: obj.steps[index].url,
      type: obj.steps[index].type,
      interactive_type: obj.steps[index].interactive_type,
    },
    buttons: buttons,
    id: obj.steps[index].ordering,
    arrow: true,
    when: {
      show() {
        const currentStepIndex = `${tour.currentStep.id}`;
        sessionStorage.setItem('currentStepId', String(currentStepIndex));
        const theElement = this.getElement();
        if (theElement) {
          theElement.focus = () => {

              const tabbed_elements = document.querySelectorAll('[tabindex]');
              tabbed_elements.forEach(function(elt) {
                  elt.setAttribute('tabindex', '-1');
              });

              tour.currentStep.getTarget().focus();
              tour.currentStep.getTarget().tabIndex = 1;

              const popup_buttons = tour.currentStep.getElement().querySelectorAll('.shepherd-content button');
              popup_buttons.forEach(function(elt, index) {
                  //elt.setAttribute('tabindex', popup_buttons.length + 1 - index); // loose tab on 'back'
                  elt.setAttribute('tabindex', index + 2);
              });
          }
      }
        if (obj.steps[index].type === 1) {
          checkAndRedirect(Joomla.getOptions('system.paths').rootFull + tour.currentStep.options.attachTo.url);
        }
      },
    },
  });
}

function addInitialStepToTourButton(tour, obj) {
  tour.addStep({
    title: obj.title,
    text: obj.description,
    classes: 'intro-step shepherd-theme-arrows',
    attachTo: {
      on: 'bottom',
    },
    buttons: [
      {
        classes: 'shepherd-button-primary',
        action() {
          return tour.next();
        },
        text: Joomla.Text._('PLG_SYSTEM_TOUR_START'),
      },
    ],
    id: 0,
  });
}

function addCancelTourEvent(tour) {
  tour.on('cancel', () => {
    sessionStorage.removeItem('currentStepId');
    sessionStorage.removeItem('tourId');
    const url = `${Joomla.getOptions('system.paths').rootFull}administrator/index.php?option=com_ajax&plugin=tour&group=system&format=raw&method=post&tour_id=-1`;
    fetch(
      url,
      {
        method: 'GET',
      },
    )
      .catch((error) => console.error(error));
    tour.steps = [];
  });
}

function pushCompleteButton(buttons, tour) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_TOUR_COMPLETE'),
    classes: 'shepherd-button-primary',
    action: function () {
      return tour.cancel();
    },
  });
}

function pushNextButton(buttons, tour, stepId, disabled = false) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_TOUR_NEXT'),
    classes: `shepherd-button-primary step-next-button-${stepId}`,
    action: function () {
      return tour.next();
    },
    disabled: disabled,
  });
}

function pushBackButton(buttons, tour, prevStep) {
  buttons.push({
    text: Joomla.Text._('PLG_SYSTEM_TOUR_BACK'),
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

function CreateAndStartTour(obj) {
  const currentStepId = sessionStorage.getItem('currentStepId');
  let prevStep = '';
  const tour = instantiateTour();
  let ind = 0;
  if (currentStepId) {
    ind = obj.steps.findIndex((x) => x.id === Number(currentStepId));
    if (ind < 0) {
      return;
    }
    if (ind > 0) {
      prevStep = obj.steps[ind - 1];
    }
  } else {
    addInitialStepToTourButton(tour, obj);
  }

  const len = obj.steps.length;
  let buttons;

  // eslint-disable-next-line no-plusplus
  for (let index = ind; index < len; index++) {
    buttons = [];

    pushBackButton(buttons, tour, prevStep);

    if (
      obj
      && obj.steps[index].target
      && obj.steps[index].type === 2
    ) {
      const ele = document.querySelector(obj.steps[index].target);
      if (ele) {
        if (obj && obj.steps[index].interactive_type === 2) {
          ele.step_id = index;
          ele.addEventListener('input', enableButton, enableButton);
        }
        if (obj && obj.steps[index].interactive_type === 1) {
          ele.addEventListener('click', tour.next, tour.next);
        }
      }
    }

    if (index !== len - 1) {
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
  addCancelTourEvent(tour);
  tour.start();
}

function tourWasSelected(element) {
  if (element.getAttribute('data-id') > 0) {
    const url = `${Joomla.getOptions('system.paths').rootFull}administrator/index.php?option=com_ajax&plugin=tour&group=system&format=raw&method=post&tour_id=${element.getAttribute('data-id')}`;
    fetch(
      url,
      {
        method: 'GET',
      },
    )
      .then((response) => response.json())
      .then((json) => {
        if (Object.keys(json).length > 0) {
          document.dispatchEvent(new CustomEvent('GuidedTourLoaded', { bubbles: true, detail: json }));
        }
      })
      .catch((error) => console.error(error));
  } else {
    console.log('tour: no data-id');
  }
}

Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  document.addEventListener('GuidedTourLoaded', (event) => {
    sessionStorage.setItem('tourId', event.detail.id);
    const uri = Joomla.getOptions('system.paths').rootFull;
    const currentURL = window.location.href;
    if (currentURL !== uri + event.detail.url) {
      window.location.href = uri + event.detail.url;
    } else {
      CreateAndStartTour(event.detail);
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    const tourId = sessionStorage.getItem('tourId');
    if (tourId) {
      const myTour = Joomla.getOptions('myTour');
      if (myTour) {
        CreateAndStartTour(JSON.parse(myTour));
      } else {
        sessionStorage.removeItem('currentStepId');
        sessionStorage.removeItem('tourId');
      }
    }

    // Opt-in Start buttons
    const elements = document.querySelectorAll('.button-start-guidedtour');

    elements.forEach(elem => {
      elem.addEventListener('click', e => {
        tourWasSelected(e.target);
      });
    });
  });
})(Joomla, document);
