/**
 * Original code by Arjan Haverkamp
 * Copyright 2013-2015 Arjan Haverkamp (arjan@webgear.nl)
 */

window.tinymce.PluginManager.add('highlightPlus', (editor) => {
  const showSourceEditor = () => {
    const cmSettings = editor.options.get('codemirror');

    if (!cmSettings) {
      throw new Error('Codemirror settings are not defined');
    }

    const iframeUrl = new URL(`${Joomla.getOptions('system.paths').baseFull}index.php?option=com_ajax&group=editors&plugin=tinymceHighlighter&format=raw`);
    editor.focus();
    editor.selection.collapse(true);

    // Insert caret marker
    if (cmSettings.config.saveCursorPosition) {
      editor.selection.setContent('<span class="CmCaReT">&#x0;</span>');
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

    const config = {
      title: 'Source code',
      url: iframeUrl.toString(),
      width: window.innerWidth - 50,
      height: window.innerHeight - 150,
      resizable: true,
      maximizable: true,
      fullScreen: cmSettings.fullscreen,
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

    if (cmSettings.fullscreen) {
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
