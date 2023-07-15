(function () {
  'use strict';

  /* eslint-disable no-undef */
  tinymce.PluginManager.add('jdragndrop', function (editor) {
    // Reset the drop area border
    var dragleaveCallback = function (e) {
      if (!e.dataTransfer.types.includes('Files')) return;
      e.stopPropagation();
      e.preventDefault();
      editor.contentAreaContainer.style.borderWidth = '0';
      return false;
    }
    tinyMCE.DOM.bind(document, 'dragleave', dragleaveCallback);

    // Remove listener when editor are removed
    editor.on('remove', function () {
      tinyMCE.DOM.unbind(document, 'dragleave', dragleaveCallback);
    });

    // Fix for Chrome
    editor.on('dragenter', function (e) {
      if (!e.dataTransfer.types.includes('Files')) return;
      e.stopPropagation();
      return false;
    });

    // Notify user when file is over the drop area
    editor.on('dragover', function (e) {
      if (!e.dataTransfer.types.includes('Files')) return;
      e.preventDefault();
      editor.contentAreaContainer.style.borderStyle = 'dashed';
      editor.contentAreaContainer.style.borderWidth = '5px';
      return false;
    });

    function uploadFile(name, content) {
      var _data;

      var url = editor.settings.uploadUri + "&path=" + editor.settings.comMediaAdapter + editor.settings.parentUploadFolder;
      var data = (_data = {}, _data[editor.settings.csrfToken] = '1', _data.name = name, _data.content = content, _data.parent = editor.settings.parentUploadFolder, _data);
      Joomla.request({
        url: url,
        method: 'POST',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: function onSuccess(resp) {
          var response;

          try {
            response = JSON.parse(resp);
          } catch (e) {
            editor.windowManager.alert(Joomla.Text._('ERROR') + ": {e}");
          }

          if (response.data && response.data.path) {
            var responseData = response.data;
            var urlPath; // For local adapters use relative paths

            var _Joomla$getOptions = Joomla.getOptions('system.paths'),
            rootFull = _Joomla$getOptions.rootFull;
            var parts = response.data.url.split(rootFull);
            if (parts.length > 1) {
              urlPath = "" + parts[1];
            } else if (responseData.url) {
              // Absolute path for different domain
              urlPath = responseData.url;
            }

            var dialogClose = function dialogClose(api) {
              var dialogData = api.getData();
              var altEmpty = dialogData.altEmpty ? ' alt=""' : '';
              var altValue = dialogData.altText ? " alt=\"" + dialogData.altText + "\"" : altEmpty;
              var lazyValue = dialogData.isLazy ? ' loading="lazy"' : '';
              var width = dialogData.isLazy ? " width=\"" + responseData.width + "\"" : '';
              var height = dialogData.isLazy ? " height=\"" + responseData.height + "\"" : '';
              editor.execCommand('mceInsertContent', false, "<img src=\"" + urlPath + "\"" + altValue + lazyValue + width + height + "/>");
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
              onSubmit: function onSubmit(api) {
                dialogClose(api);
                api.close();
              },
              onCancel: function onCancel(api) {
                dialogClose(api);
              }
            });
          }
        },
        onError: function onError(xhr) {
          editor.windowManager.alert("Error: " + xhr.statusText);
        }
      });
    }

    function readFile(file) {
      // Create a new file reader instance
      var reader = new FileReader();

      // Add the on load callback
      reader.onload = function (progressEvent) {
        var result = progressEvent.target.result;
        var splitIndex = result.indexOf('base64') + 7;
        var content = result.slice(splitIndex, result.length);

        // Upload the file
        uploadFile(file.name, content);
      };

      reader.readAsDataURL(file);
    }

    // Logic for the dropped file
    editor.on('drop', function (e) {
      if (!e.dataTransfer.types.includes('Files')) return;
      e.preventDefault();

      // Read and upload files
      if (e.dataTransfer.files.length > 0) {
        var files = [].slice.call(e.dataTransfer.files);
        files.forEach(function (file) {
          // Only images allowed
          if (file.name.toLowerCase().match(/\.(jpg|jpeg|png|gif|webp)$/)) {
            // Upload the file(s)
            readFile(file);
          }
        });
      }

      editor.contentAreaContainer.style.borderWidth = '0';
    });
  });
}());
