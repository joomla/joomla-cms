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
    this.options.currentUrl = new URL(window.location.href);

    // Initiate the registry
    this.original = {
      filename: this.options.uploadPath.split('/').pop(),
      extension: this.extension,
      contents: `data:image/${this.fileType};base64,${this.options.contents}`,
    };
    // eslint-disable-next-line no-promise-executor-return
    this.previousPluginDeactivated = new Promise((resolve) => resolve);
    this.history = {};
    this.current = this.original;
    this.plugins = {};
    this.baseContainer = document.getElementById('media-manager-edit-container');

    if (!this.baseContainer) {
      throw new Error('The image preview container is missing');
    }

    this.createImageContainer(this.original);

    Joomla.MediaManager.Edit = this;
    window.dispatchEvent(new CustomEvent('media-manager-edit-init'));

    // Once the DOM is ready, initialize everything
    customElements.whenDefined('joomla-tab').then(async () => {
      const tabContainer = document.getElementById('myTab');
      const tabsUlElement = tabContainer.firstElementChild;

      // Couple the tabs with the plugin objects
      tabsUlElement.querySelectorAll('button[aria-controls]').forEach((link, index) => {
        const tab = document.getElementById(link.getAttribute('aria-controls'));
        if (index === 0) {
          tab.insertAdjacentElement('beforeend', this.baseContainer);
        }

        link.addEventListener('joomla.tab.hidden', ({ target }) => {
          if (!target) {
            // eslint-disable-next-line no-promise-executor-return
            this.previousPluginDeactivated = new Promise((resolve) => resolve);
            return;
          }

          this.previousPluginDeactivated = new Promise((resolve, reject) => {
            this.plugins[target.getAttribute('aria-controls').replace('attrib-', '')]
              .Deactivate(this.imagePreview)
              .then(resolve)
              .catch((e) => {
                // eslint-disable-next-line no-console
                console.log(e);
                reject();
              });
          });
        });

        link.addEventListener('joomla.tab.shown', ({ target }) => {
          // Move the image container to the correct tab
          tab.insertAdjacentElement('beforeend', this.baseContainer);
          this.previousPluginDeactivated
            .then(() => this.plugins[target.getAttribute('aria-controls').replace('attrib-', '')].Activate(this.imagePreview))
            .catch((e) => {
              // eslint-disable-next-line no-console
              console.log(e);
            });
        });
      });

      tabContainer.activateTab(0, false);
    });

    this.addHistoryPoint = this.addHistoryPoint.bind(this);
    this.createImageContainer = this.createImageContainer.bind(this);
    this.Reset = this.Reset.bind(this);
    this.Undo = this.Undo.bind(this);
    this.Redo = this.Redo.bind(this);
    this.createProgressBar = this.createProgressBar.bind(this);
    this.updateProgressBar = this.updateProgressBar.bind(this);
    this.removeProgressBar = this.removeProgressBar.bind(this);
    this.upload = this.upload.bind(this);

    // Create history entry
    window.addEventListener('mediaManager.history.point', this.addHistoryPoint.bind(this));
  }

  /**
   * Creates a history snapshot
   * PRIVATE
   */
  addHistoryPoint() {
    if (this.original !== this.current) {
      const key = Object.keys(this.history).length;
      if (this.history[key] && this.history[key - 1]
        && this.history[key] === this.history[key - 1]) {
        return;
      }
      this.history[key + 1] = this.current;
    }
  }

  /**
   * Creates the images for edit and preview
   * PRIVATE
   */
  createImageContainer(data) {
    if (!data.contents) {
      throw new Error('Initialization error "edit-images.js"');
    }

    this.imagePreview = document.createElement('img');
    this.imagePreview.src = data.contents;
    this.imagePreview.id = 'image-preview';
    this.imagePreview.style.height = 'auto';
    this.imagePreview.style.maxWidth = '100%';
    this.baseContainer.appendChild(this.imagePreview);
  }

  // Reset the image to the initial state
  Reset(/* current */) {
    this.current.contents = `data:image/${this.fileType};base64,${this.options.contents}`;
    this.imagePreview.setAttribute('src', this.current.contents);

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        this.imagePreview.setAttribute('width', this.imagePreview.naturalWidth);
        this.imagePreview.setAttribute('height', this.imagePreview.naturalHeight);
      });
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

  /**
   * Uploads
   * Public
   */
  upload(url, stateChangeCallback) {
    let format = Joomla.MediaManager.Edit.original.extension.toLowerCase() === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension.toLowerCase();

    if (!format) {
      // eslint-disable-next-line prefer-destructuring
      format = /data:image\/(.+);/gm.exec(Joomla.MediaManager.Edit.original.contents)[1];
    }

    if (!format) {
      throw new Error('Unable to determine image format');
    }

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
      this.xhr = null;
    };

    this.xhr.onerror = () => {
      this.removeProgressBar();
      this.xhr = null;
    };

    this.xhr.open('PUT', url, true);
    this.xhr.setRequestHeader('Content-Type', 'application/json');
    this.createProgressBar();
    this.xhr.send(JSON.stringify({
      name: Joomla.MediaManager.Edit.options.uploadPath.split('/').pop(),
      content: Joomla.MediaManager.Edit.current.contents.replace(`data:image/${format};base64,`, ''),
      [Joomla.MediaManager.Edit.options.csrfToken]: 1,
    }));
  }
}

// Initiate the Editor API
// eslint-disable-next-line no-new
new Edit();

/**
 * Compute the current URL
 *
 * @param {boolean} isModal is the URL for a modal window
 *
 * @return {{}} the URL object
 */
const getUrl = (isModal) => {
  const newUrl = Joomla.MediaManager.Edit.options.currentUrl;
  const params = new URLSearchParams(newUrl.search);
  params.set('view', 'media');
  params.delete('path');
  params.delete('mediatypes');

  const { uploadPath } = Joomla.MediaManager.Edit.options;
  let fileDirectory = uploadPath.split('/');
  fileDirectory.pop();
  fileDirectory = fileDirectory.join('/');

  // If we are in root add a backslash
  if (fileDirectory.endsWith(':')) {
    fileDirectory = `${fileDirectory}/`;
  }

  params.set('path', fileDirectory);

  // Respect the images_only URI param
  const mediaTypes = document.querySelector('input[name="mediatypes"]');
  params.set('mediatypes', (mediaTypes && mediaTypes.value) ? mediaTypes.value : '0');

  if (isModal) {
    params.set('tmpl', 'component');
  }

  newUrl.search = params;

  return newUrl;
};

// Customize the Toolbar buttons behavior
Joomla.submitbutton = (task) => {
  const url = new URL(`${Joomla.MediaManager.Edit.options.apiBaseUrl}&task=api.files&path=${Joomla.MediaManager.Edit.options.uploadPath}`);
  switch (task) {
    case 'apply':
      Joomla.MediaManager.Edit.upload(url, null);
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
      Joomla.MediaManager.Edit.upload(url, () => {
        if (Joomla.MediaManager.Edit.xhr.readyState === XMLHttpRequest.DONE) {
          if (window.self !== window.top) {
            window.location = getUrl(true);
          } else {
            window.location = getUrl();
          }
        }
      });
      break;
    case 'cancel':
      if (window.self !== window.top) {
        window.location = getUrl(true);
      } else {
        window.location = getUrl();
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
