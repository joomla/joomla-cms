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
]);

const minimalSetup = (() => [
  highlightSpecialChars(),
  history(),
  drawSelection(),
  syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
  keymap.of([
    ...defaultKeymap,
    ...historyKeymap,
  ])
]);

const optionsToExtensions = (options) => {
  const extensions = [];

  if (options.lineNumbers) {
    extensions.push(lineNumbers());
  }

  if (options.lineWrapping) {
    extensions.push(EditorView.lineWrapping);
  }

  if (options.autoCloseTags) {
    // https://discuss.codemirror.net/t/how-to-automatically-close-html-tags-in-codemirror6/3541
    // extensions.push(closeBrackets(), autocompletion());
  }

  if (options.foldGutter) {
    extensions.push(foldGutter());
  }

console.log(extensions);
  return extensions;
}

function createFromTextarea(textarea, options) {
  console.log(options);

  const view = new EditorView({
    doc: textarea.value,
    extensions: [minimalSetup(), optionsToExtensions(options)],
  });
  textarea.parentNode.insertBefore(view.dom, textarea);
  textarea.style.display = 'none';
  if (textarea.form) {
    textarea.form.addEventListener('submit', () => {
      textarea.value = view.state.doc.toString();
    });
  }

  // Set up sizing
  if (options.width) {
    view.dom.style.width = options.width;
  }
  if (options.height) {
    view.dom.style.minHeight = options.height;
  }

  return view;
}

export { basicSetup, minimalSetup, createFromTextarea };
