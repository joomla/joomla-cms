/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// @TODO: remove the specific conditional on plg_system_shortcut/js/shortcut.es6.js when this moves to JoomlaDialog
window.tinymce.PluginManager.add('joomlaHighlighter', (editor) => {
  const setContent = (html) => {
    editor.focus();
    editor.undoManager.transact(() => {
      editor.setContent(html);
    });
    editor.selection.setCursorLocation();
    editor.nodeChanged();
  };

  const getContent = () => editor.getContent({ source_view: true });

  let running = false;
  const showSourceEditor = () => {
    if (running) {
      return;
    }
    running = true;

    // Create the dialog
    let cmEditor;
    const dialogConfig = {
      title: 'Source code',
      body: {
        type: 'panel',
        classes: ['joomla-highlighter-dialog'],
        items: [{
          type: 'textarea',
          name: 'textarea',
          inputMode: 'text',
          maximized: true,
        }],
      },
      size: 'large',
      buttons: [
        {
          type: 'cancel',
          name: 'cancel',
          text: 'Cancel',
        },
        {
          type: 'submit',
          name: 'save',
          text: 'Save',
          buttonType: 'primary',
        },
      ],
      onSubmit: (dialogApi) => {
        setContent(cmEditor.state.doc.toString());
        dialogApi.close();
      },
      onClose: () => {
        cmEditor.destroy();
        cmEditor = null;
        running = false;
      },
    };

    // Import codemirror and open the dialog
    // eslint-disable-next-line import/no-unresolved
    Promise.all([import('codemirror'), import('@codemirror/view'), import('@codemirror/commands')])
      .then(([{ createFromTextarea }, { keymap }, { indentMore }]) => {
        editor.windowManager.open(dialogConfig);

        // Find textarea and move it to shadow DOM to isolate from TinyMCE styling
        const textarea = document.querySelector('.joomla-highlighter-dialog textarea');
        const wrapper = textarea.parentElement;
        const shadow = wrapper.attachShadow({ mode: 'open' });
        textarea.value = getContent();
        shadow.appendChild(textarea);

        // Move focus out of the codemirror
        const escapeTabTrap = (view, event) => {
          event.preventDefault();
          // Find a Save button
          const dialogEl = wrapper.closest('[role="dialog"]');
          const btnEl = dialogEl.querySelector('.tox-dialog__footer [type="button"]:not(.tox-button--secondary)');
          if (btnEl) {
            btnEl.focus();
          } else {
            dialogEl.focus();
          }
        };

        const cmOptions = {
          mode: 'html',
          lineNumbers: true,
          lineWrapping: true,
          activeLine: true,
          highlightSelection: true,
          foldGutter: true,
          width: '100%',
          height: '100%',
          root: shadow,
          customExtensions: [
            // Enable Tab trapping
            () => keymap.of([{ key: 'Tab', run: indentMore, shift: escapeTabTrap }]),
          ],
        };
        const wrapperheight = wrapper.scrollHeight;

        createFromTextarea(textarea, cmOptions).then((cmView) => {
          cmEditor = cmView;
          cmEditor.focus();
          cmEditor.dom.style.maxHeight = `${wrapperheight}px`;
        });
      });
  };

  editor.ui.registry.addButton('code', {
    icon: 'sourcecode',
    title: 'Source code+',
    tooltip: 'Source code+',
    onAction: showSourceEditor,
  });

  editor.ui.registry.addMenuItem('code', {
    icon: 'sourcecode',
    text: 'Source code+',
    onAction: showSourceEditor,
    context: 'tools',
  });
  editor.addShortcut('Alt+U', 'Opens the code editor', showSourceEditor);

  return {
    getMetadata: () => ({
      name: 'Source Code Editor (Joomla)',
      url: 'https://www.joomla.org/',
    }),
  };
});
