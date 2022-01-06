/**
 * plugin.js
 *
 * Original code by Arjan Haverkamp
 * Copyright 2013-2015 Arjan Haverkamp (arjan@webgear.nl)
 *
 * Adapted for use in Joomla by Dimitrios Grammatikogiannis
 */
window.tinymce.PluginManager.add('highlightPlus', (editor, url) => {
  const showSourceEditor = () => {
    editor.focus();
    editor.selection.collapse(true);

    if (!editor.settings.codemirror) editor.settings.codemirror = {};

    // Insert caret marker
    if (editor.settings.codemirror && editor.settings.codemirror.saveCursorPosition) {
      editor.selection.setContent('<span style="display: none;" class="CmCaReT">&#x0;</span>');
    }

    const buttonsConfig = [
      {
        type: 'custom',
        text: 'Ok',
        name: 'codemirrorOk',
        primary: true,
      },
      {
        type: 'cancel',
        text: 'Cancel',
        name: 'codemirrorCancel',
      },
    ];

    const rects = document.body.getBoundingClientRect();
    const config = {
      title: 'Source code',
      url: `${url}/source.html`,
      width: rects.width - 5,
      height: rects.height - 150,
      resizable: true,
      maximizable: true,
      fullScreen: editor.settings.codemirror.fullscreen,
      saveCursorPosition: false,
      buttons: buttonsConfig,
    };

    config.onAction = (dialogApi, actionData) => {
      if (actionData.name === 'codemirrorOk') {
        const doc = document.querySelectorAll('.tox-dialog__body-iframe iframe')[0];
        doc.contentWindow.tinymceHighlighterSubmit();
        editor.undoManager.add();
        // eslint-disable-next-line no-use-before-define
        win.close();
      }
    };

    const win = editor.windowManager.openUrl(config);

    if (editor.settings.codemirror.fullscreen) {
      win.fullscreen(true);
    }
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
});
