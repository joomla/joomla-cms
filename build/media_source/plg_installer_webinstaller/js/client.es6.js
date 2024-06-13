/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

const allowList = {
  button: ['type'],
  input: ['type', 'name', 'placeholder', 'inputmode'],
  select: ['name'],
  option: ['value', 'selected'],
};

const webInstallerOptions = {
  view: 'dashboard',
  id: 0,
  ordering: '',
  version: 'current',
  list: 0,
  options: Joomla.getOptions('plg_installer_webinstaller', {}),
};

let instance;

class WebInstaller {
  initialise() {
    webInstallerOptions.loaded = 1;

    const cancelButton = document.getElementById('uploadform-web-cancel');
    cancelButton.addEventListener('click', () => {
      document.getElementById('uploadform-web').classList.add('hidden');

      if (webInstallerOptions.list && document.querySelector('.list-view')) {
        document.querySelector('.list-view').click();
      }
    });

    const installButton = document.getElementById('uploadform-web-install');
    installButton.addEventListener('click', () => {
      if (webInstallerOptions.options.installFrom === 4) {
        this.submitButtonUrl();
      } else {
        this.submitButtonWeb();
      }
    });

    this.loadweb(`${webInstallerOptions.options.base_url}index.php?format=json&option=com_apps&view=dashboard`);

    this.clickforlinks();
  }

  loadweb(url) {
    if (!url) {
      return false;
    }

    // eslint-disable-next-line prefer-regex-literals
    const pattern1 = new RegExp(webInstallerOptions.options.base_url);
    const pattern2 = /^index\.php/;

    if (!(pattern1.test(url) || pattern2.test(url))) {
      window.open(url, '_blank');

      return false;
    }

    let requestUrl = `${url}&product=${webInstallerOptions.options.product}&release=${webInstallerOptions.options.release}&dev_level=${webInstallerOptions.options.dev_level}&list=${webInstallerOptions.list ? 'list' : 'grid'}&lang=${webInstallerOptions.options.language}`;

    const orderingSelect = document.getElementById('com-apps-ordering');
    const versionSelect = document.getElementById('com-apps-filter-joomla-version');

    if (webInstallerOptions.ordering !== '' && orderingSelect && orderingSelect.value) {
      webInstallerOptions.ordering = orderingSelect.value;
      requestUrl += `&ordering=${webInstallerOptions.ordering}`;
    }

    if (webInstallerOptions.version !== '' && versionSelect && versionSelect.value) {
      webInstallerOptions.version = versionSelect.value;
      requestUrl += `&filter_version=${webInstallerOptions.version}`;
    }

    WebInstaller.showLoadingLayer();

    new Promise((resolve, reject) => {
      Joomla.request({
        url: requestUrl,
        onSuccess: (resp) => {
          let response;

          try {
            response = JSON.parse(resp);
          } catch (error) {
            throw new Error('Failed to parse JSON');
          }

          if (document.getElementById('web-loader')) {
            document.getElementById('web-loader').classList.add('hidden');
          }

          const jedContainer = document.getElementById('jed-container');
          jedContainer.innerHTML = Joomla.sanitizeHtml(response.data.html, allowList);

          document.getElementById('com-apps-searchbox').addEventListener('keydown', ({ code }) => {
            if (code === 'Enter') {
              this.initiateSearch();
            }
          });

          document.getElementById('search-extensions').addEventListener('click', () => {
            this.initiateSearch();
          });

          document.getElementById('search-reset').addEventListener('click', () => {
            const searchBox = document.getElementById('com-apps-searchbox');
            searchBox.value = '';
            this.initiateSearch();
            document.getElementById('search-reset').setAttribute('disabled', 'disabled');
          });

          if (document.getElementById('com-apps-searchbox').value === '') {
            document.getElementById('search-reset').setAttribute('disabled', 'disabled');
          }

          document.getElementById('search-reset').innerHTML = Joomla.sanitizeHtml(Joomla.Text._('JSEARCH_FILTER_CLEAR'));

          // eslint-disable-next-line no-shadow
          const orderingSelect = document.getElementById('com-apps-ordering');
          // eslint-disable-next-line no-shadow
          const versionSelect = document.getElementById('com-apps-filter-joomla-version');

          if (orderingSelect) {
            orderingSelect.addEventListener('change', () => {
              const index = orderingSelect.selectedIndex;
              webInstallerOptions.ordering = orderingSelect.options[index].value;
              this.installfromwebajaxsubmit();
            });
          }

          if (versionSelect) {
            versionSelect.addEventListener('change', () => {
              const index = versionSelect.selectedIndex;
              webInstallerOptions.version = versionSelect.options[index].value;
              this.installfromwebajaxsubmit();
            });
          }

          if (webInstallerOptions.options.installfrom_url !== '') {
            WebInstaller.installfromweb(webInstallerOptions.options.installfrom_url);
          }

          resolve();
        },
        onError: (request) => {
          const errorContainer = document.getElementById('web-loader-error');
          const loaderContainer = document.getElementById('web-loader');

          if (request.responseText && errorContainer) {
            errorContainer.innerHTML = Joomla.sanitizeHtml(request.responseText);
          }

          if (loaderContainer) {
            loaderContainer.classList.add('hidden');
            errorContainer.classList.remove('hidden');
          }
          Joomla.renderMessages({ danger: [Joomla.Text._('PLG_INSTALLER_WEBINSTALLER_INSTALL_WEB_LOADING_ERROR')] }, '#web-loader-error');

          reject();
        },
      });
    }).finally(() => {
      // Promise has been settled.
      // Run the following whether or not it was a success.

      const installAtField = document.getElementById('joomlaapsinstallatinput');

      if (installAtField) {
        installAtField.value = webInstallerOptions.options.installat_url;
      }

      this.clickforlinks();
      WebInstaller.clicker();

      if (webInstallerOptions.view !== 'extension') {
        document.querySelectorAll('div.load-extension').forEach((element) => {
          element.addEventListener('click', (event) => {
            event.preventDefault();
            this.processLinkClick(element.getAttribute('data-url'));
          });

          element.setAttribute('href', '#');
        });
      }

      if (webInstallerOptions.view === 'extension') {
        const installExtensionButton = document.getElementById('install-extension');
        const installExtensionFromExternalButton = document.getElementById('install-extension-from-external');

        if (installExtensionButton) {
          installExtensionButton.addEventListener('click', () => {
            WebInstaller.installfromweb(installExtensionButton.getAttribute('data-downloadurl'), installExtensionButton.getAttribute('data-name'));
            document.getElementById('uploadform-web-install').scrollIntoView({ behavior: 'smooth', block: 'start' });
          });
        }

        if (installExtensionFromExternalButton) {
          installExtensionFromExternalButton.addEventListener('click', () => {
            const redirectUrl = installExtensionFromExternalButton.getAttribute('data-downloadurl');
            const redirectConfirm = window.confirm(Joomla.Text._('PLG_INSTALLER_WEBINSTALLER_REDIRECT_TO_EXTERNAL_SITE_TO_INSTALL').replace('[SITEURL]', redirectUrl));

            if (redirectConfirm !== true) {
              return;
            }

            document.getElementById('adminForm').setAttribute('action', redirectUrl);
            document.querySelector('input[name=task]').setAttribute('disabled', true);
            document.querySelector('input[name=install_directory]').setAttribute('disabled', true);
            document.querySelector('input[name=install_url]').setAttribute('disabled', true);
            document.querySelector('input[name=installtype]').setAttribute('disabled', true);
            document.querySelector('input[name=filter_search]').setAttribute('disabled', true);

            document.getElementById('adminForm').submit();
          });
        }
      }

      if (webInstallerOptions.list && document.querySelector('.list-view')) {
        document.querySelector('.list-view').click();
      }

      WebInstaller.hideLoadingLayer();
    });

    return true;
  }

  clickforlinks() {
    document.querySelectorAll('a.transcode').forEach((element) => {
      const ajaxurl = element.getAttribute('href');

      element.addEventListener('click', (event) => {
        event.preventDefault();
        this.processLinkClick(ajaxurl);
      });

      element.setAttribute('href', '#');
    });
  }

  initiateSearch() {
    document.getElementById('search-reset').removeAttribute('disabled');
    webInstallerOptions.view = 'dashboard';
    this.installfromwebajaxsubmit();
  }

  installfromwebajaxsubmit() {
    let tail = `&view=${webInstallerOptions.view}`;

    if (webInstallerOptions.id) {
      tail += `&id=${webInstallerOptions.id}`;
    }

    if (document.getElementById('com-apps-searchbox').value) {
      const value = encodeURI(document.getElementById('com-apps-searchbox').value.toLowerCase().replace(/ +/g, '_').replace(/[^a-z0-9-_]/g, '').trim());
      tail += `&filter_search=${value}`;
    }

    const orderingSelect = document.getElementById('com-apps-ordering');
    const versionSelect = document.getElementById('com-apps-filter-joomla-version');

    if (webInstallerOptions.ordering !== '' && orderingSelect && orderingSelect.value) {
      webInstallerOptions.ordering = orderingSelect.value;
    }

    if (webInstallerOptions.ordering) {
      tail += `&ordering=${webInstallerOptions.ordering}`;
    }

    if (webInstallerOptions.version !== '' && versionSelect && versionSelect.value) {
      webInstallerOptions.version = versionSelect.value;
    }

    if (webInstallerOptions.version) {
      tail += `&filter_version=${webInstallerOptions.version}`;
    }

    this.loadweb(`${webInstallerOptions.options.base_url}index.php?format=json&option=com_apps${tail}`);
  }

  processLinkClick(url) {
    const pattern1 = new RegExp(webInstallerOptions.options.base_url);
    const pattern2 = /^index\.php/;

    if (pattern1.test(url) || pattern2.test(url)) {
      webInstallerOptions.view = url.replace(/^.+[&?]view=(\w+).*$/, '$1');

      if (webInstallerOptions.view === 'dashboard') {
        webInstallerOptions.id = 0;
      } else if (webInstallerOptions.view === 'category') {
        webInstallerOptions.id = url.replace(/^.+[&?]id=(\d+).*$/, '$1');
      }

      this.loadweb(webInstallerOptions.options.base_url + url);
    } else {
      this.loadweb(url);
    }
  }

  static showLoadingLayer() {
    document.getElementById('web').appendChild(document.createElement('joomla-core-loader'));
  }

  static hideLoadingLayer() {
    const spinnerElement = document.querySelector('#web joomla-core-loader');
    spinnerElement.parentNode.removeChild(spinnerElement);
  }

  static clicker() {
    if (document.querySelector('.grid-view')) {
      document.querySelector('.grid-view').addEventListener('click', () => {
        webInstallerOptions.list = 0;
        document.querySelector('.list-container').classList.add('hidden');
        document.querySelector('.grid-container').classList.remove('hidden');
        document.getElementById('btn-list-view').classList.remove('active');
        document.getElementById('btn-grid-view').classList.remove('active');
      });
    }

    if (document.querySelector('.list-view')) {
      document.querySelector('.list-view').addEventListener('click', () => {
        webInstallerOptions.list = 1;
        document.querySelector('.grid-container').classList.add('hidden');
        document.querySelector('.list-container').classList.remove('hidden');
        document.getElementById('btn-grid-view').classList.remove('active');
        document.getElementById('btn-list-view').classList.add('active');
      });
    }
  }

  /**
   * @param {string} installUrl
   * @param {string} name
   * @returns {boolean}
   */
  static installfromweb(installUrl, name = null) {
    if (!installUrl) {
      Joomla.renderMessages({ warning: [Joomla.Text._('PLG_INSTALLER_WEBINSTALLER_CANNOT_INSTALL_EXTENSION_IN_PLUGIN')] });

      return false;
    }

    document.getElementById('install_url').value = installUrl;
    document.getElementById('uploadform-web-url').innerText = installUrl;

    if (name) {
      document.getElementById('uploadform-web-name').innerText = name;
      document.getElementById('uploadform-web-name-label').classList.remove('hidden');
    } else {
      document.getElementById('uploadform-web-name-label').classList.add('hidden');
    }

    document.getElementById('uploadform-web').classList.remove('hidden');

    return true;
  }

  // eslint-disable-next-line class-methods-use-this
  submitButtonUrl() {
    const form = document.getElementById('adminForm');

    // do field validation
    if (form.install_url.value === '' || form.install_url.value === 'http://' || form.install_url.value === 'https://') {
      Joomla.renderMessages({ warning: [Joomla.Text._('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL')] });
    } else {
      const loading = document.getElementById('loading');
      if (loading) {
        loading.classList.remove('hidden');
      }

      form.installtype.value = 'url';
      form.submit();
    }
  }

  submitButtonWeb() {
    const form = document.getElementById('adminForm');

    // do field validation
    if (form.install_url.value !== '' || form.install_url.value !== 'http://' || form.install_url.value !== 'https://') {
      this.submitButtonUrl();
    } else if (form.install_url.value === '') {
      Joomla.renderMessages({ warning: [Joomla.apps.options.btntxt] });
    } else {
      document.querySelector('#appsloading').classList.remove('hidden');
      form.installtype.value = 'web';
      form.submit();
    }
  }
}

customElements.whenDefined('joomla-tab').then(() => {
  const installerTabs = document.getElementById('myTab');
  const link = installerTabs.querySelector('button[aria-controls=web]');

  // Stop if the IFW tab cannot be found
  if (!link) {
    return;
  }

  if (webInstallerOptions.options.installfromon) {
    link.click();
  }

  if (link.hasAttribute('aria-expanded') && link.getAttribute('aria-expanded') === 'true' && !instance) {
    instance = new WebInstaller();
    instance.initialise();
  }

  if (webInstallerOptions.options.installfrom_url !== '') {
    link.click();
  }

  link.addEventListener('joomla.tab.shown', () => {
    if (!instance) {
      instance = new WebInstaller();
      instance.initialise();
    }
  });
});
