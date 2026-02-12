/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
import { JoomlaEditor, JoomlaEditorDecorator } from 'editor-api';

/* global tinymce, tinyMCE */

/**
 * TinyMCE Decorator for JoomlaEditor
 */
class TinyMCEDecorator extends JoomlaEditorDecorator {
  /**
   * @returns {string}
   */
  getValue() {
    return this.instance.getContent();
  }

  /**
   * @param {String} value
   * @returns {TinyMCEDecorator}
   */
  setValue(value) {
    this.instance.setContent(value);
    return this;
  }

  /**
   * @returns {string}
   */
  getSelection() {
    return this.instance.selection.getContent({ format: 'text' });
  }

  replaceSelection(value) {
    this.instance.execCommand('mceInsertContent', false, value);
    return this;
  }

  disable(enable) {
    this.instance.setMode(!enable ? 'readonly' : 'design');
    return this;
  }

  /**
   * Toggles the editor visibility mode. Used by Toggle button.
   * Should be implemented by editor provider.
   *
   * @param {boolean} show Optional. True to show, false to hide.
   *
   * @returns {boolean} Return True when editor become visible, and false when become hidden.
   */
  toggle(show) {
    let visible = false;
    if (show || this.instance.isHidden()) {
      this.instance.show();
      visible = true;
    } else {
      this.instance.hide();
    }
    return visible;
  }
}

Joomla.JoomlaTinyMCE = {
  /**
   * Find all TinyMCE elements and initialize TinyMCE instance for each
   *
   * @param {HTMLElement}  target  Target Element where to search for the editor element
   *
   * @since 3.7.0
   */
  setupEditors: (target) => {
    const container = target || document;
    const pluginOptions = Joomla.getOptions('plg_editor_tinymce', {});
    const editors = container.querySelectorAll('.js-editor-tinymce');

    editors.forEach((editor) => {
      const currentEditor = editor.querySelector('textarea');
      const toggleButton = editor.querySelector('.js-tiny-toggler-button');
      const toggleIcon = toggleButton ? toggleButton.querySelector('.icon-eye') : false;

      // Set up the editor
      Joomla.JoomlaTinyMCE.setupEditor(currentEditor, pluginOptions);

      // Set up the toggle button
      if (toggleButton) {
        toggleButton.removeAttribute('disabled');
      }

      // Find out when editor is interacted
      editor.addEventListener('click', (event) => {
        JoomlaEditor.setActive(currentEditor.id);

        // Check for the click on a toggle button
        const toggler = event.target.closest('.js-tiny-toggler-button');
        const ed = JoomlaEditor.getActive();
        if (toggler && ed) {
          const visible = ed.toggle();

          if (toggleIcon) {
            toggleIcon.setAttribute('class', visible ? 'icon-eye' : 'icon-eye-slash');
          }
        }
      });
    });
  },

  /**
   * Initialize TinyMCE editor instance
   *
   * @param {HTMLElement}  element
   * @param {Object}       pluginOptions
   *
   * @since 3.7.0
   */
  setupEditor: (element, pluginOptions) => {
    // Check whether the editor already has been set
    if (JoomlaEditor.get(element.id)) {
      return;
    }

    const name = element ? element.getAttribute('name').replace(/\[\]|\]/g, '').split('[').pop() : 'default'; // Get Editor name
    const tinyMCEOptions = pluginOptions ? pluginOptions.tinyMCE || {} : {};
    const defaultOptions = tinyMCEOptions.default || {};
    // Check specific options by the name
    let options = tinyMCEOptions[name] ? tinyMCEOptions[name] : defaultOptions;

    // Avoid an unexpected changes, and copy the options object
    if (options.joomlaMergeDefaults) {
      options = Joomla.extend(Joomla.extend({}, defaultOptions), options);
    } else {
      options = Joomla.extend({}, options);
    }

    if (element) {
      // We already have the Target, so reset the selector and assign given element as target
      options.selector = null;
      options.target = element;
    }

    // Check for a skin that suits best for the active color scheme
    const skinLight = options.skin_light;
    const skinDark = options.skin_dark;
    delete options.skin_light;
    delete options.skin_dark;
    // Set light as default
    options.skin = skinLight;

    // For templates with OS preferred color scheme
    if ('colorSchemeOs' in document.documentElement.dataset) {
      const mql = window.matchMedia('(prefers-color-scheme: dark)');
      options.skin = mql.matches ? skinDark : skinLight;
    } else if (document.documentElement.dataset.colorScheme === 'dark') {
      options.skin = skinDark;
    }

    // Ensure tinymce is initialised in readonly mode if the textarea has readonly applied
    let readOnlyMode = false;

    if (element) {
      readOnlyMode = element.readOnly;
    }

    // Capture the original ID for cleanup later
    const origId = element.id;

    options.setup = (editor) => {
      // Create a decorator
      const jEditor = new TinyMCEDecorator(editor, 'tinymce', element.id);

      editor.mode.set(readOnlyMode ? 'readonly' : 'design');

      // We'll take over the onSubmit event
      editor.on('submit', () => {
        if (editor.isHidden()) {
          editor.show();
        }
      }, true);

      // Save the content on change and blur to ensure the textarea is always up to date
      // This is crucial for things like the subform where the editor might be moved in the DOM
      editor.on('change blur', () => {
        editor.save();
      });

      // Work around iframe behavior, when iframe element changes location in DOM and losing its content.
      // Re init editor when iframe is reloaded.
      if (!editor.inline) {
        editor.on('init', () => {
          const $iframe = editor.getContentAreaContainer().querySelector('iframe');

          if ($iframe) {
            // Settle time to avoid infinite loop on initial load
            setTimeout(() => {
              // Make sure iframe is fully loaded.
              $iframe.addEventListener('load', () => {
                // Use setTimeout to detach from the load event and allow DOM to settle
                setTimeout(() => {
                  // Ensure the element is still in the DOM
                  if (!element.id || !document.getElementById(element.id)) {
                    return;
                  }

                  // Cleanup old editor
                  editor.off();
                  try {
                    editor.remove();
                  } catch (e) {
                    // Ignore errors during removal of a broken editor
                  }

                  // Force cleanup from global manager if still present under old ID
                  if (tinymce.get(origId)) {
                    try {
                      tinymce.get(origId).remove();
                    } catch (e) {
                      // Ignore
                    }
                  }

                  // Unregister from Joomla using both IDs
                  JoomlaEditor.unregister(origId);
                  JoomlaEditor.unregister(element.id);

                  // Make sure the element is visible before re-init
                  // eslint-disable-next-line no-param-reassign
                  element.style.display = '';
                  element.removeAttribute('aria-hidden');

                  Joomla.JoomlaTinyMCE.setupEditor(element, pluginOptions);
                }, 100);
              }, { once: true });
            }, 100);
          }
        });
      }

      // Find out when editor is interacted
      editor.on('focus', () => {
        JoomlaEditor.setActive(jEditor);
      });

      // Register the editor's instance to JoomlaEditor
      JoomlaEditor.register(jEditor);
    };

    // Create a new instance
    tinymce.init(options);
  },
};

/**
 * Initialize at an initial page load
 */
document.addEventListener('DOMContentLoaded', () => { Joomla.JoomlaTinyMCE.setupEditors(document); });

/**
 * Initialize when a part of the page was updated
 */
document.addEventListener('joomla:updated', ({ target }) => Joomla.JoomlaTinyMCE.setupEditors(target));

