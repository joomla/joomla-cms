/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function() {
	"use strict";

	var options = Joomla.getOptions('menus-edit-modules');

	if (options) {
		window.viewLevels = options.viewLevels;
		window.menuId = parseInt(options.itemId);
	}

    document.addEventListener('DOMContentLoaded', function() {
        var baseLink = "index.php?option=com_modules&amp;client_id=0&amp;task=module.edit&amp;tmpl=component&amp;view=module&amp;layout=modal&amp;id=",
            iFrameAttr = "class=\"iframe jviewport-height70\"";

		document.getElementById("jform_toggle_modules_assigned1").addEventListener("click", function (event) {
			var list = document.querySelectorAll("tr.no");
			list.forEach(function(item) {
				item.style.display = 'table-row';
			});
		});

		document.getElementById("jform_toggle_modules_assigned0").addEventListener("click", function (event) {
			var list = document.querySelectorAll("tr.no");
			list.forEach(function (item) {
				item.style.display = 'none';
			});
		});

		document.getElementById("jform_toggle_modules_published1").addEventListener("click", function (event) {
			var list = document.querySelectorAll(".table tr.unpublished");
			list.forEach(function (item) {
				item.style.display = 'table-row';
			});
		});

		document.getElementById("jform_toggle_modules_published0").addEventListener("click", function (event) {
			var list = document.querySelectorAll(".table tr.unpublished");
			list.forEach(function (item) {
				item.style.display = 'none';
			});
		});

        // TODO: Dimitris Help me!!!!!
        var linkElements = document.getElementsByClassName("module-edit-link");

        for (var i = 0; i < linkElements.length; i++) {
            linkElements[i].addEventListener('click', function(event) {
                var link = baseLink + jQuery(this).data("moduleId"),
                    iFrame = jQuery("<iframe src=\"" + link + "\" " + iFrameAttr + "></iframe>");

                jQuery("#moduleEditModal").modal()
                    .find(".modal-body").empty().prepend(iFrame);

            });
        }

        var targetDiv = document.getElementById("moduleEditModal").getElementsByClassName("bar")[0];
        jQuery(document)
    		.on("click", "#moduleEditModal .modal-footer .btn", function () {
                var target = jQuery(this).data("target");

                if (target) {
                    jQuery("#moduleEditModal iframe").contents().find(target).click();
                }
            });

    });
})();
