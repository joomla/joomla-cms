/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
import {
  EditorView,
  lineNumbers,
  highlightActiveLineGutter,
  highlightSpecialChars,
  drawSelection,
  highlightActiveLine,
  keymap,
} from '@codemirror/view';
import { EditorState, Compartment } from '@codemirror/state';
import {
  foldGutter,
  syntaxHighlighting,
  defaultHighlightStyle,
} from '@codemirror/language';
import {
  history, defaultKeymap, historyKeymap, emacsStyleKeymap,
} from '@codemirror/commands';
import { highlightSelectionMatches, searchKeymap } from '@codemirror/search';
import { closeBrackets } from '@codemirror/autocomplete';

const minimalSetup = (() => [
  highlightSpecialChars(),
  history(),
  drawSelection(),
  syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
]);

/**
 * Configure and return list of extensions for given options
 *
 * @param {Object} options
 * @returns {Promise<[]>}
 */
const optionsToExtensions = async (options) => {
  const extensions = [];
  const q = [];

  // Load the language for syntax mode
  if (options.mode) {
    q.push(import(`@codemirror/lang-${options.mode}`).then((modeMod) => {
      extensions.push(modeMod[options.mode]());
    }).catch((error) => {
      // eslint-disable-next-line no-console
      console.error(`Cannot creat an extension for "${options.mode}" syntax mode.`, error);
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

  // Configurable read only
  const readOnly = new Compartment();
  // Set a custom name so later on we can retrieve this Compartment from view.state.config.compartments
  // eslint-disable-next-line no-underscore-dangle
  readOnly._j_name = 'readOnly';
  extensions.push(readOnly.of(EditorState.readOnly.of(!!options.readOnly)));

  // Check for custom extensions,
  // in format [['module1 name or URL', ['init method2']], ['module2 name or URL', ['init method2']], () => <return extension>]
  if (options.customExtensions && options.customExtensions.length) {
    options.customExtensions.forEach((extInfo) => {
      // Check whether we have a callable
      if (extInfo instanceof Function) {
        extensions.push(extInfo());
        return;
      }
      // Import the module
      const [module, methods] = extInfo;
      q.push(import(module).then((modObject) => {
        // Call each method
        methods.forEach((method) => {
          extensions.push(modObject[method]());
        });
      }));
    });
  }

  return Promise.all(q).then(() => extensions);
};

/**
 * Create an editor instance for given textarea
 *
 * @param {HTMLTextAreaElement} textarea
 * @param {Object} options
 * @returns {Promise<EditorView>}
 */
async function createFromTextarea(textarea, options) {
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
    view.dom.style.height = options.height;
  }

  return view;
}

export { minimalSetup, createFromTextarea, optionsToExtensions };
