/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

Joomla.MediaManager = Joomla.MediaManager || {};

let applyButton;
let saveButton;
const applyButtonParent = document.getElementById('toolbar-apply');
if (applyButtonParent) {
  applyButton = applyButtonParent.firstElementChild;
}
const saveButtonParent = document.getElementById('toolbar-save');
if (saveButtonParent) {
  saveButton = saveButtonParent.firstElementChild;
}

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
};

// Reset the image to the initial state
Joomla.MediaManager.Edit.Reset = (current) => {
  if (!current || (current && current === 'initial')) {
    Joomla.MediaManager.Edit.current.contents = Joomla.MediaManager.Edit.original.contents;
  }

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

    let data = Joomla.MediaManager.Edit.current;
    if (!current || (current && current !== true)) {
      data = Joomla.MediaManager.Edit.original;
    }

    link.click();

    // Move the container to the correct tab
    const mediaContainer = document.getElementById('media-manager-edit-container');
    const tab = document.getElementById(link.id.replace('tab-', ''));
    tab.insertAdjacentElement('beforeend', mediaContainer);

    activate(link.id.replace('tab-attrib-', ''), data);
  });
};

// Create history entry
window.addEventListener('mediaManager.history.point', () => {
  if (Joomla.MediaManager.Edit.original !== Joomla.MediaManager.Edit.current.contents) {
    const key = Object.keys(Joomla.MediaManager.Edit.history).length;
    if (Joomla.MediaManager.Edit.history[key] && Joomla.MediaManager.Edit.history[key - 1]
      && Joomla.MediaManager.Edit.history[key] === Joomla.MediaManager.Edit.history[key - 1]) {
      return;
    }
    Joomla.MediaManager.Edit.history[key + 1] = Joomla.MediaManager.Edit.current.contents;
  }
});

// @TODO History
Joomla.MediaManager.Edit.Undo = () => { };
// @TODO History
Joomla.MediaManager.Edit.Redo = () => { };

// @TODO Create the progress bar
Joomla.MediaManager.Edit.createProgressBar = () => { };

// @TODO Update the progress bar
Joomla.MediaManager.Edit.updateProgressBar = (/* position */) => { };

// @TODO Remove the progress bar
Joomla.MediaManager.Edit.removeProgressBar = () => { };

// Customize the buttons
Joomla.submitbutton = (task) => {
  if (applyButton) {
    applyButton.setAttribute('disabled', '');
  }
  if (saveButton) {
    saveButton.setAttribute('disabled', '');
  }

  const format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;
  const pathName = window.location.pathname.replace(/&view=file.*/g, '');
  const name = Joomla.MediaManager.options.uploadPath.split('/').pop();
  const forUpload = {
    name,
    content: Joomla.MediaManager.Edit.current.contents.replace(`data:image/${format};base64,`, ''),
  };

  // eslint-disable-next-line prefer-destructuring
  const uploadPath = Joomla.MediaManager.options.uploadPath;

  const url = `${Joomla.MediaManager.options.apiBaseUrl}&task=api.files&path=${uploadPath}`;

  const type = 'application/json';

  forUpload[Joomla.MediaManager.options.csrfToken] = '1';

  let fileDirectory = uploadPath.split('/');
  fileDirectory.pop();
  fileDirectory = fileDirectory.join('/');

  // If we are in root add a backslash
  if (fileDirectory.endsWith(':')) {
    fileDirectory = `${fileDirectory}/`;
  }

  switch (task) {
    case 'apply':
      document.addEventListener('ajaxCompleted', () => {
        if (applyButton) {
          applyButton.removeAttribute('disabled');
        }
        if (saveButton) {
          saveButton.removeAttribute('disabled');
        }
      });
      Joomla.UploadFile.exec(name, JSON.stringify(forUpload), uploadPath, url, type, function () {
        if (this.readyState === XMLHttpRequest.DONE) {
          window.location.reload();
        }
      });
      break;
    case 'save':
      document.addEventListener('ajaxCompleted', () => {
        if (applyButton) {
          applyButton.removeAttribute('disabled');
        }
        if (saveButton) {
          saveButton.removeAttribute('disabled');
        }
      });
      // eslint-disable-next-line func-names
      Joomla.UploadFile.exec(name, JSON.stringify(forUpload), uploadPath, url, type, function () {
        if (this.readyState === XMLHttpRequest.DONE) {
          if (window.self !== window.top) {
            window.location = `${pathName}?option=com_media&view=media&path=${fileDirectory}&tmpl=component`;
          } else {
            window.location = `${pathName}?option=com_media&view=media&path=${fileDirectory}`;
          }
        }
      });
      break;
    case 'cancel':
      if (window.self !== window.top) {
        window.location = `${pathName}?option=com_media&view=media&path=${fileDirectory}&tmpl=component`;
      } else {
        window.location = `${pathName}?option=com_media&view=media&path=${fileDirectory}`;
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
Joomla.UploadFile.exec = (name, data, uploadPath, url, type, stateChangeCallback) => {
  const xhr = new XMLHttpRequest();

  xhr.upload.onprogress = (e) => {
    Joomla.MediaManager.Edit.updateProgressBar((e.loaded / e.total) * 100);
  };

  if (typeof stateChangeCallback === 'function') {
    xhr.onreadystatechange = stateChangeCallback;
  }

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
          document.dispatchEvent(new Event('ajaxCompleted'));
        }

        if (resp.status === '1') {
          Joomla.renderMessages({ success: [resp.message] }, 'true');
          Joomla.MediaManager.Edit.removeProgressBar();
          document.dispatchEvent(new Event('ajaxCompleted'));
        }
      }
    } else {
      Joomla.MediaManager.Edit.removeProgressBar();
      document.dispatchEvent(new Event('ajaxCompleted'));
    }
  };

  xhr.onerror = () => {
    Joomla.MediaManager.Edit.removeProgressBar();
    document.dispatchEvent(new Event('ajaxCompleted'));
  };

  xhr.open('PUT', url, true);
  xhr.setRequestHeader('Content-Type', type);
  Joomla.MediaManager.Edit.createProgressBar();
  xhr.send(data);
};

customElements.whenDefined('joomla-tab').then(async () => {
  // Get the options from Joomla.optionStorage
  Joomla.MediaManager.options = Joomla.getOptions('com_media', {});

  if (!Joomla.MediaManager.options) {
    throw new Error('Initialization error "edit-images.js"');
  }

  // Initiate the registry
  Joomla.MediaManager.Edit.original = {
    filename: Joomla.MediaManager.options.uploadPath.split('/').pop(),
    extension: Joomla.MediaManager.options.uploadPath.split('.').pop(),
    contents: `data:image/${Joomla.MediaManager.options.uploadPath.split('.').pop()};base64,${Joomla.MediaManager.options.contents}`,
  };

  Joomla.MediaManager.Edit.history = {};
  Joomla.MediaManager.Edit.current = {};

  const tabsUlElement = document.getElementById('myTab').firstElementChild;
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
      tab.insertAdjacentElement('beforeend', container);

      activate(target.id.replace('tab-attrib-', ''), data);
    });

    link.click();
  });

  if (links[0]) {
    links[0].click();
    activate(links[0].id.replace('tab-attrib-', ''), Joomla.MediaManager.Edit.original);
  }
});
