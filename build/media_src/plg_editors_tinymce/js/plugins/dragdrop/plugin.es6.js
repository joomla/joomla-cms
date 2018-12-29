/* eslint-disable no-undef */
tinymce.PluginManager.add('jdragdrop', (editor) => {
  // Reset the drop area border
  tinyMCE.DOM.bind(document, 'dragleave', (e) => {
    e.stopPropagation();
    e.preventDefault();
    tinyMCE.activeEditor.contentAreaContainer.style.borderWidth = '1px 0 0';

    return false;
  });

  // The upload logic
  const UploadFile = (file) => {
    const fd = new FormData();
    fd.append('Filedata', file);
    fd.append('folder', tinyMCE.activeEditor.settings.mediaUploadPath);

    const xhr = new XMLHttpRequest();

    xhr.upload.onprogress = (e) => {
      const percentComplete = (e.loaded / e.total) * 100;
      document.querySelector('.bar').style.width = `${percentComplete}%`;
    };

    const removeProgessBar = () => {
      // @todo Promisify
      setTimeout(() => {
        const loader = document.querySelector('#jloader');
        loader.parentNode.removeChild(loader);
        editor.contentAreaContainer.style.borderWidth = '1px 0 0 0';
      }, 200);
    };

    xhr.onload = () => {
      const resp = JSON.parse(xhr.responseText);

      if (xhr.status === 200) {
        if (resp.status === '0') {
          removeProgessBar();

          editor.windowManager.alert(`${resp.message}: ${tinyMCE.activeEditor.settings.setCustomDir}${resp.location}`);
        }

        if (resp.status === '1') {
          removeProgessBar();

          // Create the image tag
          const newNode = tinyMCE.activeEditor.getDoc().createElement('img');
          newNode.src = tinyMCE.activeEditor.settings.setCustomDir + resp.location;
          tinyMCE.activeEditor.execCommand('mceInsertContent', false, newNode.outerHTML);
        }
      } else {
        removeProgessBar();
      }
    };

    xhr.onerror = () => {
      removeProgessBar();
    };

    xhr.open('POST', tinyMCE.activeEditor.settings.uploadUri, true);
    xhr.send(fd);
  };

  // Listers for drag and drop
  if (typeof FormData !== 'undefined') {
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
      // We override only for files
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const files = [].slice.call(e.dataTransfer.files);
        files.forEach((file) => {
          // Only images allowed
          if (file.name.toLowerCase().match(/\.(jpg|jpeg|png|gif)$/)) {
            // Create and display the progress bar
            const container = document.createElement('div');
            container.id = 'jloader';
            const innerDiv = document.createElement('div');
            innerDiv.classList.add('progress');
            innerDiv.classList.add('progress-success');
            innerDiv.classList.add('progress-striped');
            innerDiv.classList.add('active');
            innerDiv.style.width = '100%';
            innerDiv.style.height = '30px';
            const progressBar = document.createElement('div');
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
    editor.on('drop', (e) => {
      e.preventDefault();

      return false;
    });
  }
});
