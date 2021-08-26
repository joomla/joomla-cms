/* eslint-disable no-undef */
tinymce.PluginManager.add('jdragndrop', (editor) => {
  let responseData;
  // Reset the drop area border
  tinyMCE.DOM.bind(document, 'dragleave', (e) => {
    e.stopPropagation();
    e.preventDefault();
    editor.contentAreaContainer.style.borderWidth = '1px 0 0';

    return false;
  });

  // Fix for Chrome
  editor.on('dragenter', (e) => {
    e.stopPropagation();

    return false;
  });

  // Notify user when file is over the drop area
  editor.on('dragover', (e) => {
    e.preventDefault();
    editor.contentAreaContainer.style.borderStyle = 'dashed';
    editor.contentAreaContainer.style.borderWidth = '5px';

    return false;
  });

  function uploadFile(name, content) {
    const url = `${editor.settings.uploadUri}&path=${editor.settings.comMediaAdapter}`;
    const data = {
      [editor.settings.csrfToken]: '1',
      name,
      content,
      parent: editor.settings.parentUploadFolder,
    };

    Joomla.request({
      url,
      method: 'POST',
      data: JSON.stringify(data),
      headers: { 'Content-Type': 'application/json' },
      onSuccess: (resp) => {
        let response;

        try {
          response = JSON.parse(resp);
        } catch (e) {
          editor.windowManager.alert(`${Joomla.Text._('JERROR')}: {e}`);
        }

        if (response.data && response.data.path) {
          responseData = response.data;
          let urlPath;
          // For local adapters use relative paths
          if (/local-/.test(responseData.adapter)) {
            const { rootFull } = Joomla.getOptions('system.paths');

            urlPath = `${response.data.thumb_path.split(rootFull)[1]}`;
          } else if (responseData.thumb_path) {
            // Absolute path for different domain
            urlPath = responseData.thumb_path;
          }

          const dialogClose = (api) => {
            const dialogData = api.getData();
            const altEmpty = dialogData.altEmpty ? ' alt=""' : '';
            const altValue = dialogData.altText ? ` alt="${dialogData.altText}"` : altEmpty;
            const lazyValue = dialogData.isLazy ? ' loading="lazy"' : '';
            const width = dialogData.isLazy ? ` width="${responseData.width}"` : '';
            const height = dialogData.isLazy ? ` height="${responseData.height}"` : '';
            editor.execCommand('mceInsertContent', false, `<img src="${urlPath}"${altValue}${lazyValue}${width}${height}/>`);
          };

          editor.windowManager.open({
            title: Joomla.Text._('PLG_TINY_DND_ADDITIONALDATA'),
            body: {
              type: 'panel',
              items: [
                {
                  type: 'input',
                  name: 'altText',
                  label: Joomla.Text._('PLG_TINY_DND_ALTTEXT'),
                },
                {
                  type: 'checkbox',
                  name: 'altEmpty',
                  label: Joomla.Text._('PLG_TINY_DND_EMPTY_ALT'),
                },
                {
                  type: 'checkbox',
                  name: 'isLazy',
                  label: Joomla.Text._('PLG_TINY_DND_LAZYLOADED'),
                },
              ],
            },
            buttons: [
              {
                type: 'cancel',
                text: 'Cancel',
              },
              {
                type: 'submit',
                name: 'submitButton',
                text: 'Save',
                primary: true,
              },
            ],
            initialData: {
              altText: '',
              isLazy: true,
              altEmpty: false,
            },
            onSubmit(api) {
              dialogClose(api);
              api.close();
            },
            onCancel(api) {
              dialogClose(api);
            },
          });
        }
      },
      onError: (xhr) => {
        editor.windowManager.alert(`Error: ${xhr.statusText}`);
      },
    });
  }

  function readFile(file) {
    // Create a new file reader instance
    const reader = new FileReader();

    // Add the on load callback
    reader.onload = (progressEvent) => {
      const { result } = progressEvent.target;
      const splitIndex = result.indexOf('base64') + 7;
      const content = result.slice(splitIndex, result.length);

      // Upload the file
      uploadFile(file.name, content);
    };

    reader.readAsDataURL(file);
  }

  // Listers for drag and drop
  if (typeof FormData !== 'undefined') {
    // Logic for the dropped file
    editor.on('drop', (e) => {
      e.preventDefault();
      // We override only for files
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const files = [].slice.call(e.dataTransfer.files);
        files.forEach((file) => {
          // Only images allowed
          if (file.name.toLowerCase().match(/\.(jpg|jpeg|png|gif)$/)) {
            // Upload the file(s)
            readFile(file);
          }
        });
      }
      editor.contentAreaContainer.style.borderWidth = '1px 0 0';
    });
  } else {
    Joomla.renderMessages({ error: [Joomla.JText._('PLG_TINY_ERR_UNSUPPORTEDBROWSER')] });
    editor.on('drop', (e) => {
      e.preventDefault();

      return false;
    });
  }
});
