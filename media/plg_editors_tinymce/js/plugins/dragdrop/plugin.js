/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

/* eslint-disable no-undef */
tinymce.PluginManager.add('jdragndrop', function (editor) {
  // Reset the drop area border
  tinyMCE.DOM.bind(document, 'dragleave', function (e) {
    e.stopPropagation();
    e.preventDefault();
    tinyMCE.activeEditor.contentAreaContainer.style.borderWidth = '1px 0 0';
    return false;
  }); // Fix for Chrome

  editor.on('dragenter', function (e) {
    e.stopPropagation();
    return false;
  }); // Notify user when file is over the drop area

  editor.on('dragover', function (e) {
    e.preventDefault();
    editor.contentAreaContainer.style.borderStyle = 'dashed';
    editor.contentAreaContainer.style.borderWidth = '5px';
    return false;
  });

  function uploadFile(name, content) {
    var _data;

    var url = "".concat(tinyMCE.activeEditor.settings.uploadUri, "&path=").concat(tinyMCE.activeEditor.settings.comMediaAdapter);
    var data = (_data = {}, _defineProperty(_data, tinyMCE.activeEditor.settings.csrfToken, '1'), _defineProperty(_data, "name", name), _defineProperty(_data, "content", content), _defineProperty(_data, "parent", tinyMCE.activeEditor.settings.parentUploadFolder), _data);
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
          editor.windowManager.alert("".concat(Joomla.Text._('JERROR'), ": {e}"));
        }

        if (response.data && response.data.path) {
          var urlPath; // For local adapters use relative paths

          if (/local-/.test(response.data.adapter)) {
            var _Joomla$getOptions = Joomla.getOptions('system.paths'),
                rootFull = _Joomla$getOptions.rootFull;

            urlPath = "".concat(response.data.thumb_path.split(rootFull)[1]);
          } else if (response.data.thumb_path) {
            // Absolute path for different domain
            urlPath = response.data.thumb_path;
          }

          tinyMCE.activeEditor.execCommand('mceInsertContent', false, "<img loading=\"lazy\" src=\"".concat(urlPath, "\" alt=\"\"/>"));
        }
      },
      onError: function onError(xhr) {
        editor.windowManager.alert("Error: ".concat(xhr.statusText));
      }
    });
  }

  function readFile(file) {
    // Create a new file reader instance
    var reader = new FileReader(); // Add the on load callback

    reader.onload = function (progressEvent) {
      var result = progressEvent.target.result;
      var splitIndex = result.indexOf('base64') + 7;
      var content = result.slice(splitIndex, result.length); // Upload the file

      uploadFile(file.name, content);
    };

    reader.readAsDataURL(file);
  } // Listers for drag and drop


  if (typeof FormData !== 'undefined') {
    // Logic for the dropped file
    editor.on('drop', function (e) {
      e.preventDefault(); // We override only for files

      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        var files = [].slice.call(e.dataTransfer.files);
        files.forEach(function (file) {
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
    editor.on('drop', function (e) {
      e.preventDefault();
      return false;
    });
  }
});