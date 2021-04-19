/**
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  Joomla.extractionMethodHandler = (element, prefix) => {
    const dom = [
      `${prefix}_hostname`,
      `${prefix}_port`,
      `${prefix}_username`,
      `${prefix}_password`,
      `${prefix}_directory`,
    ];

    if (element.value === 'direct') {
      dom.map((el) => {
        document.getElementById(el).style.display = 'none';
        return el;
      });
    } else {
      dom.map((el) => {
        document.getElementById(el).style.display = '';
        return el;
      });
    }
  };

  Joomla.submitbuttonUpload = () => {
    const form = document.getElementById('uploadForm');

    // do field validation
    if (form.install_package.value === '') {
      alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
    } else if (form.install_package.files[0].size > form.max_upload_size.value) {
      alert(Joomla.JText._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG'), true);
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
      fileSizeElement.innerHTML = Joomla.JText._('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE').replace('%s', `${fileSizeMB.toFixed(2)} MB`);

      if (fileSize > form.max_upload_size.value) {
        warningElement.classList.remove('hidden');
      } else {
        warningElement.classList.add('hidden');
      }
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    const extractionMethod = document.getElementById('extraction_method');
    const uploadMethod = document.getElementById('upload_method');
    const uploadButton = document.getElementById('uploadButton');
    const downloadMsg = document.getElementById('downloadMessage');

    if (extractionMethod) {
      extractionMethod.addEventListener('change', () => {
        Joomla.extractionMethodHandler(extractionMethod, 'row_ftp');
      });
    }

    if (uploadMethod) {
      uploadMethod.addEventListener('change', () => {
        Joomla.extractionMethodHandler(uploadMethod, 'upload_ftp');
      });
    }

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
          // eslint-disable-next-line max-len,no-undef
          el.innerHTML = COM_JOOMLAUPDATE_VIEW_DEFAULT_SHOW_LESS_EXTENSION_COMPATIBILITY_INFORMATION;

          [].slice.call(compatibilitytypes.querySelectorAll('.exname')).forEach((extension) => {
            extension.classList.remove('col-md-8');
            extension.classList.add('col-md-4');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.extype')).forEach((extension) => {
            extension.classList.remove('col-md-4');
            extension.classList.add('col-md-2');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.upcomp')).forEach((extension) => {
            extension.classList.remove('hidden');
            extension.classList.add('col-md-2');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.currcomp')).forEach((extension) => {
            extension.classList.remove('hidden');
            extension.classList.add('col-md-2');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.instver')).forEach((extension) => {
            extension.classList.remove('hidden');
            extension.classList.add('col-md-2');
          });

          if (PreUpdateChecker.showyellowwarning && compatibilitytypes.querySelector('#updateyellowwarning')) {
            compatibilitytypes.querySelector('#updateyellowwarning').classList.remove('hidden');
          }

          if (PreUpdateChecker.showorangewarning && compatibilitytypes.querySelector('#updateorangewarning')) {
            compatibilitytypes.querySelector('#updateorangewarning').classList.remove('hidden');
          }
        } else {
          el.dataset.state = 'closed';
          // eslint-disable-next-line max-len,no-undef
          el.innerHTML = COM_JOOMLAUPDATE_VIEW_DEFAULT_SHOW_MORE_EXTENSION_COMPATIBILITY_INFORMATION;

          [].slice.call(compatibilitytypes.querySelectorAll('.exname')).forEach((extension) => {
            extension.classList.add('col-md-8');
            extension.classList.remove('col-md-4');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.extype')).forEach((extension) => {
            extension.classList.add('col-md-4');
            extension.classList.remove('col-md-2');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.upcomp')).forEach((extension) => {
            extension.classList.add('hidden');
            extension.classList.remove('col-md-2');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.currcomp')).forEach((extension) => {
            extension.classList.add('hidden');
            extension.classList.remove('col-md-2');
          });

          [].slice.call(compatibilitytypes.querySelectorAll('.instver')).forEach((extension) => {
            extension.classList.add('hidden');
            extension.classList.remove('col-md-2');
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
    [].slice.call(document.querySelectorAll(PreUpdateChecker.config.selector))
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
      }joomla-current-version=${PreUpdateChecker.joomlaCurrentVersion
      }extension-version=${node.getAttribute('data-extension-current-version')
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
      html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
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
              ? Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION')
              : extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion;
          }
          break;
        case PreUpdateChecker.STATE.INCOMPATIBLE:
          // No compatible version found -> display error label
          html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          PreUpdateChecker.showorangewarning = true;
          break;
        case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
          // Could not check compatibility state -> display warning
          html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          PreUpdateChecker.showorangewarning = true;
          break;
        default:
          // An error occured -> show unknown error note
          html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
      }
    }

    // Insert the generated html
    extensionData.element.innerHTML = html;

    // Process Current Version Extension Compatibility
    html = '';
    if (extensionData.serverError) {
      // An error occured -> show unknown error note
      html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
    } else {
      // Switch the compatibility state
      switch (extensionData.compatibilityData.currentCompatibilityStatus.state) {
        case PreUpdateChecker.STATE.COMPATIBLE:
          // eslint-disable-next-line max-len
          html = extensionData.compatibilityData.currentCompatibilityStatus.compatibleVersion === false
            ? Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION')
            : extensionData.compatibilityData.currentCompatibilityStatus.compatibleVersion;
          break;
        case PreUpdateChecker.STATE.INCOMPATIBLE:
          // No compatible version found -> display error label
          html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          break;
        case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
          // Could not check compatibility state -> display warning
          html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
          break;
        default:
          // An error occured -> show unknown error note
          html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
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

    // Have we finished?
    if (!document.querySelector('#compatibilitytype0 tbody td')) {
      document.getElementById('compatibilitytype0').style.display = 'none';
    }
  };
  // Run PreUpdateChecker on document ready
  document.addEventListener('DOMContentLoaded', PreUpdateChecker.run, false);
})(Joomla, document);
