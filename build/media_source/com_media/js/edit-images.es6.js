/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};

(() => {
  'use strict';

  // Get the options from Joomla.optionStorage
  const options = Joomla.getOptions('com_media', {});

  if (!options) {
    throw new Error('Initialization error "edit-images.js"');
  }

  // Initiate the registry
  Joomla.MediaManager.Edit.original = {
    filename: options.uploadPath.split('/').pop(),
    extension: options.uploadPath.split('.').pop(),
    contents: `data:image/${options.uploadPath.split('.').pop()};base64,${options.contents}`,
  };
  Joomla.MediaManager.Edit.history = {};
  Joomla.MediaManager.Edit.current = {};

  const addHistoryPoint = (data) => {
    if (Joomla.MediaManager.Edit.original !== Joomla.MediaManager.Edit.current.contents) {
      const key = Joomla.MediaManager.Edit.history.lastKey;
      if (Joomla.MediaManager.Edit.history[key] && Joomla.MediaManager.Edit.history[key - 1]
        && Joomla.MediaManager.Edit.history[key].file
        === Joomla.MediaManager.Edit.history[key - 1].file) {
        return;
      }
      Joomla.MediaManager.Edit.history[key + 1] = {};
      Joomla.MediaManager.Edit.history[key + 1].data = {};
      if (data === 'reset') {
        Joomla.MediaManager.Edit.history[key + 1].file = Joomla.MediaManager.Edit.original.contents;
      } else {
        Joomla.MediaManager.Edit.history[key + 1].file = Joomla.MediaManager.Edit.current.contents;
        if (Joomla.MediaManager.Edit.history[key] && Joomla.MediaManager.Edit.history[key].data) {
          Object.assign(Joomla.MediaManager.Edit.history[key + 1].data,
            Joomla.MediaManager.Edit.history[key].data);
        } else {
          Object.assign(Joomla.MediaManager.Edit.history[key + 1].data, data.detail);
        }
        // eslint-disable-next-line max-len
        Joomla.MediaManager.Edit.history[key + 1].data[data.detail.plugin] = data.detail[data.detail.plugin];
      }

      // eslint-disable-next-line max-len
      Joomla.MediaManager.Edit.history.lastKey = Number(Joomla.MediaManager.Edit.history.lastKey) + 1;
      Joomla.MediaManager.Edit.history.current = Number(Joomla.MediaManager.Edit.history.lastKey);
      if (Joomla.MediaManager.Edit.history.lastKey > options.historyLength) {
        // eslint-disable-next-line max-len
        delete Joomla.MediaManager.Edit.history[Joomla.MediaManager.Edit.history.lastKey - options.historyLength];
      }
    }
  };

  const activate = (name, data) => {
    if (!data.contents) {
      return;
    }
    // Create the images for edit and preview
    const baseContainer = document.getElementById('media-manager-edit-container');
    const editContainer = document.createElement('div');
    const previewContainer = document.createElement('div');
    const imageSrc = document.createElement('img');
    const imagePreview = document.createElement('img');

    baseContainer.innerHTML = '';
    imageSrc.src = data.contents;
    imageSrc.id = 'image-source';
    imageSrc.style.maxWidth = '100%';
    imagePreview.src = data.contents;
    imagePreview.id = 'image-preview';
    imagePreview.style.maxWidth = '100%';
    editContainer.classList.add('hidden');

    editContainer.appendChild(imageSrc);
    baseContainer.appendChild(editContainer);

    previewContainer.appendChild(imagePreview);
    baseContainer.appendChild(previewContainer);

    // Activate the first plugin
    Joomla.MediaManager.Edit[name.toLowerCase()].Activate(data);

    // Set first history point
    if (Object.keys(Joomla.MediaManager.Edit.history).length === 0) {
      Joomla.MediaManager.Edit.history.current = 0;
      Joomla.MediaManager.Edit.history.lastKey = 0;
      addHistoryPoint('reset');
    }
  };

  // Reset the image to the initial state
  Joomla.MediaManager.Edit.Reset = (current) => {
    Joomla.MediaManager.Edit.current.contents = Joomla.MediaManager.Edit.history[current].file;
    // Clear the DOM
    const container = document.getElementById('media-manager-edit-container');
    container.innerHTML = '';

    // Reactivate the current plugin
    const tabsUlElement = document.getElementById('myTab').firstElementChild;

    if (tabsUlElement.tagName !== 'UL') {
      return;
    }

    const links = [].slice.call(tabsUlElement.querySelectorAll('a'));

    links.forEach((link) => {
      if (!link.hasAttribute('active')) {
        return;
      }

      Joomla.MediaManager.Edit[link.id.replace('tab-attrib-', '').toLowerCase()].Deactivate();

      const data = Joomla.MediaManager.Edit.current;

      link.click();

      // Move the container to the correct tab
      const mediaContainer = document.getElementById('media-manager-edit-container');
      const tab = document.getElementById(link.id.replace('tab-', ''));
      tab.insertAdjacentElement('afterbegin', mediaContainer);

      activate(link.id.replace('tab-attrib-', ''), data);
    });
  };

  // Create history entry
  window.addEventListener('mediaManager.history.point', (data) => {
    addHistoryPoint(data);
  });

  Joomla.MediaManager.Edit.Undo = () => {
    if (Joomla.MediaManager.Edit.history.current && Joomla.MediaManager.Edit.history.current - 1
      > Joomla.MediaManager.Edit.history.lastKey
      - Object.keys(Joomla.MediaManager.Edit.history).length + 2) {
      // eslint-disable-next-line max-len
      Joomla.MediaManager.Edit.history.current = Number(Joomla.MediaManager.Edit.history.current) - 1;
      Joomla.MediaManager.Edit.Reset(Joomla.MediaManager.Edit.history.current);
    }
  };

  Joomla.MediaManager.Edit.Redo = () => {
    if (Joomla.MediaManager.Edit.history.current && Joomla.MediaManager.Edit.history.current + 1
      < Joomla.MediaManager.Edit.history.lastKey) {
      // eslint-disable-next-line max-len
      Joomla.MediaManager.Edit.history.current = Number(Joomla.MediaManager.Edit.history.current) + 1;
      Joomla.MediaManager.Edit.Reset(Joomla.MediaManager.Edit.history.current);
    }
  };

  // @TODO Create the progress bar
  Joomla.MediaManager.Edit.createProgressBar = () => { };

  // @TODO Update the progress bar
  Joomla.MediaManager.Edit.updateProgressBar = (/* position */) => { };

  // @TODO Remove the progress bar
  Joomla.MediaManager.Edit.removeProgressBar = () => { };

  // Customize the buttons
  Joomla.submitbutton = (task) => {
    const format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;
    const pathName = window.location.pathname.replace(/&view=file.*/g, '');
    const name = options.uploadPath.split('/').pop();
    const forUpload = {
      name,
      content: Joomla.MediaManager.Edit.current.contents.replace(`data:image/${format};base64,`, ''),
    };

    // eslint-disable-next-line prefer-destructuring
    const uploadPath = options.uploadPath;

    const url = `${options.apiBaseUrl}&task=api.files&path=${uploadPath}`;

    const type = 'application/json';

    forUpload[options.csrfToken] = '1';

    let fileDirectory = uploadPath.split('/');
    fileDirectory.pop();
    fileDirectory = fileDirectory.join('/');

    // If we are in root add a backslash
    if (fileDirectory.endsWith(':')) {
      fileDirectory = `${fileDirectory}/`;
    }

    switch (task) {
      case 'apply':
        Joomla.UploadFile.exec(name, JSON.stringify(forUpload), uploadPath, url, type);
        Joomla.MediaManager.Edit.Reset(Joomla.MediaManager.Edit.history.current);
        break;
      case 'save':
        Joomla.UploadFile.exec(name, JSON.stringify(forUpload), uploadPath, url, type);
        window.location = `${pathName}?option=com_media&path=${fileDirectory}`;
        break;
      case 'cancel':
        if (window.self !== window.top) {
          window.location = `${pathName}?option=com_media&path=${fileDirectory}&tmpl=component`;
        } else {
          window.location = `${pathName}?option=com_media&path=${fileDirectory}`;
        }
        break;
      case 'reset':
        addHistoryPoint('reset');
        Joomla.MediaManager.Edit.Reset(Object.keys(Joomla.MediaManager.Edit.history).length - 1);
        break;
      case 'undo':
        Joomla.MediaManager.Edit.Undo();
        break;
      case 'redo':
        Joomla.MediaManager.Edit.Redo();
        break;
      default:
        break;
    }
  };

  /**
   * @TODO Extend Joomla.request and drop this code!!!!
   */
  // The upload object
  Joomla.UploadFile = {};

  /**
   * @TODO Extend Joomla.request and drop this code!!!!
   */
  Joomla.UploadFile.exec = (name, data, uploadPath, url, type) => {
    const xhr = new XMLHttpRequest();

    xhr.upload.onprogress = (e) => {
      Joomla.MediaManager.Edit.updateProgressBar((e.loaded / e.total) * 100);
    };

    xhr.onload = () => {
      let resp;
      try {
        resp = JSON.parse(xhr.responseText);
      } catch (er) {
        resp = null;
      }

      if (resp) {
        if (xhr.status === 200) {
          if (resp.success === true) {
            Joomla.MediaManager.Edit.removeProgressBar();
          }

          if (resp.status === '1') {
            Joomla.renderMessages({ success: [resp.message] }, 'true');
            Joomla.MediaManager.Edit.removeProgressBar();
          }
        }
      } else {
        Joomla.MediaManager.Edit.removeProgressBar();
      }
    };

    xhr.onerror = () => {
      Joomla.MediaManager.Edit.removeProgressBar();
    };

    xhr.open('PUT', url, true);
    xhr.setRequestHeader('Content-Type', type);
    Joomla.MediaManager.Edit.createProgressBar();
    xhr.send(data);
  };

  // Once the DOM is ready, initialize everything
  document.addEventListener('DOMContentLoaded', () => {
    const func = () => {
      const tabsUlElement = document.getElementById('myTab').firstElementChild;

      if (tabsUlElement.tagName !== 'UL') {
        setTimeout(func, 50);
        return;
      }

      const links = [].slice.call(tabsUlElement.querySelectorAll('a'));

      if (links[0]) {
        activate(links[0].id.replace('tab-attrib-', ''), Joomla.MediaManager.Edit.original);
      }

      // Couple the tabs with the plugin objects
      links.forEach((link) => {
        link.addEventListener('joomla.tab.shown', ({ relatedTarget, target }) => {
          const container = document.getElementById('media-manager-edit-container');
          if (relatedTarget) {
            Joomla.MediaManager.Edit[relatedTarget.id.replace('tab-attrib-', '').toLowerCase()].Deactivate();

            // Clear the DOM
            container.innerHTML = '';
          }

          let data = Joomla.MediaManager.Edit.current;

          if (!('contents' in Joomla.MediaManager.Edit.current)) {
            data = Joomla.MediaManager.Edit.original;
          }

          // Move the container to the correct tab
          const tab = document.getElementById(target.id.replace('tab-', ''));
          tab.insertAdjacentElement('afterbegin', container);

          activate(target.id.replace('tab-attrib-', ''), data);
        });

        link.click();
      });

      if (links[0]) {
        links[0].click();
        activate(links[0].id.replace('tab-attrib-', ''), Joomla.MediaManager.Edit.original);
      }
    };

    // @TODO use promises here
    setTimeout(func, 50);
  });
})();
