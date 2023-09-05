/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// eslint-disable-next-line import/no-unresolved
import { JoomlaEditor, JoomlaEditorButton } from 'editor-api';

/**
 * @param {Editor} editor
 */
const pluginSetUp = (editor) => {
  editor.options.register('joomlaExtButtons', { processor: 'object', default: { names: [] } });

  // Get buttons list
  const buttons = editor.options.get('joomlaExtButtons').names || [];

  if (!buttons.length) {
    return;
  }

  // Build menu
  const subMenu = [];
  const icons = {
    joomla: '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M8.313 8.646c1.026-1.026 2.688-1.026 3.713-0.001l0.245 0.246 3.159-3.161-0.246-0.246c-1.801-1.803-4.329-2.434-6.638-1.891-0.331-2.037-2.096-3.591-4.224-3.592-2.364 0-4.28 1.92-4.28 4.286 0 2.042 1.425 3.75 3.333 4.182-0.723 2.42-0.133 5.151 1.776 7.062l7.12 7.122 3.156-3.163-7.119-7.121c-1.021-1.023-1.023-2.691 0.006-3.722zM31.96 4.286c0-2.368-1.916-4.286-4.281-4.286-2.164 0-3.952 1.608-4.24 3.695-2.409-0.708-5.118-0.109-7.020 1.794l-7.12 7.122 3.159 3.162 7.118-7.12c1.029-1.030 2.687-1.028 3.709-0.006 1.025 1.026 1.025 2.691-0.001 3.717l-0.244 0.245 3.157 3.164 0.246-0.248c1.889-1.893 2.49-4.586 1.8-6.989 2.098-0.276 3.717-2.074 3.717-4.25zM28.321 23.471c0.566-2.327-0.062-4.885-1.878-6.703l-7.109-7.125-3.159 3.16 7.11 7.125c1.029 1.031 1.027 2.691 0.006 3.714-1.025 1.025-2.688 1.025-3.714-0.001l-0.243-0.243-3.156 3.164 0.242 0.241c1.922 1.925 4.676 2.514 7.105 1.765 0.395 1.959 2.123 3.431 4.196 3.431 2.363 0 4.28-1.917 4.28-4.285 0-2.163-1.599-3.952-3.679-4.244zM19.136 16.521l-7.111 7.125c-1.022 1.024-2.689 1.026-3.717-0.004-1.026-1.028-1.026-2.691-0.001-3.718l0.244-0.243-3.159-3.16-0.242 0.241c-1.836 1.838-2.455 4.432-1.858 6.781-1.887 0.446-3.292 2.145-3.292 4.172-0.001 2.367 1.917 4.285 4.281 4.285 2.034-0.001 3.737-1.419 4.173-3.324 2.334 0.58 4.906-0.041 6.729-1.867l7.109-7.124-3.157-3.163z"></path></svg>',
  };

  buttons.forEach((xtdButton) => {
    const button = {};
    button.text = xtdButton.name;
    button.icon = xtdButton.icon;
    button.type = 'menuitem';

    if (xtdButton.iconSVG) {
      icons[button.icon] = xtdButton.iconSVG;
    }

    button.onAction = () => {
      JoomlaEditor.setActive(editor.id);
      if (xtdButton.action) {
        JoomlaEditorButton.runAction(xtdButton.action, xtdButton.options || {});
      } else if (xtdButton.bsModal) {
        document.getElementById(`${xtdButton.id}_modal`).open();
      } else if (xtdButton.click) {
        // eslint-disable-next-line no-new-func
        new Function(xtdButton.click)();
      }
    };

    subMenu.push(button);
  });

  // Register icons
  Object.keys(icons).forEach((icon) => {
    editor.ui.registry.addIcon(icon, icons[icon]);
  });

  // Register main button
  editor.ui.registry.addMenuButton('jxtdbuttons', {
    text: Joomla.Text._('PLG_TINY_CORE_BUTTONS'),
    icon: 'joomla',
    fetch: (callback) => callback(subMenu),
  });
};

window.tinymce.PluginManager.add('jxtdbuttons', (editor) => {
  pluginSetUp(editor);
  return {
    getMetadata: () => ({
      name: 'Editor XTD buttons (Joomla)',
      url: 'https://www.joomla.org/',
    }),
  };
});
