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
import {
  history, defaultKeymap, historyKeymap, emacsStyleKeymap,
} from '@codemirror/commands';
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
]);

// Configure extensions depend from given options
const optionsToExtensions = async (options) => {
  const extensions = [];
  const q = [];

  // Load the language for syntax mode
  if (options.mode) {
    q.push(import(`@codemirror/lang-${options.mode}`).then((modeMod) => {
      extensions.push(modeMod[options.mode]())
    }));
  }

  if (options.lineNumbers) {
    extensions.push(lineNumbers());
  }

  if (options.lineWrapping) {
    extensions.push(EditorView.lineWrapping);
  }

  if (options.activeLine) {
    extensions.push(highlightActiveLineGutter(), highlightActiveLine());
  }

  if (options.highlightSelection) {
    extensions.push(highlightSelectionMatches());
  }

  if (options.autoCloseBrackets) {
    extensions.push(closeBrackets());
  }

  if (options.foldGutter) {
    extensions.push(foldGutter());
  }

  // Keymaps
  switch (options.keyMap) {
    case 'emacs':
      extensions.push(keymap.of([...emacsStyleKeymap, ...historyKeymap]));
      break;
    default:
      extensions.push(keymap.of([...defaultKeymap, ...searchKeymap, ...historyKeymap]));
      break;
  }

  return Promise.all(q).then(() => extensions);
};

async function createFromTextarea(textarea, options) {
  console.log(options);

  const extensions = [minimalSetup(), await optionsToExtensions(options)];
  const view = new EditorView({
    doc: textarea.value,
    extensions,
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
