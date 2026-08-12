/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function () {
    'use strict';

    const READMORE_ID = 'system-readmore';

    tinymce.PluginManager.add('readmore', editor => {
        const hasReadMore = () => {
            return editor.dom.get(READMORE_ID) !== null;
        };

        const insertReadMore = () => {
            if (hasReadMore()) {
                return;
            }

            editor.insertContent(
                `<hr id="${READMORE_ID}" />`
            );
        };

        const onSetupEditable = api => {
            const nodeChanged = () => {
                api.setEnabled(
                    editor.selection.isEditable() && !hasReadMore()
                );
            };

            editor.on('NodeChange', nodeChanged);
            nodeChanged();

            return () => {
                editor.off('NodeChange', nodeChanged);
            };
        };

      editor.addCommand('mceReadMore', insertReadMore);

      editor.ui.registry.addIcon('readmore', '<svg width="24px" height="24px"><path d="M6.857 9.429V1.714h15.429v7.714h-1.257V2.971H8.171v6.457H6.857Zm15.429 3.886v9.143H6.857v-9.143h1.257v7.714h12.857v-7.714zM12 10.286h2.571V12H12zm-3.886 0h2.571V12H8.114zm7.714 0h2.571V12h-2.571zm3.886 0h2.571V12h-2.571zM1.714 7.486l3.886 3.886-3.886 3.886z"/></svg>');

      editor.ui.registry.addButton('readmore', {
        title: 'Read More',
        icon: 'readmore',
            tooltip: Joomla.Text._('PLG_TINY_TOOLBAR_BUTTON_READMORE'),
            onAction: insertReadMore,
            onSetup: onSetupEditable
        });
  return {
    getMetadata() {
      return {
        name: 'Readmore Plugin (Joomla)',
        url: 'https://www.joomla.org'
      };
    }
  };

    });
})();
