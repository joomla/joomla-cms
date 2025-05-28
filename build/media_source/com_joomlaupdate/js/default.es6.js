/**
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  Joomla.submitbuttonUpload = () => {
    const form = document.getElementById('uploadForm');
    const confirmBackup = document.getElementById('joomlaupdate-confirm-backup');

    // do field validation
    if (form.install_package.value === '') {
      alert(Joomla.Text._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
    } else if (form.install_package.files[0].size > form.max_upload_size.value) {
      alert(Joomla.Text._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG'), true);
    } else if (confirmBackup && confirmBackup.checked) {
      form.submit();
    }
  };

  Joomla.installpackageChange = () => {
    const form = document.getElementById('uploadForm');
    const fileSize = form.install_package.files[0].size;
    const fileSizeMB = (fileSize * 1.0) / 1024.0 / 1024.0;
    const fileSizeElement = document.getElementById('file_size');
    const warningElement = document.getElementById('max_upload_size_warn');

    if (form.install_package.value === '') {
      fileSizeElement.classList.add('hidden');
      warningElement.classList.add('hidden');
    } else if (fileSize) {
      fileSizeElement.classList.remove('hidden');
      fileSizeElement.innerHTML = Joomla.sanitizeHtml(Joomla.Text._('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE').replace('%s', `${fileSizeMB.toFixed(2)} MB`));

      if (fileSize > form.max_upload_size.value) {
        warningElement.classList.remove('hidden');
      } else {
        warningElement.classList.add('hidden');
      }
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    const confirmButton = document.getElementById('confirmButton');
    const uploadForm = document.getElementById('uploadForm');
    const uploadButton = document.getElementById('uploadButton');
    const uploadField = document.getElementById('install_package');
    const installButton = document.querySelector('.emptystate-btnadd', document.getElementById('joomlaupdate-wrapper'));
    const updateCheck = document.getElementById('joomlaupdate-confirm-backup');
    const form = installButton ? installButton.closest('form') : null;
    const task = form ? form.querySelector('[name=task]', form) : null;
    if (uploadButton) {
      uploadButton.addEventListener('click', Joomla.submitbuttonUpload);
      uploadButton.disabled = updateCheck && !updateCheck.checked;
      if (updateCheck) {
        updateCheck.addEventListener('change', () => {
          uploadButton.disabled = !updateCheck.checked;
        });
      }
    }
    if (confirmButton && updateCheck && !updateCheck.checked) {
      confirmButton.classList.add('disabled');
    }
    if (confirmButton && updateCheck) {
      updateCheck.addEventListener('change', () => {
        if (updateCheck.checked) {
          confirmButton.classList.remove('disabled');
        } else {
          confirmButton.classList.add('disabled');
        }
      });
    }
    if (uploadField) {
      uploadField.addEventListener('change', Joomla.installpackageChange);
      if (updateCheck) {
        uploadField.addEventListener('change', () => {
          const fileSize = uploadForm.install_package.files[0].size;
          const allowedSize = uploadForm.max_upload_size.value;
          if (fileSize <= allowedSize && updateCheck.disabled) {
            updateCheck.disabled = !updateCheck.disabled;
          } else if (fileSize <= allowedSize && !updateCheck.disabled && !updateCheck.checked) {
            updateCheck.disabled = false;
          } else if (fileSize <= allowedSize && updateCheck.checked) {
            updateCheck.checked = updateCheck.classList.contains('d-none');
            uploadButton.disabled = true;
          } else if (fileSize > allowedSize && !updateCheck.disabled) {
            updateCheck.disabled = !updateCheck.disabled;
            updateCheck.checked = updateCheck.classList.contains('d-none');
            uploadButton.disabled = true;
          }
        });
      }
    }
    // Trigger (re-) install (including checkbox confirm if we update)
    if (installButton && installButton.getAttribute('href') === '#' && task) {
      installButton.addEventListener('click', (e) => {
        e.preventDefault();
        if (updateCheck && !updateCheck.checked) {
          return;
        }
        task.value = 'update.download';
        form.submit();
      });
    }
  });
})(Joomla, document);

((Joomla, document) => {
  /**
   * PreUpdateChecker
   *
   * @type {Object}
   */
  const PreUpdateChecker = {};

  /**
   * Config object
   *
   * @type {{serverUrl: string, selector: string}}
   */
  PreUpdateChecker.config = {
    serverUrl: 'index.php?option=com_joomlaupdate&task=update.fetchextensioncompatibility',
    batchUrl: 'index.php?option=com_joomlaupdate&task=update.batchextensioncompatibility',
    selector: '.extension-check',
  };

  /**
   * Extension compatibility states returned by the server.
   *
   * @type {{
   * INCOMPATIBLE: number,
   * COMPATIBLE: number,
   * MISSING_COMPATIBILITY_TAG: number,
   * SERVER_ERROR: number}}
   */
  PreUpdateChecker.STATE = {
    INCOMPATIBLE: 0,
    COMPATIBLE: 1,
    MISSING_COMPATIBILITY_TAG: 2,
    SERVER_ERROR: 3,
  };

  PreUpdateChecker.cleanup = (status) => {
    // Set the icon in the nav-tab
    const infoIcon = document.querySelector('#joomlaupdate-precheck-extensions-tab .fa-spinner');

    let iconColor = 'success';
    let iconClass = 'check';

    switch (status) {
      case 'danger':
        iconColor = 'danger';
        iconClass = 'times';
        break;
      case 'warning':
        iconColor = 'warning';
        iconClass = 'exclamation-triangle';
        break;
      default:
    }
    if (infoIcon) {
      infoIcon.classList.remove('fa-spinner', 'fa-spin');
      infoIcon.classList.add(`fa-${iconClass}`, `text-${iconColor}`, 'bg-white');
    }
    // Hide table of addons to load
    const checkedExtensions = document.querySelector('#compatibilityTable0');
    const preupdateCheckWarning = document.querySelector('#preupdateCheckWarning');
    if (checkedExtensions) {
      checkedExtensions.classList.add('hidden');
    }
    if (preupdateCheckWarning) {
      preupdateCheckWarning.classList.add('hidden');
    }
  };

  /**
   * Run the PreUpdateChecker.
   * Called by document ready, setup below.
   */
  PreUpdateChecker.run = () => {
    // eslint-disable-next-line no-undef
    PreUpdateChecker.nonCoreCriticalPlugins = Joomla.getOptions('nonCoreCriticalPlugins', []);

    // Grab all extensions based on the selector set in the config object
    const extensions = document.querySelectorAll(PreUpdateChecker.config.selector);

    // If there are no extensions to be checked we can exit here
    if (extensions.length === 0) {
      if (document.getElementById('preupdatecheckbox') !== null) {
        document.getElementById('preupdatecheckbox').style.display = 'none';
      }
      if (document.getElementById('noncoreplugins') !== null) {
        document.getElementById('noncoreplugins').checked = true;
      }
      document.querySelectorAll('button.submitupdate').forEach((el) => {
        el.classList.remove('disabled');
        el.removeAttribute('disabled');
      });
      PreUpdateChecker.cleanup();
      return;
    }

    // Let the user make an update although there *could* be dangerous plugins in the wild
    const onChangeEvent = () => {
      const nonCorePluginCheckbox = document.getElementById('noncoreplugins');
      if (nonCorePluginCheckbox.checked) {
        if (window.confirm(Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_CONFIRM_MESSAGE'))) {
          document.querySelectorAll('button.submitupdate').forEach((el) => {
            el.classList.remove('disabled');
            el.removeAttribute('disabled');
          });
        } else {
          nonCorePluginCheckbox.checked = false;
        }
      } else {
        document.querySelectorAll('button.submitupdate').forEach((el) => {
          el.classList.add('disabled');
          el.setAttribute('disabled', '');
        });
      }
    };

    if (document.getElementById('noncoreplugins') !== null) {
      document.getElementById('noncoreplugins').addEventListener('change', onChangeEvent);
    }

    // Get version of the available joomla update
    const joomlaUpdateWrapper = document.getElementById('joomlaupdate-wrapper');
    PreUpdateChecker.joomlaTargetVersion = joomlaUpdateWrapper.getAttribute('data-joomla-target-version');
    PreUpdateChecker.joomlaCurrentVersion = joomlaUpdateWrapper.getAttribute('data-joomla-current-version');

    document.querySelectorAll('.compatibilitytoggle').forEach((el) => {
      el.addEventListener('click', () => {
        const compatibilityTable = el.closest('.compatibilityTable');

        if (el.dataset.state === 'closed') {
          el.dataset.state = 'open';
          el.innerHTML = Joomla.sanitizeHtml(Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_LESS_COMPATIBILITY_INFORMATION'));

          compatibilityTable.querySelectorAll('table .hidden').forEach((elem) => elem.classList.remove('hidden'));
        } else {
          el.dataset.state = 'closed';
          el.innerHTML = Joomla.sanitizeHtml(Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION'));

          compatibilityTable.querySelectorAll('table .instver, table .upcomp, table .currcomp').forEach((elem) => elem.classList.add('hidden'));
        }
      });
    });

    // Grab all extensions based on the selector set in the config object
    const extensionsInformation = [];

    extensions.forEach((extension) => extensionsInformation.push({
      eid: extension.getAttribute('data-extension-id'),
      version: extension.getAttribute('data-extension-current-version'),
    }));

    PreUpdateChecker.checkNextChunk(extensionsInformation);
  };

  /**
   * Converts a simple object containing query string parameters to a single, escaped query string
   *
   * @param    data   {object|string}  A plain object containing the query parameters to pass
   * @param    prefix {string}  Prefix for array-type parameters
   *
   * @returns  {string}
   */
  PreUpdateChecker.interpolateParameters = (data, prefix) => {
    let encodedString = '';

    if (typeof data !== 'object' || data === null || !data) {
      return '';
    }

    Object.keys(data).forEach((prop) => {
      const item = data[prop];

      if (encodedString.length > 0) {
        encodedString += '&';
      }

      // Scalar values
      if (typeof item === 'object') {
        const newPrefix = prefix.length ? `${prefix}[${prop}]` : prop;

        encodedString += PreUpdateChecker.interpolateParameters(item, newPrefix);

        return;
      }

      if (prefix === '') {
        encodedString += `${encodeURIComponent(prop)}=${encodeURIComponent(item)}`;

        return;
      }

      encodedString += `${encodeURIComponent(prefix)}[${encodeURIComponent(prop)}]=${encodeURIComponent(item)}`;
    });

    return encodedString;
  };

  /**
   * Check the compatibility of several extensions.
   *
   * Asks the server to check the compatibility of as many extensions as possible. The server
   * returns these results and the remainder of the extensions not already checked.
   *
   * @param {Array} extensionsArray
   */
  PreUpdateChecker.checkNextChunk = (extensionsArray) => {
    if (extensionsArray.length === 0) {
      return;
    }

    Joomla.request({
      url: PreUpdateChecker.config.batchUrl,
      method: 'POST',
      data: PreUpdateChecker.interpolateParameters({
        'joomla-target-version': PreUpdateChecker.joomlaTargetVersion,
        'joomla-current-version': PreUpdateChecker.joomlaCurrentVersion,
        extensions: extensionsArray,
      }, ''),
      onSuccess(data) {
        const response = JSON.parse(data);

        if (response.messages) {
          Joomla.renderMessages(response.messages);
        }

        const extensions = response.data.extensions || [];

        response.data.compatibility.forEach((record) => {
          const node = document.getElementById(`preUpdateCheck_${record.id}`);

          if (!node) {
            return;
          }

          PreUpdateChecker.setResultView({
            element: node,
            compatibleVersion: 0,
            serverError: 0,
            compatibilityData: record,
          });
        });

        PreUpdateChecker.checkNextChunk(extensions);
      },
      onError(xhr) {
        // Report the XHR error
        Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));

        // Mark all pending extensions as errored out on the server side
        extensionsArray.forEach((info) => {
          const node = document.getElementById(`preUpdateCheck_${info.eid}`);

          if (!node) {
            return;
          }

          PreUpdateChecker.setResultView({
            element: node,
            compatibleVersion: 0,
            serverError: 1,
          });
        });
      },
    });
  };

  /**
   * Check the compatibility for a single extension.
   * Requests the server checking the compatibility based
   * on the data set in the element's data attributes.
   *
   * @param {Object} extension
   */
  PreUpdateChecker.checkCompatibility = (node) => {
    // Result object passed to the callback
    // Set to server error by default
    const extension = {
      element: node,
      compatibleVersion: 0,
      serverError: 1,
    };

    // Request the server to check the compatibility for the passed extension and joomla version
    Joomla.request({
      url: `${PreUpdateChecker.config.serverUrl
      }&joomla-target-version=${encodeURIComponent(PreUpdateChecker.joomlaTargetVersion)
      }&joomla-current-version=${PreUpdateChecker.joomlaCurrentVersion
      }&extension-version=${node.getAttribute('data-extension-current-version')
      }&extension-id=${encodeURIComponent(node.getAttribute('data-extension-id'))}`,
      onSuccess(data) {
        const response = JSON.parse(data);
        // Extract the data from the JResponseJson object
        extension.serverError = 0;
        extension.compatibilityData = response.data;
        // Pass the retrieved data to the callback
        PreUpdateChecker.setResultView(extension);
      },
      onError() {
        extension.serverError = 1;
        // Pass the retrieved data to the callback
        PreUpdateChecker.setResultView(extension);
      },
    });
  };

  /**
   * Set the result for a passed extensionData object containing state compatible version
   *
   * @param {Object} extensionData
   */
  PreUpdateChecker.setResultView = (extensionData) => {
    let html = '';
    // const direction = (document.dir !== undefined) ? document.dir : document.getElementsByTagName('html')[0].getAttribute('dir');

    // Process Target Version Extension Compatibility
    if (extensionData.serverError) {
      // An error occurred -> show unknown error note
      html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
      // Force result into group 4 = Pre update checks failed
      extensionData.compatibilityData = {
        resultGroup: 4,
      };
    } else {
      // Switch the compatibility state
      switch (extensionData.compatibilityData.upgradeCompatibilityStatus.state) {
        case PreUpdateChecker.STATE.COMPATIBLE:
          if (extensionData.compatibilityData.upgradeWarning) {
            const compatibleVersion = Joomla.sanitizeHtml(extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion);
            html = `<span class="label label-warning">${compatibleVersion}</span>`;
            // @TODO activate when language strings are correct
            /* if (compatibilitytypes.querySelector('#updateorangewarning')) {
              compatibilitytypes.querySelector('#updateorangewarning').classList.remove('hidden');
            } */
          } else {
            html = extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion === false
              ? Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION')
              : Joomla.sanitizeHtml(extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion);
          }
          break;
        case PreUpdateChecker.STATE.INCOMPATIBLE:
          // No compatible version found -> display error label
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          // @TODO activate when language strings are correct
          /* if (document.querySelector('#updateyellowwarning')) {
            document.querySelector('#updateyellowwarning').classList.remove('hidden');
          } */
          break;
        case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
          // Could not check compatibility state -> display warning
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          // @TODO activate when language strings are correct
          /* if (document.querySelector('#updateyellowwarning')) {
            document.querySelector('#updateyellowwarning').classList.remove('hidden');
          } */
          break;
        default:
          // An error occurred -> show unknown error note
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
      }
    }

    // Insert the generated html
    extensionData.element.innerHTML = html;

    // Process Current Version Extension Compatibility
    html = '';
    if (extensionData.serverError) {
      // An error occurred -> show unknown error note
      html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
    } else {
      // Switch the compatibility state
      switch (extensionData.compatibilityData.currentCompatibilityStatus.state) {
        case PreUpdateChecker.STATE.COMPATIBLE:
          html = extensionData.compatibilityData.currentCompatibilityStatus.compatibleVersion === false
            ? Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION')
            : extensionData.compatibilityData.currentCompatibilityStatus.compatibleVersion;
          break;
        case PreUpdateChecker.STATE.INCOMPATIBLE:
          // No compatible version found -> display error label
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          break;
        case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
          // Could not check compatibility state -> display warning
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          break;
        default:
          // An error occurred -> show unknown error note
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
      }
    }
    // Insert the generated html
    const extensionId = extensionData.element.getAttribute('data-extension-id');
    document.getElementById(`available-version-${extensionId}`).innerText = html;

    const compatType = document.querySelector(`#compatibilityTable${extensionData.compatibilityData.resultGroup} tbody`);

    if (compatType) {
      compatType.appendChild(extensionData.element.closest('tr'));
    }

    // Show the table
    document.getElementById(`compatibilityTable${extensionData.compatibilityData.resultGroup}`).classList.remove('hidden');

    // Process the nonCoreCriticalPlugin list
    if (extensionData.compatibilityData.resultGroup === 3) {
      PreUpdateChecker.nonCoreCriticalPlugins = PreUpdateChecker.nonCoreCriticalPlugins
        // eslint-disable-next-line max-len
        .filter((ext) => !(ext.package_id.toString() === extensionId || ext.extension_id.toString() === extensionId));
    }

    // Have we finished?
    if (!document.querySelector('#compatibilityTable0 tbody td')) {
      document.getElementById('compatibilityTable0').classList.add('hidden');
      let status = 'success';
      PreUpdateChecker.nonCoreCriticalPlugins.forEach((plugin) => {
        let problemPluginRow = document.querySelector(`td[data-extension-id="${plugin.extension_id}"]`);
        if (!problemPluginRow) {
          problemPluginRow = document.querySelector(`td[data-extension-id="${plugin.package_id}"]`);
        }
        if (problemPluginRow) {
          const tableRow = problemPluginRow.closest('tr');
          tableRow.classList.add('error');
          const pluginTitleTableCell = tableRow.querySelector('.exname');
          pluginTitleTableCell.innerHTML = `${Joomla.sanitizeHtml(pluginTitleTableCell.innerHTML)}
              <div class="small">
              ${document.querySelector(`td[data-extension-id="${plugin.extension_id}"]`) ? '' : ` - ${plugin.name}`}
              <span class="badge bg-warning">
              <span class="icon-warning"></span>
              ${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN')}
              </span>

              <button type="button" class="btn btn-sm btn-link hasPopover"
              title="${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN')} "
              data-bs-content="${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_DESC')} "
              >
              ${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_HELP')}
              </button>
              </div>`;
          const popoverElement = pluginTitleTableCell.querySelector('.hasPopover');
          if (popoverElement) {
            popoverElement.style.cursor = 'pointer';
            // eslint-disable-next-line no-new
            new bootstrap.Popover(popoverElement, { placement: 'top', html: true, trigger: 'focus' });
          }
          status = 'danger';
        }
      });
      // Updates required
      if (document.querySelector('#compatibilityTable2 tbody td')) {
        status = 'danger';
      } else if (status !== 'danger' && document.querySelector('#compatibilityTable1 tbody td')) {
        status = 'warning';
      }

      if (PreUpdateChecker.nonCoreCriticalPlugins.length === 0 && status === 'success' && document.getElementById('preupdatecheckbox')) {
        document.getElementById('preupdatecheckbox').style.display = 'none';
      }

      if (PreUpdateChecker.nonCoreCriticalPlugins.length === 0 && status === 'success' && document.getElementById('noncoreplugins')) {
        document.getElementById('noncoreplugins').checked = true;
      }

      if (PreUpdateChecker.nonCoreCriticalPlugins.length === 0 && status === 'success') {
        document.querySelectorAll('button.submitupdate').forEach((el) => {
          el.classList.remove('disabled');
          el.removeAttribute('disabled');
        });
      } else if (PreUpdateChecker.nonCoreCriticalPlugins.length > 0) {
        document.getElementById('preupdateCheckCompleteProblems').classList.remove('hidden');
      }

      PreUpdateChecker.cleanup(status);
    }
  };
  if (document.getElementById('preupdatecheck') !== null) {
    // Run PreUpdateChecker on document ready
    document.addEventListener('DOMContentLoaded', PreUpdateChecker.run, false);
  }
})(Joomla, document);
