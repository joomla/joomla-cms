/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
if (!Joomla) {
  throw new Error('Joomla API is not properly initialized');
}

Joomla.MediaManager = Joomla.MediaManager || {};
class Edit {
  constructor() {
    // Get the options from Joomla.optionStorage
    this.options = Joomla.getOptions('com_media', {});

    if (!this.options) {
      throw new Error('Initialization error "edit-images.js"');
    }

    this.extension = this.options.uploadPath.split('.').pop();
    this.fileType = ['jpeg', 'jpg'].includes(this.extension) ? 'jpeg' : this.extension;

    // Initiate the registry
    this.original = {
      filename: this.options.uploadPath.split('/').pop(),
      extension: this.extension,
      contents: `data:image/${this.fileType};base64,${this.options.contents}`,
    };
    this.history = {};
    this.current = {};
    this.plugins = {};
    this.baseContainer = document.getElementById('media-manager-edit-container');

    if (!this.baseContainer) {
      throw new Error('The image preview container is missing');
    }

    Joomla.MediaManager.Edit = this;
    window.dispatchEvent(new CustomEvent('media-manager-edit-init'));

    // Once the DOM is ready, initialize everything
    customElements.whenDefined('joomla-tab').then(async () => {
      const tabContainer = document.getElementById('myTab');
      const tabsUlElement = tabContainer.firstElementChild;
      const links = [].slice.call(tabsUlElement.querySelectorAll('button[aria-controls]'));
      this.createImageContainer(this.original);

      if (links[0]) {
        const tabId = links[0].getAttribute('aria-controls');
        try {
          await this.activate(tabId.replace('attrib-', ''));
        } catch (e) {
          // eslint-disable-next-line no-console
          console.log(e);
        }
      }

      // Couple the tabs with the plugin objects
      links.forEach((link, index) => {
        link.addEventListener('joomla.tab.shown', async ({ relatedTarget, target }) => {
          if (relatedTarget) {
            try {
              await this.plugins[relatedTarget.getAttribute('aria-controls').replace('attrib-', '')].Deactivate(this.imagePreview);
            } catch (e) {
              // eslint-disable-next-line no-console
              console.log(e);
            }
          }

          const tab = document.getElementById(link.getAttribute('aria-controls'));
          // Move the image container to the correct tab
          tab.insertAdjacentElement('beforeend', this.baseContainer);
          try {
            await this.activate(target.getAttribute('aria-controls').replace('attrib-', ''));
          } catch (e) {
            // eslint-disable-next-line no-console
            console.log(e);
          }
        });

        tabContainer.activateTab(index, false);
      });

      const tabId = links[0].getAttribute('aria-controls');
      tabContainer.activateTab(0, false);
      try {
        await this.activate(tabId.replace('attrib-', ''));
      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
      }
    });

    this.addHistoryPoint = this.addHistoryPoint.bind(this);
    this.createImageContainer = this.createImageContainer.bind(this);
    this.activate = this.activate.bind(this);
    this.Reset = this.Reset.bind(this);
    this.Undo = this.Undo.bind(this);
    this.Redo = this.Redo.bind(this);
    this.createProgressBar = this.createProgressBar.bind(this);
    this.updateProgressBar = this.updateProgressBar.bind(this);
    this.removeProgressBar = this.removeProgressBar.bind(this);
    this.exec = this.exec.bind(this);

    // Create history entry
    window.addEventListener('mediaManager.history.point', this.addHistoryPoint.bind(this));
  }

  addHistoryPoint() {
    if (this.original !== this.current.contents) {
      const key = Object.keys(this.history).length;
      if (this.history[key] && this.history[key - 1]
        && this.history[key] === this.history[key - 1]) {
        return;
      }
      this.history[key + 1] = this.current.contents;
    }
  }

  // Create the images for edit and preview
  createImageContainer(data) {
    if (!data.contents) {
      throw new Error('Initialization error "edit-images.js"');
    }

    this.imagePreview = document.createElement('img');
    this.imagePreview.src = data.contents;
    this.imagePreview.id = 'image-preview';
    this.imagePreview.style.width = '100%';
    this.imagePreview.style.height = 'auto';
    this.imagePreview.style.maxWidth = '100%';
    this.baseContainer.appendChild(this.imagePreview);
  }

  async activate(name) {
    // Activate the first plugin
    if (name) {
      try {
        await this.plugins[name.toLowerCase()].Activate(this.imagePreview);
      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
      }
    }
  }

  // Reset the image to the initial state
  Reset(current) {
    if (!current || (current && current === 'initial')) {
      this.current.contents = this.original.contents;
      this.history = {};
      this.imagePreview.src = this.original.contents;
    }

    // Reactivate the current plugin
    const tabContainer = document.getElementById('myTab');
    const tabsUlElement = tabContainer.firstElementChild;
    const links = [].slice.call(tabsUlElement.querySelectorAll('button[aria-controls]'));

    links.forEach(async (link) => {
      if (link.getAttribute('aria-expanded') !== 'true') {
        return;
      }

      try {
        await this.plugins[link.getAttribute('aria-controls').replace('attrib-', '')].Deactivate(this.imagePreview);
      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
      }

      link.click();
      try {
        await this.activate(link.getAttribute('aria-controls').replace('attrib-', ''));
      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
      }
    });
  }

  // @TODO History
  // eslint-disable-next-line class-methods-use-this
  Undo() { }

  // @TODO History
  // eslint-disable-next-line class-methods-use-this
  Redo() { }

  // @TODO Create the progress bar
  // eslint-disable-next-line class-methods-use-this
  createProgressBar() { }

  // @TODO Update the progress bar
  // eslint-disable-next-line class-methods-use-this
  updateProgressBar(/* position */) { }

  // @TODO Remove the progress bar
  // eslint-disable-next-line class-methods-use-this
  removeProgressBar() { }

  exec(name, data, uploadPath, url, type, stateChangeCallback) {
    this.xhr = new XMLHttpRequest();

    if (typeof stateChangeCallback === 'function') {
      this.xhr.onreadystatechange = stateChangeCallback;
    }

    this.xhr.upload.onprogress = (e) => {
      this.updateProgressBar((e.loaded / e.total) * 100);
    };
    this.xhr.onload = () => {
      let resp;
      try {
        resp = JSON.parse(this.xhr.responseText);
      } catch (er) {
        resp = null;
      }

      if (resp) {
        if (this.xhr.status === 200) {
          if (resp.success === true) {
            this.removeProgressBar();
          }

          if (resp.status === '1') {
            Joomla.renderMessages({ success: [resp.message] }, 'true');
            this.removeProgressBar();
          }
        }
      } else {
        this.removeProgressBar();
      }
    };

    this.xhr.onerror = () => {
      this.removeProgressBar();
    };

    this.xhr.open('PUT', url, true);
    this.xhr.setRequestHeader('Content-Type', type);
    this.createProgressBar();
    this.xhr.send(data);
  }
}

// Initiate the Editor API
// eslint-disable-next-line no-new
new Edit();

// Customize the Toolbar buttons behavior
Joomla.submitbutton = (task) => {
  const format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;
  const pathName = window.location.pathname.replace(/&view=file.*/g, '');
  const name = Joomla.MediaManager.Edit.options.uploadPath.split('/').pop();
  const forUpload = {
    name,
    content: Joomla.MediaManager.Edit.current.contents.replace(`data:image/${format};base64,`, ''),
  };

  const { uploadPath } = Joomla.MediaManager.Edit.options;
  const url = `${Joomla.MediaManager.Edit.options.apiBaseUrl}&task=api.files&path=${uploadPath}`;
  const type = 'application/json';
  forUpload[Joomla.MediaManager.Edit.options.csrfToken] = '1';

  let fileDirectory = uploadPath.split('/');
  fileDirectory.pop();
  fileDirectory = fileDirectory.join('/');

  // If we are in root add a backslash
  if (fileDirectory.endsWith(':')) {
    fileDirectory = `${fileDirectory}/`;
  }

  // Respect the images_only URI param
  const mediaTypes = document.querySelector('input[name="mediatypes"]');
  let mediatypes;
  if (mediaTypes) {
    mediatypes = `&mediatypes=${mediaTypes.value ? mediaTypes.value : '0'}`;
  }

  switch (task) {
    case 'apply':
      Joomla.MediaManager.Edit.exec(name, JSON.stringify(forUpload), uploadPath, url, type);
      Joomla.MediaManager.Edit.imagePreview.src = Joomla.MediaManager.Edit.current.contents;
      Joomla.MediaManager.Edit.original = Joomla.MediaManager.Edit.current;
      Joomla.MediaManager.Edit.history = {};

      (async () => {
        const activeTab = [].slice.call(document.querySelectorAll('joomla-tab-element'))
          .filter((tab) => tab.hasAttribute('active'));
        try {
          await Joomla.MediaManager.Edit.plugins[activeTab[0].id.replace('attrib-', '')].Deactivate(Joomla.MediaManager.Edit.imagePreview);
          await Joomla.MediaManager.Edit.plugins[activeTab[0].id.replace('attrib-', '')].Activate(Joomla.MediaManager.Edit.imagePreview);
        } catch (e) {
          // eslint-disable-next-line no-console
          console.log(e);
        }
      })();
      break;
    case 'save':
      // eslint-disable-next-line func-names
      Joomla.MediaManager.Edit.exec(name, JSON.stringify(forUpload), uploadPath, url, type, () => {
        if (Joomla.MediaManager.Edit.xhr.readyState === XMLHttpRequest.DONE) {
          if (window.self !== window.top) {
            window.location = `${pathName}?option=com_media&view=media${mediatypes}&path=${fileDirectory}&tmpl=component`;
          } else {
            window.location = `${pathName}?option=com_media&view=media${mediatypes}&path=${fileDirectory}`;
          }
        }
      });
      break;
    case 'cancel':
      if (window.self !== window.top) {
        window.location = `${pathName}?option=com_media&view=media${mediatypes}&path=${fileDirectory}&tmpl=component`;
      } else {
        window.location = `${pathName}?option=com_media&view=media${mediatypes}&path=${fileDirectory}`;
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
