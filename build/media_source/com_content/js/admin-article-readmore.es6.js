/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* global JoomlaDialog */

const insertReadmoreHandler = () => {
  const editor = Joomla.Editor.getActive();
  if (!editor) {
    throw new Error('An active editor are not available');
  }
  const content = editor.getValue();

  if (!content) {
    editor.replaceSelection('<hr id="system-readmore">');
  } else if (content && !content.match(/<hr\s+id=("|')system-readmore("|')\s*\/*>/i)) {
    editor.replaceSelection('<hr id="system-readmore">');
  } else {
    JoomlaDialog.alert(Joomla.Text._('PLG_READMORE_ALREADY_EXISTS'));
  }
};

// @TODO: Remove in Joomla 6
window.insertReadmore = () => {
  // eslint-disable-next-line no-console
  console.warn('Method window.insertReadmore() is deprecated, use button action "insert-readmore."');
  insertReadmoreHandler();
};

Joomla.EditorButton.registerAction('insert-readmore', insertReadmoreHandler);
