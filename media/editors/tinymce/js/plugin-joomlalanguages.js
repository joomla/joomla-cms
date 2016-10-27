/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

;(function (tinyMCE) {
    "use strict";

    /**
     * Language plugin for WCAG 2.0 accessibility
     */
    tinyMCE.PluginManager.add('joomlalanguages', function (editor) {
        var settings  = editor.settings.joomla_languages || {},
            languages = settings.languages || {},
            menu = [];

        // Build drop-down menu
        var langItem, menuItem = {};
        for (var i = 0, l = languages.length; i < l; i++){
            langItem = languages[i];
            menuItem = {
                text: langItem.name + ' ' + langItem.nativeName,
                lang: langItem,
                onclick: applyLanguage
            };
            menu.push(menuItem);
        }

        // Walk the DOM
        function walkTheDOM(element, func) {
            func(element);
            element = element.firstChild;
            while (element) {
                walkTheDOM(element, func);
                element = element.nextSibling;
            }
        }

        /**
         * Apply language to selected string
         */
        function applyLanguage() {
            var lang = this.settings.lang;
            editor.focus();
            if (editor.selection.getContent().length) {
                var tmpNode = editor.selection.getNode();

               walkTheDOM(tmpNode, function (element) {

                   // Is it a Text node?
                    if (element.childNodes.length === 0 && element.nodeType === 3) {
                        editor.focus();
                        editor.selection.select(element);

                        if (element.parentNode && element.parentNode.tagName.toLowerCase() === 'span') {
                            editor.selection.select(element.parentNode);
                            element.parentNode.setAttribute("lang", lang.code);
                            element.parentNode.setAttribute("dir", lang.dir);
                            element.parentNode.setAttribute("xml:lang", lang.code);

                            editor.selection.setNode(element.parentNode);
                        } else {
                            var content = document.createElement('span');
                            content.setAttribute('lang', lang.code);
                            content.setAttribute('dir', lang.dir);
                            content.setAttribute('xml:lang', lang.code);
                            content.innerHTML = element.data;

                            editor.selection.setNode(content);
                      }

                        editor.nodeChanged();
                        editor.focus();
                    }
                });
            }
        }

        // Register the button
        editor.addButton('joomlalanguages', {
            type: "menubutton",
            icon: 'translate',
            text: Joomla.JText._('PLG_TINY_LANGUAGES_LABEL'),
            tooltip: Joomla.JText._('PLG_TINY_LANGUAGES_LABEL'),
            menu: menu
        });
    });

}(tinyMCE));
