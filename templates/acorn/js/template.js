/** template.js **/
/* various jquery / js functions needed */
"use strict";

/* TOOL TIP */
jQuery(window).ready(function () {
    jQuery('.tooltip').tooltip({
        "html": true,
        "container": "body"
    });
});

/* SCROLL TO TOP */
jQuery(document).ready(function () {
    // Show or hide the "go to top" button
    jQuery(window).scroll(function () {
        if (jQuery(this).scrollTop() > 200) {
            jQuery('.go-top').fadeIn(300);
        } else {
            jQuery('.go-top').fadeOut(300);
        }
    });

    // Animate the scroll to top
    jQuery('.go-top').click(function (event) {
        event.preventDefault();
        jQuery('html, body').animate({scrollTop: 0}, 400);
    });
});

// Set icon image to be no larger then the menu items.
jQuery(document).ready(function () {
    // is an image being used?
    if (jQuery('.navbar-search input').hasClass('image')) {
        // set max-height of image
        jQuery('.navbar-search input.image').css({'max-height': jQuery('.navbar-search input.form-control').outerHeight(true)});
    }
});

