Joomla = window.Joomla || {};
(function(Joomla, window) {
    document.addEventListener("DOMContentLoaded", function() {
        var myTours = Joomla.getOptions("myTours");
        var obj = JSON.parse(myTours);
        let btnGoods = document.querySelectorAll(".button-tour");
        for (var i = 0; i < btnGoods.length; i++) {
            btnGoods[i].addEventListener("click", function() {
                var dataID = this.getAttribute("data-id");
                var mainID = obj.findIndex((x) => x.id == dataID);
                sessionStorage.setItem("tourid", mainID);

                var currentURL = window.location.href;
                if (currentURL != obj[mainID].url) {
                    window.location.href = obj[mainID].url;
                }
                var overlay = true;
                if (obj[mainID].overlay == 0) {
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

                if (sessionStorage.getItem("tourid")) {
                    tour.addStep({
                        title: obj[mainID].title,
                        text: obj[mainID].description,
                        classes: "intro-step shepherd-theme-arrows",
                        attachTo: {
                            on: "bottom",
                        },
                        buttons: [{
                                action() {
                                    return this.back();
                                },
                                classes: "shepherd-button-secondary shepherd-theme-arrows",
                                text: "Back",
                            },
                            {
                                action() {
                                    return this.next();
                                },
                                text: "Next",
                            },
                        ],
                        id: obj[mainID].id,
                    });

                    for (index = 0; index < obj[mainID].steps.length; index++) {
                        var buttons = [];
                        var len = tour.steps.length;
                        if (index > 0) {
                            buttons.push({
                                text: "Back",
                                classes: "shepherd-button-secondary",
                                action: function() {
                                    return tour.back();
                                },
                            });
                        }

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
                            buttons.push({
                                text: "Back",
                                classes: "shepherd-button-secondary",
                                action: function() {
                                    return tour.back();
                                },
                            });
                        }

                        tour.addStep({
                            title: obj[mainID].steps[index].title,
                            text: obj[mainID].steps[index].description,
                            classes: "intro-step shepherd-theme-arrows",
                            attachTo: {
                                element: obj[mainID].steps[index].target,
                                on: obj[mainID].steps[index].position,
                            },

                            buttons: buttons,
                            id: obj[mainID].steps[index].id,
                            arrow: true,
                            showOn: obj[mainID].steps[index].position,
                            when: {
                                show() {
                                    var thisId = `${tour.steps.indexOf(tour.currentStep) + 1}`;
                                    var Id = `${tour.currentStep.id}` - "0";
                                    sessionStorage.setItem("stepID", thisId);
                                    sessionStorage.setItem("newstepID", Id);
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
        var mainID = sessionStorage.getItem("tourid");
        var newIndex = sessionStorage.getItem("stepID");
        var newId = sessionStorage.getItem("newstepID");
        newIndex = newIndex - 1;


        var overlay = true;
        if (obj[mainID].overlay == 0) {
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

        if (mainID && newId) {
            for (index = newId; index < obj[mainID].steps.length; index++) {
                var buttons = [];
                var len = tour.steps.length;
                if (index > 0) {
                    buttons.push({
                        text: "Back",
                        classes: "shepherd-button-secondary",
                        action: function() {
                            return tour.back();
                        },
                    });
                }
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
                            return tour.close();
                        },
                    });
                }

                tour.addStep({
                    title: obj[mainID].steps[index].title,
                    text: obj[mainID].steps[index].description,
                    classes: "intro-step shepherd-theme-arrows",
                    attachTo: {
                        element: obj[mainID].steps[index].target,
                        on: obj[mainID].steps[index].position,
                    },

                    buttons: buttons,
                    id: obj[mainID].steps[index].id,
                    arrow: true,
                    showOn: obj[mainID].steps[index].position,
                    when: {
                        show() {
                            var thisId = `${tour.steps.indexOf(tour.currentStep) + 1}`;
                            var Id = `${tour.currentStep.id}` - "0";
                            sessionStorage.setItem("stepID", thisId);
                            sessionStorage.setItem("newstepID", Id);
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