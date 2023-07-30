/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
window.tinymce.PluginManager.add('joomla-highlighter', (editor, url) => {
  console.log(editor);

  const setContent = (html) => {
    editor.focus();
    editor.undoManager.transact(() => {
      editor.setContent(html);
    });
    editor.selection.setCursorLocation();
    editor.nodeChanged();
  };

  const getContent = () => {
    return editor.getContent({ source_view: true });
  };

  const showSourceEditor = () => {
    let popup;
    const popupConfig = {
      title: 'Source code',
      body: {
        type: 'panel',
        items: [{
          type: 'textarea',
          name: 'joomla-highlighter-input',
          inputMode: 'text',
          maximized: true,
        }]
      },
      size: 'large',
      buttons: [
        {
          type: 'cancel',
          name: 'cancel',
          text: 'Cancel'
        },
        {
          type: 'submit',
          name: 'save',
          text: 'Save',
          primary: true
        }
      ],
      initialData: { 'joomla-highlighter-input': getContent() },
    };

    popupConfig.onSubmit = (dialogApi, actionData) => {
      console.log('onSubmit', dialogApi, actionData)
      // if (actionData.name === 'codemirrorOk') {
      //   const doc = document.querySelectorAll('.tox-dialog__body-iframe iframe')[0];
      //   doc.contentWindow.tinymceHighlighterSubmit();
      //   editor.undoManager.add();
      //   // eslint-disable-next-line no-use-before-define
      //   win.close();
      // }
      popup.close();
    };
    popupConfig.onClose = (dialogApi, actionData) => {
      console.log('onClose', dialogApi, actionData)
    };

    popup = editor.windowManager.open(popupConfig);

    console.log(popupConfig, popup);
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
