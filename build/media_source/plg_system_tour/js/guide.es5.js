/**
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function checkAndRedirect(redirectUrl) {
  var currentURL = window.location.href;
  if (currentURL != redirectUrl) {
    window.location.href = redirectUrl;
  }
}
function createTour() {
  return new Shepherd.Tour({
    defaultStepOptions: {
      scrollTo: true,
      classes: "shadow",
      cancelIcon: {
        enabled: true,
      },
      classes: "class-1 class-2 shepherd-theme-arrows",
      scrollTo: { behavior: "smooth", block: "center" },
    },
    useModalOverlay: true,
    keyboardNavigation: true,
  });
}
function addCancelTourButton(tour) {
  tour.on("cancel", () => {
    sessionStorage.clear();
  });
}
function addStepToTourButton(tour, obj, tourId, index, buttons, uri) {
  tour.addStep({
    title: obj[tourId].steps[index].title,
    text: obj[tourId].steps[index].description,
    classes: "intro-step shepherd-theme-arrows",
    attachTo: {
      element: obj[tourId].steps[index].target,
      on: obj[tourId].steps[index].position,
      url: obj[tourId].steps[index].url,
    },

    buttons: buttons,
    id: obj[tourId].steps[index].id,
    arrow: true,
    showOn: obj[tourId].steps[index].position,
    when: {
      show() {
        var currentstepIndex = `${tour.currentStep.id}` - "0";
        sessionStorage.setItem("currentStepId", currentstepIndex);
        checkAndRedirect(uri + tour.currentStep.options.attachTo.url);
      },
    },
  });
}
function addInitialStepToTourButton(tour, obj, tourId) {
  tour.addStep({
    title: obj[tourId].title,
    text: obj[tourId].description,
    classes: "intro-step shepherd-theme-arrows",
    attachTo: {
      on: "bottom",
    },
    buttons: [
      {
        action() {
          return tour.next();
        },
        text: "Next",
      },
    ],
    id: obj[tourId].id,
  });
}
function pushCompleteButton(buttons, tour) {
  buttons.push({
    text: "Complete",
    classes: "shepherd-button-primary",
    action: function () {
      return tour.cancel();
    },
  });
}
function pushNextButton(buttons, tour) {
  buttons.push({
    text: "Next",
    classes: "shepherd-button-primary",
    action: function () {
      return tour.next();
    },
  });
}
function pushBackButton(buttons, tour, prev_step) {
  buttons.push({
    text: "Back",
    classes: "shepherd-button-secondary",
    action: function () {
      if(prev_step)
      {
        const paths = Joomla.getOptions('system.paths');
        sessionStorage.setItem("currentStepId", prev_step.id);
        checkAndRedirect(paths.rootFull + prev_step.url);
      }
      return tour.back();
    },
  });
}

Joomla = window.Joomla || {};
(function (Joomla, window) {
  document.addEventListener("DOMContentLoaded", function () {
    const paths = Joomla.getOptions('system.paths');
    const uri = paths.rootFull;

    let myTours = Joomla.getOptions("myTours");
    let obj = JSON.parse(myTours);
    let btnGoods = document.querySelectorAll(".button-tour");
    for (var i = 0; i < btnGoods.length; i++) {
      btnGoods[i].addEventListener("click", function () {
        var dataID = this.getAttribute("data-id");
        var tourId = obj.findIndex((x) => x.id == dataID);
        sessionStorage.setItem("tourId", dataID);

        checkAndRedirect(uri + obj[tourId].url);

        const tour = createTour();

        if (sessionStorage.getItem("tourId")) {
          let prev_step = '';
          addInitialStepToTourButton(tour, obj, tourId);
          for (index = 0; index < obj[tourId].steps.length; index++) {
            var buttons = [];
            var len = obj[tourId].steps.length;
            pushBackButton(buttons, tour, prev_step);
            if (index != len - 1) {
              pushNextButton(buttons, tour);
            } else {
              pushCompleteButton(buttons, tour);
            }
            addStepToTourButton(tour, obj, tourId, index, buttons, uri);
            prev_step = obj[tourId].steps[index];
          }
        }
        tour.start();
        addCancelTourButton(tour);
      });
    }
    var tourId = sessionStorage.getItem("tourId");
    var currentStepId = sessionStorage.getItem("currentStepId");
    let prev_step = '';

    if (tourId) {
      tourId = obj.findIndex((x) => x.id == tourId);
      const tour = createTour();
      var ind = 0;
      if (currentStepId) {
        ind = obj[tourId].steps.findIndex((x) => x.id == currentStepId);
        if( ind > 0){
          prev_step = obj[tourId].steps[ind-1]
        }
      } else {
        ind = 0;
      }
      for (index = ind; index < obj[tourId].steps.length; index++) {
        let buttons = [];
        var len = obj[tourId].steps.length;

        pushBackButton(buttons, tour, prev_step);

        if (index != len - 1) {
          pushNextButton(buttons, tour);
        } else {
          pushCompleteButton(buttons, tour);
        }

        addStepToTourButton(tour, obj, tourId, index, buttons, uri);
        prev_step = obj[tourId].steps[index];
      }
      tour.start();
      addCancelTourButton(tour);
    }
  });
})(Joomla, window);
