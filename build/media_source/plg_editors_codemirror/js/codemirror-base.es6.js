/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Based on https://github.com/codemirror/basic-setup
 */
import {
  EditorView,
  lineNumbers,
  highlightActiveLineGutter,
  highlightSpecialChars,
  drawSelection,
  dropCursor,
  rectangularSelection,
  crosshairCursor,
  highlightActiveLine,
  keymap,
} from '@codemirror/view';
import { EditorState } from '@codemirror/state';
import {
  foldGutter,
  indentOnInput,
  syntaxHighlighting,
  defaultHighlightStyle,
  bracketMatching,
  foldKeymap,
} from '@codemirror/language';
import { history, defaultKeymap, historyKeymap } from '@codemirror/commands';
import { highlightSelectionMatches, searchKeymap } from '@codemirror/search';
import {
  closeBrackets, autocompletion, closeBracketsKeymap, completionKeymap,
} from '@codemirror/autocomplete';
import { lintKeymap } from '@codemirror/lint';

const basicSetup = (() => [
  lineNumbers(),
  highlightActiveLineGutter(),
  highlightSpecialChars(),
  history(),
  foldGutter(),
  drawSelection(),
  dropCursor(),
  EditorState.allowMultipleSelections.of(true),
  indentOnInput(),
  syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
  bracketMatching(),
  closeBrackets(),
  autocompletion(),
  rectangularSelection(),
  crosshairCursor(),
  highlightActiveLine(),
  highlightSelectionMatches(),
  keymap.of([
    ...closeBracketsKeymap,
    ...defaultKeymap,
    ...searchKeymap,
    ...historyKeymap,
    ...foldKeymap,
    ...completionKeymap,
    ...lintKeymap,
  ]),
])();

function createFromTextArea(textarea, extensions) {
  const view = new EditorView({
    doc: textarea.value,
    extensions: [basicSetup, langHtml(), langXml(), langPhp(), langJs()],
  });
  textarea.parentNode.insertBefore(view.dom, textarea);
  textarea.style.display = 'none';
  if (textarea.form) {
    textarea.form.addEventListener('submit', () => {
      textarea.value = view.state.doc.toString();
    });
  }
  return view;
}

export { basicSetup, createFromTextArea };
