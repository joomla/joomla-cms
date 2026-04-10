/**
 * File reader helper
 *
 * @param {*} file the file
 * @param {*} callback function to callback
 *
 * @TODO replace it with await new Response(file)
 */
function readFile(file, callback) {
  // Create a new file reader instance
  const reader = new FileReader();

  // Add the on load callback
  reader.onload = event => {
    const {
      result
    } = event.target;
    const splitIndex = result.indexOf('base64') + 7;
    const content = result.slice(splitIndex, result.length);

    // Upload the file
    callback(file.name, content);
  };
  reader.readAsDataURL(file);
}
window.tinymce.PluginManager.add('jdragndrop', editor => {
  const registerOption = editor.options.register;
  registerOption('uploadUri', {
    processor: 'string'
  });
  registerOption('parentUploadFolder', {
    processor: 'string'
  });

  // Reset the drop area border
  const dragleaveCallback = e => {
    if (!e.dataTransfer.types.includes('Files')) return;
    e.stopPropagation();
    e.preventDefault();
    editor.contentAreaContainer.style.borderWidth = '0';
  };
  window.tinyMCE.DOM.bind(document, 'dragleave', dragleaveCallback);

  // Remove listener when editor are removed
  editor.on('remove', () => window.tinyMCE.DOM.unbind(document, 'dragleave', dragleaveCallback));

  // Fix for Chrome
  editor.on('dragenter', e => {
    if (!e.dataTransfer.types.includes('Files')) return;
    e.stopPropagation();
  });

  // Notify user when file is over the drop area
  editor.on('dragover', e => {
    if (!e.dataTransfer.types.includes('Files')) return;
    e.preventDefault();
    editor.contentAreaContainer.style.borderStyle = 'dashed';
    editor.contentAreaContainer.style.borderWidth = '5px';
  });
  async function uploadFile(name, content) {
    const settings = editor.options.get;
    Joomla.request({
      url: `${settings('uploadUri')}&path=${settings('parentUploadFolder')}`,
      method: 'POST',
      data: JSON.stringify({
        name,
        content,
        parent: settings('parentUploadFolder')
      }),
      headers: {
        'Content-Type': 'application/json'
      },
      promise: true
    }).then(resp => {
      let response;
      try {
        response = JSON.parse(resp.responseText);
      } catch (e) {
        editor.windowManager.alert(`${Joomla.Text._('ERROR')}: {${e}}`);
      }
      if (response.data && response.data.path) {
        const responseData = response.data;
        let urlPath;
        const paths = Joomla.getOptions('system.paths');
        const {
          rootFull
        } = paths;
        const parts = response.data.url.split(rootFull);
        if (parts.length > 1) {
          // For local adapters use relative paths
          urlPath = `${parts[1]}`;
        } else if (responseData.url) {
          // Absolute path for different domain
          urlPath = responseData.url;
        }
        const dialogClose = function dialogClose(api) {
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
            items: [{
              type: 'input',
              name: 'altText',
              label: Joomla.Text._('PLG_TINY_DND_ALTTEXT')
            }, {
              type: 'checkbox',
              name: 'altEmpty',
              label: Joomla.Text._('PLG_TINY_DND_EMPTY_ALT')
            }, {
              type: 'checkbox',
              name: 'isLazy',
              label: Joomla.Text._('PLG_TINY_DND_LAZYLOADED')
            }]
          },
          buttons: [{
            type: 'cancel',
            text: 'Cancel'
          }, {
            type: 'submit',
            name: 'submitButton',
            text: 'Save',
            primary: true
          }],
          initialData: {
            altText: '',
            isLazy: true,
            altEmpty: false
          },
          onSubmit: api => {
            dialogClose(api);
            api.close();
          },
          onCancel: api => dialogClose(api)
        });
      }
    }).catch(xhr => {
      let message = `Error: ${xhr.statusText}`;
      if (xhr.status === 409) {
        message = Joomla.Text._('PLG_TINY_DND_FILE_EXISTS_ERROR').replace('%s', `${settings('parentUploadFolder')}/${name}`);
      }
      editor.windowManager.alert(message);
    });
  }

  // Logic for the dropped file
  editor.on('drop', e => {
    if (!e.dataTransfer.types.includes('Files')) return;
    e.preventDefault();

    // Read and upload files
    if (e.dataTransfer.files.length > 0) {
      Array.from(e.dataTransfer.files).forEach(file => {
        // Only images allowed
        if (file.name.toLowerCase().match(/\.(jpg|jpeg|png|gif|webp)$/)) {
          // Upload the file(s)
          readFile(file, uploadFile);
        }
      });
    }
    editor.contentAreaContainer.style.borderWidth = '0';
  });
  return {
    getMetadata: () => ({
      name: 'Drag and Drop (Joomla)',
      url: 'https://www.joomla.org/'
    })
  };
});
