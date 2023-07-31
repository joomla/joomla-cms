/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
window.tinymce.PluginManager.add('joomlaHighlighter', (editor) => {
  console.log(editor);

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
    let dialog;
    let cmEditor;
    const dialogConfig = {
      title: 'Source code',
      body: {
        type: 'panel',
        classes: ['joomla-highlighter-dialog'],
        items: [{
          type: 'textarea',
          name: 'joomla_highlighter_input',
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
          primary: true,
        },
      ],
      initialData: { joomla_highlighter_input: getContent() },
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
    import('codemirror').then(({ createFromTextarea }) => {
      dialog = editor.windowManager.open(dialogConfig);
      // dialog.toggleFullscreen();

      const cmOptions = {
        mode: 'html',
        lineNumbers: true,
        lineWrapping: true,
        activeLine: true,
        highlightSelection: true,
        foldGutter: true,
      };
      const textarea = document.querySelector('.joomla-highlighter-dialog textarea');
      createFromTextarea(textarea, cmOptions).then((cmView) => {
        cmEditor = cmView;
      });

      console.log(dialogConfig, dialog);
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
      name: 'Joomla highlighter plugin',
      url: 'https://www.joomla.org/',
    }),
  };
});
