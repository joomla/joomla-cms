/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};
Joomla.MediaManager = Joomla.MediaManager || {};

(function () {
  'use strict'; // Get the options from Joomla.optionStorage

  var options = Joomla.getOptions('com_media', {});

  if (!options) {
    throw new Error('Initialization error "edit-images.js"');
  } // Initiate the registry


  Joomla.MediaManager.Edit.original = {
    filename: options.uploadPath.split('/').pop(),
    extension: options.uploadPath.split('.').pop(),
    contents: "data:image/".concat(options.uploadPath.split('.').pop(), ";base64,").concat(options.contents)
  };
  Joomla.MediaManager.Edit.history = {};
  Joomla.MediaManager.Edit.current = {};

  var activate = function activate(name, data) {
    if (!data.contents) {
      return;
    } // Create the images for edit and preview


    var baseContainer = document.getElementById('media-manager-edit-container');
    var editContainer = document.createElement('div');
    var previewContainer = document.createElement('div');
    var imageSrc = document.createElement('img');
    var imagePreview = document.createElement('img');
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
    baseContainer.appendChild(previewContainer); // Activate the first plugin

    Joomla.MediaManager.Edit[name.toLowerCase()].Activate(data);
  }; // Reset the image to the initial state


  Joomla.MediaManager.Edit.Reset = function (current) {
    if (!current || current && current === 'initial') {
      Joomla.MediaManager.Edit.current.contents = Joomla.MediaManager.Edit.original.contents;
    } // Clear the DOM


    var container = document.getElementById('media-manager-edit-container');
    container.innerHTML = ''; // Reactivate the current plugin

    var tabsUlElement = document.getElementById('myTab').firstElementChild;

    if (tabsUlElement.tagName !== 'UL') {
      return;
    }

    var links = [].slice.call(tabsUlElement.querySelectorAll('a'));
    links.forEach(function (link) {
      if (!link.hasAttribute('active')) {
        return;
      }

      Joomla.MediaManager.Edit[link.id.replace('tab-attrib-', '').toLowerCase()].Deactivate();
      var data = Joomla.MediaManager.Edit.current;

      if (!current || current && current !== true) {
        data = Joomla.MediaManager.Edit.original;
      }

      link.click(); // Move the container to the correct tab

      var mediaContainer = document.getElementById('media-manager-edit-container');
      var tab = document.getElementById(link.id.replace('tab-', ''));
      tab.insertAdjacentElement('afterbegin', mediaContainer);
      activate(link.id.replace('tab-attrib-', ''), data);
    });
  }; // Create history entry


  window.addEventListener('mediaManager.history.point', function () {
    if (Joomla.MediaManager.Edit.original !== Joomla.MediaManager.Edit.current.contents) {
      var key = Object.keys(Joomla.MediaManager.Edit.history).length;

      if (Joomla.MediaManager.Edit.history[key] && Joomla.MediaManager.Edit.history[key - 1] && Joomla.MediaManager.Edit.history[key] === Joomla.MediaManager.Edit.history[key - 1]) {
        return;
      }

      Joomla.MediaManager.Edit.history[key + 1] = Joomla.MediaManager.Edit.current.contents;
    }
  }); // @TODO History

  Joomla.MediaManager.Edit.Undo = function () {}; // @TODO History


  Joomla.MediaManager.Edit.Redo = function () {}; // @TODO Create the progress bar


  Joomla.MediaManager.Edit.createProgressBar = function () {}; // @TODO Update the progress bar


  Joomla.MediaManager.Edit.updateProgressBar = function ()
  /* position */
  {}; // @TODO Remove the progress bar


  Joomla.MediaManager.Edit.removeProgressBar = function () {}; // Customize the buttons


  Joomla.submitbutton = function (task) {
    var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;
    var pathName = window.location.pathname.replace(/&view=file.*/g, '');
    var name = options.uploadPath.split('/').pop();
    var forUpload = {
      name: name,
      content: Joomla.MediaManager.Edit.current.contents.replace("data:image/".concat(format, ";base64,"), '')
    }; // eslint-disable-next-line prefer-destructuring

    var uploadPath = options.uploadPath;
    var url = "".concat(options.apiBaseUrl, "&task=api.files&path=").concat(uploadPath);
    var type = 'application/json';
    forUpload[options.csrfToken] = '1';
    var fileDirectory = uploadPath.split('/');
    fileDirectory.pop();
    fileDirectory = fileDirectory.join('/'); // If we are in root add a backslash

    if (fileDirectory.endsWith(':')) {
      fileDirectory = "".concat(fileDirectory, "/");
    }

    switch (task) {
      case 'apply':
        Joomla.UploadFile.exec(name, JSON.stringify(forUpload), uploadPath, url, type);
        Joomla.MediaManager.Edit.Reset(true);
        break;

      case 'save':
        Joomla.UploadFile.exec(name, JSON.stringify(forUpload), uploadPath, url, type);
        window.location = "".concat(pathName, "?option=com_media&path=").concat(fileDirectory);
        break;

      case 'cancel':
        if (window.self !== window.top) {
          window.location = "".concat(pathName, "?option=com_media&path=").concat(fileDirectory, "&tmpl=component");
        } else {
          window.location = "".concat(pathName, "?option=com_media&path=").concat(fileDirectory);
        }

        break;

      case 'reset':
        Joomla.MediaManager.Edit.Reset('initial');
        break;

      case 'undo':
        // @TODO magic goes here
        break;

      case 'redo':
        // @TODO other magic goes here
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

  Joomla.UploadFile.exec = function (name, data, uploadPath, url, type) {
    var xhr = new XMLHttpRequest();

    xhr.upload.onprogress = function (e) {
      Joomla.MediaManager.Edit.updateProgressBar(e.loaded / e.total * 100);
    };

    xhr.onload = function () {
      var resp;

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
            Joomla.renderMessages({
              success: [resp.message]
            }, 'true');
            Joomla.MediaManager.Edit.removeProgressBar();
          }
        }
      } else {
        Joomla.MediaManager.Edit.removeProgressBar();
      }
    };

    xhr.onerror = function () {
      Joomla.MediaManager.Edit.removeProgressBar();
    };

    xhr.open('PUT', url, true);
    xhr.setRequestHeader('Content-Type', type);
    Joomla.MediaManager.Edit.createProgressBar();
    xhr.send(data);
  }; // Once the DOM is ready, initialize everything


  document.addEventListener('DOMContentLoaded', function () {
    var func = function func() {
      var tabsUlElement = document.getElementById('myTab').firstElementChild;

      if (tabsUlElement.tagName !== 'UL') {
        setTimeout(func, 50);
        return;
      }

      var links = [].slice.call(tabsUlElement.querySelectorAll('a'));

      if (links[0]) {
        activate(links[0].id.replace('tab-attrib-', ''), Joomla.MediaManager.Edit.original);
      } // Couple the tabs with the plugin objects


      links.forEach(function (link) {
        link.addEventListener('joomla.tab.shown', function (_ref) {
          var relatedTarget = _ref.relatedTarget,
              target = _ref.target;
          var container = document.getElementById('media-manager-edit-container');

          if (relatedTarget) {
            Joomla.MediaManager.Edit[relatedTarget.id.replace('tab-attrib-', '').toLowerCase()].Deactivate(); // Clear the DOM

            container.innerHTML = '';
          }

          var data = Joomla.MediaManager.Edit.current;

          if (!('contents' in Joomla.MediaManager.Edit.current)) {
            data = Joomla.MediaManager.Edit.original;
          } // Move the container to the correct tab


          var tab = document.getElementById(target.id.replace('tab-', ''));
          tab.insertAdjacentElement('afterbegin', container);
          activate(target.id.replace('tab-attrib-', ''), data);
        });
        link.click();
      });

      if (links[0]) {
        links[0].click();
        activate(links[0].id.replace('tab-attrib-', ''), Joomla.MediaManager.Edit.original);
      }
    }; // @TODO use promises here


    setTimeout(func, 50);
  });
})();