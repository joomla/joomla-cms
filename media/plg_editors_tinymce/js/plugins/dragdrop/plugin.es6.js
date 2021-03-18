/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/* eslint-disable no-undef */
tinymce.PluginManager.add('jdragndrop', editor => {
  // Reset the drop area border
  tinyMCE.DOM.bind(document, 'dragleave', e => {
    e.stopPropagation();
    e.preventDefault();
    tinyMCE.activeEditor.contentAreaContainer.style.borderWidth = '1px 0 0';
    return false;
  }); // Fix for Chrome

  editor.on('dragenter', e => {
    e.stopPropagation();
    return false;
  }); // Notify user when file is over the drop area

  editor.on('dragover', e => {
    e.preventDefault();
    editor.contentAreaContainer.style.borderStyle = 'dashed';
    editor.contentAreaContainer.style.borderWidth = '5px';
    return false;
  });

  function uploadFile(name, content) {
    const url = `${tinyMCE.activeEditor.settings.uploadUri}&path=${tinyMCE.activeEditor.settings.comMediaAdapter}`;
    const data = {
      [tinyMCE.activeEditor.settings.csrfToken]: '1',
      name,
      content,
      parent: tinyMCE.activeEditor.settings.parentUploadFolder
    };
    Joomla.request({
      url,
      method: 'POST',
      data: JSON.stringify(data),
      headers: {
        'Content-Type': 'application/json'
      },
      onSuccess: resp => {
        let response;

        try {
          response = JSON.parse(resp);
        } catch (e) {
          editor.windowManager.alert(`${Joomla.Text._('JERROR')}: {e}`);
        }

        if (response.data && response.data.path) {
          let urlPath; // For local adapters use relative paths

          if (/local-/.test(response.data.adapter)) {
            const {
              rootFull
            } = Joomla.getOptions('system.paths');
            urlPath = `${response.data.thumb_path.split(rootFull)[1]}`;
          } else if (response.data.thumb_path) {
            // Absolute path for different domain
            urlPath = response.data.thumb_path;
          }

          tinyMCE.activeEditor.execCommand('mceInsertContent', false, `<img loading="lazy" src="${urlPath}" alt=""/>`);
        }
      },
      onError: xhr => {
        editor.windowManager.alert(`Error: ${xhr.statusText}`);
      }
    });
  }

  function readFile(file) {
    // Create a new file reader instance
    const reader = new FileReader(); // Add the on load callback

    reader.onload = progressEvent => {
      const {
        result
      } = progressEvent.target;
      const splitIndex = result.indexOf('base64') + 7;
      const content = result.slice(splitIndex, result.length); // Upload the file

      uploadFile(file.name, content);
    };

    reader.readAsDataURL(file);
  } // Listers for drag and drop


  if (typeof FormData !== 'undefined') {
    // Logic for the dropped file
    editor.on('drop', e => {
      e.preventDefault(); // We override only for files

      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const files = [].slice.call(e.dataTransfer.files);
        files.forEach(file => {
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
    Joomla.renderMessages({
      error: [Joomla.JText._('PLG_TINY_ERR_UNSUPPORTEDBROWSER')]
    });
    editor.on('drop', e => {
      e.preventDefault();
      return false;
    });
  }
});