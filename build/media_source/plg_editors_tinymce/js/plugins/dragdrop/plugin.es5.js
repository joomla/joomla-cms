(function() {
  // Reset the drop area border
  tinyMCE.DOM.bind(document, 'dragleave', () =>{
    tinyMCE.activeEditor.contentAreaContainer.style.borderWidth = '1px 0 0';
  });

  function blobToBase64(blob) {
    return new Promise((resolve, reject) => {
      Object.assign(new FileReader(), {
        onload: (e) => resolve(e.target.result),
        onerror: (error) => reject(error)
      }).readAsDataURL(blob);
    });
  }

  function helperDialog(urlPath, responseData, editor) {
    function dialogClose(api) {
      var dialogData = api.getData();
      var altEmpty = dialogData.altEmpty ? ' alt=""' : '';
      var altValue = dialogData.altText ? " alt=\"" + dialogData.altText + "\"" : altEmpty;
      var lazyValue = dialogData.isLazy ? ' loading="lazy"' : '';
      var width = dialogData.isLazy ? " width=\"" + responseData.width + "\"" : '';
      var height = dialogData.isLazy ? " height=\"" + responseData.height + "\"" : '';
      editor.execCommand('mceInsertContent', false, "<img src=\"" + urlPath + "\"" + altValue + lazyValue + width + height + "/>");
    }

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
      onSubmit: (api) => {
        dialogClose(api);
        api.close();
      },
      onCancel: (api) => {
        dialogClose(api);
      }
    });
  }

  function uploadFile(name, rawContent, editor) {
    const sliceIndex = rawContent.indexOf('base64') + 7;
    if (sliceIndex < 1) {
      return Promise.reject();
    }

    const url = new URL(`${editor.settings.uploadUri}&path=${editor.settings.comMediaAdapter}`;
    const options = {
      method: 'POST',
      body: JSON.stringify({
        'name': encodeURIComponent(name),
        'action': 'upload',
        'content': rawContent.slice(sliceIndex, rawContent.length),
        'path': encodeURIComponent(editor.settings.parentUploadFolder),
        [encodeURIComponent(editor.settings.csrfToken)]: 1,
      }),
      headers: {
        'Content-Type': 'application/json',
      },
    };

    fetch(url, options)
    .then(function (response) {
      return response.json();
    }).then(function (resp) {
      if (resp.success) {
        const responseData = resp.data;
        let rootFull = Joomla.getOptions('system.paths').rootFull;
        const urlPath = responseData.url.startsWith('http') || responseData.url.startsWith('//') ? responseData.url : response.data.url.split(rootFull)[1];

        helperDialog(urlPath, responseData, editor);
      } else {
        editor.windowManager.alert(resp.message);
        return Promise.reject();
      }
    }).catch(function (error) {
      editor.windowManager.alert(`${error.statusText} ${error.status}`);
      return Promise.reject();
    });
  }

  window.tinymce.PluginManager.add('jdragndrop', function (editor) {
    'use strict';

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

    // Logic for the dropped file
    editor.on('drop', (e) => {
      if (!e.dataTransfer || !e.dataTransfer.files || e.dataTransfer.files.length !== 1) {
        return;
      }

      // We override only for files
      e.preventDefault();
      for (const file of [...e.dataTransfer.files]) {
        // Only images allowed
        if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
          return;
        }

        Promise
          .all([blobToBase64(file)])
          .then((result) => {
            // Upload the file

            uploadFile(file.name, result[0], editor);
          })
          .catch((error) => {});
      }

      editor.contentAreaContainer.style.borderWidth = '1px 0 0';
    });
  });
})();
