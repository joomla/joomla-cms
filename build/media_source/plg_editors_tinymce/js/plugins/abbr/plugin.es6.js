tinymce.PluginManager.add('abbr', function (editor) {

  function getCurrentAbbr() {
    const node = editor.selection.getNode();
    return editor.dom.getParent(node, 'abbr');
  }

  function openDialog() {
    const abbr = getCurrentAbbr();
    const selectedText = abbr
      ? abbr.innerText
      : editor.selection.getContent({ format: 'text' });

    if (!selectedText) {
      editor.notificationManager.open({
        text: Joomla.Text._('PLG_TINY_ABBREVIATION_WARNING_NO_SELECTION'),
        type: 'warning'
      });
      return;
    }

    editor.windowManager.open({
      title: abbr ? Joomla.Text._('PLG_TINY_ABBREVIATION_EDIT') : Joomla.Text._('PLG_TINY_ABBREVIATION_INSERT'),

    initialData: {
      title: abbr ? abbr.getAttribute('title') || '' : ''
    },

      body: {
        type: 'panel',
        items: [
          {
            type: 'input',
            name: 'title',
            label: Joomla.Text._('PLG_TINY_ABBREVIATION_DESCRIPTION_LABEL'),
            placeholder: 'e.g. Web Content Accessibility Guidelines'
          }
        ]
      },
      buttons: [
        { type: 'cancel', text: 'Cancel' },
        {
          type: 'submit',
          text: abbr ? 'Update' : 'Insert',
          primary: true
        }
      ],
      onSubmit(api) {
        const data = api.getData();

        if (!data.title) {
          editor.notificationManager.open({
            text: Joomla.Text._('PLG_TINY_ABBREVIATION_WARNING_NO_DESCRIPTION'),
            type: 'warning'
          });
          return;
        }

        if (abbr) {
          editor.dom.setAttrib(abbr, 'title', data.title);
        } else {
          editor.insertContent(
            `<abbr title="${editor.dom.encode(data.title)}">${editor.dom.encode(selectedText)}</abbr>`
          );
        }

        api.close();
      }
    });
  }

  function removeAbbr() {
    const abbr = getCurrentAbbr();

    if (!abbr) {
      editor.notificationManager.open({
        text: Joomla.Text._('PLG_TINY_ABBREVIATION_WARNING_REMOVE'),
        type: 'warning'
      });
      return;
    }

    // Replace <abbr> with its inner content
    const content = abbr.innerHTML;
    editor.dom.replace(editor.dom.createFragment(content), abbr);
  }

  /* icons */
  editor.ui.registry.addIcon('abbr', '<svg height="24" width="24"><path d="M5 8a1 1 0 0 0 2 0V7h1a1 1 0 0 0 0-2H7V4a1 1 0 0 0-2 0v1H4a1 1 0 0 0 0 2h1Zm13-3h-6a1 1 0 0 0 0 2h6a1 1 0 0 1 1 1v9.72l-1.57-1.45a1 1 0 0 0-.68-.27H8a1 1 0 0 1-1-1v-3a1 1 0 0 0-2 0v3a3 3 0 0 0 3 3h8.36l3 2.73A1 1 0 0 0 20 21a1.1 1.1 0 0 0 .4-.08A1 1 0 0 0 21 20V8a3 3 0 0 0-3-3Z"></path></svg>');

  editor.ui.registry.addIcon('abbr_remove', '<svg height="24" width="24"><path d="M19,6H15a1,1,0,0,0,0,2h4a1,1,0,0,1,1,1v9.72l-1.57-1.45a1,1,0,0,0-.68-.27H9a1,1,0,0,1-1-1V15a1,1,0,0,0-2,0v1a3,3,0,0,0,3,3h8.36l3,2.73A1,1,0,0,0,21,22a1.1,1.1,0,0,0,.4-.08A1,1,0,0,0,22,21V9A3,3,0,0,0,19,6Zm-8.46,4.54A5,5,0,1,0,7,12,5,5,0,0,0,10.54,10.54ZM4,7A3,3,0,0,1,7,4a3,3,0,0,1,1.28.3l-4,4A3,3,0,0,1,4,7ZM9.7,5.71A3,3,0,0,1,10,7,3,3,0,0,1,5.72,9.7Z"></path></svg>');

  /* Buttons */
  editor.ui.registry.addButton('abbr', {
    title: 'Abbr',
    icon: 'abbr',
    tooltip: Joomla.Text._('PLG_TINY_TOOLBAR_BUTTON_ABBREVIATION'),
    onAction: openDialog,
  });

  editor.ui.registry.addButton('abbr_remove', {
    title: 'Remove Abbr',
    icon: 'abbr_remove',
    tooltip: Joomla.Text._('PLG_TINY_TOOLBAR_BUTTON_REMOVE_ABBREVIATION'),
    onAction: removeAbbr,
  });

  return {
    getMetadata() {
      return {
        name: 'Abbreviation Plugin (Joomla)',
        url: 'https://www.joomla.org'
      };
    }
  };
});
