/**
 * plugin.js
 *
 * Original code by Arjan Haverkamp
 * Copyright 2013-2015 Arjan Haverkamp (arjan@webgear.nl)
 */
window.tinymce.PluginManager.add('highlightPlus', (editor, url) => {
  const showSourceEditor = () => {
    editor.focus();
    editor.selection.collapse(true);

    editor.options.register('codemirror', {
      processor: 'object',
      default: {
        codemirrorWidth: 800,
        codemirrorHeight: 550,
        fullscreen: false,
        indentOnInit: true,
        config: {
          mode: 'htmlmixed',
          theme: 'default',
          lineNumbers: true,
          lineWrapping: true,
          indentUnit: 2,
          tabSize: 2,
          indentWithTabs: true,
          matchBrackets: true,
          saveCursorPosition: false,
          styleActiveLine: true,
        },
      },
    });

    const cmSettings = editor.options.get('codemirror');

    // Insert caret marker
    if (cmSettings.config.saveCursorPosition) {
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

    const config = {
      title: 'Source code',
      url: `${url}/source.html`,
      width: cmSettings.width,
      height: cmSettings.height,
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
