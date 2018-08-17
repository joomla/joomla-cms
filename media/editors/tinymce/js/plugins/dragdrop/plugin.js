/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/* eslint-disable no-undef */
tinymce.PluginManager.add('jdragdrop', function (editor) {
  // Reset the drop area border
  tinyMCE.DOM.bind(document, 'dragleave', function (e) {
    e.stopPropagation();
    e.preventDefault();
    tinyMCE.activeEditor.contentAreaContainer.style.borderWidth = '1px 0 0';

    return false;
  });

  // The upload logic
  var UploadFile = function UploadFile(file) {
    var fd = new FormData();
    fd.append('Filedata', file);
    fd.append('folder', tinyMCE.activeEditor.settings.mediaUploadPath);

    var xhr = new XMLHttpRequest();

    xhr.upload.onprogress = function (e) {
      var percentComplete = e.loaded / e.total * 100;
      document.querySelector('.bar').style.width = percentComplete + '%';
    };

    var removeProgessBar = function removeProgessBar() {
      // @todo Promisify
      setTimeout(function () {
        var loader = document.querySelector('#jloader');
        loader.parentNode.removeChild(loader);
        editor.contentAreaContainer.style.borderWidth = '1px 0 0 0';
      }, 200);
    };

    xhr.onload = function () {
      var resp = JSON.parse(xhr.responseText);

      if (xhr.status === 200) {
        if (resp.status === '0') {
          removeProgessBar();

          editor.windowManager.alert(resp.message + ': ' + tinyMCE.activeEditor.settings.setCustomDir + resp.location);
        }

        if (resp.status === '1') {
          removeProgessBar();

          // Create the image tag
          var newNode = tinyMCE.activeEditor.getDoc().createElement('img');
          newNode.src = tinyMCE.activeEditor.settings.setCustomDir + resp.location;
          tinyMCE.activeEditor.execCommand('mceInsertContent', false, newNode.outerHTML);
        }
      } else {
        removeProgessBar();
      }
    };

    xhr.onerror = function () {
      removeProgessBar();
    };

    xhr.open('POST', tinyMCE.activeEditor.settings.uploadUri, true);
    xhr.send(fd);
  };

  // Listers for drag and drop
  if (typeof FormData !== 'undefined') {
    // Fix for Chrome
    editor.on('dragenter', function (e) {
      e.stopPropagation();

      return false;
    });

    // Notify user when file is over the drop area
    editor.on('dragover', function (e) {
      e.preventDefault();
      editor.contentAreaContainer.style.borderStyle = 'dashed';
      editor.contentAreaContainer.style.borderWidth = '5px';

      return false;
    });

    // Logic for the dropped file
    editor.on('drop', function (e) {
      // We override only for files
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        var files = [].slice.call(e.dataTransfer.files);
        files.forEach(function (file) {
          // Only images allowed
          if (file.name.toLowerCase().match(/\.(jpg|jpeg|png|gif)$/)) {
            // Create and display the progress bar
            var container = document.createElement('div');
            container.id = 'jloader';
            var innerDiv = document.createElement('div');
            innerDiv.classList.add('progress');
            innerDiv.classList.add('progress-success');
            innerDiv.classList.add('progress-striped');
            innerDiv.classList.add('active');
            innerDiv.style.width = '100%';
            innerDiv.style.height = '30px';
            var progressBar = document.createElement('div');
            progressBar.classList.add('bar');
            progressBar.style.width = '0';
            innerDiv.appendChild(progressBar);
            container.appendChild(innerDiv);
            document.querySelector('.mce-toolbar-grp').appendChild(container);

            // Upload the file(s)
            UploadFile(file);
          }

          e.preventDefault();
        });
      }
      editor.contentAreaContainer.style.borderWidth = '1px 0 0';
    });
  } else {
    Joomla.renderMessages({ error: [Joomla.JText._('PLG_TINY_ERR_UNSUPPORTEDBROWSER')] });
    editor.on('drop', function (e) {
      e.preventDefault();

      return false;
    });
  }
});
