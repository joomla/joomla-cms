/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!window.jQuery) {
  throw new Error('WebInstaller plugin requires jQuery');
}

if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

((window, document, Joomla, jQuery) => {
  'use strict';

  const webInstallerOptions = {
    view: 'dashboard',
    id: 0,
    ordering: '',
    list: 0,
    options: Joomla.getOptions('plg_installer_webinstaller', {}),
  };

  let instance;

  class WebInstaller {
    initialise() {
      webInstallerOptions.loaded = 1;

      if (document.getElementById('myTabContent')) {
        const webTab = document.getElementById('web');
        const cancelButton = document.getElementById('uploadform-web-cancel');

        cancelButton.addEventListener('click', () => {
          document.getElementById('uploadform-web').classList.add('hidden');

          // jQuery('#jed-container').slideDown(300);
          if (webInstallerOptions.list && document.querySelector('.list-view')) {
            document.querySelector('.list-view').click();
          }
        });

        webTab.insertAdjacentHTML('afterbegin', '<div id="appsloading" class="ifw-loading-container"></div>');
        webTab.style.position = 'absolute';

        jQuery('#appsloading').on('ajaxStart', () => {
          document.body.classList.add('ifw-busy');
          document.getElementById('appsloading').classList.remove('hidden');
        }).on('ajaxStop', () => {
          document.getElementById('appsloading').classList.add('hidden');
          document.body.classList.remove('ifw-busy');
        });
      }

      this.loadweb(`${webInstallerOptions.options.base_url}index.php?format=json&option=com_apps&view=dashboard`);

      this.clickforlinks();
    }

    loadweb(url) {
      if (!url) {
        return false;
      }

      const self = this;
      const pattern1 = new RegExp(webInstallerOptions.options.base_url);
      const pattern2 = new RegExp('^index.php');

      if (!(pattern1.test(url) || pattern2.test(url))) {
        window.open(url, '_blank');

        return false;
      }

      let requestUrl = `${url}&product=${webInstallerOptions.options.product}&release=${webInstallerOptions.options.release}&dev_level=${webInstallerOptions.options.dev_level}&list=${webInstallerOptions.list ? 'list' : 'grid'}&lang=${webInstallerOptions.options.language}`;

      if (webInstallerOptions.ordering !== '' && document.getElementById('com-apps-ordering').value) {
        webInstallerOptions.ordering = document.getElementById('com-apps-ordering').value;
        requestUrl += `&ordering=${webInstallerOptions.ordering}`;
      }

      // jQuery('html, body').animate({ scrollTop: 0 }, 0);
      if (document.getElementById('myTabContent')) {
        const element = document.getElementById('appsloading');
        element.style.position = 'absolute';
        element.style.left = '0';
        element.style.top = '0';
        element.style.width = '100%';
        element.style.height = '100%';

        const web = document.getElementById('web');
        web.style.position = 'relative';
        web.appendChild(element);

        jQuery('#appsloading').trigger('ajaxStart');
      }

      // @todo convert to vanilla, (why JSONP?)
      jQuery.ajax({
        url: requestUrl,
        dataType: 'jsonp',
        cache: true,
        jsonpCallback: 'jedapps_jsonpcallback',
        timeout: 20000,
        success(response) {
          if (document.getElementById('web-loader')) {
            document.getElementById('web-loader').classList.add('hidden');
          }

          const jedContainer = document.getElementById('jed-container');
          jedContainer.innerHTML = response.data.html;

          document.getElementById('com-apps-searchbox').addEventListener('keypress', (event) => {
            if (event.which === 13) {
              self.initiateSearch();
            }
          });

          document.getElementById('search-extensions').addEventListener('click', () => {
            self.initiateSearch();
          });

          document.getElementById('search-reset').addEventListener('click', () => {
            const searchBox = document.getElementById('com-apps-searchbox');
            searchBox.value = '';
            self.initiateSearch();
          });

          const orderingSelect = document.getElementById('com-apps-ordering');

          if (orderingSelect) {
            orderingSelect.addEventListener('change', () => {
              const index = orderingSelect.selectedIndex;
              webInstallerOptions.ordering = orderingSelect.options[index].value;
              self.installfromwebajaxsubmit();
            });
          }

          if (webInstallerOptions.options.installfrom_url !== '') {
            self.installfromweb(webInstallerOptions.options.installfrom_url);
          }
        },
        fail() {
          if (document.getElementById('web-loader')) {
            document.getElementById('web-loader').classList.add('hidden');
            document.getElementById('web-loader-error').classList.remove('hidden');
          }
        },
        complete() {
          const installAtField = document.getElementById('joomlaapsinstallatinput');

          if (installAtField) {
            installAtField.value = webInstallerOptions.options.installat_url;
          }

          self.clickforlinks();
          WebInstaller.clicker();

          if (webInstallerOptions.view !== 'extension') {
            [].slice.call(document.querySelectorAll('div.load-extension')).forEach((element) => {
              element.addEventListener('click', (event) => {
                event.preventDefault();
                self.processLinkClick(element.getAttribute('data-url'));
              });

              element.setAttribute('href', '#');
            });
          }

          if (webInstallerOptions.view === 'extension') {
            const installExtensionButton = document.getElementById('install-extension');
            const installExtensionFromExternalButton = document.getElementById('install-extension-from-external');

            if (installExtensionButton) {
              installExtensionButton.addEventListener('click', () => {
                self.installfromweb(installExtensionButton.getAttribute('data-downloadurl'), installExtensionButton.getAttribute('data-name'));
              });
            }

            if (installExtensionFromExternalButton) {
              // @todo Migrate this handler's confirm to a CE dialog
              installExtensionFromExternalButton.addEventListener('click', () => {
                const redirectUrl = installExtensionFromExternalButton.getAttribute('data-downloadurl');
                const redirectConfirm = window.confirm(Joomla.JText._('PLG_INSTALLER_WEBINSTALLER_REDIRECT_TO_EXTERNAL_SITE_TO_INSTALL').replace('[SITEURL]', redirectUrl));

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

          if (document.getElementById('myTabContent')) {
            jQuery('#appsloading').trigger('ajaxStop');
          }
        },
        error(request) {
          const errorContainer = document.getElementById('web-loader-error');
          const loaderContainer = document.getElementById('web-loader');

          if (request.responseText && errorContainer) {
            errorContainer.innerHTML = request.responseText;
          }

          if (loaderContainer) {
            loaderContainer.classList.add('hidden');
            errorContainer.classList.remove('hidden');
          }
        },
      });

      return true;
    }

    clickforlinks() {
      const self = this;

      [].slice.call(document.querySelectorAll('a.transcode')).forEach((element) => {
        const ajaxurl = element.getAttribute('href');

        element.addEventListener('click', (event) => {
          event.preventDefault();
          self.processLinkClick(ajaxurl);
        });

        element.setAttribute('href', '#');
      });
    }

    initiateSearch() {
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

      if (webInstallerOptions.ordering !== '' && document.getElementById('com-apps-ordering').value) {
        webInstallerOptions.ordering = document.getElementById('com-apps-ordering').value;
      }

      if (webInstallerOptions.ordering) {
        tail += `&ordering=${webInstallerOptions.ordering}`;
      }

      this.loadweb(`${webInstallerOptions.options.base_url}index.php?format=json&option=com_apps${tail}`);
    }

    processLinkClick(url) {
      const pattern1 = new RegExp(webInstallerOptions.options.base_url);
      const pattern2 = new RegExp('^index.php');

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
     * @todo Migrate this function's alert to a CE dialog
     */
    installfromweb(installUrl, name) {
      if (!installUrl) {
        alert(Joomla.JText._('PLG_INSTALLER_WEBINSTALLER_CANNOT_INSTALL_EXTENSION_IN_PLUGIN'));

        return false;
      }

      const installUrlField = document.getElementById('install_url');
      const uploadUrlContainer = document.getElementById('uploadform-web-url');

      installUrlField.value = installUrl;
      uploadUrlContainer.innerHTML = installUrl;

      if (name) {
        const nameElement = document.getElementById('uploadform-web-name');
        nameElement.innerHTML = name;
        document.getElementById('uploadform-web-name-label').classList.remove('hidden');
      } else {
        document.getElementById('uploadform-web-name-label').classList.add('hidden');
      }

      // jQuery('#jed-container').slideUp(300);
      document.getElementById('uploadform-web').classList.remove('hidden');

      return true;
    }
  }

  jQuery(($) => {
    const link = $('#myTabTabs').find('a[href="#web"]');

    if (webInstallerOptions.options.installfromon) {
      link.click();
    }

    if (link.hasClass('active')) {
      if (!instance) {
        instance = new WebInstaller();
        instance.initialise();
      }
    }

    link.closest('li').click(() => {
      if (!instance) {
        instance = new WebInstaller();
        instance.initialise();
      }
    });

    if (webInstallerOptions.options.installfrom_url !== '') {
      link.closest('li').click();
    }

    link.on('shown.bs.tab', () => {
      if (!instance) {
        instance = new WebInstaller();
        instance.initialise();
      }
    });
  });
})(window, document, Joomla, window.jQuery);
