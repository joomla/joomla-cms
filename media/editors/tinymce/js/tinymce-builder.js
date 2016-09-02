/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function($) {
    "use strict";

    /**
     * Joomla TinyMCE Builder
     *
     * @param {HTMLElement} container
     * @param {Object}      options
     * @constructor
     *
     * @since  __DEPLOY_VERSION__
     */
    var JoomlaTinyMCEBuilder = function(container, options) {
        this.$container = $(container);
        this.options    = options;

        console.log(this);
    };


    // Init the builder
    $(document).ready(function(){
        var options = Joomla.getOptions ? Joomla.getOptions('plg_editors_tinymce_builder', {})
        			:  (Joomla.optionsStorage.plg_editors_tinymce_builder || {});

        new JoomlaTinyMCEBuilder($('#joomla-tinymce-builder'), options);

        $("#view-level-tabs a").on('click', function (event) {
            event.preventDefault();
            $(this).tab("show");
        });
    });
}(jQuery));
