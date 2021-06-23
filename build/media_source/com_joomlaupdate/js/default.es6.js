/**
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  Joomla.submitbuttonUpload = () => {
    const form = document.getElementById('uploadForm');

    // do field validation
    if (form.install_package.value === '') {
      alert(Joomla.Text._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
    } else if (form.install_package.files[0].size > form.max_upload_size.value) {
      alert(Joomla.Text._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG'), true);
    } else {
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
    const uploadButton = document.getElementById('uploadButton');
    const downloadMsg = document.getElementById('downloadMessage');

    if (uploadButton) {
      uploadButton.addEventListener('click', () => {
        if (downloadMsg) {
          downloadMsg.classList.remove('hidden');
        }
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

  /**
   * Run the PreUpdateChecker.
   * Called by document ready, setup below.
   */
  PreUpdateChecker.run = () => {
    [].slice.call(document.querySelectorAll('.settingstoggle')).forEach((el) => {
      el.style.float = 'right';
      el.style.cursor = 'pointer';
      el.addEventListener('click', () => {
        const settingsfieldset = el.closest('fieldset');
        if (el.dataset.state === 'closed') {
          el.dataset.state = 'open';
          el.innerHTML = Joomla.sanitizeHtml(Joomla.getOptions('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_LESS_COMPATIBILITY_INFORMATION'));
          settingsfieldset.querySelectorAll('.settingsInfo').forEach((fieldset) => {
            fieldset.classList.remove('hidden');
          });
        } else {
          el.dataset.state = 'closed';
          el.innerHTML = Joomla.sanitizeHtml(Joomla.getOptions('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION'));
          settingsfieldset.querySelectorAll('.settingsInfo').forEach((fieldset) => {
            fieldset.classList.add('hidden');
          });
        }
      });
    });

    // eslint-disable-next-line no-undef
    PreUpdateChecker.nonCoreCriticalPlugins = Joomla.getOptions('nonCoreCriticalPlugins', []);

    // If there are no non Core Critical Plugins installed, and we are in the update view, then
    // disable the warnings upfront
    if (PreUpdateChecker.nonCoreCriticalPlugins.length === 0 && document.getElementById('updateView') !== null) {
      document.getElementById('preupdateCheckWarning').style.display = 'none';
      document.getElementById('preupdateconfirmation').style.display = 'none';
      document.getElementById('preupdatecheckbox').style.display = 'none';
      document.getElementById('preupdatecheckheadings').style.display = 'none';
      document.getElementById('preupdatecheckbox').checked = true;
      document.getElementById('noncoreplugins').checked = true;

      [].slice.call(document.querySelectorAll('button.submitupdate')).forEach((el) => {
        el.classList.remove('disabled');
        el.removeAttribute('disabled');
      });
    }

    // Grab all extensions based on the selector set in the config object
    const extensions = document.querySelectorAll(PreUpdateChecker.config.selector);

    // If there are no extensions to be checked we can exit here
    if (extensions.length === 0) {
      return;
    }

    const onChangeEvent = () => {
      const preUpdateCheckbox = document.getElementById('preupdatecheckbox').checked;
      const nonCorePluginCheckbox = document.getElementById('noncoreplugins').checked;
      if (preUpdateCheckbox && nonCorePluginCheckbox) {
        if (window.confirm(Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_CONFIRM_MESSAGE'))) {
          [].slice.call(document.querySelectorAll('button.submitupdate')).forEach((el) => {
            el.classList.remove('disabled');
            el.removeAttribute('disabled');
          });
        } else {
          document.getElementById('preupdatecheckbox').checked = true;
          document.getElementById('noncoreplugins').checked = true;
        }
      } else {
        [].slice.call(document.querySelectorAll('button.submitupdate')).forEach((el) => {
          el.classList.remove('disabled');
          el.removeAttribute('disabled');
        });
      }
    };

    if (document.getElementById('updateView') !== null) {
      document.getElementById('preupdatecheckbox').addEventListener('change', onChangeEvent);
      document.getElementById('noncoreplugins').addEventListener('change', onChangeEvent);
    }

    // Get version of the available joomla update
    const joomlaUpdateWrapper = document.getElementById('joomlaupdate-wrapper');
    PreUpdateChecker.joomlaTargetVersion = joomlaUpdateWrapper.getAttribute('data-joomla-target-version');
    PreUpdateChecker.joomlaCurrentVersion = joomlaUpdateWrapper.getAttribute('data-joomla-current-version');

    // No point creating and loading a component stylesheet for 4 settings
    [].slice.call(document.querySelectorAll('.compatibilitytypes img')).forEach((el) => {
      el.style.height = '20px';
    });
    [].slice.call(document.querySelectorAll('.compatibilitytypes')).forEach((el) => {
      el.style.display = 'none';
      el.style.marginLeft = 0;
    });
    // The currently processing line should show until it’s finished
    const compatibilityType0 = document.getElementById('compatibilitytype0');

    if (compatibilityType0) {
      compatibilityType0.style.display = 'block';
    }

    [].slice.call(document.querySelectorAll('.compatibilitytoggle')).forEach((el) => {
      el.style.float = 'right';
      el.style.cursor = 'pointer';
      el.addEventListener('click', () => {
        const compatibilitytypes = el.closest('fieldset.compatibilitytypes');

        if (el.dataset.state === 'closed') {
          el.dataset.state = 'open';
          el.innerHTML = Joomla.sanitizeHtml(Joomla.getOptions('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_LESS_COMPATIBILITY_INFORMATION'));

          [].slice.call(compatibilitytypes.querySelectorAll('.exname')).forEach((extension) => {
            extension.classList.remove('col-md-8');
            extension.classList.add('col-md-4');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.extype')).forEach((extension) => {
            extension.classList.remove('col-md-4');
            extension.classList.add('col-md-1');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.upcomp')).forEach((extension) => {
            extension.classList.remove('hidden');
            extension.classList.add('col-md-3');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.currcomp')).forEach((extension) => {
            extension.classList.remove('hidden');
            extension.classList.add('col-md-3');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.instver')).forEach((extension) => {
            extension.classList.remove('hidden');
            extension.classList.add('col-md-1');
          });

          if (PreUpdateChecker.showyellowwarning && compatibilitytypes.querySelector('#updateyellowwarning')) {
            compatibilitytypes.querySelector('#updateyellowwarning').classList.remove('hidden');
          }

          if (PreUpdateChecker.showorangewarning && compatibilitytypes.querySelector('#updateorangewarning')) {
            compatibilitytypes.querySelector('#updateorangewarning').classList.remove('hidden');
          }
        } else {
          el.dataset.state = 'closed';
          el.innerHTML = Joomla.sanitizeHtml(Joomla.getOptions('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION'));

          [].slice.call(compatibilitytypes.querySelectorAll('.exname')).forEach((extension) => {
            extension.classList.add('col-md-8');
            extension.classList.remove('col-md-4');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.extype')).forEach((extension) => {
            extension.classList.add('col-md-4');
            extension.classList.remove('col-md-1');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.upcomp')).forEach((extension) => {
            extension.classList.add('hidden');
            extension.classList.remove('col-md-3');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.currcomp')).forEach((extension) => {
            extension.classList.add('hidden');
            extension.classList.remove('col-md-3');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.instver')).forEach((extension) => {
            extension.classList.add('hidden');
            extension.classList.remove('col-md-1');
          });

          if (PreUpdateChecker.showyellowwarning && compatibilitytypes.querySelector('#updateyellowwarning')) {
            compatibilitytypes.querySelector('#updateyellowwarning').classList.add('hidden');
          }

          if (PreUpdateChecker.showorangewarning && compatibilitytypes.querySelector('#updateorangewarning')) {
            compatibilitytypes.querySelector('#updateorangewarning').classList.add('hidden');
          }
        }
      });
    });

    // Grab all extensions based on the selector set in the config object
    [].slice.call(extensions)
      .forEach((extension) => {
        // Check compatibility for each extension, pass an object and a callback
        // function after completing the request
        PreUpdateChecker.checkCompatibility(extension, PreUpdateChecker.setResultView);
      });
  };

  /**
   * Check the compatibility for a single extension.
   * Requests the server checking the compatibility based
   * on the data set in the element's data attributes.
   *
   * @param {Object} extension
   * @param {callable} callback
   */
  PreUpdateChecker.checkCompatibility = (node, callback) => {
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
        callback(extension);
      },
      onError() {
        // Pass the retrieved data to the callback
        callback(extension);
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
    // eslint-disable-next-line max-len
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
            // eslint-disable-next-line max-len
            html = `<span class="label label-warning">${extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion}</span>`;
            PreUpdateChecker.showyellowwarning = true;
          } else {
            // eslint-disable-next-line max-len
            html = extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion === false
              ? Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION')
              : extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion;
          }
          break;
        case PreUpdateChecker.STATE.INCOMPATIBLE:
          // No compatible version found -> display error label
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          PreUpdateChecker.showorangewarning = true;
          break;
        case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
          // Could not check compatibility state -> display warning
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          PreUpdateChecker.showorangewarning = true;
          break;
        default:
          // An error occurred -> show unknown error note
          html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
      }
    }

    // Insert the generated html
    extensionData.element.innerHTML = Joomla.sanitizeHtml(html);

    // Process Current Version Extension Compatibility
    html = '';
    if (extensionData.serverError) {
      // An error occurred -> show unknown error note
      html = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
    } else {
      // Switch the compatibility state
      switch (extensionData.compatibilityData.currentCompatibilityStatus.state) {
        case PreUpdateChecker.STATE.COMPATIBLE:
          // eslint-disable-next-line max-len
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

    const compatType = document.querySelector(`#compatibilitytype${extensionData.compatibilityData.resultGroup} tbody`);

    if (compatType) {
      compatType.appendChild(extensionData.element.closest('tr'));
    }

    document.getElementById(`compatibilitytype${extensionData.compatibilityData.resultGroup}`).style.display = 'block';
    document.getElementById('compatibilitytype0').style.display = 'block';

    // Process the nonCoreCriticalPlugin list
    if (extensionData.compatibilityData.resultGroup === 3) {
      PreUpdateChecker.nonCoreCriticalPlugins.forEach((plugin, cpi) => {
        if (plugin.package_id.toString() === extensionId
            || plugin.extension_id.toString() === extensionId) {
          document.getElementById(`#plg_${plugin.extension_id}`).remove();
          PreUpdateChecker.nonCoreCriticalPlugins.splice(cpi, 1);
        }
      });
    }

    // Have we finished running through the potentially critical plugins - if so we can hide the
    // warning before all the checks are completed
    const headingsElement = document.getElementById('preupdatecheckheadings');
    if (headingsElement && headingsElement.querySelectorAll('table td').length === 0) {
      headingsElement.style.display = 'none';
    }

    // Have we finished?
    if (!document.querySelector('#compatibilitytype0 tbody td')) {
      document.getElementById('compatibilitytype0').style.display = 'none';
      PreUpdateChecker.nonCoreCriticalPlugins.forEach((plugin) => {
        let problemPluginRow = document.querySelector(`td[data-extension-id="${plugin.extension_id}"]`);
        if (!problemPluginRow) {
          problemPluginRow = document.querySelector(`td[data-extension-id="${plugin.package_id}"]`);
        }
        if (problemPluginRow) {
          const tableRow = problemPluginRow.closest('tr');
          tableRow.classList.add('error');
          const pluginTitleTableCell = tableRow.querySelector('td:first-child');
          pluginTitleTableCell.innerHTML = Joomla.sanitizeHtml(`${pluginTitleTableCell.innerHTML}
              <span class="label label-warning " >
              <span class="icon-warning"></span>
              ${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN')}
              </span>

              <span class="label label-important hasPopover"
              title="${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN')} "
              data-bs-content="${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_DESC')} "
              >
              <span class="icon-help"></span>
              ${Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_HELP')}
              </span>`);
          const popoverElement = pluginTitleTableCell.querySelector('.hasPopover');
          if (popoverElement) {
            popoverElement.style.cursor = 'pointer';
            // eslint-disable-next-line no-new
            new bootstrap.Popover(popoverElement, { placement: 'top', html: true, trigger: 'focus click' });
          }
        }
      });

      // If we aren't in the update view now - bail early.
      if (document.getElementById('updateView') === null) {
        return;
      }

      if (PreUpdateChecker.nonCoreCriticalPlugins.length === 0) {
        document.getElementById('preupdateCheckWarning').style.display = 'none';
        document.getElementById('preupdateconfirmation').style.display = 'none';
        document.getElementById('preupdatecheckbox').style.display = 'none';
        document.getElementById('preupdatecheckheadings').style.display = 'none';
        document.getElementById('preupdatecheckbox').checked = true;
        document.getElementById('noncoreplugins').checked = true;

        [].slice.call(document.querySelectorAll('button.submitupdate')).forEach((el) => {
          el.classList.remove('disabled');
          el.removeAttribute('disabled');
        });
      } else {
        document.getElementById('preupdateCheckWarning').classList.add('hidden');
        document.getElementById('preupdateCheckCompleteProblems').classList.remove('hidden');

        [].slice.call(document.querySelectorAll('#preupdateconfirmation .preupdateconfirmation_label h3')).forEach((el) => {
          el.innerText = Joomla.Text._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_LIST');
        });

        [].slice.call(document.querySelectorAll('#preupdateconfirmation .preupdateconfirmation_label')).forEach((el) => {
          el.classList.add('label-important');
          el.classList.remove('label-warning');
        });
      }
    }
  };
  // Run PreUpdateChecker on document ready
  document.addEventListener('DOMContentLoaded', PreUpdateChecker.run, false);
})(Joomla, document);
