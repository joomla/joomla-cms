Joomla = window.Joomla || {};
(function(Joomla, window) {
    document.addEventListener("DOMContentLoaded", function() {
        var myTours = Joomla.getOptions("myTours");
        var obj = JSON.parse(myTours);
        let btnGoods = document.querySelectorAll(".button-tour");
        for (var i = 0; i < btnGoods.length; i++) {
            btnGoods[i].addEventListener("click", function() {
                var dataID = this.getAttribute("data-id");
                var tourId = obj.findIndex((x) => x.id == dataID);
                sessionStorage.setItem("tourId", tourId);

                var currentURL = window.location.href;
                if (currentURL != obj[tourId].url) {
                    window.location.href = obj[tourId].url;
                }
                var overlay = true;
                if (obj[tourId].overlay == 0) {
                    overlay = false;
                }

                const tour = new Shepherd.Tour({
                    defaultStepOptions: {
                        scrollTo: true,
                        classes: "shadow",
                        cancelIcon: {
                            enabled: true,
                        },
                        classes: "class-1 class-2 shepherd-theme-arrows",
                        scrollTo: { behavior: "smooth", block: "center" },
                    },
                    keyboardNavigation: true,
                    useModalOverlay: overlay,
                });

                if (sessionStorage.getItem("tourId")) {
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

                    for (index = 0; index < obj[tourId].steps.length; index++) {
                        var buttons = [];
                        var len = obj[tourId].steps.length;
                            buttons.push({
                                text: "Back",
                                classes: "shepherd-button-secondary",
                                action: function() {
                                    return tour.back();
                                },
                            });

                        if (index != len - 1) {
                            buttons.push({
                                text: "Next",
                                classes: "shepherd-button-primary",
                                action: function() {
                                    return tour.next();
                                },
                            });
                        } else {
                            buttons.push({
                                text: "Complete",
                                classes: "shepherd-button-primary",
                                action: function() {
                                    return tour.cancel();
                                },
                            });
                        }

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
                                    var stepId = `${tour.steps.indexOf(tour.currentStep) + 1}`;
                                    var newstepId = `${tour.currentStep.id}` - "0";
                                    var currentStepindex = `${tour.steps.indexOf(tour.currentStep)}`;

                                    sessionStorage.setItem("stepId", stepId);
                                    sessionStorage.setItem("newstepId", newstepId);
                                    sessionStorage.setItem("currentStepindex", currentStepindex);

                                  var currentTourUrl = window.location.href;

                                  if(currentTourUrl != tour.currentStep.options.attachTo.url){
                                    window.location.href = tour.currentStep.options.attachTo.url;
                                  }
                                },
                            },
                        });
                    }
                }
                tour.start();
                tour.on("cancel", () => {
                    sessionStorage.clear();
                });
            });
        }
        var tourId = sessionStorage.getItem("tourId");
        var stepId = sessionStorage.getItem("stepId");
        var newstepId = sessionStorage.getItem("newstepId");
        var currentStepindex = sessionStorage.getItem("currentStepindex");

        var overlay = true;
        if (obj[tourId].overlay == 0) {
            overlay = false;
        }

        const tour = new Shepherd.Tour({
            defaultStepOptions: {
                scrollTo: true,
                classes: "shadow",
                cancelIcon: {
                    enabled: true,
                },
                classes: "class-1 class-2 shepherd-theme-arrows",
                scrollTo: { behavior: "smooth", block: "center" },
            },
            keyboardNavigation: true,
            useModalOverlay: overlay,
        });

        if (tourId && currentStepindex) {
            for (index = currentStepindex-1; index < obj[tourId].steps.length; index++) {
                var buttons = [];
                var len = obj[tourId].steps.length;

                    buttons.push({
                        text: "Back",
                        classes: "shepherd-button-secondary",
                        action: function() {
                            return tour.back();
                        },
                    });

                if (index != len - 1) {
                    buttons.push({
                        text: "Next",
                        classes: "shepherd-button-primary",
                        action: function() {
                            return tour.next();
                        },
                    });
                } else {
                    buttons.push({
                        text: "Complete",
                        classes: "shepherd-button-primary",
                        action: function() {
                            return tour.cancel();
                        },
                    });
                }

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
                            var stepId = `${tour.steps.indexOf(tour.currentStep) + 1}`;
                            var newstepId = `${tour.currentStep.id}` - "0";
                            var currentStepindex = `${tour.steps.indexOf(tour.currentStep)}`;

                            sessionStorage.setItem("stepId", stepId);
                            sessionStorage.setItem("newstepId", newstepId);
                            sessionStorage.setItem("currentStepindex", newstepId);

                          var currentUrl = window.location.href;
                          if(currentUrl != tour.currentStep.options.attachTo.url){
                            window.location.href = tour.currentStep.options.attachTo.url;
                          }
                        },
                    },
                });
            }
        }

        tour.start();
        tour.on("cancel", () => {
            sessionStorage.clear();
        });
    });
})(Joomla, window);
