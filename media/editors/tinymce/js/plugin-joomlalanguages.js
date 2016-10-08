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

        /**
         * Apply language to selected string
         */
        function applyLanguage() {
            var lang = this.settings.lang;

            editor.focus();
            if (editor.selection.getContent().length) {
                var tmpNode = editor.selection.getNode();

                if (/^<span/.test(tmpNode.outerHTML.toLowerCase())) {
                    tmpNode.setAttribute("lang", lang.code);
                    tmpNode.setAttribute("dir", lang.dir);
                    tmpNode.setAttribute("xml:lang", lang.code);
                } else {
                    var content = '<span lang="' + lang.code + '" dir="' + lang.dir + '" xml:lang="' + lang.code + '">'
                        + editor.selection.getContent() + '</span>';

                    editor.selection.setContent(content);
                }
            }
            editor.nodeChanged();
            editor.focus();
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
